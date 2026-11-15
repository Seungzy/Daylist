@extends('layouts.membership')
@section('title','My tasks')
@section('subtitle','Your personal to-do list, alongside community activities and assignments.')
@section('content')
<div class="personal-task-workspace">
    <section class="panel personal-task-form" aria-labelledby="add-personal-task">
        <p class="eyebrow">ONE THING AT A TIME</p><h2 id="add-personal-task">Add a personal task</h2>
        <p class="muted">A little space for what you need to get done. Personal tasks are visible only to you.</p>
        <form method="POST" action="{{ route('portal.tasks.store') }}">@csrf
            @include('portal.task-fields',['task'=>new \App\Models\Task])
            <div class="form-actions"><button class="btn" type="submit">+ Add task</button></div>
        </form>
    </section>
    <section aria-label="Your task list">
        <div class="panel-title"><h2>My task list</h2><span class="muted">{{ $tasks->total() }} {{ $tasks->total()===1 ? 'task':'tasks' }}</span></div>
        <div class="portal-task-grid personal-task-list">
        @forelse($tasks as $task)
            <article class="panel managed-task">
                <div class="managed-task-heading"><span class="badge {{ $task->completed ? 'badge-approved':'badge-pending' }}">{{ $task->completed ? 'Completed':'Not completed' }}</span><span class="muted small-copy">{{ $task->is_personal ? 'Personal' : ($task->member_id ? 'Assigned to you':'Community') }}</span></div>
                <h2>{{ $task->task_title }}</h2>
                <p class="preserve muted">{{ $task->description ?: 'No description provided.' }}</p>
                <p class="task-assignment">{{ $task->is_personal ? 'Created by you' : ($task->member_id ? 'Assigned to you by your administrator':'Community task') }} @if($task->due_date) · Due {{ $task->due_date->format('M d, Y') }} @endif</p>
                @if($task->is_personal)
                <div class="actions">
                    <form method="POST" action="{{ route('portal.tasks.completion',$task) }}">@csrf @method('PATCH')<input type="hidden" name="completed" value="{{ $task->completed ? 0:1 }}"><button class="btn secondary small" type="submit">{{ $task->completed ? 'Mark as incomplete':'Mark as complete' }}</button></form>
                    <a class="btn secondary small" href="{{ route('portal.tasks.edit',$task) }}">Edit</a>
                    <form method="POST" action="{{ route('portal.tasks.destroy',$task) }}" data-confirm="Delete this personal task?"><input type="hidden" name="_method" value="DELETE">@csrf<button class="btn danger small" type="submit">Delete</button></form>
                </div>
                @else
                <p class="muted small-copy">Managed by your administrator.</p>
                @endif
            </article>
        @empty
            <section class="panel"><h2>A fresh start.</h2><p class="muted">No tasks yet. Add a personal task to start your list.</p></section>
        @endforelse
        </div>
        @include('membership.pagination',['records'=>$tasks])
    </section>
</div>
@endsection
