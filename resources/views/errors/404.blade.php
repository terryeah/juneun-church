<x-layout.app title="페이지를 찾을 수 없습니다" description="요청하신 페이지가 없습니다." :noindex="true">

    <x-ui.page-header kicker="404" title="페이지를 찾을 수 없습니다">
        주소가 바뀌었거나, 지워졌거나, 성도 전용일 수 있습니다.
    </x-ui.page-header>

    <section class="container-site pb-16 lg:pb-24">
        {{-- A 성도 전용 page answers 404 rather than "로그인하세요", because
             saying "sign in" would confirm that something is there and the
             slug carries the title. That is right, but it leaves the
             congregation looking at a dead end when somebody shares an
             album in a chat - so the page says what is worth saying to
             both readers without telling either which one they are. --}}
        @guest
            <p class="max-w-xl font-kr text-body leading-relaxed text-navy-700">
                성도에게만 공개된 페이지일 수 있습니다.
                <a href="{{ route('login') }}" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">로그인</a> 후 다시 열어보세요.
            </p>
        @endguest

        <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('home') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 홈으로</a>
            <a href="{{ route('album.index') }}" class="text-caption font-bold text-accent hover:text-accent-700">앨범 보기</a>
            <a href="{{ route('news.index') }}" class="text-caption font-bold text-accent hover:text-accent-700">교회 소식</a>
        </div>
    </section>

</x-layout.app>
