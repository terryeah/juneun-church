<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected ?string $subheading = '읽기 전용 목록입니다. 사이트 계정은 성도 레코드의 사이트 계정 섹션에서 만들고 수정합니다.';
}
