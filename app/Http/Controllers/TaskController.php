<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Notification;
Use App\Notifications\MyFirstNotification;

class TaskController extends Controller
{


    public function index(Request $request, $tags = null)
    {
        $tasks = Task::getUserTasks();
    
        $tasks = Task::forUser()->filterByTags($tags)->get();
        
        // Filter tasks by tags if any are selected
        // $tasks = $this->filterTasksByTags($tasks, $tags);
    


        // Retrieve tasks in different statuses
        $toDoTasks = Task::getTaskByProgress($tasks, 'to_do');
        $inProgressTasks =  Task::getTaskByProgress($tasks, 'in_progress');
        $doneTasks =  Task::getTaskByProgress($tasks, 'done');
    
        // Count tasks in each status
        $countToDo = $this->countToDo($tasks);
        $countInProgress = $this->countInProgress($tasks);
        $countDone = $this->countDone($tasks);
    
        // Get all tags for filtering
        $allTags = $this->getAllTags();
    
        // Convert tags from the URL into an array
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
            'selectedTags' // Pass selected tags to the view
        ));
    }
    
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
    
        Task::create($data);    
        
        return to_route('tasks.index')->with('success', 'Task created.');
    }
    
    // public function filterTags(Request $request)
    // {
    
    //     $data['user_id'] = $request->user()->id;
    //     $task = Task::create($data);
    
    //     // Corrected return statement
    //     return to_route('tasks.index')->with('success', 'Task was created');
    // }


    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task = Task::forUser()->find($task->id);
    
        if (!$task) {
            abort(403);
        }
    
        return view('task.show', ['task' => $task]);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
        return view('task.edit' , ['task' => $task]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'due' => ['required', 'date'],
            'priority' => ['required', 'string'],
            'progress' => ['required', 'string'],
            'tag' => ['required', 'string'],
        ]);

        $task->update($data);

        return to_route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function updateProgress(Request $request, Task $task) 
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        // $originalProgress = $task->progress;

        $data = $request->validate([
            'progress' => ['required', 'string'],
        ]);

        $task->update($data);

        // Set the success message based on the original Progress
        if ($data['progress'] == 'to_do') {
            $message = 'Task reopened successfully.';

        } elseif ($data['progress'] == 'in_progress') {
            $message = 'Task started successfully.';

        }
        else {
            $message = 'Task completed successfully.';
        }


        return to_route('tasks.show', $task)->with('success', $message);
    }

    // public function fileUpload(Request $request, Task $task) {

    //     $data = $request->validate([
    //         'attachment' => ['file'],
    //     ]);
        
    //     if ($request->hasFile('attachment')) {
    //         $attachment = $request->file('attachment')->store('public');
    //         $data['attachment'] = $attachment; // Add file path to $data
    //     }
        
        

    //     $task->update($data);

    //     return to_route('tasks.show', $task)->with('success', 'Success');

    // }
    
    public function fileUpload(Request $request, Task $task) {

        $data = $request->validate([
            'attachment' => ['required', 'file'],
        ],
        [
            'attachment.required' => 'Please attach a file.',
        ]);
        
        $taskName = $task->name;

        // if ($request->hasFile('attachment')) {
        //     $attachment = $request->file('attachment')->store('public');
        //     $data['attachment'] = $attachment; // Add file path to $data
        // }

        $task->addMediaFromRequest('attachment')->toMediaCollection('attachments');

        return to_route('tasks.show', $task)->with('success', 'Successfully added an attachment to ' . $taskName . '.');
        // return to_route('tasks.show', $task)->with('success', 'Successfully added an attachment to ' . '\''. $taskName. '\'.'); // With ''

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
    

    // Function for the deleting in task details
    public function destroy(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->delete();
        
        return to_route('tasks.index')->with('success', 'Task deleted successfully.');

    }

    // Function for the fetch api
    public function deleteTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    
        $task->delete();
    
        return response()->json(['success' => 'Task deleted successfully', 'task' => $task]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'taskId' => 'required|exists:tasks,id',
            'newStatus' => 'required|in:to_do,in_progress,done',
        ]);

        $task = Task::findOrFail($request->taskId);
        $task->progress = $request->newStatus;
        $task->save();

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    private function countTodo($tasks) 
    {
        return $tasks->where('progress', 'to_do')
        ->count();
    }

    private function countInProgress($tasks) 
    {
        return $tasks->where('progress', 'in_progress')
        ->count();
    }

    private function countDone($tasks) 
    {
        return $tasks->where('progress', 'done')
        ->count();
    }

    // This is what i did #2
    private function getAllTags() 
    {
        return Task::where('user_id', auth()->id())
            ->distinct()
            ->pluck('tag')
            ->filter(); // This removes any null or empty string tags
    }
   

}
