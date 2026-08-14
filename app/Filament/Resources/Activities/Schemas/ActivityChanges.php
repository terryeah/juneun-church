<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Filament\Support\Author;
use App\Models\Album;
use App\Models\Cell;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Position;
use App\Models\ServiceType;
use App\Models\User;
use App\Providers\AppServiceProvider;
use ArrayObject;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Turns a stored activity row into something a person can read.
 *
 * The log is written for machines: raw column names, raw values, one
 * JSON blob per row. Printed back as pretty JSON it answered the
 * question "was anything recorded" and nothing else - the reader was
 * left counting braces to see that position_id went from 4 to 9, and
 * still had no idea which 직분 either number was.
 *
 * So the blob becomes a table of 항목 / 이전 / 이후 with the column
 * names in Korean, the foreign keys resolved to the thing they point at,
 * and dates and booleans written the way the rest of the panel writes
 * them.
 */
class ActivityChanges
{
    /**
     * Korean names for the columns the log records, across every model
     * that logs. Anything missing falls through to the raw column name,
     * which is still readable and is what a new column will show until
     * somebody adds it here.
     *
     * @var array<string, string>
     */
    private const LABELS = [
        'address' => '주소',
        'album_id' => '앨범',
        'amount' => '금액',
        'baptism_date' => '세례일',
        'baptism_type' => '세례 여부',
        'bio' => '소개',
        'birth_date' => '생년월일',
        'caption' => '설명',
        'category' => '분류',
        'cell_id' => '셀',
        'content' => '내용',
        'cover_photo_path' => '대표 사진',
        'cover_thumbnail_path' => '대표 사진 썸네일',
        'created_by' => '작성자',
        'department' => '부서',
        'description' => '설명',
        'email' => '이메일',
        'end_date' => '종료일',
        'end_time' => '종료 시각',
        'event_date' => '행사일',
        'event_time' => '행사 시각',
        'expires_at' => '만료 일시',
        'featured_image' => '대표 이미지',
        'featured_in_slider' => '홈 슬라이더',
        'file_path' => '파일',
        'file_size' => '파일 크기',
        'filename' => '파일명',
        'gender' => '성별',
        'group' => '그룹',
        'head_id' => '가족 대표',
        'height' => '높이',
        'is_highlighted' => '홈 강조',
        'is_members_only' => '성도 전용',
        'is_pinned' => '상단 고정',
        'is_published' => '게시',
        'items' => '헌금 항목',
        'key' => '키',
        'leader_id' => '셀장',
        'location' => '행사장',
        'matched_member_id' => '연결된 성도',
        'member_id' => '성도',
        'name' => '이름',
        'new_family_completed_at' => '새가족 수료',
        'note' => '메모',
        'notes' => '메모',
        'offering_id' => '헌금 주차',
        'original_filename' => '원본 파일명',
        'path' => '경로',
        'phone' => '전화번호',
        'photo' => '사진',
        'position_id' => '직분',
        'preacher' => '설교자',
        'published_at' => '게시 일시',
        'registered_at' => '등록일',
        'relationship' => '가족 대표와의 관계',
        'reviewed_at' => '처리일',
        'reviewed_by' => '처리자',
        'scripture_reference' => '본문',
        'sermon_date' => '설교일',
        'service_type_id' => '예배 종류',
        'slug' => '주소 (slug)',
        'sort_order' => '정렬 순서',
        'status' => '상태',
        'sunday_date' => '주일',
        'thumbnail_path' => '썸네일',
        'title' => '제목',
        'type' => '종류',
        'uploaded_by' => '업로더',
        'url' => '링크',
        'user_id' => '사이트 계정',
        'value' => '값',
        'verification_method' => '확인 방법',
        'width' => '너비',
        'youtube_id' => '유튜브 ID',
        'youtube_video_id' => '유튜브 ID',
    ];

