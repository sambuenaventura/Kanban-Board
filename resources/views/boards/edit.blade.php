<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p id="datePlaceholder" class="text-gray-500"></p>
                <h1 id="timePlaceholder" class="text-2xl font-bold text-gray-700"></h1>
            </div>

        </div>
    </x-slot>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg overflow-hidden shadow-md">
            <div class="px-6 py-4 bg-gray-100 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Edit Board</h1>
            </div>
            <form action="{{ route('boards.update', $board->id) }}" method="POST" class="px-6 py-4">
                @csrf
                @method('PUT')
            
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Board Name</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ $board->name }}" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                        Update Board
                    </button>
                    <a href="{{ route('boards.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
