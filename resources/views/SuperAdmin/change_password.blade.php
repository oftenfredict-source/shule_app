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
                    <form method="POST" action="{{ route('superadmin.change_password.store') }}">
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
                                <input type="password" name="new_password" class="form-control border-start-0" placeholder="Enter new password" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">CONFIRM NEW PASSWORD</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bx bx-lock"></i></span>
                                <input type="password" name="new_password_confirmation" class="form-control border-start-0" placeholder="Repeat new password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold mt-2">
                            <i class="bx bx-save me-2"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
