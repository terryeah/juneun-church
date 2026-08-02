<?php

namespace App\Support;

/**
 * Korean display labels for the internal role keys.
 */
class RoleLabel
{
    /**
     * @var array<string, string>
     */
    public const LABELS = [
        'super_admin' => '최고 관리자',
        'admin' => '관리자',
        'content_editor' => '콘텐츠 편집자',
        'contributor' => '기고자',
        'media_coordinator' => '미디어 담당',
        'developer' => '개발자',
    ];

    /**
     * The label for a role key, falling back to the key itself.
     */
    public static function label(string $name): string
    {
        return self::LABELS[$name] ?? $name;
    }
}
