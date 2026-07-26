@php
    use App\Models\SiteSetting;

    $locations = [
        [
            'label' => SiteSetting::get('address_main_label', '본당'),
            'address' => SiteSetting::get('address_main'),
            'note' => SiteSetting::get('sunday_service_name').' · '.SiteSetting::get('sunday_service_time'),
        ],
        [
            'label' => SiteSetting::get('address_education_label', '교육관'),
            'address' => SiteSetting::get('address_education'),
            'note' => SiteSetting::get('kids_service_name').' · '.SiteSetting::get('kids_service_time'),
        ],
    ];
@endphp

<x-layout.app title="오시는 길" description="브리즈번 주는교회 오시는 길과 예배 시간 안내입니다.">

    <x-ui.page-header kicker="Visit · 오시는 길" title="오시는 길" />

    <section class="container-site">
        <x-home.service-strip />
    </section>

    <section class="container-site py-10 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-11">
            @foreach ($locations as $location)
                <div>
                    <x-ui.kicker>{{ $location['label'] }}</x-ui.kicker>
                    <h2 class="mt-2 font-kr text-display-sm font-medium">{{ $location['address'] }}</h2>
                    <p class="mt-1 text-[12px] text-navy-400">{{ $location['note'] }}</p>
                    <div class="mt-4 overflow-hidden rounded-media border border-line">
                        <iframe
                            src="https://www.google.com/maps?q={{ urlencode($location['address']) }}&output=embed"
                            class="h-[320px] w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ $location['label'] }} 지도"
                        ></iframe>
                    </div>
                </div>
            @endforeach
        </div>

        @if (SiteSetting::get('contact_email') || SiteSetting::get('contact_phone'))
            <div class="mt-10 border-t border-line pt-6">
                <x-ui.kicker>Contact</x-ui.kicker>
                <p class="mt-2 text-[14px] text-navy-700">
                    @if (SiteSetting::get('contact_email'))
                        <a href="mailto:{{ SiteSetting::get('contact_email') }}" class="hover:text-accent">{{ SiteSetting::get('contact_email') }}</a>
                    @endif
                    @if (SiteSetting::get('contact_phone'))
                        · {{ SiteSetting::get('contact_phone') }}
                    @endif
                </p>
            </div>
        @endif
    </section>

</x-layout.app>
