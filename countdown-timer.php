<?php
/*
Plugin Name: jQuery T(-) Countdown Widget
Plugin URI: http://www.twinpictures.de/t-countdown-widget
Description: Display and configure a jQuery countdown timer as a sidebar widget.
Version: 1.0
Author: Twinpictures
Author URI: http://www.twinpictures.de
License: GPL2
*/

/*  Copyright 2010 Twinpictures (email : baden@twinpictures.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//replace jQuery google's jQuery (faster load times, take advangage of probable caching)
function my_jQuery_init_method() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
}    
 
add_action('init', 'my_jQuery_init_method');


//widgit scripts
function countdown_script(){
        $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
        if (!is_admin()){
                //lwtCountdown script
                wp_register_script('countdown-script', $plugin_url.'/js/jquery.lwtCountdown-1.0.js', array (), '1.0' );
                wp_enqueue_script('countdown-script');
        }
}



// Inserts scripts into header
add_action( 'wp_print_scripts', 'countdown_script' );


//the widget
function widget_countdown_timer_init() {
        
        if ( !function_exists('register_sidebar_widget') )
                        return;
                        
        function sanitizer($name) {
    	$name = strtolower($name); // all lowercase
    	$name = preg_replace('/[^a-z0-9 ]/','', $name); // nothing but a-z 0-9 and spaces
    	$name = preg_replace('/\s+/','-', $name); // spaces become hyphens
    	return $name;
        }
        
        //widget css
        function countdown_style($args){
                $plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
                if (!is_admin()){
                        //css
                        extract($args);
                        $options = array_merge(widget_countdown_options(), get_option('widget_countdown'));
                        unset($options[0]); //returned by get_option(), but we don't need it
                        if($options['style'] == 'light'){
                            wp_register_style( 'countdown-css', $plugin_url.'/css/light-style.css', array (), '1.0' );    
                        }
                        else{
                            wp_register_style( 'countdown-css', $plugin_url.'/css/dark-style.css', array (), '1.0' );    
                        }
                        wp_enqueue_style( 'countdown-css' );
                }
        }
        
        //insert some style into your life
        add_action( 'wp_print_styles', 'countdown_style' );
                
        // Options and default values for this widget
        function widget_countdown_options() {
                 return array(
	        'title' => 'Countdown',
	        'description' => '',
	        'url' => '',
                        'urltarget' => '_blank',
                        'urltext' => '',
                        'urlpos' => 'bottom',
	        'day' => 15,
	        'month' => 10,
	        'year' => 2010,
	        'hour' => 20,
	        'min'  => 0,
	        'sec' => 0,
                        'weektitle' => 'weeks',
                        'daytitle' => 'days',
                        'hourtitle' => 'hours',
                        'mintitle' => 'minutes',
                        'sectitle' => 'seconds',
	        'omitweeks' => 'false',
                        'style' => 'light'
	);
        }
        
        function widget_countdown($args) {
	extract($args);
	$options = array_merge(widget_countdown_options(), get_option('widget_countdown'));
	unset($options[0]); //returned by get_option(), but we don't need it
	
                //calc the inital difference
                $now = time();
	$target = mktime(
		$options['hour'], 
		$options['min'], 
		$options['sec'], 
		$options['month'], 
		$options['day'], 
		$options['year']
	);

	$diffSecs = $target - $now;

	$date = array();
	$date['secs'] = $diffSecs % 60;
	$date['mins'] = floor($diffSecs/60)%60;
	$date['hours'] = floor($diffSecs/60/60)%24;
	$date['days'] = floor($diffSecs/60/60/24)%7;
	$date['weeks']	= floor($diffSecs/60/60/24/7);
	
	foreach ($date as $i => $d) {
		$d1 = $d%10;
		$d2 = ($d-$d1) / 10;
		$date[$i] = array(
			(int)$d2,
			(int)$d1,
			(int)$d
		);
	}
                
	echo $before_widget;
                if($options['title']){
                        echo $before_title . $options['title'] . $after_title;
                }
	if($options['description']){
                        echo '<p>'. $options['description'] .'</p>';    
                }
                if($options['url'] && $options['urlpos'] == 'top'){
                        echo '<a href="'. $options['url'] .'" target="'. $options['urltarget'] .'">'. $options['urltext'] .'</a>';
                }
                
                echo '<div id="countdown_dashboard">';
                
                if($options['omitweeks'] == 'false'){
                        echo '<div class="dash weeks_dash">
                                <span class="dash_title">'.$options['weektitle'].'</span>
		<div class="digit">'.$date['weeks'][0].'</div>
		<div class="digit">'.$date['weeks'][1].'</div>
                              </div>'; 
                }
                
	echo '<div class="dash days_dash">
                                <span class="dash_title">'.$options['daytitle'].'</span>
                                <div class="digit">'.$date['days'][0].'</div>
                                <div class="digit">'.$date['days'][1].'</div>
                        </div>

                        <div class="dash hours_dash">
                                <span class="dash_title">'.$options['hourtitle'].'</span>
                                <div class="digit">'.$date['hours'][0].'</div>
                                <div class="digit">'.$date['hours'][1].'</div>
                        </div>

                        <div class="dash minutes_dash">
                                <span class="dash_title">'.$options['mintitle'].'</span>
                                <div class="digit">'.$date['mins'][0].'</div>
                                <div class="digit">'.$date['mins'][1].'</div>
                        </div>

                        <div class="dash seconds_dash">
                                <span class="dash_title">'.$options['sectitle'].'</span>
                                <div class="digit">'.$date['secs'][0].'</div>
                                <div class="digit">'.$date['secs'][1].'</div>
                        </div>        
                </div>';
                if($options['url'] && $options['urlpos'] == 'bottom'){
                        echo '<a href="'. $options['url'] .'" target="'. $options['urltarget'] .'">'. $options['urltext'] .'</a>';
                }
                //echo '<p>Phones riggin dude!</p>';
                echo $after_widget;
        }
        
        //add the script to the footer
        function jquery_countdown_js($args){
                extract($args);
	$options = array_merge(widget_countdown_options(), get_option('widget_countdown'));
	unset($options[0]); //returned by get_option(), but we don't need it
                ?>                
                <script language="javascript" type="text/javascript">
	        jQuery(document).ready(function() {
                                //alert('Phones Ringin, Dude.');	
                                jQuery('#countdown_dashboard').countDown({	
		        targetDate: {
			'day': 	<?php echo $options['day']; ?>,
			'month': 	<?php echo $options['month']; ?>,
			'year': 	<?php echo $options['year']; ?>,
			'hour': 	<?php echo $options['hour']; ?>,
			'min': 	<?php echo $options['min']; ?>,
			'sec': 	<?php echo $options['sec']; ?>
		        },
		        omitWeeks: <?php echo $options['omitweeks']; ?>
		});
                                
	        });
	</script>
                <?php
        }
        
        add_action('wp_head','jquery_countdown_js');
        
        
        //add the widget control form
        function widget_countdown_control() {
                if(($options = get_option('widget_countdown')) === FALSE) $options = array();
                $options = array_merge(widget_countdown_options(), $options);
	unset($options[0]); //returned by get_option(), but we don't need it

	// If user is submitting custom option values for this widget
	if ( $_POST['countdown-submit'] ) {
                        // Remember to sanitize and format use input appropriately.
                         foreach($options as $key => $value){
                                $options[$key] = strip_tags(stripslashes($_POST['countdown-'.sanitizer($key)]));
                        }
                        
                        // Save changes
	        update_option('widget_countdown', $options);
	}
        
                // title option
                echo '<p style="text-align:left"><label for="countdown-title">Title: <input style="width: 200px;" id="countdown-title" name="countdown-title" type="text" value="'.$options['title'].'" /></label></p>';
                        
                //description
                echo '<p style="text-align:left"><label for="countdown-description">Description: <input style="width: 200px;" id="countdown-description" name="countdown-description" type="text" value="'.$options['description'].'" /></label></p>';
                
                //url
                echo '<p style="text-align:left"><label for="countdown-url">URL: <input style="width: 200px;" id="countdown-url" name="countdown-url" type="text" value="'.$options['url'].'" /></label></p>';
                
                //url target
                echo '<p style="text-align:left"><label for="countdown-urltarget">Link Target: <input style="width: 200px;" id="countdown-urltarget" name="countdown-urltarget" type="text" value="'.$options['urltarget'].'" /></label></p>';
                
                //url text
                echo '<p style="text-align:left"><label for="countdown-urltext">Link Text: <input style="width: 200px;" id="countdown-urltext" name="countdown-urltext" type="text" value="'.$options['urltext'].'" /></label></p>';
                
                //url position Slector
                $dom = '';
                $sub = '';
                if($options['urlpos'] == 'top'){
                        $dom = 'CHECKED';
                }
                else{
                        $sub = 'CHECKED'; 
                }
                
                //Is the link a top or a bottom?
                echo '<p style="text-align:left"><label for="countdown-urlpos">Link Position: <br/><input id="countdown-urlpos" name="countdown-urlpos" type="radio" '.$dom.' value="top" /> Above Counter </label><input id="countdown-urlpos" name="countdown-urlpos" type="radio" '.$sub.' value="bottom" /> Below Counter </label> </p>';
                
                //Target Date
                echo '<p style="text-align:left"><label for="countdown-day">Target Date (DD-MM-YYYY):<br/><input style="width: 30px;" id="countdown-day" name="countdown-day" type="text" value="'.$options['day'].'" /></label>-<input style="width: 30px;" id="countdown-month" name="countdown-month" type="text" value="'.$options['month'].'" />-<input style="width: 40px;" id="countdown-year" name="countdown-year" type="text" value="'.$options['year'].'" /></p>';
                
                //Target Time
                echo '<p style="text-align:left"><label for="countdown-hour">Target Time (HH:MM:SS):<br/><input style="width: 30px;" id="countdown-hour" name="countdown-hour" type="text" value="'.$options['hour'].'" /></label>:<input style="width: 30px;" id="countdown-min" name="countdown-min" type="text" value="'.$options['min'].'" />:<input style="width: 30px;" id="countdown-sec" name="countdown-sec" type="text" value="'.$options['sec'].'" /></p>';
                
                 //weeks text
                echo '<p style="text-align:left"><label for="countdown-weektitle">How do you spell "weeks"?: <input style="width: 200px;" id="countdown-weektitle" name="countdown-weektitle" type="text" value="'.$options['weektitle'].'" /></label></p>';
                
                 //days text
                echo '<p style="text-align:left"><label for="countdown-urltext">How do you spell "days"?: <input style="width: 200px;" id="countdown-daytitle" name="countdown-daytitle" type="text" value="'.$options['daytitle'].'" /></label></p>';
                
                 //hours text
                echo '<p style="text-align:left"><label for="countdown-hourtitle">How do you spell "hours"?: <input style="width: 200px;" id="countdown-hourtitle" name="countdown-hourtitle" type="text" value="'.$options['hourtitle'].'" /></label></p>';
                
                 //minutes text
                echo '<p style="text-align:left"><label for="countdown-mintitle">How do you spell "minutes"?: <input style="width: 200px;" id="countdown-mintitle" name="countdown-mintitle" type="text" value="'.$options['mintitle'].'" /></label></p>';
        
                 //seconds text
                echo '<p style="text-align:left"><label for="countdown-sectitle">And "seconds" are spelled how?: <input style="width: 200px;" id="countdown-sectitle" name="countdown-sectitle" type="text" value="'.$options['sectitle'].'" /></label></p>';
                
                
                //Omit Week Slector
                $negative = '';
                $positive = '';
                if($options['omitweeks'] == 'false'){
                        $negative = 'CHECKED';
                }
                else{
                        $positive = 'CHECKED'; 
                }
                
                //Omit Weeks
                echo '<p style="text-align:left"><label for="countdown-omitweeks">Omit Weeks:<input id="countdown-omitweeks" name="countdown-omitweeks" type="radio" '.$negative.' value="false" /> No </label><input id="countdown-omitweeks" name="countdown-omitweeks" type="radio" '.$positive.' value="true" /> Yes </label> </p>';
                
                //style Slector
                $light = '';
                $dark = '';
                if($options['style'] == 'light'){
                        $light = 'CHECKED';
                }
                else{
                        $dark = 'CHECKED'; 
                }
                
                //Light or Dark Style?  You choose!
                echo '<p style="text-align:left"><label for="countdown-style">What side of the Force are you on?: <br/><input id="countdown-style" name="countdown-style" type="radio" '.$light.' value="light" /> Jedi </label><input id="countdown-style" name="countdown-style" type="radio" '.$dark.' value="dark" /> Darth </label> </p>';
                
        
                // Submit
                echo '<input type="hidden" id="countdown-submit" name="countdown-submit" value="1" />';
        }
        // This registers our widget so it appears with the other available
        // widgets and can be dragged and dropped into any active sidebars.
        //register_sidebar_widget('jQuery T(-) CountDown', 'widget_countdown');
        wp_register_sidebar_widget( 'jquery-countdown', 'jQuery T(-) CountDown', 'widget_countdown');

        // This registers our optional widget control form.
        wp_register_widget_control('jquery-countdown', 'jQuery T(-) CountDown', 'widget_countdown_control');
}

// Run code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_countdown_timer_init');

?>