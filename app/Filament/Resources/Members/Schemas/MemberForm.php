<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Models\Ministry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->helperText('본인이 세대주이면 비워두세요.'),
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
            ]);
    }
}
