<x-layout.app title="열 수 없는 페이지입니다" description="이 페이지를 볼 권한이 없습니다." :noindex="true">

    <x-ui.page-header kicker="403" title="열 수 없는 페이지입니다">
        지금 로그인한 계정으로는 이 페이지를 볼 수 없습니다.
    </x-ui.page-header>

    <section class="container-site pb-16 lg:pb-24">
        <div class="flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('home') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 홈으로</a>
        </div>
    </section>

</x-layout.app>
