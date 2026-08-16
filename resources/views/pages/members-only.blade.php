{{-- What a reader who is not on the 교적 gets in place of a 성도 전용
     page: the section's own eyebrow and title, so they know where they
     landed, and one line saying why there is nothing under it.

     The whole page is restricted now rather than row by row, so the
     controller never runs the queries and there is nothing here to
     hide - the response simply does not contain the content.

     noindex because a crawler is a guest: without it the church's news,
     events and albums would rank on a login notice. --}}
<x-layout.app :title="$title" :noindex="true">

    <x-ui.page-header :kicker="$kicker" :title="$title" />

    <x-ui.sign-in-required class="section-members-only" />

</x-layout.app>
