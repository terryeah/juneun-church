@php
    use App\Models\SiteSetting;
@endphp

<footer class="border-t-2 border-navy bg-paper">
    <div class="container-site py-12 lg:py-16">
        <div class="grid gap-10 md:grid-cols-[minmax(0,1fr)_auto_auto] md:gap-x-[88px]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-navy">
                    <span class="h-[34px]"><x-ui.logo /></span>
                    <span>
                        <span class="block font-kr text-[18px] font-medium leading-tight">{{ SiteSetting::get('church_name', '브리즈번 주는교회') }}</span>
                        <span class="block text-[10px] font-medium tracking-[0.04em] text-navy-400">{{ SiteSetting::get('denomination', '대한예수교 장로회') }}</span>
                    </span>
                </a>
                <p class="mt-4 max-w-sm font-kr text-[13.5px] leading-relaxed text-navy-700">
                    받은 은혜를 흘려보내는 교회 — 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라갑니다.
                </p>
                <div class="mt-5 flex gap-3">
                    <a href="{{ SiteSetting::get('instagram_url', '#') }}" class="text-navy hover:text-accent" aria-label="Instagram">
                        <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                    <a href="{{ SiteSetting::get('youtube_url', '#') }}" class="text-navy hover:text-accent" aria-label="YouTube">
                        <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="2.5" y="5.5" width="19" height="13" rx="3.5"/>
                            <path d="M10.5 9.5v5l4.5-2.5z" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h2 class="text-kicker font-extrabold uppercase tracking-[0.16em] text-accent">Locations</h2>
                <ul class="mt-4 space-y-4 text-[13px] leading-relaxed text-navy-700">
                    <li>
                        <span class="block font-kr font-medium text-navy">{{ SiteSetting::get('address_main_label', '본당') }}</span>
                        {{ SiteSetting::get('address_main') }}
                    </li>
                    <li>
                        <span class="block font-kr font-medium text-navy">{{ SiteSetting::get('address_education_label', '교육관') }}</span>
                        {{ SiteSetting::get('address_education') }}
                    </li>
                </ul>
            </div>

            <div>
                <h2 class="text-kicker font-extrabold uppercase tracking-[0.16em] text-accent">Contact</h2>
                <ul class="mt-4 space-y-2 text-[13px] leading-relaxed text-navy-700">
                    @if (SiteSetting::get('contact_email'))
                        <li><a href="mailto:{{ SiteSetting::get('contact_email') }}" class="hover:text-accent">{{ SiteSetting::get('contact_email') }}</a></li>
                    @endif
                    @if (SiteSetting::get('contact_phone'))
                        <li>{{ SiteSetting::get('contact_phone') }}</li>
                    @endif
                    <li><a href="{{ route('events') }}" class="hover:text-accent">교회 행사</a></li>
                    <li><a href="{{ route('people') }}" class="hover:text-accent">섬기는 사람들</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 border-t border-line pt-5 text-[11px] text-navy-400 md:flex-row md:items-center md:justify-between">
            <p>© 2024–{{ now()->year }} {{ SiteSetting::get('church_name_en', 'Brisbane Ju-neun Church') }}</p>
            <p class="font-kr">{{ SiteSetting::get('denomination', '대한예수교장로회') }}</p>
        </div>
    </div>
</footer>
