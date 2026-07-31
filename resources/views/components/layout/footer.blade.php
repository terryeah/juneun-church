@php
    use App\Models\SiteSetting;

    /**
     * Split the contact phone setting into the dialable number and any
     * trailing label such as "(담임목사)", so only the number is linked.
     */
    $contactPhone = trim((string) SiteSetting::get('contact_phone'));
    $phoneNumber = null;
    $phoneLabel = null;

    if (preg_match('/^([0-9+][0-9+\- ]*[0-9])\s*(.*)$/u', $contactPhone, $phoneParts)) {
        $phoneNumber = $phoneParts[1];
        $phoneLabel = $phoneParts[2] !== '' ? $phoneParts[2] : null;
    }
@endphp

<footer class="border-t-2 border-navy bg-paper">
    <div class="container-site py-12 lg:py-16">
        <div class="grid gap-10 md:grid-cols-[minmax(0,1fr)_auto_auto] md:gap-x-10 lg:gap-x-[88px]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-navy">
                    <span class="h-[34px]"><x-ui.logo /></span>
                    <span>
                        <span class="block font-kr text-[18px] font-medium leading-tight">{{ SiteSetting::get('church_name', '브리즈번 주는교회') }}</span>
                        <span class="block text-[10px] font-medium tracking-[0.04em] text-navy-400">{{ SiteSetting::get('denomination', '대한예수교장로회') }}</span>
                    </span>
                </a>
                <p class="mt-4 max-w-sm font-kr text-[13.5px] leading-relaxed text-navy-700">
                    받은 은혜를 흘려보내는 교회 - 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라갑니다.
                </p>
                <div class="mt-4 flex gap-3">
                    <a href="{{ SiteSetting::get('instagram_url', '#') }}" target="_blank" rel="noopener" class="flex text-navy hover:text-accent" aria-label="Instagram (새 창)">
                        <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.2" cy="6.8" r="0.9" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                    <a href="{{ SiteSetting::get('youtube_url', '#') }}" target="_blank" rel="noopener" class="flex text-navy hover:text-accent" aria-label="YouTube (새 창)">
                        <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="2" y="4.5" width="20" height="15" rx="4"/>
                            <path d="M10 9.3v5.4l5-2.7z" fill="currentColor" stroke="none"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h2 lang="en" class="text-kicker font-extrabold uppercase tracking-[0.16em] text-accent">Locations</h2>
                <ul class="mt-4 space-y-2 text-[13px] leading-relaxed text-navy-700">
                    <li>
                        <a href="https://www.google.com/maps?q={{ urlencode(SiteSetting::get('address_main', '')) }}" target="_blank" rel="noopener" class="block hover:text-accent">
                            <span class="block font-kr font-medium text-navy">{{ SiteSetting::get('address_main_label', '본당') }}</span>
                            {{ SiteSetting::get('address_main') }}<span class="sr-only"> (새 창)</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.google.com/maps?q={{ urlencode(SiteSetting::get('address_education', '')) }}" target="_blank" rel="noopener" class="block hover:text-accent">
                            <span class="block font-kr font-medium text-navy">{{ SiteSetting::get('address_education_label', '교육관') }}</span>
                            {{ SiteSetting::get('address_education') }}<span class="sr-only"> (새 창)</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h2 lang="en" class="text-kicker font-extrabold uppercase tracking-[0.16em] text-accent">Contact</h2>
                <ul class="mt-4 space-y-2 text-[13px] leading-relaxed text-navy-700">
                    @if (SiteSetting::get('contact_email'))
                        <li><a href="mailto:{{ SiteSetting::get('contact_email') }}" class="hover:text-accent">{{ SiteSetting::get('contact_email') }}</a></li>
                    @endif
                    @if ($phoneNumber)
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phoneNumber) }}" class="hover:text-accent">{{ $phoneNumber }}</a>
                            @if ($phoneLabel)
                                <span class="font-kr">{{ $phoneLabel }}</span>
                            @endif
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 pt-5 text-[11px] text-navy-400 md:flex-row md:items-center md:justify-between">
            <p>© {{ now('Australia/Brisbane')->year }} · {{ SiteSetting::get('church_name_en', 'Brisbane Juneun Church') }}</p>
            <p class="font-kr">{{ SiteSetting::get('denomination', '대한예수교장로회') }}</p>
        </div>
    </div>
</footer>
