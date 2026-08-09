{{-- Standalone introduction to the site, shown to church leadership.
     Served in its own document so its styles cannot collide with the
     admin panel; the Filament page frames it. --}}
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>브리즈번 주는교회 홈페이지</title>
</head>
<body>

<style>
  :root {
    --navy: #16223c;
    --navy-900: #0d1730;
    --navy-700: #233559;
    --navy-400: #5c6f94;
    --cream: #f4f1ea;
    --paper: #ffffff;
    --accent: #004aad;
    --accent-100: #dce7f7;
    --success: #2fbf71;
    --line: rgba(22, 34, 60, 0.16);

    --ground: var(--paper);
    --ground-soft: var(--cream);
    --ink: var(--navy);
    --ink-muted: var(--navy-400);
    --ink-strong: var(--navy-900);
    --rule: var(--line);
    --link: var(--accent);

    --kr: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Pretendard",
      "Noto Sans KR", "Malgun Gothic", sans-serif;

    --measure: 72rem;
    --pad: clamp(1.25rem, 4vw, 3.5rem);
  }

  @media (prefers-color-scheme: dark) {
    :root {
      --ground: #0b1120;
      --ground-soft: #111c33;
      --ink: #eef2f8;
      --ink-muted: #9aabc7;
      --ink-strong: #ffffff;
      --rule: rgba(238, 242, 248, 0.18);
      --link: #7aa9ec;
      --accent-100: rgba(122, 169, 236, 0.16);
    }
  }

  :root[data-theme="dark"] {
    --ground: #0b1120;
    --ground-soft: #111c33;
    --ink: #eef2f8;
    --ink-muted: #9aabc7;
    --ink-strong: #ffffff;
    --rule: rgba(238, 242, 248, 0.18);
    --link: #7aa9ec;
    --accent-100: rgba(122, 169, 236, 0.16);
  }

  :root[data-theme="light"] {
    --ground: #ffffff;
    --ground-soft: #f4f1ea;
    --ink: #16223c;
    --ink-muted: #5c6f94;
    --ink-strong: #0d1730;
    --rule: rgba(22, 34, 60, 0.16);
    --link: #004aad;
    --accent-100: #dce7f7;
  }

  body {
    margin: 0;
    background: var(--ground);
    color: var(--ink);
    font-family: var(--kr);
    line-height: 1.6;
    word-break: keep-all;
    overflow-wrap: break-word;
    -webkit-font-smoothing: antialiased;
  }

  .wrap {
    max-width: var(--measure);
    margin-inline: auto;
    padding-inline: var(--pad);
  }

  section { padding-block: clamp(3rem, 8vw, 6rem); }
  section.soft { background: var(--ground-soft); }
  section.dark { background: var(--navy); color: #f4f1ea; }
  section.dark .kicker { color: #8fb4ee; }
  section.dark .lede { color: rgba(244, 241, 234, 0.72); }

  .kicker {
    font-size: 0.6875rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    color: var(--link);
    margin: 0 0 0.75rem;
  }

  h1 {
    font-size: clamp(2rem, 6vw, 3.5rem);
    line-height: 1.15;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0 0 1.25rem;
    text-wrap: balance;
  }

  h2 {
    font-size: clamp(1.5rem, 3.6vw, 2.25rem);
    line-height: 1.25;
    font-weight: 700;
    letter-spacing: -0.015em;
    margin: 0 0 0.75rem;
    text-wrap: balance;
  }

  h3 {
    font-size: 1.0625rem;
    font-weight: 700;
    margin: 0 0 0.375rem;
  }

  .lede {
    font-size: clamp(1rem, 1.6vw, 1.125rem);
    color: var(--ink-muted);
    max-width: 44rem;
    margin: 0;
  }

  .hero {
    background: var(--navy);
    color: #f4f1ea;
    padding-block: clamp(3.5rem, 10vw, 7rem);
  }
  .hero h1 { color: #ffffff; }
  .hero .kicker { color: #8fb4ee; }
  .hero p { color: rgba(244, 241, 234, 0.78); font-size: clamp(1rem, 1.8vw, 1.25rem); max-width: 40rem; }

  .url {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
    padding: 0.75rem 1.25rem;
    border: 0.125rem solid rgba(244, 241, 234, 0.35);
    border-radius: 0.75rem;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    letter-spacing: 0.01em;
    transition: background-color 200ms ease, border-color 200ms ease;
  }
  .url:hover { background: rgba(244, 241, 234, 0.1); border-color: rgba(244, 241, 234, 0.6); }
  .url .dot { width: 0.5rem; height: 0.5rem; border-radius: 999px; background: var(--success); flex: none; }

  .grid { display: grid; gap: 1.25rem; }
  @media (min-width: 40rem) { .grid.two { grid-template-columns: repeat(2, 1fr); } }
  @media (min-width: 56rem) { .grid.three { grid-template-columns: repeat(3, 1fr); } }

  .card {
    background: var(--ground);
    border: 0.0625rem solid var(--rule);
    border-radius: 1rem;
    padding: 1.5rem;
  }
  section.soft .card { background: var(--paper); }
  @media (prefers-color-scheme: dark) { section.soft .card { background: #0b1120; } }
  :root[data-theme="dark"] section.soft .card { background: #0b1120; }
  :root[data-theme="light"] section.soft .card { background: #ffffff; }

  .card p { margin: 0; color: var(--ink-muted); font-size: 0.9375rem; }

  .pagelist { border-top: 0.125rem solid var(--ink); margin-top: 2rem; }
  .pagerow {
    display: grid;
    gap: 0.25rem 1.5rem;
    padding-block: 1.125rem;
    border-bottom: 0.0625rem solid var(--rule);
  }
  @media (min-width: 40rem) {
    .pagerow { grid-template-columns: minmax(0, 14rem) minmax(0, 1fr); align-items: baseline; }
  }
  .pagerow strong { font-size: 1.0625rem; }
  .pagerow span { color: var(--ink-muted); font-size: 0.9375rem; }

  .stats { display: grid; gap: 1.5rem 1rem; grid-template-columns: repeat(2, 1fr); margin-top: 2.5rem; }
  @media (min-width: 48rem) { .stats { grid-template-columns: repeat(4, 1fr); } }
  .stat .n {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
    color: #ffffff;
  }
  .stat .l { font-size: 0.875rem; color: rgba(244, 241, 234, 0.62); margin-top: 0.5rem; }

  .flow { margin-top: 2rem; display: grid; gap: 1rem; }
  @media (min-width: 48rem) { .flow { grid-template-columns: repeat(3, 1fr); } }
  .step {
    position: relative;
    border: 0.0625rem solid var(--rule);
    border-radius: 1rem;
    padding: 1.5rem 1.25rem 1.25rem;
    background: var(--ground);
    transition: border-color 320ms ease, transform 320ms ease, box-shadow 320ms ease;
  }
  section.soft .step { background: var(--paper); }
  :root[data-theme="dark"] section.soft .step,
  :root[data-theme="dark"] .step { background: #0b1120; }
  @media (prefers-color-scheme: dark) { section.soft .step, .step { background: #0b1120; } }
  :root[data-theme="light"] section.soft .step, :root[data-theme="light"] .step { background: #ffffff; }

  .step.on {
    border-color: var(--link);
    transform: translateY(-0.25rem);
    box-shadow: 0 0.75rem 2rem rgba(0, 74, 173, 0.14);
  }
  .step .num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem; height: 2rem;
    border-radius: 999px;
    background: var(--accent-100);
    color: var(--link);
    font-weight: 800;
    font-size: 0.875rem;
    margin-bottom: 0.875rem;
  }
  .step.on .num { background: var(--link); color: #ffffff; }

  .roles { width: 100%; border-collapse: collapse; margin-top: 2rem; font-size: 0.9375rem; }
  .roles th, .roles td { text-align: left; padding: 0.875rem 0.75rem; border-bottom: 0.0625rem solid var(--rule); vertical-align: top; }
  .roles thead th { border-bottom: 0.125rem solid var(--ink); font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-muted); }
  .roles td:first-child { font-weight: 700; white-space: nowrap; }
  .tablescroll { overflow-x: auto; }

  .checks { list-style: none; padding: 0; margin: 1.5rem 0 0; display: grid; gap: 0.875rem; }
  .checks li { display: grid; grid-template-columns: 1.5rem 1fr; gap: 0.75rem; align-items: start; }
  .tick { width: 1.25rem; height: 1.25rem; margin-top: 0.1875rem; color: var(--success); flex: none; }
  .checks strong { display: block; }
  .checks span { color: var(--ink-muted); font-size: 0.9375rem; }

  .next { counter-reset: n; display: grid; gap: 1rem; margin-top: 2rem; }
  .next .item {
    display: grid; grid-template-columns: 2.25rem 1fr; gap: 1rem;
    border: 0.0625rem solid var(--rule); border-radius: 1rem; padding: 1.25rem;
  }
  .next .item::before {
    counter-increment: n; content: counter(n);
    width: 2.25rem; height: 2.25rem; border-radius: 999px;
    background: var(--ink); color: var(--ground);
    display: grid; place-items: center; font-weight: 800; font-size: 0.9375rem;
  }
  .next p { margin: 0.25rem 0 0; color: var(--ink-muted); font-size: 0.9375rem; }

  .reveal { opacity: 0; transform: translateY(1.25rem); transition: opacity 620ms ease, transform 620ms ease; }
  .reveal.in { opacity: 1; transform: none; }

  footer { padding-block: 3rem; border-top: 0.0625rem solid var(--rule); color: var(--ink-muted); font-size: 0.875rem; }

  @media (prefers-reduced-motion: reduce) {
    .reveal { opacity: 1; transform: none; transition: none; }
    .step, .url { transition: none; }
  }
</style>

<header class="hero">
  <div class="wrap">
    <p class="kicker reveal">Brisbane Juneun Church</p>
    <h1 class="reveal">교회 홈페이지가<br>문을 열었습니다</h1>
    <p class="reveal">예배 시간과 주보, 소식과 사진을 한곳에 모았습니다.
      그리고 이 모든 것을 교회가 직접 관리할 수 있습니다.</p>
    <a class="url reveal" href="https://www.juneun.com" target="_blank" rel="noopener">
      <span class="dot"></span> www.juneun.com
    </a>
  </div>
</header>

<section>
  <div class="wrap">
    <p class="kicker reveal">홈페이지 · Home</p>
    <h2 class="reveal">아홉 개의 화면</h2>
    <p class="lede reveal">처음 오시는 분이나 교인들이 알아야 할 것과 자주 찾는 걸 나눠 담았습니다.</p>

    <div class="pagelist reveal">
      <div class="pagerow"><strong>홈</strong><span>교회 소개, 이번 주 예배, 최근 소식과 설교 영상, 사진 슬라이드</span></div>
      <div class="pagerow"><strong>예배 안내</strong><span>다섯 예배의 시간과 장소, 지난 예배 영상</span></div>
      <div class="pagerow"><strong>교회 소식</strong><span>주보에 실리는 광고를 그대로 - 상단 고정과 하이라이트 지정</span></div>
      <div class="pagerow"><strong>교회 행사</strong><span>월별로 묶은 일정표</span></div>
      <div class="pagerow"><strong>주보</strong><span>주간 주보 PDF, 날짜순 보관</span></div>
      <div class="pagerow"><strong>헌금</strong><span>호주·한국 계좌 안내와 주간 헌금 내역</span></div>
      <div class="pagerow"><strong>갤러리</strong><span>앨범별 사진, 크게 보기와 무한 스크롤</span></div>
      <div class="pagerow"><strong>섬기는 사람들</strong><span>목사·전도사·선교사와 각 부서에서 섬기는 분들을 직분 순서대로</span></div>
      <div class="pagerow"><strong>오시는 길</strong><span>본당·교육관 지도, 예배 시간, 청년부 차량 픽업 안내</span></div>
    </div>
  </div>
</section>

<section class="dark">
  <div class="wrap">
    <p class="kicker reveal">지금까지 · So far</p>
    <h2 class="reveal" style="color:#fff">이미 담겨 있는 것들</h2>
    <p class="lede reveal">비어 있는 껍데기가 아니라, 교회의 실제 기록이 들어가 있습니다.</p>

    <div class="stats">
      <div class="stat reveal"><div class="n" data-count="16">0</div><div class="l">등록된 성도</div></div>
      <div class="stat reveal"><div class="n" data-count="11">0</div><div class="l">교회 소식</div></div>
      <div class="stat reveal"><div class="n" data-count="44">0</div><div class="l">갤러리 사진</div></div>
      <div class="stat reveal"><div class="n" data-count="14">0</div><div class="l">사진 앨범</div></div>
      <div class="stat reveal"><div class="n" data-count="7">0</div><div class="l">예배 영상</div></div>
      <div class="stat reveal"><div class="n" data-count="8">0</div><div class="l">교회 행사</div></div>
      <div class="stat reveal"><div class="n" data-count="9">0</div><div class="l">부서</div></div>
      <div class="stat reveal"><div class="n" data-count="1">0</div><div class="l">주보 (테스트 PDF)</div></div>
    </div>
  </div>
</section>

<section class="soft">
  <div class="wrap">
    <p class="kicker reveal">관리 · Management</p>
    <h2 class="reveal">주보 한 장 올리는 데 세 단계</h2>
    <p class="lede reveal">관리자가 클릭 몇 번으로 할 수 있고, 휴대폰으로도 됩니다.</p>

    <div class="flow" data-flow>
      <div class="step" data-step>
        <div class="num">1</div>
        <h3>관리자 화면 열기</h3>
        <p>juneun.com/admin 에 로그인합니다. 왼쪽 메뉴에서 <strong>주보</strong>를 누릅니다.</p>
      </div>
      <div class="step" data-step>
        <div class="num">2</div>
        <h3>파일 올리기</h3>
        <p>새로운 주보를 누르고 제목과 날짜를 적은 뒤 PDF를 끌어다 놓습니다.</p>
      </div>
      <div class="step" data-step>
        <div class="num">3</div>
        <h3>저장</h3>
        <p>저장하면 곧바로 홈페이지 주보 페이지에 나타납니다. 따로 할 일은 없습니다.</p>
      </div>
    </div>

    <div class="grid two" style="margin-top:2.5rem">
      <div class="card reveal">
        <h3>소식과 행사</h3>
        <p>주보의 광고를 그대로 옮겨 적으면 홈 화면에 바로 올라갑니다. 중요한 소식은 맨 위에 고정할 수 있고,
          소식을 쓸 때 <strong>하이라이트</strong>를 켜면 홈 화면 가장 큰 자리에 사진과 함께 걸립니다.
          하이라이트는 한 번에 하나만 지정됩니다.</p>
      </div>
      <div class="card reveal">
        <h3>사진과 영상</h3>
        <p>사진은 앨범을 만들어 여러 장을 한 번에 올립니다. 예배 영상은 유튜브 주소만 넣으면
          제목과 미리보기가 자동으로 붙습니다.</p>
      </div>
      <div class="card reveal">
        <h3>헌금 내역</h3>
        <p>주보에 실리는 주간 헌금 통계를 그대로 입력합니다. 개인별 헌금도 따로 기록할 수 있습니다.</p>
      </div>
      <div class="card reveal">
        <h3>예배 시간·주소·계좌</h3>
        <p>한 화면에서 고치면 홈·예배 안내·오시는 길에 동시에 반영됩니다.
          어느 항목이 어디에 나타나는지 화면에 적혀 있습니다.</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <p class="kicker reveal">권한 · Who can do what</p>
    <h2 class="reveal">맡은 만큼만 열립니다</h2>
    <p class="lede reveal">한 사람이 모든 것을 짊어지지 않도록, 일에 따라 보이는 화면이 다릅니다.</p>

    <div class="tablescroll reveal">
      <table class="roles">
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
  </div>
</section>

<section class="soft">
  <div class="wrap">
    <p class="kicker reveal">교적 · The roster</p>
    <h2 class="reveal">교인 관리도 이 안에서</h2>
    <p class="lede reveal">이름과 연락처만이 아니라, 교회가 실제로 쓰는 방식대로 담았습니다.</p>

    <div class="grid three" style="margin-top:2rem">
      <div class="card reveal">
        <h3>성도 명부</h3>
        <p>직분, 부서, 세례 여부, 등록일과 새가족 수료일, 가족 관계까지. 심방 기록은 내부 메모로만 남습니다.</p>
      </div>
      <div class="card reveal">
        <h3>셀과 부서</h3>
        <p>셀장을 지정하면 셀 이름이 자동으로 붙습니다. 부서별 인원도 한눈에 보입니다.</p>
      </div>
      <div class="card reveal">
        <h3>가입 신청 승인</h3>
        <p>성도가 홈페이지에서 가입을 신청하면, 교적부와 대조해 확인한 뒤 승인합니다.
          누가 어떤 방법으로 확인했는지 남습니다.</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <p class="kicker reveal">보안 · Safeguards</p>
    <h2 class="reveal">교회의 자료를 지키는 장치</h2>

    <ul class="checks reveal">
      <li>
        <svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
        <div><strong>2단계 인증</strong><span>관리자 계정은 비밀번호만으로 열리지 않습니다. 휴대폰 인증 앱의 6자리 숫자를 한 번 더 확인합니다.</span></div>
      </li>
      <li>
        <svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
        <div><strong>헌금 내역은 로그인한 성도에게만</strong><span>주보를 받는 성도만 보시도록, 로그인하지 않으면 화면에 나타나지 않습니다.</span></div>
      </li>
      <li>
        <svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
        <div><strong>매일 자동 백업</strong><span>모든 기록이 매일 새벽 안전한 곳에 복사됩니다. 서버에 문제가 생겨도 자료는 남습니다.</span></div>
      </li>
      <li>
        <svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5.5 5.5L20 7"/></svg>
        <div><strong>개인정보는 최소한으로</strong><span>성도의 생년월일·연락처·주소는 관리자만 볼 수 있습니다.</span></div>
      </li>
    </ul>
  </div>
</section>

<section class="soft">
  <div class="wrap">
    <p class="kicker reveal">다음 · What we need</p>
    <h2 class="reveal">교회의 결정이 필요한 것</h2>
    <p class="lede reveal">기술로 해결되는 부분은 준비돼 있습니다. 남은 것은 교회가 정해 주셔야 할 일들입니다.</p>

    <div class="next">
      <div class="item reveal">
        <div>
          <h3>실제 주보 올리기</h3>
          <p>지금 올라가 있는 한 건은 시험용 파일입니다. 실제 주보를 올리면 매주 쌓이기 시작합니다.</p>
        </div>
      </div>
      <div class="item reveal">
        <div>
          <h3>교회 이메일과 구글 계정</h3>
          <p>juneun.com 주소로 교회 이메일을 만들 수 있습니다. 등록 비영리단체는 구글 업무용 계정을 무료로 쓸 수 있어,
            유튜브 채널과 통계도 개인 계정이 아닌 교회 소유로 옮길 수 있습니다.</p>
        </div>
      </div>
      <div class="item reveal">
        <div>
          <h3>구글 지도 등록</h3>
          <p>"브리즈번 한인교회"를 검색했을 때 지도와 함께 뜨려면 교회 명의의 등록이 필요합니다.
            새로 오시는 분이 교회를 찾는 가장 큰 통로입니다.</p>
        </div>
      </div>
      <div class="item reveal">
        <div>
          <h3>성도들께 안내</h3>
          <p>헌금 내역을 보시려면 홈페이지에서 가입 신청을 하셔야 합니다.
            광고 시간에 한 번 알려 주시면 신청이 들어오기 시작합니다.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<script>
  (function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /** Reveal each block as it enters the viewport. */
    var reveals = document.querySelectorAll('.reveal');
    if (reduce || !('IntersectionObserver' in window)) {
      reveals.forEach(function (el) { el.classList.add('in'); });
    } else {
      var seen = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry, i) {
          if (!entry.isIntersecting) { return; }
          var el = entry.target;
          window.setTimeout(function () { el.classList.add('in'); }, Math.min(i, 6) * 70);
          seen.unobserve(el);
        });
      }, { rootMargin: '0px 0px -10% 0px' });
      reveals.forEach(function (el) { seen.observe(el); });
    }

    /** Count the figures up once, when the band first appears. */
    var counters = document.querySelectorAll('[data-count]');
    function runCount(el) {
      var target = parseInt(el.getAttribute('data-count'), 10);
      if (reduce) { el.textContent = target.toLocaleString('ko-KR'); return; }
      var started = null;
      var duration = 1100;
      function tick(now) {
        if (started === null) { started = now; }
        var p = Math.min((now - started) / duration, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased).toLocaleString('ko-KR');
        if (p < 1) { window.requestAnimationFrame(tick); }
      }
      window.requestAnimationFrame(tick);
    }
    if (!('IntersectionObserver' in window)) {
      counters.forEach(runCount);
    } else {
      var counted = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) { return; }
          runCount(entry.target);
          counted.unobserve(entry.target);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { counted.observe(el); });
    }

    /** Walk the three upload steps so the sequence reads at a glance. */
    var flow = document.querySelector('[data-flow]');
    if (flow && !reduce && 'IntersectionObserver' in window) {
      var steps = flow.querySelectorAll('[data-step]');
      var timer = null;
      var at = 0;
      function advance() {
        steps.forEach(function (s, i) { s.classList.toggle('on', i === at); });
        at = (at + 1) % steps.length;
      }
      var flowSeen = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && timer === null) {
            advance();
            timer = window.setInterval(advance, 1900);
          } else if (!entry.isIntersecting && timer !== null) {
            window.clearInterval(timer);
            timer = null;
          }
        });
      }, { threshold: 0.3 });
      flowSeen.observe(flow);
    }
  })();
</script>

</body>
</html>
