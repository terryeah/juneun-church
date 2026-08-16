<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
    <style>
        .an-frame { display: block; width: 100%; height: 78vh; min-height: 40rem; border: 0; border-radius: 0.75rem; background-color: rgba(128, 138, 160, 0.06); }
    </style>

    @if (filled($this->shareUrl))
        {{-- Umami draws its own dashboard here. The share link carries no
             session, so nothing about this panel travels with it. --}}
        <iframe
            src="{{ $this->shareUrl }}"
            class="an-frame"
            title="Umami 방문자 통계"
            loading="lazy"
            referrerpolicy="no-referrer"
        ></iframe>
    @else
        <x-filament::section>
            <x-slot name="heading">Umami 연동이 아직 설정되지 않았습니다</x-slot>

            <p class="text-sm">
                Umami에서 <strong>Websites → 해당 사이트 Edit → Share URL</strong>로 공유 링크를 만든 뒤,
                <code>.env</code>의 <code>UMAMI_SHARE_URL</code>에 넣어주세요.
                링크를 아는 사람은 로그인 없이 통계를 볼 수 있으니, 필요하면 Umami에서 다시 발급할 수 있습니다.
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
