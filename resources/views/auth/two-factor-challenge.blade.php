@php
    /** Field styling follows the site tokens; no shared input component exists yet. */
    $labelClass = 'block font-kr text-body-sm font-bold text-navy';
    $inputClass = 'mt-2 block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body text-navy transition-colors duration-200 placeholder:text-navy-400 focus:border-navy focus:outline-none';
    $errorClass = 'mt-1.5 font-kr text-body-sm text-accent';
@endphp

<x-layout.app title="2단계 인증" description="브리즈번 주는교회 홈페이지 2단계 인증 확인입니다." :noindex="true">

    <x-ui.page-header kicker="추가 확인 · Two-step" title="2단계 인증" narrow>
        이 계정에는 인증 앱이 등록되어 있습니다. 앱에 표시된 여섯 자리 코드를 입력해 주시면 로그인이 완료됩니다.
    </x-ui.page-header>

    <section class="container-site pb-12 lg:pb-16">
        <form method="POST" action="{{ route('login.challenge.store') }}" class="mx-auto max-w-3xl rounded-frame border-2 border-navy bg-paper p-8">
            @csrf

            <div>
                <label for="code" class="{{ $labelClass }}">인증 코드 여섯 자리</label>
                <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" autofocus
                       class="{{ $inputClass }}">
                @error('code')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>

            <details class="mt-6 border-t-2 border-line pt-6" @if ($errors->has('recovery_code')) open @endif>
                <summary class="cursor-pointer font-kr text-body-sm font-bold text-accent hover:text-accent-700">
                    휴대폰을 사용할 수 없으신가요?
                </summary>

                <div class="mt-4">
                    <label for="recovery_code" class="{{ $labelClass }}">복구 코드</label>
                    <p class="mt-1.5 font-kr text-body-sm leading-relaxed text-navy-400">
                        2단계 인증을 등록하실 때 받아 두신 복구 코드를 대신 입력하실 수 있습니다. 한 번 사용한 코드는 다시 쓸 수 없습니다.
                    </p>
                    <input id="recovery_code" name="recovery_code" type="text" maxlength="255" autocomplete="one-time-code"
                           class="{{ $inputClass }}">
                    @error('recovery_code')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </details>

            <button type="submit" class="mt-7 inline-flex cursor-pointer items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                <span class="font-kr">확인</span>
            </button>

            <p class="mt-4 font-kr text-body-sm leading-relaxed text-navy-400">
                다른 계정으로 로그인하시겠어요?
                <a href="{{ route('login') }}" class="font-bold text-accent hover:text-accent-700">처음부터 다시 로그인</a>
            </p>
        </form>
    </section>

</x-layout.app>
