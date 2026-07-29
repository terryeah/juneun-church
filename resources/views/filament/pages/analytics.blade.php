<x-filament-panels::page>
    @php
        $breakdownGroups = [
            'country' => '국가',
            'path' => '페이지',
            'referer' => '유입 경로',
            'host' => '호스트',
            'browser' => '브라우저',
            'os' => '운영체제',
            'device' => '기기 유형',
        ];
        $deviceLabels = ['desktop' => '데스크탑', 'mobile' => '모바일', 'tablet' => '태블릿'];
    @endphp

    @if (filled($this->breakdowns))
        <x-filament::section>
            <x-slot name="heading">페이지뷰 분석 · 최근 30일</x-slot>

            {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
            <style>
                .an-breakdowns { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; }
                @media (min-width: 768px) { .an-breakdowns { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
            </style>
            <div class="an-breakdowns">
                @foreach ($breakdownGroups as $key => $title)
                    @php
                        $rows = $this->breakdowns[$key] ?? [];
                        $max = max(array_column($rows, 'count') ?: [1]);
                    @endphp

                    @if (filled($rows))
                        <div>
                            <p style="margin-bottom: 0.75rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178);">{{ $title }}</p>

                            @foreach ($rows as $row)
                                @php
                                    $label = $row['label'] === '' ? '직접 방문' : ($key === 'device' ? ($deviceLabels[$row['label']] ?? $row['label']) : $row['label']);
                                @endphp
                                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.3rem 0; font-size: 0.875rem;">
                                    <span style="flex: 1 1 0; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $label }}</span>
                                    <span style="font-variant-numeric: tabular-nums; color: rgb(148 158 178);">{{ number_format($row['count']) }}</span>
                                    <span style="flex: 0 0 90px; height: 8px; border-radius: 9999px; background: rgba(128, 138, 160, 0.2); overflow: hidden;">
                                        <span style="display: block; height: 100%; width: {{ round($row['count'] / $max * 100) }}%; border-radius: 9999px; background: rgb(59 130 246);"></span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </x-filament::section>
    @endif

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
