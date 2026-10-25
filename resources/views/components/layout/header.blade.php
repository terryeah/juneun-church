@php
    use App\Models\SiteSetting;

    /**
     * The 성도 pages are folded into two groups so the bar stays short.
     * A group opens on hover or keyboard focus with CSS alone, and reads
     * as active when any page inside it is.
     */
    $navItems = [
        ['label' => '예배 안내', 'href' => route('worship'), 'active' => request()->routeIs('worship')],
        ['label' => '말씀과 기도', 'items' => [
            ['label' => '큐티', 'href' => route('qt.index'), 'active' => request()->routeIs('qt.*')],
            ['label' => '기도제목', 'href' => route('prayer.index'), 'active' => request()->routeIs('prayer.*')],
            ['label' => '성경 필사', 'href' => route('typing.index'), 'active' => request()->routeIs('typing.*')],
        ]],
        ['label' => '교회 생활', 'items' => [
            ['label' => '교회 소식', 'href' => route('news.index'), 'active' => request()->routeIs('news.*')],
            ['label' => '교회 행사', 'href' => route('events'), 'active' => request()->routeIs('events')],
            ['label' => '앨범', 'href' => route('album.index'), 'active' => request()->routeIs('album.*')],
            ['label' => '자료실', 'href' => route('downloads'), 'active' => request()->routeIs('downloads')],
            ['label' => '헌금', 'href' => route('giving'), 'active' => request()->routeIs('giving')],
        ]],
        ['label' => '섬기는 사람들', 'href' => route('people'), 'active' => request()->routeIs('people')],
        ['label' => '오시는 길', 'href' => route('location'), 'active' => request()->routeIs('location')],
    ];
@endphp

<header class="border-b-2 border-navy bg-paper">
    <div class="container-site flex items-center justify-between py-4">
        <a href="{{ route('home') }}" class="flex items-start gap-3 text-navy">
            <span class="h-[2.125rem]"><x-ui.logo /></span>
            <span>
                <span class="block font-kr text-[1.125rem] font-medium leading-tight">{{ SiteSetting::get('church_name', '브리즈번 주는교회') }}</span>
                <span class="block text-caption font-medium tracking-[0.04em] text-navy-400">{{ SiteSetting::get('denomination', '대한예수교장로회') }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-0.5 xl:flex" aria-label="주 메뉴">
            @foreach ($navItems as $item)
                @if (isset($item['items']))
                    @php $open = collect($item['items'])->contains('active', true); @endphp
                    <div class="group relative">
                        <button type="button" aria-haspopup="true"
                                class="flex items-center gap-1 rounded-nav px-3 py-[0.5625rem] font-kr text-body-sm font-medium {{ $open ? 'bg-accent text-on-accent' : 'text-navy group-hover:bg-accent-100 group-focus-within:bg-accent-100' }}">
                            {{ $item['label'] }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:rotate-180 group-focus-within:rotate-180" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                        {{-- The padding keeps the pointer inside the group on its way down to the list. --}}
                        <div class="invisible absolute left-0 top-full z-30 pt-2 opacity-0 transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                            <div class="flex min-w-[10rem] flex-col gap-0.5 rounded-frame border-2 border-navy bg-paper p-1.5">
                                @foreach ($item['items'] as $sub)
                                    <x-layout.nav-link :href="$sub['href']" :active="$sub['active']" class="block whitespace-nowrap">{{ $sub['label'] }}</x-layout.nav-link>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <x-layout.nav-link :href="$item['href']" :active="$item['active']">{{ $item['label'] }}</x-layout.nav-link>
                @endif
            @endforeach

            {{-- The account item is an action rather than a page, so it sits behind a rule. --}}
            <span class="ml-1.5 flex items-center border-l border-line pl-1.5">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-nav px-3 py-[0.5625rem] font-kr text-body-sm font-medium text-navy hover:bg-accent-100">로그아웃</button>
                    </form>
                @else
                    <x-layout.nav-link :href="route('login')" :active="request()->routeIs('login')">로그인</x-layout.nav-link>
                @endauth
            </span>
        </nav>

        <button
            type="button"
            class="group rounded-nav p-2 text-navy hover:bg-accent-100 xl:hidden"
            aria-expanded="false"
            aria-controls="mobile-menu"
            aria-label="메뉴 열기"
            data-mobile-nav-toggle
        >
            <span class="relative block h-6 w-6">
                <svg class="absolute inset-0 h-6 w-6 transition-all duration-300 ease-in-out group-aria-expanded:-rotate-90 group-aria-expanded:scale-75 group-aria-expanded:opacity-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg class="absolute inset-0 h-6 w-6 rotate-90 scale-75 opacity-0 transition-all duration-300 ease-in-out group-aria-expanded:rotate-0 group-aria-expanded:scale-100 group-aria-expanded:opacity-100" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" d="m9.2 9.2 5.6 5.6m0-5.6-5.6 5.6"/>
                </svg>
            </span>
        </button>
    </div>

    <nav id="mobile-menu" class="hidden fixed inset-0 top-(--header-h) z-40 overflow-y-auto bg-paper xl:hidden" aria-label="모바일 메뉴" data-mobile-nav-menu>
        <div class="container-site divide-y divide-line py-4">
            @foreach ($navItems as $item)
                @if (isset($item['items']))
                    <div class="py-1.5">
                        <p class="px-4 pb-1 pt-3 font-kr text-caption font-bold text-navy-400">{{ $item['label'] }}</p>
                        @foreach ($item['items'] as $sub)
                            <x-layout.nav-link :href="$sub['href']" :active="$sub['active']" :mobile="true">{{ $sub['label'] }}</x-layout.nav-link>
                        @endforeach
                    </div>
                @else
                    <div class="py-1.5"><x-layout.nav-link :href="$item['href']" :active="$item['active']" :mobile="true">{{ $item['label'] }}</x-layout.nav-link></div>
                @endif
            @endforeach

            <div class="py-1.5">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-nav px-4 py-3.5 text-left font-kr text-body font-medium text-navy hover:bg-accent-100">로그아웃</button>
                    </form>
                @else
                    <x-layout.nav-link :href="route('login')" :active="request()->routeIs('login')" :mobile="true">로그인</x-layout.nav-link>
                @endauth
            </div>
        </div>
    </nav>
</header>
