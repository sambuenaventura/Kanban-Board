<div id="todoModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-center justify-center h-screen px-4 text-center">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white p-6 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <h3 class="text-2xl leading-6 font-bold text-gray-900 flex items-center justify-between mb-4" id="modal-title">
                            <span class="flex items-center">
                                <svg class="h-6 w-6 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add New Task
                            </span>
                        </h3>
                        <div class="mt-2">
                            <form action="{{ route('boards.tasks.store', $board->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="board_id" value="{{ $board->id }}">
                                <input type="hidden" name="idempotency_key" value="{{ session('idempotency_key') ?? Str::random(32) }}">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="col-span-2">
                                        <label for="name" class="block text-sm font-medium text-gray-700">Task name</label>
                                        <input type="text" name="name" id="name" placeholder="Enter todo name" value="{{ old('name') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <div class="col-span-2">
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea name="description" id="description" rows="3" placeholder="Add description" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                                    </div>

                                    <div>
                                        <label for="due" class="block text-sm font-medium text-gray-700">Due date</label>
                                        <input type="date" name="due" id="due" value="{{ old('due') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>

                                    <div>
                                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                        <select name="priority" id="priority" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="" disabled selected>Select priority</option>
                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="progress" class="block text-sm font-medium text-gray-700">Progress</label>
                                        <select name="progress" id="progress" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="" disabled selected>Select progress</option>
                                            <option value="to_do" {{ old('progress') == 'to_do' ? 'selected' : '' }}>To Do</option>
                                            <option value="in_progress" {{ old('progress') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="done" {{ old('progress') == 'done' ? 'selected' : '' }}>Done</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                                        <input type="text" name="tag" id="tag" value="{{ old('tag') }}" placeholder="Add tag" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="pt-2 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                        Add Task
                                    </button>
                                    <button type="button" id="closeModalBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                        Cancel
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
