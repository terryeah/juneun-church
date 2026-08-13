<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Schemas\MembershipRequestInfolist;
use App\Models\Cell;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\User;
use App\Support\RoleLabel;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
                    ->options(fn (): array => static::assignableRoles()
                        ->mapWithKeys(fn (Role $role): array => [$role->id => RoleLabel::label($role->name)])
                        ->all())
                    ->visible(fn (Get $get): bool => (bool) $get('site_account'))
                    ->requiredIf('site_account', true)
                    ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                        $allowed = static::assignableRoles()->pluck('id');

                        foreach ((array) $value as $roleId) {
                            if (! $allowed->contains((int) $roleId)) {
                                $fail('부여할 수 없는 롤이 포함되어 있습니다.');
                            }
                        }
                    }),
                /**
                 * The enabled pill mirrors the 성도 전용 badge on the
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
                static::signupSubmission(),
            ]);
    }

    /**
     * What the applicant typed into the public 가입 신청 form, for a
     * member whose account came from one.
     *
     * The fields above are the church's record and are edited freely, so
     * once an approval has copied a name onto the 교적 nothing on this
     * page still shows what the person actually submitted - and an
     * applicant's browser will autofill a romanised name into 이름
     * without them noticing. Reading it back beside the roster record is
     * how an office finds out, so the submission is shown as a
     * comparison rather than as a second copy of the same fields.
     *
     * It is read-only on purpose. A 가입 신청 is the evidence an
     * approval was made on; editing it here would rewrite the evidence.
     */
    private static function signupSubmission(): Section
    {
        return Section::make(fn (?Member $record): string => static::signupConflicts($record) === 0
                ? '가입 신청 내용'
                : '가입 신청 내용 · 다른 항목 '.static::signupConflicts($record).'건')
            ->description('이 성도가 홈페이지에서 가입 신청할 때 본인이 적은 내용입니다. 신청서는 그대로 보관되며 고칠 수 없습니다. 다른 항목이 있으면 위의 교적부 내용을 고쳐 주세요.')
            ->icon(Heroicon::OutlinedInboxArrowDown)
            ->iconColor(fn (?Member $record): string => static::signupConflicts($record) === 0 ? 'gray' : 'warning')
            ->columnSpanFull()
            ->columns(2)
            ->collapsible()
            /**
             * Open when there is something to see and shut when there is
             * not. Collapsing it unconditionally would put the
             * submission back out of sight, which is the bug; leaving it
             * open on the many records where every field agrees buys a
             * screenful of nothing.
             */
            ->collapsed(fn (?Member $record): bool => static::signupConflicts($record) === 0)
            ->visible(fn (?Member $record): bool => static::signupRequest($record) !== null)
            ->schema([
                Callout::make()
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArchiveBoxXMark)
                    ->heading('신청서 내용은 지워졌습니다')
                    ->description('처리한 지 90일이 지나 신청자가 적은 개인정보를 지웠습니다. 아래 처리 기록만 남아 있습니다.')
                    ->visible(fn (?Member $record): bool => static::signupRequest($record)?->isRedacted() ?? false)
                    ->columnSpanFull(),
                /**
                 * The same comparison the reviewing administrator saw on
                 * the 가입 신청 itself, so the two screens do not
                 * disagree about what counts as a match.
                 *
                 * Entry labels are set rather than hidden: below the
                 * repeatable's own breakpoint Filament drops the header
                 * row and stacks each row as a block, captioning every
                 * cell with its entry label - so hiddenLabel() leaves a
                 * phone showing four bare values, two of which are
                 * names, with nothing to say which is which.
                 */
                RepeatableEntry::make('signup_comparison')
                    ->hiddenLabel()
                    ->state(fn (?Member $record): array => static::signupRequest($record)?->comparison($record) ?? [])
                    ->visible(fn (?Member $record): bool => ! (static::signupRequest($record)?->isRedacted() ?? true))
                    ->helperText('승인할 때 신청서 내용이 그대로 교적부에 옮겨지므로 대부분은 일치로 나옵니다. 불일치는 신청서에 다르게 적혀 있거나 승인한 뒤에 교적부를 고친 항목이니, 그 줄만 확인하시면 됩니다.')
                    ->table([
                        TableColumn::make('항목'),
                        TableColumn::make('신청서 내용'),
                        TableColumn::make('현재 교적부'),
                        TableColumn::make('대조'),
                    ])
                    ->schema([
                        TextEntry::make('field')->label('항목'),
                        TextEntry::make('submitted')->label('신청서 내용')->placeholder('-'),
                        TextEntry::make('held')->label('현재 교적부')->placeholder('비어 있음'),
                        TextEntry::make('verdict')
                            ->label('대조')
                            ->badge()
                            ->color(fn (string $state): string => MembershipRequestInfolist::verdictColour($state)),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('signup_note')
                    ->label('남기실 말씀')
                    ->state(fn (?Member $record): ?string => static::signupRequest($record)?->note)
                    ->visible(fn (?Member $record): bool => ! (static::signupRequest($record)?->isRedacted() ?? true))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('signup_created_at')
                    ->label('신청일')
                    ->state(fn (?Member $record): ?Carbon => static::signupRequest($record)?->created_at)
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('signup_verification_method')
                    ->label('확인 방법')
                    ->state(fn (?Member $record): ?string => static::signupRequest($record)?->verification_method)
                    ->placeholder('-'),
                TextEntry::make('signup_reviewer')
                    ->label('처리자')
                    ->state(fn (?Member $record): ?string => ($request = static::signupRequest($record))?->reviewed_at
                        ? ($request->reviewer?->name ?? '삭제된 계정')
                        : null)
                    ->placeholder('-'),
                TextEntry::make('signup_reviewed_at')
                    ->label('처리일')
                    ->state(fn (?Member $record): ?Carbon => static::signupRequest($record)?->reviewed_at)
                    ->dateTime()
                    ->placeholder('-'),
                /**
                 * 확인 메모 may name other people, so it stays where the
                 * 가입 신청 permission is checked in full rather than
                 * being copied onto the roster form. The link opens in a
                 * new tab because this form may hold unsaved edits, and
                 * because the reason to read the original is to correct
                 * the fields above it.
                 */
                Actions::make([
                    Action::make('viewSignupRequest')
                        ->label('가입 신청 원본 보기')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->color('gray')
                        ->url(fn (?Member $record): ?string => MembershipRequestResource::getUrl('view', [
                            'record' => static::signupRequest($record),
                        ]))
                        ->openUrlInNewTab(),
                ])->columnSpanFull(),
            ]);
    }

    /**
     * How many submitted fields disagree with the roster record.
     *
     * Drives whether the section announces itself: an approval copies
     * the submission straight onto the 교적, so on most records every
     * field agrees and there is nothing to look at.
     */
    private static function signupConflicts(?Member $record): int
    {
        $request = static::signupRequest($record);

        if (! $request || $request->isRedacted()) {
            return 0;
        }

        return collect($request->comparison($record))
            ->where('verdict', MembershipRequest::VERDICT_CONFLICT)
            ->count();
    }

    /**
     * The 가입 신청 behind this roster record, or null when the office
     * registered the member by hand or the viewer may not read one.
     *
     * The permission is checked here rather than trusted from the
     * roster's own: a 성도 record is edited by more people than review
     * sign-ups, and the submission carries the applicant's own words
     * along with the free-text answers the reviewer gave. Eloquent
     * memoises the relationship, so asking repeatedly across the section
     * costs one query.
     *
     * viewAny is asked for as well as view, because that is what makes
     * the 가입 신청 resource reachable at all - without it, a role given
     * only View:MembershipRequest by hand in the Shield editor could
     * read a submission here that it can reach nowhere else.
     *
     * Public because the edit page logs the read, and the log has to
     * answer to exactly the same question the section does. Asking it
     * twice in two places drifted immediately: the page recorded a
     * 열람 for a viewer the section had shown nothing to.
     */
    public static function signupRequest(?Member $record): ?MembershipRequest
    {
        $request = $record?->membershipRequest;
        $user = auth()->user();

        return $request && $user?->can('viewAny', MembershipRequest::class) && $user->can('view', $request)
            ? $request
            : null;
    }

    /**
     * Roles that may be granted from this form.
     *
     * developer is never offered here: it reaches the activity log, the
     * schema explorer and the password-reset links, so it is granted by
     * hand rather than by anyone editing a roster record. super_admin
     * stays hidden from everyone below it, so nobody can lift an account
     * above their own.
     *
     * @return Collection<int, Role>
     */
    public static function assignableRoles(): Collection
    {
        return Role::query()
            ->where('name', '!=', 'developer')
            ->when(
                ! auth()->user()?->hasRole('super_admin'),
                fn ($query) => $query->where('name', '!=', 'super_admin'),
            )
            ->orderBy('id')
            ->get();
    }
}
