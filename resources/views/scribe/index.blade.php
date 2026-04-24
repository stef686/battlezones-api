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
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.9.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.9.0.js") }}"></script>

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
                                                                                <li class="tocify-item level-2" data-unique="events-GETapi-events--event_slug--gallery">
                                <a href="#events-GETapi-events--event_slug--gallery">List Event Gallery</a>
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
        <li>Last updated: April 24, 2026</li>
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
        &quot;id&quot;: 43,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
            'recipient_ids' =&gt; [
                16,
            ],
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
        &quot;id&quot;: 44,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
<p>The <code>id</code> of an existing record in the users table.</p>
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
        &quot;id&quot;: 19,
        &quot;conversation_id&quot;: 45,
        &quot;user_id&quot;: 201,
        &quot;body&quot;: &quot;Animi quos velit et fugiat. Nihil accusantium harum mollitia modi deserunt. Ab provident perspiciatis quo omnis nostrum aut adipisci.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
            'recipient_ids' =&gt; [
                16,
            ],
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
        &quot;id&quot;: 46,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
<p>The <code>id</code> of an existing record in the users table.</p>
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
        &quot;id&quot;: 47,
        &quot;is_group&quot;: false,
        &quot;name&quot;: null,
        &quot;participants&quot;: [],
        &quot;is_archived&quot;: false,
        &quot;unread_count&quot;: 0,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
        &quot;id&quot;: 20,
        &quot;conversation_id&quot;: 48,
        &quot;user_id&quot;: 202,
        &quot;body&quot;: &quot;Et animi quos velit et fugiat. Nihil accusantium harum mollitia modi deserunt. Ab provident perspiciatis quo omnis nostrum aut adipisci.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
        &quot;id&quot;: 21,
        &quot;conversation_id&quot;: 49,
        &quot;user_id&quot;: 203,
        &quot;body&quot;: &quot;Et animi quos velit et fugiat. Nihil accusantium harum mollitia modi deserunt. Ab provident perspiciatis quo omnis nostrum aut adipisci.&quot;,
        &quot;type&quot;: null,
        &quot;is_system&quot;: false,
        &quot;is_deleted&quot;: false,
        &quot;is_edited&quot;: false,
        &quot;is_editable&quot;: true,
        &quot;edited_at&quot;: null,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
        &quot;id&quot;: 36,
        &quot;name&quot;: &quot;Eius et animi&quot;,
        &quot;slug&quot;: &quot;eius-et-animi-565&quot;,
        &quot;description&quot;: &quot;Sunt nihil accusantium harum mollitia. Deserunt aut ab provident perspiciatis quo omnis nostrum. Adipisci quidem nostrum qui commodi incidunt iure.&quot;,
        &quot;status&quot;: &quot;draft&quot;,
        &quot;pairing_format&quot;: &quot;swiss&quot;,
        &quot;starts_at&quot;: &quot;2026-08-21T10:03:46Z&quot;,
        &quot;ends_at&quot;: &quot;2026-08-23T10:03:46Z&quot;,
        &quot;max_attendees&quot;: 100,
        &quot;venue&quot;: {
            &quot;name&quot;: &quot;McLaughlin, Leuschke and Bauch Hall&quot;,
            &quot;address&quot;: &quot;45058 Bailee Rue&quot;,
            &quot;city&quot;: &quot;South Matildaburgh&quot;,
            &quot;country&quot;: &quot;IE&quot;
        },
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
<p>Filter by game system slug. The <code>slug</code> of an existing record in the game_systems table. Example: <code>warhammer-40k</code></p>
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
        &quot;id&quot;: 37,
        &quot;name&quot;: &quot;Fugit deleniti distinctio eum&quot;,
        &quot;slug&quot;: &quot;fugit-deleniti-distinctio-eum-65128&quot;,
        &quot;description&quot;: &quot;Aliquam veniam corporis dolorem mollitia deleniti nemo. Quia officia est dignissimos neque. Odio veritatis excepturi doloribus delectus fugit qui repudiandae. Est alias tenetur ratione.&quot;,
        &quot;status&quot;: &quot;draft&quot;,
        &quot;pairing_format&quot;: &quot;swiss&quot;,
        &quot;starts_at&quot;: &quot;2026-08-10T16:56:01Z&quot;,
        &quot;ends_at&quot;: &quot;2026-08-12T16:56:01Z&quot;,
        &quot;max_attendees&quot;: 40,
        &quot;venue&quot;: {
            &quot;name&quot;: &quot;Casper-Smith Hall&quot;,
            &quot;address&quot;: &quot;5481 Dibbert Stravenue Suite 237&quot;,
            &quot;city&quot;: &quot;Ednaside&quot;,
            &quot;country&quot;: &quot;AU&quot;
        },
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
        &quot;id&quot;: 13,
        &quot;title&quot;: &quot;Quia maiores assumenda&quot;,
        &quot;body&quot;: &quot;Repellat officiis corporis nesciunt ut. Iure impedit molestiae ut rem est esse sint. Molestiae sunt suscipit doloribus fugiat ut aut. Et error neque recusandae et.\n\nEt ut dicta vitae assumenda consequatur. Et sunt quisquam sit repellendus ut.&quot;,
        &quot;pinned&quot;: false,
        &quot;published_at&quot;: &quot;2026-03-27T19:57:13Z&quot;,
        &quot;author&quot;: {
            &quot;id&quot;: 204,
            &quot;name&quot;: &quot;Christian Klocko PhD&quot;
        },
        &quot;created_at&quot;: &quot;2026-04-24T20:33:54Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:54Z&quot;
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
$url = 'https://battlezones.test/api/events/summer-showdown-2026/attendees';
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
    "https://battlezones.test/api/events/summer-showdown-2026/attendees"
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
        &quot;id&quot;: 41,
        &quot;user&quot;: {
            &quot;id&quot;: 205,
            &quot;name&quot;: &quot;Rodrigo O&#039;Connell&quot;
        },
        &quot;faction&quot;: {
            &quot;id&quot;: 23,
            &quot;name&quot;: &quot;Tenetur Ratione&quot;
        },
        &quot;clubs&quot;: []
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--attendees"
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

                    <h2 id="events-GETapi-events--event_slug--attendees--id-">Show Event Attendee</h2>

