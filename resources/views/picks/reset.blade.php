@extends('layouts.app')

@section('title', 'Reset Outstanding Picks')

@section('content')

<h1>Reset outstanding picks</h1>

<div class="card" style="border-left: 5px solid #c0392b;">

    @if ($count === 0)
        <p style="margin-bottom:1rem;">
            There are no outstanding picks to remove. Nothing will be changed.
        </p>
        <div class="actions">
            <a href="{{ route('picks.today') }}" class="btn btn-secondary">← Back</a>
        </div>
    @else
        <p style="margin-bottom:0.75rem;">
            This will permanently delete
            <strong>{{ $count }} outstanding {{ Str::plural('pick', $count) }}</strong>
            — past days where no cause was chosen.
        </p>
        <p style="margin-bottom:1.25rem; color:#888;">
            This cannot be undone. The rotation will resume from the next pick as normal.
        </p>

        <form method="POST" action="{{ route('picks.reset.execute') }}">
            @csrf
            <div class="actions">
                <button type="submit" class="btn btn-danger">
                    Yes, delete {{ $count }} {{ Str::plural('pick', $count) }}
                </button>
                <a href="{{ route('picks.today') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif

</div>

@endsection