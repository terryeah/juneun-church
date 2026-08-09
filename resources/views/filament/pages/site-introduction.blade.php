<x-filament-panels::page>
    {{-- Every class is prefixed and every rule is scoped to .intro, so
         nothing here can reach Filament's own card, step or grid
         styling, and nothing of Filament's reaches in. --}}
    <div class="intro">

        <header class="intro-band intro-band-dark intro-reveal">
            <p class="intro-kicker">Brisbane Juneun Church</p>
            <h2 class="intro-h1">교회 홈페이지가<br>문을 열었습니다</h2>
            <p class="intro-hero-lede">예배 시간과 주보, 소식과 사진을 한곳에 모았습니다.
                그리고 이 모든 것을 교회가 직접 관리할 수 있습니다.</p>
            <a class="intro-url" href="https://www.juneun.com" target="_blank" rel="noopener">
                <span class="intro-dot"></span> www.juneun.com
            </a>
        </header>

        <section class="intro-band intro-reveal">
            <p class="intro-kicker">홈페이지 · Home</p>
            <h2 class="intro-h2">아홉 개의 화면</h2>
            <p class="intro-lede">처음 오시는 분이나 교인들이 알아야 할 것과 자주 찾는 걸 나눠 담았습니다.</p>

            <div class="intro-rows">
                <div class="intro-row"><strong>홈</strong><span>교회 소개, 이번 주 예배, 최근 소식과 설교 영상, 사진 슬라이드</span></div>
                <div class="intro-row"><strong>예배 안내</strong><span>다섯 예배의 시간과 장소, 지난 예배 영상</span></div>
                <div class="intro-row"><strong>교회 소식</strong><span>주보에 실리는 광고를 그대로 - 상단 고정과 하이라이트 지정</span></div>
                <div class="intro-row"><strong>교회 행사</strong><span>월별로 묶은 일정표</span></div>
                <div class="intro-row"><strong>주보</strong><span>주간 주보 PDF, 날짜순 보관</span></div>
                <div class="intro-row"><strong>헌금</strong><span>호주·한국 계좌 안내와 주간 헌금 내역</span></div>
                <div class="intro-row"><strong>갤러리</strong><span>앨범별 사진, 크게 보기와 무한 스크롤</span></div>
                <div class="intro-row"><strong>섬기는 사람들</strong><span>목사·전도사·선교사와 각 부서에서 섬기는 분들을 직분 순서대로</span></div>
                <div class="intro-row"><strong>오시는 길</strong><span>본당·교육관 지도, 예배 시간, 청년부 차량 픽업 안내</span></div>
            </div>
        </section>

        <section class="intro-band intro-band-dark intro-reveal">
            <p class="intro-kicker">지금까지 · So far</p>
            <h2 class="intro-h2">이미 담겨 있는 것들</h2>
            <p class="intro-lede">비어 있는 껍데기가 아니라, 교회의 실제 기록이 들어가 있습니다.</p>

            <div class="intro-stats">
                <div class="intro-stat"><span class="intro-n" data-intro-count="16">0</span><span class="intro-l">등록된 성도</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="11">0</span><span class="intro-l">교회 소식</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="44">0</span><span class="intro-l">갤러리 사진</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="14">0</span><span class="intro-l">사진 앨범</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="7">0</span><span class="intro-l">예배 영상</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="8">0</span><span class="intro-l">교회 행사</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="9">0</span><span class="intro-l">부서</span></div>
                <div class="intro-stat"><span class="intro-n" data-intro-count="1">0</span><span class="intro-l">주보 (테스트 PDF)</span></div>
            </div>
        </section>

        <section class="intro-band intro-band-soft intro-reveal">
            <p class="intro-kicker">관리 · Management</p>
            <h2 class="intro-h2">주보 한 장 올리는 데 세 단계</h2>
            <p class="intro-lede">관리자가 클릭 몇 번으로 할 수 있고, 휴대폰으로도 됩니다.</p>

            <div class="intro-flow" data-intro-flow>
                <div class="intro-step" data-intro-step>
                    <span class="intro-num">1</span>
                    <h3 class="intro-h3">관리자 화면 열기</h3>
                    <p>왼쪽 메뉴에서 <strong>주보</strong>를 누릅니다.</p>
                </div>
                <div class="intro-step" data-intro-step>
                    <span class="intro-num">2</span>
                    <h3 class="intro-h3">파일 올리기</h3>
                    <p>새로운 주보를 누르고 제목과 날짜를 적은 뒤 PDF를 끌어다 놓습니다.</p>
                </div>
                <div class="intro-step" data-intro-step>
                    <span class="intro-num">3</span>
                    <h3 class="intro-h3">저장</h3>
                    <p>저장하면 곧바로 홈페이지 주보 페이지에 나타납니다. 따로 할 일은 없습니다.</p>
                </div>
            </div>

            <div class="intro-cards">
                <div class="intro-card">
                    <h3 class="intro-h3">소식과 행사</h3>
                    <p>주보의 광고를 그대로 옮겨 적으면 홈 화면에 바로 올라갑니다. 중요한 소식은 맨 위에 고정할 수 있고,
                        소식을 쓸 때 <strong>하이라이트</strong>를 켜면 홈 화면 가장 큰 자리에 사진과 함께 걸립니다.
                        하이라이트는 한 번에 하나만 지정됩니다.</p>
                </div>
                <div class="intro-card">
                    <h3 class="intro-h3">사진과 영상</h3>
                    <p>사진은 앨범을 만들어 여러 장을 한 번에 올립니다. 예배 영상은 유튜브 주소만 넣으면
                        제목과 미리보기가 자동으로 붙습니다.</p>
                </div>
                <div class="intro-card">
                    <h3 class="intro-h3">헌금 내역</h3>
                    <p>주보에 실리는 주간 헌금 통계를 그대로 입력합니다. 개인별 헌금도 따로 기록할 수 있습니다.</p>
                </div>
                <div class="intro-card">
                    <h3 class="intro-h3">예배 시간·주소·계좌</h3>
                    <p>한 화면에서 고치면 홈·예배 안내·오시는 길에 동시에 반영됩니다.
                        어느 항목이 어디에 나타나는지 화면에 적혀 있습니다.</p>
                </div>
            </div>
        </section>

        <section class="intro-band intro-reveal">
            <p class="intro-kicker">권한 · Who can do what</p>
            <h2 class="intro-h2">맡은 만큼만 열립니다</h2>
            <p class="intro-lede">한 사람이 모든 것을 짊어지지 않도록, 일에 따라 보이는 화면이 다릅니다.</p>

            <div class="intro-tablescroll">
                <table class="intro-roles">
                    <thead>
                        <tr><th>역할</th><th>맡는 분</th><th>할 수 있는 일</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>관리자</td>
                            <td>담임목사 · 사무</td>
                            <td>모든 콘텐츠, 성도 명부와 셀, 헌금 내역, 예배 시간과 계좌 설정, 계정 관리</td>
                        </tr>
                        <tr>
                            <td>콘텐츠 담당</td>
                            <td>사무 · 홍보</td>
                            <td>소식, 행사, 주보, 예배 영상, 사진과 앨범. 성도 개인정보와 계좌에는 접근할 수 없습니다</td>
                        </tr>
                        <tr>
                            <td>재정 담당</td>
                            <td>재정부</td>
                            <td>주간 헌금과 개인 헌금 기록만. 성도 명부는 열리지 않습니다</td>
                        </tr>
                        <tr>
                            <td>성도</td>
                            <td>성도</td>
                            <td>로그인해서 주간 헌금 내역을 봅니다. 관리 화면은 열리지 않습니다</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="intro-band intro-band-soft intro-reveal">
            <p class="intro-kicker">교적 · The roster</p>
            <h2 class="intro-h2">교인 관리도 이 안에서</h2>
            <p class="intro-lede">이름과 연락처만이 아니라, 교회가 실제로 쓰는 방식대로 담았습니다.</p>

            <div class="intro-cards intro-cards-three">
                <div class="intro-card">
                    <h3 class="intro-h3">성도 명부</h3>
                    <p>직분, 부서, 세례 여부, 등록일과 새가족 수료일, 가족 관계까지. 심방 기록은 내부 메모로만 남습니다.</p>
                </div>
                <div class="intro-card">
                    <h3 class="intro-h3">셀과 부서</h3>
                    <p>셀장을 지정하면 셀 이름이 자동으로 붙습니다. 부서별 인원도 한눈에 보입니다.</p>
                </div>
                <div class="intro-card">
                    <h3 class="intro-h3">가입 신청 승인</h3>
                    <p>성도가 홈페이지에서 가입을 신청하면, 교적부와 대조해 확인한 뒤 승인합니다.
                        누가 어떤 방법으로 확인했는지 남습니다.</p>
                </div>
            </div>
        </section>

        <section class="intro-band intro-reveal">
            <p class="intro-kicker">보안 · Safeguards</p>
            <h2 class="intro-h2">교회의 자료를 지키는 장치</h2>

            <ul class="intro-checks">
                <li>
                    <svg class="intro-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
                    <span><strong>2단계 인증</strong>관리자 계정은 비밀번호만으로 열리지 않습니다. 휴대폰 인증 앱의 6자리 숫자를 한 번 더 확인합니다.</span>
                </li>
                <li>
                    <svg class="intro-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
                    <span><strong>헌금 내역은 로그인한 성도에게만</strong>주보를 받는 성도만 보시도록, 로그인하지 않으면 화면에 나타나지 않습니다.</span>
                </li>
                <li>
                    <svg class="intro-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
                    <span><strong>매일 자동 백업</strong>모든 기록이 매일 새벽 안전한 곳에 복사됩니다. 서버에 문제가 생겨도 자료는 남습니다.</span>
                </li>
                <li>
                    <svg class="intro-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
                    <span><strong>개인정보는 최소한으로</strong>성도의 생년월일·연락처·주소는 관리자만 볼 수 있습니다.</span>
                </li>
            </ul>
        </section>

        <section class="intro-band intro-band-soft intro-reveal">
            <p class="intro-kicker">다음 · What we need</p>
            <h2 class="intro-h2">교회의 결정이 필요한 것</h2>
            <p class="intro-lede">기술로 해결되는 부분은 준비돼 있습니다. 남은 것은 교회가 정해 주셔야 할 일들입니다.</p>

            <ol class="intro-next">
                <li>
                    <h3 class="intro-h3">실제 주보 올리기</h3>
                    <p>지금 올라가 있는 한 건은 시험용 파일입니다. 실제 주보를 올리면 매주 쌓이기 시작합니다.</p>
                </li>
                <li>
                    <h3 class="intro-h3">교회 이메일과 구글 계정</h3>
                    <p>juneun.com 주소로 교회 이메일을 만들 수 있습니다. 등록 비영리단체는 구글 업무용 계정을 무료로 쓸 수 있어,
                        유튜브 채널과 통계도 개인 계정이 아닌 교회 소유로 옮길 수 있습니다.</p>
                </li>
                <li>
                    <h3 class="intro-h3">구글 지도 등록</h3>
                    <p>"브리즈번 한인교회"를 검색했을 때 지도와 함께 뜨려면 교회 명의의 등록이 필요합니다.
                        새로 오시는 분이 교회를 찾는 가장 큰 통로입니다.</p>
                </li>
                <li>
                    <h3 class="intro-h3">성도들께 안내</h3>
                    <p>헌금 내역을 보시려면 홈페이지에서 가입 신청을 하셔야 합니다.
                        광고 시간에 한 번 알려 주시면 신청이 들어오기 시작합니다.</p>
                </li>
            </ol>
        </section>

    </div>

    <style>
        .intro {
            --intro-navy: #16223c;
            --intro-cream: #f4f1ea;
            --intro-accent: #004aad;
            --intro-accent-soft: #dce7f7;
            --intro-success: #2fbf71;
            --intro-ink: #16223c;
            --intro-muted: #5c6f94;
            --intro-ground: #ffffff;
            --intro-soft: #f4f1ea;
            --intro-rule: rgba(22, 34, 60, 0.16);
            display: grid;
            gap: 1.5rem;
            font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Pretendard", "Noto Sans KR", "Malgun Gothic", sans-serif;
            color: var(--intro-ink);
            line-height: 1.6;
            word-break: keep-all;
        }
        .dark .intro {
            --intro-ink: #eef2f8;
            --intro-muted: #9aabc7;
            --intro-ground: #16203a;
            --intro-soft: #111c33;
            --intro-accent: #7aa9ec;
            --intro-accent-soft: rgba(122, 169, 236, 0.18);
            --intro-rule: rgba(238, 242, 248, 0.18);
        }

        .intro-band {
            background: var(--intro-ground);
            border: 0.0625rem solid var(--intro-rule);
            border-radius: 1rem;
            padding: clamp(1.5rem, 4vw, 2.75rem);
        }
        .intro-band-soft { background: var(--intro-soft); }
        .intro-band-dark {
            background: var(--intro-navy);
            border-color: var(--intro-navy);
            color: var(--intro-cream);
        }
        .intro-band-dark .intro-kicker { color: #8fb4ee; }
        .intro-band-dark .intro-h1,
        .intro-band-dark .intro-h2 { color: #ffffff; }
        .intro-band-dark .intro-lede { color: rgba(244, 241, 234, 0.72); }

        .intro-kicker {
            margin: 0 0 0.625rem;
            font-size: 0.6875rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            color: var(--intro-accent);
        }
        .intro-h1 {
            margin: 0 0 1rem;
            font-size: clamp(1.75rem, 4.5vw, 2.75rem);
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .intro-h2 {
            margin: 0 0 0.5rem;
            font-size: clamp(1.375rem, 3vw, 1.875rem);
            line-height: 1.3;
            font-weight: 700;
            letter-spacing: -0.015em;
        }
        .intro-h3 { margin: 0 0 0.25rem; font-size: 1rem; font-weight: 700; }
        .intro-lede { margin: 0; color: var(--intro-muted); max-width: 42rem; }
        .intro-hero-lede { margin: 0; color: rgba(244, 241, 234, 0.78); max-width: 34rem; font-size: 1.0625rem; }

        .intro-url {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.625rem 1.125rem;
            border: 0.125rem solid rgba(244, 241, 234, 0.35);
            border-radius: 0.75rem;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            transition: background-color 200ms ease, border-color 200ms ease;
        }
        .intro-url:hover { background: rgba(244, 241, 234, 0.1); border-color: rgba(244, 241, 234, 0.6); }
        .intro-dot { width: 0.5rem; height: 0.5rem; border-radius: 999px; background: var(--intro-success); }

        .intro-rows { margin-top: 1.5rem; border-top: 0.125rem solid var(--intro-ink); }
        .intro-row {
            display: grid;
            gap: 0.125rem 1.5rem;
            padding-block: 0.9375rem;
            border-bottom: 0.0625rem solid var(--intro-rule);
        }
        @media (min-width: 40rem) {
            .intro-row { grid-template-columns: minmax(0, 12rem) minmax(0, 1fr); align-items: baseline; }
        }
        .intro-row strong { font-size: 1rem; }
        .intro-row span { color: var(--intro-muted); font-size: 0.9375rem; }

        .intro-stats {
            margin-top: 2rem;
            display: grid;
            gap: 1.5rem 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 48rem) { .intro-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .intro-stat { display: grid; gap: 0.375rem; }
        .intro-n {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
            color: #ffffff;
        }
        .intro-l { font-size: 0.8125rem; color: rgba(244, 241, 234, 0.62); }

        .intro-flow { margin-top: 1.75rem; display: grid; gap: 1rem; }
        @media (min-width: 48rem) { .intro-flow { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .intro-step {
            background: var(--intro-ground);
            border: 0.0625rem solid var(--intro-rule);
            border-radius: 0.875rem;
            padding: 1.25rem;
            transition: border-color 320ms ease, transform 320ms ease, box-shadow 320ms ease;
        }
        .intro-step.on {
            border-color: var(--intro-accent);
            transform: translateY(-0.25rem);
            box-shadow: 0 0.625rem 1.75rem rgba(0, 74, 173, 0.14);
        }
        .intro-step p { margin: 0; color: var(--intro-muted); font-size: 0.9375rem; }
        .intro-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            margin-bottom: 0.75rem;
            border-radius: 999px;
            background: var(--intro-accent-soft);
            color: var(--intro-accent);
            font-weight: 800;
            font-size: 0.8125rem;
        }
        .intro-step.on .intro-num { background: var(--intro-accent); color: #ffffff; }

        .intro-cards { margin-top: 1.75rem; display: grid; gap: 1rem; }
        @media (min-width: 44rem) { .intro-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 60rem) { .intro-cards-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .intro-card {
            background: var(--intro-ground);
            border: 0.0625rem solid var(--intro-rule);
            border-radius: 0.875rem;
            padding: 1.25rem;
        }
        .intro-card p { margin: 0; color: var(--intro-muted); font-size: 0.9375rem; }

        .intro-tablescroll { overflow-x: auto; margin-top: 1.5rem; }
        .intro-roles { width: 100%; border-collapse: collapse; font-size: 0.9375rem; }
        .intro-roles th,
        .intro-roles td {
            text-align: left;
            padding: 0.75rem 0.625rem;
            border-bottom: 0.0625rem solid var(--intro-rule);
            vertical-align: top;
        }
        .intro-roles thead th {
            border-bottom: 0.125rem solid var(--intro-ink);
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            color: var(--intro-muted);
            font-weight: 700;
        }
        .intro-roles td:first-child { font-weight: 700; white-space: nowrap; }

        .intro-checks { list-style: none; padding: 0; margin: 1.5rem 0 0; display: grid; gap: 0.875rem; }
        .intro-checks li { display: grid; grid-template-columns: 1.25rem minmax(0, 1fr); gap: 0.75rem; align-items: start; }
        .intro-checks span { color: var(--intro-muted); font-size: 0.9375rem; }
        .intro-checks strong { display: block; color: var(--intro-ink); }
        .intro-tick { width: 1.25rem; height: 1.25rem; margin-top: 0.1875rem; color: var(--intro-success); }

        .intro-next { counter-reset: intro-n; list-style: none; padding: 0; margin: 1.5rem 0 0; display: grid; gap: 0.875rem; }
        .intro-next li {
            display: grid;
            grid-template-columns: 2rem minmax(0, 1fr);
            gap: 1rem;
            padding: 1.125rem;
            background: var(--intro-ground);
            border: 0.0625rem solid var(--intro-rule);
            border-radius: 0.875rem;
        }
        .intro-next li::before {
            counter-increment: intro-n;
            content: counter(intro-n);
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            background: var(--intro-ink);
            color: var(--intro-ground);
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 0.875rem;
        }
        .intro-next p { margin: 0.125rem 0 0; color: var(--intro-muted); font-size: 0.9375rem; }

        .intro-reveal { opacity: 0; transform: translateY(1rem); transition: opacity 560ms ease, transform 560ms ease; }
        .intro-reveal.in { opacity: 1; transform: none; }

        @media (prefers-reduced-motion: reduce) {
            .intro-reveal { opacity: 1; transform: none; transition: none; }
            .intro-step, .intro-url { transition: none; }
        }
    </style>

    <script>
        (function () {
            var root = document.querySelector('.intro');

            if (! root || root.dataset.introReady) {
                return;
            }

            root.dataset.introReady = 'true';

            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var supported = 'IntersectionObserver' in window;

            /** Reveal each band as it scrolls into view. */
            var bands = root.querySelectorAll('.intro-reveal');

            if (reduce || ! supported) {
                bands.forEach(function (band) { band.classList.add('in'); });
            } else {
                var seen = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (! entry.isIntersecting) { return; }
                        entry.target.classList.add('in');
                        seen.unobserve(entry.target);
                    });
                }, { rootMargin: '0px 0px -8% 0px' });

                bands.forEach(function (band) { seen.observe(band); });
            }

            /** Count each figure up once, when its band first appears. */
            function runCount(el) {
                var target = parseInt(el.getAttribute('data-intro-count'), 10);

                if (reduce) {
                    el.textContent = target.toLocaleString('ko-KR');

                    return;
                }

                var started = null;

                function tick(now) {
                    if (started === null) { started = now; }

                    var progress = Math.min((now - started) / 1100, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.round(target * eased).toLocaleString('ko-KR');

                    if (progress < 1) { window.requestAnimationFrame(tick); }
                }

                window.requestAnimationFrame(tick);
            }

            var figures = root.querySelectorAll('[data-intro-count]');

            if (! supported) {
                figures.forEach(runCount);
            } else {
                var counted = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (! entry.isIntersecting) { return; }
                        runCount(entry.target);
                        counted.unobserve(entry.target);
                    });
                }, { threshold: 0.4 });

                figures.forEach(function (figure) { counted.observe(figure); });
            }

            /** Walk the three upload steps so the sequence reads at a glance. */
            var flow = root.querySelector('[data-intro-flow]');

            if (flow && ! reduce && supported) {
                var steps = flow.querySelectorAll('[data-intro-step]');
                var timer = null;
                var at = 0;

                function advance() {
                    steps.forEach(function (step, index) { step.classList.toggle('on', index === at); });
                    at = (at + 1) % steps.length;
                }

                new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && timer === null) {
                            advance();
                            timer = window.setInterval(advance, 1900);
                        } else if (! entry.isIntersecting && timer !== null) {
                            window.clearInterval(timer);
                            timer = null;
                        }
                    });
                }, { threshold: 0.3 }).observe(flow);
            }
        })();
    </script>
</x-filament-panels::page>
