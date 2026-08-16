<x-layout.app title="페이지를 찾을 수 없습니다" description="요청하신 페이지가 없습니다." :noindex="true">

    <x-ui.page-header kicker="404" title="페이지를 찾을 수 없습니다">
        주소가 바뀌었거나, 지워졌거나, 성도 전용일 수 있습니다.
    </x-ui.page-header>

    <section class="container-site pb-16 lg:pb-24">
        {{-- 성도 전용 pages answer 200 with their own notice now, so what
             still lands here is a 주보 or a 서식: a file answers 404 to
             anybody who may not have it, because saying "로그인하세요"
             would confirm the file is there and the address carries its
             name. That leaves the congregation looking at a dead end when
             somebody forwards a 주보 link in a 단톡방, so the page says
             what is worth saying to both readers without telling either
             which one they are. --}}
        @guest
            <p class="max-w-xl font-kr text-body leading-relaxed text-navy-700">
                성도에게만 공개된 자료일 수 있습니다.
                <a href="{{ route('login') }}" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">로그인</a> 후 다시 열어보세요.
            </p>
        @endguest

        {{-- Every link here is open to anybody: sending a guest to 앨범 or
             교회 소식 from a dead end only walked them into a second one. --}}
        <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
            <a href="{{ route('home') }}" class="text-caption font-bold text-accent hover:text-accent-700">← 홈으로</a>
            <a href="{{ route('worship') }}" class="text-caption font-bold text-accent hover:text-accent-700">예배 안내</a>
            <a href="{{ route('location') }}" class="text-caption font-bold text-accent hover:text-accent-700">오시는 길</a>
        </div>
    </section>

</x-layout.app>
