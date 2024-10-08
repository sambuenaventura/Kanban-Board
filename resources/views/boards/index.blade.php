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
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-6 border-b border-gray-200">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4 sm:mb-0">My Boards</h1>
                        <button id="openBoardModalBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Board
                        </button>
                    </div>
                    
                    {{-- Boards Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($boardsOwned as $board)
                            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-1 duration-300 ease-in-out border border-gray-200 flex flex-col">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-t-lg"></div>
                                <div class="p-6 flex-grow flex flex-col">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-gray-900 truncate">{{ $board->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Created {{ $board->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-4 flex-grow">{{ $board->description ?? 'No description available.' }}</p>
                                    
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            {{ $board->tasks_count }} Tasks
                                        </span>
                                        @if($board->taskCounts['overdue'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['overdue'] }} Overdue
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueToday'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['dueToday'] }} Due Today
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueSoon'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['dueSoon'] }} Due Soon
                                            </span>
                                        @endif
                                        @if($board->taskCounts['overdue'] == 0 && $board->taskCounts['dueToday'] == 0 && $board->taskCounts['dueSoon'] == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                All tasks on track
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- Collaborators --}}
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Collaborators:</p>
                                        <div class="flex items-center -space-x-2 overflow-hidden">
                                            @foreach ($board->sortedCollaborators as $index => $boardUser)
                                                @if (!isset($boardUser['remaining_count']))
                                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                        {{ $boardUser->role === 'owner' ? 'bg-indigo-500 text-white' : ($boardUser->user_id === $userId ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600') }}
                                                        border-2 border-white text-sm font-semibold"
                                                        title="{{ $boardUser->user->name }} ({{ ucfirst($boardUser->role) }})">
                                                        {{ strtoupper(substr($boardUser->user->name, 0, 1)) }}
                                                    </div>
                                                @else
                                                    @if ($boardUser['remaining_count'] > 0)
                                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 border-2 border-white text-sm font-semibold text-gray-600">
                                                            +{{ $boardUser['remaining_count'] }}
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>                   
                                    
                                </div>
                                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between items-center">
                                    <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">View Board</a>
                                    <div class="flex space-x-3">
                                        <button class="text-gray-600 hover:text-gray-800 transition edit-board-btn" data-board-id="{{ $board->id }}" data-board-name="{{ $board->name }}" data-board-description="{{ $board->description }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" class="text-red-600 hover:text-red-800 transition delete-board-btn" data-board-id="{{ $board->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full">
                                <div class="text-center py-12 bg-white rounded-lg shadow-md border border-gray-200">
                                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <p class="mt-4 text-lg font-medium text-gray-900">No boards found</p>
                                    <p class="mt-2 text-sm text-gray-600">Get started by creating a new board.</p>
                                    <button id="createFirstBoardBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Create Your First Board
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination for Owned Boards --}}
                    @if ($boardsOwned->hasPages())
                        <div class="mt-8">
                            {{ $boardsOwned->appends(['collaborated_page' => $boardsCollaborated->currentPage()])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Board as a Collaborator --}}
    <div class="flex flex-col pb-6">
        <div class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-6 border-b border-gray-200">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4 sm:mb-0">Team Boards</h1>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 mr-2">Boards you're collaborating on</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $boardsCollaborated->total() }} Boards
                            </span>
                        </div>
                    </div>
                    
                    {{-- Boards Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="board-container">
                        @forelse ($boardsCollaborated as $board)
                            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-1 duration-300 ease-in-out border border-gray-200 flex flex-col" data-board-id="{{ $board->id }}">
                                <div class="bg-gradient-to-r from-indigo-700 to-purple-800 h-2 rounded-t-lg"></div>
                                <div class="p-6 flex-grow flex flex-col">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-gray-900 truncate">{{ $board->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $board->user->id == $userId ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $board->user->id == $userId ? 'You' : $board->user->name }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-4 flex-grow">{{ $board->description ?? 'No description available.' }}</p>
                                    
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                            {{ $board->tasks_count }} Tasks
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Created {{ $board->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                    
                                    {{-- Task Status --}}
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @if($board->taskCounts['overdue'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['overdue'] }} Overdue
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueToday'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['dueToday'] }} Due Today
                                            </span>
                                        @endif
                                        @if($board->taskCounts['dueSoon'] > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                {{ $board->taskCounts['dueSoon'] }} Due Soon
                                            </span>
                                        @endif
                                        @if($board->taskCounts['overdue'] == 0 && $board->taskCounts['dueToday'] == 0 && $board->taskCounts['dueSoon'] == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                All tasks on track
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- Collaborators --}}
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Collaborators:</p>
                                        <div class="flex items-center -space-x-2 overflow-hidden">
                                            @foreach ($board->sortedCollaborators as $index => $boardUser)
                                                @if (!isset($boardUser['remaining_count']))
                                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                        {{ $boardUser->role === 'owner' ? 'bg-indigo-500 text-white' : ($boardUser->user_id === $userId ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600') }}
                                                        border-2 border-white text-sm font-semibold"
                                                        title="{{ $boardUser->user->name }} ({{ ucfirst($boardUser->role) }})">
                                                        {{ strtoupper(substr($boardUser->user->name, 0, 1)) }}
                                                    </div>
                                                @else
                                                    @if ($boardUser['remaining_count'] > 0)
                                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 border-2 border-white text-sm font-semibold text-gray-600">
                                                            +{{ $boardUser['remaining_count'] }}
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between items-center">
                                    <a href="{{ route('boards.show', $board->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm transition duration-150 ease-in-out">View Board</a>
                                    <span class="text-gray-500 text-sm">Updated {{ $board->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full">
                                <div class="bg-gray-100 p-6 rounded-lg text-center">
                                    <h3 class="text-lg font-semibold text-gray-700">No Boards Collaborated</h3>
                                    <p class="text-gray-500">You have not collaborated on any boards yet.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    
                    {{-- Pagination for Collaborated Boards --}}
                    @if ($boardsCollaborated->hasPages())
                        <div class="mt-8">
                            {{ $boardsCollaborated->appends(['page' => $boardsOwned->currentPage()])->links() }}
                        </div>
                    @endif

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