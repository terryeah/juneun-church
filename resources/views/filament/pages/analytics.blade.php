<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include
         arbitrary utilities from app views.

         A dashboard inside a dashboard is two sets of scrollbars, and on
         a phone that is unusable however tall the frame is made: every
         drag inside it fights the page behind it, and Umami's own date
         pickers and charts want the gestures too. So the frame is for a
         laptop, and a phone gets a way out to Umami itself instead.

         min-width only, per the house rule, so the phone case is what
         is written first and the frame is the addition. --}}
    <style>
        .an-frame { display: none; }
        .an-handoff { display: block; }

        @media (min-width: 48rem) {
            .an-frame { display: block; width: 100%; height: 78vh; min-height: 40rem; border: 0; border-radius: 0.75rem; background-color: rgba(128, 138, 160, 0.06); }
            .an-handoff { display: none; }
        }
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

        <div class="an-handoff">
            <x-filament::section>
                <x-slot name="heading">방문자 통계</x-slot>
                <x-slot name="description">
                    페이지별 조회수, 유입 경로, 지금 보고 있는 사람까지 Umami에서 볼 수 있습니다.
                    화면 안에 끼워 넣으면 폰에서는 손가락이 두 화면과 싸우게 되어, 여기서는 바로 열도록 했습니다.
                </x-slot>

                <x-filament::button
                    tag="a"
                    href="{{ $this->shareUrl }}"
                    target="_blank"
                    rel="noopener"
                    icon="heroicon-o-arrow-top-right-on-square"
                    size="lg"
                >
                    Umami에서 열기
                </x-filament::button>
            </x-filament::section>
        </div>
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
