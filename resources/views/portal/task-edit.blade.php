@extends('layouts.membership')
@section('title','Edit personal task')
@section('subtitle','Keep the details of your own to-do list up to date.')
@section('actions')<a class="btn secondary" href="{{ route('portal.tasks') }}">← My tasks</a>@endsection
@section('content')<section class="panel form-panel"><form method="POST" action="{{ route('portal.tasks.update',$task) }}">@csrf @method('PUT')
@include('portal.task-fields')
<div class="form-actions"><button class="btn" type="submit">Save changes</button><a class="text-link" href="{{ route('portal.tasks') }}">Cancel</a></div>
</form></section>@endsection
