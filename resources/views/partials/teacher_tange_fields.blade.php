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
        <i class="bi bi-card-checklist"></i> TANGE — Teachers Particular
    </h6>
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">Check Number (C/N) <span class="text-danger">*</span></label>
    <input type="text" name="employee_number" id="{{ $p }}employee_number" class="form-control" value="{{ $val('employee_number') }}" required placeholder="Check Number / Login ID">
    <small class="text-muted">Used as login username</small>
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">TSC Number (TSC/N)</label>
    <input type="text" name="tsc_number" id="{{ $p }}tsc_number" class="form-control" value="{{ $val('tsc_number') }}" placeholder="TSC/N">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Establishment Rank</label>
    <input type="text" name="establishment_rank" id="{{ $p }}establishment_rank" class="form-control" value="{{ $val('establishment_rank') }}" placeholder="Cheo cha Muundo">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Salary Scale</label>
    <input type="text" name="salary_scale" id="{{ $p }}salary_scale" class="form-control" value="{{ $val('salary_scale') }}" placeholder="e.g. TGS D">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Position of Authority</label>
    <input type="text" name="position" id="{{ $p }}position" class="form-control" value="{{ $val('position') }}" placeholder="Cheo cha Madaraka">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Work Station</label>
    <input type="text" name="work_station" id="{{ $p }}work_station" class="form-control" value="{{ $val('work_station') }}" placeholder="Duty station">
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">Date of Birth</label>
    <input type="date" name="date_of_birth" id="{{ $p }}date_of_birth" class="form-control tange-dob" value="{{ $val('date_of_birth') }}" data-age-target="{{ $p }}age_display">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Age (auto)</label>
    <input type="text" id="{{ $p }}age_display" class="form-control" value="{{ $t && $t->age !== null ? $t->age . ' years' : '' }}" readonly placeholder="Calculated from DOB">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Date of Employment</label>
    <input type="date" name="date_hired" id="{{ $p }}date_hired" class="form-control tange-hired" value="{{ $val('date_hired') }}" data-exp-target="{{ $p }}experience_display">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Work Experience (auto)</label>
    <input type="text" name="experience" id="{{ $p }}experience_display" class="form-control" value="{{ $t && $t->years_of_experience !== null ? $t->years_of_experience . ' years' : ($val('experience')) }}" readonly placeholder="Calculated from employment date">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Date of Confirmation</label>
    <input type="date" name="date_confirmed" id="{{ $p }}date_confirmed" class="form-control" value="{{ $val('date_confirmed') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Date of Current Rank</label>
    <input type="date" name="date_current_rank" id="{{ $p }}date_current_rank" class="form-control" value="{{ $val('date_current_rank') }}">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Date of Retirement</label>
    <input type="date" name="date_of_retirement" id="{{ $p }}date_of_retirement" class="form-control" value="{{ $val('date_of_retirement') }}">
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">Highest Education Level</label>
    <input type="text" name="qualification" id="{{ $p }}qualification" class="form-control" value="{{ $val('qualification') }}" placeholder="e.g. Bachelor Degree">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Subjects Studied</label>
    <input type="text" name="specialization" id="{{ $p }}specialization" class="form-control" value="{{ $val('specialization') }}" placeholder="Subjects studied">
</div>
<div class="col-md-4">
    <label class="form-label fw-bold">Subjects Teaching</label>
    <input type="text" name="subjects_teaching" id="{{ $p }}subjects_teaching" class="form-control" value="{{ $val('subjects_teaching') }}" placeholder="Subjects currently teaching">
</div>
<div class="col-md-12">
    <label class="form-label fw-bold">Place of Residence</label>
    <input type="text" name="address" id="{{ $p }}address" class="form-control" value="{{ $val('address') }}" placeholder="Residential address">
</div>
