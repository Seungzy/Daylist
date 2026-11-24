<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#03045e">
    <title>@yield('title', 'My Tasks') Â· Daylist</title>
    @include('partials.fonts')@include('partials.scrollbars')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <a class="skip-link" href="#main">Skip to Content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('tasks.index') }}" aria-label="Daylist home"><span class="brand-mark" aria-hidden="true"><i></i><i></i><i></i></span>daylist<span class="brand-dot">.</span></a>
            <nav aria-label="Main navigation">
                <a class="nav-link" href="{{ route('dashboard') }}">Membership</a>
                <a href="{{ route('tasks.index') }}" @class(['nav-link', 'active' => request()->is('tasks*')]) @if(request()->is('tasks*')) aria-current="page" @endif>My Tasks</a>
                <a href="{{ url('/about') }}" @class(['nav-link', 'active' => request()->is('about')]) @if(request()->is('about')) aria-current="page" @endif>About</a>
            </nav>
        </div>
    </header>
    <main id="main" class="page-shell">@yield('content')</main>
    <footer class="site-footer"><span>A little space to get things done.</span><span class="palette-dots" aria-hidden="true"><i></i><i></i><i></i></span></footer>
</body>
</html>
