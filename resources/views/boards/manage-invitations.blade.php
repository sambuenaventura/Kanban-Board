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
                    
                    <div id="invitation-container">
                        @forelse($pendingInvitations as $invitation)
                            <div id="invitation-{{ $invitation->id }}" class="flex items-center justify-between py-4 px-6 bg-white hover:bg-gray-50 border-b border-gray-200 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-semibold text-sm">{{ substr($invitation->board->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-md font-medium text-gray-900">
                                            <span class="font-semibold text-indigo-600">{{ $invitation->inviter->name }}</span> invited you to join <span class="font-semibold">{{ $invitation->board->name }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $invitation->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('boards.acceptInvitation', $invitation) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                                        <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Accept
                                        </button>
                                    </form>
                                    <form action="{{ route('boards.declineInvitation', $invitation) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                                        <button type="submit" class="inline-flex items-center px-5 py-2 border border-gray-300 text-sm font-medium rounded-full shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Decline
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div id="no-invitations-message" class="col-span-full">
                                <div class="text-center py-12 bg-white rounded-lg shadow-md border border-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                    </svg>
                                    <p class="mt-4 text-lg font-medium text-gray-900">No Pending Invitations</p>
                                    <p class="mt-2 text-sm text-gray-600">Check back later for new invites.</p>
                                </div>
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