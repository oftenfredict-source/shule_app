<!-- Review Subtasks Modal (for HOD) -->
<div class="modal fade" id="reviewSubtasksModal{{ $task->taskID }}" tabindex="-1">
    <div class="modal-dialog" style="max-width: 95%; width: 1200px;">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('teacher.sgpm.review_subtasks', ['count' => $task->subtasks->count()]) }}: {{ $task->kpi }}</h5>
                <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Filters -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><strong>{{ __('teacher.goals.target_member') }}</strong></label>
                                <select class="form-control filter-member" data-task="{{ $task->taskID }}">
                                    <option value="">{{ __('teacher.goals.select_member') }}</option>
                                    @php
                                        $assignedUser = $task->assignee;
                                        $profile = $assignedUser->teacher ?? $assignedUser->staff;
                                        $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $assignedUser->name;
                                    @endphp
                                    <option value="{{ $assignedUser->id }}" selected>{{ $fullName }} ({{ $assignedUser->name }})</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>{{ __('teacher.goals.status') }}</strong></label>
                                <select class="form-control filter-status" data-task="{{ $task->taskID }}">
                                    <option value="">{{ __('teacher.goals.status') }}</option>
                                    <option value="Draft">{{ __('teacher.goals.pending') }}</option>
                                    <option value="Submitted" selected>{{ __('teacher.goals.submitted') }}</option>
                                    <option value="Approved">{{ __('teacher.goals.approved') }}</option>
                                    <option value="Rejected">{{ __('teacher.goals.reject') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered subtasks-table" id="subtasksTable{{ $task->taskID }}">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th style="width: 25%;">{{ __('teacher.goals.task_name') }}</th>
                                <th style="width: 15%;">{{ __('teacher.sgpm.assigned_to') }}</th>
                                <th style="width: 8%;">{{ __('teacher.goals.weight') }}</th>
                                <th style="width: 10%;">{{ __('teacher.sgpm.due_date') }}</th>
                                <th style="width: 10%;">{{ __('teacher.goals.status') }}</th>
                                <th style="width: 17%;">{{ __('teacher.sgpm.upload_evidence') }}</th>
                                <th style="width: 15%;">{{ __('teacher.goals.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($task->subtasks as $subtask)
                                @php
                                    $assignedUser = $task->assignee;
                                    $profile = $assignedUser->teacher ?? $assignedUser->staff;
                                    $fullName = $profile ? ($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . $profile->last_name) : $assignedUser->name;
                                @endphp
                                <tr class="subtask-row" data-status="{{ $subtask->status }}" data-member="{{ $assignedUser->id }}">
                                    <td>
                                        <strong>{{ $subtask->title }}</strong>
                                        @if($subtask->description)
                                            <br><small class="text-muted">{{ $subtask->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $fullName }}</strong>
                                        <br><small class="text-muted">{{ $assignedUser->name }}</small>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $subtask->weight_percentage }}%</span></td>
                                    <td>{{ $subtask->due_date }}</td>
                                    <td>
                                        <span class="badge {{ $subtask->status == 'Draft' ? 'bg-secondary' : ($subtask->status == 'Submitted' ? 'bg-info' : ($subtask->status == 'Approved' ? 'bg-success' : 'bg-danger')) }}">
                                            @if($subtask->status == 'Submitted')
                                                {{ __('teacher.goals.submitted') }}
                                            @elseif($subtask->status == 'Approved')
                                                {{ __('teacher.goals.approved') }}
                                            @elseif($subtask->status == 'Rejected')
                                                {{ __('teacher.goals.reject') }}
                                            @else
                                                {{ __('teacher.goals.pending') }}
                                            @endif
                                        </span>
                                        @if($subtask->status == 'Approved')
                                            <br><small class="text-success"><strong>{{ __('teacher.sgpm.score') }}: {{ $subtask->achieved_score }}/{{ $subtask->weight_percentage }}%</strong></small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subtask->evidence_remarks)
                                            <small>{{ $subtask->evidence_remarks }}</small>
                                        @else
                                            <em class="text-muted">{{ __('teacher.goals.no_impl_steps') }}</em>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subtask->status == 'Submitted')
                                            <button class="btn btn-xs btn-success mb-1" data-bs-toggle="modal" data-bs-target="#approveSubtaskModal{{ $subtask->subtaskID }}" data-toggle="modal" data-target="#approveSubtaskModal{{ $subtask->subtaskID }}">
                                                <i class="fa fa-check"></i> {{ __('teacher.goals.approve_mark') }}
                                            </button>
                                            <button class="btn btn-xs btn-danger approve-subtask" data-id="{{ $subtask->subtaskID }}" data-action="reject">
                                                <i class="fa fa-times"></i> {{ __('teacher.goals.reject') }}
                                            </button>
                                        @elseif($subtask->status == 'Approved')
                                            <small class="text-success"><strong>✓ {{ __('teacher.goals.approved') }}</strong></small>
                                            @if($subtask->hod_comments)
                                                <br><em class="text-muted">"{{ $subtask->hod_comments }}"</em>
                                            @endif
                                        @elseif($subtask->status == 'Rejected')
                                            <small class="text-danger"><strong>✗ {{ __('teacher.goals.reject') }}</strong></small>
                                            @if($subtask->hod_comments)
                                                <br><em class="text-muted">"{{ $subtask->hod_comments }}"</em>
                                            @endif
                                        @else
                                            <em class="text-muted">{{ __('teacher.goals.pending') }}</em>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Approve Subtask Modal -->
                                <div class="modal fade" id="approveSubtaskModal{{ $subtask->subtaskID }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
                                            <form class="approve-subtask-form" data-id="{{ $subtask->subtaskID }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title">{{ __('teacher.goals.approve_mark') }}: {{ $subtask->title }}</h6>
                                                    <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <strong>{{ __('teacher.goals.weight') }}:</strong> {{ $subtask->weight_percentage }}%
                                                    </div>
                                                    <div class="form-group">
                                                        <label>{{ __('teacher.goals.marks_assign') }} {{ $subtask->weight_percentage }}%)</label>
                                                        <input type="number" name="achieved_score" class="form-control"
                                                               min="0" max="{{ $subtask->weight_percentage }}"
                                                               step="0.01" required>
                                                        <small class="text-muted">{{ __('teacher.goals.enter_weight_achieved') }}</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>{{ __('teacher.sgpm.comments') }}</label>
                                                        <textarea name="hod_comments" class="form-control" rows="3" placeholder="{{ __('teacher.goals.desc_ph') }}"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.sgpm.cancel') }}</button>
                                                    <button type="submit" class="btn text-white" style="background-color: #940000;">
                                                        <i class="fa fa-check"></i> {{ __('teacher.goals.approve_mark') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">{{ __('teacher.sgpm.no_subtasks_filter') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.sgpm.close') }}</button>
            </div>
        </div>
    </div>
</div>
