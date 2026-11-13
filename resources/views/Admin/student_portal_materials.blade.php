@if(($user_type ?? '') === 'Admin')
@include('includes.Admin_nav')
@else
@include('includes.teacher_nav')
@endif

<link rel="stylesheet" href="{{ asset('css/learning-portal-hub.css') }}?v=5">

@php $activeTab = session('portal_tab', request('tab', !empty($editingQuiz) ? 'quizzes' : 'notes')); @endphp

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
                        @include('Teacher.partials.portal_class_fields', ['subjectClassMap' => $subjectClassMap ?? []])
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
                        <p class="file-hint">Optional: body, file, and/or link - max file {{ $uploadLimit ?? '256M' }}</p>
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
                                    {{ optional($classes->firstWhere('classID', $note->classID))->class_name ?? '-' }}
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
                        @include('Teacher.partials.portal_class_fields', ['subjectClassMap' => $subjectClassMap ?? []])
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
                                    {{ optional($classes->firstWhere('classID', $book->classID))->class_name ?? '-' }}
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
                        <h2 id="quizFormHeading">{{ !empty($editingQuiz) ? 'Edit quiz / exam' : 'Create quiz / exam' }}</h2>
                        <p id="quizFormSub">{{ !empty($editingQuiz) ? 'Update title, schedule, marks, or questions, then save.' : 'Fill details below, then add questions one by one.' }}</p>
                    </div>
                    @if(($user_type ?? '') === 'Teacher' && $classes->isEmpty())
                        <div class="lph-hint-warn">
                            You have no teaching classes assigned yet. Ask admin to assign your class and subject before creating a quiz.
                        </div>
                    @endif
                    @if(!empty($editingQuiz) && ($editingQuiz['attempts_count'] ?? 0) > 0)
                        <div class="lph-hint-warn">
                            This quiz already has <strong>{{ $editingQuiz['attempts_count'] }}</strong> attempt(s). Saving will replace questions; past scores stay, but answer review may no longer match old question IDs.
                        </div>
                    @endif
                    <form method="POST"
                          action="{{ !empty($editingQuiz) ? route('student_portal.materials.quizzes.update', $editingQuiz['id']) : route('student_portal.materials.quizzes.store') }}"
                          id="quizForm"
                          data-store-url="{{ route('student_portal.materials.quizzes.store') }}"
                          data-update-url-template="{{ url('student-portal-materials/quizzes') }}/__ID__">
                        @csrf
                        @if(!empty($editingQuiz))
                            @method('PUT')
                        @endif
                        <div class="lph-quiz-meta">
                            @include('Teacher.partials.portal_class_fields', [
                                'requireSubject' => ($user_type ?? '') === 'Teacher',
                                'subjectClassMap' => $subjectClassMap ?? [],
                            ])
                            <div class="lph-field">
                                <label class="lph-label">Quiz title *</label>
                                <input class="form-control" name="title" placeholder="Quiz / exam title" required maxlength="180" value="{{ $editingQuiz['title'] ?? '' }}">
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">Instructions</label>
                                <textarea class="form-control" name="instructions" rows="3" placeholder="Guidance for students">{{ $editingQuiz['instructions'] ?? '' }}</textarea>
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">Opens at</label>
                                <input class="form-control" type="datetime-local" name="opens_at" value="{{ $editingQuiz['opens_at'] ?? '' }}">
                            </div>
                            <div class="lph-field">
                                <label class="lph-label">Closes at</label>
                                <input class="form-control" type="datetime-local" name="closes_at" value="{{ $editingQuiz['closes_at'] ?? '' }}">
                            </div>
                        </div>

                        <div class="lph-panel-head" style="margin-top:1.25rem;">
                            <h2 style="font-size:1.05rem;margin:0;">Questions</h2>
                            <p style="margin:0.35rem 0 0;">Chagua aina ya swali, bofya <strong>Add question</strong>, kisha andika maudhui.</p>
                        </div>

                        <div class="lph-quiz-add-row">
                            <div class="lph-field">
                                <label class="lph-label" for="sectionTypeSelect">Question type</label>
                                <select class="form-control" id="sectionTypeSelect">
                                    <option value="multiple_choice">Multiple choice</option>
                                    <option value="true_false">True / False</option>
                                    <option value="matching">Matching</option>
                                    <option value="fill_blank">Fill in the blanks</option>
                                    <option value="short_answer">Short answer</option>
                                    <option value="essay">Essay / Assignment</option>
                                </select>
                            </div>
                            <button type="button" class="lph-btn lph-btn-soft" id="addSectionBtn">
                                <i class="fa fa-plus"></i> Add question
                            </button>
                        </div>
                        <div id="quizSections"></div>
                        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;align-items:center;">
                            <button class="lph-btn lph-btn-brand" type="submit" id="quizSubmitBtn">
                                <i class="fa fa-save"></i> {{ !empty($editingQuiz) ? 'Update exam' : 'Save exam' }}
                            </button>
                            <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials') }}" id="quizCancelEditBtn"
                               style="{{ empty($editingQuiz) ? 'display:none;' : '' }}">Cancel edit</a>
                        </div>
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
                                    · {{ $quiz->questions->groupBy('section_number')->count() }} sections
                                    · {{ $quiz->questions->count() }} items
                                    · {{ $quiz->attempts->count() }} attempts
                                    @php
                                        $types = $quiz->questions->pluck('question_type')->unique()->filter()->values();
                                    @endphp
                                    @if($types->isNotEmpty())
                                        <br><span class="lph-type-line">{{ $types->map(fn ($t) => \App\Support\StudentPortalQuizScoring::typeLabel($t))->implode(' · ') }}</span>
                                    @endif
                                    @if(method_exists($quiz, 'isScheduled') && $quiz->isScheduled())
                                        <br><span class="lph-badge lph-badge-sched">{{ $quiz->opens_at->format('d M Y H:i') }} → {{ $quiz->closes_at->format('d M Y H:i') }}</span>
                                    @endif
                                    <br>
                                    <span class="lph-badge {{ $quiz->is_published ? 'lph-badge-pub' : 'lph-badge-hid' }}">{{ $quiz->is_published ? 'Published' : 'Hidden' }}</span>
                                </div>
                            </div>
                            <div class="lph-item-actions">
                                <a class="lph-btn lph-btn-soft" href="{{ route('student_portal.materials', ['edit' => $quiz->id, 'tab' => 'quizzes']) }}">Edit</a>
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.toggle', $quiz->id) }}">@csrf
                                    <button class="lph-btn lph-btn-soft" type="submit">{{ $quiz->is_published ? 'Hide' : 'Publish' }}</button>
                                </form>
                                <form method="POST" action="{{ route('student_portal.materials.quizzes.delete', $quiz->id) }}" onsubmit="return confirm('Delete this quiz and its attempts?');">@csrf @method('DELETE')
                                    <button class="lph-btn lph-btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="lph-empty">No quizzes yet. Create one above.</div>
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
                        <p>Fungua <strong>Review / Mark</strong> kuona majibu yote. Short answer &amp; essay/assignment weka alama mwenyewe.</p>
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
                                    <span class="lph-type-line" style="font-size:0.82rem;">{{ $quiz->attempts->count() }} attempts · {{ $quiz->questions->count() }} questions</span>
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
                                        <tr><th>Student</th><th>Admission</th><th>Score</th><th>%</th><th>Status</th><th>Submitted</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach($quiz->attempts as $attempt)
                                        @php
                                            $st = $studentsById->get($attempt->studentID);
                                            $name = $st ? trim(($st->first_name ?? '').' '.($st->middle_name ?? '').' '.($st->last_name ?? '')) : 'Student #'.$attempt->studentID;
                                            $pct = $attempt->total > 0 ? round(($attempt->score / $attempt->total) * 100) : 0;
                                            $hasManualQs = $quiz->questions->contains(fn ($q) => in_array($q->question_type, ['short_answer', 'essay'], true));
                                            $needsMark = $hasManualQs && empty($attempt->graded_at);
                                        @endphp
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>{{ $st->admission_number ?? '-' }}</td>
                                            <td><strong>{{ $attempt->score }}</strong> / {{ $attempt->total }}</td>
                                            <td><span class="lph-pct {{ $pct >= 50 ? 'pass' : 'fail' }}">{{ $pct }}%</span></td>
                                            <td>
                                                @if($needsMark)
                                                    <span class="lph-badge lph-badge-sched">Needs marking</span>
                                                @elseif($hasManualQs && $attempt->graded_at)
                                                    <span class="lph-badge lph-badge-pub">Graded</span>
                                                @else
                                                    <span class="lph-badge lph-badge-hid">Auto</span>
                                                @endif
                                            </td>
                                            <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('d M Y H:i') : '—' }}</td>
                                            <td>
                                                <a class="lph-btn lph-btn-brand" style="width:auto;padding:4px 10px;font-size:0.8rem;"
                                                   href="{{ route('student_portal.materials.attempts.review', ['quizId' => $quiz->id, 'attemptId' => $attempt->id]) }}">
                                                    <i class="fa fa-eye"></i> Review / Mark
                                                </a>
                                            </td>
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

    var sectionsBox = document.getElementById('quizSections');
    if (sectionsBox) {
        var sectionSeq = 0;
        var typeLabels = {
            multiple_choice: 'Multiple choice',
            true_false: 'True / False',
            matching: 'Matching (set correct pairs)',
            fill_blank: 'Fill in the blanks',
            short_answer: 'Short answers (teacher marks)',
            essay: 'Essay / Assignment (teacher marks)'
        };

        function renumberSections() {
            sectionsBox.querySelectorAll('.lph-section').forEach(function (el, idx) {
                var lab = el.querySelector('.lph-section-num');
                if (lab) lab.textContent = 'Question ' + (idx + 1);
            });
        }

        function marksField(name, defaultVal) {
            return '<div class="row align-items-center mt-2">'
                + '<div class="col-auto"><label class="lph-label mb-0" style="letter-spacing:0.06em;">Marks</label></div>'
                + '<div class="col-4 col-md-2">'
                + '<input class="form-control" type="number" min="0.5" step="0.5" name="'+name+'" value="'+defaultVal+'" required title="Marks for this question">'
                + '</div></div>';
        }

        function setNamedValue(root, nameEnds, value) {
            if (value == null) return;
            var els = root.querySelectorAll('input, textarea, select');
            for (var i = 0; i < els.length; i++) {
                var n = els[i].getAttribute('name') || '';
                if (n.slice(-nameEnds.length) === nameEnds) {
                    els[i].value = value;
                    return;
                }
            }
        }

        function addItemRow(sectionEl, type, si, data) {
            var items = sectionEl.querySelector('.lph-section-items');
            if (!items) return;
            var ii = items.querySelectorAll('.lph-item-row').length;
            var ptsName = 'sections['+si+'][items]['+ii+'][points]';
            var defPts = (type === 'essay') ? '5' : (type === 'short_answer' ? '2' : '1');
            if (data && data.points != null) defPts = String(data.points);
            var html = '';
            if (type === 'multiple_choice') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>Item '+(ii+1)+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="Question text *" required>'
                    + '<div class="row">'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_a]" placeholder="A *" required></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_b]" placeholder="B *" required></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_c]" placeholder="C"></div>'
                    + '<div class="col-6 mb-2"><input class="form-control" name="sections['+si+'][items]['+ii+'][option_d]" placeholder="D"></div>'
                    + '</div>'
                    + '<select class="form-control" name="sections['+si+'][items]['+ii+'][correct_option]" required>'
                    + '<option value="A">Correct: A</option><option value="B">Correct: B</option>'
                    + '<option value="C">Correct: C</option><option value="D">Correct: D</option></select>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'true_false') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>Item (roman) '+(ii+1)+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="Statement *" required>'
                    + '<select class="form-control" name="sections['+si+'][items]['+ii+'][correct_option]" required>'
                    + '<option value="true">True</option><option value="false">False</option></select>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'fill_blank') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>Blank '+(ii+1)+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" placeholder="Sentence with blank e.g. The capital is ____ *" required>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][correct_answer]" placeholder="Correct answer *" required>'
                    + '<input class="form-control mb-2" name="sections['+si+'][items]['+ii+'][alt_answers]" placeholder="Alt answers (optional, separate with |)">'
                    + marksField(ptsName, defPts)
                    + '</div>';
            } else if (type === 'short_answer' || type === 'essay') {
                html = '<div class="lph-item-row lph-q">'
                    + '<div class="lph-q-top"><small>'+(type === 'essay' ? 'Essay' : 'Short')+' '+(ii+1)+'</small>'
                    + '<button type="button" class="lph-btn lph-btn-danger remove-item" style="padding:2px 8px;">×</button></div>'
                    + '<textarea class="form-control mb-2" name="sections['+si+'][items]['+ii+'][question_text]" rows="2" placeholder="Prompt *" required></textarea>'
                    + marksField(ptsName, defPts)
                    + '</div>';
            }
            if (!html) return;
            items.insertAdjacentHTML('beforeend', html);
            if (!data) return;
            var row = items.lastElementChild;
            setNamedValue(row, '[question_text]', data.question_text || '');
            setNamedValue(row, '[option_a]', data.option_a || '');
            setNamedValue(row, '[option_b]', data.option_b || '');
            setNamedValue(row, '[option_c]', data.option_c || '');
            setNamedValue(row, '[option_d]', data.option_d || '');
            setNamedValue(row, '[correct_option]', data.correct_option || '');
            setNamedValue(row, '[correct_answer]', data.correct_answer || '');
            setNamedValue(row, '[alt_answers]', data.alt_answers || '');
            setNamedValue(row, '[points]', data.points != null ? data.points : defPts);
        }

        function addMatchPair(sectionEl, si, leftText, rightText) {
            var pairs = sectionEl.querySelector('.lph-match-pairs');
            if (!pairs) return;
            var pi = pairs.querySelectorAll('.lph-match-row').length;
            if (pi >= 10) { alert('Maximum 10 matching pairs.'); return; }
            pairs.insertAdjacentHTML('beforeend',
                '<div class="lph-match-row row" style="margin-bottom:6px;">'
                + '<div class="col-5"><input class="form-control" name="sections['+si+'][left]['+pi+']" placeholder="A'+(pi+1)+' — prompt *" required></div>'
                + '<div class="col-5"><input class="form-control" name="sections['+si+'][right]['+pi+']" placeholder="Correct B match for A'+(pi+1)+' *" required></div>'
                + '<div class="col-2"><input type="hidden" name="sections['+si+'][match]['+pi+']" value="'+pi+'">'
                + '<button type="button" class="lph-btn lph-btn-danger remove-match" style="width:100%;padding:6px;">×</button></div></div>'
            );
            var row = pairs.lastElementChild;
            if (leftText != null) setNamedValue(row, '[left]['+pi+']', leftText);
            if (rightText != null) setNamedValue(row, '[right]['+pi+']', rightText);
        }

        function addSection(type, seed) {
            var si = sectionSeq++;
            var wrap = document.createElement('div');
            wrap.className = 'lph-section lph-q';
            wrap.dataset.si = String(si);
            wrap.dataset.type = type;
            var matchPts = (seed && seed.points != null) ? String(seed.points) : '5';
            var body = '<div class="lph-q-top">'
                + '<small><span class="lph-section-num">Question</span> · '+ (typeLabels[type] || type) +'</small>'
                + '<button type="button" class="lph-btn lph-btn-danger remove-section" style="padding:2px 8px;">×</button></div>'
                + '<input type="hidden" name="sections['+si+'][question_type]" value="'+type+'">'
                + '<input class="form-control mb-2" name="sections['+si+'][section_title]" placeholder="Section title (optional) e.g. Matching items">'
                + '<textarea class="form-control mb-2" name="sections['+si+'][instructions]" rows="1" placeholder="Section instructions (optional)"></textarea>';

            if (type === 'matching') {
                body += '<div class="lph-hint">'
                    + '<strong>How matching works:</strong><br>'
                    + '1) Enter a <em>prompt</em> in column A and its <em>correct answer</em> in column B (one pair = the system answer key).<br>'
                    + '2) The system stores those pairs as correct answers and <strong>marks automatically</strong>.<br>'
                    + '3) Students see A in order, with B shuffled — they drag B onto A.<br>'
                    + 'Max 10 pairs. Partial credit: each correct pair earns a share of the total marks below.'
                    + '</div>'
                    + marksField('sections['+si+'][points]', matchPts)
                    + '<div class="row mb-1 mt-2 lph-match-cols"><div class="col-5">Column A (prompt)</div><div class="col-5">Column B (correct answer)</div></div>'
                    + '<div class="lph-match-pairs"></div>'
                    + '<button type="button" class="lph-btn lph-btn-soft mb-2 add-match-pair"><i class="fa fa-plus"></i> Add correct pair</button>';
            } else {
                body += '<div class="lph-section-items"></div>'
                    + '<button type="button" class="lph-btn lph-btn-soft mb-2 add-section-item"><i class="fa fa-plus"></i> Add item</button>';
            }
            wrap.innerHTML = body;
            sectionsBox.appendChild(wrap);

            if (seed) {
                setNamedValue(wrap, '[section_title]', seed.section_title || '');
                setNamedValue(wrap, '[instructions]', seed.instructions || '');
            }

            if (type === 'matching') {
                if (seed && seed.left && seed.left.length) {
                    for (var mi = 0; mi < seed.left.length; mi++) {
                        addMatchPair(wrap, si, seed.left[mi] || '', (seed.right && seed.right[mi]) ? seed.right[mi] : '');
                    }
                } else {
                    addMatchPair(wrap, si);
                    addMatchPair(wrap, si);
                }
            } else if (seed && seed.items && seed.items.length) {
                seed.items.forEach(function (item) { addItemRow(wrap, type, si, item); });
            } else {
                addItemRow(wrap, type, si);
                if (type === 'true_false') addItemRow(wrap, type, si);
            }
            renumberSections();
        }

        var addSectionBtn = document.getElementById('addSectionBtn');
        var typeSelect = document.getElementById('sectionTypeSelect');
        if (addSectionBtn && typeSelect) {
            addSectionBtn.addEventListener('click', function () {
                addSection(typeSelect.value || 'multiple_choice');
            });
        }

        sectionsBox.addEventListener('click', function (e) {
            var sectionEl = e.target.closest('.lph-section');
            if (!sectionEl) return;
            var si = sectionEl.dataset.si;
            var type = sectionEl.dataset.type;
            if (e.target.closest('.remove-section')) {
                if (sectionsBox.querySelectorAll('.lph-section').length <= 1) return;
                sectionEl.remove();
                renumberSections();
                return;
            }
            if (e.target.closest('.add-section-item')) {
                addItemRow(sectionEl, type, si);
                return;
            }
            if (e.target.closest('.add-match-pair')) {
                addMatchPair(sectionEl, si);
                return;
            }
            if (e.target.closest('.remove-item')) {
                var items = sectionEl.querySelectorAll('.lph-item-row');
                if (items.length > 1) e.target.closest('.lph-item-row').remove();
                return;
            }
            if (e.target.closest('.remove-match')) {
                var rows = sectionEl.querySelectorAll('.lph-match-row');
                if (rows.length > 2) e.target.closest('.lph-match-row').remove();
            }
        });

        var editingQuiz = @json($editingQuiz ?? null);
        if (editingQuiz && editingQuiz.sections && editingQuiz.sections.length) {
            editingQuiz.sections.forEach(function (sec) {
                addSection(sec.question_type || 'multiple_choice', sec);
            });
        } else {
            addSection('multiple_choice');
        }
    }

    function filterPortalPlacement(form, keepSubject) {
        var classSel = form.querySelector('.portal-class');
        var sub = form.querySelector('.portal-subclass');
        var subjectSel = form.querySelector('.portal-subject');
        if (!classSel) return;
        var classId = classSel.value;

        if (sub) {
            Array.prototype.forEach.call(sub.options, function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                opt.hidden = !classId || opt.getAttribute('data-class') !== classId;
            });
            if (!classSel.dataset.keepSubclass) sub.value = '';
            delete classSel.dataset.keepSubclass;
        }

        if (subjectSel) {
            var current = subjectSel.value;
            var visibleCount = 0;
            Array.prototype.forEach.call(subjectSel.options, function (opt) {
                if (!opt.value) { opt.hidden = false; return; }
                var classes = (opt.getAttribute('data-classes') || '').split(',').filter(Boolean);
                var show = !classId || classes.indexOf(classId) !== -1;
                opt.hidden = !show;
                if (show) visibleCount++;
            });
            if (!keepSubject || !current) {
                subjectSel.value = '';
            } else {
                var selected = subjectSel.querySelector('option[value="'+current+'"]');
                if (!selected || selected.hidden) subjectSel.value = '';
            }
            // Auto-select when only one taught subject for this class
            if (!subjectSel.value && visibleCount === 1) {
                Array.prototype.some.call(subjectSel.options, function (opt) {
                    if (opt.value && !opt.hidden) {
                        subjectSel.value = opt.value;
                        return true;
                    }
                    return false;
                });
            }
        }
    }

    document.querySelectorAll('.portal-class').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var form = sel.closest('form');
            if (!form) return;
            filterPortalPlacement(form, false);
        });
    });

    var editingQuizMeta = @json($editingQuiz ?? null);
    if (editingQuizMeta) {
        var quizForm = document.getElementById('quizForm');
        if (quizForm) {
            var classSel = quizForm.querySelector('.portal-class');
            var subSel = quizForm.querySelector('.portal-subclass');
            var subjectSel = quizForm.querySelector('select[name="subjectID"]');
            if (classSel && editingQuizMeta.classID) {
                classSel.dataset.keepSubclass = '1';
                classSel.value = String(editingQuizMeta.classID);
                filterPortalPlacement(quizForm, true);
            }
            if (subSel && editingQuizMeta.subclassID) {
                subSel.value = String(editingQuizMeta.subclassID);
            }
            if (subjectSel && editingQuizMeta.subjectID) {
                subjectSel.value = String(editingQuizMeta.subjectID);
            }
        }
    } else {
        document.querySelectorAll('.portal-class').forEach(function (sel) {
            var form = sel.closest('form');
            if (form) filterPortalPlacement(form, false);
        });
    }
})();
</script>
@endif
@include('includes.footer')
