@php
    $variant = $variant ?? 'home';
@endphp
<div class="col-lg-4 col-md-6 sx-team-col">
    @php
        $details = $member['more_bio'] ?? ($member['bio'] ?? $member['tagline'] ?? '');
        $slug = $member['slug'] ?? '';
    @endphp
    <article id="{{ $slug ? ('team-member-' . $slug) : null }}" class="sx-team-card sx-team-card--{{ $variant }}" itemscope itemtype="https://schema.org/Person">
        <div class="sx-team-card__glow" aria-hidden="true"></div>
        <div class="sx-team-card__inner">
            <div class="sx-team-card__frame">
                <div class="sx-team-card__ring" aria-hidden="true"></div>
                <div class="sx-team-card__photo">
                    <img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}, {{ $member['role'] }}, EmCa Techonology" loading="lazy" itemprop="image">
                </div>
            </div>
            <div class="sx-team-card__body">
                <h3 class="sx-team-card__name" itemprop="name">{{ $member['name'] }}</h3>
                <p class="sx-team-card__role" itemprop="jobTitle">{{ $member['role'] }}</p>
                <p class="sx-team-card__tagline">{{ $member['tagline'] }}</p>
                @if($variant === 'page' && !empty($member['bio']))
                    <p class="sx-team-card__bio">{{ $member['bio'] }}</p>
                @endif
                <meta itemprop="worksFor" content="EmCa Techonology">
                @if(!empty($details))
                    <meta itemprop="description" content="{{ trim(str_replace(["\n", "\r"], ' ', (string) $details)) }}">
                @endif
                @if($slug)
                    <link itemprop="url" href="{{ route('site.team.member', $slug) }}">
                @endif

                @if(!empty($member['slug']))
                    <div class="sx-team-card__see-more mt-3">
                        <a href="{{ route('site.team.member', $member['slug']) }}"
                           class="sx-team-see-more-link">
                            See more about this
                        </a>
                    </div>
                @endif

                @if(!empty($member['social']))
                    <div class="sx-team-social" aria-label="Social links for {{ $member['name'] }}">
                        @foreach($member['social'] as $social)
                            <a href="{{ $social['url'] }}"
                               @if(!str_starts_with($social['url'], 'tel:') && !str_starts_with($social['url'], 'mailto:'))
                                   target="_blank" rel="noopener noreferrer"
                               @endif
                               title="{{ $social['label'] }}"
                               aria-label="{{ $social['label'] }}">
                                <i class="fa {{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </article>
</div>
