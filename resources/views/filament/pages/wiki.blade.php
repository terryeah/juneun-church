<x-filament-panels::page>
    {{-- Every class is prefixed and every rule is scoped to .wiki, so
         nothing here can reach Filament's own card, step or grid
         styling, and nothing of Filament's reaches in.

         Written for a phone first: each part is a closed accordion, so
         the page opens as a short list of questions rather than several
         thousand pixels of prose to scroll past. --}}
    <div class="wiki">

        <p class="wiki-intro">이 홈페이지를 쓰다가 막히면 여기를 보세요. 자주 하는 일은 순서대로 적어 두었고, 헷갈리기 쉬운 것과 되돌릴 수 없는 것은 따로 표시해 두었습니다.</p>

        <details class="wiki-part" open>
            <summary class="wiki-summary"><span class="wiki-summary-title">자주 하는 일</span><span class="wiki-summary-note">주보·사진·소식 올리기</span></summary>
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
                    <p class="wiki-note">성도가 자료실에서 주보를 누르면 <b>주보 &middot; 2026년 8월 16일</b>처럼 이름 붙은 페이지가 열리고, 그 안에 주보가 펼쳐집니다. 휴대폰에서 잘 안 보이면 그 위에 있는 <b>PDF 열기</b>를 누르면 됩니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">사진 올리기</h3>
                    <ol class="wiki-steps">
                        <li>먼저 <b>미디어 &rsaquo; 앨범</b>에서 사진을 담을 앨범을 만듭니다. 앨범명과 행사 날짜만 있으면 됩니다.</li>
                        <li><b>미디어 &rsaquo; 사진</b>으로 가서 <b>업로드</b>를 누릅니다.</li>
                        <li>앨범을 고르고 사진 파일을 올린 뒤 저장합니다.</li>
                        <li>앨범의 <b>활성화</b>가 켜져 있어야 홈페이지에 나옵니다. 새로 만든 앨범은 처음부터 켜져 있습니다.</li>
                    </ol>
                    <p class="wiki-note">올린 사진은 자동으로 가볍게 바뀝니다. 아이폰 사진도 그대로 올리시면 됩니다. 자세한 건 아래 <b>사진과 파일</b>에 있습니다.</p>
                    <p class="wiki-note"><b>앨범은 성도만 봅니다.</b> 홈페이지의 앨범 페이지는 로그인한 성도에게만 열리니, 아이들 얼굴이 담긴 사진도 마음 놓고 올리시면 됩니다. 홈 화면에 내보내고 싶은 사진이 있으면 아래 <b>홈 화면 사진 띠 채우기</b>를 보세요.</p>
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
                        <li><b>미디어 &rsaquo; 사진</b>에서 내보내고 싶은 사진을 엽니다.</li>
                        <li>맨 아래 <b>홈 슬라이더에 표시</b>를 켜고 저장합니다. 최대 열 장까지 됩니다.</li>
                        <li>그 사진이 든 앨범의 <b>활성화</b>도 켜져 있어야 합니다.</li>
                    </ol>
                    <p class="wiki-warn"><b>지금은 한 장도 켜져 있지 않습니다.</b> 그래서 홈 화면 <b>주는교회의 순간들</b> 자리에는 글귀 두 줄만 있고 사진 띠가 아예 나오지 않습니다. 몇 장 골라 켜 주시면 그 아래에 사진 띠가 생깁니다.</p>
                    <p class="wiki-note"><b>이 체크는 &ldquo;이 사진은 누구나 봐도 괜찮다&rdquo;는 뜻입니다.</b> 홈 화면은 로그인하지 않은 분도 보는 자리이기 때문입니다. 앨범 전체는 성도만 보지만, 여기에 켠 사진은 홈 화면에 나옵니다. 그 사진을 누르면 앨범으로 넘어가는데, 앨범은 성도 전용이라 로그인하지 않은 분에게는 거기서 로그인 안내가 나옵니다.</p>
                    <p class="wiki-note">앨범에서 알아서 채워 오던 예전 방식은 없어졌습니다. 이제는 <b>켜 두신 사진만</b> 나옵니다.</p>
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
                    <p class="wiki-warn"><b>우리 교회 성도가 아닌 분은 교적에 올리지 마세요.</b> 교회 소식 &middot; 교회 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범, 이 다섯 페이지가 교적 하나로 한꺼번에 열립니다. 교적에 올리지 않으면 로그인은 되지만 그 다섯 페이지에는 로그인 안내만 보입니다. 나중에 성도가 되시면 그때 교적에 올리면 됩니다.</p>
                    <p class="wiki-note">승인하면 신청자가 가입할 때 정한 비밀번호로 계정이 만들어집니다. 거절해도 기록만 남고, 그분은 다시 신청할 수 있습니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">헌금 내역 입력하기</h3>
                    <ol class="wiki-steps">
                        <li><b>재정 &rsaquo; 헌금 내역 &rsaquo; 새로 만들기</b>.</li>
                        <li>주일 날짜를 고르고, 항목마다 구분·이름·금액을 넣습니다.</li>
                    </ol>
                    <p class="wiki-note">헌금 페이지에서 <b>로그인한 성도에게만</b> 보입니다. 지난 12주까지 넘겨볼 수 있습니다. 재정부 역할을 받은 분은 이 메뉴만 보입니다.</p>
                </article>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">누가 무엇을 보나</span><span class="wiki-summary-note">성도만 보는 페이지가 어디인지</span></summary>
            <div class="wiki-body">

                <p class="wiki-lede">홈페이지는 <b>누구나 보는 페이지</b>와 <b>성도만 보는 페이지</b>로 나뉩니다. 글 하나하나가 아니라 <b>페이지 통째로</b> 나뉩니다.</p>

                <div class="wiki-rows">
                    <div class="wiki-row"><b>누구나</b><span>홈 &middot; 예배 안내 &middot; 섬기는 사람들 &middot; 오시는 길</span></div>
                    <div class="wiki-row"><b>성도만</b><span>교회 소식 &middot; 교회 행사 &middot; 자료실(주보 &middot; 문서) &middot; 헌금 &middot; 앨범</span></div>
                </div>

                <p class="wiki-note">메뉴는 누구에게나 여덟 개 그대로 보입니다. 성도가 아닌 분이 성도 전용 메뉴를 누르면 페이지 이름과 안내 한 줄만 나옵니다. 목록을 아예 만들지 않기 때문에 소식 제목도, 주보 파일 이름도 그 화면에는 실려 나가지 않습니다.</p>

                <p>그 안내는 보는 분에 따라 다릅니다.</p>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>로그아웃 상태</b><span>&ldquo;… 성도에게만 공개됩니다&rdquo;와 <b>로그인</b> 링크. 로그인하면 보시던 그 페이지로 바로 돌아갑니다</span></div>
                    <div class="wiki-row"><b>로그인했지만 교적에 없음</b><span>교적 등록이 필요하다는 안내와 교회 사무실 이메일. 그 주소는 <b>기준 정보 &rsaquo; 사이트 설정 &rsaquo; 연락처</b>의 <b>대표 이메일</b>입니다</span></div>
                </div>

                <p class="wiki-warn"><b>기준은 로그인이 아니라 교적입니다.</b> 계정만 있고 교적에 없는 분은 로그인해도 다섯 페이지가 열리지 않습니다. 가입 신청은 누구나 넣을 수 있어서, 로그인을 기준으로 삼으면 교회가 모르는 분에게도 주보와 헌금 내역이 열리기 때문입니다.</p>

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
                <p class="wiki-warn"><b>홈 화면 사진 띠는 켜 둔 사진만 나옵니다.</b> 앨범에서 알아서 채워 오지 않습니다. <b>미디어 &rsaquo; 사진</b>에서 사진을 열고 <b>홈 슬라이더에 표시</b>를 켜야 나오며, 그 체크는 &ldquo;이 사진은 누구나 봐도 괜찮다&rdquo;는 뜻입니다. 지금은 한 장도 켜져 있지 않아 그 자리에 사진 띠가 나오지 않습니다.</p>

                <h3 class="wiki-h3">검색 엔진</h3>
                <p>구글에 알려주는 목록에는 <b>홈 &middot; 예배 안내 &middot; 오시는 길 &middot; 섬기는 사람들</b> 네 페이지만 들어 있습니다. 성도 전용 다섯 페이지와 그 안의 소식 &middot; 앨범은 검색 결과에 올라가지 않습니다.</p>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">화면 안내</span><span class="wiki-summary-note">왼쪽 메뉴가 무엇을 하는 곳인지</span></summary>
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
                    <div class="wiki-row"><b>사진</b><span>사진 한 장씩. 홈 화면 사진 띠에 넣을 사진을 <b>여기서만</b> 고릅니다</span></div>
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

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">역할</span><span class="wiki-summary-note">누구에게 무엇을 열어줄지</span></summary>
            <div class="wiki-body">

                <p class="wiki-lede">계정을 만들 때 역할을 하나 줍니다. 역할이 그 사람에게 열리는 메뉴를 정합니다.</p>

                <div class="wiki-rows">
                    <div class="wiki-row"><b>관리자</b><span>재정을 포함해 거의 전부. 목사님·사모님·사무 담당</span></div>
                    <div class="wiki-row"><b>편집자</b><span>소식·행사·예배 영상·주보·문서·앨범·사진·동영상. <b>교적부와 헌금, 사이트 설정은 열리지 않습니다</b></span></div>
                    <div class="wiki-row"><b>재정부</b><span>헌금 내역과 개인 헌금만. 다른 메뉴는 아예 보이지 않습니다</span></div>
                    <div class="wiki-row"><b>일반회원</b><span>관리자 화면은 못 씁니다. 홈페이지만 봅니다</span></div>
                </div>

                <p class="wiki-note">주보와 소식만 올려주실 분에게는 <b>편집자</b>가 맞습니다. 성도들의 인적사항과 헌금이 열리지 않기 때문입니다.</p>

                <p class="wiki-warn"><b>역할에 '성도'는 없습니다.</b> 성도인지 아닌지는 <b>교적에 있는지</b>로 정해집니다. 가입 신청은 누구나 넣을 수 있어서, 승인했다는 것만으로는 우리 교회 성도라는 뜻이 되지 않기 때문입니다. 그래서 승인할 때 <b>교적에 올릴지</b>를 함께 고릅니다 - 교적에 올리면 소식 &middot; 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범이 열리고, 올리지 않으면 로그인만 되는 <b>일반회원</b>이 됩니다.</p>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">사진과 파일</span><span class="wiki-summary-note">무엇이 올라가고 무슨 일이 일어나는지</span></summary>
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
                <p class="wiki-note"><b>올린 PDF는 로그인한 성도만 열 수 있습니다.</b> 파일이 비공개로 저장되고, 홈페이지를 거쳐야만 열리기 때문입니다. 주소만 따로 복사해 단톡방에 올려도 성도가 아닌 분이 누르면 열리지 않습니다. 예전에 올린 주보와 문서도 주소를 모두 새로 바꿔 두어, 밖으로 나갔던 옛 주소는 이제 아무것도 열지 못합니다.</p>
                <p class="wiki-warn"><b>다만 파일 자체가 넘어가는 것은 막지 못합니다.</b> 성도가 열어서 내려받은 PDF를 다른 분께 보내면 그것까지 되돌릴 방법은 없습니다. 링크는 안전하지만 파일은 그렇지 않다고 생각하시면 됩니다.</p>
                <p class="wiki-note">저장할 때 붙는 이름은 주보가 <b>Bulletin_2026_08_16.pdf</b>처럼 날짜, 문서는 <b>적어 두신 제목 그대로</b>입니다. 문서 제목을 알아보기 쉽게 적어 두시면 받는 분 컴퓨터에도 그 이름으로 남습니다.</p>

                <h3 class="wiki-h3">지우면 되돌릴 수 없습니다</h3>
                <p class="wiki-warn"><b>앨범을 지우면 그 안의 사진이 전부 함께 지워집니다.</b> 사진 파일까지 지워지고, 휴지통은 없습니다. 사진 800장짜리 앨범도 확인 한 번이면 사라집니다. 잠시 감추고 싶은 것이라면 지우지 말고 <b>활성화만 꺼 두세요.</b></p>
                <p>주보·문서·사진을 지우면 파일도 함께 지워집니다. 다만 인터넷에 이미 퍼진 그림은 한동안 남아 있을 수 있습니다. 급히 내려야 할 사진이라면 지운 뒤에 알려주세요.</p>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">로그인과 계정</span><span class="wiki-summary-note">2단계 인증, 성도 계정 만들기</span></summary>
            <div class="wiki-body">

                <p class="wiki-lede">교회 소식 &middot; 교회 행사 &middot; 자료실 &middot; 헌금 &middot; 앨범은 로그인해야 보입니다. 성도님들께 계정을 만들어 드릴 일이 예전보다 많아졌습니다.</p>

                <h3 class="wiki-h3">성도가 계정을 갖는 두 가지 길</h3>
                <p>성도가 홈페이지에서 <b>가입 신청</b>을 하면 관리자가 승인합니다. 또는 관리자가 <b>교적 &rsaquo; 성도</b>에서 그분의 기록을 열고 <b>사이트 계정</b>을 켜서 직접 만들어 줄 수도 있습니다.</p>
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

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">저절로 일어나는 일</span><span class="wiki-summary-note">아무도 안 했는데 생기는 것들</span></summary>
            <div class="wiki-body">

                <div class="wiki-rows">
                    <div class="wiki-row"><b>설교 영상</b><span>유튜브에 <b>주일설교</b>가 올라오면 예배 영상에 저절로 들어옵니다</span></div>
                    <div class="wiki-row"><b>인스타그램 사진</b><span>새 게시물이 올라오면 앨범이 하나 만들어집니다. 아무도 안 만든 앨범이 보이는 이유입니다</span></div>
                    <div class="wiki-row"><b>주소(슬러그)</b><span>비워두면 자동으로 만들어집니다. 한글 제목은 날짜로 만들어집니다. 제목을 나중에 고쳐도 주소는 그대로입니다</span></div>
                </div>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">막혔을 때</span><span class="wiki-summary-note">증상별로 찾기</span></summary>
            <div class="wiki-body">

                <div class="wiki-qa">
                    <p class="wiki-q">앨범을 만들었는데 홈페이지에 안 보여요</p>
                    <p class="wiki-a"><b>활성화</b>가 꺼져 있을 가능성이 큽니다. 앨범 목록에서 바로 켤 수 있습니다. 켰는데도 안 보이면, 지금 로그아웃 상태로 확인하고 계신 것은 아닌지 보세요 - 앨범 페이지는 로그인한 성도에게만 열립니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">사진이 안 올라가고 빨간 알림이 떠요</p>
                    <p class="wiki-a">그 파일을 웹에서 보이는 형식으로 바꾸지 못한 것입니다. 다른 파일로 올려보세요. 64MB가 넘어도 거절됩니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 화면 맨 위에 사진이 안 나와요</p>
                    <p class="wiki-a"><b>사이트 설정</b> 맨 아래 파일 이름이 비어 있거나, 지워진 사진을 가리키고 있습니다. 위 <b>자주 하는 일</b>의 순서대로 다시 넣어주세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">슬라이더에 사진을 더 넣으려는데 안 돼요</p>
                    <p class="wiki-a">홈 슬라이더는 <b>10장까지</b>입니다. 열한 번째를 켜면 지금 들어 있는 열 장을 알려주니, 하나를 빼고 다시 켜세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 화면에 사진 띠가 안 보여요</p>
                    <p class="wiki-a"><b>홈 슬라이더에 표시</b>를 켠 사진이 한 장도 없어서입니다. 지금이 그 상태이고, 그럴 때는 <b>주는교회의 순간들</b> 글귀만 나옵니다. <b>미디어 &rsaquo; 사진</b>에서 사진을 열고 맨 아래 그 체크를 켜 주세요. 위 <b>자주 하는 일</b>에 순서가 있습니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">홈 슬라이더에 표시를 켰는데 홈 화면에 안 나와요</p>
                    <p class="wiki-a">그 사진이 든 앨범의 <b>활성화</b>가 꺼져 있습니다. 앨범을 켜면 나옵니다.</p>
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
                    <p class="wiki-a">주보는 로그인한 성도만 볼 수 있습니다. 로그아웃 상태에서 누르면 주보 안내와 <b>로그인</b> 링크가 나오니, 그 자리에서 로그인하면 바로 주보로 이어집니다. 로그인은 되어 있는데 교적에 없는 분에게는 <b>페이지를 찾을 수 없습니다</b>가 나옵니다. <b>PDF 열기</b>로 열리는 파일 주소는 성도가 아니면 어느 경우에도 열리지 않습니다 - 주보에는 셀 편성과 헌금 내역이 들어 있어, 파일이 한 번 밖으로 나가면 되돌릴 수 없기 때문입니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">가입 신청을 했다는데 목록에 없어요</p>
                    <p class="wiki-a">이미 계정이 있거나, 지난번 신청이 아직 기다리고 있는 경우입니다. 그럴 때는 신청이 새로 쌓이지 않습니다. 계정 &rsaquo; 사이트 유저에서 그분 이메일을 찾아보세요.</p>
                </div>

            </div>
        </details>

        <p class="wiki-foot">고쳐야 할 것이나 여기 없는 내용이 있으면 알려주세요.</p>

    </div>

    <style>
        /* Mobile first, and min-width only, per the project's CSS rule. */
        .wiki { max-width: 52rem; }

        .wiki-intro {
            margin: 0 0 1.5rem;
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--gray-500);
        }
        .dark .wiki-intro { color: var(--gray-400); }

        .wiki-part {
            border: 1px solid color-mix(in srgb, var(--gray-500) 22%, transparent);
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            background-color: color-mix(in srgb, var(--gray-500) 4%, transparent);
            overflow: hidden;
        }

        .wiki-summary {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            padding: 1rem 1.125rem;
            cursor: pointer;
            list-style: none;
            user-select: none;
        }
        .wiki-summary::-webkit-details-marker { display: none; }
        .wiki-summary::after {
            content: '';
            position: absolute;
            inset-inline-end: 1.125rem;
            width: 0.5rem;
            height: 0.5rem;
            border-inline-end: 2px solid currentColor;
            border-block-end: 2px solid currentColor;
            transform: translateY(0.35rem) rotate(45deg);
            opacity: 0.45;
        }
        .wiki-part { position: relative; }
        .wiki-part[open] > .wiki-summary::after { transform: translateY(0.6rem) rotate(-135deg); }

        .wiki-summary-title { font-size: 1.0625rem; font-weight: 700; }
        .wiki-summary-note { font-size: 0.8125rem; color: var(--gray-500); }
        .dark .wiki-summary-note { color: var(--gray-400); }

        .wiki-body {
            padding: 0 1.125rem 1.25rem;
            border-block-start: 1px solid color-mix(in srgb, var(--gray-500) 16%, transparent);
            padding-block-start: 1.125rem;
        }

        .wiki-body p { margin: 0 0 0.75rem; font-size: 0.9375rem; line-height: 1.75; }
        .wiki-body p:last-child { margin-bottom: 0; }
        .wiki-body b { font-weight: 600; }

        .wiki-lede { color: var(--gray-500); }
        .dark .wiki-lede { color: var(--gray-400); }

        .wiki-h3 {
            margin: 1.5rem 0 0.625rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.55;
        }
        .wiki-h3:first-child { margin-top: 0; }

        .wiki-task { margin-bottom: 1.75rem; }
        .wiki-task:last-child { margin-bottom: 0; }
        .wiki-task-title { margin: 0 0 0.625rem; font-size: 1rem; font-weight: 700; }

        /* Filament's reset strips list markers, and a numbered recipe
           without its numbers is not a recipe. */
        .wiki-steps {
            margin: 0 0 0.75rem;
            padding-inline-start: 1.35rem;
            list-style: decimal outside;
        }
        .wiki-steps li {
            margin-bottom: 0.375rem;
            padding-inline-start: 0.15rem;
            font-size: 0.9375rem;
            line-height: 1.7;
            list-style: decimal outside;
        }
        .wiki-steps li::marker { font-weight: 700; opacity: 0.55; }

        .wiki-rows { display: grid; gap: 0.5rem; margin-bottom: 0.75rem; }
        .wiki-row {
            display: grid;
            gap: 0.15rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            background-color: color-mix(in srgb, var(--gray-500) 7%, transparent);
        }
        .wiki-row > b { font-size: 0.875rem; font-weight: 700; }
        .wiki-row > span { font-size: 0.875rem; line-height: 1.6; color: var(--gray-500); }
        .dark .wiki-row > span { color: var(--gray-400); }

        .wiki-note, .wiki-warn {
            padding: 0.75rem 0.875rem;
            border-radius: 0.5rem;
            border-inline-start: 3px solid;
            font-size: 0.875rem !important;
            line-height: 1.7 !important;
        }
        .wiki-note {
            border-color: color-mix(in srgb, var(--gray-500) 45%, transparent);
            background-color: color-mix(in srgb, var(--gray-500) 8%, transparent);
        }
        .wiki-warn {
            border-color: var(--warning-500);
            background-color: color-mix(in srgb, var(--warning-500) 10%, transparent);
        }

        .wiki-qa { margin-bottom: 1.125rem; }
        .wiki-qa:last-child { margin-bottom: 0; }
        .wiki-q { margin: 0 0 0.25rem !important; font-weight: 700; }
        .wiki-a { color: var(--gray-500); }
        .dark .wiki-a { color: var(--gray-400); }

        .wiki-foot {
            margin: 1.5rem 0 0;
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        @media (min-width: 48rem) {
            .wiki-summary { flex-direction: row; align-items: baseline; gap: 0.625rem; padding: 1.125rem 1.25rem; }
            .wiki-summary-title { font-size: 1.125rem; }
            .wiki-body { padding-inline: 1.25rem; }
            .wiki-row { grid-template-columns: 11rem 1fr; gap: 1rem; align-items: baseline; }
        }
    </style>
</x-filament-panels::page>