    /**
     * Columns holding a foreign key, and the model and column that names
     * what it points at. A bare id tells the reader nothing, and these
     * are the ones the logged models actually record.
     *
     * @var array<string, array{class-string<Model>, string}>
     */
    private const REFERENCES = [
        'album_id' => [Album::class, 'title'],
        'cell_id' => [Cell::class, 'name'],
        'created_by' => [User::class, 'name'],
        'head_id' => [Member::class, 'name'],
        'leader_id' => [Member::class, 'name'],
        'matched_member_id' => [Member::class, 'name'],
        'member_id' => [Member::class, 'name'],
        'offering_id' => [Offering::class, 'sunday_date'],
        'position_id' => [Position::class, 'name'],
        'reviewed_by' => [User::class, 'name'],
        'service_type_id' => [ServiceType::class, 'name'],
        'uploaded_by' => [User::class, 'name'],
        'user_id' => [User::class, 'name'],
    ];

    /**
     * Property keys that describe the request rather than the record.
     * They are the whole story on an auth or page row, which carries no
     * attribute changes at all.
     *
     * @var array<string, string>
     */
    private const CONTEXT = [
        'ip' => 'IP 주소',
        'url' => '페이지 주소',
        'email' => '입력한 이메일',
    ];

    /**
     * What each recorded event is called in Korean.
     *
     * The column stored 'updated' and the screen showed 'updated', which
     * is the log talking to itself. Anything not listed falls through to
     * the raw name, so a new event is unlabelled rather than invisible.
     *
     * @var array<string, string>
     */
    public const EVENTS = [
        'created' => '등록',
        'updated' => '수정',
        'deleted' => '삭제',
        'login' => '로그인',
        'logout' => '로그아웃',
        'failed_login' => '로그인 실패',
        'visited' => '열람',
        'password_changed' => '비밀번호 변경',
        'password_reset_link' => '재설정 링크 생성',
        '2fa_enabled' => '2단계 인증 등록',
        '2fa_disabled' => '2단계 인증 해제',
        '2fa_recovery_codes' => '복구 코드 재생성',
    ];

    /**
     * Longest value shown before it is cut. A logged 내용 is a whole
     * announcement body; the log is for spotting that it changed, not
     * for reading it back.
     */
    private const LIMIT = 300;

    /**
     * Container key for the per-request memo of resolved foreign keys
     * and built rows.
     *
     * Both are asked for twice per entry - once to build the state, once
     * to decide whether the entry is hidden - and Filament re-evaluates
     * those closures several times over a modal's lifecycle. Without a
     * memo a member update touching three foreign keys cost eighty-four
     * queries to show six values.
     *
     * It hangs off the container rather than a static property so it
     * empties between requests. A static outlives them, and would carry
     * a 직분 name resolved under id 4 into the next request, where id 4
     * may name something else - which is exactly what happened between
     * two tests sharing a process.
     */
    private const MEMO = 'activity-changes.memo';

    /**
     * What changed on the record, one row per column.
     *
     * @return list<array{field: string, before: ?string, after: ?string}>
     */
    public static function rows(Activity $activity): array
    {
        return self::remember('rows:'.$activity->getKey(), function () use ($activity): array {
            $changes = $activity->attribute_changes?->toArray() ?? [];
            $after = (array) ($changes['attributes'] ?? []);
            $before = (array) ($changes['old'] ?? []);

            /**
             * A deletion has no 이후: the record is gone. What it stores
             * is the final state, and logOnlyDirty() puts that under
             * 'old' - so whichever of the two arrived is what was lost,
             * and it belongs in the 이전 column with nothing beside it.
             */
            if ($activity->event === 'deleted') {
                $before = $before ?: $after;
                $after = [];
            }

            return collect(array_keys($before + $after))
                ->map(fn (string $field): array => [
                    'field' => self::LABELS[$field] ?? $field,
                    'before' => self::value($field, $before[$field] ?? null, $activity->subject_type),
                    'after' => self::value($field, $after[$field] ?? null, $activity->subject_type),
                ])
                ->all();
        });
    }

    /**
     * The request details stored alongside, as one line each.
     *
     * @return list<array{field: string, value: ?string}>
     */
    public static function context(Activity $activity): array
    {
        return self::remember('context:'.$activity->getKey(), fn (): array => collect($activity->properties?->toArray() ?? [])
            ->except(['attributes', 'old'])
            ->map(fn (mixed $value, string $key): array => [
                'field' => self::CONTEXT[$key] ?? self::LABELS[$key] ?? $key,
                'value' => self::value($key, $value),
            ])
            ->values()
            ->all());
    }

