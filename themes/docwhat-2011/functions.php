<?php

add_action( 'after_setup_theme', 'docwhat_theme_setup' );

add_action('wp_head', 'docwhat_headers');
function docwhat_headers() {
  /*
   * device-width
   *      Occupy full width of the screen in its current orientation
   * initial-scale = 1.0
   *      retains dimensions instead of zooming out if page height > device height
   * maximum-scale = 1.0
   *      retains dimensions instead of zooming in if page width < device width
   */
  echo "\n";
  echo '<meta name="viewport" content="width=device-width, maximum-scale=1.0" />';
  echo "\n";
}

function docwhat_theme_setup() {
        // add the_post_thumbnail() wherever thumbnail should appear
        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size(250, 250);
}

// Add post thumbnail to post excerpt
$docwhat_count = 0;
function docwhat_add_post_thumb($content) {
  global $docwhat_count;
  if (has_post_thumbnail()) {
    global $id;
    $align = $docwhat_count++ % 2 == 0 ? 'left' : 'right';
    return get_the_post_thumbnail(NULL,
                                  array(250,250),
                                  array('class' => 'wp-post-image-' . $align)) . $content;
  } else {
    return $content;
  }
}
// Add it to the excerpt on the home page.
add_filter('the_excerpt', 'docwhat_add_post_thumb');
// Add it to the content for a single post.
add_filter('the_content',  'docwhat_add_post_thumb');

// Add custom js
function my_scripts_method() {
  wp_register_script( 'my_site_script', get_stylesheet_directory_uri() . '/js/script.js', array(), false, true );
  wp_enqueue_script( 'my_site_script' );
}
add_action('wp_enqueue_scripts', 'my_scripts_method');

// Use dquo spans for first double quote.
function docwhat_dquo($text) {
  return preg_replace('/&#8220;/', '<span class="dquo">&#8220;</span>', $text);
}
add_filter('the_content',  'docwhat_dquo');
add_filter('the_excerpt',  'docwhat_dquo');
add_filter('comment_text', 'docwhat_dquo');

// Better comment layout
function toolbox_comment( $comment, $args, $depth ) {
  $GLOBALS['comment'] = $comment;
  switch ( $comment->comment_type ) :
  case '' :
    ?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
  <article id="comment-<?php comment_ID(); ?>" class="comment">
    <header>
      <?php echo get_avatar( $comment, 40 ); ?>
      <div class="reply">
        <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
      </div><!-- /.reply -->
    </header>

    <section>
      <div class="comment-author vcard">
       <?php printf( __( '%s <span class="says">says:</span>', 'toolbox' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
      </div><!-- .comment-author .vcard -->

      <?php if ( $comment->comment_approved == '0' ) : ?>
      <em><?php _e( 'Your comment is awaiting moderation.', 'toolbox' ); ?></em>
      <br />
      <?php endif; ?>

      <div class="comment-content"><?php comment_text(); ?></div>

      <footer class="comment-meta commentmetadata">
        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>"><time pubdate datetime="<?php comment_time( 'c' ); ?>">
        <?php
          /* translators: 1: date, 2: time */
          printf( __( '%1$s at %2$s', 'toolbox' ), get_comment_date(),  get_comment_time() ); ?>
        </time></a>
        <?php edit_comment_link( __( '(Edit)', 'toolbox' ), ' ' ); ?>
      </footer><!-- .comment-meta .commentmetadata -->
    </section>
  </article><!-- #comment-##  -->

<?php
      break;
      case 'pingback'  :
      case 'trackback' :
?>
<li class="post pingback">
 <p><?php _e( 'Pingback:', 'toolbox' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'toolbox'), ' ' ); ?></p>
<?php
   break;
 endswitch;
}

/*********************************
  Search Widget
 *********************************/
function widget_docwhat_search() {
?>
<li class="nomin gradwhite boxc" id="sidebar_search">
    <form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
      <div id="searchformbox">
        <input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
        <input type="submit" id="searchsubmit" value="Search" />
      </div>
    </form>
</li>
<?php
}


/*********************************
  Meta
 *********************************/
class Docwhat_Widget_Meta extends WP_Widget {

  function Docwhat_Widget_Meta() {
    $widget_ops = array('classname' => 'widget_docwhat_meta', 'description' => __( "Log in/out, and admin links") );
    $this->WP_Widget('docwhat_meta', __('Docwhat Meta'), $widget_ops);
  }

  function widget( $args, $instance ) {
    extract($args);
    if (empty($instance['title'])) {
      $title = '';
    } else {
      $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
    }

    echo $before_widget;
    if ( $title ) {
      echo $before_title . $title . $after_title;
    }
    ?>
    <ul>
       <?php wp_register(); ?>
       <li><?php wp_loginout(); ?></li>
	<?php wp_meta(); ?>
    </ul>
<?php
      echo $after_widget;
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);

    return $instance;
  }

  function form( $instance ) {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = strip_tags($instance['title']);
    ?>
    <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
  }
}


