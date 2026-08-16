<?php

/**
 * Validation messages the church writes itself.
 *
 * Laravel is left in English everywhere else on purpose: each form
 * names its own messages beside its own rules, which keeps the wording
 * next to the thing it is about. What cannot be said there is a message
 * belonging to a rule the form never spells out - Password::defaults()
 * is registered once for the whole site, and both the sign-up form and
 * the panel's own password screens inherit it.
 *
 * Anything not named here falls through to English, which is what the
 * site did before this file existed.
 */
return [

    'password' => [
        'uncompromised' => '다른 사이트에서 유출된 적이 있는 비밀번호입니다. 다른 비밀번호로 정해주세요.',
    ],

];
