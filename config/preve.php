<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Inactivity Threshold
    |--------------------------------------------------------------------------
    |
    | Days without a manually created transaction (recurring-generated ones
    | don't count) before a user is considered inactive for re-engagement
    | purposes (banner and reminder email).
    |
    */

    'inactivity_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Banner Dismissal Window
    |--------------------------------------------------------------------------
    |
    | How many days the re-engagement banner stays hidden after the user
    | dismisses it. It reappears if the user is still inactive afterwards.
    |
    */

    'banner_dismiss_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Re-engagement Email Cooldown
    |--------------------------------------------------------------------------
    |
    | Minimum days between two re-engagement emails to the same user, so the
    | weekly command never turns into weekly spam.
    |
    */

    'reengagement_email_cooldown_days' => 30,

];
