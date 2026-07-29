@php
    use App\Models\SiteSetting;
@endphp

<x-layout.app title="온라인 헌금" description="브리즈번 주는교회 온라인 헌금 계좌 안내입니다.">

    <x-ui.page-header kicker="온라인 헌금 · Giving" title="온라인 헌금">
        받은 은혜에 감사하며 드리는 헌금은 교회의 사역과 이웃 나눔에 소중히 사용됩니다.
    </x-ui.page-header>

    <section class="section-giving-details container-site pb-12 lg:pb-16">
        <div class="grid max-w-4xl gap-6 md:grid-cols-2 md:items-start">
            <div class="rounded-frame border-2 border-navy bg-paper p-8">
                <x-ui.kicker>호주 계좌 · Australia</x-ui.kicker>
                <dl class="mt-5 space-y-4">
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">Bank</dt>
                        <dd class="min-w-0 text-right text-[15px] font-bold">{{ SiteSetting::get('giving_bank') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">Account Name</dt>
                        <dd class="min-w-0 text-right text-[15px] font-bold">{{ SiteSetting::get('giving_account_name') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-line pb-3">
                        <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">BSB</dt>
                        <dd class="min-w-0 text-right text-[15px] font-bold tabular-nums">{{ SiteSetting::get('giving_bsb') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">Account Number</dt>
                        <dd class="min-w-0 text-right text-[15px] font-bold tabular-nums">{{ SiteSetting::get('giving_account_number') }}</dd>
                    </div>
                </dl>
            </div>

            @if (SiteSetting::get('giving_kr_account_number'))
                <div class="rounded-frame border-2 border-navy bg-paper p-8">
                    <x-ui.kicker>한국 계좌 · Korea</x-ui.kicker>
                    <dl class="mt-5 space-y-4">
                        <div class="flex items-baseline justify-between border-b border-line pb-3">
                            <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">은행</dt>
                            <dd class="min-w-0 text-right font-kr text-[15px] font-medium">{{ SiteSetting::get('giving_kr_bank') }}</dd>
                        </div>
                        @if (SiteSetting::get('giving_kr_account_name'))
                            <div class="flex items-baseline justify-between border-b border-line pb-3">
                                <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">예금주</dt>
                                <dd class="min-w-0 text-right font-kr text-[15px] font-medium">{{ SiteSetting::get('giving_kr_account_name') }}</dd>
                            </div>
                        @endif
                        <div class="flex items-baseline justify-between">
                            <dt class="text-[12px] uppercase tracking-[0.16em] text-navy-400">계좌번호</dt>
                            <dd class="min-w-0 text-right text-[15px] font-bold tabular-nums">{{ SiteSetting::get('giving_kr_account_number') }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        <p class="mt-6 max-w-4xl font-kr text-[12.5px] leading-relaxed text-navy-400">
            이체 시 참조란에 이름과 헌금 종류를 약자로 함께 적어 주세요 -
            주일헌금 O · 십일조 T · 감사헌금 TH · 선교헌금 M · 기타헌금 E
        </p>
    </section>

</x-layout.app>
