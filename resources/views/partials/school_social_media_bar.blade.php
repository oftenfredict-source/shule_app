{{-- Shared school social media icons (Teacher / Staff / reusable) --}}
@php
    $ssmBarSchoolID = $ssmBarSchoolID ?? Session::get('schoolID');
    $ssmBarSchool = $ssmBarSchoolID ? \App\Models\School::find($ssmBarSchoolID) : null;
    $ssmBarLinks = $ssmBarSchool ? $ssmBarSchool->socialMediaLinks() : [];
@endphp

@if(!empty($ssmBarLinks))
    @once
        <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
        <style>
            .school-sm-bar {
                width: 100%;
                background: #fff;
                border-bottom: 1px solid #f0d0d0;
                padding: 8px 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-wrap: wrap;
                gap: 10px;
                box-sizing: border-box;
            }
            .school-sm-bar .school-sm-icon {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff !important;
                border: none;
                text-decoration: none;
                font-size: 18px;
                transition: transform 0.15s ease, opacity 0.15s ease;
                flex-shrink: 0;
            }
            .school-sm-bar .school-sm-icon i {
                color: #fff !important;
            }
            .school-sm-bar .school-sm-icon:hover {
                opacity: 0.9;
                transform: translateY(-1px);
                color: #fff !important;
            }
            @media (max-width: 767.98px) {
                .school-sm-bar {
                    padding: 6px 10px;
                    gap: 8px;
                }
                .school-sm-bar .school-sm-icon {
                    width: 30px;
                    height: 30px;
                    font-size: 16px;
                }
            }
        </style>
    @endonce

    <div class="school-sm-bar" title="School social media">
        @foreach($ssmBarLinks as $sm)
            <a href="{{ $sm['href'] }}"
               target="_blank"
               rel="noopener noreferrer"
               class="school-sm-icon"
               title="{{ $sm['label'] }}"
               style="background: {{ $sm['color'] ?? '#940000' }};">
                <i class="bx {{ $sm['icon'] }}"></i>
            </a>
        @endforeach
    </div>
@endif
