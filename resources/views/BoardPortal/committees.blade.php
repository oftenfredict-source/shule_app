@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-sitemap"></i> Committees</h2>
            @if(!empty($caps['can_manage_committees']))
                <button type="button" class="bp-btn" id="toggleCreateCommittee"><i class="fa fa-plus"></i> New committee</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_committees']))
            <div class="bp-create-panel" id="createCommitteePanel">
                <form id="createCommitteeForm">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Committee name *</label>
                            <input class="bp-input" type="text" name="committee_name" required>
                        </div>
                        <div>
                            <label class="bp-label">Formed date</label>
                            <input class="bp-input" type="date" name="formed_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="bp-label">Status</label>
                            <select class="bp-select" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="full">
                            <label class="bp-label">Description</label>
                            <textarea class="bp-textarea" name="description"></textarea>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Save committee</button>
                    </div>
                </form>
            </div>
        @endif

        @if($committees->isEmpty())
            <div class="bp-empty">No committees yet.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Members</th>
                            <th>Formed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($committees as $c)
                            <tr>
                                <td>
                                    <strong>{{ $c->committee_name }}</strong>
                                    @if($c->description)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit($c->description, 100) }}</div>
                                    @endif
                                </td>
                                <td><span class="bp-badge">{{ $c->status }}</span></td>
                                <td>{{ $c->members_count ?? 0 }}</td>
                                <td>{{ $c->formed_date ? \Carbon\Carbon::parse($c->formed_date)->format('d M Y') : '—' }}</td>
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
    const toggle = document.getElementById('toggleCreateCommittee');
    const panel = document.getElementById('createCommitteePanel');
    const form = document.getElementById('createCommitteeForm');
    if (toggle && panel) {
        toggle.addEventListener('click', function () {
            panel.classList.toggle('is-open');
        });
    }
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(form);
        fetch(`{{ route('board.portal.committees.store') }}`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        }).then(r => r.json()).then(data => {
            if (!data.success) throw new Error(data.message || 'Failed');
            location.reload();
        }).catch(err => alert(err.message || 'Failed'));
    });
})();
</script>
@endsection
