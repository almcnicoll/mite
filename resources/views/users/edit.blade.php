@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<h1>Edit — {{ $user->name }}</h1>

<div class="card">
    <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('partials.errors')

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="password">New password <small style="font-weight:normal;">(leave blank to keep
                    current)</small></label>
            <input type="password" name="password" id="password">
        </div>

        <div class="form-group">
            <label for="rotation_order">Rotation order *</label>
            <input type="number" name="rotation_order" id="rotation_order"
                value="{{ old('rotation_order', $user->rotation_order) }}" min="1" required>
        </div>

        <div class="form-group">
            <label for="colour">Colour</label>
            <input type="color" name="colour" id="colour" value="{{ old('colour', $user->colour ?? '#3498db') }}"
                style="width:60px; height:48px; padding:0.2rem; cursor:pointer;">
        </div>

        <div class="form-group">
            <label for="picture">Picture</label>
            @if ($user->picture)
            <div style="margin-bottom:0.5rem;">
                <img src="{{ asset('storage/'.$user->picture) }}" class="avatar">
                <small>Current picture</small>
            </div>
            @endif
            <input type="file" name="picture" id="picture" accept="image/*">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save changes</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection