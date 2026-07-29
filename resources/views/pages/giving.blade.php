@php
    use App\Models\SiteSetting;
@endphp

<x-layout.app title="온라인헌금" description="브리즈번 주는교회 온라인헌금 계좌 안내입니다.">

    <x-ui.page-header kicker="온라인헌금 · Giving" title="온라인헌금">
        받은 은혜에 감사하며 드리는 헌금은 교회의 사역과 이웃 나눔에 소중히 사용됩니다.
    </x-ui.page-header>

    <section class="section-giving-details container-site pb-12 lg:pb-16">
        <div class="max-w-lg rounded-frame border-2 border-navy bg-paper p-8">
            <x-ui.kicker>계좌 이체 · Bank Transfer</x-ui.kicker>
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
            <p class="mt-6 font-kr text-[12.5px] leading-relaxed text-navy-400">
                이체 시 참조란에 이름과 헌금 종류(십일조, 감사, 선교 등)를 적어 주세요.
            </p>
        </div>
    </section>

</x-layout.app>
