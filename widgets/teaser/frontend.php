<style>
    <?php echo '#'.$args['widget_id'];  ?> .widget-title {
        background-color: <?php echo $instance['bg_color'] ? $instance['bg_color'] : 'none' ?>; 
        color: <?php echo $instance['title_color'] ? $instance['title_color'] : 'inherit'; ?>;
        font-size:<?php echo $instance['widget_title_font_size'] ? $instance['widget_title_font_size']. 'px !important' : 'inherit'; ?>;
        <?php if ($instance['teaser_widget_title_rounded']): 
            $redius = $instance['teaser_widget_title_rounded_radius']; ?>
        border-radius: <?php echo $redius; ?>px !important;
        -moz-border-radius: <?php echo $redius; ?>px !important;
        -webkit-border-radius: <?php echo $redius; ?>px !important;
        <?php endif; ?>
    }
</style>

<?php
    $title = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( ! empty( $title ) )
        echo $args['before_title'] .$title. $args['after_title'];
    
    $logo_position = explode('_', $ac_options['teaser_widget_logo_position']);
?>

<?php 
if ( ( $logo_position[0] == 't' || $logo_position[0] == 'tb' ) && isset( $ac_options['teaser_widget_logo_url'] ) && $ac_options['teaser_widget_logo_url'] ): ?>
    <div style="text-align: <?php echo isset($logo_position[1]) && $logo_position[1] ? $logo_position[1] : 'right' ?>">
        <img src="<?php echo $ac_options['teaser_widget_logo_url'];  ?>" />
    </div>
<?php endif; ?>

<?php

if (! empty($featured_events) ) { ?>
<ul>
    <?php
        $total_event = count($featured_events);
        $num_displayed = 0;
        
        for ( $i = 0; $i < $total_event; $i++ ): 
            
            $event = Artsopolis_Calendar_Shortcode::get_cdata_xml($featured_events[$i]);
        
            // Event time
            $start      = $event->eventDateBegin;
            $end        = $event->eventDateEnd;
            $start_arr  = explode('-', $start);
            $start_time = mktime(0, 0, 0, $start_arr[0], $start_arr[1], $start_arr[2]);
            $end_arr    = explode('-', $end);
            $end_time   = mktime(0, 0, 0, $end_arr[0], $end_arr[1], $end_arr[2]);

            $today      = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

            // Check event expired
            // Thienld : custom logic to check expired event
            if ( acIsExpiredTime($end,$event->event_dates_times) ) continue;


            // Break if enough data as configuration
            if ( $num_displayed == $num_display_widget ) break;
            
            $num_displayed++;

            $event_img = Artsopolis_Calendar_Shortcode::get_event_url($event->eventImage);
            
            if ( $start == $end ) {
                $date_string = date('M j', $start_time). ','. date(' Y', $start_time);
            } else {
                if ( $start_arr[2] == $end_arr[2] ) {
                    $date_string = date('M j ', $start_time) .' - '. date('M j', $end_time).','. date(' Y', $start_time);
                } else {
                    $date_string = date('M j', $start_time). ','. date(' Y', $start_time) .' - '. date('M j', $end_time). ','. date(' Y', $end_time);
                }
            }


            // Event link
            if ( ! $ac_options['details_link_to'] ) {
                $link = $event->link;
                $target = 'target="_blank"';
            } else {
                // Custom url follow the permalink structure
                $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
                $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
                $title_slug = Artsopolis_Calendar_Shortcode::slugify($event->eventName);
                $link = $parent_link. $url_ext_sign . $title_slug . '&event_id='. $event->eventID. ( isset($instance['teaser_widget_feed_id']) && $instance['teaser_widget_feed_id'] ? '&fid='.$instance['teaser_widget_feed_id'] : '' );
                $target = '';
            }
        
    
?>
    <li>
        <div class="widget-event-img-wrapper">
            <span class="widget-event-img-mask">
                <a target="<?php echo $target; ?>" href="<?php echo $link; ?>"><img src="<?php echo $event_img; ?>" /></a>
            </span>
        </div>
            
        <div class="widget-event-content-right">
                <span><a style="font-weight:<?php echo isset($instance['event_title_bold']) && 
            $instance['event_title_bold'] ? 'bold' : 'normal';  ?>;
            font-size:<?php echo isset($instance['event_title_font_size']) && 
            $instance['event_title_font_size'] ? $instance['event_title_font_size']. 'px' : 'inherit';  ?>;
            color: <?php echo isset($instance['event_title_color']) && 
            $instance['event_title_color'] ? $instance['event_title_color'] : '';  ?>;" target="<?php echo $target; ?>" href="<?php echo $link; ?>"><?php echo $event->eventName; ?></a></span>

            <span style="color: <?php echo isset($instance['event_date_color']) && $instance['event_date_color'] ? $instance['event_date_color'] : 'inherit' ?>;
                  font-size: <?php echo isset($instance['event_date_font_size']) && $instance['event_date_font_size'] ? $instance['event_date_font_size']. 'px' : 'inherit' ?>;"><?php echo $date_string; ?></span>
        </div>
    </li>
    <?php endfor; ?>
</ul>
<?php } ?>

<?php if ( isset($ac_options['calendar_slug']) && $ac_options['calendar_slug'] ): ?>
<p class="view-more-events"><a href='<?php echo get_site_url(). '/'. $ac_options['calendar_slug'] ?>'>&raquo; View more events</a></p>
<?php endif;

if ( ( $logo_position[0] == 'b' || $logo_position[0] == 'tb' ) && isset( $ac_options['teaser_widget_logo_url'] ) && $ac_options['teaser_widget_logo_url'] ): ?>
    <div style="text-align: <?php echo isset($logo_position[1]) && $logo_position[1] ? $logo_position[1] : 'right' ?>">
        <a href="<?php echo $ac_options['teaser_widget_logo_link_to'];  ?>" target="_blank"><img src="<?php echo $ac_options['teaser_widget_logo_url'];  ?>" /></a>
    </div>
<?php endif; ?>

<?php echo $args['after_widget'];
?>
