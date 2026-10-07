<div class="lph-field">
    <label class="lph-label">Class *</label>
    <select class="form-control portal-class" name="classID" required>
        <option value="">Select class</option>
        @foreach($classes as $class)
            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
        @endforeach
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
    <label class="lph-label">Subject</label>
    <select class="form-control" name="subjectID">
        <option value="">Optional</option>
        @foreach($subjects as $subject)
            <option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}@if($subject->subject_code) ({{ $subject->subject_code }})@endif</option>
        @endforeach
    </select>
</div>
@endif
