@extends('layouts.site')

@php
    $sp = asset('studypresshtml-62/studypresshtml-62/studypress-client-files-v6.2/studypress-bootstrap5-new-version');
    $homeFeaturedSlugs = [
        'lesson-plans',
        'fees-payments',
        'attendance-biometric',
        'exams-results',
    ];
    $homeModShorts = __('site.home_mod_shorts');
    if (!is_array($homeModShorts)) {
        $homeModShorts = [];
    }
    $homeModules = collect($modules)
        ->keyBy('slug')
        ->only($homeFeaturedSlugs)
        ->filter()
        ->map(function ($module) use ($homeModShorts) {
            $slug = $module['slug'] ?? '';
            if (!empty($homeModShorts[$slug])) {
                $module['short'] = $homeModShorts[$slug];
            }
            $module['card_image'] = asset('images/modules/'.$slug.'.jpg');
            return $module;
        })
        ->values()
        ->all();
    $pillars = [
        ['icon' => 'fa fa-money', 'title' => __('site.pillar_fees'), 'text' => __('site.fees_text'), 'link' => route('site.module', 'fees-payments')],
        ['icon' => 'fa fa-hand-paper-o', 'title' => __('site.pillar_biometric'), 'text' => __('site.biometric_text'), 'link' => route('site.module', 'attendance-biometric')],
        ['icon' => 'fa fa-users', 'title' => __('site.pillar_parent'), 'text' => __('site.parent_text'), 'link' => route('site.module', 'parent-portal')],
        ['icon' => 'fa fa-graduation-cap', 'title' => __('site.pillar_exams'), 'text' => __('site.pillar_exams_text'), 'link' => route('site.module', 'exams-results')],
        ['icon' => 'fa fa-calendar', 'title' => __('site.pillar_modules'), 'text' => __('site.pillar_modules_text'), 'link' => route('site.modules')],
        ['icon' => 'fa fa-mobile', 'title' => __('site.pillar_app'), 'text' => __('site.pillar_app_text'), 'link' => route('site.download')],
    ];
    $reviews = [
        [
            'name' => 'Naamani Basso',
            'role' => 'Headmaster, Bi. Alpha Memorial School',
            'quote' => 'ShuleXpert has greatly improved the way we manage our school. From admissions to attendance and examinations, everything is now more organized and efficient. It is an excellent system that we highly recommend.',
            'img' => $sp.'/images/testimonials/3.jpg',
        ],
        [
            'name' => 'Werney Kapinga',
            'role' => 'Head Teacher, Malecela Secondary School',
            'quote' => 'ShuleXpert has made academic records simple and efficient. It is easy to use, secure, and improves communication between teachers, parents, and management. Highly recommended!',
            'img' => $sp.'/images/testimonials/1.jpg',
        ],
        [
            'name' => 'Asha Mwakasege',
            'role' => 'School Administrator, Arusha Primary School',
            'quote' => 'Fee control numbers and parent SMS reminders transformed our collections. Attendance and results are clear for parents, and our office work is finally organized in one place.',
            'img' => $sp.'/images/testimonials/2.jpg',
        ],
        [
            'name' => 'Joseph Kimaro',
            'role' => 'Academic Master, Moshi High School',
            'quote' => 'Entering results and publishing to parents is finally simple. Lesson plans, exams and reports save our teachers hours every week. ShuleXpert fits how real schools work.',
            'img' => $sp.'/images/testimonials/1.jpg',
        ],
    ];
@endphp

