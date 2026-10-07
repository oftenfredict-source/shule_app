@php
    $user_type = $user_type ?? Session::get('user_type');
@endphp
@if($user_type == 'Admin')
    @include('includes.Admin_nav')
@elseif($user_type == 'Staff')
    @include('includes.staff_nav')
@else
    @include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body, .content, .card, .btn, .table, .form-control, .form-select, .modal {
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .sx-board-wrap { padding: 1rem 1.25rem 2rem; }
    .sx-board-hero {
        background: linear-gradient(135deg, #940000 0%, #6b0000 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .sx-board-hero h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 0.35rem; }
    .sx-board-hero p { margin: 0; opacity: 0.9; font-size: 0.92rem; }
    .sx-board-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; }
    .sx-board-nav a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 12px; border: 1px solid #e8e8e8; background: #fff;
        color: #333; text-decoration: none; font-size: 0.85rem; font-weight: 600;
    }
    .sx-board-nav a:hover { border-color: #940000; color: #940000; }
    .sx-board-nav a.active { background: #940000; border-color: #940000; color: #fff; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; border-radius: 0 !important; }
    .btn-primary-custom:hover { background: #b30000; border-color: #b30000; color: #fff; }
    .bg-primary-custom { background: #940000 !important; }
    .card, .btn, .form-control, .form-select, .modal-content { border-radius: 0 !important; }
    .member-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; }
    .member-stat {
        background: #fff; border: 1px solid #eee; padding: 12px 16px; min-width: 130px;
    }
    .member-stat .label { font-size: 0.75rem; color: #777; text-transform: uppercase; font-weight: 700; }
    .member-stat .value { font-size: 1.4rem; font-weight: 800; color: #940000; }
    .doc-preview { max-width: 280px; white-space: pre-wrap; font-size: 0.82rem; color: #555; }
    .meeting-meta { font-size: 0.8rem; color: #777; }
    .badge-cat {
        background: #fff5f5; color: #940000; border: 1px solid rgba(148,0,0,.25); font-weight: 600;
    }
</style>

@php
    $activeCount = $documents->where('status', 'Active')->count();
    $archivedCount = $documents->where('status', 'Archived')->count();
    $byCategory = $documents->groupBy('category')->map->count();
@endphp

<div class="sx-board-wrap">
    <div class="sx-board-hero">
        <h1><i class="fa fa-university"></i> School Board</h1>
        <p>{{ $subtitle }}</p>
    </div>

    <div class="sx-board-nav">
        @foreach($menu as $item)
            <a href="{{ route($item['route']) }}" class="{{ $section === $item['key'] ? 'active' : '' }}">
                <i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="member-stats">
        <div class="member-stat">
            <div class="label">Total Files</div>
            <div class="value">{{ $documents->count() }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Active</div>
            <div class="value">{{ $activeCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Archived</div>
            <div class="value">{{ $archivedCount }}</div>
        </div>
        <div class="member-stat">
            <div class="label">Categories</div>
            <div class="value">{{ $byCategory->count() }}</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0" style="color:#940000;font-weight:800;">
                <i class="fa fa-folder-open-o"></i> Documents
            </h5>
            <button type="button" class="btn btn-primary-custom btn-sm" id="openAddDocumentBtn">
                <i class="fa fa-upload"></i> Upload Document
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-primary-custom text-white">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Linked To</th>
                            <th>File</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $doc)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $doc->title }}</strong>
                                    @if($doc->description)
                                        <div class="doc-preview mt-1">{{ \Illuminate\Support\Str::limit($doc->description, 90) }}</div>
                                    @endif
                                </td>
                                <td><span class="badge badge-cat">{{ $doc->category }}</span></td>
                                <td class="small">
                                    @if($doc->meeting)
                                        <div><i class="fa fa-calendar"></i> {{ $doc->meeting->title }}</div>
                                    @endif
                                    @if($doc->resolution)
                                        <div><i class="fa fa-gavel"></i> {{ $doc->resolution->resolution_number ?: $doc->resolution->title }}</div>
                                    @endif
                                    @if($doc->committee)
                                        <div><i class="fa fa-sitemap"></i> {{ $doc->committee->committee_name }}</div>
                                    @endif
                                    @if(!$doc->meeting && !$doc->resolution && !$doc->committee)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $doc->original_filename ?: '—' }}
                                    <div class="meeting-meta">{{ $doc->file_size_label }}</div>
                                </td>
                                <td class="small">
                                    {{ $doc->created_at ? $doc->created_at->format('d M Y H:i') : '—' }}
                                    @if($doc->uploaded_by_name)
                                        <div class="meeting-meta">by {{ $doc->uploaded_by_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($doc->status === 'Active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Archived</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.school_board.documents.download', $doc->documentID) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning edit-document-btn"
                                        data-id="{{ $doc->documentID }}"
                                        data-title="{{ $doc->title }}"
                                        data-category="{{ $doc->category }}"
                                        data-description="{{ $doc->description }}"
                                        data-meeting="{{ $doc->meetingID }}"
                                        data-resolution="{{ $doc->resolutionID }}"
                                        data-committee="{{ $doc->committeeID }}"
                                        data-status="{{ $doc->status }}"
                                        data-filename="{{ $doc->original_filename }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-document-btn"
                                        data-id="{{ $doc->documentID }}"
                                        data-title="{{ $doc->title }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No documents yet. Click <strong>Upload Document</strong> to add the first file.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Upload / Edit Modal --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="documentModalTitle">Upload Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="documentForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentID" id="documentID">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category" id="category">
                                <option value="General">General</option>
                                <option value="Agenda">Agenda</option>
                                <option value="Minutes">Minutes</option>
                                <option value="Resolution">Resolution</option>
                                <option value="Policy">Policy</option>
                                <option value="Report">Report</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Link Meeting</label>
                            <select class="form-control" name="meetingID" id="meetingID">
                                <option value="">— None —</option>
                                @foreach($meetings as $m)
                                    <option value="{{ $m->meetingID }}">
                                        {{ $m->meeting_date ? $m->meeting_date->format('d M Y') : '' }} — {{ $m->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Link Resolution</label>
                            <select class="form-control" name="resolutionID" id="resolutionID">
                                <option value="">— None —</option>
                                @foreach($resolutions as $r)
                                    <option value="{{ $r->resolutionID }}">
                                        {{ $r->resolution_number ?: '#' . $r->resolutionID }} — {{ \Illuminate\Support\Str::limit($r->title, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Link Committee</label>
                            <select class="form-control" name="committeeID" id="committeeID">
                                <option value="">— None —</option>
                                @foreach($committees as $c)
                                    <option value="{{ $c->committeeID }}">{{ $c->committee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">
                                File <span class="text-danger" id="fileRequiredMark">*</span>
                            </label>
                            <input type="file" class="form-control" name="document_file" id="document_file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.csv,.zip">
                            <small class="text-muted" id="fileHint">Max 10MB. PDF, Office, images, ZIP.</small>
                            <div class="meeting-meta mt-1" id="currentFileLabel" style="display:none;"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status">
                                <option value="Active">Active</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom" id="documentSubmitBtn">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('includes.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const form = document.getElementById('documentForm');
    const modal = document.getElementById('documentModal');
    if (!form || !modal) return;

    function openModal(el) {
        if (!el) return;
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal('show');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function closeModal(el) {
        if (!el) return;
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
            jQuery(el).modal('hide');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).hide();
        }
    }

    function jsonHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
        };
    }

    function parseErrorMessage(data, fallback) {
        if (!data) return fallback;
        if (data.message) return data.message;
        if (data.errors) {
            try { return Object.values(data.errors).flat().join('\n'); } catch (e) {}
        }
        return fallback;
    }

    function resetForm() {
        form.reset();
        document.getElementById('documentID').value = '';
        document.getElementById('category').value = 'General';
        document.getElementById('status').value = 'Active';
        document.getElementById('document_file').required = true;
        document.getElementById('fileRequiredMark').style.display = '';
        document.getElementById('currentFileLabel').style.display = 'none';
        document.getElementById('documentModalTitle').textContent = 'Upload Document';
        document.getElementById('documentSubmitBtn').textContent = 'Upload';
    }

    document.getElementById('openAddDocumentBtn').addEventListener('click', function () {
        resetForm();
        openModal(modal);
    });

    document.querySelectorAll('.edit-document-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('documentID').value = btn.dataset.id || '';
            document.getElementById('title').value = btn.dataset.title || '';
            document.getElementById('category').value = btn.dataset.category || 'General';
            document.getElementById('description').value = btn.dataset.description || '';
            document.getElementById('meetingID').value = btn.dataset.meeting || '';
            document.getElementById('resolutionID').value = btn.dataset.resolution || '';
            document.getElementById('committeeID').value = btn.dataset.committee || '';
            document.getElementById('status').value = btn.dataset.status || 'Active';
            document.getElementById('document_file').value = '';
            document.getElementById('document_file').required = false;
            document.getElementById('fileRequiredMark').style.display = 'none';
            const label = document.getElementById('currentFileLabel');
            label.style.display = '';
            label.textContent = 'Current file: ' + (btn.dataset.filename || '—') + ' (leave empty to keep)';
            document.getElementById('documentModalTitle').textContent = 'Edit Document';
            document.getElementById('documentSubmitBtn').textContent = 'Update';
            openModal(modal);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('documentID').value;
        const url = id
            ? @json(route('admin.school_board.documents.update'))
            : @json(route('admin.school_board.documents.store'));
        const fd = new FormData(form);

        fetch(url, { method: 'POST', headers: jsonHeaders(), body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    Swal.fire('Error', parseErrorMessage(res.data, 'Could not save document.'), 'error');
                    return;
                }
                closeModal(modal);
                Swal.fire({ icon: 'success', title: res.data.message, timer: 1400, showConfirmButton: false })
                    .then(function () { window.location.reload(); });
            })
            .catch(function () {
                Swal.fire('Error', 'Network error while saving document.', 'error');
            });
    });

    document.querySelectorAll('.delete-document-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete document?',
                text: btn.dataset.title || 'File will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#940000',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('documentID', btn.dataset.id || '');
                fetch(@json(route('admin.school_board.documents.delete')), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        Swal.fire('Error', parseErrorMessage(res.data, 'Could not delete document.'), 'error');
                        return;
                    }
                    Swal.fire({ icon: 'success', title: res.data.message, timer: 1200, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function () {
                    Swal.fire('Error', 'Network error while deleting document.', 'error');
                });
            });
        });
    });
    });
})();
</script>
