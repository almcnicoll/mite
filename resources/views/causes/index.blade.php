@extends('layouts.app')

@section('title', 'Causes')

@section('head')
<style>
    .cause-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .cause-grid {
            grid-template-columns: 1fr;
        }
    }

    .cause-tile {
        margin-bottom: 0;
        padding: 0.9rem;
        display: flex;
        flex-direction: column;
    }

    .cause-tile .tile-head {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.4rem;
    }

    .cause-tile .tile-head strong {
        font-size: 1rem;
        overflow-wrap: anywhere;
    }

    .cause-tile .tile-notes {
        color: #555;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cause-tile .tile-balance {
        font-size: 0.95rem;
    }

    .cause-tile .actions {
        margin-top: auto;
        padding-top: 0.75rem;
        gap: 0.4rem;
    }

    .cause-tile .btn {
        padding: 0.4rem 0.7rem;
        min-width: 0;
        min-height: 38px;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">Causes</h1>
    <a href="{{ route('causes.create') }}" class="btn btn-success">+ Add cause</a>
</div>

@if ($causes->isNotEmpty())
@include('partials.tab-bar', ['tabs' => ['name' => 'A–Z', 'added' => 'Date added', 'raised' => 'Amount raised']])

<div class="cause-grid" id="cause-grid">
    @foreach ($causes as $cause)
    @php
        $picked  = $cause->totalPicked();
        $donated = $cause->totalDonated();
        $balance = $picked - $donated;
    @endphp
    <div class="card cause-tile"
         style="{{ $cause->colour ? 'border-left: 5px solid '.$cause->colour : '' }}"
         data-name="{{ $cause->name }}"
         data-added="{{ $cause->created_at?->timestamp ?? 0 }}"
         data-id="{{ $cause->id }}"
         data-raised="{{ $picked }}">
        <div class="tile-head">
            @if ($cause->picture)
            <img src="{{ asset('storage/'.$cause->picture) }}" class="avatar">
            @elseif ($cause->colour)
            <span class="colour-dot" style="background:{{ $cause->colour }}; width:32px; height:32px;"></span>
            @endif
            <strong>{{ $cause->name }}</strong>
        </div>

        @if ($cause->notes)
        <p class="tile-notes">{{ $cause->notes }}</p>
        @endif

        <p class="tile-balance">
            Balance owed:
            <span class="{{ $balance > 0 ? 'balance-positive' : 'balance-zero' }}">
                £{{ number_format($balance, 2) }}
            </span>
            <br>
            <small style="color:#888;">
                £{{ number_format($picked, 2) }} picked,
                £{{ number_format($donated, 2) }} donated
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
</div>

<script>
// Sort the tiles in place; the choice is remembered per browser by miteTabs (layout).
const byName = (a, b) => a.dataset.name.localeCompare(b.dataset.name, undefined, { sensitivity: 'base' });
const sorters = {
    name:   byName,
    added:  (a, b) => (b.dataset.added - a.dataset.added) || (b.dataset.id - a.dataset.id),
    raised: (a, b) => (b.dataset.raised - a.dataset.raised) || byName(a, b),
};

miteTabs('causes', 'name', name => {
    const grid = document.getElementById('cause-grid');
    Array.from(grid.children).sort(sorters[name]).forEach(tile => grid.appendChild(tile));
});
</script>
@else
<div class="card" style="color:#888;">No causes yet. Add one!</div>
@endif
@endsection
