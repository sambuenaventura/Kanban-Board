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
        $response = $this->taskService->updateTask($taskId, $request->validated());
        
        if (isset($response['error'])) {
            return redirect()->route('boards.index')
                             ->withErrors(['task' => $response['error']]);
        }
        
        if (isset($response['warning'])) {
            return redirect()->route('boards.tasks.edit', ['boardId' => $boardId, 'taskId' => $taskId])
                             ->with('warning', $response['warning']);
        }
        
        return redirect()->route('boards.tasks.show', ['boardId' => $boardId, 'taskId' => $response['task']->id])
                         ->with('success', $response['success']);
    }
    
    public function uploadFile(UploadFileRequest $request, Task $task) 
    {
        $response = $this->taskService->addAttachmentToTask($task, $request->file('attachment'));
    
        if (isset($response['error'])) {
            return redirect()->route('boards.show', $task->board_id)
                             ->withErrors(['task' => $response['error']]);
        }
    
        return to_route('boards.tasks.show', ['boardId' => $task->board_id, 'taskId' => $task->id])
            ->with('success', $response['success']);
    }

    public function destroy($boardId, $taskId)
    {
        $response = $this->taskService->deleteTask($taskId);
    
        if (isset($response['error'])) {
            return redirect()->route('boards.show', ['id' => $boardId])
                             ->withErrors(['task' => $response['error']]);
        }
    
        return redirect()->route('boards.show', ['id' => $boardId])
                         ->with('success', $response['success']);
    }
    
    // js/destroy.js (Deleting a task from a board using the dropdown.)
    public function remove($id)
    {
        try {
            $response = $this->taskService->deleteTaskAjax($id);
            return response()->json(['message' => $response['success']]);      
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Task not found.'], 404);
        } 
        catch (AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } 
        catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(UpdateTaskStatusRequest $request, $taskId)
    {
        try {
            $response = $this->taskService->updateTaskStatus($taskId, $request->input('progress'));
            return response()->json($response);
        } 
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Task not found.'], 404);
        } 
        catch (AuthorizationException $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        } 
        catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroyFile(Task $task, $attachmentId)
    {
        $response = $this->taskService->deleteAttachment($task, $attachmentId);

        if (isset($response['error'])) {
            return redirect()->back()->withErrors(['attachment' => $response['error']]);
        }
    
        return redirect()->back()->with('success', $response['message']);
    }
    


}
