@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;">Users</h1>
    <a href="{{ route('users.create') }}" class="btn btn-success">+ Add user</a>
</div>

@foreach ($users as $user)
<div class="card" style="{{ $user->colour ? 'border-left: 5px solid '.$user->colour : '' }}">
    <div style="display:flex; align-items:center; gap:0.75rem;">
        @if ($user->picture)
        <img src="{{ asset('storage/'.$user->picture) }}" class="avatar">
        @elseif ($user->colour)
        <span class="colour-dot" style="background:{{ $user->colour }}; width:36px; height:36px;"></span>
        @endif
        <div>
            <strong>{{ $user->name }}</strong>
            <small style="color:#888; display:block;">Order: {{ $user->rotation_order }}</small>
        </div>
        <div class="actions" style="margin-left:auto; margin-top:0;">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">Edit</a>
            <form method="POST" action="{{ route('users.destroy', $user) }}"
                onsubmit="return confirm('Remove this user?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection