@extends('includes.superadmin_nav')

@section('content')
<style>
    :root { --brand: #940000; }
    .card-brand { border-top: 4px solid var(--brand); }
    .form-label { font-weight: 700; font-size: 0.85rem; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
    .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--brand); font-size: 1.1rem; }
    .with-icon { padding-left: 42px; }
    .help { font-size: .8rem; color: #777; margin-top: 4px; }
    .required::after { content: " *"; color: var(--brand); font-weight: 700; }
    /* Zero Radius */
    .card, .btn, .form-control, .form-select, .alert { border-radius: 0 !important; }
    .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(148,0,0,.1) !important; }
</style>

<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--brand);">Institutional Onboarding</h4>
            <p class="text-muted small mb-0">Register a new school into the ShuleXpert system</p>
        </div>
        <a href="{{ route('superadmin.schools.index') }}" class="btn btn-outline-primary-custom btn-sm">
            <i class="bx bx-list-ul me-1"></i> Registered Schools
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border card-brand">
                <div class="card-body p-4 p-md-5">
                    <form id="schoolForm" method="POST" action="{{ route('save_school') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <h6 class="fw-bold border-bottom pb-2 mb-4"><i class="bx bx-info-circle me-2 text-primary"></i> Basic Information</h6>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="form-label required" for="school_name">Official School Name</label>
                                    <div class="position-relative">
                                        <i class="bx bx-building input-icon"></i>
                                        <input type="text" id="school_name" name="school_name" class="form-control with-icon @error('school_name') is-invalid @enderror" value="{{ old('school_name') }}" placeholder="Enter full institutional name" required>
                                        @error('school_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="registration_number">Registration Number</label>
                                    <div class="position-relative">
                                        <i class="bx bx-hash input-icon"></i>
                                        <input type="text" id="registration_number" name="registration_number" class="form-control with-icon @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" placeholder="e.g. S927/123">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required" for="school_type">Education Level</label>
                                    <select id="school_type" name="school_type" class="form-select @error('school_type') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('school_type') ? '' : 'selected' }}>-- Select --</option>
                                        <option value="Primary" {{ old('school_type') == 'Primary' ? 'selected' : '' }}>Primary School</option>
                                        <option value="Secondary" {{ old('school_type') == 'Secondary' ? 'selected' : '' }}>Secondary School</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required" for="ownership">Ownership Type</label>
                                    <select id="ownership" name="ownership" class="form-select @error('ownership') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('ownership') ? '' : 'selected' }}>-- Select --</option>
                                        <option value="Public" {{ old('ownership') == 'Public' ? 'selected' : '' }}>Public (Government)</option>
                                        <option value="Private" {{ old('ownership') == 'Private' ? 'selected' : '' }}>Private / NGO</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="established_year">Established Year</label>
                                    <input type="number" id="established_year" name="established_year" class="form-control" min="1900" max="{{ date('Y') }}" value="{{ old('established_year') }}" placeholder="YYYY">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold border-bottom pb-2 mb-4"><i class="bx bx-map me-2 text-primary"></i> Location & Contact</h6>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label required" for="region">Region</label>
                                    <input type="text" id="region" name="region" class="form-control" value="{{ old('region') }}" placeholder="e.g. Dar es Salaam" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required" for="district">District</label>
                                    <input type="text" id="district" name="district" class="form-control" value="{{ old('district') }}" placeholder="e.g. Kinondoni" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="ward">Ward / Street</label>
                                    <input type="text" id="ward" name="ward" class="form-control" value="{{ old('ward') }}" placeholder="e.g. Sinza">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Official Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bx bx-envelope"></i></span>
                                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="contact@school.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Primary Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bx bx-phone"></i></span>
                                        <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+255 ...">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="address">Postal Address</label>
                                    <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" placeholder="P.O. Box 1234, ...">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold border-bottom pb-2 mb-4"><i class="bx bx-cog me-2 text-primary"></i> System Settings</h6>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label" for="environment">System Environment</label>
                                    <select id="environment" name="environment" class="form-select">
                                        <option value="Demo" {{ old('environment', 'Demo') == 'Demo' ? 'selected' : '' }}>Demo / Testing</option>
                                        <option value="Live" {{ old('environment') == 'Live' ? 'selected' : '' }}>Live / Production</option>
                                    </select>
                                    <div class="help">Determines data persistence policy.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="senderID">SMS Sender ID</label>
                                    <div class="position-relative">
                                        <i class="bx bx-paper-plane input-icon"></i>
                                        <input type="text" id="senderID" name="senderID" class="form-control with-icon" value="{{ old('senderID') }}" placeholder="e.g. KILIMANJARO" maxlength="11">
                                    </div>
                                    <div class="help">Branded name for SMS notifications.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required" for="status">Account Status</label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option value="Active">Active (Unlocked)</option>
                                        <option value="Inactive">Inactive (Locked)</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label" for="school_logo">School Brand Logo</label>
                                    <input type="file" id="school_logo" name="school_logo" class="form-control" accept="image/*">
                                    <div class="help">Max file size 2MB (JPG, PNG).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required" for="login_status">Login Access</label>
                                    <select id="login_status" name="login_status" class="form-select" required>
                                        <option value="1" selected>Grant Access (True)</option>
                                        <option value="0">Deny Access (False)</option>
                                    </select>
                                    <div class="help">Controls whether users from this school can log in.</div>
                                </div>
                                <div class="col-md-6" id="suspension_reason_container" style="display: none;">
                                    <label class="form-label required" for="suspension_reason">Suspension Reason</label>
                                    <textarea id="suspension_reason" name="suspension_reason" class="form-control" rows="2" placeholder="e.g., Service suspended due to maintenance or billing."></textarea>
                                    <div class="help">This message will be shown to users when they try to login.</div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="two_factor_enabled" name="two_factor_enabled" value="1" {{ old('two_factor_enabled') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="two_factor_enabled">ENFORCE TWO-FACTOR AUTHENTICATION (2FA)</label>
                                        <div class="help">Highly recommended for production environments. Admins must verify via SMS.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const loginStatus = document.getElementById('login_status');
                                const reasonContainer = document.getElementById('suspension_reason_container');
                                const reasonInput = document.getElementById('suspension_reason');

                                function toggleReason() {
                                    if (loginStatus.value === '0') {
                                        reasonContainer.style.display = 'block';
                                        reasonInput.setAttribute('required', 'required');
                                    } else {
                                        reasonContainer.style.display = 'none';
                                        reasonInput.removeAttribute('required');
                                    }
                                }

                                loginStatus.addEventListener('change', toggleReason);
                                toggleReason(); // Run on load
                            });
                        </script>
                        @endpush

                        <hr class="my-5">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label small fw-bold" for="agree">I CONFIRM ALL DETAILS ARE ACCURATE</label>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="reset" class="btn btn-outline-secondary px-4">RESET</button>
                                <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-bold">
                                    <i class="bx bx-save me-1"></i> REGISTER SCHOOL
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
