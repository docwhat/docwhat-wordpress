<?php

function searchbox () {
    global $s;
?>
    <form method="get" action="<?php bloginfo('url'); ?>/">
          <input type="text" value="<?php echo $s ?>" name="s" id="s" />
          <input type="submit" id="searchsubmit" value="Search" />
    </form>
<?php
}

$errors = Array(
        404 => "Not Found",
        403 => "Forbidden"
        );

//The error code:
$errno = $_REQUEST["error"];
$error_string = $errors[404];
if (array_key_exists($errno, $errors)) {
    $error_string = $errors[$errno];
} else {
    $errno = 404;
}

//the administrator email address, according to wordpress
$admin_email = get_bloginfo('admin_email');

//gets your blog's url from wordpress
$website = get_bloginfo('url');

//gets your blog's url from wordpress
$websitename = get_bloginfo('name');

function CurrentPageURL() {
    $pageURL = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    $pageURL .= $_SERVER['SERVER_PORT'] != '80' ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] : $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    return $pageURL;
}

//The uri (including hostname portion)
$uri = parse_url(CurrentPageURL());

if ( ($uri['schema'] == 'https' && $uri['port'] == 443) ||
     ($uri['schema'] == 'http' && $uri['port'] == 80) ) {
    $base_uri = $uri['scheme']."://".$uri['host'];
} else {
    $base_uri = $uri['scheme']."://".$uri['host'].':'.$uri['port'];
}

