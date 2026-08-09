<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * The 사이트 설정 form, grouped by where each value appears on the
 * public site rather than by database key.
 *
 * Every field name is the `site_settings.key` the public views read, so
 * the state array this schema produces is already keyed for saving. The
 * two giving sections carry a warning icon and a callout because a typo
 * there sends a congregation's offering to a stranger.
 */
class SiteSettingForm
{
    /**
     * Assemble the eight groups in the order a secretary reads the site:
     * identity, addresses, contact, service times, giving, social, home.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                static::identity(),
                static::addresses(),
                static::contact(),
                static::serviceTimes(),
                static::australianAccount(),
                static::koreanAccount(),
                static::social(),
                static::homePage(),
            ]);
    }

    /**
     * Church name and denomination, shown in the header and the footer.
     */
    protected static function identity(): Section
    {
        return Section::make('교회 이름과 교단')
            ->description('모든 페이지 맨 위 로고 옆과 맨 아래에 표시되고, 구글 검색 결과의 교회 정보로도 쓰여요.')
            ->icon(Heroicon::OutlinedBuildingLibrary)
            ->columns(3)
            ->schema([
                TextInput::make('church_name')
                    ->label('교회 이름 (한글)')
                    ->helperText('로고 옆 첫 줄과 페이지 맨 아래에 그대로 표시돼요.')
                    ->placeholder('브리즈번 주는교회')
                    ->required(),
                TextInput::make('church_name_en')
                    ->label('교회 이름 (영문)')
                    ->helperText('페이지 맨 아래 저작권 줄과 구글 검색 정보에 쓰여요.')
                    ->placeholder('Brisbane Juneun Church')
                    ->required(),
                TextInput::make('denomination')
                    ->label('교단')
                    ->helperText('교회 이름 바로 아래 작은 글씨로 맨 위와 맨 아래에 함께 붙어요.')
                    ->placeholder('대한예수교장로회')
                    ->required(),
            ]);
    }

    /**
     * The two venues. Both feed the footer links, the 오시는 길 maps and
     * the address line beside each service in the timetable.
     */
    protected static function addresses(): Section
    {
        return Section::make('예배 장소 주소')
            ->description('페이지 맨 아래 지도 링크, 오시는 길의 지도, 그리고 홈 화면과 예배 안내의 예배 시간표에 함께 표시돼요.')
            ->icon(Heroicon::OutlinedMapPin)
            ->columns(2)
            ->schema([
                TextInput::make('address_main_label')
                    ->label('본당 이름표')
                    ->helperText('주소 위에 붙는 짧은 이름이에요.')
                    ->placeholder('본당')
                    ->required(),
                TextInput::make('address_main')
                    ->label('본당 주소')
                    ->helperText('오시는 길 지도가 이 주소를 그대로 찾아요. 주일 2부 · 유초등부 · 청소년부 예배 장소로 안내돼요.')
                    ->placeholder('71 Newnham Rd, Mt Gravatt East QLD 4122')
                    ->required()
                    ->extraInputAttributes(['data-google-places' => 'true', 'autocomplete' => 'off']),
                TextInput::make('address_education_label')
                    ->label('교육관 이름표')
                    ->helperText('주소 위에 붙는 짧은 이름이에요.')
                    ->placeholder('교육관')
                    ->required(),
                TextInput::make('address_education')
                    ->label('교육관 주소')
                    ->helperText('오시는 길 지도가 이 주소를 그대로 찾아요. 주일 1부 예배와 수요기도회 장소로 안내돼요.')
                    ->placeholder('147 Kameruka St, Calamvale QLD 4116')
                    ->required()
                    ->extraInputAttributes(['data-google-places' => 'true', 'autocomplete' => 'off']),
            ]);
    }

