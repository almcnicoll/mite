@extends('layouts.app')

@section('title', 'Email reminders')
@section('no-nav', '1')

@section('content')
<h1>Email reminders</h1>

<div class="card">
    @if ($done)
    <p>Done — {{ $user->name }} won't get any more pick reminders.</p>
    <p style="color:#555; margin-top:0.5rem;">
        To turn them back on, go to <a href="{{ route('users.edit', $user) }}">Users → {{ $user->name }}</a>
        and tick "Email me reminders".
    </p>
    @else
    <p style="margin-bottom:1rem;">Stop sending pick reminder emails to <strong>{{ $user->name }}</strong>?</p>
    {{-- Posts back to the same signed URL --}}
    <form method="POST" action="{{ request()->fullUrl() }}">
        @csrf
        <button type="submit" class="btn btn-danger">Yes, stop reminders</button>
        <a href="{{ route('picks.today') }}" class="btn btn-secondary">Cancel</a>
    </form>
    @endif
</div>
@endsection
