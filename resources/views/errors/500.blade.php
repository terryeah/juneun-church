<x-layout.app title="문제가 생겼습니다" description="일시적인 오류입니다." :noindex="true">

    <x-ui.page-header kicker="500" title="문제가 생겼습니다">
        일시적인 오류입니다. 잠시 뒤 다시 시도해 주세요. 계속 이러면 교회로 알려주세요.
    </x-ui.page-header>

    <section class="container-site pb-16 lg:pb-24">
        <div class="flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('home') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 홈으로</a>
        </div>
    </section>

</x-layout.app>
