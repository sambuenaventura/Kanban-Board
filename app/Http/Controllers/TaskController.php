<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\Board;
use App\Models\BoardUser;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index($boardId)
    {
        $tasks = Task::where('board_id', $boardId)->get(); // Good, retrieves tasks for the specified board
        return view('boards.tasks.index', compact('tasks', 'boardId')); // Correct
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

        // Check if this idempotency key has already been processed
        if (Cache::has('idempotency_' . $request->idempotency_key)) {
            return redirect()->route('boards.show', $request->input('board_id'))
                             ->with('info', 'Task has already been created.');
        }
    
        // Retrieve the board_user_id associated with the authenticated user and the board
        $boardUser = BoardUser::where('board_id', $request->input('board_id'))
                               ->where('user_id', auth()->id())
                               ->first();


        if (!$boardUser) {
            return redirect()->route('boards.show', $request->input('board_id'))
                             ->withErrors(['board_user' => 'You are not authorized to add tasks to this board.']);
        }
    
        // Create the task using mass assignment
        $task = Task::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'due' => $request->input('due'),
            'priority' => $request->input('priority'),
            'progress' => $request->input('progress'),
            'tag' => $request->input('tag'),
            'board_id' => $request->input('board_id'),
            'board_user_id' => $boardUser->id,
        ]);
    
        // Store the idempotency key in the cache to prevent duplicate processing
        Cache::put('idempotency_' . $request->idempotency_key, true, 86400); // Store for 24 hours
    
        return redirect()->route('boards.show', $request->input('board_id'))
                         ->with('success', 'Task created successfully.');
    }
    
    
    
    
    public function show($boardId, $taskId)
    {
        $task = Task::with('media')->findOrFail($taskId); // Load the task with its attachments
        $board = Board::findOrFail($boardId); // Fetch the board based on the boardId
    
        return view('boards.tasks.show', [
            'task' => $task,
            'board' => $board, // Pass the board to the view
            'boardId' => $boardId,
            'attachments' => $task->media // Pass the attachments to the view
        ]);
    }
    
    
    public function edit($boardId, $taskId)
    {
        $task = Task::findOrFail($taskId); // Find task by ID
        return view('boards.tasks.edit', compact('task', 'boardId')); // Pass the board ID as well
    }
    
    public function update(UpdateTaskRequest $request, $boardId, $taskId)
    {
        // Find the task by ID
        $task = Task::findOrFail($taskId);
    
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
        // $this->authorize('owner', $task);
    
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
        $task->delete();
    
        // Redirect back to the board with the correct boardId
        return redirect()->route('boards.show', ['id' => $boardId])->with('success', 'Task deleted successfully');
    }
    
    


// In app/Http/Controllers/TaskController.php

    public function remove($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Directly delete the task without authorization check
            $task->delete();
            return response()->json(['message' => 'Task removed successfully.']);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Task not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function updateStatus(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $request->validate([
            'progress' => 'required|string|in:to_do,in_progress,done',
        ]);
        
        $task->progress = $request->input('progress');
        $task->save();
        
        return response()->json(['success' => true, 'message' => 'Task status updated']);
    }

    // public function destroyFile($boardId, $taskId, $attachmentId)
    // {
    //     // Fetch the task by ID
    //     $task = Task::findOrFail($taskId);
        
    //     // Check if the user is authorized (assuming you're using the 'owner' method)
    //     $this->authorize('owner', $task);
    
    //     // Find the attachment and delete it
    //     if ($attachmentItem = $task->media()->find($attachmentId)) {
    //         $attachmentItem->delete();
    //     } else {
    //         return redirect()->route('boards.tasks.show', ['boardId' => $boardId, 'taskId' => $taskId])
    //                          ->with('error', 'Attachment item not found.');
    //     }
    
    //     return redirect()->route('boards.tasks.show', ['boardId' => $boardId, 'taskId' => $taskId])
    //                      ->with('success', 'Attachment deleted successfully');
    // }

    public function destroyFile(Task $task, $attachmentId)
    {  
        // $this->authorize('owner', $task);

        $media = $task->getMedia('attachments')->find($attachmentId);
        if ($media) {
            $media->delete();
        }
        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
        

}
