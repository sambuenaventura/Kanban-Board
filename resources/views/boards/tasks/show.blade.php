
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Task Details
        </h2>
    </x-slot>
    
    {{-- Content --}}
    <div class="">
        <div class="mx-auto sm:px-6 lg:px-8 sm:pb-4 lg:pb-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <!-- Task Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">{{ $task->name }}</h1>
                        <div class="flex space-x-2">
                            <!-- Progress Button -->
                            <form action="{{ route('boards.tasks.update', ['boardId' => $boardId, 'taskId' => $task->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="source" value="progress">
                                <div class="relative inline-block">
                                    <select 
                                        onchange="this.form.submit(); adjustWidth(this);" 
                                        name="progress" 
                                        class="task-progress-select appearance-none border-none text-white pr-8 pl-3 py-2 rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 transition"
                                        style="color: white; 
                                            background-color: {{ $task->progress == 'to_do' ? '#4F46E5' : ($task->progress == 'in_progress' ? '#D97706' : '#16A34A') }};">
                                        <option value="to_do" {{ old('progress', $task->progress) == 'to_do' ? 'selected' : '' }}>To Do</option>
                                        <option value="in_progress" {{ old('progress', $task->progress) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="done" {{ old('progress', $task->progress) == 'done' ? 'selected' : '' }}>Done</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                        </svg>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Edit Button -->
                            <a href="{{ route('boards.tasks.edit', ['boardId' => $boardId, 'taskId' => $task->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            <!-- Delete Button -->
                            <button type="button" data-task-id="{{ $task->id }}" data-board-id="{{ $boardId }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition delete-task-btn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
    
                    <!-- Task Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Task Information</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Due Date</p>
                                    <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($task->due)->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Priority</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $task->priority == 'high' ? 'bg-red-100 text-red-800' : ($task->priority == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Progress</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $task->progress == 'to_do' ? 'bg-blue-100 text-blue-800' : ($task->progress == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ str_replace('_', ' ', ucfirst($task->progress)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Tag</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        {{ $task->tag }}
                                    </span>
                                </div>
                            </div>
                        </div>
    
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                            <p class="text-gray-700">{{ $task->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
    
                    <!-- Task Actions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Attachment</h3>
                        <form action="{{ route('tasks.uploadFile', $task) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            @method('POST')
                            <div class="flex items-center space-x-4">
                                <input type="file" name="attachment" accept="file/*" class="block w-56 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                    Upload
                                </button>
                            </div>
                        </form>
                        @if($task->getMedia('attachments')->isNotEmpty())
                            @foreach ($task->getMedia('attachments')->sortDesc() as $attachment)
                            <div class="mt-4">
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <img 
                                            src="{{ $attachment->getUrl() }}" 
                                            alt="Attachment" 
                                            class="w-20 h-20 object-cover rounded-md cursor-pointer" 
                                            data-modal-target="#modal-{{ $attachment->id }}" 
                                            loading="lazy"
                                        >
                                        <div class="ml-4 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->file_name }}</p>
                                            <p class="text-sm text-gray-500">{{ $attachment->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                        <button type="button" data-task-id="{{ $task->id }}" data-attachment-id="{{ $attachment->id }}" class="delete-attachment-btn ml-4 inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        
                                        
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Modal for Previewing Image -->
                            <div id="modal-{{ $attachment->id }}" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 hidden z-50 overflow-hidden">
                                <div class="relative max-w-[90vw] max-h-[90vh] bg-white rounded-lg overflow-hidden flex flex-col items-center justify-center">
                                    <img src="{{ $attachment->getUrl() }}" alt="Attachment" class="max-w-full max-h-[80vh] object-contain">
                                </div>
                                <button class="absolute top-6 right-6 p-1 close-modal rounded-md hover:bg-gray-500 hover:bg-opacity-30" data-modal-target="#modal-{{ $attachment->id }}">
                                    <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        @else
                            <p class="mt-4 text-gray-500">No attachments uploaded yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for deleting task --}}
    <x-task-modal modal-type="delete-task" />

    
    {{-- Modal for deleting attachment --}}
    <x-task-modal modal-type="delete-attachment" />
    
</x-app-layout>
