@php
    $phoneValue = old('phone', $phoneDefault ?? '255');
    if ($phoneValue === null || $phoneValue === '') {
        $phoneValue = '255';
    }
@endphp
<label class="required" for="{{ $phoneId ?? 'phone' }}">Phone number</label>
<div class="sx-input-wrap">
    <i class="fa fa-phone sx-field-icon"></i>
    <input
        type="tel"
        id="{{ $phoneId ?? 'phone' }}"
        name="phone"
        class="form-control sx-tz-phone"
        inputmode="numeric"
        autocomplete="{{ $phoneAutocomplete ?? 'tel' }}"
        placeholder="255XXXXXXXXX"
        value="{{ $phoneValue }}"
        maxlength="12"
        pattern="255[67][0-9]{8}"
        title="Phone must start with 255 (e.g. 255712345678)"
        required
    >
</div>
<p class="sx-tz-phone-hint mb-0 mt-1">Must start with <strong>255</strong>: continue with your 9-digit mobile number.</p>