<p>
</p>

<p>Attendee detail for a publicly visible event.</p>

<span id="example-requests-GETapi-events--event_slug--attendees--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/attendees/1';
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
    "https://battlezones.test/api/events/summer-showdown-2026/attendees/1"
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
        &quot;id&quot;: 42,
        &quot;user&quot;: {
            &quot;id&quot;: 206,
            &quot;name&quot;: &quot;Cordia Cummings&quot;
        },
        &quot;faction&quot;: {
            &quot;id&quot;: 24,
            &quot;name&quot;: &quot;Accusantium Odit&quot;
        },
        &quot;clubs&quot;: [],
        &quot;army_list&quot;: &quot;Modi rerum ex repellendus assumenda et tenetur. Reiciendis quia perspiciatis deserunt ducimus corrupti et. Quia maiores assumenda odit. Repellat officiis corporis nesciunt ut.&quot;,
        &quot;checked_in_at&quot;: null,
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
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
<p>The ID of the attendee. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>attendee</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="attendee"                data-endpoint="GETapi-events--event_slug--attendees--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the attendee. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--rounds">List Event Rounds</h2>

<p>
</p>

<p>List rounds for an event. Only visible for Active/Completed events.</p>

<span id="example-requests-GETapi-events--event_slug--rounds">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/rounds';
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
    "https://battlezones.test/api/events/summer-showdown-2026/rounds"
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
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;&quot;
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--rounds"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--rounds--id-">Show Event Round</h2>

<p>
</p>

<p>Round detail with games for an event.</p>

<span id="example-requests-GETapi-events--event_slug--rounds--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/rounds/1';
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
    "https://battlezones.test/api/events/summer-showdown-2026/rounds/1"
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
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Round] 1&quot;
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
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
<p>The ID of the round. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>round</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="round"                data-endpoint="GETapi-events--event_slug--rounds--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the round. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--games--id-">Show Event Game</h2>

