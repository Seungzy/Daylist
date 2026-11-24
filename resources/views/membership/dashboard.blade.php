@extends('layouts.membership')
@section('title','Dashboard')
@section('subtitle')Welcome back, {{ auth()->user()->name }}. Here’s your community at a glance.@endsection
@section('actions')<span class="dash-date">{{ $through->format('M d, Y') }}</span><a class="btn" href="{{ route('members.create') }}">+ Register Member</a>@endsection
@push('head')<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">@endpush
@section('content')
<div class="admin-dashboard">
    <section class="dash-stats" aria-label="Membership overview">
        <article class="dash-stat">
            <div class="dash-stat-top"><span>Total Members</span><span class="dash-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span></div>
            <strong>{{ number_format($total) }}</strong><p>Your registered community</p><a href="{{ route('members.index') }}">View Directory <span aria-hidden="true">↗</span></a>
        </article>
        <article class="dash-stat">
            <div class="dash-stat-top"><span>Active Members</span><span class="dash-stat-icon dash-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></span></div>
            <strong>{{ number_format($active) }}</strong><p>Current approved memberships</p><span class="dash-stat-note">Enabled member accounts</span>
        </article>
        <article class="dash-stat">
            <div class="dash-stat-top"><span>Pending Transactions</span><span class="dash-stat-icon dash-icon-amber" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div>
            <strong>{{ number_format($pending) }}</strong><p>Awaiting full payment</p><a href="{{ route('transactions.index',['status'=>'pending']) }}">Review Transactions <span aria-hidden="true">↗</span></a>
        </article>
        <article class="dash-stat dash-stat-collection">
            <div class="dash-stat-top"><span>Collected Payments</span><span class="dash-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 8V5a2 2 0 0 0-2-2L4 6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h16V8H4a2 2 0 0 1 0-4"/><path d="M20 12h-5v5h5"/></svg></span></div>
            <strong>₱{{ number_format($collected,2) }}</strong><p>Total recorded collections</p><a href="{{ route('payments.index') }}">View Payments <span aria-hidden="true">↗</span></a>
        </article>
    </section>

    <div class="dash-visuals">
        <section class="dash-panel dash-payment-panel" id="payment-overview" data-live-region aria-labelledby="payment-overview-title">
            <div class="dash-panel-heading">
                <div><p class="dash-kicker">COLLECTIONS OVER TIME</p><h2 id="payment-overview-title">Payment Overview</h2><p class="dash-description">Monthly collections · {{ $periodStart->format('M Y') }} – {{ $through->format('M Y') }}</p></div>
                <nav class="dash-period" aria-label="Chart period">
                    <a href="{{ route('dashboard',['months'=>6]) }}" @class(['is-selected'=>$months===6]) @if($months===6) aria-current="true" @endif>6 months</a>
                    <a href="{{ route('dashboard',['months'=>12]) }}" @class(['is-selected'=>$months===12]) @if($months===12) aria-current="true" @endif>12 months</a>
                </nav>
            </div>
            <div class="dash-chart-summary">
                <div><span>Selected Period</span><strong>₱{{ number_format($periodCents/100,2) }}</strong></div>
                <div><span>Payments Recorded</span><strong>{{ number_format($periodPayments) }}</strong></div>
                <div><span>Average / Month</span><strong>₱{{ number_format($periodCents/100/$months,2) }}</strong></div>
            </div>
            @if($periodPayments)
                <p class="dash-chart-hint">Scroll across the chart to see every month. Exact amounts are available below.</p>
                <div class="dash-chart-scroll" tabindex="0" role="region" aria-label="Monthly payment chart. Scroll horizontally on smaller screens.">
                    <svg class="dash-line-chart" viewBox="0 0 760 280" role="img" aria-labelledby="payment-chart-title payment-chart-description">
                        <title id="payment-chart-title">Monthly payment collections</title>
                        <desc id="payment-chart-description">Recorded payments by payment date. {{ $paymentSeries->map(fn($point) => $point['full_label'].': PHP '.number_format($point['cents']/100,2))->implode('; ') }}. The figures are also available in the table below.</desc>
                        <defs><linearGradient id="payment-area" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0077b6" stop-opacity=".2"/><stop offset="100%" stop-color="#0077b6" stop-opacity=".015"/></linearGradient></defs>
                        @for($row=0;$row<=4;$row++)
                            @php($gridY=30+$row*50.5)
                            <line x1="78" x2="730" y1="{{ $gridY }}" y2="{{ $gridY }}" class="dash-grid-line"/>
                            <text x="66" y="{{ $gridY+5 }}" text-anchor="end" class="dash-axis-label">{{ number_format($chartMaximum*(4-$row)/4,0) }}</text>
                        @endfor
                        <polygon points="78,232 {{ $chartLine }} 730,232" fill="url(#payment-area)"/>
                        <polyline points="{{ $chartLine }}" class="dash-chart-line"/>
                        @foreach($chartPoints as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" class="dash-chart-point" tabindex="0" aria-label="{{ $point['full_label'] }}: PHP {{ number_format($point['cents']/100,2) }}"><title>{{ $point['full_label'] }} · ₱{{ number_format($point['cents']/100,2) }} · {{ $point['count'] }} payments</title></circle>
                            <text x="{{ $point['x'] }}" y="263" text-anchor="middle" class="dash-axis-label">{{ $point['label'] }}</text>
                        @endforeach
                    </svg>
                </div>
            @else
                <div class="dash-chart-empty"><span aria-hidden="true">↗</span><h3>No Payments in This Period</h3><p>Recorded collections will appear here automatically.</p><a class="text-link" href="{{ route('transactions.index',['status'=>'pending']) }}">Find a Transaction to Pay →</a></div>
            @endif
            <div class="dash-chart-footnote"><span><i class="dash-legend-dot" aria-hidden="true"></i> Recorded payments (PHP)</span><span>Through {{ $through->format('M d, Y') }}</span></div>
            <details class="dash-data-table"><summary>View Monthly Figures</summary><div class="table-wrap"><table><caption class="dash-table-caption">Monthly payment totals for the selected {{ $months }} months</caption><thead><tr><th>Month</th><th>Payments</th><th>Collected</th></tr></thead><tbody>@foreach($paymentSeries as $point)<tr><td>{{ $point['full_label'] }}</td><td>{{ $point['count'] }}</td><td>₱{{ number_format($point['cents']/100,2) }}</td></tr>@endforeach</tbody></table></div></details>
        </section>

        <section class="dash-panel dash-level-panel" aria-labelledby="level-distribution-title">
            <div class="dash-panel-heading"><div><p class="dash-kicker">YOUR COMMUNITY</p><h2 id="level-distribution-title">Membership Levels</h2><p class="dash-description">All registered members by level</p></div></div>
            @if($total)
                <div class="dash-donut" style="--donut-segments:{{ $donutGradient }}" role="img" aria-label="Membership level distribution: {{ $levelDistribution->map(fn($level) => $level['name'].' '.$level['count'].' members')->implode(', ') }}"><div><strong>{{ number_format($total) }}</strong><span>Registered Members</span></div></div>
                <ul class="dash-level-legend">@foreach($levelDistribution as $level)<li><a href="{{ route('members.index',['level'=>$level['id']]) }}"><span class="dash-level-name"><i style="--level-color:{{ $level['color'] }}" aria-hidden="true"></i>{{ $level['name'] }}</span><strong>{{ $level['count'] }}</strong><span>{{ number_format($level['percent'],1) }}%</span></a></li>@endforeach</ul>
                <p class="dash-legend-help">Select a level to view its members.</p>
            @else
                <div class="dash-level-empty"><h3>Your Community Starts Here</h3><p>Register a member to see the level breakdown.</p><a class="text-link" href="{{ route('members.create') }}">Register a Member →</a></div>
            @endif
        </section>
    </div>

    <section class="dash-shortcuts" aria-label="Quick actions">
        <a href="{{ route('members.index') }}"><span class="dash-shortcut-symbol" aria-hidden="true">01</span><span><strong>Member Directory</strong><small>Profiles, memberships and history</small></span><span class="dash-shortcut-arrow" aria-hidden="true">↗</span></a>
        <a href="{{ route('applications.index') }}"><span class="dash-shortcut-symbol" aria-hidden="true">02</span><span><strong>Review Applications</strong><small>Follow new membership requests</small></span><span class="dash-shortcut-arrow" aria-hidden="true">↗</span></a>
        <a href="{{ route('transactions.create') }}"><span class="dash-shortcut-symbol" aria-hidden="true">03</span><span><strong>New Transaction</strong><small>Start an application or renewal</small></span><span class="dash-shortcut-arrow" aria-hidden="true">↗</span></a>
    </section>

    <section class="dash-panel dash-latest" aria-labelledby="latest-activity-title">
        <div class="dash-panel-heading"><div><p class="dash-kicker">LATEST ACTIVITY</p><h2 id="latest-activity-title">Recent Transactions</h2><p class="dash-description">The latest applications and membership renewals.</p></div><a class="text-link" href="{{ route('transactions.index') }}">View All →</a></div>
        @include('membership.transactions.table',['transactions'=>$recent])
    </section>
</div>
@endsection