/*********************************
  Subscribe
 *********************************/
function widget_docwhat_subscribe($args) {
    extract($args);
    $title   = __('Subscribe');

    echo $before_widget . $before_title . $title . $after_title . "<ul>\n";
?>
     <li id="docwhat-feed-rss">
       <a href="<?php bloginfo('rss2_url'); ?>" title="Subscribe to this Feed via RSS">Subscribe via RSS</a>
     </li>
     <li id="docwhat-feed-email">
       <a target="_blank" href="http://www.feedburner.com/fb/a/emailverifySubmit?feedId=773095" title="Subscribe via Email" >Subscribe via eMail</a>
     </li>
</ul>
    <?php if(function_exists('honeypot_link')) {echo honeypot_link();} ?>
<?php
    echo $after_widget;
}


/*********************************
  Pages
 *********************************/
function widget_docwhat_pages($args) {
    extract($args);
    $options = get_option('widget_pages');
    $title = empty($options['title']) ? __('Pages') : $options['title'];
    $depth = empty($options['depth']) ? '' : '&depth='.intval($options['depth']);
    echo $before_widget . $before_title . $title . $after_title . "<ul>\n";
    wp_list_pages("title_li=".$depth);
    echo "</ul>";
    ?>
    <?php
    echo $after_widget;
}
function widget_docwhat_pages_control() {
    $options = $newoptions = get_option('widget_pages');
    if ( $_POST["pages-submit"] ) {
        // Clean up control form submission options
        $newoptions['title'] = strip_tags(stripslashes($_POST["pages-title"]));
        $newoptions['depth'] = strip_tags(stripslashes($_POST["pages-depth"]));
    }
    // If original widget options do not match control form
    // submission options, update them.
    if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('widget_pages', $options);
    }

    $title = attribute_escape($options['title']);
    $depth = attribute_escape($options['depth']);
?>
            <p><label for="pages-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="pages-title" name="pages-title" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="pages-depth"><?php _e('Depth:'); ?> <input style="width: 2em;" id="pages-depth" name="pages-depth" type="text" value="<?php echo $depth; ?>" /></label></p>
            <input type="hidden" id="pages-submit" name="pages-submit" value="1" />
<?php
}



/*********************************
  Alteregos
 *********************************/
function widget_docwhat_alteregos($args) {
    extract($args);
    $options = get_option('widget_pages');
    $title   = __('Alter Egos');

    echo $before_widget;
    echo $before_title . $title . $after_title;
    load_template(get_stylesheet_directory() . '/alter-egos.php');
    echo $after_widget;
}

/*********************************
  CSS Naked Day Widget
 *********************************/
function widget_docwhat_cssnaked() {
   if (function_exists('is_naked_day') && is_naked_day()) {
   echo $before_widget . $before_title . $title . $after_title . "<ul>\n";
?>  <h2>What happened to the design?</h2>
    <p>To know more about why styles are disabled on this website
      visit the <a href="http://naked.dustindiaz.com" title="Web
      Standards Naked Day Host Website">Annual CSS Naked Day</a>
      website for more information.</p>
<?php
   echo $after_widget;
  }
}

/*********************************
  Null Widget
 *********************************/
function widget_docwhat_null() {
}

add_action('widgets_init', 'Docwhat_Widget_Load');
function Docwhat_Widget_Load() {
  register_widget('Docwhat_Widget_Meta');
  register_sidebar_widget(__('docwhat Search'), 'widget_docwhat_search');

  register_sidebar_widget(__('docwhat Subscribe'), 'widget_docwhat_subscribe');

  register_sidebar_widget(__('docwhat Pages'), 'widget_docwhat_pages');
  register_widget_control(__('docwhat Pages'), 'widget_docwhat_pages_control');
  register_sidebar_widget(__('docwhat Alter Egos'), 'widget_docwhat_alteregos');
  register_sidebar_widget(__('docwhat CSS Naked Day'), 'widget_docwhat_cssnaked');
  register_sidebar_widget(__('docwhat NULL'), 'widget_docwhat_null');
}

/* Not bloody likely that I'll need Windows Live Writer link. */
remove_action('wp_head', 'wlwmanifest_link');

/* I'm not a fan of including my wordpress version everywhere. */
function rm_generator_filter() { return ''; }
add_filter('the_generator', 'rm_generator_filter');

// EOF