@extends('layouts.app')

@section('title', "Today's Pick")

@section('content')

<p class="picker-label">
    <strong>{{ today()->format('l j F Y') }}</strong>
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

    // ── Helpers ──────────────────────────────────────────────

    function setSelectedCause(causeId) {
        $('.cause-circle').removeClass('selected');
        if (causeId) {
            $('.cause-circle[data-id="' + causeId + '"]').addClass('selected');
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

    // ── Circle clicks ─────────────────────────────────────────

    $(document).on('click', '.cause-circle:not(.empty)', function () {
        setSelectedCause($(this).data('id'));
        $('#make-choice-btn').focus();
    });

    // ── Dropdown change ───────────────────────────────────────

    $('#cause-select').on('change', function () {
        setSelectedCause($(this).val());
    });

    // ── Guest flow ────────────────────────────────────────────

    $('#guest-pick-btn').on('click', enterGuestMode);
    $('#guest-back-btn').on('click', exitGuestMode);

    $('#guest-name-input').on('input', function () {
        $('#hidden-guest-name').val(guestName());
        refreshSubmitState();
    });

    // ── Form submission ───────────────────────────────────────

    $('#pick-form').on('submit', function () {
        if (isGuestMode()) {
            if (!guestName()) return false;
            $('#hidden-guest-name').val(guestName());
        }
    });

    // ── Init ──────────────────────────────────────────────────

    refreshSubmitState();

});
</script>
@endsection