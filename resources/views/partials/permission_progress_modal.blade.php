{{-- Progress Modal --}}
<div class="modal fade" id="progressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sx-progress-modal rounded-0">
            <div class="modal-header sx-progress-modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-diagram-3"></i> {{ __('parent.approval_progress') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"
                    onclick="if (typeof window.sxHideModal === 'function') { window.sxHideModal('#progressModal'); } else if (window.jQuery) { window.jQuery('#progressModal').modal('hide'); }"></button>
            </div>
            <div class="modal-body sx-progress-modal-body">
                <div id="progressContent"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .sx-progress-modal {
        border: 1px solid #eadfdf !important;
        box-shadow: 0 18px 40px rgba(80,0,0,.14) !important;
        overflow: hidden;
    }
    .sx-progress-modal-header {
        background: linear-gradient(180deg, #fff8f8, #fff) !important;
        border-bottom: 1px solid #eadfdf !important;
        border-radius: 0 !important;
        padding: .95rem 1.1rem;
    }
    .sx-progress-modal-header .modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800 !important;
        color: #940000 !important;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .sx-progress-modal-header .modal-title i {
        color: #940000 !important;
    }
    .sx-progress-modal-header .btn-close {
        box-shadow: none !important;
        opacity: .65;
    }
    .sx-progress-modal-body {
        padding: 1.1rem 1.15rem 1.25rem;
        background: #fff;
    }

    .sx-progress-timeline {
        position: relative;
        padding: .35rem 0 .2rem 1.15rem;
        margin: 0;
        list-style: none;
    }
    .sx-progress-timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: #eadfdf;
    }
    .sx-progress-step {
        position: relative;
        margin: 0 0 .85rem;
        padding-left: .35rem;
    }
    .sx-progress-step:last-child { margin-bottom: 0; }
    .sx-progress-dot {
        position: absolute;
        left: -1.15rem;
        top: 14px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #d9d0d0;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #d9d0d0;
        z-index: 1;
    }
    .sx-progress-step.is-completed .sx-progress-dot {
        background: #28a745;
        box-shadow: 0 0 0 1px #28a745;
    }
    .sx-progress-step.is-current .sx-progress-dot {
        background: #ffc107;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.22);
    }
    .sx-progress-step.is-rejected .sx-progress-dot {
        background: #dc3545;
        box-shadow: 0 0 0 1px #dc3545;
    }
    .sx-progress-card {
        border: 1px solid #eadfdf;
        border-left: 3px solid #d9d0d0;
        background: #faf7f7;
        padding: .75rem .85rem;
    }
    .sx-progress-step.is-completed .sx-progress-card {
        border-left-color: #28a745;
        background: #f3fbf5;
    }
    .sx-progress-step.is-current .sx-progress-card {
        border-left-color: #ffc107;
        background: #fffbeb;
    }
    .sx-progress-step.is-rejected .sx-progress-card {
        border-left-color: #dc3545;
        background: #fff5f5;
    }
    .sx-progress-role {
        display: block;
        font-size: .9rem;
        font-weight: 800 !important;
        color: #940000 !important;
        line-height: 1.3;
        margin-bottom: .2rem;
    }
    .sx-progress-status {
        display: block;
        font-size: .8rem;
        font-weight: 600 !important;
        color: #555 !important;
    }
    .sx-progress-status.is-waiting { color: #7a5a00 !important; }
    .sx-progress-status.is-rejected { color: #a11919 !important; }
    .sx-progress-time {
        display: block;
        margin-top: .35rem;
        font-size: .72rem;
        font-weight: 600 !important;
        color: #888 !important;
    }
    .sx-progress-empty {
        text-align: center;
        padding: 1.5rem .75rem;
        color: #666 !important;
        font-weight: 600 !important;
    }
    .spinner-custom { color: #940000; }
</style>

<script>
    function viewPermissionProgress(permissionID, isStudentPermission = false) {
        var $ = window.jQuery;
        if (typeof $ !== 'function') {
            console.error('jQuery is required for viewPermissionProgress');
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Page scripts failed to load. Please refresh.', confirmButtonColor: '#940000' });
            }
            return;
        }
        window.$ = $;

        $('#progressContent').html('<div class="sx-progress-empty"><i class="fa fa-spinner fa-spin fa-2x spinner-custom"></i><p class="mb-0 mt-2">{{ __('parent.loading_progress') }}</p></div>');
        if (typeof window.sxShowModal === 'function') {
            window.sxShowModal('#progressModal');
        } else {
            $('#progressModal').modal('show');
        }

        $.get("{{ route('admin.permissions.progress') }}", { permissionID: permissionID, is_student_permission: isStudentPermission ? 1 : 0 }, function(response) {
            if (response.success) {
                const p = response.permission;
                const chain = response.chain || [];
                const logs = response.logs || [];

                let html = '<div class="sx-progress-timeline">';

                chain.forEach(step => {
                    const name = step.role_name || step.profession_name || @json(__('parent.approval_stage'));
                    const log = logs.find(l => l.approval_order == step.approval_order);
                    const isCompleted = !!log;
                    const isCurrent = !isCompleted && p.current_approval_order == step.approval_order && p.status === 'pending';
                    const statusLabel = isCompleted
                        ? @json(__('parent.approved'))
                        : (isCurrent
                            ? @json(__('parent.awaiting_action'))
                            : (p.status === 'rejected' ? @json(__('parent.terminated')) : @json(__('parent.upcoming'))));
                    const stepClass = isCompleted ? 'is-completed' : (isCurrent ? 'is-current' : '');
                    const statusClass = isCurrent ? 'is-waiting' : '';

                    html += `
                        <div class="sx-progress-step ${stepClass}">
                            <div class="sx-progress-dot"></div>
                            <div class="sx-progress-card">
                                <span class="sx-progress-role">${name} · {{ __('parent.stage') }} ${step.approval_order}</span>
                                <span class="sx-progress-status ${statusClass}">${statusLabel}</span>
                                ${log ? `<span class="sx-progress-time"><i class="fa fa-clock-o"></i> ${new Date(log.created_at).toLocaleString()}</span>` : ''}
                            </div>
                        </div>
                    `;
                });

                if (p.status === 'rejected') {
                    html += `
                        <div class="sx-progress-step is-rejected">
                            <div class="sx-progress-dot"></div>
                            <div class="sx-progress-card">
                                <span class="sx-progress-role">${@json(__('parent.request_rejected'))}</span>
                                <span class="sx-progress-status is-rejected">${p.admin_response || @json(__('parent.no_reason'))}</span>
                            </div>
                        </div>
                    `;
                }

                html += '</div>';
                if (!chain.length) {
                    html = '<div class="sx-progress-empty">No approval stages found.</div>';
                }
                $('#progressContent').html(html);
            } else {
                $('#progressContent').html('<div class="alert alert-danger rounded-0 mb-0">{{ __('parent.progress_load_failed') }}</div>');
            }
        }).fail(function() {
            $('#progressContent').html('<div class="alert alert-danger rounded-0 mb-0">{{ __('parent.progress_fetch_error') }}</div>');
        });
    }
</script>
