@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
<h1>Create Task</h1>
<form action="{{ route('boards.tasks.store', $boardId) }}" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Task Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <input type="hidden" name="board_id" value="{{ $boardId }}">
    <button type="submit" class="btn btn-success">Create Task</button>
</form>
@endsection
