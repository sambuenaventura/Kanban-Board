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

        // Initialize the BoardService with model instances
        $this->boardService = new BoardService(
            $boardModel,
            $taskModel,
            $boardUserModel,
            $userModel,
            $boardInvitationModel
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
        $response->assertSee('<div id="boardModal"', false);
    
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
            'description' // is omitted
        ];

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
    
}
