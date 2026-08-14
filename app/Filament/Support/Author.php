<?php

namespace App\Filament\Support;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * How the panel names whoever stamped a record.
 *
 * A maintenance account is not a person the church knows - it belongs
 * to whoever keeps the site running, and what it stamps is housekeeping:
 * an import, a backfill, a fix made on somebody's behalf. The activity
 * log already refuses to name those accounts, so a 작성자 column naming
 * one told two different stories about the same account on two screens.
 *
 * It reads 시스템 in grey instead, which is what a row with nobody
 * behind it has always read here.
 */
final class Author
{
    /**
     * What an unattributed row is called.
     */
    public const SYSTEM = '시스템';

    /**
     * The 작성자 / 업로더 column, for a relationship such as author.name.
     */
    public static function column(string $name, string $label): TextColumn
    {
        $relationship = str($name)->before('.')->toString();

        return TextColumn::make($name)
            ->label($label)
            ->state(fn (Model $record): string => self::name($record->{$relationship}))
            ->color(fn (string $state): ?string => $state === self::SYSTEM ? 'gray' : null)
            ->sortable();
    }

    /**
     * The name to show for a stamping account, or 시스템 where there is
     * no-one to name.
     */
    public static function name(?Model $author): string
    {
        return $author instanceof User && ! $author->is_audit_exempt
            ? $author->name
            : self::SYSTEM;
    }
}
