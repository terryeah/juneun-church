@php
    /** Field styling follows the site tokens; no shared input component exists yet. */
    $labelClass = 'block font-kr text-body-sm font-bold text-navy';
    $inputClass = 'mt-2 block w-full rounded-btn border-2 border-line bg-paper px-4 py-3 font-kr text-body text-navy transition-colors duration-200 placeholder:text-navy-400 focus:border-navy focus:outline-none';
    $errorClass = 'mt-1.5 font-kr text-body-sm text-accent';
@endphp

<x-layout.app title="가입 신청" description="브리즈번 주는교회 홈페이지 가입 신청 안내입니다.">

    <x-ui.page-header kicker="함께하는 성도 · Sign up" title="가입 신청" narrow>
        헌금 내역처럼 성도에게만 열려 있는 내용을 보시려면 계정이 필요합니다. 신청해 주시면 교적부와 대조한 뒤 관리자가 승인해 드립니다.
    </x-ui.page-header>

    <section class="container-site pb-12 lg:pb-16">
        @if ($submitted)
            <div class="mx-auto max-w-3xl rounded-frame border-2 border-navy bg-paper p-8">
                <svg class="h-12 w-12 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m8 12.5 2.8 2.8L16 10"/>
                </svg>
                <h2 class="mt-4 font-kr text-display-sm font-medium">가입 신청이 완료되었습니다</h2>
                <p class="mt-4 font-kr text-body leading-relaxed text-navy-700">
                    관리자가 교적부와 대조하여 확인한 뒤 승인해 드립니다. 승인 전까지는 로그인하실 수 없으며,
                    확인이 필요한 경우 교회 사무실에서 연락드립니다.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('signup.store') }}" class="mx-auto max-w-3xl rounded-frame border-2 border-navy bg-paper p-8">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="name" class="{{ $labelClass }}">이름</label>
                        <input id="name" name="name" type="text" required maxlength="255" autocomplete="name"
                               value="{{ old('name') }}" class="{{ $inputClass }}">
                        @error('name')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="birth_date" class="{{ $labelClass }}">생년월일</label>
                        {{-- A text field rather than a native date picker, whose
                             displayed order follows the browser locale. --}}
                        <input id="birth_date" name="birth_date" type="text" required inputmode="numeric"
                               placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" maxlength="10"
                               value="{{ old('birth_date') }}" class="{{ $inputClass }}">
                        @error('birth_date')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="{{ $labelClass }}">전화번호</label>
                        <input id="phone" name="phone" type="tel" required maxlength="10" placeholder="0411222333"
                               autocomplete="tel" value="{{ old('phone') }}" class="{{ $inputClass }}">
                        @error('phone')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClass }}">이메일</label>
                        <input id="email" name="email" type="email" required maxlength="255" autocomplete="email"
                               value="{{ old('email') }}" class="{{ $inputClass }}">
                        @error('email')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="{{ $labelClass }}">비밀번호</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="{{ $inputClass }}">
                        @error('password')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="{{ $labelClass }}">비밀번호 확인</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label for="note" class="{{ $labelClass }}">남기실 말씀 <span class="font-medium text-navy-400">(선택)</span></label>
                        <textarea id="note" name="note" rows="4" maxlength="1000"
                                  placeholder="함께 등록한 가족, 출석하시는 예배 등 확인에 도움이 되는 내용을 적어 주세요."
                                  class="{{ $inputClass }}">{{ old('note') }}</textarea>
                        @error('note')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>
                </div>

                <button type="submit" class="mt-7 inline-flex cursor-pointer items-center gap-2 rounded-btn bg-accent px-5 py-3 text-body font-extrabold text-on-accent transition-colors duration-200 hover:bg-accent-700 active:bg-accent-700">
                    <span class="font-kr">가입 신청하기</span>
                </button>

                <p class="mt-4 font-kr text-body-sm leading-relaxed text-navy-400">
                    신청하신 정보는 교인 확인 목적으로만 사용되며, 승인 전까지 어떤 내용도 열람되지 않습니다.
                </p>
            </form>
        @endif
    </section>

</x-layout.app>
