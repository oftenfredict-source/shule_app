<footer class="footer-area section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>ShuleXpert</h4>
                    <p style="color:#aaa;font-size:13px;line-height:1.7;">
                        {{ __('site.footer_tagline') }}
                    </p>
                    <p class="sx-powered mt-3">{{ __('site.powered_by') }}</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>{{ __('site.quick_links') }}</h4>
                    <ul>
                        <li><a href="{{ route('site.home') }}">{{ __('site.home') }}</a></li>
                        <li><a href="{{ route('site.about') }}">{{ __('site.about') }}</a></li>
                        <li><a href="{{ route('site.team') }}">{{ __('site.team') }}</a></li>
                        <li><a href="{{ route('site.services') }}">{{ __('site.services') }}</a></li>
                        <li><a href="{{ route('site.modules') }}">{{ __('site.modules') }}</a></li>
                        <li><a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a></li>
                        <li><a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>{{ __('site.modules') }}</h4>
                    <ul>
                        <li><a href="{{ route('site.module', 'fees-payments') }}">{{ __('site.fees_title') }}</a></li>
                        <li><a href="{{ route('site.module', 'attendance-biometric') }}">{{ __('site.biometric_title') }}</a></li>
                        <li><a href="{{ route('site.module', 'exams-results') }}">{{ __('site.exams_results') }}</a></li>
                        <li><a href="{{ route('site.module', 'parent-portal') }}">{{ __('site.parent_title') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>{{ __('site.resources') }}</h4>
                    <ul>
                        <li><a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a></li>
                        <li><a href="{{ route('site.download') }}">{{ __('site.download_app') }}</a></li>
                        <li><a href="{{ route('login') }}">{{ __('site.school_login') }}</a></li>
                        <li><a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>{{ __('site.office') }}</h4>
                    <ul>
                        <li><a href="#"><i class="fa fa-map-marker"></i> Moshi, Kilimanjaro</a></li>
                        <li><a href="tel:{{ config('shulexpert.phone_e164') }}"><i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}</a></li>
                        <li><a href="mailto:emca@emca.tech"><i class="fa fa-envelope"></i> emca@emca.tech</a></li>
                    </ul>
                    <p class="sx-powered mt-3">{{ __('site.powered_by') }}</p>
                </div>
            </div>
        </div>
        <div class="row footer-bottom d-flex justify-content-between align-items-center">
            <p class="col-lg-8 col-sm-12 footer-text m-0" style="color:#888;">
                &copy; {{ date('Y') }} ShuleXpert &nbsp;|&nbsp; <span class="sx-powered">{{ __('site.powered_by') }}</span>
            </p>
            <div class="col-lg-4 col-sm-12 footer-social">
                <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa fa-facebook"></i></a>
                <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa fa-twitter"></i></a>
                <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa fa-linkedin"></i></a>
                <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa fa-youtube-play"></i></a>
            </div>
        </div>
    </div>
</footer>
