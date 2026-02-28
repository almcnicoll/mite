@extends('layouts.app')

@section('title', $cause->name)

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">{{ $cause->name }}</h1>
    <a href="{{ route('causes.edit', $cause) }}" class="btn btn-warning">Edit</a>
</div>

<div class="card">
    @if ($cause->picture)
    <img src="{{ asset('storage/'.$cause->picture) }}"
        style="max-width:200px; border-radius:8px; margin-bottom:0.75rem;">
    @endif

    @if ($cause->email)
    <p><strong>Email:</strong> {{ $cause->email }}</p>
    @endif

    @if ($cause->notes)
    <p style="margin-top:0.5rem;">{{ $cause->notes }}</p>
    @endif
</div>

<div class="card">
    <h2>Balance</h2>
    <table>
        <tr>
            <td>Total picked</td>
            <td><strong>£{{ number_format($totalPicked, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Total donated</td>
            <td><strong>£{{ number_format($totalDonated, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Outstanding balance</td>
            <td>
                <strong class="{{ $balance > 0 ? 'balance-positive' : 'balance-zero' }}">
                    £{{ number_format($balance, 2) }}
                </strong>
            </td>
        </tr>
    </table>

    @if ($balance > 0)
    <div class="actions">
        <a href="{{ route('donations.create', ['cause_id' => $cause->id]) }}" class="btn btn-success">Record
            donation</a>
    </div>
    @endif
</div>

<div class="card">
    <h2>Donation history</h2>
    @forelse ($donations as $donation)
    <div style="display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #eee;">
        <span>{{ $donation->date_paid->format('j M Y') }}</span>
        <strong>£{{ number_format($donation->amount, 2) }}</strong>
        <form method="POST" action="{{ route('donations.destroy', $donation) }}"
            onsubmit="return confirm('Remove this donation?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" style="padding:0.3rem 0.6rem; min-height:0;">✕</button>
        </form>
    </div>
    @empty
    <p style="color:#888;">No donations yet.</p>
    @endforelse
</div>

<div class="card">
    <h2>Picks for this cause</h2>
    @forelse ($picks as $pick)
    <div style="display:flex; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid #eee;">
        <span>{{ $pick->date->format('j M Y') }}</span>
        <span>{{ $pick->pickerName() }}</span>
        <span>£{{ number_format($pick->dailyAmount(), 2) }}</span>
    </div>
    @empty
    <p style="color:#888;">No picks yet.</p>
    @endforelse
</div>

<div class="actions">
    <a href="{{ route('causes.index') }}" class="btn btn-secondary">← Back to causes</a>
</div>
@endsection