@props([
    'photo',
])

{{-- Every album is on 앨범 now, so a slide always has somewhere to go.
     A guest who follows one is met by the 성도 전용 notice, the same
     answer the home page's 소식 titles give. --}}
<a href="{{ route('album.show', $photo->album) }}" class="block overflow-hidden rounded-[1.35rem]">
    <img src="{{ $photo->thumbnailUrl() }}" alt="{{ $photo->caption ?? $photo->album?->title }}" class="aspect-square w-full object-cover" loading="lazy">
</a>
