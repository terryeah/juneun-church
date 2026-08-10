@php
    use App\Models\SiteSetting;
@endphp

<x-layout.app title="헌금" description="브리즈번 주는교회 헌금 계좌 안내입니다.">

    <x-ui.page-header kicker="은혜를 흘려보내는 · Giving" title="헌금">
        받은 은혜에 감사하며 드리는 헌금은 교회의 사역과 이웃 나눔에 소중히 사용됩니다.
    </x-ui.page-header>

    <section class="section-giving-details container-site pb-12 lg:pb-16">
        <div class="grid gap-6 md:grid-cols-2 md:gap-8 lg:gap-11">
            <div class="rounded-frame border-2 border-navy bg-paper p-8">
                <x-ui.kicker>호주 계좌 · Australia</x-ui.kicker>
                <dl class="mt-5 space-y-4">
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">Bank</dt>
                        <dd class="min-w-0 text-right text-body font-bold">{{ SiteSetting::get('giving_bank') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">Account Name</dt>
                        <dd class="min-w-0 text-right text-body font-bold">{{ SiteSetting::get('giving_account_name') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">BSB</dt>
                        <dd class="min-w-0 text-right text-body font-bold tabular-nums">{{ SiteSetting::get('giving_bsb') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">Account Number</dt>
                        <dd class="min-w-0 text-right text-body font-bold tabular-nums">{{ SiteSetting::get('giving_account_number') }}</dd>
                    </div>
                </dl>
            </div>

            @if (SiteSetting::get('giving_kr_account_number'))
                <div class="rounded-frame border-2 border-navy bg-paper p-8">
                    <x-ui.kicker>한국 계좌 · Korea</x-ui.kicker>
                    <dl class="mt-5 space-y-4">
                        <div class="flex items-baseline justify-between border-b border-line pb-3">
                            <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">은행</dt>
                            <dd class="min-w-0 text-right font-kr text-body font-medium">{{ SiteSetting::get('giving_kr_bank') }}</dd>
                        </div>
                        @if (SiteSetting::get('giving_kr_account_name'))
                            <div class="flex items-baseline justify-between border-b border-line pb-3">
                                <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">예금주</dt>
                                <dd class="min-w-0 text-right font-kr text-body font-medium">{{ SiteSetting::get('giving_kr_account_name') }}</dd>
                            </div>
                        @endif
                        <div class="flex items-baseline justify-between">
                            <dt class="text-body-sm uppercase tracking-[0.16em] text-navy-400">계좌번호</dt>
                            <dd class="min-w-0 text-right text-body font-bold tabular-nums">{{ SiteSetting::get('giving_kr_account_number') }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        <div class="mt-6 font-kr text-body-sm leading-relaxed text-navy-400">
            <p>이체 시 참조란에 이름과 헌금 종류를 약자로 함께 적어주세요.</p>
            <ul class="mt-2 space-y-1 md:mt-1 md:flex md:flex-wrap md:gap-x-5 md:space-y-0">
                <li>주일헌금 <span class="font-bold">O</span></li>
                <li>십일조 <span class="font-bold">T</span></li>
                <li>감사헌금 <span class="font-bold">TH</span></li>
                <li>선교헌금 <span class="font-bold">M</span></li>
                <li>기타헌금 <span class="font-bold">E</span></li>
            </ul>
        </div>
    </section>

    {{-- The bulletin only reaches the congregation, so the records stay
         with the 성도 on the 교적 rather than with anyone who signed up. --}}
    @if (auth()->user()?->isChurchMember())
    @if ($offering)
        <section class="section-giving-records container-site pb-12 lg:pb-16" data-giving-weeks>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.kicker>헌금 소식 · Records</x-ui.kicker>
                {{-- Matches the green 사용 중 badge in 관리자 페이지: slate pill, 1px success border, success label. --}}
                <span class="inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 font-kr text-xs font-medium text-success">성도 전용</span>
            </div>
            <h2 class="mt-3 font-kr text-display-sm font-medium">{{ $offering->sunday_date->translatedFormat('Y년 n월 j일') }} 주일 헌금 내역</h2>
            <p class="mt-2 font-kr text-body-sm text-navy-400">주보에 실리는 내용과 동일합니다. 함께 드린 손길에 감사드립니다.</p>

            <div class="mt-6 grid gap-8 md:grid-cols-2">
                @foreach ($offering->groupedItems() as $category => $items)
                    <div data-giving-category>
                        <h3 class="border-b-2 border-navy pb-2 font-kr text-body font-bold">{{ $category }}</h3>
                        <ul class="mt-1">
                            @foreach ($items as $item)
                                <li class="flex items-baseline justify-between gap-4 border-b border-line py-2.5">
                                    <span class="font-kr text-body-sm">{{ $item['name'] ?? '' }}</span>
                                    @if (filled($item['amount'] ?? null))
                                        <span class="text-body-sm font-bold tabular-nums">${{ number_format((float) $item['amount'], 2) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @if ($offering->total() > 0)
                <p class="mt-6 text-right font-kr text-body font-bold">합계 <span class="tabular-nums">${{ number_format($offering->total(), 2) }}</span></p>
            @endif
            @if ($offering->note)
                <p class="mt-2 text-right font-kr text-body-sm text-navy-400">{{ $offering->note }}</p>
            @endif

            @if ($weeks->count() > 1)
                <div class="mt-8">
                    <h3 class="text-caption font-extrabold uppercase tracking-[0.16em] text-navy-400">지난 주일 보기</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($weeks as $week)
                            <a href="{{ route('giving', ['week' => $week->sunday_date->toDateString()]) }}"
                               class="rounded-nav px-3 py-1.5 font-kr text-body-sm transition-colors {{ $week->is($offering) ? 'bg-navy text-cream' : 'bg-navy/5 text-navy hover:bg-navy/10' }}"
                               data-giving-week>
                                {{ $week->sunday_date->translatedFormat('n월 j일') }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif
    @endif

    {{-- Anyone who cannot see the records is told why, rather than being
         left with a page that simply ends early. That now includes a
         signed-in 일반회원, who is not on the 교적. --}}
    @unless (auth()->user()?->isChurchMember())
        <x-ui.sign-in-required
            class="section-giving-signup"
            body="주보에 실리는 주일 헌금 내역은 성도에게만 공개됩니다."
        />
    @endunless

</x-layout.app>
