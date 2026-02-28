@extends('layouts.app')

@section('title', 'Edit Pick')

@section('content')
<h1>Edit Pick — {{ $pick->date->format('l j F Y') }}</h1>

<div class="card">
    <form method="POST" action="{{ route('picks.update', $pick) }}">
        @csrf
        @method('PUT')
        @include('partials.errors')

        <div class="form-group">
            <label for="cause_id">Cause</label>
            <select name="cause_id" id="cause_id">
                <option value="">— none yet —</option>
                @foreach ($causes as $cause)
                <option value="{{ $cause->id }}" {{ old('cause_id', $pick->cause_id) == $cause->id ? 'selected' : '' }}>
                    {{ $cause->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="user_id">User (leave blank for guest)</label>
            <select name="user_id" id="user_id">
                <option value="">— guest —</option>
                @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $pick->user_id) == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="guest_name">Guest name (if guest)</label>
            <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name', $pick->guest_name) }}"
                placeholder="Leave blank if not a guest pick">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('picks.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection