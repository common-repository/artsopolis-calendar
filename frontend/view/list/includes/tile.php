<?php
/**
 * Created by PhpStorm.
 * User: vu
 * Date: 18/10/2017
 * Time: 14:21
 */

// Start to get the current url
if (!session_id()) {
    session_start();
}

$numShowDate = 3;
$classShadow = isset($ac_options['drop_shadow_style']) && $ac_options['drop_shadow_style'] ? 'ac-shadow' : '';
$classNoImage = !empty($ac_options['disable_event_image']) ? 'ac-thumb-no-image' : '';
$classNoHover = empty($ac_options['enable_hover_effect']) ? 'ac-no-hover' : '';
?>
<div class="ac-search-bkl">
    <nav class="ac-search-list-thumb <?php echo $classShadow ?>">

<?php
        if(!empty($events)):
            $imgStyle = isset($ac_options['img_style']) ? $ac_options['img_style'] : '';
            foreach ($events as $event):
                $event = self::get_cdata_xml($event);
                if ( !empty($event) && acIsExpiredTime($event->eventDateEnd,$event->event_dates_times) ) continue;
                $event_img = self::get_event_url($event->eventImage);

                if (! $ac_options['details_link_to']) {
                    $link = $event->link;
                    $target =  $ac_options['open_new_tab'] ? 'target="_blank"' : '';
                } else {
                    // Custom url follow the permalink structure
                    $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
                    $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
                    $title_slug = self::slugify($event->eventName);
                    $link = $parent_link. $url_ext_sign . $title_slug . '&event_id='. $event->eventID. ( self::$fid ? '&fid='. self::$fid : '' );
                    $target = '';
                }
                $year = '';
                $month = '';

                /** @ticket #25103: Git-248 Calendar plugin date display option
                 * git: https://git.elidev.info/nhanlt/apollo-theme/-/issues/248
                 * FE tile always display "today" date it's an active date
                 */
                if ($ac_options['settings_display_order'] == 'NEXT-ACTIVE-DATE') {
                    $today          = date('Y-m-d');
                    $dateToCheck    = strtotime($today);

                    foreach($event->event_dates_times as $date ) {
                        if($date->timestamp >= $dateToCheck) {
                            $month  = date('M d', (int) $date->timestamp);
                            $year   = date('Y', (int) $date->timestamp);
                            $month .= ', ';
                            break 1;
                        }
                    }
                } else {
                    if ($event->eventDateBegin) {
                        $startDate = DateTime::createFromFormat('m-d-Y', $event->eventDateBegin);
                        $ds = $startDate->getTimestamp();
                        $month .= date('M d', $ds);
                    }
                    if ($event->eventDateEnd) {
                        $endDate = DateTime::createFromFormat('m-d-Y', $event->eventDateEnd);
                        $du = $endDate->getTimestamp();
                        $year = date('Y', $du);
                        if ($event->eventDateBegin && strcmp($event->eventDateBegin, $event->eventDateEnd) == 0) {
                            $month .= ', ';
                        } else {
                            $month .= ' - ' . date('M d', $du) . ', ';
                        }
                    }
                }

                ?>
                <li class="<?php echo ($classNoImage . ' ' . $classNoHover) ?>">
                    <a class="div-one lazy-load-image-wrapper" <?php echo $target; ?> href="<?php echo $link; ?>">
                        <div class="search-img">
                            <img src="<?php echo $event_img; ?>" class="eli_img" />
                        </div>
                        <div class="search-info">
                            <p><span class="ev-tt"  data-n="2" ><?php echo $event->eventName; ?></span></p>
                            <p class="meta auth"><?php _e('Presented by ', 'apollo'); ?>
                                <span><?php echo $event->orgName; ?></span>
                                <?php if($event->venueName) : ?>
                                    <span class="venue-event"><?php _e(' at '); ?><?php echo ($event->venueName . ($event->venueCity ? ', '. $event->venueCity : '')); ?></span>
                                <?php endif; ?>
                            </p>
                            <span class="sch-date"><?php echo $month; ?><span><?php echo $year; ?></span></span>
                        </div>
                    </a>

                    <div class="ico-date">
                        <a><i class="fa fa-clock-o fa-2x"></i></a>
                    </div>
                    <div class="div-two">
                        <div class="show-events">
                        <?php
                            if ($event->event_dates_times) :
                                foreach ($event->event_dates_times as $k=>$date_time) :
                                    if($k == 7) {
                                        echo '<div class="item item-last">...</div>';
                                        break;
                                    }
                                    $dateData = explode('-', $date_time->date);
                                    $date = $dateData ? $dateData[0]. ', '.$dateData[1]. ' '. ($dateData[2] < 10 ? '0'. $dateData[2] : $dateData[2]) .' @ '. $date_time->time : '';
                        ?>
                                    <div class="item"><?php echo $date; ?></div>
                        <?php
                                endforeach;
                            endif;
                        ?>
                        </div>
                    </div>

                </li>
                <?php endforeach;
            else :
            _e( 'No results', 'apollo' );
        endif; ?>

    </nav>
</div>