<p>
</p>

<p>Game detail with full score breakdown and army lists.</p>

<span id="example-requests-GETapi-events--event_slug--games--id-">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/games/1';
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
    "https://battlezones.test/api/events/summer-showdown-2026/games/1"
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
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;&quot;
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
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
<p>The ID of the game. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>game</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="game"                data-endpoint="GETapi-events--event_slug--games--id-"
               value="1"
               data-component="url">
    <br>
<p>The id of the game. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="events-GETapi-events--event_slug--standings">List Event Standings</h2>

<p>
</p>

<p>Paginated standings for a publicly visible event with optional search and sorting.</p>

<span id="example-requests-GETapi-events--event_slug--standings">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/standings';
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
    "https://battlezones.test/api/events/summer-showdown-2026/standings"
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
    &quot;data&quot;: {
        &quot;id&quot;: 29,
        &quot;position&quot;: 76,
        &quot;attendee&quot;: {
            &quot;id&quot;: 43,
            &quot;name&quot;: &quot;Jovanny Cartwright&quot;,
            &quot;faction&quot;: {
                &quot;id&quot;: 25,
                &quot;name&quot;: &quot;Sed Rem&quot;
            },
            &quot;clubs&quot;: []
        },
        &quot;scores&quot;: []
    }
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--standings"
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

                    <h2 id="events-GETapi-events--event_slug--gallery">List Event Gallery</h2>

<p>
</p>

<p>Paginated photos for a publicly visible event.</p>

<span id="example-requests-GETapi-events--event_slug--gallery">
<blockquote>Example request:</blockquote>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'https://battlezones.test/api/events/summer-showdown-2026/gallery';
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
    "https://battlezones.test/api/events/summer-showdown-2026/gallery"
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
        &quot;id&quot;: 45,
        &quot;name&quot;: &quot;In quod id aspernatur.&quot;,
        &quot;description&quot;: null,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/488b4973-cced-39cd-9019-4e5670dd35d4.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/338aa13c-ee9b-3c59-9dad-eeca56f85ba2.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:54Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:54Z&quot;
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
               value="summer-showdown-2026"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>summer-showdown-2026</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>event</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="event"                data-endpoint="GETapi-events--event_slug--gallery"
               value="london-grand-tournament"
               data-component="url">
    <br>
<p>The slug of the event. Example: <code>london-grand-tournament</code></p>
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
        &quot;id&quot;: 40,
        &quot;name&quot;: &quot;Eum omnis autem et ut.&quot;,
        &quot;description&quot;: null,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/756f42fe-19b4-314c-ab4b-e7d4d22d9ece.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/2470e22c-104d-39a9-be22-d184758f5176.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
                'contents' =&gt; fopen('/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/phpk4vhkvtrdhgo9J21lN1', 'r')
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
        &quot;id&quot;: 41,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Fugiat sunt nihil accusantium harum mollitia. Deserunt aut ab provident perspiciatis quo omnis nostrum. Adipisci quidem nostrum qui commodi incidunt iure.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/d9be5934-80e7-34a9-a136-841b5f0aea83.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/6c868c6d-85fb-3170-87f4-2bdcd8769991.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
<p>The photo file (jpg, jpeg, png, or webp, max 10MB). Must be an image. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/phpk4vhkvtrdhgo9J21lN1</code></p>
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
        &quot;id&quot;: 42,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Fugiat sunt nihil accusantium harum mollitia. Deserunt aut ab provident perspiciatis quo omnis nostrum. Adipisci quidem nostrum qui commodi incidunt iure.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/d9be5934-80e7-34a9-a136-841b5f0aea83.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/6c868c6d-85fb-3170-87f4-2bdcd8769991.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
                'contents' =&gt; fopen('/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/php04ivn7cl0gub6QnwjLP', 'r')
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
        &quot;id&quot;: 43,
        &quot;name&quot;: &quot;Eius et animi.&quot;,
        &quot;description&quot;: &quot;Fugiat sunt nihil accusantium harum mollitia. Deserunt aut ab provident perspiciatis quo omnis nostrum. Adipisci quidem nostrum qui commodi incidunt iure.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/d9be5934-80e7-34a9-a136-841b5f0aea83.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/6c868c6d-85fb-3170-87f4-2bdcd8769991.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
