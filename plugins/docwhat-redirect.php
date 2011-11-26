<?php
/*
Plugin Name: Docwhat's Redirect
Description: Redirection for Docwhat's site
Version: 1.0
Author URI: http://docwhat.org/
*/

/*
http://willnorris.com/2008/12/challenges-in-changing-my-openid
*/

function docwhat_redirect_wp($wp) {
    // Everything is copacetic, don't do anything.
    if (strpos($_SERVER['SCRIPT_URI'], 'http://docwhat.org/', 0) == 0) return;

    // only redirect plain home page requests
    if (!is_front_page() && !is_home()) return;
    if (!empty($_SERVER['QUERY_STRING'])) return;

    // don't redirect OpenID requests
    if (stripos($_SERVER['HTTP_ACCEPT'], 'application/xrds+xml') !== FALSE) return;
    if (stripos($_SERVER['HTTP_USER_AGENT'], 'openid') !== FALSE) return;
    if (empty($_SERVER['HTTP_USER_AGENT'])) return;

    wp_redirect('http://docwhat.org/', 301);
    exit;
}

add_action('wp', 'docwhat_redirect_wp');

