@extends('layouts.app')

@section('title', 'Pick History')

@section('content')
<h1>Pick History</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Picker</th>
                <th>Cause</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($picks as $pick)
            <tr>
                <td>{{ $pick->date->format('j M Y') }}</td>
                <td>
                    @if ($pick->user?->colour)
                    <span class="colour-dot" style="background:{{ $pick->user->colour }}"></span>
                    @endif
                    {{ $pick->pickerName() }}
                </td>
                <td>{{ $pick->cause?->name ?? '—' }}</td>
                <td>
                    @if ($pick->cause)
                    £{{ number_format($pick->dailyAmount(), 2) }}
                    @else
                    —
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('picks.date', ['date' => $pick->date->toDateString()]) }}"
                        class="btn btn-warning">Edit</a>
                        <form method="POST" action="{{ route('picks.destroy', $pick) }}"
                            onsubmit="return confirm('Delete pick for {{ $pick->date->format('j M Y') }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="color:#888;">No picks yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $picks->links() }}
</div>
@endsection