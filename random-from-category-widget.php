<?php
/*
Plugin Name: Random Posts from Category
Plugin URI: http://sillybean.net/code/wordpress/
Description: A widget that lists random posts from a chosen category.
Version: 1.30
Author: Stephanie Leary
Author URI: http://sillybean.net/
Text Domain: random-posts-from-category 
License: GPL2
*/

class RandomPostsFromCategory extends WP_Widget {

	function __construct() {
			$widget_ops = array('classname' => 'random_from_cat', 'description' => __( 'random posts from a chosen category', 'random-posts-from-category') );
			parent::__construct('RandomPostsFromCategory', __('Random Posts from Category', 'random-posts-from-category'), $widget_ops);
	}
	
	
	function widget( $args, $instance ) {
			extract( $args );
			
			$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Random Posts' , 'random-posts-from-category') : $instance['title']);
			
			echo $before_widget;
			if ( $title ) {
				if ($instance['postlink'] == 1)  {
					$before_title .= '<a href="'.get_category_link($instance['cat']).'">';
					$after_title = '</a>'.$after_title;
				}
				echo $before_title.$title.$after_title;
			}
			
			$args = array(
				'cat' => $instance['cat'],
				'posts_per_page' => $instance['showposts'],
				'orderby' => 'rand',
			);
			$args = apply_filters( 'random_posts_from_category_args', $args );
			$random = new WP_Query( $args ); 
			// the Loop
			if ($random->have_posts()) : 
				echo '<ul>';
				while ($random->have_posts()) : $random->the_post(); ?>
	                <li>
					<?php
						if ($instance['content'] != 'excerpt-notitle' && $instance['content'] != 'content-notitle') { ?>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<?php
					} 
					if ($instance['content'] == 'excerpt' || $instance['content'] == 'excerpt-notitle') {
						if (function_exists('the_excerpt_reloaded')) 
							the_excerpt_reloaded($instance['words'], $instance['tags'], 'content', FALSE, '', '', '1', '');
						else the_excerpt();  // this covers Advanced Excerpt as well as the built-in one
					}
					if ($instance['content'] == 'content' || $instance['content'] == 'content-notitle') the_content();
				endwhile;
				echo '</ul>';
			endif;
			
			echo $after_widget;
			wp_reset_query();
	}
	
	
	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = sanitize_text_field($new_instance['title']);
			$instance['cat'] = intval($new_instance['cat']);
			$instance['showposts'] = intval($new_instance['showposts']);
			$instance['content'] = sanitize_text_field($new_instance['content']);
			$instance['postlink'] = intval($new_instance['postlink']);
			$instance['words'] = intval($new_instance['words']);
			$instance['tags'] = $new_instance['tags'];
			return $instance;
	}

	function form( $instance ) {
			//Defaults
				$instance = wp_parse_args( (array) $instance, array( 
						'title' => __('Recent Posts', 'random-posts-from-category'),
						'cat' => 1,
						'showposts' => 1,
						'content' => 'title',
						'postlink' => 0,
						'words' => '99999',
						'tags' => '<p><div><span><br><img><a><ul><ol><li><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6>'));	
	?>  
       
<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'random-posts-from-category'); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
	name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Show posts from category:', 'random-posts-from-category'); ?></label> 
<?php wp_dropdown_categories(array('name' => $this->get_field_name('cat'), 'hide_empty'=>0, 'hierarchical'=>1, 'selected'=>$instance['cat'])); ?></label>
</p>

<p>
<input id="<?php echo $this->get_field_id('postlink'); ?>" name="<?php echo $this->get_field_name('postlink'); ?>" 
	type="checkbox" <?php if ($instance['postlink']) { ?> checked="checked" <?php } ?> value="1" />
<label for="<?php echo $this->get_field_id('postlink'); ?>"><?php _e('Link widget title to category archive', 'random-posts-from-category'); ?></label>
</p>

<p><label for="<?php echo $this->get_field_id('showposts'); ?>"><?php _e('Number of posts to show:', 'random-posts-from-category'); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id('showposts'); ?>" name="<?php echo $this->get_field_name('showposts'); ?>" 
	type="text" value="<?php echo $instance['showposts']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('content'); ?>"><?php _e('Display:', 'random-posts-from-category'); ?></label> 
<select id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>" class="postform">
	<option value="title"<?php selected( $instance['content'], 'title' ); ?>><?php _e('Title Only', 'random-posts-from-category'); ?></option>
	<option value="excerpt"<?php selected( $instance['content'], 'excerpt' ); ?>><?php _e('Title and Excerpt', 'random-posts-from-category'); ?></option>
	<option value="excerpt-notitle"<?php selected( $instance['content'], 'excerpt-notitle' ); ?>><?php _e('Excerpt without Title', 'random-posts-from-category'); ?></option>
	<option value="content"<?php selected( $instance['content'], 'content' ); ?>><?php _e('Title and Content', 'random-posts-from-category'); ?></option>
	<option value="content-notitle"<?php selected( $instance['content'], 'content-notitle' ); ?>><?php _e('Content without Title', 'random-posts-from-category'); ?></option>
</select>
</p>

<?php
if (function_exists('the_excerpt_reloaded')) { ?>
<p>
<label for="<?php echo $this->get_field_id('words'); ?>"><?php _e('Limit excerpts to how many words?:', 'random-posts-from-category'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('words'); ?>" name="<?php echo $this->get_field_name('words'); ?>" 
	type="text" value="<?php echo esc_attr($instance['words']); ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('tags'); ?>"><?php _e('Allowed HTML tags in excerpts:', 'random-posts-from-category'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" 
	type="text" value="<?php echo htmlspecialchars($instance['tags'], ENT_QUOTES); ?>" />
<br />
<small><?php _e('E.g.: ', 'random-posts-from-category'); ?>&lt;p&gt;&lt;div&gt;&lt;span&gt;&lt;br&gt;&lt;img&gt;&lt;a&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;blockquote&gt;&lt;cite&gt;&lt;em&gt;&lt;i&gt;&lt;strong&gt;&lt;b&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;&lt;h5&gt;&lt;h6&gt;
</small>
</p>
<?php } // end if function_exists

	} // function form
} // widget class

function random_from_cat_init() {
	register_widget('RandomPostsFromCategory');
}

add_action('widgets_init', 'random_from_cat_init');

// i18n
load_plugin_textdomain( 'RandomPostsFromCategory', false, plugin_dir_path(__FILE__) . '/languages' );