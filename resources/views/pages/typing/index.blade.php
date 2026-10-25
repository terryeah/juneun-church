@php
    use App\Models\BibleVerse;
@endphp

<x-layout.app title="성경 필사" description="개역한글 성경을 한 절씩 직접 옮겨 쓰는 성경 필사입니다." :noindex="true">

    <x-ui.page-header kicker="말씀 필사 · Scripture Writing" title="성경 필사">
        성경을 한 절씩 직접 옮겨 씁니다. 띄어쓰기는 달라도 되지만 글자는 모두 맞아야 한 절로 셉니다.
    </x-ui.page-header>

    <section class="section-typing container-site pb-12 lg:pb-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <div>
                <div class="rounded-frame border-2 border-navy p-6 lg:p-8">
                    <p class="text-caption font-extrabold uppercase tracking-[0.16em] text-accent">나의 기록</p>
                    <p class="mt-3 font-kr text-display-sm font-medium">
                        이번 달 {{ number_format($mine['month']) }}절 · 지금까지 {{ number_format($mine['total']) }}절
                    </p>
                    <p class="mt-1 font-kr text-body-sm text-navy-400">성경 전체 {{ number_format($totalVerses) }}절</p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <x-ui.button :href="route('typing.write', ['book' => $mine['book']])">
                            <span class="font-kr">{{ BibleVerse::BOOKS[$mine['book']] }} 이어서 쓰기</span>
                        </x-ui.button>
                        <form method="GET" action="{{ route('typing.write') }}" class="flex items-center gap-2">
                            <label for="book" class="sr-only">다른 책 고르기</label>
                            <select id="book" name="book" class="rounded-btn border-2 border-line bg-paper px-3 py-3 font-kr text-body-sm text-navy focus:border-navy focus:outline-none">
                                @foreach (BibleVerse::BOOKS as $number => $name)
                                    <option value="{{ $number }}" @selected($number === $mine['book'])>{{ $name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-btn border-2 border-navy px-4 py-3 font-kr text-body-sm font-bold text-navy hover:bg-accent-100">다른 책</button>
                        </form>
                    </div>
                </div>

                <p class="mt-4 font-kr text-caption text-navy-400">본문: 성경전서 개역한글판 (대한성서공회)</p>
            </div>

            <div class="space-y-8">
                @foreach ([['이번 달 · 개인', $monthPeople], ['이번 달 · 셀', $cells], ['지금까지 · 개인', $allPeople]] as [$heading, $rows])
                    <div>
                        <h2 class="font-kr text-body font-bold text-navy">{{ $heading }}</h2>
                        <ol class="mt-2 border-t-2 border-navy">
                            @forelse ($rows as $row)
                                <li class="flex items-baseline gap-3 border-b border-line py-2.5 font-kr text-body-sm">
                                    <span class="w-5 text-caption font-extrabold text-accent">{{ $loop->iteration }}</span>
                                    <span class="flex-1 text-navy">{{ $row['name'] }}</span>
                                    <span class="text-navy-700">{{ number_format($row['verses']) }}절</span>
                                </li>
                            @empty
                                <li class="py-3 font-kr text-body-sm text-navy-400">아직 기록이 없습니다.</li>
                            @endforelse
                        </ol>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</x-layout.app>
