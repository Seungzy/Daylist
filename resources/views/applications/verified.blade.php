@extends('layouts.public')
@section('title','Email confirmed')
@section('content')<section class="panel confirmation-card"><span class="badge badge-approved">Email confirmed</span><h1>Thank you for confirming.</h1><p>Your email address has been verified. Your application remains subject to administrator review and membership payment/approval.</p><a class="btn" href="{{ route('home') }}">Return to Home →</a></section>@endsection
