<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Board Invitations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($pendingInvitations->isEmpty())
                        <p class="text-gray-600">You have no pending board invitations.</p>
                    @else
                        <h3 class="text-lg font-semibold mb-4">Pending Invitations</h3>
                        <div class="space-y-4">
                            @foreach($pendingInvitations as $invitation)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ $invitation->board->name }}</p>
                                        <p class="text-sm text-gray-600">Invited by: {{ $invitation->inviter->name }}</p> <!-- Updated here -->
                                    </div>
                                    <div class="flex space-x-2">
                                        <form action="{{ route('boards.acceptInvitation', $invitation) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Accept</button>
                                        </form>
                                        <form action="{{ route('boards.declineInvitation', $invitation) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Decline</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
