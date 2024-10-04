<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Board Invitations') }}
        </h2>
    </x-slot>

    <div class="flex flex-col pb-6">
        <div class="flex-grow overflow-hidden sm:px-6 lg:px-8">
            <div class="h-full bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="h-full p-4 sm:p-6 text-gray-900 flex flex-col">
                    <div class="flex items-center mb-6 pb-4 border-b">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 sm:mb-0">Pending Invitations</h1>
                    </div>
                    
                    <div id="invitation-container" class="space-y-4">
                        @forelse($pendingInvitations as $invitation)
                            <div id="invitation-{{ $invitation->id }}" class="flex flex-col sm:flex-row items-center justify-between p-6 bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300">
                                <div class="mb-4 sm:mb-0 text-center sm:text-left">
                                    <p class="font-bold text-xl text-gray-800 mb-1">{{ $invitation->board->name }}</p>
                                    <p class="text-sm text-gray-600 mb-1">Invited by: <span class="font-semibold">{{ $invitation->inviter->name }}</span></p>
                                    <p class="text-xs text-gray-500">{{ $invitation->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex space-x-3">
                                    <form action="{{ route('boards.acceptInvitation', $invitation) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="idempotency_key" value="{{ session('idempotency_key') ?? Str::random(32) }}">
                                        <button type="submit" class="px-6 py-2 bg-emerald-500 text-white rounded-full hover:bg-emerald-600 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 shadow-md hover:shadow-lg">
                                            Accept
                                        </button>
                                    </form>
                                    <form action="{{ route('boards.declineInvitation', $invitation) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="idempotency_key" value="{{ session('idempotency_key') ?? Str::random(32) }}">
                                        <button type="submit" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 shadow-md hover:shadow-lg">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div id="no-invitations-message" class="p-8 bg-gray-100 rounded-lg text-center">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <p class="mt-4 text-lg font-medium text-gray-600">You have no pending board invitations.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript files with Vite -->
    @vite([
        'resources/views/boards/scripts/boards.manage-invitations.js',
    ])
    
</x-app-layout>
