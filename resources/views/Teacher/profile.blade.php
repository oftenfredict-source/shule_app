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
                    <p class="profile-role">Teacher ID: {{ $teacher->employee_number ?? 'N/A' }} | {{ $teacher->qualification ?? 'N/A' }}</p>

                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="{{ route('teacher.profile.download_pdf') }}" class="btn btn-action btn-download-pdf">
                            <i class="fa fa-file-pdf-o"></i> Download PDF
                        </a>
                        <button class="btn btn-action btn-edit-profile" data-toggle="modal" data-target="#editModal">
                            <i class="fa fa-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Gender</div>
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
                        <div class="info-label">Specialization</div>
                        <div class="info-value">{{ $teacher->specialization ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Experience</div>
                        <div class="info-value">{{ $teacher->experience ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge {{ ($teacher->status ?? '') === 'Active' ? 'badge-success' : 'badge-danger' }}">
                                {{ $teacher->status ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">{{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('Y-m-d') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date Joined</div>
                        <div class="info-value">{{ $teacher->date_hired ? \Carbon\Carbon::parse($teacher->date_hired)->format('Y-m-d') : 'N/A' }}</div>
                    </div>
                </div>

                <div class="p-4 password-section">
                    <h5 class="mb-4">Account Security</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('teacher.change_password') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="info-label">Current Password</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label class="info-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" id="new_pw" required>
                                    <div class="mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div id="pw_bar" class="progress-bar" style="width: 0%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="info-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="new_password_confirmation" required>
                                </div>
                                <button type="submit" class="btn btn-action btn-edit-profile m-0">Update Password</button>
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
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title">Edit Profile Information</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('update_teacher') }}" method="POST">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="first_name" value="{{ $teacher->first_name }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="{{ $teacher->middle_name }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="{{ $teacher->last_name }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" value="{{ $teacher->phone_number }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ $teacher->email }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ $teacher->address }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary-custom text-white">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Image Upload AJAX
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
                        $('#profileDisplayImg').attr('src', response.image_url);
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

    // Password Strength
    $('#new_pw').on('input', function() {
        const pw = $(this).val();
        let score = 0;
        if (pw.length >= 8) score += 25;
        if (/[a-z]/.test(pw)) score += 15;
        if (/[A-Z]/.test(pw)) score += 15;
        if (/[0-9]/.test(pw)) score += 15;
        if (/[^a-zA-Z0-9]/.test(pw)) score += 15;
        if (pw.length >= 12) score += 15;

        const p = Math.min(100, score);
        $('#pw_bar').css('width', p + '%');
        if (p < 35) $('#pw_bar').removeClass().addClass('progress-bar bg-danger');
        else if (p < 70) $('#pw_bar').removeClass().addClass('progress-bar bg-warning');
        else $('#pw_bar').removeClass().addClass('progress-bar bg-success');
    });
});
</script>
