<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Task Details
        </h2>
    </x-slot>

    <div class="">
        <div x-data="{ showDeleteModalTask: false, showDeleteModalAttachment: false, attachmentId: null }">
            <div class="mx-auto sm:px-6 lg:px-8 sm:pb-4 lg:pb-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8">
                        <!-- Task Header -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">{{ $task->name }}</h1>
                            <div class="flex space-x-2">
                                <!-- Progress Button -->
                                <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="source" value="progress">

                                    {{-- <input type="hidden" name="progress" value="{{ $task->progress == 'to_do' ? 'in_progress' : ($task->progress == 'in_progress' ? 'done' : 'to_do') }}"> --}}
                                    {{-- <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"> --}}
                                    {{-- <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                        @if ($task->progress == "to_do")
                                            <!-- Icon for Start Task -->
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                        @elseif ($task->progress == "in_progress")
                                            <!-- Icon for Complete Task -->
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @else
                                            <!-- Icon for Reopen Task -->
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        @endif
                                        {{ $task->progress == 'to_do' ? 'Start Task' : ($task->progress == 'in_progress' ? 'Complete Task' : 'Reopen Task') }}
                                    </button> --}}
                                    <select 
                                    onchange="this.form.submit()" 
                                    name="progress" 
                                    class="inline-flex items-center px-2 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest disabled:opacity-25 transition
                                        {{ $task->progress == 'to_do' ? 'bg-indigo-600 hover:bg-indigo-500 focus:ring-indigo-300 w-[5.7rem]' : 
                                           ($task->progress == 'in_progress' ? 'bg-yellow-600 hover:bg-yellow-500 focus:ring-yellow-300 w-[8.5rem]' : 
                                           'bg-green-600 hover:bg-green-500 focus:ring-green-300 w-[5rem]') }}"
                                    style="color: #ffffff;">
                                    <option value="to_do" {{ old('progress', $task->progress) == 'to_do' ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ old('progress', $task->progress) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ old('progress', $task->progress) == 'done' ? 'selected' : '' }}>Done</option>
                                </select>

                                </form>
                                <!-- Edit Button -->
                                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <!-- Delete Button -->
                                <button @click="showDeleteModalTask = true" type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                                <x-delete-modal-task :route="route('tasks.destroy', $task)" />

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
                                            <!-- Display file name here -->
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->file_name }}</p>
                                            <!-- Display creation date here -->
                                            <p class="text-sm text-gray-500">{{ $attachment->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                        {{-- <form action="{{ route('tasks.destroyFile', [$task, $media->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE') --}}
                                            <button @click="attachmentId = {{ $attachment->id }}; showDeleteModalAttachment = true" type="button" class="ml-4 inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        {{-- </form> --}}
                                    </div>
                                </div>
                            </div>

                            
                        <!-- Modal for Previewing Image -->
                        <div id="modal-{{ $attachment->id }}" class="modal fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 hidden z-50 overflow-hidden">
                            <div class="relative max-w-[90vw] max-h-[90vh] bg-white rounded-lg overflow-hidden flex flex-col items-center justify-center">
                                <!-- Image -->
                                <img src="{{ $attachment->getUrl() }}" alt="Attachment" class="max-w-full max-h-[80vh] object-contain">
                            </div>
                            <!-- Close Button -->
                            <button class="absolute top-6 right-6 p-1 close-modal rounded-md hover:bg-gray-500 hover:bg-opacity-30" data-modal-target="#modal-{{ $attachment->id }}">
                                <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>


                            @endforeach

                        {{-- <div x-show="showDeleteModalAttachment" 
                            x-transition:enter="ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed z-10 inset-0 overflow-y-auto" 
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div 
                                    x-show="showDeleteModalAttachment"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                    aria-hidden="true">
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div 
                                    x-show="showDeleteModalAttachment"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Delete Task
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">
                                                        Are you sure you want to delete this attachment? This action cannot be undone.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <form action="{{ route('tasks.destroyFile', [$task, $media->id]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Delete
                                                </button>
                                            </form>
                                            <button @click="showDeleteModalAttachment = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                </div>
                            </div>
                        </div> --}}


                        <div x-show="showDeleteModalAttachment" 
                            x-transition:enter="ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed z-10 inset-0 overflow-y-auto" 
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div 
                                    x-show="showDeleteModalAttachment"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                                    aria-hidden="true">
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div 
                                    x-show="showDeleteModalAttachment"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                    Delete Task
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">
                                                        Are you sure you want to delete this attachment? This action cannot be undone.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        {{-- Route::delete('/tasks/{task}/{media}/files', [TaskController::class, 'destroyFile'])->name('tasks.destroyFile'); --}}
                                        <form :action="`{{ route('tasks.destroyFile', [$task->id, '']) }}/${attachmentId}`" method="POST" class="inline">
                                            {{-- <form action="{{ route('tasks.destroyFile', [$task, $media->id]) }}" method="POST" class="inline"> --}}
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Delete
                                            </button>
                                        </form>
                                        <button @click="showDeleteModalAttachment = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    

                        
                        </div>
                        
                    </div>
                </div>
            </div>





        </div>
        
    </div>
</x-app-layout>

<script>

</script>