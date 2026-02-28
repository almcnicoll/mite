@extends('layouts.app')

@section('title', 'Causes')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">Causes</h1>
    <a href="{{ route('causes.create') }}" class="btn btn-success">+ Add cause</a>
</div>

@foreach ($causes as $cause)
<div class="card" style="{{ $cause->colour ? 'border-left: 5px solid '.$cause->colour : '' }}">
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
        @if ($cause->picture)
        <img src="{{ asset('storage/'.$cause->picture) }}" class="avatar">
        @elseif ($cause->colour)
        <span class="colour-dot" style="background:{{ $cause->colour }}; width:32px; height:32px;"></span>
        @endif
        <strong style="font-size:1.1rem;">{{ $cause->name }}</strong>
    </div>

    @if ($cause->notes)
    <p style="color:#555; margin-bottom:0.5rem;">{{ $cause->notes }}</p>
    @endif

    @php
    $balance = $cause->balance();
    @endphp

    <p>
        Balance owed:
        <span class="{{ $balance > 0 ? 'balance-positive' : 'balance-zero' }}">
            £{{ number_format($balance, 2) }}
        </span>
        <small style="color:#888;">
            (£{{ number_format($cause->totalPicked(), 2) }} picked,
            £{{ number_format($cause->totalDonated(), 2) }} donated)
        </small>
    </p>

    <div class="actions">
        <a href="{{ route('causes.show', $cause) }}" class="btn btn-primary">Detail</a>
        <a href="{{ route('causes.edit', $cause) }}" class="btn btn-warning">Edit</a>
        <form method="POST" action="{{ route('causes.destroy', $cause) }}"
            onsubmit="return confirm('Delete this cause?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
@endforeach

@if ($causes->isEmpty())
<div class="card" style="color:#888;">No causes yet. Add one!</div>
@endif
@endsection