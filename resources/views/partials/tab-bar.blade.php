{{--
    Shared tab / sort selector. Usage:
        @include('partials.tab-bar', ['tabs' => ['key' => 'Label', ...]])
    then in the page script: miteTabs('<page-key>', '<default key>', name => { ... });
    miteTabs (layouts/app) restores the user's last choice for that page-key from localStorage.
--}}
<div class="tab-bar" role="tablist">
    @foreach ($tabs as $name => $label)
    <button type="button" class="tab-btn" role="tab" aria-selected="false" id="tab-{{ $name }}"
            data-tab="{{ $name }}">{{ $label }}</button>
    @endforeach
</div>
