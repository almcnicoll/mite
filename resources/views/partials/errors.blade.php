@if ($errors->any())
<div class="alert alert-danger">
    <strong>Please fix the following:</strong>
    <ul style="margin-top:0.5rem; padding-left:1.2rem;">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif