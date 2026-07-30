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

<div class="an-breakdowns">
    @foreach ($breakdownGroups as $key => $title)
        @php
            $rows = $data[$key] ?? [];
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
