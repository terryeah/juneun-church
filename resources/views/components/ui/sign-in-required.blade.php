@props([
    'body',
])

{{-- Tells a guest, in one line, why a page is holding something back.
     Shared by 헌금 and 자료실 so the two read as one site. The login
     screen carries the 가입 신청 link, so someone without an account is
     one step away rather than at a dead end. --}}
<section {{ $attributes->merge(['class' => 'container-site pb-12 lg:pb-16']) }}>
    <p class="max-w-xl font-kr text-body-sm leading-relaxed text-navy-400">{{ $body }} <a href="{{ route('login') }}" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">로그인</a> 후 확인해 주세요.</p>
</section>
