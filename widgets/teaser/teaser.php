<?php

class artsopolis_calendar_teaser_widget extends WP_Widget {
    
    public function __construct() {
        
        parent::__construct(
            
            // ID of widget
            'artsopolis_calendar_teaser_widget', 

            // Widget name 
            __('Artsopolis Calendar Teaser', 'artsopolis_calendar_teaser_widget_domain'), 

            // Widget description
            array( 'description' => __( 'Artsopolis Calendar Teaser', 'artsopolis_calendar_teaser_widget_domain' ), ) 
        );
        
        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'wp_print_styles', array( $this, 'print_scripts' ) );
    }
        
    // Widget Backend 
    public function form( $instance ) {
       
        $feed_id                = isset( $instance['teaser_widget_feed_id'] ) ? $instance['teaser_widget_feed_id'] : '';
        $title                  = isset( $instance['title'] ) ? $instance['title'] : TEASER_WIDGET_DEFAULT_TITLE;
        $bg_color               = isset( $instance['bg_color'] ) ? $instance['bg_color'] : TEASER_WIDGET_DEFAULT_BG_COLOR;
        $title_color            = isset( $instance['title_color'] ) ? $instance['title_color'] : TEASER_WIDGET_DEFAULT_TITLE_COLOR;
        $link                   = isset( $instance['link'] ) ? $instance['link'] : '';
        $num_events             = isset( $instance['num_events'] ) ? $instance['num_events'] : TEASER_WIDGET_DEFAULT_NUM_DISPLAY;
        $event_title_bold       = isset( $instance['event_title_bold'] ) && $instance['event_title_bold'] ? 1 : 0;
        $event_title_font_size  = isset( $instance['event_title_font_size'] ) ? $instance['event_title_font_size'] : TEASER_WIDGET_DEFAULT_EVENT_TITLE_SIZE;
        $event_date_font_size   = isset( $instance['event_date_font_size'] ) ? $instance['event_date_font_size'] : TEASER_WIDGET_DEFAULT_EVENT_DATE_SIZE;
        $event_date_color       = isset( $instance['event_date_color'] ) ? $instance['event_date_color'] : TEASER_WIDGET_DEFAULT_TITLE_COLOR;
        $widget_title_font_size = isset( $instance['widget_title_font_size'] ) ? $instance['widget_title_font_size'] : TEASER_WIDGET_DEFAULT_EVENT_TITLE_SIZE;
        $event_title_color      = isset( $instance['event_title_color'] ) ? $instance['event_title_color'] : '';
        $teaser_widget_title_rounded    = isset( $instance['teaser_widget_title_rounded'] ) ? $instance['teaser_widget_title_rounded'] : '';
        $teaser_widget_title_rounded_radius = isset( $instance['teaser_widget_title_rounded_radius'] )
            && $instance['teaser_widget_title_rounded_radius'] ? $instance['teaser_widget_title_rounded_radius'] : TEASER_WIDGET_DEFAULT_ROUNDED_CORNER_RADIUS;
       
        ob_start();
        include dirname(__FILE__). '/admin.php';
        $html = ob_get_contents();
        ob_end_clean();
        echo $html;
    }
	
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        
        $instance = array();
        $instance['teaser_widget_feed_id']  = isset( $new_instance['teaser_widget_feed_id'] ) ? strip_tags( $new_instance['teaser_widget_feed_id'] ) : '';
        $instance['title']                  = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['bg_color']               = !empty( $new_instance['bg_color'] ) ? strip_tags($new_instance['bg_color']) : '';
        $instance['title_color']            = !empty( $new_instance['title_color'] ) ? strip_tags($new_instance['title_color']) : '';
        $instance['num_events']             = !empty( $new_instance['num_events'] ) ? intval(strip_tags($new_instance['num_events'])) : '';
        $instance['link']                   = !empty( $new_instance['link'] ) ? strip_tags($new_instance['link']) : '';
        $instance['event_title_font_size']  = !empty( $new_instance['event_title_font_size'] ) ? strip_tags($new_instance['event_title_font_size']) : '';
        $instance['event_title_bold']       = !empty( $new_instance['event_title_bold'] ) ? 1 : 0;
        $instance['event_date_font_size']   = !empty( $new_instance['event_date_font_size'] ) ? $new_instance['event_date_font_size'] : '';
        $instance['event_date_color']       = !empty( $new_instance['event_date_color'] ) ? $new_instance['event_date_color'] : '';
        $instance['widget_title_font_size'] = !empty( $new_instance['widget_title_font_size'] ) ? $new_instance['widget_title_font_size'] : '';
        $instance['event_title_color']      = !empty( $new_instance['event_title_color'] ) ? $new_instance['event_title_color'] : 'inherit';
        $instance['teaser_widget_title_rounded']        = !empty( $new_instance['teaser_widget_title_rounded'] ) ? 1 : 0;
        $instance['teaser_widget_title_rounded_radius'] = !empty( $new_instance['teaser_widget_title_rounded_radius'] ) ? $new_instance['teaser_widget_title_rounded_radius'] : '';
        
        return $instance;
    }
    
    /**
     * Creating widget front-end
     * This is where the action happens
     */
    public function widget( $args, $instance ) {
        
        $fid = isset( $instance['teaser_widget_feed_id'] ) ? $instance['teaser_widget_feed_id'] : '';
        $option_key = Artsopolis_Calendar_API::get_option_key($fid);
        
        $featured_events = Artsopolis_Calendar_Shortcode::get_featured_events( $fid );
        $ac_options = get_option( $option_key );
        
        if ( empty( $featured_events ) || ! $instance['num_events'] )  return false;
        
        $num_display_widget = count($featured_events) > $instance['num_events'] ? $instance['num_events'] : count($featured_events);
        
        ob_start();
        include dirname(__FILE__). '/frontend.php';
        $html = ob_get_contents();
        ob_end_clean();
        echo $html;
    }
    
    function register_scripts() {
        
        wp_register_style('teater-widget-css', plugins_url('artsopolis-calendar/widgets/teaser/css/style.css'));
    }

    function print_scripts() {
        
        if ( is_active_widget( '', '', 'artsopolis_calendar_teaser_widget' ) ) {
            wp_print_styles('teater-widget-css');
        }
    }
    
}  
