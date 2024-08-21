<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Task Details
        </h2>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6 pb-4 border-b">Edit Task: {{ $task->name }}</h1>

                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="source" value="edit">

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Task Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $task->name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" value="{{ old('description', $task->description) }}" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <div>
                                <label for="due" class="block text-sm font-medium text-gray-700">Due Date</label>
                                <input type="date" name="due" id="due" value="{{ old('due', $task->due) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                <select name="priority" id="priority" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>

                            <div>
                                <label for="progress" class="block text-sm font-medium text-gray-700">Progress</label>
                                <select name="progress" id="progress" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="to_do" {{ old('progress', $task->progress) == 'to_do' ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ old('progress', $task->progress) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ old('progress', $task->progress) == 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>

                            <div>
                                <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                                <input type="text" name="tag" id="tag" value="{{ old('tag', $task->tag) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <a href="{{ route('tasks.show', $task) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <form action="{{ route('tasks.update', $task)}}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update Task
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>