    /**
     * What the row is about, named the way the panel names it.
     *
     * 'MembershipRequest #4' is the class the developer wrote, not the
     * thing the church has - and every resource already declares what to
     * call its records, so the label is asked of the panel rather than
     * spelled out a second time here. A page opening has no record; the
     * path it recorded is the whole story.
     */
    public static function subject(Activity $activity): ?string
    {
        if (blank($activity->subject_type)) {
            return $activity->log_name === 'page' ? (string) $activity->description : null;
        }

        return self::modelLabel($activity->subject_type).' #'.$activity->subject_id;
    }

    /**
     * A recorded event in Korean.
     */
    public static function event(?string $event): ?string
    {
        return $event === null ? null : (self::EVENTS[$event] ?? $event);
    }

    /**
     * Whether the stored 내용 says anything the 동작 badge has not.
     *
     * A model event logs its own name as the description, so 내용 read
     * 'updated' directly under a badge already saying 수정.
     */
    public static function hasDescription(Activity $activity): bool
    {
        return filled($activity->description)
            && $activity->description !== $activity->event
            && $activity->log_name !== 'page';
    }

    /**
     * What the panel calls records of the given model.
     */
    private static function modelLabel(string $type): string
    {
        return self::remember('label:'.$type, function () use ($type): string {
            $resource = rescue(fn (): ?string => Filament::getModelResource($type), null, report: false);

            return $resource ? $resource::getModelLabel() : class_basename($type);
        });
    }

    /**
     * One stored value, written the way the panel writes that kind of
     * value. Null for anything empty, so the entry draws its placeholder
     * rather than an empty cell that reads as a missing row.
     */
    private static function value(string $field, mixed $value, ?string $subjectType = null): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '예' : '아니오';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        /**
         * A boolean column comes back from the log as 0 or 1, and
         * 'is_published: 1' is the single most common row here. The
         * value is checked as well as the name, so a future is_* column
         * holding something other than a flag is left alone rather than
         * being announced as 예.
         */
        if (self::isFlag($field) && in_array($value, [0, 1, '0', '1'], true)) {
            return $value ? '예' : '아니오';
        }

        if (isset(self::REFERENCES[$field])) {
            return self::reference($field, $value);
        }

