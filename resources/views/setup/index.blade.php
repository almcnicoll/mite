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
        @error('donation_threshold')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <div class="form-group">
            <label for="donation_threshold">Ready-for-donation threshold (£)</label>
            <input type="number" name="donation_threshold" id="donation_threshold" step="0.01" min="0"
                value="{{ old('donation_threshold', $threshold) }}" required>
            <p style="color:#555; margin-top:0.25rem;">
                Stats flags any cause whose next donation is at or above this amount.
            </p>
        </div>
        <button type="submit" class="btn btn-success">Save threshold</button>
    </form>
</div>

<div class="card">
    <form method="POST" action="{{ route('setup.reminders') }}">
        @csrf
        @error('reminder_threshold')<div class="alert alert-danger">{{ $message }}</div>@enderror
        @error('reminder_emails_enabled')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <div class="form-group">
            <input type="hidden" name="reminder_emails_enabled" value="0">
            <label for="reminder_emails_enabled" style="display:inline;">
                <input type="checkbox" name="reminder_emails_enabled" id="reminder_emails_enabled" value="1"
                    style="width:auto; margin-right:0.4rem;"
                    {{ old('reminder_emails_enabled', $remindersOn) ? 'checked' : '' }}>
                Enable reminder emails
            </label>
            <p style="color:#555; margin-top:0.25rem;">
                Master switch. Off by default; nothing is emailed until this is ticked.
            </p>
        </div>
        <div class="form-group">
            <label for="reminder_threshold">Email reminder threshold (outstanding picks)</label>
            <input type="number" name="reminder_threshold" id="reminder_threshold" step="1" min="0"
                value="{{ old('reminder_threshold', $reminderThreshold) }}" required>
            <p style="color:#555; margin-top:0.25rem;">
                Someone with this many outstanding picks or more gets a reminder email, at most once a week.
                Set to 0 to turn reminders off.
            </p>
        </div>
        <button type="submit" class="btn btn-success">Save reminder settings</button>
    </form>

    {{-- View-only: generated from the real email template, so it always matches what's sent --}}
    <div class="form-group" style="margin-top:1.25rem;">
        <label for="reminder_email_text">The reminder email (read-only)</label>
        <textarea id="reminder_email_text" readonly rows="15"
            style="width:100%; font-family:monospace; font-size:0.9rem; background:#f7f7f7;">{{ $emailPreview }}</textarea>
        <p style="color:#555; margin-top:0.25rem;">
            What each person receives. Items in {curly braces} are filled in per person.
        </p>
    </div>
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