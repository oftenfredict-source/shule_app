@foreach($schools as $school)
    @if($school['hasAnySpaces'])
        <div class="col-md-6 col-lg-4 mb-4 sx-apply-school-col" data-school-id="{{ $school['schoolID'] }}">
            @include('partials.online_application_school_card', [
                'school' => $school,
                'requireAuth' => $requireAuth ?? false,
            ])
        </div>
    @endif
@endforeach
