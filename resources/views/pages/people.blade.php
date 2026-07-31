<x-layout.app title="섬기는 사람들" description="브리즈번 주는교회를 섬기는 사람들을 소개합니다.">

    <x-ui.page-header kicker="함께 세워가는 공동체 · People" title="섬기는 사람들" />

    <section class="section-people-directory container-site pb-12 lg:pb-16">
        @forelse ($positions as $position)
            <div class="mb-10 border-t border-line pt-8 first:border-t-2 first:border-navy">
                <h2 class="font-kr text-display-sm font-medium">{{ $position->name }}</h2>
                <div class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($position->staffMembers as $member)
                        <div class="flex items-center gap-4">
                            @if ($member->photoUrl())
                                <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" class="h-20 w-20 object-cover shrink-0 rounded-media">
                            @else
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-media bg-navy/8 text-navy-400">
                                    <span class="h-8"><x-ui.logo /></span>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-kr text-body font-medium">{{ $member->name }}</h3>
                                <p class="mt-0.5 text-body-sm text-navy-400">
                                    {{ $position->name }}@if ($member->department) · {{ $member->department }}@endif
                                </p>
                                @if ($member->bio)
                                    <p class="mt-2 font-kr text-body-sm leading-relaxed text-navy-700">{{ $member->bio }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-body-sm text-navy-400">등록된 내용이 없습니다.</p>
        @endforelse
    </section>

</x-layout.app>
