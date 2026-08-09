<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

/**
 * The 내 프로필 page, with the 2단계 인증 card lifted inside the form.
 *
 * Filament renders the profile as a form whose footer holds 저장 and
 * 취소, then hangs the two-factor card off the content schema as a
 * sibling underneath. That strands the buttons halfway down the page,
 * with a whole card of unrelated controls below them. Nesting the card
 * in the form's own schema instead puts it between the fields and the
 * footer, so the page reads 이름/이메일 -> 2단계 인증 -> 저장/취소 and the
 * buttons keep submitting the form they already belonged to.
 *
 * The card is only moved, never rebuilt: it is the same component the
 * parent returns, so its schema path - and therefore the #content\.app
 * id every two-factor CSS rule in AdminPanelProvider is anchored on -
 * survives the move. The Form component carries no key of its own and
 * its child schema inherits the content schema's, so the card's
 * absolute key stays 'content' + 'app' either way.
 */
class EditProfile extends BaseEditProfile
{
    /**
     * The page body: one form holding the profile fields, then the
     * two-factor card, then the form actions in the footer.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent()
                    ->schema([
                        EmbeddedSchema::make('form'),
                        ...Arr::wrap($this->getMultiFactorAuthenticationContentComponent()),
                    ]),
            ]);
    }
}
