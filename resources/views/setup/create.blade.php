@extends('layouts.app')

@section('title', 'New Rate')

@section('content')
<h1>Set a new daily rate</h1>

<div class="card">
    <form method="POST" action="{{ route('setup.store') }}">
        @csrf
        @include('partials.errors')

        <div class="form-group">
            <label for="date_from">From date *</label>
            <input type="date" name="date_from" id="date_from" value="{{ old('date_from', today()->toDateString()) }}"
                required>
        </div>

        <div class="form-group">
            <label for="amount_per_day">Amount per day (£) *</label>
            <input type="number" name="amount_per_day" id="amount_per_day" step="0.01" min="0.01"
                value="{{ old('amount_per_day') }}" required>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('setup.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection