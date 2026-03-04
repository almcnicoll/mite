@extends('layouts.app')

@section('title', "Today's Pick")

@section('content')
<h1>Today — {{ today()->format('l j F Y') }}</h1>

<div class="card">
    @if ($pick->user)
    <p style="margin-bottom:0.75rem;">
        @if ($pick->user->colour)
        <span class="colour-dot" style="background:{{ $pick->user->colour }}"></span>
        @endif
        <strong>{{ $pick->user->name }}'s turn</strong> to choose a cause.
    </p>
    @else
    <p style="margin-bottom:0.75rem;">
        <strong>Guest pick</strong>
        @if ($pick->guest_name)— {{ $pick->guest_name }}@endif
    </p>
    @endif

    @if ($pick->cause)
    <p style="margin-bottom:1rem;">
        Chosen cause: <strong>{{ $pick->cause->name }}</strong>
    </p>
    <div class="actions">
        <a href="{{ route('picks.edit', $pick) }}" class="btn btn-warning">Change choice</a>
    </div>
    @else
    <p style="margin-bottom:1rem; color:#888;">No cause chosen yet for today.</p>

    <form method="POST" action="{{ route('picks.update', $pick) }}">
        @csrf
        @method('PUT')
        @include('partials.errors')

        <div class="form-group">
            <label for="cause_id">Choose a cause</label>
            <select name="cause_id" id="cause_id" style="font-size:1.1rem;">
                <option value="">— select —</option>
                @foreach ($causes as $cause)
                <option value="{{ $cause->id }}">{{ $cause->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Guest option --}}
        <div class="form-group">
            <label>
                <input type="checkbox" id="is_guest" name="is_guest" value="1" style="width:auto; margin-bottom:0;"
                    {{ old('is_guest') ? 'checked' : '' }}>
                This is a guest pick
            </label>
        </div>

        <div id="guest_name_row" class="form-group" style="display:none;">
            <label for="guest_name">Guest name</label>
            <input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name') }}"
                placeholder="Guest's name">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save today's pick</button>
            <a href="{{ route('causes.create') }}" class="btn btn-secondary">+ New cause</a>
        </div>
    </form>
    @endif
</div>

{{-- Unresolved retrospective picks --}}
    @if ($outstanding->count())
    <div class="card">
        <h2>Outstanding picks</h2>
        <p style="margin-bottom:0.75rem; color:#888;">These past days haven't had a cause chosen yet.</p>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Picker</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($outstanding as $p)
                <tr>
                    <td>{{ $p->date->format('j M Y') }}</td>
                    <td>{{ $p->pickerName() }}</td>
                    <td>
                        <a href="{{ route('picks.edit', $p) }}" class="btn btn-warning">Choose</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @endsection

    @section('head')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('is_guest');
        const guestRow = document.getElementById('guest_name_row');
        const userIdField = document.querySelector('[name=user_id]');

        function toggle() {
            guestRow.style.display = checkbox.checked ? 'block' : 'none';
        }

        checkbox.addEventListener('change', toggle);
        toggle();
    });
    </script>
    @endsection