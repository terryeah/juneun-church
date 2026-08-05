<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
    <style>
        .dbg { display: flex; flex-direction: column; gap: 1rem; }
        .dbg-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .dbg-legend { display: flex; flex-wrap: wrap; gap: 0.375rem; }
        .dbg-legend-item { display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; border: 1px solid color-mix(in srgb, currentColor 16%, transparent); border-radius: 62rem; padding: 0.25rem 0.7rem; font-size: 0.75rem; line-height: 1.5; }
        .dbg-legend-item[aria-pressed="false"] { opacity: 0.4; }
        .dbg-swatch { width: 0.6rem; height: 0.6rem; border-radius: 50%; }
        .dbg-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; font-size: 0.75rem; }
        .dbg-switch { display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; }
        .dbg-counts { font-variant-numeric: tabular-nums; opacity: 0.7; }
        .dbg-wrap { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); }
        .dbg-stage { position: relative; height: clamp(24rem, 58vh, 44rem); overflow: hidden; touch-action: none; border: 1px solid color-mix(in srgb, currentColor 12%, transparent); border-radius: 0.75rem; background: radial-gradient(circle at 50% 38%, color-mix(in srgb, currentColor 8%, transparent), transparent 70%), color-mix(in srgb, currentColor 4%, transparent); }
        .dbg-status { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; font-size: 0.875rem; opacity: 0.65; }
        .dbg-panel { max-height: clamp(24rem, 58vh, 44rem); overflow-y: auto; border: 1px solid color-mix(in srgb, currentColor 12%, transparent); border-radius: 0.75rem; padding: 1rem 1.125rem; }
        .dbg-panel-title { font-size: 1rem; font-weight: 700; word-break: break-all; }
        .dbg-panel-meta { margin-top: 0.15rem; font-size: 0.75rem; opacity: 0.7; }
        .dbg-panel-subtitle { margin-top: 1.1rem; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; opacity: 0.6; }
        .dbg-panel-hint { font-size: 0.8125rem; line-height: 1.6; opacity: 0.65; }
        .dbg-columns { margin-top: 0.75rem; display: flex; flex-direction: column; }
        .dbg-columns li { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; padding: 0.3rem 0; border-top: 1px solid color-mix(in srgb, currentColor 10%, transparent); font-size: 0.8125rem; }
        .dbg-columns li:first-child { border-top: 0; }
        .dbg-column-name { display: inline-flex; align-items: center; gap: 0.3rem; word-break: break-all; }
        .dbg-column-type { flex: none; font-size: 0.6875rem; font-variant-numeric: tabular-nums; opacity: 0.6; }
        .dbg-badge { border: 1px solid color-mix(in srgb, currentColor 25%, transparent); border-radius: 0.25rem; padding: 0 0.2rem; font-size: 0.5625rem; font-weight: 700; letter-spacing: 0.04em; opacity: 0.8; }
        .dbg-relations { margin-top: 0.4rem; display: flex; flex-direction: column; gap: 0.3rem; }
        .dbg-relations li { font-size: 0.75rem; line-height: 1.5; word-break: break-all; opacity: 0.85; }
        .dbg-note { font-size: 0.75rem; line-height: 1.6; opacity: 0.6; }
        @media (min-width: 64rem) { .dbg-wrap { grid-template-columns: minmax(0, 1fr) 20rem; } }
    </style>

    {{-- Held outside Livewire's reach so a re-render never tears down the WebGL canvas. --}}
    <div class="dbg" data-database-graph wire:ignore>
        <script type="application/json" data-dbg-payload>@json($this->graph, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)</script>

        <div class="dbg-toolbar">
            <div class="dbg-legend">
                @foreach ($this->graph['domains'] as $key => $domain)
                    <button type="button" class="dbg-legend-item" data-dbg-domain="{{ $key }}" aria-pressed="true">
                        <span class="dbg-swatch" style="background: {{ $domain['color'] }}"></span>{{ $domain['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="dbg-tools">
                <span class="dbg-counts" data-dbg-counts></span>
                <label class="dbg-switch">
                    <input type="checkbox" class="fi-checkbox-input" data-dbg-system>
                    시스템 테이블 표시
                </label>
            </div>
        </div>

        <div class="dbg-wrap">
            <div class="dbg-stage" data-dbg-stage>
                <p class="dbg-status" data-dbg-status>3D 스키마 그래프를 불러오는 중입니다. 자바스크립트가 필요합니다.</p>
            </div>

            <aside class="dbg-panel">
                <div data-dbg-panel-body>
                    <p class="dbg-panel-hint">테이블을 선택하면 컬럼과 관계가 표시됩니다.</p>
                </div>
            </aside>
        </div>

        <p class="dbg-note">
            노드 크기는 행 수, 색상은 도메인을 나타냅니다. 드래그로 회전, 스크롤 또는 두 손가락으로 확대·축소, 노드를 누르면 상세 정보가 열립니다.
            그래프는 실제 스키마와 외래 키 제약에서 5분 캐시로 읽어옵니다.
        </p>
    </div>

    @vite('resources/ts/admin/database-graph.ts')
</x-filament-panels::page>
