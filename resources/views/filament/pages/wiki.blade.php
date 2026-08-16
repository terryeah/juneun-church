<x-filament-panels::page>
    {{-- Every class is prefixed and every rule is scoped to .wiki, so
         nothing here can reach Filament's own card, step or grid
         styling, and nothing of Filament's reaches in.

         Written for a phone first and widened with min-width only, per
         the project's CSS rule. The page opens as a short index of
         eight questions; each part is a closed accordion beneath it.

         The three things staff get wrong - who may see which page,
         what 교적 등록 actually decides, and why a photograph they
         picked never reached the front page - are drawn rather than
         described: a permission table, a step flow, and a two-condition
         gate carry them.

         Motion is enabled by JavaScript (it adds .wiki-anim), so with
         no JavaScript, or with prefers-reduced-motion, everything is
         simply visible and still. --}}
    <div class="wiki">

        <p class="wiki-intro">이 홈페이지를 쓰다가 막히면 여기를 보세요. 자주 하는 일은 순서대로 적어 두었고, 헷갈리기 쉬운 것과 되돌릴 수 없는 것은 따로 표시해 두었습니다.</p>

        <nav class="wiki-toc" aria-label="목차">
            <a class="wiki-toc-item" href="#wiki-tasks"><span class="wiki-toc-num">1</span><span class="wiki-toc-text"><b>자주 하는 일</b><span>주보 &middot; 사진 &middot; 소식 올리기</span></span></a>
            <a class="wiki-toc-item" href="#wiki-who"><span class="wiki-toc-num">2</span><span class="wiki-toc-text"><b>누가 무엇을 보나</b><span>성도만 보는 페이지가 어디인지</span></span></a>
            <a class="wiki-toc-item" href="#wiki-roles"><span class="wiki-toc-num">3</span><span class="wiki-toc-text"><b>역할</b><span>누구에게 무엇을 열어줄지</span></span></a>
            <a class="wiki-toc-item" href="#wiki-menu"><span class="wiki-toc-num">4</span><span class="wiki-toc-text"><b>화면 안내</b><span>왼쪽 메뉴가 무엇을 하는 곳인지</span></span></a>
            <a class="wiki-toc-item" href="#wiki-files"><span class="wiki-toc-num">5</span><span class="wiki-toc-text"><b>사진과 파일</b><span>올리면 무슨 일이 일어나는지</span></span></a>
            <a class="wiki-toc-item" href="#wiki-login"><span class="wiki-toc-num">6</span><span class="wiki-toc-text"><b>로그인과 계정</b><span>2단계 인증, 성도 계정 만들기</span></span></a>
            <a class="wiki-toc-item" href="#wiki-auto"><span class="wiki-toc-num">7</span><span class="wiki-toc-text"><b>저절로 일어나는 일</b><span>아무도 안 했는데 생기는 것들</span></span></a>
            <a class="wiki-toc-item" href="#wiki-help"><span class="wiki-toc-num">8</span><span class="wiki-toc-text"><b>막혔을 때</b><span>증상별로 찾기</span></span></a>
        </nav>

        <details class="wiki-part" id="wiki-tasks" open>
            <summary class="wiki-summary">
                <span class="wiki-summary-num">1</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">자주 하는 일</span>
                    <span class="wiki-summary-note">주보 &middot; 사진 &middot; 소식 올리기</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <article class="wiki-task">
                    <h3 class="wiki-task-title">주보 올리기</h3>
                    <ol class="wiki-steps">
                        <li>왼쪽 <b>콘텐츠 &rsaquo; 주보</b>를 엽니다.</li>
                        <li>오른쪽 위 <b>새로운 주보</b>를 누릅니다.</li>
                        <li>제목은 <b>주일 예배 주보</b>처럼 적습니다. 날짜는 아래 발행일이 따로 보여주니 제목에 넣지 않아도 됩니다.</li>
                        <li>발행일에 그 주일 날짜를 고릅니다. 자료실에서 이 날짜순으로 정렬됩니다.</li>
                        <li>PDF 파일을 올리고 <b>저장</b>합니다.</li>
                    </ol>
                    <p class="wiki-note"><b>따로 켜거나 끌 것은 없습니다.</b> 자료실은 성도만 들어오는 페이지라, 올리시면 그대로 성도에게만 보입니다.</p>
                    <p class="wiki-note">성도가 자료실에서 <b>PDF 보기 &rarr;</b>를 누르면 <b>주보 PDF가 새 탭에서 바로 열립니다.</b> 문서와 똑같습니다. 열린 탭 이름은 <b>Bulletin_2026_08_16.pdf</b>처럼 파일 이름입니다. 예전에 주보만 따로 열리던 안내 페이지는 없앴습니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">사진 올리기</h3>
                    <ol class="wiki-steps">
                        <li>먼저 <b>미디어 &rsaquo; 앨범</b>에서 사진을 담을 앨범을 만듭니다. 앨범명과 행사 날짜만 있으면 됩니다.</li>
                        <li><b>미디어 &rsaquo; 사진</b>으로 가서 <b>업로드</b>를 누릅니다.</li>
                        <li>앨범을 고르고 사진 파일을 올린 뒤 저장합니다.</li>
                        <li>앨범의 <b>활성화</b>가 켜져 있어야 홈페이지에 나옵니다. 새로 만든 앨범은 처음부터 켜져 있습니다.</li>
                    </ol>
                    <p class="wiki-note">올린 사진은 자동으로 가볍게 바뀝니다. 아이폰 사진도 그대로 올리시면 됩니다. 자세한 건 <b>사진과 파일</b>에 있습니다.</p>
                    <p class="wiki-note"><b>앨범은 성도만 봅니다.</b> 홈페이지의 앨범 페이지는 로그인한 성도에게만 열리니, 아이들 얼굴이 담긴 사진도 마음 놓고 올리시면 됩니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">소식 쓰기</h3>
                    <ol class="wiki-steps">
                        <li><b>콘텐츠 &rsaquo; 교회 소식 &rsaquo; 새로운 소식</b>.</li>
                        <li>제목과 내용을 적습니다. 슬러그는 비워두면 알아서 만들어집니다.</li>
                        <li>맨 아래 스위치로 정합니다. <b>상단 고정</b>은 소식 목록 맨 위에, <b>하이라이트</b>는 홈 화면 큰 칸에 올립니다.</li>
                    </ol>
                    <p class="wiki-note"><b>성도의 이름이 들어가도 괜찮습니다.</b> 교회 소식 페이지는 성도만 들어오는 곳이라, 새가족 소개나 셀 배정처럼 이름이 들어가는 글도 그대로 쓰시면 됩니다. 글마다 켜 두시던 <b>성도 전용</b> 스위치는 없어졌습니다.</p>
                    <p class="wiki-warn"><b>다만 제목과 하이라이트는 홈 화면에 나갑니다.</b> 홈 화면은 누구나 보는 자리인데, 거기에 최근 소식 <b>제목</b> 네 개가 그대로 보이고 하이라이트로 켠 소식은 제목과 앞부분, 대표 이미지까지 보입니다. 제목에는 이름을 넣지 마시고, 이름이나 연락처로 시작하는 글은 하이라이트 대신 <b>상단 고정</b>까지만 쓰세요.</p>
                    <p class="wiki-note">하이라이트는 <b>한 번에 하나만</b> 가능합니다. 새로 켜면 먼저 있던 소식에서 자동으로 꺼지고, 저장 전에 어떤 소식이 내려가는지 알려줍니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">사진 목록에서 원하는 사진 찾기</h3>
                    <ol class="wiki-steps">
                        <li><b>미디어 &rsaquo; 사진</b>에서 목록 위 <b>필터</b>를 누릅니다.</li>
                        <li><b>앨범</b> 칸에서 앨범을 하나 고릅니다. 앨범 이름을 쳐서 찾을 수 있습니다.</li>
                        <li>홈 화면 사진 띠에 넣은 사진만 보려면 <b>홈 슬라이더</b>를 <b>넣은 사진</b>으로 바꿉니다. <b>전체 &middot; 넣은 사진 &middot; 넣지 않은 사진</b> 중에서 고릅니다.</li>
                    </ol>
                    <p class="wiki-note"><b>사진 일은 앨범을 고르는 것부터 시작하세요.</b> 사진이 3,199장이라, 앨범을 고르기 전에는 여러 앨범의 사진이 뒤섞여 보입니다. 끌어서 순서를 바꾸는 것도 <b>한 앨범 안에서만</b> 뜻이 있으니 앨범을 먼저 고른 뒤에 하세요.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">홈 화면 대표 사진 바꾸기</h3>
                    <ol class="wiki-steps">
                        <li><b>미디어 &rsaquo; 사진</b>에서 쓰고 싶은 사진을 엽니다.</li>
                        <li><b>사진을 손가락으로 누르면</b> 파일 이름이 복사됩니다. 맨 위 <b>파일 이름</b> 칸의 복사 버튼을 눌러도 됩니다.</li>
                        <li><b>기준 정보 &rsaquo; 사이트 설정</b>으로 가서 맨 아래 <b>홈 화면 대표 사진</b> 칸에 붙여넣고 저장합니다.</li>
                    </ol>
                    <p class="wiki-note">이름이 비어 있거나 맞지 않으면 홈 화면 맨 위가 사진 없이 나옵니다. 이 사진은 카카오톡 등에 링크를 공유할 때 미리보기 그림으로도 쓰입니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">홈 화면 사진 띠 채우기</h3>
                    <ol class="wiki-steps">
                        <li><b>미디어 &rsaquo; 사진</b>에서 <b>필터</b>로 앨범을 하나 고릅니다.</li>
                        <li>내보내고 싶은 사진들의 <b>왼쪽 네모를 체크</b>합니다. 여러 장을 한꺼번에 골라도 됩니다.</li>
                        <li>목록 위 <b>일괄 작업</b>을 열고 <b>홈 슬라이더에 넣기</b>를 누릅니다.</li>
                        <li>확인 창을 읽고 <b>넣기</b>를 누릅니다. 뺄 때는 같은 자리에서 <b>홈 슬라이더에서 빼기</b>입니다.</li>
                    </ol>
                    <p class="wiki-note"><b>한 장씩 열어서 저장하지 않아도 됩니다.</b> 예전에는 사진을 하나 열고 저장하고 다시 나오기를 되풀이해야 했는데, 이제 체크해서 한 번에 넣고 뺍니다.</p>

                    <h3 class="wiki-h3">홈 화면에 나오려면 두 가지가 다 켜져 있어야 합니다</h3>

                    <div class="wiki-gate">
                        <div class="wiki-gate-cond">
                            <b>사진에 체크</b>
                            <span>사진을 골라 <b>홈 슬라이더에 넣기</b></span>
                        </div>
                        <span class="wiki-gate-op">그리고</span>
                        <div class="wiki-gate-cond">
                            <b>그 사진이 든 앨범이 활성화</b>
                            <span><b>미디어 &rsaquo; 앨범</b>에서 <b>활성화</b>가 켜져 있어야</span>
                        </div>
                        <span class="wiki-gate-op" aria-hidden="true">&darr;</span>
                        <div class="wiki-gate-out">
                            <b>홈 화면 사진 띠에 나옵니다</b>
                            <span>둘 중 하나라도 빠지면 나오지 않습니다</span>
                        </div>
                    </div>

                    <p class="wiki-warn"><b>사진에 체크만 해서는 나오지 않습니다.</b> 실제로 네 장을 골랐는데 홈 화면에는 한 장만 나온 일이 있었습니다. 나머지 세 장이 <b>활성화가 꺼진 앨범</b>에 들어 있었기 때문입니다.</p>
                    <p class="wiki-note">이제는 넣고 나면 <b>어느 앨범이 꺼져 있는지 이름으로 알려줍니다.</b> 사진 목록에서도 별 모양으로 구분됩니다.</p>

                    <div class="wiki-rows">
                        <div class="wiki-row"><b><span class="wiki-star-on">&starf;</span> 노란 별</b><span>홈 화면 사진 띠에 나오고 있습니다</span></div>
                        <div class="wiki-row"><b><span class="wiki-star-off">&star;</span> 속이 빈 회색 별</b><span>체크는 되어 있지만 앨범이 비활성이라 나오지 않습니다. 별에 마우스를 올리면 까닭이 뜹니다</span></div>
                    </div>

                    <p class="wiki-warn"><b>사진 띠는 열 장까지입니다.</b> 자리가 모자라면 <b>한 장도 들어가지 않고</b> 통째로 거절되면서, 남은 자리가 몇 장인지 알려줍니다. 여덟 장이 들어 있는데 다섯 장을 고르시면 앞의 두 장만 들어가는 것이 아니라 다섯 장 모두 들어가지 않습니다. 몇 장을 빼신 뒤 다시 고르세요. 이미 들어 있는 사진을 또 골라도 자리를 두 번 차지하지는 않습니다.</p>
                    <p class="wiki-warn"><b>지금은 한 장도 들어 있지 않습니다.</b> 그래서 홈 화면 <b>주는교회의 순간들</b> 자리에는 글귀 두 줄만 있고 사진 띠가 아예 나오지 않습니다. 몇 장 넣어 주시면 그 아래에 사진 띠가 생깁니다. 앨범에서 알아서 채워 오던 예전 방식은 없어졌습니다.</p>
                    <p class="wiki-note"><b>여기에 넣는 것은 &ldquo;이 사진은 누구나 봐도 괜찮다&rdquo;는 뜻입니다.</b> 홈 화면은 로그인하지 않은 분도 보는 자리라, 넣기 전에 확인 창이 성도의 얼굴이 담긴 사진인지 한 번 묻습니다. 앨범 전체는 성도만 보지만 여기에 넣은 사진은 홈 화면에 나옵니다. 그 사진을 누르면 앨범으로 넘어가는데, 앨범은 성도 전용이라 로그인하지 않은 분에게는 거기서 로그인 안내가 나옵니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">가입 신청 승인하기</h3>
                    <ol class="wiki-steps">
                        <li><b>계정 &rsaquo; 가입 신청</b>. 메뉴 옆 숫자가 기다리는 신청 수입니다.</li>
                        <li>신청을 열면 <b>교적부 대조</b>표가 나옵니다. 신청서에 적은 내용과 교회가 가진 기록을 나란히 보여줍니다.</li>
                        <li><b>교적 처리</b>를 고릅니다. 교적에 있는 분과 연결하거나, 새 성도로 교적에 올리거나, <b>교적에 올리지 않고 계정만</b> 만듭니다.</li>
                        <li>확인이 되면 <b>확인 방법</b>을 고르고 승인합니다.</li>
                    </ol>
                    <p class="wiki-warn"><b>이름과 생년월일이 맞는 것만으로는 본인 확인이 되지 않습니다.</b> 섬기는 사람들 페이지에 이름이 공개되어 있기 때문입니다. 교회가 따로 적어 둔 전화번호나 이메일이 맞을 때, 또는 직접 아는 분일 때만 승인하세요.</p>
                    <p class="wiki-note">승인하면 신청자가 가입할 때 정한 비밀번호로 계정이 만들어집니다. 거절해도 기록만 남고, 그분은 다시 신청할 수 있습니다. 3번에서 무엇이 갈리는지는 <b>누가 무엇을 보나</b>의 그림을 보세요.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">헌금 내역 입력하기</h3>
                    <ol class="wiki-steps">
                        <li><b>재정 &rsaquo; 헌금 내역 &rsaquo; 새로 만들기</b>.</li>
                        <li>주일 날짜를 고르고, 항목마다 구분 &middot; 이름 &middot; 금액을 넣습니다.</li>
                    </ol>
                    <p class="wiki-note">헌금 페이지에서 <b>로그인한 성도에게만</b> 보입니다. 지난 12주까지 넘겨볼 수 있습니다. 재정부 역할을 받은 분은 이 메뉴만 보입니다.</p>
                </article>

            </div>
        </details>

        <details class="wiki-part" id="wiki-who">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">2</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">누가 무엇을 보나</span>
                    <span class="wiki-summary-note">성도만 보는 페이지가 어디인지</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <p class="wiki-lede">홈페이지는 <b>누구나 보는 페이지</b>와 <b>성도만 보는 페이지</b>로 나뉩니다. 글 하나하나가 아니라 <b>페이지 통째로</b> 나뉩니다.</p>

                <h3 class="wiki-h3">한눈에 보기</h3>

                <ul class="wiki-legend">
                    <li><b>손님</b><span>로그인하지 않은 분</span></li>
                    <li><b>일반회원</b><span>로그인은 했지만 교적에 없는 분</span></li>
                    <li><b>성도</b><span>교적에 이름이 있는 분</span></li>
                </ul>

                <div class="wiki-scroll">
                    <table class="wiki-matrix">
                        <caption class="wiki-sr">페이지별로 손님 &middot; 일반회원 &middot; 성도가 볼 수 있는지</caption>
                        <thead>
                            <tr>
                                <th scope="col">페이지</th>
                                <th scope="col">손님</th>
                                <th scope="col">일반회원</th>
                                <th scope="col">성도</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th scope="row">홈</th><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">예배 안내</th><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">섬기는 사람들</th><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">오시는 길</th><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr class="wiki-matrix-gate"><th scope="row">교회 소식</th><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">교회 행사</th><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">자료실 <span class="wiki-matrix-sub">주보 &middot; 문서</span></th><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">헌금</th><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                            <tr><th scope="row">앨범</th><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-no"><span aria-hidden="true">&times;</span><span class="wiki-sr">안 보임</span></td><td class="wiki-yes"><span aria-hidden="true">&check;</span><span class="wiki-sr">보임</span></td></tr>
                        </tbody>
                    </table>
                </div>

                <p class="wiki-warn"><b>기준은 로그인이 아니라 교적입니다.</b> 가운데 칸을 보세요. 계정만 있고 교적에 없는 분은 로그인해도 아래 다섯 페이지가 열리지 않습니다. 가입 신청은 누구나 넣을 수 있어서, 로그인을 기준으로 삼으면 교회가 모르는 분에게도 주보와 헌금 내역이 열리기 때문입니다.</p>

                <h3 class="wiki-h3">교적에 올릴지가 모든 것을 정합니다</h3>

                <ol class="wiki-flow">
                    <li class="wiki-flow-step">
                        <span class="wiki-flow-dot">1</span>
                        <div class="wiki-flow-card"><b>가입 신청</b><span>홈페이지에서 누구나 넣습니다. 신청만으로는 아무것도 열리지 않습니다.</span></div>
                    </li>
                    <li class="wiki-flow-step">
                        <span class="wiki-flow-dot">2</span>
                        <div class="wiki-flow-card"><b>관리자 승인</b><span>교적부 대조표로 본인을 확인하고 승인합니다. 계정이 만들어집니다.</span></div>
                    </li>
                    <li class="wiki-flow-step">
                        <span class="wiki-flow-dot">3</span>
                        <div class="wiki-flow-card"><b>교적 처리</b><span>여기서 갈립니다. 교적에 올릴지 말지를 고릅니다.</span></div>
                    </li>
                </ol>

                <div class="wiki-cards wiki-cards-2">
                    <div class="wiki-card wiki-card-open">
                        <span class="wiki-card-tag">교적에 올림</span>
                        <b class="wiki-card-title">성도</b>
                        <span class="wiki-card-body">소식 &middot; 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범 다섯 페이지가 <b>한꺼번에</b> 열립니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-shut">
                        <span class="wiki-card-tag">교적에 올리지 않음</span>
                        <b class="wiki-card-title">일반회원</b>
                        <span class="wiki-card-body">로그인은 되지만 그 다섯 페이지에는 안내만 보입니다. 나중에 성도가 되시면 그때 교적에 올리면 됩니다.</span>
                    </div>
                </div>

                <p class="wiki-warn"><b>우리 교회 성도가 아닌 분은 교적에 올리지 마세요.</b> 다섯 페이지가 교적 하나로 한꺼번에 열립니다.</p>

                <h3 class="wiki-h3">막혔을 때 보이는 화면</h3>

                <p>메뉴는 누구에게나 여덟 개 그대로 보입니다. 성도가 아닌 분이 성도 전용 메뉴를 누르면 페이지 이름과 안내 한 줄만 나옵니다. 목록을 아예 만들지 않기 때문에 소식 제목도, 주보 파일 이름도 그 화면에는 실려 나가지 않습니다.</p>

                <div class="wiki-cards wiki-cards-2">
                    <div class="wiki-card wiki-card-shut">
                        <span class="wiki-card-tag">손님</span>
                        <b class="wiki-card-title">로그인해 주세요</b>
                        <span class="wiki-card-body">&ldquo;… 성도에게만 공개됩니다&rdquo;와 <b>로그인</b> 링크. 로그인하면 보시던 그 페이지로 바로 돌아갑니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-no">
                        <span class="wiki-card-tag">일반회원</span>
                        <b class="wiki-card-title">교적 등록이 필요합니다</b>
                        <span class="wiki-card-body">교회 사무실 이메일이 함께 나옵니다. 그 주소는 <b>기준 정보 &rsaquo; 사이트 설정 &rsaquo; 연락처</b>의 <b>대표 이메일</b>입니다.</span>
                    </div>
                </div>

                <h3 class="wiki-h3">글마다 켜던 스위치는 없어졌습니다</h3>
                <p>주보 &middot; 문서 &middot; 교회 소식 &middot; 앨범에 있던 <b>성도 전용</b> 스위치를 모두 없앴습니다. 페이지가 통째로 닫혀 있으니 항목마다 정할 것이 없습니다.</p>
                <p class="wiki-note"><b>성도 이름 때문에 무언가를 켜 두실 일이 이제 없습니다.</b> 대신 <b>교적에 올리는 일</b>이 유일하게 중요한 결정이 되었습니다. 교적에 이름이 올라가는 순간 그분에게 다섯 페이지가 한꺼번에 열립니다.</p>

                <h3 class="wiki-h3">앨범 스위치는 하나입니다</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>활성화 꺼짐</b><span>아무에게도 안 보입니다. 성도가 로그인해도 안 보입니다. 아직 정리 중인 앨범</span></div>
                    <div class="wiki-row"><b>활성화 켜짐</b><span>로그인한 성도에게 보입니다. 앨범 페이지 자체가 성도 전용입니다</span></div>
                </div>
                <p class="wiki-note">홈페이지 앨범 화면 위쪽에 있던 <b>전체 &middot; 성도 전용 &middot; 모두 공개</b> 단추도 없어졌습니다. 남은 것은 <b>사진 &middot; 동영상</b> 구분뿐입니다.</p>

                <h3 class="wiki-h3">홈 화면은 그대로 열려 있습니다</h3>
                <p>처음 오시는 분이 반드시 보는 자리라 예전과 같습니다. 맨 위 대표 사진, 최근 소식 <b>제목</b> 네 개, 하이라이트로 켠 소식의 제목과 앞부분, 최근 예배 영상이 누구에게나 보입니다. 제목을 눌러 들어가면 그때 로그인 안내가 나옵니다.</p>
                <p class="wiki-warn"><b>홈 화면 사진 띠는 골라 넣은 사진만 나옵니다.</b> 앨범에서 알아서 채워 오지 않습니다. <b>미디어 &rsaquo; 사진</b>에서 사진을 체크해 <b>일괄 작업 &rsaquo; 홈 슬라이더에 넣기</b>로 넣으며, 넣는다는 것은 &ldquo;이 사진은 누구나 봐도 괜찮다&rdquo;는 뜻입니다. 그 사진이 든 앨범의 <b>활성화</b>까지 켜져 있어야 나옵니다. 순서는 <b>자주 하는 일</b>의 그림에 있습니다. 지금은 한 장도 들어 있지 않아 그 자리에 사진 띠가 나오지 않습니다.</p>

                <h3 class="wiki-h3">검색 엔진</h3>
                <p>구글에 알려주는 목록에는 <b>홈 &middot; 예배 안내 &middot; 오시는 길 &middot; 섬기는 사람들</b> 네 페이지만 들어 있습니다. 성도 전용 다섯 페이지와 그 안의 소식 &middot; 앨범은 검색 결과에 올라가지 않습니다.</p>

            </div>
        </details>

        <details class="wiki-part" id="wiki-roles">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">3</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">역할</span>
                    <span class="wiki-summary-note">누구에게 무엇을 열어줄지</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <p class="wiki-lede">계정을 만들 때 역할을 하나 줍니다. 역할이 그 사람에게 열리는 <b>관리자 메뉴</b>를 정합니다.</p>

                <div class="wiki-rows">
                    <div class="wiki-row"><b>관리자</b><span>재정을 포함해 거의 전부. 목사님 &middot; 사모님 &middot; 사무 담당</span></div>
                    <div class="wiki-row"><b>편집자</b><span>소식 &middot; 행사 &middot; 예배 영상 &middot; 주보 &middot; 문서 &middot; 앨범 &middot; 사진 &middot; 동영상. <b>교적부와 헌금, 사이트 설정은 열리지 않습니다</b></span></div>
                    <div class="wiki-row"><b>재정부</b><span>헌금 내역과 개인 헌금만. 다른 메뉴는 아예 보이지 않습니다</span></div>
                    <div class="wiki-row"><b>일반회원</b><span>관리자 화면은 못 씁니다. 홈페이지만 봅니다</span></div>
                </div>

                <p class="wiki-note">주보와 소식만 올려주실 분에게는 <b>편집자</b>가 맞습니다. 성도들의 인적사항과 헌금이 열리지 않기 때문입니다.</p>

                <p class="wiki-warn"><b>역할에 &lsquo;성도&rsquo;는 없습니다.</b> 성도인지 아닌지는 <b>교적에 있는지</b>로만 정해집니다. 가입 신청은 누구나 넣을 수 있어서, 승인했다는 것만으로는 우리 교회 성도라는 뜻이 되지 않기 때문입니다. 역할과 교적은 서로 다른 두 개의 열쇠입니다.</p>

                <div class="wiki-cards wiki-cards-2">
                    <div class="wiki-card wiki-card-key">
                        <span class="wiki-card-tag">역할</span>
                        <b class="wiki-card-title">관리자 화면</b>
                        <span class="wiki-card-body">왼쪽 메뉴에 무엇이 보이는지를 정합니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-key">
                        <span class="wiki-card-tag">교적</span>
                        <b class="wiki-card-title">홈페이지</b>
                        <span class="wiki-card-body">성도 전용 다섯 페이지가 열리는지를 정합니다.</span>
                    </div>
                </div>

            </div>
        </details>

        <details class="wiki-part" id="wiki-menu">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">4</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">화면 안내</span>
                    <span class="wiki-summary-note">왼쪽 메뉴가 무엇을 하는 곳인지</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <h3 class="wiki-h3">콘텐츠</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>교회 소식</b><span>주보에 실리는 광고를 그대로. 상단 고정과 하이라이트만 정합니다. 소식 페이지는 <b>성도만</b></span></div>
                    <div class="wiki-row"><b>교회 행사</b><span>일정. 행사 페이지에 월별로 모입니다. <b>성도만</b></span></div>
                    <div class="wiki-row"><b>예배 영상</b><span>유튜브 주소만 넣으면 됩니다. 새 설교는 자동으로도 들어옵니다. 누구나 봅니다</span></div>
                    <div class="wiki-row"><b>주보</b><span>주간 주보 PDF. 자료실의 첫 번째 탭. <b>성도만</b></span></div>
                    <div class="wiki-row"><b>문서</b><span>새가족 카드, 지출결의서 같은 서식. 자료실의 두 번째 탭. <b>성도만</b></span></div>
                </div>

                <h3 class="wiki-h3">미디어</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>앨범</b><span>사진과 영상을 담는 그릇. 여기서 정하는 것은 <b>활성화</b> 하나입니다</span></div>
                    <div class="wiki-row"><b>사진</b><span>사진 한 장씩. <b>필터</b>로 앨범을 먼저 고르고 보세요. 홈 화면 사진 띠에 넣을 사진을 <b>여기서만</b> 고르며, 체크한 뒤 <b>일괄 작업</b>으로 한꺼번에 넣고 뺍니다</span></div>
                    <div class="wiki-row"><b>동영상</b><span>유튜브 주소를 붙여넣으면 됩니다. 앨범에서 종류를 <b>동영상</b>으로 만든 뒤 여기에 담습니다</span></div>
                </div>

                <h3 class="wiki-h3">재정</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>헌금 내역</b><span>주일별 헌금. 헌금 페이지에 로그인한 성도에게만 보입니다</span></div>
                    <div class="wiki-row"><b>개인 헌금</b><span>성도별 헌금 기록</span></div>
                </div>

                <h3 class="wiki-h3">교적</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>성도</b><span>교적부. 인적사항, 직분, 셀, 그리고 <b>홈페이지 계정</b>도 여기서 만듭니다</span></div>
                    <div class="wiki-row"><b>셀</b><span>셀 편성</span></div>
                    <div class="wiki-row"><b>섬김이</b><span><b>읽기 전용.</b> 직분이나 부서가 있는 분이 자동으로 모입니다. 고치려면 성도에서</span></div>
                </div>

                <h3 class="wiki-h3">계정</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>가입 신청</b><span>홈페이지에서 들어온 계정 신청. 옆 숫자가 대기 건수</span></div>
                    <div class="wiki-row"><b>사이트 유저</b><span><b>읽기 전용.</b> 계정 목록만 봅니다. 계정은 성도 화면에서 만들고 없앱니다</span></div>
                </div>
                <p class="wiki-note">교적과 계정은 다릅니다. 교적부에 계신 분이 홈페이지 계정은 없을 수 있고, 그 반대도 됩니다. 계정은 언제나 <b>성도</b> 화면에서 만들고 없앱니다.</p>

                <h3 class="wiki-h3">기준 정보</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>사이트 설정</b><span>교회 이름, 주소, 연락처, 예배 시간, 헌금 계좌, 홈 대표 사진</span></div>
                    <div class="wiki-row"><b>예배 종류 &middot; 부서 &middot; 직분</b><span>다른 화면에서 고르는 목록들. 직분 순서가 섬기는 사람들 페이지 순서가 됩니다</span></div>
                </div>

                <h3 class="wiki-h3">모니터링</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>방문자 통계</b><span>홈페이지에 몇 명이 다녀갔는지</span></div>
                </div>

            </div>
        </details>

        <details class="wiki-part" id="wiki-files">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">5</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">사진과 파일</span>
                    <span class="wiki-summary-note">올리면 무슨 일이 일어나는지</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <h3 class="wiki-h3">올리면 저절로 일어나는 일</h3>
                <p>사진은 올리는 순간 <b>웹에 맞는 가벼운 형식으로 바뀝니다.</b> 크기도 알아서 줄고, 목록에 쓸 작은 그림도 함께 만들어집니다. 아이폰 사진, 안드로이드 사진, 카메라 RAW까지 그대로 올리시면 됩니다.</p>
                <p class="wiki-note"><b>원본은 서버에 남지 않습니다.</b> 바뀐 사진이 저장되는 즉시 지워집니다.</p>

                <h3 class="wiki-h3">한 장에 올릴 수 있는 크기</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>사진</b><span>64MB까지</span></div>
                    <div class="wiki-row"><b>주보 &middot; 문서 PDF</b><span>20MB까지 &middot; PDF만 됩니다</span></div>
                    <div class="wiki-row"><b>앨범 커버 &middot; 소식 대표 이미지</b><span>15MB까지</span></div>
                    <div class="wiki-row"><b>성도 사진 &middot; 설교 썸네일</b><span>10MB까지</span></div>
                </div>

                <h3 class="wiki-h3">안 되는 것</h3>
                <p class="wiki-warn"><b>바꾸지 못하는 사진은 저장되지 않고 거절됩니다.</b> 빨간 알림이 뜨면 다른 파일로 다시 올려주세요. 깨진 사진이 홈페이지에 올라가 있는 것보다 낫기 때문에 일부러 이렇게 했습니다.</p>
                <p>주보와 문서 칸은 <b>PDF만</b> 받습니다. 한글 파일이나 워드 파일은 PDF로 저장한 뒤 올려주세요.</p>

                <h3 class="wiki-h3">주보 링크를 남에게 보내면</h3>
                <p>파일이 비공개로 저장되고 홈페이지를 거쳐야만 열리기 때문에, 주소만 따로 복사해 단톡방에 올려도 받는 분에 따라 이렇게 갈립니다.</p>

                <div class="wiki-cards">
                    <div class="wiki-card wiki-card-shut">
                        <span class="wiki-card-tag">손님</span>
                        <b class="wiki-card-title">로그인 안내</b>
                        <span class="wiki-card-body">주보 안내와 <b>로그인</b> 링크가 나오고, 그 자리에서 로그인하면 바로 주보로 이어집니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-no">
                        <span class="wiki-card-tag">일반회원</span>
                        <b class="wiki-card-title">페이지를 찾을 수 없습니다</b>
                        <span class="wiki-card-body">로그인은 되어 있지만 교적에 없는 분입니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-open">
                        <span class="wiki-card-tag">성도</span>
                        <b class="wiki-card-title">주보 PDF가 열립니다</b>
                        <span class="wiki-card-body">중간에 아무 화면도 거치지 않고 <b>주보 PDF가 바로 열립니다.</b></span>
                    </div>
                </div>

                <p class="wiki-note"><b>PDF 파일 주소 자체도 마찬가지입니다.</b> 성도가 아니면 어느 경우에도 열리지 않습니다. 주보에는 셀 편성과 헌금 내역이 들어 있기 때문입니다. 예전에 올린 주보와 문서도 주소를 모두 새로 바꿔 두어, 밖으로 나갔던 옛 주소는 이제 아무것도 열지 못합니다.</p>
                <p class="wiki-warn"><b>다만 파일 자체가 넘어가는 것은 막지 못합니다.</b> 성도가 열어서 내려받은 PDF를 다른 분께 보내면 그것까지 되돌릴 방법은 없습니다. 링크는 안전하지만 파일은 그렇지 않다고 생각하시면 됩니다.</p>
                <p class="wiki-note">저장할 때 붙는 이름은 주보가 <b>Bulletin_2026_08_16.pdf</b>처럼 날짜, 문서는 <b>적어 두신 제목 그대로</b>입니다. 문서 제목을 알아보기 쉽게 적어 두시면 받는 분 컴퓨터에도 그 이름으로 남습니다.</p>

                <h3 class="wiki-h3">지우면 되돌릴 수 없습니다</h3>
                <p class="wiki-warn"><b>앨범을 지우면 그 안의 사진이 전부 함께 지워집니다.</b> 사진 파일까지 지워지고, 휴지통은 없습니다. 사진 800장짜리 앨범도 확인 한 번이면 사라집니다. 잠시 감추고 싶은 것이라면 지우지 말고 <b>활성화만 꺼 두세요.</b></p>
                <p>주보 &middot; 문서 &middot; 사진을 지우면 파일도 함께 지워집니다. 다만 인터넷에 이미 퍼진 그림은 한동안 남아 있을 수 있습니다. 급히 내려야 할 사진이라면 지운 뒤에 알려주세요.</p>

            </div>
        </details>

        <details class="wiki-part" id="wiki-login">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">6</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">로그인과 계정</span>
                    <span class="wiki-summary-note">2단계 인증, 성도 계정 만들기</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <p class="wiki-lede">교회 소식 &middot; 교회 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범은 로그인해야 보입니다. 성도님들께 계정을 만들어 드릴 일이 예전보다 많아졌습니다.</p>

                <h3 class="wiki-h3">성도가 계정을 갖는 두 가지 길</h3>
                <div class="wiki-cards wiki-cards-2">
                    <div class="wiki-card wiki-card-key">
                        <span class="wiki-card-tag">성도가 신청</span>
                        <b class="wiki-card-title">가입 신청 &rarr; 승인</b>
                        <span class="wiki-card-body">성도가 홈페이지에서 신청하고 관리자가 <b>계정 &rsaquo; 가입 신청</b>에서 승인합니다.</span>
                    </div>
                    <div class="wiki-card wiki-card-key">
                        <span class="wiki-card-tag">관리자가 직접</span>
                        <b class="wiki-card-title">사이트 계정 켜기</b>
                        <span class="wiki-card-body"><b>교적 &rsaquo; 성도</b>에서 그분의 기록을 열고 <b>사이트 계정</b>을 켜서 만들어 줍니다.</span>
                    </div>
                </div>
                <p class="wiki-warn"><b>사이트 계정을 끄면 그 계정이 삭제됩니다.</b> 연결만 끊는 것이 아닙니다. 2단계 인증 등록도, 그분이 올린 글의 작성자 표시도 함께 사라집니다. 끄기 전에 안내 문구를 꼭 읽어주세요.</p>

                <h3 class="wiki-h3">2단계 인증</h3>
                <p>관리자로 일하는 분들은 <b>인증 앱</b>을 등록해야 합니다. 비밀번호가 새어 나가도 계정을 지키기 위해서입니다. 그냥 성도 계정에는 요구하지 않습니다.</p>
                <p>홈페이지 로그인 화면에서도 인증이 끝납니다. 비밀번호를 넣으면 여섯 자리 코드를 묻고, 맞으면 로그인됩니다.</p>
                <p class="wiki-note"><b>휴대폰을 잃어버리셨다면</b> 코드 입력 화면의 <b>휴대폰을 사용할 수 없으신가요?</b>를 펼쳐 복구 코드를 넣으세요. 처음 등록할 때 받아 두신 코드입니다. 한 번 쓴 코드는 다시 쓸 수 없습니다.</p>

                <h3 class="wiki-h3">얼마나 로그인이 유지되나</h3>
                <p><b>30일</b>입니다. 브라우저를 닫아도 유지됩니다. 로그인할 때 <b>로그인 상태 유지</b>를 켜두시면 그 뒤로도 다시 묻지 않습니다.</p>
                <p class="wiki-warn">공용 컴퓨터나 다른 분의 휴대폰으로 보셨다면 <b>반드시 로그아웃</b>하세요. 메뉴 맨 아래에 있습니다.</p>

            </div>
        </details>

        <details class="wiki-part" id="wiki-auto">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">7</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">저절로 일어나는 일</span>
                    <span class="wiki-summary-note">아무도 안 했는데 생기는 것들</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <div class="wiki-rows">
                    <div class="wiki-row"><b>설교 영상</b><span>유튜브에 <b>주일설교</b>가 올라오면 예배 영상에 저절로 들어옵니다</span></div>
                    <div class="wiki-row"><b>인스타그램 사진</b><span>새 게시물이 올라오면 앨범이 하나 만들어집니다. 아무도 안 만든 앨범이 보이는 이유입니다</span></div>
                    <div class="wiki-row"><b>주소(슬러그)</b><span>비워두면 자동으로 만들어집니다. 한글 제목은 날짜로 만들어집니다. 제목을 나중에 고쳐도 주소는 그대로입니다</span></div>
                </div>

            </div>
        </details>

        <details class="wiki-part" id="wiki-help">
            <summary class="wiki-summary">
                <span class="wiki-summary-num">8</span>
                <span class="wiki-summary-text">
                    <span class="wiki-summary-title">막혔을 때</span>
                    <span class="wiki-summary-note">증상별로 찾기</span>
                </span>
                <span class="wiki-chev" aria-hidden="true"></span>
            </summary>
            <div class="wiki-body">

                <div class="wiki-qa">
                    <p class="wiki-q">앨범을 만들었는데 홈페이지에 안 보여요</p>
                    <p class="wiki-a"><b>활성화</b>가 꺼져 있을 가능성이 큽니다. 앨범 목록에서 바로 켤 수 있습니다. 켰는데도 안 보이면, 지금 로그아웃 상태로 확인하고 계신 것은 아닌지 보세요. 앨범 페이지는 로그인한 성도에게만 열립니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">사진이 안 올라가고 빨간 알림이 떠요</p>
                    <p class="wiki-a">그 파일을 웹에서 보이는 형식으로 바꾸지 못한 것입니다. 다른 파일로 올려보세요. 64MB가 넘어도 거절됩니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 화면 맨 위에 사진이 안 나와요</p>
                    <p class="wiki-a"><b>사이트 설정</b> 맨 아래 파일 이름이 비어 있거나, 지워진 사진을 가리키고 있습니다. <b>자주 하는 일</b>의 순서대로 다시 넣어주세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">사진이 너무 많아서 찾을 수가 없어요</p>
                    <p class="wiki-a">사진이 3,199장이라 그대로 보면 여러 앨범이 뒤섞여 나옵니다. 목록 위 <b>필터</b>를 열고 <b>앨범</b>을 하나 고르세요. 홈 화면 사진 띠에 넣은 사진만 보려면 <b>홈 슬라이더</b>를 <b>넣은 사진</b>으로 바꾸면 됩니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 화면에 사진 띠가 안 보여요</p>
                    <p class="wiki-a">홈 슬라이더에 넣은 사진이 한 장도 없어서입니다. 지금이 그 상태이고, 그럴 때는 <b>주는교회의 순간들</b> 글귀만 나옵니다. <b>미디어 &rsaquo; 사진</b>에서 사진을 체크하고 <b>일괄 작업 &rsaquo; 홈 슬라이더에 넣기</b>를 눌러 주세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 슬라이더에 넣었는데 홈 화면에 안 나와요</p>
                    <p class="wiki-a">그 사진이 든 앨범의 <b>활성화</b>가 꺼져 있습니다. 넣을 때 어느 앨범이 꺼져 있는지 알려주고, 사진 목록에서도 그런 사진은 <b><span class="wiki-star-off">&star;</span> 속이 빈 회색 별</b>로 보입니다. <b>미디어 &rsaquo; 앨범</b>에서 그 앨범의 활성화를 켜면 나옵니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 슬라이더에 사진을 더 넣으려는데 안 돼요</p>
                    <p class="wiki-a">홈 슬라이더는 <b>10장까지</b>입니다. 자리보다 많이 고르시면 <b>한 장도 들어가지 않고</b> 남은 자리가 몇 장인지 알려줍니다. 몇 장을 <b>홈 슬라이더에서 빼기</b>로 뺀 뒤 다시 고르세요. 사진을 하나 열어 저장하는 방식으로 열한 번째를 넣으려 할 때는 지금 들어 있는 열 장을 보여줍니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">성도가 아닌 분이 소식 페이지에 들어갔더니 로그인 안내만 나온대요</p>
                    <p class="wiki-a">정상입니다. 교회 소식 &middot; 교회 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범은 성도만 보는 페이지라, 그 다섯 곳은 로그인 안내 한 줄만 보입니다. 계정이 없으시면 가입 신청을 안내해 주세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">로그인했는데도 계속 로그인 안내만 나온대요</p>
                    <p class="wiki-a">그분이 <b>교적에 없는</b> 것입니다. 계정과 교적은 별개입니다. 그런 분에게는 안내 문구도 달라져서, 로그인하라는 말 대신 교적 등록이 필요하다는 안내와 교회 사무실 이메일이 보입니다. <b>교적 &rsaquo; 성도</b>에서 그분이 있는지 확인하고, 우리 교회 성도가 맞으면 교적에 올려주세요. 올리는 즉시 다섯 페이지가 열립니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">주보 링크를 보내드렸는데 안 열린대요</p>
                    <p class="wiki-a">주보는 로그인한 성도만 볼 수 있습니다. 받은 분이 어떤 화면을 보게 되는지는 <b>사진과 파일</b>의 그림에 정리해 두었습니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">가입 신청을 했다는데 목록에 없어요</p>
                    <p class="wiki-a">이미 계정이 있거나, 지난번 신청이 아직 기다리고 있는 경우입니다. 그럴 때는 신청이 새로 쌓이지 않습니다. <b>계정 &rsaquo; 사이트 유저</b>에서 그분 이메일을 찾아보세요.</p>
                </div>

            </div>
        </details>

        <p class="wiki-foot">고쳐야 할 것이나 여기 없는 내용이 있으면 알려주세요.</p>

    </div>

    <style>
        /* Mobile first, and min-width only, per the project's CSS rule.
           Colours come from Filament's own custom properties so the page
           follows the panel into dark mode. */

        .wiki {
            --wiki-line: color-mix(in srgb, var(--gray-500) 20%, transparent);
            --wiki-tint: color-mix(in srgb, var(--gray-500) 5%, transparent);
            --wiki-tint-strong: color-mix(in srgb, var(--gray-500) 9%, transparent);
            --wiki-muted: var(--gray-500);
            /* 43rem of text at 0.9375rem is roughly 43 Korean characters
               a line, which is where this reads most comfortably. */
            max-width: 46rem;
            padding-block-end: 1rem;
        }
        .dark .wiki { --wiki-muted: var(--gray-400); }

        .wiki-sr {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip-path: inset(50%);
            white-space: nowrap;
        }

        .wiki-intro {
            margin: 0 0 1.75rem;
            font-size: 0.9375rem;
            line-height: 1.9;
            color: var(--wiki-muted);
        }

        /* Table of contents: the way in. */
        .wiki-toc {
            display: grid;
            gap: 0.5rem;
            margin-block-end: 2.5rem;
        }
        .wiki-toc-item {
            display: grid;
            grid-template-columns: 1.625rem 1fr;
            gap: 0.75rem;
            align-items: center;
            padding: 0.75rem 0.875rem;
            border: 1px solid var(--wiki-line);
            border-radius: 0.625rem;
            background-color: var(--wiki-tint);
            text-decoration: none;
            color: inherit;
            transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
        }
        .wiki-toc-item:hover {
            background-color: var(--wiki-tint-strong);
            border-color: color-mix(in srgb, var(--primary-500) 45%, transparent);
            transform: translateY(-1px);
        }
        .wiki-toc-num {
            display: grid;
            place-items: center;
            width: 1.625rem;
            height: 1.625rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary-600);
            background-color: color-mix(in srgb, var(--primary-500) 15%, transparent);
        }
        .dark .wiki-toc-num { color: var(--primary-400); }
        .wiki-toc-text { display: grid; gap: 0.1rem; min-width: 0; }
        .wiki-toc-text > b { font-size: 0.9375rem; font-weight: 700; }
        .wiki-toc-text > span { font-size: 0.8125rem; color: var(--wiki-muted); }

        /* Sections. */
        .wiki-part {
            border: 1px solid var(--wiki-line);
            border-radius: 0.875rem;
            margin-block-end: 1rem;
            background-color: var(--wiki-tint);
            overflow: hidden;
            scroll-margin-block-start: 5rem;
        }

        .wiki-summary {
            display: grid;
            grid-template-columns: 1.75rem 1fr 0.75rem;
            gap: 0.875rem;
            align-items: center;
            padding: 1.125rem 1.125rem;
            cursor: pointer;
            list-style: none;
            user-select: none;
            transition: background-color 0.18s ease;
        }
        .wiki-summary::-webkit-details-marker { display: none; }
        .wiki-summary:hover { background-color: var(--wiki-tint-strong); }
        .wiki-summary:focus-visible {
            outline: 2px solid var(--primary-500);
            outline-offset: -2px;
        }

        .wiki-summary-num {
            display: grid;
            place-items: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--primary-600);
            background-color: color-mix(in srgb, var(--primary-500) 15%, transparent);
        }
        .dark .wiki-summary-num { color: var(--primary-400); }

        .wiki-summary-text { display: grid; gap: 0.15rem; min-width: 0; }
        .wiki-summary-title { font-size: 1.0625rem; font-weight: 700; }
        .wiki-summary-note { font-size: 0.8125rem; color: var(--wiki-muted); }

        .wiki-chev {
            width: 0.5rem;
            height: 0.5rem;
            border-inline-end: 2px solid currentColor;
            border-block-end: 2px solid currentColor;
            transform: rotate(45deg) translate(-0.1rem, -0.1rem);
            opacity: 0.45;
            transition: transform 0.25s ease;
        }
        .wiki-part[open] > .wiki-summary .wiki-chev { transform: rotate(-135deg) translate(-0.1rem, -0.1rem); }

        .wiki-body {
            padding: 1.5rem 1.125rem 1.75rem;
            border-block-start: 1px solid color-mix(in srgb, var(--gray-500) 14%, transparent);
        }
        .wiki-part[open] > .wiki-body { animation: wiki-open 0.28s ease-out; }
        @keyframes wiki-open {
            from { opacity: 0; transform: translateY(-0.375rem); }
            to { opacity: 1; transform: none; }
        }

        .wiki-body p { margin: 0 0 1rem; font-size: 0.9375rem; line-height: 1.9; }
        .wiki-body p:last-child { margin-block-end: 0; }
        .wiki-body b { font-weight: 600; }

        .wiki-lede {
            margin-block-end: 1.75rem !important;
            font-size: 1rem !important;
            color: var(--wiki-muted);
        }

        .wiki-h3 {
            margin: 2.5rem 0 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            opacity: 0.55;
        }
        .wiki-h3:first-child { margin-block-start: 0; }

        /* Tasks read as numbered recipes. */
        .wiki-task {
            margin-block-end: 2.5rem;
            padding-block-end: 2.5rem;
            border-block-end: 1px dashed var(--wiki-line);
        }
        .wiki-task:last-child { margin-block-end: 0; padding-block-end: 0; border-block-end: 0; }
        .wiki-task-title { margin: 0 0 1rem; font-size: 1.0625rem; font-weight: 700; }

        /* Filament's reset strips list markers, and a numbered recipe
           without its numbers is not a recipe, so the counters are
           drawn by hand as badges. */
        .wiki-steps {
            counter-reset: wiki-step;
            list-style: none;
            margin: 0 0 1.25rem;
            padding: 0;
            display: grid;
            gap: 0.625rem;
        }
        /* The badge is positioned rather than laid out as a grid cell,
           because a grid would break each inline <b> in the step onto a
           column of its own. */
        .wiki-steps li {
            counter-increment: wiki-step;
            position: relative;
            padding-inline-start: 2.125rem;
            font-size: 0.9375rem;
            line-height: 1.8;
            list-style: none;
        }
        .wiki-steps li::before {
            content: counter(wiki-step);
            position: absolute;
            inset-inline-start: 0;
            inset-block-start: 0.2rem;
            display: grid;
            place-items: center;
            width: 1.375rem;
            height: 1.375rem;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--wiki-muted);
            background-color: color-mix(in srgb, var(--gray-500) 16%, transparent);
        }

        .wiki-rows { display: grid; gap: 0.5rem; margin-block-end: 1rem; }
        .wiki-row {
            display: grid;
            gap: 0.2rem;
            padding: 0.75rem 0.875rem;
            border-radius: 0.5rem;
            background-color: var(--wiki-tint-strong);
            transition: background-color 0.18s ease;
        }
        .wiki-row:hover { background-color: color-mix(in srgb, var(--gray-500) 14%, transparent); }
        .wiki-row > b { font-size: 0.875rem; font-weight: 700; }
        .wiki-row > span { font-size: 0.875rem; line-height: 1.75; color: var(--wiki-muted); }

        /* Who sees what: the permission table. */
        .wiki-legend {
            list-style: none;
            margin: 0 0 1rem;
            padding: 0;
            display: grid;
            gap: 0.35rem;
        }
        .wiki-legend li {
            display: grid;
            grid-template-columns: 4.5rem 1fr;
            gap: 0.625rem;
            font-size: 0.8125rem;
            line-height: 1.6;
        }
        .wiki-legend b { font-weight: 700; }
        .wiki-legend span { color: var(--wiki-muted); }

        .wiki-scroll { overflow-x: auto; margin-block-end: 1.25rem; }

        .wiki-matrix {
            width: 100%;
            min-width: 18rem;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .wiki-matrix th,
        .wiki-matrix td {
            padding: 0.625rem 0.375rem;
            text-align: center;
            border-block-end: 1px solid var(--wiki-line);
        }
        .wiki-matrix thead th {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--wiki-muted);
            white-space: nowrap;
        }
        .wiki-matrix thead th:first-child { text-align: start; padding-inline-start: 0.625rem; }
        .wiki-matrix tbody th {
            text-align: start;
            font-weight: 600;
            padding-inline-start: 0.625rem;
        }
        .wiki-matrix tbody tr { transition: background-color 0.18s ease; }
        .wiki-matrix tbody tr:hover { background-color: var(--wiki-tint-strong); }
        .wiki-matrix tbody tr:last-child th,
        .wiki-matrix tbody tr:last-child td { border-block-end: 0; }
        .wiki-matrix-sub { display: block; font-size: 0.6875rem; font-weight: 400; color: var(--wiki-muted); }
        /* The line where the 교적 gate falls. */
        .wiki-matrix-gate th,
        .wiki-matrix-gate td { border-block-start: 2px solid color-mix(in srgb, var(--gray-500) 40%, transparent); }
        .wiki-yes span[aria-hidden], .wiki-no span[aria-hidden] { font-size: 1.0625rem; font-weight: 700; line-height: 1; }
        .wiki-yes span[aria-hidden] { color: var(--success-600); }
        .dark .wiki-yes span[aria-hidden] { color: var(--success-400); }
        .wiki-no span[aria-hidden] { color: var(--danger-600); opacity: 0.75; }
        .dark .wiki-no span[aria-hidden] { color: var(--danger-400); opacity: 0.8; }

        /* Step flow with a rail down the left. */
        .wiki-flow {
            list-style: none;
            margin: 0 0 1.25rem;
            padding: 0;
            display: grid;
            gap: 0.75rem;
        }
        .wiki-flow-step {
            position: relative;
            display: grid;
            grid-template-columns: 1.75rem 1fr;
            gap: 0.875rem;
            align-items: start;
            list-style: none;
        }
        .wiki-flow-step::before {
            content: '';
            position: absolute;
            inset-block-start: 1.9rem;
            inset-block-end: -0.85rem;
            inset-inline-start: calc(0.875rem - 1px);
            width: 2px;
            background-color: color-mix(in srgb, var(--gray-500) 28%, transparent);
        }
        /* The rail runs past the last step so it feeds into the two
           outcome cards below rather than stopping dead. */
        .wiki-flow-step:last-child::before { inset-block-end: -1.1rem; }
        .wiki-flow-dot {
            display: grid;
            place-items: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--primary-600);
            background-color: color-mix(in srgb, var(--primary-500) 15%, transparent);
        }
        .dark .wiki-flow-dot { color: var(--primary-400); }
        .wiki-flow-card {
            display: grid;
            gap: 0.2rem;
            padding: 0.5rem 0 0.5rem;
        }
        .wiki-flow-card > b { font-size: 0.9375rem; font-weight: 700; }
        .wiki-flow-card > span { font-size: 0.875rem; line-height: 1.75; color: var(--wiki-muted); }

        /* Outcome cards: what the flow forks into. */
        .wiki-cards { display: grid; gap: 0.625rem; margin-block-end: 1.25rem; }
        .wiki-card {
            display: grid;
            gap: 0.25rem;
            align-content: start;
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            border-inline-start: 3px solid var(--wiki-line);
            background-color: var(--wiki-tint-strong);
            transition: background-color 0.18s ease;
        }
        .wiki-card:hover { background-color: color-mix(in srgb, var(--gray-500) 14%, transparent); }
        .wiki-card-tag {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            opacity: 0.6;
        }
        .wiki-card-title { font-size: 0.9375rem; font-weight: 700; }
        .wiki-card-body { font-size: 0.875rem; line-height: 1.75; color: var(--wiki-muted); }
        .wiki-card-open {
            border-inline-start-color: var(--success-500);
            background-color: color-mix(in srgb, var(--success-500) 9%, transparent);
        }
        .wiki-card-shut {
            border-inline-start-color: var(--warning-500);
            background-color: color-mix(in srgb, var(--warning-500) 9%, transparent);
        }
        .wiki-card-no {
            border-inline-start-color: var(--danger-500);
            background-color: color-mix(in srgb, var(--danger-500) 8%, transparent);
        }
        /* Neutral on purpose: the panel's own accent is amber, and a
           card in that colour would read as another warning. */
        .wiki-card-key {
            border-inline-start-color: color-mix(in srgb, var(--gray-500) 55%, transparent);
            background-color: color-mix(in srgb, var(--gray-500) 10%, transparent);
        }

        /* The two-condition rule for the home band, drawn rather than
           written: a photograph reaches the front page only when the
           tick and its album's 활성화 are both on, and the sentence
           saying so was read as one condition rather than two. Stacked
           at every width, because three cards in a row inside a 40rem
           column would wrap into something narrower than it is worth. */
        .wiki-gate { display: grid; gap: 0.375rem; margin-block-end: 1.25rem; }
        .wiki-gate-cond,
        .wiki-gate-out {
            display: grid;
            gap: 0.2rem;
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            border-inline-start: 3px solid color-mix(in srgb, var(--gray-500) 55%, transparent);
            background-color: color-mix(in srgb, var(--gray-500) 10%, transparent);
        }
        .wiki-gate-out {
            border-inline-start-color: var(--success-500);
            background-color: color-mix(in srgb, var(--success-500) 10%, transparent);
        }
        .wiki-gate-cond > b, .wiki-gate-out > b { font-size: 0.9375rem; font-weight: 700; }
        .wiki-gate-cond > span, .wiki-gate-out > span { font-size: 0.875rem; line-height: 1.75; color: var(--wiki-muted); }
        .wiki-gate-op {
            justify-self: center;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1.4;
            letter-spacing: 0.08em;
            color: var(--wiki-muted);
        }

        /* The same two marks the 사진 list uses, in the same colours. */
        .wiki-star-on, .wiki-star-off { font-size: 1rem; line-height: 1; }
        .wiki-star-on { color: var(--warning-500); }
        .wiki-star-off { color: var(--gray-500); }

        /* Notes and warnings stay visibly different at a glance. */
        .wiki-note, .wiki-warn {
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            border-inline-start: 3px solid;
            font-size: 0.875rem !important;
            line-height: 1.8 !important;
        }
        .wiki-note {
            border-color: color-mix(in srgb, var(--gray-500) 45%, transparent);
            background-color: color-mix(in srgb, var(--gray-500) 8%, transparent);
        }
        .wiki-warn {
            border-color: var(--warning-500);
            background-color: color-mix(in srgb, var(--warning-500) 11%, transparent);
        }

        .wiki-qa {
            margin-block-end: 1.5rem;
            padding-block-end: 1.5rem;
            border-block-end: 1px dashed var(--wiki-line);
        }
        .wiki-qa:last-child { margin-block-end: 0; padding-block-end: 0; border-block-end: 0; }
        .wiki-q { margin: 0 0 0.5rem !important; font-weight: 700; }
        .wiki-a { color: var(--wiki-muted); }

        .wiki-foot {
            margin: 2rem 0 0;
            font-size: 0.8125rem;
            color: var(--wiki-muted);
        }

        /* Gentle reveal as each part scrolls in. Enabled only once the
           script has added .wiki-anim, so without JavaScript nothing is
           ever left invisible. */
        .wiki-anim .wiki-rise {
            opacity: 0;
            transform: translateY(0.75rem);
            transition: opacity 0.45s ease-out, transform 0.45s ease-out;
        }
        .wiki-anim .wiki-rise.wiki-in { opacity: 1; transform: none; }

        @media (min-width: 30rem) {
            .wiki-toc { grid-template-columns: 1fr 1fr; gap: 0.625rem; }
        }

        @media (min-width: 40rem) {
            .wiki-cards-2 { grid-template-columns: 1fr 1fr; }
        }

        @media (min-width: 48rem) {
            .wiki-summary { padding: 1.25rem 1.5rem; }
            .wiki-summary-title { font-size: 1.125rem; }
            .wiki-body { padding: 1.75rem 1.75rem 2rem; }
            .wiki-row { grid-template-columns: 11rem 1fr; gap: 1rem; align-items: baseline; }
            .wiki-matrix th, .wiki-matrix td { padding: 0.75rem 0.5rem; }
            .wiki-matrix thead th { font-size: 0.8125rem; }
        }

        /* Nothing moves for a reader who has asked for stillness. */
        @media (prefers-reduced-motion: reduce) {
            .wiki *, .wiki *::before, .wiki *::after {
                animation: none !important;
                transition: none !important;
            }
            .wiki-toc-item:hover { transform: none; }
            .wiki-anim .wiki-rise { opacity: 1; transform: none; }
        }
    </style>

    <script>
        /**
         * Two small conveniences for the wiki page.
         *
         * Opening a section from the index: a fragment link cannot open
         * a closed details element on its own, so the click does it.
         *
         * Revealing sections as they scroll in: the hidden starting
         * state lives behind .wiki-anim, which is added here and never
         * added when the reader has asked for reduced motion, so the
         * page is readable with the script absent or motion refused.
         */
        (function () {
            const setUp = function () {
                const wiki = document.querySelector('.wiki');

                if (! wiki || wiki.dataset.wikiReady === '1') {
                    return;
                }

                wiki.dataset.wikiReady = '1';

                /** Opening the target section before the browser jumps to it. */
                const open = function (hash) {
                    const part = hash && document.querySelector(hash);

                    if (part instanceof HTMLDetailsElement) {
                        part.open = true;
                    }
                };

                wiki.querySelectorAll('.wiki-toc-item').forEach(function (link) {
                    link.addEventListener('click', function () {
                        open(link.getAttribute('href'));
                    });
                });

                open(window.location.hash);

                const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (still || ! ('IntersectionObserver' in window)) {
                    return;
                }

                wiki.classList.add('wiki-anim');

                const parts = wiki.querySelectorAll('.wiki-toc, .wiki-part, .wiki-foot');

                parts.forEach(function (part) {
                    part.classList.add('wiki-rise');
                });

                const watcher = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('wiki-in');
                            watcher.unobserve(entry.target);
                        }
                    });
                });

                parts.forEach(function (part) {
                    watcher.observe(part);
                });
            };

            setUp();
            document.addEventListener('livewire:navigated', setUp);
        })();
    </script>
</x-filament-panels::page>
