<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Board Invitations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Pending Invitations</h1>
                    
                    <div id="invitation-container" class="space-y-6">
                        @forelse($pendingInvitations as $invitation)
                            <div id="invitation-{{ $invitation->id }}" class="bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                                <div class="p-6">
                                    <div class="flex flex-col sm:flex-row items-center justify-between">
                                        <div class="mb-4 sm:mb-0 text-center sm:text-left">
                                            <p class="font-bold text-2xl text-gray-800 mb-2">{{ $invitation->board->name }}</p>
                                            <p class="text-sm text-gray-600 mb-1">Invited by: <span class="font-semibold">{{ $invitation->inviter->name }}</span></p>
                                            <p class="text-xs text-gray-500">{{ $invitation->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                            <form action="{{ route('boards.acceptInvitation', $invitation) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="idempotency_key" value="{{ session('idempotency_key') ?? Str::random(32) }}">
                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-colors duration-200">
                                                    Accept
                                                </button>
                                            </form>
                                            <form action="{{ route('boards.declineInvitation', $invitation) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="idempotency_key" value="{{ session('idempotency_key') ?? Str::random(32) }}">
                                                <button type="submit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                                                    Decline
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                        <div id="no-invitations-message" class="p-8 bg-gray-100 rounded-lg text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
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