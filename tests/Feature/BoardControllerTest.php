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
    
}
