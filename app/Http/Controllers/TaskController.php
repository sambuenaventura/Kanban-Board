<?php

namespace App\Http\Controllers;

use App\Events\BoardTaskCreated;
use App\Events\BoardTaskDeleted;
use App\Events\BoardTaskUpdated;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\Board;
use App\Models\BoardUser;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    use AuthorizesRequests;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index($boardId)
    {
        $tasks = Task::where('board_id', $boardId)->get();
        return view('boards.tasks.index', compact('tasks', 'boardId'));
    }

    public function create($boardId)
    {
        return view('boards.tasks.create', compact('boardId'));
    }
    
    public function store(StoreTaskRequest $request)
    {
        // Retrieve the board
        $board = Board::findOrFail($request->input('board_id'));

        // Authorize the user to add tasks to the board
        $this->authorize('view', $board);

        // Call the service and get the response
        $response = $this->taskService->createTask($board, $request->validated(), $request->idempotency_key);

        // Check for errors
        if (isset($response['error'])) {
            return redirect()->route('boards.show', $board->id)
                             ->withErrors(['board_user' => $response['error']]);
        }

        // Check for warnings
        if (isset($response['warning'])) {
            return redirect()->route('boards.show', $board->id)
                             ->with('warning', $response['warning']);
        }
        
        // If successful
        return redirect()->route('boards.show', $board->id)
                         ->with('success', $response['success']);
    }
    
    public function show($boardId, $taskId)
    {
        $taskDetails = $this->taskService->getTaskDetails($boardId, $taskId);

        return view('boards.tasks.show', [
            'task' => $taskDetails['task'],
            'board' => $taskDetails['board'],
            'attachments' => $taskDetails['attachments'],
            'boardId' => $boardId,
        ]);
    }
    
    public function edit($boardId, $taskId)
    {
        $task = $this->taskService->getEditableTask($taskId);
    
        return view('boards.tasks.edit', compact('task', 'boardId'));
    }
    
    public function update(UpdateTaskRequest $request, $boardId, $taskId)
    {
        // Find the task by ID
        $task = Task::findOrFail($taskId);
        
        $this->authorize('isOwnerOrCollaborator', $task);

        // Get validated data from the request
        $validatedData = $request->validated();
    
        // Check if the progress is being updated
        $progressChanged = $task->progress !== $validatedData['progress'];
    
        // Update the task
        $task->update($validatedData);
    
        // Determine the message based on the progress change and source
        $message = 'Task updated successfully.';
        if ($progressChanged) {
            switch ($validatedData['progress']) {
                case 'to_do':
                    $message = 'Task reopened successfully.';
                    break;
                case 'in_progress':
                    $message = 'Task started successfully.';
                    break;
                case 'done':
                    $message = 'Task completed successfully.';
                    break;
            }
        }
    
        // Redirect back to the specific task's detail page
        return redirect()->route('boards.tasks.show', ['boardId' => $boardId, 'taskId' => $taskId])
                         ->with('success', $message);
    }
    
    
    public function uploadFile(UploadFileRequest $request, Task $task) 
    {
        $this->authorize('isOwnerOrCollaborator', $task);
    
        $taskName = $task->name;
    
        // Add media to task
        $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');
    
        // Redirect with boardId and taskId
        return to_route('boards.tasks.show', ['boardId' => $task->board_id, 'taskId' => $task->id])
            ->with('success', 'Successfully added an attachment to ' . $taskName . '.');
    }
    
    

    public function destroy($boardId, $taskId)
    {
        // Find and delete the task
        $task = Task::findOrFail($taskId);

        $this->authorize('isOwnerOrCollaborator', $task);
        
        $task->delete();
    
        // Redirect back to the board with the correct boardId
        return redirect()->route('boards.show', ['id' => $boardId])->with('success', 'Task deleted successfully');
    }
    
    
    // js/destroy.js (Deleting a task from a board using the dropdown.)
    public function remove($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Check authorization before deleting the task
            $this->authorize('isOwnerOrCollaborator', $task);

            // Get the board ID before deleting the task
            $boardId = $task->board_id;

            // Directly delete the task
            $task->delete();

            // Dispatch the TaskDeleted event
            broadcast(new BoardTaskDeleted($task->id, $boardId));

            return response()->json(['message' => 'Task removed successfully.']);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Task not found.'], 404);
        } catch (AuthorizationException $e) {
            // Return a JSON response with 403 status code
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function updateStatus(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $this->authorize('isOwnerOrCollaborator', $task);
        
        $request->validate([
            'progress' => 'required|string|in:to_do,in_progress,done',
        ]);
        
        $task->progress = $request->input('progress');
        $task->save();

        broadcast(new BoardTaskUpdated($task->id, $task->board_id, auth()->id()));

        return response()->json(['success' => true, 'message' => 'Task status updated']);
    }

    public function destroyFile(Task $task, $attachmentId)
    {  
        $this->authorize('isOwnerOrCollaborator', $task);

        $media = $task->getMedia('attachments')->find($attachmentId);
        if ($media) {
            $media->delete();
        }
        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
        

}
