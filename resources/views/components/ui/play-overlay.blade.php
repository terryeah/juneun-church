{{-- The site's one play control, over a dimmed poster frame: a bare
     white rounded triangle, no circle, growing slightly on hover.

     Shared rather than copied so 최근 예배 on the home page and the
     동영상 앨범 cannot drift into two different-looking players. Its
     parent must be `relative` and carry `group` for the hover. --}}
<span class="absolute inset-0 bg-black/20 transition-colors duration-300 group-hover:bg-black/30" aria-hidden="true"></span>
<span class="absolute inset-0 flex items-center justify-center">
    <svg class="h-[4.5rem] w-[4.5rem] text-white drop-shadow-[0_0.25rem_1rem_rgba(0,0,0,0.3)] transition-transform duration-300 ease-out group-hover:scale-[1.06]" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M9 6.6v10.8l9-5.4z" fill="currentColor" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/>
    </svg>
</span>
