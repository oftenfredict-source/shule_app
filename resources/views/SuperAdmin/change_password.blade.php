@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 fw-bold" style="color: #940000;"><i class="bx bx-lock-alt me-2"></i>Change Password</h5>
                    <a href="{{ route('superAdminDashboard') }}" class="btn btn-sm btn-outline-primary-custom"><i class="bx bx-arrow-back me-1"></i> Dashboard</a>
                </div>
                <div class="card-body p-4">
                    <form id="superAdminChangePasswordForm" method="POST" action="{{ route('superadmin.change_password.store') }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">CURRENT PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bx bx-key"></i></span>
                                <input type="password" name="current_password" class="form-control border-start-0" placeholder="Enter current password" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">NEW PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bx bx-lock-open"></i></span>
                                <input type="password" name="new_password" id="super_new_password" class="form-control border-start-0" placeholder="Enter new password" required autocomplete="new-password">
                            </div>
                            <div class="mt-2 p-3 bg-light rounded border" id="super_requirements_card">
                                <h6 class="fw-bold mb-2 small text-secondary">PASSWORD REQUIREMENTS:</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="requirement-item text-danger" id="super_req_len">
                                        <i class="bx bx-x-circle me-1 align-middle fs-6" id="super_icon_len"></i> At least 6 characters
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">CONFIRM NEW PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bx bx-lock"></i></span>
                                <input type="password" name="new_password_confirmation" id="super_confirm_password" class="form-control border-start-0" placeholder="Repeat new password" required autocomplete="new-password">
                            </div>
                            <div class="mt-2 small" id="super_confirm_feedback" style="display: none;">
                                <span id="super_confirm_status"></span>
                            </div>
                        </div>

                        <button type="submit" id="superAdminSubmitBtn" class="btn btn-primary-custom w-100 py-2 fw-bold mt-2" disabled>
                            <i class="bx bx-save me-2"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const newPasswordInput = $('#super_new_password');
    const confirmPasswordInput = $('#super_confirm_password');
    const submitBtn = $('#superAdminSubmitBtn');

    let state = {
        length: false,
        match: false
    };

    function updateRequirementUI(elementId, iconId, isMet) {
        const element = $(elementId);
        const icon = $(iconId);
        if (isMet) {
            element.removeClass('text-danger').addClass('text-success');
            icon.removeClass('bx-x-circle text-danger').addClass('bx-check-circle text-success');
        } else {
            element.removeClass('text-success').addClass('text-danger');
            icon.removeClass('bx-check-circle text-success').addClass('bx-x-circle text-danger');
        }
    }

    function checkPassword() {
        const pw = newPasswordInput.val();
        state.length = pw.length >= 6;
        updateRequirementUI('#super_req_len', '#super_icon_len', state.length);
        checkMatch();
    }

    function checkMatch() {
        const pw = newPasswordInput.val();
        const confirm = confirmPasswordInput.val();
        const feedback = $('#super_confirm_feedback');
        const statusSpan = $('#super_confirm_status');

        if (!confirm) {
            feedback.hide();
            state.match = false;
            toggleSubmit();
            return;
        }

        feedback.show();
        if (pw === confirm) {
            state.match = true;
            statusSpan.html('<i class="bx bx-check-circle text-success me-1"></i> Passwords match').removeClass('text-danger').addClass('text-success');
        } else {
            state.match = false;
            statusSpan.html('<i class="bx bx-x-circle text-danger me-1"></i> Passwords do not match').removeClass('text-success').addClass('text-danger');
        }

        toggleSubmit();
    }

    function toggleSubmit() {
        const allPassed = state.length && state.match;
        submitBtn.prop('disabled', !allPassed);
    }

    // Input listeners
    newPasswordInput.on('input', checkPassword);
    confirmPasswordInput.on('input', checkMatch);

    // Form submit AJAX
    $('#superAdminChangePasswordForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#superAdminSubmitBtn');
        const originalBtnHtml = submitBtn.html();

        // Clear previous alerts
        $('.alert-dynamic').remove();

        // Loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Updating...').attr('disabled', true);

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
                        $('#super_confirm_feedback').hide();
                        
                        // Reset validation checklist UI
                        $('#super_req_len').removeClass('text-success').addClass('text-danger');
                        $('#super_icon_len').removeClass('bx-check-circle text-success').addClass('bx-x-circle text-danger');
                        
                        state = {
                            length: false,
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
                    <div class="alert alert-danger alert-dismissible fade show alert-dynamic mt-3" role="alert">
                        <strong>Error:</strong> ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
@endsection
