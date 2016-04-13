<?php
/*
 * Plugin Name: OpenWeatherMap - Widget
 * Plugin URI: 
 * Description: A widget by makakken.de to display weather.
 * Version: 1.0
 * Author: post@makakken.de
 * Author URI: http://makakken.de/
 */


//Classes
class openweathermap_widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 
			'classname' => 'widget_weather', 
			'description' => __('A widget to display weather in the sidebar.', 'makakken') 
		);

		parent::__construct( 
			'openweathermap_widget', 
			__('OpenWeatherMap: Weather', 'makakken'), 
			$widget_ops 
		);
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		/* Our variables from the widget settings. */
		$location_id = $instance['location_id'];
		$weather_unit = $instance['weather_unit'];
		$unit = $instance['weather_unit'];
		$api_id = $instance['api_id'];

		echo $before_widget;
		echo '<h3>'.__('Das aktuelle Wetter','makakken').'</h3>';
				
		if ( $weather_unit == "metric" ){
			$unit_name = "&deg;C";
		} else {
			$unit_name = "F";
		}
		
				
		if ( !empty( $location_id ) ){			
			$cache_time = 3600; //one hour	
			$stored_location_id = get_option('makakken_weather_location_id');
			
			if( ($location_id != $stored_location_id) OR  ($_SERVER['REQUEST_TIME'] > get_option('makakken_weather_cache_time')) ) {
				$query_url = 'http://api.openweathermap.org/data/2.5/forecast/';
				$quer_url  .= 'city?id='.$location_id;
				$query_url .= '&units='.$unit.'&lang=de';
				$query_url .= '&APPID='.$api_id;

				$data = null;
				if($json = @file_get_contents($query_url)){  
					$error = FALSE;
					$data = json_decode($json); 
				}else{  
					$error = TRUE;
				}	 
	  
				if(!$error){  
					$city = $data->city->name;  

					foreach($data->list as $item) {
						$today = new DateTime();
						$data_date = new DateTime(date( 'c' , $item->dt));
						$interval = $today->diff($data_date);

						if( ! isset($dates[$interval->days])) {
							$dates[$interval->days] = $data_date->format('d.m.Y');
							$temps[$interval->days] = $item->main->temp;
							$temps_max[$interval->days] = $item->main->temp_max;
							$temps_min[$interval->days] = $item->main->temp_min;
							$conditions[$interval->days] = $item->weather[0]->main;
							$humidities[$interval->days] = $item->main->humidity;
							$winds[$interval->days] = $item->wind->speed;
							$weather_codes[$interval->days] = $item->weather[0]->id;
							$weather_description[$interval->days] = $item->weather[0]->description;
						}
					}
		
					$temperature = $temps[0];			
					$conditions_text = $conditions[0]; 
					$today_code = $weather_codes[0]; 
			
					$humidity = $humidities[0]; 
					$wind = $winds[0]; 
			 
					$next_day = $dates[1];
					$next_day_low = $temps_min[1];
					$next_day_high = $temps_max[1];
					$next_day_code = $weather_codes[1];
			
					$day_after = $dates[2];
					$day_after_low = $temps_min[2];
					$day_after_high = $temps_max[3];
					$day_after_code = $weather_codes[2];
				
					$code_types = array(
						'storm'	=> array(
							200,201,202,210,211,212,221,230,231,232
						),
						'snow'	=> array(
							600,601,602,611,612,615,616,620,621,622
						),
						'rain'	=> array(
							500,501,502,503,504,511,520,521,522,531
						),
						'cloudy' => array(
							801,802,803,804
						),
						'sunny'	=> array(
							800
						)
					);
									
					if(in_array($today_code, $code_types['storm'])) {
						$today_icon = 'storm.png';
					} elseif(in_array($today_code, $code_types['snow'])) {
						$today_icon = 'snow.png';
					} elseif(in_array($today_code, $code_types['rain'])) {
						$today_icon = 'rain.png';
					} elseif(in_array($today_code, $code_types['cloudy'])) {
						$today_icon = 'cloud.png';
					} elseif(in_array($today_code, $code_types['sunny'])) {
						$today_icon = 'sun.png';
					} else {
						$today_icon = 'mysterious.png';
					}
				
					if(in_array($next_day_code, $code_types['storm'])) {
						$next_day_icon = 'storm.png';
					} elseif(in_array($next_day_code, $code_types['snow'])) {
						$next_day_icon = 'snow.png';
					} elseif(in_array($next_day_code, $code_types['rain'])) {
						$next_day_icon = 'rain.png';
					} elseif(in_array($next_day_code, $code_types['cloudy'])) {
						$next_day_icon = 'cloud.png';
					} elseif(in_array($next_day_code, $code_types['sunny'])) {
						$next_day_icon = 'sun.png';
					} else {
						$next_day_icon = 'mysterious.png';
					}
				
					if(in_array($day_after_code, $code_types['storm'])) {
						$day_after_icon = 'storm.png';
					} elseif(in_array($day_after_code, $code_types['snow'])) {
						$day_after_icon = 'snow.png';
					} elseif(in_array($day_after_code, $code_types['rain'])) {
						$day_after_icon = 'rain.png';
					} elseif(in_array($day_after_code, $code_types['cloudy'])) {
						$day_after_icon = 'cloud.png';
					} elseif(in_array($day_after_code, $code_types['sunny'])) {
						$day_after_icon = 'sun.png';
					} else {
						$day_after_icon = 'mysterious.png';
					}
					
					$conditions_text = $weather_description[0];
					
					$wind = intval($wind);

					if (!empty($city)){
						update_option('makakken_weather_cache_time', $_SERVER['REQUEST_TIME'] + $cache_time);
						update_option('makakken_weather_location_id', (string)$location_id);
						update_option('makakken_weather_city', (string)$city);
						update_option('makakken_weather_temp', (string)$temperature);
						update_option('makakken_weather_condition', (string)$conditions_text);
						update_option('makakken_weather_today_icon', (string)$today_icon);
						update_option('makakken_weather_humidity', (string)$humidity);
						update_option('makakken_weather_wind', (string)$wind);
						update_option('makakken_weather_nextday', (string)$next_day);
						update_option('makakken_weather_nextday_low', (string)$next_day_low);
						update_option('makakken_weather_nextday_high', (string)$next_day_high);
						update_option('makakken_weather_nextday_icon', (string)$next_day_icon);
						update_option('makakken_weather_dayafter', (string)$day_after);
						update_option('makakken_weather_dayafter_low', (string)$day_after_low);
						update_option('makakken_weather_dayafter_high', (string)$day_after_high);
						update_option('makakken_weather_dayafter_icon', (string)$day_after_icon);
					}				
				} // no error			
			} //update
		?>
			
			<div class="today">
				<div class="left">
					<img src="<?php echo get_template_directory_uri() .'/images/weather/'.get_option('makakken_weather_today_icon'); ?>" />
					<div class="temp"><?php echo round(get_option('makakken_weather_temp')); ?> <?php echo $unit_name; ?></div>
				</div>
				<div class="right">
					<h2><?php echo get_option('makakken_weather_city'); ?></h2>			
					<div class="condition"><?php echo get_option('makakken_weather_condition'); ?></div>
					<div class="humidity"><?php _e('Humidity:', 'makakken'); ?> <?php echo get_option('makakken_weather_humidity') . ' %'; ?></div>
					<div class="wind"><?php _e('Wind:', 'makakken'); ?> <?php echo get_option('makakken_weather_wind'); ?> <?php _e('km/h', 'makakken'); ?></div>		
				</div>
			</div>
		
			<div class="forecast">
				<div class="nextday">
					<div class="date"><?php get_option('makakken_weather_nextday'); echo 'Heute'; ?></div>
					<img src="<?php echo get_template_directory_uri() .'/images/weather/'.get_option('makakken_weather_nextday_icon'); ?>" />
					<div class="temp">
						<span class="nextday-low"><?php echo round(get_option('makakken_weather_nextday_low')); ?> <?php echo $unit_name; ?></span>
						<span class="nextday-high"><?php echo round(get_option('makakken_weather_nextday_high')); ?> <?php echo $unit_name; ?></span>			
					</div>
				</div>
			
				<div class="dayafter">			
					<div class="date"><?php get_option('makakken_weather_dayafter'); echo 'Morgen'; ?></div>
					<img src="<?php echo get_template_directory_uri() .'/images/weather/'.get_option('makakken_weather_dayafter_icon'); ?>" />
					<div class="temp">
						<span class="dayafter-low"><?php echo round(get_option('makakken_weather_dayafter_low')); ?> <?php echo $unit_name; ?></span>
						<span class="dayafter-high"><?php echo round(get_option('makakken_weather_dayafter_high')); ?> <?php echo $unit_name; ?></span>
					</div>					
				</div>        		
			</div>		
		
		<?php				
		//}  
		} 
      ?>		
		
	          
    <?php
		echo $after_widget;
	}
	
	/**
	 * update widget settings
	 */	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['api_id'] = $new_instance['api_id'];
		$instance['location_id'] = $new_instance['location_id'];
		$instance['weather_unit'] = $new_instance['weather_unit'];			
		return $instance;
	}
	
	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	 function form( $instance ) {
	
		/* Set up some default widget settings. */
		$defaults = array(
			'api_id' => '',
			'location_id' => '',
			'weather_unit' => 'metric'			
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'api_id' ); ?>"><?php _e('API-Key:', 'makakken') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'api_id' ); ?>" name="<?php echo $this->get_field_name( 'api_id' ); ?>" value="<?php echo $instance['api_id']; ?>" />
			<?php _e( 'API-Key openweathermap.org.', 'makakken' ); ?>
		</p>		

		<p>
			<label for="<?php echo $this->get_field_id( 'location_id' ); ?>"><?php _e('Location ID:', 'makakken') ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'location_id' ); ?>" name="<?php echo $this->get_field_name( 'location_id' ); ?>" value="<?php echo $instance['location_id']; ?>" />
			<?php _e( 'Location ID from openweathermap.org', 'makakken' ); ?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'unit' ); ?>"><?php _e('Weather unit:', 'makakken') ?></label>			
			<select name="<?php echo $this->get_field_name('weather_unit'); ?>" id="<?php echo $this->get_field_id('weather_unit'); ?>" class="widefat">
				<option value="metric"<?php selected( $instance['weather_unit'], 'metric' ); ?>><?php _e('Celsius', 'makakken'); ?></option>
				<option value="imperial"<?php selected( $instance['weather_unit'], 'imperial' ); ?>><?php _e('Fahrenheit', 'makakken'); ?></option>
			</select>
			
		</p>
	<?php
	}
}

//Functions
function openweathermap_widgets() {
	register_widget( 'openweathermap_widget' );
}

//Actions 
add_action( 'widgets_init', 'openweathermap_widgets' );

?>