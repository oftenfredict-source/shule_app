    const canCreate = {!! !empty($canCreate) ? 'true' : 'false' !!};
    const canUpdate = {!! !empty($canUpdate) ? 'true' : 'false' !!};
    const canDelete = {!! !empty($canDelete) ? 'true' : 'false' !!};
    let notEnrolledSelectedIds = new Set();

    // Ensure $ is bound to jQuery and detect if select2 is available
    if (typeof jQuery !== 'undefined') {
        window.$ = window.jQuery = jQuery;
    }

    (function($) {
        if (typeof $ !== 'function') {
            console.error('jQuery is required on manage_student but was not loaded.');
            return;
        }
        $(document).ready(function() {
        // Safety check for Select2
        function safeInitSelect2(selector, options) {
            const $el = $(selector);
            if ($el.length === 0) return;
            
            if (typeof $.fn.select2 === 'function') {
                $el.select2(options);
            } else {
                console.warn('Select2 not yet loaded for ' + selector + ', retrying in 200ms...');
                setTimeout(function() {
                    safeInitSelect2(selector, options);
                }, 200);
            }
        }

        let currentStatus = 'Active';
        let currentStudentsData = [];
        let studentsTable = null;
        let studentSearchKeepFocus = false;
        let gridLoadingMore = false;
        let studentsPage = 1;
        let studentsLastPage = 1;
        let studentsHasMore = false;
        let studentsTotal = 0;
        const GRID_PAGE_SIZE = 10;
        let studentUserScrolled = false;

        // System SweetAlert theme (whitesmoke + #940000)
        if (typeof Swal !== 'undefined') {
            window.StudentSwal = Swal.mixin({
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                customClass: {
                    popup: 'student-swal-popup',
                    confirmButton: 'student-swal-confirm',
                    cancelButton: 'student-swal-cancel'
                }
            });
        }

        function studentIsMobile() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }

        function studentDefaultView() {
            return studentIsMobile() ? 'grid' : 'list';
        }

        function studentPrefKey() {
            return 'student_view_' + (studentIsMobile() ? 'mobile' : 'desktop');
        }

        function studentUserPrefKey() {
            return 'student_view_user_' + (studentIsMobile() ? 'mobile' : 'desktop');
        }

        function studentGetView() {
            try {
                if (localStorage.getItem(studentUserPrefKey()) === '1') {
                    const saved = localStorage.getItem(studentPrefKey());
                    if (saved === 'list' || saved === 'grid') return saved;
                }
            } catch (e) {}
            return studentDefaultView();
        }

        function studentSetUserViewPref(view) {
            try {
                localStorage.setItem(studentPrefKey(), view);
                localStorage.setItem(studentUserPrefKey(), '1');
            } catch (e) {}
        }

        function setStudentView(view, userChoice) {
            const $wrapper = $('#studentsDataWrapper');
            $wrapper.attr('data-active-view', view);
            $('.student-view-toggle [data-view]').removeClass('active');
            $('.student-view-toggle [data-view="' + view + '"]').addClass('active');
            if (userChoice) studentSetUserViewPref(view);

            if (view === 'list') {
                setTimeout(function () {
                    try {
                        if (studentsTable) studentsTable.columns.adjust();
                    } catch (e) {}
                }, 50);
            }
        }

        function escapeHtmlStudent(text) {
            if (text === null || text === undefined) return '';
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(String(text)));
            return d.innerHTML;
        }

        function studentPhotoUrl(student) {
            let studentPhoto = student.photo;
            const femalePh = {!! json_encode(asset("images/female.png")) !!};
            const malePh = {!! json_encode(asset("images/male.png")) !!};
            if (!studentPhoto || studentPhoto === 'undefined' || studentPhoto === 'null') {
                return student.gender === 'Female' ? femalePh : malePh;
            }
            return studentPhoto;
        }

        function studentPhotoFallback(student) {
            return student && student.gender === 'Female'
                ? {!! json_encode(asset("images/female.png")) !!}
                : {!! json_encode(asset("images/male.png")) !!};
        }

        /** img tag that never leaves a broken src (placeholder if missing / onerror) */
        function studentPhotoImgHtml(student, className, altText) {
            const src = studentPhotoUrl(student);
            const fb = studentPhotoFallback(student);
            const cls = className ? (' class="' + className + '"') : '';
            const alt = escapeHtmlStudent(altText || student.full_name || '');
            // onerror: one-time swap; if src already placeholder this is a no-op
            return '<img src="' + src + '" alt="' + alt + '"' + cls +
                ' onerror="this.onerror=null;this.src=\'' + fb.replace(/'/g, "\\'") + '\';">';
        }

        function studentHasHealthAlert(student) {
            return (student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") ||
                (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") ||
                (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") ||
                (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") ||
                (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1");
        }

        function isNotEnrolledBulkMode() {
            return canDelete && ($('#statusFilter').val() || '') === 'NotEnrolled';
        }

        function updateNotEnrolledBulkUi() {
            const on = isNotEnrolledBulkMode();
            $('#notEnrolledBulkBar').toggleClass('d-none', !on);
            $('#studentsTable').toggleClass('not-enrolled-bulk-mode', on);
            $('.not-enrolled-check-col').toggleClass('d-none', !on);
            if (!on) {
                clearNotEnrolledSelection(false);
            } else {
                syncNotEnrolledCheckboxes();
                updateNotEnrolledSelectionCount();
            }
        }

        function clearNotEnrolledSelection(refreshUi) {
            notEnrolledSelectedIds.clear();
            $('#notEnrolledSelectPageCb').prop('checked', false);
            if (refreshUi !== false) {
                syncNotEnrolledCheckboxes();
                updateNotEnrolledSelectionCount();
            }
        }

        function syncNotEnrolledCheckboxes() {
            $('.not-enrolled-student-check').each(function () {
                const id = parseInt($(this).val(), 10);
                $(this).prop('checked', notEnrolledSelectedIds.has(id));
            });
            syncNotEnrolledPageSelectAllState();
        }

        function syncNotEnrolledPageSelectAllState() {
            const $boxes = $('.not-enrolled-student-check:visible');
            if (!$boxes.length) {
                $('#notEnrolledSelectPageCb').prop('checked', false);
                return;
            }
            $('#notEnrolledSelectPageCb').prop('checked', $boxes.filter(':checked').length === $boxes.length);
        }

        function updateNotEnrolledSelectionCount() {
            const n = notEnrolledSelectedIds.size;
            $('#notEnrolledSelectedCount').text(n + ' selected');
            $('#deleteSelectedNotEnrolledBtn').prop('disabled', n === 0);
        }

        function renderNotEnrolledStudentCheckbox(studentID) {
            if (!isNotEnrolledBulkMode()) {
                return '';
            }
            const id = parseInt(studentID, 10);
            const checked = notEnrolledSelectedIds.has(id) ? ' checked' : '';
            return '<input type="checkbox" class="not-enrolled-student-check form-check-input m-0" value="' + id + '"' + checked + ' aria-label="Select student">';
        }

        function buildStudentActionsHtml(student, status, compact) {
            let items = '';
            const isNotEnrolled = student.is_not_enrolled === true ||
                status === 'Not Enrolled' ||
                status === 'NotEnrolled';

            items += '<button type="button" class="dropdown-item view-student-btn" data-student-id="' + student.studentID + '">' +
                '<i class="bi bi-eye text-info"></i> View Details</button>';

            if (canCreate && isNotEnrolled) {
                items += '<button type="button" class="dropdown-item enroll-student-now-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.full_name || '') + '">' +
                    '<i class="bi bi-person-plus" style="color:#940000;"></i> Enroll Now</button>';
            }

            if (canUpdate && isNotEnrolled) {
                items += '<button type="button" class="dropdown-item edit-student-btn" data-student-id="' + student.studentID + '">' +
                    '<i class="bi bi-pencil-square text-warning"></i> Edit Student</button>';
            }

            if (canUpdate && status === 'Active') {
                items += '<button type="button" class="dropdown-item edit-student-btn" data-student-id="' + student.studentID + '">' +
                    '<i class="bi bi-pencil-square text-warning"></i> Edit Student</button>';
                items += '<button type="button" class="dropdown-item single-move-student-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.full_name || '') + '" data-current-class="' + escapeHtmlStudent(student.class || '') + '">' +
                    '<i class="bi bi-arrow-left-right" style="color:#940000;"></i> Move Student</button>';
                items += '<button type="button" class="dropdown-item edit-enrollment-status-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.full_name || '') + '" data-current-class="' + escapeHtmlStudent(student.class || '') + '">' +
                    '<i class="bi bi-journal-x" style="color:#940000;"></i> Edit Enrollment</button>';
            }

            if (status === 'Active') {
                if (!student.fingerprint_id) {
                    items += '<a href="#" class="dropdown-item send-student-to-fingerprint-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '">' +
                        '<i class="bi bi-fingerprint text-success"></i> Send to Fingerprint</a>';
                } else if (!student.sent_to_device) {
                    items += '<button type="button" class="dropdown-item register-student-to-device-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.first_name || student.full_name || '') + '" data-fingerprint-id="' + (student.fingerprint_id || '') + '">' +
                        '<i class="bi bi-device-hdd text-success"></i> Register to Device</button>';
                }
                items += '<button type="button" class="dropdown-item generate-id-btn" data-student-id="' + student.studentID + '">' +
                    '<i class="bi bi-card-text text-primary"></i> Generate ID Card</button>';
            }

            if (canDelete && isNotEnrolled) {
                items += '<button type="button" class="dropdown-item text-danger delete-not-enrolled-student-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.full_name || '') + '">' +
                    '<i class="bi bi-trash"></i> Delete Student</button>';
            }

            if (canDelete && status === 'Active') {
                items += '<button type="button" class="dropdown-item text-danger delete-student-btn" data-student-id="' + student.studentID + '" data-student-name="' + escapeHtmlStudent(student.full_name) + '">' +
                    '<i class="bi bi-trash"></i> Delete Student</button>';
            }

            const dropdown =
                '<div class="dropdown student-action-dropdown">' +
                    '<button class="btn btn-sm student-action-toggle" type="button" title="Actions" aria-haspopup="true" aria-expanded="false">' +
                        '<i class="bi bi-three-dots-vertical"></i>' +
                    '</button>' +
                    '<div class="dropdown-menu dropdown-menu-right shadow-sm">' + items + '</div>' +
                '</div>';

            if (compact) {
                return dropdown;
            }

            return '<div class="student-widget-actions-wrap">' + dropdown + '</div>';
        }

        function getStudentFilterParams(extra) {
            const params = {
                status: $('#statusFilter').val() || 'Active',
                promotion_status: $('#promotionFilter').val() || '',
                classID: $('#classFilter').val() || '',
                subclassID: $('#subclassFilter').val() || '',
                gender: $('#genderFilter').val() || '',
                health: $('#healthFilter').val() || '',
                paginate: 1,
                per_page: GRID_PAGE_SIZE,
                page: 1,
                search: ($('#studentGridSearch').val() || '').trim()
            };
            return Object.assign(params, extra || {});
        }

        function getListFilterParams(dtData) {
            const length = (dtData && dtData.length) ? dtData.length : 10;
            const start = (dtData && typeof dtData.start === 'number') ? dtData.start : 0;
            const searchVal = (dtData && dtData.search && dtData.search.value)
                ? String(dtData.search.value).trim()
                : '';
            return {
                status: $('#statusFilter').val() || 'Active',
                promotion_status: $('#promotionFilter').val() || '',
                classID: $('#classFilter').val() || '',
                subclassID: $('#subclassFilter').val() || '',
                gender: $('#genderFilter').val() || '',
                health: $('#healthFilter').val() || '',
                paginate: 1,
                per_page: length,
                page: Math.floor(start / Math.max(1, length)) + 1,
                search: searchVal
            };
        }

        function getCurrentStudentSearch() {
            const view = $('#studentsDataWrapper').attr('data-active-view') || 'list';
            if (view === 'grid') {
                return ($('#studentGridSearch').val() || '').trim();
            }
            if (studentsTable && $.fn.DataTable.isDataTable('#studentsTable')) {
                return String(studentsTable.search() || '').trim();
            }
            return ($('#studentGridSearch').val() || '').trim();
        }

        function getExportFilterParams() {
            return getStudentFilterParams({
                paginate: 0,
                page: 1,
                per_page: 5000,
                search: getCurrentStudentSearch()
            });
        }

        function applyPaginationMeta(meta) {
            studentsPage = meta.page || 1;
            studentsLastPage = meta.last_page || 1;
            studentsHasMore = !!meta.has_more;
            studentsTotal = meta.total || 0;
        }

        function destroyStudentsTable() {
            if (studentsTable && $.fn.DataTable.isDataTable('#studentsTable')) {
                studentsTable.destroy();
                studentsTable = null;
            }
            $('#studentsTableBody').empty();
            $('#studentsListPager').remove();
        }

        function initStudentsDataTable() {
            destroyStudentsTable();

            if (!$.fn.DataTable) {
                $('#studentsTableBody').html('<tr><td colspan="12" class="text-center text-danger">DataTables failed to load.</td></tr>');
                return;
            }

            const dtColumns = [];
            if (canDelete) {
                dtColumns.push({
                    data: 'studentID',
                    orderable: false,
                    searchable: false,
                    className: 'not-enrolled-check-cell text-center',
                    render: function (id) {
                        return renderNotEnrolledStudentCheckbox(id);
                    }
                });
            }
            dtColumns.push(
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return studentPhotoImgHtml(row, 'student-list-photo', '');
                        }
                    },
                    {
                        data: 'admission_number',
                        render: function (data) {
                            return escapeHtmlStudent(data || '-');
                        }
                    },
                    {
                        data: 'full_name',
                        render: function (data, type, row) {
                            const healthIcon = studentHasHealthAlert(row)
                                ? ' <i class="bi bi-exclamation-triangle-fill" style="color:#940000;" title="Health Condition Alert"></i>'
                                : '';
                            return escapeHtmlStudent(data || '-') + healthIcon;
                        }
                    },
                    {
                        data: 'class',
                        render: function (data) {
                            return escapeHtmlStudent(data || '-');
                        }
                    },
                    {
                        data: 'enrollment_status',
                        orderable: false,
                        render: function (data, type, row) {
                            var enr = data || row.status || currentStatus || 'Active';
                            return '<span class="enrollment-status-pill ' + enrollmentStatusBadgeClass(enr) + '">' +
                                escapeHtmlStudent(enr) + '</span>';
                        }
                    },
                    {
                        data: 'promotion_status',
                        orderable: false,
                        render: function (data, type, row) {
                            var promo = data || null;
                            if (!promo) {
                                return '<span class="text-muted">-</span>';
                            }
                            return '<span class="enrollment-status-pill ' + promotionStatusBadgeClass(promo) + '" title="' + escapeHtmlStudent(promotionStatusLabel(promo)) + '">' +
                                escapeHtmlStudent(promotionStatusLabel(promo)) + '</span>';
                        }
                    },
                    {
                        data: 'gender',
                        render: function (data) {
                            return escapeHtmlStudent(data || '-');
                        }
                    },
                    {
                        data: 'parent_name',
                        render: function (data) {
                            return escapeHtmlStudent(data || '-');
                        }
                    },
                    {
                        data: 'fingerprint_id',
                        orderable: false,
                        render: function (data) {
                            return data
                                ? '<span class="badge" style="background:#f5f5f5;color:#940000;border:1px solid #940000;"><i class="bi bi-fingerprint"></i> ' + escapeHtmlStudent(data) + '</span>'
                                : '<span class="badge" style="background:#f5f5f5;color:#666;border:1px solid #ccc;">No ID</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'student-actions-cell',
                        render: function (data, type, row) {
                            return buildStudentActionsHtml(row, row.enrollment_status || currentStatus, true);
                        }
                    }
            );

            studentsTable = $('#studentsTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ordering: false,
                paging: true,
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                info: true,
                autoWidth: false,
                deferRender: true,
                dom:
                    "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                ajax: function (data, callback) {
                    $.ajax({
                        url: {!! json_encode(route("get_students_list")) !!},
                        type: 'GET',
                        dataType: 'json',
                        data: getListFilterParams(data),
                        success: function (response) {
                            const students = (response && response.success) ? (response.students || []) : [];
                            const meta = (response && response.pagination) ? response.pagination : {};
                            currentStudentsData = students;
                            currentStatus = $('#statusFilter').val() || 'Active';
                            applyPaginationMeta(meta);
                            updateNotEnrolledBulkUi();
                            callback({
                                draw: data.draw,
                                recordsTotal: meta.total || students.length,
                                recordsFiltered: meta.total || students.length,
                                data: students
                            });
                            loadStatistics();
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to load students';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            currentStudentsData = [];
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: []
                            });
                            if (typeof StudentSwal !== 'undefined') {
                                StudentSwal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                            }
                        }
                    });
                },
                columns: dtColumns,
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ students',
                    infoEmpty: 'Showing 0 students',
                    infoFiltered: '(filtered from _MAX_ total students)',
                    zeroRecords: 'No matching students found',
                    emptyTable: 'No students found matching the selected filters.',
                    processing: '<i class="bi bi-hourglass-split"></i> Loading students...',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Prev'
                    }
                },
                drawCallback: function () {
                    try { this.api().columns.adjust(); } catch (e) {}
                    syncNotEnrolledCheckboxes();
                }
            });

            // Prevent Enter in DT search from submitting any parent form / reloading page
            $('#studentsTable_filter input')
                .attr('autocomplete', 'off')
                .off('keydown.studentDtEnter')
                .on('keydown.studentDtEnter', function (e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                });
        }

        function reloadStudentsDataTable(resetPaging) {
            if (studentsTable && $.fn.DataTable.isDataTable('#studentsTable')) {
                studentsTable.ajax.reload(null, resetPaging !== false);
            } else {
                initStudentsDataTable();
            }
        }

        function buildStudentGridCard(student, status) {
            let fingerprintIdHtml = student.fingerprint_id
                ? '<span class="badge" style="background:#f5f5f5;color:#940000;border:1px solid #940000;"><i class="bi bi-fingerprint"></i> ' + escapeHtmlStudent(student.fingerprint_id) + '</span>'
                : '<span class="badge" style="background:#f5f5f5;color:#666;border:1px solid #ccc;"><i class="bi bi-dash"></i> No ID</span>';
            let healthAlarmIcon = studentHasHealthAlert(student)
                ? ' <i class="bi bi-exclamation-triangle-fill" style="color:#940000;" title="Health Condition Alert"></i>'
                : '';
            var enr = student.enrollment_status || student.status || status || currentStatus || 'Active';
            var enrPill = '<span class="enrollment-status-pill ' + enrollmentStatusBadgeClass(enr) + '">' +
                escapeHtmlStudent(enr) + '</span>';
            var promo = student.promotion_status || null;
            var promoPill = promo
                ? '<span class="enrollment-status-pill ' + promotionStatusBadgeClass(promo) + '" title="' + escapeHtmlStudent(promotionStatusLabel(promo)) + '">' +
                    escapeHtmlStudent(promotionStatusLabel(promo)) + '</span>'
                : '';

            var selectChk = '';
            var widgetExtraClass = '';
            if (isNotEnrolledBulkMode()) {
                widgetExtraClass = ' not-enrolled-selectable';
                selectChk = '<label class="student-widget-select-check">' + renderNotEnrolledStudentCheckbox(student.studentID) + '</label>';
            }

            return '<div class="col-12 col-sm-6 col-xl-4">' +
                '<div class="student-widget' + widgetExtraClass + '">' +
                    '<div class="student-widget-header">' +
                        selectChk +
                        buildStudentActionsHtml(student, enr, false) +
                        studentPhotoImgHtml(student, '', student.full_name) +
                    '</div>' +
                    '<div class="student-widget-body">' +
                        '<h3>' + escapeHtmlStudent(student.full_name || '-') + healthAlarmIcon + '</h3>' +
                        '<p class="form mb-1">' + escapeHtmlStudent(student.class || '-') + '</p>' +
                        '<div class="enrollment-status-row">' + enrPill +
                            (promoPill ? ' ' + promoPill : '') +
                        '</div>' +
                        '<div class="info">' +
                            '<p><strong>Admission:</strong> ' + escapeHtmlStudent(student.admission_number || '-') + '</p>' +
                            '<p><strong>Gender:</strong> ' + escapeHtmlStudent(student.gender || '-') + '</p>' +
                            '<p><strong>Parent:</strong> ' + escapeHtmlStudent(student.parent_name || '-') + '</p>' +
                            '<p><strong>Fingerprint:</strong> ' + fingerprintIdHtml + '</p>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function renderGridFromCurrentData(append) {
            if (!append) {
                $('#studentsWidgetGrid').empty();
                $('#studentGridSentinel').addClass('d-none');
                $('#studentGridEnd').addClass('d-none');
            }

            if (!currentStudentsData.length) {
                $('#studentsWidgetGrid').html('<div class="col-12"><div class="student-grid-empty">No students found matching the selected filters.</div></div>');
                return;
            }

            // When appending, only render the latest page batch
            let toRender = currentStudentsData;
            if (append) {
                const start = Math.max(0, currentStudentsData.length - GRID_PAGE_SIZE);
                toRender = currentStudentsData.slice(start);
            }

            let html = '';
            toRender.forEach(function (student) {
                html += buildStudentGridCard(student, currentStatus);
            });
            $('#studentsWidgetGrid').append(html);

            if (studentsHasMore) {
                $('#studentGridEnd').addClass('d-none');
            } else if (studentsTotal > GRID_PAGE_SIZE) {
                $('#studentGridEnd').removeClass('d-none');
            }
        }

        function resetAndRenderGrid() {
            studentUserScrolled = false;
            gridLoadingMore = false;
            renderGridFromCurrentData(false);
        }

        function loadMoreStudentsForGrid() {
            if ($('#studentsDataWrapper').attr('data-active-view') !== 'grid') return;
            if (gridLoadingMore || !studentsHasMore) return;

            gridLoadingMore = true;
            $('#studentGridSentinel').removeClass('d-none');
            const nextPage = studentsPage + 1;

            $.ajax({
                url: {!! json_encode(route("get_students_list")) !!},
                type: 'GET',
                data: getStudentFilterParams({ page: nextPage }),
                dataType: 'json'
            }).done(function (response) {
                if (!response.success) return;
                const batch = response.students || [];
                applyPaginationMeta(response.pagination || {});
                if (batch.length) {
                    currentStudentsData = currentStudentsData.concat(batch);
                    renderGridFromCurrentData(true);
                }
            }).always(function () {
                gridLoadingMore = false;
                $('#studentGridSentinel').addClass('d-none');
            });
        }

        function maybeLoadMoreGridOnScroll() {
            if ($('#studentsDataWrapper').attr('data-active-view') !== 'grid') return;
            if (!studentUserScrolled) return;
            if (gridLoadingMore || !studentsHasMore) return;

            const gridEl = document.getElementById('studentsWidgetGrid');
            if (!gridEl) return;
            const rect = gridEl.getBoundingClientRect();
            if (rect.bottom < window.innerHeight + 200) {
                loadMoreStudentsForGrid();
            }
        }

        function renderListPager() {
            // Native DataTables pagination is used for list view
            $('#studentsListPager').remove();
        }

        function renderStudentsUI(students, status) {
            currentStatus = status || currentStatus;
            const view = $('#studentsDataWrapper').attr('data-active-view');
            if (view === 'grid') {
                resetAndRenderGrid();
            } else {
                reloadStudentsDataTable(true);
            }
        }

        // Load students: list = full DataTables; grid = infinite cards + full-width search
        function loadStudents(options) {
            const opts = options || {};
            const view = $('#studentsDataWrapper').attr('data-active-view') || 'list';
            currentStatus = $('#statusFilter').val() || 'Active';

            if (view === 'grid') {
                loadStudentsGrid(opts);
            } else {
                const resetPaging = opts.resetPaging !== false && (opts.page === 1 || opts.page === undefined);
                reloadStudentsDataTable(resetPaging);
            }
        }

        function loadStudentsGrid(options) {
            const opts = options || {};
            const page = opts.page || 1;
            const status = $('#statusFilter').val() || 'Active';
            currentStatus = status;
            studentUserScrolled = false;
            gridLoadingMore = false;

            $('#studentsWidgetGrid').html('<div class="col-12"><div class="student-grid-empty"><i class="bi bi-hourglass-split"></i><br>Loading students...</div></div>');
            $('#studentGridSentinel, #studentGridEnd').addClass('d-none');

            $.ajax({
                url: {!! json_encode(route("get_students_list")) !!},
                type: 'GET',
                data: getStudentFilterParams({ page: page }),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        currentStudentsData = response.students || [];
                        applyPaginationMeta(response.pagination || {
                            page: 1,
                            last_page: 1,
                            has_more: false,
                            total: currentStudentsData.length
                        });
                        updateNotEnrolledBulkUi();
                        resetAndRenderGrid();
                        loadStatistics();
                        if (studentSearchKeepFocus) {
                            const $search = $('#studentGridSearch');
                            if ($search.length) {
                                const val = $search.val() || '';
                                $search.trigger('focus');
                                try {
                                    const el = $search.get(0);
                                    if (el && typeof el.setSelectionRange === 'function') {
                                        el.setSelectionRange(val.length, val.length);
                                    }
                                } catch (e) {}
                            }
                            studentSearchKeepFocus = false;
                        }
                    } else {
                        currentStudentsData = [];
                        applyPaginationMeta({ page: 1, last_page: 1, has_more: false, total: 0 });
                        resetAndRenderGrid();
                        loadStatistics();
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to load students';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    currentStudentsData = [];
                    $('#studentsWidgetGrid').html(
                        '<div class="col-12"><div class="student-grid-empty" style="color:#940000;">' +
                        '<i class="bi bi-exclamation-triangle"></i><br>' + escapeHtmlStudent(errorMessage) +
                        '<br><button class="btn btn-sm mt-2" style="background:#940000;color:#fff;border:0;" onclick="location.reload()">Refresh Page</button>' +
                        '</div></div>'
                    );
                    loadStatistics();
                }
            });
        }

        // Handle Register New Student Button Click
        $('#addStudentBtn').on('click', function(e) {
            e.preventDefault();
            const modal = document.getElementById('classSelectorModal');
            if (modal) {
                // Ensure subclasses are loading
                if (typeof window.loadSubclasses === 'function') {
                    window.loadSubclasses();
                }

                // Try Bootstrap 5 modal
                if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                    const bsModal = new bootstrap.Modal(modal, {
                        backdrop: 'static',
                        keyboard: true
                    });
                    bsModal.show();
                } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                    // Fallback to jQuery Bootstrap modal
                    jQuery(modal).modal('show');
                } else {
                    // Manual fallback
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'classSelectorBackdrop';
                    document.body.appendChild(backdrop);
                }
            } else {
                console.error('classSelectorModal not found');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Class selector modal not found. Please refresh the page.',
                    confirmButtonColor: '#940000'
                });
            }
        });

        // Handle Import Excel Button Click
        $('#importBtn').on('click', function(e) {
            e.preventDefault();
            const modal = document.getElementById('importStudentsModal');
            if (modal) {
                // Try Bootstrap 5 modal
                if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                    const bsModal = new bootstrap.Modal(modal, {
                        backdrop: true,
                        keyboard: true
                    });
                    bsModal.show();
                } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                    // Fallback to jQuery Bootstrap modal
                    jQuery(modal).modal('show');
                } else {
                    // Manual fallback
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.id = 'importSelectorBackdrop';
                    document.body.appendChild(backdrop);
                }
            } else {
                console.error('importStudentsModal not found');
            }
        });

        // Add robust close handlers for the import modal
        $('#importStudentsModal .btn-close, #importStudentsModal .btn-secondary').on('click', function() {
            const modal = document.getElementById('importStudentsModal');
            if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                } else {
                    $('#importStudentsModal').removeClass('show').css('display', 'none');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }
            } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                $('#importStudentsModal').modal('hide');
            } else {
                $('#importStudentsModal').removeClass('show').css('display', 'none');
                $('#importSelectorBackdrop').remove();
                $('body').removeClass('modal-open');
            }
        });

        // Handle Import form
        $('#importStudentsForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            formData.append('_token', {!! json_encode(csrf_token()) !!});

            $('#importSubmitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Importing...');

            $.ajax({
                url: {!! json_encode(route("upload_students")) !!},
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importSubmitBtn').prop('disabled', false).html('<i class="bi bi-upload"></i> Upload \u0026 Import Data');
                    if (response.success) {
                        $('#importStudentsModal').modal('hide');
                        $('#importStudentsForm')[0].reset();
                        
                        let msg = response.message;
                        if (response.errors && response.errors.length > 0) {
                            msg += "<br><br><strong>Note:</strong> Some rows failed. See console or notify admin.";
                            console.error("Import Row Errors:", response.errors);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: msg,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            reloadData();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: response.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function(xhr) {
                    $('#importSubmitBtn').prop('disabled', false).html('<i class="bi bi-upload"></i> Upload \u0026 Import Data');
                    let errorMsg = 'An error occurred during import.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: errorMsg,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // ===================== MOVE STUDENTS WIZARD =====================
        let moveStep = 1;
        let movePreviewRows = [];
        let moveLevel = {!! !empty($hasALevel) ? 'null' : json_encode('o_level') !!};
        const moveHasALevel = {!! !empty($hasALevel) ? 'true' : 'false' !!};
        const moveOLevelYears = @json(($oLevelAcademicYears ?? $academicYears ?? collect())->map(function ($y) {
            return [
                'id' => $y->academic_yearID,
                'label' => ($y->year_name ?: (string) $y->year) . (!empty($y->is_current) ? ' (Current)' : ''),
            ];
        })->values());
        const moveALevelYears = @json(($aLevelAcademicYears ?? collect())->map(function ($y) {
            return [
                'id' => $y->academic_yearID,
                'label' => ($y->year_name ?: (string) $y->year) . (!empty($y->is_current) ? ' (Current)' : ''),
            ];
        })->values());
        const moveAllYears = @json(($academicYears ?? collect())->map(function ($y) {
            return [
                'id' => $y->academic_yearID,
                'label' => ($y->year_name ?: (string) $y->year) . (!empty($y->is_current) ? ' (Current)' : ''),
            ];
        })->values());

        function fillMoveTargetYears(level) {
            let years = moveAllYears;
            if (moveHasALevel) {
                if (level === 'a_level') {
                    years = moveALevelYears;
                } else if (level === 'o_level') {
                    years = moveOLevelYears;
                } else {
                    years = [];
                }
            }
            let html = '<option value="">-- Select Academic Year --</option>';
            (years || []).forEach(function (y) {
                html += '<option value="' + y.id + '">' + escapeHtmlStudent(y.label) + '</option>';
            });
            $('#moveTargetYear').html(html);
            if (moveHasALevel) {
                $('#moveYearWrap').toggleClass('d-none', !level);
            } else {
                $('#moveYearWrap').removeClass('d-none');
            }
        }

        function updateMoveNextEnabled() {
            if (moveStep === 1) {
                const hasYear = !!$('#moveTargetYear').val();
                const hasLevel = !moveHasALevel || !!moveLevel;
                $('#moveNextBtn').prop('disabled', !(hasYear && hasLevel));
                $('#moveYearWarning').toggle(hasYear && hasLevel);
            } else if (moveStep === 2) {
                updateMoveSelectedCount();
            }
        }

        function highlightMoveLevel(level) {
            moveLevel = level;
            $('.move-level-btn').css({ background: '#fff', color: '#940000' });
            if (level) {
                $('.move-level-btn[data-level="' + level + '"]').css({ background: '#940000', color: '#fff' });
            }
            fillMoveTargetYears(level);
            updateMoveNextEnabled();
        }

        function filterMoveClassesByLevel() {
            let classHtml = '<option value="">-- All Classes --</option>';
            let classes = {};
            moveAllSubclassesCache.forEach(function (subclass) {
                if (!subclass.classID || !subclass.class_name || classes[subclass.classID]) return;
                const name = subclass.class_name || '';
                const n = String(name).toLowerCase().replace(/[\s\-]+/g, '_');
                const isA = n.indexOf('form_five') !== -1 || n.indexOf('form_5') !== -1 || n.indexOf('form_six') !== -1 || n.indexOf('form_6') !== -1
                    || n === 'form_five' || n === 'form_5' || n === 'form_six' || n === 'form_6';
                if (moveLevel === 'a_level' && !isA) return;
                if (moveLevel === 'o_level' && isA) return;
                classes[subclass.classID] = subclass.class_name;
                classHtml += '<option value="' + subclass.classID + '">' + escapeHtmlStudent(subclass.class_name) + '</option>';
            });
            $('#moveFilterClass').html(classHtml);
            fillMoveSubclasses('');
        }

        function openStudentModal(id) {
            const $modal = $('#' + id);
            if (!$modal.length) return;

            // Static: do not close on outside click / Esc (BS4 + BS5 attrs)
            $modal.attr({
                'data-backdrop': 'static',
                'data-keyboard': 'false',
                'data-bs-backdrop': 'static',
                'data-bs-keyboard': 'false'
            });

            // Prefer jQuery Bootstrap plugin (Admin_nav loads Bootstrap 4.6)
            if (typeof $modal.modal === 'function') {
                try {
                    // Reset prior instance so static options always apply (BS4)
                    $modal.removeData('bs.modal');
                    $modal.modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });
                } catch (e) {
                    $modal.modal('show');
                }
                return;
            }

            // Bootstrap 5 native (no getOrCreateInstance, may be missing on older builds)
            if (window.bootstrap && bootstrap.Modal) {
                try {
                    let instance = (typeof bootstrap.Modal.getInstance === 'function')
                        ? bootstrap.Modal.getInstance($modal[0])
                        : null;
                    if (!instance) {
                        instance = new bootstrap.Modal($modal[0], {
                            backdrop: 'static',
                            keyboard: false
                        });
                    }
                    instance.show();
                    return;
                } catch (e) {
                    console.warn('Native Bootstrap modal failed, using manual show', e);
                }
            }

            // Manual fallback
            $('body').addClass('modal-open');
            $modal.css('display', 'block').addClass('show').attr('aria-hidden', 'false');
            if ($('.modal-backdrop').length === 0) {
                $('body').append('<div class="modal-backdrop fade show"></div>');
            }
        }

        function closeStudentModal(id) {
            hideModal(id);
        }

        function setMoveStep(step) {
            moveStep = step;
            $('.move-step').addClass('d-none');
            $('#moveStep' + step).removeClass('d-none');
            $('.move-step-badge').removeClass('active');
            $('.move-step-badge[data-step="' + step + '"]').addClass('active');
            $('#movePrevBtn').toggleClass('d-none', step === 1);
            $('#moveNextBtn').toggleClass('d-none', step === 3);
            if (step === 1) {
                updateMoveNextEnabled();
            } else if (step === 2) {
                filterMoveClassesByLevel();
                updateMoveSelectedCount();
            } else if (step === 3) {
                const selected = getSelectedMoveRows();
                const yearLabel = $('#moveTargetYear option:selected').text();
                const promoted = selected.filter(r => r.action === 'Promoted').length;
                const graduated = selected.filter(r => r.action === 'Graduated').length;
                $('#moveConfirmSummary').html(
                    '<div>Target year: <strong>' + escapeHtmlStudent(yearLabel) + '</strong></div>' +
                    '<div>Selected: <strong>' + selected.length + '</strong></div>' +
                    '<div>Will promote: <strong>' + promoted + '</strong></div>' +
                    '<div>Will graduate: <strong>' + graduated + '</strong></div>'
                );
                $('#moveConfirmBox').addClass('d-none');
                $('#moveConfirmWordWrap').addClass('d-none');
                $('#moveConfirmYesNo').val('');
                $('#moveConfirmWord').val('');
                $('#moveExecuteBtn').prop('disabled', true);
            }
        }

        function getSelectedMoveRows() {
            const ids = $('.move-row-check:checked').map(function () { return parseInt($(this).val(), 10); }).get();
            return movePreviewRows.filter(function (r) {
                if (ids.indexOf(r.enrollmentID) === -1) return false;
                if (r.action === 'Graduated') return true;
                return !!r.destination_subclassID;
            });
        }

        function buildMoveDestSelect(row) {
            const options = row.destination_options || [];
            if (!options.length || row.action === 'Graduated') {
                let html = escapeHtmlStudent(row.destination_class || '-');
                if (row.note) html += '<br><small class="text-muted">' + escapeHtmlStudent(row.note) + '</small>';
                if (row.error) html += '<br><small style="color:#940000;">' + escapeHtmlStudent(row.error) + '</small>';
                return html;
            }

            let html = '<select class="form-control form-control-sm move-dest-select" data-enrollment-id="' + row.enrollmentID + '" style="border-radius:0;min-width:190px;">';
            html += '<option value="">-- Select subclass --</option>';
            options.forEach(function (opt) {
                const selected = String(row.destination_subclassID || '') === String(opt.subclassID) ? ' selected' : '';
                html += '<option value="' + opt.subclassID + '"' + selected + '>' + escapeHtmlStudent(opt.label || '') + '</option>';
            });
            html += '</select>';
            if (row.note) {
                html += '<small class="text-muted d-block mt-1">' + escapeHtmlStudent(row.note) + '</small>';
            }
            if (row.error && !row.destination_subclassID) {
                html += '<small class="d-block mt-1" style="color:#940000;">' + escapeHtmlStudent(row.error) + '</small>';
            }
            return html;
        }

        function applyMoveDestinationChoice(enrollmentID, subclassID) {
            const row = movePreviewRows.find(r => String(r.enrollmentID) === String(enrollmentID));
            if (!row) return;
            const opt = (row.destination_options || []).find(o => String(o.subclassID) === String(subclassID));
            if (!subclassID || !opt) {
                row.destination_subclassID = null;
                row.destination_combie = null;
                row.can_move = false;
                row.error = 'Select destination subclass';
            } else {
                row.destination_subclassID = parseInt(opt.subclassID, 10);
                row.destination_classID = parseInt(opt.classID, 10);
                row.destination_class = opt.label;
                row.destination_combie = opt.combie || null;
                row.error = null;
                row.can_move = true;
                row.action = 'Promoted';
            }

            const $tr = $('.move-dest-select[data-enrollment-id="' + enrollmentID + '"]').closest('tr');
            $tr.toggleClass('table-secondary', !row.destination_subclassID && row.action === 'Promoted');
            updateMoveSelectedCount();
        }

        function updateMoveSelectedCount() {
            const n = getSelectedMoveRows().length;
            $('#moveSelectedCount').text(n + ' selected');
            $('#moveNextBtn').prop('disabled', moveStep === 2 ? n === 0 : !$('#moveTargetYear').val());
        }

        let moveAllSubclassesCache = [];

        function loadMoveClasses() {
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    moveAllSubclassesCache = (response.success && response.subclasses) ? response.subclasses : [];
                    let classHtml = '<option value="">-- All Classes --</option>';
                    let classes = {};
                    moveAllSubclassesCache.forEach(function (subclass) {
                        if (subclass.classID && subclass.class_name && !classes[subclass.classID]) {
                            classes[subclass.classID] = subclass.class_name;
                            classHtml += '<option value="' + subclass.classID + '">' + escapeHtmlStudent(subclass.class_name) + '</option>';
                        }
                    });
                    $('#moveFilterClass').html(classHtml);
                    $('#moveFilterSubclass').html('<option value="">-- All Subclasses --</option>');
                    filterMoveClassesByLevel();
                }
            });
        }

        function fillMoveSubclasses(classID) {
            let html = '<option value="">-- All Subclasses --</option>';
            moveAllSubclassesCache.forEach(function (subclass) {
                if (!classID || String(subclass.classID) === String(classID)) {
                    const displayName = subclass.display_name || ((subclass.class_name || '') + ' ' + (subclass.subclass_name || ''));
                    html += '<option value="' + subclass.subclassID + '">' + escapeHtmlStudent(displayName.trim()) + '</option>';
                }
            });
            $('#moveFilterSubclass').html(html);
        }

        let movePreviewRequest = null;
        function ensureMovePreviewTableHead() {
            $('#movePreviewTable thead').html(
                '<tr>' +
                '<th width="40"></th>' +
                '<th>Admission</th>' +
                '<th>Name</th>' +
                '<th>Current Class</th>' +
                '<th>Will Go To</th>' +
                '<th>Action</th>' +
                '<th width="50"></th>' +
                '</tr>'
            );
        }

        function loadMovePreview() {
            ensureMovePreviewTableHead();
            if (!$('#moveTargetYear').val()) {
                $('#movePreviewBody').html('<tr><td colspan="7" class="text-center text-muted">Select academic year first</td></tr>');
                movePreviewRows = [];
                updateMoveSelectedCount();
                return;
            }

            $('#movePreviewBody').html('<tr><td colspan="7" class="text-center text-muted">Loading active enrollments...</td></tr>');
            if (movePreviewRequest && movePreviewRequest.readyState !== 4) {
                movePreviewRequest.abort();
            }
            movePreviewRequest = $.ajax({
                url: {!! json_encode(route("move_students_preview")) !!},
                type: 'GET',
                data: {
                    academic_yearID: $('#moveTargetYear').val() || '',
                    classID: $('#moveFilterClass').val() || '',
                    subclassID: $('#moveFilterSubclass').val() || '',
                    level: moveLevel || ''
                },
                dataType: 'json',
                success: function (res) {
                    movePreviewRows = res.students || [];
                    const alreadyMoved = res.already_moved || [];
                    if (!movePreviewRows.length && !alreadyMoved.length) {
                        $('#movePreviewBody').html('<tr><td colspan="7" class="text-center text-muted">No active enrollments found</td></tr>');
                        updateMoveSelectedCount();
                        return;
                    }

                    let html = '';
                    alreadyMoved.forEach(function (row) {
                        html += '<tr class="table-warning">' +
                            '<td></td>' +
                            '<td>' + escapeHtmlStudent(row.admission_number || '-') + '</td>' +
                            '<td>' + escapeHtmlStudent(row.full_name || '-') + '</td>' +
                            '<td>' + escapeHtmlStudent(row.current_class || '-') + '</td>' +
                            '<td>' + escapeHtmlStudent(row.destination_class || '-') +
                                '<br><small class="text-muted">Already moved</small></td>' +
                            '<td>Already moved</td>' +
                            '<td><button type="button" class="btn btn-sm move-undo-item-btn" title="Revert / Undo this student" data-item-id="' + row.itemID + '" style="background:#940000;color:#fff;border-radius:0;padding:2px 6px;">' +
                                '<i class="bi bi-arrow-counterclockwise"></i></button></td>' +
                            '</tr>';
                    });
                    movePreviewRows.forEach(function (row) {
                        const canSelect = row.action === 'Graduated' ||
                            (row.destination_options && row.destination_options.length) || row.can_move;
                        const disabled = canSelect ? '' : 'disabled';
                        const needsPick = row.action === 'Promoted' && !row.destination_subclassID;
                        html += '<tr class="' + (needsPick ? 'table-secondary' : '') + '" data-enrollment-id="' + row.enrollmentID + '">' +
                            '<td><input type="checkbox" class="move-row-check" value="' + row.enrollmentID + '" ' + disabled + '></td>' +
                            '<td>' + escapeHtmlStudent(row.admission_number || '-') + '</td>' +
                            '<td>' + escapeHtmlStudent(row.full_name || '-') + '</td>' +
                            '<td>' + escapeHtmlStudent(row.current_class || '-') + '</td>' +
                            '<td>' + buildMoveDestSelect(row) + '</td>' +
                            '<td>' + escapeHtmlStudent(row.action || '-') + '</td>' +
                            '<td></td>' +
                            '</tr>';
                    });
                    $('#movePreviewBody').html(html);
                    $('#moveSelectAll').prop('checked', false);
                    updateMoveSelectedCount();
                },
                error: function (xhr) {
                    if (xhr.statusText === 'abort') return;
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load';
                    $('#movePreviewBody').html('<tr><td colspan="7" class="text-center" style="color:#940000;">' + escapeHtmlStudent(msg) + '</td></tr>');
                }
            });
        }

        $(document).on('change', '.move-dest-select', function () {
            applyMoveDestinationChoice($(this).data('enrollment-id'), $(this).val());
        });

        $('#moveStudentsBtn').on('click', function () {
            setMoveStep(1);
            moveLevel = moveHasALevel ? null : 'o_level';
            highlightMoveLevel(moveLevel);
            $('#moveYearWarning').hide();
            $('#moveNextBtn').prop('disabled', true);
            loadMoveClasses();
            openStudentModal('moveStudentsModal');
        });

        $(document).on('click', '.move-level-btn', function () {
            highlightMoveLevel($(this).data('level'));
        });

        $('#moveTargetYear').on('change', function () {
            updateMoveNextEnabled();
        });

        $('#moveNextBtn').on('click', function () {
            if (moveStep === 1 && (!$('#moveTargetYear').val() || (moveHasALevel && !moveLevel))) return;
            if (moveStep === 2 && getSelectedMoveRows().length === 0) return;
            if (moveStep === 1) {
                setMoveStep(2);
                loadMovePreview();
            } else if (moveStep === 2) {
                setMoveStep(3);
            }
        });

        $('#movePrevBtn').on('click', function () {
            if (moveStep > 1) setMoveStep(moveStep - 1);
        });

        $('#moveReloadPreviewBtn').on('click', loadMovePreview);

        $('#moveFilterClass').on('change', function () {
            fillMoveSubclasses($(this).val());
            loadMovePreview();
        });

        $('#moveFilterSubclass').on('change', function () {
            loadMovePreview();
        });

        $(document).on('click', '.move-undo-item-btn', function () {
            const itemID = $(this).data('item-id');
            const $row = $(this).closest('tr');
            const panelHtml = '<div class="p-2" style="border:1px dashed #940000;background:#fff;">' +
                '<p class="mb-1 small fw-bold" style="color:#940000;">Undo this student move? Type <strong>yes</strong></p>' +
                '<input type="text" class="form-control form-control-sm mb-2 move-undo-item-yes" placeholder="yes" style="border-radius:0;">' +
                '<button type="button" class="btn btn-sm move-undo-item-confirm" data-item-id="' + itemID + '" style="background:#940000;color:#fff;border-radius:0;" disabled>Undo</button>' +
                '</div>';
            $row.find('td').last().html(panelHtml);
        });

        $(document).on('keyup change', '.move-undo-item-yes', function () {
            const ok = (($(this).val() || '').trim().toLowerCase() === 'yes');
            $(this).closest('td').find('.move-undo-item-confirm').prop('disabled', !ok);
        });

        $(document).on('click', '.move-undo-item-confirm', function () {
            const itemID = $(this).data('item-id');
            const $btn = $(this).prop('disabled', true).text('...');
            $.ajax({
                url: {!! json_encode(route("move_students_undo_item")) !!},
                type: 'POST',
                data: { _token: {!! json_encode(csrf_token()) !!}, itemID: itemID, confirm_word: 'yes' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        loadMovePreview();
                        loadStudents({ page: 1 });
                    } else {
                        alert(res.message || 'Undo failed');
                        $btn.prop('disabled', false).text('Undo');
                    }
                },
                error: function (xhr) {
                    alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Undo failed');
                    $btn.prop('disabled', false).text('Undo');
                }
            });
        });

        $(document).on('change', '#moveSelectAll', function () {
            const checked = $(this).is(':checked');
            $('.move-row-check:not(:disabled)').prop('checked', checked);
            updateMoveSelectedCount();
        });
        $(document).on('change', '.move-row-check', updateMoveSelectedCount);

        $('#moveExpandConfirmBtn').on('click', function () {
            $('#moveConfirmBox').removeClass('d-none');
        });

        $('#moveConfirmYesNo').on('keyup change', function () {
            const v = ($(this).val() || '').trim().toLowerCase();
            if (v === 'no') {
                $('#moveConfirmWordWrap').addClass('d-none');
                $('#moveExecuteBtn').prop('disabled', true);
                return;
            }
            if (v === 'yes') {
                $('#moveConfirmWordWrap').removeClass('d-none');
            } else {
                $('#moveConfirmWordWrap').addClass('d-none');
            }
            validateMoveConfirmWord();
        });

        $('#moveConfirmWord').on('keyup change', validateMoveConfirmWord);

        function validateMoveConfirmWord() {
            const a = ($('#moveConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($('#moveConfirmWord').val() || '').trim().toLowerCase();
            $('#moveExecuteBtn').prop('disabled', !(a === 'yes' && b === 'yes'));
        }

        $('#moveExecuteBtn').on('click', function () {
            const selected = getSelectedMoveRows();
            if (!selected.length) return;
            const missing = selected.filter(r => r.action === 'Promoted' && !r.destination_subclassID);
            if (missing.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select destination subclass',
                    text: missing.length + ' selected student(s) still need a destination subclass.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            const destinationSubclass = {};
            selected.forEach(function (r) {
                if (r.destination_subclassID) {
                    destinationSubclass[r.enrollmentID] = r.destination_subclassID;
                }
            });

            const $btn = $(this).prop('disabled', true).text('Moving...');
            $.ajax({
                url: {!! json_encode(route("move_students_execute")) !!},
                type: 'POST',
                data: {
                    _token: {!! json_encode(csrf_token()) !!},
                    academic_yearID: $('#moveTargetYear').val(),
                    enrollmentIDs: selected.map(r => r.enrollmentID),
                    destination_subclass: destinationSubclass,
                    confirm_word: 'yes'
                },
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).html('Confirm \u0026 Move');
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Move Completed',
                            html: escapeHtmlStudent(res.message || 'Done') +
                                (res.batchID ? '<br><small>Batch #' + res.batchID + ', you can Undo from menu</small>' : ''),
                            confirmButtonColor: '#940000'
                        }).then(function () {
                            closeStudentModal('moveStudentsModal');
                            loadStudents({ page: 1 });
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Move Failed', text: res.message || 'Failed', confirmButtonColor: '#940000' });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('Confirm \u0026 Move');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Move failed';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#940000' });
                }
            });
        });

        $('#undoMoveBatchesBtn').on('click', function () {
            resetUndoMoveConfirm();
            $('#undoMoveBatchesList').html('Loading...');
            openStudentModal('undoMoveModal');
            $.getJSON({!! json_encode(route("move_students_batches")) !!}, function (res) {
                const batches = res.batches || [];
                if (!batches.length) {
                    $('#undoMoveBatchesList').html('<div class="text-muted">No move batches yet.</div>');
                    return;
                }
                let html = '<div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr>' +
                    '<th>#</th><th>Year</th><th>Promoted</th><th>Graduated</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>';
                batches.forEach(function (b) {
                    html += '<tr>' +
                        '<td>' + b.batchID + '</td>' +
                        '<td>' + escapeHtmlStudent(b.to_year) + '</td>' +
                        '<td>' + b.moved_count + '</td>' +
                        '<td>' + b.graduated_count + '</td>' +
                        '<td>' + escapeHtmlStudent(b.status) + '</td>' +
                        '<td>' + escapeHtmlStudent(b.created_at || '') + '</td>' +
                        '<td>' + (b.can_undo
                            ? '<button type="button" class="btn btn-sm undo-move-batch-btn" data-batch-id="' + b.batchID + '" style="background:#940000;color:#fff;border-radius:0;">Undo</button>'
                            : '<span class="text-muted small">-</span>') + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table></div>';
                $('#undoMoveBatchesList').html(html);
            }).fail(function () {
                $('#undoMoveBatchesList').html('<div style="color:#940000;">Failed to load batches</div>');
            });
        });

        let undoMoveBatchID = null;

        function resetUndoMoveConfirm() {
            undoMoveBatchID = null;
            $('#undoMoveConfirmBox').addClass('d-none');
            $('#undoMoveConfirmWordWrap').addClass('d-none');
            $('#undoMoveConfirmYesNo').val('');
            $('#undoMoveConfirmWord').val('');
            $('#undoMoveConfirmBatchLabel').text('-');
            $('#undoMoveExecuteBtn').prop('disabled', true).html('Confirm \u0026 Undo');
        }

        function validateUndoMoveConfirmWord() {
            const a = ($('#undoMoveConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($('#undoMoveConfirmWord').val() || '').trim().toLowerCase();
            $('#undoMoveExecuteBtn').prop('disabled', !(a === 'yes' && b === 'yes'));
        }

        $(document).on('click', '.undo-move-batch-btn', function () {
            undoMoveBatchID = $(this).data('batch-id');
            $('#undoMoveConfirmBatchLabel').text(undoMoveBatchID);
            $('#undoMoveConfirmYesNo').val('');
            $('#undoMoveConfirmWord').val('');
            $('#undoMoveConfirmWordWrap').addClass('d-none');
            $('#undoMoveExecuteBtn').prop('disabled', true);
            $('#undoMoveConfirmBox').removeClass('d-none');
            setTimeout(function () {
                $('#undoMoveConfirmYesNo').trigger('focus');
            }, 50);
        });

        $('#undoMoveConfirmYesNo').on('keyup change', function () {
            const v = ($(this).val() || '').trim().toLowerCase();
            if (v === 'no') {
                resetUndoMoveConfirm();
                return;
            }
            if (v === 'yes') {
                $('#undoMoveConfirmWordWrap').removeClass('d-none');
            } else {
                $('#undoMoveConfirmWordWrap').addClass('d-none');
            }
            validateUndoMoveConfirmWord();
        });

        $('#undoMoveConfirmWord').on('keyup change', validateUndoMoveConfirmWord);

        $('#undoMoveExecuteBtn').on('click', function () {
            if (!undoMoveBatchID) return;
            const a = ($('#undoMoveConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($('#undoMoveConfirmWord').val() || '').trim().toLowerCase();
            if (a !== 'yes' || b !== 'yes') return;

            const $btn = $(this).prop('disabled', true).html('Undoing...');
            $.ajax({
                url: {!! json_encode(route("move_students_undo")) !!},
                type: 'POST',
                data: { _token: {!! json_encode(csrf_token()) !!}, batchID: undoMoveBatchID, confirm_word: 'yes' },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        resetUndoMoveConfirm();
                        alert(res.message || 'Move undone successfully.');
                        $('#undoMoveBatchesBtn').click();
                        loadStudents({ page: 1 });
                    } else {
                        $btn.prop('disabled', false).html('Confirm \u0026 Undo');
                        alert(res.message || 'Undo failed');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('Confirm \u0026 Undo');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Undo failed';
                    alert(msg);
                }
            });
        });

        // ===================== SINGLE STUDENT MOVE (Internal / External) =====================
        let singleMoveState = {
            studentID: null,
            type: null,
            classes: [],
            destinations: []
        };

        function resetSingleMoveUI() {
            singleMoveState.type = null;
            singleMoveState.classes = [];
            singleMoveState.destinations = [];
            $('.single-move-type-btn').css({ background: '#fff', color: '#940000' });
            $('#singleMoveDestWrap').addClass('d-none');
            $('#singleMoveClass').html('<option value="">-- Select class --</option>');
            $('#singleMoveSubclass').html('<option value="">-- Select subclass --</option>').prop('disabled', true);
            $('#singleMoveActionHint').hide().text('');
            $('#singleMoveConfirmBtn').prop('disabled', true);
        }

        function highlightSingleMoveType(type) {
            $('.single-move-type-btn').css({ background: '#fff', color: '#940000' });
            $('.single-move-type-btn[data-type="' + type + '"]').css({ background: '#940000', color: '#fff' });
        }

        function fillSingleMoveDestinations(payload) {
            singleMoveState.classes = payload.classes || [];
            singleMoveState.destinations = payload.destinations || [];

            if (payload.student) {
                $('#singleMoveStudentName').text(payload.student.full_name || '-');
                $('#singleMoveCurrentClass').text(payload.student.current_class || '-');
            }

            if (!singleMoveState.destinations.length) {
                $('#singleMoveDestWrap').removeClass('d-none');
                $('#singleMoveClass').html('<option value="">No destinations available</option>');
                $('#singleMoveSubclass').html('<option value="">-</option>').prop('disabled', true);
                $('#singleMoveActionHint').show().text('No eligible destinations for this move type.');
                $('#singleMoveConfirmBtn').prop('disabled', true);
                return;
            }

            $('#singleMoveDestWrap').removeClass('d-none');
            let classHtml = '<option value="">-- Select class --</option>';
            singleMoveState.classes.forEach(function (c) {
                classHtml += '<option value="' + c.classID + '" data-action="' + escapeHtmlStudent(c.action || '') + '" data-action-label="' + escapeHtmlStudent(c.action_label || '') + '">' +
                    escapeHtmlStudent(c.class_name) +
                    (c.action === 'Reverted' ? ' (Revert)' : (c.action === 'Moved' ? ' (Move)' : '')) +
                    '</option>';
            });
            $('#singleMoveClass').html(classHtml);
            $('#singleMoveSubclass').html('<option value="">-- Select subclass --</option>').prop('disabled', true);
            $('#singleMoveActionHint').hide().text('');
            $('#singleMoveConfirmBtn').prop('disabled', true);

            // Internal: often one class, auto-select
            if (singleMoveState.type === 'internal' && singleMoveState.classes.length === 1) {
                $('#singleMoveClass').val(String(singleMoveState.classes[0].classID)).trigger('change');
            }
        }

        $(document).on('click', '.single-move-student-btn', function () {
            singleMoveState.studentID = $(this).data('student-id');
            resetSingleMoveUI();
            $('#singleMoveStudentName').text($(this).data('student-name') || '-');
            $('#singleMoveCurrentClass').text($(this).data('current-class') || '-');
            $('#singleMoveConfirmPanel').addClass('d-none');
            $('#singleMoveConfirmYesNo').val('');
            $('#singleMoveConfirmWord').val('');
            openStudentModal('singleMoveStudentModal');
        });

        $(document).on('click', '.single-move-type-btn', function () {
            const type = $(this).data('type');
            if (!singleMoveState.studentID) return;
            singleMoveState.type = type;
            highlightSingleMoveType(type);
            $('#singleMoveDestWrap').removeClass('d-none');
            $('#singleMoveClass').html('<option value="">Loading...</option>');
            $('#singleMoveSubclass').prop('disabled', true);
            $('#singleMoveConfirmBtn').prop('disabled', true);

            $.ajax({
                url: {!! json_encode(url("/move_student_options")) !!} + '/' + singleMoveState.studentID,
                type: 'GET',
                data: { type: type },
                dataType: 'json',
                success: function (res) {
                    if (!res.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to load destinations', confirmButtonColor: '#940000' });
                        return;
                    }
                    fillSingleMoveDestinations(res);
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load destinations';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#940000' });
                }
            });
        });

        $('#singleMoveClass').on('change', function () {
            const classID = $(this).val();
            const $opt = $(this).find('option:selected');
            const action = $opt.data('action');
            const actionLabel = $opt.data('action-label');

            if (!classID) {
                $('#singleMoveSubclass').html('<option value="">-- Select subclass --</option>').prop('disabled', true);
                $('#singleMoveActionHint').hide();
                $('#singleMoveConfirmBtn').prop('disabled', true);
                return;
            }

            const group = singleMoveState.classes.find(c => String(c.classID) === String(classID));
            let html = '<option value="">-- Select subclass --</option>';
            (group ? group.subclasses : []).forEach(function (s) {
                const subLabel = s.label || s.subclass_name || '';
                html += '<option value="' + s.subclassID + '" data-action="' + escapeHtmlStudent(s.action || '') + '" data-label="' + escapeHtmlStudent(s.label || '') + '" data-combie="' + escapeHtmlStudent(s.combie || '') + '">' +
                    escapeHtmlStudent(subLabel) + '</option>';
            });
            $('#singleMoveSubclass').html(html).prop('disabled', false);
            $('#singleMoveConfirmBtn').prop('disabled', true);

            if (action) {
                let tip = actionLabel || action;
                if (action === 'Reverted') {
                    tip = 'This will REVERT the student to a previous class level.';
                } else if (action === 'Moved') {
                    tip = 'This will MOVE the student to another main class.';
                } else {
                    tip = 'This will move the student within the same class (subclass/stream).';
                }
                $('#singleMoveActionHint').show().text(tip);
            }
        });

        $('#singleMoveSubclass').on('change', function () {
            $('#singleMoveConfirmBtn').prop('disabled', !$(this).val());
        });

        $('#singleMoveConfirmBtn').on('click', function () {
            const subclassID = $('#singleMoveSubclass').val();
            if (!singleMoveState.studentID || !singleMoveState.type || !subclassID) return;

            const destLabel = $('#singleMoveSubclass option:selected').data('label') || $('#singleMoveSubclass option:selected').text();
            const action = $('#singleMoveSubclass option:selected').data('action') || $('#singleMoveClass option:selected').data('action') || '';
            const title = action === 'Reverted'
                ? 'Are you sure you want to revert this student?'
                : 'Are you sure you want to perform this action?';

            $('#singleMoveConfirmTitle').text(title);
            $('#singleMoveConfirmPanel').removeClass('d-none');
            $('#singleMoveConfirmWordWrap').addClass('d-none');
            $('#singleMoveConfirmYesNo').val('').focus();
            $('#singleMoveConfirmWord').val('');
            $('#singleMoveExecuteBtn').prop('disabled', true).data('subclass-id', subclassID).data('dest-label', destLabel);
            $(this).prop('disabled', true);
        });

        $('#singleMoveConfirmYesNo').on('keyup change', function () {
            const v = ($(this).val() || '').trim().toLowerCase();
            if (v === 'no') {
                $('#singleMoveConfirmPanel').addClass('d-none');
                $('#singleMoveConfirmBtn').prop('disabled', !$('#singleMoveSubclass').val());
                return;
            }
            if (v === 'yes') {
                $('#singleMoveConfirmWordWrap').removeClass('d-none');
            } else {
                $('#singleMoveConfirmWordWrap').addClass('d-none');
            }
            const b = ($('#singleMoveConfirmWord').val() || '').trim().toLowerCase();
            $('#singleMoveExecuteBtn').prop('disabled', !(v === 'yes' && b === 'yes'));
        });

        $('#singleMoveConfirmWord').on('keyup change', function () {
            const a = ($('#singleMoveConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($(this).val() || '').trim().toLowerCase();
            $('#singleMoveExecuteBtn').prop('disabled', !(a === 'yes' && b === 'yes'));
        });

        $('#singleMoveExecuteBtn').on('click', function () {
            const subclassID = $(this).data('subclass-id') || $('#singleMoveSubclass').val();
            if (!singleMoveState.studentID || !singleMoveState.type || !subclassID) return;
            const $btn = $(this).prop('disabled', true).text('Moving...');
            $.ajax({
                url: {!! json_encode(route("move_student_single")) !!},
                type: 'POST',
                data: {
                    _token: {!! json_encode(csrf_token()) !!},
                    studentID: singleMoveState.studentID,
                    new_subclassID: subclassID,
                    type: singleMoveState.type
                },
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).html('Confirm \u0026 Move');
                    if (res.success) {
                        closeStudentModal('singleMoveStudentModal');
                        loadStudents({ page: 1 });
                        Swal.fire({
                            icon: 'success',
                            title: 'Moved',
                            text: res.message || 'Student moved successfully.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Move Failed',
                            text: res.message || 'Move failed',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('Confirm \u0026 Move');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Move failed';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // ===================== EDIT ENROLLMENT (Dropped / Transferred = left school) =====================
        let editEnrollmentState = { studentID: null, action: null };

        function resetEditEnrollmentConfirm() {
            editEnrollmentState.action = null;
            $('.edit-enrollment-action-btn').css({ background: '#fff', color: '#940000' });
            $('#editEnrollmentConfirmPanel').addClass('d-none');
            $('#editEnrollmentConfirmWordWrap').addClass('d-none');
            $('#editEnrollmentConfirmYesNo').val('');
            $('#editEnrollmentConfirmWord').val('');
            $('#editEnrollmentNotes').val('');
            $('#editEnrollmentExecuteBtn').prop('disabled', true).text('Confirm');
        }

        function validateEditEnrollmentConfirm() {
            const a = ($('#editEnrollmentConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($('#editEnrollmentConfirmWord').val() || '').trim().toLowerCase();
            $('#editEnrollmentExecuteBtn').prop('disabled', !(editEnrollmentState.action && a === 'yes' && b === 'yes'));
        }

        $(document).on('click', '.edit-enrollment-status-btn', function () {
            editEnrollmentState.studentID = $(this).data('student-id');
            resetEditEnrollmentConfirm();
            $('#editEnrollmentStudentName').text($(this).data('student-name') || '-');
            $('#editEnrollmentCurrentClass').text($(this).data('current-class') || '-');
            openStudentModal('editEnrollmentStatusModal');
        });

        $(document).on('click', '.edit-enrollment-action-btn', function () {
            const action = $(this).data('action');
            editEnrollmentState.action = action;
            $('.edit-enrollment-action-btn').css({ background: '#fff' });
            $(this).css({ background: '#940000', color: '#fff' });
            const titles = {
                Dropped: 'Mark enrollment as Dropped (expelled / stopped, promotion: Dropout)?',
                Transferred: 'Mark enrollment as Transferred (left school)?',
                Truancy: 'Mark as Truancy (Long-term absentee)?',
                Dropout: 'Mark as Dropout (stopped schooling)?'
            };
            $('#editEnrollmentConfirmTitle').text(titles[action] || ('Confirm ' + action + '?'));
            $('#editEnrollmentConfirmYesNo').val('');
            $('#editEnrollmentConfirmWord').val('');
            $('#editEnrollmentConfirmWordWrap').addClass('d-none');
            $('#editEnrollmentExecuteBtn').prop('disabled', true);
            $('#editEnrollmentConfirmPanel').removeClass('d-none');
            setTimeout(function () { $('#editEnrollmentConfirmYesNo').trigger('focus'); }, 50);
        });

        $('#editEnrollmentConfirmYesNo').on('keyup change', function () {
            const v = ($(this).val() || '').trim().toLowerCase();
            if (v === 'no') {
                resetEditEnrollmentConfirm();
                return;
            }
            if (v === 'yes') {
                $('#editEnrollmentConfirmWordWrap').removeClass('d-none');
            } else {
                $('#editEnrollmentConfirmWordWrap').addClass('d-none');
            }
            validateEditEnrollmentConfirm();
        });

        $('#editEnrollmentConfirmWord').on('keyup change', validateEditEnrollmentConfirm);

        $('#editEnrollmentExecuteBtn').on('click', function () {
            if (!editEnrollmentState.studentID || !editEnrollmentState.action) return;
            const a = ($('#editEnrollmentConfirmYesNo').val() || '').trim().toLowerCase();
            const b = ($('#editEnrollmentConfirmWord').val() || '').trim().toLowerCase();
            if (a !== 'yes' || b !== 'yes') return;

            const $btn = $(this).prop('disabled', true).text('Saving...');
            $.ajax({
                url: {!! json_encode(route("update_enrollment_exit_status")) !!},
                type: 'POST',
                data: {
                    _token: {!! json_encode(csrf_token()) !!},
                    studentID: editEnrollmentState.studentID,
                    action: editEnrollmentState.action,
                    confirm_word: 'yes',
                    notes: ($('#editEnrollmentNotes').val() || '').trim()
                },
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).text('Confirm');
                    if (res.success) {
                        closeStudentModal('editEnrollmentStatusModal');
                        loadStudents({ page: 1 });
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: res.message || 'Enrollment updated.',
                            confirmButtonColor: '#940000'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: res.message || 'Update failed',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text('Confirm');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Update failed';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Show/hide allergies details based on checkbox
        $('#has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#allergiesDetailsContainer').slideDown();
            } else {
                $('#allergiesDetailsContainer').slideUp();
                $('#allergies_details').val('');
            }
        });

        // Show/hide health details for edit form
        $('#edit_has_disability').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editDisabilityDetailsContainer').slideDown();
            } else {
                $('#editDisabilityDetailsContainer').slideUp();
                $('#edit_disability_details').val('');
            }
        });
        
        $('#edit_has_chronic_illness').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editChronicIllnessDetailsContainer').slideDown();
            } else {
                $('#editChronicIllnessDetailsContainer').slideUp();
                $('#edit_chronic_illness_details').val('');
            }
        });
        
        $('#edit_has_allergies').on('change', function() {
            if ($(this).is(':checked')) {
                $('#editAllergiesDetailsContainer').slideDown();
            } else {
                $('#editAllergiesDetailsContainer').slideUp();
                $('#edit_allergies_details').val('');
            }
        });

        // Load subclasses and parents for registration form
        function loadFormData(preSelectSubclassID = null) {
            // Load subclasses
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassID');

                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }

                        subclassSelect.html('<option value="">Choose a class...</option>');
                        
                        // Filter subclasses if preSelectSubclassID is provided (for teacher view)
                        let filteredSubclasses = response.subclasses;
                        if (preSelectSubclassID) {
                            filteredSubclasses = response.subclasses.filter(function(subclass) {
                                return subclass.subclassID == preSelectSubclassID;
                            });
                        }
                        
                        filteredSubclasses.forEach(function(subclass) {
                            // Display display_name (class_name + subclass_name) e.g., "Form Four A"
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            subclassSelect.append('<option value="' + subclass.subclassID + '">' + displayName + '</option>');
                        });

                        // Initialize Select2 for class select with search
                        if (typeof subclassSelect.select2 === 'function') {
                            subclassSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: preSelectSubclassID ? 'Class (Pre-selected)' : 'Search and select a class...',
                                allowClear: !preSelectSubclassID, // Disable clear if pre-selected
                                width: '100%',
                                dropdownParent: $('#addStudentModal')
                            });
                        }
                        
                        // Pre-select subclass if provided
                        if (preSelectSubclassID) {
                            subclassSelect.val(preSelectSubclassID).trigger('change');
                            subclassSelect.prop('disabled', true); // Disable selection for teacher
                            
                            // Trigger event to notify that form is ready
                            $(document).trigger('subclassPreSelected', [preSelectSubclassID]);
                        }
                    }
                }
            });

            // Load parents
            $.ajax({
                url: {!! json_encode(route("get_parents")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let parentSelect = $('#parentID');
                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            // Build full name from first_name, middle_name, last_name
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' '); // Clean up extra spaces
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            parentSelect.append('<option value="' + parent.parentID + '">' + displayText + '</option>');
                        });

                        // Initialize Select2 for parent select with search
                        if (parentSelect.length) {
                            // Destroy existing Select2 if it exists
                            if (parentSelect.hasClass('select2-hidden-accessible')) {
                                parentSelect.select2('destroy');
                            }

                            if (typeof parentSelect.select2 === 'function') {
                                parentSelect.select2({
                                    theme: 'bootstrap-5',
                                    placeholder: 'Search and select a parent...',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: $('#addStudentModal')
                                });
                            }
                        }
                    }
                }
            });

            // Load sponsors
            $.ajax({
                url: {!! json_encode(route("get_sponsors")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let sponsorSelect = $('#sponsor_id');
                        sponsorSelect.html('<option value="">Choose a sponsor...</option>');
                        response.sponsors.forEach(function(sponsor) {
                            sponsorSelect.append('<option value="' + sponsor.sponsorID + '">' + sponsor.sponsor_name + '</option>');
                        });
                    }
                }
            });
        }

        // Handle payment type change
        $('#payment_type').on('change', function() {
            if ($(this).val() === 'sponsor') {
                $('#sponsorSelectContainer').slideDown();
                $('#sponsorshipPercentageContainer').slideDown();
            } else {
                $('#sponsorSelectContainer').slideUp();
                $('#sponsorshipPercentageContainer').slideUp();
                $('#sponsor_id').val('');
                $('#sponsorship_percentage').val('');
            }
        });

        $('#edit_payment_type').on('change', function() {
            if ($(this).val() === 'sponsor') {
                $('#editSponsorSelectContainer').slideDown();
                $('#editSponsorshipPercentageContainer').slideDown();
            } else {
                $('#editSponsorSelectContainer').slideUp();
                $('#editSponsorshipPercentageContainer').slideUp();
                $('#edit_sponsor_id').val('');
                $('#edit_sponsorship_percentage').val('');
            }
        });

        // Grid-only full-width search
        let gridSearchTimeout;
        $('#studentGridSearch').on('input', function () {
            if ($('#studentsDataWrapper').attr('data-active-view') !== 'grid') return;
            studentSearchKeepFocus = true;
            clearTimeout(gridSearchTimeout);
            gridSearchTimeout = setTimeout(function () {
                loadStudents({ page: 1 });
            }, 350);
        }).on('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();
                if ($('#studentsDataWrapper').attr('data-active-view') !== 'grid') return false;
                clearTimeout(gridSearchTimeout);
                studentSearchKeepFocus = true;
                loadStudents({ page: 1 });
                return false;
            }
        });

        $(document).on('click', '.student-view-toggle [data-view]', function () {
            setStudentView($(this).data('view'), true);
            loadStudents({ page: 1, resetPaging: true });
        });

        $(window).on('scroll', function () {
            studentUserScrolled = true;
            maybeLoadMoreGridOnScroll();
        });

        $(window).on('resize', function () {
            clearTimeout(window._studentResizeTimer);
            window._studentResizeTimer = setTimeout(function () {
                // Only auto-switch default when user has not chosen a preference for this device
                try {
                    if (localStorage.getItem(studentUserPrefKey()) !== '1') {
                        setStudentView(studentDefaultView(), false);
                    }
                } catch (e) {}
            }, 200);
        });

        // Initialize default view (mobile=grid, desktop=list)
        setStudentView(studentGetView(), false);

        // Actions dropdown toggle (⋮), row actions
        $(document).on('click', '.student-action-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const $dropdown = $(this).closest('.student-action-dropdown');
            const wasOpen = $dropdown.hasClass('show');
            $('.student-action-dropdown.show').removeClass('show');
            $('#studentPageActionsDropdown').removeClass('show')
                .find('#studentPageActionsToggle').attr('aria-expanded', 'false');
            if (!wasOpen) {
                $dropdown.addClass('show');
                $(this).attr('aria-expanded', 'true');
            } else {
                $(this).attr('aria-expanded', 'false');
            }
        });

        // Header page actions toggle (Manage Students ⋮)
        $(document).on('click', '#studentPageActionsToggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const $dropdown = $('#studentPageActionsDropdown');
            const wasOpen = $dropdown.hasClass('show');
            $('.student-action-dropdown.show').removeClass('show');
            if (!wasOpen) {
                $dropdown.addClass('show');
                $(this).attr('aria-expanded', 'true');
            } else {
                $dropdown.removeClass('show');
                $(this).attr('aria-expanded', 'false');
            }
        });

        $(document).on('click', function () {
            $('.student-action-dropdown.show').removeClass('show')
                .find('.student-action-toggle').attr('aria-expanded', 'false');
            $('#studentPageActionsDropdown').removeClass('show')
                .find('#studentPageActionsToggle').attr('aria-expanded', 'false');
        });

        $(document).on('click', '.student-action-dropdown .dropdown-menu, .student-page-actions-menu', function (e) {
            e.stopPropagation();
        });

        $(document).on('click', '.student-action-dropdown .dropdown-item, .student-page-actions-menu .dropdown-item', function () {
            $(this).closest('.student-action-dropdown, .student-page-actions-dropdown').removeClass('show')
                .find('.student-action-toggle, #studentPageActionsToggle').attr('aria-expanded', 'false');
        });

        // Keep legacy name used by other handlers
        function renderStudentWidgets(students, status) {
            renderStudentsUI(students || currentStudentsData, status || currentStatus);
        }


        // Function to load statistics
        function loadStatistics() {
            // Get status from filter
            let status = $('#statusFilter').val() || 'Active';
            let promotionStatus = $('#promotionFilter').val() || '';
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            // Show loading state on statistics
            $('#statTotalStudents, #statMaleCount, #statFemaleCount, #statGoodHealth, #statBadHealth')
                .html('<i class="bi bi-hourglass-split"></i>');

            $.ajax({
                url: {!! json_encode(route("get_student_statistics")) !!},
                type: 'GET',
                data: {
                    status: status,
                    promotion_status: promotionStatus,
                    classID: classID,
                    subclassID: subclassID,
                    gender: gender,
                    health: health
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.statistics) {
                        $('#statTotalStudents').text(response.statistics.total_students || 0);
                        $('#statMaleCount').text(response.statistics.male_count || 0);
                        $('#statFemaleCount').text(response.statistics.female_count || 0);
                        $('#statGoodHealth').text(response.statistics.good_health_count || 0);
                        $('#statBadHealth').text(response.statistics.bad_health_count || 0);
                    } else {
                        // Reset to 0 if error
                        $('#statTotalStudents').text('0');
                        $('#statMaleCount').text('0');
                        $('#statFemaleCount').text('0');
                        $('#statGoodHealth').text('0');
                        $('#statBadHealth').text('0');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load statistics:', xhr);
                    // Reset to 0 on error
                    $('#statTotalStudents').text('0');
                    $('#statMaleCount').text('0');
                    $('#statFemaleCount').text('0');
                    $('#statGoodHealth').text('0');
                    $('#statBadHealth').text('0');
                }
            });
        }

        // Function to reload data and statistics
        function reloadData() {
            loadStudents();
            loadStatistics();
            refreshDuplicateNameAlert();
        }

        // Filter change handlers
        let filterTimeout;
        function handleFilterChange() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                reloadData();
            }, 300);
        }

        // Status filter
        $('#statusFilter').on('change', function() {
            let status = $(this).val() || 'Active';
            currentStatus = status;
            clearNotEnrolledSelection();
            updateNotEnrolledBulkUi();
            reloadData();
        });

        // Promotion status filter
        $('#promotionFilter').on('change', function() {
            handleFilterChange();
        });

        // Academic year is controlled from the global header selector

        // Class filter
        $('#classFilter').on('change', function() {
            let classID = $(this).val();
            
            // Reload subclasses based on selected class
            if (classID) {
                loadSubclassesForFilter(classID);
            } else {
                // Reset subclass filter and load all subclasses
                $('#subclassFilter').html('<option value="">-- All Subclasses --</option>');
                loadAllSubclassesForFilter();
            }
            
            handleFilterChange();
        });

        // Subclass, Gender, Health filters
        $('#subclassFilter, #genderFilter, #healthFilter').on('change', function() {
            handleFilterChange();
        });

        // Not enrolled bulk selection
        $(document).on('change', '.not-enrolled-student-check', function () {
            const id = parseInt($(this).val(), 10);
            if ($(this).prop('checked')) {
                notEnrolledSelectedIds.add(id);
            } else {
                notEnrolledSelectedIds.delete(id);
            }
            updateNotEnrolledSelectionCount();
            syncNotEnrolledPageSelectAllState();
        });

        $('#notEnrolledSelectPageCb').on('change', function () {
            const checked = $(this).prop('checked');
            $('.not-enrolled-student-check:visible').each(function () {
                const id = parseInt($(this).val(), 10);
                $(this).prop('checked', checked);
                if (checked) {
                    notEnrolledSelectedIds.add(id);
                } else {
                    notEnrolledSelectedIds.delete(id);
                }
            });
            updateNotEnrolledSelectionCount();
        });

        $('#notEnrolledSelectAllBtn').on('click', function () {
            const $btn = $(this).prop('disabled', true);
            const params = Object.assign(getStudentFilterParams({ status: 'NotEnrolled' }), {
                ids_only: 1,
                paginate: 0
            });

            $.getJSON({!! json_encode(route('get_students_list')) !!}, params)
                .done(function (res) {
                    if (!res.success || !res.studentIDs || !res.studentIDs.length) {
                        Swal.fire({
                            icon: 'info',
                            title: 'No students',
                            text: 'No not-enrolled students found.',
                            confirmButtonColor: '#940000'
                        });
                        return;
                    }

                    notEnrolledSelectedIds.clear();
                    res.studentIDs.forEach(function (id) {
                        notEnrolledSelectedIds.add(parseInt(id, 10));
                    });

                    if (res.truncated) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Partial selection',
                            text: 'Selected ' + res.studentIDs.length + ' of ' + res.total + ' students (limit reached).',
                            confirmButtonColor: '#940000'
                        });
                    }

                    syncNotEnrolledCheckboxes();
                    updateNotEnrolledSelectionCount();

                    if ($('#studentsDataWrapper').attr('data-active-view') === 'grid') {
                        resetAndRenderGrid();
                    } else {
                        reloadStudentsDataTable(false);
                    }
                })
                .fail(function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Could not load student list for selection.' });
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        });

        $('#notEnrolledClearSelectionBtn').on('click', function () {
            clearNotEnrolledSelection();
            if ($('#studentsDataWrapper').attr('data-active-view') === 'grid') {
                resetAndRenderGrid();
            } else {
                reloadStudentsDataTable(false);
            }
        });

        $('#deleteSelectedNotEnrolledBtn').on('click', function () {
            const ids = Array.from(notEnrolledSelectedIds);
            if (!ids.length) {
                return;
            }

            Swal.fire({
                title: 'Delete selected students?',
                html: 'Delete <strong>' + ids.length + '</strong> selected not-enrolled student(s)?<br><span class="text-danger">This cannot be undone.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete selected',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: 'Deleting...',
                    html: 'Please wait.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () { Swal.showLoading(); }
                });

                $.ajax({
                    url: {!! json_encode(route('delete_selected_not_enrolled_students')) !!},
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        studentIDs: ids
                    },
                    dataType: 'json',
                    timeout: 600000,
                    success: function (response) {
                        if (response.success) {
                            clearNotEnrolledSelection();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: response.message || ('Deleted ' + (response.deleted || 0) + ' student(s)'),
                                confirmButtonColor: '#940000'
                            }).then(function () {
                                reloadData();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete failed',
                                text: response.message || 'No students were deleted'
                            });
                        }
                    },
                    error: function (xhr, textStatus) {
                        let msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed';
                        if (textStatus === 'timeout') {
                            msg = 'Delete timed out. Refresh and check the Not Enrolled list.';
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });
        });

        // Enroll Now (not-enrolled students only)
        $(document).on('click', '.enroll-student-now-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const studentID = $(this).data('student-id');
            const studentName = $(this).data('student-name') || '';
            if (typeof openEnrollStudentModal === 'function') {
                openEnrollStudentModal({ studentID: studentID, studentName: studentName });
            }
        });

        // View Student Details
        $(document).on('click', '.view-student-btn', function() {
            let studentID = $(this).data('student-id');
            let currentStudentID = studentID; // Store for use in other functions

            $.ajax({
                url: {!! json_encode(route("get_student_details", ":id")) !!}.replace(':id', studentID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let student = response.student;
                        
                        // Create tab structure (Details | Enrollment | Report)
                        let html = '<ul class="nav nav-tabs mb-3" id="studentDetailsTabs" role="tablist" style="border-radius: 0;">';
                        html += '<li class="nav-item" role="presentation">';
                        html += '<button class="nav-link active" id="particulars-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#particulars-pane" data-bs-target="#particulars-pane" type="button" role="tab" aria-controls="particulars-pane" aria-selected="true">';
                        html += '<i class="bi bi-person-vcard"></i> Student Details';
                        html += '</button>';
                        html += '</li>';
                        html += '<li class="nav-item" role="presentation">';
                        html += '<button class="nav-link" id="enrollment-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#enrollment-pane" data-bs-target="#enrollment-pane" type="button" role="tab" aria-controls="enrollment-pane" aria-selected="false">';
                        html += '<i class="bi bi-journal-bookmark"></i> Enrollment';
                        html += '</button>';
                        html += '</li>';
                        html += '<li class="nav-item" role="presentation">';
                        html += '<button class="nav-link" id="report-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#report-pane" data-bs-target="#report-pane" type="button" role="tab" aria-controls="report-pane" aria-selected="false">';
                        html += '<i class="bi bi-file-earmark-bar-graph"></i> Report';
                        html += '</button>';
                        html += '</li>';
                        html += '</ul>';
                        
                        html += '<div class="tab-content" id="studentDetailsTabContent">';
                        
                        // Tab 1: Student Details
                        html += '<div class="tab-pane fade show active" id="particulars-pane" role="tabpanel" aria-labelledby="particulars-tab">';
                        html += '<div class="school-details-card" style="border-radius: 0;">';
                        html += '<div class="school-header">';
                        html += '<div class="d-flex align-items-center">';
                        html += '<div class="school-logo-preview me-3">';
                        html += studentPhotoImgHtml(student, '', student.full_name);
                        html += '</div>';
                        html += '<div>';
                        // Check for bad health conditions - support both boolean and integer values
                        let hasBadHealth = false;
                        let healthConditions = [];
                        
                        // Check is_disabled (can be true, 1, or "1")
                        if (student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Disabled');
                        }
                        
                        // Check has_epilepsy (can be true, 1, or "1")
                        if (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Epilepsy/Seizure Disorder');
                        }
                        
                        // Check has_allergies (can be true, 1, or "1")
                        if (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Allergies');
                        }
                        
                        // Check has_disability (can be true, 1, or "1")
                        if (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") {
                            hasBadHealth = true;
                            if (!healthConditions.includes('Disabled')) {
                                healthConditions.push('Disability');
                            }
                        }
                        
                        // Check has_chronic_illness (can be true, 1, or "1")
                        if (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1") {
                            hasBadHealth = true;
                            healthConditions.push('Chronic Illness');
                        }
                        
                        let healthAlertIcon = '';
                        if (hasBadHealth) {
                            healthAlertIcon = ' <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.2rem;" title="Health Condition Alert - This student has health conditions: ' + healthConditions.join(', ') + '"></i>';
                        }
                        
                        html += '<h3 class="school-title">' + student.full_name + healthAlertIcon + '</h3>';
                        html += '<small class="text-muted">Admission: ' + student.admission_number + '</small>';
                        html += '</div>';
                        html += '</div>';

                        // Enrollment status for header-selected academic year
                        var enrStatus = student.enrollment_status || null;
                        var promoStatus = student.promotion_status || null;
                        var enrYear = student.enrollment_year_name || '';
                        if (enrStatus) {
                            var enrBadgeClass = enrollmentStatusBadgeClass(enrStatus);
                            var enrLabel = escapeHtmlStudent(enrStatus);
                            if (enrYear) {
                                enrLabel += ' · ' + escapeHtmlStudent(enrYear);
                            }
                            html += '<span class="badge text-white ' + enrBadgeClass + '" title="Enrollment status for selected academic year">' +
                                '<i class="bi bi-journal-check"></i> ' + enrLabel + '</span>';
                        } else {
                            html += '<span class="badge bg-' + (student.status === 'Active' ? 'success' : 'secondary') + '">' +
                                escapeHtmlStudent(student.status || 'N/A') + '</span>';
                        }
                        if (promoStatus) {
                            html += ' <span class="badge text-white ' + promotionStatusBadgeClass(promoStatus) + '" title="Promotion status for selected academic year">' +
                                '<i class="bi bi-arrow-up-right"></i> ' + escapeHtmlStudent(promotionStatusLabel(promoStatus)) + '</span>';
                        }
                        html += '</div>';

                        html += '<div class="school-info-grid">';
                        html += '<div class="info-item"><i class="bi bi-gender-ambiguous"></i><div class="info-item-content"><div class="info-item-label">Gender</div><div class="info-item-value">' + student.gender + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-calendar-event"></i><div class="info-item-content"><div class="info-item-label">Date of Birth</div><div class="info-item-value">' + student.date_of_birth + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-calendar-check"></i><div class="info-item-content"><div class="info-item-label">Admission Date</div><div class="info-item-value">' + student.admission_date + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-book"></i><div class="info-item-content"><div class="info-item-label">Class' +
                            (enrYear ? ' <small class="text-muted">(' + escapeHtmlStudent(enrYear) + ')</small>' : '') +
                            '</div><div class="info-item-value">' + escapeHtmlStudent(student.class || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-journal-bookmark"></i><div class="info-item-content"><div class="info-item-label">Enrollment Status' +
                            (enrYear ? ' <small class="text-muted">(' + escapeHtmlStudent(enrYear) + ')</small>' : '') +
                            '</div><div class="info-item-value">' + escapeHtmlStudent(enrStatus || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-arrow-up-right"></i><div class="info-item-content"><div class="info-item-label">Promotion Status' +
                            (enrYear ? ' <small class="text-muted">(' + escapeHtmlStudent(enrYear) + ')</small>' : '') +
                            '</div><div class="info-item-value">' + escapeHtmlStudent(promotionStatusLabel(promoStatus) || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-person-badge"></i><div class="info-item-content"><div class="info-item-label">Student Record Status</div><div class="info-item-value">' + escapeHtmlStudent(student.status || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-geo-alt"></i><div class="info-item-content"><div class="info-item-label">Address</div><div class="info-item-value">' + student.address + '</div></div></div>';

                        // Health Information Section - Rebuild health conditions array
                        let healthConditionsList = [];
                        
                        // Check is_disabled (can be true, 1, or "1")
                        if (student.is_disabled === true || student.is_disabled == 1 || student.is_disabled === "1") {
                            let disabledText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Disabled</span>';
                            if (student.disability_details) {
                                disabledText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.disability_details + '</small>';
                            }
                            healthConditionsList.push(disabledText);
                        }
                        
                        // Check has_disability (can be true, 1, or "1")
                        if (student.has_disability === true || student.has_disability == 1 || student.has_disability === "1") {
                            let disabilityText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Disability</span>';
                            if (student.disability_details) {
                                disabilityText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.disability_details + '</small>';
                            }
                            healthConditionsList.push(disabilityText);
                        }
                        
                        // Check has_epilepsy (can be true, 1, or "1")
                        if (student.has_epilepsy === true || student.has_epilepsy == 1 || student.has_epilepsy === "1") {
                            healthConditionsList.push('<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Epilepsy/Seizure Disorder</span>');
                        }
                        
                        // Check has_allergies (can be true, 1, or "1")
                        if (student.has_allergies === true || student.has_allergies == 1 || student.has_allergies === "1") {
                            let allergiesText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Allergies</span>';
                            if (student.allergies_details) {
                                allergiesText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.allergies_details + '</small>';
                            }
                            healthConditionsList.push(allergiesText);
                        }
                        
                        // Check has_chronic_illness (can be true, 1, or "1")
                        if (student.has_chronic_illness === true || student.has_chronic_illness == 1 || student.has_chronic_illness === "1") {
                            let chronicText = '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> Chronic Illness</span>';
                            if (student.chronic_illness_details) {
                                chronicText += '<br><small class="text-danger mt-1 d-block">Details: ' + student.chronic_illness_details + '</small>';
                            }
                            healthConditionsList.push(chronicText);
                        }
                        
                        // Check general_health_condition - separate good health from bad health
                        let generalHealthCondition = '';
                        let isGoodHealth = false;
                        if (student.general_health_condition && student.general_health_condition !== 'N/A' && student.general_health_condition.trim() !== '') {
                            let healthConditionLower = student.general_health_condition.toLowerCase().trim();
                            // Check if it's a positive/good health condition
                            if (healthConditionLower === 'good' || healthConditionLower === 'excellent' || healthConditionLower === 'fine' || healthConditionLower === 'healthy') {
                                isGoodHealth = true;
                                generalHealthCondition = student.general_health_condition;
                            } else {
                                // Bad health condition - add to alert list
                            healthConditionsList.push('<span class="badge bg-warning text-dark"><i class="bi bi-info-circle-fill"></i> General Health: ' + student.general_health_condition + '</span>');
                            }
                        }
                        
                        // Display health conditions alert if there are bad conditions
                        if (healthConditionsList.length > 0) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #fff5f5; border: 2px solid #dc3545; border-radius: 0; padding: 15px;">';
                            html += '<i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="color: #dc3545; font-weight: 700; font-size: 0.9rem;">HEALTH CONDITIONS ALERT</div>';
                            html += '<div class="info-item-value mt-2">' + healthConditionsList.join('<br>') + '</div>';
                            html += '</div>';
                            html += '</div>';
                        } else {
                            // Display good health status (green) ONLY if there are NO bad health conditions
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f0f9f0; border: 2px solid #28a745; border-radius: 0; padding: 15px;">';
                            html += '<i class="bi bi-heart-pulse-fill text-success" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="color: #28a745; font-weight: 700; font-size: 0.9rem;">HEALTH STATUS</div>';
                            if (isGoodHealth && generalHealthCondition) {
                                html += '<div class="info-item-value mt-2"><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> ' + generalHealthCondition + '</span></div>';
                            } else {
                                html += '<div class="info-item-value mt-2"><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Good Health</span></div>';
                            }
                            html += '</div>';
                            html += '</div>';
                        }

                        // Parent Information
                        if (student.parent) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-person-heart text-primary-custom" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">PARENT INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-6"><div class="info-item-label">Name</div><div class="info-item-value">' + student.parent.full_name + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Phone</div><div class="info-item-value">' + student.parent.phone + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Email</div><div class="info-item-value">' + student.parent.email + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Occupation</div><div class="info-item-value">' + student.parent.occupation + '</div></div>';
                            if (student.parent.relationship) {
                                html += '<div class="col-md-6"><div class="info-item-label">Relationship</div><div class="info-item-value">' + student.parent.relationship + '</div></div>';
                            }
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Sponsorship Information
                        if (student.sponsor_id) {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #fff9e6; padding: 15px; border: 1px solid #ffd633;">';
                            html += '<i class="bi bi-handshake-fill text-warning" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #856404;">SPONSORSHIP INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-4"><div class="info-item-label">Sponsor</div><div class="info-item-value">' + (student.sponsor_name || 'Assigned') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Sponsor ID</div><div class="info-item-value">' + student.sponsor_id + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Percentage Cover</div><div class="info-item-value">' + (student.sponsorship_percentage || 0) + '%</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Emergency Contact (Next of Kin)
                        if (student.emergency_contact_name && student.emergency_contact_name !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-telephone-forward text-warning" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">EMERGENCY CONTACT (NEXT OF KIN)</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-4"><div class="info-item-label">Name</div><div class="info-item-value">' + (student.emergency_contact_name || 'N/A') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Relationship</div><div class="info-item-value">' + (student.emergency_contact_relationship || 'N/A') + '</div></div>';
                            html += '<div class="col-md-4"><div class="info-item-label">Phone</div><div class="info-item-value">' + (student.emergency_contact_phone || 'N/A') + '</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Registration Information
                        if (student.registering_officer_name && student.registering_officer_name !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6;">';
                            html += '<i class="bi bi-person-check text-info" style="font-size: 1.5rem;"></i>';
                            html += '<div class="info-item-content">';
                            html += '<div class="info-item-label" style="font-weight: 700; font-size: 0.9rem; color: #940000;">REGISTRATION INFORMATION</div>';
                            html += '<div class="row mt-2">';
                            html += '<div class="col-md-6"><div class="info-item-label">Registered By</div><div class="info-item-value">' + (student.registering_officer_name || 'N/A') + '</div></div>';
                            html += '<div class="col-md-6"><div class="info-item-label">Registration Date</div><div class="info-item-value">' + (student.declaration_date || 'N/A') + '</div></div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        }
                        
                        // Additional Student Details
                        html += '<div class="info-item"><i class="bi bi-card-text"></i><div class="info-item-content"><div class="info-item-label">Birth Certificate Number</div><div class="info-item-value">' + (student.birth_certificate_number || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-heart"></i><div class="info-item-content"><div class="info-item-label">Religion</div><div class="info-item-value">' + (student.religion || 'N/A') + '</div></div></div>';
                        html += '<div class="info-item"><i class="bi bi-globe"></i><div class="info-item-content"><div class="info-item-label">Nationality</div><div class="info-item-value">' + (student.nationality || 'N/A') + '</div></div></div>';
                        if (student.immunization_details && student.immunization_details !== 'N/A') {
                            html += '<div class="info-item" style="grid-column: 1 / -1;"><i class="bi bi-shield-check"></i><div class="info-item-content"><div class="info-item-label">Immunization Details</div><div class="info-item-value">' + student.immunization_details + '</div></div></div>';
                        }

                        html += '</div></div>'; // Close school-details-card
                        html += '</div>'; // Close particulars-pane
                        
                        // Tab 2: Enrollment
                        html += '<div class="tab-pane fade" id="enrollment-pane" role="tabpanel" aria-labelledby="enrollment-tab">';
                        html += '<div class="p-3 p-md-4" style="border-radius: 0;">';
                        html += '<div class="mb-3">';
                        html += '<label class="form-label fw-bold mb-2" for="enrollmentYearSelector"><i class="bi bi-calendar3"></i> Academic Year</label>';
                        html += '<select class="form-control enrollment-year-select" id="enrollmentYearSelector" data-student-id="' + studentID + '">';
                        html += '<option value="all" selected>All</option>';
                        html += '</select>';
                        html += '</div>';
                        html += '<div id="enrollmentClassWidgets">';
                        html += '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 mb-0">Loading enrollments...</p></div>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>'; // Close enrollment-pane

                        // Tab 3: Report (placeholder)
                        html += '<div class="tab-pane fade" id="report-pane" role="tabpanel" aria-labelledby="report-tab">';
                        html += '<div class="p-4 text-center text-muted" style="border-radius: 0;">';
                        html += '<i class="bi bi-file-earmark-bar-graph" style="font-size: 2.5rem; color: #940000; opacity: 0.7;"></i>';
                        html += '<p class="mt-3 mb-1 font-weight-bold" style="color: #495057;">Reports</p>';
                        html += '<p class="mb-0 small">Coming soon. This section will be configured later.</p>';
                        html += '</div>';
                        html += '</div>'; // Close report-pane
                        
                        html += '</div>'; // Close tab-content

                        $('#studentDetailsContent').html(html);
                        showModal('viewStudentModal');
                        
                        try {
                            setTimeout(function() {
                                loadStudentEnrollmentHistory(studentID);
                            }, 50);
                        } catch (e) {
                            console.error('Error loading enrollment history:', e);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading student details:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', status);
                    let errorMessage = 'Failed to load student details';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            let errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            // Not JSON, use response text if short
                            if (xhr.responseText.length < 200) {
                                errorMessage = xhr.responseText;
                            }
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        footer: 'Status: ' + xhr.status + ' | Error: ' + error
                    });
                }
            });
        });

        // Edit Student Button Click
        $(document).on('click', '.edit-student-btn', function() {
            let studentID = $(this).data('student-id');

            // Load student data
            $.ajax({
                url: {!! json_encode(route("get_student", ":id")) !!}.replace(':id', studentID),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.student) {
                        let student = response.student;

                        // Set form values
                        $('#editStudentID').val(student.studentID);
                        $('#edit_first_name').val(student.first_name || '');
                        $('#edit_middle_name').val(student.middle_name || '');
                        $('#edit_last_name').val(student.last_name || '');
                        $('#edit_gender').val(student.gender || '');
                        $('#edit_date_of_birth').val(student.date_of_birth || '');
                        $('#edit_admission_date').val(student.admission_date || '');
                        $('#edit_admission_number').val(student.admission_number || '');
                        $('#edit_address').val(student.address || '');
                        $('#edit_status').val(student.status || 'Active');
                        $('#edit_parentID').val(student.parentID || '');
                        $('#edit_subclassID').val(student.subclassID || '');

                        // Set additional particulars
                        $('#edit_birth_certificate_number').val(student.birth_certificate_number || '');
                        $('#edit_religion').val(student.religion || '');
                        $('#edit_nationality').val(student.nationality || '');
                        
                        // Set health information fields
                        $('#edit_general_health_condition').val(student.general_health_condition || '');
                        $('#edit_has_disability').prop('checked', student.has_disability == 1 || student.has_disability === true);
                        $('#edit_disability_details').val(student.disability_details || '');
                        $('#edit_has_chronic_illness').prop('checked', student.has_chronic_illness == 1 || student.has_chronic_illness === true);
                        $('#edit_chronic_illness_details').val(student.chronic_illness_details || '');
                        $('#edit_immunization_details').val(student.immunization_details || '');
                        $('#edit_is_disabled').prop('checked', student.is_disabled == 1 || student.is_disabled === true);
                        $('#edit_has_epilepsy').prop('checked', student.has_epilepsy == 1 || student.has_epilepsy === true);
                        $('#edit_has_allergies').prop('checked', student.has_allergies == 1 || student.has_allergies === true);
                        $('#edit_allergies_details').val(student.allergies_details || '');
                        
                        $('#edit_emergency_contact_name').val(student.emergency_contact_name || '');
                        $('#edit_emergency_contact_relationship').val(student.emergency_contact_relationship || '');
                        $('#edit_emergency_contact_phone').val(student.emergency_contact_phone || '');
                        
                        // Official Use fields
                        $('#edit_declaration_date').val(student.declaration_date || '');
                        $('#edit_registering_officer_name').val(student.registering_officer_name || '');
                        $('#edit_registering_officer_title').val(student.registering_officer_title || '');
                        
                        // Set sponsorship fields
                        if (student.sponsor_id) {
                            $('#edit_payment_type').val('sponsor').trigger('change');
                            window.pendingEditSponsorID = student.sponsor_id; // Store for after sponsors load
                            $('#edit_sponsor_id').val(student.sponsor_id);
                            $('#edit_sponsorship_percentage').val(student.sponsorship_percentage);
                            $('#editSponsorSelectContainer').show();
                            $('#editSponsorshipPercentageContainer').show();
                        } else {
                            $('#edit_payment_type').val('own').trigger('change');
                            window.pendingEditSponsorID = null;
                            $('#edit_sponsor_id').val('');
                            $('#edit_sponsorship_percentage').val('');
                            $('#editSponsorSelectContainer').hide();
                            $('#editSponsorshipPercentageContainer').hide();
                        }

                        // Show/hide details containers based on checkboxes
                        if (student.has_disability == 1 || student.has_disability === true) {
                            $('#editDisabilityDetailsContainer').show();
                        } else {
                            $('#editDisabilityDetailsContainer').hide();
                        }
                        
                        if (student.has_chronic_illness == 1 || student.has_chronic_illness === true) {
                            $('#editChronicIllnessDetailsContainer').show();
                        } else {
                            $('#editChronicIllnessDetailsContainer').hide();
                        }
                        
                        if (student.has_allergies == 1 || student.has_allergies === true) {
                            $('#editAllergiesDetailsContainer').show();
                        } else {
                            $('#editAllergiesDetailsContainer').hide();
                        }

                        // Show photo preview
                        let photoPreview = '';
                        if (student.photo) {
                            photoPreview = studentPhotoImgHtml(student, 'img-fluid rounded', 'Current Photo')
                                .replace('class="img-fluid rounded"', 'class="img-fluid rounded" style="max-width: 150px; max-height: 150px; border: 2px solid #e9ecef;"');
                        }
                        $('#editPhotoPreview').html(photoPreview);

                        // Load subclasses and parents
                        loadEditFormData(student.subclassID, student.parentID);

                        // Show modal
                        showModal('editStudentModal');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load student data'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load student data'
                    });
                }
            });
        });

        // Load form data for edit modal
        function loadEditFormData(targetSubclassID = null, targetParentID = null) {
            // Load subclasses
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#edit_subclassID');
                        
                        // Destroy existing Select2 if it exists
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            subclassSelect.select2('destroy');
                        }

                        subclassSelect.html('<option value="">Choose a class...</option>');
                        response.subclasses.forEach(function(subclass) {
                            const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                            let selected = (subclass.subclassID == targetSubclassID) ? 'selected' : '';
                            subclassSelect.append('<option value="' + subclass.subclassID + '" ' + selected + '>' + displayName + '</option>');
                        });

                        if (typeof subclassSelect.select2 === 'function') {
                            subclassSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search and select a class...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#editStudentModal')
                            });
                        }

                        if (targetSubclassID) {
                            subclassSelect.val(targetSubclassID).trigger('change');
                        }
                    }
                }
            });

            // Load parents
            $.ajax({
                url: {!! json_encode(route("get_parents")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let parentSelect = $('#edit_parentID');
                        
                        if (parentSelect.hasClass('select2-hidden-accessible')) {
                            parentSelect.select2('destroy');
                        }

                        parentSelect.html('<option value="">Choose a parent...</option>');
                        response.parents.forEach(function(parent) {
                            let fullName = (parent.first_name || '') + ' ' + (parent.middle_name || '') + ' ' + (parent.last_name || '');
                            fullName = fullName.trim().replace(/\s+/g, ' ');
                            let displayText = fullName + (parent.phone ? ' (' + parent.phone + ')' : '');
                            let selected = (parent.parentID == targetParentID) ? 'selected' : '';
                            parentSelect.append('<option value="' + parent.parentID + '" ' + selected + '>' + displayText + '</option>');
                        });

                        if (typeof parentSelect.select2 === 'function') {
                            parentSelect.select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search and select a parent...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#editStudentModal')
                            });
                        }

                        if (targetParentID) {
                            parentSelect.val(targetParentID).trigger('change');
                        }
                    }
                }
            });

            // Load sponsors for edit modal
            $.ajax({
                url: {!! json_encode(route("get_sponsors")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let sponsorSelect = $('#edit_sponsor_id');
                        sponsorSelect.html('<option value="">Choose a sponsor...</option>');
                        response.sponsors.forEach(function(sponsor) {
                            sponsorSelect.append('<option value="' + sponsor.sponsorID + '">' + sponsor.sponsor_name + '</option>');
                        });
                        
                        // If we have a pending sponsor ID, set it now
                        if (window.pendingEditSponsorID) {
                            sponsorSelect.val(window.pendingEditSponsorID);
                            window.pendingEditSponsorID = null;
                        }
                    }
                }
            });
        }


        // Generate ID Card Button Click
        $(document).on('click', '.generate-id-btn', function() {
            let studentID = $(this).data('student-id');
            $('#downloadIdCardBtn').data('student-id', studentID);

            if (!studentID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Student ID not found'
                });
                return;
            }

            // Show loading
            $('#studentIdCardContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading ID Card...</p></div>');
            showModal('studentIdCardModal');

            // Load student details and school details
            $.when(
                $.ajax({
                    url: {!! json_encode(route("get_student_details", ":id")) !!}.replace(':id', studentID),
                    type: 'GET',
                    dataType: 'json'
                }),
                $.ajax({
                    url: {!! json_encode(route("get_school_details")) !!},
                    type: 'GET',
                    dataType: 'json'
                })
            ).done(function(studentResponse, schoolResponse) {
                if (studentResponse[0].success && schoolResponse[0].success) {
                    let student = studentResponse[0].student;
                    let school = schoolResponse[0].school;
                    
                    // Check if required data exists
                    if (!student || !school) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Missing student or school data'
                        });
                        hideModal('studentIdCardModal');
                        return;
                    }
                    let cardColor = $('#idCardColorPicker').val() || '#940000';

                    // Generate barcode value (using admission number)
                    let barcodeValue = student.admission_number || student.studentID.toString();

                    // Build ID Card HTML with dynamic color
                    let idCardHtml = '<div class="student-id-card" data-card-color="' + cardColor + '" style="border-color: ' + cardColor + '; --card-color: ' + cardColor + ';">' +
                        '<div class="id-card-header" style="border-bottom-color: ' + cardColor + ';">' +
                        '<div class="id-card-school-logo">';

                    if (school.school_logo) {
                        idCardHtml += '<img src="' + school.school_logo + '" alt="School Logo">';
                    } else {
                        idCardHtml += '<i class="bi bi-building" style="font-size: 30px; color: #6c757d;"></i>';
                    }

                    idCardHtml += '</div>' +
                        '<div class="id-card-school-info">' +
                        '<h4 class="id-card-school-name" style="color: ' + cardColor + ';">' + school.school_name + '</h4>' +
                        '<p class="id-card-school-reg">Reg. No: ' + (school.registration_number || 'N/A') + '</p>' +
                        '</div>' +
                        '</div>' +
                        '<div class="id-card-body">' +
                        '<div class="id-card-photo-section">' +
                        '<div class="id-card-photo" style="border-color: ' + cardColor + ';">' +
                        studentPhotoImgHtml(student, '', student.full_name) +
                        '</div>' +
                        '</div>' +
                        '<div class="id-card-details">' +
                        '<h3 class="id-card-title">Student Identity Card</h3>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Name:</span>' +
                        '<span class="id-card-info-value">' + student.full_name + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Admission No:</span>' +
                        '<span class="id-card-info-value">' + student.admission_number + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Class:</span>' +
                        '<span class="id-card-info-value">' + student.class + '</span>' +
                        '</div>' +
                        '<div class="id-card-info-row">' +
                        '<span class="id-card-info-label">Gender:</span>' +
                        '<span class="id-card-info-value">' + student.gender + '</span>' +
                        '</div>';

                    // Extract only class name (not subclass name)
                    let className = 'N/A';
                    if (student.class && student.class !== 'N/A') {
                        // Split by space and take first part (class name)
                        let classParts = student.class.split(' ');
                        className = classParts[0] || student.class;
                    }

                    idCardHtml += '</div>' +
                        '</div>' +
                        '<div class="id-card-footer" style="border-top-color: ' + cardColor + ';">' +
                        '<p class="id-card-footer-text">This card is the property of ' + school.school_name + '</p>' +
                        '</div>' +
                        '</div>';

                    // Update class value in the HTML
                    idCardHtml = idCardHtml.replace(
                        '<span class="id-card-info-value">' + student.class + '</span>',
                        '<span class="id-card-info-value">' + className + '</span>'
                    );

                    $('#studentIdCardContent').html(idCardHtml);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load student or school details'
                    });
                    hideModal('studentIdCardModal');
                }
            }).fail(function(xhr, status, error) {
                console.error('Error loading ID card:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', status);
                let errorMessage = 'Failed to load ID card data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        if (xhr.responseText.length < 200) {
                            errorMessage = xhr.responseText;
                        }
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    footer: 'Status: ' + xhr.status + ' | Error: ' + error
                });
                hideModal('studentIdCardModal');
            });
        });

        // Color Picker Change Handler
        $('#idCardColorPicker').on('change', function() {
            let newColor = $(this).val();
            let idCard = $('.student-id-card');
            if (idCard.length) {
                idCard.css({
                    'border-color': newColor
                });
                idCard.find('.id-card-header').css('border-bottom-color', newColor);
                idCard.find('.id-card-school-name').css('color', newColor);
                idCard.find('.id-card-photo').css('border-color', newColor);
                idCard.find('.id-card-footer').css('border-top-color', newColor);
                idCard.attr('data-card-color', newColor);

                // Update ::before pseudo-element background
                idCard[0].style.setProperty('--card-color', newColor);
            }
        });

        // Print ID Card
        $('#printIdCardBtn').on('click', function() {
            window.print();
        });

        // Download ID Card (as PDF via Backend)
        $('#downloadIdCardBtn').on('click', function() {
            let studentID = $(this).data('student-id');
            if (!studentID) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Student ID not found'
                });
                return;
            }

            let cardColor = $('#idCardColorPicker').val() || '#940000';
            
            Swal.fire({
                title: 'Generating PDF...',
                html: 'Please wait while your ID card is being prepared.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Redirect to PDF download route
            window.location.href = `/admin/export-student-id-card-pdf/${studentID}?card_color=${encodeURIComponent(cardColor)}`;

            // Close loading alert after redirection starts
            setTimeout(function() {
                Swal.close();
            }, 2000);
        });

        // Register Student to Device Button Click (similar to sample project)
        $(document).on('click', '.register-student-to-device-btn', function() {
            let studentId = $(this).data('student-id');
            let studentName = $(this).data('student-name');
            let fingerprintId = $(this).data('fingerprint-id');

            // Show warning about firmware compatibility issue (similar to sample project)
            const warning = `⚠️ DIRECT REGISTRATION WARNING

Your device (UF200-S firmware 6.60) may have firmware compatibility issues with direct registration.

✅ RECOMMENDED METHOD:
1. Register student directly on device (User Management → Add User)
2. Then click "Sync Users from Device" button
3. Student will appear automatically!

Would you like to try direct registration anyway, or use the manual method?`;

            Swal.fire({
                title: 'Register Student to Device?',
                html: warning,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Try Direct Registration',
                cancelButtonText: 'Use Manual Method',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ask for device IP and port
                    Swal.fire({
                        title: 'Device Settings',
                        html: '<input id="swal-ip" class="swal2-input" placeholder="Device IP (e.g., 192.168.100.108)" value="192.168.100.108">' +
                              '<input id="swal-port" class="swal2-input" placeholder="Port (e.g., 4370)" value="4370">',
                        showCancelButton: true,
                        confirmButtonText: 'Register',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#28a745',
                        preConfirm: () => {
                            return {
                                ip: document.getElementById('swal-ip').value,
                                port: document.getElementById('swal-port').value
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            let ip = result.value.ip;
                            let port = result.value.port;

                            // Show loading
                            Swal.fire({
                                title: 'Registering...',
                                html: 'Please wait while we register the student to the device.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Make AJAX call
                            $.ajax({
                                url: {!! json_encode(route("students.register-device", ":id")) !!}.replace(':id', studentId),
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'Accept': 'application/json'
                                },
                                data: {
                                    ip: ip,
                                    port: port
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: response.message || 'Student registered to device successfully!',
                                            confirmButtonColor: '#28a745'
                                        }).then(() => {
                                            // Reload students table
                                            loadStudents();
                                        });
                                    } else {
                                        let errorMsg = '✗ ' + (response.message || 'Registration Failed');

                                        // Show troubleshooting guide if provided
                                        if (response.troubleshooting) {
                                            errorMsg += '\n\n' + response.troubleshooting;
                                        }

                                        // Show quick solution
                                        if (response.quick_solution) {
                                            errorMsg += '\n\n💡 QUICK SOLUTION:\n' + response.quick_solution;
                                        }

                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Registration Failed',
                                            html: '<pre style="text-align: left; white-space: pre-wrap; font-size: 0.9em;">' + errorMsg + '</pre>',
                                            confirmButtonColor: '#dc3545',
                                            width: '600px'
                                        });

                                        // If user might be registered, offer to sync
                                        if (response.might_be_registered) {
                                            Swal.fire({
                                                title: 'Sync Users?',
                                                text: 'The device responded. Would you like to sync users from device to check if student was added?',
                                                icon: 'question',
                                                showCancelButton: true,
                                                confirmButtonText: 'Yes, Sync',
                                                cancelButtonText: 'No'
                                            }).then((syncResult) => {
                                                if (syncResult.isConfirmed) {
                                                    // Redirect to fingerprint device settings page or call sync function
                                                    window.location.href = {!! json_encode(route("fingerprint_device_settings")) !!};
                                                }
                                            });
                                        }
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Error: ' + (xhr.responseJSON?.message || 'Unknown error occurred');
                                    if (xhr.responseJSON?.troubleshooting) {
                                        errorMsg += '\n\n' + xhr.responseJSON.troubleshooting;
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: '<pre style="text-align: left; white-space: pre-wrap; font-size: 0.9em;">' + errorMsg + '</pre>',
                                        confirmButtonColor: '#dc3545',
                                        width: '600px'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    // User chose manual method - show instructions
                    Swal.fire({
                        title: 'Manual Registration Instructions',
                        html: `<div style="text-align: left;">
                            <p><strong>1. On Device (192.168.100.108):</strong></p>
                            <ul>
                                <li>Press MENU → User Management → Add User</li>
                                <li>Enter Enroll ID: <strong>${fingerprintId}</strong></li>
                                <li>Enter Name: <strong>${studentName}</strong></li>
                                <li>Save</li>
                            </ul>
                            <p><strong>2. On This Page:</strong></p>
                            <ul>
                                <li>Go to "Fingerprint Device Settings" page</li>
                                <li>Click "Sync Users from Device" button</li>
                                <li>Student will appear automatically!</li>
                            </ul>
                            <p><strong>3. Enroll Fingerprint (optional):</strong></p>
                            <ul>
                                <li>On device: User Management → Enroll Fingerprint</li>
                                <li>Enter Enroll ID: <strong>${fingerprintId}</strong></li>
                                <li>Place finger 3 times</li>
                            </ul>
                        </div>`,
                        icon: 'info',
                        confirmButtonColor: '#17a2b8',
                        width: '600px'
                    });
                }
            });
        });

        // Delete not-enrolled student (single)
        $(document).on('click', '.delete-not-enrolled-student-btn', function () {
            const studentID = $(this).data('student-id');
            const studentName = $(this).data('student-name') || 'this student';

            Swal.fire({
                title: 'Delete not enrolled student?',
                html: 'Delete <strong>' + escapeHtmlStudent(studentName) + '</strong>?<br><small class="text-muted">Only students without Active enrollment are removed.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: {!! json_encode(route('delete_not_enrolled_student', ':id')) !!}.replace(':id', studentID),
                    type: 'DELETE',
                    data: getStudentFilterParams(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: response.message || 'Student deleted',
                                confirmButtonColor: '#940000'
                            }).then(function () {
                                reloadData();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Delete failed' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed'
                        });
                    }
                });
            });
        });

        // Delete all not-enrolled students (bulk)
        $(document).on('click', '#deleteAllNotEnrolledBtn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('#studentPageActionsDropdown').removeClass('show');

            const params = getStudentFilterParams({ status: 'NotEnrolled', paginate: 1, per_page: 1, page: 1 });
            $.getJSON({!! json_encode(route('get_students')) !!}, params).done(function (res) {
                const total = (res.success && res.pagination) ? (res.pagination.total || 0) : 0;
                if (!total) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No students',
                        text: 'No not-enrolled students found for the current filters.',
                        confirmButtonColor: '#940000'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Delete all not enrolled?',
                    html: 'Delete <strong>' + total + '</strong> not-enrolled student(s) matching current filters?<br><br><span class="text-danger">This cannot be undone.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Delete all (' + total + ')',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Deleting...',
                        html: 'Please wait while not-enrolled students are removed.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function () { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: {!! json_encode(route('delete_all_not_enrolled_students')) !!},
                        type: 'POST',
                        data: Object.assign(getStudentFilterParams({ status: 'NotEnrolled' }), {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }),
                        dataType: 'json',
                        timeout: 600000,
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted',
                                    text: response.message || ('Deleted ' + (response.deleted || 0) + ' student(s)'),
                                    confirmButtonColor: '#940000'
                                }).then(function () {
                                    if ($('#statusFilter').val() === 'NotEnrolled') {
                                        reloadData();
                                    } else {
                                        $('#statusFilter').val('NotEnrolled');
                                        reloadData();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete failed',
                                    text: response.message || 'No students were deleted'
                                });
                            }
                        },
                        error: function (xhr, textStatus) {
                            let msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed';
                            if (textStatus === 'timeout') {
                                msg = 'Delete timed out. Refresh and check the Not Enrolled list, some students may have been removed.';
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });
            }).fail(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not count not-enrolled students.' });
            });
        });

        // Delete Student Button Click
        $(document).on('click', '.delete-student-btn', function() {
            let studentID = $(this).data('student-id');
            let studentName = $(this).data('student-name');

            Swal.fire({
                title: 'Delete Student?',
                html: 'Are you sure you want to delete <strong>' + studentName + '</strong>?<br><br>This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: {!! json_encode(route("delete_student", ":id")) !!}.replace(':id', studentID),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message || 'Student deleted successfully',
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    reloadData();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to delete student'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to delete student';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                }
            });
        });

        function generateAdmissionNumber() {
            const now = new Date();
            const pad = (value, length = 2) => String(value).padStart(length, '0');
            const timestamp = [
                now.getFullYear(),
                pad(now.getMonth() + 1),
                pad(now.getDate()),
                pad(now.getHours()),
                pad(now.getMinutes()),
                pad(now.getSeconds()),
                pad(now.getMilliseconds(), 3)
            ].join('');
            const random = Math.floor(100 + Math.random() * 900);
            return `${timestamp}${random}`;
        }

        // Register Student Form
        $('#addStudentForm').on('submit', function(e) {
            e.preventDefault();

            // Client-side validation
            let first_name = $('#first_name').val().trim();
            let last_name = $('#last_name').val().trim();
            let gender = $('#gender').val();
            let subclassID = $('#subclassID').val();
            let admission_number = $('#admission_number').val().trim();

            // Clear previous error messages
            $('.text-danger.validation-error').remove();
            $('.form-control, .form-select').removeClass('is-invalid');

            let hasErrors = false;

            // Validate required fields
            if (!first_name) {
                $('#first_name').addClass('is-invalid').after('<div class="text-danger validation-error small">First name is required</div>');
                hasErrors = true;
            }

            if (!last_name) {
                $('#last_name').addClass('is-invalid').after('<div class="text-danger validation-error small">Last name is required</div>');
                hasErrors = true;
            }

            if (!gender) {
                $('#gender').addClass('is-invalid').after('<div class="text-danger validation-error small">Gender is required</div>');
                hasErrors = true;
            }

            if (!subclassID) {
                $('#subclassID').addClass('is-invalid').after('<div class="text-danger validation-error small">Class is required</div>');
                hasErrors = true;
            }

            if (hasErrors) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            if (!admission_number) {
                admission_number = generateAdmissionNumber();
                $('#admission_number').val(admission_number);
            }

            let formData = new FormData(this);
            formData.set('admission_number', admission_number);

            // Debug: Log form data to console for review
            console.log('--- Registering Student - Reviewing Form Data ---');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            console.log('------------------------------------------------');
            let submitBtn = $(this).find('button[type="submit"]');
            let originalBtnText = submitBtn.html();

            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Registering...');

            $.ajax({
                url: {!! json_encode(route("save_student")) !!},
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    // Show loading overlay
                    $('body').append('<div id="formLoadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 10px; text-align: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Registering student...</p></div></div>');
                },
                success: function(response) {
                    $('#formLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (response.success) {
                        // Close the registration modal first
                        if ($('#parentID').hasClass('select2-hidden-accessible')) {
                            if (typeof $('#parentID').select2 === 'function') {
                                $('#parentID').select2('destroy');
                            }
                        }
                        if ($('#subclassID').hasClass('select2-hidden-accessible')) {
                            if (typeof $('#subclassID').select2 === 'function') {
                                $('#subclassID').select2('destroy');
                            }
                        }
                        hideModal('addStudentModal');
                        $('#addStudentForm')[0].reset();
                        $('.is-invalid').removeClass('is-invalid');
                        $('.validation-error').remove();

                        // Show simple SweetAlert message with fingerprintID
                        var fingerprintId = response.fingerprint_id || '';
                        Swal.fire({
                            title: 'Student Registered Successfully!',
                            html: '<div class="text-center">' +
                                  '<p class="mb-3">Student registered successfully</p>' +
                                  '<p class="mb-0">Please continue register user in fingerprint device ID <strong style="font-size: 1.2rem; color: #940000;">' + fingerprintId + '</strong></p>' +
                                  '</div>',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#940000',
                            width: '500px'
                        }).then(() => {
                            loadStudents('Active', 'activeStudentsTable');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to register student',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $('#formLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    let errors = xhr.responseJSON?.errors || {};
                    let errorMessage = xhr.responseJSON?.message || 'Failed to register student';

                    // Handle admission number duplicate error
                    if (xhr.responseJSON?.errors?.admission_number) {
                        $('#admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">' + xhr.responseJSON.errors.admission_number[0] + '</div>');
                    }

                    if (Object.keys(errors).length > 0) {
                        let errorList = Object.entries(errors).map(([field, msg]) => {
                            if (Array.isArray(msg)) {
                                return msg[0];
                            }
                            return msg;
                        }).join('<br>');
                        errorMessage = errorList;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        html: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Edit Student Form
        $('#editStudentForm').on('submit', function(e) {
            e.preventDefault();

            // Client-side validation
            let first_name = $('#edit_first_name').val().trim();
            let last_name = $('#edit_last_name').val().trim();
            let gender = $('#edit_gender').val();
            let subclassID = $('#edit_subclassID').val();
            let admission_number = $('#edit_admission_number').val().trim();

            // Clear previous error messages
            $('#editStudentModal .text-danger.validation-error').remove();
            $('#editStudentModal .form-control, #editStudentModal .form-select').removeClass('is-invalid');

            let formData = new FormData(this);
            let submitBtn = $(this).find('button[type="submit"]');
            let originalBtnText = submitBtn.html();

            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

            $.ajax({
                url: {!! json_encode(route("update_student")) !!},
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                beforeSend: function() {
                    // Show loading overlay
                    $('body').append('<div id="editFormLoadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 10px; text-align: center;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Updating student...</p></div></div>');
                },
                success: function(response) {
                    $('#editFormLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Student updated successfully',
                            confirmButtonColor: '#940000'
                        }).then(() => {
                            // Destroy Select2 before hiding modal
                            if ($('#edit_parentID').hasClass('select2-hidden-accessible')) {
                                if (typeof $('#edit_parentID').select2 === 'function') {
                                    $('#edit_parentID').select2('destroy');
                                }
                            }
                            if ($('#edit_subclassID').hasClass('select2-hidden-accessible')) {
                                if (typeof $('#edit_subclassID').select2 === 'function') {
                                    $('#edit_subclassID').select2('destroy');
                                }
                            }
                            hideModal('editStudentModal');
                            $('#editStudentForm')[0].reset();
                            $('.is-invalid').removeClass('is-invalid');
                            $('.validation-error').remove();
                            // Reload active students table
                            loadStudents('Active', 'activeStudentsTable');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message || 'Failed to update student',
                            confirmButtonColor: '#940000'
                        });
                    }
                },
                error: function(xhr) {
                    $('#editFormLoadingOverlay').remove();
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    let errors = xhr.responseJSON?.errors || {};
                    let errorMessage = xhr.responseJSON?.message || 'Failed to update student';

                    // Handle admission number duplicate error
                    if (xhr.responseJSON?.errors?.admission_number) {
                        $('#edit_admission_number').addClass('is-invalid').after('<div class="text-danger validation-error small">' + xhr.responseJSON.errors.admission_number[0] + '</div>');
                    }

                    if (Object.keys(errors).length > 0) {
                        let errorList = Object.entries(errors).map(([field, msg]) => {
                            if (Array.isArray(msg)) {
                                return msg[0];
                            }
                            return msg;
                        }).join('<br>');
                        errorMessage = errorList;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        // Transfer Student Form
        $('#transferStudentForm').on('submit', function(e) {
            e.preventDefault();

            let formData = {
                studentID: $('#transferStudentID').val(),
                new_subclassID: $('#newSubclassID').val()
            };

            if (!formData.new_subclassID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a class to transfer to',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            Swal.fire({
                title: 'Transfer Student?',
                html: 'Are you sure you want to transfer this student to the selected class?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, transfer!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: {!! json_encode(route("transfer_student")) !!},
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message || 'Student transferred successfully',
                                    confirmButtonColor: '#940000'
                                }).then(() => {
                                    // Destroy Select2 before hiding modal
                                    if ($('#newSubclassID').hasClass('select2-hidden-accessible')) {
                                        if (typeof $('#newSubclassID').select2 === 'function') {
                                            $('#newSubclassID').select2('destroy');
                                        }
                                    }
                                    hideModal('transferStudentModal');
                                    $('#transferStudentForm')[0].reset();
                                    // Reload active students table
                                    loadStudents('Active', 'activeStudentsTable');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message || 'Failed to transfer student',
                                    confirmButtonColor: '#940000'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to transfer student';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonColor: '#940000'
                            });
                        }
                    });
                }
            });
        });

        // Function to show modal manually
        function showModal(modalId) {
            openStudentModal(modalId);
        }

        // Function to hide modal manually
        function hideModal(modalId) {
            if (!modalId) return;

            let $modal = $('#' + modalId);
            if ($modal.length === 0) return;

            // jQuery Bootstrap 4/5 plugin
            if (typeof $modal.modal === 'function') {
                try {
                    $modal.modal('hide');
                } catch (e) {}
            }

            // Bootstrap 5 native instance (if any)
            if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
                try {
                    const bsModal = bootstrap.Modal.getInstance($modal[0]);
                    if (bsModal) bsModal.hide();
                } catch (e) {}
            }

            // Hard cleanup so modal never stays stuck
            $modal.removeClass('show in').css('display', 'none').attr('aria-hidden', 'true');
            $modal.removeAttr('aria-modal');
            $('body').removeClass('modal-open').css({ 'padding-right': '', 'overflow': '' });
            $('.modal-backdrop').remove();

            $('#' + modalId + ' select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible') && typeof $(this).select2 === 'function') {
                    $(this).select2('destroy');
                }
            });
        }

        // Close via X / Cancel / Close (BS4 data-dismiss + BS5 data-bs-dismiss)
        $(document).on('click', '.modal .btn-close, .modal .close, .modal [data-dismiss="modal"], .modal [data-bs-dismiss="modal"], .modal-footer .btn-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const modalId = $(this).closest('.modal').attr('id');
            if (modalId) hideModal(modalId);
        });

        // Allow typing inside SweetAlert while a Bootstrap modal is open (BS4 focus trap)
        if (window.jQuery && $.fn.modal && $.fn.modal.Constructor) {
            try {
                $.fn.modal.Constructor.prototype.enforceFocus = function () {};
            } catch (e) {}
        }

        $(document).on('hidden.bs.modal', '.modal', function() {
            if ($('.modal.show').length === 0) {
                $('body').removeClass('modal-open').css('padding-right', '');
                $('.modal-backdrop').remove();
            }
        });

        // Helper function for safe Select2 initialization
        function safeInitSelect2(element, options) {
            let $element = $(element);
            if (typeof $element.select2 === 'function') {
                $element.select2(options);
            } else {
                console.warn('Select2 is not available for element:', element);
            }
        }

        // Load classes and subclasses for filters
        function loadClassesForFilter() {
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let classSelect = $('#classFilter');
                        classSelect.html('<option value="">-- All Classes --</option>');
                        
                        // Extract unique classes from subclasses
                        let classes = {};
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                // Make sure classID exists and is valid
                                let classID = subclass.classID;
                                if (classID && subclass.class_name && !classes[classID]) {
                                    classes[classID] = subclass.class_name;
                                    classSelect.append('<option value="' + classID + '">' + subclass.class_name + '</option>');
                                }
                            });
                        } else {
                            console.warn('No subclasses in response or invalid format:', response);
                        }
                        
                        // Initialize Select2
                        if (!classSelect.hasClass('select2-hidden-accessible')) {
                            safeInitSelect2(classSelect, {
                                theme: 'bootstrap-5',
                                placeholder: 'Select class...',
                                allowClear: true,
                                width: '100%'
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load classes:', error);
                    console.error('Response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load classes. Please refresh the page.',
                        confirmButtonColor: '#940000'
                    });
                }
            });
        }

        function loadAllSubclassesForFilter() {
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassFilter');
                        subclassSelect.html('<option value="">-- All Subclasses --</option>');
                        
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                                subclassSelect.append('<option value="' + subclass.subclassID + '" data-class-id="' + (subclass.classID || '') + '">' + displayName + '</option>');
                            });
                        }
                        
                        // Initialize Select2
                        if (!subclassSelect.hasClass('select2-hidden-accessible')) {
                            if (typeof subclassSelect.select2 === 'function') {
                                subclassSelect.select2({
                                    theme: 'bootstrap-5',
                                    placeholder: 'Select subclass...',
                                    allowClear: true,
                                    width: '100%'
                                });
                            }
                        }
                    }
                }
            });
        }

        function loadSubclassesForFilter(classID) {
            $.ajax({
                url: {!! json_encode(route("get_subclasses_for_school")) !!},
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let subclassSelect = $('#subclassFilter');
                        subclassSelect.html('<option value="">-- All Subclasses --</option>');
                        
                        if (response.subclasses && Array.isArray(response.subclasses)) {
                            response.subclasses.forEach(function(subclass) {
                                if (subclass.classID == classID) {
                                    const displayName = subclass.display_name || (subclass.class_name + ' ' + subclass.subclass_name) || subclass.subclass_name;
                                    subclassSelect.append('<option value="' + subclass.subclassID + '" data-class-id="' + (subclass.classID || '') + '">' + displayName + '</option>');
                                }
                            });
                        }
                        
                        // Update Select2
                        if (subclassSelect.hasClass('select2-hidden-accessible')) {
                            if (typeof subclassSelect.select2 === 'function') {
                                subclassSelect.trigger('change');
                            }
                        }
                    }
                }
            });
        }

        // Export PDF using JavaScript — fetch ALL filtered students, not the current DataTable page
        $('#exportPdfBtn').on('click', function() {
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'PDF library not loaded. Please refresh the page.',
                    confirmButtonColor: '#940000'
                });
                return;
            }

            Swal.fire({
                title: 'Generating PDF...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: {!! json_encode(route("get_students_list")) !!},
                type: 'GET',
                dataType: 'json',
                data: getExportFilterParams(),
                success: function(response) {
                    const exportStudents = (response && response.success) ? (response.students || []) : [];
                    if (!exportStudents.length) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data',
                            text: 'No students data to export for the selected filters.',
                            confirmButtonColor: '#940000'
                        });
                        return;
                    }
                    downloadFilteredStudentsPdf(exportStudents);
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to load students for PDF export.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg,
                        confirmButtonColor: '#940000'
                    });
                }
            });
        });

        function downloadFilteredStudentsPdf(exportStudents) {
            const { jsPDF } = window.jspdf;
            let status = $('#statusFilter').val() || 'Active';
            let promotionStatus = $('#promotionFilter').val() || '';
            let classID = $('#classFilter').val() || '';
            let subclassID = $('#subclassFilter').val() || '';
            let gender = $('#genderFilter').val() || '';
            let health = $('#healthFilter').val() || '';

            let tableData = exportStudents.map(function(student) {
                return [
                    student.admission_number || '',
                    student.full_name || '',
                    student.class || '',
                    student.gender || '',
                    student.parent_phone || '',
                    student.fingerprint_id || ''
                ];
            });

            try {
                // Create new PDF document
                const doc = new jsPDF('l', 'mm', 'a4'); // landscape orientation

                // Add title
                doc.setFontSize(16);
                doc.setTextColor(148, 0, 0); // #940000
                doc.text('Students Report', 14, 15);

                // Add filter info
                doc.setFontSize(10);
                doc.setTextColor(0, 0, 0);
                let yPos = 22;
                let filterText = 'Filters: ';
                let filters = [];
                if (status) filters.push('Enrollment Status: ' + status);
                if (promotionStatus) filters.push('Promotion Status: ' + promotionStatus);
                @php
                    $pdfAyLabels = [];
                    if (!empty($headerAcademicYear['selected_o_level_year'])) {
                        $y = $headerAcademicYear['selected_o_level_year'];
                        $pdfAyLabels[] = 'O-Level: ' . ($y->year_name ?: $y->year);
                    } elseif (!empty($headerAcademicYear['selected_year'])) {
                        $y = $headerAcademicYear['selected_year'];
                        $pdfAyLabels[] = $y->year_name ?: $y->year;
                    }
                    if (!empty($headerAcademicYear['selected_a_level_year'])) {
                        $y = $headerAcademicYear['selected_a_level_year'];
                        $pdfAyLabels[] = 'A-Level: ' . ($y->year_name ?: $y->year);
                    }
                @endphp
                @if(!empty($pdfAyLabels))
                filters.push({!! json_encode('Academic Year: ' . implode(' | ', $pdfAyLabels)) !!});
                @endif
                if (classID) {
                    let classOption = $('#classFilter option:selected').text();
                    if (classOption !== '-- All Classes --') filters.push('Class: ' + classOption);
                }
                if (subclassID) {
                    let subclassOption = $('#subclassFilter option:selected').text();
                    if (subclassOption !== '-- All Subclasses --') filters.push('Subclass: ' + subclassOption);
                }
                if (gender) filters.push('Gender: ' + gender);
                if (health) filters.push('Health: ' + (health === 'good' ? 'Good Health' : 'Bad Health'));
                
                if (filters.length > 0) {
                    doc.text(filterText + filters.join(', '), 14, yPos);
                    yPos += 5;
                }

                // Add date
                let currentDate = new Date().toLocaleDateString();
                doc.text('Generated on: ' + currentDate, 14, yPos);
                yPos += 8;

                // Prepare table data
                let tableRows = [];
                
                // Helper function to extract text from HTML
                function extractText(html) {
                    if (!html) return '';
                    if (typeof html === 'string') {
                        let $temp = $('<div>').html(html);
                        return $temp.text().trim().replace(/⚠️/g, '').replace(/\s+/g, ' ');
                    }
                    return String(html).trim();
                }

                tableData.forEach(function(row, index) {
                    // Row is an array of data from currentStudentsData
                    let rowData = Array.isArray(row) ? row : [];

                    let admissionNumber = extractText(rowData[0] || '');
                    let fullName = extractText(rowData[1] || '');
                    let className = extractText(rowData[2] || '');
                    let genderVal = extractText(rowData[3] || '');
                    let parentPhone = extractText(rowData[4] || '');
                    let fingerprintId = extractText(rowData[5] || '');

                    tableRows.push([
                        index + 1,
                        admissionNumber || 'N/A',
                        fullName || 'N/A',
                        className || 'N/A',
                        genderVal || 'N/A',
                        parentPhone || 'N/A',
                        fingerprintId || 'N/A'
                    ]);
                });

                // Add table using autoTable
                doc.autoTable({
                    head: [['#', 'Admission No', 'Full Name', 'Class', 'Gender', 'Parent Phone', 'Fingerprint ID']],
                    body: tableRows,
                    startY: yPos,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [148, 0, 0], // #940000
                        textColor: [255, 255, 255],
                        fontStyle: 'bold'
                    },
                    styles: {
                        fontSize: 8,
                        cellPadding: 2
                    },
                    columnStyles: {
                        0: { cellWidth: 15 },
                        1: { cellWidth: 40 },
                        2: { cellWidth: 55 },
                        3: { cellWidth: 40 },
                        4: { cellWidth: 22 },
                        5: { cellWidth: 42 },
                        6: { cellWidth: 35 }
                    },
                    margin: { top: yPos }
                });

                // Add statistics at the end
                let finalY = doc.lastAutoTable.finalY + 10;
                doc.setFontSize(10);
                doc.setTextColor(148, 0, 0);
                doc.text('Statistics:', 14, finalY);
                finalY += 5;

                doc.setFontSize(9);
                doc.setTextColor(0, 0, 0);
                const exportedMale = exportStudents.filter(function(s) { return s.gender === 'Male'; }).length;
                const exportedFemale = exportStudents.filter(function(s) { return s.gender === 'Female'; }).length;
                doc.text('Total Students: ' + exportStudents.length, 14, finalY);
                finalY += 5;
                doc.text('Male: ' + exportedMale + ' | Female: ' + exportedFemale, 14, finalY);
                finalY += 5;
                doc.text('Good Health: ' + $('#statGoodHealth').text() + ' | Bad Health: ' + $('#statBadHealth').text(), 14, finalY);

                // Generate filename
                let filename = 'Students_Report_' + status + '_' + new Date().toISOString().split('T')[0] + '.pdf';

                // Save PDF
                doc.save(filename);

                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'PDF exported successfully',
                    confirmButtonColor: '#940000',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Error generating PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate PDF: ' + error.message,
                    confirmButtonColor: '#940000'
                });
            }
        }

        // Export Excel — same filters as the table, all matching students (not one page)
        $('#exportExcelBtn').on('click', function() {
            const url = {!! json_encode(route("export_students_excel")) !!} + '?' + $.param(getExportFilterParams());
            window.open(url, '_blank');
        });

        // Initialize Select2 for filters
        $('#classFilter, #subclassFilter, #genderFilter, #healthFilter, #statusFilter, #promotionFilter').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                safeInitSelect2(this, {
                    theme: 'bootstrap-5',
                    placeholder: 'Select...',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            }
        });

        // Check if subclassID is provided in URL (for teacher view)
        const urlParams = new URLSearchParams(window.location.search);
        const subclassID = urlParams.get('subclassID');
        
        // Initialize on page load
        loadFormData(subclassID || null);
        loadClassesForFilter();
        loadAllSubclassesForFilter();
        
        // If subclassID is provided, auto-open registration modal
        if (subclassID) {
            console.log('SubclassID found in URL:', subclassID);
            
            // Listen for when subclass is pre-selected
            $(document).on('subclassPreSelected', function(event, selectedSubclassID) {
                console.log('Subclass pre-selected:', selectedSubclassID);
                if (selectedSubclassID == subclassID) {
                    // Wait a bit more for Select2 to initialize
                    setTimeout(function() {
                        openRegistrationModal();
                    }, 300);
                }
            });
            
            // Also try to open after form data loads (fallback)
            setTimeout(function() {
                if ($('#subclassID').val() == subclassID) {
                    openRegistrationModal();
                }
            }, 2000);
            
            function openRegistrationModal() {
                const modal = document.getElementById('addStudentModal');
                if (modal) {
                    console.log('Opening registration modal...');
                    // Use Bootstrap 5 modal
                    if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                        const bsModal = new bootstrap.Modal(modal, {
                            backdrop: 'static',
                            keyboard: true
                        });
                        bsModal.show();
                        console.log('Modal opened successfully');
                    } else if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                        // Fallback to jQuery Bootstrap modal
                        jQuery(modal).modal('show');
                        console.log('Modal opened with jQuery');
                    } else {
                        // Manual fallback
                        modal.style.display = 'block';
                        modal.classList.add('show');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                        console.log('Modal opened manually');
                    }
                } else {
                    console.error('Modal not found');
                }
            }
        }

        // Load initial data
        setTimeout(function() {
            loadStudents();
            loadStatistics();
            refreshDuplicateNameAlert();
        }, 500);

        let dupNameGroups = [];
        let dupNameSearchTimer = null;

        function updateDuplicateNameAlert(groupCount, studentCount) {
            const groups = parseInt(groupCount || 0, 10);
            const students = parseInt(studentCount || 0, 10);
            const $alert = $('#duplicateNamesAlert');
            const $badge = $('#duplicateNamesMenuBadge');
            if (groups > 0) {
                const label = groups + ' duplicate name' + (groups === 1 ? '' : 's') +
                    ', ' + students + ' student' + (students === 1 ? '' : 's') + '. Click to view.';
                $('#duplicateNamesAlertText').text(label);
                $('#duplicateNamesAlertCount').text(groups);
                $alert.removeClass('d-none');
                $badge.text(groups).show();
            } else {
                $alert.addClass('d-none');
                $badge.hide();
            }
        }

        function refreshDuplicateNameAlert() {
            $.ajax({
                url: '{{ route("get_duplicate_student_names") }}',
                method: 'GET',
                data: {
                    duplicate_type: $('#dupTypeFilter').val() || 'fname_lname',
                    status: $('#statusFilter').val() || 'Active',
                    promotion_status: $('#promotionFilter').val() || '',
                    classID: $('#classFilter').val() || '',
                    subclassID: $('#subclassFilter').val() || '',
                    summary: 1
                },
                success: function (res) {
                    if (!res || !res.success) {
                        updateDuplicateNameAlert(0, 0);
                        return;
                    }
                    updateDuplicateNameAlert(res.group_count || 0, res.student_count || 0);
                },
                error: function () {
                    updateDuplicateNameAlert(0, 0);
                }
            });
        }

        function showDuplicateNamesModal() {
            const modal = document.getElementById('duplicateNamesModal');
            if (!modal) return;
            $('#dupStatusFilter').val($('#statusFilter').val() || 'Active');
            $('#dupPromotionFilter').val($('#promotionFilter').val() || '');
            $('#dupNameSearch').val('');
            if (window.jQuery && typeof jQuery.fn.modal === 'function') {
                jQuery(modal).modal('show');
            } else if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            } else if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                new bootstrap.Modal(modal).show();
            } else {
                modal.style.display = 'block';
                modal.classList.add('show');
                document.body.classList.add('modal-open');
            }
            loadDuplicateNames();
        }

        function loadDuplicateNames() {
            const $list = $('#dupNameList');
            const $summary = $('#dupNameSummary');
            $list.html('<div class="text-muted py-3"><i class="bi bi-hourglass-split"></i> Loading duplicate names...</div>');
            $summary.text('Loading...');
            $.ajax({
                url: '{{ route("get_duplicate_student_names") }}',
                method: 'GET',
                data: {
                    duplicate_type: $('#dupTypeFilter').val() || 'fname_lname',
                    status: $('#dupStatusFilter').val() || 'Active',
                    promotion_status: $('#dupPromotionFilter').val() || '',
                    classID: $('#classFilter').val() || '',
                    subclassID: $('#subclassFilter').val() || '',
                    search: ($('#dupNameSearch').val() || '').trim()
                },
                success: function (res) {
                    if (!res || !res.success) {
                        $list.html('<div class="text-danger py-3">' + escapeHtmlStudent((res && res.message) || 'Failed to load duplicate names.') + '</div>');
                        $summary.text('');
                        return;
                    }
                    dupNameGroups = res.groups || [];
                    renderDuplicateNameGroups();
                    updateDuplicateNameAlert(res.group_count || dupNameGroups.length, res.student_count || 0);
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load duplicate names.';
                    $list.html('<div class="text-danger py-3">' + escapeHtmlStudent(msg) + '</div>');
                    $summary.text('');
                }
            });
        }

        function renderDuplicateNameGroups() {
            const groups = dupNameGroups;
            const $list = $('#dupNameList');
            const $summary = $('#dupNameSummary');
            if (!groups.length) {
                $summary.text('No duplicate names found for the selected filters.');
                $list.html('<div class="text-muted py-3">No students share this name pattern.</div>');
                return;
            }
            $summary.text(groups.length + ' duplicate name' + (groups.length === 1 ? '' : 's') + ', ' + (groups.reduce(function (n, g) { return n + (g.count || 0); }, 0)) + ' students');
            let html = '';
            groups.forEach(function (group, idx) {
                const students = group.students || [];
                html += '<div class="dup-name-group" data-dup-idx="' + idx + '">';
                html += '<button type="button" class="dup-name-toggle">';
                html += '<span class="dup-name-label">' + escapeHtmlStudent(group.name || '') + '</span>';
                html += '<span class="badge">' + (group.count || students.length) + '</span>';
                html += '<i class="bi bi-chevron-down"></i>';
                html += '</button>';
                html += '<div class="dup-name-students"><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="bg-light"><tr>';
                html += '<th></th><th>Student</th><th>Admission No</th><th>Class</th><th>Gender</th><th>Enrollment</th><th>Promotion</th>';
                html += '</tr></thead><tbody>';
                students.forEach(function (st) {
                    html += '<tr>';
                    html += '<td>' + studentPhotoImgHtml(st, '', st.full_name) + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.full_name || '') + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.admission_number || '-') + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.class || '-') + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.gender || '-') + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.enrollment_status || '-') + '</td>';
                    html += '<td>' + escapeHtmlStudent(st.promotion_status ? promotionStatusLabel(st.promotion_status) : '-') + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            });
            $list.html(html);
        }

        $(document).on('click', '#duplicateNamesBtn, #duplicateNamesAlert', function (e) {
            e.preventDefault();
            showDuplicateNamesModal();
        });

        $(document).on('change', '#dupTypeFilter, #dupStatusFilter, #dupPromotionFilter', function () {
            if ($('#duplicateNamesModal').hasClass('show') || $('#duplicateNamesModal').is(':visible')) {
                loadDuplicateNames();
            }
        });

        $(document).on('input', '#dupNameSearch', function () {
            clearTimeout(dupNameSearchTimer);
            dupNameSearchTimer = setTimeout(function () {
                loadDuplicateNames();
            }, 350);
        });

        $(document).on('click', '.dup-name-toggle', function () {
            $(this).closest('.dup-name-group').toggleClass('open');
        });
    });

    // ===== Student Enrollment History (Details modal) =====
    var studentEnrollmentHistoryCache = {
        studentID: null,
        years: [],
        enrollments: []
    };

    function enrollmentStatusBadgeClass(status) {
        var s = String(status || '').toLowerCase().replace(/\s+/g, '_');
        if (s === 'active') return 'enrollment-badge-active';
        if (s === 'completed') return 'enrollment-badge-completed';
        if (s === 'repeated') return 'enrollment-badge-repeated';
        if (s === 'graduated') return 'enrollment-badge-graduated';
        if (s === 'transferred') return 'enrollment-badge-transferred';
        if (s === 'dropped') return 'enrollment-badge-dropped';
        if (s === 'cancelled') return 'enrollment-badge-cancelled';
        if (s === 'not_enrolled' || s === 'notenrolled') return 'enrollment-badge-other';
        return 'enrollment-badge-other';
    }

    function promotionStatusLabel(status) {
        var labels = {
            Pending: 'Pending',
            Promoted: 'Promoted',
            Repeated: 'Repeated',
            Graduated: 'Graduated',
            Transferred: 'Transferred',
            Truancy: 'Truancy (Long-term absentee)',
            Dropout: 'Dropout'
        };
        return labels[status] || status || '-';
    }

    function promotionStatusBadgeClass(status) {
        var s = String(status || '').toLowerCase().replace(/\s+/g, '_');
        if (s === 'pending') return 'promotion-badge-pending';
        if (s === 'promoted') return 'promotion-badge-promoted';
        if (s === 'repeated') return 'promotion-badge-repeated';
        if (s === 'graduated') return 'promotion-badge-graduated';
        if (s === 'transferred') return 'promotion-badge-transferred';
        if (s === 'truancy') return 'promotion-badge-truancy';
        if (s === 'dropout') return 'promotion-badge-dropout';
        return 'promotion-badge-other';
    }

    function escapeHtmlEnrollment(text) {
        if (text === null || text === undefined) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(text)));
        return d.innerHTML;
    }

    function loadStudentEnrollmentHistory(studentID) {
        if (!studentID) {
            $('#enrollmentClassWidgets').html('<p class="text-danger text-center py-3 mb-0">Student ID missing</p>');
            return;
        }

        studentEnrollmentHistoryCache = { studentID: studentID, years: [], enrollments: [] };

        $.ajax({
            url: {!! json_encode(route("get_student_enrollment_history", ":id")) !!}.replace(':id', studentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    $('#enrollmentClassWidgets').html('<p class="text-muted text-center py-3 mb-0">No enrollment records found</p>');
                    return;
                }

                studentEnrollmentHistoryCache.years = response.years || [];
                studentEnrollmentHistoryCache.enrollments = response.enrollments || [];

                var $selector = $('#enrollmentYearSelector');
                $selector.find('option:not([value="all"])').remove();
                studentEnrollmentHistoryCache.years.forEach(function(year) {
                    var label = escapeHtmlEnrollment(year.year_name || year.year);
                    $selector.append(
                        '<option value="' + year.academic_yearID + '">' + label + '</option>'
                    );
                });

                // Default to header-selected academic year when available
                var defaultYear = response.default_academic_yearID;
                if (defaultYear && String(defaultYear) !== 'all' &&
                    $selector.find('option[value="' + defaultYear + '"]').length) {
                    $selector.val(String(defaultYear));
                } else if (response.selected_academic_yearIDs && response.selected_academic_yearIDs.length) {
                    var matched = null;
                    response.selected_academic_yearIDs.forEach(function(id) {
                        if (!matched && $selector.find('option[value="' + id + '"]').length) {
                            matched = id;
                        }
                    });
                    $selector.val(matched ? String(matched) : 'all');
                } else {
                    $selector.val('all');
                }

                renderEnrollmentClassWidgets($selector.val() || 'all');
            },
            error: function(xhr) {
                console.error('Error loading enrollment history:', xhr.responseText);
                $('#enrollmentClassWidgets').html('<p class="text-danger text-center py-3 mb-0">Failed to load enrollments</p>');
            }
        });
    }

    function buildEnrollmentClassGroups(yearFilter) {
        var enrollments = studentEnrollmentHistoryCache.enrollments || [];
        var filtered = enrollments;
        if (yearFilter && yearFilter !== 'all') {
            var yearId = String(yearFilter);
            filtered = enrollments.filter(function(e) {
                return String(e.academic_yearID) === yearId;
            });
        }

        var groups = {};
        filtered.forEach(function(row) {
            // One main-class widget per academic year + class
            var key = String(row.academic_yearID) + '_' + String(row.classID);
            if (!groups[key]) {
                groups[key] = {
                    key: key,
                    academic_yearID: row.academic_yearID,
                    year_name: row.year_name,
                    classID: row.classID,
                    class_name: row.class_name || 'Unknown class',
                    subclasses: []
                };
            }
            groups[key].subclasses.push(row);
        });

        return Object.keys(groups).map(function(k) { return groups[k]; });
    }

    function enrollmentDisplayLabel(row) {
        var main = String(row.class_name || '').trim();
        var sub = String(row.subclass_name || '').trim();
        if (!main && !sub) return 'Unknown subclass';
        if (!sub) return main;
        if (!main) return sub;
        if (sub.toLowerCase().indexOf(main.toLowerCase()) === 0) {
            return sub;
        }
        return (main + ' ' + sub).trim();
    }

    function enrollmentYearSpanLabel(row) {
        var fromYear = null;
        var toYear = null;
        if (row.joined_date) {
            fromYear = String(row.joined_date).slice(0, 4);
        } else if (row.year_name) {
            fromYear = String(row.year_name).split(/[\/\\-]/)[0].trim() || row.year_name;
        }
        if (row.left_date) {
            toYear = String(row.left_date).slice(0, 4);
        } else if (String(row.status || '').toLowerCase() === 'active') {
            toYear = 'Present';
        } else if (row.year_name) {
            toYear = String(row.year_name).split(/[\/\\-]/).pop().trim() || row.year_name;
        }
        if (fromYear && toYear) {
            return 'From ' + fromYear + ' to ' + toYear;
        }
        if (row.year_name) {
            return 'Academic year: ' + row.year_name;
        }
        return 'Academic year: N/A';
    }

    function renderEnrollmentSubclassDetails(sub) {
        var html = '';
        html += '<div class="enrollment-period-meta small mt-0" style="border-top:0;padding-top:0;">';
        html += '<div><i class="bi bi-calendar-range"></i> <strong>Enrolled:</strong> ' +
            escapeHtmlEnrollment(enrollmentYearSpanLabel(sub)) + '</div>';
        if (sub.year_name) {
            html += '<div><i class="bi bi-calendar3"></i> <strong>Academic year:</strong> ' +
                escapeHtmlEnrollment(sub.year_name) + '</div>';
        }
        html += '<div><i class="bi bi-box-arrow-in-right"></i> <strong>Entered:</strong> ' +
            escapeHtmlEnrollment(sub.joined_date_display || sub.joined_date || 'N/A') + '</div>';
        html += '<div><i class="bi bi-box-arrow-right"></i> <strong>' +
            escapeHtmlEnrollment(sub.left_date_label || 'Left') + ':</strong> ' +
            escapeHtmlEnrollment(sub.left_date_display || sub.left_date || (String(sub.status || '').toLowerCase() === 'active' ? 'Still enrolled' : 'N/A')) +
            '</div>';
        html += '<div><i class="bi bi-hourglass-split"></i> <strong>Duration:</strong> ' +
            escapeHtmlEnrollment(sub.duration_label || (sub.duration_months ? (sub.duration_months + ' months') : 'N/A')) +
            '</div>';
        if (sub.promotion_status) {
            html += '<div><i class="bi bi-arrow-up-right"></i> <strong>Promotion:</strong> ' +
                escapeHtmlEnrollment(promotionStatusLabel(sub.promotion_status)) + '</div>';
        }
        if (sub.roll_number) {
            html += '<div><i class="bi bi-hash"></i> <strong>Roll:</strong> ' +
                escapeHtmlEnrollment(sub.roll_number) + '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderEnrollmentClassWidgets(yearFilter) {
        var $container = $('#enrollmentClassWidgets');
        if (!$container.length) return;

        var groups = buildEnrollmentClassGroups(yearFilter);
        if (!groups.length) {
            $container.html('<p class="text-muted text-center py-4 mb-0">No enrollments for the selected academic year.</p>');
            return;
        }

        var showYearInTitle = !yearFilter || yearFilter === 'all';
        var html = '';

        groups.forEach(function(group) {
            var title = escapeHtmlEnrollment(group.class_name || 'Class');
            if (showYearInTitle && group.year_name) {
                title += ' <span class="text-muted font-weight-normal">(' + escapeHtmlEnrollment(group.year_name) + ')</span>';
            }
            var subclassCount = group.subclasses.length;
            var meta = subclassCount + (subclassCount === 1 ? ' subclass' : ' subclasses') + ' · click to view';

            html += '<div class="enrollment-class-widget" data-group-key="' + escapeHtmlEnrollment(group.key) + '">';
            html += '<div class="enrollment-class-header" role="button" tabindex="0">';
            html += '<div>';
            html += '<p class="enrollment-class-title"><i class="bi bi-building" style="color:#940000;"></i> ' + title + '</p>';
            html += '<div class="enrollment-class-meta">' + meta + '</div>';
            html += '</div>';
            html += '<i class="bi bi-chevron-down enrollment-toggle-icon"></i>';
            html += '</div>';
            html += '<div class="enrollment-subclasses">';

            group.subclasses.forEach(function(sub) {
                var subclassTitle = escapeHtmlEnrollment(enrollmentDisplayLabel(sub));
                if (sub.combie) {
                    var combieText = String(sub.combie).replace(/\s*\([^)]*\)\s*$/, '').trim() || sub.combie;
                    subclassTitle += ' <span class="text-muted font-weight-normal">(' + escapeHtmlEnrollment(combieText) + ')</span>';
                }
                var badgeClass = enrollmentStatusBadgeClass(sub.status);

                html += '<div class="enrollment-subclass-widget" data-enrollment-id="' + escapeHtmlEnrollment(String(sub.enrollmentID || '')) + '">';
                html += '<div class="enrollment-subclass-header" role="button" tabindex="0">';
                html += '<div class="d-flex align-items-center flex-wrap" style="gap:0.5rem;flex:1;min-width:0;">';
                html += '<div style="min-width:0;">';
                html += '<p class="enrollment-subclass-title"><i class="bi bi-mortarboard" style="color:#940000;"></i> ' + subclassTitle + '</p>';
                html += '<div class="enrollment-class-meta">click to view details</div>';
                html += '</div>';
                html += '<span class="badge text-white ' + badgeClass + ' ml-auto ms-auto">' + escapeHtmlEnrollment(sub.status || 'N/A') + '</span>';
                html += '</div>';
                html += '<i class="bi bi-chevron-down enrollment-toggle-icon"></i>';
                html += '</div>';
                html += '<div class="enrollment-subclass-details">';
                html += renderEnrollmentSubclassDetails(sub);
                html += '</div></div>';
            });

            html += '</div></div>';
        });

        $container.html(html);
    }

    $(document).on('change', '#enrollmentYearSelector', function() {
        renderEnrollmentClassWidgets($(this).val() || 'all');
    });

    $(document).on('click', '#studentDetailsTabs .nav-link', function(e) {
        e.preventDefault();
        var target = $(this).attr('data-bs-target') || $(this).attr('data-target');
        if (!target) return;
        $('#studentDetailsTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        $('#studentDetailsTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });

    $(document).on('click', '.enrollment-class-header', function(e) {
        e.preventDefault();
        $(this).closest('.enrollment-class-widget').toggleClass('is-expanded');
    });

    $(document).on('keydown', '.enrollment-class-header', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).closest('.enrollment-class-widget').toggleClass('is-expanded');
        }
    });

    $(document).on('click', '.enrollment-subclass-header', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.enrollment-subclass-widget').toggleClass('is-expanded');
    });

    $(document).on('keydown', '.enrollment-subclass-header', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.enrollment-subclass-widget').toggleClass('is-expanded');
        }
    });

    // Function to load academic years for student
    function loadAcademicYearsForStudent(studentID) {
        if (!studentID) {
            console.error('Student ID is required');
            $('#academicYearSelector').html('<option value="">Error: Student ID missing</option>');
            return;
        }
        
        $.ajax({
            url: {!! json_encode(route("get_student_academic_years", ":id")) !!}.replace(':id', studentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.years) {
                    let selector = $('#academicYearSelector');
                    if (selector.length === 0) {
                        console.error('Academic year selector not found');
                        return;
                    }
                    
                    selector.html('<option value="">Select Academic Year</option>');
                    
                    response.years.forEach(function(year) {
                        let selected = year.is_active ? 'selected' : '';
                        let statusBadge = year.status === 'Active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Closed</span>';
                        selector.append('<option value="' + year.academic_yearID + '" data-status="' + year.status + '" ' + selected + '>' + year.year_name + ' ' + statusBadge + '</option>');
                    });
                    
                    // Enable widgets if active year is selected by default
                    if (response.years.some(y => y.is_active)) {
                        $('.academic-widget').removeClass('disabled').css('opacity', '1');
                        let activeYear = response.years.find(y => y.is_active);
                        if (activeYear) {
                            loadAcademicDataForYear(studentID, activeYear.academic_yearID, activeYear.status);
                        }
                    }
                } else {
                    console.error('Invalid response:', response);
                    $('#academicYearSelector').html('<option value="">No academic years found</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading academic years:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', status);
                $('#academicYearSelector').html('<option value="">Error loading years</option>');
            }
        });
    }
    
    // Handle academic year selection change
    $(document).on('change', '#academicYearSelector', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).val();
        let status = $(this).find('option:selected').data('status');
        
        if (academicYearID) {
            // Enable widgets
            $('.academic-widget').removeClass('disabled').css('opacity', '1');
            loadAcademicDataForYear(studentID, academicYearID, status);
        } else {
            // Reset widgets
            $('.academic-widget').addClass('disabled').css('opacity', '0.6');
            $('#classWidgetContent, #resultsWidgetContent, #attendanceWidgetContent').html('<p class="text-muted text-center py-3">Select Academic Year first</p>');
            // Collapse all widgets
            $('.academic-widget').attr('data-expanded', 'false');
            $('.widget-content').slideUp();
            $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
    });
    
    // Handle widget click to expand/collapse
    $(document).on('click', '.academic-widget .card-header', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let widget = $(this).closest('.academic-widget');
        let isExpanded = widget.attr('data-expanded') === 'true';
        let content = widget.find('.widget-content');
        let icon = widget.find('.widget-toggle-icon');
        let widgetId = widget.attr('id');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        // Check if academic year is selected
        if (!academicYearID) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Academic Year',
                text: 'Please select an academic year first'
            });
            return;
        }
        
        if (isExpanded) {
            // Collapse
            content.slideUp(300, function() {
                widget.attr('data-expanded', 'false');
            });
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            // Expand first
            content.slideDown(300, function() {
                widget.attr('data-expanded', 'true');
            });
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            
            // Load data if not loaded yet
            let contentText = widget.find('.widget-content').text().trim();
            let needsLoading = contentText.includes('Click to load') || 
                              contentText.includes('Select Academic Year') || 
                              contentText.includes('Loading...') ||
                              contentText === '' ||
                              contentText.length < 50; // If content is too short, probably needs loading
            
            console.log('Widget clicked:', widgetId, 'Needs loading:', needsLoading, 'Content:', contentText);
            
            if (needsLoading && academicYearID) {
                if (widgetId === 'classWidget') {
                    console.log('Loading class data...');
                    loadClassDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'resultsWidget') {
                    console.log('Loading results data...');
                    loadResultsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'attendanceWidget') {
                    console.log('Loading attendance data...');
                    loadAttendanceDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'paymentsWidget') {
                    console.log('Loading payments data...');
                    loadPaymentsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'debitsWidget') {
                    console.log('Loading debts data...');
                    loadDebitsDataForWidget(studentID, academicYearID, yearStatus);
                }
            }
        }
    });
    
    // Function to load academic data for selected year (prepare widgets)
    function loadAcademicDataForYear(studentID, academicYearID, yearStatus) {
        // Just prepare widgets - data will load when clicked
        // Make sure widgets are visible and ready
        $('.academic-widget').removeClass('disabled').css('opacity', '1');
        $('#classWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load class data...</p>');
        $('#resultsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load results...</p>');
        $('#attendanceWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load attendance...</p>');
        $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load payments...</p>');
        $('#debitsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load debts...</p>');
        
        // Collapse all widgets initially
        $('.academic-widget').attr('data-expanded', 'false');
        $('.widget-content').hide();
        $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
    }
    
    // Function to load class data for widget
    function loadClassDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#classWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#classWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading classes...</p></div>');
        
        $.ajax({
            url: {!! json_encode(route("get_student_classes_for_year")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Class data response:', response);
                if (response.success && response.classes && response.classes.length > 0) {
                    let content = '<div class="list-group" style="border-radius: 0;">';
                    response.classes.forEach(function(cls) {
                        content += '<div class="list-group-item class-item" data-class-id="' + cls.class_id + '" data-class-name="' + cls.class_name + '" style="border-radius: 0; cursor: pointer;">';
                        content += '<div class="d-flex justify-content-between align-items-center">';
                        content += '<div><i class="bi bi-book text-primary-custom"></i> <strong>' + cls.class_name + '</strong></div>';
                        content += '<i class="bi bi-chevron-right"></i>';
                        content += '</div>';
                        content += '<div class="subclasses-list mt-2" style="display: none;"></div>';
                        content += '</div>';
                    });
                    content += '</div>';
                    $('#classWidgetContent').html(content);
                } else {
                    $('#classWidgetContent').html('<p class="text-muted text-center py-3">No classes found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading classes:', error, xhr.responseText);
                $('#classWidgetContent').html('<p class="text-danger text-center py-3">Error loading classes. Please try again.</p>');
            }
        });
    }
    
    // Function to load results data for widget
    function loadResultsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
        
        $.ajax({
            url: {!! json_encode(route("get_student_terms_for_year")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Results/terms response:', response);
                if (response.success && response.terms && response.terms.length > 0) {
                    let content = '<div class="mb-3">';
                    content += '<label class="form-label fw-bold">Select Term:</label>';
                    content += '<select class="form-select term-selector" style="border-radius: 0;">';
                    content += '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        content += '<option value="' + term.term + '">' + term.term_name + '</option>';
                    });
                    content += '</select>';
                    content += '</div>';
                    content += '<div id="termResultsOptions" style="display: none;"></div>';
                    $('#resultsWidgetContent').html(content);
                } else {
                    $('#resultsWidgetContent').html('<p class="text-muted text-center py-3">No terms/results found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading results:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading results. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load attendance data for widget
    function loadAttendanceDataForWidget(studentID, academicYearID, yearStatus) {
        let content = '<div class="mb-3">';
        content += '<label class="form-label fw-bold">Filter Type:</label>';
        content += '<select class="form-select attendance-filter-type" style="border-radius: 0;">';
        content += '<option value="">Select Filter Type</option>';
        content += '<option value="date_range">Filter by Date Range</option>';
        content += '<option value="month">Filter by Month</option>';
        content += '</select>';
        content += '</div>';
        content += '<div id="attendanceFilterOptions" style="display: none;"></div>';
        content += '<div id="attendanceResults" class="mt-3"></div>';
        $('#attendanceWidgetContent').html(content);
    }
    
    // Function to load payments data for widget
    function loadPaymentsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#paymentsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading payments...</p></div>');
        
        $.ajax({
            url: {!! json_encode(route("get_student_payments_for_year")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Payments response:', response);
                if (response.success) {
                    let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Control Number</th><th>Fee Type</th><th>Amount</th><th>Payment Method</th><th>Receipt</th></tr></thead><tbody>';
                    
                    if (response.payments && response.payments.length > 0) {
                        response.payments.forEach(function(payment) {
                            content += '<tr>';
                            content += '<td>' + (payment.date || 'N/A') + '</td>';
                            content += '<td>' + (payment.control_number || 'N/A') + '</td>';
                            content += '<td>' + (payment.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (payment.amount || 0) + ' TZS</td>';
                            content += '<td>' + (payment.payment_method || 'N/A') + '</td>';
                            content += '<td>' + (payment.receipt_number || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="6" class="text-center text-muted">No payments found for this academic year</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    $('#paymentsWidgetContent').html(content);
                } else {
                    $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3">No payments found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading payments:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading payments. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load debits (debts) data for widget
    function loadDebitsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#debitsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading debts...</p></div>');
        
        $.ajax({
            url: {!! json_encode(route("get_student_debts_for_year")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Debts response:', response);
                if (response.success) {
                    let content = '<div class="alert alert-warning" style="border-radius: 0;"><strong>Total Outstanding Debt: ' + (response.total_debt || 0) + ' TZS</strong></div>';
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Fee Type</th><th>Required Amount</th><th>Paid Amount</th><th>Outstanding</th></tr></thead><tbody>';
                    
                    if (response.debts && response.debts.length > 0) {
                        response.debts.forEach(function(debt) {
                            content += '<tr>';
                            content += '<td>' + (debt.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (debt.required_amount || 0) + ' TZS</td>';
                            content += '<td>' + (debt.paid_amount || 0) + ' TZS</td>';
                            content += '<td class="text-danger"><strong>' + (debt.outstanding || 0) + ' TZS</strong></td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-success"><strong>No outstanding debts</strong></td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    
                    // Add library records section (books not returned)
                    if (response.library_records && response.library_records.length > 0) {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Borrow Date</th><th>Book Title</th><th>Subject</th><th>Class</th></tr></thead><tbody>';
                        
                        response.library_records.forEach(function(record) {
                            content += '<tr>';
                            content += '<td>' + (record.borrow_date || 'N/A') + '</td>';
                            content += '<td>' + (record.book_title || 'N/A') + '</td>';
                            content += '<td>' + (record.subject_name || 'N/A') + '</td>';
                            content += '<td>' + (record.class_name || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div></div>';
                    } else {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<p class="text-muted text-center py-2">No books borrowed or all books have been returned</p></div>';
                    }
                    
                    $('#debitsWidgetContent').html(content);
                } else {
                    $('#debitsWidgetContent').html('<p class="text-muted text-center py-3">No debts found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading debts:', error);
                $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Error loading debts</p>');
            }
        });
    }
    
    // Handle class item click to show subclasses
    $(document).on('click', '.class-item', function() {
        let classID = $(this).data('class-id');
        let className = $(this).data('class-name');
        let subclassesList = $(this).find('.subclasses-list');
        
        if (subclassesList.is(':visible')) {
            subclassesList.slideUp();
            return;
        }
        
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        subclassesList.html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>').slideDown();
        
        $.ajax({
            url: {!! json_encode(route("get_student_subclasses_for_class")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                class_id: classID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.subclasses && response.subclasses.length > 0) {
                    let html = '<ul class="list-group list-group-flush" style="border-radius: 0;">';
                    response.subclasses.forEach(function(subclass) {
                        html += '<li class="list-group-item" style="border-radius: 0;"><i class="bi bi-layers text-info"></i> ' + subclass.subclass_name + '</li>';
                    });
                    html += '</ul>';
                    subclassesList.html(html);
                } else {
                    subclassesList.html('<p class="text-muted text-center py-2">No subclasses found</p>');
                }
            },
            error: function() {
                subclassesList.html('<p class="text-danger text-center py-2">Error loading subclasses</p>');
            }
        });
    });
    
    // Handle term selection for results widget
    $(document).on('change', '.term-selector', function() {
        let term = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (term && academicYearID) {
            // Show filter options for exam results or term report
            let content = '<div class="mb-3">';
            content += '<label class="form-label fw-bold">View Type:</label>';
            content += '<select class="form-select" id="resultViewType" style="border-radius: 0;">';
            content += '<option value="">Select View Type</option>';
            content += '<option value="exam_results">Exam Results</option>';
            content += '<option value="term_report">Term Report</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="examFilterContainer" style="display: none;" class="mb-3">';
            content += '<label class="form-label fw-bold">Select Exam:</label>';
            content += '<select class="form-select" id="examSelector" style="border-radius: 0;">';
            content += '<option value="">Loading...</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="resultsDisplayArea"></div>';
            
            $('#termResultsOptions').html(content).slideDown();
            
            // Load exams for this term
            loadExamsForTerm(studentID, academicYearID, term, yearStatus);
        } else {
            $('#termResultsOptions').slideUp().html('');
        }
    });
    
    // Handle result view type change
    $(document).on('change', '#resultViewType', function() {
        let viewType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (viewType === 'exam_results') {
            $('#examFilterContainer').show();
        } else if (viewType === 'term_report') {
            $('#examFilterContainer').hide();
            // Load term report
            if (term) {
                loadTermReport(studentID, academicYearID, term, yearStatus);
            }
        } else {
            $('#examFilterContainer').hide();
            $('#resultsDisplayArea').html('');
        }
    });
    
    // Handle exam selection change
    $(document).on('change', '#examSelector', function() {
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let examID = $(this).val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        console.log('Exam selected:', examID, 'Term:', term, 'Year:', academicYearID, 'Status:', yearStatus);
        
        if (examID && term && academicYearID) {
            $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
            loadExamResults(studentID, academicYearID, term, examID, yearStatus);
        } else {
            $('#resultsDisplayArea').html('<p class="text-warning text-center py-3">Please select term and exam</p>');
        }
    });
    
    // Function to load exams for term
    function loadExamsForTerm(studentID, academicYearID, term, yearStatus) {
        $.ajax({
            url: {!! json_encode(route("get_exams_for_term")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let examSelector = $('#examSelector');
                    examSelector.html('<option value="">Select Exam</option>');
                    
                    if (response.exams && response.exams.length > 0) {
                        response.exams.forEach(function(exam) {
                            examSelector.append('<option value="' + exam.exam_id + '">' + exam.exam_name + '</option>');
                        });
                    } else {
                        examSelector.append('<option value="">No exams found</option>');
                    }
                }
            }
        });
    }
    
    // Function to load exam results
    function loadExamResults(studentID, academicYearID, term, examID, yearStatus) {
        if (!studentID || !academicYearID || !term || !examID) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $.ajax({
            url: {!! json_encode(route("get_student_exam_results")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                exam_id: examID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Exam results response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Position summary
                    if (response.position && response.total_students) {
                        content += '<div class="alert alert-info mb-3" style="border-radius: 0;">';
                        content += '<strong><i class="bi bi-trophy"></i> Position: ' + response.position + ' out of ' + response.total_students + ' students</strong>';
                        content += '</div>';
                    }
                    
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead><tbody>';
                    
                    if (response.results && response.results.length > 0) {
                        response.results.forEach(function(result) {
                            content += '<tr><td>' + (result.subject_name || 'N/A') + '</td><td>' + (result.marks || 0) + '</td><td>' + (result.grade || 'N/A') + '</td><td>' + (result.remark || '') + '</td></tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-muted">No results found for this exam</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    if (response.results && response.results.length > 0) {
                        content += '<div class="mt-3"><button class="btn btn-primary export-exam-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-exam-id="' + examID + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading exam results:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found for this exam</p>');
            }
        });
    }
    
    // Function to load term report
    function loadTermReport(studentID, academicYearID, term, yearStatus) {
        if (!studentID || !academicYearID || !term) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading term report...</p></div>');
        
        $.ajax({
            url: {!! json_encode(route("get_student_term_report")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Term report response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Student Information Section
                    content += '<div class="card mb-3" style="border-radius: 0;">';
                    content += '<div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-person"></i> Student Information</h5></div>';
                    content += '<div class="card-body">';
                    content += '<div class="row">';
                    content += '<div class="col-md-6"><strong>Student Name:</strong> ' + (response.student_name || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Term:</strong> ' + (response.term || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Year:</strong> ' + (response.year || 'N/A') + '</div>';
                    content += '</div>';
                    if (response.position && response.total_students) {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Position:</strong> ' + response.position + ' out of ' + response.total_students + '</div>';
                        content += '<div class="col-md-3"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-3"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    } else {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-6"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    }
                    content += '</div></div>';
                    
                    // Examinations Table
                    if (response.examinations && response.examinations.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Examinations</h6></div>';
                        content += '<div class="card-body">';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Examination</th><th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.examinations.forEach(function(exam) {
                            content += '<tr>';
                            content += '<td>' + (exam.exam_name || 'N/A') + '</td>';
                            content += '<td>' + (exam.average || 0) + '</td>';
                            content += '<td>' + (exam.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    }
                    
                    // Subject Results Table
                    if (response.report && response.report.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bi bi-book"></i> Subject Results</h6></div>';
                        content += '<div class="card-body">';
                        
                        // Build table header with exam names
                        let examNames = [];
                        if (response.examinations && response.examinations.length > 0) {
                            response.examinations.forEach(function(exam) {
                                examNames.push(exam.exam_name);
                            });
                        }
                        
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th>';
                        examNames.forEach(function(examName) {
                            content += '<th>' + examName + '</th>';
                        });
                        content += '<th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.report.forEach(function(item) {
                            content += '<tr>';
                            content += '<td>' + (item.subject_name || 'N/A') + '</td>';
                            
                            // Add marks for each exam
                            examNames.forEach(function(examName) {
                                if (item.exam_marks && item.exam_marks[examName]) {
                                    let examMark = item.exam_marks[examName];
                                    content += '<td>' + examMark.marks + '-' + examMark.grade + '</td>';
                                } else {
                                    content += '<td>-</td>';
                                }
                            });
                            
                            content += '<td>' + (item.average || 0) + '</td>';
                            content += '<td>' + (item.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    } else {
                        content += '<div class="alert alert-info" style="border-radius: 0;">No report data found for this term</div>';
                    }
                    
                    if (response.report && response.report.length > 0) {
                        // Store response data in a global variable for PDF export
                        window.termReportData = response;
                        content += '<div class="mt-3"><button class="btn btn-primary export-term-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading term report:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found for this term</p>');
            }
        });
    }
    
    // Handle PDF export for exam results
    $(document).on('click', '.export-exam-pdf-btn', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let examID = $(this).data('exam-id');
        let yearStatus = $(this).data('year-status');
        
        // Get results data from table
        let results = [];
        $('#resultsDisplayArea table tbody tr').each(function() {
            let cells = $(this).find('td');
            if (cells.length >= 3) {
                results.push({
                    subject: $(cells[0]).text().trim(),
                    marks: $(cells[1]).text().trim(),
                    grade: $(cells[2]).text().trim(),
                    remark: $(cells[3]).text().trim() || ''
                });
            }
        });
        
        if (results.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No results to export'
            });
            return;
        }
        
        // Get student name
        let studentName = $('#viewStudentModal .school-title').text().trim() || 'Student';
        
        // Generate PDF using jsPDF
        if (typeof jsPDF === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded'
            });
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Header
        doc.setFontSize(16);
        doc.setTextColor(148, 0, 0);
        doc.text('EXAM RESULTS', 105, 15, { align: 'center' });
        
        // Student info
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text('Student: ' + studentName, 14, 25);
        doc.text('Term: ' + term.replace('_', ' ').toUpperCase(), 14, 32);
        
        // Table
        doc.autoTable({
            startY: 40,
            head: [['Subject', 'Marks', 'Grade', 'Remark']],
            body: results.map(r => [r.subject, r.marks, r.grade, r.remark]),
            theme: 'grid',
            headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
            styles: { fontSize: 9 }
        });
        
        // Add footer on all pages
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(128, 128, 128);
            doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
        }
        
        // Save
        let filename = 'Exam_Results_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
        doc.save(filename);
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'PDF exported successfully',
            timer: 2000,
            showConfirmButton: false
        });
    });
    
    // Handle PDF export for term report
    $(document).on('click', '.export-term-pdf-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Export PDF button clicked');
        
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let yearStatus = $(this).data('year-status');
        
        console.log('Button data:', { studentID, academicYearID, term, yearStatus });
        console.log('Stored termReportData:', window.termReportData);
        
        // Get report data from stored response
        let response = window.termReportData;
        
        if (!response) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Report data not found. Please reload the term report first.'
            });
            return;
        }
        
        if (!response.report || response.report.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No report data to export'
            });
            return;
        }
        
        // Get student name and other info from response
        let studentName = response.student_name || 'Student';
        let termName = response.term || term.replace('_', ' ').toUpperCase();
        let yearName = response.year || 'N/A';
        let overallAvg = response.overall_average || 0;
        let overallGrade = response.overall_grade || 'N/A';
        let position = response.position || null;
        let totalStudents = response.total_students || null;
        
        // Generate PDF using jsPDF
        if (typeof window.jspdf === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded. Please refresh the page.'
            });
            console.error('jsPDF library not found');
            return;
        }
        
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Header
            doc.setFontSize(18);
            doc.setTextColor(148, 0, 0);
            doc.text(studentName.toUpperCase() + ' - TERM REPORT DETAILS', 105, 15, { align: 'center' });
            
            // Student Information Section
            let yPos = 30;
            doc.setFontSize(12);
            doc.setTextColor(148, 0, 0);
            doc.text('Student Information', 14, yPos);
            yPos += 8;
            
            doc.setFontSize(10);
            doc.setTextColor(0, 0, 0);
            doc.text('Student Name: ' + studentName, 14, yPos);
            yPos += 6;
            doc.text('Term: ' + termName, 14, yPos);
            yPos += 6;
            doc.text('Year: ' + yearName, 14, yPos);
            yPos += 6;
            if (position && totalStudents) {
                doc.text('Position: ' + position + ' out of ' + totalStudents, 14, yPos);
                yPos += 6;
            }
            doc.text('Overall Average: ' + overallAvg, 14, yPos);
            yPos += 6;
            doc.text('Grade: ' + overallGrade, 14, yPos);
            yPos += 10;
            
            // Examinations Table
            if (response.examinations && response.examinations.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Examinations', 14, yPos);
                yPos += 8;
                
                let examTableData = response.examinations.map(exam => [
                    exam.exam_name || 'N/A',
                    exam.average || 0,
                    exam.grade || 'N/A'
                ]);
                
                doc.autoTable({
                    startY: yPos,
                    head: [['Examination', 'Average', 'Grade']],
                    body: examTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 9 },
                    margin: { left: 14 }
                });
                
                yPos = doc.lastAutoTable.finalY + 10;
            }
            
            // Subject Results Table
            if (response.report && response.report.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Subject Results', 14, yPos);
                yPos += 8;
                
                // Build table header with exam names
                let examNames = [];
                if (response.examinations && response.examinations.length > 0) {
                    response.examinations.forEach(function(exam) {
                        examNames.push(exam.exam_name);
                    });
                }
                
                let tableHead = ['Subject'];
                examNames.forEach(function(examName) {
                    tableHead.push(examName);
                });
                tableHead.push('Average');
                tableHead.push('Grade');
                
                let tableBody = [];
                response.report.forEach(function(item) {
                    let row = [item.subject_name || 'N/A'];
                    
                    // Add marks for each exam
                    examNames.forEach(function(examName) {
                        if (item.exam_marks && item.exam_marks[examName]) {
                            let examMark = item.exam_marks[examName];
                            row.push(examMark.marks + '-' + examMark.grade);
                        } else {
                            row.push('-');
                        }
                    });
                    
                    row.push(item.average || 0);
                    row.push(item.grade || 'N/A');
                    tableBody.push(row);
                });
                
                doc.autoTable({
                    startY: yPos,
                    head: [tableHead],
                    body: tableBody,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 8 },
                    margin: { left: 14 },
                    columnStyles: {
                        0: { cellWidth: 50 }, // Subject column
                    }
                });
            }
            
            // Add footer on all pages
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(128, 128, 128);
                doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
            }
            
            // Save
            let filename = 'Term_Report_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
            doc.save(filename);
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'PDF exported successfully',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error generating PDF:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate PDF: ' + (error.message || 'Unknown error')
            });
        }
    });
    
    // Handle attendance filter type selection
    $(document).on('change', '.attendance-filter-type', function() {
        let filterType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType && academicYearID) {
            let content = '';
            
            if (filterType === 'date_range') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">From Date</label><input type="date" class="form-control" id="attendanceFromDate" style="border-radius: 0;"></div>';
                content += '<div class="col-md-6"><label class="form-label fw-bold">To Date</label><input type="date" class="form-control" id="attendanceToDate" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="date_range" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            } else if (filterType === 'month') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">Month</label><input type="month" class="form-control" id="attendanceMonth" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="month" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            }
            
            $('#attendanceFilterOptions').html(content).slideDown();
        } else {
            $('#attendanceFilterOptions').slideUp().html('');
            $('#attendanceResults').html('');
        }
    });
    
    // Handle load attendance button click
    $(document).on('click', '.load-attendance-btn', function() {
        let filterType = $(this).data('filter-type');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType === 'date_range') {
            loadAttendanceByDateRange(studentID, academicYearID, yearStatus);
        } else if (filterType === 'month') {
            loadAttendanceByMonth(studentID, academicYearID, yearStatus);
        }
    });
    
    // Function to load attendance by date range
    function loadAttendanceByDateRange(studentID, academicYearID, yearStatus) {
        let fromDate = $('#attendanceFromDate').val();
        let toDate = $('#attendanceToDate').val();
        
        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select both from and to dates'
            });
            return;
        }
        
        $.ajax({
            url: {!! json_encode(route("get_student_attendance")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                from_date: fromDate,
                to_date: toDate,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, fromDate, toDate);
                }
            }
        });
    }
    
    // Function to load attendance by month
    function loadAttendanceByMonth(studentID, academicYearID, yearStatus) {
        let month = $('#attendanceMonth').val();
        
        if (!month) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a month'
            });
            return;
        }
        
        $.ajax({
            url: {!! json_encode(route("get_student_attendance")) !!},
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                month: month,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, null, null, month);
                }
            }
        });
    }
    
    // Function to display attendance data
    function displayAttendanceData(attendance, studentID, academicYearID, fromDate, toDate, month) {
        let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Remark</th></tr></thead><tbody>';
        
        if (attendance && attendance.length > 0) {
            attendance.forEach(function(record) {
                content += '<tr><td>' + record.date + '</td><td><span class="badge bg-' + (record.status === 'Present' ? 'success' : 'danger') + '">' + record.status + '</span></td><td>' + (record.check_in || 'N/A') + '</td><td>' + (record.check_out || 'N/A') + '</td><td>' + (record.remark || '') + '</td></tr>';
            });
        } else {
            content += '<tr><td colspan="5" class="text-center">No attendance records found</td></tr>';
        }
        
        content += '</tbody></table></div>';
        content += '<div class="mt-3">';
        content += '<button class="btn btn-primary me-2" onclick="exportAttendancePDF(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button>';
        content += '<button class="btn btn-success" onclick="exportAttendanceExcel(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-excel"></i> Export Excel</button>';
        content += '</div>';
        
        $('#attendanceResults').html(content);
    }
    
    // Export functions (to be implemented)
    function exportResultsPDF(studentID, academicYearID, term, examID, type) {
        // Implementation for PDF export
        window.location.href = {!! json_encode(route("export_student_results_pdf")) !!} + '?student_id=' + studentID +
            '\u0026academic_year_id=' + academicYearID +
            '\u0026term=' + term +
            '\u0026exam_id=' + examID +
            '\u0026type=' + type;
    }
    
    function exportAttendancePDF(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for PDF export
        window.location.href = {!! json_encode(route("export_student_attendance_pdf")) !!} + '?student_id=' + studentID +
            '\u0026academic_year_id=' + academicYearID +
            '\u0026from_date=' + fromDate +
            '\u0026to_date=' + toDate +
            '\u0026month=' + month;
    }
    
    function exportAttendanceExcel(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for Excel export
        window.location.href = {!! json_encode(route("export_student_attendance_excel")) !!} + '?student_id=' + studentID +
            '\u0026academic_year_id=' + academicYearID +
            '\u0026from_date=' + fromDate +
            '\u0026to_date=' + toDate +
            '\u0026month=' + month;
    }

    // Handle Send Student to Fingerprint Device Button Click
    $(document).on('click', '.send-student-to-fingerprint-btn', function(e) {
        e.preventDefault();
        var studentId = $(this).data('student-id');
        var studentName = $(this).data('student-name');
        var fingerprintId = $(this).data('fingerprint-id');
        var $btn = $(this);
        var originalHtml = $btn.html();

        // Check if student already has fingerprint_id
        if (fingerprintId && fingerprintId.trim() !== '') {
            Swal.fire({
                icon: 'info',
                title: 'Fingerprint ID Already Assigned',
                html: 'Student <strong>' + studentName + '</strong> already has a fingerprint ID: <strong>' + fingerprintId + '</strong>',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Send to Fingerprint Device?',
            html: 'Are you sure you want to send <strong>' + studentName + '</strong> to the fingerprint device?<br><br><small class="text-muted">This will generate a unique fingerprint ID and register the student to the biometric device.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-fingerprint"></i> Yes, Send',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');

                return $.ajax({
                    url: {!! json_encode(route('send_student_to_fingerprint')) !!},
                    type: "POST",
                    data: {
                        _token: {!! json_encode(csrf_token()) !!},
                        student_id: studentId
                    },
                    success: function(response) {
                        return response;
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to send student to fingerprint device.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.showValidationMessage(errorMsg);
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                var response = result.value;
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: 'Student <strong>' + studentName + '</strong> has been successfully sent to the fingerprint device.<br><br><small class="text-muted">Fingerprint ID: <strong>' + (response.fingerprint_id || 'N/A') + '</strong></small>',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to show updated data
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Partial Success',
                        html: response.message || 'Student was processed but there may have been issues with the fingerprint device.',
                        confirmButtonText: 'OK'
                    });
                    $btn.prop('disabled', false).html(originalHtml);
                }
            } else {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    })(window.jQuery || window.$);
