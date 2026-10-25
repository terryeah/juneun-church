@php
    $inputClass = 'mt-2 block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body leading-relaxed text-navy placeholder:text-navy-400 focus:border-navy focus:outline-none';
@endphp

<x-layout.app title="기도제목" description="성도들이 서로의 기도제목을 나누는 자리입니다." :noindex="true">

    <x-ui.page-header kicker="함께 기도 · Prayer" title="기도제목">
        셀과 교회가 서로의 기도제목을 두고 함께 기도합니다. 목회자에게만 전하는 기도제목은 목회자 외에는 아무도 보지 않습니다.
    </x-ui.page-header>

    <section class="section-prayer-form container-site pb-10">
        <form method="POST" action="{{ route('prayer.store') }}" class="rounded-frame border-2 border-navy bg-paper p-6 lg:p-8">
            @csrf
            <label for="body" class="block font-kr text-body font-bold text-navy">기도제목 나누기</label>
            <textarea id="body" name="body" rows="4" required maxlength="2000" class="{{ $inputClass }}"
                      placeholder="함께 기도해 주셨으면 하는 제목을 적어 주세요.">{{ old('body') }}</textarea>
            @error('body')<p class="mt-1.5 font-kr text-body-sm text-accent">{{ $message }}</p>@enderror

            <fieldset class="mt-4">
                <legend class="font-kr text-body-sm font-bold text-navy">누구와 나눌까요?</legend>
                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2">
                    @foreach (\App\Models\PrayerRequest::SCOPES as $value => $label)
                        <label class="flex items-center gap-2 font-kr text-body-sm text-navy">
                            <input type="radio" name="scope" value="{{ $value }}" class="size-4 border-2 border-line text-accent focus:border-navy"
                                   @checked(old('scope', $cellId ? 'cell' : 'church') === $value)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('scope')<p class="mt-1.5 font-kr text-body-sm text-accent">{{ $message }}</p>@enderror
            </fieldset>

            <div class="mt-5 flex flex-wrap items-center gap-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                    <span class="font-kr">나누기</span>
                </button>
                @if (session('status'))
                    <p class="font-kr text-body-sm font-bold text-navy" role="status">{{ session('status') }}</p>
                @endif
            </div>
        </form>
    </section>

    {{-- The whole section swaps on a tab click, the way 자료실 does. --}}
    <section class="section-prayer-list container-site pb-12 lg:pb-16" data-prayer>
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('prayer.index', ['tab' => $key]) }}"
                   class="rounded-nav px-4 py-2 font-kr text-body-sm transition-colors {{ $key === $tab ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                   @if ($key === $tab) aria-current="page" @endif
                   data-prayer-tab>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-6 border-t-2 border-navy">
            @forelse ($requests as $prayer)
                <article class="border-b border-line py-5" data-prayer-item>
                    <p class="text-caption text-navy-400">
                        @if ($tab !== 'mine')
                            <span class="font-kr font-bold text-navy">{{ $prayer->user->name }}</span>
                            @if ($prayer->scope === 'cell' && $prayer->user->member?->cell)
                                · {{ $prayer->user->member->cell->displayName() }}
                            @endif
                            ·
                        @else
                            <span class="font-kr">{{ \App\Models\PrayerRequest::SCOPES[$prayer->scope] }}</span> ·
                        @endif
                        {{ $prayer->created_at->translatedFormat('n월 j일') }}
                        @if ($prayer->is_answered)
                            <span class="ml-2 font-kr font-bold text-accent">응답받음</span>
                        @endif
                    </p>
                    <p class="mt-2 whitespace-pre-line font-kr text-body leading-relaxed text-navy-700">{{ $prayer->body }}</p>

                    @if ($prayer->canBeRemovedBy(auth()->user()))
                        <div class="mt-3 flex flex-wrap gap-4 font-kr text-caption font-bold">
                            @if ($prayer->user_id === auth()->id())
                                <form method="POST" action="{{ route('prayer.answered', $prayer) }}">
                                    @csrf
                                    <button type="submit" class="text-accent hover:text-accent-700">{{ $prayer->is_answered ? '응답 표시 지우기' : '응답받았어요' }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('prayer.destroy', $prayer) }}" onsubmit="return confirm('이 기도제목을 지울까요?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-navy-400 hover:text-navy">지우기</button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <p class="py-8 font-kr text-body-sm text-navy-400">
                    @if ($tab === 'cell' && ! $cellId && ! auth()->user()->isPastor())
                        아직 셀에 속해 있지 않습니다. 셀 배정은 교회 사무실에 문의해 주세요.
                    @else
                        아직 나눈 기도제목이 없습니다.
                    @endif
                </p>
            @endforelse
        </div>
        <div class="mt-8">
            {{ $requests->links() }}
        </div>
    </section>

</x-layout.app>
