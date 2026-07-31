<?php

/**
 * Overrides the default "만들기" wording on create pages: the primary
 * action reads 저장, which suits registering church content better.
 * The photo pages override their own actions with 업로드 in code.
 */
return [

    'title' => '새로운 :label',

    'breadcrumb' => '새로운 :label',

    'form' => [

        'actions' => [

            'cancel' => [
                'label' => '취소',
            ],

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

];
