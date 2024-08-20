<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>

        </div>
    </x-slot>
    

    <div class="flex flex-col h-[calc(100vh-13rem)]"> <!-- Adjust 4rem based on your actual header height -->
        <div x-data="{ showFilters: {{ count($selectedTags) > 0 ? 'true' : 'false' }} }" class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="h-full p-4 sm:p-6 text-gray-900 flex flex-col ">
                    <div class="flex items-center mb-6 pb-4 border-b">
                        <button @click="showFilters = !showFilters" class="inline-flex items-center px-2 py-2 mr-2 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-300 focus:ring focus:ring-gray-200 disabled:opacity-25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"></path>
                            </svg>
                        </button>
                        
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">Task Board</h1>
                        <div class="flex space-x-4 ml-auto">
                            <button id="openModalBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Todo
                            </button>
                        </div>
                    </div>
                    
                    
                    
                    <!-- Todo Modal -->
                    <div id="todoModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                Edit Todo
                                            </h3>
                                            <button type="button" id="closeModalBtn2" class="absolute top-3 right-3 text-gray-400 hover:text-gray-500">
                                                <span class="sr-only">Close</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                            <div class="mt-2">
                                                <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label for="name" class="block text-sm font-medium text-gray-700">Todo name</label>
                                                        <input type="text" name="name" id="name" placeholder="Add a new todo" value="{{ old('name') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                                        <textarea name="description" id="description" placeholder="Add description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="due" class="block text-sm font-medium text-gray-700">Due date</label>
                                                        <input type="date" name="due" id="due" value="{{ old('due') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                                        <select name="priority" id="priority" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                            <option value="" disabled selected>Select priority</option>
                                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="progress" class="block text-sm font-medium text-gray-700">Progress</label>
                                                        <select name="progress" id="progress" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                            <option value="" disabled selected>Select progress</option>
                                                            <option value="to_do" {{ old('progress') == 'to_do' ? 'selected' : '' }}>To Do</option>
                                                            <option value="in_progress" {{ old('progress') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                            <option value="done" {{ old('progress') == 'done' ? 'selected' : '' }}>Done</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                                                        <input type="text" name="tag" id="tag" value="{{ old('tag') }}" placeholder="Add tag" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                    </div>
                                                    <div class="flex justify-end space-x-3">
                                                        <button type="button" id="closeModalBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Add Todo
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Container Filter and Columns -->
                    <div class="flex flex-col sm:flex-row flex-grow overflow-hidden">
                        <!-- Filter Sidebar -->
                        <div class="w-full sm:w-64 bg-gray-100 p-4 rounded-lg mb-4 sm:mb-0 sm:mr-4 overflow-y-auto" x-show="showFilters">
                            <h3 class="text-lg font-semibold mb-4">Filter by tag</h3>
                            <form id="filter-form">
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
                        <!-- Main Content -->
                        <div class="flex-grow overflow-hidden">
                            {{-- <button @click="showFilters = !showFilters" class="bg-blue-500 text-white px-4 py-2 rounded-md mb-4 w-full sm:w-auto">Toggle Filters</button>                --}}
                            <!-- Kanban Board -->
                            <div class="h-full flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 overflow-x-auto">
                                <!-- To Do Column -->
                                <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                                    <h4 class="font-semibold text-center bg-blue-600 p-4 text-white">To Do <span class="text-xs task-count">({{ $countToDo }})</span></h4>
                                    <div id="todo-list" class="kanban-column p-4 flex-grow overflow-y-auto" data-column="to_do">
                                        @foreach ($toDoTasks as $date => $tasksForDate)
                                            <div class="mb-4">
                                                <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                                <ul class="space-y-3">
                                                    @foreach ($tasksForDate as $task)
                                                        <li class="bg-white p-4 rounded-lg shadow-sm cursor-move" draggable="true" data-task-id="{{ $task->id }}">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="inline-block">
                                                                        <a href="{{ route('tasks.show', $task) }}" class="inline group">
                                                                            <span class="text-gray-700 hover:text-blue-600 transition-all duration-200" :class="{ 'text-task_name': showFilters }">
                                                                                {{ $task->name }}
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                                                        <span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
                                                                            {{ $task->priority }}
                                                                        </span>
                                                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
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
                                        @endforeach
                                    </div>
                                </div>                    
                                <!-- In Progress Column -->
                                <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                                    <h4 class="font-semibold text-center bg-yellow-600 p-4 text-white">In Progress <span class="text-xs task-count">({{ $countInProgress }})</span></h4>
                                    <div id="in-progress-list" class="kanban-column p-4 flex-grow overflow-y-auto" data-column="in_progress">
                                        @foreach ($inProgressTasks as $date => $tasksForDate)
                                            <div class="mb-4">
                                                <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                                <ul class="space-y-3">
                                                    @foreach ($tasksForDate as $task)
                                                        <li class="bg-white p-4 rounded-lg shadow-sm cursor-move" draggable="true" data-task-id="{{ $task->id }}">
                                                            <div class="flex items-center justify-between">
                                                                <div class="inline-block">
                                                                    <a href="{{ route('tasks.show', $task) }}" class="inline group">
                                                                        <span class="text-gray-700 hover:text-blue-600 transition-all duration-200" :class="{ 'text-task_name': showFilters }">
                                                                            {{ $task->name }}
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                                <div class="flex items-center space-x-2 flex-shrink-0">
                                                                    <span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
                                                                        {{ $task->priority }}
                                                                    </span>
                                                                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
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
                                        @endforeach
                                    </div>
                                </div>
                                <!-- Done Column -->
                                <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden flex flex-col min-w-[300px]">
                                    <h4 class="font-semibold text-center bg-green-600 p-4 text-white">Done <span class="text-xs task-count">({{ $countDone }})</span></h4>
                                    <div id="done-list" class="kanban-column p-4 flex-grow overflow-y-auto" data-column="done">
                                        @foreach ($doneTasks as $date => $tasksForDate)
                                            <div class="mb-4">
                                                <h5 class="text-sm font-semibold text-gray-600 mb-2">{{ \Carbon\Carbon::parse($date)->format('n/j/Y') }}</h5>
                                                <ul class="space-y-3">
                                                    @foreach ($tasksForDate as $task)
                                                        <li class="bg-white p-4 rounded-lg shadow-sm cursor-move" draggable="true" data-task-id="{{ $task->id }}">
                                                            <div class="flex items-center justify-between">
                                                                <div class="inline-block">
                                                                    <a href="{{ route('tasks.show', $task) }}" class="inline group">
                                                                        <span class="text-gray-700 strikethrough hover:text-blue-600 transition-all duration-200" :class="{ 'text-task_name': showFilters }">
                                                                            {{ $task->name }}
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                                <div class="flex items-center space-x-2 flex-shrink-0">
                                                                    <span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
                                                                        {{ $task->priority }}
                                                                    </span>
                                                                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full whitespace-nowrap" :class="{ 'text-task_tag': showFilters }">
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
                                        @endforeach
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
