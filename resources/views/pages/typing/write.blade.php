@php
    use App\Models\BibleVerse;
@endphp

<x-layout.app :title="BibleVerse::BOOKS[$book].' 쓰기'" description="개역한글 성경을 한 절씩 직접 써 내려가는 성경 타자입니다." :noindex="true">

    <section class="section-typing-write container-site py-12 lg:py-16">
        <div class="mx-auto max-w-3xl">
            <x-ui.kicker>말씀 쓰기 · Bible Typing</x-ui.kicker>
            <div class="mt-3 flex flex-wrap items-baseline justify-between gap-3">
                <h1 class="font-kr text-display-md font-medium">{{ BibleVerse::BOOKS[$book] }}</h1>
                <p class="font-kr text-body-sm text-navy-400">{{ number_format($progress['done']) }} / {{ number_format($progress['total']) }}절</p>
            </div>

            @if (session('status'))
                <p class="mt-3 font-kr text-body-sm font-bold text-accent" role="status">✓ {{ session('status') }}</p>
            @endif

            @if ($verse)
                <section class="mt-8 rounded-frame border-2 border-navy p-6">
                    <h2 class="font-kr text-body font-bold text-navy">{{ $verse->reference() }}</h2>
                    <p class="mt-3 font-kr text-body-lg leading-relaxed text-navy-700" lang="ko">{{ $verse->text }}</p>
                </section>

                <form method="POST" action="{{ route('typing.submit') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="verse_id" value="{{ $verse->id }}">
                    <label for="body" class="sr-only">{{ $verse->reference() }} 따라 쓰기</label>
                    <textarea id="body" name="body" rows="4" required maxlength="2000" autofocus data-typing-pad
                              class="block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body-lg leading-relaxed text-navy placeholder:text-navy-400 focus:border-navy focus:outline-none"
                              placeholder="위 말씀을 그대로 쳐 주세요.">{{ old('body') }}</textarea>
                    @error('body')<p class="mt-1.5 font-kr text-body-sm text-accent">{{ $message }}</p>@enderror

                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                            <span class="font-kr">다 썼어요</span>
                        </button>
                        <a href="{{ route('typing.index') }}" class="font-kr text-body-sm font-bold text-accent hover:text-accent-700">기록과 순위 보기 →</a>
                    </div>
                </form>
            @else
                <p class="mt-8 font-kr text-body leading-relaxed text-navy-700">{{ BibleVerse::BOOKS[$book] }}을 다 쓰셨습니다. 수고하셨습니다!</p>
                <div class="mt-6">
                    <x-ui.button :href="route('typing.index')"><span class="font-kr">다른 책 고르기</span></x-ui.button>
                </div>
            @endif
        </div>
    </section>

</x-layout.app>
