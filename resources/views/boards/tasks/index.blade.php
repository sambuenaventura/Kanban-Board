@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<h1>Tasks for Board {{ $boardId }}</h1>
<a href="{{ route('boards.tasks.create', $boardId) }}">Create New Task</a>
<ul>
    @foreach ($tasks as $task)
        <li>
            <a href="{{ route('tasks.show', $task->id) }}">{{ $task->name }}</a>
            <a href="{{ route('tasks.edit', $task->id) }}">Edit</a>
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </li>
    @endforeach
</ul>
@endsection