// Calculate the reason
$reason = 'user';
if( $errno == 403 ) {
    $reason = 'forbidden';
    if( !empty($_SERVER['HTTP_REFERER']) &&
        $uri['host'] == $referer['host'] ) {
        $reason = 'our_forbidden';
    } else {
        $reason = 'forbidden';
    }
} else {
    if( !empty($_SERVER['HTTP_REFERER']) ) {
      $referer = parse_url($_SERVER['HTTP_REFERER']);
      if( $uri['host'] == $referer['host'] ) {
          if ($uri['path'] == $referer['path']) {
              $reason = 'badlink';
          } else {
              $reason = 'our_badlink';
          }
      } else {
        $reason = 'badlink';
      }
    }

    # aim:goim links are bad bots.
    if( preg_match('!(/aim:goim\?)!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

    # http or https is a bad spambot.
    if( preg_match('!(/(http|https):/)!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

    # And fragments shouldn't make it this far....
    if( preg_match('!(#|[^%]+%23|\\")!', $_SERVER['HTTP_REFERER']) ) {
        $reason = 'spambot';
    }

    # A new failed spambot attempt.
    # Example:
    # Refering link:
    # http://docwhat.org/2007/11/no-laptop/+%5BPLM=0%5D+GET+http://docwhat.org/2007/11/no-laptop/+%5B0,15030,18391%5D+-%3E+%5BN%5D+POST+http://docwhat.org/post/+%5B0,0,441%5D
    # Bad link:
    # http://docwhat.org/2007/11/no-laptop/+%5BPLM=0%5D+GET+http://docwhat.org/2007/11/no-laptop/+%5B0,15030,18391%5D+-%3E+%5BN%5D+POST+http://docwhat.org/post/+%5B0,0,441%5D

    if( preg_match('!\+%5BPLM=0%5D\+!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

    if( preg_match('!/docwhat@gmail.com!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

    if( preg_match('!register!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

    if( preg_match('!signup!', $_SERVER['REQUEST_URI']) ) {
        $reason = 'spambot';
    }

}

if( $_REQUEST['testreason'] == 'badlink' ) {
  $reason = 'badlink';
  $_SERVER['HTTP_REFERER'] = 'testreason_badlink';
} elseif( $_REQUEST['testreason'] == 'user' ) {
  $reason = 'user';
} elseif( $_REQUEST['testreason'] == 'spambot' ) {
  $reason = 'spambot';
} elseif( $_REQUEST['testreason'] == 'our_badlink' ) {
  $reason = 'our_badlink';
  $_SERVER['HTTP_REFERER'] = 'testreason_our_badlink';
} elseif( $_REQUEST['testreason'] == 'our_forbidden' ) {
  $reason = 'our_forbidden';
  $_SERVER['HTTP_REFERER'] = 'testreason_our_forbidden';
}

/***************************************************************************/
/*********** At this point, the reason should be set in stone. *************/
/***************************************************************************/

header("X-Reason: $reason");

if( $_REQUEST['debugging'] == 'true' ) {
  header("Content-Type: text/plain;");
  print "Debugging Info:\n";
  print "Referrer:    " . $_SERVER['HTTP_REFERER'] . "\n";
  print "Reason:      " . $reason . "\n";
  print "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
  exit();
}

if( $reason == 'spambot' ) {
    // Send them to http://spampoison.com  Yay! Doom!
    header("Location: http://english-89667721068.spampoison.com");
    exit();
}

if( $reason == 'our_badlink' || $reason == 'our_forbidden' ) {
  $body = array(
    "A user tried clicking on a link and got an error message.",
    "Refering link:    ".$_SERVER['HTTP_REFERER'],
    "Bad link:         http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
    "IP:               http://whatismyipaddress.com/ip/".urlencode($_SERVER['REMOTE_ADDR']),
    "Reason:           ".$reason,
    "");
  foreach( $_GET as $key => $value ) {
      array_push($body, sprintf("_GET['%-s'] = '%s'",$key, $value));
  }
  foreach( $_POST as $key => $value ) {
      array_push($body, sprintf("_POST['%-s'] = '%s'",$key, $value));
  }
  foreach( $_SERVER as $key => $value ) {
      if ($key != 'PHP_AUTH_PW' and $key != 'PHP_AUTH_USER') {
          array_push($body, sprintf("%-20s \t%s",$key, $value));
      }
  }
  array_push($body, "

-- \n
automated message from $websitename: $website");
  $ref = '404 from ' . $_SERVER['REMOTE_ADDR'];

  mail( $admin_email,
        "docwhat.org error from ip " . $_SERVER['REMOTE_ADDR'],
        implode("\n", $body),
        "From: $websitename <noreply@$website>\n" .
        "X-Script: docwhat.org:404\n" .
        "In-Reply-To: $ref\n"
        );
}

function str_startswith($haystack, $needle) {
  return (strstr($haystack,$needle) == $haystack);
}

// Resume Catcher
if( preg_match('!resume(|.html|.php|.htm|_html)$!', $uri['path']) ) {
    header("Location: ".$base_uri."/resume/");
    exit();
}

// Pubkey Catcher
if( preg_match('!pubkey(|\.html|\.php|\.htm|_html)$!', $uri['path']) ) {
    header("Location: ".$base_uri."/pubkey/");
    exit();
}

// Mail Catcher
if( preg_match('!(mail|contact)(|\.html|\.php|\.htm|_html)$!', $uri['path']) ) {
    header("Location: ".$base_uri."/email/");
    exit();
}

// Robot Catcher
if( preg_match('!robots\.txt$!', $uri['path']) ) {
    header("Location: ".$base_uri."/robots.txt");
    exit();
}

// View Source Catcher
if( preg_match('!view_source(|\.html|\.php|\.htm|_html)$!', $uri['path']) ) {
    header("Location: ".$base_uri.get_settings('category_base')."/code/");
    exit();
}

$tagbase = get_settings('category_base');

$tags = Array(
              'projects/powerbutton'        => '2006/09/my-older-projects-have-been-moved/',
              'software/palm/powerbutton'   => '2006/09/my-older-projects-have-been-moved/',
              'code/powerbutton'            => '2006/09/my-older-projects-have-been-moved/',
              'cooking'                     => $tagbase.'/cooking',
              'project'                     => $tagbase.'/code',
              'software'                    => $tagbase.'/code',
              'cwimp'                       => $tagbase.'/code',
              'sigchan'                     => $tagbase.'/code',
              'files'                       => '',
              'photos'                      => '',
              'about/bio'                   => 'resume',
              'about'                       => 'about',
              'sitemap'                     => '',
              'copyright'                   => 'about'
              );

foreach( $tags as $search => $tag ) {
    if( str_startswith($uri['path'], '/'.$search) ) {
        header("Location: ".$base_uri."/".ltrim($tag,'/'));
        exit();
    }
}
header("HTTP/1.1 " . $errno . " " . $error_string);

// set a search path that might be useful
$s = trim(implode(' ',explode('/',$uri['path'])));

/**
 ** Render the Page
 **/

get_header();
?>

	<div id="primary">
		<div id="content" role="main">

			<article id="post-0" class="post error404 not-found">

<?php if( $reason == 'our_badlink' )
{
?>
  <h1 class="entry-title">Oops!  I made a mistake! (Error 404 - Not Found)</h1>

  <div class="entry" style="padding-top: 1em">
  <p> It seems that I made a mistake by linking to this url.  I'll probably fix it shortly. </p>
  <p> Till then, you can: </p>
  <ul>
   <li> go back and choose a different link. </li>
   <li> you can try searching for the page this was supposed to link to. </li>
   <li> both </li>
   <li> neither </li>
  </ul>

<p> I'll leave it to you to decide what to do. </p>

   </div>

<?php
} elseif( $reason == 'badlink' ) {
?>

<h1 class="entry-title">You have been led astray!</h1>

<div class="entry" style="padding-top: 1em">

<p> Somewhere, a malicious search engine or website is laughing at
being able to trick you into going to a page that doesn&rsquo;t exist.
Perhaps it was a rough virus?  That&rsquo;s it!  We&rsquo;ll blame a virus!
</p>

<p> Those things are  always making bad things happen.
</p>

<p> It&rsquo;s too late to pretend this didn&rsquo;t happen.  Here are you choices
now: </p>

  <ul>
    <li> You can try the <a href="<?=$website?>">home page</a> where you can browse around till you lose the uneasy feeling that the internet is out to get you. </li>
    <li> You can search for what you thought you were looking using the search box below. </li>
    <li> You can stock up on pitchforks and torches so that the next
    time a "virus" comes by, you can show it <em>what for</em>. </li>
  </ul>

  <p> Personally, I think that pitchforks are always useful to have around.
</p>

   <?php searchbox() ?>
  </div>

<?php
} elseif ($reason == 'our_forbidden') { // '
?>
<h1 class="entry-title">Yipes!  Ignore the man behind the curtain!</h1>

  <div class="entry" style="padding-top: 1em">
  <p>
     Somehow I sent you to a top-secret part of our site!  Please close your eyes and back out slowly!
  </p>

  </div>
<?php
} elseif ($reason == 'forbidden') { // '
?>
<h1 class="entry-title">Naughty, naughty monkey! </h1>

  <div class="entry" style="padding-top: 1em">
  <p>You&rsquo;re trying to access something you aren&rsquo;t allowed to!  You should know better!
  </p>

  </div>
<?php
} else {
?>

 <h1 class="entry-title">Something has gone horribly horribly bad</h1>

  <div class="entry" style="padding-top: 1em">

  <p> Who knows what happened; a slip of the finger, an out-of-date
  web browser, or maybe one of your bytes is wandering lost around the
  internet. </p>

  <p> Things you could do to try to salvage the day: </p>
  <ul>
    <li> You can try the <a href="<?=$website?>">home page</a> where you can browse around till you lose the uneasy feeling that the internet is out to get you. </li>
    <li> You can search for what you are looking for using the handy search box below. </li>
    <li> You can go back to bed and pull the covers over your head. </li>
  </ul>

  <p> I suggest hiding in bed; It seems the safest.
</p>

   <?php searchbox() ?>

  </div>


<?php
} ?>
			</article><!-- #post-0 -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar() ?>
<?php get_footer() ?>
