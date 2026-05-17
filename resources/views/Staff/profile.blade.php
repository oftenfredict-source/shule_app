@include('includes.staff_nav')
<style>
    body, .content, .card, .btn, .form-control, .form-select, .table, .list-group-item, .alert {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: white;
    }
    .page-card {
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .page-header {
        background: #fff7f7;
        border-bottom: 1px solid #f0dada;
        color: #940000;
        font-weight: 600;
        padding: 12px 16px;
    }
    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(148, 0, 0, 0.35);
        background: rgba(148, 0, 0, 0.06);
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }
    .detail-item {
        border: 1px solid #f0f0f0;
        padding: 10px 12px;
        background: #fafafa;
    }
    .detail-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .detail-value {
        font-weight: 600;
        color: #2f2f2f;
    }
</style>

<div class="container-fluid mt-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card page-card">
        <div class="page-header">My Profile</div>
        <div class="card-body">
            @php
                $profileImg = isset($staff) && $staff && $staff->image
                    ? asset('userImages/' . $staff->image)
                    : (isset($staff) && $staff && $staff->gender == 'Female'
                        ? asset('images/female.png')
                        : asset('images/male.png'));
            @endphp

            <div class="d-flex align-items-center mb-4">
                <img src="{{ $profileImg }}" alt="Profile" class="profile-avatar mr-3">
                <div>
                    <h5 class="mb-1">{{ $staff->first_name ?? 'Staff' }} {{ $staff->last_name ?? '' }}</h5>
                    <div class="text-muted">
                        {{ $staffProfession->name ?? 'Staff' }}
                    </div>
                    <div class="text-muted" style="font-size: 0.9rem;">
                        {{ $user->email ?? $staff->email ?? '' }}
                    </div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">
                        {{ trim(($staff->first_name ?? '') . ' ' . ($staff->middle_name ?? '') . ' ' . ($staff->last_name ?? '')) ?: 'N/A' }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Employee Number</div>
                    <div class="detail-value">{{ $staff->employee_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Profession</div>
                    <div class="detail-value">{{ $staffProfession->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Position</div>
                    <div class="detail-value">{{ $staff->position ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Qualification</div>
                    <div class="detail-value">{{ $staff->qualification ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Specialization</div>
                    <div class="detail-value">{{ $staff->specialization ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Experience</div>
                    <div class="detail-value">{{ $staff->experience ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Gender</div>
                    <div class="detail-value">{{ $staff->gender ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date of Birth</div>
                    <div class="detail-value">{{ $staff->date_of_birth ? $staff->date_of_birth->format('Y-m-d') : 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date Hired</div>
                    <div class="detail-value">{{ $staff->date_hired ? $staff->date_hired->format('Y-m-d') : 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">National ID</div>
                    <div class="detail-value">{{ $staff->national_id ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $staff->email ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value">{{ $staff->phone_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Address</div>
                    <div class="detail-value">{{ $staff->address ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Bank Account</div>
                    <div class="detail-value">{{ $staff->bank_account_number ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fingerprint ID</div>
                    <div class="detail-value">{{ $staff->fingerprint_id ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">{{ $staff->status ?? 'N/A' }}</div>
                </div>
            </div>

            <form id="staffProfileForm" action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary-custom mb-3">Profile Picture</h6>
                        <div class="form-group">
                            <label for="profile_image">Upload New Picture</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">JPG/PNG, max 2MB.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-primary-custom mb-3">Change Password</h6>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password">
                            <div class="mt-2">
                                <div class="progress" style="height: 8px;">
                                    <div id="staff_pw_bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small id="staff_pw_label" class="text-muted">Strength: 0%</small>
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded" id="staff_requirements_card" style="border: 1px solid #e3e6f0; display: none;">
                                <h6 class="font-weight-bold mb-2 small text-secondary">PASSWORD REQUIREMENTS:</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="requirement-item text-danger" id="s_req_len">
                                        <i class="fa fa-times-circle mr-2" id="s_icon_len"></i> At least 8 characters
                                    </li>
                                    <li class="requirement-item text-danger" id="s_req_upper">
                                        <i class="fa fa-times-circle mr-2" id="s_icon_upper"></i> At least one uppercase letter (A-Z)
                                    </li>
                                    <li class="requirement-item text-danger" id="s_req_lower">
                                        <i class="fa fa-times-circle mr-2" id="s_icon_lower"></i> At least one lowercase letter (a-z)
                                    </li>
                                    <li class="requirement-item text-danger" id="s_req_num">
                                        <i class="fa fa-times-circle mr-2" id="s_icon_num"></i> At least one number (0-9)
                                    </li>
                                    <li class="requirement-item text-danger" id="s_req_spec">
                                        <i class="fa fa-times-circle mr-2" id="s_icon_spec"></i> At least one special character (@, $, !, %, etc.)
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
                            <div class="mt-2 small" id="staff_confirm_feedback" style="display: none;">
                                <span id="staff_confirm_status"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" id="staffSubmitBtn" class="btn btn-primary-custom">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('staffDashboard') }}" class="btn btn-outline-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const staffNewPassword = $('#new_password');
    const staffConfirmPassword = $('#new_password_confirmation');
    const staffCurrentPassword = $('#current_password');
    const staffSubmitBtn = $('#staffSubmitBtn');

    let staffState = {
        length: false,
        upper: false,
        lower: false,
        number: false,
        special: false,
        match: false
    };

    function updateStaffRequirementUI(elementId, iconId, isMet) {
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

    function checkStaffPassword() {
        const pw = staffNewPassword.val();
        
        if (!pw) {
            $('#staff_requirements_card').slideUp(200);
            $('#staff_confirm_feedback').slideUp(200);
            $('#staff_pw_bar').css('width', '0%');
            $('#staff_pw_label').text('Strength: 0%');
            toggleStaffSubmit();
            return;
        }

        $('#staff_requirements_card').slideDown(200);

        // 1. Length
        staffState.length = pw.length >= 8;
        updateStaffRequirementUI('#s_req_len', '#s_icon_len', staffState.length);

        // 2. Upper case
        staffState.upper = /[A-Z]/.test(pw);
        updateStaffRequirementUI('#s_req_upper', '#s_icon_upper', staffState.upper);

        // 3. Lower case
        staffState.lower = /[a-z]/.test(pw);
        updateStaffRequirementUI('#s_req_lower', '#s_icon_lower', staffState.lower);

        // 4. Number
        staffState.number = /[0-9]/.test(pw);
        updateStaffRequirementUI('#s_req_num', '#s_icon_num', staffState.number);

        // 5. Special Character
        staffState.special = /[^a-zA-Z0-9]/.test(pw);
        updateStaffRequirementUI('#s_req_spec', '#s_icon_spec', staffState.special);

        // Strength progress bar
        let score = 0;
        if (staffState.length) score += 25;
        if (staffState.upper) score += 15;
        if (staffState.lower) score += 15;
        if (staffState.number) score += 15;
        if (staffState.special) score += 15;
        if (pw.length >= 12) score += 15;

        const p = Math.min(100, score);
        const bar = $('#staff_pw_bar');
        bar.css('width', p + '%');
        $('#staff_pw_label').text('Strength: ' + p + '%');

        if (p < 35) {
            bar.removeClass('bg-warning bg-success').addClass('bg-danger');
        } else if (p < 70) {
            bar.removeClass('bg-danger bg-success').addClass('bg-warning');
        } else {
            bar.removeClass('bg-danger bg-warning').addClass('bg-success');
        }

        checkStaffMatch();
    }

    function checkStaffMatch() {
        const pw = staffNewPassword.val();
        const confirm = staffConfirmPassword.val();
        const feedback = $('#staff_confirm_feedback');
        const statusSpan = $('#staff_confirm_status');

        if (!pw) {
            feedback.slideUp(200);
            staffState.match = false;
            toggleStaffSubmit();
            return;
        }

        if (!confirm) {
            feedback.slideUp(200);
            staffState.match = false;
            toggleStaffSubmit();
            return;
        }

        feedback.slideDown(200);
        if (pw === confirm) {
            staffState.match = true;
            statusSpan.html('<i class="fa fa-check-circle text-success mr-2"></i> Passwords match').removeClass('text-danger').addClass('text-success');
        } else {
            staffState.match = false;
            statusSpan.html('<i class="fa fa-times-circle text-danger mr-2"></i> Passwords do not match').removeClass('text-success').addClass('text-danger');
        }

        toggleStaffSubmit();
    }

    function toggleStaffSubmit() {
        const pw = staffNewPassword.val();
        if (!pw) {
            // Password is empty, they can submit the profile form normally
            staffSubmitBtn.prop('disabled', false);
            return;
        }

        const currentFilled = staffCurrentPassword.val().length > 0;
        const allPassed = staffState.length && staffState.upper && staffState.lower && staffState.number && staffState.special && staffState.match && currentFilled;
        staffSubmitBtn.prop('disabled', !allPassed);
    }

    // Input event listeners
    staffNewPassword.on('input', checkStaffPassword);
    staffConfirmPassword.on('input', checkStaffMatch);
    staffCurrentPassword.on('input', toggleStaffSubmit);

    // Profile form submit AJAX
    $('#staffProfileForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#staffSubmitBtn');
        const originalBtnHtml = submitBtn.html();

        // Clear dynamic alerts
        $('.alert-dynamic').remove();

        // Loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Saving...').attr('disabled', true);

        // Construct FormData for file uploads
        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                        // Clear password inputs
                        form.find('input[type="password"]').val('');
                        $('#staff_pw_bar').css('width', '0%');
                        $('#staff_pw_label').text('Strength: 0%');
                        $('#staff_requirements_card').hide();
                        $('#staff_confirm_feedback').hide();

                        // Reset states
                        staffState = {
                            length: false,
                            upper: false,
                            lower: false,
                            number: false,
                            special: false,
                            match: false
                        };
                        
                        // Reload page to show updated profile photo if one was uploaded
                        if (form.find('input[type="file"]').val()) {
                            window.location.reload();
                        } else {
                            toggleStaffSubmit();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Something went wrong.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    toggleStaffSubmit();
                }
            },
            error: function(xhr) {
                submitBtn.html(originalBtnHtml).attr('disabled', false);

                let errorMsg = 'Failed to update profile. Please try again.';
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
                toggleStaffSubmit();
            }
        });
    });
});
</script>

@include('includes.footer')
