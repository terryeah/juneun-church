<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
    <style>
        .an-breakdowns { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; }
        @media (min-width: 768px) { .an-breakdowns { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .an-range { display: block; width: 100%; margin-bottom: 1.5rem; border-radius: 0.5rem; padding: 0.375rem 2rem 0.375rem 0.75rem; font-size: 0.875rem; }
        @media (min-width: 640px) { .an-range { width: auto; min-width: 12rem; } }
    </style>

    @if ($this->dailySnapshots->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">일별 상세</x-slot>

            {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; font-variant-numeric: tabular-nums;">
                    <thead>
                        <tr>
                            <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); border-bottom: 1px solid rgba(128, 138, 160, 0.3);">날짜</th>
                            <th style="padding: 0.625rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); border-bottom: 1px solid rgba(128, 138, 160, 0.3);">실방문자</th>
                            <th style="padding: 0.625rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); border-bottom: 1px solid rgba(128, 138, 160, 0.3);">페이지뷰</th>
                            <th style="padding: 0.625rem 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); border-bottom: 1px solid rgba(128, 138, 160, 0.3);">총 요청 (봇 포함)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->dailySnapshots as $snapshot)
                            <tr>
                                <td style="padding: 0.625rem 1rem; text-align: left; border-bottom: 1px solid rgba(128, 138, 160, 0.15); white-space: nowrap;">{{ $snapshot->snapshot_date->format('Y-m-d') }}</td>
                                <td style="padding: 0.625rem 1rem; text-align: right; border-bottom: 1px solid rgba(128, 138, 160, 0.15); font-weight: 600;">{{ number_format($snapshot->unique_visitors) }}</td>
                                <td style="padding: 0.625rem 1rem; text-align: right; border-bottom: 1px solid rgba(128, 138, 160, 0.15);">{{ number_format($snapshot->page_views) }}</td>
                                <td style="padding: 0.625rem 1rem; text-align: right; border-bottom: 1px solid rgba(128, 138, 160, 0.15); color: rgb(148 158 178);">{{ number_format($snapshot->requests) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    @if ($this->isConfigured)
        <x-filament::section>
            <x-slot name="heading">페이지뷰 분석 · 실방문자 · {{ $this->rangeOptions[$this->visitorRange] }}</x-slot>
            <x-slot name="description">알려진 봇을 제외한 방문 기준입니다. 실제 브라우저로 위장한 해외 크롤러는 무료 플랜에서 걸러지지 않으므로, 1페이지만 보고 나가는 해외 트래픽은 크롤러일 가능성이 큽니다.</x-slot>
            <select wire:model.live="visitorRange" class="fi-input fi-select-input an-range">
                @foreach ($this->rangeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if (filled($this->breakdowns))
                @include('filament.pages.partials.breakdowns', ['data' => $this->breakdowns])
            @else
                <p class="fi-color-gray" style="font-size: 0.875rem;">선택한 기간에 집계된 방문이 아직 없습니다.</p>
            @endif
        </x-filament::section>
    @endif

    @if ($this->isDeveloper && filled($this->botBreakdowns))
        <x-filament::section>
            <x-slot name="heading">요청 분석 · 봇 포함 · 최근 24시간</x-slot>
            <x-slot name="description">크롤러와 봇을 포함한 전체 HTTP 요청 기준입니다. 무료 플랜에서는 상세 분석이 24시간으로 제한됩니다.</x-slot>

            @include('filament.pages.partials.breakdowns', ['data' => $this->botBreakdowns])
        </x-filament::section>
    @endif

    @unless ($this->isConfigured)
        <x-filament::section>
            <x-slot name="heading">Cloudflare 연동이 아직 설정되지 않았습니다</x-slot>

            <p class="text-sm">
                도메인을 Cloudflare에 연결한 뒤 <code>.env</code>에
                <code>CLOUDFLARE_API_TOKEN</code>(Zone Analytics:Read 권한)과
                <code>CLOUDFLARE_ZONE_ID</code>를 설정하면 자동으로 데이터가 수집됩니다.
                수집은 매일 새벽 3시에 실행되며, 우측 상단의 "지금 동기화" 버튼으로 즉시 가져올 수도 있습니다.
            </p>
        </x-filament::section>
    @endunless
</x-filament-panels::page>
