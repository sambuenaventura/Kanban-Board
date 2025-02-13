<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\Task;
use App\Services\BoardService;
use App\Events\BoardCreated;
use App\Services\IdempotencyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;

class BoardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $boardService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create instances of the models
        $boardModel = new Board();
        $taskModel = new Task();
        $boardUserModel = new BoardUser();
        $userModel = new User();
        $boardInvitationModel = new BoardInvitation();
        $idempotencyService = new IdempotencyService();

        // Initialize the BoardService with model instances
        $this->boardService = new BoardService(
            $boardModel,
            $taskModel,
            $boardUserModel,
            $userModel,
            $boardInvitationModel,
            $idempotencyService,
        );
    }

    public function test_index_displays_owned_and_collaborated_boards_passed()
    {
        // Create a user
        $user = User::factory()->create();

        // Create boards owned by the user
        $ownedBoard = Board::factory()->create(['user_id' => $user->id]);

        // Create boards where the user is a collaborator
        $collaboratedBoard = Board::factory()->create();
        BoardUser::factory()->create([
            'board_id' => $collaboratedBoard->id,
            'user_id' => $user->id,
            'role' => 'collaborator'
        ]);

        // Acting as the user
        $this->actingAs($user)
            ->get(route('boards.index'))
            ->assertStatus(200)
            ->assertViewHas('boardsOwned')  // Assert that the owned boards are passed to the view
            ->assertViewHas('boardsCollaborated')  // Assert that the collaborated boards are passed to the view
            ->assertSee($ownedBoard->name)  // Check the owned board name appears in the view
            ->assertSee($collaboratedBoard->name);  // Check the collaborated board name appears in the view
    }
    
    public function test_index_does_not_show_a_specific_board_name()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board that should not be shown
        $board = Board::factory()->create(['user_id' => $user->id, 'name' => 'Specific Board']);
    
        // Delete the board
        $board->delete();
    
        // Acting as the user
        $this->actingAs($user)
            ->get(route('boards.index'))
            ->assertStatus(200)
            ->assertViewHas('boardsOwned', function ($boardsOwned) {
                return $boardsOwned->isEmpty();  // Assert no owned boards
            })
            ->assertViewHas('boardsCollaborated', function ($boardsCollaborated) {
                return $boardsCollaborated->isEmpty();  // Assert no collaborated boards
            })
            ->assertDontSee($board->name);  // Ensure the specific board name is not displayed
    }
    
    public function test_index_page_renders_board_modals()
    {
        $user = User::factory()->create();
    
        $response = $this->actingAs($user)
                         ->get(route('boards.index'));
    
        // Checking if the modal div is present
        $response->assertSee('<div id="createBoardModal"', false);
    
        $response->assertSee('<div id="deleteBoardModal"', false);
    
        $response->assertSee('<div id="editBoardModal"', false);
    
        // Optionally, check for specific text or elements within the modal
        $response->assertSee('Create New Board', false);
        $response->assertSee('Delete Board', false);
        $response->assertSee('Edit Board', false); 
    }

    public function test_store_creates_a_board_and_broadcasts_event()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Define the board data
        $data = [
            'name' => 'Test Board',
            'description' => 'This is a test board.',
        ];
    
        // Fake the event dispatcher
        Event::fake();
    
        // Act: Make a POST request to the store method
        $response = $this->withoutMiddleware()->post(route('boards.store'), $data);
    
        // Assert: Check if the board was created successfully
        $response->assertRedirect(route('boards.index')); // Ensure it redirects to the boards index
        $response->assertSessionHas('success', 'Board created successfully.'); // Check for the success message
    
        // Assert that the board exists in the database
        $this->assertDatabaseHas('boards', [
            'name' => 'Test Board',
            'description' => 'This is a test board.',
            'user_id' => $user->id,  // Ensure the user is linked to the board
        ]);
    
        // Assert that the event was dispatched
        Event::assertDispatched(BoardCreated::class, function ($event) use ($data, $user) {
            return $event->board->name === $data['name'] && $event->board->user_id === $user->id;
        });
    }
    
    public function test_store_fails_when_board_name_already_exists()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a board with a unique name
        $existingBoard = Board::factory()->create([
            'name' => 'Existing Board',
            'user_id' => $user->id,
        ]);

        // Define the board data with the same name as the existing board
        $data = [
            'name' => $existingBoard->name, // Duplicate name
            'description' => 'This is another test board.',
        ];

        // Act: Make a POST request to the store method
        $response = $this->withoutMiddleware()->post(route('boards.store'), $data);

        // Assert: Check if the response has validation errors
        $response->assertSessionHasErrors(['name']); // Check for errors related to the 'name' field

        // Assert that the error message matches expected message
        $this->assertEquals('The board name must be unique. Please choose a different name.', session('errors')->get('name')[0]);

        // Check that no new board was created in the database
        $this->assertDatabaseCount('boards', 1); // Ensure only the existing board remains
    }

    public function test_store_fails_when_name_is_missing()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Define the board data without the 'name' field
        $data = [
            'description' => 'This is a test board without a name.',
        ];
    
        // Act: Make a POST request to the store method
        $response = $this->withoutMiddleware()->post(route('boards.store'), $data);
    
        // Assert: Check for validation errors
        $response->assertSessionHasErrors(['name']);
        $this->assertEquals('The name is required.', session('errors')->get('name')[0]);
    }

    public function test_store_fails_when_name_exceeds_max_length()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Define the board data with a name exceeding the maximum length
        $data = [
            'name' => str_repeat('A', 256), // 256 characters
            'description' => 'This is a test board with a very long name.',
        ];

        // Act: Make a POST request to the store method
        $response = $this->withoutMiddleware()->post(route('boards.store'), $data);

        // Assert: Check for validation errors
        $response->assertSessionHasErrors(['name']);
        $this->assertEquals('The name field must not be greater than 255 characters.', session('errors')->get('name')[0]);
    }

    public function test_store_creates_a_board_without_description()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Define the board data without a description
        $data = [
            'name' => 'Test Board Without Description',
            'description' => null, // Explicitly set to null
        ];
        
        // Fake the event dispatcher
        Event::fake();

        // Act: Make a POST request to the store method
        $response = $this->withoutMiddleware()->post(route('boards.store'), $data);
    
        // Assert: Check if the board was created successfully
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'Board created successfully.');
    
        // Assert that the board exists in the database
        $this->assertDatabaseHas('boards', [
            'name' => 'Test Board Without Description',
            'user_id' => $user->id,
            'description' => null, // Description should be null
        ]);

        // Assert that the event was dispatched
        Event::assertDispatched(BoardCreated::class, function ($event) use ($data, $user) {
            return $event->board->name === $data['name'] && $event->board->user_id === $user->id;
        });
    }

    public function test_show_returns_board_view()
    {
        // Arrange: Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]); // Create a board for the user
        $this->actingAs($user); // Act as the authenticated user

        // Act: Make a GET request to the show method
        $response = $this->get(route('boards.show', $board->id));

        // Assert: Check that the response is successful and the view is returned
        $response->assertStatus(200); // Check for a successful response
        $response->assertViewIs('boards.show'); // Ensure the correct view is returned
        $response->assertViewHas('board', $board); // Check that the board is passed to the view
    }

    public function test_show_throws_404_if_board_not_found()
    {
        // Arrange: Create a user and act as that user
        $user = User::factory()->create();
        $this->actingAs($user); // Act as the authenticated user
    
        // Act: Make a GET request to a non-existing board ID
        $response = $this->get(route('boards.show', 9999)); // Assuming 9999 does not exist
    
        // Assert: Check that a 404 response is returned
        $response->assertStatus(404); // Expecting a 404 status
    }

    public function test_show_allows_authorized_user_to_view_board()
    {
        // Arrange: Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]); // Create a board for the user
        $this->actingAs($user); // Act as the authenticated user

        // Act: Make a GET request to the show method
        $response = $this->get(route('boards.show', $board->id));

        // Assert: Check that the response is successful and the view is returned
        $response->assertStatus(200); // Check for a successful response
        $response->assertViewIs('boards.show'); // Ensure the correct view is returned
        $response->assertViewHas('board', $board); // Check that the board is passed to the view
    }
    
    public function test_show_denies_access_to_unauthorized_user()
    {
        // Arrange: Create two users and a board owned by the first user
        $owner = User::factory()->create();
        $unauthorizedUser = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $owner->id]); // Create a board for the owner
        $this->actingAs($unauthorizedUser); // Act as the unauthorized user
    
        // Act: Attempt to access the board
        $response = $this->get(route('boards.show', $board->id));
    
        // Assert: Check that the response has a 403 status code
        $response->assertStatus(403); // Unauthorized access should return a 403 status
    }
    
    public function test_show_displays_tasks_for_the_board()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner', // Assuming the creator is the owner
        ]);
    
        // Create tasks associated with the board using the board_user_id
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id, // Use the board_user_id
            'name' => 'Task 1',
            'description' => 'Description for Task 1',
            'due' => now()->addDays(7),
            'priority' => 'medium',
            'progress' => 'to_do',
        ]);

        // Create tasks associated with the board using the board_user_id
        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id, // Use the board_user_id
            'name' => 'Task 2',
            'description' => 'Description for Task 2',
            'due' => now()->addDays(7),
            'priority' => 'low',
            'progress' => 'in_progress',
        ]);
    
        $task3 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id, // Use the board_user_id
            'name' => 'Task 3',
            'description' => 'Description for Task 3',
            'due' => now()->addDays(14),
            'priority' => 'high',
            'progress' => 'done',
        ]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Access the board's show method
        $response = $this->get(route('boards.show', $board->id));

        $this->assertTaskInView($response, 'toDoTasks', $task1);
        $this->assertTaskInView($response, 'inProgressTasks', $task2);
        $this->assertTaskInView($response, 'doneTasks', $task3);
    }
    
    public function test_show_board_without_tasks()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a board with the owner
        $board = Board::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
    
        // Act: Make a GET request to the show method
        $response = $this->get(route('boards.show', $board->id));
    
        // Assert: Check that the response is successful and the view is returned
        $response->assertStatus(200);
        $response->assertViewIs('boards.show');
        $response->assertViewHas('board', $board);
       
        // Create an empty collection for reuse
        $emptyCollection = new Collection();

        // Assert that no tasks are present ($emptyCollection - Ensures they are an empty collection)
        $response->assertViewHas('toDoTasks', $emptyCollection);
        $response->assertViewHas('inProgressTasks', $emptyCollection);
        $response->assertViewHas('doneTasks', $emptyCollection);
    }
            // Helper function to assert task is in the view
        protected function assertTaskInView($response, $viewVariable, $task)
        {
            $response->assertViewHas($viewVariable, function ($groupedTasks) use ($task) {
                foreach ($groupedTasks as $date => $tasksForDate) {
                    if ($tasksForDate->contains($task)) {
                        return true;
                    }
                }
                return false;
            });
        }
    
        // Helper function to assert task is NOT in the view
        protected function assertTaskNotInView($response, $viewVariable, $task)
        {
            $response->assertViewHas($viewVariable, function ($groupedTasks) use ($task) {
                foreach ($groupedTasks as $date => $tasksForDate) {
                    if ($tasksForDate->contains($task)) {
                        return false;  // Fail if the task is found
                    }
                }
                return true;  // Pass if the task is not found
            });
        }

        public function test_show_fetched_collaborators_non_collaborators_and_pending_invitations()
        {
            // Create users
            $owner = User::factory()->create();
            $collaborator1 = User::factory()->create();
            $collaborator2 = User::factory()->create();        
            $nonCollaborator = User::factory()->create();
            $pendingInvitedUser = User::factory()->create();
        
            // Create a board with the owner   
            $board = Board::factory()->create(['user_id' => $owner->id]);
        
            // Create BoardUser records for the owner and collaborator
            BoardUser::factory()->create([
                'board_id' => $board->id,
                'user_id' => $owner->id,
                'role' => 'owner',
            ]);
        
            BoardUser::factory()->create([
                'board_id' => $board->id,
                'user_id' => $collaborator1->id,
                'role' => 'collaborator',
            ]);
            
            BoardUser::factory()->create([
                'board_id' => $board->id,
                'user_id' => $collaborator2->id,
                'role' => 'collaborator',
            ]);
        
            // Create a pending invitation
            BoardInvitation::factory()->create([
                'board_id' => $board->id,
                'user_id' => $pendingInvitedUser->id,
                'invited_by' => $owner->id,
                'status' => 'pending',
            ]);
        
            // Act: Authenticate the owner
            $this->actingAs($owner);
        
            // Act: Access the board's show method
            $response = $this->get(route('boards.show', $board->id));
        
            // Assert: Check collaborators are fetched correctly
            $collaborators = $response->viewData('collaborators');
            $this->assertCount(2, $collaborators);
            $this->assertTrue($collaborators->contains($collaborator1));
            $this->assertTrue($collaborators->contains($collaborator2));
        
            // Assert: Check non-collaborators are fetched correctly
            $nonCollaborators = $response->viewData('nonCollaborators');
            $this->assertCount(1, $nonCollaborators);
            $this->assertTrue($nonCollaborators->contains($nonCollaborator));
        
            // Assert: Check pending invitations are fetched correctly
            $pendingInvitations = $response->viewData('pendingInvitations');
            $this->assertCount(1, $pendingInvitations);
            $this->assertTrue($pendingInvitations->contains(function ($invitation) use ($pendingInvitedUser) {
                return $invitation->user_id === $pendingInvitedUser->id;
            }));
        }
        

    public function test_show_filters_tasks_by_tags()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner', 
        ]);
    
        // Create tasks associated with the board using the board_user_id and with tags
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 1',
            'tag' => 'urgent',
            'progress' => 'to_do',
        ]);
    
        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 2',
            'tag' => 'feature',
            'progress' => 'in_progress',
        ]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Make a request with selected tags
        $response = $this->get(route('boards.show', [
            'id' => $board->id,
            'tags' => ['urgent'],  // Request only tasks tagged as 'urgent'
        ]));
    
        // Assert: Only tasks with the 'urgent' tag should be returned
        $this->assertTaskInView($response, 'toDoTasks', $task1);
        $this->assertTaskNotInView($response, 'inProgressTasks', $task2);
    }

    public function test_show_displays_only_tasks_with_selected_tags()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner',
        ]);
    
        // Create tasks associated with the board using the board_user_id
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Urgent Task',
            'description' => 'Description for Urgent Task',
            'due' => now()->addDays(7),
            'priority' => 'medium',
            'progress' => 'to_do',
            'tag' => 'urgent',
        ]);

        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Regular Task',
            'description' => 'Description for Regular Task',
            'due' => now()->addDays(14),
            'priority' => 'low',
            'progress' => 'in_progress',
            'tag' => 'regular',
        ]);

    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Access the board's show method with a filter for 'urgent'
        $response = $this->get(route('boards.show', [
            'id' => $board->id,
            'tags' => ['urgent'],
        ]));
    
        // Assert that the response contains only the urgent task
        $this->assertTaskInView($response, 'toDoTasks', $task1);
        $this->assertTaskNotInView($response, 'inProgressTasks', $task2); // This should fail
    }

    public function test_show_displays_correct_task_counts()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner',
        ]);
    
        // Create tasks with appropriate progress statuses
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'progress' => 'to_do',
        ]);
    
        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'progress' => 'in_progress',
        ]);
    
        $task3 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'progress' => 'done',
        ]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Access the board's show method
        $response = $this->get(route('boards.show', $board->id));
    
        // Assert: The response should contain the correct task counts
        $response->assertViewHas('taskCounts', function ($taskCounts) use ($task1, $task2, $task3) {
            return $taskCounts['to_do'] === 1 &&
                   $taskCounts['in_progress'] === 1 &&
                   $taskCounts['done'] === 1;
        });
    }
    
    public function test_show_displays_all_tags_for_the_board()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner',
        ]);
    
        // Create tasks associated with the board with various tags
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task with Tag 1',
            'tag' => 'urgent',
            'progress' => 'to_do',
        ]);
    
        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task with Tag 2',
            'tag' => 'feature',
            'progress' => 'in_progress',
        ]);
    
        $task3 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task with Tag 3',
            'tag' => 'urgent',
            'progress' => 'done',
        ]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Access the board's show method
        $response = $this->get(route('boards.show', $board->id));
    
        // Assert: The response should contain all unique tags from the tasks
        $response->assertViewHas('allTags', function ($tags) use ($task1, $task2, $task3) {
            return collect($tags)->contains('urgent') &&
                   collect($tags)->contains('feature');
        });
    }

    public function test_show_displays_board_owner_information()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner',
        ]);
    
        // Simulate a request to the show method
        $response = $this->actingAs($user)->get(route('boards.show', $board->id));
    
        // Assert that the response is successful
        $response->assertStatus(200);
    
        // Assert that the owner's information is present in the view
        $response->assertSee($user->name);
    }

    public function test_show_handles_empty_tags_gracefully()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner',
        ]);
    
        // Act: Simulate the request to the show method
        $response = $this->actingAs($user)->get(route('boards.show', $board->id));
    
        // Assert: Check that the view is returned and no errors occur
        $response->assertStatus(200);
    
        // Assert the filter section exists
        $response->assertSee('Filter Tasks');
        $response->assertSee('Select Tags');
        $response->assertSee('No tags available.');
    
        // Assert that there are no checkboxes rendered for tags
        $response->assertDontSee('<input type="checkbox" name="tags[]"'); // This checks if checkbox inputs for tags are not present
    }
    
    public function test_show_filters_tasks_by_multiple_tags()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board with the user as the owner
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Create the BoardUser record for the user on this board
        $boardUser = BoardUser::factory()->create([
            'user_id' => $user->id,
            'board_id' => $board->id,
            'role' => 'owner', 
        ]);
    
        // Create tasks associated with the board using the board_user_id and with tags
        $task1 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 1',
            'tag' => 'urgent',
            'progress' => 'to_do',
        ]);
    
        $task2 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 2',
            'tag' => 'feature',
            'progress' => 'in_progress',
        ]);
    
        $task3 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 3',
            'tag' => 'urgent',
            'progress' => 'done',
        ]);

        $task4 = Task::factory()->create([
            'board_id' => $board->id,
            'board_user_id' => $boardUser->id,
            'name' => 'Task 4',
            'tag' => 'backlog',
            'progress' => 'to_do',
        ]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Make a request with multiple selected tags
        $response = $this->get(route('boards.show', [
            'id' => $board->id,
            'tags' => ['urgent', 'feature'],  // Request tasks tagged as 'urgent' and 'feature'
        ]));
    
        // Assert: Tasks with the 'urgent' or 'feature' tags should be returned
        $this->assertTaskInView($response, 'toDoTasks', $task1);
        $this->assertTaskInView($response, 'inProgressTasks', $task2);
        $this->assertTaskInView($response, 'doneTasks', $task3);
        $this->assertTaskNotInView($response, 'toDoTasks', $task4);
    }

    public function test_update_board_name()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board for the user
        $board = Board::factory()->create(['user_id' => $user->id, 'name' => 'Old Board Name']);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Send a request to update the board name
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => 'Updated Board Name'
        ]);
    
        // Assert: Redirected to boards.index with success message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'Board updated successfully.');
    
        // Assert: The board's name is updated
        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Updated Board Name'
        ]);
    }

    public function test_update_board_description()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Create a board for the user with name and description
        $board = Board::factory()->create(['user_id' => $user->id, 'name' => 'Board Name', 'description' => 'Old Board Description']);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Send a request to update the board description
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => 'Board Name',
            'description' => 'Updated Board Description'
        ]);
    
        // Assert: Redirected to boards.index with success message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'Board updated successfully.');
    
        // Assert: The board's description is updated
        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Board Name',
            'description' => 'Updated Board Description'
        ]);
    }
    
    public function test_unauthorized_user_cannot_update_board()
    {
        // Create two users: one as the board owner and one unauthorized
        $owner = User::factory()->create();
        $unauthorizedUser = User::factory()->create();

        // Create a board owned by the first user
        $board = Board::factory()->create(['user_id' => $owner->id]);

        // Act: Authenticate as the unauthorized user
        $this->actingAs($unauthorizedUser);

        // Act: Attempt to update the board
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => 'New Name',
            'description' => 'New Description'
        ]);

        // Assert: The user is forbidden from updating the board
        $response->assertStatus(403);
        
    }

    public function test_update_board_fails_when_name_is_missing()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);

        // Act: Authenticate the user
        $this->actingAs($user);

        // Act: Attempt to update the board with invalid data
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => '',  // Invalid empty name
            'description' => 'Description'
        ]);

        // Assert: The request fails validation and redirects back
        $response->assertSessionHasErrors(['name']);
        $this->assertEquals('The name is required.', session('errors')->get('name')[0]);
    }
    
    public function test_update_board_fails_when_name_exceeds_max_length()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);

        // Act: Authenticate the user
        $this->actingAs($user);

        // Act: Attempt to update the board with invalid data
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => str_repeat('A', 256),
            'description' => 'Description'
        ]);

        // Assert: The request fails validation and redirects back
        $response->assertSessionHasErrors(['name']);
        $this->assertEquals('The name field must not be greater than 255 characters.', session('errors')->get('name')[0]);
    }

    public function test_update_nonexistent_board_fails()
    {
        // Create a user
        $user = User::factory()->create();

        // Act: Authenticate the user
        $this->actingAs($user);

        // Act: Attempt to update a non-existent board
        $response = $this->withoutMiddleware()->put(route('boards.update', 9999), [
            'name' => 'New Name',
            'description' => 'New Description'
        ]);

        // Assert: The response should be a 404 Not Found
        $response->assertNotFound();
    }

    public function test_update_board_with_no_changes()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id, 'name' => 'Same Name', 'description' => 'Same Description']);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Update the board with the same data
        $response = $this->withoutMiddleware()->put(route('boards.update', $board->id), [
            'name' => 'Same Name',
            'description' => 'Same Description'
        ]);
    
        // Assert: The response is still successful
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'Board updated successfully.');
    }

    public function test_destroy_board_successfully()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);

        // Act: Authenticate the user
        $this->actingAs($user);

        // Act: Send a delete request
        $response = $this->withoutMiddleware()->delete(route('boards.destroy', $board->id), [
            'idempotency_key' => 'unique_key_123'
        ]);

        // Assert: Redirected to boards.index with success message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'Board deleted successfully.');

        // Assert: The board is deleted from the database
        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    }

    public function test_destroy_board_with_duplicate_idempotency_key()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Attempt to delete without providing an idempotency key
        $response = $this->delete(route('boards.destroy', $board->id), []);
    
        // Assert: Ensure the response is redirected back with a validation error for 'idempotency_key'
        $response->assertSessionHasErrors(['idempotency_key']);
    
        // Act: Send a delete request with a unique idempotency key
        $response = $this->delete(route('boards.destroy', $board->id), [
            'idempotency_key' => 'unique_key_123'
        ]);
    
        // Assert: Check that the board has been deleted
        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    
        // Act: Attempt to delete again with the same idempotency key
        $response = $this->delete(route('boards.destroy', $board->id), [
            'idempotency_key' => 'unique_key_123'
        ]);
    
        // Assert: Ensure the response is redirected with the correct warning message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }
    
    public function test_unauthorized_user_cannot_delete_board()
    {
        // Create two users: one as the board owner and one unauthorized
        $owner = User::factory()->create();
        $unauthorizedUser = User::factory()->create();

        // Create a board owned by the first user
        $board = Board::factory()->create(['user_id' => $owner->id]);

        // Act: Authenticate as the unauthorized user
        $this->actingAs($unauthorizedUser);

        // Act: Attempt to delete the board
        $response = $this->withoutMiddleware()->delete(route('boards.destroy', $board->id), [
            'idempotency_key' => 'unique_key_123'
        ]);

        // Assert: The user is forbidden from deleting the board
        $response->assertForbidden();
    }

    public function test_destroy_nonexistent_board()
    {
        // Create a user
        $user = User::factory()->create();
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Attempt to delete a non-existent board
        $response = $this->delete(route('boards.destroy', 9999), [
            'idempotency_key' => 'unique_key_123'
        ]);
    
        // Assert: Ensure the response is a 404 status
        $response->assertStatus(404);
    }

    public function test_destroy_board_without_idempotency_key()
    {
        // Create a user and a board
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
    
        // Act: Authenticate the user
        $this->actingAs($user);
    
        // Act: Send a delete request without an idempotency key
        $response = $this->delete(route('boards.destroy', $board->id));
    
        // Assert: Redirected to boards.index with an error message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHasErrors(['idempotency_key' => 'Idempotency key is required.']);
    
        // Assert: The board still exists in the database
        $this->assertDatabaseHas('boards', ['id' => $board->id]);
    }
    
}
