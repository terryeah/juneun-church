<?php

/**
 * Copy for turning the authenticator app off, in the Toss style.
 *
 * The card itself now shows the state as the loudest thing on it and
 * keeps this action muted, so the warning that used to be carried by
 * red text and an open padlock is carried by these words instead: the
 * heading asks, and the description says plainly what is lost.
 *
 * Only the changed keys are listed. Laravel merges a vendor override
 * over the package file recursively, so everything left out here still
 * comes from filament/filament's own ko translations.
 */
return [

    'label' => '2단계 인증 끄기',

    'modal' => [

        'heading' => '2단계 인증을 끌까요?',

        'description' => '끄면 비밀번호만으로 로그인할 수 있어요. 비밀번호가 새어 나가면 계정을 그대로 내주게 돼요.',

        'form' => [

            'code' => [

                'label' => '인증 앱에 표시된 6자리 코드를 입력해 주세요',

                'actions' => [

                    'use_recovery_code' => [
                        'label' => '복구 코드로 인증하기',
                    ],

                ],

                'messages' => [

                    'invalid' => '코드가 맞지 않아요. 다시 확인해 주세요.',

                ],

            ],

            'recovery_code' => [

                'label' => '또는 복구 코드를 입력해 주세요',

                'messages' => [

                    'invalid' => '복구 코드가 맞지 않아요. 다시 확인해 주세요.',

                ],

            ],

        ],

        'actions' => [

            'submit' => [
                'label' => '2단계 인증 끄기',
            ],

        ],

    ],

    'notifications' => [

        'disabled' => [
            'title' => '2단계 인증을 껐어요',
        ],

    ],

];
