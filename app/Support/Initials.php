<?php

namespace App\Support;

/**
 * The characters shown in an avatar circle when there is no photo.
 *
 * Filament's own rule - the first character of every space-separated
 * segment - reduces a Korean name to the family name alone, because a
 * Korean name has no spaces: 양민규 becomes 양, and nobody in Korea is
 * addressed by their 성 on its own. So a name written in Hangul keeps
 * its given name instead, and everything else keeps Filament's rule.
 */
class Initials
{
    /**
     * The two-syllable Korean family names, which need two characters
     * dropped rather than one. Every other 성 is a single syllable.
     *
     * ponytail: this is the census list of compound surnames and not
     * the complete historical set; a name carrying an unlisted one
     * simply keeps a syllable too many. Adding it here is the fix.
     *
     * @var list<string>
     */
    public const COMPOUND_SURNAMES = [
        '남궁', '황보', '제갈', '사공', '선우', '서문',
        '독고', '동방', '망절', '어금', '강전', '소봉',
    ];

    /**
     * The initials for a display name.
     *
     * A name written entirely in Hangul drops the family name - the
     * leading syllable, or two for a compound surname - and keeps the
     * given name, so 양민규 gives 민규. A two-syllable name is kept
     * whole, since dropping the 성 would leave a single character that
     * reads as no name at all: 한별 gives 한별.
     *
     * Anything else takes one character per space-separated segment,
     * so Terry Yang gives TY.
     */
    public static function for(string $name): string
    {
        $name = trim($name);

        if (preg_match('/^\p{Hangul}+$/u', $name) !== 1) {
            return collect(preg_split('/\s+/u', $name, flags: PREG_SPLIT_NO_EMPTY) ?: [])
                ->map(fn (string $segment): string => mb_substr($segment, 0, 1))
                ->implode('');
        }

        $length = mb_strlen($name);

        if ($length >= 4 && in_array(mb_substr($name, 0, 2), self::COMPOUND_SURNAMES, true)) {
            return mb_substr($name, 2);
        }

        return $length >= 3 ? mb_substr($name, 1) : $name;
    }
}
