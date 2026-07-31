<x-layout.app title="교회 행사" description="브리즈번 주는교회의 다가오는 행사 일정입니다.">

    <x-ui.page-header kicker="교회 행사 · Events" title="교회 행사" />

    <section class="section-events-schedule container-site pb-12 lg:pb-16">
        @forelse ($eventsByMonth as $month => $events)
            <div class="mb-10 lg:mb-12">
                <h2 class="font-kr text-display-sm font-medium">{{ $month }}</h2>

                <div class="mt-4 border-t-2 border-navy">
                    <div class="hidden gap-6 py-3 text-[11px] font-extrabold uppercase tracking-[0.16em] text-navy-400 md:grid md:grid-cols-[240px_minmax(0,1fr)_280px]">
                        <span>행사일</span>
                        <span>행사명</span>
                        <span>행사장</span>
                    </div>

                    @foreach ($events as $event)
                        <div class="grid gap-1 border-t border-line py-4 md:grid-cols-[240px_minmax(0,1fr)_280px] md:items-baseline md:gap-6 md:border-t md:py-4">
                            <p class="text-[13px] text-navy-700">
                                {{ $event->event_date->translatedFormat('n월 j일 (D)') }}
                                @if ($event->event_time)
                                    <span class="text-navy-400">{{ \Illuminate\Support\Carbon::parse($event->event_time)->format('H:i') }}</span>
                                @endif
                                @if ($event->end_date && ! $event->end_date->isSameDay($event->event_date))
                                    ~ {{ $event->end_date->translatedFormat('n월 j일 (D)') }}
                                    @if ($event->end_time)
                                        <span class="text-navy-400">{{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}</span>
                                    @endif
                                @elseif ($event->end_time)
                                    ~ <span class="text-navy-400">{{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}</span>
                                @endif
                            </p>
                            <h3 class="font-kr text-[15px] font-medium">{{ $event->title }}</h3>
                            <p class="font-kr text-[13px] text-navy-400 md:text-navy-700">{{ $event->location }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-[13px] text-navy-400">예정된 행사가 없습니다.</p>
        @endforelse
    </section>

</x-layout.app>
