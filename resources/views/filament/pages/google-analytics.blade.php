<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
    <style>
        .an-breakdowns { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; }
        @media (min-width: 48rem) { .an-breakdowns { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .an-range { display: block; width: 100%; margin-bottom: 1.5rem; border-radius: 0.5rem; padding: 0.375rem 2rem 0.375rem 0.75rem; font-size: 0.875rem; }
        @media (min-width: 40rem) { .an-range { width: auto; min-width: 12rem; } }
        .ga-totals { display: flex; flex-wrap: wrap; gap: 2.5rem; margin-bottom: 1.5rem; }
        .ga-total-label { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); }
        .ga-total-value { font-size: 1.75rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .ga-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; font-variant-numeric: tabular-nums; }
        .ga-table th { padding: 0.625rem 1rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgb(148 158 178); border-bottom: 1px solid rgba(128, 138, 160, 0.3); }
        .ga-table td { padding: 0.625rem 1rem; border-bottom: 1px solid rgba(128, 138, 160, 0.15); }
        .ga-table th:first-child, .ga-table td:first-child { text-align: left; white-space: nowrap; }
        .ga-table th:not(:first-child), .ga-table td:not(:first-child) { text-align: right; }
    </style>

    @if ($this->isConfigured)
        <x-filament::section>
            <x-slot name="heading">방문 통계 · {{ $this->rangeOptions[$this->range] }}</x-slot>
            <x-slot name="description">구글 애널리틱스 4 기준입니다. 태그를 실행하는 브라우저만 집계되므로 대부분의 크롤러는 포함되지 않습니다.</x-slot>

            <select wire:model.live="range" class="fi-input fi-select-input an-range">
                @foreach ($this->rangeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if (filled($this->report['daily'] ?? []))
                <div class="ga-totals">
                    <div>
                        <p class="ga-total-label">실사용자</p>
                        <p class="ga-total-value">{{ number_format($this->totals['visitors']) }}</p>
                    </div>
                    <div>
                        <p class="ga-total-label">페이지뷰</p>
                        <p class="ga-total-value">{{ number_format($this->totals['page_views']) }}</p>
                    </div>
                </div>

                {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
                <div style="overflow-x: auto;">
                    <table class="ga-table">
                        <thead>
                            <tr>
                                <th>날짜</th>
                                <th>실사용자</th>
                                <th>페이지뷰</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->report['daily'] as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td style="font-weight: 600;">{{ number_format($row['visitors']) }}</td>
                                    <td>{{ number_format($row['page_views']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="fi-color-gray" style="font-size: 0.875rem;">선택한 기간에 집계된 방문이 아직 없습니다. 공개 사이트에 구글 애널리틱스 태그가 설치되어 있어야 데이터가 쌓입니다.</p>
            @endif
        </x-filament::section>

        @if (filled($this->report['path'] ?? []) || filled($this->report['country'] ?? []) || filled($this->report['referer'] ?? []))
            <x-filament::section>
                <x-slot name="heading">상세 분석 · {{ $this->rangeOptions[$this->range] }}</x-slot>
                <x-slot name="description">페이지뷰 기준 상위 8개입니다.</x-slot>

                @include('filament.pages.partials.breakdowns', ['data' => $this->report])
            </x-filament::section>
        @endif
    @else
        <x-filament::section>
            <x-slot name="heading">구글 애널리틱스 연동이 아직 설정되지 않았습니다</x-slot>

            <p class="text-sm">아래 순서를 마치면 이 페이지에 자동으로 데이터가 표시됩니다.</p>

            <ol class="text-sm" style="margin-top: 0.75rem; padding-inline-start: 1.25rem; list-style: decimal; line-height: 1.9;">
                <li>구글 애널리틱스에서 GA4 속성을 만들고, 보고 시간대를 <strong>(GMT+10:00) 브리즈번</strong>으로 설정합니다.</li>
                <li>관리 → 속성 세부정보에서 <strong>속성 ID</strong>(숫자)를 확인합니다.</li>
                <li>구글 클라우드 콘솔에서 <strong>Google Analytics Data API</strong>를 사용 설정하고 서비스 계정을 만든 뒤 JSON 키를 내려받습니다.</li>
                <li>GA4 속성의 액세스 관리에서 그 서비스 계정 이메일에 <strong>뷰어</strong> 권한을 부여합니다.</li>
                <li>JSON 키를 서버의 <code>storage/app/analytics/service-account-credentials.json</code> 에 두고, 웹 서버 사용자만 읽을 수 있게 권한을 제한합니다.</li>
                <li><code>.env</code> 에 <code>ANALYTICS_PROPERTY_ID</code> 를 설정하고 <code>php artisan config:clear</code> 를 실행합니다.</li>
                <li>공개 사이트에 GA4 태그(gtag.js)를 넣어야 실제 방문이 수집됩니다.</li>
            </ol>
        </x-filament::section>
    @endif
</x-filament-panels::page>
