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
    
}
