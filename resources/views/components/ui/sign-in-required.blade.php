@props([
    'body' => '이 페이지는 성도에게만 공개됩니다.',
])

@php
    use App\Models\SiteSetting;

    /** The office address the footer already publishes; no new setting. */
    $contactEmail = trim((string) SiteSetting::get('contact_email'));
@endphp

{{-- Tells a reader who is not on the 교적, in one line, why a page is
     holding something back. Every 성도 전용 page says the same sentence:
     five pages each explaining themselves differently read as five
     different rules rather than one, and the page's own heading sits
     directly above this line, so naming the section again adds nothing.

     The gate is the 교적, not the session, so the two readers who hit
     it need different things said to them. Someone signed out is one
     step away and gets the login, which carries the 가입 신청 link and
     - through ?next - the page they were reading. Someone already
     signed in has nothing to gain from a login form, so they are told
     the real requirement and where to ask for it. --}}
<section {{ $attributes->merge(['class' => 'container-site pb-12 lg:pb-16']) }}>
    <p class="max-w-xl font-kr text-body-sm leading-relaxed text-navy-400">
        {{ $body }}
        @auth
            로그인은 되어 있지만 아직 교적에 등록된 계정이 아닙니다. 교회 사무실@if ($contactEmail)(<a href="mailto:{{ $contactEmail }}" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">{{ $contactEmail }}</a>)@endif로 문의해 주시면 교적 등록을 도와드립니다.
        @else
            <a href="{{ route('login', ['next' => request()->getRequestUri()]) }}" class="font-medium text-accent underline underline-offset-4 hover:text-accent-700">로그인</a> 후 확인해 주세요.
        @endauth
    </p>
</section>
