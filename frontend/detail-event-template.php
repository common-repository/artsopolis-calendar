
<?php

/**
 * This file is part of the Artsopolis Calendar Plugin.
 *
 * (c) vulh@elinext.com <http://elinext.com>
 *
 * This source file is subject to the elinext.com license that is bundled
 * with this source code in the file LICENSE.
 */

    if (!$event) {
        return;
    }

 $event_img = self::get_event_url($event->eventImage);
 $gmap_address = urlencode($event->venueAddress1).
                ', '.$event->venueCity. ', '.$event->venueState. ' ' . $event->venueZip;
 
 $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
 $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
 $title_slug = self::slugify($event->eventName);
 $link = $parent_link. $url_ext_sign . $title_slug . '&event_id=' . $event->eventID;
$imgStyle = isset($ac_options['img_style']) ? $ac_options['img_style'] : '';
$classNoImage = !empty($ac_options['disable_event_image']) ? 'ac-event-no-image' : '';
$googleKey = !empty($ac_options['ac_google_map_key']) ? $ac_options['ac_google_map_key'] : '';
$enableMapSection = isset($ac_options['enable_map_section']) ? $ac_options['enable_map_section'] : 1;

?>
<?php if (! empty($event)) { ?>
<div class="eli_row eli_row-detail eli_content">
    <h4 class="eli_h4 eli_title">
        <a class="eli_a"><?php echo $event->eventName; ?></a>
    </h4>

    <span class="eli_address">
        <span class="eli_span eli_title">Presented by </span>
            <?php echo $event->orgName. ($event->venueName ? ' at '.$event->venueName. ($event->venueCity ? ', '. $event->venueCity : '') : '') ?>
        
        <?php
            $start_d_arr = explode('-', $event->eventDateBegin);
            $start_date = mktime(0, 0, 0, $start_d_arr[0], $start_d_arr[1], $start_d_arr[2]);
            
            $end_d_arr = explode('-', $event->eventDateEnd);
            $end_date = mktime(0, 0, 0, $end_d_arr[0], $end_d_arr[1], $end_d_arr[2]);
        ?>
        
        <?php if ($start_date == $end_date) { ?>
            <p class="eli_p"><?php echo date('F j, Y', $start_date); ?></p>
        <?php } else { ?>
            <p class="eli_p"><?php echo date('F d', $start_date); ?> - <?php echo date('F d', $end_date); ?></p>
        <?php } ?>
    </span>

    <div class="eli_row-main <?php echo $classNoImage ?>">
        <a class="eli_a eli_img <?php echo $imgStyle ?>"><img src="<?php echo $event_img; ?>" class="eli_img" /></a>

        <div class="eli_information">
            <div class="eli_information-inner">
                <div class="eli_information-left">
                    <div class="eli_button-container-detail">

                        <?php if (trim($event->eventTicketUrl)): ?>
                            <a target="_blank" href="<?php echo $event->eventTicketUrl ?>" class="eli_a eli_button eli_large eli_btn-buy-ticket"><span class="eli_span">Buy Tickets</span></a>
                        <?php endif; ?>
                            
                        <?php 
                        if (trim($event->discountUrl)): ?>
                        <a target="_blank" href="<?php echo $event->discountUrl ?>" class="eli_a eli_button eli_large eli_green eli_btn-buy-ticket"><span class="eli_span">Check Discounts</span></a>
                        <?php endif; ?>

                        <?php if (!isset($ac_options['enable_fb_button']) || $ac_options['enable_fb_button'] == 1): ?>
                        <a class="eli_a eli_m-r-5" href="http://www.facebook.com/sharer.php?u=<?php echo $link; ?>&t=<?php echo $event->eventName; ?>"
                        target="_blank" title="Share FB">
                            <img class="eli_img eli_fb_icon" alt="facebook icon" src="<?php echo plugins_url('artsopolis-calendar/img/facebook-icon.png'); ?>">
                        </a>
                        <?php endif; ?>

                        <?php if (!isset($ac_options['enable_tw_button']) || $ac_options['enable_tw_button'] == 1): ?>
                        <a class="eli_a" href="https://twitter.com/share?url=<?php echo $link; ?>&text=<?php echo $event->eventName; ?>"
                        target="_blank" title="Share Twitter">
                            <img class="eli_img eli_tw_icon" alt="twitter icon" src="<?php echo plugins_url('artsopolis-calendar/img/twitter-icon.png'); ?>">
                        </a>
                        <?php endif; ?>
                        
                        <?php if (trim($event->websiteUrl)): ?>
                        <a target="_blank" class="eli_a eli_button eli_btn-event-website" href="<?php echo $event->websiteUrl; ?>"><span class="eli_span">Event Website</span></a>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div> <!--#information -->
    </div> <!-- .row-main -->

    <?php
        $limitDesc = isset($ac_options['limit_desc_detail']) && intval($ac_options['limit_desc_detail']) ? $ac_options['limit_desc_detail'] : AC_LIMIT_DESC_DETAIL;

        $descArr = acGetStringByLength($event->eventDescription, intval($limitDesc));

    ?>
    <div id="artsopolis-calendar-summary" class="eli_summary">
        <div class="eli_summary-short">
            <?php echo acFormatContent($descArr['text']) ?>
            <?php if ($descArr['have_more']) { ?>
            <a href="javascript:void(0);" class="eli_a eli_expand-summary">[more+]</a>
            <?php } ?>
        </div>

        <div class="eli_summary-full eli_hidden">
            <?php echo acFormatContent($event->eventDescription); ?>
            <a href="javascript:void(0);" class="eli_a eli_less-summary">[less-]</a>
        </div>
    </div>
</div><!-- .row-detail -->
    
<?php
if ($event->event_dates_times): ?>
<div class="eli_row-detail eli_font-s-16">
	<h4 class="eli_h4 eli_heading-bar"><i class="eli_i eli_detail-icon eli_calendar-icon"></i>Dates & times</h4>
	<div class="eli_clear eli_content">
        
        <?php 
            $i = 0;
            $total_dates = count($event->event_dates_times);
            foreach ($event->event_dates_times as $date_time) {

                $dateData = explode('-', $date_time->date);

                $date = $dateData ? $dateData[0]. ', '.$dateData[1]. ' '. ($dateData[2] < 10 ? '0'. $dateData[2] : $dateData[2]) .' @ '. $date_time->time : '';

            if ($i < 3) { ?>
            <span class="eli_span eli_full-price-content eli_full-w-float-l"><?php echo $date ?></span>
            <?php echo $i == 2 && $total_dates > 3 ? '<span class="eli_span eli_expand-more-dates"> + '.($total_dates - $i - 1).' more dates and times</span>' : ''; ?>
        <?php } else { 
            echo $i == 3 ? '<p class="eli_p eli_more-date eli_hidden">' : '';
        ?>
            <span class="eli_span eli_full-price-content eli_full-w-float-l"><?php echo $date; ?></span>
        <?php 
            echo $i >= 3 && $i == $total_dates - 1 ? '<span class="eli_span eli_collapse-more-dates">- Less dates</span></p>' : '';
            }
            $i++;
        } ?>

    </div>
</div> <!-- End row-detail -->
<?php endif; ?>

<div class="eli_row-detail eli_font-s-16">
	<h4 class="eli_h4 eli_heading-bar"><i class="eli_i eli_admission-icon"></i>Admission</h4>
    <div class="eli_content">

        <?php if ($event->eventTicketInfo): ?>
        <p class="eli_p"><?php  echo $event->eventTicketInfo;  ?></p>
        <?php endif; ?>

        <?php if ($event->ticketPhone): ?>
        <p class="eli_p"><?php echo $event->ticketPhone; ?> </p>
        <?php endif; ?>
            
        <?php if ($event->eventEmail): ?>
        <p class="eli_p"><?php echo $event->eventEmail; ?></p>
        <?php endif; ?>
            
        <?php if ($event->eventTicketUrl): ?>
        <p class="eli_p"><a target="_blank" href="<?php echo $event->eventTicketUrl; ?>">Ticket Website</p>
        <?php endif; ?>
    </div>
</div> <!--.row-detail --> 

<div class="eli_row-detail">
	<h4 class="eli_h4 eli_heading-bar"><i class="eli_i eli_detail-icon eli_location-icon"></i>Location</h4>
	
    <div class="eli_content <?php echo $imgStyle; ?>">
        <h6 class="eli_h6 eli_font-s-16">
            <a class="eli_a" target="_blank" href="<?= 'http://maps.google.com/maps?f=q&hl=en&ie=UTF8om=1&q=' . $gmap_address; ?>">
                <?php echo $event->venueName; ?>
                <img class="eli_img eli_arrow-map" alt="Arrow icon" src="<?php echo plugins_url('artsopolis-calendar/img/arrow-right-icon.png'); ?>">
            </a>
        </h6>

        <p class="eli_p eli_font-s-16">
            <?php 
                echo $event->venueAddress1;
                if ($event->venueAddress2) echo ', '.$event->venueAddress2; 
                if ($event->venueCity) echo ', '.$event->venueCity;
                if ($event->venueState) echo ', '.$event->venueState;
                if ($event->venueZip) echo ' '.$event->venueZip;
            ?>

        </p>
        <?php if ($enableMapSection && !empty($googleKey)) : ?>
            <div id="artsopolis_calendar_map_canvas" class="eli_m-t-15"></div>
        <?php endif; ?>
        <p class="eli_p eli_full-map">
            <a class="eli_a" target="_blank" href="<?= 'http://maps.google.com/maps?f=q&hl=en&ie=UTF8om=1&q=' . $gmap_address; ?>">
                Full map and directions 
                <img class="eli_img eli_arrow-map" alt="Arrow icon" src="<?php echo plugins_url('artsopolis-calendar/img/arrow-right-icon.png'); ?>">
            </a>
        </p>
    </div>
</div> <!--.row-detail -->
<?php if ($enableMapSection && !empty($googleKey)) : ?>
    <script>

        var __elisoft = typeof __elisoft  === 'undefined'  ? {} : __elisoft;
        __elisoft.art_calendar = {};
        __elisoft.art_calendar.google_map_api_key = '<?php echo $googleKey ?>';
        <?php if (1==2 && $event->venueLatitude &&  $event->venueLongitude): ?>
            __elisoft.art_calendar.latitude  = '<?php echo $event->venueLatitude; ?>';
            __elisoft.art_calendar.longitude = '<?php echo $event->venueLongitude; ?>';
        <?php else: ?>
            __elisoft.art_calendar.gmap_address = '<?php echo $gmap_address; ?>';
        <?php endif ?>

    </script>
<?php endif; ?>
<?php } else { ?>
    This event no longer exists or has been deleted
<?php } ?>