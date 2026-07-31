<x-layout.app title="예배 안내" description="브리즈번 주는교회 예배 시간과 지난 예배 영상을 안내합니다.">

    <x-ui.page-header kicker="예배 안내 · Worship" title="함께 드리는 예배">
        주는교회의 모든 예배는 누구에게나 열려 있습니다. 이번 주, 함께 예배해요.
    </x-ui.page-header>

    <section class="section-worship-services container-site pb-8 md:pb-10 lg:pb-14">
        <x-home.service-strip />
    </section>

    <section class="section-worship-groups container-site py-8 md:py-10 lg:py-14">
        @php
            $fellowship = [
                ['name' => '남/여전도회', 'note' => '주일예배 후 · 매월 첫째 주'],
                ['name' => '셀 모임', 'note' => '주일예배 후 · 둘째-다섯째 주'],
                ['name' => '청년부 모임', 'note' => '주일예배 이후'],
                ['name' => '성경 공부', 'note' => '월요일-금요일 · 교육관'],
            ];
            $sundaySchool = [
                ['name' => 'Joy Little Kids', 'grade' => '30개월-Kindy', 'note' => '주일 오후 1:30 · Hall'],
                ['name' => 'Glory Kids', 'grade' => 'Prep-Y6', 'note' => '주일 오후 1:30 · Hall'],
                ['name' => 'Holy Youth', 'grade' => 'Y7-Y12', 'note' => '주일 오후 1:30 · Chapel'],
                ['name' => 'Holy Youth 월모임', 'grade' => null, 'note' => '장소 및 시간 추후 공지'],
            ];
        @endphp

        <div class="grid gap-10 md:grid-cols-2 md:gap-8 lg:gap-11">
            <div>
                <x-ui.kicker>모임 · Fellowship</x-ui.kicker>
                <div class="mt-4 border-t-2 border-navy">
                    @foreach ($fellowship as $group)
                        <div class="flex items-baseline justify-between gap-4 border-b border-line py-3.5">
                            <h3 class="font-kr text-body font-medium">{{ $group['name'] }}</h3>
                            <p class="text-right font-kr text-body-sm text-navy-400">{{ $group['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <x-ui.kicker>주일학교 · Sunday School</x-ui.kicker>
                <div class="mt-4 border-t-2 border-navy">
                    @foreach ($sundaySchool as $class)
                        <div class="flex items-baseline justify-between gap-4 border-b border-line py-3.5">
                            <h3 class="text-body font-medium">
                                {{ $class['name'] }}
                                @if ($class['grade'])
                                    <span class="ml-1 text-caption font-normal text-navy-400">{{ $class['grade'] }}</span>
                                @endif
                            </h3>
                            <p class="text-right font-kr text-body-sm text-navy-400">{{ $class['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="section-worship-archive container-site pt-8 pb-12 md:pt-10 lg:pt-14 lg:pb-16">
        <x-ui.kicker>지난 예배 · Archive</x-ui.kicker>
        <div class="mt-6 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($sermons as $sermon)
                <div>
                    <x-ui.sermon-video :sermon="$sermon" />
                    <h3 class="mt-3 font-kr text-body font-medium">{{ $sermon->title }}</h3>
                    <p class="mt-1 text-body-sm text-navy-400">
                        {{ $sermon->sermon_date->translatedFormat('Y년 n월 j일') }} · {{ $sermon->serviceType?->name }}
                    </p>
                </div>
            @empty
                <p class="text-body-sm text-navy-400">등록된 예배 영상이 없습니다.</p>
            @endforelse
        </div>
    </section>

</x-layout.app>
