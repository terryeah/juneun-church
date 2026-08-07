<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Cell;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\User;
use App\Support\RoleLabel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

/**
 * Form schema for the congregation roster (성도).
 */
class MemberForm
{
    /**
     * Configure the member form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('이름')
                    ->required()
                    ->maxLength(255),
                Select::make('gender')
                    ->label('성별')
                    ->options(['남' => '남', '여' => '여']),
                DatePicker::make('birth_date')
                    ->label('생년월일')
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                TextInput::make('phone')
                    ->label('전화번호')
                    ->tel()
                    ->placeholder('0411222333')
                    ->maxLength(10)
                    ->extraInputAttributes(['maxlength' => '10']),
                TextInput::make('email')
                    ->label('이메일')
                    ->email()
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('주소')
                    ->maxLength(255)
                    ->extraInputAttributes(['data-google-places' => 'true', 'autocomplete' => 'off']),
                Select::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name')
                    ->default(fn (): ?int => Position::query()->where('name', '성도')->value('id')),
                Select::make('department')
                    ->label('부서')
                    ->options(fn (): array => Ministry::query()->orderBy('sort_order')->pluck('name', 'name')->all()),
                Select::make('cell_id')
                    ->label('셀')
                    ->options(fn (): array => Cell::query()
                        ->with('leader:id,name')
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (Cell $cell): array => [$cell->id => $cell->displayName()])
                        ->all()),
                Select::make('baptism_type')
                    ->label('세례 여부')
                    ->options([
                        '유아세례' => '유아세례',
                        '세례' => '세례',
                        '입교' => '입교',
                        '미세례' => '미세례',
                    ]),
                DatePicker::make('baptism_date')
                    ->label('세례일')
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                Select::make('status')
                    ->label('상태')
                    ->options(MemberResource::STATUSES)
                    ->default('재적')
                    ->required(),
                DatePicker::make('registered_at')
                    ->label('등록일')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->default(today()),
                DatePicker::make('new_family_completed_at')
                    ->label('새가족 수료')
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                /** Searched on the server so the page never carries the whole roster. */
                Select::make('head_id')
                    ->label('가족 대표')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search, ?Member $record): array => Member::query()
                        ->whereNull('head_id')
                        ->whereKeyNot($record?->getKey())
                        ->whereLike('name', "%{$search}%")
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->all())
                    ->getOptionLabelUsing(fn (?string $value): ?string => Member::query()->whereKey($value)->value('name'))
                    ->helperText('본인이 가족 대표이면 비워두세요.')
                    ->rule(fn (?Member $record): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                        if ($value && $record && $record->family()->exists()) {
                            $fail('가족을 거느린 가족 대표에게는 가족 대표를 지정할 수 없습니다.');
                        }
                        if ($value && Member::query()->whereKey($value)->whereNotNull('head_id')->exists()) {
                            $fail('가족 대표가 아닌 성도를 가족 대표로 지정할 수 없습니다.');
                        }
                    }),
                Select::make('relationship')
                    ->label('가족 대표와의 관계')
                    ->options([
                        '배우자' => '배우자',
                        '자녀' => '자녀',
                        '부모' => '부모',
                        '조부모' => '조부모',
                        '형제자매' => '형제자매',
                        '기타' => '기타',
                    ]),
                Textarea::make('notes')
                    ->label('메모')
                    ->rows(9)
                    ->helperText('심방 기록 등 내부 메모입니다.'),
                FileUpload::make('photo')
                    ->label('사진')
                    ->image()
                    ->maxSize(10240)
                    ->imageEditor()
                    ->disk(config('filesystems.media'))
                    ->directory('members')
                    ->visibility('public'),
                Toggle::make('site_account')
                    ->label('사이트 계정')
                    ->helperText('켜면 이 성도가 관리자 사이트에 로그인할 수 있는 계정을 갖게 됩니다.')
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (Toggle $component, ?Member $record) => $component->state($record?->user_id !== null))
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('site_email')
                    ->label('로그인 이메일')
                    ->email()
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (TextInput $component, ?Member $record) => $component->state($record?->user?->email))
                    ->visible(fn (Get $get): bool => (bool) $get('site_account'))
                    ->requiredIf('site_account', true)
                    ->rule(fn (?Member $record): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                        if ($value && User::query()->where('email', $value)->whereKeyNot($record?->user_id)->exists()) {
                            $fail('이미 다른 계정이 사용 중인 이메일입니다.');
                        }
                    }),
                TextInput::make('site_password')
                    ->label('비밀번호')
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->visible(fn (Get $get): bool => (bool) $get('site_account'))
                    ->required(fn (?Member $record): bool => $record?->user_id === null)
                    ->helperText('수정 시 비워두면 기존 비밀번호가 유지됩니다.'),
                Select::make('site_roles')
                    ->label('롤')
                    ->multiple()
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (Select $component, ?Member $record) => $component->state($record?->user?->roles->pluck('id')->all() ?? []))
                    ->options(function (): array {
                        $user = auth()->user();

                        return Role::query()
                            ->when(! $user?->hasRole('super_admin'), fn ($query) => $query->where('name', '!=', 'super_admin'))
                            ->when(! $user?->hasRole('developer'), fn ($query) => $query->where('name', '!=', 'developer'))
                            ->get()
                            ->mapWithKeys(fn ($role) => [$role->id => RoleLabel::label($role->name)])
                            ->all();
                    })
                    ->visible(fn (Get $get): bool => (bool) $get('site_account'))
                    ->requiredIf('site_account', true)
                    ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                        $user = auth()->user();
                        $allowed = Role::query()
                            ->when(! $user?->hasRole('super_admin'), fn ($query) => $query->where('name', '!=', 'super_admin'))
                            ->when(! $user?->hasRole('developer'), fn ($query) => $query->where('name', '!=', 'developer'))
                            ->pluck('id');

                        foreach ((array) $value as $roleId) {
                            if (! $allowed->contains((int) $roleId)) {
                                $fail('부여할 수 없는 롤이 포함되어 있습니다.');
                            }
                        }
                    }),
                /**
                 * The enabled pill mirrors the 로그인 유저 전용 badge on the
                 * public 헌금 page. Site utility classes are absent inside the
                 * admin panel, so the pill is styled inline with the same
                 * --color-success green on navy, which reads on both the
                 * light and dark admin themes. The disabled state matches the
                 * grey 미설정 badge shown on the 계정 (users) table.
                 */
                Placeholder::make('two_factor')
                    ->label('2단계 인증')
                    ->content(fn (?Member $record): HtmlString => filled($record?->user?->app_authentication_secret)
                        ? new HtmlString('<span style="display:inline-block;border:0.0625rem solid #2fbf71;border-radius:0.375rem;background-color:#16223c;padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:500;line-height:1.25rem;color:#2fbf71;">사용 중</span>')
                        : new HtmlString('<span class="fi-badge fi-size-md fi-color fi-color-gray"><span class="fi-badge-label-ctn"><span class="fi-badge-label">미설정</span></span></span>'))
                    ->visible(fn (?Member $record): bool => $record?->user_id !== null),
            ]);
    }
}
