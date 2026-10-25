<x-filament-panels::page>
    {{-- Styled inline because the panel's compiled CSS does not include arbitrary utilities from app views. --}}
    <style>
        .bt-stats { display: grid; gap: 1rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bt-stat b { display: block; font-size: 1.5rem; font-weight: 600; line-height: 1.2; }
        .bt-stat span { font-size: 0.875rem; opacity: 0.7; }
        .bt-cells { width: 100%; font-size: 0.875rem; border-collapse: collapse; }
        .bt-cells th, .bt-cells td { padding: 0.5rem 0.25rem; text-align: left; border-bottom: 1px solid rgba(128, 138, 160, 0.25); }
        .bt-cells th:not(:first-child), .bt-cells td:not(:first-child) { text-align: right; }
        .bt-cells thead th { font-weight: 600; }
    </style>

    <x-filament::section>
        <div class="bt-stats">
            <div class="bt-stat"><b>{{ number_format($this->totals['month']) }}절</b><span>이번 달</span></div>
            <div class="bt-stat"><b>{{ number_format($this->totals['total']) }}절</b><span>지금까지</span></div>
            <div class="bt-stat"><b>{{ number_format($this->totals['people']) }}명</b><span>쓴 성도</span></div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">셀별</x-slot>
        <x-slot name="description">교적의 셀 칸을 따릅니다. 셀이 비어 있는 성도는 아래 표에만 나옵니다.</x-slot>

        @if ($this->cells->isEmpty())
            <p style="font-size: 0.875rem; opacity: 0.7;">아직 셀 기록이 없습니다.</p>
        @else
            <table class="bt-cells">
                <thead>
                    <tr><th>셀</th><th>쓴 성도</th><th>이번 달</th><th>지금까지</th></tr>
                </thead>
                <tbody>
                    @foreach ($this->cells as $cell)
                        <tr>
                            <td>{{ $cell['name'] }}</td>
                            <td>{{ number_format($cell['people']) }}명</td>
                            <td>{{ number_format($cell['month']) }}절</td>
                            <td>{{ number_format($cell['total']) }}절</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    {{ $this->table }}
</x-filament-panels::page>
