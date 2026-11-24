@extends('layouts.membership')
@section('title','Member Profile')
@section('subtitle','The full picture, from first application to latest renewal.')
@section('actions')<button class="btn secondary" data-print type="button">Print Record</button><a class="btn" href="{{ route('members.edit',$member) }}">Edit Member</a>@endsection
@section('content')
@if(session('access_credentials'))<section class="alert success" role="status"><strong>Member login — shown once</strong><p>Email: {{ session('access_credentials.email') }}<br>Password: <code>{{ session('access_credentials.password') }}</code></p><a class="text-link" href="{{ route('login') }}">Sign-in Page</a><p>Share these credentials privately. This member can view only their own profile and available tasks.</p></section>@endif
<section class="panel profile digital-membership-card admin-member-info-card" aria-label="Member Information Card">
<div class="member-card-top"><span class="member-card-brand"><span class="member-card-brand-icon" aria-hidden="true">d.</span><span class="member-card-wordmark">daylist.</span></span><span aria-label="Account Status"><x-membership-status :status="$member->is_active ? 'enabled' : 'deactivated'" /></span></div>
<p class="member-card-caption">MEMBER PROFILE</p>
<div class="profile-title"><div><h2>{{ $member->full_name }}</h2><p class="mono muted">{{ $member->membership_number }}</p></div></div><dl class="details"><div><dt>Email</dt><dd>{{ $member->email }}</dd></div><div><dt>Phone</dt><dd>{{ $member->phone ?: 'Not provided' }}</dd></div><div><dt>Category</dt><dd>{{ $member->category->name }}</dd></div><div><dt>Membership Level</dt><dd>{{ $member->level->name }}</dd></div><div class="wide"><dt>Address</dt><dd>{{ $member->address ?: 'Not provided' }}</dd></div><div><dt>Registered</dt><dd>{{ $member->created_at->format('M d, Y') }}</dd></div><div><dt>Current Membership</dt><dd>{{ $member->is_active && $member->transactions()->where('status','approved')->whereDate('valid_from','<=',today())->whereDate('valid_until','>=',today())->exists() ? 'Active' : 'Inactive' }}</dd></div></dl>
<div class="form-actions no-print">@if($member->is_active)<a class="btn" href="{{ route('transactions.create',['member'=>$member->id]) }}">+ Membership Transaction</a><form method="POST" action="{{ route('members.destroy',$member) }}" data-confirm="Deactivate this member? History will be preserved.">@csrf @method('DELETE')<button class="btn danger">Deactivate Member</button></form>@else<form method="POST" action="{{ route('members.reactivate',$member) }}">@csrf @method('PATCH')<button class="btn">Reactivate Member</button></form>@endif</div></section>
<section class="panel no-print"><h2>Member Login Access</h2><p class="muted">{{ $member->user_id ? 'This member has a linked login. Reset the password here if needed.' : 'This older member record does not have a login yet. Create one here.' }}</p><form method="POST" action="{{ route('members.access',$member) }}">@csrf<div class="form-grid"><div><label for="access_password">New Password (Optional)</label><input id="access_password" name="password" type="password" minlength="6" maxlength="128" autocomplete="new-password"></div><div><label for="access_password_confirmation">Confirm Password</label><input id="access_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div></div><p class="muted small-copy">Leave both blank to generate a new password. Deactivated members cannot sign in.</p><button class="btn secondary">{{ $member->user_id ? 'Reset Member Password':'Create Member Login' }}</button></form></section>
@if($member->latitude !== null && $member->longitude !== null)<section class="panel"><h2>Member Location & Weather</h2><x-location-panel :record="$member" :readonly="true" /></section>@endif
<section class="panel"><div class="panel-title"><h2>Transaction & Payment History</h2><span class="muted">{{ $transactions->total() }} transactions</span></div>@include('membership.transactions.table')@include('membership.pagination',['records'=>$transactions])</section>
<section class="panel no-print" aria-labelledby="delete-member-heading">
    <h2 id="delete-member-heading">Permanently Delete Member</h2>
    <p>This cannot be undone. It removes this member's profile, login, personal and assigned tasks, and linked membership application. Other members and community tasks are kept.</p>
    <form method="POST" action="{{ route('members.permanent-destroy', $member) }}" data-confirm="Permanently delete this member and the selected records? This cannot be undone.">
        @csrf
        @method('DELETE')
        <label class="check-label">
            <input type="checkbox" name="remove_history" value="1" @checked(old('remove_history'))>
            <span>Also delete this member's transactions, payments and receipts, and remove their amounts from report totals.</span>
        </label>
        <p class="muted small-copy">{{ $transactions->total() }} transactions on record. If transaction history exists, the checkbox is required to delete this member. Use Deactivate member to preserve history. Previously downloaded or printed receipts and reports are unaffected.</p>
        <label for="confirm_membership_number">Type {{ $member->membership_number }} to confirm *</label>
        <input id="confirm_membership_number" name="confirm_membership_number" type="text" maxlength="40" autocomplete="off" required value="{{ old('confirm_membership_number') }}">
        <div class="form-actions"><button class="btn danger" type="submit">Permanently Delete Member</button></div>
    </form>
</section>
@endsection
