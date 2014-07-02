<?php
/*
Plugin Name: Docwhat's Widgets
Description: Widgets for Docwhat's site
Version: 1.0
Author URI: http://docwhat.org/
*/

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
    load_template('./alter-egos.php');
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

/* registration. */
function register_docwhat_widgets() {
    if ( function_exists('register_sidebar_widget') ) {
        register_sidebar_widget(__('docwhat Search'), 'widget_docwhat_search');

        register_sidebar_widget(__('docwhat Subscribe'), 'widget_docwhat_subscribe');

        register_sidebar_widget(__('docwhat Pages'), 'widget_docwhat_pages');
        register_widget_control(__('docwhat Pages'), 'widget_docwhat_pages_control');
        register_sidebar_widget(__('docwhat Alter Egos'), 'widget_docwhat_alteregos');
        register_sidebar_widget(__('docwhat CSS Naked Day'), 'widget_docwhat_cssnaked');
        register_sidebar_widget(__('docwhat NULL'), 'widget_docwhat_null');
    }
};
add_action('plugins_loaded', 'register_docwhat_widgets');

add_action('widgets_init', 'Docwhat_Widget_Load');
function Docwhat_Widget_Load() {
  register_widget('Docwhat_Widget_Meta');
}

/* Not bloody likely that I'll need Windows Live Writer link. */
remove_action('wp_head', 'wlwmanifest_link');

/* I'm not a fan of including my wordpress version everywhere. */
function rm_generator_filter() { return ''; }
add_filter('the_generator', 'rm_generator_filter');

?>
