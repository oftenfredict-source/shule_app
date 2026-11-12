@php
    $phone = config('shulexpert.phone_display', '+255 749 719 998');
    $phoneTel = preg_replace('/\s+/', '', (string) config('shulexpert.phone_e164', '+255749719998'));
    $email = config('shulexpert.email_company', config('shulexpert.email', 'emca@emcatech.co.tz'));
    $address = config('shulexpert.address', 'Ben Bella Street, Moshi Municipality');
    $address2 = config('shulexpert.address_line_2', 'Opposite High Court of Tanzania');
    $poBox = config('shulexpert.po_box', 'P.O. Box 20, Moshi - Kilimanjaro');
    $mapsUrl = config('shulexpert.maps_url', '#');
    $company = config('shulexpert.company_name', 'EmCa Techonologies');
    $legal = config('shulexpert.company_legal_name', 'EmCa Techonologies LTD');
    $companyWeb = config('shulexpert.company_website', 'https://emcatech.co.tz');
    $socials = collect(config('shulexpert.social_links', []))->filter(fn ($s) => !empty($s['url']) && ($s['url'] ?? '#') !== '#');
    $footerModules = config('shulexpert.footer_modules', []);
    $footerBg = asset('images/footer-bg.jpg');
    $brandLogo = asset('shule.png');
@endphp
{{-- EmCa-style public footer --}}
<footer id="footer" class="footer sx-emca-footer">
  <div class="sx-emca-footer-bg" style="--sx-footer-bg: url('{{ $footerBg }}')" aria-hidden="true"></div>
  <div class="sx-emca-footer-inner">
    <div class="container">
      <div class="sx-emca-contact-bar">
        <div class="sx-emca-contact-item">
          <span class="sx-emca-contact-icon" aria-hidden="true"><i class="fas fa-phone-alt"></i></span>
          <div class="sx-emca-contact-text">
            <span class="sx-emca-contact-label">{{ __('site.footer_call_us') }}</span>
            <a href="tel:{{ $phoneTel }}">{{ $phone }}</a>
          </div>
        </div>
        <div class="sx-emca-contact-item">
          <span class="sx-emca-contact-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
          <div class="sx-emca-contact-text">
            <span class="sx-emca-contact-label">{{ __('site.footer_email_us') }}</span>
            <a href="mailto:{{ $email }}">{{ $email }}</a>
          </div>
        </div>
        <div class="sx-emca-contact-item">
          <span class="sx-emca-contact-icon" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></span>
          <div class="sx-emca-contact-text">
            <span class="sx-emca-contact-label">{{ __('site.footer_our_office') }}</span>
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener">{{ $address }}</a>
          </div>
        </div>
      </div>
    </div>

    <div class="sx-emca-widgets">
      <div class="container">
        <div class="row gy-4">
          <div class="col-md-6 col-xl-3">
            <div class="sx-emca-widget">
              <a href="{{ route('site.home') }}" class="sx-emca-logo-link">
                <img src="{{ $brandLogo }}" alt="ShuleXpert" class="sx-emca-logo sx-emca-logo--shulexpert">
              </a>
              <p class="sx-emca-about">{{ __('site.footer_brand_blurb') }}</p>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3 col-xl-3">
            <div class="sx-emca-widget">
              <h5 class="sx-emca-widget-title">{{ __('site.footer_solutions') }}</h5>
              <ul class="sx-emca-list">
                @foreach ($footerModules as $module)
                  @php
                    $labelKey = $module['label_key'] ?? '';
                    $slug = $module['slug'] ?? null;
                    $label = $labelKey ? __('site.'.$labelKey) : '';
                    $url = $slug ? route('site.module', $slug) : route('site.modules');
                  @endphp
                  @if($label !== '')
                  <li>
                    <a href="{{ $url }}">
                      <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                      <span>{{ $label }}</span>
                    </a>
                  </li>
                  @endif
                @endforeach
              </ul>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-3 col-xl-3">
            <div class="sx-emca-widget">
              <h5 class="sx-emca-widget-title">{{ __('site.footer_contact_info') }}</h5>
              <p class="sx-emca-address">
                {{ $address }}<br>
                {{ $address2 }}<br>
                {{ $poBox }}
              </p>
              <h5 class="sx-emca-widget-title sx-emca-widget-title--spaced">{{ __('site.footer_working_hours') }}</h5>
              <p class="sx-emca-hours">
                {{ __('site.footer_working_days') }}<br>
                {{ __('site.footer_working_time') }}
              </p>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="sx-emca-widget">
              <h5 class="sx-emca-widget-title">{{ __('site.footer_follow_us') }}</h5>
              <p class="sx-emca-follow-text">{{ __('site.footer_follow_blurb') }}</p>
              <div class="sx-emca-social">
                @foreach ($socials as $social)
                  <a href="{{ $social['url'] }}"
                     class="sx-emca-social-btn sx-emca-social-{{ $social['key'] }}"
                     target="_blank"
                     rel="noopener noreferrer"
                     aria-label="{{ $social['label'] ?? $social['key'] }}">
                    <i class="{{ $social['icon'] }}"></i>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="sx-emca-bottom">
      <div class="container">
        <div class="sx-emca-bottom-row">
          <p class="sx-emca-copy mb-0">
            Copyright &copy; {{ date('Y') }}
            <a href="{{ $companyWeb }}" target="_blank" rel="noopener">{{ $company }}</a>.
            {{ __('site.footer_rights') }}
          </p>
          <ul class="sx-emca-bottom-menu">
            <li><a href="{{ route('site.about') }}">{{ __('site.about') }}</a></li>
            <li><a href="{{ route('pricing') }}">{{ __('site.pricing') }}</a></li>
            <li><a href="{{ route('site.contact') }}">{{ __('site.contact') }}</a></li>
            <li><a href="{{ route('site.faq') }}">{{ __('site.faq') }}</a></li>
            <li><a href="{{ route('online_application') }}">{{ __('site.online_application') }}</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</footer>
