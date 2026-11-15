<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#03045e"><title>@yield('title', 'Membership') · Daylist</title>@include('partials.fonts')<link rel="stylesheet" href="{{ asset('css/membership.css') }}?v={{ filemtime(public_path('css/membership.css')) }}">@stack('head')</head>
<body><a class="skip" href="#main">Skip to content</a>
@php($administrator = auth()->user()->is_admin)
<button class="sidebar-overlay no-print" type="button" data-sidebar-close aria-label="Close navigation" tabindex="-1"></button>
<aside id="workspace-sidebar" class="sidebar no-print" aria-label="Workspace sidebar">
    <a class="brand" href="{{ route($administrator ? 'dashboard' : 'portal.profile') }}"><span class="brand-icon">d.</span><span class="brand-word">daylist<span class="brand-dot">.</span></span></a>
    <div class="sidebar-heading"><p class="nav-caption">{{ $administrator ? 'ADMIN WORKSPACE' : 'MEMBER SPACE' }}</p><button class="sidebar-close" type="button" data-sidebar-close aria-label="Close navigation">×</button></div>
    <nav aria-label="Main navigation">
    @php($navigation = $administrator ? ['dashboard'=>['Dashboard','01'],'members.index'=>['Members','02'],'applications.index'=>['Applications','03'],'tasks.index'=>['Tasks','04'],'categories.index'=>['Categories','05'],'levels.index'=>['Membership levels','06'],'transactions.index'=>['Transactions','07'],'payments.index'=>['Payments','08'],'status.index'=>['Status','09']] : ['portal.profile'=>['My profile','01'],'portal.tasks'=>['My tasks','02']])
    @foreach($navigation as $route=>[$label,$number])
        @php($active = request()->routeIs($route) || request()->routeIs($route.'.*') || (str_contains($route,'.index') && request()->routeIs(str_replace('.index','.*',$route))))
        <a href="{{ route($route) }}" @class(['nav-item','selected'=>$active]) @if($active) aria-current="page" @endif title="{{ $label }}"><span class="nav-number">{{ $number }}</span><span class="nav-label">{{ $label }}</span></a>
    @endforeach
    </nav>
    <div class="sidebar-bottom"><a href="{{ route('home') }}">↗ Home</a><p>{{ $administrator ? 'Manage members, tasks, and applications.' : 'Your profile and tasks, in one place.' }}</p></div>
</aside>
<div class="app"><header class="topbar no-print"><div class="topbar-start"><button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="workspace-sidebar" aria-expanded="false" aria-label="Open navigation">☰</button><span class="breadcrumb">{{ $administrator ? 'Administration' : 'Member portal' }} / @yield('title')</span></div><div class="account"><span class="avatar">{{ substr(auth()->user()->name,0,1) }}</span><span class="account-name">{{ auth()->user()->name }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout">Sign out</button></form></div></header>
<main id="main" class="main"><div class="page-heading"><div><p class="eyebrow">DAYLIST / {{ $administrator ? 'ADMINISTRATION' : 'MEMBER PORTAL' }}</p><h1>@yield('title')</h1><p class="intro">@yield('subtitle')</p></div><div class="actions no-print">@yield('actions')</div></div>
@include('partials.alerts')
@yield('content')
<footer class="footer no-print"><span>Daylist · Membership Management & Transaction Processing</span><span>{{ $administrator ? 'Administrator workspace' : 'Your member workspace' }}</span></footer></main></div><script src="{{ asset('js/membership.js') }}?v={{ filemtime(public_path('js/membership.js')) }}" defer></script>@stack('scripts')</body></html>
