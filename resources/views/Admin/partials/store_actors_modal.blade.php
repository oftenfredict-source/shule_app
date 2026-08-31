<div class="modal fade store-modal store-actors-modal" id="inventoryActorsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title"><i class="fa fa-users"></i> Inventory Actors</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body store-actors-modal-body">
                <ul class="nav nav-tabs mb-3" id="actorModalTabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#actorTabList">Current Actors</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#actorTabAdd" id="actorTabAddLink">Add Actor</a></li>
                </ul>

                <form id="inventoryActorForm">
                    <input type="hidden" id="actorEditId" value="">

                    {{-- Person fields nje ya scroll, Select2 focus hairudishi scroll chini --}}
                    <div id="actorPersonFieldsStrip" class="store-actors-person-strip" style="display:none;">
                        <div class="row">
                            <div class="col-md-4 form-group mb-2">
                                <label class="font-weight-bold">Person Type</label>
                                <select class="form-control actor-form-select" id="actorPersonType" name="actor_type" required>
                                    <option value="">Select type...</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div class="col-md-8 form-group mb-2">
                                <label class="font-weight-bold">Select Person</label>
                                <select class="form-control actor-form-select" id="actorPersonSelect" name="actor_ref_id" required disabled>
                                    <option value="">Select type first...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="store-actors-tab-scroll">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="actorTabList">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="inventoryActorsTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Categories</th>
                                                <th>Privileges</th>
                                                <th>Granted</th>
                                                <th width="140">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inventoryActorsTableBody">
                                            @foreach($inventoryActors ?? [] as $i => $actor)
                                            <tr data-actor-id="{{ $actor['actorID'] }}">
                                                <td>{{ $i + 1 }}</td>
                                                <td><strong>{{ $actor['actor_name'] }}</strong></td>
                                                <td>{{ $actor['actor_type_label'] }}</td>
                                                <td class="small">{{ $actor['category_summary'] }}</td>
                                                <td class="small">{{ $actor['privileges_summary'] }}</td>
                                                <td class="small">{{ $actor['created_at'] }}<br><span class="text-muted">{{ $actor['granted_by'] }}</span></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-actor" data-id="{{ $actor['actorID'] }}"><i class="fa fa-pencil"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-actor" data-id="{{ $actor['actorID'] }}" data-name="{{ $actor['actor_name'] }}"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="actorTabAdd">
                                <div class="form-group">
                                    <label class="font-weight-bold">Category Access</label>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="actorScopeAll" name="actorCategoryScope" class="custom-control-input" value="all" checked>
                                        <label class="custom-control-label" for="actorScopeAll">All categories</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="actorScopeSpecific" name="actorCategoryScope" class="custom-control-input" value="specific">
                                        <label class="custom-control-label" for="actorScopeSpecific">Specific categories only</label>
                                    </div>
                                    <div id="actorCategoryChecks" class="border p-3 mt-2" style="display:none;">
                                        <div class="store-checkbox-toolbar">
                                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#actorCategoryChecks">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#actorCategoryChecks">Deselect All</button>
                                        </div>
                                        @foreach($allCategories ?? $categories as $cat)
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input actor-category-check" id="actorCat{{ $cat->categoryID }}" value="{{ $cat->categoryID }}">
                                            <label class="custom-control-label" for="actorCat{{ $cat->categoryID }}">{{ $cat->category_name }}</label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Privileges</label>
                                    <div id="actorPrivilegeChecks">
                                        <div class="store-checkbox-toolbar">
                                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-all-btn" data-target="#actorPrivilegeChecks">Select All</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary store-check-none-btn" data-target="#actorPrivilegeChecks">Deselect All</button>
                                        </div>
                                        <div class="row">
                                        @foreach(\App\Models\StoreInventoryActor::privilegeLabels() as $key => $label)
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input actor-privilege-check" id="actorPriv{{ $key }}" value="{{ $key }}">
                                                <label class="custom-control-label" for="actorPriv{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="actorModalFooter" style="display:none;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="inventoryActorForm" class="btn btn-primary-custom" id="btnSaveInventoryActor">
                    <i class="fa fa-save"></i> Save Actor Access
                </button>
            </div>
        </div>
    </div>
</div>
