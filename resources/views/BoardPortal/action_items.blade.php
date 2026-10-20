@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-check-square-o"></i> Action items</h2>
            @if(!empty($caps['can_manage_action_items']))
                <button type="button" class="bp-btn" id="toggleCreateAction"><i class="fa fa-plus"></i> New action</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_action_items']))
            <div class="bp-create-panel" id="createActionPanel">
                <form id="createActionForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Meeting *</label>
                            <select class="bp-select" name="meetingID" required>
                                <option value="">— Select meeting —</option>
                                @foreach($sourceMeetings as $m)
                                    <option value="{{ $m->meetingID }}">{{ $m->title }} ({{ $m->meeting_date?->format('d M Y') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="full">
                            <label class="bp-label">Title *</label>
                            <input class="bp-input" type="text" name="title" required>
                        </div>
                        <div class="full">
                            <label class="bp-label">Description</label>
                            <textarea class="bp-textarea" name="description"></textarea>
                        </div>
                        <div>
                            <label class="bp-label">Assign to</label>
                            <select class="bp-select" name="assigned_to">
                                <option value="">— Optional —</option>
                                @foreach($boardMembers as $m)
                                    <option value="{{ $m->memberID }}">{{ $m->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="bp-label">Due date</label>
                            <input class="bp-input" type="date" name="due_date">
                        </div>
                        <div>
                            <label class="bp-label">Priority</label>
                            <select class="bp-select" name="priority">
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Save action</button>
                    </div>
                </form>
            </div>
        @endif

        @if($actionItems->isEmpty())
            <div class="bp-empty">No action items for your access level.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assignee</th>
                            <th>Due</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($actionItems as $item)
                            @php
                                $canEdit = ($caps['can_update_any_actions'] ?? false)
                                    || ((int) $item->assigned_to === (int) $member->memberID && ($caps['can_update_own_actions'] ?? false));
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->title }}</strong>
                                    @if($item->description)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 100) }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->assignee->full_name ?? '—' }}</td>
                                <td>{{ $item->due_date?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $item->priority ?: '—' }}</td>
                                <td>
                                    @if($canEdit)
                                        <select class="bp-select bp-action-status" data-id="{{ $item->actionItemID }}">
                                            @foreach(['Open','In Progress','Completed','Cancelled'] as $st)
                                                <option value="{{ $st }}" @selected(in_array($item->status, [$st], true) || ($st === 'Open' && $item->status === 'Pending'))>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="bp-badge">{{ $item->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toggle = document.getElementById('toggleCreateAction');
    const panel = document.getElementById('createActionPanel');
    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');

    const form = document.getElementById('createActionForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(`{{ route('board.portal.action_items.store') }}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            }).then(r => r.json()).then(data => {
                if (!data.success) throw new Error(data.message || 'Failed');
                location.reload();
            }).catch(err => alert(err.message || 'Failed'));
        });
    }

    document.querySelectorAll('.bp-action-status').forEach(function (el) {
        el.addEventListener('change', function () {
            fetch(`{{ route('board.portal.action_items.update_status') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ actionItemID: el.getAttribute('data-id'), status: el.value })
            })
            .then(r => r.json())
            .then(data => { if (!data.success) alert(data.message || 'Update failed'); })
            .catch(() => alert('Update failed'));
        });
    });
})();
</script>
@endsection
