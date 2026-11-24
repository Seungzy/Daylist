@extends('layouts.membership')
@section('title','Status')
@section('subtitle','Check email connectivity, mailer settings, and connected services.')
@section('actions')<a class="btn secondary" href="{{ route('status.index') }}">Refresh Status</a>@endsection
@section('content')
<section class="panel">
    <div class="panel-title"><div><p class="eyebrow">EMAIL CONNECTION</p><h2>Mailer & SMTP</h2></div>
        <span class="badge {{ ($smtpCheck['status'] ?? '')==='working' ? 'badge-approved' : (($smtpCheck['status'] ?? '')==='failed' ? 'badge-cancelled':'badge-pending') }}">{{ match($smtpCheck['status'] ?? null) { 'working'=>'SMTP connected', 'failed'=>'Connection failed', 'not_applicable'=>'SMTP not used', default=>'Not checked yet' } }}</span>
    </div>
    <dl class="details">
        <div><dt>Current Mailer</dt><dd>{{ strtoupper($mailer) }}</dd></div>
        <div><dt>What This Mailer Does</dt><dd>{{ match($settings['transport'] ?? $mailer) { 'smtp'=>'Sends messages through an SMTP server.', 'log'=>'Writes emails to the local log; does not deliver to an inbox.', 'array'=>'Stores messages in memory for tests; does not deliver to an inbox.', default=>'Uses the configured email provider.' } }}</dd></div>
        @if(($settings['transport'] ?? null)==='smtp')
        <div><dt>SMTP Host</dt><dd>{{ $settings['host'] ?? 'Not configured' }}</dd></div>
        <div><dt>SMTP Port</dt><dd>{{ $settings['port'] ?? 'Not configured' }}</dd></div>
        <div><dt>Connection Mode</dt><dd>{{ ($settings['scheme'] ?? '')==='smtps' ? 'Implicit TLS' : 'SMTP with automatic STARTTLS when supported' }}</dd></div>
        <div><dt>Authentication Settings</dt><dd>{{ !empty($settings['username']) && !empty($settings['password']) ? 'Username and password configured' : 'No complete username/password pair configured' }}</dd></div>
        @endif
        <div><dt>Sender Name</dt><dd>{{ config('mail.from.name') ?: 'Not configured' }}</dd></div>
        <div><dt>Sender Address</dt><dd>{{ config('mail.from.address') ? preg_replace('/^(.).*(@.*)$/u','$1•••$2',config('mail.from.address')) : 'Not configured' }}</dd></div>
        <div><dt>Last SMTP Check</dt><dd>{{ $smtpCheck['checked_at'] ?? 'No check for these settings in this session' }}</dd></div>
        <div><dt>Check Result</dt><dd role="status">{{ $smtpCheck['message'] ?? 'Use Check SMTP connection to verify the current settings.' }}</dd></div>
    </dl>
    <div class="form-actions"><form method="POST" action="{{ route('status.smtp') }}">@csrf<button class="btn" type="submit">Check SMTP Connection</button></form><span class="muted small-copy">Tests connection and configured authentication only. No test email is sent.</span></div>
    <p class="muted small-copy">A successful SMTP check confirms connectivity, not inbox delivery. Results apply to the time shown; refreshing this page does not run another connection check.</p>
</section>
<section class="stats" aria-label="Email queue status">
    <article class="stat"><span>Waiting jobs</span><strong>{{ $waiting }}</strong><small>Waiting for a queue worker</small></article>
    <article class="stat"><span>Reserved jobs</span><strong>{{ $processing }}</strong><small>Claimed by a worker; may be in progress</small></article>
    <article class="stat"><span>Failed jobs</span><strong>{{ $failed }}</strong><small>Recorded queue failures</small></article>
    <article class="stat"><span>Queue connection</span><strong class="status-queue-name">{{ config('queue.default') }}</strong><small>Worker availability is not monitored</small></article>
</section>
<section class="panel"><h2>Application Emails</h2><p class="muted">Last processed application email: <strong>{{ $lastMail?->format('M d, Y H:i:s') ?? 'None yet' }}</strong></p><p class="muted small-copy">This timestamp records completion of the mail transport. With the log mailer it means the email was logged locally.</p></section>
<section class="quick-grid">
    <article class="panel"><p class="eyebrow">GOOGLE MAPS</p><h2>Map Status</h2><p><span class="badge {{ config('services.google_maps.key') ? 'badge-approved':'badge-pending' }}">{{ config('services.google_maps.key') ? 'Key configured':'Needs browser key' }}</span></p><p class="muted">{{ config('services.google_maps.key') ? 'A browser key is configured. Open the application form to verify map rendering and referrer access.' : 'No Google Maps browser key is configured.' }}</p><a class="text-link" href="{{ route('membership.create') }}">Open map & weather →</a></article>
    <article class="panel"><p class="eyebrow">OPENWEATHER</p><h2>Weather Status</h2><p><span class="badge {{ config('services.openweather.key') ? 'badge-approved':'badge-pending' }}">{{ config('services.openweather.key') ? 'Key configured':'Needs server key' }}</span></p><p class="muted">{{ config('services.openweather.key') ? 'A server key is configured. Live weather still depends on key activation and provider availability.' : 'No OpenWeather server key is configured.' }}</p><p class="muted small-copy">Configured keys are not displayed on this page.</p></article>
</section>
@endsection