@section('content')
    {{-- Section: home hero (school images + readable text) --}}
    <section id="home" class="sx-hero-slider">
      <div id="sxHeroOwl" class="owl-carousel owl-theme sx-hero-owl">
        <div class="sx-hero-slide">
          <div class="sx-hero-slide__bg" style="background-image:url('{{ asset('images/hero/sx-hero-classroom.jpg') }}')"></div>
          <div class="sx-hero-slide__overlay"></div>
          <div class="container sx-hero-slide__inner">
            <div class="sx-hero-copy sx-hero-copy--center">
              <p class="sx-hero-kicker">ShuleXpert</p>
              <h1 class="sx-hero-title">{{ __('site.hero_title_1') }}</h1>
              <p class="sx-hero-lead">{{ __('site.hero_lead_short') }}</p>
              <div class="sx-hero-actions sx-hero-actions--center">
                <a href="{{ route('site.modules') }}" class="btn btn-theme-colored1 btn-circled btn-lg text-white">{{ __('site.explore_modules') }}</a>
                <a href="{{ route('login') }}" class="btn btn-theme-colored2 btn-circled btn-lg text-white">{{ __('site.school_login') }}</a>
              </div>
            </div>
          </div>
        </div>
        <div class="sx-hero-slide">
          <div class="sx-hero-slide__bg" style="background-image:url('{{ asset('images/hero/sx-hero-campus.jpg') }}')"></div>
          <div class="sx-hero-slide__overlay"></div>
          <div class="container sx-hero-slide__inner">
            <div class="sx-hero-copy sx-hero-copy--center">
              <p class="sx-hero-kicker">{{ __('site.why_choose') }}</p>
              <h1 class="sx-hero-title">{{ __('site.built_worldwide') }}</h1>
              <p class="sx-hero-lead">{{ __('site.hero_slide2_lead') }}</p>
              <div class="sx-hero-actions sx-hero-actions--center">
                <a href="{{ route('site.about') }}" class="btn btn-theme-colored1 btn-circled btn-lg text-white">{{ __('site.about_shulexpert') }}</a>
                <a href="{{ route('login') }}" class="btn btn-theme-colored2 btn-circled btn-lg text-white">{{ __('site.school_login') }}</a>
              </div>
            </div>
          </div>
        </div>
        <div class="sx-hero-slide">
          <div class="sx-hero-slide__bg" style="background-image:url('{{ asset('images/hero/sx-hero-digital.jpg') }}')"></div>
          <div class="sx-hero-slide__overlay"></div>
          <div class="container sx-hero-slide__inner">
            <div class="sx-hero-copy sx-hero-copy--center">
              <p class="sx-hero-kicker">EmCa Techonology</p>
              <h1 class="sx-hero-title">{{ __('site.ready_digitize') }}</h1>
              <p class="sx-hero-lead">{{ __('site.hero_slide3_lead') }}</p>
              <div class="sx-hero-actions sx-hero-actions--center">
                <a href="{{ route('site.contact') }}" class="btn btn-theme-colored1 btn-circled btn-lg text-white">{{ __('site.contact_us') }}</a>
                <a href="{{ route('online_application') }}" class="btn btn-theme-colored2 btn-circled btn-lg text-white">{{ __('site.online_application') }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Section: About (layout2) --}}
    <section class="sx-about-section">
      <div class="container">
        <div class="section-content">
          <div class="row align-items-center">
            <div class="col-sm-12 col-md-12 col-lg-8 col-xl-8">
              <h3 class="text-uppercase font-weight-800 mt-0 mb-0 line-height-1 sx-about-heading">{{ __('site.about_shulexpert') }}</h3>
              <h6 class="text-uppercase line-bottom line-bottom-theme-colored1 title font-playfair text-uppercase">{{ __('site.built_worldwide') }}</h6>
              <p class="sx-about-lead">{{ __('site.built_worldwide_p1') }}</p>
              <div class="row sx-about-pillars">
                @foreach($pillars as $pillar)
                <div class="col-sm-6">
                  <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left mb-30 sx-about-pillar">
                    <div class="icon-box-wrapper">
                      <a href="{{ $pillar['link'] }}" class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10 sx-about-icon">
                        <i class="{{ $pillar['icon'] }}"></i>
                      </a>
                      <div class="icon-text">
                        <h5 class="icon-box-title text-uppercase mb-10 sx-about-pillar-title"><a href="{{ $pillar['link'] }}">{{ $pillar['title'] }}</a></h5>
                        <p class="mb-0 sx-about-pillar-text">{{ \Illuminate\Support\Str::limit($pillar['text'], 90) }}</p>
                      </div>
                      <div class="clearfix"></div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
              <form id="home_appointment_form" name="home_appointment_form" class="booking-form form-home sx-contact-card p-30" method="get" action="{{ route('site.contact') }}">
                <h3 class="text-white mt-0 mb-20 sx-contact-card__title">{{ __('site.contact_us') }}</h3>

                <div class="sx-contact-field mb-10">
                  <span class="sx-contact-field__icon" aria-hidden="true"><i class="fa fa-user"></i></span>
                  <input name="name" class="form-control" type="text" placeholder="{{ __('site.ask_float_name_ph') }}" autocomplete="name">
                </div>

                <div class="sx-contact-field mb-10">
                  <span class="sx-contact-field__icon" aria-hidden="true"><i class="fa fa-envelope"></i></span>
                  <input name="email" class="form-control" type="email" placeholder="{{ __('site.ask_float_email_ph') }}" autocomplete="email">
                </div>

                <div class="sx-contact-field mb-10">
                  <span class="sx-contact-field__icon" aria-hidden="true"><i class="fa fa-phone"></i></span>
                  <input name="phone" class="form-control" type="tel" placeholder="{{ config('shulexpert.phone_display') }}" autocomplete="tel">
                </div>

                <div class="sx-contact-field mb-10">
                  <span class="sx-contact-field__icon" aria-hidden="true"><i class="fa fa-th-large"></i></span>
                  <select name="subject" class="form-control">
                    <option value="">{{ __('site.modules') }}</option>
                    <option value="fees">{{ __('site.fees_title') }}</option>
                    <option value="biometric">{{ __('site.biometric_title') }}</option>
                    <option value="parent">{{ __('site.parent_title') }}</option>
                    <option value="register">{{ __('site.register_school') }}</option>
                  </select>
                </div>

                <div class="sx-contact-field sx-contact-field--textarea mb-10">
                  <span class="sx-contact-field__icon" aria-hidden="true"><i class="fa fa-comment"></i></span>
                  <textarea name="message" class="form-control" placeholder="{{ __('site.ask_float_message_ph') }}" rows="3"></textarea>
                </div>

                <div class="mb-0 mt-20">
                  <button type="submit" class="btn btn-theme-colored1 btn-block">
                    <i class="fa fa-paper-plane me-1" aria-hidden="true"></i>{{ __('site.contact_us') }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Section: Courses / Modules (layout2) --}}
    <section class="bg-silver-light sx-modules-section">
      <div class="container pb-40">
        <div class="section-title sx-modules-section__head text-center mb-30">
          <h2 class="text-uppercase line-bottom line-bottom-theme-colored1 sx-modules-section__title">{{ __('site.modules') }}</h2>
          <p class="sx-modules-lead mb-0">{{ __('site.modules_lead_short') }}</p>
        </div>
        <div class="section-content">
          <div class="row">
            @foreach($homeModules as $module)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <article class="course-item mb-30 bg-white sx-module-card">
                <a href="{{ route('site.module', $module['slug']) }}" class="sx-module-card__media">
                  <img alt="{{ $module['title'] }}" src="{{ $module['card_image'] }}" class="sx-module-card__img" loading="lazy">
                  <span class="sx-module-card__shine" aria-hidden="true"></span>
                  <span class="sx-module-card__badge"><i class="fa {{ $module['icon'] }}"></i></span>
                </a>
                <div class="content text-left flip sx-module-card__body">
                  <h4 class="line-bottom line-bottom-theme-colored1 sx-module-card__title">{{ $module['title'] }}</h4>
                  <p class="sx-module-card__desc">{{ $module['short'] }}</p>
                  <a class="btn btn-theme-colored1 btn-xs text-uppercase" href="{{ route('site.module', $module['slug']) }}">{{ __('site.view_module') }}</a>
                </div>
              </article>
            </div>
            @endforeach
          </div>
          <div class="row">
            <div class="col-12 text-center mt-10 mb-20">
              <a href="{{ route('site.modules') }}" class="btn btn-theme-colored1 btn-circled">{{ __('site.view_all_modules') }}</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Section: Why Choose Us --}}
    <section class="sx-why-section">
      <div class="container">
        <div class="row align-items-center gx-4 gy-4">
          <div class="col-md-12 col-lg-5">
            <div class="sx-why-media">
              <img src="{{ asset('images/why-choose-school.jpg') }}" alt="ShuleXpert schools" class="sx-why-media__img">
            </div>
          </div>
          <div class="col-md-12 col-lg-7">
            <h3 class="sx-why-title line-bottom line-bottom-theme-colored1">{{ __('site.why_choose') }} <span>ShuleXpert</span></h3>
            <p class="sx-why-lead">{{ __('site.why_choose_lead') }}</p>
            <div class="row sx-why-links">
              <div class="col-sm-4">
                <div class="sx-why-link">
                  <span class="sx-why-link__icon"><i class="fa fa-shield"></i></span>
                  <span class="sx-why-link__label">{{ __('site.why_secure') }}</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="sx-why-link">
                  <span class="sx-why-link__icon"><i class="fa fa-headphones"></i></span>
                  <span class="sx-why-link__label">{{ __('site.why_support') }}</span>
                </div>
              </div>
              <div class="col-sm-4">
                <div class="sx-why-link">
                  <span class="sx-why-link__icon"><i class="fa fa-money"></i></span>
                  <span class="sx-why-link__label">{{ __('site.why_cost') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Section: Funfacts (layout2) --}}
    <section class="layer-overlay overlay-theme-colored2-9 bg-no-repeat bg-pos-center-center sx-funfacts" data-tm-bg-img="{{ $sp }}/images/bg/bg2.jpg">
      <div class="container pt-80 pb-80">
        <div class="section-content">
          <div class="row">
            <div class="col-md-6 col-lg-3 col-xl-3">
              <div class="funfact-item text-center mb-md-40">
                <i class="funfact-icon fa fa-building text-theme-colored1"></i>
                <h2 class="text-white mt-0 mb-10 sx-funfact-num">
                  <span data-animation-duration="2000" data-value="120" class="counter animate-number">0</span>+
                </h2>
                <p class="text-white title mb-0">{{ __('site.trusted_leaders') }}</p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 col-xl-3">
              <div class="funfact-item text-center mb-md-40">
                <i class="funfact-icon fa fa-graduation-cap text-theme-colored1"></i>
                <h2 class="text-white mt-0 mb-10 sx-funfact-num">
                  <span data-animation-duration="2000" data-value="15000" class="counter animate-number">0</span>+
                </h2>
                <p class="text-white title mb-0">{{ __('site.stat_students') }}</p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 col-xl-3">
              <div class="funfact-item text-center mb-sm-40">
                <i class="funfact-icon fa fa-th-large text-theme-colored1"></i>
                <h2 class="text-white mt-0 mb-10 sx-funfact-num">
                  <span data-animation-duration="2000" data-value="33" class="counter animate-number">0</span>+
                </h2>
                <p class="text-white title mb-0">{{ __('site.stat_modules') }}</p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 col-xl-3">
              <div class="funfact-item text-center">
                <i class="funfact-icon fa fa-users text-theme-colored1"></i>
                <h2 class="text-white mt-0 mb-10 sx-funfact-num">
                  <span data-animation-duration="2000" data-value="300" class="counter animate-number">0</span>+
                </h2>
                <p class="text-white title mb-0">{{ __('site.stat_teachers') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- Section: Pricing (home) --}}
    <section class="sx-home-pricing" id="pricing">
      <div class="container">
        <div class="section-title text-center mb-40">
          <h2 class="text-uppercase line-bottom line-bottom-theme-colored1 d-inline-block sx-home-pricing__title">{{ __('pricing.title') }}</h2>
          <p class="sx-home-pricing__lead mx-auto mb-0">{{ __('pricing.lead') }}</p>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-6 col-lg-5 mb-30">
            <article class="sx-home-price-card sx-home-price-card--featured">
              <span class="sx-home-price-card__tag">{{ __('pricing.one_time') }}</span>
              <h3>{{ __('pricing.fee_title') }}</h3>
              <p>{{ __('pricing.fee_desc') }}</p>
              <div class="sx-home-price-card__amount">TZS 1,000,000</div>
              <small>{{ __('pricing.one_time_setup') }}</small>
              <ul class="sx-home-price-card__list">
                <li>{{ __('pricing.inst1_label') }}</li>
                <li>{{ __('pricing.inst3_label') }}</li>
                <li>{{ __('pricing.inst4_label') }}</li>
                <li>{{ __('pricing.inst5_label') }}</li>
              </ul>
            </article>
          </div>
          <div class="col-md-6 col-lg-5 mb-30">
            <article class="sx-home-price-card">
              <span class="sx-home-price-card__tag sx-home-price-card__tag--soft">{{ __('pricing.monthly') }}</span>
              <h3>{{ __('pricing.sub_title') }}</h3>
              <p>{{ __('pricing.sub_desc') }}</p>
              <div class="sx-home-price-card__amount">TZS 500</div>
              <small>{{ __('pricing.per_student_month') }}</small>
              <ul class="sx-home-price-card__list">
                <li>{{ __('pricing.mon1_label') }}</li>
                <li>{{ __('pricing.mon2_label') }}</li>
                <li>{{ __('pricing.mon3_label') }}</li>
                <li>{{ __('pricing.mon4_label') }}</li>
              </ul>
            </article>
          </div>
        </div>
        <div class="sx-price-calc" id="sxPriceCalc" data-rate="500">
          <div class="sx-price-calc__head text-center">
            <p class="sx-price-calc__kicker mb-1">{{ __('pricing.calc_kicker') }}</p>
            <h3 class="sx-price-calc__title">{{ __('pricing.calc_title') }}</h3>
            <p class="sx-price-calc__hint mb-0">{{ __('pricing.calc_hint') }}</p>
          </div>
          <div class="row g-3 align-items-end sx-price-calc__row">
            <div class="col-md-4">
              <label class="sx-price-calc__label" for="sxPriceStudents">{{ __('pricing.calc_students') }}</label>
              <div class="sx-price-calc__input-wrap">
                <i class="fa fa-graduation-cap" aria-hidden="true"></i>
                <input id="sxPriceStudents" type="number" min="1" step="1" value="200" inputmode="numeric" class="form-control sx-price-calc__input">
              </div>
            </div>
            <div class="col-md-4">
              <span class="sx-price-calc__label">{{ __('pricing.calc_period') }}</span>
              <div class="sx-price-calc__period" role="group" aria-label="{{ __('pricing.calc_period') }}">
                <button type="button" class="sx-price-calc__period-btn is-active" data-period="month">{{ __('pricing.monthly') }}</button>
                <button type="button" class="sx-price-calc__period-btn" data-period="year">{{ __('pricing.yearly') }}</button>
              </div>
            </div>
            <div class="col-md-4">
              <span class="sx-price-calc__label">{{ __('pricing.calc_total') }}</span>
              <div class="sx-price-calc__total" aria-live="polite">
                <strong id="sxPriceTotal">TZS 100,000</strong>
                <small id="sxPriceTotalPeriod">{{ __('pricing.per_month') }}</small>
              </div>
            </div>
          </div>
          <p class="sx-price-calc__note mb-0 text-center">{{ __('pricing.calc_note') }}</p>
        </div>
        <div class="text-center mt-25 mb-10">
          <a href="{{ route('site.contact') }}" class="btn btn-theme-colored1 btn-circled">{{ __('site.request_quotation') }}</a>
        </div>
      </div>
    </section>

    {{-- Section: Trusted by school leaders --}}
    <section class="sx-testimonials">
      <div class="container">
        <div class="section-title text-center mb-40 sx-section-head">
          <h2 class="text-uppercase line-bottom line-bottom-theme-colored1 d-inline-block sx-section-head__title">{{ __('site.testimonials_eyebrow') }}</h2>
          <p class="sx-section-head__lead mx-auto mb-0">{{ __('site.trusted_leaders_desc') }}</p>
        </div>
        <div id="sxTestimonialsOwl" class="owl-carousel owl-theme sx-testimonials-owl">
          @foreach($reviews as $review)
          <div class="sx-testimonial-card">
            <div class="sx-testimonial-card__top">
              <div class="sx-testimonial-card__avatar" aria-hidden="true">
                <i class="fa fa-user"></i>
              </div>
              <span class="sx-testimonial-card__quote-mark"><i class="fa fa-quote-right"></i></span>
            </div>
            <h4 class="sx-testimonial-card__name">{{ $review['name'] }}</h4>
            <p class="sx-testimonial-card__role">{{ $review['role'] }}</p>
            <span class="sx-testimonial-card__label">{{ __('site.client_label') }}</span>
            <div class="sx-testimonial-card__divider"><i class="fa fa-quote-left"></i></div>
            <p class="sx-testimonial-card__text">"{{ $review['quote'] }}"</p>
          </div>
          @endforeach
        </div>
      </div>
    </section>

    {{-- Section: Working process (4 steps) --}}
    <section class="sx-process" aria-labelledby="sx-process-heading">
      <div class="container">
        <div class="section-title text-center mb-40 sx-section-head">
          <h2 id="sx-process-heading" class="text-uppercase line-bottom line-bottom-theme-colored1 d-inline-block sx-section-head__title">{{ __('site.process_eyebrow') }}</h2>
          <p class="sx-section-head__lead mx-auto mb-0">{{ __('site.process_lead') }}</p>
        </div>
        <div class="sx-process__track">
          <div class="sx-process__step">
            <div class="sx-process__icon-wrap">
              <span class="sx-process__num">1</span>
              <span class="sx-process__icon"><i class="fa fa-laptop"></i></span>
            </div>
            <h4 class="sx-process__name">{{ __('site.process_1_title') }}</h4>
            <p class="sx-process__desc">{{ __('site.process_1_text') }}</p>
          </div>

          <div class="sx-process__step">
            <div class="sx-process__icon-wrap">
              <span class="sx-process__num">2</span>
              <span class="sx-process__icon"><i class="fa fa-calendar"></i></span>
            </div>
            <h4 class="sx-process__name">{{ __('site.process_2_title') }}</h4>
            <p class="sx-process__desc">{{ __('site.process_2_text') }}</p>
          </div>

          <div class="sx-process__step">
            <div class="sx-process__icon-wrap">
              <span class="sx-process__num">3</span>
              <span class="sx-process__icon"><i class="fa fa-file-text-o"></i></span>
            </div>
            <h4 class="sx-process__name">{{ __('site.process_3_title') }}</h4>
            <p class="sx-process__desc">{{ __('site.process_3_text') }}</p>
          </div>

          <div class="sx-process__step">
            <div class="sx-process__icon-wrap">
              <span class="sx-process__num">4</span>
              <span class="sx-process__icon"><i class="fa fa-rocket"></i></span>
            </div>
            <h4 class="sx-process__name">{{ __('site.process_4_title') }}</h4>
            <p class="sx-process__desc">{{ __('site.process_4_text') }}</p>
          </div>
        </div>
      </div>
    </section>

    {{-- Clients / CTA divider (layout2) --}}
    <section class="bg-theme-colored1">
      <div class="container pt-40 pb-40">
        <div class="row align-items-center">
          <div class="col-lg-8 mb-20 mb-lg-0">
            <h2 class="text-white text-uppercase mt-0 mb-0">{{ __('site.start_today') }}</h2>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-theme-colored2 btn-circled btn-lg text-white" href="{{ route('online_application') }}">{{ __('site.online_application') }}</a>
          </div>
        </div>
      </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
  var tries = 0;
  function initSxHero() {
    var $ = window.jQuery;
    if (!$ || !$.fn || !$.fn.owlCarousel) return false;
    var $hero = $('#sxHeroOwl');
    if (!$hero.length) return true;
    if ($hero.hasClass('owl-loaded') || $hero.data('sxHeroReady')) return true;

    $hero.owlCarousel({
      items: 1,
      loop: true,
      margin: 0,
      nav: true,
      dots: true,
      autoplay: true,
      autoplayTimeout: 5500,
      autoplayHoverPause: true,
      autoplaySpeed: 900,
      smartSpeed: 900,
      slideTransition: 'ease-in-out',
      mouseDrag: true,
      touchDrag: true,
      pullDrag: true,
      navText: [
        '<i class="fa fa-chevron-left"></i>',
        '<i class="fa fa-chevron-right"></i>'
      ]
    });
    $hero.data('sxHeroReady', true);
    return true;
  }

  function initSxTestimonials() {
    var $ = window.jQuery;
    if (!$ || !$.fn || !$.fn.owlCarousel) return false;
    var $owl = $('#sxTestimonialsOwl');
    if (!$owl.length) return true;
    if ($owl.hasClass('owl-loaded') || $owl.data('sxTestimonialsReady')) return true;

    $owl.owlCarousel({
      loop: true,
      margin: 24,
      nav: false,
      dots: true,
      autoplay: true,
      autoplayTimeout: 5000,
      autoplayHoverPause: true,
      smartSpeed: 700,
      responsive: {
        0: { items: 1 },
        768: { items: 2 },
        992: { items: 3 }
      }
    });
    $owl.data('sxTestimonialsReady', true);
    return true;
  }

  function boot() {
    var heroOk = initSxHero();
    var reviewsOk = initSxTestimonials();
    if (heroOk && reviewsOk) return;
    tries += 1;
    if (tries < 25) setTimeout(boot, 200);
  }

  function initSxPriceCalc() {
    var root = document.getElementById('sxPriceCalc');
    if (!root || root.getAttribute('data-ready') === '1') return;
    root.setAttribute('data-ready', '1');

    var rate = parseInt(root.getAttribute('data-rate') || '500', 10) || 500;
    var input = document.getElementById('sxPriceStudents');
    var totalEl = document.getElementById('sxPriceTotal');
    var periodEl = document.getElementById('sxPriceTotalPeriod');
    var period = 'month';
    var perMonthLabel = @json(__('pricing.per_month'));
    var perYearLabel = @json(__('pricing.per_year'));

    function formatTzs(n) {
      try {
        return 'TZS ' + Number(n).toLocaleString('en-US');
      } catch (e) {
        return 'TZS ' + String(n);
      }
    }

    function recalc() {
      var students = parseInt((input && input.value) || '0', 10);
      if (!isFinite(students) || students < 1) students = 0;
      var monthly = students * rate;
      var amount = period === 'year' ? monthly * 12 : monthly;
      if (totalEl) totalEl.textContent = formatTzs(amount);
      if (periodEl) periodEl.textContent = period === 'year' ? perYearLabel : perMonthLabel;
    }

    if (input) {
      input.addEventListener('input', recalc);
      input.addEventListener('change', recalc);
    }
    root.querySelectorAll('.sx-price-calc__period-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        root.querySelectorAll('.sx-price-calc__period-btn').forEach(function (b) {
          b.classList.remove('is-active');
        });
        btn.classList.add('is-active');
        period = btn.getAttribute('data-period') === 'year' ? 'year' : 'month';
        recalc();
      });
    });
    recalc();
  }

  if (window.jQuery) {
    window.jQuery(boot);
  } else if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSxPriceCalc);
  } else {
    initSxPriceCalc();
  }
})();
</script>
@endpush

@push('schema')
@php
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Je, ShuleXpert ni mfumo bora wa shule Tanzania?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Ndiyo. ShuleXpert ni mfumo bora wa shule Tanzania na school management system kamili: ada, mahudhurio ya biometric, mitihani, lesson plans, scheme of work, parent portal na SMS. Imejengwa na EmCa Techonology kwa shule duniani.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the best school management system / school software for Tanzania?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'ShuleXpert is a better school management system and school software for Tanzania and worldwide: fees with control numbers, biometric attendance, exams and results, parent portal, timetable, and online admission, one school system for admin, teachers, staff and parents.',
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
