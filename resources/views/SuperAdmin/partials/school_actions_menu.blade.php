@php
    $sid = $school->schoolID;
    $sCount = $studentsBySchool[$sid] ?? 0;
    $tCount = $teachersBySchool[$sid] ?? 0;
    $stCount = $staffBySchool[$sid] ?? 0;
    $pCount = $parentsBySchool[$sid] ?? 0;
    $classes = $classesBySchool[$sid] ?? collect();
    $subclasses = $subclassesBySchool[$sid] ?? collect();
    $isBlocked = (int) ($school->login_status ?? 1) === 0;
    $menuClass = $menuClass ?? '';
@endphp
<div class="dropdown sx-action-menu {{ $menuClass }}">
    <button type="button"
            class="btn sx-dots-btn"
            data-bs-toggle="dropdown"
            data-bs-display="static"
            aria-expanded="false"
            title="{{ __('superadmin.schools.actions') }}">
        <i class="fi fi-rr-menu-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
        <li>
            <button type="button"
                    class="dropdown-item view-more-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#viewMoreModal"
                    data-school='@json($school)'
                    data-students="{{ number_format($sCount) }}"
                    data-teachers="{{ number_format($tCount) }}"
                    data-staff="{{ number_format($stCount) }}"
                    data-parents="{{ number_format($pCount) }}"
                    data-classes-count="{{ $classes->count() }}"
                    data-subclasses-count="{{ $subclasses->count() }}"
                    data-classes-list="{{ json_encode($classes->pluck('class_name')) }}"
                    data-subclasses-list="{{ json_encode($subclasses->pluck('full_name')) }}"
                    data-logo-url="{{ $school->logo_url }}"
                    data-stamp-url="{{ $school->stamp_url ?? '' }}"
                    data-sign-url="{{ $school->sign_url ?? '' }}">
                <i class="fi fi-rr-eye me-2"></i> {{ __('superadmin.schools.menu_view_more') }}
            </button>
        </li>
        <li>
            <button type="button"
                    class="dropdown-item edit-school-btn"
                    data-school='@json($school)'
                    data-logo-url="{{ $school->logo_url }}"
                    data-stamp-url="{{ $school->stamp_url ?? '' }}"
                    data-sign-url="{{ $school->sign_url ?? '' }}">
                <i class="fi fi-rr-pencil me-2"></i> {{ __('superadmin.schools.menu_edit') }}
            </button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            @if($isBlocked)
                <button type="button"
                        class="dropdown-item unblock-school-btn"
                        data-id="{{ $sid }}"
                        data-name="{{ $school->school_name }}">
                    <i class="fi fi-rr-unlock me-2"></i> {{ __('superadmin.schools.menu_unblock') }}
                </button>
            @else
                <button type="button"
                        class="dropdown-item block-school-btn"
                        data-id="{{ $sid }}"
                        data-name="{{ $school->school_name }}">
                    <i class="fi fi-rr-ban me-2"></i> {{ __('superadmin.schools.menu_block') }}
                </button>
            @endif
        </li>
        <li>
            <button type="button"
                    class="dropdown-item text-danger delete-school-btn"
                    data-id="{{ $sid }}"
                    data-name="{{ $school->school_name }}">
                <i class="fi fi-rr-trash me-2"></i> {{ __('superadmin.schools.menu_delete') }}
            </button>
        </li>
    </ul>
</div>
