<x-layout.app description="브리즈번 주는교회 - 함께 예배하고, 넉넉히 나누며, 예수 그리스도를 따라가는 젊은 한인교회입니다." :image="$heroPhoto?->url()">

    {{-- Hero --}}
    <section class="section-hero container-site py-8 md:py-10 lg:py-14">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:gap-11">
            <div class="lg:flex-[1.05]">
                <x-ui.kicker data-hero-item>Brisbane Juneun Church · Since 2024</x-ui.kicker>
                <h1 class="mt-4 font-kr text-display-lg" data-hero-item>받은 은혜를<br>흘려보내는 교회</h1>
                <p lang="en" class="mt-5 max-w-[25rem] text-body-lg leading-relaxed text-navy-700" data-hero-item>
                    A young Korean church in Brisbane - worshipping together, giving generously, and growing as followers of Jesus Christ.
                </p>
                <div class="mt-7 flex flex-wrap gap-3" data-hero-item>
                    <x-ui.button :href="route('worship')">예배 안내 →</x-ui.button>
                    <x-ui.button :href="route('location')" variant="secondary">오시는 길</x-ui.button>
                </div>
            </div>
            <div class="lg:flex-1">
                <div data-zoom class="overflow-hidden rounded-media">
                    @if ($heroPhoto)
                        <img src="{{ $heroPhoto->thumbnailUrl() }}" alt="함께 예배하는 주는교회 성도들" class="aspect-[4/3] w-full object-cover" fetchpriority="high" decoding="async">
                    @else
                        <x-ui.photo-placeholder label="Worship · Congregation" class="aspect-[4/3]" />
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Value rows: the three 주는교회 identities --}}
    <section class="section-identity container-site py-8 md:py-10 lg:py-14">
        @php
            $identities = [
                [
                    'korean' => '주는', 'english' => 'Lord', 'suffix' => '교회',
                    'verse' => '주는 그리스도시요 살아계신 하나님의 아들이시니이다',
                    'reference' => '마태복음 16:16',
                    'description' => '귀한 믿음의 고백 위에 세워진 교회입니다.',
                ],
                [
                    'korean' => '보여', 'english' => 'Revealing', 'suffix' => '주는교회',
                    'verse' => '너희가 서로 사랑하면 이로써 모든 사람이 너희가 내 제자인 줄 알리라',
                    'reference' => '요한복음 13:35',
                    'description' => '간판이 아닌 모인 이들을 통해 교회라 인정받는 교회입니다.',
                ],
                [
                    'korean' => '주는', 'english' => 'Giving', 'suffix' => '교회',
                    'verse' => '예수께서 친히 말씀하신 바 주는 것이 받는 것보다 복이 있다 하심을 기억하여야 할지니라',
                    'reference' => '사도행전 20:35',
                    'description' => '우리에게 일용할 양식을 주심을 위해 기도하며 이웃의 부족함을 채워주는 교회입니다.',
                ],
            ];
        @endphp

        <div class="divide-y divide-line">
            @foreach ($identities as $identity)
                <div class="grid gap-4 py-[1.625rem] first:pt-0 last:pb-0 md:grid-cols-[minmax(0,280px)_minmax(0,1fr)] md:gap-10">
                    <h2 class="font-kr text-display-sm font-medium">
                        {{ $identity['korean'] }}<span class="text-accent">({{ $identity['english'] }})</span>{{ $identity['suffix'] }}
                    </h2>
                    <div>
                        <p class="-mt-[0.1875rem] font-kr text-body leading-relaxed">{{ $identity['verse'] }}</p>
                        <p class="mt-2 text-caption font-bold tracking-[0.08em] text-accent">{{ $identity['reference'] }}</p>
                        <p class="mt-3 font-kr text-body-sm leading-relaxed text-navy-700">{{ $identity['description'] }}</p>
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
                            <p class="text-caption text-navy-400">
                                {{ $announcement->published_at?->translatedFormat('Y년 n월 j일') }}
                                @if ($announcement->is_pinned)
                                    <span class="ml-2 font-extrabold uppercase tracking-[0.16em] text-accent">Pinned</span>
                                @endif
                            </p>
                            <h3 class="mt-1 font-kr text-body font-medium group-hover:text-accent">{{ $announcement->title }}</h3>
                        </a>
                    @empty
                        <p class="py-4 text-body-sm text-navy-400">등록된 소식이 없습니다.</p>
                    @endforelse
                </div>
                <a href="{{ route('news.index') }}" class="mt-2 inline-block text-caption font-bold text-accent hover:text-accent-700">소식 전체 보기 →</a>
            </div>

            @if ($latestSermon)
                <div>
                    <div class="flex items-baseline justify-between">
                        <x-ui.kicker>최근 예배 · Latest Sermon</x-ui.kicker>
                        <a href="{{ \App\Models\SiteSetting::get('youtube_url', '#') }}" target="_blank" rel="noopener" class="text-caption font-bold leading-none text-accent hover:text-accent-700">YouTube →<span class="sr-only"> (새 창)</span></a>
                    </div>
                    <div class="mt-4">
                        <x-ui.sermon-video :sermon="$latestSermon" />
                    </div>
                    <h3 class="mt-4 font-kr text-display-sm font-medium">{{ $latestSermon->title }}</h3>
                    <p class="mt-1 text-body-sm text-navy-400">
                        {{ $latestSermon->preacher }} · {{ $latestSermon->sermon_date->translatedFormat('Y년 n월 j일') }}
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Highlight: the 교회 소식 flagged as the church's current featured happening --}}
    @if ($highlight)
        <section class="section-highlight container-site py-8 md:py-10 lg:py-14">
            @php $highlightLink = route('news.show', $highlight); @endphp
            {{-- Without a 대표 이미지 the copy simply runs full width; an
                 empty picture frame would read as a fault rather than a gap. --}}
            <div @class(['grid gap-8 lg:gap-11', 'lg:grid-cols-[1.3fr_1fr]' => $highlight->featured_image])>
                @if ($highlight->featured_image)
                    <div class="order-2 flex items-center lg:order-1">
                        <a href="{{ $highlightLink }}" class="block w-full overflow-hidden rounded-media">
                            <img src="{{ Illuminate\Support\Facades\Storage::disk(config('filesystems.media'))->url($highlight->featured_image) }}" alt="{{ $highlight->title }}" class="aspect-video w-full object-cover" loading="lazy">
                        </a>
                    </div>
                @endif
                <div @class(['order-1 lg:order-2', 'lg:border-l lg:border-line lg:pl-10' => $highlight->featured_image])>
                    <x-ui.kicker>하이라이트 · Highlight</x-ui.kicker>
                    <a href="{{ $highlightLink }}" class="block"><h2 class="mt-3 font-kr text-display-md font-medium leading-snug transition-colors duration-300 hover:text-accent">{{ $highlight->title }}</h2></a>
                    <p class="mt-4 font-kr text-body-sm leading-relaxed text-navy-700">{{ $highlight->excerpt(280) }}</p>
                    <a href="{{ $highlightLink }}" class="mt-6 inline-block text-caption font-bold text-accent hover:text-accent-700">자세히 보기 →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Poster band and its sliding gallery preview. They are one section
         so the reveal animation moves the whole navy band together; split
         across two sections, each got its own trigger and the page
         background showed through the gap between them. --}}
    <section class="section-moments bg-navy pb-5">
        <div class="section-moments-intro container-site py-12 md:py-16 lg:py-[4.75rem]">
            <x-ui.kicker color="text-cream/55">주는교회의 순간들 · Moments</x-ui.kicker>
            <p class="mt-5 font-kr text-display-lg text-cream">함께 예배하고, 함께 나누는<br>교회의 일상입니다.</p>
        </div>

        @if ($recentPhotos->isNotEmpty())
            @php
                /**
                 * Only the cards a first view can show ship with a resolvable
                 * src. The widest breakpoint puts 4.2 of them on screen (see
                 * --slides on .moments-track), so four cover it. The rest ride
                 * along inside an inert <template>: the browser parses that
                 * markup but never fetches its photographs, which keeps 580 KiB
                 * of below-the-fold images off the connection while the hero
                 * loads. PhotoSlider moves them into the track once the page
                 * has finished loading. Without JavaScript the band is those
                 * four photos, each still linking through to its album.
                 */
                $deferredPhotos = $recentPhotos->slice(4);
            @endphp
            <div data-photo-slider>
                <div class="moments-track flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pe-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" data-slider-track tabindex="0" aria-label="교회 사진 모음">
                    @foreach ($recentPhotos->take(4) as $photo)
                        <a href="{{ route('album.show', $photo->album) }}" class="block overflow-hidden rounded-[1.35rem]">
                            <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album->title }}" class="aspect-square w-full object-cover" loading="lazy">
                        </a>
                    @endforeach
                </div>
                @if ($deferredPhotos->isNotEmpty())
                    <template data-slider-deferred>
                        @foreach ($deferredPhotos as $photo)
                            <a href="{{ route('album.show', $photo->album) }}" class="block overflow-hidden rounded-[1.35rem]">
                                <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album->title }}" class="aspect-square w-full object-cover" loading="lazy">
                            </a>
                        @endforeach
                    </template>
                @endif
                <div class="container-site mt-6 flex items-center justify-end gap-3">
                    <button type="button" data-slider-prev aria-label="이전 사진" class="flex h-9 w-9 items-center justify-center rounded-full bg-cream/10 text-cream transition-colors hover:bg-cream/20 disabled:pointer-events-none disabled:opacity-30">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 5.5 8 12l6.5 6.5"/></svg>
                    </button>
                    <button type="button" data-slider-next aria-label="다음 사진" class="flex h-9 w-9 items-center justify-center rounded-full bg-cream/10 text-cream transition-colors hover:bg-cream/20 disabled:pointer-events-none disabled:opacity-30">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 5.5 16 12l-6.5 6.5"/></svg>
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-1 md:grid-cols-3">
                @foreach (['Fellowship', 'Worship', 'Next-Gen'] as $label)
                    <x-ui.photo-placeholder :label="$label" class="aspect-square rounded-[0.625rem] bg-navy-700/60 text-cream/55" />
                @endforeach
            </div>
        @endif
    </section>

</x-layout.app>
