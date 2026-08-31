@php
    $inputName = $field->is_system ? $field->field_key : 'custom[' . $field->field_key . ']';
    $inputId = 'field_' . $field->field_key;
    $oldKey = $field->is_system ? $field->field_key : 'custom.' . $field->field_key;
    $admission = $admission ?? null;
    $preview = $preview ?? false;
    $editing = $editing ?? false;

    $systemMap = [
        'first_name' => 'student_first_name',
        'middle_name' => 'student_middle_name',
        'last_name' => 'student_last_name',
        'gender' => 'student_gender',
        'student_photo' => 'student_photo',
        'parent_phone' => 'parent_phone',
        'parent_first_name' => 'parent_first_name',
        'parent_middle_name' => 'parent_middle_name',
        'parent_last_name' => 'parent_last_name',
        'parent_gender' => 'parent_gender',
        'parent_photo' => 'parent_photo',
    ];

    $existingFile = null;
    if ($admission) {
        if ($field->is_system && isset($systemMap[$field->field_key])) {
            $existingFile = in_array($field->field_type, ['file'], true)
                ? ($admission->{$systemMap[$field->field_key]} ?? null)
                : null;
            $defaultValue = $admission->{$systemMap[$field->field_key]} ?? null;
        } else {
            $custom = is_array($admission->custom_responses) ? $admission->custom_responses : [];
            $defaultValue = $custom[$field->field_key] ?? null;
            $existingFile = $field->field_type === 'file' ? $defaultValue : null;
        }
    } else {
        $defaultValue = null;
    }

    $value = old($oldKey, $defaultValue);
    if ($field->field_type === 'phone' && ($value === null || $value === '')) {
        $value = '255';
    }
    $reqClass = $field->is_required ? ' sx-apply-label--req' : '';
@endphp

@if($field->field_type === 'section')
    <h3 class="sx-oa-section-title">{{ $field->label }}</h3>
@else
    <div class="form-group {{ in_array($field->field_type, ['textarea']) ? 'col-12' : 'col-md-6' }} mb-3">
        <label for="{{ $inputId }}" class="sx-apply-label{{ $reqClass }}">{{ $field->label }}</label>

        @if($field->field_type === 'textarea')
            <textarea class="form-control @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}" name="{{ $inputName }}" rows="3"
                @if($field->is_required && !$preview) required @endif>{{ $value }}</textarea>
        @elseif($field->field_type === 'gender')
            <select class="form-control @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}" name="{{ $inputName }}"
                @if($field->is_required && !$preview) required @endif>
                <option value="">Select gender</option>
                <option value="Male" {{ $value === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ $value === 'Female' ? 'selected' : '' }}>Female</option>
            </select>
        @elseif($field->field_type === 'select')
            <select class="form-control @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}" name="{{ $inputName }}"
                @if($field->is_required && !$preview) required @endif>
                <option value="">Select...</option>
                @foreach(($field->options ?? []) as $opt)
                    <option value="{{ $opt }}" {{ $value === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        @elseif($field->field_type === 'radio')
            <div class="sx-oa-radio-group">
                @foreach(($field->options ?? []) as $opt)
                    <label class="sx-oa-radio-option d-block mb-2">
                        <input type="radio" name="{{ $inputName }}" value="{{ $opt }}"
                            {{ $value === $opt ? 'checked' : '' }}
                            @if($field->is_required && !$preview) required @endif>
                        <span>{{ $opt }}</span>
                    </label>
                @endforeach
            </div>
        @elseif($field->field_type === 'checkbox')
            <label class="sx-apply-check">
                <input type="checkbox" id="{{ $inputId }}" name="{{ $inputName }}" value="1"
                    {{ $value == '1' ? 'checked' : '' }}>
                <span>{{ $field->label }}</span>
            </label>
        @elseif($field->field_type === 'file')
            <input type="file" class="form-control @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}" name="{{ $inputName }}"
                accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp"
                @if($field->is_required && !$preview && !$editing) required @endif>
            <small class="text-muted">PDF, DOC, DOCX, PNG, JPG, JPEG, WEBP, max 10MB</small>
            @if($editing && $existingFile)
                <small class="d-block text-success mt-1">Current file: {{ $existingFile }} (leave empty to keep)</small>
            @endif
        @elseif($field->field_type === 'phone')
            <input type="tel"
                class="form-control sx-tz-phone @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}"
                name="{{ $inputName }}"
                value="{{ $value }}"
                inputmode="numeric"
                autocomplete="tel"
                placeholder="255XXXXXXXXX"
                maxlength="12"
                pattern="255[67][0-9]{8}"
                title="Phone must start with 255 (e.g. 255712345678)"
                @if($field->is_required && !$preview) required @endif>
            <small class="text-muted d-block mt-1">Must start with <strong>255</strong>: continue with your 9-digit mobile number.</small>
        @else
            <input type="text" class="form-control @error($oldKey) is-invalid @enderror"
                id="{{ $inputId }}" name="{{ $inputName }}" value="{{ $value }}"
                @if($field->is_required && !$preview) required @endif>
        @endif

        @error($oldKey)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
@endif
