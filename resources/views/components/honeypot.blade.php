@php
    $prefix = config('defender.honeypot.field_prefix', 'my_full_name_');
    $fieldName = $prefix . \Illuminate\Support\Str::random(12);
    $validFrom = encrypt(now()->timestamp);
@endphp
<div id="{{ $fieldName }}_wrap" style="display: none" aria-hidden="true">
    <input id="{{ $fieldName }}"
           name="{{ $fieldName }}"
           type="text"
           value=""
           autocomplete="nope"
           tabindex="-1">
    <input name="valid_from"
           type="text"
           value="{{ $validFrom }}"
           autocomplete="off"
           tabindex="-1">
</div>