        return self::datetime((string) $value, self::isDateOnly($field, $subjectType))
            ?? self::time($field, (string) $value)
            /** Announcement bodies are stored as editor HTML. */
            ?? Str::limit(self::text((string) $value), self::LIMIT, '…');
    }

    /**
     * Whether the column holds a date without a time, asked of the model
     * the row is about.
     *
     * Reading it off the value instead does not work: published_at is a
     * date on 주보 and a datetime on 교회 소식, and a date serialised
     * before the site moved to Brisbane time sits at UTC midnight rather
     * than local. That made one column render two ways depending on how
     * its value happened to be written. The cast is the only thing that
     * knows, and it is the same answer every time.
     */
    private static function isDateOnly(string $field, ?string $subjectType): bool
    {
        /**
         * subject_type is a stored string, and is_a()'s third argument
         * hands it to the autoloader. Bounding it to this application's
         * own models keeps a value read out of the database from
         * choosing which file gets required - and the log does carry
         * rows naming classes that have since been retired.
         */
        if (! $subjectType || ! str_starts_with($subjectType, 'App\\Models\\') || ! is_a($subjectType, Model::class, true)) {
            return false;
        }

        $casts = self::remember('casts:'.$subjectType, fn (): array => (new $subjectType)->getCasts());
        $cast = $casts[$field] ?? '';

        return $cast === 'date' || str_starts_with($cast, 'date:');
    }

    /**
     * What a foreign key points at, named.
     */
    private static function reference(string $field, mixed $id): string
    {
        [$model, $column] = self::REFERENCES[$field];

        /**
         * 작성자 and 업로더 follow the rule the tables follow: a
         * maintenance account is never named, it is 시스템. An account
         * that has since been deleted is a different answer and keeps
         * its own, because an audit trail saying 삭제됨 is saying
         * something.
         */
        if (in_array($field, ['created_by', 'uploaded_by'], true)) {
            $author = self::remember('author|'.$id, fn (): ?User => User::query()->whereKey($id)->first());

            return match (true) {
                $author === null => '삭제됨 #'.$id,
                $author->is_audit_exempt => Author::SYSTEM,
                default => $author->name.' #'.$id,
            };
        }

        $name = self::remember($model.'|'.$column.'|'.$id, fn (): ?string => $model::query()->whereKey($id)->value($column));

        return ($name ?? '삭제됨').' #'.$id;
    }

    /**
     * A stored timestamp written the way the rest of the panel writes
     * one, or null when the value is not a timestamp at all.
     *
     * Eloquent serialises a date cast into the log as UTC, so the value
     * has to come back to the church's own timezone before it is read -
     * without that a Brisbane 행사일 of 2024-06-24 was stored as
     * 2024-06-23T14:00:00Z and shown back as the twenty-third.
     *
     * Whether the time is shown at all is decided by the column's cast
     * rather than by whether the converted value lands on midnight, so
     * one column always reads the same way.
     */
    private static function datetime(string $value, bool $dateOnly = false): ?string
    {
        if (! preg_match('/^(\d{4}-\d{2}-\d{2})([ T]\d{2}:\d{2}(:\d{2})?)?(\.\d+)?(Z|[+-]\d{2}:?\d{2})?$/', $value, $matches)) {
            return null;
        }

        $parsed = rescue(fn (): Carbon => Carbon::parse($value), null, report: false);

        /**
         * Carbon rolls an impossible date forward rather than refusing
         * it, turning 2026-02-30 into 2026-03-02. An audit log may not
         * quietly rewrite what it stored, so the round trip is checked
         * and anything that does not survive it is left as it was found.
         */
        if (! $parsed || $parsed->format('Y-m-d') !== $matches[1]) {
            return null;
        }

        $local = $parsed->setTimezone(config('app.timezone'));

        return $local->format($dateOnly || ($matches[2] ?? '') === ''
            ? AppServiceProvider::DATE_FORMAT
            : AppServiceProvider::DATE_TIME_FORMAT);
    }

    /**
     * A bare time column - 행사 시각, 종료 시각 - written the way the
     * panel writes one, or null when the value is not a real time on a
     * column that holds one.
     *
     * Both halves of that matter. The column has to be a time column,
     * or a 제목 of '19:30:00' would be shortened along with them; and
     * the hours and minutes are range-checked, so '99:99:99' is shown
     * as it was stored rather than quietly becoming '99:99'. An audit
     * log may not rewrite its own evidence, which is the same rule the
     * date path already follows.
     */
    private static function time(string $field, string $value): ?string
    {
        if (! str_ends_with($field, '_time')) {
            return null;
        }

        return preg_match('/^([01]\d|2[0-3]):([0-5]\d):[0-5]\d$/', $value, $matches) === 1
            ? $matches[1].':'.$matches[2]
            : null;
    }

    /**
     * Editor HTML as the words it holds.
     *
     * Block tags become a space before anything is stripped, so two
     * paragraphs do not run into one another, and the entities the
     * editor writes are decoded rather than shown as &nbsp;.
     */
    private static function text(string $value): string
    {
        $spaced = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6]|\/tr|\/td)\b[^>]*>/i', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5)));
    }

    /**
     * Whether a column name marks a yes/no flag.
     */
    private static function isFlag(string $field): bool
    {
        return str_starts_with($field, 'is_') || $field === 'featured_in_slider';
    }

    /**
     * Run the callback once per key for the life of the request.
     *
     * offsetExists rather than ??=, so a foreign key whose target has
     * been deleted - which resolves to null - is remembered as
     * looked-up rather than being asked for again on every pass.
     */
    private static function remember(string $key, callable $callback): mixed
    {
        if (! app()->bound(self::MEMO)) {
            app()->instance(self::MEMO, new ArrayObject);
        }

        $memo = app(self::MEMO);

        if (! $memo->offsetExists($key)) {
            $memo[$key] = $callback();
        }

        return $memo[$key];
    }
}
