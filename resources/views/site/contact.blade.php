@extends('layouts.site')

@section('content')
<section class="banner-area relative about-banner">
    <div class="overlay overlay-bg"></div>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="about-content col-lg-12">
                <h1 class="text-white">Contact</h1>
                <p class="text-white link-nav">
                    <a href="{{ route('site.home') }}">Home</a>
                    <span class="lnr lnr-arrow-right"></span>
                    <a href="{{ route('site.contact') }}">Contact</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="contact-page-area section-gap">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" style="border-radius:0;border-left:4px solid #940000;">{{ session('success') }}</div>
        @endif
        <div class="row">
            <div class="col-lg-4 d-flex flex-column address-wrap">
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-map-marker"></i></div>
                    <div class="contact-details">
                        <h5>Moshi, Kilimanjaro</h5>
                        <p>Tanzania — EmCa Techonology office</p>
                    </div>
                </div>
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-phone"></i></div>
                    <div class="contact-details">
                        <h5><a href="tel:{{ config('shulexpert.phone_e164') }}" style="color:#222;">{{ config('shulexpert.phone_display') }}</a></h5>
                        <p>Mon–Fri 09:00–19:00 · Sat 09:00–12:00</p>
                    </div>
                </div>
                <div class="sx-contact-box single-contact-address d-flex flex-row">
                    <div class="icon"><i class="fa fa-envelope"></i></div>
                    <div class="contact-details">
                        <h5><a href="mailto:emca@emca.tech" style="color:#222;">emca@emca.tech</a></h5>
                        <p>Sales, onboarding &amp; support</p>
                    </div>
                </div>
                <p class="sx-powered mt-20">Powered by EmCa Techonology</p>
            </div>
            <div class="col-lg-8">
                <form class="form-area" action="{{ route('site.contact.submit') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <input name="name" placeholder="Enter your name" value="{{ old('name') }}"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter your name'"
                                   class="common-input mb-20 form-control" required type="text">
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror

                            <input name="email" placeholder="Enter email address" value="{{ old('email') }}"
                                   pattern="[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter email address'"
                                   class="common-input mb-20 form-control" required type="email">
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror

                            <input name="phone" placeholder="Enter phone number" value="{{ old('phone') }}"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter phone number'"
                                   class="common-input mb-20 form-control" required type="tel">
                            @error('phone')<small class="text-danger">{{ $message }}</small>@enderror

                            <input name="subject" placeholder="Enter subject" value="{{ old('subject') }}"
                                   onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter subject'"
                                   class="common-input mb-20 form-control" required type="text">
                            @error('subject')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-lg-6 form-group">
                            <textarea class="common-textarea form-control" name="message" placeholder="Enter message"
                                      onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter message'"
                                      required>{{ old('message') }}</textarea>
                            @error('message')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-lg-12">
                            <button class="primary-btn text-uppercase" type="submit" style="float:right;">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('schema')
@php
    $contact = [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contact ShuleXpert',
        'url' => url('/contact'),
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'EmCa Techonology',
            'email' => 'emca@emca.tech',
            'telephone' => config('shulexpert.phone_e164'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Moshi',
                'addressRegion' => 'Kilimanjaro',
                'addressCountry' => 'TZ',
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($contact, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
