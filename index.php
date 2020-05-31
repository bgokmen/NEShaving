<?php

/**
 * +++++++++++++++++++++++++
 * PHP Microsite Boilerplate
 * +++++++++++++++++++++++++
 * 
 * Version: 1.0.0
 * Creator: Jens Kuerschner (https://jenskuerschner.de)
 * Project: https://github.com/jekuer/php-microsite-boilerplate
 * License: GNU General Public License v3.0	(gpl-3.0)
 */


// Load default configuration.
$language = array();
require_once ('./config.php');


// Load additional functions and classes.
// Include more, if needed.
require_once ('./lib/helper_functions.php');
if ($directus_url != '') require_once ('./lib/directus_connect.php');
require_once ('./class.page.php');


// URL parsing.
$amp = false;
$the_page_url_full = $the_page_url; // holds the base url plus settings path elements (amp, language)
$current_url = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL); // holds the full url incl. slug
require_once ('./lib/url_parsing.php');


/*
// Set Security Headers.
// Only necessary and recommended, if it is not possible on the server side (e.g. via htaccess on Apache).
// For testing and more details, see https://securityheaders.com/ .
header("X-Frame-Options: sameorigin");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Access-Control-Allow-Origin: ". $the_page_url);
// Adjust to your needs. GET should be enough for simple landingpages. Sometimes, you might need 'GET, POST'.
header("Access-Control-Allow-Methods: GET");
// Be careful here. Enabling it limits you on what third-party tools you can include on your page. The provided configuration is only an example.
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.youtube-nocookie.com https://cdn.ampproject.org https://www.googletagmanager.com https://tagmanager.google.com https://www.google-analytics.com https://cookiehub.net; img-src 'self' https://www.gstatic.com https://ssl.gstatic.com https://i.ytimg.com https://stats.g.doubleclick.net https://cdn.ampproject.org https://www.google-analytics.com data:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.ampproject.org https://www.googletagmanager.com https://tagmanager.google.com https://www.google-analytics.com https://cookiehub.net");
// Usually, you would not need any special browser features, so keep them set to 'none'. Some none intrusive things might be helpful for services like YouTube.
header("Feature-Policy: geolocation 'none'; midi 'none'; notifications 'none'; push 'none'; sync-xhr 'none'; microphone 'none'; camera 'none'; magnetometer 'none'; gyroscope '*'; accelerometer '*'; encrypted-media '*'; ambient-light-sensor 'none'; usb 'none'; vr 'none'; speaker 'none'; vibrate 'none'; fullscreen 'self'; payment 'none'");
// Try to hide the server's identity.
header_remove("X-Powered-By");

// Some performance and caching adjustments - if not possible on the server side.
header("Connection: keep-alive");
header_remove("ETag");
*/


// Routing.
require_once ('./routing.php');
if (!isset($url_parts[0]) or $url_parts[0] == '') $url_parts[0] = 'main';
$page_id = $url_parts[0];


// Check for deployment hook call (GitHub).
if ($page_id == 'deploy') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once ('./deploy.php'); // Adjust to a file of yours, where you run a Git pull command and maybe more. Mind to do some checksum test there and do NOT include this file within the repo (or use secured variables). A sample file is included in this repo.
    die();
  } else {
    http_response_code(400);
    $page_id = 'error';
  }
}


// In all other cases, prepare page.
$the_page = new Page($page_id, $pages[$language['active']]);


// Render page (compressed and with stripped HTML comments).
ob_start("ob_html_compress");
if ($amp) {
  if ($the_page->controller != '') include_once ('./controller/'. $the_page->controller .'.php');
  include_once ('./templates/header_amp.php');
  include_once ('./pages/'. $the_page->view .'.php');
  include_once ('./templates/footer_amp.php');
} else {
  if ($the_page->controller != '') include_once ('./controller/'. $the_page->controller .'.php');
  include_once ('./templates/header.php');
  include_once ('./pages/'. $the_page->view .'.php');
  include_once ('./templates/footer.php');
}
ob_end_flush();


?>