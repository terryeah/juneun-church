<?php

namespace App\Support;

/**
 * Korean display labels for the internal role keys.
 *
 * A role says what somebody may run, never who they are: 성도 is not
 * here, because that is answered by the 교적 record an account is
 * linked to rather than by anything granted. 일반회원 is the account
 * that runs nothing at all, whether or not it belongs to a 성도.
 */
class RoleLabel
{
    /**
     * @var array<string, string>
     */
    public const LABELS = [
        'super_admin' => '최고 관리자',
        'admin' => '관리자',
        'content_editor' => '편집자',
        'finance_officer' => '재정부',
        'developer' => '개발자',
        'general_member' => '일반회원',
    ];

    /**
     * The label for a role key, falling back to the key itself.
     */
    public static function label(string $name): string
    {
        return self::LABELS[$name] ?? $name;
    }
}
