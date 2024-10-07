<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>

        </div>
    </x-slot>

    {{-- Content --}}
    <div class="flex flex-col h-[calc(100vh-13rem)]">
        <div x-data="{ showFilters: {{ count($selectedTags) > 0 ? 'true' : 'false' }} }" class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="h-full p-4 sm:p-6 text-gray-900 flex flex-col ">
                    
                    <div class="flex items-center mb-6 pb-4 border-b">
                        <!-- Filter Button -->
                        <button onclick="toggleFilterModal()" class="mr-1 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </button>
                        <div class="flex-grow min-w-0 mr-4">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">
                                {{ $board->name }}
                                <span id="update-indicator" class="inline-flex items-center px-2 py-0.5 ml-2 text-xs font-medium text-indigo-800 bg-indigo-100 rounded-full opacity-0 transition-opacity duration-300">
                                    Updated
                                </span>
                            </h1>
                        </div>
                        <div class="flex space-x-4 flex-shrink-0">
                            <button id="openModalBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Task
                            </button>
                            
                            @if ($board->user_id == Auth::id())
                                <button id="openCollaboratorModalBtn" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-300 disabled:opacity-25 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Invite a Collaborator
                                </button>
                            @endif
                        </div>
                    </div>
                    

                    {{-- Board --}}
                    <div class="flex gap-6 flex-col sm:flex-row flex-grow overflow-hidden">

                        <!-- To Do Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">To Do <span class="text-xs task-count">({{ $taskCounts['to_do'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="to_do">
                                @foreach ($toDoTasks as $date => $tasksForDate)
                                <div class="mb-6">
                                    <h5 class="flex items-center text-sm font-semibold text-gray-600 mb-3">
                                        <span class="material-symbols-outlined text-gray-400 text-lg mr-1">schedule</span>
                                        <span class="text-gray-600">Due: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
                                    </h5>
                                    
                                    
                                    <ul class="space-y-4">
                                        @foreach ($tasksForDate as $task)
                                        <li class="bg-white rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden" draggable="true" data-task-id="{{ $task->id }}">
                                            <div class="p-4">
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="text-md font-medium text-gray-800 hover:text-blue-600 transition-all duration-200">{{ $task->name }}</a>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs font-semibold {{ $task->priority === 'high' ? 'text-red-700 bg-red-100' : ($task->priority === 'medium' ? 'text-orange-700 bg-orange-100' : 'text-yellow-700 bg-yellow-100') }} rounded-full">
                                                            {{ ucfirst($task->priority) }}
                                                        </span>
                                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                            {{ $task->tag }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($task->is_overdue || $task->is_due_today || $task->is_due_soon)
                                            <div class="w-full h-1 {{ $task->is_overdue ? 'bg-red-500' : ($task->is_due_today ? 'bg-yellow-500' : 'bg-orange-500') }}"></div>
                                            <div class="px-4 py-2 {{ $task->is_overdue ? 'bg-red-50 text-red-700' : ($task->is_due_today ? 'bg-yellow-50 text-yellow-700' : 'bg-orange-50 text-orange-700') }} text-sm font-medium">
                                                    @if($task->is_overdue)
                                                        Overdue
                                                    @elseif($task->is_due_today)
                                                        Due Today
                                                    @elseif($task->is_due_soon)
                                                        Due Soon
                                                    @endif                                                    
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- In Progress Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">In Progress <span class="text-xs task-count">({{ $taskCounts['in_progress'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="in_progress">
                                @foreach ($inProgressTasks as $date => $tasksForDate)
                                <div class="mb-6">
                                    <h5 class="flex items-center text-sm font-semibold text-gray-600 mb-3">
                                        <span class="material-symbols-outlined text-gray-400 text-lg mr-1">schedule</span>
                                        <span class="text-gray-600">Due: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
                                    </h5>

                                    <ul class="space-y-4">
                                        @foreach ($tasksForDate as $task)
                                        <li class="bg-white rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden" draggable="true" data-task-id="{{ $task->id }}">
                                            <div class="p-4">
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="text-md font-medium text-gray-800 hover:text-blue-600 transition-all duration-200">{{ $task->name }}</a>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs font-semibold {{ $task->priority === 'high' ? 'text-red-700 bg-red-100' : ($task->priority === 'medium' ? 'text-orange-700 bg-orange-100' : 'text-yellow-700 bg-yellow-100') }} rounded-full">
                                                            {{ ucfirst($task->priority) }}
                                                        </span>
                                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                            {{ $task->tag }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($task->is_overdue || $task->is_due_today || $task->is_due_soon)
                                                <div class="w-full h-1 {{ $task->is_overdue ? 'bg-red-500' : ($task->is_due_today ? 'bg-yellow-500' : 'bg-orange-500') }}"></div>
                                                <div class="px-4 py-2 {{ $task->is_overdue ? 'bg-red-50 text-red-700' : ($task->is_due_today ? 'bg-yellow-50 text-yellow-700' : 'bg-orange-50 text-orange-700') }} text-sm font-medium">
                                                    @if($task->is_overdue)
                                                        Overdue
                                                    @elseif($task->is_due_today)
                                                        Due Today
                                                    @elseif($task->is_due_soon)
                                                        Due Soon
                                                    @endif                                                    
                                                </div>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endforeach
                            </div>
                        </div>


                        <!-- Done Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">Done <span class="text-xs task-count">({{ $taskCounts['done'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="done">
                                @foreach ($doneTasks as $date => $tasksForDate)
                                <div class="mb-6">
                                    <h5 class="flex items-center text-sm font-semibold text-gray-600 mb-3">
                                        <span class="material-symbols-outlined text-gray-400 text-lg mr-1">schedule</span>
                                        <span class="text-gray-600">Due: {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
                                    </h5>

                                    <ul class="space-y-4">
                                        @foreach ($tasksForDate as $task)
                                        <li class="bg-white rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden" draggable="true" data-task-id="{{ $task->id }}">
                                            <div class="p-4">
                                                <div class="flex items-center justify-between">
                                                    <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="text-md font-medium text-gray-800 hover:text-blue-600 transition-all duration-200 strikethrough">{{ $task->name }}</a>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-1 text-xs font-semibold {{ $task->priority === 'high' ? 'text-red-700 bg-red-100' : ($task->priority === 'medium' ? 'text-orange-700 bg-orange-100' : 'text-yellow-700 bg-yellow-100') }} rounded-full">
                                                            {{ ucfirst($task->priority) }}
                                                        </span>
                                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                            {{ $task->tag }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endforeach
                            </div>
                        </div>



                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript files with Vite -->
    @vite([
        'resources/views/boards/scripts/boards.show.js',
        'resources/views/boards/scripts/boards.add-task-modal.js',
        'resources/views/boards/scripts/boards.add-collaborator-modal.js',
        'resources/views/boards/scripts/boards.drag-drop.js',
        'resources/views/boards/scripts/boards.delete-dropdown.js',
        'resources/views/boards/scripts/boards.fetch-delete-task.js',
        'resources/views/boards/scripts/boards.tag-filter.js',

    ])
    
    {{-- Filter Task Modal --}}
    <x-task-modal 
        :board="$board" 
        :all-tags="$allTags" 
        :selected-tags="$selectedTags" 
        :selected-priority="$selectedPriority" 
        modal-type="filter" 
    />
    {{-- Add Task Modal --}}
    <x-task-modal :board="$board" modal-type="create" />
    
    <!-- Remove Task Modal -->
    <x-task-modal modal-type="delete-board-task" />

    <!-- Manage Collaborators Modal -->
    <x-task-modal 
        :board="$board" 
        :collaborators="$collaborators" 
        :non-collaborators="$nonCollaborators" 
        :pending-invitations="$pendingInvitations"
        modal-type="manage-collaborator" 
    />

</x-app-layout>

<script type="module">
    // Global variable for boardId
    window.boardId = {{ $board->id }};
</script>