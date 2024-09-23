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
    <div class="flex flex-co pb-6">
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
                            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-1 border border-indigo-100 overflow-hidden">
                                <div class="bg-gray-700 h-2"></div>
                                <div class="p-5">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-xl font-semibold text-gray-900 truncate">{{ $board->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $board->user->id == $userId ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $board->user->id == $userId ? 'You' : $board->user->name }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 mb-4">{{ $board->description ?? 'No description available.' }}</p>
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="flex items-center bg-indgrigo-100 px-2 py-1 rounded">
                                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            Tasks: {{ $board->tasks_count }}
                                        </span>
                                        <span class="flex items-center bg-indigo-100 px-2 py-1 rounded">
                                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $board->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3 flex justify-between items-center border-t border-indigo-100">
                                    <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View Board</a>
                                    <div class="flex space-x-2">
                                        <button 
                                            class="text-gray-600 hover:text-gray-800 edit-board-btn" 
                                            data-board-id="{{ $board->id }}"
                                            data-board-name="{{ $board->name }}"
                                            data-board-description="{{ $board->description }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" class="text-red-600 hover:text-red-800 delete-board-btn" data-board-id="{{ $board->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-md border border-indigo-100">
                                <p class="text-gray-600 text-lg">No boards found. Click "Create New Board" to get started.</p>
                            </div>
                        @endforelse
                    </div>
                    
                </div>
            </div>
        </div>
    </div>  

    {{-- Board as a Collaborator --}}
    <div class="flex flex-co pb-6">
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
    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($boardsCollaborated as $board)
                            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 ease-in-out transform hover:-translate-y-1 border border-indigo-100 overflow-hidden">
                                <div class="bg-gray-400  h-2"></div>
                                <div class="p-5">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-xl font-semibold text-gray-900 truncate">{{ $board->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $board->user->id == $userId ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $board->user->id == $userId ? 'You' : $board->user->name }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 mb-4">{{ $board->description ?? 'No description available.' }}</p>
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="flex items-center bg-indigo-100 px-2 py-1 rounded">
                                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            Tasks: {{ $board->tasks_count }}
                                        </span>
                                        <span class="flex items-center bg-indigo-100 px-2 py-1 rounded">
                                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $board->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3 flex justify-between items-center border-t border-indigo-100">
                                    <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View Board</a>
                                    <div class="flex space-x-2">
                                        <button 
                                            class="text-gray-600 hover:text-gray-800 edit-board-btn" 
                                            data-board-id="{{ $board->id }}"
                                            data-board-name="{{ $board->name }}"
                                            data-board-description="{{ $board->description }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" class="text-red-600 hover:text-red-800 delete-board-btn" data-board-id="{{ $board->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-md border border-indigo-100">
                                <p class="text-gray-600 text-lg">No team boards found.</p>
                            </div>
                        @endforelse
                    </div>
                    
                </div>
            </div>
        </div>
    </div>  
    
    <!-- Create Board Modal -->
    <x-board-modal modal-type="create" />

    <!-- Edit Board Modal -->
    <x-board-modal modal-type="update" />

    <!-- Delete Board Confirmation Modal -->
    <x-board-modal modal-type="delete" />

</x-app-layout>
