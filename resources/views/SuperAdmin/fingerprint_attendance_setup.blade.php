@extends('includes.superadmin_nav')

@section('content')
<div class="container-fluid mt-3">
    <style>
        :root { --primary-red: #940000; }
        .btn-primary-custom { background: var(--primary-red); color: #fff; border: none; font-weight: 600; }
        .btn-primary-custom:hover { background: #7a0000; color: #fff; }
        .card, .btn, .form-control, .modal-content { border-radius: 0 !important; }
        .fp-upload-progress .progress-bar { background-color: var(--primary-red); }
        .fp-setup-download-progress .progress-bar { background-color: var(--primary-red); }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-red);">Fingerprint Attendance Setup</h4>
            <p class="text-muted small mb-0">Upload desktop app (Local Agent), setup moja tu kwa mfumo wote</p>
            @if(!empty($phpUploadLimits))
            <p class="text-muted small mb-0">Server upload limit: <strong>{{ $phpUploadLimits['upload_max_filesize'] ?? '?' }}</strong> (post: {{ $phpUploadLimits['post_max_size'] ?? '?' }})</p>
            @endif
        </div>
        @if(!$setup)
        <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#uploadSetupModal">
            <i class="bx bx-upload me-1"></i> Upload Setup
        </button>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($setup)
    <div class="card border shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="fw-bold mb-1">{{ $setup->title }}</h5>
                    @if($setup->version)
                    <div class="text-muted small mb-2">Version: <strong>{{ $setup->version }}</strong></div>
                    @endif
                    <div class="small text-muted">
                        <i class="bx bx-file me-1"></i>{{ $setup->original_filename }}
                        <span class="ms-2">({{ $setup->file_size_human }})</span>
                    </div>
                    <div class="small text-muted mt-1">
                        <i class="bx bx-time me-1"></i>Uploaded {{ $setup->created_at?->format('d M Y H:i') }}
                    </div>
                    <div class="fp-setup-download-progress d-none mt-3" style="max-width: 320px;">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fp-setup-download-label">Downloading...</span>
                            <strong class="fp-setup-download-percent">0%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated fp-setup-download-bar" role="progressbar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button"
                        class="btn btn-outline-success btn-sm fp-setup-download-btn"
                        data-download-url="{{ route('fingerprint_attendance_setup.download', $setup->setupID) }}"
                        data-filename="{{ $setup->original_filename ?: 'fingerprint_setup.zip' }}">
                        <i class="bx bx-download"></i> Download
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm edit-setup-btn"
                        data-setup='@json($setup)'>
                        <i class="bx bx-edit"></i> Edit
                    </button>
                    <form action="{{ route('superadmin.fingerprint_attendance_setup.destroy', $setup->setupID) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this setup?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bx bx-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card border shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bx bx-desktop d-block mb-2" style="font-size: 2.5rem; opacity: 0.4;"></i>
            Hakuna setup bado. Bofya <strong>Upload Setup</strong> kuongeza desktop app.
        </div>
    </div>
    @endif
</div>

@if(!$setup)
<!-- Upload Modal -->
<div class="modal fade" id="uploadSetupModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadSetupForm" action="{{ route('superadmin.fingerprint_attendance_setup.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--primary-red);">Upload Fingerprint Attendance Setup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="uploadModalClose"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold small">APP NAME *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. ShuleXpert Fingerprint Attendance" value="{{ old('title') }}">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">VERSION</label>
                        <input type="text" name="version" class="form-control" placeholder="e.g. 1.0" value="{{ old('version') }}">
                    </div>
                    <div class="mb-0">
                        <label class="fw-bold small">SETUP FILE *</label>
                        <input type="file" name="setup_file" class="form-control" required accept=".zip,.exe,.msi,.rar,.7z">
                        <small class="text-muted">ZIP, EXE, MSI, RAR, 7Z, max 500MB</small>
                    </div>
                    <div id="uploadProgressWrap" class="fp-upload-progress d-none mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="uploadProgressLabel">Uploading...</span>
                            <strong id="uploadPercentText">0%</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="uploadCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="uploadSubmitBtn">
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Modal -->
<div class="modal fade" id="editSetupModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSetupForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color: var(--primary-red);">Edit Setup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="editModalClose"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold small">APP NAME *</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">VERSION</label>
                        <input type="text" name="version" id="edit_version" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="fw-bold small">REPLACE FILE (optional)</label>
                        <input type="file" name="setup_file" id="edit_setup_file" class="form-control" accept=".zip,.exe,.msi,.rar,.7z">
                        <small class="text-muted" id="edit_current_file"></small>
                    </div>
                    <div id="editProgressWrap" class="fp-upload-progress d-none mt-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="editProgressLabel">Uploading...</span>
                            <strong id="editPercentText">0%</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="editProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="editCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="editSubmitBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function resetProgress(wrapId, barId, textId, labelId) {
        var wrap = document.getElementById(wrapId);
        var bar = document.getElementById(barId);
        var text = document.getElementById(textId);
        var label = document.getElementById(labelId);
        if (wrap) wrap.classList.add('d-none');
        if (bar) {
            bar.style.width = '0%';
            bar.setAttribute('aria-valuenow', '0');
        }
        if (text) text.textContent = '0%';
        if (label) label.textContent = 'Uploading...';
    }

    function showProgress(wrapId, barId, textId, labelId, percent, label) {
        var wrap = document.getElementById(wrapId);
        var bar = document.getElementById(barId);
        var text = document.getElementById(textId);
        var labelEl = document.getElementById(labelId);
        if (wrap) wrap.classList.remove('d-none');
        if (bar) {
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', String(percent));
        }
        if (text) text.textContent = percent + '%';
        if (labelEl && label) labelEl.textContent = label;
    }

    function ajaxErrorMessage(xhr) {
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) return xhr.responseJSON.message;
            if (xhr.responseJSON.error) return xhr.responseJSON.error;
            if (xhr.responseJSON.errors) {
                var errs = xhr.responseJSON.errors;
                return Object.values(errs).flat().join('\n');
            }
        }
        if (xhr.status === 413) return 'File ni kubwa sana kwa server. Punguza ukubwa au ongeza PHP upload limit.';
        if (xhr.status === 419) return 'Session imeisha. Refresh page kisha jaribu tena.';
        if (xhr.status === 0) return 'Network error. Check your connection.';
        if (xhr.status >= 500) return 'Server error (' + xhr.status + '). Jaribu tena.';
        if (xhr.status === 422) return 'Upload haikufanikiwa. Angalia file na ukubwa wake.';
        return 'Request failed (' + xhr.status + '). Try again.';
    }

    function submitWithProgress(form, options) {
        var $form = $(form);
        var $btn = $(options.submitBtn);
        var $cancel = $(options.cancelBtns);
        var $close = $(options.closeBtns);
        var hasFile = options.hasFile !== false;

        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        $btn.prop('disabled', true);
        $cancel.prop('disabled', true);
        $close.prop('disabled', true);

        if (hasFile) {
            resetProgress(options.wrapId, options.barId, options.textId, options.labelId);
            showProgress(options.wrapId, options.barId, options.textId, options.labelId, 0, 'Uploading...');
        }

        $.ajax({
            url: options.url,
            type: options.method || 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                if (!hasFile) return xhr;

                xhr.upload.addEventListener('progress', function (evt) {
                    if (!evt.lengthComputable) return;
                    var percent = Math.min(100, Math.round((evt.loaded / evt.total) * 100));
                    var label = percent >= 100 ? 'Processing on server...' : 'Uploading...';
                    showProgress(options.wrapId, options.barId, options.textId, options.labelId, percent, label);
                }, false);

                return xhr;
            },
            success: function (res) {
                if (hasFile) {
                    showProgress(options.wrapId, options.barId, options.textId, options.labelId, 100, 'Complete!');
                }
                Swal.fire('Success', res.message || 'Saved successfully.', 'success').then(function () {
                    location.reload();
                });
            },
            error: function (xhr) {
                Swal.fire('Error', ajaxErrorMessage(xhr), 'error');
                $btn.prop('disabled', false);
                $cancel.prop('disabled', false);
                $close.prop('disabled', false);
                if (hasFile) {
                    resetProgress(options.wrapId, options.barId, options.textId, options.labelId);
                }
            }
        });
    }

    document.querySelectorAll('.edit-setup-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var s = JSON.parse(this.getAttribute('data-setup'));
            document.getElementById('editSetupForm').action = '{{ url("super-admin/fingerprint-attendance-setup") }}/' + s.setupID;
            document.getElementById('edit_title').value = s.title || '';
            document.getElementById('edit_version').value = s.version || '';
            document.getElementById('edit_current_file').textContent = 'Current: ' + (s.original_filename || '') + ' (' + (s.file_size_human || '') + ')';
            var fileInput = document.getElementById('edit_setup_file');
            if (fileInput) fileInput.value = '';
            resetProgress('editProgressWrap', 'editProgressBar', 'editPercentText', 'editProgressLabel');
            new bootstrap.Modal(document.getElementById('editSetupModal')).show();
        });
    });

    var uploadForm = document.getElementById('uploadSetupForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitWithProgress(uploadForm, {
                url: uploadForm.action,
                method: 'POST',
                submitBtn: '#uploadSubmitBtn',
                cancelBtns: '#uploadCancelBtn',
                closeBtns: '#uploadModalClose',
                wrapId: 'uploadProgressWrap',
                barId: 'uploadProgressBar',
                textId: 'uploadPercentText',
                labelId: 'uploadProgressLabel',
                hasFile: true
            });
        });
    }

    var editForm = document.getElementById('editSetupForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fileInput = document.getElementById('edit_setup_file');
            var hasNewFile = fileInput && fileInput.files && fileInput.files.length > 0;

            submitWithProgress(editForm, {
                url: editForm.action,
                method: 'POST',
                submitBtn: '#editSubmitBtn',
                cancelBtns: '#editCancelBtn',
                closeBtns: '#editModalClose',
                wrapId: 'editProgressWrap',
                barId: 'editProgressBar',
                textId: 'editPercentText',
                labelId: 'editProgressLabel',
                hasFile: hasNewFile
            });
        });
    }
})();
</script>
@endpush
@include('partials.fingerprint_setup_download_ajax')
@endsection