    /**
     * Phone and email, shown in the footer and handed to search engines.
     */
    protected static function contact(): Section
    {
        return Section::make('연락처')
            ->description('페이지 맨 아래 연락처 칸에 표시되고, 구글 검색 정보에도 함께 쓰여요.')
            ->icon(Heroicon::OutlinedPhone)
            ->columns(2)
            ->schema([
                TextInput::make('contact_phone')
                    ->label('대표 전화번호')
                    ->tel()
                    ->helperText('앞쪽 숫자만 전화 걸기에 쓰여요. 뒤에 담당자를 괄호로 덧붙여도 괜찮아요.')
                    ->placeholder('0415 346 455 (담임목사)'),
                TextInput::make('contact_email')
                    ->label('대표 이메일')
                    ->email()
                    ->helperText('누르면 메일 쓰기가 열려요. 비워두면 그 줄이 사라져요.')
                    ->placeholder('juneunchurch@gmail.com'),
            ]);
    }

    /**
     * The five services making up the timetable.
     */
    protected static function serviceTimes(): Section
    {
        return Section::make('예배 시간')
            ->description('홈 화면, 예배 안내(/worship), 오시는 길(/location) 세 곳에 같은 표로 나와요. 예배 옆 주소는 위에 적은 본당 · 교육관 주소를 그대로 가져와요.')
            ->icon(Heroicon::OutlinedClock)
            ->schema([
                static::service('sunday_first_service', '주일 1부 예배', '표의 첫 줄입니다. 교육관 주소와 함께 표시되고, 오시는 길 교육관 지도 아래에도 이름과 시간이 붙습니다.'),
                static::service('sunday_service', '주일 2부 예배', '본당 주소와 함께 표시되고, 오시는 길 본당 지도 아래에도 이름과 시간이 붙습니다.'),
                static::service('wednesday_service', '수요기도회', '교육관 주소와 함께 표시됩니다.'),
                static::service('kids_service', '유초등부', '본당 주소와 함께 표시됩니다.'),
                static::service('youth_service', '청소년부', '본당 주소와 함께 표시됩니다.'),
            ]);
    }

    /**
     * One row of the timetable: name, time and venue label.
     *
     * @param  string  $prefix  Setting key prefix, for example sunday_service
     * @param  string  $heading  The row's name in the admin panel
     * @param  string  $description  Where this row surfaces on the site
     */
    protected static function service(string $prefix, string $heading, string $description): Section
    {
        return Section::make($heading)
            ->description($description)
            ->compact()
            ->columns(3)
            ->schema([
                TextInput::make($prefix.'_name')
                    ->label('예배 이름')
                    ->helperText('표의 첫 줄에 굵게 나와요.')
                    ->required(),
                TextInput::make($prefix.'_time')
                    ->label('예배 시간')
                    ->helperText('요일과 시간을 함께 적어주세요.')
                    ->placeholder('주일 오후 1:30')
                    ->required(),
                TextInput::make($prefix.'_venue')
                    ->label('장소 표기')
                    ->helperText('주소 위에 나오는 짧은 장소 이름이에요.')
                    ->placeholder('본당 · Worship Centre')
                    ->required(),
            ]);
    }

