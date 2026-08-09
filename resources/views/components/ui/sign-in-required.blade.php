@props([
    'kicker',
    'title',
    'body',
])

{{-- Tells a guest why a page is holding something back, and offers the
     two ways in. Shared by 헌금 and 주보 so the two read as one site. --}}
<section {{ $attributes->merge(['class' => 'container-site pb-12 lg:pb-16']) }}>
    <div class="max-w-xl rounded-frame border-2 border-navy p-8">
        <x-ui.kicker>{{ $kicker }}</x-ui.kicker>
        <h2 class="mt-3 font-kr text-display-sm font-medium">{{ $title }}</h2>
        <p class="mt-4 font-kr text-body leading-relaxed text-navy-400">{{ $body }}</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <x-ui.button href="{{ route('signup') }}">가입 신청</x-ui.button>
            <x-ui.button href="{{ route('login') }}" variant="secondary">로그인</x-ui.button>
        </div>
    </div>
</section>
