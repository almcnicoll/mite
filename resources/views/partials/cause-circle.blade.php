@php
    $palette = ['#e74c3c','#e67e22','#8e44ad','#2980b9','#16a085','#c0392b','#27ae60','#d35400'];
@endphp

@if ($cause)
    @php
        $bg = $cause->colour ?? $palette[$cause->id % count($palette)];
    @endphp
    <div class="cause-circle"
         data-id="{{ $cause->id }}"
         style="background: {{ $bg }};"
         title="{{ $cause->name }}">
        @if ($cause->picture)
            <img src="{{ asset('storage/' . $cause->picture) }}"
                 style="width:55%; border-radius:50%; margin-bottom:6px; display:block; margin-left:auto; margin-right:auto;">
        @endif
        <span class="circle-label">{{ $cause->name }}</span>
    </div>
@else
    <div class="cause-circle empty">
        <span class="circle-label" style="color:#aaa;">—</span>
    </div>
@endif