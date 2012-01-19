<?php
/*
Plugin Name: Валутни Курсове
Plugin Script: valutni-kursove.php
Plugin URI: http://marto.lazarov.org/plugins/valutni-kursove
Description: Easiest way to show exchange rates of the Bulgarian National Bank
Version: 2.0.0
Author: mlazarov
Author URI: http://marto.lazarov.org/
*/

if (class_exists('WP_Widget')) {
	class Valutni_Kursove_Widget extends WP_Widget {
		var $plugin;
		var $plugin_url;
		var $settings;
		var $currencies = array('AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK',
								'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS',
								'INR', 'JPY', 'KRW', 'LTL', 'LVL', 'MXN', 'MYR',
								'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'RUB', 'SEK',
								'SGD', 'THB', 'TRY', 'USD', 'XAU', 'ZAR');
		function Valutni_Kursove_Widget(){
			$widget_ops = array(
							'classname' => 'widget_valutni_kursove',
							'description' => 'Show exchange rates of the Bulgarian National Bank.<br/> Изведете лесно валутните курсове на БНБ във вашия блог' );
			$this->WP_Widget('valutni_kursove', 'Валутни Курсове', $widget_ops);
			$this->settings = $this->get_settings();
			if(!$this->settings['updated'] || $this->settings['updated']<time()-3600) $this->getFreshData();
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['currencies'] = (array)$new_instance['currencies'];
			return $instance;
		}
		function form($instance) {
			$plugin = get_plugin_data( __FILE__ );
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Валутни курсове', 'width' => '300', 'height' => '400' ) );
			$title = strip_tags($instance['title']);
			$currencies = (array)$instance['currencies'];
			?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('currencies'); ?>">Select currencies:<br/>
					<?
					foreach($this->currencies as $currency){
						echo '<input type="checkbox" name="'.$this->get_field_name('currencies').'[]" value="'.$currency.'"'.(in_array($currency,$currencies)?' checked="checked"':'').'/> '.$currency."<br/>";
					}?>
			 	</label>
			</p>
			<?php
		}
		function register(){
			// Get Fresh data onregister
			$this->getFreshData();
		}
		function widget($args, $instance) {
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			$currencies = (array)$instance['currencies'];
			echo $args['before_widget'];
			if ( !empty( $title ) ) { echo $args['before_title'] . $title . $args['after_title']; };
			echo '<table border="0">';
			foreach($currencies as $currency){
				echo '<tr><td align="right">'.$this->settings['rates']->{$currency}->ratio.'&nbsp;</td><td>&nbsp;'.$currency.'</td><td>&nbsp;=&nbsp;</td><td>&nbsp;'.$this->settings['rates']->{$currency}->rate.' лв</td></tr>';
			}
			echo '</table>';

			echo $args['after_widget'];
		}
		function getFreshData(){
			if(function_exists('get_plugin_data'))
				$plugin = get_plugin_data( __FILE__ );
			else
				$plugin['Version'] = 'unk';
			$json = file_get_contents('http://cdn.wms-tools.com/bnb.php?v='.$plugin['Version']);
			$data = json_decode($json);

			$this->settings['rates'] = $data;
			$this->settings['currencies'] = array_keys((array)$data);
			$this->settings['updated'] = time();

			$this->save_settings($this->settings);
		}
	}

	function Valutni_Kursove_Init() {
		register_widget('Valutni_Kursove_Widget');
	}

	add_action('widgets_init', 'Valutni_Kursove_Init');

}
?>
