<div id="filterModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-center justify-center h-screen px-4 text-center">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white p-6 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <h3 class="text-2xl leading-6 font-bold text-gray-900 flex items-center justify-between mb-4" id="modal-title">
                            Filter Tasks
                            <button onclick="toggleFilterModal()" class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </h3>
                        <div class="bg-gray-100 rounded-lg shadow-inner p-6">
                            <!-- Tags Section -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-lg font-semibold text-gray-800">Select Tags</span>
                                    <button onclick="clearAllFilters()" class="text-sm text-indigo-600 hover:text-indigo-800 transition-colors duration-200">Clear All</button>
                                </div>
                                <div class="max-h-40 overflow-y-auto space-y-2 pr-2 custom-scrollbar" id="tagContainer">
                                    @foreach($allTags as $tag)
                                    <label class="flex items-center bg-white rounded-full px-4 py-2 text-sm font-medium text-gray-700 cursor-pointer hover:bg-gray-200 transition-colors duration-200 mb-2">
                                        <input type="checkbox" name="tags[]" value="{{ $tag }}" {{ in_array($tag, $selectedTags) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out mr-3">
                                        {{ $tag }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Priority Section -->
                            <div class="mb-6">
                                <span class="text-lg font-semibold text-gray-800 block mb-3">Filter by Priority</span>
                                <div class="flex space-x-4">
                                    @foreach(['low', 'medium', 'high'] as $priority)
                                    <label class="flex items-center">
                                        <input type="radio" name="priority" value="{{ $priority }}" {{ $selectedPriority == $priority ? 'checked' : '' }} class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                        <span class="ml-2 text-sm font-medium text-gray-700 capitalize">{{ $priority }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="applyFilters()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                    Apply Filters (<span id="selectedCount">0</span>)
                </button>
                <button type="button" onclick="toggleFilterModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>