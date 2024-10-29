<?php require_once '_admin_menu.php'; ?>
<div class="wrap clear"><style>input[type='text'] { width:200px; padding:4px; } </style>
    <input type="hidden" id="artsopolis-calendar-selected-events" name="artsopolis-calendar-selected-events" value="<?php if ( is_array($selected_events) && !empty($selected_events) ) echo implode(',', $selected_events); ?>" />

    <input type="hidden" id="artsopolis-calendar-spotlight-events" name="artsopolis-calendar-spotlight-events" value="<?php if ( is_array($spotlight_events) && !empty($spotlight_events) ) echo implode(',', $spotlight_events); ?>" />
    <fieldset>
        <?php 
            $feeds = @unserialize( get_option( AC_PLUGIN_OPTION_ARR_KEYS ) );
            $perPage = !empty($ac_options['admin_featured_event_perpage']) && $ac_options['admin_featured_event_perpage'] > 0 ? $ac_options['admin_featured_event_perpage'] : AC_ADMIN_FEATURED_EVENT_PERPAGE;

            if ( $feeds ):
        ?>
        <div id="ac-feed-filter">
            <label>Filter by Feed</label>    
            <select id="ac-filter-by-feed" data-href="<?php echo admin_url() ?>admin.php?page=artsopolis-calendar-featured-events&fid=">
                <option value='-1'><?php _e( 'Select feed', 'apollo' ) ?></option>
                <?php 
                    foreach ( $feeds as $f ):
                        $k = self::get_option_key( $f );
                        $op = get_option( $k );
                        if ( ! $op ) continue;
                        $fid = isset( $_REQUEST['fid'] ) ?  $_REQUEST['fid'] : '';
                        echo '<option '.( $fid == $f ? 'selected' : '' ).' value="'.($f ? $f : 0).'" >'.( $op['title'] ? $op['title'] : "No title". ' (ID:'.$f. ')' ).'</option>';
                    endforeach;
                ?>
            </select>
        </div>
    <?php endif; ?>    
	<legend><h2>Featured Events Listing</h2></legend>
	<hr/>
	<form method="post" >
        <p id="processing-artsopolis-calendar">Processing ...</p>
        <table style="visibility: hidden" cellpadding="0" cellspacing="0" border="0" class="display" id="artsopolis-calendar-featured-events" width="100%" data-per-page="<?php echo $perPage; ?>">
            <thead>
                <tr>
                    <th>Featured Events</th>
                    <th>Spotlight</th>
                    <th>Name</th>
                    <th style="width: 200px;">Date Range</th>
                    <th>Presenting Org Name</th>
                    <th style="width: 90px;">City Location</th>
                </tr>
            </thead>
                
                <?php 
                
                if( $events ) {
                    foreach ( $events as $event ) {
                        $event = self::get_cdata_xml( $event );
                        // Thienld : custom logic to check expired event
                        if ( !empty($event) && acIsExpiredTime($event->eventDateEnd,$event->event_dates_times) ) continue;
                        $date_start_arr = explode('-', $event->eventDateBegin);
                        $date_start_time = mktime(0, 0, 0, $date_start_arr[0], $date_start_arr[1], $date_start_arr[2]);

                        $date_end_arr = explode('-', $event->eventDateEnd);
                        $date_end_time = mktime(0, 0, 0, $date_end_arr[0], $date_end_arr[1], $date_end_arr[2]);

                        // Event link
                        if ( ! $ac_options['details_link_to'] ) {
                            $link = $event->link;
                        } else {
                            // Custom url follow the permalink structure
                            $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
                            $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
                            $title_slug = Artsopolis_Calendar_Shortcode::slugify($event->eventName);
                            $link = $parent_link. $url_ext_sign . $title_slug . '&event_id=' . $event->eventID. ( isset( $_GET['fid'] ) && $_GET['fid'] ? '&fid='. $_GET['fid']:'' );
                        }
                ?>
                        <tr class="odd gradeX">
                            <td>
                                <input name="event_ids[]" value="<?php echo $event->eventID; ?>" type="checkbox" />
                                <input name="all_event_ids[]" value="<?php echo $event->eventID; ?>" type="hidden" />
                            </td>
                            <td>
                                <input name="spotlight_event_ids[]" value="<?php echo $event->eventID; ?>" type="checkbox" />
                            </td>
                            <td><a target="_blank" href='<?php echo $link ?>'><?php echo $event->eventName; ?></a></td>
                            <td><?php echo date('Y-m-d', $date_start_time)  . ' to '. date('Y-m-d', $date_end_time) ; ?></td>
                            <td><?php echo $event->orgName; ?></td>
                            <td><?php echo $event->venueCity; ?></td>
                        </tr>
                <?php
                    }
                } ?>
                        
                <tbody></tbody>        
                        
            </table>
      
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
        <form>
	</fieldset>
</div>

<input type="hidden" id="ac-feature-event-page" value="1" />