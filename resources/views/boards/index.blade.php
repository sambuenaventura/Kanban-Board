<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>

        </div>
    </x-slot>
    
    {{-- Board Owned --}}
    <div class="flex flex-col pb-6">
        <div class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="h-full p-4 sm:p-6 text-gray-900 flex flex-col">
                    <div class="flex items-center mb-6 pb-4 border-b">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">My Boards</h1>
                        <div class="flex space-x-4 ml-auto">
                            <button id="openBoardModalBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create New Board
                            </button>
                        </div>
                    </div>
    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($boardsOwned as $board)
                            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-1 duration-300 ease-in-out border border-gray-200 flex flex-col h-full">
                                <div class="bg-indigo-600 h-1"></div>
                                <div class="p-6 flex-grow flex flex-col">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-gray-900 truncate">{{ $board->name }}</h3>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $board->user->id == $userId ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $board->user->id == $userId ? 'You' : $board->user->name }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 mb-4 long-text">{{ $board->description ?? 'No description available.' }}</p>
                                    <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                                        <span class="flex items-center bg-indigo-50 px-2 py-1 rounded-lg">
                                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            {{ $board->tasks_count }} Tasks
                                        </span>
                                        <span class="flex items-center bg-gray-50 px-2 py-1 rounded-lg">
                                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Created {{ $board->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                    
                                    <div class="flex flex-col space-y-2 flex-grow">
                                        @if($board->taskCounts['overdue'] > 0 || $board->taskCounts['dueToday'] > 0 || $board->taskCounts['dueSoon'] > 0)
                                            @if($board->taskCounts['overdue'] > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-100 text-red-800 text-xs">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                    Overdue: {{ $board->taskCounts['overdue'] }}
                                                </span>
                                            @endif
                                            @if($board->taskCounts['dueToday'] > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-yellow-100 text-yellow-800 text-xs">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                    Due Today: {{ $board->taskCounts['dueToday'] }}
                                                </span>
                                            @endif
                                            @if($board->taskCounts['dueSoon'] > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-orange-100 text-orange-800 text-xs">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                    Due Soon: {{ $board->taskCounts['dueSoon'] }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-100 text-green-800 text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                All tasks completed or not due soon
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-t border-gray-200 mt-auto">
                                    <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">View Board</a>
                                    <div class="flex space-x-3">
                                        <button class="text-gray-600 hover:text-gray-800 edit-board-btn" data-board-id="{{ $board->id }}" data-board-name="{{ $board->name }}" data-board-description="{{ $board->description }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" class="text-red-600 hover:text-red-800 delete-board-btn" data-board-id="{{ $board->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full text-center py-12 bg-gray-100 rounded-lg shadow-md border border-gray-200">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <p class="mt-4 text-lg font-medium text-gray-600">No boards found. Click "Create New Board" to get started.</p>
                            </div>
                        @endforelse
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Board as a Collaborator --}}
    <div class="flex flex-col pb-6">
        <div class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="h-full p-4 sm:p-6 text-gray-900 flex flex-col">
                    <div class="flex items-center mb-6 pb-4 border-b">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">Team Boards</h1>
                        <div class="flex space-x-4 ml-auto">
                            {{-- <button id="openBoardModalBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create New Board
                            </button> --}}
                        </div>
                    </div>
    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="board-container">                        
                        @forelse ($boardsCollaborated as $board)
                        
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-1 duration-300 ease-in-out border border-gray-200 flex flex-col h-full" data-board-id="{{ $board->id }}">
                            <div class="bg-indigo-900 h-1"></div>
                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-900 truncate">{{ $board->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $board->user->id == $userId ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $board->user->id == $userId ? 'You' : $board->user->name }}
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 mb-4">{{ $board->description ?? 'No description available.' }}</p>
                                <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                                    <span class="flex items-center bg-indigo-50 px-2 py-1 rounded-lg">
                                        <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                        {{ $board->tasks_count }} Tasks
                                    </span>
                                    <span class="flex items-center bg-gray-50 px-2 py-1 rounded-lg">
                                        <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Created {{ $board->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                        
                                <div class="flex flex-col space-y-2 flex-grow">
                                    @if($board->taskCounts['overdue'] > 0 || $board->taskCounts['dueToday'] > 0 || $board->taskCounts['dueSoon'] > 0)
                                        @if($board->taskCounts['overdue'] > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-100 text-red-800 text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                Overdue: {{ $board->taskCounts['overdue'] }}
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueToday'] > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-yellow-100 text-yellow-800 text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                Due Today: {{ $board->taskCounts['dueToday'] }}
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueSoon'] > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-orange-100 text-orange-800 text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                Due Soon: {{ $board->taskCounts['dueSoon'] }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-100 text-green-800 text-xs">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            All tasks completed or not due soon
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-t border-gray-200 mt-auto">
                                <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">View Board</a>
                                <div class="flex space-x-3">
                                    <button class="text-gray-600 hover:text-gray-800 edit-board-btn" data-board-id="{{ $board->id }}" data-board-name="{{ $board->name }}" data-board-description="{{ $board->description }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" class="text-red-600 hover:text-red-800 delete-board-btn" data-board-id="{{ $board->id }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-12 bg-gray-100 rounded-lg shadow-md border border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" height="64px" viewBox="0 -960 960 960" width="64px" class="mx-auto" fill="#9CA3AF">
                                <path d="M240-120q-66 0-113-47T80-280q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm480 0q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm-480-80q33 0 56.5-23.5T320-280q0-33-23.5-56.5T240-360q-33 0-56.5 23.5T160-280q0 33 23.5 56.5T240-200Zm480 0q33 0 56.5-23.5T800-280q0-33-23.5-56.5T720-360q-33 0-56.5 23.5T640-280q0 33 23.5 56.5T720-200ZM480-520q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T560-680q0-33-23.5-56.5T480-760q-33 0-56.5 23.5T400-680q0 33 23.5 56.5T480-600Zm0-80Zm240 400Zm-480 0Z"/>
                            </svg>
                            <p class="mt-4 text-lg font-medium text-gray-600">No boards found. Join a team to start collaborating!</p>
                        </div>
                        
                        
                        
                    @endforelse
                    
                    </div>
                    

                    
                </div>
            </div>
        </div>
    </div>  

    <!-- JavaScript files with Vite -->
    @vite([
        'resources/views/boards/scripts/boards.index.js',
        'resources/views/boards/scripts/boards.add-board-modal.js',
        'resources/views/boards/scripts/boards.edit-board-modal.js',
        'resources/views/boards/scripts/boards.delete-board-modal.js',
    ])

    <!-- Create Board Modal -->
    <x-board-modal modal-type="create" />

    <!-- Edit Board Modal -->
    <x-board-modal modal-type="update" />

    <!-- Delete Board Confirmation Modal -->
    <x-board-modal modal-type="delete" />

</x-app-layout>