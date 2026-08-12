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
                    <p class="wiki-note"><b>성도 전용이 처음부터 켜져 있습니다.</b> 주보에는 셀 편성과 섬김이 명단, 헌금 내역이 들어가기 때문입니다. 로그인하지 않은 사람에게도 보여주려면 그때만 끄세요.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">사진 올리기</h3>
                    <ol class="wiki-steps">
                        <li>먼저 <b>미디어 &rsaquo; 앨범</b>에서 사진을 담을 앨범을 만듭니다. 앨범명과 행사 날짜만 있으면 됩니다.</li>
                        <li><b>미디어 &rsaquo; 사진</b>으로 가서 <b>업로드</b>를 누릅니다.</li>
                        <li>앨범을 고르고 사진 파일을 올린 뒤 저장합니다.</li>
                        <li>앨범으로 돌아가 <b>활성화</b>를 켜야 홈페이지에 나옵니다.</li>
                    </ol>
                    <p class="wiki-note">올린 사진은 자동으로 가볍게 바뀝니다. 아이폰 사진도 그대로 올리시면 됩니다. 자세한 건 아래 <b>사진과 파일</b>에 있습니다.</p>
                </article>

                <article class="wiki-task">
                    <h3 class="wiki-task-title">소식 쓰기</h3>
                    <ol class="wiki-steps">
                        <li><b>콘텐츠 &rsaquo; 교회 소식 &rsaquo; 새로운 소식</b>.</li>
                        <li>제목과 내용을 적습니다. 슬러그는 비워두면 알아서 만들어집니다.</li>
                        <li>맨 아래 스위치로 정합니다. <b>상단 고정</b>은 소식 목록 맨 위에, <b>하이라이트</b>는 홈 화면 큰 칸에 올립니다.</li>
                    </ol>
                    <p class="wiki-warn"><b>성도의 이름이 들어가면 성도 전용을 켜세요.</b> 새가족 소개, 셀 배정, 봉사자 연락처 같은 것입니다. 켜지 않으면 이름이 검색 엔진에 그대로 올라갑니다.</p>
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
                    <h3 class="wiki-task-title">가입 신청 승인하기</h3>
                    <ol class="wiki-steps">
                        <li><b>계정 &rsaquo; 가입 신청</b>. 메뉴 옆 숫자가 기다리는 신청 수입니다.</li>
                        <li>신청을 열면 <b>교적부 대조</b>표가 나옵니다. 신청서에 적은 내용과 교회가 가진 기록을 나란히 보여줍니다.</li>
                        <li><b>교적 처리</b>를 고릅니다. 교적에 있는 분과 연결하거나, 새 성도로 교적에 올리거나, <b>교적에 올리지 않고 계정만</b> 만듭니다.</li>
                        <li>확인이 되면 <b>확인 방법</b>을 고르고 승인합니다.</li>
                    </ol>
                    <p class="wiki-warn"><b>이름과 생년월일이 맞는 것만으로는 본인 확인이 되지 않습니다.</b> 섬기는 사람들 페이지에 이름이 공개되어 있기 때문입니다. 교회가 따로 적어 둔 전화번호나 이메일이 맞을 때, 또는 직접 아는 분일 때만 승인하세요.</p>
                    <p class="wiki-warn"><b>우리 교회 성도가 아닌 분은 교적에 올리지 마세요.</b> 주보와 헌금 내역이 열리는 기준이 교적이기 때문입니다. 교적에 올리지 않으면 로그인은 되지만 성도 전용 자료는 보이지 않습니다. 나중에 성도가 되시면 그때 교적에 올리면 됩니다.</p>
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
            <summary class="wiki-summary"><span class="wiki-summary-title">누가 무엇을 보나</span><span class="wiki-summary-note">성도 전용이 어떻게 동작하는지</span></summary>
            <div class="wiki-body">

                <p class="wiki-lede">성도 전용을 켜면 <b>교적에 없는 사람의 화면에서 아예 빠집니다.</b> 가려 놓는 것이 아니라 목록에서 빠지기 때문에, 제목도 파일 주소도 나가지 않습니다.</p>

                <p class="wiki-warn"><b>기준은 로그인이 아니라 교적입니다.</b> 계정만 있고 교적에 없는 분은 로그인해도 성도 전용 자료가 보이지 않습니다. 가입 신청은 누구나 넣을 수 있어서, 로그인을 기준으로 삼으면 교회가 모르는 분에게도 주보와 헌금 내역이 열리기 때문입니다.</p>

                <h3 class="wiki-h3">처음 설정된 값</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>주보</b><span>처음부터 <b>켜짐</b> &middot; 셀 편성과 헌금 내역이 들어가서</span></div>
                    <div class="wiki-row"><b>문서</b><span>처음부터 <b>켜짐</b> &middot; 등록 카드에 가족 정보가 들어가서</span></div>
                    <div class="wiki-row"><b>교회 소식</b><span>처음에 <b>꺼짐</b> &middot; 이름이 들어갈 때만 직접 켜기</span></div>
                    <div class="wiki-row"><b>앨범</b><span>처음에 <b>꺼짐</b> &middot; 아이들 얼굴이 많으면 켜기</span></div>
                    <div class="wiki-row"><b>헌금 내역</b><span>스위치가 없습니다. <b>언제나</b> 로그인한 성도에게만 보입니다</span></div>
                </div>

                <h3 class="wiki-h3">앨범의 두 스위치는 다른 것입니다</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>활성화 꺼짐</b><span>아무에게도 안 보입니다. 로그인해도 안 보입니다. 아직 정리 중인 앨범</span></div>
                    <div class="wiki-row"><b>활성화만 켜짐</b><span>누구나 봅니다. 검색 엔진에도 올라갑니다</span></div>
                    <div class="wiki-row"><b>둘 다 켜짐</b><span>로그인한 성도만 봅니다. 검색 엔진에는 절대 올라가지 않습니다</span></div>
                </div>
                <p class="wiki-note"><b>활성화를 끄면 성도 전용 스위치가 화면에서 사라집니다.</b> 설정이 지워진 것이 아니라, 아무에게도 안 보이는 앨범에는 그 구분이 의미가 없어서 숨긴 것입니다. 활성화를 다시 켜면 돌아옵니다.</p>

                <h3 class="wiki-h3">알아두실 두 가지</h3>
                <p class="wiki-note"><b>홈 화면 사진은 모두 공개인 앨범에서만 나옵니다.</b> 앨범을 성도 전용으로 두면 그 안의 사진은 홈 화면에 올라가지 않습니다. <b>홈 슬라이더에 표시</b>를 켜 두었더라도 마찬가지입니다. 홈 화면은 처음 오시는 분이 반드시 보는 자리이기 때문입니다.</p>
                <p class="wiki-note"><b>성도 전용 링크를 받은 분이 "페이지가 없다"고 합니다.</b> 정상입니다. "로그인하세요"라고 하면 그 주소에 무언가 있다는 것을 알려주는 셈이라, 아예 없는 것처럼 처리합니다. 로그인하시면 바로 보입니다 - 다만 <b>교적에 계신 분만</b> 그렇습니다. 로그인해도 계속 안 보인다면 그분이 교적에 없는 것이니, <b>교적 &rsaquo; 성도</b>에서 확인해 주세요.</p>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">화면 안내</span><span class="wiki-summary-note">왼쪽 메뉴가 무엇을 하는 곳인지</span></summary>
            <div class="wiki-body">

                <h3 class="wiki-h3">콘텐츠</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>교회 소식</b><span>주보에 실리는 광고를 그대로. 상단 고정·하이라이트·성도 전용</span></div>
                    <div class="wiki-row"><b>교회 행사</b><span>일정. 홈 화면에 다가오는 것 3개, 행사 페이지에 월별로</span></div>
                    <div class="wiki-row"><b>예배 영상</b><span>유튜브 주소만 넣으면 됩니다. 새 설교는 자동으로도 들어옵니다</span></div>
                    <div class="wiki-row"><b>주보</b><span>주간 주보 PDF. 자료실의 첫 번째 탭</span></div>
                    <div class="wiki-row"><b>문서</b><span>새가족 카드, 지출결의서 같은 서식. 자료실의 두 번째 탭</span></div>
                </div>

                <h3 class="wiki-h3">미디어</h3>
                <div class="wiki-rows">
                    <div class="wiki-row"><b>앨범</b><span>사진을 담는 그릇. 활성화와 성도 전용을 여기서 정합니다</span></div>
                    <div class="wiki-row"><b>사진</b><span>사진 한 장씩. 홈 슬라이더에 넣을 사진도 여기서 고릅니다</span></div>
                    <div class="wiki-row"><b>동영상</b><span>아직 비어 있습니다. 나중에 영상이 들어올 자리</span></div>
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
                    <div class="wiki-row"><b>편집자</b><span>소식·행사·예배 영상·주보·문서·앨범·사진. <b>교적부와 헌금, 사이트 설정은 열리지 않습니다</b></span></div>
                    <div class="wiki-row"><b>재정부</b><span>헌금 내역과 개인 헌금만. 다른 메뉴는 아예 보이지 않습니다</span></div>
                    <div class="wiki-row"><b>일반회원</b><span>관리자 화면은 못 씁니다. 홈페이지만 봅니다</span></div>
                </div>

                <p class="wiki-note">주보와 소식만 올려주실 분에게는 <b>편집자</b>가 맞습니다. 성도들의 인적사항과 헌금이 열리지 않기 때문입니다.</p>

                <p class="wiki-warn"><b>역할에 '성도'는 없습니다.</b> 성도인지 아닌지는 <b>교적에 있는지</b>로 정해집니다. 가입 신청은 누구나 넣을 수 있어서, 승인했다는 것만으로는 우리 교회 성도라는 뜻이 되지 않기 때문입니다. 그래서 승인할 때 <b>교적에 올릴지</b>를 함께 고릅니다 - 교적에 올리면 주보와 헌금 내역이 열리고, 올리지 않으면 로그인만 되는 <b>일반회원</b>이 됩니다.</p>

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

                <h3 class="wiki-h3">지우면 되돌릴 수 없습니다</h3>
                <p class="wiki-warn"><b>앨범을 지우면 그 안의 사진이 전부 함께 지워집니다.</b> 사진 파일까지 지워지고, 휴지통은 없습니다. 사진 800장짜리 앨범도 확인 한 번이면 사라집니다. 잠시 감추고 싶은 것이라면 지우지 말고 <b>활성화만 꺼 두세요.</b></p>
                <p>주보·문서·사진을 지우면 파일도 함께 지워집니다. 다만 인터넷에 이미 퍼진 그림은 한동안 남아 있을 수 있습니다. 급히 내려야 할 사진이라면 지운 뒤에 알려주세요.</p>

            </div>
        </details>

        <details class="wiki-part">
            <summary class="wiki-summary"><span class="wiki-summary-title">로그인과 계정</span><span class="wiki-summary-note">2단계 인증, 성도 계정 만들기</span></summary>
            <div class="wiki-body">

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
                    <p class="wiki-q">앨범을 만들었는데 갤러리에 안 보여요</p>
                    <p class="wiki-a"><b>활성화</b>가 꺼져 있을 가능성이 큽니다. 앨범 목록에서 바로 켤 수 있습니다. 켰는데도 안 보이면 <b>성도 전용</b>이 켜져 있는지 보세요 - 그러면 로그인한 사람에게만 보입니다.</p>
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
                    <p class="wiki-q">홈 슬라이더에 표시를 켰는데 홈 화면에 안 나와요</p>
                    <p class="wiki-a">그 사진이 든 앨범이 <b>성도 전용</b>이거나 <b>활성화</b>가 꺼져 있습니다. 홈 화면 사진은 모두 공개인 앨범에서만 가져옵니다.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">성도님이 링크를 눌렀는데 페이지가 없대요</p>
                    <p class="wiki-a">성도 전용 글입니다. 로그인하면 보입니다. 계정이 없으시면 가입 신청을 안내해 주세요.</p>
                </div>

                <div class="wiki-qa">
                    <p class="wiki-q">자료실에 아무것도 없다고 해요</p>
                    <p class="wiki-a">주보와 문서는 처음부터 성도 전용이라, 로그인하지 않으면 목록 자체가 나오지 않고 로그인 안내만 보입니다.</p>
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
