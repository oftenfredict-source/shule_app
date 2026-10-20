@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-gavel"></i> Resolutions</h2>
            @if(!empty($caps['can_manage_resolutions']))
                <button type="button" class="bp-btn" id="toggleCreateResolution"><i class="fa fa-plus"></i> New resolution</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_resolutions']))
            <div class="bp-create-panel" id="createResolutionPanel">
                <form id="createResolutionForm">
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
                            <label class="bp-label">Proposed by</label>
                            <select class="bp-select" name="proposed_by">
                                <option value="{{ $member->memberID }}">{{ $member->full_name }}</option>
                                @foreach($boardMembers as $m)
                                    @if((int)$m->memberID !== (int)$member->memberID)
                                        <option value="{{ $m->memberID }}">{{ $m->full_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="bp-label">Assigned to</label>
                            <select class="bp-select" name="assigned_to">
                                <option value="">— Optional —</option>
                                @foreach($boardMembers as $m)
                                    <option value="{{ $m->memberID }}">{{ $m->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="bp-label">Status</label>
                            <select class="bp-select" name="status">
                                <option value="Proposed">Proposed</option>
                                <option value="Adopted">Adopted</option>
                                <option value="Deferred">Deferred</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Implemented">Implemented</option>
                            </select>
                        </div>
                        <div>
                            <label class="bp-label">Deadline</label>
                            <input class="bp-input" type="date" name="implementation_deadline">
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Save resolution</button>
                    </div>
                </form>
            </div>
        @endif

        @if($resolutions->isEmpty())
            <div class="bp-empty">No resolutions found.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Assigned</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resolutions as $res)
                            <tr>
                                <td>{{ $res->resolution_number ?: '—' }}</td>
                                <td>
                                    <strong>{{ $res->title }}</strong>
                                    @if($res->description)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($res->description), 120) }}</div>
                                    @endif
                                </td>
                                <td>{{ $res->resolution_date?->format('d M Y') ?: '—' }}</td>
                                <td>{{ $res->assignee->full_name ?? '—' }}</td>
                                <td><span class="bp-badge">{{ $res->status }}</span></td>
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
    const toggle = document.getElementById('toggleCreateResolution');
    const panel = document.getElementById('createResolutionPanel');
    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');
    const form = document.getElementById('createResolutionForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(`{{ route('board.portal.resolutions.store') }}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        }).then(r => r.json()).then(data => {
            if (!data.success) throw new Error(data.message || 'Failed');
            location.reload();
        }).catch(err => alert(err.message || 'Failed'));
    });
})();
</script>
@endsection
