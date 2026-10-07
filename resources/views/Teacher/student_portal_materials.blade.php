@if(($user_type ?? '') === 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<link rel="stylesheet" href="{{ asset('css/learning-portal-hub.css') }}?v=3">

@php $activeTab = session('portal_tab', request('tab', 'notes')); @endphp

<div class="content mt-3 lph">
    <section class="lph-hero">
        <div class="lph-hero-inner">
            <div>
                <span class="lph-kicker">Teacher workspace</span>
                <h1>Learning Portal</h1>
                <p>Publish notes, files, and quizzes to the Student Portal. Students see them after signing in with their admission number.</p>
            </div>
            @if(!empty($enabled))
            <div class="lph-hero-actions">
                <a href="{{ route('student_portal.login') }}" target="_blank" class="lph-btn lph-btn-solid">
                    <i class="fa fa-external-link"></i> Open student view
                </a>
            </div>
            @endif
        </div>
    </section>

    @if(session('success'))<div class="lph-flash ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="lph-flash err">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="lph-flash err">
            <ul class="mb-0 pl-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if(empty($enabled))
        <div class="lph-flash warn">
            Student portal is <strong>not enabled</strong> for this school. A Super Admin can turn it on during school registration or in school settings.
        </div>
    @else

    <div class="lph-stats">
        <div class="lph-stat"><div class="n">{{ $stats['notes'] }}</div><div class="l">Notes</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['books'] }}</div><div class="l">Files &amp; books</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['quizzes'] }}</div><div class="l">Quizzes</div></div>
        <div class="lph-stat"><div class="n">{{ $stats['attempts'] }}</div><div class="l">Student attempts</div></div>
    </div>

    <div class="lph-tabs" role="tablist">
        <button type="button" class="{{ $activeTab === 'notes' ? 'active' : '' }}" data-lph-tab="notes"><i class="fa fa-file-text-o"></i> Notes</button>
        <button type="button" class="{{ $activeTab === 'files' ? 'active' : '' }}" data-lph-tab="files"><i class="fa fa-cloud-upload"></i> Files</button>
        <button type="button" class="{{ $activeTab === 'quizzes' ? 'active' : '' }}" data-lph-tab="quizzes"><i class="fa fa-pencil-square-o"></i> Quizzes</button>
        <button type="button" class="{{ $activeTab === 'results' ? 'active' : '' }}" data-lph-tab="results"><i class="fa fa-bar-chart"></i> Results</button>
    </div>

    <div class="lph-tab-content">
        {{-- NOTES --}}
        <div class="lph-pane {{ $activeTab === 'notes' ? 'active' : '' }}" id="tab-notes" data-lph-pane="notes">
            <div class="lph-grid">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>New note</h2>
                        <p>Write content and optionally attach a file.</p>
                    </div>
                    <form method="POST" action="{{ route('student_portal.materials.notes.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('Teacher.partials.portal_class_fields')
                        <div class="lph-field">
                            <label class="lph-label">Title *</label>
                            <input class="form-control" name="title" placeholder="Note title" required maxlength="180">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Body</label>
                            <textarea class="form-control" name="body" rows="5" placeholder="Lesson notes or instructions"></textarea>
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Link (YouTube / website)</label>
                            <input class="form-control" name="external_url" type="url" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('external_url') }}">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Attachment</label>
                            <input class="form-control" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.mp4,.mp3">
                        </div>
                        <p class="file-hint">Optional: body, file, and/or link — max file {{ $uploadLimit ?? '256M' }}</p>
                        <button class="lph-btn lph-btn-brand" type="submit"><i class="fa fa-save"></i> Save note</button>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>Published notes</h2>
                        <p>{{ $notes->count() }} item{{ $notes->count() === 1 ? '' : 's' }} in the portal library</p>
                    </div>
                    <div class="lph-list">
                    @forelse($notes as $note)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $note->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $note->classID))->class_name ?? '—' }}
                                    @if($note->subclassID) · {{ optional($subclasses->firstWhere('subclassID', $note->subclassID))->subclass_name }}@endif
                                    @if($note->subjectID) · {{ optional($subjects->firstWhere('subjectID', $note->subjectID))->subject_name }}@endif
                                    @if($note->original_name)<br><i class="fa fa-paperclip"></i> {{ $note->original_name }}@endif
                                    @if(!empty($note->external_url))
                                        <br><a href="{{ $note->external_url }}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open link</a>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $note->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $note->is_published ? 'Published' : 'Hidden' }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <form method="POST" action="{{ route('student_portal.materials.notes.toggle', $note->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $note->is_published ? 'Hide' : 'Publish' }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.notes.delete', $note->id) }}" onsubmit="return confirm('Delete this note?');">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">No notes yet. Create one on the left.</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- FILES --}}
        <div class="lph-pane {{ $activeTab === 'files' ? 'active' : '' }}" id="tab-files" data-lph-pane="files">
            <div class="lph-grid">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>Upload material</h2>
                        <p>Books, handouts, slides, YouTube / web links, or audio.</p>
                    </div>
                    <form method="POST" action="{{ route('student_portal.materials.books.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('Teacher.partials.portal_class_fields')
                        <div class="lph-field">
                            <label class="lph-label">Title *</label>
                            <input class="form-control" name="title" placeholder="Material title" required maxlength="180">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Author / source</label>
                            <input class="form-control" name="author" placeholder="Optional">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Short description"></textarea>
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Link (YouTube / website)</label>
                            <input class="form-control" name="external_url" type="url" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('external_url') }}">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">File</label>
                            <input class="form-control" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.mp4,.mp3,.epub">
                        </div>
                        <p class="file-hint">Add a file and/or a link (YouTube, Drive, website). File max {{ $uploadLimit ?? '256M' }}</p>
                        <button class="lph-btn lph-btn-brand" type="submit"><i class="fa fa-upload"></i> Save material</button>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>Library</h2>
                        <p>{{ $books->count() }} uploaded item{{ $books->count() === 1 ? '' : 's' }}</p>
                    </div>
                    <div class="lph-list">
                    @forelse($books as $book)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $book->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $book->classID))->class_name ?? '—' }}
                                    @if($book->author) · {{ $book->author }}@endif
                                    @if($book->file_path)
                                        <br><a href="{{ asset($book->file_path) }}" target="_blank"><i class="fa fa-download"></i> {{ $book->original_name ?? 'Download' }}</a>
                                    @endif
                                    @if(!empty($book->external_url))
                                        <br><a href="{{ $book->external_url }}" target="_blank" rel="noopener"><i class="fa fa-youtube-play"></i> Open link</a>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $book->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $book->is_published ? 'Published' : 'Hidden' }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <form method="POST" action="{{ route('student_portal.materials.books.toggle', $book->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $book->is_published ? 'Hide' : 'Publish' }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.books.delete', $book->id) }}" onsubmit="return confirm('Delete this material?');">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">No files yet. Upload on the left.</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- QUIZZES --}}
        <div class="lph-pane {{ $activeTab === 'quizzes' ? 'active' : '' }}" id="tab-quizzes" data-lph-pane="quizzes">
            <div class="lph-grid quiz">
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>Create quiz</h2>
                        <p>Multiple choice (A–D) with instant scoring.</p>
                    </div>
                    <form method="POST" action="{{ route('student_portal.materials.quizzes.store') }}" id="quizForm">
                        @csrf
                        @include('Teacher.partials.portal_class_fields')
                        <div class="lph-field">
                            <label class="lph-label">Quiz title *</label>
                            <input class="form-control" name="title" placeholder="Quiz / exam title" required maxlength="180">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Instructions</label>
                            <textarea class="form-control" name="instructions" rows="2" placeholder="Guidance for students"></textarea>
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Opens at</label>
                            <input class="form-control" type="datetime-local" name="opens_at">
                        </div>
                        <div class="lph-field">
                            <label class="lph-label">Closes at</label>
                            <input class="form-control" type="datetime-local" name="closes_at">
                        </div>
                        <p class="file-hint">Optional window — if both are set, the quiz only accepts answers between those times.</p>
                        <div id="quizQuestions"></div>
                        <button type="button" class="lph-btn lph-btn-soft mb-2" id="addQuestion"><i class="fa fa-plus"></i> Add question</button>
                        <button class="lph-btn lph-btn-brand" type="submit"><i class="fa fa-save"></i> Save quiz</button>
                    </form>
                </div>
                <div class="lph-panel">
                    <div class="lph-panel-head">
                        <h2>Your quizzes</h2>
                        <p>{{ $quizzes->count() }} {{ $quizzes->count() === 1 ? 'quiz' : 'quizzes' }}</p>
                    </div>
                    <div class="lph-list">
                    @forelse($quizzes as $quiz)
                        <div class="lph-item">
                            <div>
                                <div class="lph-item-title">{{ $quiz->title }}</div>
                                <div class="lph-item-meta">
                                    {{ optional($classes->firstWhere('classID', $quiz->classID))->class_name ?? '—' }}
                                    · {{ $quiz->questions->count() }} questions · {{ $quiz->attempts->count() }} attempts
                                    @if(method_exists($quiz, 'isScheduled') && $quiz->isScheduled())
                                        <br><span class="lph-badge lph-badge-sched">{{ $quiz->opens_at->format('d M Y H:i') }} → {{ $quiz->closes_at->format('d M Y H:i') }}</span>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $quiz->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $quiz->is_published ? 'Published' : 'Hidden' }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.toggle', $quiz->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $quiz->is_published ? 'Hide' : 'Publish' }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.delete', $quiz->id) }}" onsubmit="return confirm('Delete this quiz and its attempts?');">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">No quizzes yet. Build one on the left.</div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- RESULTS --}}
        <div class="lph-pane {{ $activeTab === 'results' ? 'active' : '' }}" id="tab-results" data-lph-pane="results">
            <div class="lph-panel">
                <div class="lph-panel-head" style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:flex-end;">
                    <div>
                        <h2>Quiz results</h2>
                        <p>Scores submitted by students in the portal</p>
                    </div>
                    @php $hasAttempts = $quizzes->contains(fn ($q) => $q->attempts->count() > 0); @endphp
                    @if($hasAttempts)
                    <div class="lph-hero-actions">
                        <a class="lph-btn lph-btn-brand" style="width:auto;" href="{{ route('student_portal.materials.results.export', ['format' => 'xlsx']) }}">
                            <i class="fa fa-file-excel-o"></i> Export all (Excel)
                        </a>
                        <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials.results.export', ['format' => 'csv']) }}">
                            <i class="fa fa-download"></i> Export all (CSV)
                        </a>
                    </div>
                    @endif
                </div>
                @if(!$hasAttempts)
                    <div class="lph-empty">No attempts yet. Results appear here after students submit a quiz.</div>
                @else
                    @foreach($quizzes as $quiz)
                        @if($quiz->attempts->count())
                        <div class="lph-result-block">
                            <div class="lph-result-head">
                                <strong>{{ $quiz->title }}</strong>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                    <span style="color:#6b5c5c;font-size:0.82rem;">{{ $quiz->attempts->count() }} attempts · {{ $quiz->questions->count() }} questions</span>
                                    <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials.results.export.quiz', ['quizId' => $quiz->id, 'format' => 'xlsx']) }}">
                                        <i class="fa fa-file-excel-o"></i> Excel
                                    </a>
                                    <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials.results.export.quiz', ['quizId' => $quiz->id, 'format' => 'csv']) }}">
                                        CSV
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="lph-table">
                                    <thead>
                                        <tr><th>Student</th><th>Admission</th><th>Score</th><th>%</th><th>Submitted</th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach($quiz->attempts as $attempt)
                                        @php
                                            $st = $studentsById->get($attempt->studentID);
                                            $name = $st ? trim(($st->first_name ?? '').' '.($st->middle_name ?? '').' '.($st->last_name ?? '')) : 'Student #'.$attempt->studentID;
                                            $pct = $attempt->total > 0 ? round(($attempt->score / $attempt->total) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>{{ $st->admission_number ?? '—' }}</td>
                                            <td><strong>{{ $attempt->score }}</strong> / {{ $attempt->total }}</td>
                                            <td><span class="lph-pct {{ $pct >= 50 ? 'pass' : 'fail' }}">{{ $pct }}%</span></td>
                                            <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@if(!empty($enabled))
<script>
(function () {
    function showTab(name) {
        document.querySelectorAll('.lph-tabs [data-lph-tab]').forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-lph-tab') === name);
        });
        document.querySelectorAll('.lph-pane').forEach(function (pane) {
            pane.classList.toggle('active', pane.getAttribute('data-lph-pane') === name);
        });
    }

    document.querySelectorAll('.lph-tabs [data-lph-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showTab(btn.getAttribute('data-lph-tab'));
        });
    });

    var box = document.getElementById('quizQuestions');
    if (box) {
        var n = 0;
        function add() {
            var i = n++;
            var html = '<div class="lph-q" data-qi="'+i+'">'
                + '<div class="lph-q-top"><small>Question '+(i+1)+'</small>'
                + '<button type="button" class="lph-btn lph-btn-danger remove-q" style="padding:2px 8px;">×</button></div>'
                + '<input class="form-control mb-2" name="questions['+i+'][question_text]" placeholder="Write the question *" required>'
                + '<div class="row">'
                + '<div class="col-6 mb-2"><input class="form-control" name="questions['+i+'][option_a]" placeholder="A *" required></div>'
                + '<div class="col-6 mb-2"><input class="form-control" name="questions['+i+'][option_b]" placeholder="B *" required></div>'
                + '<div class="col-6 mb-2"><input class="form-control" name="questions['+i+'][option_c]" placeholder="C"></div>'
                + '<div class="col-6 mb-2"><input class="form-control" name="questions['+i+'][option_d]" placeholder="D"></div>'
                + '</div>'
                + '<select class="form-control" name="questions['+i+'][correct_option]" required>'
                + '<option value="A">Correct: A</option><option value="B">Correct: B</option>'
                + '<option value="C">Correct: C</option><option value="D">Correct: D</option></select>'
                + '</div>';
            box.insertAdjacentHTML('beforeend', html);
        }
        var addBtn = document.getElementById('addQuestion');
        if (addBtn) addBtn.addEventListener('click', add);
        box.addEventListener('click', function (e) {
            var btn = e.target.closest('.remove-q');
            if (!btn) return;
            var row = btn.closest('.lph-q');
            if (box.querySelectorAll('.lph-q').length > 1) row.remove();
        });
        add();
    }

    document.querySelectorAll('.portal-class').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var form = sel.closest('form');
            var sub = form.querySelector('.portal-subclass');
            if (!sub) return;
            var classId = sel.value;
            Array.prototype.forEach.call(sub.options, function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                opt.hidden = opt.getAttribute('data-class') !== classId;
            });
            sub.value = '';
        });
        sel.dispatchEvent(new Event('change'));
    });
})();
</script>
@endif
@include('includes.footer')
