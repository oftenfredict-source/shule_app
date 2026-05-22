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
