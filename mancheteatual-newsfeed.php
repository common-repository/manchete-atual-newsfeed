<?php
/*
Plugin Name: Manchete Atual - Newsfeed
Plugin URI: http://mancheteatual.com.br/sites/default/files/newsfeed
Description: Brazilian news, powered with content from Manchete Atual website feeds.
Version: 1.0.2
Author: Luís Peralta @ http://mancheteatual.com.br
Author URI: http://mancheteatual.com.br
License: GPL2
*/

class newsfeed extends WP_Widget {
	private static $feeds = array(
		"Destaques Manchete Atual" => "destaques",
		"Notícias Recentes" => "recentes",
	);
	
	private function parseFeed ($url) {
		$content = file_get_contents($url);  
    	$simpleXml = new SimpleXmlElement($content);
		return $simpleXml;
	}

	// constructor
	function newsfeed() {
 		$widget_ops = array('classname' => 'newsfeed-wrapper', 'description' => __('Brazilian news, powered with content from Manchete Atual website feeds.', 'newsfeed'));
		parent::WP_Widget(false, $name = __('Manchete Atual Newsfeed', 'newsfeed'), $widget_ops);
	}

	// widget form creation
	function form($instance) {	
		// Check values
		if( $instance) {
		     $title = esc_attr($instance['title']);
			 $category = esc_attr($instance['category']);
			 $items = esc_attr($instance['items']);
			 $optin = esc_attr($instance['optin']);
		} else {
		     $title = '';
			 $category = '';
			 $items = '';
			 $optin = '';
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'newsfeed'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Feed', 'newsfeed'); ?></label>
			<select name="<?php echo $this->get_field_name('category'); ?>" id="<?php echo $this->get_field_id('category'); ?>" class="widefat">
			<?php
				foreach (self::$feeds as $name => $option) {
					echo '<option value="' . $option . '" id="' . $option . '"', $category == $option ? ' selected="selected"' : '', '>', $name, '</option>';
				}
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('Items', 'newsfeed'); ?></label>
			<select name="<?php echo $this->get_field_name('items'); ?>" id="<?php echo $this->get_field_id('items'); ?>" class="widefat">
			<?php
				for($i=1; $i<=10; $i++) {
					echo '<option value="' . $i . '" id="' . $i . '"', $items == $i ? ' selected="selected"' : '', '>', $i, '</option>';
				}
			?>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('optin'); ?>" name="<?php echo $this->get_field_name('optin'); ?>" type="checkbox" value="1" <?php checked( '1', $optin ); ?> />
			<label for="<?php echo $this->get_field_id('optin'); ?>"><?php _e('Support Manchete Atual', 'newsfeed'); ?></label>
		</p>
		<?php
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['items'] = strip_tags($new_instance['items']);
		$instance['optin'] = strip_tags($new_instance['optin']);
		return $instance;
	}

	// widget display
	function widget($args, $instance) {
		extract($args);
		
		// these are the widget options
		$title = apply_filters('widget_title', $instance['title']);
		$category = $instance['category'];
		$items = $instance['items'];
		$optin = $instance['optin'];

		// Check player options
		$support = "";
		if( $optin AND $optin == '1' ) {
			$support = '<div>por <a href="http://mancheteatual.com.br/">Manchete Atual</a></div>';
		}

		echo $before_widget;
		echo '<div class="widget-newsfeed wp_widget_plugin_box">';
		if ($title) echo $before_title . $title . $after_title;
		
		//get feed data
		$key = "feed-".$category;
		$group = "newsfeed";
		$data = wp_cache_get( $key, $group );
		if(!$data){
			$data = "";
			$feed = "http://mancheteatual.com.br/noticias/$category/feed.xml";
			$array = self::parseFeed($feed);
			$count = 1;
			
			$data .= "<ul class='newsfeed $key'>";
			foreach($array->channel->item as $entry) {
				$cat = $entry->category;
				$cat = $cat[0];
				$title = $entry->title;
				$link = $entry->link;
				$data .= "<li><a href='$link' title='$title'>$title</a><span>$cat</span></li>";
				
				$count++;
				if(intval($items) < $count) break;
    		}
			$data .= "</ul>".$support;
			wp_cache_set($key, $data, $group, 300);
		}

		echo $data;
		echo "</div>";
		echo $after_widget;

	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("newsfeed");'));

//register css
wp_enqueue_style('newsfeed-css', plugins_url("/css/jquery.mancheteatual.newsfeed.css", __FILE__), false, false, 'all' );
?>
