@include('includes.teacher_nav')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .profile-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        background: #fff;
    }

    .profile-header-bg {
        height: 150px;
        background: linear-gradient(135deg, #940000 0%, #610000 100%);
        position: relative;
    }

    .profile-img-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: -75px auto 15px;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: #f8f9fa;
        overflow: hidden;
    }

    .profile-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-img-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.5);
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
    }

    .profile-img-container:hover .profile-img-overlay {
        opacity: 1;
    }

    .profile-img-overlay i {
        color: #fff;
        font-size: 18px;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }

    .profile-role {
        color: #666;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .info-item {
        padding: 15px;
        border-radius: 10px;
        background: #f8f9fa;
        transition: transform 0.2s;
    }

    .info-item:hover {
        transform: translateY(-2px);
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .info-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 15px;
        color: #333;
        font-weight: 600;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        margin: 5px;
    }

    .btn-edit-profile {
        background: #940000;
        color: #fff;
        border: none;
    }

    .btn-edit-profile:hover {
        background: #7a0000;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-download-pdf {
        background: #fff;
        color: #940000;
        border: 1px solid #940000;
    }

    .btn-download-pdf:hover {
        background: #f8f1f1;
        color: #940000;
    }

    .bg-primary-custom {
        background-color: #940000 !important;
        color: #fff !important;
    }

    .btn-primary-custom {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }

    .btn-primary-custom:hover,
    .btn-primary-custom:focus {
        background-color: #7a0000 !important;
        border-color: #7a0000 !important;
        color: #fff !important;
    }

    #editModal .modal-header {
        background-color: #940000 !important;
        color: #fff !important;
        border-bottom: none;
    }

    #editModal .modal-header .modal-title,
    #editModal .modal-header .close,
    #editModal .modal-header .close span {
        color: #fff !important;
        opacity: 1;
        text-shadow: none;
    }

    #editModal .modal-footer .btn-primary-custom {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
    }

    #imageUploadInput {
        display: none;
    }

    .password-section {
        margin-top: 30px;
        border-top: 1px solid #eee;
        padding-top: 30px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="profile-card">
                <div class="profile-header-bg"></div>
                
                <div class="text-center pb-4">
                    <div class="profile-img-container" onclick="document.getElementById('imageUploadInput').click();">
                        @php
                            $profileImage = $teacher->image
                                ? asset('userImages/'.$teacher->image)
                                : (($teacher->gender ?? '') === 'Female' ? asset('images/female.png') : asset('images/male.png'));
                        @endphp
                        <img src="{{ $profileImage }}" id="profileDisplayImg" alt="Profile Image">
                        <div class="profile-img-overlay">
                            <i class="fa fa-camera"></i>
                        </div>
                    </div>
                    <input type="file" id="imageUploadInput" accept="image/*">

                    <h2 class="profile-name">{{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}</h2>
                    <p class="profile-role">C/N: {{ $teacher->employee_number ?? 'N/A' }} | {{ $teacher->position ?? ($teacher->qualification ?? 'Teacher') }}</p>

                    @php
                        $profilePct = (int) ($teacher->tange_completion_percent ?? 0);
                        $profileBar = $profilePct >= 100 ? 'bg-success' : ($profilePct >= 50 ? 'bg-warning' : 'bg-danger');
                        $retireAlert = $teacher->retirement_alert ?? null;
                    @endphp
                    <div class="px-4 mb-3" style="max-width:420px;margin:0 auto;">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>TANGE registration</span>
                            <strong>{{ $profilePct }}%</strong>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar {{ $profileBar }}" style="width:{{ $profilePct }}%"></div>
                        </div>
                        @if($profilePct < 100)
                            <p class="small text-muted mt-2 mb-0">Complete missing details to reach 100%.</p>
                        @endif
                    </div>
                    @if($retireAlert)
                        <div class="alert alert-{{ $retireAlert['level'] === 'danger' ? 'danger' : 'warning' }} mx-4 py-2">
                            <strong>HR Alert:</strong> {{ $retireAlert['message'] }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="{{ route('teacher.profile.download_pdf') }}" class="btn btn-action btn-download-pdf js-download-tange" data-filename="TANGE_My_Particulars">
                            <i class="fa fa-file-pdf-o"></i> Download My TANGE
                        </a>
                        <button class="btn btn-action btn-edit-profile" data-toggle="modal" data-target="#editModal">
                            <i class="fa fa-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Sex</div>
                        <div class="info-value">{{ $teacher->gender ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">{{ $teacher->phone_number ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ $teacher->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Check Number (C/N)</div>
                        <div class="info-value">{{ $teacher->employee_number ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">TSC Number (TSC/N)</div>
                        <div class="info-value">{{ $teacher->tsc_number ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Establishment Rank</div>
                        <div class="info-value">{{ $teacher->establishment_rank ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Salary Scale</div>
                        <div class="info-value">{{ $teacher->salary_scale ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Position of Authority</div>
                        <div class="info-value">{{ $teacher->position ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Work Station</div>
                        <div class="info-value">{{ $teacher->work_station ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Highest Education</div>
                        <div class="info-value">{{ $teacher->qualification ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Subjects Studied</div>
                        <div class="info-value">{{ $teacher->specialization ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Subjects Teaching</div>
                        <div class="info-value">{{ $teacher->subjects_teaching ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Age</div>
                        <div class="info-value">{{ $teacher->age !== null ? $teacher->age . ' years' : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Work Experience</div>
                        <div class="info-value">{{ $teacher->years_of_experience !== null ? $teacher->years_of_experience . ' years' : ($teacher->experience ?? 'N/A') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">{{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Employment</div>
                        <div class="info-value">{{ $teacher->date_hired ? \Carbon\Carbon::parse($teacher->date_hired)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Confirmation</div>
                        <div class="info-value">{{ $teacher->date_confirmed ? \Carbon\Carbon::parse($teacher->date_confirmed)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Current Rank</div>
                        <div class="info-value">{{ $teacher->date_current_rank ? \Carbon\Carbon::parse($teacher->date_current_rank)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Retirement</div>
                        <div class="info-value">{{ $teacher->date_of_retirement ? \Carbon\Carbon::parse($teacher->date_of_retirement)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Place of Residence</div>
                        <div class="info-value">{{ $teacher->address ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">National ID</div>
                        <div class="info-value">{{ $teacher->national_id ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge {{ ($teacher->status ?? '') === 'Active' ? 'badge-success' : 'badge-danger' }}">
                                {{ $teacher->status ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-4 password-section">
                    <h5 class="mb-4">Account Security</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="teacherChangePasswordForm" method="POST" action="{{ route('teacher.change_password') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="info-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label class="info-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" id="new_pw" required autocomplete="new-password">
                                    <div class="mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div id="pw_bar" class="progress-bar" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-light rounded" id="teacher_requirements_card" style="border: 1px solid #e3e6f0;">
                                        <h6 class="font-weight-bold mb-2 small text-secondary">PASSWORD REQUIREMENTS:</h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="requirement-item text-danger" id="t_req_len">
                                                <i class="fa fa-times-circle mr-2" id="t_icon_len"></i> At least 8 characters
                                            </li>
                                            <li class="requirement-item text-danger" id="t_req_upper">
                                                <i class="fa fa-times-circle mr-2" id="t_icon_upper"></i> At least one uppercase letter (A-Z)
                                            </li>
                                            <li class="requirement-item text-danger" id="t_req_lower">
                                                <i class="fa fa-times-circle mr-2" id="t_icon_lower"></i> At least one lowercase letter (a-z)
                                            </li>
                                            <li class="requirement-item text-danger" id="t_req_num">
                                                <i class="fa fa-times-circle mr-2" id="t_icon_num"></i> At least one number (0-9)
                                            </li>
                                            <li class="requirement-item text-danger" id="t_req_spec">
                                                <i class="fa fa-times-circle mr-2" id="t_icon_spec"></i> At least one special character (@, $, !, %, etc.)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="info-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="new_password_confirmation" id="teacher_confirm_password" required autocomplete="new-password">
                                    <div class="mt-2 small" id="teacher_confirm_feedback" style="display: none;">
                                        <span id="teacher_confirm_status"></span>
                                    </div>
                                </div>
                                <button type="submit" id="teacherSubmitBtn" class="btn btn-action btn-edit-profile m-0" disabled>Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#940000;color:#fff;">
                <h5 class="modal-title text-white mb-0">Edit Profile / TANGE Particulars</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;">
                    <span aria-hidden="true" style="color:#fff;">&times;</span>
                </button>
            </div>
            <form id="teacherSelfUpdateForm" action="{{ route('update_teacher') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <input type="hidden" name="status" value="{{ $teacher->status ?? 'Active' }}">
                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    <div class="row g-3">
                        <div class="col-12 form-group text-center mb-3">
                            @php
                                $editPreviewImage = $teacher->image
                                    ? asset('userImages/'.$teacher->image)
                                    : (($teacher->gender ?? '') === 'Female' ? asset('images/female.png') : asset('images/male.png'));
                            @endphp
                            <img id="editProfileImagePreview" src="{{ $editPreviewImage }}" alt="Profile"
                                 style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:3px solid #940000;">
                            <div class="mt-2">
                                <label class="form-label d-block font-weight-bold">Profile Photo</label>
                                <input type="file" class="form-control-file" name="image" id="edit_profile_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted d-block">JPG or PNG, max 2MB. Leave empty to keep current photo.</small>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" value="{{ $teacher->first_name }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="{{ $teacher->middle_name }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" value="{{ $teacher->last_name }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Sex <span class="text-danger">*</span></label>
                            <select class="form-control" name="gender" required>
                                <option value="Male" {{ ($teacher->gender ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ ($teacher->gender ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_number" value="{{ $teacher->phone_number }}" pattern="^255\d{9}$" maxlength="12" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ $teacher->email }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>National ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="national_id" value="{{ $teacher->national_id }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Bank Account Number</label>
                            <input type="text" class="form-control" name="bank_account_number" value="{{ $teacher->bank_account_number }}">
                        </div>

                        @include('partials.teacher_tange_fields', ['prefix' => 'profile_', 'teacher' => $teacher])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn text-white" style="background-color:#940000;border-color:#940000;">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    function tangeYearsFromDate(dateStr) {
        if (!dateStr) return null;
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return null;
        var now = new Date();
        var years = now.getFullYear() - d.getFullYear();
        var m = now.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < d.getDate())) years--;
        return years < 0 ? 0 : years;
    }
    function refreshTangeAutoFields() {
        $('.tange-dob').each(function () {
            var target = $(this).data('age-target');
            var y = tangeYearsFromDate($(this).val());
            if (target) $('#' + target).val(y !== null ? y + ' years' : '');
        });
        $('.tange-hired').each(function () {
            var target = $(this).data('exp-target');
            var y = tangeYearsFromDate($(this).val());
            if (target) $('#' + target).val(y !== null ? y + ' years' : '');
        });
    }
    $(document).on('change input', '.tange-dob, .tange-hired', refreshTangeAutoFields);
    refreshTangeAutoFields();

    $('#edit_profile_image').on('change', function () {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Image must not exceed 2MB.', 'error');
            $(this).val('');
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#editProfileImagePreview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $('#teacherSelfUpdateForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var original = $btn.html();
        $btn.prop('disabled', true).html('Saving...');

        var formData = new FormData($form[0]);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html(original);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.success,
                        confirmButtonColor: '#940000'
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Update failed', 'error');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(original);
                var msg = 'Update failed';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                    else if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).join('\n');
                    }
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Image Upload AJAX (click avatar)
    $('#imageUploadInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('teacher_image', file);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            Swal.fire({
                title: 'Updating profile picture...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ route('teacher.profile.update_image') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const url = response.image_url + (response.image_url.indexOf('?') >= 0 ? '&' : '?') + 'v=' + Date.now();
                        $('#profileDisplayImg').attr('src', url);
                        // Keep sidebar + header in sync (same display as profile)
                        $('.sidebar-profile img.profile-image, .user-area img.user-avatar').attr('src', url);
                        Swal.fire('Success!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Upload failed', 'error');
                }
            });
        }
    });

    // Password Strength & Realtime Validation
    const teacherNewPassword = $('#new_pw');
    const teacherConfirmPassword = $('#teacher_confirm_password');
    const teacherSubmitBtn = $('#teacherSubmitBtn');

    let teacherState = {
        length: false,
        upper: false,
        lower: false,
        number: false,
        special: false,
        match: false
    };

    function updateTeacherRequirementUI(elementId, iconId, isMet) {
        const element = $(elementId);
        const icon = $(iconId);
        if (isMet) {
            element.removeClass('text-danger').addClass('text-success');
            icon.removeClass('fa-times-circle text-danger').addClass('fa-check-circle text-success');
        } else {
            element.removeClass('text-success').addClass('text-danger');
            icon.removeClass('fa-check-circle text-success').addClass('fa-times-circle text-danger');
        }
    }

    function checkTeacherPassword() {
        const pw = teacherNewPassword.val();
        
        // 1. Length
        teacherState.length = pw.length >= 8;
        updateTeacherRequirementUI('#t_req_len', '#t_icon_len', teacherState.length);

        // 2. Upper case
        teacherState.upper = /[A-Z]/.test(pw);
        updateTeacherRequirementUI('#t_req_upper', '#t_icon_upper', teacherState.upper);

        // 3. Lower case
        teacherState.lower = /[a-z]/.test(pw);
        updateTeacherRequirementUI('#t_req_lower', '#t_icon_lower', teacherState.lower);

        // 4. Number
        teacherState.number = /[0-9]/.test(pw);
        updateTeacherRequirementUI('#t_req_num', '#t_icon_num', teacherState.number);

        // 5. Special Character
        teacherState.special = /[^a-zA-Z0-9]/.test(pw);
        updateTeacherRequirementUI('#t_req_spec', '#t_icon_spec', teacherState.special);

        // Strength progress bar
        let score = 0;
        if (pw) {
            if (teacherState.length) score += 25;
            if (teacherState.upper) score += 15;
            if (teacherState.lower) score += 15;
            if (teacherState.number) score += 15;
            if (teacherState.special) score += 15;
            if (pw.length >= 12) score += 15;
        }

        const p = Math.min(100, score);
        $('#pw_bar').css('width', p + '%');
        if (p < 35) $('#pw_bar').removeClass('bg-warning bg-success').addClass('bg-danger');
        else if (p < 70) $('#pw_bar').removeClass('bg-danger bg-success').addClass('bg-warning');
        else $('#pw_bar').removeClass('bg-danger bg-warning').addClass('bg-success');

        checkTeacherMatch();
    }

    function checkTeacherMatch() {
        const pw = teacherNewPassword.val();
        const confirm = teacherConfirmPassword.val();
        const feedback = $('#teacher_confirm_feedback');
        const statusSpan = $('#teacher_confirm_status');

        if (!confirm) {
            feedback.hide();
            teacherState.match = false;
            toggleTeacherSubmit();
            return;
        }

        feedback.show();
        if (pw === confirm) {
            teacherState.match = true;
            statusSpan.html('<i class="fa fa-check-circle text-success mr-2"></i> Passwords match').removeClass('text-danger').addClass('text-success');
        } else {
            teacherState.match = false;
            statusSpan.html('<i class="fa fa-times-circle text-danger mr-2"></i> Passwords do not match').removeClass('text-success').addClass('text-danger');
        }

        toggleTeacherSubmit();
    }

    function toggleTeacherSubmit() {
        const allPassed = teacherState.length && teacherState.upper && teacherState.lower && teacherState.number && teacherState.special && teacherState.match;
        teacherSubmitBtn.prop('disabled', !allPassed);
    }

    // Input event listeners
    teacherNewPassword.on('input', checkTeacherPassword);
    teacherConfirmPassword.on('input', checkTeacherMatch);

    // Password Change AJAX
    $('#teacherChangePasswordForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#teacherSubmitBtn');
        const originalBtnHtml = submitBtn.html();

        // Clear previous alerts
        $('.alert-dynamic').remove();

        // Loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Updating...').attr('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.html(originalBtnHtml).attr('disabled', false);

                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#940000',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        form.find('input[type="password"]').val('');
                        $('#pw_bar').css('width', '0%');
                        $('#teacher_confirm_feedback').hide();
                        
                        // Reset requirements checklist UI
                        $('.requirement-item').removeClass('text-success').addClass('text-danger');
                        $('.requirement-item i').removeClass('fa-check-circle text-success').addClass('fa-times-circle text-danger');
                        
                        teacherState = {
                            length: false,
                            upper: false,
                            lower: false,
                            number: false,
                            special: false,
                            match: false
                        };
                        toggleTeacherSubmit();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Something went wrong.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    toggleTeacherSubmit();
                }
            },
            error: function(xhr) {
                submitBtn.html(originalBtnHtml).attr('disabled', false);

                let errorMsg = 'Failed to change password. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show alert-dynamic mt-3" role="alert">
                        <strong>Error:</strong> ${errorMsg}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                form.before(alertHtml);

                Swal.fire({
                    title: 'Error!',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#940000'
                });
                toggleTeacherSubmit();
            }
        });
    });
});
</script>

@include('includes.footer')
@include('partials.tange_download_progress')
