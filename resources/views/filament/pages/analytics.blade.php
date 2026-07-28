<x-filament-panels::page>
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
