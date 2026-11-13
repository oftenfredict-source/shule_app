{{--
  Shared TANGE (Teachers Particular) fields.
  $prefix: '' for add form, 'edit_' for edit modal IDs.
  $teacher: optional model for prefill (profile page).
--}}
@php
    $p = $prefix ?? '';
    $t = $teacher ?? null;
    $val = function ($field, $default = '') use ($t) {
        if (!$t) return $default;
        $v = $t->{$field} ?? null;
        if ($v instanceof \Carbon\Carbon) {
            return $v->format('Y-m-d');
        }
        return old($field, $v ?? $default);
    };
@endphp

<div class="col-12 mt-2">
    <h6 class="text-primary-custom fw-bold border-bottom pb-2 mb-3">
        <i class="bi bi-card-checklist"></i> {{ __('teacher.prof.tange_section') }}
    </h6>
</div>

<div class="col-md-4" id="{{ $p }}employee_number_mode_wrap">
    <label class="form-label fw-bold">{{ __('teacher.prof.emp_no_mode') }} <span class="text-danger">*</span></label>
    @if(($p ?? '') === '')
        <div class="d-flex gap-3 flex-wrap mt-1">
            <label class="mb-0"><input type="radio" name="employee_number_mode" value="auto" checked class="js-emp-no-mode"> {{ __('teacher.prof.auto_unique') }}</label>
            <label class="mb-0"><input type="radio" name="employee_number_mode" value="custom" class="js-emp-no-mode"> {{ __('teacher.prof.custom') }}</label>
        </div>
        <small class="text-muted">{{ __('teacher.prof.emp_no_hint_add') }}</small>
    @else
        <input type="hidden" name="employee_number_mode" value="custom">
        <div class="form-control-plaintext small text-muted">{{ __('teacher.prof.custom_edit') }}</div>
    @endif
</div>
<div class="col-md-4" id="{{ $p }}employee_number_field_wrap">
    <label class="form-label fw-bold">{{ __('teacher.prof.emp_check_no') }} <span class="text-danger emp-no-required-star">*</span></label>
    <input type="text" name="employee_number" id="{{ $p }}employee_number" class="form-control" value="{{ $val('employee_number') }}" @if(($p ?? '') !== '') required @endif placeholder="{{ __('teacher.prof.emp_check_ph') }}" @if(($p ?? '') === '') disabled @endif>
    <small class="text-muted">{{ __('teacher.prof.used_as_username') }}</small>
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.tsc_n') }}</label>
    <input type="text" name="tsc_number" id="{{ $p }}tsc_number" class="form-control" value="{{ $val('tsc_number') }}" placeholder="TSC/N">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.establishment_rank') }}</label>
    <input type="text" name="establishment_rank" id="{{ $p }}establishment_rank" class="form-control" value="{{ $val('establishment_rank') }}" placeholder="{{ __('teacher.prof.ph_cheo_muundo') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.salary_scale') }}</label>
    <input type="text" name="salary_scale" id="{{ $p }}salary_scale" class="form-control" value="{{ $val('salary_scale') }}" placeholder="{{ __('teacher.prof.ph_salary') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.position_authority') }}</label>
    <input type="text" name="position" id="{{ $p }}position" class="form-control" value="{{ $val('position') }}" placeholder="{{ __('teacher.prof.ph_cheo_madaraka') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.work_station') }}</label>
    <input type="text" name="work_station" id="{{ $p }}work_station" class="form-control" value="{{ $val('work_station') }}" placeholder="{{ __('teacher.prof.ph_duty_station') }}">
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.date_of_birth') }}</label>
    <input type="date" name="date_of_birth" id="{{ $p }}date_of_birth" class="form-control tange-dob" value="{{ $val('date_of_birth') }}" data-age-target="{{ $p }}age_display">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.age_auto') }}</label>
    <input type="text" id="{{ $p }}age_display" class="form-control" value="{{ $t && $t->age !== null ? __('teacher.prof.years_n', ['n' => $t->age]) : '' }}" readonly placeholder="{{ __('teacher.prof.age_ph') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.date_employment') }}</label>
    <input type="date" name="date_hired" id="{{ $p }}date_hired" class="form-control tange-hired" value="{{ $val('date_hired') }}" data-exp-target="{{ $p }}experience_display">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.exp_auto') }}</label>
    <input type="text" name="experience" id="{{ $p }}experience_display" class="form-control" value="{{ $t && $t->years_of_experience !== null ? __('teacher.prof.years_n', ['n' => $t->years_of_experience]) : ($val('experience')) }}" readonly placeholder="{{ __('teacher.prof.exp_ph') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.date_confirmation') }}</label>
    <input type="date" name="date_confirmed" id="{{ $p }}date_confirmed" class="form-control" value="{{ $val('date_confirmed') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.date_current_rank') }}</label>
    <input type="date" name="date_current_rank" id="{{ $p }}date_current_rank" class="form-control" value="{{ $val('date_current_rank') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.date_retirement') }}</label>
    <input type="date" name="date_of_retirement" id="{{ $p }}date_of_retirement" class="form-control" value="{{ $val('date_of_retirement') }}">
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.highest_edu_level') }}</label>
    <input type="text" name="qualification" id="{{ $p }}qualification" class="form-control" value="{{ $val('qualification') }}" placeholder="{{ __('teacher.prof.ph_bachelor') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.subjects_studied') }}</label>
    <input type="text" name="specialization" id="{{ $p }}specialization" class="form-control" value="{{ $val('specialization') }}" placeholder="{{ __('teacher.prof.ph_subjects_studied') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">{{ __('teacher.prof.subjects_teaching') }}</label>
    <input type="text" name="subjects_teaching" id="{{ $p }}subjects_teaching" class="form-control" value="{{ $val('subjects_teaching') }}" placeholder="{{ __('teacher.prof.ph_subjects_teaching') }}">
</div>
<div class="col-md-12">
    <label class="form-label fw-bold">{{ __('teacher.prof.place_of_residence') }}</label>
    <input type="text" name="address" id="{{ $p }}address" class="form-control" value="{{ $val('address') }}" placeholder="{{ __('teacher.prof.ph_address') }}">
</div>
