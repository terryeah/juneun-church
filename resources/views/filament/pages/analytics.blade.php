<x-filament-panels::page>
    @if ($this->dailySnapshots->isNotEmpty())
        <x-filament::section>
            <x-slot name="heading">일별 상세</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400">
                            <th class="py-2 font-medium">날짜</th>
                            <th class="py-2 text-right font-medium">실방문자</th>
                            <th class="py-2 text-right font-medium">페이지뷰</th>
                            <th class="py-2 text-right font-medium">총 요청 (봇 포함)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($this->dailySnapshots as $snapshot)
                            <tr>
                                <td class="py-2 tabular-nums">{{ $snapshot->snapshot_date->format('Y-m-d') }}</td>
                                <td class="py-2 text-right tabular-nums">{{ number_format($snapshot->unique_visitors) }}</td>
                                <td class="py-2 text-right tabular-nums">{{ number_format($snapshot->page_views) }}</td>
                                <td class="py-2 text-right tabular-nums">{{ number_format($snapshot->requests) }}</td>
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
