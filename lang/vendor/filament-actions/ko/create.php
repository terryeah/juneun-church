<?php

/**
 * Overrides the default "만들기" wording: every create button reads
 * "새로운 {아이템}" so the action names what it makes. Pages that
 * override the label in code (사진 업로드 등) keep their own wording.
 */
return [

    'single' => [

        'label' => '새로운 :label',

        'modal' => [

            'heading' => '새로운 :label',

            'actions' => [

                'create' => [
                    'label' => '저장',
                ],

                'create_another' => [
                    'label' => '저장 후 계속',
                ],

            ],

        ],

        'notifications' => [

            'created' => [
                'title' => '생성 완료',
            ],

        ],

    ],

];
