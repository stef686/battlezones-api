<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Battlezones API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .php-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "https://battlezones.test";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;php&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authentication" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authentication">
                    <a href="#authentication">Authentication</a>
                </li>
                                    <ul id="tocify-subheader-authentication" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="authentication-POSTapi-login-token">
                                <a href="#authentication-POSTapi-login-token">Login</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-register">
                                <a href="#authentication-POSTapi-register">Register</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-auth-refresh">
                                <a href="#authentication-POSTapi-auth-refresh">Refresh Token</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-auth-resend-verification">
                                <a href="#authentication-POSTapi-auth-resend-verification">Resend verification email</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-auth-forgot-password">
                                <a href="#authentication-POSTapi-auth-forgot-password">Forgot Password</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="authentication-POSTapi-auth-reset-password">
                                <a href="#authentication-POSTapi-auth-reset-password">Reset Password</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-conversations" class="tocify-header">
                <li class="tocify-item level-1" data-unique="conversations">
                    <a href="#conversations">Conversations</a>
                </li>
                                    <ul id="tocify-subheader-conversations" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="conversations-GETapi-conversations">
                                <a href="#conversations-GETapi-conversations">List Conversations</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations">
                                <a href="#conversations-POSTapi-conversations">Start Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-GETapi-conversations--id-">
                                <a href="#conversations-GETapi-conversations--id-">Show Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--members">
                                <a href="#conversations-POSTapi-conversations--conversation_id--members">Add Group Members</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-DELETEapi-conversations--conversation_id--members--user_id-">
                                <a href="#conversations-DELETEapi-conversations--conversation_id--members--user_id-">Remove Group Member</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--leave">
                                <a href="#conversations-POSTapi-conversations--conversation_id--leave">Leave Group Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-PATCHapi-conversations--conversation_id--name">
                                <a href="#conversations-PATCHapi-conversations--conversation_id--name">Update Group Name</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-DELETEapi-conversations--id-">
                                <a href="#conversations-DELETEapi-conversations--id-">Delete Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--archive">
                                <a href="#conversations-POSTapi-conversations--conversation_id--archive">Archive Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--unarchive">
                                <a href="#conversations-POSTapi-conversations--conversation_id--unarchive">Unarchive Conversation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--read">
                                <a href="#conversations-POSTapi-conversations--conversation_id--read">Mark Conversation as Read</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-POSTapi-conversations--conversation_id--messages">
                                <a href="#conversations-POSTapi-conversations--conversation_id--messages">Send Message</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-PATCHapi-conversations--conversation_id--messages--id-">
                                <a href="#conversations-PATCHapi-conversations--conversation_id--messages--id-">Update Message</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="conversations-DELETEapi-conversations--conversation_id--messages--id-">
                                <a href="#conversations-DELETEapi-conversations--conversation_id--messages--id-">Delete Message</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-events" class="tocify-header">
                <li class="tocify-item level-1" data-unique="events">
                    <a href="#events">Events</a>
                </li>
                                    <ul id="tocify-subheader-events" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="events-GETapi-events">
                                <a href="#events-GETapi-events">List Events</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--slug-">
                                <a href="#events-GETapi-events--slug-">Show Event</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--updates">
                                <a href="#events-GETapi-events--event_slug--updates">List Event Updates</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--attendees">
                                <a href="#events-GETapi-events--event_slug--attendees">List Event Attendees</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--factions">
                                <a href="#events-GETapi-events--event_slug--factions">List the Factions on offer</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--attendees--id-">
                                <a href="#events-GETapi-events--event_slug--attendees--id-">Show Event Attendee</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--rounds">
                                <a href="#events-GETapi-events--event_slug--rounds">List Event Rounds</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--rounds--id-">
                                <a href="#events-GETapi-events--event_slug--rounds--id-">Show Event Round</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--games--id-">
                                <a href="#events-GETapi-events--event_slug--games--id-">Show Event Game</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--standings">
                                <a href="#events-GETapi-events--event_slug--standings">List Event Standings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--pulse">
                                <a href="#events-GETapi-events--event_slug--pulse">Event Pulse</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--gallery">
                                <a href="#events-GETapi-events--event_slug--gallery">List Event Gallery</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--schedule">
                                <a href="#events-GETapi-events--event_slug--schedule">List the Event Schedule</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-feedback--token-">
                                <a href="#events-GETapi-feedback--token-">Show the Feedback Form</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-feedback--token-">
                                <a href="#events-POSTapi-feedback--token-">Submit Feedback</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-invites--token-">
                                <a href="#events-GETapi-invites--token-">Open an Invitation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-invites--token--session">
                                <a href="#events-POSTapi-invites--token--session">Enter with an Invitation</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-invites--token--claim">
                                <a href="#events-POSTapi-invites--token--claim">Claim an Invited Account</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--organisers">
                                <a href="#events-GETapi-events--event_slug--organisers">List Event Organisers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--organisers">
                                <a href="#events-POSTapi-events--event_slug--organisers">Appoint an Organiser</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-DELETEapi-events--event_slug--organisers--user_id-">
                                <a href="#events-DELETEapi-events--event_slug--organisers--user_id-">Remove an Organiser</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--invites">
                                <a href="#events-POSTapi-events--event_slug--invites">Invite a Captain</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--feedback-requests">
                                <a href="#events-POSTapi-events--event_slug--feedback-requests">Send Feedback Requests</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--feedback">
                                <a href="#events-GETapi-events--event_slug--feedback">Read Feedback</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--feedback-export">
                                <a href="#events-GETapi-events--event_slug--feedback-export">Export Feedback</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--polls">
                                <a href="#events-GETapi-events--event_slug--polls">List Polls</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--polls">
                                <a href="#events-POSTapi-events--event_slug--polls">Create a Poll</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--polls--poll_id--open">
                                <a href="#events-POSTapi-events--event_slug--polls--poll_id--open">Open a Poll</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--polls--poll_id--candidates">
                                <a href="#events-GETapi-events--event_slug--polls--poll_id--candidates">List Poll Candidates</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--polls--poll_id--results">
                                <a href="#events-GETapi-events--event_slug--polls--poll_id--results">Read Poll Tallies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--polls--poll_id--close">
                                <a href="#events-POSTapi-events--event_slug--polls--poll_id--close">Close a Poll</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PUTapi-events--event_slug--polls--poll_id--ballot">
                                <a href="#events-PUTapi-events--event_slug--polls--poll_id--ballot">Replace your Ballot</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PATCHapi-events--event_slug--attendees--attendee_id--painting">
                                <a href="#events-PATCHapi-events--event_slug--attendees--attendee_id--painting">Mark a Painting Entry</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--schedule">
                                <a href="#events-POSTapi-events--event_slug--schedule">Add a Schedule Block</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--schedule-reorder">
                                <a href="#events-POSTapi-events--event_slug--schedule-reorder">Reorder Schedule Blocks</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PATCHapi-events--event_slug--schedule--block_id-">
                                <a href="#events-PATCHapi-events--event_slug--schedule--block_id-">Edit a Schedule Block</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-DELETEapi-events--event_slug--schedule--block_id-">
                                <a href="#events-DELETEapi-events--event_slug--schedule--block_id-">Delete a Schedule Block</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--my-game">
                                <a href="#events-GETapi-events--event_slug--my-game">Show My Current Game</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PATCHapi-events--event_slug--my-faction">
                                <a href="#events-PATCHapi-events--event_slug--my-faction">Record My Faction</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--games--game_id--result">
                                <a href="#events-POSTapi-events--event_slug--games--game_id--result">Submit a Game Result</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PUTapi-events--event_slug--games--game_id--result">
                                <a href="#events-PUTapi-events--event_slug--games--game_id--result">Correct a Game Result</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--games--game_id--flag">
                                <a href="#events-POSTapi-events--event_slug--games--game_id--flag">Flag a Game Result</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--games--game_id--flag-resolve">
                                <a href="#events-POSTapi-events--event_slug--games--game_id--flag-resolve">Resolve a Flagged Result</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--flags">
                                <a href="#events-GETapi-events--event_slug--flags">List Flagged Results</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--rounds">
                                <a href="#events-POSTapi-events--event_slug--rounds">Generate the next Round</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--rounds--round_id--publish">
                                <a href="#events-POSTapi-events--event_slug--rounds--round_id--publish">Publish a Round</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--rounds--round_id--swap">
                                <a href="#events-POSTapi-events--event_slug--rounds--round_id--swap">Swap two Pairings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-DELETEapi-events--event_slug--rounds--round_id--publish">
                                <a href="#events-DELETEapi-events--event_slug--rounds--round_id--publish">Unpublish a Round</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PUTapi-events--event_slug--army-list">
                                <a href="#events-PUTapi-events--event_slug--army-list">Submit Your Army List</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">
                                <a href="#events-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">Reveal a Team's Army Lists</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">
                                <a href="#events-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">Unlock an Army List</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--attendees">
                                <a href="#events-POSTapi-events--event_slug--attendees">Register a Team</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-PATCHapi-events--event_slug--attendees--id-">
                                <a href="#events-PATCHapi-events--event_slug--attendees--id-">Amend a Team</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-POSTapi-events--event_slug--attendees--attendee_id--members">
                                <a href="#events-POSTapi-events--event_slug--attendees--attendee_id--members">Add a Player to a Team</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="events-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">
                                <a href="#events-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">Remove a Player from a Team</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-gallery" class="tocify-header">
                <li class="tocify-item level-1" data-unique="gallery">
                    <a href="#gallery">Gallery</a>
                </li>
                                    <ul id="tocify-subheader-gallery" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="gallery-GETapi-gallery">
                                <a href="#gallery-GETapi-gallery">List Photos</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="gallery-POSTapi-gallery">
                                <a href="#gallery-POSTapi-gallery">Store Photo</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="gallery-GETapi-gallery--photo_id-">
                                <a href="#gallery-GETapi-gallery--photo_id-">Show Photo</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="gallery-PATCHapi-gallery--photo_id-">
                                <a href="#gallery-PATCHapi-gallery--photo_id-">Update Photo</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="gallery-DELETEapi-gallery--photo_id-">
                                <a href="#gallery-DELETEapi-gallery--photo_id-">Delete Photo</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="gallery-GETapi-users--user_id--gallery">
                                <a href="#gallery-GETapi-users--user_id--gallery">User Gallery</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-reactions" class="tocify-header">
                <li class="tocify-item level-1" data-unique="reactions">
                    <a href="#reactions">Reactions</a>
                </li>
                                    <ul id="tocify-subheader-reactions" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="reactions-POSTapi-gallery--photo_id--react">
                                <a href="#reactions-POSTapi-gallery--photo_id--react">Toggle Reaction</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-users" class="tocify-header">
                <li class="tocify-item level-1" data-unique="users">
                    <a href="#users">Users</a>
                </li>
                                    <ul id="tocify-subheader-users" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="users-GETapi-profile">
                                <a href="#users-GETapi-profile">Current User Profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-PATCHapi-profile">
                                <a href="#users-PATCHapi-profile">Update Profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-profile-email">
                                <a href="#users-POSTapi-profile-email">Change Email</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-profile-password">
                                <a href="#users-POSTapi-profile-password">Change Password</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-profile--user_id-">
                                <a href="#users-GETapi-profile--user_id-">User Profile</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-notifications">
                                <a href="#users-GETapi-notifications">List Notifications</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-notifications-read">
                                <a href="#users-POSTapi-notifications-read">Mark All Notifications Read</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-notifications--notification--read">
                                <a href="#users-POSTapi-notifications--notification--read">Mark a Notification Read</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-notification-settings">
                                <a href="#users-GETapi-notification-settings">Get Notification Settings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-PATCHapi-notification-settings">
                                <a href="#users-PATCHapi-notification-settings">Update Notification Settings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-privacy-settings">
                                <a href="#users-GETapi-privacy-settings">Get Privacy Settings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-PATCHapi-privacy-settings">
                                <a href="#users-PATCHapi-privacy-settings">Update Privacy Settings</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-users-search">
                                <a href="#users-GETapi-users-search">Search Users</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-users--user_id--follow">
                                <a href="#users-POSTapi-users--user_id--follow">Follow User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-DELETEapi-users--user_id--follow">
                                <a href="#users-DELETEapi-users--user_id--follow">Unfollow User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-users--user_id--followers">
                                <a href="#users-GETapi-users--user_id--followers">List Followers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-users--user_id--following">
                                <a href="#users-GETapi-users--user_id--following">List Following</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-POSTapi-users--user_id--block">
                                <a href="#users-POSTapi-users--user_id--block">Block User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-DELETEapi-users--user_id--block">
                                <a href="#users-DELETEapi-users--user_id--block">Unblock User</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="users-GETapi-blocked-users">
                                <a href="#users-GETapi-blocked-users">List Blocked Users</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: August 24, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>https://battlezones.test</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="authentication">Authentication</h1>

    <p>APIs for authentication</p>

                                <h2 id="authentication-POSTapi-login-token">Login</h2>

<p>
</p>

<p>Returns an authorization token for the user</p>

<span id="example-requests-POSTapi-login-token">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/login/token';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'player@example.com',
            'password' =&gt; 'password',
            'device_name' =&gt; 'iPhone',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/login/token"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "player@example.com",
    "password": "password",
    "device_name": "iPhone"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-login-token">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;{AUTH_TOKEN}&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;email&quot;: [
        &quot;The provided credentials are incorrect.&quot;
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-login-token" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-login-token"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-login-token"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-login-token" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-login-token">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-login-token" data-method="POST"
      data-path="api/login/token"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-login-token', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-login-token"
                    onclick="tryItOut('POSTapi-login-token');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-login-token"
                    onclick="cancelTryOut('POSTapi-login-token');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-login-token"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/login/token</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-login-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-login-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-login-token"
               value="player@example.com"
               data-component="body">
    <br>
<p>The user's email address. Must be a valid email address. Example: <code>player@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-login-token"
               value="password"
               data-component="body">
    <br>
<p>The user's password. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-login-token"
               value="iPhone"
               data-component="body">
    <br>
<p>The name of the device logging in. Example: <code>iPhone</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-register">Register</h2>

<p>
</p>

<p>Registers a new user</p>

<span id="example-requests-POSTapi-register">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/register';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'John Doe',
            'email' =&gt; 'player@example.com',
            'password' =&gt; 'password',
            'device_name' =&gt; 'iPhone',
            'password_confirmation' =&gt; 'password',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "John Doe",
    "email": "player@example.com",
    "password": "password",
    "device_name": "iPhone",
    "password_confirmation": "password"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-register">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;{AUTH_TOKEN}&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-register" data-method="POST"
      data-path="api/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-register"
                    onclick="tryItOut('POSTapi-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-register"
                    onclick="cancelTryOut('POSTapi-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-register"
               value="John Doe"
               data-component="body">
    <br>
<p>The user's display name. Example: <code>John Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-register"
               value="player@example.com"
               data-component="body">
    <br>
<p>The user's email address. Must be a valid email address. Example: <code>player@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-register"
               value="password"
               data-component="body">
    <br>
<p>The new user's desired password. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-register"
               value="iPhone"
               data-component="body">
    <br>
<p>The name of the device logging in. Example: <code>iPhone</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-register"
               value="password"
               data-component="body">
    <br>
<p>Confirmation of the password field. Example: <code>password</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-auth-refresh">Refresh Token</h2>

<p>
</p>

<p>Exchange a current or recently-expired token for a new one</p>

<span id="example-requests-POSTapi-auth-refresh">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/auth/refresh';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/auth/refresh"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-refresh">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;{AUTH_TOKEN}&quot;,
    &quot;expires_at&quot;: &quot;2026-05-04T00:00:00Z&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-auth-refresh" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-auth-refresh"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-refresh"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-refresh" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-refresh">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-auth-refresh" data-method="POST"
      data-path="api/auth/refresh"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-refresh', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-refresh"
                    onclick="tryItOut('POSTapi-auth-refresh');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-refresh"
                    onclick="cancelTryOut('POSTapi-auth-refresh');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-refresh"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/refresh</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-refresh"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-refresh"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="authentication-POSTapi-auth-resend-verification">Resend verification email</h2>

<p>
</p>

<p>Resend verification email</p>

<span id="example-requests-POSTapi-auth-resend-verification">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/auth/resend-verification';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'test@test.com',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/auth/resend-verification"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "test@test.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-resend-verification">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Verification link resent!&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (400):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Email already verified.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User not found.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-auth-resend-verification" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-auth-resend-verification"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-resend-verification"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-resend-verification" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-resend-verification">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-auth-resend-verification" data-method="POST"
      data-path="api/auth/resend-verification"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-resend-verification', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-resend-verification"
                    onclick="tryItOut('POSTapi-auth-resend-verification');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-resend-verification"
                    onclick="cancelTryOut('POSTapi-auth-resend-verification');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-resend-verification"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/resend-verification</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-resend-verification"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-resend-verification"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-auth-resend-verification"
               value="test@test.com"
               data-component="body">
    <br>
<p>The new user's email address to send the verification email to. Example: <code>test@test.com</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-auth-forgot-password">Forgot Password</h2>

<p>
</p>

<p>Sends a password reset link to the given email address</p>

<span id="example-requests-POSTapi-auth-forgot-password">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/auth/forgot-password';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'user@example.com',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/auth/forgot-password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "user@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-forgot-password">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;If a user with that email address exists, we have sent a password reset link. Please check your email.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-auth-forgot-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-auth-forgot-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-forgot-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-forgot-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-forgot-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-auth-forgot-password" data-method="POST"
      data-path="api/auth/forgot-password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-forgot-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-forgot-password"
                    onclick="tryItOut('POSTapi-auth-forgot-password');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-forgot-password"
                    onclick="cancelTryOut('POSTapi-auth-forgot-password');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-forgot-password"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/forgot-password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-forgot-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-forgot-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-auth-forgot-password"
               value="user@example.com"
               data-component="body">
    <br>
<p>The email address associated with the account. Example: <code>user@example.com</code></p>
        </div>
        </form>

                    <h2 id="authentication-POSTapi-auth-reset-password">Reset Password</h2>

<p>
</p>

<p>Resets the user's password using a valid reset token</p>

<span id="example-requests-POSTapi-auth-reset-password">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/auth/reset-password';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'token' =&gt; 'architecto',
            'email' =&gt; 'user@example.com',
            'password' =&gt; 'new-password',
            'password_confirmation' =&gt; 'new-password',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/auth/reset-password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "token": "architecto",
    "email": "user@example.com",
    "password": "new-password",
    "password_confirmation": "new-password"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-reset-password">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Your password has been reset. You may now log in with your new password.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;This password reset token is invalid or has expired.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-auth-reset-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-auth-reset-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-reset-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-reset-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-reset-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-auth-reset-password" data-method="POST"
      data-path="api/auth/reset-password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-reset-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-reset-password"
                    onclick="tryItOut('POSTapi-auth-reset-password');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-reset-password"
                    onclick="cancelTryOut('POSTapi-auth-reset-password');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-reset-password"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/reset-password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-reset-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-reset-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-auth-reset-password"
               value="architecto"
               data-component="body">
    <br>
<p>The password reset token from the reset email. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-auth-reset-password"
               value="user@example.com"
               data-component="body">
    <br>
<p>The email address associated with the account. Example: <code>user@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-auth-reset-password"
               value="new-password"
               data-component="body">
    <br>
<p>The new password (minimum 8 characters). Example: <code>new-password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-auth-reset-password"
               value="new-password"
               data-component="body">
    <br>
<p>Confirmation of the new password. Example: <code>new-password</code></p>
        </div>
        </form>

                <h1 id="conversations">Conversations</h1>

    <p>APIs for Conversations</p>

                                <h2 id="conversations-GETapi-conversations">List Conversations</h2>

<p>
</p>

<p>List the authenticated user's conversations.</p>

<span id="example-requests-GETapi-conversations">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'tab' =&gt; 'primary',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations"
);

const params = {
    "tab": "primary",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-conversations">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 359,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-conversations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-conversations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-conversations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-conversations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-conversations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-conversations" data-method="GET"
      data-path="api/conversations"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-conversations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-conversations"
                    onclick="tryItOut('GETapi-conversations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-conversations"
                    onclick="cancelTryOut('GETapi-conversations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-conversations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/conversations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>tab</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="tab"                data-endpoint="GETapi-conversations"
               value="primary"
               data-component="query">
    <br>
<p>Filter by tab. One of: primary, events, requests, archived. Example: <code>primary</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>primary</code></li> <li><code>events</code></li> <li><code>requests</code></li> <li><code>archived</code></li></ul>
            </div>
                </form>

                    <h2 id="conversations-POSTapi-conversations">Start Conversation</h2>

<p>
</p>

<p>Start a new direct or group conversation.</p>

<span id="example-requests-POSTapi-conversations">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'recipient_ids' =&gt; [16],
            'name' =&gt; 'Game Night Chat',
            'body' =&gt; 'Hey, want to play this weekend?',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "recipient_ids": [
        16
    ],
    "name": "Game Night Chat",
    "body": "Hey, want to play this weekend?"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 360,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations" data-method="POST"
      data-path="api/conversations"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations"
                    onclick="tryItOut('POSTapi-conversations');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations"
                    onclick="cancelTryOut('POSTapi-conversations');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>recipient_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="recipient_ids[0]"                data-endpoint="POSTapi-conversations"
               data-component="body">
        <input type="number" style="display: none"
               name="recipient_ids[1]"                data-endpoint="POSTapi-conversations"
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-conversations"
               value="Game Night Chat"
               data-component="body">
    <br>
<p>The group name (required when starting a group conversation with multiple recipients). Must not be greater than 255 characters. Example: <code>Game Night Chat</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body"                data-endpoint="POSTapi-conversations"
               value="Hey, want to play this weekend?"
               data-component="body">
    <br>
<p>The initial message body. Must not be greater than 1000 characters. Example: <code>Hey, want to play this weekend?</code></p>
        </div>
        </form>

                    <h2 id="conversations-GETapi-conversations--id-">Show Conversation</h2>

<p>
</p>

<p>Get the messages in a conversation.</p>

<span id="example-requests-GETapi-conversations--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-conversations--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 154,
        &quot;conversation_id&quot;: 361,
        &quot;user_id&quot;: 1418,
        &quot;body&quot;: &quot;Consectetur expedita ratione minus facere aut vel quia velit. Fuga nihil facere quas a fugit consequatur quibusdam. Qui odit asperiores rem dolores. Tempore architecto quia labore omnis veniam.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-conversations--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-conversations--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-conversations--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-conversations--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-conversations--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-conversations--id-" data-method="GET"
      data-path="api/conversations/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-conversations--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-conversations--id-"
                    onclick="tryItOut('GETapi-conversations--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-conversations--id-"
                    onclick="cancelTryOut('GETapi-conversations--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-conversations--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/conversations/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-conversations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-conversations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-conversations--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--members">Add Group Members</h2>

<p>
</p>

<p>Add new members to a group conversation.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--members">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/members';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'recipient_ids' =&gt; [16],
            'include_history' =&gt; false,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/members"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "recipient_ids": [
        16
    ],
    "include_history": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--members">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 362,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--members" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--members"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--members"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--members" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--members">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--members" data-method="POST"
      data-path="api/conversations/{conversation_id}/members"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--members', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--members"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--members');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--members"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--members');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--members"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/members</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--members"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>recipient_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="recipient_ids[0]"                data-endpoint="POSTapi-conversations--conversation_id--members"
               data-component="body">
        <input type="number" style="display: none"
               name="recipient_ids[1]"                data-endpoint="POSTapi-conversations--conversation_id--members"
               data-component="body">
    <br>
<p>Must match an existing stored value.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>include_history</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-conversations--conversation_id--members" style="display: none">
            <input type="radio" name="include_history"
                   value="true"
                   data-endpoint="POSTapi-conversations--conversation_id--members"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-conversations--conversation_id--members" style="display: none">
            <input type="radio" name="include_history"
                   value="false"
                   data-endpoint="POSTapi-conversations--conversation_id--members"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Whether new members should see existing message history. Example: <code>false</code></p>
        </div>
        </form>

                    <h2 id="conversations-DELETEapi-conversations--conversation_id--members--user_id-">Remove Group Member</h2>

<p>
</p>

<p>Remove a member from a group conversation.</p>

<span id="example-requests-DELETEapi-conversations--conversation_id--members--user_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/members/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/members/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-conversations--conversation_id--members--user_id-">
            <blockquote>
            <p>Example response (204):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-conversations--conversation_id--members--user_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-conversations--conversation_id--members--user_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-conversations--conversation_id--members--user_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-conversations--conversation_id--members--user_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-conversations--conversation_id--members--user_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-conversations--conversation_id--members--user_id-" data-method="DELETE"
      data-path="api/conversations/{conversation_id}/members/{user_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-conversations--conversation_id--members--user_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-conversations--conversation_id--members--user_id-"
                    onclick="tryItOut('DELETEapi-conversations--conversation_id--members--user_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-conversations--conversation_id--members--user_id-"
                    onclick="cancelTryOut('DELETEapi-conversations--conversation_id--members--user_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-conversations--conversation_id--members--user_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/conversations/{conversation_id}/members/{user_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-conversations--conversation_id--members--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-conversations--conversation_id--members--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="DELETEapi-conversations--conversation_id--members--user_id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="DELETEapi-conversations--conversation_id--members--user_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--leave">Leave Group Conversation</h2>

<p>
</p>

<p>Leave a group conversation.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--leave">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/leave';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/leave"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--leave">
            <blockquote>
            <p>Example response (204):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--leave" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--leave"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--leave"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--leave" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--leave">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--leave" data-method="POST"
      data-path="api/conversations/{conversation_id}/leave"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--leave', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--leave"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--leave');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--leave"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--leave');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--leave"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/leave</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--leave"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--leave"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--leave"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-PATCHapi-conversations--conversation_id--name">Update Group Name</h2>

<p>
</p>

<p>Update the name of a group conversation.</p>

<span id="example-requests-PATCHapi-conversations--conversation_id--name">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/name';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Weekend Warriors',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/name"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Weekend Warriors"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-conversations--conversation_id--name">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 363,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-conversations--conversation_id--name" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-conversations--conversation_id--name"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-conversations--conversation_id--name"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-conversations--conversation_id--name" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-conversations--conversation_id--name">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-conversations--conversation_id--name" data-method="PATCH"
      data-path="api/conversations/{conversation_id}/name"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-conversations--conversation_id--name', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-conversations--conversation_id--name"
                    onclick="tryItOut('PATCHapi-conversations--conversation_id--name');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-conversations--conversation_id--name"
                    onclick="cancelTryOut('PATCHapi-conversations--conversation_id--name');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-conversations--conversation_id--name"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/conversations/{conversation_id}/name</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-conversations--conversation_id--name"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-conversations--conversation_id--name"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="PATCHapi-conversations--conversation_id--name"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PATCHapi-conversations--conversation_id--name"
               value="Weekend Warriors"
               data-component="body">
    <br>
<p>The new group conversation name. Must not be greater than 255 characters. Example: <code>Weekend Warriors</code></p>
        </div>
        </form>

                    <h2 id="conversations-DELETEapi-conversations--id-">Delete Conversation</h2>

<p>
</p>

<p>Delete a conversation for the authenticated user.</p>

<span id="example-requests-DELETEapi-conversations--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-conversations--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation deleted.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-conversations--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-conversations--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-conversations--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-conversations--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-conversations--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-conversations--id-" data-method="DELETE"
      data-path="api/conversations/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-conversations--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-conversations--id-"
                    onclick="tryItOut('DELETEapi-conversations--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-conversations--id-"
                    onclick="cancelTryOut('DELETEapi-conversations--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-conversations--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/conversations/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-conversations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-conversations--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-conversations--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--archive">Archive Conversation</h2>

<p>
</p>

<p>Archive a conversation for the authenticated user.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--archive">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/archive';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/archive"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--archive">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation archived.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--archive" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--archive"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--archive"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--archive" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--archive">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--archive" data-method="POST"
      data-path="api/conversations/{conversation_id}/archive"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--archive', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--archive"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--archive');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--archive"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--archive');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--archive"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/archive</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--archive"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--unarchive">Unarchive Conversation</h2>

<p>
</p>

<p>Unarchive a previously archived conversation.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--unarchive">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/unarchive';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/unarchive"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--unarchive">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation unarchived.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--unarchive" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--unarchive"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--unarchive"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--unarchive" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--unarchive">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--unarchive" data-method="POST"
      data-path="api/conversations/{conversation_id}/unarchive"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--unarchive', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--unarchive"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--unarchive');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--unarchive"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--unarchive');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--unarchive"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/unarchive</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--unarchive"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--read">Mark Conversation as Read</h2>

<p>
</p>

<p>Mark a conversation as read for the authenticated user.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--read">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/read';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/read"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--read">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Conversation marked as read.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--read" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--read"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--read"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--read" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--read" data-method="POST"
      data-path="api/conversations/{conversation_id}/read"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--read', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--read"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--read');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--read"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--read');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--read"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/read</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--read"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    </form>

                    <h2 id="conversations-POSTapi-conversations--conversation_id--messages">Send Message</h2>

<p>
</p>

<p>Send a new message in a conversation.</p>

<span id="example-requests-POSTapi-conversations--conversation_id--messages">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/messages';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'body' =&gt; 'Looking forward to the game!',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/messages"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "body": "Looking forward to the game!"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-conversations--conversation_id--messages">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 155,
        &quot;conversation_id&quot;: 364,
        &quot;user_id&quot;: 1419,
        &quot;body&quot;: &quot;Et animi quos velit et fugiat. Nihil accusantium harum mollitia modi deserunt. Ab provident perspiciatis quo omnis nostrum aut adipisci.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-conversations--conversation_id--messages" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-conversations--conversation_id--messages"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-conversations--conversation_id--messages"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-conversations--conversation_id--messages" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-conversations--conversation_id--messages">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-conversations--conversation_id--messages" data-method="POST"
      data-path="api/conversations/{conversation_id}/messages"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-conversations--conversation_id--messages', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-conversations--conversation_id--messages"
                    onclick="tryItOut('POSTapi-conversations--conversation_id--messages');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-conversations--conversation_id--messages"
                    onclick="cancelTryOut('POSTapi-conversations--conversation_id--messages');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-conversations--conversation_id--messages"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/conversations/{conversation_id}/messages</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-conversations--conversation_id--messages"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-conversations--conversation_id--messages"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="POSTapi-conversations--conversation_id--messages"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body"                data-endpoint="POSTapi-conversations--conversation_id--messages"
               value="Looking forward to the game!"
               data-component="body">
    <br>
<p>The message content. Must be at least 1 character. Must not be greater than 1000 characters. Example: <code>Looking forward to the game!</code></p>
        </div>
        </form>

                    <h2 id="conversations-PATCHapi-conversations--conversation_id--messages--id-">Update Message</h2>

<p>
</p>

<p>Edit an existing message in a conversation.</p>

<span id="example-requests-PATCHapi-conversations--conversation_id--messages--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/messages/16';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'body' =&gt; 'Updated: Looking forward to the game!',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/messages/16"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "body": "Updated: Looking forward to the game!"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-conversations--conversation_id--messages--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 156,
        &quot;conversation_id&quot;: 365,
        &quot;user_id&quot;: 1420,
        &quot;body&quot;: &quot;Et animi quos velit et fugiat. Nihil accusantium harum mollitia modi deserunt. Ab provident perspiciatis quo omnis nostrum aut adipisci.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-conversations--conversation_id--messages--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-conversations--conversation_id--messages--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-conversations--conversation_id--messages--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-conversations--conversation_id--messages--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-conversations--conversation_id--messages--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-conversations--conversation_id--messages--id-" data-method="PATCH"
      data-path="api/conversations/{conversation_id}/messages/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-conversations--conversation_id--messages--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-conversations--conversation_id--messages--id-"
                    onclick="tryItOut('PATCHapi-conversations--conversation_id--messages--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-conversations--conversation_id--messages--id-"
                    onclick="cancelTryOut('PATCHapi-conversations--conversation_id--messages--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-conversations--conversation_id--messages--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/conversations/{conversation_id}/messages/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-conversations--conversation_id--messages--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-conversations--conversation_id--messages--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="PATCHapi-conversations--conversation_id--messages--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="PATCHapi-conversations--conversation_id--messages--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the message. Example: <code>16</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body"                data-endpoint="PATCHapi-conversations--conversation_id--messages--id-"
               value="Updated: Looking forward to the game!"
               data-component="body">
    <br>
<p>The updated message content. Must be at least 1 character. Must not be greater than 1000 characters. Example: <code>Updated: Looking forward to the game!</code></p>
        </div>
        </form>

                    <h2 id="conversations-DELETEapi-conversations--conversation_id--messages--id-">Delete Message</h2>

<p>
</p>

<p>Soft-delete a message in a conversation.</p>

<span id="example-requests-DELETEapi-conversations--conversation_id--messages--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/conversations/16/messages/16';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/conversations/16/messages/16"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-conversations--conversation_id--messages--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Message deleted.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-conversations--conversation_id--messages--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-conversations--conversation_id--messages--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-conversations--conversation_id--messages--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-conversations--conversation_id--messages--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-conversations--conversation_id--messages--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-conversations--conversation_id--messages--id-" data-method="DELETE"
      data-path="api/conversations/{conversation_id}/messages/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-conversations--conversation_id--messages--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-conversations--conversation_id--messages--id-"
                    onclick="tryItOut('DELETEapi-conversations--conversation_id--messages--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-conversations--conversation_id--messages--id-"
                    onclick="cancelTryOut('DELETEapi-conversations--conversation_id--messages--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-conversations--conversation_id--messages--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/conversations/{conversation_id}/messages/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-conversations--conversation_id--messages--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-conversations--conversation_id--messages--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>conversation_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="conversation_id"                data-endpoint="DELETEapi-conversations--conversation_id--messages--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the conversation. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-conversations--conversation_id--messages--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the message. Example: <code>16</code></p>
            </div>
                    </form>

                <h1 id="events">Events</h1>

    <p>APIs for Events</p>

                                <h2 id="events-GETapi-events">List Events</h2>

<p>
</p>

<p>List publicly visible events with optional filters and search.</p>

<span id="example-requests-GETapi-events">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'search' =&gt; 'Grand Tournament',
            'status' =&gt; 'active',
            'game_system' =&gt; 'warhammer-40k',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events"
);

const params = {
    "search": "Grand Tournament",
    "status": "active",
    "game_system": "warhammer-40k",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 638,
        &quot;name&quot;: &quot;Eius et animi&quot;,
        &quot;slug&quot;: &quot;eius-et-animi-565&quot;,
        &quot;description&quot;: &quot;Sunt nihil accusantium harum mollitia. Deserunt aut ab provident perspiciatis quo omnis nostrum. Adipisci quidem nostrum qui commodi incidunt iure.&quot;,
        &quot;status&quot;: &quot;draft&quot;,
        &quot;pairing_format&quot;: &quot;swiss&quot;,
        &quot;starts_at&quot;: &quot;2027-02-12T22:45:50Z&quot;,
        &quot;ends_at&quot;: &quot;2027-02-14T22:45:50Z&quot;,
        &quot;max_attendees&quot;: 100,
        &quot;attendee_size&quot;: 1,
        &quot;requires_allegiance&quot;: false,
        &quot;registration_closes_at&quot;: null,
        &quot;is_full&quot;: false,
        &quot;venue&quot;: {
            &quot;name&quot;: &quot;McLaughlin, Leuschke and Bauch Hall&quot;,
            &quot;address&quot;: &quot;45058 Bailee Rue&quot;,
            &quot;city&quot;: &quot;South Matildaburgh&quot;,
            &quot;country&quot;: &quot;IE&quot;
        },
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events" data-method="GET"
      data-path="api/events"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events"
                    onclick="tryItOut('GETapi-events');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events"
                    onclick="cancelTryOut('GETapi-events');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-events"
               value="Grand Tournament"
               data-component="query">
    <br>
<p>Search events by name. Must not be greater than 255 characters. Example: <code>Grand Tournament</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="GETapi-events"
               value="active"
               data-component="query">
    <br>
<p>Filter by event status. One of: published, active, completed. Example: <code>active</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>draft</code></li> <li><code>published</code></li> <li><code>active</code></li> <li><code>completed</code></li> <li><code>cancelled</code></li></ul>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game_system</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="game_system"                data-endpoint="GETapi-events"
               value="warhammer-40k"
               data-component="query">
    <br>
<p>Filter by game system slug. Must match an existing stored value. Example: <code>warhammer-40k</code></p>
            </div>
                </form>

                    <h2 id="events-GETapi-events--slug-">Show Event</h2>

<p>
</p>

<p>Get a publicly visible event by slug.</p>

<span id="example-requests-GETapi-events--slug-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/summer-showdown-2026"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--slug-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;London Grand Tournament&quot;,
        &quot;slug&quot;: &quot;london-grand-tournament&quot;,
        &quot;description&quot;: &quot;A two-day Horus Heresy doubles event.&quot;,
        &quot;status&quot;: &quot;published&quot;,
        &quot;pairing_format&quot;: &quot;swiss&quot;,
        &quot;starts_at&quot;: &quot;2026-09-12T09:00:00Z&quot;,
        &quot;ends_at&quot;: &quot;2026-09-13T18:00:00Z&quot;,
        &quot;max_attendees&quot;: 32,
        &quot;attendee_size&quot;: 2,
        &quot;requires_allegiance&quot;: true,
        &quot;registration_closes_at&quot;: &quot;2026-09-05T23:59:00Z&quot;,
        &quot;attendees_count&quot;: 18,
        &quot;is_full&quot;: false,
        &quot;venue&quot;: {
            &quot;name&quot;: &quot;The Hall&quot;,
            &quot;address&quot;: &quot;1 Example Street&quot;,
            &quot;city&quot;: &quot;London&quot;,
            &quot;country&quot;: &quot;GB&quot;
        },
        &quot;game_system&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Horus Heresy&quot;,
            &quot;slug&quot;: &quot;horus-heresy&quot;
        },
        &quot;documents&quot;: [],
        &quot;created_at&quot;: &quot;2026-06-01T10:00:00Z&quot;,
        &quot;updated_at&quot;: &quot;2026-06-01T10:00:00Z&quot;,
        &quot;viewer&quot;: {
            &quot;is_organiser&quot;: true,
            &quot;is_lead_organiser&quot;: false,
            &quot;is_attendee&quot;: true,
            &quot;attendee_id&quot;: 9,
            &quot;permissions&quot;: {
                &quot;organise&quot;: true,
                &quot;register&quot;: false,
                &quot;manage_organisers&quot;: false
            }
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--slug-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--slug-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--slug-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--slug-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--slug-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--slug-" data-method="GET"
      data-path="api/events/{slug}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--slug-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--slug-"
                    onclick="tryItOut('GETapi-events--slug-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--slug-"
                    onclick="cancelTryOut('GETapi-events--slug-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--slug-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{slug}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--slug-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="slug"                data-endpoint="GETapi-events--slug-"
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    </form>

    <h3>Response</h3>
    <h4 class="fancy-heading-panel"><b>Response Fields</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>data</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>attendee_size</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>How many Players make up one party. Two for a doubles Event.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>requires_allegiance</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether a party must declare a side, because this Event pairs across the divide.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>attendees_count</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>How many parties have entered. Present where the caller counted them.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>is_full</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether every place has been taken. A null limit is no limit.</p>
                    </div>
                                                                <div style=" margin-left: 14px; clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>viewer</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>What the reader may see and do at this Event. Null for an anonymous request.</p>
            </summary>
                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>is_organiser</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether the reader runs this Event.</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>is_lead_organiser</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether the reader leads it, and so may appoint Organisers.</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>is_attendee</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether the reader is competing here.</p>
                    </div>
                                                                <div style="margin-left: 28px; clear: unset;">
                        <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>The party the reader competes as, if any.</p>
                    </div>
                                                                <div style=" margin-left: 28px; clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>permissions</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 42px; clear: unset;">
                        <b style="line-height: 2;"><code>organise</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Publish Rounds, correct results, open Polls, read tallies.</p>
                    </div>
                                                                <div style="margin-left: 42px; clear: unset;">
                        <b style="line-height: 2;"><code>register</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Enter the Event.</p>
                    </div>
                                                                <div style="margin-left: 42px; clear: unset;">
                        <b style="line-height: 2;"><code>manage_organisers</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Appoint and remove Organisers.</p>
                    </div>
                                    </details>
        </div>
                                        </details>
        </div>
                                        </details>
        </div>
                        <h2 id="events-GETapi-events--event_slug--updates">List Event Updates</h2>

<p>
</p>

<p>List updates for a publicly visible event, pinned first then most recent.</p>

<span id="example-requests-GETapi-events--event_slug--updates">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/updates';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/summer-showdown-2026/updates"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--updates">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 58,
        &quot;title&quot;: &quot;Fugit deleniti distinctio eum doloremque&quot;,
        &quot;body&quot;: &quot;Libero aliquam veniam corporis dolorem mollitia deleniti. Odit quia officia est dignissimos neque blanditiis odio. Excepturi doloribus delectus fugit qui repudiandae laboriosam.\n\nTenetur ratione nemo voluptate accusamus ut et recusandae modi. Ex repellendus assumenda et tenetur ab reiciendis. Perspiciatis deserunt ducimus corrupti et dolores quia. Assumenda odit doloribus repellat officiis corporis nesciunt ut.&quot;,
        &quot;pinned&quot;: false,
        &quot;published_at&quot;: &quot;2026-08-06T06:47:10Z&quot;,
        &quot;author&quot;: {
            &quot;id&quot;: 1421,
            &quot;name&quot;: &quot;Cordia Cummings&quot;
        },
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--updates" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--updates"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--updates"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--updates" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--updates">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--updates" data-method="GET"
      data-path="api/events/{event_slug}/updates"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--updates', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--updates"
                    onclick="tryItOut('GETapi-events--event_slug--updates');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--updates"
                    onclick="cancelTryOut('GETapi-events--event_slug--updates');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--updates"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/updates</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--updates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--updates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--updates"
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--attendees">List Event Attendees</h2>

<p>
</p>

<p>Paginated list of attendees for a publicly visible event.</p>

<span id="example-requests-GETapi-events--event_slug--attendees">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'search' =&gt; 'john',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees"
);

const params = {
    "search": "john",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--attendees">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 177,
        &quot;name&quot;: &quot;&quot;,
        &quot;allegiance&quot;: null,
        &quot;members&quot;: []
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--attendees" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--attendees"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--attendees"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--attendees" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--attendees">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--attendees" data-method="GET"
      data-path="api/events/{event_slug}/attendees"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--attendees', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--attendees"
                    onclick="tryItOut('GETapi-events--event_slug--attendees');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--attendees"
                    onclick="cancelTryOut('GETapi-events--event_slug--attendees');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--attendees"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/attendees</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--attendees"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--attendees"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--attendees"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-events--event_slug--attendees"
               value="john"
               data-component="query">
    <br>
<p>Search attendees by name or username. Must not be greater than 255 characters. Example: <code>john</code></p>
            </div>
                </form>

                    <h2 id="events-GETapi-events--event_slug--factions">List the Factions on offer</h2>

<p>
</p>

<p>Every Faction in this Event's game system, for the picker a Player records theirs with.</p>

<span id="example-requests-GETapi-events--event_slug--factions">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/factions';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/factions"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--factions">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Sons of Horus&quot;,
            &quot;slug&quot;: &quot;sons-of-horus&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Imperial Fists&quot;,
            &quot;slug&quot;: &quot;imperial-fists&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--factions" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--factions"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--factions"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--factions" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--factions">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--factions" data-method="GET"
      data-path="api/events/{event_slug}/factions"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--factions', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--factions"
                    onclick="tryItOut('GETapi-events--event_slug--factions');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--factions"
                    onclick="cancelTryOut('GETapi-events--event_slug--factions');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--factions"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/factions</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--factions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--factions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--factions"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--attendees--id-">Show Event Attendee</h2>

<p>
</p>

<p>Attendee detail for a publicly visible event.</p>

<span id="example-requests-GETapi-events--event_slug--attendees--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--attendees--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 178,
        &quot;name&quot;: &quot;&quot;,
        &quot;allegiance&quot;: null,
        &quot;members&quot;: [],
        &quot;checked_in_at&quot;: null,
        &quot;painting_entered&quot;: false,
        &quot;display_number&quot;: null,
        &quot;custom_field_responses&quot;: [],
        &quot;games&quot;: []
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--attendees--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--attendees--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--attendees--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--attendees--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--attendees--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--attendees--id-" data-method="GET"
      data-path="api/events/{event_slug}/attendees/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--attendees--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--attendees--id-"
                    onclick="tryItOut('GETapi-events--event_slug--attendees--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--attendees--id-"
                    onclick="cancelTryOut('GETapi-events--event_slug--attendees--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--attendees--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/attendees/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--rounds">List Event Rounds</h2>

<p>
</p>

<p>List rounds for an event. Only visible for Active/Completed events. Draft rounds are shown to Organisers only.</p>

<span id="example-requests-GETapi-events--event_slug--rounds">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--rounds">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 95,
            &quot;number&quot;: 1,
            &quot;name&quot;: null,
            &quot;status&quot;: &quot;draft&quot;
        },
        {
            &quot;id&quot;: 96,
            &quot;number&quot;: 3,
            &quot;name&quot;: null,
            &quot;status&quot;: &quot;draft&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--rounds" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--rounds"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--rounds"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--rounds" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--rounds">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--rounds" data-method="GET"
      data-path="api/events/{event_slug}/rounds"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--rounds', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--rounds"
                    onclick="tryItOut('GETapi-events--event_slug--rounds');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--rounds"
                    onclick="cancelTryOut('GETapi-events--event_slug--rounds');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--rounds"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/rounds</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--rounds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--rounds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--rounds"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--rounds--id-">Show Event Round</h2>

<p>
</p>

<p>Round detail with games for an event. Draft rounds are visible to Organisers only.</p>

<span id="example-requests-GETapi-events--event_slug--rounds--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--rounds--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 4,
        &quot;number&quot;: 2,
        &quot;name&quot;: &quot;Round 2&quot;,
        &quot;status&quot;: &quot;live&quot;,
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;is_rematch&quot;: false,
                &quot;result&quot;: {
                    &quot;submitted_at&quot;: null,
                    &quot;is_flagged&quot;: false
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;allegiance&quot;: &quot;loyalist&quot;,
                        &quot;members&quot;: [
                            {
                                &quot;id&quot;: 12,
                                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                                &quot;faction&quot;: {
                                    &quot;id&quot;: 3,
                                    &quot;name&quot;: &quot;Sons of Horus&quot;
                                },
                                &quot;army_list_locked&quot;: true
                            }
                        ],
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--rounds--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--rounds--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--rounds--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--rounds--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--rounds--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--rounds--id-" data-method="GET"
      data-path="api/events/{event_slug}/rounds/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--rounds--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--rounds--id-"
                    onclick="tryItOut('GETapi-events--event_slug--rounds--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--rounds--id-"
                    onclick="cancelTryOut('GETapi-events--event_slug--rounds--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--rounds--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/rounds/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the round. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--games--id-">Show Event Game</h2>

<p>
</p>

<p>Game detail with full score breakdown and army lists. Games in a Draft round are visible to Organisers only.</p>

<span id="example-requests-GETapi-events--event_slug--games--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/games/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/games/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--games--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 18,
        &quot;table_number&quot;: 5,
        &quot;is_bye&quot;: false,
        &quot;round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2,
            &quot;name&quot;: &quot;Round 2&quot;
        },
        &quot;result&quot;: {
            &quot;submitted_at&quot;: &quot;2026-09-12T14:05:00+00:00&quot;,
            &quot;submitted_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;edited_at&quot;: null,
            &quot;edited_by&quot;: null,
            &quot;is_flagged&quot;: false
        },
        &quot;attendees&quot;: [
            {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list_locked&quot;: true,
                        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;
                    }
                ],
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                }
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--games--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--games--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--games--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--games--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--games--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--games--id-" data-method="GET"
      data-path="api/events/{event_slug}/games/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--games--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--games--id-"
                    onclick="tryItOut('GETapi-events--event_slug--games--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--games--id-"
                    onclick="cancelTryOut('GETapi-events--event_slug--games--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--games--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/games/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--standings">List Event Standings</h2>

<p>
</p>

<p>Paginated standings for a publicly visible event, computed from Games. Ranked on Match Points then Victory Points, with tied Attendees sharing a position. Sorting by a Score Type changes the order of the list but never the reported position.</p>

<span id="example-requests-GETapi-events--event_slug--standings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/standings';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'search' =&gt; 'john',
            'sort_by' =&gt; 'points',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/standings"
);

const params = {
    "search": "john",
    "sort_by": "points",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--standings">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 9,
            &quot;position&quot;: 1,
            &quot;attendee&quot;: {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list_locked&quot;: true,
                        &quot;clubs&quot;: [
                            {
                                &quot;id&quot;: 2,
                                &quot;name&quot;: &quot;The Ordo Ludi&quot;
                            }
                        ]
                    }
                ]
            },
            &quot;scores&quot;: [
                {
                    &quot;value&quot;: 6,
                    &quot;score_type&quot;: {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Match Points&quot;,
                        &quot;slug&quot;: &quot;match-points&quot;,
                        &quot;sort_direction&quot;: &quot;desc&quot;
                    }
                }
            ]
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--standings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--standings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--standings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--standings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--standings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--standings" data-method="GET"
      data-path="api/events/{event_slug}/standings"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--standings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--standings"
                    onclick="tryItOut('GETapi-events--event_slug--standings');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--standings"
                    onclick="cancelTryOut('GETapi-events--event_slug--standings');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--standings"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/standings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--standings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--standings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--standings"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="search"                data-endpoint="GETapi-events--event_slug--standings"
               value="john"
               data-component="query">
    <br>
<p>Search standings by player name or username. Must not be greater than 255 characters. Example: <code>john</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>sort_by</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sort_by"                data-endpoint="GETapi-events--event_slug--standings"
               value="points"
               data-component="query">
    <br>
<p>The field to sort standings by. Must not be greater than 255 characters. Example: <code>points</code></p>
            </div>
                </form>

                    <h2 id="events-GETapi-events--event_slug--pulse">Event Pulse</h2>

<p>
</p>

<p>Change stamps for the live-critical resources of an Event. Cheap enough to poll: four aggregates, no recomputation, and a fixed number of queries however large the Event.</p>

<span id="example-requests-GETapi-events--event_slug--pulse">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/pulse';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/pulse"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--pulse">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;current_round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2
        },
        &quot;rounds&quot;: &quot;2026-09-12T13:30:00Z&quot;,
        &quot;standings&quot;: &quot;2026-09-12T14:05:12Z&quot;,
        &quot;polls&quot;: &quot;2026-09-12T16:00:00Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--pulse" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--pulse"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--pulse"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--pulse" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--pulse">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--pulse" data-method="GET"
      data-path="api/events/{event_slug}/pulse"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--pulse', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--pulse"
                    onclick="tryItOut('GETapi-events--event_slug--pulse');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--pulse"
                    onclick="cancelTryOut('GETapi-events--event_slug--pulse');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--pulse"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/pulse</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--pulse"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--pulse"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--pulse"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

    <h3>Response</h3>
    <h4 class="fancy-heading-panel"><b>Response Fields</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>data</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>current_round</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>The highest-numbered Live Round, or null before any Round is published.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>rounds</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>When a Round was last created, published or unpublished. Null when the Event has no Rounds.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>standings</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>When a score last changed. Null before any result is in.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>polls</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>When a Poll was last opened, closed or changed. Null when the Event has no Polls.</p>
                    </div>
                                    </details>
        </div>
                        <h2 id="events-GETapi-events--event_slug--gallery">List Event Gallery</h2>

<p>
</p>

<p>Paginated photos for a publicly visible event.</p>

<span id="example-requests-GETapi-events--event_slug--gallery">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/gallery';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/gallery"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--gallery">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 302,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Et fugiat sunt nihil accusantium. Mollitia modi deserunt aut ab provident perspiciatis quo. Nostrum aut adipisci quidem nostrum.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/445bd3f6-8f2c-38cb-aa04-2f4e1edb32bb.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/deea2dce-ea5d-340f-90ce-c06cddd4c879.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--gallery" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--gallery"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--gallery"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--gallery" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--gallery">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--gallery" data-method="GET"
      data-path="api/events/{event_slug}/gallery"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--gallery', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--gallery"
                    onclick="tryItOut('GETapi-events--event_slug--gallery');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--gallery"
                    onclick="cancelTryOut('GETapi-events--event_slug--gallery');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--gallery"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/gallery</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--gallery"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--schedule">List the Event Schedule</h2>

<p>
</p>

<p>The Event's schedule grouped by day in the Event's own timezone, each day's blocks in time order.</p>

<span id="example-requests-GETapi-events--event_slug--schedule">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/schedule';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/schedule"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--schedule">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;date&quot;: &quot;2026-09-12&quot;,
            &quot;blocks&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;label&quot;: &quot;Round 1&quot;,
                    &quot;type&quot;: &quot;round&quot;,
                    &quot;starts_at&quot;: &quot;2026-09-12T09:30:00+00:00&quot;,
                    &quot;ends_at&quot;: &quot;2026-09-12T12:00:00+00:00&quot;,
                    &quot;display_order&quot;: 0,
                    &quot;target_id&quot;: 4,
                    &quot;is_target_live&quot;: true,
                    &quot;round&quot;: {
                        &quot;id&quot;: 4,
                        &quot;number&quot;: 1,
                        &quot;name&quot;: &quot;Round 1&quot;
                    }
                }
            ]
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--schedule" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--schedule"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--schedule"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--schedule" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--schedule">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--schedule" data-method="GET"
      data-path="api/events/{event_slug}/schedule"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--schedule', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--schedule"
                    onclick="tryItOut('GETapi-events--event_slug--schedule');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--schedule"
                    onclick="cancelTryOut('GETapi-events--event_slug--schedule');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--schedule"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/schedule</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--schedule"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-feedback--token-">Show the Feedback Form</h2>

<p>
</p>

<p>The questions behind a feedback link. A spent or expired link is not found rather than explained, since the token is the only credential.</p>

<span id="example-requests-GETapi-feedback--token-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/feedback/aVeryLongRandomToken';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/feedback/aVeryLongRandomToken"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-feedback--token-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;event&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;London Grand Tournament&quot;,
            &quot;slug&quot;: &quot;london-grand-tournament&quot;
        },
        &quot;expires_at&quot;: &quot;2026-09-20T09:00:00+00:00&quot;,
        &quot;questions&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;key&quot;: &quot;overall&quot;,
                &quot;prompt&quot;: &quot;How was the Event overall?&quot;,
                &quot;type&quot;: &quot;rating&quot;
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, The link is unknown, already used, or expired.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-feedback--token-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-feedback--token-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-feedback--token-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-feedback--token-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-feedback--token-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-feedback--token-" data-method="GET"
      data-path="api/feedback/{token}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-feedback--token-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-feedback--token-"
                    onclick="tryItOut('GETapi-feedback--token-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-feedback--token-"
                    onclick="cancelTryOut('GETapi-feedback--token-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-feedback--token-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/feedback/{token}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-feedback--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-feedback--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="GETapi-feedback--token-"
               value="aVeryLongRandomToken"
               data-component="url">
    <br>
<p>The token from the feedback email. Example: <code>aVeryLongRandomToken</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-feedback--token-">Submit Feedback</h2>

<p>
</p>

<p>Answers are stored against the Event and the question only — never against the Player. The link is spent afterwards, which is the sole reason the invitation records who it belonged to.</p>

<span id="example-requests-POSTapi-feedback--token-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/feedback/aVeryLongRandomToken';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'answers' =&gt; [
                ['question_id' =&gt; 1, 'rating' =&gt; 5],
                ['question_id' =&gt; 8, 'answer' =&gt; 'The missions were excellent.'],
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/feedback/aVeryLongRandomToken"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "answers": [
        {
            "question_id": 1,
            "rating": 5
        },
        {
            "question_id": 8,
            "answer": "The missions were excellent."
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-feedback--token-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;submitted&quot;: true
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, The link is unknown, already used, or expired.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-feedback--token-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-feedback--token-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-feedback--token-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-feedback--token-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-feedback--token-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-feedback--token-" data-method="POST"
      data-path="api/feedback/{token}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-feedback--token-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-feedback--token-"
                    onclick="tryItOut('POSTapi-feedback--token-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-feedback--token-"
                    onclick="cancelTryOut('POSTapi-feedback--token-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-feedback--token-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/feedback/{token}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-feedback--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-feedback--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-feedback--token-"
               value="aVeryLongRandomToken"
               data-component="url">
    <br>
<p>The token from the feedback email. Example: <code>aVeryLongRandomToken</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>answers</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>One entry per question answered: a rating for rating questions, an answer for text questions. Unanswered questions may be left out.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>question_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="answers.0.question_id"                data-endpoint="POSTapi-feedback--token-"
               value="16"
               data-component="body">
    <br>
<p>Must match an existing stored value. Example: <code>16</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>rating</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="answers.0.rating"                data-endpoint="POSTapi-feedback--token-"
               value="2"
               data-component="body">
    <br>
<p>Must be at least 1. Must not be greater than 5. Example: <code>2</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>answer</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="answers.0.answer"                data-endpoint="POSTapi-feedback--token-"
               value="g"
               data-component="body">
    <br>
<p>Must not be greater than 5000 characters. Example: <code>g</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="events-GETapi-invites--token-">Open an Invitation</h2>

<p>
</p>

<p>Resolve an emailed invite token. The link is reusable for the life of the invite.</p>

<span id="example-requests-GETapi-invites--token-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/invites/architecto';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/invites/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-invites--token-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 7,
        &quot;role&quot;: &quot;captain&quot;,
        &quot;email&quot;: &quot;captain@example.com&quot;,
        &quot;name&quot;: &quot;Ada Lovelace&quot;,
        &quot;is_claimed&quot;: false,
        &quot;attendee_id&quot;: null,
        &quot;event&quot;: {
            &quot;slug&quot;: &quot;london-grand-tournament&quot;,
            &quot;name&quot;: &quot;London Grand Tournament&quot;,
            &quot;starts_at&quot;: &quot;2026-09-12T09:00:00+00:00&quot;,
            &quot;ends_at&quot;: &quot;2026-09-13T18:00:00+00:00&quot;
        },
        &quot;expires_at&quot;: &quot;2026-09-12T09:00:00+00:00&quot;,
        &quot;revoked_at&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, The token is unknown, spent, revoked or expired.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-invites--token-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-invites--token-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-invites--token-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-invites--token-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-invites--token-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-invites--token-" data-method="GET"
      data-path="api/invites/{token}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-invites--token-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-invites--token-"
                    onclick="tryItOut('GETapi-invites--token-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-invites--token-"
                    onclick="cancelTryOut('GETapi-invites--token-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-invites--token-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/invites/{token}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-invites--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-invites--token-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="GETapi-invites--token-"
               value="architecto"
               data-component="url">
    <br>
<p>The token from the invitation email. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-invites--token--session">Enter with an Invitation</h2>

<p>
</p>

<p>Exchange an invite token for an API token acting as the invited account. The token expires with the invitation.</p>

<span id="example-requests-POSTapi-invites--token--session">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/invites/architecto/session';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'device_name' =&gt; 'iPhone',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/invites/architecto/session"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "device_name": "iPhone"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-invites--token--session">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;{AUTH_TOKEN}&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-invites--token--session" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-invites--token--session"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-invites--token--session"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-invites--token--session" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-invites--token--session">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-invites--token--session" data-method="POST"
      data-path="api/invites/{token}/session"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-invites--token--session', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-invites--token--session"
                    onclick="tryItOut('POSTapi-invites--token--session');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-invites--token--session"
                    onclick="cancelTryOut('POSTapi-invites--token--session');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-invites--token--session"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/invites/{token}/session</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-invites--token--session"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-invites--token--session"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-invites--token--session"
               value="architecto"
               data-component="url">
    <br>
<p>The token from the invitation email. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-invites--token--session"
               value="iPhone"
               data-component="body">
    <br>
<p>The name of the device entering. Example: <code>iPhone</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-invites--token--claim">Claim an Invited Account</h2>

<p>
</p>

<p>Set a password on the invited account, turning it into a real one. This revokes the invitation.</p>

<span id="example-requests-POSTapi-invites--token--claim">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/invites/architecto/claim';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'password' =&gt; 'password',
            'device_name' =&gt; 'iPhone 15',
            'name' =&gt; 'Horus Lupercal',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/invites/architecto/claim"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "password": "password",
    "device_name": "iPhone 15",
    "name": "Horus Lupercal"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-invites--token--claim">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;{AUTH_TOKEN}&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-invites--token--claim" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-invites--token--claim"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-invites--token--claim"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-invites--token--claim" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-invites--token--claim">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-invites--token--claim" data-method="POST"
      data-path="api/invites/{token}/claim"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-invites--token--claim', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-invites--token--claim"
                    onclick="tryItOut('POSTapi-invites--token--claim');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-invites--token--claim"
                    onclick="cancelTryOut('POSTapi-invites--token--claim');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-invites--token--claim"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/invites/{token}/claim</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-invites--token--claim"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-invites--token--claim"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>token</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="token"                data-endpoint="POSTapi-invites--token--claim"
               value="architecto"
               data-component="url">
    <br>
<p>The token from the invitation email. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-invites--token--claim"
               value="password"
               data-component="body">
    <br>
<p>The password to set on the account. Must be at least 8 characters. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-invites--token--claim"
               value="iPhone 15"
               data-component="body">
    <br>
<p>A name identifying the device requesting the token. Must not be greater than 255 characters. Example: <code>iPhone 15</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-invites--token--claim"
               value="Horus Lupercal"
               data-component="body">
    <br>
<p>The name to go by, if it differs from the one the organiser entered. Must not be greater than 255 characters. Example: <code>Horus Lupercal</code></p>
        </div>
        </form>

                    <h2 id="events-GETapi-events--event_slug--organisers">List Event Organisers</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The Players trusted to run this event. Organisers only.</p>

<span id="example-requests-GETapi-events--event_slug--organisers">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/organisers';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/organisers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--organisers">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 12,
            &quot;name&quot;: &quot;Ada Lovelace&quot;,
            &quot;role&quot;: &quot;lead&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--organisers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--organisers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--organisers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--organisers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--organisers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--organisers" data-method="GET"
      data-path="api/events/{event_slug}/organisers"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--organisers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--organisers"
                    onclick="tryItOut('GETapi-events--event_slug--organisers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--organisers"
                    onclick="cancelTryOut('GETapi-events--event_slug--organisers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--organisers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/organisers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--organisers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--organisers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--organisers"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--organisers">Appoint an Organiser</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Lead organisers only. The account must already be claimed.</p>

<span id="example-requests-POSTapi-events--event_slug--organisers">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/organisers';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'organiser@example.com',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/organisers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "organiser@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--organisers">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 12,
        &quot;name&quot;: &quot;Ada Lovelace&quot;,
        &quot;role&quot;: &quot;lead&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--organisers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--organisers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--organisers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--organisers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--organisers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--organisers" data-method="POST"
      data-path="api/events/{event_slug}/organisers"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--organisers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--organisers"
                    onclick="tryItOut('POSTapi-events--event_slug--organisers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--organisers"
                    onclick="cancelTryOut('POSTapi-events--event_slug--organisers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--organisers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/organisers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--organisers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--organisers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--organisers"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-events--event_slug--organisers"
               value="organiser@example.com"
               data-component="body">
    <br>
<p>The email of the claimed account to appoint. Example: <code>organiser@example.com</code></p>
        </div>
        </form>

                    <h2 id="events-DELETEapi-events--event_slug--organisers--user_id-">Remove an Organiser</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Lead organisers only.</p>

<span id="example-requests-DELETEapi-events--event_slug--organisers--user_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/organisers/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/organisers/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-events--event_slug--organisers--user_id-">
            <blockquote>
            <p>Example response (200, The Organiser was removed.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;"></code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-events--event_slug--organisers--user_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-events--event_slug--organisers--user_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-events--event_slug--organisers--user_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-events--event_slug--organisers--user_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-events--event_slug--organisers--user_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-events--event_slug--organisers--user_id-" data-method="DELETE"
      data-path="api/events/{event_slug}/organisers/{user_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-events--event_slug--organisers--user_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-events--event_slug--organisers--user_id-"
                    onclick="tryItOut('DELETEapi-events--event_slug--organisers--user_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-events--event_slug--organisers--user_id-"
                    onclick="cancelTryOut('DELETEapi-events--event_slug--organisers--user_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-events--event_slug--organisers--user_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/events/{event_slug}/organisers/{user_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-events--event_slug--organisers--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-events--event_slug--organisers--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="DELETEapi-events--event_slug--organisers--user_id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="DELETEapi-events--event_slug--organisers--user_id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the organiser to remove. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--invites">Invite a Captain</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Emails a time-limited credential for this Event.</p>

<span id="example-requests-POSTapi-events--event_slug--invites">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/invites';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'email' =&gt; 'captain@example.com',
            'name' =&gt; 'Horus Lupercal',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/invites"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "captain@example.com",
    "name": "Horus Lupercal"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--invites">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 7,
        &quot;role&quot;: &quot;captain&quot;,
        &quot;email&quot;: &quot;captain@example.com&quot;,
        &quot;name&quot;: &quot;Ada Lovelace&quot;,
        &quot;is_claimed&quot;: false,
        &quot;attendee_id&quot;: null,
        &quot;event&quot;: {
            &quot;slug&quot;: &quot;london-grand-tournament&quot;,
            &quot;name&quot;: &quot;London Grand Tournament&quot;,
            &quot;starts_at&quot;: &quot;2026-09-12T09:00:00+00:00&quot;,
            &quot;ends_at&quot;: &quot;2026-09-13T18:00:00+00:00&quot;
        },
        &quot;expires_at&quot;: &quot;2026-09-12T09:00:00+00:00&quot;,
        &quot;revoked_at&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--invites" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--invites"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--invites"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--invites" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--invites">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--invites" data-method="POST"
      data-path="api/events/{event_slug}/invites"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--invites', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--invites"
                    onclick="tryItOut('POSTapi-events--event_slug--invites');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--invites"
                    onclick="cancelTryOut('POSTapi-events--event_slug--invites');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--invites"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/invites</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--invites"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--invites"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-events--event_slug--invites"
               value="captain@example.com"
               data-component="body">
    <br>
<p>The email address to invite. Example: <code>captain@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-events--event_slug--invites"
               value="Horus Lupercal"
               data-component="body">
    <br>
<p>The name to give the invited account, if it does not exist yet. Example: <code>Horus Lupercal</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--feedback-requests">Send Feedback Requests</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Emails every Player their own one-time link, valid for 30 days. Players who have already answered are left alone.</p>

<span id="example-requests-POSTapi-events--event_slug--feedback-requests">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/feedback/requests';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/feedback/requests"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--feedback-requests">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;invited&quot;: 24
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--feedback-requests" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--feedback-requests"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--feedback-requests"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--feedback-requests" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--feedback-requests">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--feedback-requests" data-method="POST"
      data-path="api/events/{event_slug}/feedback/requests"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--feedback-requests', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--feedback-requests"
                    onclick="tryItOut('POSTapi-events--event_slug--feedback-requests');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--feedback-requests"
                    onclick="cancelTryOut('POSTapi-events--event_slug--feedback-requests');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--feedback-requests"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/feedback/requests</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--feedback-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--feedback-requests"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--feedback-requests"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--feedback">Read Feedback</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Ratings summarised, free text listed, and nothing tying either to a Player — the responses carry no such link to begin with.</p>

<span id="example-requests-GETapi-events--event_slug--feedback">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/feedback';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/feedback"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--feedback">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;invitations&quot;: {
            &quot;sent&quot;: 32,
            &quot;submitted&quot;: 19
        },
        &quot;questions&quot;: [
            {
                &quot;key&quot;: &quot;overall&quot;,
                &quot;prompt&quot;: &quot;How was the Event overall?&quot;,
                &quot;type&quot;: &quot;rating&quot;,
                &quot;response_count&quot;: 19,
                &quot;average&quot;: 4.42,
                &quot;distribution&quot;: {
                    &quot;1&quot;: 0,
                    &quot;2&quot;: 1,
                    &quot;3&quot;: 2,
                    &quot;4&quot;: 5,
                    &quot;5&quot;: 11
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--feedback" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--feedback"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--feedback"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--feedback" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--feedback">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--feedback" data-method="GET"
      data-path="api/events/{event_slug}/feedback"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--feedback', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--feedback"
                    onclick="tryItOut('GETapi-events--event_slug--feedback');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--feedback"
                    onclick="cancelTryOut('GETapi-events--event_slug--feedback');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--feedback"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/feedback</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--feedback"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--feedback"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--feedback"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--feedback-export">Export Feedback</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. A CSV of every answer, grouped by question and shuffled within it, so no row can be tied to a Player or to another row. Synchronous: one Event is a few hundred rows.</p>

<span id="example-requests-GETapi-events--event_slug--feedback-export">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/feedback/export';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/feedback/export"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--feedback-export">
            <blockquote>
            <p>Example response (200, A CSV download.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">question_key,prompt,type,rating,answer
overall,How was the Event overall?,rating,5,
</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--feedback-export" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--feedback-export"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--feedback-export"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--feedback-export" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--feedback-export">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--feedback-export" data-method="GET"
      data-path="api/events/{event_slug}/feedback/export"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--feedback-export', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--feedback-export"
                    onclick="tryItOut('GETapi-events--event_slug--feedback-export');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--feedback-export"
                    onclick="cancelTryOut('GETapi-events--event_slug--feedback-export');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--feedback-export"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/feedback/export</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--feedback-export"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--feedback-export"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--feedback-export"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--polls">List Polls</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The Event's Polls and whether each is open. Tallies are not here, and are never readable by Players.</p>

<span id="example-requests-GETapi-events--event_slug--polls">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--polls">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 217,
            &quot;name&quot;: &quot;Best Painted Army&quot;,
            &quot;type&quot;: &quot;painting&quot;,
            &quot;votes_per_player&quot;: 1,
            &quot;opens_at&quot;: null,
            &quot;closes_at&quot;: null,
            &quot;is_open&quot;: false,
            &quot;is_open_for_me&quot;: null,
            &quot;my_ballot&quot;: []
        },
        {
            &quot;id&quot;: 218,
            &quot;name&quot;: &quot;Best Painted Army&quot;,
            &quot;type&quot;: &quot;painting&quot;,
            &quot;votes_per_player&quot;: 1,
            &quot;opens_at&quot;: null,
            &quot;closes_at&quot;: null,
            &quot;is_open&quot;: false,
            &quot;is_open_for_me&quot;: null,
            &quot;my_ballot&quot;: []
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--polls" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--polls"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--polls"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--polls" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--polls">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--polls" data-method="GET"
      data-path="api/events/{event_slug}/polls"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--polls', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--polls"
                    onclick="tryItOut('GETapi-events--event_slug--polls');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--polls"
                    onclick="cancelTryOut('GETapi-events--event_slug--polls');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--polls"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/polls</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--polls"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--polls"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--polls"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--polls">Create a Poll</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. A Poll opens closed: the window is set by opening it, not by creating it.</p>

<span id="example-requests-POSTapi-events--event_slug--polls">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Best Painted Army',
            'type' =&gt; 'painting',
            'votes_per_player' =&gt; 3,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Best Painted Army",
    "type": "painting",
    "votes_per_player": 3
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--polls">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 219,
        &quot;name&quot;: &quot;Best Painted Army&quot;,
        &quot;type&quot;: &quot;painting&quot;,
        &quot;votes_per_player&quot;: 1,
        &quot;opens_at&quot;: null,
        &quot;closes_at&quot;: null,
        &quot;is_open&quot;: false,
        &quot;is_open_for_me&quot;: null,
        &quot;my_ballot&quot;: []
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--polls" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--polls"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--polls"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--polls" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--polls">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--polls" data-method="POST"
      data-path="api/events/{event_slug}/polls"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--polls', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--polls"
                    onclick="tryItOut('POSTapi-events--event_slug--polls');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--polls"
                    onclick="cancelTryOut('POSTapi-events--event_slug--polls');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--polls"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/polls</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--polls"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--polls"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--polls"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-events--event_slug--polls"
               value="Best Painted Army"
               data-component="body">
    <br>
<p>What the Poll is called. Example: <code>Best Painted Army</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-events--event_slug--polls"
               value="painting"
               data-component="body">
    <br>
<p>Which Attendees may be picked: painting or favourite_opponent. Example: <code>painting</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>votes_per_player</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="votes_per_player"                data-endpoint="POSTapi-events--event_slug--polls"
               value="3"
               data-component="body">
    <br>
<p>How many Attendees each Player may pick. Defaults to one. Example: <code>3</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--polls--poll_id--open">Open a Poll</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Opens this Poll's voting window, independently of every other Poll.</p>

<span id="example-requests-POSTapi-events--event_slug--polls--poll_id--open">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls/1/open';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls/1/open"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--polls--poll_id--open">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 220,
        &quot;name&quot;: &quot;Best Painted Army&quot;,
        &quot;type&quot;: &quot;painting&quot;,
        &quot;votes_per_player&quot;: 1,
        &quot;opens_at&quot;: null,
        &quot;closes_at&quot;: null,
        &quot;is_open&quot;: false,
        &quot;is_open_for_me&quot;: null,
        &quot;my_ballot&quot;: []
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--polls--poll_id--open" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--polls--poll_id--open"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--polls--poll_id--open"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--polls--poll_id--open" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--polls--poll_id--open">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--polls--poll_id--open" data-method="POST"
      data-path="api/events/{event_slug}/polls/{poll_id}/open"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--polls--poll_id--open', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--polls--poll_id--open"
                    onclick="tryItOut('POSTapi-events--event_slug--polls--poll_id--open');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--polls--poll_id--open"
                    onclick="cancelTryOut('POSTapi-events--event_slug--polls--poll_id--open');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--polls--poll_id--open"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/polls/{poll_id}/open</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--open"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--open"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--open"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>poll_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="poll_id"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--open"
               value="1"
               data-component="url">
    <br>
<p>The id of the poll. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--polls--poll_id--candidates">List Poll Candidates</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The Attendees this Player may pick in this Poll: armies on the display table for a painting Poll, and the teams this Player actually played for a favourite-opponent Poll. A Bye shortens the list rather than appearing in it.</p>

<span id="example-requests-GETapi-events--event_slug--polls--poll_id--candidates">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls/1/candidates';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls/1/candidates"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--polls--poll_id--candidates">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 9,
            &quot;name&quot;: &quot;Ada and Grace&quot;,
            &quot;allegiance&quot;: &quot;loyalist&quot;,
            &quot;members&quot;: [
                {
                    &quot;id&quot;: 12,
                    &quot;name&quot;: &quot;Ada Lovelace&quot;,
                    &quot;faction&quot;: {
                        &quot;id&quot;: 3,
                        &quot;name&quot;: &quot;Sons of Horus&quot;
                    },
                    &quot;army_list_locked&quot;: true,
                    &quot;clubs&quot;: [
                        {
                            &quot;id&quot;: 2,
                            &quot;name&quot;: &quot;The Ordo Ludi&quot;
                        }
                    ]
                }
            ]
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--polls--poll_id--candidates" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--polls--poll_id--candidates"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--polls--poll_id--candidates"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--polls--poll_id--candidates" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--polls--poll_id--candidates">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--polls--poll_id--candidates" data-method="GET"
      data-path="api/events/{event_slug}/polls/{poll_id}/candidates"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--polls--poll_id--candidates', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--polls--poll_id--candidates"
                    onclick="tryItOut('GETapi-events--event_slug--polls--poll_id--candidates');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--polls--poll_id--candidates"
                    onclick="cancelTryOut('GETapi-events--event_slug--polls--poll_id--candidates');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--polls--poll_id--candidates"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/polls/{poll_id}/candidates</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--polls--poll_id--candidates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--polls--poll_id--candidates"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--polls--poll_id--candidates"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>poll_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="poll_id"                data-endpoint="GETapi-events--event_slug--polls--poll_id--candidates"
               value="1"
               data-component="url">
    <br>
<p>The id of the poll. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--polls--poll_id--results">Read Poll Tallies</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only, permanently — not live, not after close, not to Players. Winners are announced in the venue, and the announcement is an Event update. Ties come back unresolved: which of two equal armies wins is a judgement, not a rule to invent in code.</p>

<span id="example-requests-GETapi-events--event_slug--polls--poll_id--results">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls/1/results';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls/1/results"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--polls--poll_id--results">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;poll&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Best Painted Army&quot;,
            &quot;type&quot;: &quot;best_painted&quot;,
            &quot;is_open&quot;: false
        },
        &quot;tallies&quot;: [
            {
                &quot;attendee&quot;: {
                    &quot;id&quot;: 9,
                    &quot;name&quot;: &quot;Ada and Grace&quot;,
                    &quot;display_number&quot;: 4
                },
                &quot;votes&quot;: 11
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--polls--poll_id--results" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--polls--poll_id--results"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--polls--poll_id--results"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--polls--poll_id--results" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--polls--poll_id--results">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--polls--poll_id--results" data-method="GET"
      data-path="api/events/{event_slug}/polls/{poll_id}/results"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--polls--poll_id--results', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--polls--poll_id--results"
                    onclick="tryItOut('GETapi-events--event_slug--polls--poll_id--results');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--polls--poll_id--results"
                    onclick="cancelTryOut('GETapi-events--event_slug--polls--poll_id--results');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--polls--poll_id--results"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/polls/{poll_id}/results</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--polls--poll_id--results"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--polls--poll_id--results"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--polls--poll_id--results"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>poll_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="poll_id"                data-endpoint="GETapi-events--event_slug--polls--poll_id--results"
               value="1"
               data-component="url">
    <br>
<p>The id of the poll. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--polls--poll_id--close">Close a Poll</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Closes this Poll's voting window. Results stay Organiser-only afterwards: winners are announced in the venue.</p>

<span id="example-requests-POSTapi-events--event_slug--polls--poll_id--close">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls/1/close';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls/1/close"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--polls--poll_id--close">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 221,
        &quot;name&quot;: &quot;Best Painted Army&quot;,
        &quot;type&quot;: &quot;painting&quot;,
        &quot;votes_per_player&quot;: 1,
        &quot;opens_at&quot;: null,
        &quot;closes_at&quot;: null,
        &quot;is_open&quot;: false,
        &quot;is_open_for_me&quot;: null,
        &quot;my_ballot&quot;: []
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--polls--poll_id--close" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--polls--poll_id--close"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--polls--poll_id--close"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--polls--poll_id--close" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--polls--poll_id--close">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--polls--poll_id--close" data-method="POST"
      data-path="api/events/{event_slug}/polls/{poll_id}/close"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--polls--poll_id--close', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--polls--poll_id--close"
                    onclick="tryItOut('POSTapi-events--event_slug--polls--poll_id--close');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--polls--poll_id--close"
                    onclick="cancelTryOut('POSTapi-events--event_slug--polls--poll_id--close');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--polls--poll_id--close"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/polls/{poll_id}/close</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--close"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--close"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--close"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>poll_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="poll_id"                data-endpoint="POSTapi-events--event_slug--polls--poll_id--close"
               value="1"
               data-component="url">
    <br>
<p>The id of the poll. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-PUTapi-events--event_slug--polls--poll_id--ballot">Replace your Ballot</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Send the complete set of Attendees you are picking; an empty array clears your Ballot. In doubles both Players vote independently.</p>

<span id="example-requests-PUTapi-events--event_slug--polls--poll_id--ballot">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/polls/1/ballot';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'attendee_ids' =&gt; [4, 9],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/polls/1/ballot"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "attendee_ids": [
        4,
        9
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-events--event_slug--polls--poll_id--ballot">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;poll_id&quot;: 1,
        &quot;attendee_ids&quot;: [
            4,
            9
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The Poll is closed, or this Player may not vote in it.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Voting is not open for you in this poll.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-events--event_slug--polls--poll_id--ballot" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-events--event_slug--polls--poll_id--ballot"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-events--event_slug--polls--poll_id--ballot"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-events--event_slug--polls--poll_id--ballot" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-events--event_slug--polls--poll_id--ballot">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-events--event_slug--polls--poll_id--ballot" data-method="PUT"
      data-path="api/events/{event_slug}/polls/{poll_id}/ballot"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-events--event_slug--polls--poll_id--ballot', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-events--event_slug--polls--poll_id--ballot"
                    onclick="tryItOut('PUTapi-events--event_slug--polls--poll_id--ballot');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-events--event_slug--polls--poll_id--ballot"
                    onclick="cancelTryOut('PUTapi-events--event_slug--polls--poll_id--ballot');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-events--event_slug--polls--poll_id--ballot"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/events/{event_slug}/polls/{poll_id}/ballot</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>poll_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="poll_id"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               value="1"
               data-component="url">
    <br>
<p>The id of the poll. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>attendee_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_ids[0]"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               data-component="body">
        <input type="number" style="display: none"
               name="attendee_ids[1]"                data-endpoint="PUTapi-events--event_slug--polls--poll_id--ballot"
               data-component="body">
    <br>
<p>The complete Ballot: every Attendee this Player is picking. An empty array clears it.</p>
        </div>
        </form>

                    <h2 id="events-PATCHapi-events--event_slug--attendees--attendee_id--painting">Mark a Painting Entry</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>A Player enters their own army; an Organiser enters anyone's and assigns the display number. Entry and display number are separate fields, so someone walking the display table can tick teams off one-handed and number them later.</p>

<span id="example-requests-PATCHapi-events--event_slug--attendees--attendee_id--painting">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1/painting';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'painting_entered' =&gt; true,
            'display_number' =&gt; 14,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1/painting"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "painting_entered": true,
    "display_number": 14
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-events--event_slug--attendees--attendee_id--painting">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list_locked&quot;: true,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-events--event_slug--attendees--attendee_id--painting" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-events--event_slug--attendees--attendee_id--painting"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-events--event_slug--attendees--attendee_id--painting"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-events--event_slug--attendees--attendee_id--painting" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-events--event_slug--attendees--attendee_id--painting">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-events--event_slug--attendees--attendee_id--painting" data-method="PATCH"
      data-path="api/events/{event_slug}/attendees/{attendee_id}/painting"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-events--event_slug--attendees--attendee_id--painting', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-events--event_slug--attendees--attendee_id--painting"
                    onclick="tryItOut('PATCHapi-events--event_slug--attendees--attendee_id--painting');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-events--event_slug--attendees--attendee_id--painting"
                    onclick="cancelTryOut('PATCHapi-events--event_slug--attendees--attendee_id--painting');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-events--event_slug--attendees--attendee_id--painting"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/events/{event_slug}/attendees/{attendee_id}/painting</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>painting_entered</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting" style="display: none">
            <input type="radio" name="painting_entered"
                   value="true"
                   data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting" style="display: none">
            <input type="radio" name="painting_entered"
                   value="false"
                   data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Whether this Attendee has an army on the display table. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>display_number</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="display_number"                data-endpoint="PATCHapi-events--event_slug--attendees--attendee_id--painting"
               value="14"
               data-component="body">
    <br>
<p>The number their army sits under. Independent of entry: teams get ticked off before anyone numbers them. Example: <code>14</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--schedule">Add a Schedule Block</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. The day the block appears under is derived from its start time in the Event timezone.</p>

<span id="example-requests-POSTapi-events--event_slug--schedule">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/schedule';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'label' =&gt; 'Round 1',
            'type' =&gt; 'round',
            'starts_at' =&gt; '2026-07-11T09:00:00+01:00',
            'ends_at' =&gt; '2026-07-11T11:30:00+01:00',
            'display_order' =&gt; 39,
            'round_id' =&gt; 4,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/schedule"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "label": "Round 1",
    "type": "round",
    "starts_at": "2026-07-11T09:00:00+01:00",
    "ends_at": "2026-07-11T11:30:00+01:00",
    "display_order": 39,
    "round_id": 4
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--schedule">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 84,
        &quot;label&quot;: &quot;Awards&quot;,
        &quot;type&quot;: &quot;info&quot;,
        &quot;starts_at&quot;: &quot;2026-09-01T08:09:53+00:00&quot;,
        &quot;ends_at&quot;: &quot;2026-09-01T09:09:53+00:00&quot;,
        &quot;display_order&quot;: 0,
        &quot;target_id&quot;: null,
        &quot;is_target_live&quot;: false,
        &quot;round&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--schedule" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--schedule"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--schedule"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--schedule" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--schedule">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--schedule" data-method="POST"
      data-path="api/events/{event_slug}/schedule"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--schedule', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--schedule"
                    onclick="tryItOut('POSTapi-events--event_slug--schedule');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--schedule"
                    onclick="cancelTryOut('POSTapi-events--event_slug--schedule');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--schedule"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/schedule</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>label</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="label"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="Round 1"
               data-component="body">
    <br>
<p>What the block is called on the schedule. Example: <code>Round 1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="round"
               data-component="body">
    <br>
<p>One of info, round, painting_voting. Example: <code>round</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="2026-07-11T09:00:00+01:00"
               data-component="body">
    <br>
<p>When the block starts, as an ISO 8601 timestamp. Example: <code>2026-07-11T09:00:00+01:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ends_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ends_at"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="2026-07-11T11:30:00+01:00"
               data-component="body">
    <br>
<p>When the block ends, as an ISO 8601 timestamp. Example: <code>2026-07-11T11:30:00+01:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>display_order</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="display_order"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="39"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>39</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>round_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round_id"                data-endpoint="POSTapi-events--event_slug--schedule"
               value="4"
               data-component="body">
    <br>
<p>The Round this block runs. Required for a round block, and rejected on any other type. Example: <code>4</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--schedule-reorder">Reorder Schedule Blocks</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Sets the order blocks appear in when they start at the same time — two things at ten o'clock still need an order on the page.</p>

<span id="example-requests-POSTapi-events--event_slug--schedule-reorder">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/schedule/reorder';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'block_ids' =&gt; [3, 1, 2],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/schedule/reorder"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "block_ids": [
        3,
        1,
        2
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--schedule-reorder">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;block_ids&quot;: [
            3,
            1,
            2
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--schedule-reorder" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--schedule-reorder"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--schedule-reorder"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--schedule-reorder" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--schedule-reorder">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--schedule-reorder" data-method="POST"
      data-path="api/events/{event_slug}/schedule/reorder"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--schedule-reorder', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--schedule-reorder"
                    onclick="tryItOut('POSTapi-events--event_slug--schedule-reorder');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--schedule-reorder"
                    onclick="cancelTryOut('POSTapi-events--event_slug--schedule-reorder');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--schedule-reorder"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/schedule/reorder</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--schedule-reorder"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--schedule-reorder"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--schedule-reorder"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>block_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="block_ids[0]"                data-endpoint="POSTapi-events--event_slug--schedule-reorder"
               data-component="body">
        <input type="number" style="display: none"
               name="block_ids[1]"                data-endpoint="POSTapi-events--event_slug--schedule-reorder"
               data-component="body">
    <br>
<p>The block ids in the order they should appear.</p>
        </div>
        </form>

                    <h2 id="events-PATCHapi-events--event_slug--schedule--block_id-">Edit a Schedule Block</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Moving a block across midnight moves it to the other day, because the day is derived rather than stored.</p>

<span id="example-requests-PATCHapi-events--event_slug--schedule--block_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/schedule/1';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'label' =&gt; 'Round 1',
            'type' =&gt; 'round',
            'starts_at' =&gt; '2026-07-11T09:00:00+01:00',
            'ends_at' =&gt; '2026-07-11T11:30:00+01:00',
            'display_order' =&gt; 39,
            'round_id' =&gt; 4,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/schedule/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "label": "Round 1",
    "type": "round",
    "starts_at": "2026-07-11T09:00:00+01:00",
    "ends_at": "2026-07-11T11:30:00+01:00",
    "display_order": 39,
    "round_id": 4
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-events--event_slug--schedule--block_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 85,
        &quot;label&quot;: &quot;Awards&quot;,
        &quot;type&quot;: &quot;info&quot;,
        &quot;starts_at&quot;: &quot;2026-09-01T08:09:53+00:00&quot;,
        &quot;ends_at&quot;: &quot;2026-09-01T09:09:53+00:00&quot;,
        &quot;display_order&quot;: 0,
        &quot;target_id&quot;: null,
        &quot;is_target_live&quot;: false,
        &quot;round&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-events--event_slug--schedule--block_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-events--event_slug--schedule--block_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-events--event_slug--schedule--block_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-events--event_slug--schedule--block_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-events--event_slug--schedule--block_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-events--event_slug--schedule--block_id-" data-method="PATCH"
      data-path="api/events/{event_slug}/schedule/{block_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-events--event_slug--schedule--block_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-events--event_slug--schedule--block_id-"
                    onclick="tryItOut('PATCHapi-events--event_slug--schedule--block_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-events--event_slug--schedule--block_id-"
                    onclick="cancelTryOut('PATCHapi-events--event_slug--schedule--block_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-events--event_slug--schedule--block_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/events/{event_slug}/schedule/{block_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>block_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="block_id"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the schedule block. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>label</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="label"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="Round 1"
               data-component="body">
    <br>
<p>What the block is called on the schedule. Example: <code>Round 1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="round"
               data-component="body">
    <br>
<p>One of info, round, painting_voting. Example: <code>round</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>starts_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="starts_at"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="2026-07-11T09:00:00+01:00"
               data-component="body">
    <br>
<p>When the block starts, as an ISO 8601 timestamp. Example: <code>2026-07-11T09:00:00+01:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>ends_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="ends_at"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="2026-07-11T11:30:00+01:00"
               data-component="body">
    <br>
<p>When the block ends, as an ISO 8601 timestamp. Example: <code>2026-07-11T11:30:00+01:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>display_order</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="display_order"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="39"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>39</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>round_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round_id"                data-endpoint="PATCHapi-events--event_slug--schedule--block_id-"
               value="4"
               data-component="body">
    <br>
<p>The Round this block runs. Required for a round block, and rejected on any other type. Example: <code>4</code></p>
        </div>
        </form>

                    <h2 id="events-DELETEapi-events--event_slug--schedule--block_id-">Delete a Schedule Block</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only.</p>

<span id="example-requests-DELETEapi-events--event_slug--schedule--block_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/schedule/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/schedule/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-events--event_slug--schedule--block_id-">
            <blockquote>
            <p>Example response (204):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-events--event_slug--schedule--block_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-events--event_slug--schedule--block_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-events--event_slug--schedule--block_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-events--event_slug--schedule--block_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-events--event_slug--schedule--block_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-events--event_slug--schedule--block_id-" data-method="DELETE"
      data-path="api/events/{event_slug}/schedule/{block_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-events--event_slug--schedule--block_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-events--event_slug--schedule--block_id-"
                    onclick="tryItOut('DELETEapi-events--event_slug--schedule--block_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-events--event_slug--schedule--block_id-"
                    onclick="cancelTryOut('DELETEapi-events--event_slug--schedule--block_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-events--event_slug--schedule--block_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/events/{event_slug}/schedule/{block_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-events--event_slug--schedule--block_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-events--event_slug--schedule--block_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="DELETEapi-events--event_slug--schedule--block_id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>block_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="block_id"                data-endpoint="DELETEapi-events--event_slug--schedule--block_id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the schedule block. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--my-game">Show My Current Game</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The authenticated Player's Game and table number in the current Round. Null until that Round is published.</p>

<span id="example-requests-GETapi-events--event_slug--my-game">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/my-game';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/my-game"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--my-game">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 18,
        &quot;table_number&quot;: 5,
        &quot;is_bye&quot;: false,
        &quot;round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2,
            &quot;name&quot;: &quot;Round 2&quot;
        },
        &quot;result&quot;: {
            &quot;submitted_at&quot;: &quot;2026-09-12T14:05:00+00:00&quot;,
            &quot;submitted_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;edited_at&quot;: null,
            &quot;edited_by&quot;: null,
            &quot;is_flagged&quot;: false
        },
        &quot;attendees&quot;: [
            {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list_locked&quot;: true,
                        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;
                    }
                ],
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, The current Round is not published, or this Player is not playing in it.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: null
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--my-game" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--my-game"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--my-game"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--my-game" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--my-game">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--my-game" data-method="GET"
      data-path="api/events/{event_slug}/my-game"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--my-game', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--my-game"
                    onclick="tryItOut('GETapi-events--event_slug--my-game');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--my-game"
                    onclick="cancelTryOut('GETapi-events--event_slug--my-game');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--my-game"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/my-game</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--my-game"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--my-game"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--my-game"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-PATCHapi-events--event_slug--my-faction">Record My Faction</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The Faction this Player is bringing. Personal to the Player, not the party: a doubles team fields two Factions under one Allegiance.</p>

<span id="example-requests-PATCHapi-events--event_slug--my-faction">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/my-faction';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'faction_id' =&gt; 3,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/my-faction"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "faction_id": 3
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-events--event_slug--my-faction">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ],
        &quot;checked_in_at&quot;: null,
        &quot;custom_field_responses&quot;: [],
        &quot;games&quot;: []
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, This Player has not entered this Event.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Not Found.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-events--event_slug--my-faction" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-events--event_slug--my-faction"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-events--event_slug--my-faction"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-events--event_slug--my-faction" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-events--event_slug--my-faction">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-events--event_slug--my-faction" data-method="PATCH"
      data-path="api/events/{event_slug}/my-faction"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-events--event_slug--my-faction', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-events--event_slug--my-faction"
                    onclick="tryItOut('PATCHapi-events--event_slug--my-faction');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-events--event_slug--my-faction"
                    onclick="cancelTryOut('PATCHapi-events--event_slug--my-faction');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-events--event_slug--my-faction"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/events/{event_slug}/my-faction</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-events--event_slug--my-faction"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-events--event_slug--my-faction"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PATCHapi-events--event_slug--my-faction"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>faction_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="faction_id"                data-endpoint="PATCHapi-events--event_slug--my-faction"
               value="3"
               data-component="body">
    <br>
<p>The Faction this Player is bringing, or null to withdraw the choice. Example: <code>3</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--games--game_id--result">Submit a Game Result</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Either Player in a Game submits scores for both Attendees. The first submission wins and locks the Game: a later one is rejected and the result has to be flagged for an Organiser instead. Derived Score Types such as Match Points are computed server-side.</p>

<span id="example-requests-POSTapi-events--event_slug--games--game_id--result">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/games/1/result';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'scores' =&gt; [
                1 =&gt; ['victory-points' =&gt; 85],
                ['victory-points' =&gt; 70],
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/games/1/result"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "scores": {
        "1": {
            "victory-points": 85
        },
        "2": {
            "victory-points": 70
        }
    }
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--games--game_id--result">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 18,
        &quot;table_number&quot;: 5,
        &quot;is_bye&quot;: false,
        &quot;round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2,
            &quot;name&quot;: &quot;Round 2&quot;
        },
        &quot;result&quot;: {
            &quot;submitted_at&quot;: &quot;2026-09-12T14:05:00+00:00&quot;,
            &quot;submitted_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;edited_at&quot;: null,
            &quot;edited_by&quot;: null,
            &quot;is_flagged&quot;: false
        },
        &quot;attendees&quot;: [
            {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list_locked&quot;: true,
                        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;
                    }
                ],
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (409, A result already exists. The body carries the Game as it stands, so a client whose own submission lost its response can recognise it rather than reporting a dispute.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;A result has already been submitted for this game. Flag it if it needs correcting.&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 18,
        &quot;table_number&quot;: 5,
        &quot;is_bye&quot;: false,
        &quot;round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2,
            &quot;name&quot;: &quot;Round 2&quot;
        },
        &quot;result&quot;: {
            &quot;submitted_at&quot;: &quot;2026-09-12T14:05:00+00:00&quot;,
            &quot;submitted_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;edited_at&quot;: null,
            &quot;edited_by&quot;: null,
            &quot;is_flagged&quot;: false
        },
        &quot;attendees&quot;: [
            {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list_locked&quot;: true,
                        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;
                    }
                ],
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--games--game_id--result" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--games--game_id--result"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--games--game_id--result"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--games--game_id--result" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--games--game_id--result">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--games--game_id--result" data-method="POST"
      data-path="api/events/{event_slug}/games/{game_id}/result"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--games--game_id--result', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--games--game_id--result"
                    onclick="tryItOut('POSTapi-events--event_slug--games--game_id--result');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--games--game_id--result"
                    onclick="cancelTryOut('POSTapi-events--event_slug--games--game_id--result');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--games--game_id--result"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/games/{game_id}/result</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--games--game_id--result"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--games--game_id--result"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--games--game_id--result"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game_id"                data-endpoint="POSTapi-events--event_slug--games--game_id--result"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scores</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scores"                data-endpoint="POSTapi-events--event_slug--games--game_id--result"
               value=""
               data-component="body">
    <br>
<p>Scores keyed by Attendee id, then by Score Type slug. Every Attendee in the Game must be present, and derived Score Types are rejected.</p>
        </div>
        </form>

                    <h2 id="events-PUTapi-events--event_slug--games--game_id--result">Correct a Game Result</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only, at any point and in any Round. Correcting is separate from resolving a flag: an Organiser who corrects a result still has to close the flag, and one who finds the result was right can close it without touching the scores.</p>

<span id="example-requests-PUTapi-events--event_slug--games--game_id--result">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/games/1/result';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'scores' =&gt; [
                1 =&gt; ['victory-points' =&gt; 85],
                ['victory-points' =&gt; 70],
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/games/1/result"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "scores": {
        "1": {
            "victory-points": 85
        },
        "2": {
            "victory-points": 70
        }
    }
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-events--event_slug--games--game_id--result">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 18,
        &quot;table_number&quot;: 5,
        &quot;is_bye&quot;: false,
        &quot;round&quot;: {
            &quot;id&quot;: 4,
            &quot;number&quot;: 2,
            &quot;name&quot;: &quot;Round 2&quot;
        },
        &quot;result&quot;: {
            &quot;submitted_at&quot;: &quot;2026-09-12T14:05:00+00:00&quot;,
            &quot;submitted_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;edited_at&quot;: null,
            &quot;edited_by&quot;: null,
            &quot;is_flagged&quot;: false
        },
        &quot;attendees&quot;: [
            {
                &quot;id&quot;: 9,
                &quot;name&quot;: &quot;Ada and Grace&quot;,
                &quot;members&quot;: [
                    {
                        &quot;id&quot;: 12,
                        &quot;name&quot;: &quot;Ada Lovelace&quot;,
                        &quot;faction&quot;: {
                            &quot;id&quot;: 3,
                            &quot;name&quot;: &quot;Sons of Horus&quot;
                        },
                        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;
                    }
                ],
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-events--event_slug--games--game_id--result" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-events--event_slug--games--game_id--result"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-events--event_slug--games--game_id--result"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-events--event_slug--games--game_id--result" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-events--event_slug--games--game_id--result">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-events--event_slug--games--game_id--result" data-method="PUT"
      data-path="api/events/{event_slug}/games/{game_id}/result"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-events--event_slug--games--game_id--result', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-events--event_slug--games--game_id--result"
                    onclick="tryItOut('PUTapi-events--event_slug--games--game_id--result');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-events--event_slug--games--game_id--result"
                    onclick="cancelTryOut('PUTapi-events--event_slug--games--game_id--result');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-events--event_slug--games--game_id--result"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/events/{event_slug}/games/{game_id}/result</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-events--event_slug--games--game_id--result"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-events--event_slug--games--game_id--result"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PUTapi-events--event_slug--games--game_id--result"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game_id"                data-endpoint="PUTapi-events--event_slug--games--game_id--result"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scores</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scores"                data-endpoint="PUTapi-events--event_slug--games--game_id--result"
               value=""
               data-component="body">
    <br>
<p>Scores keyed by Attendee id, then by Score Type slug. Every Attendee in the Game must be present, and derived Score Types are rejected.</p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--games--game_id--flag">Flag a Game Result</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>A Player in the Game, or an Organiser, claims the submitted result is wrong. Flagging again while a flag is open returns the open flag rather than raising a second one.</p>

<span id="example-requests-POSTapi-events--event_slug--games--game_id--flag">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/games/1/flag';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'reason' =&gt; 'We agreed 85-70 the other way round.',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/games/1/flag"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "reason": "We agreed 85-70 the other way round."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--games--game_id--flag">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 3,
        &quot;game_id&quot;: 18,
        &quot;reason&quot;: &quot;We agreed 85-70 but it went in the other way round.&quot;,
        &quot;flagged_at&quot;: &quot;2026-09-12T14:20:00+00:00&quot;,
        &quot;flagged_by&quot;: {
            &quot;id&quot;: 12,
            &quot;name&quot;: &quot;Ada Lovelace&quot;
        },
        &quot;resolved_at&quot;: null,
        &quot;resolved_by&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--games--game_id--flag" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--games--game_id--flag"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--games--game_id--flag"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--games--game_id--flag" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--games--game_id--flag">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--games--game_id--flag" data-method="POST"
      data-path="api/events/{event_slug}/games/{game_id}/flag"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--games--game_id--flag', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--games--game_id--flag"
                    onclick="tryItOut('POSTapi-events--event_slug--games--game_id--flag');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--games--game_id--flag"
                    onclick="cancelTryOut('POSTapi-events--event_slug--games--game_id--flag');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--games--game_id--flag"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/games/{game_id}/flag</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game_id"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>reason</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="reason"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag"
               value="We agreed 85-70 the other way round."
               data-component="body">
    <br>
<p>Why the result is wrong. Optional, but it is what an Organiser adjudicates on. Example: <code>We agreed 85-70 the other way round.</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--games--game_id--flag-resolve">Resolve a Flagged Result</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Closes the open flag on a Game. Resolving is deliberately separate from editing: an Organiser who checks a flag and finds the original result was right still needs a way to clear it.</p>

<span id="example-requests-POSTapi-events--event_slug--games--game_id--flag-resolve">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/games/1/flag/resolve';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/games/1/flag/resolve"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--games--game_id--flag-resolve">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 3,
        &quot;game_id&quot;: 18,
        &quot;reason&quot;: &quot;We agreed 85-70 but it went in the other way round.&quot;,
        &quot;flagged_at&quot;: &quot;2026-09-12T14:20:00+00:00&quot;,
        &quot;flagged_by&quot;: {
            &quot;id&quot;: 12,
            &quot;name&quot;: &quot;Ada Lovelace&quot;
        },
        &quot;resolved_at&quot;: null,
        &quot;resolved_by&quot;: null
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--games--game_id--flag-resolve" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--games--game_id--flag-resolve"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--games--game_id--flag-resolve"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--games--game_id--flag-resolve" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--games--game_id--flag-resolve">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--games--game_id--flag-resolve" data-method="POST"
      data-path="api/events/{event_slug}/games/{game_id}/flag/resolve"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--games--game_id--flag-resolve', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--games--game_id--flag-resolve"
                    onclick="tryItOut('POSTapi-events--event_slug--games--game_id--flag-resolve');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--games--game_id--flag-resolve"
                    onclick="cancelTryOut('POSTapi-events--event_slug--games--game_id--flag-resolve');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--games--game_id--flag-resolve"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/games/{game_id}/flag/resolve</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag-resolve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag-resolve"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag-resolve"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game_id"                data-endpoint="POSTapi-events--event_slug--games--game_id--flag-resolve"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--flags">List Flagged Results</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. The open flags on this Event, oldest first, with the Game and its current scores.</p>

<span id="example-requests-GETapi-events--event_slug--flags">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/flags';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/flags"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-events--event_slug--flags">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 3,
            &quot;game_id&quot;: 18,
            &quot;reason&quot;: &quot;We agreed 85-70 but it went in the other way round.&quot;,
            &quot;flagged_at&quot;: &quot;2026-09-12T14:20:00+00:00&quot;,
            &quot;flagged_by&quot;: {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;
            },
            &quot;game&quot;: {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;round&quot;: {
                    &quot;id&quot;: 4,
                    &quot;number&quot;: 2,
                    &quot;name&quot;: &quot;Round 2&quot;
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            },
            &quot;resolved_at&quot;: null,
            &quot;resolved_by&quot;: null
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-events--event_slug--flags" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-events--event_slug--flags"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-events--event_slug--flags"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-events--event_slug--flags" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-events--event_slug--flags">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-events--event_slug--flags" data-method="GET"
      data-path="api/events/{event_slug}/flags"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-events--event_slug--flags', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-events--event_slug--flags"
                    onclick="tryItOut('GETapi-events--event_slug--flags');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-events--event_slug--flags"
                    onclick="cancelTryOut('GETapi-events--event_slug--flags');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-events--event_slug--flags"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/events/{event_slug}/flags</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-events--event_slug--flags"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-events--event_slug--flags"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="GETapi-events--event_slug--flags"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--rounds">Generate the next Round</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Pairs the field into a new Draft Round. Rejected while the current Round is unpublished or has results outstanding, and once the Event's round count is reached.</p>

<span id="example-requests-POSTapi-events--event_slug--rounds">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--rounds">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 4,
        &quot;number&quot;: 2,
        &quot;name&quot;: &quot;Round 2&quot;,
        &quot;status&quot;: &quot;live&quot;,
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;is_rematch&quot;: false,
                &quot;result&quot;: {
                    &quot;submitted_at&quot;: null,
                    &quot;is_flagged&quot;: false
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;allegiance&quot;: &quot;loyalist&quot;,
                        &quot;members&quot;: [
                            {
                                &quot;id&quot;: 12,
                                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                                &quot;faction&quot;: {
                                    &quot;id&quot;: 3,
                                    &quot;name&quot;: &quot;Sons of Horus&quot;
                                },
                                &quot;army_list_locked&quot;: true
                            }
                        ],
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--rounds" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--rounds"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--rounds"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--rounds" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--rounds">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--rounds" data-method="POST"
      data-path="api/events/{event_slug}/rounds"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--rounds', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--rounds"
                    onclick="tryItOut('POSTapi-events--event_slug--rounds');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--rounds"
                    onclick="cancelTryOut('POSTapi-events--event_slug--rounds');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--rounds"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/rounds</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--rounds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--rounds"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--rounds"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--rounds--round_id--publish">Publish a Round</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Makes the Round's pairings and table numbers visible to Players. Earlier Rounds stay Live.</p>

<span id="example-requests-POSTapi-events--event_slug--rounds--round_id--publish">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds/1/publish';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds/1/publish"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--rounds--round_id--publish">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 4,
        &quot;number&quot;: 2,
        &quot;name&quot;: &quot;Round 2&quot;,
        &quot;status&quot;: &quot;live&quot;,
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;is_rematch&quot;: false,
                &quot;result&quot;: {
                    &quot;submitted_at&quot;: null,
                    &quot;is_flagged&quot;: false
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;allegiance&quot;: &quot;loyalist&quot;,
                        &quot;members&quot;: [
                            {
                                &quot;id&quot;: 12,
                                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                                &quot;faction&quot;: {
                                    &quot;id&quot;: 3,
                                    &quot;name&quot;: &quot;Sons of Horus&quot;
                                },
                                &quot;army_list_locked&quot;: true
                            }
                        ],
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--rounds--round_id--publish" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--rounds--round_id--publish"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--rounds--round_id--publish"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--rounds--round_id--publish" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--rounds--round_id--publish">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--rounds--round_id--publish" data-method="POST"
      data-path="api/events/{event_slug}/rounds/{round_id}/publish"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--rounds--round_id--publish', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--rounds--round_id--publish"
                    onclick="tryItOut('POSTapi-events--event_slug--rounds--round_id--publish');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--rounds--round_id--publish"
                    onclick="cancelTryOut('POSTapi-events--event_slug--rounds--round_id--publish');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--rounds--round_id--publish"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/rounds/{round_id}/publish</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--publish"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--publish"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--publish"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>round_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round_id"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--publish"
               value="1"
               data-component="url">
    <br>
<p>The id of the round. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--rounds--round_id--swap">Swap two Pairings</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only, on a Draft Round. Exchanges the same-allegiance side between two Games, or moves the Bye when one of them is a Bye. Table numbers stay with the Game, and rematch flags are recomputed.</p>

<span id="example-requests-POSTapi-events--event_slug--rounds--round_id--swap">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds/1/swap';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'game_ids' =&gt; [12, 15],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds/1/swap"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "game_ids": [
        12,
        15
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--rounds--round_id--swap">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 4,
        &quot;number&quot;: 2,
        &quot;name&quot;: &quot;Round 2&quot;,
        &quot;status&quot;: &quot;live&quot;,
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;is_rematch&quot;: false,
                &quot;result&quot;: {
                    &quot;submitted_at&quot;: null,
                    &quot;is_flagged&quot;: false
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;allegiance&quot;: &quot;loyalist&quot;,
                        &quot;members&quot;: [
                            {
                                &quot;id&quot;: 12,
                                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                                &quot;faction&quot;: {
                                    &quot;id&quot;: 3,
                                    &quot;name&quot;: &quot;Sons of Horus&quot;
                                },
                                &quot;army_list_locked&quot;: true
                            }
                        ],
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--rounds--round_id--swap" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--rounds--round_id--swap"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--rounds--round_id--swap"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--rounds--round_id--swap" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--rounds--round_id--swap">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--rounds--round_id--swap" data-method="POST"
      data-path="api/events/{event_slug}/rounds/{round_id}/swap"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--rounds--round_id--swap', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--rounds--round_id--swap"
                    onclick="tryItOut('POSTapi-events--event_slug--rounds--round_id--swap');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--rounds--round_id--swap"
                    onclick="cancelTryOut('POSTapi-events--event_slug--rounds--round_id--swap');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--rounds--round_id--swap"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/rounds/{round_id}/swap</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>round_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round_id"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               value="1"
               data-component="url">
    <br>
<p>The id of the round. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>game_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game_ids[0]"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               data-component="body">
        <input type="number" style="display: none"
               name="game_ids[1]"                data-endpoint="POSTapi-events--event_slug--rounds--round_id--swap"
               data-component="body">
    <br>
<p>The two Games to recombine. The exchange itself is not a choice: the system performs the only legal one.</p>
        </div>
        </form>

                    <h2 id="events-DELETEapi-events--event_slug--rounds--round_id--publish">Unpublish a Round</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Returns a Round to Draft so a broken pairing can be fixed out of sight. Rejected once any result exists.</p>

<span id="example-requests-DELETEapi-events--event_slug--rounds--round_id--publish">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/rounds/1/publish';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/rounds/1/publish"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-events--event_slug--rounds--round_id--publish">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 4,
        &quot;number&quot;: 2,
        &quot;name&quot;: &quot;Round 2&quot;,
        &quot;status&quot;: &quot;live&quot;,
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;is_rematch&quot;: false,
                &quot;result&quot;: {
                    &quot;submitted_at&quot;: null,
                    &quot;is_flagged&quot;: false
                },
                &quot;attendees&quot;: [
                    {
                        &quot;id&quot;: 9,
                        &quot;name&quot;: &quot;Ada and Grace&quot;,
                        &quot;allegiance&quot;: &quot;loyalist&quot;,
                        &quot;members&quot;: [
                            {
                                &quot;id&quot;: 12,
                                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                                &quot;faction&quot;: {
                                    &quot;id&quot;: 3,
                                    &quot;name&quot;: &quot;Sons of Horus&quot;
                                },
                                &quot;army_list_locked&quot;: true
                            }
                        ],
                        &quot;scores&quot;: {
                            &quot;match-points&quot;: 3,
                            &quot;victory-points&quot;: 85
                        }
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-events--event_slug--rounds--round_id--publish" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-events--event_slug--rounds--round_id--publish"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-events--event_slug--rounds--round_id--publish"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-events--event_slug--rounds--round_id--publish" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-events--event_slug--rounds--round_id--publish">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-events--event_slug--rounds--round_id--publish" data-method="DELETE"
      data-path="api/events/{event_slug}/rounds/{round_id}/publish"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-events--event_slug--rounds--round_id--publish', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-events--event_slug--rounds--round_id--publish"
                    onclick="tryItOut('DELETEapi-events--event_slug--rounds--round_id--publish');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-events--event_slug--rounds--round_id--publish"
                    onclick="cancelTryOut('DELETEapi-events--event_slug--rounds--round_id--publish');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-events--event_slug--rounds--round_id--publish"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/events/{event_slug}/rounds/{round_id}/publish</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-events--event_slug--rounds--round_id--publish"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-events--event_slug--rounds--round_id--publish"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="DELETEapi-events--event_slug--rounds--round_id--publish"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>round_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round_id"                data-endpoint="DELETEapi-events--event_slug--rounds--round_id--publish"
               value="1"
               data-component="url">
    <br>
<p>The id of the round. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-PUTapi-events--event_slug--army-list">Submit Your Army List</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Submitting locks the list. Only an Organiser can reopen it.</p>

<span id="example-requests-PUTapi-events--event_slug--army-list">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/army-list';
$response = $client-&gt;put(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'army_list' =&gt; '2000pts Ultramarines...',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/army-list"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "army_list": "2000pts Ultramarines..."
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-events--event_slug--army-list">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
        &quot;submitted_at&quot;: &quot;2026-09-10T18:30:00+00:00&quot;,
        &quot;is_locked&quot;: false
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PUTapi-events--event_slug--army-list" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-events--event_slug--army-list"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-events--event_slug--army-list"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-events--event_slug--army-list" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-events--event_slug--army-list">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-events--event_slug--army-list" data-method="PUT"
      data-path="api/events/{event_slug}/army-list"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-events--event_slug--army-list', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-events--event_slug--army-list"
                    onclick="tryItOut('PUTapi-events--event_slug--army-list');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-events--event_slug--army-list"
                    onclick="cancelTryOut('PUTapi-events--event_slug--army-list');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-events--event_slug--army-list"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/events/{event_slug}/army-list</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-events--event_slug--army-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-events--event_slug--army-list"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PUTapi-events--event_slug--army-list"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>army_list</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="army_list"                data-endpoint="PUTapi-events--event_slug--army-list"
               value="2000pts Ultramarines..."
               data-component="body">
    <br>
<p>The list as free text. No format is imposed. Example: <code>2000pts Ultramarines...</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">Reveal a Team&#039;s Army Lists</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Frees a team held hostage by a Player who never submitted.</p>

<span id="example-requests-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1/army-lists/reveal';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1/army-lists/reveal"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list_locked&quot;: true,
                &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ],
        &quot;checked_in_at&quot;: null,
        &quot;custom_field_responses&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Dietary requirements&quot;,
                &quot;type&quot;: &quot;text&quot;,
                &quot;value&quot;: &quot;None&quot;
            }
        ],
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;round_number&quot;: 2,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                },
                &quot;opponents&quot;: [
                    {
                        &quot;id&quot;: 11,
                        &quot;name&quot;: &quot;Grace and Alan&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal" data-method="POST"
      data-path="api/events/{event_slug}/attendees/{attendee_id}/army-lists/reveal"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
                    onclick="tryItOut('POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
                    onclick="cancelTryOut('POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/attendees/{attendee_id}/army-lists/reveal</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--army-lists-reveal"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">Unlock an Army List</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Organisers only. Locking has no other escape, and a wrong list matters for every opponent who prepares against it.</p>

<span id="example-requests-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1/members/1/army-list/unlock';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1/members/1/army-list/unlock"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
        &quot;submitted_at&quot;: &quot;2026-09-10T18:30:00+00:00&quot;,
        &quot;is_locked&quot;: false
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock" data-method="POST"
      data-path="api/events/{event_slug}/attendees/{attendee_id}/members/{member_id}/army-list/unlock"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
                    onclick="tryItOut('POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
                    onclick="cancelTryOut('POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/attendees/{attendee_id}/members/{member_id}/army-list/unlock</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>member_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="member_id"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members--member_id--army-list-unlock"
               value="1"
               data-component="url">
    <br>
<p>The id of the Player whose list to reopen. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-POSTapi-events--event_slug--attendees">Register a Team</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Enters a party for the Event, inviting any Player who has no account yet.</p>

<span id="example-requests-POSTapi-events--event_slug--attendees">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Sons of Terra',
            'allegiance' =&gt; 'loyalist',
            'players' =&gt; [
                ['name' =&gt; 'Tarik Torgaddon', 'email' =&gt; 'tarik@example.com', 'faction_id' =&gt; 1, 'army_list' =&gt; 'architecto'],
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Sons of Terra",
    "allegiance": "loyalist",
    "players": [
        {
            "name": "Tarik Torgaddon",
            "email": "tarik@example.com",
            "faction_id": 1,
            "army_list": "architecto"
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--attendees">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list_locked&quot;: true,
                &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ],
        &quot;checked_in_at&quot;: null,
        &quot;custom_field_responses&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Dietary requirements&quot;,
                &quot;type&quot;: &quot;text&quot;,
                &quot;value&quot;: &quot;None&quot;
            }
        ],
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;round_number&quot;: 2,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                },
                &quot;opponents&quot;: [
                    {
                        &quot;id&quot;: 11,
                        &quot;name&quot;: &quot;Grace and Alan&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (409, The last place went while this registration was in flight.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;London Grand Tournament is full. Ask an organiser whether there is a waiting list.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--attendees" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--attendees"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--attendees"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--attendees" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--attendees">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--attendees" data-method="POST"
      data-path="api/events/{event_slug}/attendees"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--attendees', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--attendees"
                    onclick="tryItOut('POSTapi-events--event_slug--attendees');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--attendees"
                    onclick="cancelTryOut('POSTapi-events--event_slug--attendees');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--attendees"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/attendees</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="Sons of Terra"
               data-component="body">
    <br>
<p>The name the party competes under. Example: <code>Sons of Terra</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>allegiance</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="allegiance"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="loyalist"
               data-component="body">
    <br>
<p>The side the party fights for, where the Event divides the field. Example: <code>loyalist</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>players</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>One entry per Player, including whoever is registering.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="players.0.name"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="Tarik Torgaddon"
               data-component="body">
    <br>
<p>The Player's name. Example: <code>Tarik Torgaddon</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="players.0.email"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="tarik@example.com"
               data-component="body">
    <br>
<p>The Player's email address. Example: <code>tarik@example.com</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>faction_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="players.0.faction_id"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="1"
               data-component="body">
    <br>
<p>The Faction this Player brings. Example: <code>1</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>army_list</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="players.0.army_list"                data-endpoint="POSTapi-events--event_slug--attendees"
               value="architecto"
               data-component="body">
    <br>
<p>This Player's army list. Example: <code>architecto</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="events-PATCHapi-events--event_slug--attendees--id-">Amend a Team</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Members and Organisers may change the party name; allegiance freezes once a Round is Live.</p>

<span id="example-requests-PATCHapi-events--event_slug--attendees--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Sons of Terra',
            'allegiance' =&gt; 'traitor',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Sons of Terra",
    "allegiance": "traitor"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-events--event_slug--attendees--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list_locked&quot;: true,
                &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ],
        &quot;checked_in_at&quot;: null,
        &quot;custom_field_responses&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Dietary requirements&quot;,
                &quot;type&quot;: &quot;text&quot;,
                &quot;value&quot;: &quot;None&quot;
            }
        ],
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;round_number&quot;: 2,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                },
                &quot;opponents&quot;: [
                    {
                        &quot;id&quot;: 11,
                        &quot;name&quot;: &quot;Grace and Alan&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-events--event_slug--attendees--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-events--event_slug--attendees--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-events--event_slug--attendees--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-events--event_slug--attendees--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-events--event_slug--attendees--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-events--event_slug--attendees--id-" data-method="PATCH"
      data-path="api/events/{event_slug}/attendees/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-events--event_slug--attendees--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-events--event_slug--attendees--id-"
                    onclick="tryItOut('PATCHapi-events--event_slug--attendees--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-events--event_slug--attendees--id-"
                    onclick="cancelTryOut('PATCHapi-events--event_slug--attendees--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-events--event_slug--attendees--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/events/{event_slug}/attendees/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the attendee. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="Sons of Terra"
               data-component="body">
    <br>
<p>The name the party competes under. Example: <code>Sons of Terra</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>allegiance</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="allegiance"                data-endpoint="PATCHapi-events--event_slug--attendees--id-"
               value="traitor"
               data-component="body">
    <br>
<p>The side the party fights for. Frozen once a Round is Live. Example: <code>traitor</code></p>
        </div>
        </form>

                    <h2 id="events-POSTapi-events--event_slug--attendees--attendee_id--members">Add a Player to a Team</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Invites the Player if they have no account yet. Closed to members once registration closes; Organisers are never blocked.</p>

<span id="example-requests-POSTapi-events--event_slug--attendees--attendee_id--members">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1/members';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'Tarik Torgaddon',
            'email' =&gt; 'tarik@example.com',
            'faction_id' =&gt; 1,
            'army_list' =&gt; 'architecto',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1/members"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Tarik Torgaddon",
    "email": "tarik@example.com",
    "faction_id": 1,
    "army_list": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-events--event_slug--attendees--attendee_id--members">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 9,
        &quot;name&quot;: &quot;Ada and Grace&quot;,
        &quot;allegiance&quot;: &quot;loyalist&quot;,
        &quot;members&quot;: [
            {
                &quot;id&quot;: 12,
                &quot;name&quot;: &quot;Ada Lovelace&quot;,
                &quot;faction&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Sons of Horus&quot;
                },
                &quot;army_list_locked&quot;: true,
                &quot;army_list&quot;: &quot;Legion Tactical Squad, 10 models...&quot;,
                &quot;clubs&quot;: [
                    {
                        &quot;id&quot;: 2,
                        &quot;name&quot;: &quot;The Ordo Ludi&quot;
                    }
                ]
            }
        ],
        &quot;checked_in_at&quot;: null,
        &quot;custom_field_responses&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Dietary requirements&quot;,
                &quot;type&quot;: &quot;text&quot;,
                &quot;value&quot;: &quot;None&quot;
            }
        ],
        &quot;games&quot;: [
            {
                &quot;id&quot;: 18,
                &quot;round_number&quot;: 2,
                &quot;table_number&quot;: 5,
                &quot;is_bye&quot;: false,
                &quot;scores&quot;: {
                    &quot;match-points&quot;: 3,
                    &quot;victory-points&quot;: 85
                },
                &quot;opponents&quot;: [
                    {
                        &quot;id&quot;: 11,
                        &quot;name&quot;: &quot;Grace and Alan&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-events--event_slug--attendees--attendee_id--members" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-events--event_slug--attendees--attendee_id--members"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-events--event_slug--attendees--attendee_id--members"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-events--event_slug--attendees--attendee_id--members" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-events--event_slug--attendees--attendee_id--members">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-events--event_slug--attendees--attendee_id--members" data-method="POST"
      data-path="api/events/{event_slug}/attendees/{attendee_id}/members"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-events--event_slug--attendees--attendee_id--members', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-events--event_slug--attendees--attendee_id--members"
                    onclick="tryItOut('POSTapi-events--event_slug--attendees--attendee_id--members');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-events--event_slug--attendees--attendee_id--members"
                    onclick="cancelTryOut('POSTapi-events--event_slug--attendees--attendee_id--members');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-events--event_slug--attendees--attendee_id--members"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/events/{event_slug}/attendees/{attendee_id}/members</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="Tarik Torgaddon"
               data-component="body">
    <br>
<p>The Player's name, used if they have no account yet. Example: <code>Tarik Torgaddon</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="tarik@example.com"
               data-component="body">
    <br>
<p>The Player's email address. Example: <code>tarik@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>faction_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="faction_id"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="1"
               data-component="body">
    <br>
<p>The Faction this Player brings. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>army_list</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="army_list"                data-endpoint="POSTapi-events--event_slug--attendees--attendee_id--members"
               value="architecto"
               data-component="body">
    <br>
<p>This Player's army list. Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="events-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">Remove a Player from a Team</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Closed to members once registration closes; Organisers are never blocked.</p>

<span id="example-requests-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/london-grand-tournament/attendees/1/members/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/events/london-grand-tournament/attendees/1/members/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">
            <blockquote>
            <p>Example response (200, The member was removed from the Attendee.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;"></code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-events--event_slug--attendees--attendee_id--members--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-events--event_slug--attendees--attendee_id--members--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-events--event_slug--attendees--attendee_id--members--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-events--event_slug--attendees--attendee_id--members--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-events--event_slug--attendees--attendee_id--members--id-" data-method="DELETE"
      data-path="api/events/{event_slug}/attendees/{attendee_id}/members/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-events--event_slug--attendees--attendee_id--members--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
                    onclick="tryItOut('DELETEapi-events--event_slug--attendees--attendee_id--members--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
                    onclick="cancelTryOut('DELETEapi-events--event_slug--attendees--attendee_id--members--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/events/{event_slug}/attendees/{attendee_id}/members/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event_slug</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_slug"                data-endpoint="DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee_id"                data-endpoint="DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-events--event_slug--attendees--attendee_id--members--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the Player to remove. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="gallery">Gallery</h1>

    <p>APIs for Gallery</p>

                                <h2 id="gallery-GETapi-gallery">List Photos</h2>

<p>
</p>

<p>List the authenticated user's photos.</p>

<span id="example-requests-GETapi-gallery">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-gallery">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 297,
        &quot;name&quot;: &quot;Sunt suscipit doloribus fugiat.&quot;,
        &quot;description&quot;: &quot;Deserunt et error neque recusandae et. Dolorem et ut dicta. Assumenda consequatur ut et sunt quisquam. Repellendus ut eaque alias ratione dolores.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/2b9698b7-206f-39ef-afe7-680996a8a00c.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/b791a78a-bdb6-3c15-a840-a911ad9b9a6e.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-gallery" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-gallery"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-gallery"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-gallery" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-gallery">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-gallery" data-method="GET"
      data-path="api/gallery"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-gallery', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-gallery"
                    onclick="tryItOut('GETapi-gallery');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-gallery"
                    onclick="cancelTryOut('GETapi-gallery');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-gallery"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/gallery</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="gallery-POSTapi-gallery">Store Photo</h2>

<p>
</p>

<p>Upload a new photo to the authenticated user's gallery.</p>

<span id="example-requests-POSTapi-gallery">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'name',
                'contents' =&gt; 'My painted army'
            ],
            [
                'name' =&gt; 'description',
                'contents' =&gt; 'My fully painted Space Marines army.'
            ],
            [
                'name' =&gt; 'photo',
                'contents' =&gt; fopen('/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/phpkd393ambo94hdZOEYtr', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('name', 'My painted army');
body.append('description', 'My fully painted Space Marines army.');
body.append('photo', document.querySelector('input[name="photo"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-gallery">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 298,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Et fugiat sunt nihil accusantium. Mollitia modi deserunt aut ab provident perspiciatis quo. Nostrum aut adipisci quidem nostrum.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/445bd3f6-8f2c-38cb-aa04-2f4e1edb32bb.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/deea2dce-ea5d-340f-90ce-c06cddd4c879.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-gallery" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-gallery"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-gallery"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-gallery" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-gallery">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-gallery" data-method="POST"
      data-path="api/gallery"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-gallery', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-gallery"
                    onclick="tryItOut('POSTapi-gallery');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-gallery"
                    onclick="cancelTryOut('POSTapi-gallery');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-gallery"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/gallery</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-gallery"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-gallery"
               value="My painted army"
               data-component="body">
    <br>
<p>The photo title. Must not be greater than 255 characters. Example: <code>My painted army</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>photo</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="photo"                data-endpoint="POSTapi-gallery"
               value=""
               data-component="body">
    <br>
<p>The photo file (jpg, jpeg, png, or webp, max 10MB). Must be an image. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/phpkd393ambo94hdZOEYtr</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-gallery"
               value="My fully painted Space Marines army."
               data-component="body">
    <br>
<p>An optional description of the photo. Must not be greater than 1000 characters. Example: <code>My fully painted Space Marines army.</code></p>
        </div>
        </form>

                    <h2 id="gallery-GETapi-gallery--photo_id-">Show Photo</h2>

<p>
</p>

<p>Get a specific photo by ID.</p>

<span id="example-requests-GETapi-gallery--photo_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-gallery--photo_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 299,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Et fugiat sunt nihil accusantium. Mollitia modi deserunt aut ab provident perspiciatis quo. Nostrum aut adipisci quidem nostrum.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/445bd3f6-8f2c-38cb-aa04-2f4e1edb32bb.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/deea2dce-ea5d-340f-90ce-c06cddd4c879.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-gallery--photo_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-gallery--photo_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-gallery--photo_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-gallery--photo_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-gallery--photo_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-gallery--photo_id-" data-method="GET"
      data-path="api/gallery/{photo_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-gallery--photo_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-gallery--photo_id-"
                    onclick="tryItOut('GETapi-gallery--photo_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-gallery--photo_id-"
                    onclick="cancelTryOut('GETapi-gallery--photo_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-gallery--photo_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/gallery/{photo_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-gallery--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-gallery--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>photo_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_id"                data-endpoint="GETapi-gallery--photo_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the photo. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="gallery-PATCHapi-gallery--photo_id-">Update Photo</h2>

<p>
</p>

<p>Update a photo's name, description, or image.</p>

<span id="example-requests-PATCHapi-gallery--photo_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery/1';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'name',
                'contents' =&gt; 'My painted army'
            ],
            [
                'name' =&gt; 'description',
                'contents' =&gt; 'My fully painted Space Marines army.'
            ],
            [
                'name' =&gt; 'photo',
                'contents' =&gt; fopen('/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/php7gf924eie2h3bfT9pSX', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery/1"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('name', 'My painted army');
body.append('description', 'My fully painted Space Marines army.');
body.append('photo', document.querySelector('input[name="photo"]').files[0]);

fetch(url, {
    method: "PATCH",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-gallery--photo_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 300,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Et fugiat sunt nihil accusantium. Mollitia modi deserunt aut ab provident perspiciatis quo. Nostrum aut adipisci quidem nostrum.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/445bd3f6-8f2c-38cb-aa04-2f4e1edb32bb.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/deea2dce-ea5d-340f-90ce-c06cddd4c879.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-gallery--photo_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-gallery--photo_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-gallery--photo_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-gallery--photo_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-gallery--photo_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-gallery--photo_id-" data-method="PATCH"
      data-path="api/gallery/{photo_id}"
      data-authed="0"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-gallery--photo_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-gallery--photo_id-"
                    onclick="tryItOut('PATCHapi-gallery--photo_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-gallery--photo_id-"
                    onclick="cancelTryOut('PATCHapi-gallery--photo_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-gallery--photo_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/gallery/{photo_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-gallery--photo_id-"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-gallery--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>photo_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_id"                data-endpoint="PATCHapi-gallery--photo_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the photo. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PATCHapi-gallery--photo_id-"
               value="My painted army"
               data-component="body">
    <br>
<p>The photo title. Must not be greater than 255 characters. Example: <code>My painted army</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>photo</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="photo"                data-endpoint="PATCHapi-gallery--photo_id-"
               value=""
               data-component="body">
    <br>
<p>A replacement photo file (jpg, jpeg, png, or webp, max 10MB). Must be an image. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/php7gf924eie2h3bfT9pSX</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PATCHapi-gallery--photo_id-"
               value="My fully painted Space Marines army."
               data-component="body">
    <br>
<p>An updated description of the photo. Must not be greater than 1000 characters. Example: <code>My fully painted Space Marines army.</code></p>
        </div>
        </form>

                    <h2 id="gallery-DELETEapi-gallery--photo_id-">Delete Photo</h2>

<p>
</p>

<p>Delete a photo from the gallery.</p>

<span id="example-requests-DELETEapi-gallery--photo_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-gallery--photo_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Photo successfully deleted.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-gallery--photo_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-gallery--photo_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-gallery--photo_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-gallery--photo_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-gallery--photo_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-gallery--photo_id-" data-method="DELETE"
      data-path="api/gallery/{photo_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-gallery--photo_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-gallery--photo_id-"
                    onclick="tryItOut('DELETEapi-gallery--photo_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-gallery--photo_id-"
                    onclick="cancelTryOut('DELETEapi-gallery--photo_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-gallery--photo_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/gallery/{photo_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-gallery--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-gallery--photo_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>photo_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_id"                data-endpoint="DELETEapi-gallery--photo_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the photo. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="gallery-GETapi-users--user_id--gallery">User Gallery</h2>

<p>
</p>

<p>List photos from a specific user's gallery.</p>

<span id="example-requests-GETapi-users--user_id--gallery">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/gallery';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/gallery"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users--user_id--gallery">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 301,
        &quot;name&quot;: &quot;Fugit deleniti distinctio eum.&quot;,
        &quot;description&quot;: &quot;Aut libero aliquam veniam corporis. Mollitia deleniti nemo odit quia officia. Dignissimos neque blanditiis odio.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/dc407d02-9b9b-35e5-8d69-87904a6787e3.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/b3dfd3b4-abf6-34e6-9ab5-ef739060a5da.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-users--user_id--gallery" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-users--user_id--gallery"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users--user_id--gallery"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users--user_id--gallery" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-users--user_id--gallery">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-users--user_id--gallery" data-method="GET"
      data-path="api/users/{user_id}/gallery"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users--user_id--gallery', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users--user_id--gallery"
                    onclick="tryItOut('GETapi-users--user_id--gallery');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users--user_id--gallery"
                    onclick="cancelTryOut('GETapi-users--user_id--gallery');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users--user_id--gallery"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users/{user_id}/gallery</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users--user_id--gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users--user_id--gallery"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="GETapi-users--user_id--gallery"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="reactions">Reactions</h1>

    <p>APIs for Reactions</p>

                                <h2 id="reactions-POSTapi-gallery--photo_id--react">Toggle Reaction</h2>

<p>
</p>

<p>Toggle a reaction on a photo. Adds a reaction if none exists, removes it otherwise.</p>

<span id="example-requests-POSTapi-gallery--photo_id--react">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/gallery/1/react';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/gallery/1/react"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-gallery--photo_id--react">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;reactions_count&quot;: 5,
    &quot;has_reacted&quot;: true
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-gallery--photo_id--react" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-gallery--photo_id--react"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-gallery--photo_id--react"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-gallery--photo_id--react" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-gallery--photo_id--react">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-gallery--photo_id--react" data-method="POST"
      data-path="api/gallery/{photo_id}/react"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-gallery--photo_id--react', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-gallery--photo_id--react"
                    onclick="tryItOut('POSTapi-gallery--photo_id--react');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-gallery--photo_id--react"
                    onclick="cancelTryOut('POSTapi-gallery--photo_id--react');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-gallery--photo_id--react"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/gallery/{photo_id}/react</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-gallery--photo_id--react"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-gallery--photo_id--react"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>photo_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="photo_id"                data-endpoint="POSTapi-gallery--photo_id--react"
               value="1"
               data-component="url">
    <br>
<p>The ID of the photo. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="users">Users</h1>

    <p>APIs for Users</p>

                                <h2 id="users-GETapi-profile">Current User Profile</h2>

<p>
</p>

<p>Display the current user's profile data.</p>

<span id="example-requests-GETapi-profile">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/profile';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/profile"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-profile">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 12,
        &quot;updated_at&quot;: &quot;2026-08-01T10:00:00Z&quot;,
        &quot;public_name&quot;: &quot;Ada Lovelace&quot;,
        &quot;country&quot;: &quot;GB&quot;,
        &quot;email&quot;: &quot;ada@example.com&quot;,
        &quot;is_claimed&quot;: true,
        &quot;email_verified&quot;: true,
        &quot;unread_notifications_count&quot;: 3,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 4,
        &quot;following_count&quot;: 7
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-profile" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-profile"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-profile"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-profile" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-profile" data-method="GET"
      data-path="api/profile"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-profile', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-profile"
                    onclick="tryItOut('GETapi-profile');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-profile"
                    onclick="cancelTryOut('GETapi-profile');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-profile"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/profile</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

    <h3>Response</h3>
    <h4 class="fancy-heading-panel"><b>Response Fields</b></h4>
    <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>data</code></b>&nbsp;&nbsp;
<small>object</small>&nbsp;
 &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>is_claimed</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether this account has been claimed with a password. An unclaimed account exists only because someone invited it, and the SPA restricts what it may do.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>email_verified</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Whether the email address on the account has been verified.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>unread_notifications_count</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>How many in-app notifications are unread.</p>
                    </div>
                                    </details>
        </div>
                        <h2 id="users-PATCHapi-profile">Update Profile</h2>

<p>
</p>

<p>Update the current user's profile.</p>

<span id="example-requests-PATCHapi-profile">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/profile';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'name' =&gt; 'John Doe',
            'username' =&gt; 'johndoe',
            'country' =&gt; 'US',
            'show_public_name' =&gt; true,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/profile"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "John Doe",
    "username": "johndoe",
    "country": "US",
    "show_public_name": true
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-profile">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1398,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Ms. Audra Crooks II&quot;,
        &quot;country&quot;: &quot;NR&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-profile" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-profile"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-profile"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-profile" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-profile" data-method="PATCH"
      data-path="api/profile"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-profile', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-profile"
                    onclick="tryItOut('PATCHapi-profile');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-profile"
                    onclick="cancelTryOut('PATCHapi-profile');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-profile"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/profile</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PATCHapi-profile"
               value="John Doe"
               data-component="body">
    <br>
<p>The user's display name. Must not be greater than 255 characters. Example: <code>John Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>username</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="username"                data-endpoint="PATCHapi-profile"
               value="johndoe"
               data-component="body">
    <br>
<p>A unique username (3-30 chars, starts with letter, allows letters/digits/underscores/hyphens). Must match the regex /^[a-zA-Z][a-zA-Z0-9_-]{2,29}$/. Example: <code>johndoe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>country</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="country"                data-endpoint="PATCHapi-profile"
               value="US"
               data-component="body">
    <br>
<p>An ISO 3166-1 alpha-2 country code. Example: <code>US</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>AF</code></li> <li><code>AX</code></li> <li><code>AL</code></li> <li><code>DZ</code></li> <li><code>AS</code></li> <li><code>AD</code></li> <li><code>AO</code></li> <li><code>AI</code></li> <li><code>AQ</code></li> <li><code>AG</code></li> <li><code>AR</code></li> <li><code>AM</code></li> <li><code>AW</code></li> <li><code>AU</code></li> <li><code>AT</code></li> <li><code>AZ</code></li> <li><code>BS</code></li> <li><code>BH</code></li> <li><code>BD</code></li> <li><code>BB</code></li> <li><code>BY</code></li> <li><code>BE</code></li> <li><code>BZ</code></li> <li><code>BJ</code></li> <li><code>BM</code></li> <li><code>BT</code></li> <li><code>BO</code></li> <li><code>BA</code></li> <li><code>BW</code></li> <li><code>BV</code></li> <li><code>BR</code></li> <li><code>IO</code></li> <li><code>BN</code></li> <li><code>BG</code></li> <li><code>BF</code></li> <li><code>BI</code></li> <li><code>CV</code></li> <li><code>KH</code></li> <li><code>CM</code></li> <li><code>CA</code></li> <li><code>KY</code></li> <li><code>CF</code></li> <li><code>TD</code></li> <li><code>CL</code></li> <li><code>CN</code></li> <li><code>CX</code></li> <li><code>CC</code></li> <li><code>CO</code></li> <li><code>KM</code></li> <li><code>CG</code></li> <li><code>CD</code></li> <li><code>CK</code></li> <li><code>CR</code></li> <li><code>CI</code></li> <li><code>HR</code></li> <li><code>CU</code></li> <li><code>CW</code></li> <li><code>CY</code></li> <li><code>CZ</code></li> <li><code>DK</code></li> <li><code>DJ</code></li> <li><code>DM</code></li> <li><code>DO</code></li> <li><code>EC</code></li> <li><code>EG</code></li> <li><code>SV</code></li> <li><code>GQ</code></li> <li><code>ER</code></li> <li><code>EE</code></li> <li><code>SZ</code></li> <li><code>ET</code></li> <li><code>FK</code></li> <li><code>FO</code></li> <li><code>FJ</code></li> <li><code>FI</code></li> <li><code>FR</code></li> <li><code>GF</code></li> <li><code>PF</code></li> <li><code>TF</code></li> <li><code>GA</code></li> <li><code>GM</code></li> <li><code>GE</code></li> <li><code>DE</code></li> <li><code>GH</code></li> <li><code>GI</code></li> <li><code>GR</code></li> <li><code>GL</code></li> <li><code>GD</code></li> <li><code>GP</code></li> <li><code>GU</code></li> <li><code>GT</code></li> <li><code>GG</code></li> <li><code>GN</code></li> <li><code>GW</code></li> <li><code>GY</code></li> <li><code>HT</code></li> <li><code>HM</code></li> <li><code>VA</code></li> <li><code>HN</code></li> <li><code>HK</code></li> <li><code>HU</code></li> <li><code>IS</code></li> <li><code>IN</code></li> <li><code>ID</code></li> <li><code>IR</code></li> <li><code>IQ</code></li> <li><code>IE</code></li> <li><code>IM</code></li> <li><code>IL</code></li> <li><code>IT</code></li> <li><code>JM</code></li> <li><code>JP</code></li> <li><code>JE</code></li> <li><code>JO</code></li> <li><code>KZ</code></li> <li><code>KE</code></li> <li><code>KI</code></li> <li><code>KP</code></li> <li><code>KR</code></li> <li><code>KW</code></li> <li><code>KG</code></li> <li><code>LA</code></li> <li><code>LV</code></li> <li><code>LB</code></li> <li><code>LS</code></li> <li><code>LR</code></li> <li><code>LY</code></li> <li><code>LI</code></li> <li><code>LT</code></li> <li><code>LU</code></li> <li><code>MO</code></li> <li><code>MG</code></li> <li><code>MW</code></li> <li><code>MY</code></li> <li><code>MV</code></li> <li><code>ML</code></li> <li><code>MT</code></li> <li><code>MH</code></li> <li><code>MQ</code></li> <li><code>MR</code></li> <li><code>MU</code></li> <li><code>YT</code></li> <li><code>MX</code></li> <li><code>FM</code></li> <li><code>MD</code></li> <li><code>MC</code></li> <li><code>MN</code></li> <li><code>ME</code></li> <li><code>MS</code></li> <li><code>MA</code></li> <li><code>MZ</code></li> <li><code>MM</code></li> <li><code>NA</code></li> <li><code>NR</code></li> <li><code>NP</code></li> <li><code>NL</code></li> <li><code>NC</code></li> <li><code>NZ</code></li> <li><code>NI</code></li> <li><code>NE</code></li> <li><code>NG</code></li> <li><code>NU</code></li> <li><code>NF</code></li> <li><code>MK</code></li> <li><code>MP</code></li> <li><code>NO</code></li> <li><code>OM</code></li> <li><code>PK</code></li> <li><code>PW</code></li> <li><code>PS</code></li> <li><code>PA</code></li> <li><code>PG</code></li> <li><code>PY</code></li> <li><code>PE</code></li> <li><code>PH</code></li> <li><code>PN</code></li> <li><code>PL</code></li> <li><code>PT</code></li> <li><code>PR</code></li> <li><code>QA</code></li> <li><code>RE</code></li> <li><code>RO</code></li> <li><code>RU</code></li> <li><code>RW</code></li> <li><code>BL</code></li> <li><code>SH</code></li> <li><code>KN</code></li> <li><code>LC</code></li> <li><code>MF</code></li> <li><code>PM</code></li> <li><code>VC</code></li> <li><code>WS</code></li> <li><code>SM</code></li> <li><code>ST</code></li> <li><code>SA</code></li> <li><code>SN</code></li> <li><code>RS</code></li> <li><code>SC</code></li> <li><code>SL</code></li> <li><code>SG</code></li> <li><code>SX</code></li> <li><code>SK</code></li> <li><code>SI</code></li> <li><code>SB</code></li> <li><code>SO</code></li> <li><code>ZA</code></li> <li><code>GS</code></li> <li><code>SS</code></li> <li><code>ES</code></li> <li><code>LK</code></li> <li><code>SD</code></li> <li><code>SR</code></li> <li><code>SJ</code></li> <li><code>SE</code></li> <li><code>CH</code></li> <li><code>SY</code></li> <li><code>TW</code></li> <li><code>TJ</code></li> <li><code>TZ</code></li> <li><code>TH</code></li> <li><code>TL</code></li> <li><code>TG</code></li> <li><code>TK</code></li> <li><code>TO</code></li> <li><code>TT</code></li> <li><code>TN</code></li> <li><code>TR</code></li> <li><code>TM</code></li> <li><code>TC</code></li> <li><code>TV</code></li> <li><code>UG</code></li> <li><code>UA</code></li> <li><code>AE</code></li> <li><code>GB</code></li> <li><code>US</code></li> <li><code>UM</code></li> <li><code>UY</code></li> <li><code>UZ</code></li> <li><code>VU</code></li> <li><code>VE</code></li> <li><code>VN</code></li> <li><code>VG</code></li> <li><code>VI</code></li> <li><code>WF</code></li> <li><code>EH</code></li> <li><code>YE</code></li> <li><code>ZM</code></li> <li><code>ZW</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>show_public_name</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PATCHapi-profile" style="display: none">
            <input type="radio" name="show_public_name"
                   value="true"
                   data-endpoint="PATCHapi-profile"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PATCHapi-profile" style="display: none">
            <input type="radio" name="show_public_name"
                   value="false"
                   data-endpoint="PATCHapi-profile"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Whether to display the user's real name publicly. Example: <code>true</code></p>
        </div>
        </form>

                    <h2 id="users-POSTapi-profile-email">Change Email</h2>

<p>
</p>

<p>Initiate change email address for a user</p>

<span id="example-requests-POSTapi-profile-email">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/profile/email';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'current_password' =&gt; 'password',
            'email' =&gt; 'newemail@example.com',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/profile/email"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "current_password": "password",
    "email": "newemail@example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-profile-email">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;A verification link has been sent to your new email address.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-profile-email" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-profile-email"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-profile-email"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-profile-email" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-profile-email">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-profile-email" data-method="POST"
      data-path="api/profile/email"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-profile-email', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-profile-email"
                    onclick="tryItOut('POSTapi-profile-email');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-profile-email"
                    onclick="cancelTryOut('POSTapi-profile-email');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-profile-email"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/profile/email</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-profile-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-profile-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>current_password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="current_password"                data-endpoint="POSTapi-profile-email"
               value="password"
               data-component="body">
    <br>
<p>The user's current password for verification. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-profile-email"
               value="newemail@example.com"
               data-component="body">
    <br>
<p>The new email address. Must be a valid email address. Example: <code>newemail@example.com</code></p>
        </div>
        </form>

                    <h2 id="users-POSTapi-profile-password">Change Password</h2>

<p>
</p>

<p>Request a password change. A confirmation link will be sent to the user's email.</p>

<span id="example-requests-POSTapi-profile-password">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/profile/password';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'current_password' =&gt; 'password',
            'password' =&gt; 'newpassword',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/profile/password"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "current_password": "password",
    "password": "newpassword"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-profile-password">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;A confirmation link has been sent to your email address.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-profile-password" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-profile-password"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-profile-password"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-profile-password" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-profile-password">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-profile-password" data-method="POST"
      data-path="api/profile/password"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-profile-password', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-profile-password"
                    onclick="tryItOut('POSTapi-profile-password');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-profile-password"
                    onclick="cancelTryOut('POSTapi-profile-password');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-profile-password"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/profile/password</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-profile-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-profile-password"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>current_password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="current_password"                data-endpoint="POSTapi-profile-password"
               value="password"
               data-component="body">
    <br>
<p>The user's current password for verification. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-profile-password"
               value="newpassword"
               data-component="body">
    <br>
<p>The new password (min 8 characters). Must be at least 8 characters. Example: <code>newpassword</code></p>
        </div>
        </form>

                    <h2 id="users-GETapi-profile--user_id-">User Profile</h2>

<p>
</p>

<p>Display the given user's profile data.</p>

<span id="example-requests-GETapi-profile--user_id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/profile/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/profile/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-profile--user_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1399,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Ms. Audra Crooks II&quot;,
        &quot;country&quot;: &quot;MX&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-profile--user_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-profile--user_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-profile--user_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-profile--user_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-profile--user_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-profile--user_id-" data-method="GET"
      data-path="api/profile/{user_id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-profile--user_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-profile--user_id-"
                    onclick="tryItOut('GETapi-profile--user_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-profile--user_id-"
                    onclick="cancelTryOut('GETapi-profile--user_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-profile--user_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/profile/{user_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-profile--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-profile--user_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="GETapi-profile--user_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-GETapi-notifications">List Notifications</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>The authenticated Player's in-app notifications, newest first, with the unread count alongside. Event notifications always arrive here, whatever the Player's channel preferences say.</p>

<span id="example-requests-GETapi-notifications">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/notifications';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/notifications"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-notifications">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: &quot;9c1f0f4e-1d2a-4f3b-9a5c-2f6f3f9e1a11&quot;,
            &quot;data&quot;: {
                &quot;type&quot;: &quot;round_published&quot;,
                &quot;event_slug&quot;: &quot;london-grand-tournament&quot;,
                &quot;round_id&quot;: 4
            },
            &quot;read_at&quot;: null,
            &quot;created_at&quot;: &quot;2026-09-12T12:00:00+00:00&quot;
        }
    ],
    &quot;links&quot;: {
        &quot;first&quot;: &quot;https://api.battlezones.test/api/notifications?page=1&quot;,
        &quot;last&quot;: &quot;https://api.battlezones.test/api/notifications?page=1&quot;,
        &quot;prev&quot;: null,
        &quot;next&quot;: null
    },
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;from&quot;: 1,
        &quot;last_page&quot;: 1,
        &quot;path&quot;: &quot;https://api.battlezones.test/api/notifications&quot;,
        &quot;per_page&quot;: 15,
        &quot;to&quot;: 1,
        &quot;total&quot;: 1,
        &quot;unread_count&quot;: 3
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-notifications" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-notifications"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-notifications"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-notifications" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-notifications">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-notifications" data-method="GET"
      data-path="api/notifications"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-notifications', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-notifications"
                    onclick="tryItOut('GETapi-notifications');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-notifications"
                    onclick="cancelTryOut('GETapi-notifications');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-notifications"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/notifications</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-notifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-notifications"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="users-POSTapi-notifications-read">Mark All Notifications Read</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Clears the unread count for the authenticated Player.</p>

<span id="example-requests-POSTapi-notifications-read">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/notifications/read';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/notifications/read"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-notifications-read">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;unread_count&quot;: 0
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-notifications-read" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-notifications-read"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-notifications-read"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-notifications-read" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-notifications-read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-notifications-read" data-method="POST"
      data-path="api/notifications/read"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-notifications-read', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-notifications-read"
                    onclick="tryItOut('POSTapi-notifications-read');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-notifications-read"
                    onclick="cancelTryOut('POSTapi-notifications-read');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-notifications-read"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/notifications/read</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-notifications-read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-notifications-read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="users-POSTapi-notifications--notification--read">Mark a Notification Read</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Scoped to the authenticated Player: another Player's notification is not found rather than forbidden.</p>

<span id="example-requests-POSTapi-notifications--notification--read">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/notifications/9b1d4d0a-1f5f-4a8c-9c1e-2b6f2b3f0a11/read';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/notifications/9b1d4d0a-1f5f-4a8c-9c1e-2b6f2b3f0a11/read"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-notifications--notification--read">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;9c1f0f4e-1d2a-4f3b-9a5c-2f6f3f9e1a11&quot;,
        &quot;data&quot;: {
            &quot;type&quot;: &quot;round_published&quot;,
            &quot;event_slug&quot;: &quot;london-grand-tournament&quot;,
            &quot;round_id&quot;: 4
        },
        &quot;read_at&quot;: &quot;2026-09-12T12:01:00+00:00&quot;,
        &quot;created_at&quot;: &quot;2026-09-12T12:00:00+00:00&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401, The request carries no valid token.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-notifications--notification--read" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-notifications--notification--read"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-notifications--notification--read"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-notifications--notification--read" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-notifications--notification--read">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-notifications--notification--read" data-method="POST"
      data-path="api/notifications/{notification}/read"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-notifications--notification--read', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-notifications--notification--read"
                    onclick="tryItOut('POSTapi-notifications--notification--read');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-notifications--notification--read"
                    onclick="cancelTryOut('POSTapi-notifications--notification--read');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-notifications--notification--read"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/notifications/{notification}/read</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-notifications--notification--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-notifications--notification--read"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>notification</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="notification"                data-endpoint="POSTapi-notifications--notification--read"
               value="9b1d4d0a-1f5f-4a8c-9c1e-2b6f2b3f0a11"
               data-component="url">
    <br>
<p>The id of the notification. Example: <code>9b1d4d0a-1f5f-4a8c-9c1e-2b6f2b3f0a11</code></p>
            </div>
                    </form>

                    <h2 id="users-GETapi-notification-settings">Get Notification Settings</h2>

<p>
</p>

<p>Get the authenticated user's notification settings.</p>

<span id="example-requests-GETapi-notification-settings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/notification-settings';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/notification-settings"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-notification-settings">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;primary_messages&quot;: {
            &quot;label&quot;: &quot;Primary Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;message_requests&quot;: {
            &quot;label&quot;: &quot;Message Requests&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;event_messages&quot;: {
            &quot;label&quot;: &quot;Event Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;round_live&quot;: {
            &quot;label&quot;: &quot;Round Live&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        },
        &quot;result_activity&quot;: {
            &quot;label&quot;: &quot;Result Activity&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        },
        &quot;voting_open&quot;: {
            &quot;label&quot;: &quot;Voting Open&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-notification-settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-notification-settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-notification-settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-notification-settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-notification-settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-notification-settings" data-method="GET"
      data-path="api/notification-settings"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-notification-settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-notification-settings"
                    onclick="tryItOut('GETapi-notification-settings');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-notification-settings"
                    onclick="cancelTryOut('GETapi-notification-settings');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-notification-settings"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/notification-settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-notification-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-notification-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="users-PATCHapi-notification-settings">Update Notification Settings</h2>

<p>
</p>

<p>Update the authenticated user's notification settings.</p>

<span id="example-requests-PATCHapi-notification-settings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/notification-settings';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'primary_messages' =&gt; ['push'],
            'message_requests' =&gt; ['push'],
            'event_messages' =&gt; ['push'],
            'round_live' =&gt; ['push'],
            'result_activity' =&gt; ['email'],
            'voting_open' =&gt; ['push'],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/notification-settings"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "primary_messages": [
        "push"
    ],
    "message_requests": [
        "push"
    ],
    "event_messages": [
        "push"
    ],
    "round_live": [
        "push"
    ],
    "result_activity": [
        "email"
    ],
    "voting_open": [
        "push"
    ]
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-notification-settings">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;primary_messages&quot;: {
            &quot;label&quot;: &quot;Primary Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;message_requests&quot;: {
            &quot;label&quot;: &quot;Message Requests&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;event_messages&quot;: {
            &quot;label&quot;: &quot;Event Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: false
        },
        &quot;round_live&quot;: {
            &quot;label&quot;: &quot;Round Live&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        },
        &quot;result_activity&quot;: {
            &quot;label&quot;: &quot;Result Activity&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        },
        &quot;voting_open&quot;: {
            &quot;label&quot;: &quot;Voting Open&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ],
            &quot;always_in_app&quot;: true
        }
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-notification-settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-notification-settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-notification-settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-notification-settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-notification-settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-notification-settings" data-method="PATCH"
      data-path="api/notification-settings"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-notification-settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-notification-settings"
                    onclick="tryItOut('PATCHapi-notification-settings');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-notification-settings"
                    onclick="cancelTryOut('PATCHapi-notification-settings');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-notification-settings"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/notification-settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-notification-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-notification-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>primary_messages</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="primary_messages[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="primary_messages[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>message_requests</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="message_requests[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="message_requests[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>event_messages</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event_messages[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="event_messages[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>round_live</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="round_live[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="round_live[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>result_activity</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="result_activity[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="result_activity[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>voting_open</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="voting_open[0]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
        <input type="text" style="display: none"
               name="voting_open[1]"                data-endpoint="PATCHapi-notification-settings"
               data-component="body">
    <br>

Must be one of:
<ul style="list-style-type: square;"><li><code>email</code></li> <li><code>push</code></li></ul>
        </div>
        </form>

                    <h2 id="users-GETapi-privacy-settings">Get Privacy Settings</h2>

<p>
</p>

<p>Get the authenticated user's privacy settings.</p>

<span id="example-requests-GETapi-privacy-settings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/privacy-settings';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/privacy-settings"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-privacy-settings">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;messaging&quot;: {
            &quot;value&quot;: &quot;anyone&quot;,
            &quot;label&quot;: &quot;Anyone&quot;
        },
        &quot;profile&quot;: {
            &quot;value&quot;: &quot;anyone&quot;,
            &quot;label&quot;: &quot;Anyone&quot;
        },
        &quot;options&quot;: [
            {
                &quot;value&quot;: &quot;anyone&quot;,
                &quot;label&quot;: &quot;Anyone&quot;
            },
            {
                &quot;value&quot;: &quot;followers_only&quot;,
                &quot;label&quot;: &quot;Followers Only&quot;
            },
            {
                &quot;value&quot;: &quot;following_only&quot;,
                &quot;label&quot;: &quot;Following Only&quot;
            },
            {
                &quot;value&quot;: &quot;mutual_followers&quot;,
                &quot;label&quot;: &quot;Mutual Followers&quot;
            },
            {
                &quot;value&quot;: &quot;fellow_club_members&quot;,
                &quot;label&quot;: &quot;Fellow Club Members&quot;
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-privacy-settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-privacy-settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-privacy-settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-privacy-settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-privacy-settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-privacy-settings" data-method="GET"
      data-path="api/privacy-settings"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-privacy-settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-privacy-settings"
                    onclick="tryItOut('GETapi-privacy-settings');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-privacy-settings"
                    onclick="cancelTryOut('GETapi-privacy-settings');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-privacy-settings"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/privacy-settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-privacy-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-privacy-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="users-PATCHapi-privacy-settings">Update Privacy Settings</h2>

<p>
</p>

<p>Update the authenticated user's privacy settings.</p>

<span id="example-requests-PATCHapi-privacy-settings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/privacy-settings';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'messaging' =&gt; 'anyone',
            'profile' =&gt; 'anyone',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/privacy-settings"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "messaging": "anyone",
    "profile": "anyone"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-privacy-settings">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;messaging&quot;: {
            &quot;value&quot;: &quot;anyone&quot;,
            &quot;label&quot;: &quot;Anyone&quot;
        },
        &quot;profile&quot;: {
            &quot;value&quot;: &quot;anyone&quot;,
            &quot;label&quot;: &quot;Anyone&quot;
        },
        &quot;options&quot;: [
            {
                &quot;value&quot;: &quot;anyone&quot;,
                &quot;label&quot;: &quot;Anyone&quot;
            },
            {
                &quot;value&quot;: &quot;followers_only&quot;,
                &quot;label&quot;: &quot;Followers Only&quot;
            },
            {
                &quot;value&quot;: &quot;following_only&quot;,
                &quot;label&quot;: &quot;Following Only&quot;
            },
            {
                &quot;value&quot;: &quot;mutual_followers&quot;,
                &quot;label&quot;: &quot;Mutual Followers&quot;
            },
            {
                &quot;value&quot;: &quot;fellow_club_members&quot;,
                &quot;label&quot;: &quot;Fellow Club Members&quot;
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, The submitted data failed validation.):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The given data was invalid.&quot;,
    &quot;errors&quot;: {
        &quot;field_name&quot;: [
            &quot;The field name field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-privacy-settings" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-privacy-settings"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-privacy-settings"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-privacy-settings" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-privacy-settings">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-privacy-settings" data-method="PATCH"
      data-path="api/privacy-settings"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-privacy-settings', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-privacy-settings"
                    onclick="tryItOut('PATCHapi-privacy-settings');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-privacy-settings"
                    onclick="cancelTryOut('PATCHapi-privacy-settings');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-privacy-settings"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/privacy-settings</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-privacy-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-privacy-settings"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>messaging</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="messaging"                data-endpoint="PATCHapi-privacy-settings"
               value="anyone"
               data-component="body">
    <br>
<p>Who can send messages. One of: anyone, followers_only, following_only, mutual_followers, fellow_club_members. Example: <code>anyone</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>anyone</code></li> <li><code>followers_only</code></li> <li><code>following_only</code></li> <li><code>mutual_followers</code></li> <li><code>fellow_club_members</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>profile</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="profile"                data-endpoint="PATCHapi-privacy-settings"
               value="anyone"
               data-component="body">
    <br>
<p>Who can view the profile. One of: anyone, followers_only, following_only, mutual_followers, fellow_club_members. Example: <code>anyone</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>anyone</code></li> <li><code>followers_only</code></li> <li><code>following_only</code></li> <li><code>mutual_followers</code></li> <li><code>fellow_club_members</code></li></ul>
        </div>
        </form>

                    <h2 id="users-GETapi-users-search">Search Users</h2>

<p>
</p>

<p>Search for users by username or name.</p>

<span id="example-requests-GETapi-users-search">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/search';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'query' =&gt; [
            'q' =&gt; 'john',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/search"
);

const params = {
    "q": "john",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users-search">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1404,
            &quot;public_name&quot;: &quot;Ms. Audra Crooks II&quot;,
            &quot;username&quot;: &quot;breitenberg.gilbert&quot;
        },
        {
            &quot;id&quot;: 1405,
            &quot;public_name&quot;: &quot;Miss Jazlyn Keebler III&quot;,
            &quot;username&quot;: &quot;bauch.marcelo&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-users-search" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-users-search"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users-search"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users-search" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-users-search">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-users-search" data-method="GET"
      data-path="api/users/search"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users-search', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users-search"
                    onclick="tryItOut('GETapi-users-search');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users-search"
                    onclick="cancelTryOut('GETapi-users-search');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users-search"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users/search</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users-search"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users-search"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>q</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="q"                data-endpoint="GETapi-users-search"
               value="john"
               data-component="query">
    <br>
<p>The search query to find users by name or username. Must be at least 1 character. Example: <code>john</code></p>
            </div>
                </form>

                    <h2 id="users-POSTapi-users--user_id--follow">Follow User</h2>

<p>
</p>

<p>Follow the given user.</p>

<span id="example-requests-POSTapi-users--user_id--follow">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/follow';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/follow"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-users--user_id--follow">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1406,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Eulalia VonRueden&quot;,
        &quot;country&quot;: &quot;MF&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-users--user_id--follow" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-users--user_id--follow"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-users--user_id--follow"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-users--user_id--follow" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-users--user_id--follow">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-users--user_id--follow" data-method="POST"
      data-path="api/users/{user_id}/follow"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-users--user_id--follow', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-users--user_id--follow"
                    onclick="tryItOut('POSTapi-users--user_id--follow');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-users--user_id--follow"
                    onclick="cancelTryOut('POSTapi-users--user_id--follow');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-users--user_id--follow"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/users/{user_id}/follow</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-users--user_id--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-users--user_id--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-users--user_id--follow"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-DELETEapi-users--user_id--follow">Unfollow User</h2>

<p>
</p>

<p>Unfollow the given user.</p>

<span id="example-requests-DELETEapi-users--user_id--follow">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/follow';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/follow"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-users--user_id--follow">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1407,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Akeem Hettinger&quot;,
        &quot;country&quot;: &quot;MR&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-users--user_id--follow" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-users--user_id--follow"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-users--user_id--follow"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-users--user_id--follow" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-users--user_id--follow">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-users--user_id--follow" data-method="DELETE"
      data-path="api/users/{user_id}/follow"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-users--user_id--follow', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-users--user_id--follow"
                    onclick="tryItOut('DELETEapi-users--user_id--follow');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-users--user_id--follow"
                    onclick="cancelTryOut('DELETEapi-users--user_id--follow');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-users--user_id--follow"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/users/{user_id}/follow</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-users--user_id--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-users--user_id--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="DELETEapi-users--user_id--follow"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-GETapi-users--user_id--followers">List Followers</h2>

<p>
</p>

<p>List the given user's followers.</p>

<span id="example-requests-GETapi-users--user_id--followers">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/followers';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/followers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users--user_id--followers">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1408,
        &quot;public_name&quot;: &quot;Colt Balistreri&quot;,
        &quot;avatar&quot;: &quot;&quot;,
        &quot;is_following&quot;: false
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-users--user_id--followers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-users--user_id--followers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users--user_id--followers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users--user_id--followers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-users--user_id--followers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-users--user_id--followers" data-method="GET"
      data-path="api/users/{user_id}/followers"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users--user_id--followers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users--user_id--followers"
                    onclick="tryItOut('GETapi-users--user_id--followers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users--user_id--followers"
                    onclick="cancelTryOut('GETapi-users--user_id--followers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users--user_id--followers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users/{user_id}/followers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users--user_id--followers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users--user_id--followers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="GETapi-users--user_id--followers"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-GETapi-users--user_id--following">List Following</h2>

<p>
</p>

<p>List the users that the given user is following.</p>

<span id="example-requests-GETapi-users--user_id--following">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/following';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/following"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users--user_id--following">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1409,
        &quot;public_name&quot;: &quot;Dr. Alan Green&quot;,
        &quot;avatar&quot;: &quot;&quot;,
        &quot;is_following&quot;: false
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-users--user_id--following" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-users--user_id--following"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users--user_id--following"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users--user_id--following" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-users--user_id--following">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-users--user_id--following" data-method="GET"
      data-path="api/users/{user_id}/following"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users--user_id--following', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users--user_id--following"
                    onclick="tryItOut('GETapi-users--user_id--following');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users--user_id--following"
                    onclick="cancelTryOut('GETapi-users--user_id--following');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users--user_id--following"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users/{user_id}/following</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users--user_id--following"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users--user_id--following"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="GETapi-users--user_id--following"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-POSTapi-users--user_id--block">Block User</h2>

<p>
</p>

<p>Block the given user.</p>

<span id="example-requests-POSTapi-users--user_id--block">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/block';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/block"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-users--user_id--block">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1410,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Fausto Conroy&quot;,
        &quot;country&quot;: &quot;IN&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-users--user_id--block" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-users--user_id--block"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-users--user_id--block"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-users--user_id--block" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-users--user_id--block">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-users--user_id--block" data-method="POST"
      data-path="api/users/{user_id}/block"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-users--user_id--block', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-users--user_id--block"
                    onclick="tryItOut('POSTapi-users--user_id--block');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-users--user_id--block"
                    onclick="cancelTryOut('POSTapi-users--user_id--block');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-users--user_id--block"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/users/{user_id}/block</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-users--user_id--block"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-users--user_id--block"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-users--user_id--block"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-DELETEapi-users--user_id--block">Unblock User</h2>

<p>
</p>

<p>Unblock the given user.</p>

<span id="example-requests-DELETEapi-users--user_id--block">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/users/1/block';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/users/1/block"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-users--user_id--block">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1411,
        &quot;updated_at&quot;: &quot;2026-08-24T09:15:56Z&quot;,
        &quot;public_name&quot;: &quot;Garett Runolfsson&quot;,
        &quot;country&quot;: &quot;CD&quot;,
        &quot;game_systems&quot;: [],
        &quot;avatar&quot;: &quot;&quot;,
        &quot;location&quot;: &quot;&quot;,
        &quot;events_count&quot;: 0,
        &quot;followers_count&quot;: 0,
        &quot;following_count&quot;: 0
    }
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-users--user_id--block" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-users--user_id--block"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-users--user_id--block"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-users--user_id--block" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-users--user_id--block">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-users--user_id--block" data-method="DELETE"
      data-path="api/users/{user_id}/block"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-users--user_id--block', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-users--user_id--block"
                    onclick="tryItOut('DELETEapi-users--user_id--block');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-users--user_id--block"
                    onclick="cancelTryOut('DELETEapi-users--user_id--block');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-users--user_id--block"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/users/{user_id}/block</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-users--user_id--block"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-users--user_id--block"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="DELETEapi-users--user_id--block"
               value="1"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="users-GETapi-blocked-users">List Blocked Users</h2>

<p>
</p>

<p>List the authenticated user's blocked users.</p>

<span id="example-requests-GETapi-blocked-users">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/blocked-users';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://battlezones.test/api/blocked-users"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-blocked-users">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 1412,
        &quot;public_name&quot;: &quot;Noemy Klocko&quot;,
        &quot;avatar&quot;: &quot;&quot;,
        &quot;is_following&quot;: false
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-blocked-users" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-blocked-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-blocked-users"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-blocked-users" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-blocked-users">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-blocked-users" data-method="GET"
      data-path="api/blocked-users"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-blocked-users', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-blocked-users"
                    onclick="tryItOut('GETapi-blocked-users');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-blocked-users"
                    onclick="cancelTryOut('GETapi-blocked-users');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-blocked-users"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/blocked-users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-blocked-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-blocked-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
