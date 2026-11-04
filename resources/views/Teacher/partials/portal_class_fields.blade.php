@php
    $requireSubject = $requireSubject ?? false;
    $subjectClassMap = $subjectClassMap ?? [];
@endphp
<div class="lph-field">
    <label class="lph-label">Class *</label>
    <select class="form-control portal-class" name="classID" required>
        <option value="">Select class</option>
        @forelse($classes as $class)
            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
        @empty
            <option value="" disabled>No teaching classes assigned</option>
        @endforelse
    </select>
</div>
<div class="lph-field">
    <label class="lph-label">Stream</label>
    <select class="form-control portal-subclass" name="subclassID">
        <option value="">Whole class (all streams)</option>
        @foreach($subclasses as $sub)
            <option value="{{ $sub->subclassID }}" data-class="{{ $sub->classID }}">{{ $sub->subclass_name }}</option>
        @endforeach
    </select>
</div>
@if(isset($subjects) && $subjects->count())
<div class="lph-field">
    <label class="lph-label">Subject{{ $requireSubject ? ' *' : '' }}</label>
    <select class="form-control portal-subject" name="subjectID" @if($requireSubject) required @endif>
        <option value="">{{ $requireSubject ? 'Select subject' : 'Optional' }}</option>
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
