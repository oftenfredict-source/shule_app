<div class="modal fade" id="blackBookActorsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-users"></i> Black Book Actors</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#bbActorTabList">Current Actors</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bbActorTabAdd" id="bbActorTabAddLink">Add Actor</a></li>
                </ul>

                <form id="blackBookActorForm">
                    <input type="hidden" id="bbActorEditId" value="">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="bbActorTabList">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Privileges</th>
                                            <th>Granted</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bbActorsTableBody">
                                        @foreach($blackBookActors ?? [] as $i => $actor)
                                        <tr data-actor-id="{{ $actor['actorID'] }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td><strong>{{ $actor['actor_name'] }}</strong></td>
                                            <td>{{ $actor['actor_type_label'] }}</td>
                                            <td class="small">{{ $actor['privileges_summary'] }}</td>
                                            <td class="small">{{ $actor['created_at'] }}<br><span class="text-muted">{{ $actor['granted_by'] }}</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-bb-actor" data-id="{{ $actor['actorID'] }}"><i class="fa fa-pencil"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bb-actor" data-id="{{ $actor['actorID'] }}" data-name="{{ $actor['actor_name'] }}"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="bbActorTabAdd">
                            <div class="row mb-3" id="bbActorPersonFields">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Person Type</label>
                                    <select class="form-control bb-search-select" id="bbActorPersonType">
                                        <option value="">Search and select type...</option>
                                        <option value="teacher">Teacher</option>
                                        <option value="staff">Staff</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="font-weight-bold">Select Person</label>
                                    <select class="form-control bb-search-select" id="bbActorPersonSelect" disabled>
                                        <option value="">Select type first...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Privileges</label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="bbActorSelectAllPriv">Select All (Full Access)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="bbActorClearPriv">Clear All</button>
                                </div>
                                <div class="row" id="bbActorPrivilegeChecks">
                                    @foreach(\App\Models\BlackBookActor::privilegeLabels() as $key => $label)
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input bb-actor-privilege-check" id="bbActorPriv{{ $key }}" value="{{ $key }}">
                                            <label class="custom-control-label" for="bbActorPriv{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="bbActorModalFooter" style="display:none;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="blackBookActorForm" class="btn btn-primary-custom" id="btnSaveBbActor">
                    <i class="fa fa-save"></i> Save Actor Access
                </button>
            </div>
        </div>
    </div>
</div>
