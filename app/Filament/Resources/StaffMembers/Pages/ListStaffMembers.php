<?php

namespace App\Filament\Resources\StaffMembers\Pages;

use App\Filament\Resources\StaffMembers\StaffMemberResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffMembers extends ListRecords
{
    protected static string $resource = StaffMemberResource::class;

    protected ?string $subheading = '직분이나 부서 / 사역이 채워진 성도가 자동으로 나타나는 읽기 전용 목록입니다. 수정은 성도 레코드에서 하세요.';
}
