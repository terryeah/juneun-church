<x-layout.app description="브리즈번 주는교회 - 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라가는 젊은 한인교회입니다.">

    {{-- Hero --}}
    <section class="container-site py-10 lg:py-14">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:gap-11">
            <div class="lg:flex-[1.05]">
                <x-ui.kicker>Brisbane Ju-neun Church · Since 2024</x-ui.kicker>
                <h1 class="mt-4 font-kr text-display-lg">받은 은혜를<br>흘려보내는 교회</h1>
                <p class="mt-5 max-w-[400px] text-[16px] leading-relaxed text-navy-700">
                    A young Korean church in Brisbane - worshipping together, giving generously, and growing as followers of Jesus Christ.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.button :href="route('worship')">예배 안내 →</x-ui.button>
                    <x-ui.button :href="route('location')" variant="secondary">오시는 길</x-ui.button>
                </div>
            </div>
            <div class="lg:flex-1">
                <x-ui.photo-placeholder label="Worship · Congregation" class="h-[300px] md:h-[420px]" />
            </div>
        </div>
    </section>

    {{-- Service strip --}}
    <section class="container-site">
        <x-home.service-strip />
    </section>

    {{-- Value rows: the three 주는교회 identities --}}
    <section class="container-site py-10 lg:py-14">
        @php
            $identities = [
                [
                    'korean' => '주는', 'english' => 'Lord',
                    'verse' => '너희가 나를 선생이라 또는 주라 하니 너희 말이 옳도다 내가 그러하다',
                    'reference' => '요한복음 13:13',
                    'description' => '예수님을 주님으로 고백하며 그분의 다스리심을 따르는 교회입니다.',
                ],
                [
                    'korean' => '보여주는', 'english' => 'Revealing',
                    'verse' => '이같이 너희 빛이 사람 앞에 비치게 하여 그들로 너희 착한 행실을 보고 하늘에 계신 너희 아버지께 영광을 돌리게 하라',
                    'reference' => '마태복음 5:16',
                    'description' => '말이 아닌 삶으로 복음을 보여주는 교회입니다.',
                ],
                [
                    'korean' => '주는', 'english' => 'Giving',
                    'verse' => '주라 그리하면 너희에게 줄 것이니 곧 후히 되어 누르고 흔들어 넘치도록 하여 너희에게 안겨 주리라',
                    'reference' => '누가복음 6:38',
                    'description' => '받은 은혜를 이웃과 넉넉히 나누는 교회입니다.',
                ],
            ];
        @endphp

        <div class="divide-y divide-line">
            @foreach ($identities as $identity)
                <div class="grid gap-4 py-[26px] md:grid-cols-[minmax(0,280px)_minmax(0,1fr)] md:gap-10">
                    <h2 class="font-kr text-display-sm font-medium">
                        {{ $identity['korean'] }}<span class="text-accent">({{ $identity['english'] }})</span>교회
                    </h2>
                    <div>
                        <p class="font-kr text-[15px] leading-relaxed">{{ $identity['verse'] }}</p>
                        <p class="mt-2 text-[11px] font-bold tracking-[0.08em] text-accent">{{ $identity['reference'] }}</p>
                        <p class="mt-3 font-kr text-[13.5px] leading-relaxed text-navy-700">{{ $identity['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- News + latest sermon band --}}
    <section class="container-site border-t border-line py-10 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.3fr] lg:gap-11">
            <div>
                <x-ui.kicker>교회 소식 · News</x-ui.kicker>
                <div class="mt-4">
                    @forelse ($announcements as $announcement)
                        <a href="{{ route('news.show', $announcement) }}" class="group block border-t border-line py-4">
                            <p class="text-[11px] text-navy-400">
                                {{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}
                                @if ($announcement->is_pinned)
                                    <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Pinned</span>
                                @endif
                            </p>
                            <h3 class="mt-1 font-kr text-[15px] font-medium group-hover:text-accent">{{ $announcement->title }}</h3>
                        </a>
                    @empty
                        <p class="border-t border-line py-4 text-[13px] text-navy-400">등록된 소식이 없습니다.</p>
                    @endforelse
                </div>
                <a href="{{ route('news.index') }}" class="mt-2 inline-block text-[13px] font-bold text-accent hover:text-accent-700">소식 전체 보기 →</a>
            </div>

            @if ($latestSermon)
                <div>
                    <div class="flex items-baseline justify-between">
                        <x-ui.kicker>최근 예배 · Latest Sermon</x-ui.kicker>
                        <a href="{{ $latestSermon->youtubeUrl() }}" class="text-[12px] font-bold text-accent hover:text-accent-700" rel="noopener">YouTube →</a>
                    </div>
                    <div class="mt-4">
                        <x-ui.sermon-video :sermon="$latestSermon" />
                    </div>
                    <h3 class="mt-4 font-kr text-display-sm font-medium">{{ $latestSermon->title }}</h3>
                    <p class="mt-1 text-[12px] text-navy-400">
                        {{ $latestSermon->preacher }} · {{ $latestSermon->sermon_date->translatedFormat('Y년 n월 j일') }}
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Meal sharing (반찬나눔) --}}
    <section class="container-site border-t border-line py-10 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-[1.3fr_1fr] lg:gap-11">
            <div class="flex items-center">
                <x-ui.photo-placeholder label="Meal Sharing · 반찬나눔" class="aspect-video w-full" />
            </div>
            <div class="lg:border-l lg:border-line lg:pl-10">
                <x-ui.kicker>Meal Sharing · 반찬나눔</x-ui.kicker>
                <h2 class="mt-3 font-kr text-[1.7rem] font-medium leading-snug">정성껏 준비한 반찬을<br>이웃과 나눕니다</h2>
                <p class="mt-4 font-kr text-[13.5px] leading-relaxed text-navy-700">
                    주는교회는 매월 정성껏 준비한 반찬을 이웃과 나누며 받은 은혜를 흘려보냅니다.
                    누구나 함께할 수 있습니다.
                </p>
                <div class="mt-6 grid grid-cols-2 border-t border-line pt-5">
                    <div>
                        <p class="font-kr text-display-sm font-medium">71명</p>
                        <p class="mt-1 text-[11px] text-navy-400">지난 나눔 참여</p>
                    </div>
                    <div>
                        <p class="font-kr text-display-sm font-medium">매월 정기</p>
                        <p class="mt-1 text-[11px] text-navy-400">Regular serving</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Poster band: the one saturated field on the page --}}
    <section class="bg-navy py-[76px]">
        <div class="container-site">
            <p class="text-kicker font-extrabold uppercase tracking-[0.16em] text-cream/55">Visit Us · Sunday 11:30 AM</p>
            <p class="mt-5 font-kr text-display-lg text-cream">누구나 환영합니다.<br>이번 주일, 함께 예배해요.</p>
        </div>
    </section>

    {{-- Photo band: 3-up gallery preview --}}
    <section class="bg-navy pb-[2px]">
        <div class="grid grid-cols-1 gap-[2px] md:grid-cols-3">
            @forelse ($recentPhotos as $photo)
                <a href="{{ route('gallery.show', $photo->album) }}" class="block aspect-square overflow-hidden rounded-[10px]">
                    <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album->title }}" class="photo-treatment h-full w-full" loading="lazy">
                </a>
            @empty
                @foreach (['Fellowship', 'Worship', 'Next-Gen'] as $label)
                    <x-ui.photo-placeholder :label="$label" class="aspect-square rounded-[10px] bg-navy-700/60 text-cream/40" />
                @endforeach
            @endforelse
        </div>
    </section>

</x-layout.app>
