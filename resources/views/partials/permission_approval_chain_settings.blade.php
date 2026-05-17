<form id="chainSetupForm" action="{{ route('admin.permissions.chain.save') }}">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="font-weight-bold">Apply chain to:</label>
            <select name="permission_type" id="chainTypeSelect" class="form-control" onchange="loadChainForType(this.value)">
                <option value="teacher">Teacher Permissions</option>
                <option value="staff">Staff Permissions</option>
                <option value="student">Student Permissions</option>
            </select>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Define the order of approvals. The first step will receive the request first.
    </div>

    <div id="chainStepsContainer" class="mb-3">
        <!-- Steps dynamically populated via JS when type changes -->
        <div class="text-center py-3" id="chainLoading" style="display:none;">
            <i class="fa fa-spinner fa-spin text-primary-custom fa-2x"></i>
        </div>
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary mb-3" onclick="addChainStep()">
        <i class="fa fa-plus"></i> Add Approval Step
    </button>

    <hr>
    <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger" onclick="deleteChainSetup()">
            <i class="fa fa-trash"></i> Delete Chain
        </button>
        <button type="button" class="btn btn-primary-custom" onclick="saveChainSetup()">
            <i class="fa fa-save"></i> Save Approval Chain
        </button>
    </div>
</form>

<script>
    var availableRoles = @json($roles);
    var availablePositions = @json($positions);

    function addChainStep(existingData = null) {
        const container = document.getElementById('chainStepsContainer');
        const stepCount = container.querySelectorAll('.chain-step').length + 1;
        
        const stepDiv = document.createElement('div');
        stepDiv.className = 'chain-step p-3 mb-2 border rounded bg-light position-relative';
        
        let roleOptions = '<optgroup label="Roles">';
        availableRoles.forEach(r => {
            roleOptions += `<option value="role_${r.id}">${r.name}</option>`;
        });
        roleOptions += '</optgroup><optgroup label="Positions">';
        availablePositions.forEach(p => {
            roleOptions += `<option value="prof_${p.id}">${p.name}</option>`;
        });
        roleOptions += '</optgroup>';

        stepDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="mr-3 font-weight-bold step-number">Step ${stepCount}</div>
                <div class="flex-grow-1 mr-3">
                    <select class="form-control approver-select">
                        <option value="">-- Select Approver --</option>
                        ${roleOptions}
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.chain-step').remove(); updateStepNumbers();">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(stepDiv);

        if (existingData) {
            const select = stepDiv.querySelector('.approver-select');
            if (existingData.role_id) {
                select.value = 'role_' + existingData.role_id;
            } else if (existingData.profession_id) {
                select.value = 'prof_' + existingData.profession_id;
            }
        }
    }

    function updateStepNumbers() {
        const steps = document.querySelectorAll('.chain-step');
        steps.forEach((step, index) => {
            step.querySelector('.step-number').innerText = 'Step ' + (index + 1);
        });
    }

    function loadChainForType(type) {
        document.querySelectorAll('.chain-step').forEach(e => e.remove());
        document.getElementById('chainLoading').style.display = 'block';

        fetch("{{ route('admin.permissions.chain.get') }}?type=" + type, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('chainLoading').style.display = 'none';
            if (data.success && data.data && data.data.length > 0) {
                data.data.forEach(chain => addChainStep(chain));
            } else {
                addChainStep(); // Add one empty step by default
            }
        });
    }

    function saveChainSetup() {
        const type = document.getElementById('chainTypeSelect').value;
        const steps = document.querySelectorAll('.chain-step');
        
        let approvers = [];
        let valid = true;

        steps.forEach((step, index) => {
            const val = step.querySelector('.approver-select').value;
            const select = step.querySelector('.approver-select');
            const text = select.options[select.selectedIndex].text;
            if (!val) { valid = false; return; }
            
            if (val.startsWith('role_')) {
                approvers.push({ role_id: val.split('_')[1], role_name: text });
            } else if (val.startsWith('prof_')) {
                approvers.push({ profession_id: val.split('_')[1], role_name: text });
            }
        });

        if (!valid || approvers.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please define at least one valid approver.' });
            return;
        }

        const btn = event.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        fetch("{{ route('admin.permissions.chain.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                permission_type: type,
                approvers: approvers
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Saved', text: data.message, confirmButtonColor: '#940000' });
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed' });
            }
        });
    }

    function deleteChainSetup() {
        const type = document.getElementById('chainTypeSelect').value;
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove all approval steps for " + type + " permissions.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('admin.permissions.chain.delete') }}?type=" + type, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: data.message, confirmButtonColor: '#940000' });
                        document.querySelectorAll('.chain-step').forEach(e => e.remove());
                        addChainStep();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Operation failed' });
                    }
                });
            }
        });
    }

    // Initial load
    setTimeout(() => {
        const tabInput = document.querySelector('#permissionFilterForm input[name="tab"]');
        if (tabInput && tabInput.value) {
            document.getElementById('chainTypeSelect').value = tabInput.value;
        }
        loadChainForType(document.getElementById('chainTypeSelect').value);
    }, 100);
</script>
