@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-file-text-o"></i> Minutes</h2>
            @if(!empty($caps['can_manage_minutes']))
                <button type="button" class="bp-btn" id="toggleCreateMinute"><i class="fa fa-plus"></i> Record minutes</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_minutes']))
            <div class="bp-create-panel" id="createMinutePanel">
                <form id="createMinuteForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Meeting *</label>
                            <select class="bp-select" name="meetingID" required>
                                <option value="">— Select ongoing/completed meeting —</option>
                                @foreach($eligibleMeetings as $m)
                                    <option value="{{ $m->meetingID }}">{{ $m->title }} ({{ $m->meeting_date?->format('d M Y') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="full">
                            <label class="bp-label">Title *</label>
                            <input class="bp-input" type="text" name="title" required placeholder="Minutes of …">
                        </div>
                        <div class="full">
                            <label class="bp-label">Content</label>
                            <textarea class="bp-textarea" name="content" style="min-height:120px;"></textarea>
                        </div>
                        <div class="full">
                            <label class="bp-label">Decisions summary</label>
                            <textarea class="bp-textarea" name="decisions_summary"></textarea>
                        </div>
                        <div>
                            <label class="bp-label">Status</label>
                            <select class="bp-select" name="status">
                                <option value="Draft">Draft</option>
                                <option value="Submitted">Submitted</option>
                                <option value="Approved">Approved</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Save minutes</button>
                    </div>
                </form>
            </div>
        @endif

        @if($minutes->isEmpty())
            <div class="bp-empty">No minutes available.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Meeting</th>
                            <th>Prepared by</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($minutes as $minute)
                            <tr>
                                <td>
                                    <strong>{{ $minute->title }}</strong>
                                    @if($minute->decisions_summary)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($minute->decisions_summary), 140) }}</div>
                                    @endif
                                </td>
                                <td>{{ $minute->meeting->title ?? '—' }}</td>
                                <td>{{ $minute->preparer->full_name ?? '—' }}</td>
                                <td><span class="bp-badge">{{ $minute->status }}</span></td>
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
    const toggle = document.getElementById('toggleCreateMinute');
    const panel = document.getElementById('createMinutePanel');
    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');
    const form = document.getElementById('createMinuteForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(`{{ route('board.portal.minutes.store') }}`, {
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
