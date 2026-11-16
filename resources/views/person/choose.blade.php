@extends('layouts.app')

@section('title', 'Who are you?')
@section('no-nav', '1')

@section('content')
<h1>Who are you?</h1>

<div class="card">
    <p style="color:#555; margin-bottom:1rem;">
        Pick your name. This device will remember it until you change it (top-right of every page).
    </p>
    @include('partials.errors')
    <form method="POST" action="{{ route('person.store') }}" class="actions" style="margin-top:0;">
        @csrf
        @foreach ($people as $person)
        <button type="submit" name="user_id" value="{{ $person->id }}" class="btn btn-primary"
                style="font-size:1.2rem; min-width:140px;">
            @if ($person->colour)
            <span class="colour-dot" style="background:{{ $person->colour }}"></span>
            @endif
            {{ $person->name }}
        </button>
        @endforeach
    </form>
</div>
@endsection
