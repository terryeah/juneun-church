<?php

namespace App\Filament\Resources\Devotions\Schemas;

use App\Models\Devotion;
use App\Models\Sermon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DevotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('devotion_date')
                    ->label('날짜')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->helperText('성도님들께는 이 날짜부터 보입니다. 미리 만들어 두셔도 됩니다.')
                    ->default(fn () => Devotion::query()->latest('devotion_date')->first()?->devotion_date->addDay() ?? today())
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255),
                TextInput::make('scripture')
                    ->label('본문')
                    ->placeholder('사도행전 11:22-24')
                    ->required()
                    ->maxLength(255),
                Select::make('sermon_id')
                    ->label('연결된 설교')
                    ->helperText('이 큐티의 바탕이 된 주일 설교입니다. 비워 두셔도 됩니다.')
                    ->relationship('sermon', 'title', fn (Builder $query) => $query->orderByDesc('sermon_date'))
                    ->getOptionLabelFromRecordUsing(fn (Sermon $sermon): string => $sermon->sermon_date?->format('Y-m-d').' '.$sermon->title)
                    ->searchable()
                    ->preload(),
                Textarea::make('passage')
                    ->label('성경 말씀')
                    ->helperText('본문 말씀을 붙여 넣으세요. 비워 두면 본문 주소만 보입니다.')
                    ->rows(8)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('묵상 길잡이')
                    ->helperText('해설, 묵상 질문, 기도 등 성도님들이 읽고 묵상할 내용입니다.')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('게시')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}
