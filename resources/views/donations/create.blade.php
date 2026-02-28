@extends('layouts.app')

@section('title', 'Record Donation')

@section('content')
<h1>Record a donation</h1>

<div class="card">
    <form method="POST" action="{{ route('donations.store') }}">
        @csrf
        @include('partials.errors')

        <div class="form-group">
            <label for="cause_id">Cause *</label>
            <select name="cause_id" id="cause_id" required>
                <option value="">— select —</option>
                @foreach ($causes as $cause)
                @php $balance = $cause->balance(); @endphp
                <option value="{{ $cause->id }}" {{ old('cause_id', $suggested?->id) == $cause->id ? 'selected' : '' }}>
                    {{ $cause->name }}
                    @if ($balance > 0)
                    (£{{ number_format($balance, 2) }} outstanding)
                    @endif
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="date_paid">Date paid *</label>
            <input type="date" name="date_paid" id="date_paid" value="{{ old('date_paid', today()->toDateString()) }}"
                required>
        </div>

        <div class="form-group">
            <label for="amount">Amount (£) *</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                value="{{ old('amount', $suggested ? number_format($suggested->balance(), 2) : '') }}" required>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save donation</button>
            <a href="{{ route('donations.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection