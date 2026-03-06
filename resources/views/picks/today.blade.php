@extends('layouts.app')

@section('title', "Today's Pick")

@section('content')

<p class="picker-label">
    @if ($isToday)
        <strong>{{ $targetDate->format('l j F Y') }}</strong>
    @else
        <strong style="color: #e67e22;">
            {{ $targetDate->format('l j F Y') }}
            ({{ $dateLabel }})
        </strong>
    @endif

    @if ($pick->user)
        —
        @if ($pick->user->colour)
            <span class="colour-dot" style="background:{{ $pick->user->colour }}"></span>
        @endif
        <strong>{{ $pick->user->name }}'s turn</strong>
    @else
        — <strong>Guest pick</strong>
        @if ($pick->guest_name) ({{ $pick->guest_name }})@endif
    @endif
</p>

@if (!$isToday)
    <div class="alert" style="background:#fef3cd; color:#856404; margin-bottom:1rem;">
        You are picking for a past date.
    </div>
@endif

@include('partials.errors')

@if ($pick->cause)

    {{-- Already chosen today --}}
    <div class="card">
        <p style="font-size:1.2rem; margin-bottom:1rem;">
            Today's cause: <strong>{{ $pick->cause->name }}</strong>
        </p>
        <div class="actions">
            <a href="{{ route('picks.edit', $pick) }}" class="btn btn-warning">Change choice</a>
        </div>
    </div>

@else

    {{-- Choice UI --}}
    <form method="POST" action="{{ route('picks.update', $pick) }}" id="pick-form">
        @csrf
        @method('PUT')

        {{-- Hidden fields controlled by JS --}}
        <input type="hidden" name="cause_id"   id="hidden-cause-id"   value="">
        <input type="hidden" name="user_id"    id="hidden-user-id"    value="{{ $pick->user_id }}">
        <input type="hidden" name="guest_name" id="hidden-guest-name" value="">

        {{-- Circles + add button --}}
        <div class="circles-wrapper">
            <div class="circles-grid">
                <div class="circles-row top-row">
                    @for ($i = 0; $i < 3; $i++)
                        @include('partials.cause-circle', ['cause' => $featured[$i]])
                    @endfor
                </div>
                <div class="circles-row bottom-row">
                    @for ($i = 3; $i < 5; $i++)
                        @include('partials.cause-circle', ['cause' => $featured[$i]])
                    @endfor
                </div>
            </div>
            <div class="circles-add-col">
                <a href="{{ route('causes.create') }}" class="add-cause-btn" title="Add new cause">+</a>
                <svg id="piggy-bank"
                    style="width:120px; margin-top:1rem; display:block;"
                    role="img"
                    aria-label="Piggy bank">
                    <use href="{{ asset('images/piggy-bank.svg') }}#svgRoot"></use>
                </svg>
            </div>
        </div>

        {{-- Dropdown --}}
        <div class="form-group">
            <label for="cause-select">Or choose from all causes:</label>
            <select id="cause-select">
                <option value="">— select a cause —</option>
                @foreach ($causes as $cause)
                    <option value="{{ $cause->id }}">{{ $cause->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Action buttons --}}
        <div class="action-row">

            <button type="submit" class="big-btn btn-make-choice" id="make-choice-btn" disabled>
                Make Choice
            </button>

            <div id="guest-area">
                <button type="button" class="big-btn btn-guest" id="guest-pick-btn">
                    Guest Pick
                </button>
                <div id="guest-name-area" style="display:none; flex:1; display:none;">
                    <button type="button" class="big-btn btn-back" id="guest-back-btn">&#8592;</button>
                    <input type="text" id="guest-name-input"
                           placeholder="Guest's name" class="guest-name-input">
                </div>
            </div>

        </div>

    </form>

@endif

{{-- Outstanding retrospective picks --}}
@if ($outstanding->count())
<div class="card" style="margin-top:2rem;">
    <h2>Outstanding picks</h2>
    <p style="margin-bottom:0.75rem; color:#888;">These past days have no cause chosen yet.</p>
    <table>
        <thead>
            <tr><th>Date</th><th>Picker</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($outstanding as $p)
            <tr>
                <td>{{ $p->date->format('j M Y') }}</td>
                <td>{{ $p->pickerName() }}</td>
                <td>
                    <a href="{{ route('picks.date', ['date' => $p->date->toDateString()]) }}" class="btn btn-warning">Choose</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection

@section('head')
<style>
    .picker-label {
        font-size: 1.2rem;
        margin-bottom: 1.25rem;
    }

    /* ── Circles ── */
    .circles-wrapper {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin: 1.5rem 0;
    }

    .circles-grid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .circles-row {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .cause-circle {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        cursor: pointer;
        padding: 1rem;
        border: 4px solid transparent;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        user-select: none;
        color: white;
        text-shadow: 0 1px 3px rgba(0,0,0,0.45);
        word-break: break-word;
        line-height: 1.25;
    }

    .cause-circle.empty {
        background: #d0d0d0 !important;
        cursor: default;
        text-shadow: none;
    }

    .cause-circle:not(.empty):hover {
        transform: scale(1.08);
        box-shadow: 0 6px 20px rgba(0,0,0,0.22);
    }

    .cause-circle.selected {
        border-color: white;
        box-shadow: 0 0 0 5px #2980b9, 0 6px 20px rgba(0,0,0,0.22);
        transform: scale(1.08);
    }

    .circle-label {
        font-weight: 700;
        font-size: 0.95rem;
        display: block;
    }

    /* ── Add button ── */
    .circles-add-col {
        display: flex;
        align-items: center;
    }

    .add-cause-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #27ae60;
        color: white;
        font-size: 2.2rem;
        line-height: 1;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.15s, transform 0.15s;
        flex-shrink: 0;
    }

    .add-cause-btn:hover {
        background: #219a52;
        transform: scale(1.1);
    }

    /* ── Action row ── */
    .action-row {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        align-items: stretch;
    }

    .big-btn {
        padding: 1rem 1.5rem;
        font-size: 1.2rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        min-height: 68px;
        flex: 1;
        transition: opacity 0.15s, background 0.15s, transform 0.1s;
    }

    .big-btn:active:not(:disabled) { transform: scale(0.97); }

    .btn-make-choice              { background: #27ae60; color: white; }
    .btn-make-choice:disabled     { background: #95a5a6; cursor: not-allowed; }
    .btn-guest                    { background: #8e44ad; color: white; }
    .btn-back                     { background: #7f8c8d; color: white; flex: 0 0 68px; font-size: 1.6rem; }

    #guest-area {
        display: flex;
        gap: 0.75rem;
        flex: 1;
        align-items: stretch;
    }

    #guest-name-area {
        display: flex;
        gap: 0.75rem;
        flex: 1;
        align-items: stretch;
    }

    .guest-name-input {
        flex: 1;
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 3px solid #8e44ad;
        margin-bottom: 0;
        min-height: 68px;
    }

    /* ── Mobile ── */
    @media (max-width: 640px) {
        .cause-circle {
            width: 90px;
            height: 90px;
            font-size: 0.72rem;
            padding: 0.5rem;
        }

        .circle-label { font-size: 0.72rem; }
        .circles-row  { gap: 0.5rem; }
        .circles-grid { gap: 0.5rem; }

        .add-cause-btn { width: 50px; height: 50px; font-size: 1.6rem; }

        .action-row { flex-direction: column; }

        .big-btn   { font-size: 1rem; min-height: 58px; }
        .btn-back  { flex: 0 0 58px; }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(function () {

    // ── Animation configuration ───────────────────────────────
    // Adjust these to tune the effect without touching logic below

    var ANIM_DURATION_MS   = 600;   // total flight time in milliseconds
    var SHRINK_TO          = 0.08;  // fraction of original size at destination (0.1 = 10%)
    var BOUNCE_EASING      = 'swing'; // 'swing' or 'linear' (add jQuery UI for 'easeInBack' etc.)

    // Fine-tune where on the piggy bank image the circle flies to.
    // 0,0 = exact top-centre of the image as computed from its bounding rect.
    // Positive x moves right, positive y moves down.
    var PIGGY_X_TWEAK      = 0;     // pixels, horizontal fine-tune
    var PIGGY_Y_TWEAK      = 10;    // pixels, vertical fine-tune

    // ── State ─────────────────────────────────────────────────

    var selectedCircleEl = null;  // the actual DOM circle element last clicked

    // ── Helpers ──────────────────────────────────────────────

    function setSelectedCause(causeId, circleEl) {
        $('.cause-circle').removeClass('selected');
        selectedCircleEl = null;

        if (causeId) {
            var $circle = circleEl
                ? $(circleEl)
                : $('.cause-circle[data-id="' + causeId + '"]');
            $circle.addClass('selected');
            selectedCircleEl = $circle.length ? $circle[0] : null;
        }

        $('#hidden-cause-id').val(causeId || '');
        $('#cause-select').val(causeId || '');
        refreshSubmitState();
    }

    function isGuestMode() {
        return $('#guest-name-area').is(':visible');
    }

    function guestName() {
        return $('#guest-name-input').val().trim();
    }

    function refreshSubmitState() {
        var hasCause = $('#hidden-cause-id').val() !== '';
        var guestOk  = !isGuestMode() || guestName().length > 0;
        $('#make-choice-btn').prop('disabled', !(hasCause && guestOk));
    }

    function enterGuestMode() {
        $('#guest-pick-btn').hide();
        $('#guest-name-area').css('display', 'flex');
        $('#hidden-user-id').val('');
        $('#guest-name-input').focus();
        refreshSubmitState();
    }

    function exitGuestMode() {
        $('#guest-name-area').hide();
        $('#guest-pick-btn').show();
        $('#guest-name-input').val('');
        $('#hidden-guest-name').val('');
        $('#hidden-user-id').val('{{ $pick->user_id }}');
        refreshSubmitState();
    }

    // ── Piggy bank target position ────────────────────────────
    // Returns the pixel coordinates (page-relative) of the coin slot:
    // top-centre of the piggy bank image, plus tweaks.

    function piggyTarget() {
        var piggy  = document.getElementById('piggy-bank');
        var rect   = piggy.getBoundingClientRect();
        var scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        var scrollY = window.pageYOffset || document.documentElement.scrollTop;

        return {
            x: rect.left + scrollX + (rect.width / 2) + PIGGY_X_TWEAK,
            y: rect.top  + scrollY                    + PIGGY_Y_TWEAK
        };
    }

    // ── Circle-to-piggy animation, then submit ────────────────

    function animateAndSubmit() {
        if (!selectedCircleEl) {
            // No circle was used — just submit directly
            document.getElementById('pick-form').submit();
            return;
        }

        var $circle = $(selectedCircleEl);
        var rect    = selectedCircleEl.getBoundingClientRect();
        var scrollX = window.pageXOffset || document.documentElement.scrollLeft;
        var scrollY = window.pageYOffset || document.documentElement.scrollTop;

        var startW  = rect.width;
        var startH  = rect.height;
        var startX  = rect.left + scrollX;
        var startY  = rect.top  + scrollY;

        // Clone the circle and place it in body at the same absolute position
        var $clone = $circle.clone()
            .removeClass('selected')
            .css({
                position:  'absolute',
                left:      startX,
                top:       startY,
                width:     startW,
                height:    startH,
                margin:    0,
                zIndex:    9999,
                pointerEvents: 'none',
                transition: 'none'
            })
            .appendTo('body');

        // Target: centre the (shrunk) clone over the piggy slot
        var target    = piggyTarget();
        var endW      = startW * SHRINK_TO;
        var endH      = startH * SHRINK_TO;
        var endX      = target.x - (endW / 2);
        var endY      = target.y - (endH / 2);

        // Hide the original circle during flight
        $circle.css('visibility', 'hidden');

        // Animate position and size simultaneously
        $clone.animate(
            {
                left:   endX,
                top:    endY,
                width:  endW,
                height: endH
            },
            {
                duration: ANIM_DURATION_MS,
                easing:   BOUNCE_EASING,
                complete: function () {
                    $clone.remove();
                    document.getElementById('pick-form').submit();
                }
            }
        );
    }

    // ── Circle clicks ─────────────────────────────────────────

    $(document).on('click', '.cause-circle:not(.empty)', function () {
        setSelectedCause($(this).data('id'), this);
        $('#make-choice-btn').focus();
    });

    // ── Dropdown change ───────────────────────────────────────
    // Selecting via dropdown clears the circle selection (no circle to animate)

    $('#cause-select').on('change', function () {
        setSelectedCause($(this).val(), null);
    });

    // ── Guest flow ────────────────────────────────────────────

    $('#guest-pick-btn').on('click', enterGuestMode);
    $('#guest-back-btn').on('click', exitGuestMode);

    $('#guest-name-input').on('input', function () {
        $('#hidden-guest-name').val(guestName());
        refreshSubmitState();
    });

    // ── Make Choice button ────────────────────────────────────
    // Intercept click to run animation first; actual submit happens at end of animation.

    $('#make-choice-btn').on('click', function (e) {
        e.preventDefault();

        if (isGuestMode()) {
            if (!guestName()) return;
            $('#hidden-guest-name').val(guestName());
        }

        animateAndSubmit();
    });

    // ── Init ──────────────────────────────────────────────────

    refreshSubmitState();

});
</script>
@endsection