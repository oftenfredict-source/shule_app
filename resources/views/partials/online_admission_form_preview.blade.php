@if(empty($formSections) || count($formSections) === 0)
    <p class="text-muted mb-0">No active sections / questions configured yet.</p>
@else
    <div class="oa-preview-steps mb-3">
        @foreach($formSections as $i => $section)
            <span class="badge mr-1 mb-1" style="background:#fff5f5;color:#940000;border:1px solid #940000;">
                Step {{ $i + 1 }}: {{ $section->title }}
            </span>
        @endforeach
    </div>
    @foreach($formSections as $i => $section)
        <div class="mb-4 pb-3 border-bottom">
            <h5 class="text-primary-custom mb-1">
                <span class="badge badge-secondary mr-1">{{ $i + 1 }}</span>
                {{ $section->title }}
            </h5>
            @if($section->description)
                <p class="text-muted small mb-3">{{ $section->description }}</p>
            @endif
            <div class="row">
                @foreach($section->fields as $field)
                    @include('partials.online_admission_form_field', ['field' => $field, 'preview' => true])
                @endforeach
            </div>
        </div>
    @endforeach
@endif
