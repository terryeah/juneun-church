@php
    /** Field styling follows the site tokens; no shared input component exists yet. */
    $labelClass = 'block font-kr text-body-sm font-bold text-navy';
    $inputClass = 'mt-2 block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body text-navy transition-colors duration-200 placeholder:text-navy-400 focus:border-navy focus:outline-none';
    $errorClass = 'mt-1.5 font-kr text-body-sm text-accent';
@endphp

<x-layout.app title="로그인" description="브리즈번 주는교회 홈페이지 로그인입니다." :noindex="true">

    <x-ui.page-header kicker="함께하는 성도 · Log in" title="로그인" narrow>
        교회 소식, 교회 행사, 자료실, 헌금, 앨범은 성도에게만 열려 있습니다. 로그인하시면 보시던 페이지로 돌아갑니다.
    </x-ui.page-header>

    <section class="container-site pb-12 lg:pb-16">
        <form method="POST" action="{{ route('login.store') }}" class="mx-auto max-w-3xl rounded-frame border-2 border-navy bg-paper p-8">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="email" class="{{ $labelClass }}">이메일</label>
                    <input id="email" name="email" type="email" required maxlength="255" autocomplete="email" autofocus
                           value="{{ old('email') }}" class="{{ $inputClass }}">
                    @error('email')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="{{ $labelClass }}">비밀번호</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="{{ $inputClass }}">
                    @error('password')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>

                <label for="remember" class="flex items-center gap-2.5 font-kr text-body-sm text-navy">
                    <input id="remember" name="remember" type="checkbox" value="1"
                           class="size-4 rounded-nav border-2 border-line text-accent focus:border-navy">
                    로그인 상태 유지
                </label>
            </div>

            <button type="submit" class="mt-7 inline-flex items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                <span class="font-kr">로그인</span>
            </button>

            <p class="mt-4 font-kr text-body-sm leading-relaxed text-navy-400">
                아직 계정이 없으신가요?
                <a href="{{ route('signup') }}" class="font-bold text-accent hover:text-accent-700">가입 신청하기</a>
            </p>
        </form>
    </section>

</x-layout.app>
