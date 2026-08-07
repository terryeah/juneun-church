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
 * two giving sections carry a danger icon and a callout because a typo
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
            ->description('화면 맨 위 로고 옆과 맨 아래 바닥글에 표시되고, 구글 검색 결과의 교회 정보로도 쓰입니다.')
            ->icon(Heroicon::OutlinedBuildingLibrary)
            ->columns(3)
            ->schema([
                TextInput::make('church_name')
                    ->label('교회 이름 (한글)')
                    ->helperText('로고 옆 첫 줄과 바닥글에 그대로 표시됩니다.')
                    ->placeholder('브리즈번 주는교회')
                    ->required(),
                TextInput::make('church_name_en')
                    ->label('교회 이름 (영문)')
                    ->helperText('바닥글 맨 아래 저작권 줄과 구글 검색 정보에 쓰입니다.')
                    ->placeholder('Brisbane Juneun Church')
                    ->required(),
                TextInput::make('denomination')
                    ->label('교단')
                    ->helperText('교회 이름 바로 아래 작은 글씨로 머리글과 바닥글에 붙습니다.')
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
            ->description('바닥글의 지도 링크, 오시는 길(/location)의 구글 지도, 그리고 홈 화면과 예배 안내의 예배 시간표에 함께 표시됩니다.')
            ->icon(Heroicon::OutlinedMapPin)
            ->columns(2)
            ->schema([
                TextInput::make('address_main_label')
                    ->label('본당 이름표')
                    ->helperText('주소 위에 붙는 짧은 이름입니다.')
                    ->placeholder('본당')
                    ->required(),
                TextInput::make('address_main')
                    ->label('본당 주소')
                    ->helperText('오시는 길 지도가 이 주소를 그대로 검색합니다. 주일 2부 · 유초등부 · 청소년부 예배 장소로 안내됩니다.')
                    ->placeholder('71 Newnham Rd, Mt Gravatt East QLD 4122')
                    ->required()
                    ->extraInputAttributes(['data-google-places' => 'true', 'autocomplete' => 'off']),
                TextInput::make('address_education_label')
                    ->label('교육관 이름표')
                    ->helperText('주소 위에 붙는 짧은 이름입니다.')
                    ->placeholder('교육관')
                    ->required(),
                TextInput::make('address_education')
                    ->label('교육관 주소')
                    ->helperText('오시는 길 지도가 이 주소를 그대로 검색합니다. 주일 1부 예배와 수요기도회 장소로 안내됩니다.')
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
            ->description('바닥글 연락처 칸에 표시되고, 구글 검색 정보의 전화번호와 이메일로도 쓰입니다.')
            ->icon(Heroicon::OutlinedPhone)
            ->columns(2)
            ->schema([
                TextInput::make('contact_phone')
                    ->label('대표 전화번호')
                    ->tel()
                    ->helperText('앞쪽 숫자만 전화 걸기 링크에 쓰이므로 뒤에 담당자를 괄호로 덧붙여도 됩니다.')
                    ->placeholder('0415 346 455 (담임목사)'),
                TextInput::make('contact_email')
                    ->label('대표 이메일')
                    ->email()
                    ->helperText('바닥글에서 메일 보내기 링크가 됩니다. 비워 두면 그 줄이 사라집니다.')
                    ->placeholder('juneunchurch@gmail.com'),
            ]);
    }

    /**
     * The five services making up the timetable.
     */
    protected static function serviceTimes(): Section
    {
        return Section::make('예배 시간')
            ->description('홈 화면, 예배 안내(/worship), 오시는 길(/location) 세 곳에 같은 표로 표시됩니다. 각 예배 옆 주소는 위에 적은 본당 · 교육관 주소를 그대로 가져옵니다.')
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
                    ->helperText('표의 첫 줄에 굵게 표시됩니다.')
                    ->required(),
                TextInput::make($prefix.'_time')
                    ->label('예배 시간')
                    ->helperText('요일과 시간을 함께 적습니다.')
                    ->placeholder('주일 오후 1:30')
                    ->required(),
                TextInput::make($prefix.'_venue')
                    ->label('장소 표기')
                    ->helperText('주소 위에 표시되는 짧은 장소 이름입니다.')
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
            ->description('헌금 페이지(/giving) 왼쪽 카드에 그대로 표시됩니다.')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->iconColor('danger')
            ->columns(2)
            ->schema([
                Callout::make()
                    ->color('danger')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->heading('저장하기 전에 통장이나 은행 앱과 한 자리씩 대조해 주세요.')
                    ->description('성도들이 실제로 이체하는 계좌입니다. 숫자 하나만 틀려도 헌금이 다른 사람에게 갑니다.')
                    ->columnSpanFull(),
                TextInput::make('giving_bank')
                    ->label('은행 (Bank)')
                    ->helperText('카드 첫 줄에 표시됩니다.')
                    ->placeholder('Westpac')
                    ->required(),
                TextInput::make('giving_account_name')
                    ->label('예금주 (Account Name)')
                    ->helperText('은행에 등록된 교회 이름과 글자 하나까지 같아야 이체가 됩니다.')
                    ->placeholder('JU-NEUN PRESBYTERIAN CHURCH OF BRISBANE INC.')
                    ->required(),
                TextInput::make('giving_bsb')
                    ->label('BSB')
                    ->helperText('숫자 6자리입니다. 034069 처럼 붙여 적거나 034-069 처럼 적습니다.')
                    ->placeholder('034069')
                    ->required()
                    ->rule('regex:/^\d{3}-?\d{3}$/')
                    ->validationMessages(['regex' => 'BSB는 숫자 6자리여야 합니다.']),
                TextInput::make('giving_account_number')
                    ->label('계좌번호 (Account Number)')
                    ->helperText('숫자만 적습니다.')
                    ->placeholder('615113')
                    ->required()
                    ->rule('regex:/^[\d\- ]{5,}$/')
                    ->validationMessages(['regex' => '계좌번호는 숫자로만 적어 주세요.']),
            ]);
    }

    /**
     * The Korean giving account. The whole card disappears from /giving
     * when the account number is blank, which is why that field says so.
     */
    protected static function koreanAccount(): Section
    {
        return Section::make('헌금 계좌 (한국)')
            ->description('헌금 페이지(/giving) 오른쪽 카드에 표시됩니다.')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->iconColor('danger')
            ->columns(2)
            ->schema([
                Callout::make()
                    ->color('danger')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->heading('한국 계좌도 실제 송금에 쓰입니다.')
                    ->description('저장하기 전에 계좌번호를 한 자리씩 확인해 주세요.')
                    ->columnSpanFull(),
                TextInput::make('giving_kr_bank')
                    ->label('은행')
                    ->helperText('카드 첫 줄에 표시됩니다.')
                    ->placeholder('카카오뱅크'),
                TextInput::make('giving_kr_account_name')
                    ->label('예금주')
                    ->helperText('비워 두면 헌금 페이지에서 예금주 줄만 사라집니다.'),
                TextInput::make('giving_kr_account_number')
                    ->label('계좌번호')
                    ->helperText('이 칸을 비우면 한국 계좌 카드가 통째로 숨겨집니다.')
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
            ->description('바닥글의 인스타그램 · 유튜브 아이콘이 이 주소로 연결됩니다.')
            ->icon(Heroicon::OutlinedShare)
            ->columns(2)
            ->schema([
                TextInput::make('instagram_url')
                    ->label('인스타그램 주소')
                    ->url()
                    ->helperText('갤러리 사진 자동 가져오기도 이 계정을 기준으로 동작합니다.')
                    ->placeholder('https://www.instagram.com/juneun.church_brisbane/'),
                TextInput::make('youtube_url')
                    ->label('유튜브 주소')
                    ->url()
                    ->helperText('홈 화면 예배 영상 옆 "YouTube →" 링크와 예배 영상 자동 가져오기도 이 채널을 씁니다.')
                    ->placeholder('https://www.youtube.com/@juneun_church'),
            ]);
    }

    /**
     * The home page hero, named by gallery filename.
     */
    protected static function homePage(): Section
    {
        return Section::make('홈 화면 대표 사진')
            ->description('홈 화면을 열었을 때 맨 위에 크게 깔리는 사진입니다.')
            ->icon(Heroicon::OutlinedPhoto)
            ->schema([
                TextInput::make('home_hero_photo')
                    ->label('대표 사진 파일 이름')
                    ->helperText('갤러리에 올린 사진의 파일 이름을 그대로 적습니다. 사진 관리 화면에서 복사해 오세요. 비워 두거나 이름이 맞지 않으면 사진 없이 표시됩니다.')
                    ->placeholder('f632f799-3dee-4760-a3ae-f271ec8be9bf.webp'),
            ]);
    }
}
