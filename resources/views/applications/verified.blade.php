@extends('layouts.public')
@section('title','Email Confirmed')
@section('content')<section class="panel confirmation-card"><span class="badge badge-approved">Email Confirmed</span><h1>Thank You for Confirming.</h1><p>Your email address has been verified. Your application remains subject to administrator review and membership payment/approval.</p><a class="btn" href="{{ route('home') }}">Return to Home →</a></section>@endsection
