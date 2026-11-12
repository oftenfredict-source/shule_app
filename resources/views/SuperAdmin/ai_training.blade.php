@extends('includes.superadmin_nav')

@section('title', 'AI Training')

@section('content')
<style>
    .page-hero {
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        border: 1px solid #f0dada;
        padding: 20px 24px;
        margin-bottom: 20px;
    }
    .page-hero h4 { color: #940000; font-weight: 800; margin: 0; }
    .card, .btn, .form-control, .form-select, .modal-content, .badge { border-radius: 0 !important; }
    .btn-primary-custom { background: #940000; border-color: #940000; color: #fff; font-weight: 600; }
    .btn-primary-custom:hover { background: #7a0000; border-color: #7a0000; color: #fff; }
    .table thead th { background: whitesmoke; color: #940000; border-bottom: 2px solid #940000; }
    .btn-outline-primary { color: #940000 !important; border-color: #940000 !important; background: #fff; }
    .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
        background: #940000 !important; border-color: #940000 !important; color: #fff !important; box-shadow: none !important;
    }
    .text-primary { color: #940000 !important; }
    .form-control:focus, .form-select:focus {
        border-color: #940000 !important;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.15) !important;
    }
    .answer-preview { max-width: 420px; white-space: pre-wrap; }
</style>

<div class="container-fluid mt-3">
    <div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4><i class="bx bx-bot me-2"></i>AI Training — Common Questions</h4>
            <p class="mb-0 text-muted small">
                Fundisha ShuleXpert AI majibu ya maswali ya kawaida. Trained answers zina priority juu ya FAQ/moduli.
            </p>
        </div>
        <button type="button" class="btn btn-primary-custom" id="btnOpenCreate">
            <i class="bx bx-plus-circle"></i> Add Q&amp;A
        </button>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="qaTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Locale</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $item)
                            <tr data-id="{{ $item->id }}">
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-bold" style="max-width:220px;">{{ $item->question }}</td>
                                <td><div class="answer-preview small text-muted">{{ \Illuminate\Support\Str::limit($item->answer, 140) }}</div></td>
                                <td><span class="badge bg-secondary">{{ strtoupper($item->locale) }}</span></td>
                                <td>{{ $item->priority }}</td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
                                        data-id="{{ $item->id }}"
                                        data-question="{{ e($item->question) }}"
                                        data-answer="{{ e($item->answer) }}"
                                        data-locale="{{ $item->locale }}"
                                        data-keywords="{{ e($item->keywords) }}"
                                        data-priority="{{ $item->priority }}"
                                        data-active="{{ $item->is_active ? 1 : 0 }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-id="{{ $item->id }}"
                                        data-question="{{ e($item->question) }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Hakuna trained Q&amp;A bado. Bofya <strong>Add Q&amp;A</strong> kuanza.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: whitesmoke; border-bottom: 2px solid #940000;">
                <h5 class="modal-title text-primary fw-bold" id="qaModalTitle">Add Common Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qaId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Question <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qa_question" placeholder="e.g. How do I reset a teacher password?">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Answer <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="qa_answer" rows="5" placeholder="Write the exact answer AI should give..."></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Language</label>
                        <select class="form-select" id="qa_locale">
                            <option value="both">Both (EN + SW)</option>
                            <option value="en">English</option>
                            <option value="sw">Swahili</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Priority</label>
                        <input type="number" class="form-control" id="qa_priority" min="0" max="100" value="10">
                        <small class="text-muted">Higher = preferred when multiple match.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" id="qa_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 mb-0">
                    <label class="form-label fw-bold">Keywords / synonyms</label>
                    <input type="text" class="form-control" id="qa_keywords" placeholder="e.g. password, reset, nenosiri, teacher">
                    <small class="text-muted">Optional. Comma-separated words that should also match this answer.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" id="btnSaveQa">Save training</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let qaModal;

    function getModal() {
        if (!qaModal) qaModal = new bootstrap.Modal(document.getElementById('qaModal'));
        return qaModal;
    }

    function decodeAttr(v) {
        const t = document.createElement('textarea');
        t.innerHTML = v || '';
        return t.value;
    }

    $('#btnOpenCreate').on('click', function () {
        $('#qaModalTitle').text('Add Common Question');
        $('#qaId').val('');
        $('#qa_question').val('');
        $('#qa_answer').val('');
        $('#qa_locale').val('both');
        $('#qa_keywords').val('');
        $('#qa_priority').val(10);
        $('#qa_active').val('1');
        getModal().show();
    });

    $(document).on('click', '.btn-edit', function () {
        const $b = $(this);
        $('#qaModalTitle').text('Edit Common Question');
        $('#qaId').val($b.data('id'));
        $('#qa_question').val(decodeAttr($b.attr('data-question')));
        $('#qa_answer').val(decodeAttr($b.attr('data-answer')));
        $('#qa_locale').val($b.data('locale') || 'both');
        $('#qa_keywords').val(decodeAttr($b.attr('data-keywords')));
        $('#qa_priority').val($b.data('priority') || 10);
        $('#qa_active').val(String($b.data('active')));
        getModal().show();
    });

    $('#btnSaveQa').on('click', function () {
        const id = $('#qaId').val();
        const payload = {
            question: $('#qa_question').val(),
            answer: $('#qa_answer').val(),
            locale: $('#qa_locale').val(),
            keywords: $('#qa_keywords').val(),
            priority: parseInt($('#qa_priority').val(), 10) || 10,
            is_active: $('#qa_active').val() === '1',
            _token: csrf
        };

        const url = id
            ? @json(url('super-admin/ai-training')) + '/' + id
            : @json(route('superadmin.ai_training.store'));
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: payload,
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.error || 'Save failed.');
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Save failed.';
                alert(msg);
            }
        });
    });

    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        const q = decodeAttr($(this).attr('data-question'));
        if (!confirm('Delete this training?\n\n' + q)) return;

        $.ajax({
            url: @json(url('super-admin/ai-training')) + '/' + id,
            method: 'DELETE',
            data: { _token: csrf },
            success: function (res) {
                if (res.success) location.reload();
                else alert(res.error || 'Delete failed.');
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Delete failed.';
                alert(msg);
            }
        });
    });
});
</script>
@endsection
