<?php

/**
 * This file is part of the Artsopolis Calendar Plugin.
 *
 * (c) vulh@elinext.com <http://elinext.com>
 *
 * This source file is subject to the elinext.com license that is bundled
 * with this source code in the file LICENSE.
 */
?>

<?php 

// Start to get the current url
if (!session_id()) {
    session_start();
}

$numShowDate = 3;
if(!empty($events)):
$imgStyle = isset($ac_options['img_style']) ? $ac_options['img_style'] : '';
$classNoImage = !empty($ac_options['disable_event_image']) ? 'ac-event-no-image' : '';
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
		$link = $parent_link. $url_ext_sign . $title_slug . '&event_id=' . $event->eventID. ( self::$fid ? '&fid='. self::$fid : '' );
		$target = '';
	}
    
?>

<div class="eli_row">
<h4 class="eli_h4 eli_title">
    <a class="eli_a" <?php echo $target; ?> href="<?php echo $link; ?>"><?php echo $event->eventName; ?></a>
    <div class="hidden"><?php echo $event->eventDateBegin ?></div>
</h4>
    <p class="endate eli_hidden"><?php echo $event->eventDateEnd; ?></p>
<span class="eli_span eli_address">
    <span class="eli_span eli_title">Presented by </span>
    <?php echo $event->orgName. ($event->venueName ? ' at '.$event->venueName. ($event->venueCity ? ', '. $event->venueCity : '') : '') ?>
</span>
<div class="eli_row-main <?php echo $classNoImage?>">
    <a <?php echo $target; ?> href="<?php echo $link; ?>" class="eli_a eli_img <?php echo $imgStyle ?>"><img src="<?php echo $event_img; ?>" class="eli_img" /></a>

    <div class="eli_information">
        <div class="eli_information-inner">
            <div class="eli_information-left">
                <?php if ($event->event_dates_times): ?>
                <div class="eli_offer-date clear">
                    <span class="eli_span eli_offer-date-title">Upcoming Dates:</span>
                    <?php 
                        $i = 0;
                        $total_dates = count($event->event_dates_times);
                        foreach ($event->event_dates_times as $date_time) { 

                        $dateData = explode('-', $date_time->date);


                        $date = $dateData ? $dateData[0]. ', '.$dateData[1]. ' '. ($dateData[2] < 10 ? '0'. $dateData[2] : $dateData[2]) .' @ '. $date_time->time : '';

                        if ($i < 3) { ?>
                        <span class="eli_span eli_full-price-content eli_full-w-float-l"><?php echo $date ?></span>
                        <?php echo $i == 2 && $total_dates > 3 ? '<span class="eli_expand-more-dates"> + '.($total_dates - $i - 1).' more dates and times</span>' : ''; ?>
                    <?php } else { 
                        echo $i == 3 ? '<p class="eli_p eli_more-date eli_hidden">' : '';
                    ?>
                        <span class="eli_span eli_full-price-content eli_full-w-float-l"><?php echo $date ?></span>
                    <?php
                        echo $i >= 3 && $i == $total_dates - 1 ? '<span class="eli_span eli_collapse-more-dates">- less dates and times</span></p>' : '';
                        }
                        $i++;
                    } ?>
                    
                </div>
                <?php endif; ?>
            </div>


            <?php
            $eventTicketUrl = $event->eventTicketUrl;
            $discountUrl = $event->discountUrl;
            if (trim($eventTicketUrl) || trim($discountUrl)): ?>
            <div class="eli_information-right">
                <?php if ($eventTicketUrl): ?>
                <div class="eli_button-container">
                    <a  target="_blank" href="<?php echo $eventTicketUrl ?>" class="eli_a eli_button eli_large"><span class="eli_span">Buy Tickets</span></a>
                </div>
                <?php endif; ?>
                
                <?php if (isset($discountUrl) && $discountUrl): ?>
                <div class="eli_button-container">
                    <a target="_blank" href="<?php echo $discountUrl ?>" class="eli_a eli_button eli_large eli_green eli_m-t-5"><span class="eli_span">Check Discounts</span></a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div> <!--#information -->

</div>

<div class="artsopolis-calendar-summary eli_summary">
    <div class="eli_summary-short">
        <?php
        $limitDesc = isset($ac_options['limit_desc_listing']) && intval($ac_options['limit_desc_listing']) ? $ac_options['limit_desc_listing'] : AC_LIMIT_DESC_LISTING;
        $descArr = acGetStringByLength($event->eventDescription, $limitDesc);
        echo acFormatContent($descArr['text']);
        ?>
        
        <?php if ( $descArr['have_more'] ) { ?>
        <a href="javascript:void(0);" class="eli_a eli_expand-summary">[more+]</a>
        <?php } ?>
       
    </div>
    
    <div class="eli_summary-full eli_hidden">
        <?php echo acFormatContent($event->eventDescription); ?>
        <a href="javascript:void(0);" class="eli_a eli_less-summary">[less-]</a>
    </div>
</div>
    
<?php if ($event->tags) :
    $tags = explode(',', $event->tags); 
    $categories = explode(',', $event->categories); 
    
    
    $cat_comb = array();
    $i = 0;

    foreach ($categories as $cat) {
        $cat = trim($cat);
        if (isset($selected_category) && in_array($cat, $selected_category)) {
            $cat_comb[] = (isset($tags[$i]) ? $tags[$i] : ''). '[+]'. $cat;
        }
        $i++;
    }
    $cat_comb_sort = self::sort_tags($cat_comb);
    
    $flag = 0;
?>
    <h6 class="eli_h6 eli_tags">Tags: <?php
        foreach ($cat_comb_sort as $tag):

            $tag = explode('[+]', $tag);
            if (!$tag[0]) continue;
            echo $flag > 0 ? ',' : '';
            echo '&nbsp;<a class="eli_a" category-name="'.ltrim($tag[0]).'" category="'.$tag[1].'">';
            echo $tag[0].'</a>';
            $flag++;
        endforeach; ?>
    </h6> 
<?php endif; ?>
<div style="clear:both"></div>

</div><!-- #end row -->
<?php endforeach; ?>

<?php if(isset($arr_filter['repagination']) && $arr_filter['repagination'] === 'yes'): ?>
    <script type="text/javascript">
        var artsopolis_calendar_paging = artsopolis_calendar_paging || {};
        artsopolis_calendar_paging = {
            total_event: <?php echo $total_event ?>,
            page_size: <?php echo $page_size ?>
        };
    </script>
    <?php endif; ?>


<?php else: ?>
    <div class="note">
        <?php if (isset($ac_options['category']) && $ac_options['category']) { ?>
        Your search did not return any matching results. Please <a class="eli_a artsopolis-calendar-search-again">search again</a>.
        <?php } else { ?>
        Please select at least a category to display events <a class="eli_a" href="/wp-admin/plugins.php?page=admin-artsopolis-calendar">Click here</a>
        <?php } ?>
        <script type="text/javascript">
            var artsopolis_calendar_paging =  artsopolis_calendar_paging || {};
            artsopolis_calendar_paging.total_event = 0;
        </script>
    </div>
<?php endif; ?>
