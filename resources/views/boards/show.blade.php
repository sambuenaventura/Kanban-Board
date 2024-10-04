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
                        <button @click="showFilters = !showFilters" class="flex-shrink-0 inline-flex items-center px-2 py-2 mr-2 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-300 focus:ring focus:ring-gray-200 disabled:opacity-25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path>
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

                        <!-- Filter Sidebar -->
                        <div class="w-full sm:w-64 bg-gray-100 p-4 rounded-lg mb-4 sm:mb-0 overflow-y-auto" x-show="showFilters">
                            <h3 class="text-lg font-semibold mb-4">Filter by tag</h3>
                            <form id="filter-form" action="{{ route('boards.show', $board->id) }}" method="GET">
                                @foreach($allTags as $tag)
                                <ul class="mb-2">
                                    <li>
                                        <label class="inline-flex items-center mr-2 mb-2">
                                            <input type="checkbox" name="tags[]" class="form-checkbox" value="{{ $tag }}"
                                                {{ in_array($tag, $selectedTags) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ $tag }}</span>
                                        </label>
                                    </li>
                                </ul>
                                @endforeach
                                <button type="button" onclick="applyFilters()" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    </svg>
                                    Apply
                                </button>                            
                            </form>
                        </div>       

                        <!-- To Do Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">To Do <span class="text-xs task-count">({{ $taskCounts['to_do'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="to_do">
                                @foreach ($toDoTasks as $date => $tasksForDate)
                                    <div class="mb-4">
                                        <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                        <ul class="space-y-3">
                                            @foreach ($tasksForDate as $task)
                                                <li class="bg-white p-4 rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1" draggable="true" data-task-id="{{ $task->id }}">
                                                    @if ($task->due_day)
                                                        <div class="bg-red-400 text-white text-xs font-bold py-1 px-2 rounded flex items-center absolute top-0 left-0">
                                                            <span class="material-symbols-outlined text-sm mr-1">event</span>
                                                            {{ $task->due_day }}
                                                        </div>
                                                    @endif
                                                    <div class="flex items-center justify-between {{ $task->due_day ? 'mt-6' : '' }}">
                                                        <div class="inline-block">
                                                            <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="inline group">
                                                                <span class="text-gray-700 hover:text-blue-600 transition-all duration-200">{{ $task->name }}</span>
                                                            </a>
                                                        </div>
                                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                                            <span class="px-2 py-1 text-xs font-semibold text-yellow-700 {{ $task->priority === 'high' ? 'bg-red-200' : ($task->priority === 'medium' ? 'bg-orange-200' : 'bg-yellow-200') }} rounded-full whitespace-nowrap">
                                                                {{ $task->formatted_priority }}
                                                            </span>
                                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap">
                                                                {{ $task->tag }}
                                                            </span>
                                                            <div class="relative flex items-center">
                                                                <span class="material-symbols-outlined cursor-pointer toggle-dropdown" data-task-id="{{ $task->id }}">
                                                                    more_vert
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="relative">
                                                        <div id="dropdown-{{ $task->id }}" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg z-10 hidden">
                                                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 confirm-remove" data-task-id="{{ $task->id }}">Remove?</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- @empty
                                    <div class="col-span-full flex items-center justify-center h-full py-12 bg-white rounded-lg shadow-md border border-indigo-100">
                                        <p class="text-gray-600 text-lg">No tasks in the To Do column.</p>
                                    </div>                                     --}}
                                @endforeach                            
                            </div>
                        </div>

                        <!-- In Progress Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">In Progress <span class="text-xs task-count">({{ $taskCounts['in_progress'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="in_progress">
                                @foreach ($inProgressTasks as $date => $tasksForDate)
                                    <div class="mb-4">
                                        <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                        <ul class="space-y-3">
                                            @foreach ($tasksForDate as $task)
                                                <li class="bg-white p-4 rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1" draggable="true" data-task-id="{{ $task->id }}">
                                                    @if ($task->due_day)
                                                        <div class="bg-red-400 text-white text-xs font-bold py-1 px-2 rounded flex items-center absolute top-0 left-0">
                                                            <span class="material-symbols-outlined text-sm mr-1">event</span>
                                                            {{ $task->due_day }}
                                                        </div>
                                                    @endif
                                                    <div class="flex items-center justify-between {{ $task->due_day ? 'mt-6' : '' }}">
                                                        <div class="inline-block">
                                                            <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="inline group">
                                                                <span class="text-gray-700 hover:text-blue-600 transition-all duration-200">{{ $task->name }}</span>
                                                            </a>
                                                        </div>
                                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                                            <span class="px-2 py-1 text-xs font-semibold text-yellow-700 {{ $task->priority === 'high' ? 'bg-red-200' : ($task->priority === 'medium' ? 'bg-orange-200' : 'bg-yellow-200') }} rounded-full whitespace-nowrap">
                                                                {{ $task->formatted_priority }}
                                                            </span>
                                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap">
                                                                {{ $task->tag }}
                                                            </span>
                                                            <div class="relative flex items-center">
                                                                <span class="material-symbols-outlined cursor-pointer toggle-dropdown" data-task-id="{{ $task->id }}">
                                                                    more_vert
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="relative">
                                                        <div id="dropdown-{{ $task->id }}" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg z-10 hidden">
                                                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 confirm-remove" data-task-id="{{ $task->id }}">Remove?</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- @empty
                                    <div class="col-span-full flex items-center justify-center h-full py-12 bg-white rounded-lg shadow-md border border-indigo-100">
                                        <p class="text-gray-600 text-lg">No tasks currently in progress.</p>
                                    </div>                                     --}}
                                @endforeach                     
                            </div>
                        </div>

                        <!-- Done Column -->
                        <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                            <h4 class="font-semibold text-center bg-gray-700 p-4 text-white uppercase">Done <span class="text-xs task-count">({{ $taskCounts['done'] }})</span></h4>
                            <div class="kanban-column p-4 flex-grow overflow-y-auto" data-column="done">
                                @foreach ($doneTasks as $date => $tasksForDate)
                                    <div class="mb-4">
                                        <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                        <ul class="space-y-3">
                                            @foreach ($tasksForDate as $task)
                                                <li class="bg-white p-4 rounded-lg shadow-sm cursor-move relative transition duration-300 ease-in-out transform hover:-translate-y-1" draggable="true" data-task-id="{{ $task->id }}">
                                                    @if ($task->due_day)
                                                        <div class="bg-red-400 text-white text-xs font-bold py-1 px-2 rounded flex items-center absolute top-0 left-0">
                                                            <span class="material-symbols-outlined text-sm mr-1">event</span>
                                                            {{ $task->due_day }}
                                                        </div>
                                                    @endif
                                                    <div class="flex items-center justify-between {{ $task->due_day ? 'mt-6' : '' }}">
                                                        <div class="inline-block">
                                                            <a href="{{ route('boards.tasks.show', ['boardId' => $board->id, 'taskId' => $task->id]) }}" class="inline group">
                                                                <span class="text-gray-700 hover:text-blue-600 transition-all duration-200 strikethrough">{{ $task->name }}</span>
                                                            </a>
                                                        </div>
                                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                                            <span class="px-2 py-1 text-xs font-semibold text-yellow-700 {{ $task->priority === 'high' ? 'bg-red-200' : ($task->priority === 'medium' ? 'bg-orange-200' : 'bg-yellow-200') }} rounded-full whitespace-nowrap">
                                                                {{ $task->formatted_priority }}
                                                            </span>
                                                            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap">
                                                                {{ $task->tag }}
                                                            </span>
                                                            <div class="relative flex items-center">
                                                                <span class="material-symbols-outlined cursor-pointer toggle-dropdown" data-task-id="{{ $task->id }}">
                                                                    more_vert
                                                                </span>
                                                                <div class="relative">
                                                                    <div id="dropdown-{{ $task->id }}" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg z-10 hidden">
                                                                        <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 confirm-remove z-10" data-task-id="{{ $task->id }}">Remove?</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    {{-- @empty
                                    <div class="col-span-full flex items-center justify-center h-full py-12 bg-white rounded-lg shadow-md border border-indigo-100">
                                        <p class="text-gray-600 text-lg">No tasks in the Done column.</p>
                                    </div>                                     --}}
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
    
    {{-- Add Task Modal --}}
    <x-task-modal :board="$board" modal-type="create" />
    
    <!-- Remove Task Modal -->
    <x-task-modal modal-type="delete-board-task" />

    <!-- Manage Collaborators Modal -->
    <x-task-modal 
        :board="$board" 
        modal-type="manage-collaborator" 
        :collaborators="$collaborators" 
        :non-collaborators="$nonCollaborators" 
        :pending-invitations="$pendingInvitations" 
    />

</x-app-layout>

<script type="module">
    // Global variable for boardId
    window.boardId = {{ $board->id }};
</script>