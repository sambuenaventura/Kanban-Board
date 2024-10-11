<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\User;
use App\Services\BoardInvitationService;
use App\Services\IdempotencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $collaborator;
    protected $nonOwnerOrNonCollaborator;
    protected $board;
    protected $boardInvitationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user who will be the owner of the board
        $this->user = User::factory()->create();
        
        // Create a collaborator user
        $this->collaborator = User::factory()->create();

        // Create a non-Owner user
        $this->nonOwnerOrNonCollaborator = User::factory()->create();

        // Create a board and associate it with the user (owner)
        $this->board = Board::factory()->create(['user_id' => $this->user->id]);

        // Associate the collaborator with the board using the pivot table
        $this->board->users()->attach($this->collaborator->id, ['role' => 'collaborator']);
    
        // Create an instances of the BoardInvitation model
        $boardInvitationModel = new BoardInvitation();
        // Create an instance of the BoardUser model
        $boardUserModel = new BoardUser();
        
        // Create an instance of the User model
        $userModel = new User();

        // Create an instance of the Idempotency Service
        $idempotencyService = new IdempotencyService();

        // Initialize the BoardService with model instances
        $this->boardInvitationService = new BoardInvitationService(
            $boardInvitationModel,
            $boardUserModel,
            $userModel,
            $idempotencyService,
        );
    }

    public function test_remove_user_from_board_success()
    {
        // Simulate the request to remove the user from the board with an idempotency key
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', ['board' => $this->board->id, 'user' => $this->collaborator->id]), [
                'idempotency_key' => 'unique-key-123', 
            ]);

        // Assert that the response redirects with a success message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'User removed from the board successfully.');

        // Check that the user has been removed from the board
        $this->assertDatabaseMissing('board_users', [
            'board_id' => $this->board->id,
            'user_id' => $this->collaborator->id,
        ]);
    }

    public function test_remove_user_from_board_non_owner_cannot_remove()
    {
        // Attempt to remove the collaborator as a non-owner, without an idempotency key
        $response = $this->actingAs($this->nonOwnerOrNonCollaborator)
            ->delete(route('boards.removeUser', ['board' => $this->board->id, 'user' => $this->collaborator->id]), [
                'idempotency_key' => 'unique-key-456', 
            ]);

        // Assert that the response status is 403 Forbidden
        $response->assertStatus(403);
    }
    
    public function test_index_cannot_remove_non_collaborator_user()
    {
        // Ensure that the user trying to remove is the owner of the board
        $response = $this->actingAs($this->user) // The board owner
            ->delete(route('boards.removeUser', [
                'board' => $this->board->id,
                'user' => $this->nonOwnerOrNonCollaborator->id // This user is not a collaborator
            ]), [
                'idempotency_key' => 'unique-key-123', 
            ]);

        // Assert that the response redirects with an error message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHasErrors(['user' => 'User is not a collaborator on this board.']);
    }

    public function test_index_removing_user_with_same_idempotency_key()
    {
        $key = 'unique-key';

        // Remove the user for the first time
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', ['board' => $this->board->id, 'user' => $this->collaborator->id]), ['idempotency_key' => $key]);

        // Check for success message
        $response->assertSessionHas('success', 'User removed from the board successfully.');

        // Try to remove the same user again with the same key
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', ['board' => $this->board->id, 'user' => $this->collaborator->id]), ['idempotency_key' => $key]);

        // Assert that the response shows a warning
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }
    
    public function test_index_board_does_not_exist()
    {
        // Attempt to remove a user from a non-existent board
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', ['board' => 999, 'user' => $this->collaborator->id]));
        
        // Assert that the response status is 404 Not Found
        $response->assertStatus(404);
    }
    
    public function test_index_collaborator_already_removed()
    {
        // Define a unique idempotency key for the test
        $idempotencyKey = 'unique-key-123';
    
        // First, remove the collaborator from the board
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', [
                'board' => $this->board->id,
                'user' => $this->collaborator->id
            ]), [
                'idempotency_key' => $idempotencyKey
            ]);
    
        // Assert the success response on the first removal
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'User removed from the board successfully.');
    
        // Attempt to remove the same collaborator again
        $response = $this->actingAs($this->user)
            ->delete(route('boards.removeUser', [
                'board' => $this->board->id,
                'user' => $this->collaborator->id
            ]), [
                'idempotency_key' => $idempotencyKey
            ]);
    
        // Assert that the response redirects with a warning message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }

    public function test_invite_user_success()
    {
        $invitee = User::factory()->create();
    
        // Simulate the request to invite the user to the board
        $response = $this->actingAs($this->user)
            ->post(route('boards.inviteUser', ['board' => $this->board->id]), [
                'user_id' => $invitee->id,
                'idempotency_key' => 'unique-key',
            ]);
    
        // Assert that the response redirects to the board's show page
        $response->assertRedirect(route('boards.show', $this->board->id));
    
        // Assert that the session has a success message indicating the invitation was sent
        $response->assertSessionHas('success', 'Invitation sent successfully.');
    
        // Check that a new invitation record has been created in the database
        $this->assertDatabaseHas('board_invitations', [
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'pending',
        ]);
    }
    
    public function test_invite_user_already_collaborator()
    {
        $collaborator = User::factory()->create();
    
        // Attach the collaborator to the board with the role of 'collaborator'
        $this->board->users()->attach($collaborator->id, ['role' => 'collaborator']);
    
        // Simulate the request to invite the user who is already a collaborator
        $response = $this->actingAs($this->user)
            ->post(route('boards.inviteUser', ['board' => $this->board->id]), [
                'user_id' => $collaborator->id,
                'idempotency_key' => 'unique-key',
            ]);
    
        // Assert that the response redirects (should handle the error internally)
        $response->assertRedirect();
    
        // Assert that the session contains an error message indicating the user is already a collaborator
        $response->assertSessionHasErrors(['user' => 'This user is already a collaborator on the board.']);
    }
    
    public function test_invite_user_pending_invitation()
    {
        $invitee = User::factory()->create();
    
        // Create a pending invitation for the user to the board
        BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,       
            'invited_by' => $this->user->id, 
            'status' => 'pending', 
        ]);
    
        // Simulate the request to invite the same user who already has a pending invitation
        $response = $this->actingAs($this->user)
            ->post(route('boards.inviteUser', ['board' => $this->board->id]), [
                'user_id' => $invitee->id,          
                'idempotency_key' => 'unique-key',
            ]);
    
        // Assert that the response redirects to the board's show page
        $response->assertRedirect(route('boards.show', $this->board->id));
    
        // Assert that the session has a warning message indicating the user already has an invitation
        $response->assertSessionHas('warning', 'An invitation has already been sent to this user.');
    }

    public function test_invite_user_idempotency_key_used()
    {
        // Arrange: Create an invitee user
        $invitee = User::factory()->create();
    
        // Arrange: Authenticate the current user
        $this->actingAs($this->user);
    
        // Act: Send a POST request with a unique idempotency key
        $this->post(route('boards.inviteUser', ['board' => $this->board->id]), [
            'user_id' => $invitee->id,
            'idempotency_key' => 'unique-key',
        ]);
    
        // Act: Send the same request again to simulate idempotency key reuse
        $response = $this->post(route('boards.inviteUser', ['board' => $this->board->id]), [
            'user_id' => $invitee->id,
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert: Redirected to the board's show page
        $response->assertRedirect(route('boards.show', $this->board->id));
    
        // Assert: The session contains a warning message about the idempotency key
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }
    
    public function test_accept_invitation_success()
    {
        $invitee = User::factory()->create();
        
        // Simulate the invitee acting as the current user
        $this->actingAs($invitee);
        
        // Create a pending invitation for the invitee to the board
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,      
            'user_id' => $invitee->id,            
            'invited_by' => $this->user->id,     
            'status' => 'pending',   
        ]);
        
        // Call the acceptInvitation method to accept the invitation
        $response = $this->post(route('boards.acceptInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',    
        ]);
        
        // Assert that the response redirects to the board's show page
        $response->assertRedirect(route('boards.show', $this->board->id));
        
        // Assert that the session has a success message indicating the user has joined the board
        $response->assertSessionHas('success', 'You have joined the board.');
        
        // Assert that the invitation status is now changed to accepted in the database
        $this->assertDatabaseHas('board_invitations', [
            'id' => $invitation->id,              
            'status' => 'accepted',                
        ]);
        
        // Assert that the user has been added as a collaborator on the board
        $this->assertDatabaseHas('board_users', [
            'board_id' => $this->board->id,      
            'user_id' => $invitee->id,        
        ]);
    }
    
    public function test_accept_invitation_already_accepted()
    {
        // Create a user and a board invitation
        $invitee = User::factory()->create();
        $this->actingAs($invitee); // The user will accept the invitation
    
        // Create an already accepted invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'accepted',
        ]);
    
        // Call the acceptInvitation method
        $response = $this->post(route('boards.acceptInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert the redirect and error message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHasErrors(['user' => 'This invitation has already been accepted.']);
    }
    
    public function test_accept_invitation_idempotency_key_used()
    {
        // Arrange: Create a user and authenticate as the invitee
        $invitee = User::factory()->create();
        $this->actingAs($invitee);
    
        // Arrange: Create a pending invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'pending',
        ]);
    
        // Act: First call to accept the invitation with a unique idempotency key
        $response = $this->post(route('boards.acceptInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert success response for the first call
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'You have joined the board.');
    
        // Act: Second call to accept the same invitation with the same idempotency key
        $response = $this->post(route('boards.acceptInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert that the response indicates the action has already been processed
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }
    
    public function test_decline_invitation_success()
    {
        $invitee = User::factory()->create();
        $this->actingAs($invitee);

        // Create a pending invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->post(route('boards.declineInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);

        // Assert the redirect and success message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'You declined the invitation.');

        // Verify the invitation is marked as declined
        $this->assertDatabaseHas('board_invitations', [
            'id' => $invitation->id,
            'status' => 'declined',
        ]);
    }

    public function test_decline_invitation_already_declined()
    {
        $invitee = User::factory()->create();
        $this->actingAs($invitee);

        // Create an already declined invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'declined',
        ]);

        $response = $this->post(route('boards.declineInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);

        // Assert the redirect and error message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHasErrors(['user' => 'This invitation has already been declined.']);
    }

    public function test_decline_invitation_idempotency_key_used()
    {
        // Arrange: Create a user and authenticate as the invitee
        $invitee = User::factory()->create();
        $this->actingAs($invitee);
    
        // Arrange: Create a pending invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $this->user->id,
            'status' => 'pending',
        ]);
    
        // Act: First call to decline the invitation with a unique idempotency key
        $response = $this->post(route('boards.declineInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert success response for the first call
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('success', 'You declined the invitation.');
    
        // Act: Second call to decline the same invitation with the same idempotency key
        $response = $this->post(route('boards.declineInvitation', $invitation->id), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert that the response indicates the action has already been processed
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }

    public function test_manage_invitations_with_pending_invitations()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Create a pending invitation for the user
        BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $user->id,
            'invited_by' => $this->user->id,
            'status' => 'pending',
        ]);
    
        // Call the manageInvitations method
        $response = $this->get(route('boards.manageInvitations'));
    
        // Assert that the response is successful and the view is returned with the invitations
        $response->assertStatus(200);
        $response->assertViewIs('boards.manage-invitations');
        $response->assertViewHas('pendingInvitations');
        $this->assertCount(1, $response->viewData('pendingInvitations')); // Check if there is one invitation
    }
    
    public function test_manage_invitations_without_pending_invitations()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Call the manageInvitations method without any pending invitations
        $response = $this->get(route('boards.manageInvitations'));
    
        // Assert that the response is successful and the view is returned with no invitations
        $response->assertStatus(200);
        $response->assertViewIs('boards.manage-invitations');
        $response->assertViewHas('pendingInvitations');
        $this->assertCount(0, $response->viewData('pendingInvitations')); // Check if there are no invitations
    }
    
    public function test_manage_invitations_unauthenticated_user()
    {
        // Call the manageInvitations method without authenticating the user
        $response = $this->get(route('boards.manageInvitations'));
    
        // Assert that the user is redirected to the login page
        $response->assertRedirect(route('login'));
    }

    public function test_cancel_invitation_success()
    {
        // Create a user, authenticate, and create a board invitation
        $user = User::factory()->create();
        $this->actingAs($user);

        $invitee = User::factory()->create();
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $invitee->id,
            'invited_by' => $user->id,
            'status' => 'pending',
        ]);

        // Call the cancelInvitation method using DELETE
        $response = $this->delete(route('boards.cancelInvitation', ['board' => $this->board->id, 'invitation' => $invitation->id]), [
            'idempotency_key' => 'unique-key',
        ]);

        // Assert the redirect and success message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'Invitation canceled successfully.');
        $this->assertDatabaseMissing('board_invitations', ['id' => $invitation->id]); // Check if the invitation was removed
    }

    public function test_cancel_invitation_already_canceled()
    {
        // Arrange: Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Arrange: Create a valid invitation
        $invitation = BoardInvitation::create([
            'board_id' => $this->board->id,
            'user_id' => $user->id,
            'invited_by' => $user->id,
            'status' => 'pending',
        ]);
    
        // Act: Cancel the invitation for the first time
        $this->delete(route('boards.cancelInvitation', ['board' => $this->board->id, 'invitation' => $invitation->id]), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Act: Call the cancelInvitation method again to simulate trying to cancel an already canceled invitation
        $response = $this->delete(route('boards.cancelInvitation', ['board' => $this->board->id, 'invitation' => $invitation->id]), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert: Redirect and warning message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('warning', 'This operation has already been processed.');
    }
    
    public function test_cancel_invitation_invalid_invitation()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Call the cancelInvitation method using DELETE with an invalid invitation ID
        $response = $this->delete(route('boards.cancelInvitation', ['board' => $this->board->id, 'invitation' => 999]), [
            'idempotency_key' => 'unique-key',
        ]);
    
        // Assert the redirect and warning message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('warning', 'The invitation has already been canceled.');
    }
    
}
