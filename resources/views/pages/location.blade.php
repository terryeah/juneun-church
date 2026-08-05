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
            'note' => SiteSetting::get('sunday_first_service_name').' · '.SiteSetting::get('sunday_first_service_time'),
        ],
    ];
@endphp

<x-layout.app title="오시는 길" description="브리즈번 주는교회 오시는 길과 예배 시간 안내입니다.">

    <x-ui.page-header kicker="브리즈번에서 만나요 · Visit" title="오시는 길" />

    <section class="section-location-services container-site pb-8 md:pb-10 lg:pb-14">
        <x-home.service-strip />
    </section>

    <section class="section-location-maps container-site pb-12 lg:pb-16">
        <div class="grid gap-10 md:grid-cols-2 md:gap-8 lg:gap-11">
            @foreach ($locations as $location)
                <div class="flex flex-col">
                    <x-ui.kicker>{{ $location['label'] }}</x-ui.kicker>
                    <h2 class="mt-2 font-kr text-display-sm font-medium">{{ $location['address'] }}</h2>
                    <p class="mb-4 mt-1 text-body-sm text-navy-400">{{ $location['note'] }}</p>
                    <div class="mt-auto overflow-hidden rounded-media border border-line">
                        <iframe
                            src="https://www.google.com/maps?q={{ urlencode($location['address']) }}&output=embed"
                            class="h-[20rem] w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ $location['label'] }} 지도"
                        ></iframe>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section-location-pickup container-site pb-12 lg:pb-16">
        {{-- Poster leads on the left; on mobile the grid stacks it above the copy. --}}
        <div class="grid gap-8 md:items-start lg:gap-11 {{ ($pickupPhoto ?? null) ? 'md:grid-cols-[minmax(0,320px)_1fr]' : '' }}">
            @if ($pickupPhoto ?? null)
                <div class="overflow-hidden rounded-media">
                    <img src="{{ $pickupPhoto->thumbnailUrl() }}" alt="청년부 차량 픽업 안내" class="w-full object-cover" loading="lazy">
                </div>
            @endif
            <div>
                <x-ui.kicker>차량 픽업 · Pick-up</x-ui.kicker>
                <h2 class="mt-3 font-kr text-display-sm font-medium leading-snug">청년들을 위해<br>차량을 운행합니다</h2>
                <p class="mt-4 max-w-xl font-kr text-body-sm leading-relaxed text-navy-700">
                    주일마다 가든시티 웨스트필드에서 오후 1시부터 1시 10분까지 기다립니다.
                    인원 파악 후 차량을 운행하니, 이용을 원하시면 담임목사 연락처 또는
                    인스타그램 DM으로 미리 연락해 주세요.
                </p>
                <p class="mt-6 text-body-sm text-navy-400">
                    Garden City Westfield · 주일 1:00-1:10 PM
                </p>
            </div>
        </div>
    </section>

</x-layout.app>
