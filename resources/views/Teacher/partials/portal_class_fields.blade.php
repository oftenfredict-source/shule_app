@php
    $requireSubject = $requireSubject ?? false;
    $subjectClassMap = $subjectClassMap ?? [];
@endphp
<div class="lph-field">
    <label class="lph-label">{{ __('teacher.portal.class') }}</label>
    <select class="form-control portal-class" name="classID" required>
        <option value="">{{ __('teacher.portal.select_class') }}</option>
        @forelse($classes as $class)
            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
        @empty
            <option value="" disabled>{{ __('teacher.portal.no_teaching_classes') }}</option>
        @endforelse
    </select>
</div>
<div class="lph-field">
    <label class="lph-label">{{ __('teacher.portal.stream') }}</label>
    <select class="form-control portal-subclass" name="subclassID">
        <option value="">{{ __('teacher.portal.whole_class') }}</option>
        @foreach($subclasses as $sub)
            <option value="{{ $sub->subclassID }}" data-class="{{ $sub->classID }}">{{ $sub->subclass_name }}</option>
        @endforeach
    </select>
</div>
@if(isset($subjects) && $subjects->count())
<div class="lph-field">
    <label class="lph-label">{{ $requireSubject ? __('teacher.portal.subject_required') : __('teacher.portal.subject') }}</label>
    <select class="form-control portal-subject" name="subjectID" @if($requireSubject) required @endif>
        <option value="">{{ $requireSubject ? __('teacher.portal.select_subject') : __('teacher.portal.subject_optional') }}</option>
        @foreach($subjects as $subject)
            @php
                $classIds = $subjectClassMap[(int) $subject->subjectID] ?? [];
            @endphp
            <option value="{{ $subject->subjectID }}" data-classes="{{ implode(',', $classIds) }}">
                {{ $subject->subject_name }}@if($subject->subject_code) ({{ $subject->subject_code }})@endif
            </option>
        @endforeach
    </select>
</div>
@endif
