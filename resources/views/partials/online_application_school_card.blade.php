<article class="sx-apply-school-card h-100">
    <div class="sx-apply-school-card__identity">
        @if($school['schoolLogo'])
            <img src="{{ asset($school['schoolLogo']) }}" alt="{{ $school['schoolName'] }}" class="sx-apply-school-card__logo">
        @else
            <div class="sx-apply-school-card__logo sx-apply-school-card__logo--placeholder">
                <i class="fa fa-university"></i>
            </div>
        @endif
        <div class="sx-apply-school-card__meta">
            <h3>{{ $school['schoolName'] }}</h3>
            <p class="sx-apply-school-card__place">{{ $school['schoolType'] }} · {{ $school['region'] }}@if(!empty($school['district'])), {{ $school['district'] }}@endif</p>
            <div class="sx-apply-school-card__chips" aria-label="School stats">
                <span><strong>{{ $school['classesCount'] }}</strong> classes</span>
                <span><strong>{{ $school['subclassesCount'] }}</strong> subclasses</span>
                <span><strong>{{ $school['activeStudentsCount'] }}</strong> students</span>
            </div>
        </div>
    </div>

    <div class="sx-apply-school-card__classes">
        <h4>Available classes</h4>
        @php
            $classesList = collect($school['classesWithSpaces']);
            $visibleClasses = $classesList->take(2);
            $hiddenClasses = $classesList->slice(2);
        @endphp

        @foreach($visibleClasses as $class)
            @include('partials.online_application_class_row', [
                'class' => $class,
                'requireAuth' => $requireAuth ?? false,
            ])
        @endforeach

        @if($hiddenClasses->count() > 0)
            <div class="sx-apply-more-classes" id="more-classes-{{ $school['schoolID'] }}" style="display:none;">
                @foreach($hiddenClasses as $class)
                    @include('partials.online_application_class_row', [
                        'class' => $class,
                        'requireAuth' => $requireAuth ?? false,
                    ])
                @endforeach
            </div>
            <button type="button" class="sx-apply-view-more-classes" data-target="more-classes-{{ $school['schoolID'] }}">
                View more classes ({{ $hiddenClasses->count() }})
            </button>
        @endif
    </div>

    <div class="sx-apply-school-card__footer">
        <a href="{{ $school['schoolUrl'] }}" class="sx-apply-btn-outline">
            <i class="fa fa-eye"></i> View more
        </a>
        <button type="button" class="sx-apply-btn-outline sx-copy-school-link"
            data-url="{{ $school['schoolShareUrl'] ?? $school['schoolUrl'] }}" title="Copy application link">
            <i class="fa fa-link"></i> Copy link
        </button>
    </div>
</article>
