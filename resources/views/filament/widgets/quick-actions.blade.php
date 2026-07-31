<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 2.5rem; flex-wrap: wrap;">
            <div>
                <p style="font-size: 0.875rem; font-weight: 600;">바로가기</p>
                <p style="font-size: 0.75rem; color: rgb(148 158 178);">자주 하는 작업</p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <x-filament::button tag="a" href="{{ url('/') }}" target="_blank" color="gray" size="sm">사이트 보기</x-filament::button>
                <x-filament::button tag="a" href="{{ url('/admin/announcements/create') }}" color="gray" size="sm">뉴스 작성</x-filament::button>
                <x-filament::button tag="a" href="{{ url('/admin/photos/create') }}" color="gray" size="sm">사진 업로드</x-filament::button>
                <x-filament::button tag="a" href="{{ url('/admin/bulletins/create') }}" color="gray" size="sm">주보 올리기</x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
