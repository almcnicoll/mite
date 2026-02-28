@extends('layouts.app')

@section('title', 'Donations')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">Donations</h1>
    <a href="{{ route('donations.create') }}" class="btn btn-success">+ Record donation</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Date paid</th>
                <th>Cause</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $donation)
            <tr>
                <td>{{ $donation->date_paid->format('j M Y') }}</td>
                <td>
                    <a href="{{ route('causes.show', $donation->cause) }}">
                        {{ $donation->cause->name }}
                    </a>
                </td>
                <td>£{{ number_format($donation->amount, 2) }}</td>
                <td>
                    <form method="POST" action="{{ route('donations.destroy', $donation) }}"
                        onsubmit="return confirm('Remove this donation?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            style="padding:0.4rem 0.7rem; min-height:0;">✕</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="color:#888;">No donations recorded yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $donations->links() }}
</div>
@endsection