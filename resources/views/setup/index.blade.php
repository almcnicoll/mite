@extends('layouts.app')

@section('title', 'Setup')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">Setup</h1>
    <a href="{{ route('setup.create') }}" class="btn btn-success">+ New rate</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('setup.threshold') }}">
        @csrf
        @include('partials.errors')
        <div class="form-group">
            <label for="donation_threshold">Ready-for-donation threshold (£)</label>
            <input type="number" name="donation_threshold" id="donation_threshold" step="0.01" min="0"
                value="{{ old('donation_threshold', $threshold) }}" required>
            <p style="color:#555; margin-top:0.25rem;">
                Stats flags any cause whose next donation is above this amount.
            </p>
        </div>
        <button type="submit" class="btn btn-success">Save threshold</button>
    </form>
</div>

<div class="card">
    <p style="color:#555; margin-bottom:1rem;">
        Each row sets the daily donation amount from a given date onwards.
        The most recent date on or before today is the active rate.
    </p>
    <table>
        <thead>
            <tr>
                <th>From date</th>
                <th>Amount per day</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $today = today(); @endphp
            @forelse ($setups as $setup)
            <tr>
                <td>{{ $setup->date_from->format('j M Y') }}</td>
                <td>£{{ number_format($setup->amount_per_day, 2) }}</td>
                <td>
                    @if ($setup->date_from->lte($today) &&
                    ($loop->first || !$setups->slice(0, $loop->index)
                    ->first(fn($s) => $s->date_from->lte($today))))
                    <strong style="color:#27ae60;">Active</strong>
                    @elseif ($setup->date_from->gt($today))
                    <span style="color:#888;">Future</span>
                    @else
                    <span style="color:#888;">Past</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="color:#888;">No setup records yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection