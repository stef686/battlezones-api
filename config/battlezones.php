<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Message Edit Window (minutes)
    |--------------------------------------------------------------------------
    */
    'message_edit_window' => 15,

    /*
    |--------------------------------------------------------------------------
    | Password Change Token Expiry (hours)
    |--------------------------------------------------------------------------
    */
    'password_change_token_expiry' => 24,

    /*
    |--------------------------------------------------------------------------
    | Event Invite Lifetime (days after the event ends)
    |--------------------------------------------------------------------------
    |
    | An Invite is meant to expire, pushing its holder into claiming a real
    | account. It runs from the end of the event rather than the registration
    | deadline so a Captain who registers late still gets in on the day.
    |
    */
    'invite_expiry_days_after_event' => 2,

    /*
    |--------------------------------------------------------------------------
    | Search Result Limit
    |--------------------------------------------------------------------------
    */
    'search_result_limit' => 10,

    /*
    |--------------------------------------------------------------------------
    | Group Conversation Member Limit
    |--------------------------------------------------------------------------
    */
    'group_member_limit' => 10,

];
