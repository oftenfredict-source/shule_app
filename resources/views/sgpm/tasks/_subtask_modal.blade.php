<!-- Subtasks Modal -->
<div class="modal fade" id="subtasksModal{{ $task->taskID }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">
            <div class="modal-header">
                <h5 class="modal-title" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;">{{ __('teacher.sgpm.manage_subtasks') }}: {{ $task->kpi }}</h5>
                <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ __('teacher.sgpm.close') }}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Progress Summary -->
                <div class="alert alert-info">
                    <strong>{{ __('teacher.goals.weight') }}:</strong> {{ $task->weight }}% |
                    <strong>{{ __('teacher.goals.allocation') }}</strong> {{ $task->subtasks->sum('weight_percentage') }}% |
                    <strong>{{ __('teacher.goals.left') }}</strong> {{ $task->weight - $task->subtasks->sum('weight_percentage') }}%
                </div>

                <!-- Sending Progress Bar (hidden by default) -->
                <div id="sendingProgress{{ $task->taskID }}" style="display:none;" class="mb-3">
                    <div class="card border-0" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%); border-left: 4px solid #940000 !important; border-left: solid;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa fa-paper-plane fa-spin text-danger mr-2"></i>
                                <strong style="color: #940000;" id="sendingLabel{{ $task->taskID }}">{{ __('teacher.sgpm.sending_review') }}</strong>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 5px; background: #f0e0e0;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar"
                                     style="width: 100%; background-color: #940000 !important;"
                                     id="sendingProgressBar{{ $task->taskID }}">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">{{ __('teacher.loading') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Add Subtask Form (using specific class, NOT ajax-form to avoid footer global handler) -->
                <div class="card mb-3">
                    <div class="card-header" style="background-color: #f8f9fa; color: #940000; font-weight: 600;">
                        <strong>{{ __('teacher.goals.add_subtasks') }}</strong>
                    </div>
                    <div class="card-body">
                        <form class="subtask-create-form" data-task="{{ $task->taskID }}" action="{{ route('sgpm.subtasks.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="taskID" value="{{ $task->taskID }}">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">{{ __('teacher.goals.subtask_name') }}</label>
                                    <input type="text" name="title" class="form-control" placeholder="{{ __('teacher.goals.example_subtask') }}" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">{{ __('teacher.goals.weight_pct') }}</label>
                                    <input type="number" name="weight_percentage" class="form-control" min="1" max="{{ $task->weight - $task->subtasks->sum('weight_percentage') }}" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">{{ __('teacher.sgpm.due_date') }}</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">{{ __('teacher.goals.desc_optional') }}</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="{{ __('teacher.goals.desc_ph') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm text-white subtask-save-btn" style="background-color: #940000;">
                                <i class="fa fa-plus"></i> {{ __('teacher.goals.add_subtasks') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Subtasks List -->
                <div class="card">
                    <div class="card-header" style="background-color: #f8f9fa; color: #940000; font-weight: 600;">
                        <strong>{{ __('teacher.goals.my_subtasks') }}</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher.goals.task_name') }}</th>
                                    <th>{{ __('teacher.goals.weight') }}</th>
                                    <th>{{ __('teacher.sgpm.due_date') }}</th>
                                    <th>{{ __('teacher.goals.status') }}</th>
                                    <th>{{ __('teacher.goals.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($task->subtasks as $subtask)
                                    <tr>
                                        <td>
                                            <strong>{{ $subtask->title }}</strong>
                                            @if($subtask->description)
                                                <br><small class="text-muted">{{ $subtask->description }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $subtask->weight_percentage }}%</td>
                                        <td>{{ $subtask->due_date }}</td>
                                        <td>
                                            @php
                                                $statusColor = match($subtask->status) {
                                                    'Draft'     => 'bg-secondary',
                                                    'Submitted' => 'bg-info',
                                                    'Approved'  => 'bg-success',
                                                    'Rejected'  => 'bg-danger',
                                                    default     => 'bg-secondary',
                                                };
                                                $statusLabel = match($subtask->status) {
                                                    'Submitted' => __('teacher.goals.awaiting_review', ['who' => __('teacher.goals.hod')]),
                                                    'Approved'  => __('teacher.goals.approved'),
                                                    'Rejected'  => __('teacher.goals.reject'),
                                                    default     => __('teacher.goals.pending'),
                                                };
                                            @endphp
                                            <span class="badge {{ $statusColor }}">{{ $statusLabel }}</span>
                                            @if($subtask->status == 'Approved')
                                                <br><small class="text-success"><strong>{{ __('teacher.sgpm.score') }}: {{ $subtask->achieved_score }}/{{ $subtask->weight_percentage }}%</strong></small>
                                            @elseif($subtask->status == 'Rejected' && $subtask->hod_comments)
                                                <br><small class="text-danger"><i class="fa fa-comment"></i> {{ $subtask->hod_comments }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subtask->status == 'Draft')
                                                <button class="btn btn-xs btn-success send-subtask-btn"
                                                        data-id="{{ $subtask->subtaskID }}"
                                                        data-task="{{ $task->taskID }}"
                                                        data-label="{{ __('teacher.sgpm.sending_review') }}">
                                                    <i class="fa fa-paper-plane"></i> {{ __('teacher.goals.hod') }}
                                                </button>
                                            @elseif($subtask->status == 'Rejected')
                                                <button class="btn btn-xs btn-warning send-subtask-btn"
                                                        data-id="{{ $subtask->subtaskID }}"
                                                        data-task="{{ $task->taskID }}"
                                                        data-label="{{ __('teacher.sgpm.sending_review') }}">
                                                    <i class="fa fa-refresh"></i> {{ __('teacher.goals.submitted') }}
                                                </button>
                                            @elseif($subtask->status == 'Submitted')
                                                <small class="text-info"><i class="fa fa-clock-o"></i> {{ __('teacher.goals.awaiting_review', ['who' => __('teacher.goals.hod')]) }}</small>
                                            @elseif($subtask->status == 'Approved')
                                                <small class="text-success"><i class="fa fa-check-circle"></i> {{ __('teacher.goals.approved') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">{{ __('teacher.goals.not_broken_down') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">{{ __('teacher.sgpm.close') }}</button>
            </div>
        </div>
    </div>
</div>
