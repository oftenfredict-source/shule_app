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
                        @if($student->school && $student->school->formattedPhones())
                            <div class="id-back-phone-row">
                                <span class="id-back-phone-icon"><i class="fa fa-phone"></i></span>
                                <span>Tel: {{ $student->school->formattedPhones() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="id-footer"><div class="id-footer-track"><div class="id-footer-fill"></div></div></div>
            </div>
        </div>
    </div>
</div>
