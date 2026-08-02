<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\User;
use App\Support\RoleLabel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
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
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('이메일')
                    ->email()
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('주소')
                    ->maxLength(255),
                Select::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name'),
                Select::make('department')
                    ->label('부서 / 사역')
                    ->options(fn (): array => Ministry::query()->orderBy('sort_order')->pluck('name', 'name')->all()),
                Select::make('baptism_type')
                    ->label('세례 구분')
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
                Select::make('head_id')
                    ->label('세대주')
                    ->options(fn (?Member $record): array => Member::query()
                        ->whereNull('head_id')
                        ->whereKeyNot($record?->getKey())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->helperText('본인이 세대주이면 비워두세요.')
                    ->rule(fn (?Member $record): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                        if ($value && $record && $record->family()->exists()) {
                            $fail('가족을 거느린 세대주에게는 세대주를 지정할 수 없습니다.');
                        }
                        if ($value && Member::query()->whereKey($value)->whereNotNull('head_id')->exists()) {
                            $fail('세대주가 아닌 성도를 세대주로 지정할 수 없습니다.');
                        }
                    }),
                Select::make('relationship')
                    ->label('세대주와의 관계')
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
            ]);
    }
}
