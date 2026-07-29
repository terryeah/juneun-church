<x-layout.app description="브리즈번 주는교회 - 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라가는 젊은 한인교회입니다.">

    {{-- Hero --}}
    <section class="section-hero container-site py-8 md:py-10 lg:py-14">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:gap-11">
            <div class="lg:flex-[1.05]">
                <x-ui.kicker data-hero-item>Brisbane Juneun Church · Since 2024</x-ui.kicker>
                <h1 class="mt-4 font-kr text-display-lg" data-hero-item>받은 은혜를<br>흘려보내는 교회</h1>
                <p class="mt-5 max-w-[400px] text-[16px] leading-relaxed text-navy-700" data-hero-item>
                    A young Korean church in Brisbane - worshipping together, giving generously, and growing as followers of Jesus Christ.
                </p>
                <div class="mt-7 flex flex-wrap gap-3" data-hero-item>
                    <x-ui.button :href="route('worship')">예배 안내 →</x-ui.button>
                    <x-ui.button :href="route('location')" variant="secondary">오시는 길</x-ui.button>
                </div>
            </div>
            <div class="lg:flex-1">
                @if ($heroPhoto)
                    <img src="{{ $heroPhoto->thumbnailUrl() }}" alt="함께 예배하는 주는교회 성도들" class="h-[300px] w-full rounded-media object-cover md:h-[420px]" fetchpriority="high" decoding="async">
                @else
                    <x-ui.photo-placeholder label="Worship · Congregation" class="h-[300px] md:h-[420px]" />
                @endif
            </div>
        </div>
    </section>

    {{-- Service strip --}}
    <section class="section-service-times container-site py-8 md:py-10 lg:py-14">
        <x-home.service-strip />
    </section>

    {{-- Value rows: the three 주는교회 identities --}}
    <section class="section-identity container-site py-8 md:py-10 lg:py-14">
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
                <div class="grid gap-4 py-[26px] first:pt-0 last:pb-0 md:grid-cols-[minmax(0,280px)_minmax(0,1fr)] md:gap-10">
                    <h2 class="font-kr text-display-sm font-medium">
                        {{ $identity['korean'] }}<span class="text-accent">({{ $identity['english'] }})</span>교회
                    </h2>
                    <div>
                        <p class="-mt-[3px] font-kr text-[15px] leading-relaxed">{{ $identity['verse'] }}</p>
                        <p class="mt-2 text-[11px] font-bold tracking-[0.08em] text-accent">{{ $identity['reference'] }}</p>
                        <p class="mt-3 font-kr text-[13.5px] leading-relaxed text-navy-700">{{ $identity['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- News + latest sermon band --}}
    <section class="section-news-sermon container-site py-8 md:py-10 lg:py-14">
        <div class="grid gap-16 lg:grid-cols-[1fr_1.3fr] lg:gap-11">
            <div>
                <x-ui.kicker>교회 소식 · News</x-ui.kicker>
                <div class="mt-4">
                    @forelse ($announcements as $announcement)
                        <a href="{{ route('news.show', $announcement) }}" class="group block border-t border-line py-4 first:border-t-0 first:pt-0">
                            <p class="text-[11px] text-navy-400">
                                {{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}
                                @if ($announcement->is_pinned)
                                    <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Pinned</span>
                                @endif
                            </p>
                            <h3 class="mt-1 font-kr text-[15px] font-medium group-hover:text-accent">{{ $announcement->title }}</h3>
                        </a>
                    @empty
                        <p class="py-4 text-[13px] text-navy-400">등록된 소식이 없습니다.</p>
                    @endforelse
                </div>
                <a href="{{ route('news.index') }}" class="mt-2 inline-block text-[11px] font-bold text-accent hover:text-accent-700">소식 전체 보기 →</a>
            </div>

            @if ($latestSermon)
                <div>
                    <div class="flex items-baseline justify-between">
                        <x-ui.kicker>최근 예배 · Latest Sermon</x-ui.kicker>
                        <a href="{{ \App\Models\SiteSetting::get('youtube_url', '#') }}" target="_blank" rel="noopener" class="text-[11px] font-bold leading-none text-accent hover:text-accent-700">YouTube →</a>
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
    <section class="section-meal-sharing container-site py-8 md:py-10 lg:py-14">
        <div class="grid gap-16 lg:grid-cols-[1.3fr_1fr] lg:gap-11">
            <div class="order-2 flex items-center lg:order-1">
                @if ($mealPhoto)
                    <img src="{{ $mealPhoto->thumbnailUrl() }}" alt="함께 음식을 만들며 나누는 주는교회 식구들" class="aspect-video w-full rounded-media object-cover" loading="lazy">
                @else
                    <x-ui.photo-placeholder label="반찬나눔 · Meal Sharing" class="aspect-video w-full" />
                @endif
            </div>
            <div class="order-1 lg:order-2 lg:border-l lg:border-line lg:pl-10">
                <x-ui.kicker>반찬나눔 · Meal Sharing</x-ui.kicker>
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

    {{-- Poster band introducing the sliding gallery preview --}}
    <section class="section-moments-intro bg-navy py-12 md:py-16 lg:py-[76px]">
        <div class="container-site">
            <p class="text-kicker font-extrabold uppercase tracking-[0.16em] text-cream/55">주는교회의 순간들 · Moments</p>
            <p class="mt-5 font-kr text-display-lg text-cream">함께 예배하고, 함께 나누는<br>교회의 일상입니다.</p>
        </div>
    </section>

    {{-- Sliding gallery preview band --}}
    <section class="section-moments-slider bg-navy pb-5">
        @if ($recentPhotos->isNotEmpty())
            <div class="overflow-hidden" data-photo-slider>
                <div class="flex gap-1 transition-transform duration-700 ease-in-out" data-slider-track>
                    @foreach ($recentPhotos as $photo)
                        <a href="{{ route('gallery.show', $photo->album) }}" class="block w-[calc((100%-4px)/2)] shrink-0 overflow-hidden rounded-[10px] md:w-[calc((100%-8px)/3)] lg:w-[calc((100%-16px)/5)]">
                            <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album->title }}" class="aspect-square w-full object-cover" loading="lazy">
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="mt-5 flex items-center justify-center gap-2" data-slider-dots></div>
        @else
            <div class="grid grid-cols-1 gap-1 md:grid-cols-3">
                @foreach (['Fellowship', 'Worship', 'Next-Gen'] as $label)
                    <x-ui.photo-placeholder :label="$label" class="aspect-square rounded-[10px] bg-navy-700/60 text-cream/40" />
                @endforeach
            </div>
        @endif
    </section>

</x-layout.app>
