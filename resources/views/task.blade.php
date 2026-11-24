@extends('layouts.app')
@section('title', 'Task Example')
@section('content')
    <div class="page-heading"><div><p class="eyebrow">ROUTE PARAMETER EXAMPLE</p><h1>{{ $title }}<span class="heading-dot">.</span></h1><p class="page-intro">Task ID: {{ $id }}</p></div></div>
    <section class="detail-panel"><h2>A Note on This Page</h2><p class="detail-copy">This tutorial example displays the ID from the URL. Visit your task list to add and view saved tasks.</p><a class="text-link" href="{{ route('tasks.index') }}">Back to My Tasks <span aria-hidden="true">→</span></a></section>
@endsection