<p>A replacement photo file (jpg, jpeg, png, or webp, max 10MB). Must be an image. Must not be greater than 10240 kilobytes. Example: <code>/private/var/folders/tv/6pq_d1gn2zvcmbpxs5428yv80000gn/T/php04ivn7cl0gub6QnwjLP</code></p>
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
        &quot;id&quot;: 44,
        &quot;name&quot;: &quot;Eum doloremque id aut.&quot;,
        &quot;description&quot;: &quot;Corporis dolorem mollitia deleniti nemo odit quia officia. Dignissimos neque blanditiis odio.&quot;,
        &quot;url&quot;: &quot;https://battlezones.test/storage/photos/dc407d02-9b9b-35e5-8d69-87904a6787e3.jpg&quot;,
        &quot;thumbnail_url&quot;: &quot;https://battlezones.test/storage/photos/thumbs/b3dfd3b4-abf6-34e6-9ab5-ef739060a5da.jpg&quot;,
        &quot;created_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;
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
        &quot;id&quot;: 180,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Ms. Elisabeth Okuneva&quot;,
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
        &quot;id&quot;: 181,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
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
        &quot;id&quot;: 182,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Ms. Audra Crooks II&quot;,
        &quot;country&quot;: &quot;IL&quot;,
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
            ]
        },
        &quot;message_requests&quot;: {
            &quot;label&quot;: &quot;Message Requests&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
        },
        &quot;event_messages&quot;: {
            &quot;label&quot;: &quot;Event Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
        },
        &quot;round_live&quot;: {
            &quot;label&quot;: &quot;Round Live&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
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
            'primary_messages' =&gt; [
                'push',
            ],
            'message_requests' =&gt; [
                'push',
            ],
            'event_messages' =&gt; [
                'push',
            ],
            'round_live' =&gt; [
                'email',
            ],
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
        "email"
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
            ]
        },
        &quot;message_requests&quot;: {
            &quot;label&quot;: &quot;Message Requests&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
        },
        &quot;event_messages&quot;: {
            &quot;label&quot;: &quot;Event Messages&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
        },
        &quot;round_live&quot;: {
            &quot;label&quot;: &quot;Round Live&quot;,
            &quot;channels&quot;: [
                &quot;email&quot;
            ]
        }
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
            &quot;id&quot;: 187,
            &quot;public_name&quot;: &quot;Ms. Audra Crooks II&quot;,
            &quot;username&quot;: &quot;breitenberg.gilbert&quot;
        },
        {
            &quot;id&quot;: 188,
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
        &quot;id&quot;: 189,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Mr. Mateo Huel&quot;,
        &quot;country&quot;: &quot;MW&quot;,
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
        &quot;id&quot;: 190,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Elda Frami&quot;,
        &quot;country&quot;: &quot;WF&quot;,
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
        &quot;id&quot;: 191,
        &quot;public_name&quot;: &quot;Ms. Billie Beer&quot;,
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
        &quot;id&quot;: 192,
        &quot;public_name&quot;: &quot;Myrl Kshlerin&quot;,
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
        &quot;id&quot;: 193,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Prof. Elouise Schiller&quot;,
        &quot;country&quot;: &quot;BY&quot;,
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
        &quot;id&quot;: 194,
        &quot;updated_at&quot;: &quot;2026-04-24T20:33:53Z&quot;,
        &quot;public_name&quot;: &quot;Kendra Rice&quot;,
        &quot;country&quot;: &quot;BW&quot;,
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
        &quot;id&quot;: 195,
        &quot;public_name&quot;: &quot;Prof. Coralie Roob I&quot;,
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
