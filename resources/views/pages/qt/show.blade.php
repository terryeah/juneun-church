<x-layout.app :title="$devotion->title" description="주일 말씀으로 이어가는 한 주간의 큐티입니다." :noindex="true">

    <article class="section-qt-article container-site py-12 lg:py-16">
        <div class="mx-auto max-w-3xl">
            <x-ui.kicker>말씀과 함께 · QT</x-ui.kicker>
            <p class="mt-3 text-body-sm text-navy-400">{{ $devotion->devotion_date->translatedFormat('Y년 n월 j일 (D)') }}</p>
            <h1 class="mt-1 font-kr text-display-md font-medium">{{ $devotion->title }}</h1>

            @if ($devotion->sermon?->is_published)
                <p class="mt-3 font-kr text-body-sm text-navy-400">
                    주일 설교
                    <a href="{{ $devotion->sermon->youtubeUrl() }}" target="_blank" rel="noopener" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">{{ $devotion->sermon->title }}</a>
                </p>
            @endif

            <section class="mt-8 rounded-frame border-2 border-navy p-6">
                <h2 class="font-kr text-body font-bold text-navy">{{ $devotion->scripture }}</h2>
                @if (filled($devotion->passage))
                    <p class="mt-3 whitespace-pre-line font-kr text-body leading-relaxed text-navy-700">{{ $devotion->passage }}</p>
                @endif
            </section>

            <div class="prose-announcement mt-8 font-kr text-body leading-relaxed text-navy-700 [&_a]:text-accent [&_a]:underline [&_h2]:mt-6 [&_h2]:font-medium [&_h2]:text-navy [&_h3]:mt-4 [&_h3]:font-medium [&_h3]:text-navy [&_p]:mt-4 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_li_p]:mt-1">
                {!! \Mews\Purifier\Facades\Purifier::clean($devotion->content) !!}
            </div>

            <form method="POST" action="{{ route('qt.note', $devotion) }}" class="mt-10 border-t-2 border-navy pt-6">
                @csrf
                <label for="body" class="block font-kr text-body font-bold text-navy">나의 묵상</label>
                <p class="mt-1 font-kr text-body-sm text-navy-400">본인만 볼 수 있습니다. 목회자도 볼 수 없습니다.</p>
                <textarea id="body" name="body" rows="10" maxlength="20000"
                          class="mt-3 block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body leading-relaxed text-navy placeholder:text-navy-400 focus:border-navy focus:outline-none"
                          placeholder="오늘 말씀을 통해 받은 은혜, 결단, 기도를 적어 보세요.">{{ old('body', $note?->body) }}</textarea>
                @error('body')<p class="mt-1.5 font-kr text-body-sm text-accent">{{ $message }}</p>@enderror

                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                        <span class="font-kr">저장</span>
                    </button>
                    @if (session('status'))
                        <p class="font-kr text-body-sm font-bold text-navy" role="status">{{ session('status') }}</p>
                    @elseif ($note)
                        <p class="font-kr text-body-sm text-navy-400">{{ $note->updated_at->translatedFormat('n월 j일 H:i') }}에 저장됨</p>
                    @endif
                </div>
            </form>

            <nav class="mt-10 flex flex-wrap justify-between gap-4 font-kr text-caption font-bold" aria-label="다른 날 큐티">
                @if ($previous)
                    <a href="{{ route('qt.show', $previous) }}" class="text-accent hover:text-accent-700">← {{ $previous->devotion_date->translatedFormat('n월 j일') }} {{ $previous->title }}</a>
                @else
                    <a href="{{ route('qt.index') }}" class="text-accent hover:text-accent-700">← 큐티 전체 보기</a>
                @endif
                @if ($next)
                    <a href="{{ route('qt.show', $next) }}" class="text-accent hover:text-accent-700">{{ $next->devotion_date->translatedFormat('n월 j일') }} {{ $next->title }} →</a>
                @else
                    <a href="{{ route('qt.notes') }}" class="text-accent hover:text-accent-700">내 묵상 노트 →</a>
                @endif
            </nav>
        </div>
    </article>

</x-layout.app>
