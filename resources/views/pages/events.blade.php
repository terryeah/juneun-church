<x-layout.app title="교회 행사" description="브리즈번 주는교회의 다가오는 행사 일정입니다.">

    <x-ui.page-header kicker="Events · 교회 행사" title="교회 행사" />

    <section class="container-site pb-12 lg:pb-16">
        @forelse ($eventsByMonth as $month => $events)
            <div class="mb-10">
                <h2 class="font-kr text-display-sm font-medium">{{ $month }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-t-2 border-navy text-left">
                        <thead>
                            <tr class="border-b border-line text-[11px] uppercase tracking-[0.16em] text-navy-400">
                                <th class="py-3 pr-4 font-extrabold">행사일</th>
                                <th class="py-3 pr-4 font-extrabold">행사명</th>
                                <th class="py-3 font-extrabold">행사장</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr class="border-b border-line">
                                    <td class="whitespace-nowrap py-4 pr-4 text-[13px] text-navy-700">
                                        {{ $event->event_date->translatedFormat('n월 j일 (D)') }}
                                        @if ($event->event_time)
                                            <span class="text-navy-400">{{ \Illuminate\Support\Carbon::parse($event->event_time)->format('H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-4 font-kr text-[15px] font-medium">{{ $event->title }}</td>
                                    <td class="py-4 font-kr text-[13px] text-navy-700">{{ $event->location }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-[13px] text-navy-400">예정된 행사가 없습니다.</p>
        @endforelse
    </section>

</x-layout.app>
