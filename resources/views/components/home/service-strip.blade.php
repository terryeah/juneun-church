@php
    use App\Models\SiteSetting;

    /**
     * Columns the strip lays out at lg and above. Three keeps every line
     * unwrapped inside the 80rem site container; the list wraps onto a
     * second row once it grows past this, so adding a service needs no
     * change to the grid template.
     */
    $columns = 3;

    $services = [
        [
            'name' => SiteSetting::get('sunday_first_service_name', '주일 1부 예배 (사역자 예배)'),
            'time' => SiteSetting::get('sunday_first_service_time'),
            'venue' => SiteSetting::get('sunday_first_service_venue'),
            'address' => SiteSetting::get('address_education'),
        ],
        [
            'name' => SiteSetting::get('sunday_service_name', '주일 2부 예배 (청장년부)'),
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
            'name' => SiteSetting::get('kids_service_name', '유초등부'),
            'time' => SiteSetting::get('kids_service_time'),
            'venue' => SiteSetting::get('kids_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
        [
            'name' => SiteSetting::get('youth_service_name', '청소년부'),
            'time' => SiteSetting::get('youth_service_time'),
            'venue' => SiteSetting::get('youth_service_venue'),
            'address' => SiteSetting::get('address_main'),
        ],
    ];
@endphp

{{-- Equal service columns, divided by 1px cells on wide screens. The divider is dropped at the start of each row so a wrapped row still reads as columns. --}}
<div class="border-y border-line py-6 lg:py-[1.625rem]">
    <div class="grid gap-6 md:grid-cols-2 md:gap-8 lg:grid-cols-[1fr_1px_1fr_1px_1fr] lg:gap-[1.875rem]">
        @foreach ($services as $service)
            @if ($loop->index % $columns !== 0)
                <div class="hidden bg-line lg:block"></div>
            @endif
            <div>
                <x-ui.kicker tracking="tracking-[0.06em]">{{ $service['name'] }}</x-ui.kicker>
                <h2 class="mt-2 font-kr text-display-sm font-medium">{{ $service['time'] }}</h2>
                <p class="mt-1 font-kr text-body-sm text-navy-700">{{ $service['venue'] }}</p>
                <p class="mt-1 text-body-sm text-navy-400">{{ $service['address'] }}</p>
            </div>
        @endforeach
    </div>
</div>
