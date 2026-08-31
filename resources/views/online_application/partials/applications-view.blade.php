@php
    $items = $items ?? collect();
    $tableId = $tableId ?? 'sxOaAppsTable';
    $rootId = $rootId ?? 'sxOaAppsViewRoot';
    $gridId = $gridId ?? 'sxOaAppsGrid';
    $storageKey = $storageKey ?? 'sx_oa_portal_apps';
    $showYear = $showYear ?? true;
    $panelTitle = $panelTitle ?? null;
    $pendingStatus = \App\Models\OnlineAdmission::STATUS_PENDING;
@endphp

<div class="sx-oa-apps-toolbar">
    @if($panelTitle)
        <h4>{{ $panelTitle }}</h4>
    @else
        <div></div>
    @endif
    <div class="sx-oa-apps-view-toggle" role="group" aria-label="Applications layout">
        <button type="button" class="sx-oa-apps-view-toggle__btn" data-sx-oa-view="list" title="List view" aria-pressed="false">
            <i class="fa fa-list"></i>
        </button>
        <button type="button" class="sx-oa-apps-view-toggle__btn" data-sx-oa-view="grid" title="Grid view" aria-pressed="false">
            <i class="fa fa-th-large"></i>
        </button>
    </div>
</div>

<div id="{{ $rootId }}"
     class="sx-oa-apps-view-root"
     data-storage-key="{{ $storageKey }}"
     data-table-id="{{ $tableId }}"
     data-show-year="{{ $showYear ? '1' : '0' }}">
    <div class="table-responsive sx-oa-apps-list-wrap">
        <table class="table table-bordered sx-oa-table mb-0 w-100" id="{{ $tableId }}">
            <thead>
                <tr>
                    <th>Application #</th>
                    <th>Student</th>
                    <th>School</th>
                    <th>Class</th>
                    @if($showYear)
                        <th>Academic Year</th>
                    @endif
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-center" style="width:70px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $app)
                    @php
                        $studentName = trim($app->student_first_name . ' ' . ($app->student_middle_name ? $app->student_middle_name . ' ' : '') . $app->student_last_name);
                        $isPending = $app->status === $pendingStatus;
                    @endphp
                    <tr data-app-row="{{ $app->id }}">
                        <td>{{ $app->application_number }}</td>
                        <td>{{ $studentName }}</td>
                        <td>{{ $app->school->school_name ?? '-' }}</td>
                        <td>{{ $app->class->class_name ?? '-' }}</td>
                        @if($showYear)
                            <td>{{ $app->academicYear->year_name ?? $app->academicYear->year ?? '-' }}</td>
                        @endif
                        <td>
                            <span class="sx-oa-status sx-oa-status--{{ strtolower($app->status) }}">{{ $app->status }}</span>
                        </td>
                        <td data-order="{{ $app->created_at?->timestamp ?? 0 }}">
                            {{ $app->created_at?->format('d M Y H:i') }}
                        </td>
                        <td class="text-center">
                            @if($isPending)
                                <div class="sx-oa-actions">
                                    <button type="button"
                                        class="sx-oa-actions__toggle"
                                        aria-label="Actions"
                                        aria-expanded="false"
                                        aria-haspopup="true">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <div class="sx-oa-actions__menu" role="menu">
                                        <a href="{{ route('online_application.portal.applications.edit', $app->id) }}" role="menuitem">
                                            <i class="fa fa-pencil"></i> Edit
                                        </a>
                                        <button type="button"
                                            class="sx-oa-actions__delete sx-oa-delete-app"
                                            role="menuitem"
                                            data-id="{{ $app->id }}"
                                            data-url="{{ route('online_application.portal.applications.delete', $app->id) }}"
                                            data-ref="{{ $app->application_number }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="{{ $gridId }}" class="sx-oa-apps-grid" aria-live="polite">
        @foreach($items as $app)
            @php
                $studentName = trim($app->student_first_name . ' ' . ($app->student_middle_name ? $app->student_middle_name . ' ' : '') . $app->student_last_name);
                $isPending = $app->status === $pendingStatus;
            @endphp
            <article class="sx-oa-app-card" data-app-card="{{ $app->id }}">
                <div class="sx-oa-app-card__top">
                    <div class="sx-oa-app-card__ref">{{ $app->application_number }}</div>
                    @if($isPending)
                        <div class="sx-oa-actions">
                            <button type="button"
                                class="sx-oa-actions__toggle"
                                aria-label="Actions"
                                aria-expanded="false"
                                aria-haspopup="true">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <div class="sx-oa-actions__menu" role="menu">
                                <a href="{{ route('online_application.portal.applications.edit', $app->id) }}" role="menuitem">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                                <button type="button"
                                    class="sx-oa-actions__delete sx-oa-delete-app"
                                    role="menuitem"
                                    data-id="{{ $app->id }}"
                                    data-url="{{ route('online_application.portal.applications.delete', $app->id) }}"
                                    data-ref="{{ $app->application_number }}">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                <h3 class="sx-oa-app-card__name">{{ $studentName }}</h3>
                <p class="sx-oa-app-card__meta"><i class="fa fa-university"></i> {{ $app->school->school_name ?? '-' }}</p>
                <p class="sx-oa-app-card__meta"><i class="fa fa-graduation-cap"></i> {{ $app->class->class_name ?? '-' }}</p>
                @if($showYear)
                    <p class="sx-oa-app-card__meta"><i class="fa fa-calendar"></i> {{ $app->academicYear->year_name ?? $app->academicYear->year ?? '-' }}</p>
                @endif
                <div class="sx-oa-app-card__footer">
                    <span class="sx-oa-status sx-oa-status--{{ strtolower($app->status) }}">{{ $app->status }}</span>
                    <span class="text-muted small">{{ $app->created_at?->format('d M Y') }}</span>
                </div>
            </article>
        @endforeach
    </div>
</div>
