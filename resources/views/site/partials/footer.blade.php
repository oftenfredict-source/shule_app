<footer class="footer-area section-gap">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>ShuleXpert</h4>
                    <p style="color:#aaa;font-size:13px;line-height:1.7;">
                        Complete school management for preschool, primary, secondary and advanced schools worldwide.
                    </p>
                    <p class="sx-powered mt-3">Powered by EmCa Techonology</p>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="{{ route('site.home') }}">Home</a></li>
                        <li><a href="{{ route('site.about') }}">About</a></li>
                        <li><a href="{{ route('site.team') }}">Team</a></li>
                        <li><a href="{{ route('site.services') }}">Services</a></li>
                        <li><a href="{{ route('site.modules') }}">Modules</a></li>
                        <li><a href="{{ route('pricing') }}">Pricing</a></li>
                        <li><a href="{{ route('site.faq') }}">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>Modules</h4>
                    <ul>
                        <li><a href="{{ route('site.module', 'fees-payments') }}">Fees &amp; Payments</a></li>
                        <li><a href="{{ route('site.module', 'attendance-biometric') }}">Attendance</a></li>
                        <li><a href="{{ route('site.module', 'exams-results') }}">Exams &amp; Results</a></li>
                        <li><a href="{{ route('site.module', 'parent-portal') }}">Parent Portal</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="{{ route('online_application') }}">Online Application</a></li>
                        <li><a href="{{ route('site.download') }}">Download App</a></li>
                        <li><a href="{{ route('login') }}">School Login</a></li>
                        <li><a href="{{ route('site.contact') }}">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="single-footer-widget">
                    <h4>Office</h4>
                    <ul>
                        <li><a href="#"><i class="fa fa-map-marker"></i> Moshi, Kilimanjaro</a></li>
                        <li><a href="tel:{{ config('shulexpert.phone_e164') }}"><i class="fa fa-phone"></i> {{ config('shulexpert.phone_display') }}</a></li>
                        <li><a href="mailto:emca@emca.tech"><i class="fa fa-envelope"></i> emca@emca.tech</a></li>
                    </ul>
                    <p class="sx-powered mt-3">Powered by EmCa Techonology</p>
                </div>
            </div>
        </div>
        <div class="row footer-bottom d-flex justify-content-between align-items-center">
            <p class="col-lg-8 col-sm-12 footer-text m-0" style="color:#888;">
                &copy; {{ date('Y') }} ShuleXpert &nbsp;|&nbsp; <span class="sx-powered">Powered by EmCa Techonology</span>
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
