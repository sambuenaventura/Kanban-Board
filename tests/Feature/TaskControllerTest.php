<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $collaborator;
    protected $board;
    protected $nonCollaborator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user (board owner) and a collaborator
        $this->user = User::factory()->create();

        // Create a collaborator user
        $this->collaborator = User::factory()->create();

        // Create a non-collaborator user
        $this->nonCollaborator = User::factory()->create();

        // Create a board and associate it with the user (owner)
        $this->board = Board::factory()->create(['user_id' => $this->user->id]);

        // Associate the collaborator with the board
        $this->board->users()->attach($this->user->id, ['role' => 'owner']);

        // Associate the collaborator with the board using the pivot table
        $this->board->users()->attach($this->collaborator->id, ['role' => 'collaborator']);
    }

    public function test_store_task_success()
    {
        // Simulate authenticating the board owner (or collaborator)
        $this->actingAs($this->user);
    
        // Prepare the task data
        $data = [
            'board_id' => $this->board->id,
            'name' => 'Test Task',
            'description' => 'This is a test task.',
            'idempotency_key' => 'unique-key',
            'due' => now()->addDays(3),
            'priority' => 'high',
            'progress' => 'not started',
            'tag' => 'urgent',
        ];
    
        // Call the store method
        $response = $this->post(route('boards.tasks.store', ['boardId' => $this->board->id]), $data);
    
        // Assert the task was created successfully
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'Task created successfully.');
    
        // Verify the task was added to the database
        $this->assertDatabaseHas('tasks', [
            'name' => 'Test Task',
            'board_id' => $this->board->id,
        ]);
    }

    public function test_store_unauthorized_user_cannot_create_task()
    {
        // Create a new user who is not associated with the board
        $unauthorizedUser = User::factory()->create();

        // Prepare the task data
        $data = [
            'board_id' => $this->board->id,
            'name' => 'Unauthorized Task',
            'description' => 'This task should not be created.',
            'idempotency_key' => 'unique-key',
            'due' => now()->addDays(3),
            'priority' => 'high',
            'progress' => 'not started',
            'tag' => 'urgent',
        ];

        // Attempt to create a task as the unauthorized user
        $response = $this->actingAs($unauthorizedUser)->post(route('boards.tasks.store', ['boardId' => $this->board->id]), $data);

        // Assert that the response is forbidden
        $response->assertStatus(403);
        $this->assertDatabaseMissing('tasks', ['name' => 'Unauthorized Task']);
    }

    public function test_store_task_validation_errors()
    {
        // Authenticate the user
        $this->actingAs($this->user);

        // Prepare incomplete task data
        $data = [
            'board_id' => $this->board->id,
            'name' => '',
            'due' => '',
            'priority' => '',
            'progress' => '',
            'tag' => '',
            'idempotency_key' => '',
        ];

        // Attempt to create a task with invalid data
        $response = $this->post(route('boards.tasks.store', ['boardId' => $this->board->id]), $data);

        // Assert that the response contains validation errors
        $response->assertSessionHasErrors(['name', 'due', 'priority', 'progress', 'tag', 'idempotency_key']);

        $this->assertCount(6, session('errors')); // Checks for total number of errors in session
    }

    public function test_store_task_with_duplicate_idempotency_key()
    {
        // Authenticate the user
        $this->actingAs($this->user);
    
        // Prepare the task data
        $data = [
            'board_id' => $this->board->id,
            'name' => 'Test Task',
            'description' => 'This is a test task.',
            'idempotency_key' => 'unique-key',
            'due' => now()->addDays(3),
            'priority' => 'high',
            'progress' => 'not started',
            'tag' => 'urgent',
        ];
    
        // Create the task for the first time
        $this->post(route('boards.tasks.store', ['boardId' => $this->board->id]), $data);
    
        // Attempt to create the task again with the same idempotency key
        $response = $this->post(route('boards.tasks.store', ['boardId' => $this->board->id]), $data);
    
        // Assert that the response redirects with a message
        $response->assertSessionHas('warning', 'A task with this name already exists on this board.');
        $this->assertDatabaseCount('tasks', 1); // Ensure only one task was created
    }
    
    public function test_show_task_success()
    {
        // Authenticate the board owner
        $this->actingAs($this->user);
    
        // Create a task associated with the board inside the test
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
            'description' => 'This is a test task.',
        ]);
    
        // Send a GET request to the show route
        $response = $this->get(route('boards.tasks.show', [
            'boardId' => $this->board->id,
            'taskId' => $task->id,
        ]));
    
        // Assert the view is rendered
        $response->assertStatus(200);
        $response->assertViewIs('boards.tasks.show');
    
        // Assert that the task data is present in the view
        $response->assertViewHas('task', $task);
        $response->assertViewHas('board', $this->board);
    
        // Adjust to check if attachments is a MediaCollection
        $response->assertViewHas('attachments', function ($attachments) {
            return $attachments instanceof MediaCollection;
        });
    
        $response->assertViewHas('boardId', $this->board->id);
    }
    
    public function test_show_task_unauthorized_access()
    {
        // Create a random user who is not a collaborator or owner
        $unauthorizedUser = User::factory()->create();
    
        // Authenticate as the unauthorized user
        $this->actingAs($unauthorizedUser);
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
        ]);
    
        // Try to access the task show route
        $response = $this->get(route('boards.tasks.show', [
            'boardId' => $this->board->id,
            'taskId' => $task->id,
        ]));
    
        // Assert that the unauthorized user gets a 403 status
        $response->assertStatus(403);
    }

    public function test_show_task_not_found()
    {
        // Authenticate as the board owner
        $this->actingAs($this->user);

        // Try to access a non-existent task
        $response = $this->get(route('boards.tasks.show', [
            'boardId' => $this->board->id,
            'taskId' => 999,
        ]));

        // Assert that a 404 status is returned
        $response->assertStatus(404);
    }

    public function test_show_board_not_found()
    {
        // Authenticate as the board owner
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
        ]);
    
        // Try to access the task with a non-existent board
        $response = $this->get(route('boards.tasks.show', [
            'boardId' => 999,
            'taskId' => $task->id,
        ]));
    
        // Assert that a 404 status is returned
        $response->assertStatus(404);
    }

    public function test_show_task_with_attachments()
    {
        // Fake the storage disk
        Storage::fake('local');
    
        // Create a file to be used in the test
        $file = UploadedFile::fake()->create('dummy_file.pdf', 100); // (filename, size)
    
        // Authenticate as the board owner
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
        ]);
    
        // Add the fake media file to the task
        $task->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('attachments');
    
        // Send a GET request to the show route
        $response = $this->get(route('boards.tasks.show', [
            'boardId' => $this->board->id,
            'taskId' => $task->id,
        ]));
    
        // Assert the response has the correct task, board, and attachments
        $response->assertStatus(200);
        $response->assertViewHas('attachments', function ($attachments) {
            return $attachments->count() === 1;
        });
    }

    public function test_edit_task_success()
    {
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
            'description' => 'This is a test task.',
        ]);

        // Simulate authentication
        $this->actingAs($this->user);
        
        // Simulate the request to edit the task
        $response = $this->get(route('boards.tasks.edit', ['boardId' => $this->board->id, 'taskId' => $task->id]));

        // Assert that the correct view is returned
        $response->assertStatus(200);
        $response->assertViewIs('boards.tasks.edit');
        $response->assertViewHas('task', $task);
        $response->assertViewHas('boardId', $this->board->id);
    }

    public function test_edit_task_unauthorized()
    {
        // Create a new user who is not the owner
        $otherUser = User::factory()->create();
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Test Task',
            'description' => 'This is a test task.',
        ]);
    
        // Simulate authentication for the other user
        $this->actingAs($otherUser);
        
        // Attempt to access the edit page for the task
        $response = $this->get(route('boards.tasks.edit', ['boardId' => $this->board->id, 'taskId' => $task->id]));
    
        // Assert that the response status is a forbidden error
        $response->assertStatus(403);
    }
    
    public function test_edit_task_not_found()
    {
        // Simulate authentication
        $this->actingAs($this->user);

        // Attempt to access the edit page for a non-existent task
        $response = $this->get(route('boards.tasks.edit', ['boardId' => $this->board->id, 'taskId' => 9999]));

        // Assert that the response status is a not found error
        $response->assertStatus(404);
    }

    public function test_update_task_success_without_progress()
    {
        // Simulate authentication
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Old Task Name',
            'description' => 'Old description.',
            'progress' => 'to_do',
            'due' => '2024-10-07',
            'priority' => 'low',
            'tag' => 'initial_tag',
        ]);
    
        // Prepare updated task data including all required fields
        $data = [
            'name' => 'Updated Task Name',
            'description' => 'Updated description.',
            'due' => '2024-10-14',
            'priority' => 'high',
            'progress' => 'to_do',
            'tag' => 'updated_tag',
            'idempotency_key' => 'unique-key',
        ];
    
        // Call the update method
        $response = $this->patch(route('boards.tasks.update', ['boardId' => $this->board->id, 'taskId' => $task->id]), $data);
    
        // Assert the task was updated successfully
        $response->assertRedirect(route('boards.tasks.show', ['boardId' => $this->board->id, 'taskId' => $task->id]));
        $response->assertSessionHas('success', 'Task updated successfully.');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Updated Task Name',
            'description' => 'Updated description.',
            'due' => '2024-10-14 00:00:00',
            'priority' => 'high',
            'progress' => 'to_do',
            'tag' => 'updated_tag',
        ]);
    }

    public function test_update_task_progress_success()
    {
        // Simulate authentication
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Old Task Name',
            'description' => 'Old description.',
            'progress' => 'to_do',
            'due' => '2024-10-07',
            'priority' => 'low',
            'tag' => 'initial_tag',
        ]);
    
        // Prepare updated progress value including the idempotency key
        $data = [
            'progress' => 'in_progress',
            'idempotency_key' => 'unique-key',
        ];
    
        // Call the update method
        $response = $this->patch(route('boards.tasks.update', ['boardId' => $this->board->id, 'taskId' => $task->id]), $data);
    
        // Assert the updated progress
        $response->assertRedirect(route('boards.tasks.show', ['boardId' => $this->board->id, 'taskId' => $task->id]));
        $response->assertSessionHas('success', 'Task started successfully.');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'progress' => 'in_progress',
        ]);
    }

    public function test_update_task_validation_errors()
    {
        // Simulate authentication
        $this->actingAs($this->user);

        // Create a task associated with the board
        $task = Task::factory()->create(['board_id' => $this->board->id]);

        // Prepare invalid task data (e.g., missing name)
        $data = [
            'name' => '',
            'description' => 'Some description.',
        ];

        // Call the update method
        $response = $this->patch(route('boards.tasks.update', ['boardId' => $this->board->id, 'taskId' => $task->id]), $data);

        // Assert that the response has validation errors
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_task_not_found()
    {
        // Simulate authentication
        $this->actingAs($this->user);
        
        // Prepare updated task data, including the idempotency key
        $data = [
            'name' => 'Updated Task Name',
            'description' => 'Updated description.',
            'progress' => 'to_do',
            'idempotency_key' => 'unique-key',
        ];
        
        // Attempt to update a non-existent task
        $response = $this->patch(route('boards.tasks.update', ['boardId' => $this->board->id, 'taskId' => 9999]), $data);
        
        // Assert that the response redirects to the boards index
        $response->assertRedirect(route('boards.index'));
        
        // Assert that the session has the error message
        $response->assertSessionHasErrors(['task' => 'Task not found.']);
    }

    public function test_upload_file_success()
    {
        // Simulate authentication
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create(['board_id' => $this->board->id]);
    
        // Create a fake file
        $file = UploadedFile::fake()->create('document.pdf', 100); // 100 KB file
    
        // Prepare the data, including the idempotency key
        $data = [
            'attachment' => $file,
            'idempotency_key' => 'unique-key',
        ];
    
        // Call the uploadFile method
        $response = $this->post(route('tasks.uploadFile', ['task' => $task->id]), $data);
    
        // Assert the response
        $response->assertRedirect(route('boards.tasks.show', ['boardId' => $this->board->id, 'taskId' => $task->id]));
        $response->assertSessionHas('success', 'Attachment uploaded successfully.');
    
        // Reload the task to check for the uploaded media
        $task->refresh(); // Refresh the task to get the latest state
    
        // Assert the media has been added to the task
        $this->assertCount(1, $task->getMedia('attachments')); // Ensure one media is attached
        $this->assertEquals('document.pdf', $task->getMedia('attachments')->first()->file_name); // Check the filename
    }
    
    public function test_upload_file_validation_errors()
    {
        // Simulate authentication
        $this->actingAs($this->user);

        // Create a task associated with the board
        $task = Task::factory()->create(['board_id' => $this->board->id]);

        // Prepare request data without an attachment
        $data = [];

        // Call the uploadFile method
        $response = $this->post(route('tasks.uploadFile', ['task' => $task->id]), $data);

        // Assert that the response has validation errors
        $response->assertRedirect();
        $response->assertSessionHasErrors(['attachment']);
    }

    public function test_destroy_task_successfully()
    {
        // Create a task associated with the board for this test
        $task = Task::factory()->create(['board_id' => $this->board->id]);

        // Simulate authenticating the board owner
        $this->actingAs($this->user);

        // Act: Send a delete request to destroy the task
        $response = $this->withoutMiddleware()->delete(route('boards.tasks.destroy', ['boardId' => $this->board->id, 'taskId' => $task->id]), [
            'idempotency_key' => 'unique_key_123'
        ]);

        // Assert: Redirected to the board's show page with a success message
        $response->assertRedirect(route('boards.show', $this->board->id));
        $response->assertSessionHas('success', 'Task deleted successfully.');

        // Assert: The task is deleted from the database
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_destroy_task_fails_without_idempotency_key()
    {
        // Create a task associated with the board for this test
        $task = Task::factory()->create(['board_id' => $this->board->id]);

        // Simulate authenticating the board owner
        $this->actingAs($this->user);

        // Act: Send a delete request without an idempotency key
        $response = $this->withoutMiddleware()->delete(route('boards.tasks.destroy', ['boardId' => $this->board->id, 'taskId' => $task->id]));

        // Assert: Redirected to the boards.index with an error message
        $response->assertRedirect(route('boards.index'));
        $response->assertSessionHasErrors(['idempotency_key' => 'Idempotency key is required.']);
    }

    public function test_destroy_task_not_found()
    {
        // Simulate authenticating the board owner
        $this->actingAs($this->user);

        // Act: Attempt to delete a task that doesn't exist
        $response = $this->delete(route('boards.tasks.destroy', ['boardId' => $this->board->id, 'taskId' => 999]), [
            'idempotency_key' => 'unique_key_123'
        ]);

        // Assert: Ensure the response is a 404 status
        $response->assertStatus(404);
    }

    public function test_update_task_status_unauthorized()
    {
        // Create a task associated with the board for this test
        $task = Task::factory()->create(['board_id' => $this->board->id]);
        
        $this->actingAs($this->nonCollaborator);

        // Prepare the request data
        $data = [
            'progress' => 'in_progress',
        ];
    
        // Act: Attempt to update the task status
        $response = $this->patch(route('tasks.updateStatus', ['taskId' => $task->id]), $data);
    
        // Assert: Unauthorized action
        $response->assertStatus(403);
    
        // Additionally, verify that the task's progress has not changed
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'progress' => $task->progress, // Check if the progress remains the same
        ]);
    }

    public function test_update_task_status_not_found()
    {
        // Simulate authenticating the board owner
        $this->actingAs($this->user);

        // Prepare the request data
        $data = [
            'progress' => 'in_progress',
        ];

        // Act: Attempt to update a non-existent task
        $response = $this->patch(route('tasks.updateStatus', ['taskId' => 999]), $data);

        // Assert: Task not found
        $response->assertStatus(404);
        $response->assertJson(['message' => 'Task not found.']);
    }

    public function test_destroy_file_success()
    {
        // Simulate authentication
        $this->actingAs($this->user);
    
        // Create a task associated with the board
        $task = Task::factory()->create(['board_id' => $this->board->id]);
    
        // Create and attach a fake file to the task
        $attachment = $task->addMedia(UploadedFile::fake()->create('document.pdf', 100))->toMediaCollection('attachments');
    
        // Prepare the data, including the idempotency key
        $data = [
            'idempotency_key' => 'unique-key',
        ];
    
        // Call the destroyFile method
        $response = $this->delete(route('tasks.destroyFile', ['task' => $task->id, 'attachment' => $attachment->id]), $data);
    
        // Assert the response
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Attachment deleted successfully.');
    
        // Assert the attachment has been removed from the task
        $task->refresh(); // Refresh the task to get the latest state
        $this->assertCount(0, $task->getMedia('attachments')); // Ensure no media is attached
    }
    
    public function test_destroy_file_unauthorized()
    {
        // Create a task associated with the board for this test
        $task = Task::factory()->create(['board_id' => $this->board->id]);
    
        // Create and attach a fake file to the task
        $attachment = $task->addMedia(UploadedFile::fake()->create('document.pdf', 100))->toMediaCollection('attachments');
    
        // Simulate authenticating a different user (not the board owner or collaborator)
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
    
        // Prepare the data, including the idempotency key
        $data = [
            'idempotency_key' => 'unique-key',
        ];
    
        // Attempt to delete the attachment
        $response = $this->delete(route('tasks.destroyFile', ['task' => $task->id, 'attachment' => $attachment->id]), $data);
    
        // Assert: Unauthorized action
        $response->assertStatus(403);
    }
    
    public function test_destroy_file_not_found()
    {
        // Simulate authentication
        $this->actingAs($this->user);
        
        // Create a task associated with the board
        $task = Task::factory()->create(['board_id' => $this->board->id]);
        
        // Prepare the data, including the idempotency key
        $data = [
            'idempotency_key' => 'unique-key',
        ];
    
        // Attempt to delete a non-existent attachment
        $response = $this->delete(route('tasks.destroyFile', ['task' => $task->id, 'attachment' => 999]), $data); // Non-existent attachment ID
        
        // Assert: Redirect response for not found attachment
        $response->assertRedirect(route('boards.tasks.show', ['boardId' => $this->board->id, 'taskId' => $task->id]));
        $response->assertSessionHasErrors(['attachment' => 'Attachment not found.']);
    }

}
