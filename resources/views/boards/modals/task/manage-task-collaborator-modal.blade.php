<div id="collaboratorModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-center justify-center h-screen px-4 text-center">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white p-6 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">

                        <h3 class="text-2xl leading-6 font-bold text-gray-900 flex items-center justify-between mb-4" id="modal-title">
                            <span class="flex items-center">
                                <svg class="h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Manage Collaborators
                            </span>
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
                                                <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 -960 960 960" width="22px" fill="currentColor"><path d="M640-520v-80h240v80H640Zm-280 40q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm80-80h480v-32q0-11-5.5-20T580-306q-54-27-109-40.5T360-360q-56 0-111 13.5T140-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T440-640q0-33-23.5-56.5T360-720q-33 0-56.5 23.5T280-640q0 33 23.5 56.5T360-560Zm0-80Zm0 400Z"/></svg>
                                                </button>
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
                                                    <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 -960 960 960" width="22px" fill="currentColor"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
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
                                <form action="{{ route('boards.inviteUser', $board->id) }}" method="POST" class=" bg-gray-50 rounded-lg p-4">
                                    @csrf
                                    <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                                    <div>
                                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Select User to Invite</label>
                                        <select name="user_id" id="user_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="" disabled selected>Choose a user to invite</option>
                                            @foreach($nonCollaborators as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="pt-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                            Send Invitation
                                        </button>
                                        <button type="button" id="closeCollaboratorModalBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                            Cancel
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
        </div>
    </div>
</div>