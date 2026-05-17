{{-- Progress Modal --}}
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white" style="background: #940000; color: white; border-radius: 0;">
                <h5 class="modal-title"><i class="fa fa-tasks"></i> Approval Progress</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="progressContent">
                    {{-- Progress timeline will be injected here --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline Styles */
    .progress-timeline { position: relative; padding-left: 30px; margin-top: 20px; }
    .progress-timeline::before { content: ''; position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: #eee; }
    .timeline-item { position: relative; margin-bottom: 25px; }
    .timeline-dot { position: absolute; left: -30px; width: 16px; height: 16px; border-radius: 50%; background: #ddd; border: 3px solid #fff; z-index: 1; }
    .timeline-dot.completed { background: #28a745; }
    .timeline-dot.current { background: #ffc107; box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2); }
    .timeline-content { padding: 10px 15px; background: #f8f9fa; border-radius: 4px; border-left: 4px solid #ddd; }
    .timeline-item.completed .timeline-content { border-left-color: #28a745; background: #f0fff4; }
    .timeline-item.current .timeline-content { border-left-color: #ffc107; background: #fffbeb; }
    .timeline-role { font-weight: 600; color: #333; display: block; font-size: 14px; }
    .timeline-status { font-size: 12px; color: #666; font-weight: 500; }
    .timeline-time { font-size: 11px; color: #999; display: block; margin-top: 5px; }
    
    .spinner-custom { color: #940000; }
</style>

<script>
    function viewPermissionProgress(permissionID) {
        // Ensure jQuery is loaded
        if (typeof $ === 'undefined') {
            console.error('jQuery is required for viewPermissionProgress');
            return;
        }

        $('#progressContent').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x spinner-custom"></i><p class="mt-2">Loading progress...</p></div>');
        $('#progressModal').modal('show');

        $.get("{{ route('admin.permissions.progress') }}", { permissionID: permissionID }, function(response) {
            if (response.success) {
                const p = response.permission;
                const chain = response.chain;
                const logs = response.logs;
                const roles = response.roles || [];
                const positions = response.positions || [];

                let html = '<div class="progress-timeline">';
                
                // Build the full chain steps
                chain.forEach(step => {
                    const name = step.role_name || step.profession_name || 'Approval Stage';
                    const log = logs.find(l => l.approval_order == step.approval_order);
                    const isCompleted = !!log;
                    const isCurrent = !isCompleted && p.current_approval_order == step.approval_order && p.status === 'pending';

                    html += `
                        <div class="timeline-item ${isCompleted ? 'completed' : (isCurrent ? 'current' : '')}">
                            <div class="timeline-dot ${isCompleted ? 'completed' : (isCurrent ? 'current' : '')}"></div>
                            <div class="timeline-content">
                                <span class="timeline-role">${name} (Stage ${step.approval_order})</span>
                                <span class="timeline-status">${isCompleted ? 'Approved' : (isCurrent ? '<span class="text-warning fw-bold">Awaiting Action (Pending)</span>' : (p.status === 'rejected' ? 'Terminated' : 'Upcoming'))}</span>
                                ${log ? `<span class="timeline-time"><i class="fa fa-clock"></i> ${new Date(log.created_at).toLocaleString()}</span>` : ''}
                            </div>
                        </div>
                    `;
                });

                // Add final Admin step
                const isAdminCompleted = p.is_chain_completed || p.status === 'approved';
                const isAdminCurrent = !isAdminCompleted && p.status === 'pending' && (chain.length === 0 || p.current_approval_order > chain.length || (chain.length > 0 && p.current_approval_order == (Math.max(...chain.map(c => c.approval_order)) + 1)));

                html += `
                    <div class="timeline-item ${isAdminCompleted ? 'completed' : (isAdminCurrent ? 'current' : '')}">
                        <div class="timeline-dot ${isAdminCompleted ? 'completed' : (isAdminCurrent ? 'current' : '')}"></div>
                        <div class="timeline-content">
                            <span class="timeline-role">System Admin (Final Approval)</span>
                            <span class="timeline-status">${isAdminCompleted ? 'Completed' : (isAdminCurrent ? 'Awaiting Action' : (p.status === 'rejected' ? 'Terminated' : 'Pending'))}</span>
                            ${isAdminCompleted && p.reviewed_at ? `<span class="timeline-time"><i class="fa fa-clock"></i> ${new Date(p.reviewed_at).toLocaleString()}</span>` : ''}
                        </div>
                    </div>
                `;

                // Add rejection status if applicable
                if (p.status === 'rejected') {
                    html += `
                        <div class="timeline-item">
                            <div class="timeline-dot bg-danger"></div>
                            <div class="timeline-content" style="border-left-color: #dc3545; background: #fff5f5;">
                                <span class="timeline-role">Request Rejected</span>
                                <span class="timeline-status text-danger">${p.admin_response || 'No reason provided'}</span>
                            </div>
                        </div>
                    `;
                }

                html += '</div>';
                $('#progressContent').html(html);
            } else {
                $('#progressContent').html('<div class="alert alert-danger">Failed to load progress details.</div>');
            }
        }).fail(function() {
            $('#progressContent').html('<div class="alert alert-danger">An error occurred while fetching progress.</div>');
        });
    }
</script>
