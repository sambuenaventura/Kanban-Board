<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{


    public function index(Request $request, $tags = null)
    {
        $tasks = Task::getUserTasks();
    
        $tasks = Task::forUser()->filterByTags($tags)->get();
        
        $toDoTasks = Task::getTaskByProgress($tasks, 'to_do');
        $inProgressTasks =  Task::getTaskByProgress($tasks, 'in_progress');
        $doneTasks =  Task::getTaskByProgress($tasks, 'done');
    
        $countToDo =  Task::countTaskByStatus($tasks, 'to_do');
        $countInProgress =  Task::countTaskByStatus($tasks, 'in_progress');
        $countDone =  Task::countTaskByStatus($tasks, 'done');
    
        $allTags = Task::getAllTags();
    
        $selectedTags = $tags ? explode(',', $tags) : [];
    
        return view('task.index', compact(
            'tasks',
            'toDoTasks',
            'inProgressTasks',
            'doneTasks',
            'countToDo',
            'countInProgress',
            'countDone',
            'allTags',
            'selectedTags'
        ));
    }
    

    public function store(StoreTaskRequest $request)
    {
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
    
        Task::create($data);    
        
        return to_route('tasks.index')->with('success', 'Task created.');
    }
    
    public function show(Task $task)
    {
        $task = Task::forUser()->find($task->id);
    
        if (!$task) {
            abort(403);
        }
    
        return view('task.show', ['task' => $task]);
    }
    
    public function edit(Task $task)
    {
        $task = Task::forUser()->find($task->id);
    
        if (!$task) {
            abort(403);
        }
        
        return view('task.edit' , ['task' => $task]);
    }


    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task = Task::forUser()->findOrFail($task->id);
        
        $validatedData = $request->validated();

        $task->update($validatedData);
    
        if ($request->input('source') === 'progress' && isset($validatedData['progress']) && $task->wasChanged('progress')) {
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
        } else {
            $message = 'Task updated successfully.';
        }
    
        return to_route('tasks.show', $task)->with('success', $message);
    }
    

    public function uploadFile(UploadFileRequest $request, Task $task) {
        
        $taskName = $task->name;

        $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');

        return to_route('tasks.show', $task)->with('success', 'Successfully added an attachment to ' . $taskName . '.');

    }

    public function destroyFile(Task $task, $attachmentId)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    
        $attachmentItem = $task->media()->findOrFail($attachmentId);
    
        if ($attachmentItem) {
            $attachmentItem->delete();
        } else {
            return redirect()->route('tasks.show', $task)->with('error', 'Attachment item not found.');
        }
    
        return redirect()->route('tasks.show', $task)->with('success', 'Attachment deleted successfully.');
    }
    

    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    
        $task->delete();
    
        if ($request->expectsJson()) {
            return response()->json(['success' => 'Task deleted successfully', 'task' => $task]);
        }
    
        return to_route('tasks.index')->with('success', 'Task deleted successfully.');
    }
    
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {

        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    
        $task->progress = $request->newStatus;
        $task->save();
    
        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

}
