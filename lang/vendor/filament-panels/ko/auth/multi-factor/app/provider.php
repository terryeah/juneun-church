<?php

/**
 * Authenticator-app labels in the Toss style.
 */
return [

    'management_schema' => [

        'actions' => [

            'label' => '인증 앱',

            'below_content' => '로그인할 때 인증 앱의 6자리 코드를 한 번 더 확인해요.',

            'messages' => [
                'enabled' => '사용 중',
                'disabled' => '사용 안 함',
            ],

        ],

    ],

    'login_form' => [

        'label' => '2단계 인증',

        'code' => [

            'label' => '인증 앱에 표시된 6자리 코드를 입력해 주세요',

            'validation_attribute' => '인증 코드',

            'actions' => [

                'use_recovery_code' => [
                    'label' => '복구 코드로 인증하기',
                ],

            ],

        ],

    ],

];