    /**
     * The Australian giving account. Money leaves the congregation's
     * hands on these four values, so the group is marked as dangerous.
     */
    protected static function australianAccount(): Section
    {
        return Section::make('헌금 계좌 (호주)')
            ->description('헌금 페이지(/giving) 왼쪽 카드에 그대로 나와요.')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->iconColor('warning')
            ->columns(2)
            ->schema([
                Callout::make()
                    ->color('warning')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->heading('저장하기 전에 통장이나 은행 앱과 한 자리씩 맞춰보세요.')
                    ->description('성도들이 실제로 이체하는 계좌예요. 숫자 하나만 틀려도 헌금이 다른 사람에게 가요.')
                    ->columnSpanFull(),
                TextInput::make('giving_bank')
                    ->label('은행 (Bank)')
                    ->helperText('카드 첫 줄에 나와요.')
                    ->placeholder('Westpac')
                    ->required(),
                TextInput::make('giving_account_name')
                    ->label('예금주 (Account Name)')
                    ->helperText('은행에 등록된 이름과 글자 하나까지 같아야 이체가 돼요.')
                    ->placeholder('JU-NEUN PRESBYTERIAN CHURCH OF BRISBANE INC.')
                    ->required(),
                TextInput::make('giving_bsb')
                    ->label('BSB')
                    ->helperText('숫자 6자리예요. 034069처럼 붙여 적거나 034-069처럼 적어요.')
                    ->placeholder('034069')
                    ->required()
                    ->rule('regex:/^\d{3}-?\d{3}$/')
                    ->validationMessages(['regex' => 'BSB는 숫자 6자리여야 합니다.']),
                TextInput::make('giving_account_number')
                    ->label('계좌번호 (Account Number)')
                    ->helperText('숫자만 적어주세요.')
                    ->placeholder('615113')
                    ->required()
                    ->rule('regex:/^[\d\- ]{5,}$/')
                    ->validationMessages(['regex' => '계좌번호는 숫자로만 적어주세요.']),
            ]);
    }

    /**
     * The Korean giving account. The whole card disappears from /giving
     * when the account number is blank, which is why that field says so.
     */
    protected static function koreanAccount(): Section
    {
        return Section::make('헌금 계좌 (한국)')
            ->description('헌금 페이지(/giving) 오른쪽 카드에 나와요.')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->iconColor('warning')
            ->columns(2)
            ->schema([
                Callout::make()
                    ->color('warning')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->heading('한국 계좌도 실제 송금에 쓰여요.')
                    ->description('저장하기 전에 계좌번호를 한 자리씩 맞춰보세요.')
                    ->columnSpanFull(),
                TextInput::make('giving_kr_bank')
                    ->label('은행')
                    ->helperText('카드 첫 줄에 나와요.')
                    ->placeholder('카카오뱅크'),
                TextInput::make('giving_kr_account_name')
                    ->label('예금주')
                    ->helperText('비워두면 예금주 줄만 사라져요.'),
                TextInput::make('giving_kr_account_number')
                    ->label('계좌번호')
                    ->helperText('이 칸을 비우면 한국 계좌 카드가 통째로 숨겨져요.')
                    ->placeholder('3333-31-2167745')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Social profiles. Both also steer the photo and sermon importers.
     */
    protected static function social(): Section
    {
        return Section::make('소셜 링크')
            ->description('페이지 맨 아래 인스타그램 · 유튜브 아이콘이 이 주소로 연결돼요.')
            ->icon(Heroicon::OutlinedShare)
            ->columns(2)
            ->schema([
                TextInput::make('instagram_url')
                    ->label('인스타그램 주소')
                    ->url()
                    ->helperText('갤러리 사진 자동 가져오기도 이 계정을 써요.')
                    ->placeholder('https://www.instagram.com/juneun.church_brisbane/'),
                TextInput::make('youtube_url')
                    ->label('유튜브 주소')
                    ->url()
                    ->helperText('홈 화면의 "YouTube →" 링크와 예배 영상 자동 가져오기도 이 채널을 써요.')
                    ->placeholder('https://www.youtube.com/@juneun_church'),
            ]);
    }

    /**
     * The home page hero, named by gallery filename.
     */
    protected static function homePage(): Section
    {
        return Section::make('홈 화면 대표 사진')
            ->description('홈 화면 맨 위에 크게 깔리는 사진이에요.')
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                TextInput::make('home_hero_photo')
                    ->label('대표 사진 파일 이름')
                    ->helperText('미디어 > 사진에서 쓰고 싶은 사진을 열면 맨 위 "파일 이름" 칸에 복사 버튼이 있어요. 눌러서 복사한 뒤 여기에 붙여넣으세요. 비워두거나 이름이 맞지 않으면 사진 없이 나와요.')
                    ->placeholder('f632f799-3dee-4760-a3ae-f271ec8be9bf.webp'),
            ]);
    }
}
