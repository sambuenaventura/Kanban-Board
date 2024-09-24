<div id="collaboratorModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Manage Collaborators
                        </h3>
                        <div class="mt-4">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Current Collaborators</h4>
                            @if($collaborators->isNotEmpty())
                                <ul class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                                    @foreach($collaborators as $collaborator)
                                        <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-sm font-medium text-gray-900">{{ $collaborator->name }}</span>
                                            <form action="{{ route('boards.removeUser', [$board->id, $collaborator->id]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200">Remove</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">No collaborators yet.</p>
                            @endif
                            
                            <h4 class="text-md font-semibold text-gray-700 mt-6 mb-3">Pending Invitations</h4>
                            @if($pendingInvitations->isNotEmpty())
                                <ul class="bg-gray-50 rounded-lg divide-y divide-gray-200 mb-6">
                                    @foreach($pendingInvitations as $invitation)
                                        <li class="px-4 py-3 flex justify-between items-center hover:bg-gray-100 transition-colors duration-200">
                                            <span class="text-sm font-medium text-gray-900">{{ $invitation->invitedUser->name }}</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-500">Pending</span>
                                                <form action="{{ route('boards.cancelInvitation', ['board' => $invitation->board_id, 'invitation' => $invitation->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4 mb-6">No pending invitations.</p>
                            @endif
                        
                        
                            

                            <h4 class="text-md font-semibold text-gray-700 mt-6 mb-3">Invite New Collaborator</h4>
                            @if($nonCollaborators->isNotEmpty())
                                <form action="{{ route('boards.inviteUser', $board->id) }}" method="POST" class="space-y-4 bg-gray-50 rounded-lg p-4">
                                    @csrf
                                    <div>
                                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Select User to Invite</label>
                                        <select name="user_id" id="user_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="" disabled selected>Choose a user to invite</option>
                                            @foreach($nonCollaborators as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-colors duration-200">
                                            Send Invitation
                                        </button>
                                    </div>
                                </form>
                            @else
                                <p class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">No users available to invite as collaborators.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="closeCollaboratorModalBtn" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>