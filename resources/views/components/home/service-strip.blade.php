@php
    use App\Models\SiteSetting;

    $services = [
        [
            'name' => SiteSetting::get('sunday_first_service_name', '주일 1부 예배 (봉사자 예배)'),
            'time' => SiteSetting::get('sunday_first_service_time'),
            'venue' => SiteSetting::get('sunday_first_service_venue'),
            'address' => SiteSetting::get('address_education'),
        ],
        [
            'name' => SiteSetting::get('sunday_service_name', '주일예배'),
            'time' => SiteSetting::get('sunday_service_time'),
            'venue' => SiteSetting::get('sunday_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
        [
            'name' => SiteSetting::get('wednesday_service_name', '수요기도회'),
            'time' => SiteSetting::get('wednesday_service_time'),
            'venue' => SiteSetting::get('wednesday_service_venue'),
            'address' => SiteSetting::get('address_education'),
        ],
        [
            'name' => SiteSetting::get('kids_service_name', '주일학교'),
            'time' => SiteSetting::get('kids_service_time'),
            'venue' => SiteSetting::get('kids_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
    ];
@endphp

{{-- Four equal service-time columns, divided by 1px cells on wide screens. --}}
<div class="border-y border-line py-6 lg:py-[26px]">
    <div class="grid gap-6 md:grid-cols-2 md:gap-8 xl:grid-cols-[1fr_1px_1fr_1px_1fr_1px_1fr] xl:gap-[30px]">
        @foreach ($services as $service)
            @if (! $loop->first)
                <div class="hidden bg-line xl:block"></div>
            @endif
            <div>
                <x-ui.kicker>{{ $service['name'] }}</x-ui.kicker>
                <h3 class="mt-2 font-kr text-display-sm font-medium">{{ $service['time'] }} · {{ $service['venue'] }}</h3>
                <p class="mt-1 text-[12px] text-navy-400">{{ $service['address'] }}</p>
            </div>
        @endforeach
    </div>
</div>
