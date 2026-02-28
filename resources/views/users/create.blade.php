@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<h1>Add a user</h1>

<div class="card">
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
        @csrf
        @include('partials.errors')

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="rotation_order">Rotation order *</label>
            <input type="number" name="rotation_order" id="rotation_order" value="{{ old('rotation_order') }}" min="1"
                required>
        </div>

        <div class="form-group">
            <label for="colour">Colour</label>
            <input type="color" name="colour" id="colour" value="{{ old('colour', '#3498db') }}"
                style="width:60px; height:48px; padding:0.2rem; cursor:pointer;">
        </div>

        <div class="form-group">
            <label for="picture">Picture</label>
            <input type="file" name="picture" id="picture" accept="image/*">
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Save user</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection