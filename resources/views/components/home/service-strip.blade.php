@php
    use App\Models\SiteSetting;

    $services = [
        [
            'name' => SiteSetting::get('sunday_service_name', '주일예배'),
            'time' => SiteSetting::get('sunday_service_time'),
            'venue' => SiteSetting::get('sunday_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
        [
            'name' => SiteSetting::get('wednesday_service_name', '수요예배'),
            'time' => SiteSetting::get('wednesday_service_time'),
            'venue' => SiteSetting::get('wednesday_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
        [
            'name' => SiteSetting::get('kids_service_name', '주일학교'),
            'time' => SiteSetting::get('kids_service_time'),
            'venue' => SiteSetting::get('kids_service_venue'),
            'address' => SiteSetting::get('address_education'),
        ],
    ];
@endphp

{{-- Three equal service-time columns separated by 1px divider cells. --}}
<div class="border-y border-line py-[26px]">
    <div class="grid gap-6 lg:grid-cols-[1fr_1px_1fr_1px_1fr] lg:gap-[30px]">
        @foreach ($services as $service)
            @if (! $loop->first)
                <div class="hidden bg-line lg:block"></div>
            @endif
            <div>
                <x-ui.kicker>{{ $service['name'] }}</x-ui.kicker>
                <h3 class="mt-2 font-kr text-display-sm font-medium">{{ $service['time'] }} · {{ $service['venue'] }}</h3>
                <p class="mt-1 text-[12px] text-navy-400">{{ $service['address'] }}</p>
            </div>
        @endforeach
    </div>
</div>
