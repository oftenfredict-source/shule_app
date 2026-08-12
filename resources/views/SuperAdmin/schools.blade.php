@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <style>
        :root {
            --primary-red: #940000;
        }
        .school-logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
            background: #fff;
        }
        .table thead th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6 !important;
        }
        .btn-primary-custom {
            background: var(--primary-red);
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background: #7a0000;
            color: white;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
            flex: 1;
        }
        .detail-value {
            font-weight: 500;
            color: #000;
            flex: 1;
            text-align: right;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .asset-preview-sm {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            background: #fff;
        }
        .asset-preview-sm img {
            max-width: 100%;
            max-height: 100%;
        }
        .cursor-pointer { cursor: pointer; }
        .expand-list {
            background: #fff;
            padding: 10px;
            border: 1px solid #dee2e6;
            margin-top: 5px;
            display: none;
        }
        /* Remove border radius as requested */
        .card, .btn, .form-control, .modal-content, .dropdown-menu, .badge, .alert {
            border-radius: 0 !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">Registered Schools</h4>
            <p class="text-muted small mb-0">Full management of institutional data</p>
        </div>
        <div class="d-flex flex-wrap" style="gap:8px;">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCopyPublicRegLink"
                    data-url="https://test.shulexpert.com/register-school">
                <i class="bx bx-link me-1"></i> Copy Register Link
            </button>
            <a href="{{ route('superadmin.schools.registration_requests') }}" class="btn btn-outline-warning btn-sm">
                <i class="bx bx-file me-1"></i> School Requests
            </a>
            <a href="{{ route('superadmin.schools.register') }}" class="btn btn-primary-custom">
                <i class="bx bx-plus me-2"></i> Register New School
            </a>
        </div>
    </div>

    <script>
    document.getElementById('btnCopyPublicRegLink')?.addEventListener('click', function () {
        var url = this.getAttribute('data-url') || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                alert('Copied: ' + url);
            });
        } else {
            prompt('Copy this link:', url);
        }
    });
    </script>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="schoolsTable" class="table table-bordered table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th width="60">Logo</th>
                            <th>School Name & Reg No.</th>
                            <th width="120">Students</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schools as $i => $school)
                            @php
                                $sid = $school->schoolID;
                                $sCount = $studentsBySchool[$sid] ?? 0;
                                $tCount = $teachersBySchool[$sid] ?? 0;
                                $stCount = $staffBySchool[$sid] ?? 0;
                                $pCount = $parentsBySchool[$sid] ?? 0;
                                $classes = $classesBySchool[$sid] ?? collect();
                                $subclasses = $subclassesBySchool[$sid] ?? collect();
                                $placeholderLogo = asset('images/school-placeholder.svg');
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="text-center">
                                    <img src="{{ $school->logo_url }}" class="school-logo-circle" alt="School logo"
                                         onerror="this.onerror=null;this.src='{{ $placeholderLogo }}'">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $school->school_name }}</div>
                                    <div class="text-muted small">{{ $school->registration_number ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge px-2 py-1 text-white" style="background-color: #940000;">
                                        {{ number_format($sCount) }} Students
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-cog"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow border">
                                            <a class="dropdown-item view-more-btn" href="#" 
                                               data-bs-toggle="modal" data-bs-target="#viewMoreModal"
                                               data-school='@json($school)'
                                               data-students="{{ number_format($sCount) }}"
                                               data-teachers="{{ number_format($tCount) }}"
                                               data-staff="{{ number_format($stCount) }}"
                                               data-parents="{{ number_format($pCount) }}"
                                               data-classes-count="{{ $classes->count() }}"
                                               data-subclasses-count="{{ $subclasses->count() }}"
                                               data-classes-list="{{ json_encode($classes->pluck('class_name')) }}"
                                               data-subclasses-list="{{ json_encode($subclasses->pluck('full_name')) }}"
                                            >
                                                <i class="bx bx-show me-2 text-primary"></i> View More
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item edit-btn" href="#"
                                               data-bs-toggle="modal" data-bs-target="#editModal"
                                               data-school='@json($school)'
                                               data-logo-url="{{ $school->logo_url }}"
                                               data-stamp-url="{{ $school->stamp_url ?? '' }}"
                                               data-sign-url="{{ $school->sign_url ?? '' }}"
                                            >
                                                <i class="bx bx-edit me-2 text-warning"></i> Full Edit
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- View More Modal -->
    <div class="modal fade" id="viewMoreModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--primary-red);">Full School Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <img id="v_logo" src="" class="img-thumbnail mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                            <h6 id="v_name_header" class="fw-bold mb-1"></h6>
                            <span id="v_reg_badge" class="badge bg-secondary mb-3"></span>
                            
                            <div class="asset-preview-sm mx-auto">
                                <img id="v_stamp" src="" title="School Stamp">
                            </div>
                            <small class="text-muted d-block mb-3">School Stamp</small>
                            
                            <div class="asset-preview-sm mx-auto">
                                <img id="v_sign" src="" title="Official Signature">
                            </div>
                            <small class="text-muted d-block">Official Signature</small>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-row"><span class="detail-label">Ownership:</span> <span class="detail-value" id="v_ownership"></span></div>
                                    <div class="detail-row"><span class="detail-label">Type:</span> <span class="detail-value" id="v_type"></span></div>
                                    <div class="detail-row"><span class="detail-label">Environment:</span> <span class="detail-value" id="v_env"></span></div>
                                    <div class="detail-row"><span class="detail-label">Status:</span> <span class="detail-value" id="v_status"></span></div>
                                    <div class="detail-row"><span class="detail-label">Est. Year:</span> <span class="detail-value" id="v_est"></span></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row"><span class="detail-label">Region:</span> <span class="detail-value" id="v_region"></span></div>
                                    <div class="detail-row"><span class="detail-label">District:</span> <span class="detail-value" id="v_district"></span></div>
                                    <div class="detail-row"><span class="detail-label">Ward:</span> <span class="detail-value" id="v_ward"></span></div>
                                    <div class="detail-row"><span class="detail-label">Village:</span> <span class="detail-value" id="v_village"></span></div>
                                    <div class="detail-row"><span class="detail-label">Address:</span> <span class="detail-value" id="v_address"></span></div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="detail-row"><span class="detail-label">Phone:</span> <span class="detail-value" id="v_phone"></span></div>
                                    <div class="detail-row"><span class="detail-label">Email:</span> <span class="detail-value" id="v_email"></span></div>
                                    <div class="detail-row"><span class="detail-label">Sender ID:</span> <span class="detail-value" id="v_sender"></span></div>
                                    <div class="detail-row"><span class="detail-label">2FA Enabled:</span> <span class="detail-value" id="v_2fa"></span></div>
                                    <div class="detail-row"><span class="detail-label">Must Change Default Password:</span> <span class="detail-value" id="v_must_change_password"></span></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row"><span class="detail-label">Students:</span> <span class="detail-value text-primary" id="v_students"></span></div>
                                    <div class="detail-row"><span class="detail-label">Teachers:</span> <span class="detail-value text-primary" id="v_teachers"></span></div>
                                    <div class="detail-row"><span class="detail-label">Staff:</span> <span class="detail-value text-primary" id="v_staff"></span></div>
                                    <div class="detail-row"><span class="detail-label">Login Access:</span> <span class="detail-value" id="v_login_status"></span></div>
                                    <div class="detail-row" id="v_suspension_reason_row" style="display: none;"><span class="detail-label">Suspension Reason:</span> <span class="detail-value text-danger" id="v_suspension_reason"></span></div>
                                    <div class="detail-row"><span class="detail-label">Parents:</span> <span class="detail-value text-primary" id="v_parents"></span></div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="detail-row cursor-pointer text-primary" onclick="$('#v_classes_list').slideToggle()">
                                <span class="detail-label">Total Classes (Click to expand):</span>
                                <span class="detail-value" id="v_classes_count"></span>
                            </div>
                            <div id="v_classes_list" class="expand-list"></div>

                            <div class="detail-row cursor-pointer text-primary mt-2" onclick="$('#v_subclasses_list').slideToggle()">
                                <span class="detail-label">Total Sub-Classes (Click to expand):</span>
                                <span class="detail-value" id="v_subclasses_count"></span>
                            </div>
                            <div id="v_subclasses_list" class="expand-list"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--primary-red);">Edit School Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="schoolID" id="e_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">SCHOOL NAME</label>
                                <input type="text" class="form-control" name="school_name" id="e_name" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="fw-bold small">ENVIRONMENT</label>
                                <select class="form-select" name="environment" id="e_env">
                                    <option value="Demo">Demo</option>
                                    <option value="Live">Live</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="fw-bold small">STATUS</label>
                                <select class="form-select" name="status" id="e_status">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">SENDER ID</label>
                                <input type="text" class="form-control" name="senderID" id="e_sender" maxlength="11">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">LOGIN ACCESS</label>
                                <select class="form-select" name="login_status" id="e_login_status">
                                    <option value="1">Grant Access (True)</option>
                                    <option value="0">Deny Access (False)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" id="e_suspension_reason_row" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label class="fw-bold small text-danger">SUSPENSION REASON (Required if access denied)</label>
                                <textarea class="form-control border-danger" name="suspension_reason" id="e_suspension_reason" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3 pt-2">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="e_2fa" name="two_factor_enabled" value="1">
                                    <label class="form-check-label fw-bold" for="e_2fa">Two-Factor Authentication (2FA)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" id="e_must_change_password" name="must_change_default_password" value="1">
                                    <label class="form-check-label fw-bold" for="e_must_change_password">Must Change Default Password</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" id="e_under_org" name="is_under_organization" value="1">
                                    <label class="form-check-label fw-bold" for="e_under_org">Is under organization?</label>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="e_org_fields" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold small">ORGANIZATION NAME</label>
                                <input type="text" class="form-control" name="organization_name" id="e_org_name" placeholder="e.g. Roman Catholic Diocese">
                            </div>
                            <div class="col-md-6 mb-3 text-center">
                                <label class="fw-bold small">ORGANIZATION LOGO</label>
                                <div class="asset-preview-sm mx-auto mb-2 border">
                                    <img id="p_org_logo" src="">
                                </div>
                                <input type="file" class="form-control form-control-sm" name="organization_logo" onchange="readURL(this, 'p_org_logo')">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12 mb-3">
                                @include('SuperAdmin.partials.school_social_media_fields', [
                                    'ssmId' => 'edit_ssm',
                                    'ssmEnabled' => false,
                                    'ssmItems' => [],
                                ])
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <label class="fw-bold small">LOGO</label>
                                <div class="asset-preview-sm mx-auto mb-2 border">
                                    <img id="p_logo" src="">
                                </div>
                                <input type="file" class="form-control form-control-sm" name="school_logo" onchange="readURL(this, 'p_logo')">
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="fw-bold small">STAMP</label>
                                <div class="asset-preview-sm mx-auto mb-2 border">
                                    <img id="p_stamp" src="">
                                </div>
                                <input type="file" class="form-control form-control-sm" name="school_stamp" onchange="readURL(this, 'p_stamp')">
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="fw-bold small">SIGNATURE</label>
                                <div class="asset-preview-sm mx-auto mb-2 border">
                                    <img id="p_sign" src="">
                                </div>
                                <input type="file" class="form-control form-control-sm" name="school_signature" onchange="readURL(this, 'p_sign')">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editForm" class="btn btn-primary-custom" id="btn_update">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- DataTables JS (Bootstrap 5 Versions) -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
    function readURL(input, target) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#' + target).attr('src', e.target.result); }
            reader.readAsDataURL(input.files[0]);
        }
    }

    const PLACEHOLDER_LOGO = @json(asset('images/school-placeholder.svg'));

    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#schoolsTable').DataTable({
                pageLength: 25,
                language: { search: "Filter:" }
            });
        }

        $(document).on('click', '.view-more-btn', function(e) {
            e.preventDefault();
            const b = $(this);
            const s = b.data('school');
            
            $('#v_logo').attr('src', s.logo_url || PLACEHOLDER_LOGO);
            $('#v_stamp').attr('src', s.stamp_url || '');
            $('#v_sign').attr('src', s.sign_url || '');
            
            $('#v_name_header').text(s.school_name);
            $('#v_reg_badge').text(s.registration_number || 'REG-N/A');
            
            $('#v_ownership').text(s.ownership);
            $('#v_type').text(s.school_type);
            $('#v_env').text(s.environment);
            $('#v_status').text(s.status);
            $('#v_est').text(s.established_year || 'N/A');
            
            $('#v_region').text(s.region);
            $('#v_district').text(s.district);
            $('#v_ward').text(s.ward || 'N/A');
            $('#v_village').text(s.village || 'N/A');
            $('#v_address').text(s.address || 'N/A');
            
            $('#v_phone').text(s.phone || 'N/A');
            $('#v_email').text(s.email || 'N/A');
            $('#v_sender').text(s.senderID || 'N/A');
            $('#v_2fa').text(s.two_factor_enabled ? 'Yes' : 'No');
            $('#v_must_change_password').text(s.must_change_default_password ? 'Yes' : 'No');
            
            $('#v_login_status').html(s.login_status != 0 ? '<span class="badge bg-success">ACTIVE</span>' : '<span class="badge bg-danger">SUSPENDED</span>');
            if (s.login_status == 0) {
                $('#v_suspension_reason').text(s.suspension_reason || 'No reason specified');
                $('#v_suspension_reason_row').show();
            } else {
                $('#v_suspension_reason_row').hide();
            }

            $('#v_students').text(b.data('students'));
            $('#v_teachers').text(b.data('teachers'));
            $('#v_staff').text(b.data('staff'));
            $('#v_parents').text(b.data('parents'));
            
            $('#v_classes_count').text(b.data('classes-count'));
            $('#v_subclasses_count').text(b.data('subclasses-count'));

            // Lists
            const cList = JSON.parse(b.attr('data-classes-list'));
            $('#v_classes_list').html(cList.length ? cList.map(c => `<div class="p-1 border-bottom small">${c}</div>`).join('') : 'None');
            
            const scList = JSON.parse(b.attr('data-subclasses-list'));
            $('#v_subclasses_list').html(scList.length ? scList.map(sc => `<div class="p-1 border-bottom small">${sc}</div>`).join('') : 'None');
            
            $('.expand-list').hide();
        });

        $(document).on('click', '.edit-btn', function(e) {
            e.preventDefault();
            const b = $(this);
            const s = b.data('school');
            $('#e_id').val(s.schoolID);
            $('#e_name').val(s.school_name);
            $('#e_env').val(s.environment);
            $('#e_status').val(s.status);
            $('#e_sender').val(s.senderID);
            $('#e_2fa').prop('checked', s.two_factor_enabled == 1);
            $('#e_must_change_password').prop('checked', s.must_change_default_password == 1);
            $('#e_under_org').prop('checked', s.is_under_organization == 1 || s.is_under_organization === true);
            $('#e_org_name').val(s.organization_name || '');
            $('#p_org_logo').attr('src', s.org_logo_url || '');
            if (s.is_under_organization == 1 || s.is_under_organization === true) {
                $('#e_org_fields').show();
            } else {
                $('#e_org_fields').hide();
            }
            if (window.SchoolSocialMediaUI) {
                window.SchoolSocialMediaUI.loadItems(
                    $('#edit_ssm_wrap'),
                    !!(s.enable_social_media == 1 || s.enable_social_media === true),
                    s.social_media || []
                );
            }
            $('#e_login_status').val(s.login_status == 0 ? '0' : '1');
            $('#e_suspension_reason').val(s.suspension_reason || '');
            
            if (s.login_status == 0) {
                $('#e_suspension_reason_row').show();
                $('#e_suspension_reason').attr('required', true);
            } else {
                $('#e_suspension_reason_row').hide();
                $('#e_suspension_reason').removeAttr('required');
            }

            $('#p_logo').attr('src', b.data('logo-url') || PLACEHOLDER_LOGO);
            $('#p_stamp').attr('src', b.data('stamp-url') || '');
            $('#p_sign').attr('src', b.data('sign-url') || '');
        });

        $(document).on('change', '#e_under_org', function() {
            if ($(this).is(':checked')) {
                $('#e_org_fields').slideDown();
            } else {
                $('#e_org_fields').slideUp();
            }
        });

        $(document).on('change', '#e_login_status', function() {
            if ($(this).val() === '0') {
                $('#e_suspension_reason_row').slideDown();
                $('#e_suspension_reason').attr('required', true).focus();
            } else {
                $('#e_suspension_reason_row').slideUp();
                $('#e_suspension_reason').removeAttr('required');
            }
        });

        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btn_update');
            $btn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: '{{ route("superadmin.schools.update_comprehensive") }}',
                type: 'POST',
                data: (function() {
                    const fd = new FormData(document.getElementById('editForm'));
                    if (!$('#e_under_org').is(':checked')) {
                        fd.set('is_under_organization', '0');
                    }
                    if (!$('#edit_ssm_enable').is(':checked')) {
                        fd.set('enable_social_media', '0');
                    }
                    return fd;
                })(),
                processData: false, contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if(res.success) {
                        Swal.fire('Updated', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        $btn.prop('disabled', false).text('Save Changes');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text('Save Changes');
                    let msg = 'Update failed';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errs = xhr.responseJSON.errors;
                            msg = typeof errs === 'object'
                                ? Object.values(errs).flat().join('\n')
                                : String(errs);
                        } else if (xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
</script>
@endpush
@endsection
