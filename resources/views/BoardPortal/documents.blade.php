@extends('BoardPortal.layout')

@section('content')
    <div class="bp-card">
        <div class="bp-card-head">
            <h2><i class="fa fa-folder-open-o"></i> Documents</h2>
            @if(!empty($caps['can_manage_documents']))
                <button type="button" class="bp-btn" id="toggleUploadDoc"><i class="fa fa-upload"></i> Upload</button>
            @endif
        </div>

        @if(!empty($caps['can_manage_documents']))
            <div class="bp-create-panel" id="uploadDocPanel">
                <form id="uploadDocForm" enctype="multipart/form-data">
                    <div class="bp-form-grid">
                        <div class="full">
                            <label class="bp-label">Title *</label>
                            <input class="bp-input" type="text" name="title" required>
                        </div>
                        <div>
                            <label class="bp-label">Category</label>
                            <input class="bp-input" type="text" name="category" placeholder="e.g. Finance, Policy">
                        </div>
                        <div>
                            <label class="bp-label">File *</label>
                            <input class="bp-input" type="file" name="file" required>
                        </div>
                        <div class="full">
                            <label class="bp-label">Description</label>
                            <textarea class="bp-textarea" name="description"></textarea>
                        </div>
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="submit" class="bp-btn"><i class="fa fa-save"></i> Upload document</button>
                    </div>
                </form>
            </div>
        @endif

        @if($documents->isEmpty())
            <div class="bp-empty">No documents uploaded yet.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="bp-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Uploaded</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr>
                                <td>
                                    <strong>{{ $doc->title }}</strong>
                                    @if($doc->description)
                                        <div class="bp-muted" style="font-size:0.82rem;margin-top:4px;">{{ \Illuminate\Support\Str::limit(strip_tags($doc->description), 100) }}</div>
                                    @endif
                                </td>
                                <td>{{ $doc->category ?: '—' }}</td>
                                <td>{{ $doc->created_at?->format('d M Y') ?: '—' }}</td>
                                <td>
                                    <a class="bp-btn" href="{{ route('board.portal.documents.download', $doc->documentID) }}">
                                        <i class="fa fa-download"></i> Download
                                    </a>
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
    const toggle = document.getElementById('toggleUploadDoc');
    const panel = document.getElementById('uploadDocPanel');
    if (toggle && panel) toggle.addEventListener('click', () => panel.classList.toggle('is-open'));
    if (panel && new URLSearchParams(location.search).get('create') === '1') panel.classList.add('is-open');
    const form = document.getElementById('uploadDocForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(`{{ route('board.portal.documents.store') }}`, {
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
