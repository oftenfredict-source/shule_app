@include('includes.Admin_nav')

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row justify-content-center">
            <div class="col-lg-6">
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
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Change Password</h5>
                        <a href="{{ route('AdminDashboard') }}" class="btn btn-sm btn-outline-primary-custom">Back</a>
                    </div>
                    <div class="card-body">
                        <form id="adminChangePasswordForm" method="POST" action="{{ route('admin.change_password.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Enter your current password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">New Password</label>
                                <input type="password" name="new_password" id="admin_new_password" class="form-control" placeholder="Enter new password" required autocomplete="new-password">
                                <div class="mt-2">
                                    <div class="progress" style="height: 8px;">
                                        <div id="admin_pw_bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small id="admin_pw_label" class="text-muted">Strength: 0%</small>
                                </div>
                                
                                <div class="mt-3 p-3 bg-light rounded" id="admin_requirements_card" style="border: 1px solid #e3e6f0;">
                                    <h6 class="font-weight-bold mb-2 small text-secondary">PASSWORD REQUIREMENTS:</h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="requirement-item text-danger" id="req_len">
                                            <i class="fa fa-times-circle mr-2" id="icon_len"></i> At least 8 characters
                                        </li>
                                        <li class="requirement-item text-danger" id="req_upper">
                                            <i class="fa fa-times-circle mr-2" id="icon_upper"></i> At least one uppercase letter (A-Z)
                                        </li>
                                        <li class="requirement-item text-danger" id="req_lower">
                                            <i class="fa fa-times-circle mr-2" id="icon_lower"></i> At least one lowercase letter (a-z)
                                        </li>
                                        <li class="requirement-item text-danger" id="req_num">
                                            <i class="fa fa-times-circle mr-2" id="icon_num"></i> At least one number (0-9)
                                        </li>
                                        <li class="requirement-item text-danger" id="req_spec">
                                            <i class="fa fa-times-circle mr-2" id="icon_spec"></i> At least one special character (@, $, !, %, etc.)
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" id="admin_confirm_password" class="form-control" placeholder="Confirm new password" required autocomplete="new-password">
                                <div class="mt-2 small" id="admin_confirm_feedback" style="display: none;">
                                    <span id="admin_confirm_status"></span>
                                </div>
                            </div>

                            <button type="submit" id="adminSubmitBtn" class="btn btn-primary-custom w-100 py-2 font-weight-bold" disabled>Update Password</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const newPasswordInput = $('#admin_new_password');
    const confirmPasswordInput = $('#admin_confirm_password');
    const submitBtn = $('#adminSubmitBtn');

    // Validation state
    let state = {
        length: false,
        upper: false,
        lower: false,
        number: false,
        special: false,
        match: false
    };

    function updateRequirementUI(elementId, iconId, isMet) {
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

    function checkPassword() {
        const pw = newPasswordInput.val();
        
        // 1. Length
        state.length = pw.length >= 8;
        updateRequirementUI('#req_len', '#icon_len', state.length);

        // 2. Upper case
        state.upper = /[A-Z]/.test(pw);
        updateRequirementUI('#req_upper', '#icon_upper', state.upper);

        // 3. Lower case
        state.lower = /[a-z]/.test(pw);
        updateRequirementUI('#req_lower', '#icon_lower', state.lower);

        // 4. Number
        state.number = /[0-9]/.test(pw);
        updateRequirementUI('#req_num', '#icon_num', state.number);

        // 5. Special Character
        state.special = /[^a-zA-Z0-9]/.test(pw);
        updateRequirementUI('#req_spec', '#icon_spec', state.special);

        // Strength progress bar
        let score = 0;
        if (pw) {
            if (state.length) score += 25;
            if (state.upper) score += 15;
            if (state.lower) score += 15;
            if (state.number) score += 15;
            if (state.special) score += 15;
            if (pw.length >= 12) score += 15;
        }
        
        const p = Math.min(100, score);
        $('#admin_pw_bar').css('width', p + '%');
        $('#admin_pw_label').text('Strength: ' + p + '%');

        if (p < 35) {
            $('#admin_pw_bar').removeClass('bg-warning bg-success').addClass('bg-danger');
        } else if (p < 70) {
            $('#admin_pw_bar').removeClass('bg-danger bg-success').addClass('bg-warning');
        } else {
            $('#admin_pw_bar').removeClass('bg-danger bg-warning').addClass('bg-success');
        }

        checkMatch();
    }

    function checkMatch() {
        const pw = newPasswordInput.val();
        const confirm = confirmPasswordInput.val();
        const feedback = $('#admin_confirm_feedback');
        const statusSpan = $('#admin_confirm_status');

        if (!confirm) {
            feedback.hide();
            state.match = false;
            toggleSubmit();
            return;
        }

        feedback.show();
        if (pw === confirm) {
            state.match = true;
            statusSpan.html('<i class="fa fa-check-circle text-success mr-2"></i> Passwords match').removeClass('text-danger').addClass('text-success');
        } else {
            state.match = false;
            statusSpan.html('<i class="fa fa-times-circle text-danger mr-2"></i> Passwords do not match').removeClass('text-success').addClass('text-danger');
        }

        toggleSubmit();
    }

    function toggleSubmit() {
        const allPassed = state.length && state.upper && state.lower && state.number && state.special && state.match;
        submitBtn.prop('disabled', !allPassed);
    }

    // Input event listeners
    newPasswordInput.on('input', checkPassword);
    confirmPasswordInput.on('input', checkMatch);

    // Form submit AJAX
    $('#adminChangePasswordForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#adminSubmitBtn');
        const originalBtnHtml = submitBtn.html();

        // Clear dynamic alerts
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
                        // Clear input fields
                        form.find('input[type="password"]').val('');
                        
                        // Reset validations and progress
                        $('#admin_pw_bar').css('width', '0%');
                        $('#admin_pw_label').text('Strength: 0%');
                        $('#admin_confirm_feedback').hide();
                        
                        // Reset all requirements to red state
                        $('.requirement-item').removeClass('text-success').addClass('text-danger');
                        $('.requirement-item i').removeClass('fa-check-circle text-success').addClass('fa-times-circle text-danger');
                        
                        state = {
                            length: false,
                            upper: false,
                            lower: false,
                            number: false,
                            special: false,
                            match: false
                        };
                        toggleSubmit();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Something went wrong.',
                        icon: 'error',
                        confirmButtonColor: '#940000'
                    });
                    toggleSubmit();
                }
            },
            error: function(xhr) {
                submitBtn.html(originalBtnHtml).attr('disabled', false);

                let errorMsg = 'Failed to change password. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show alert-dynamic" role="alert">
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
                toggleSubmit();
            }
        });
    });
});
</script>
