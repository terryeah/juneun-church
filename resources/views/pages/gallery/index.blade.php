<x-layout.app title="갤러리" description="브리즈번 주는교회의 활동 사진 앨범입니다.">

    <x-ui.page-header kicker="주는교회의 순간들 · Moments" title="갤러리" />

    <section class="section-gallery-albums container-site pb-12 lg:pb-16">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($albums as $album)
                <a href="{{ route('gallery.show', $album) }}" class="group block">
                    @if ($album->coverUrl())
                        <div class="overflow-hidden rounded-media"><img src="{{ $album->coverUrl() }}" alt="{{ $album->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy"></div>
                    @else
                        <x-ui.photo-placeholder :label="$album->title" class="aspect-[4/3] w-full" />
                    @endif
                    <h2 class="mt-3 font-kr text-body font-medium group-hover:text-accent">{{ $album->title }}</h2>
                    <p class="mt-1 text-body-sm text-navy-400">
                        @if ($album->event_date){{ $album->event_date->translatedFormat('Y년 n월 j일') }} · @endif사진 {{ $album->photos_count }}장
                    </p>
                </a>
            @empty
                <p class="text-body-sm text-navy-400">등록된 앨범이 없습니다.</p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $albums->links() }}
        </div>
    </section>

</x-layout.app>
