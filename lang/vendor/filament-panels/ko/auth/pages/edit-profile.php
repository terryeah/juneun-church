<?php

/**
 * Relabels the profile 2FA section in the Toss style: the standard
 * Korean term 2단계 인증 instead of the stiff 이중 인증 (2FA), and the
 * bare 저장 rather than a literal 변경 사항 저장 - the button sits at the
 * end of the page it saves, so naming its object again adds nothing.
 */
return [

    'form' => [

        'actions' => [

            'save' => [
                'label' => '저장',
            ],

        ],

    ],

    'multi_factor_authentication' => [
        'label' => '2단계 인증',
    ],

];
