<?php
/*
Plugin Name: Docwhat's Google Verify Header
Description: Adds my Google verify header
Version: 1.0
Author URI: http://docwhat.org/
*/

function docwhat_google_verify() {
  echo '<meta name="google-site-verification" content="caPZYkV8gUY3XzcNO0khNKflZYZvmpYNAYl280tdzn4" />';
  echo "\n";
}

add_action('wp_head', 'docwhat_google_verify');

