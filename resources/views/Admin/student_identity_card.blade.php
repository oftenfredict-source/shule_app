@php
    $user_type = Session::get('user_type');
@endphp
@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('Admin.partials.student_id_card_helpers')

<style>
    :root {
        --id-primary-color: #940000;
        --id-secondary-color: #ffffff;
        --id-accent-color: #2f2f2f;
    }

    body {
        font-family: 'Century Gothic', 'Arial', sans-serif;
    }

    .id-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (max-width: 1200px) {
        .id-cards-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .id-cards-grid { grid-template-columns: 1fr; }
    }

    .id-card-container {
        perspective: 1000px;
        width: 100%;
        aspect-ratio: 1.585;
        max-width: 340px;
        margin: 0 auto;
    }

    .id-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s;
        transform-style: preserve-3d;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        border-radius: 8px;
    }

    .id-card-container.flipped .id-card-inner {
        transform: rotateY(180deg);
    }

    .id-card-front {
        transform: rotateY(0deg);
    }

    .id-card-front, .id-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .id-card-back { transform: rotateY(180deg); }

    .id-logo-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 42%;
        max-height: 55%;
        object-fit: contain;
        opacity: 0.08;
        z-index: 0;
        pointer-events: none;
    }

    .id-card-content {
        position: relative;
        z-index: 1;
        height: 100%;
        width: 100%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .flip-btn, .download-card-btn {
        position: absolute;
        z-index: 100;
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 50%;
        width: 34px;
        height: 34px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
        color: var(--id-primary-color);
        padding: 0;
        outline: none;
    }

    .flip-btn { top: 10px; right: 10px; }
    .flip-btn:hover { background: var(--id-primary-color); color: #fff; transform: rotate(180deg) scale(1.1); }

    .download-card-btn {
        bottom: 10px;
        right: 10px;
        background: rgba(0,0,0,0.85);
        color: #fff;
    }
    .download-card-btn:hover { background: #000; transform: scale(1.15); }

    .corner-accent {
        position: absolute;
        top: 0;
        right: 0;
        width: 52px;
        height: 28px;
        background: var(--id-primary-color);
        border-top-right-radius: 8px;
        border-bottom-left-radius: 24px;
        z-index: 2;
    }
    .dot-grid {
        position: absolute;
        top: 32px;
        right: 8px;
        display: grid;
        grid-template-columns: repeat(7, 4px);
        gap: 3px;
        z-index: 2;
    }
    .dot-grid span {
        width: 4px;
        height: 4px;
        background: #d5d5d5;
        border-radius: 50%;
        display: block;
    }

    /* FRONT */
    .id-header {
        background: transparent;
        color: var(--id-primary-color);
        padding: 10px 12px 4px;
        min-height: 52px;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 1;
    }
    .id-header img {
        height: 36px;
        width: 36px;
        object-fit: contain;
        flex-shrink: 0;
    }
    .id-header-text { flex: 1; text-align: left; }
    .id-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 0.8rem;
        line-height: 1.15;
        text-transform: uppercase;
        color: var(--id-primary-color);
        white-space: nowrap;
    }
    .id-header small {
        font-size: 0.58rem;
        font-weight: 400;
        color: #888;
        display: block;
        margin-top: 2px;
        text-transform: none;
    }

    .id-body {
        flex: 1 1 auto;
        display: flex;
        padding: 6px 12px 4px;
        gap: 12px;
        background: transparent;
        align-items: center;
        min-height: 0;
        position: relative;
        z-index: 1;
    }

    .id-photo {
        width: 88px;
        height: 108px;
        border: 2px solid #b8d4f0;
        border-radius: 8px;
        object-fit: cover;
        background: #e8f2fc;
        flex-shrink: 0;
    }

    .id-details {
        flex: 1;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }

    .id-name {
        font-weight: 700;
        font-size: 0.82rem;
        color: #1a1a1a;
        margin-bottom: 4px;
        line-height: 1.1;
        text-transform: uppercase;
    }
    .id-name-line {
        height: 2px;
        background: var(--id-primary-color);
        margin-bottom: 8px;
        width: 100%;
    }

    .id-info-row {
        font-size: 0.6rem;
        margin-bottom: 4px;
        color: #222;
        display: flex;
        align-items: center;
        gap: 6px;
        line-height: 1.3;
    }
    .id-info-icon {
        width: 18px;
        height: 18px;
        min-width: 18px;
        background: var(--id-primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.48rem;
    }
    .id-info-row strong {
        color: var(--id-primary-color);
        font-weight: 700;
    }

    .id-footer {
        margin-top: auto;
        flex-shrink: 0;
        width: 100%;
        position: relative;
        z-index: 6;
        background: transparent;
    }
    .id-footer-track {
        width: 100%;
        height: 6px;
        background: #e6e6e6;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.14);
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }
    .id-footer-fill {
        position: absolute;
        left: 0;
        top: 0;
        width: 78%;
        height: 100%;
        background: var(--id-primary-color);
        border-radius: 0 6px 6px 0;
    }

    /* BACK */
    .id-card-back .id-card-content {
        justify-content: flex-start;
        min-height: 0;
    }
    .id-back-top {
        flex: 0 0 auto;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px 4px;
        position: relative;
        z-index: 1;
    }
    .id-back-top img {
        height: 36px;
        width: auto;
        max-width: 75%;
        object-fit: contain;
    }

    .id-back-banner {
        background: var(--id-primary-color);
        color: #fff;
        text-align: center;
        padding: 7px 10px;
        flex: 0 0 auto;
        width: 100%;
    }
    .id-back-banner .banner-line {
        display: block;
        font-size: 0.46rem;
        line-height: 1.3;
        font-weight: 400;
    }
    .id-back-banner .banner-school {
        display: block;
        font-size: 0.58rem;
        font-weight: 700;
        margin: 2px 0;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .id-back-bottom {
        flex: 1 1 auto;
        background: transparent;
        padding: 4px 10px 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
        overflow: visible;
        position: relative;
        z-index: 1;
    }
    .id-back-contact {
        text-align: center;
        font-size: 0.52rem;
        line-height: 1.35;
        color: #222;
        width: 100%;
    }
    .id-back-contact-name {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 3px;
    }
    .id-back-contact-name i {
        color: var(--id-primary-color);
        font-size: 0.72rem;
    }
    .id-back-phone-row {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin-top: 4px;
    }
    .id-back-phone-icon {
        width: 18px;
        height: 18px;
        border: 1.5px solid var(--id-primary-color);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--id-primary-color);
        font-size: 0.5rem;
        flex-shrink: 0;
    }

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }
    .breadcrumb-custom i { color: var(--id-primary-color); }

    .controls-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid rgba(148,0,0,0.1);
    }
    .color-picker-group {
        display: flex;
        align-items: center;
        gap: 20px;
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
    }
    .color-input-wrapper { display: flex; align-items: center; gap: 5px; }
    input[type="color"] {
        border: none;
        width: 30px;
        height: 30px;
        cursor: pointer;
        background: none;
    }
</style>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">

                <div class="breadcrumb-custom">
                    <i class="fa fa-id-card"></i>
                    <span>Student Management</span> /
                    <strong>Identity Cards</strong>
                </div>
                <p class="text-muted small mb-2">
                    Cards list <strong>Active enrollments</strong> for the academic year selected in the header.
                </p>

                <div class="controls-card">
                    <form action="{{ route('admin.student_id_cards', $encryptedClassID) }}" method="GET" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Custom Primary Color</label>
                                <div class="color-picker-group">
                                    <div class="color-input-wrapper">
                                        <input type="color" id="primaryColorPicker" value="#940000">
                                        <span class="small">Primary</span>
                                    </div>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="secondaryColorPicker" value="#ffffff">
                                        <span class="small">Text/BG</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="small font-weight-bold">Filter by Subclass</label>
                                <select name="subclassID" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="">All Subclasses</option>
                                    @foreach($subclasses as $sub)
                                        <option value="{{ $sub->subclassID }}" {{ $selectedSubclassID == $sub->subclassID ? 'selected' : '' }}>
                                            {{ $sub->subclass_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 text-right">
                                <button type="button" id="downloadAllBtn" class="btn btn-sm btn-success">
                                    <i class="fa fa-download"></i> Download All Cards (PDF)
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="flipAllBtn">
                                    <i class="fa fa-refresh"></i> Flip All
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($students->isEmpty())
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No Active enrollments found for the selected class/year. Change the academic year in the header or class filter.
                    </div>
                @else
                    <div class="id-cards-grid">
                        @foreach($students as $student)
                            @php
                                $schoolLogo = idCardAssetUrl($student->id_card_logo_path);
                                $defaultPhoto = strtolower($student->gender ?? 'male') === 'female'
                                    ? asset('images/female.png')
                                    : asset('images/male.png');
                                $photo = $student->photo ? asset('userImages/' . $student->photo) : $defaultPhoto;
                                $schoolName = $student->school->school_name ?? 'School';
                                $schoolHeaderName = idCardSchoolHeaderName($schoolName);
                            @endphp
                            <div class="id-card-container" data-student-id="{{ $student->studentID }}">
                                <button type="button" data-id="{{ $student->id_card_encrypted_id }}" class="download-card-btn" title="Download PDF"><i class="fa fa-download"></i></button>
                                <button class="flip-btn" type="button"><i class="fa fa-rotate-right"></i></button>
                                <div class="id-card-inner">
                                    <!-- Front -->
                                    <div class="id-card-front">
                                        <div class="corner-accent"></div>
                                        <div class="dot-grid">
                                            @for($d = 0; $d < 21; $d++)<span></span>@endfor
                                        </div>
                                        <div class="id-card-content">
                                            @if($schoolLogo)
                                                <img src="{{ $schoolLogo }}" class="id-logo-watermark" alt="">
                                            @endif
                                            <div class="id-header">
                                                @if($schoolLogo)
                                                    <img src="{{ $schoolLogo }}" alt="Logo">
                                                @endif
                                                <div class="id-header-text">
                                                    <h6>{{ $schoolHeaderName }}</h6>
                                                    <small>Student Identity Card</small>
                                                </div>
                                            </div>
                                            <div class="id-body">
                                                <img src="{{ $photo }}" class="id-photo" alt="Student">
                                                <div class="id-details">
                                                    <div class="id-name">{{ $student->id_card_full_name }}</div>
                                                    <div class="id-name-line"></div>
                                                    <div class="id-info-row">
                                                        <span class="id-info-icon"><i class="fa fa-id-card"></i></span>
                                                        <span><strong>Adm No:</strong> {{ $student->id_card_admission }}</span>
                                                    </div>
                                                    <div class="id-info-row">
                                                        <span class="id-info-icon"><i class="fa fa-graduation-cap"></i></span>
                                                        <span><strong>Class:</strong> {{ $student->id_card_main_class }}</span>
                                                    </div>
                                                    <div class="id-info-row">
                                                        <span class="id-info-icon"><i class="fa fa-user"></i></span>
                                                        <span><strong>Sex:</strong> {{ $student->id_card_sex }}</span>
                                                    </div>
                                                    <div class="id-info-row">
                                                        <span class="id-info-icon"><i class="fa fa-calendar"></i></span>
                                                        <span><strong>Expire:</strong> {{ $student->id_card_expire_date }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="id-footer"><div class="id-footer-track"><div class="id-footer-fill"></div></div></div>
                                        </div>
                                    </div>

                                    <!-- Back -->
                                    <div class="id-card-back">
                                        <div class="id-card-content">
                                            @if($schoolLogo)
                                                <img src="{{ $schoolLogo }}" class="id-logo-watermark" alt="">
                                            @endif
                                            <div class="id-back-top">
                                                @if($schoolLogo)
                                                    <img src="{{ $schoolLogo }}" alt="Logo">
                                                @endif
                                            </div>
                                            <div class="id-back-banner">
                                                <span class="banner-line">This identity card is the property of</span>
                                                <span class="banner-school">{{ strtoupper($schoolName) }}</span>
                                                <span class="banner-line">and is not transferable. This card must be returned to the Headmaster</span>
                                                <span class="banner-line">upon termination of studies. If found please return to address below:</span>
                                            </div>
                                            <div class="id-back-bottom">
                                                <div class="id-back-contact">
                                                    <div class="id-back-contact-name">
                                                        <i class="fa fa-map-marker"></i>
                                                        <span>{{ strtoupper($schoolName) }}</span>
                                                    </div>
                                                    @if(!empty($student->school->address))
                                                        {{ $student->school->address }}<br>
                                                    @endif
                                                    @if(!empty($student->school->district) || !empty($student->school->region))
                                                        {{ trim(($student->school->district ?? '') . ($student->school->region ? ', ' . $student->school->region : '')) }}<br>
                                                    @endif
                                                    @if(!empty($student->school->phone))
                                                        <div class="id-back-phone-row">
                                                            <span class="id-back-phone-icon"><i class="fa fa-phone"></i></span>
                                                            <span>Tel: {{ $student->school->phone }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="id-footer"><div class="id-footer-track"><div class="id-footer-fill"></div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <i class="fa fa-file-pdf-o fa-3x text-danger mb-3"></i>
                <h6 class="mb-3">Generating ID Card PDF...</h6>
                <div class="progress" style="height: 8px;">
                    <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 0%;"></div>
                </div>
                <div id="pdfProgressPercent" class="font-weight-bold text-muted mt-2" style="font-size: 14px;">0%</div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const downloadBase = @json(url('admin/student-id-cards/download'));
        const encryptedClassID = @json($encryptedClassID);
        const subclassID = "{{ $selectedSubclassID }}";

        function buildDownloadUrl(id) {
            const primary = $('#primaryColorPicker').val();
            const secondary = $('#secondaryColorPicker').val();
            let url = downloadBase + '/' + encodeURIComponent(id);
            url += `?primaryColor=${encodeURIComponent(primary)}&secondaryColor=${encodeURIComponent(secondary)}`;
            if (id === 'all') {
                url += `&classID=${encodeURIComponent(encryptedClassID)}&subclassID=${encodeURIComponent(subclassID)}`;
            }
            return url;
        }

        function downloadPdf(url, filename) {
            $('#downloadProgressModal').modal('show');
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(function() {
                if (progress < 95) {
                    let inc = 2 + Math.random() * 5;
                    if (progress > 50) inc = 1 + Math.random() * 2;
                    if (progress > 80) inc = 0.5 + Math.random();
                    progress += inc;
                    if (progress > 95) progress = 95;
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 400);

            fetch(url)
                .then(async response => {
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(text || ('Server error: ' + response.statusText));
                    }
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#pdfProgressBar').css('width', '100%');
                    $('#pdfProgressPercent').text('100%');

                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);

                        setTimeout(() => {
                            $('#downloadProgressModal').modal('hide');
                            setTimeout(() => {
                                $('#pdfProgressBar').css('width', '0%');
                                $('#pdfProgressPercent').text('0%');
                            }, 500);
                        }, 800);
                    }, 400);
                })
                .catch(error => {
                    clearInterval(interval);
                    $('#downloadProgressModal').modal('hide');
                    alert('Failed to generate PDF: ' + error.message);
                    console.error('PDF Export Error:', error);
                });
        }

        $('#downloadAllBtn').on('click', function() {
            const timestamp = new Date().toISOString().slice(0, 10);
            downloadPdf(buildDownloadUrl('all'), `All_Student_ID_Cards_${timestamp}.pdf`);
        });

        $('.download-card-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            if (!id) return;
            downloadPdf(buildDownloadUrl(id), 'Student_ID_Card.pdf');
        });

        $('.flip-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.id-card-container').toggleClass('flipped');
        });

        $('#flipAllBtn').on('click', function() {
            $('.id-card-container').toggleClass('flipped');
            if ($('.id-card-container').first().hasClass('flipped')) {
                $(this).html('<i class="fa fa-eye"></i> Show Front');
            } else {
                $(this).html('<i class="fa fa-refresh"></i> Flip All');
            }
        });

        $('#primaryColorPicker').on('input', function() {
            document.documentElement.style.setProperty('--id-primary-color', $(this).val());
        });

        $('#secondaryColorPicker').on('input', function() {
            document.documentElement.style.setProperty('--id-secondary-color', $(this).val());
        });
    });
</script>
