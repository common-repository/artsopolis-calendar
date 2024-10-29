<?php

/**
 * This file is part of the Artsopolis Calendar Plugin.
 *
 * (c) vulh@elinext.com <http://elinext.com>
 *
 * This source file is subject to the elinext.com license that is bundled
 * with this source code in the file LICENSE.
 */
if (!session_id()) {
    session_start();
}
$logo_position = explode('_', $ac_options['plugin_logo_position']);
$enableSpotlight = isset($ac_options['enable_spotlight']) && $ac_options['enable_spotlight'] == '1';
$spotlightBgColor = isset($ac_options['settings_display_spotlight_bg_color']) && $ac_options['settings_display_spotlight_bg_color'] ?
    $ac_options['settings_display_spotlight_bg_color'] : '';

$controlNavNumBgColor = isset($ac_options['settings_display_spotlight_bg_control_nav_num']) && $ac_options['settings_display_spotlight_bg_control_nav_num'] ?
    $ac_options['settings_display_spotlight_bg_control_nav_num'] : '';
$activeNavNumBgColor = isset($ac_options['settings_display_spotlight_bg_active_control_nav_num']) && $ac_options['settings_display_spotlight_bg_active_control_nav_num'] ?
    $ac_options['settings_display_spotlight_bg_active_control_nav_num'] : '#DD9933';

$footerBgColor = isset($ac_options['settings_display_spotlight_bg_footer']) && $ac_options['settings_display_spotlight_bg_footer'] ?
    $ac_options['settings_display_spotlight_bg_footer'] : '';


$spotTitleFontSize = isset($ac_options['spotlight_title_font_size']) && $ac_options['spotlight_title_font_size'] ?
    $ac_options['spotlight_title_font_size'] : '';

$spotContentFontSize = isset($ac_options['spotlight_content_font_size']) && $ac_options['spotlight_content_font_size'] ?
    $ac_options['spotlight_content_font_size'] : '';

$view = isset($_GET['view']) ? $_GET['view'] : 'list';
$default_view = isset($ac_options['settings_default_view']) ? $ac_options['settings_default_view'] : '';

$isNotMonthView = (!isset($_GET['view']) && $default_view != 'month') || (isset($_GET['view']) &&  $_GET['view'] != 'month');

$hideSearchElmClass = $isNotMonthView ? '' : 'eli_hidden';
?>
<style>
    <?php if ($controlNavNumBgColor): ?>
    .artsopolis-calendar-slider .flex-control-nav li {
        background: <?php echo $controlNavNumBgColor ?>;
    }
    <?php endif; ?>

    <?php if ($footerBgColor): ?>
    .artsopolis-calendar-slider .silder-footer {
        background: <?php echo $footerBgColor ?>;
    }
    <?php endif; ?>

    <?php if ($spotTitleFontSize): ?>
    .artsopolis-calendar-slider .slider-content h2 {
        font-size: <?php echo $spotTitleFontSize ?>px !important;
    }
    <?php endif; ?>

    <?php if ($spotContentFontSize): ?>
    .artsopolis-calendar-slider .slider-content .meta.auth,
    .artsopolis-calendar-slider .slider-content .desc,
    .artsopolis-calendar-slider .slider-content .vmore  {
        font-size: <?php echo $spotContentFontSize ?>px !important;
    }
    <?php endif; ?>

    <?php if ( $footerBgColor ): ?>
    .artsopolis-calendar-slider .silder-footer {
        background: <?php echo $footerBgColor ?> !important;
    }
    <?php endif; ?>

</style>


<div class="eli_wrap artsopolis-calendar-frontend <?php echo (isset( $_GET['event_id'] ) && $_GET['event_id']) ? 'page-detail-calendar-event' : '' ?>">

    <?php
    if ($enableSpotlight && $spotlight_events && !(isset( $_GET['event_id'] ) && $_GET['event_id'])):

        $imgStyle = isset($ac_options['img_style']) ? $ac_options['img_style'] : '';

        ?>

        <div class="artsopolis-calendar-slider <?php echo $imgStyle ?>">
            <div class="flexslider">
                <ul class="slides">
                    <?php
                    foreach($spotlight_events as $i => $event):
                        if ( $i > 4) break;
                        $event = self::get_cdata_xml($event);
                        if ( !empty($event) && acIsExpiredTime($event->eventDateEnd,$event->event_dates_times) ) continue;
                        $event_img = self::get_event_url($event->eventImage);
                        if (! $ac_options['details_link_to']) {
                            $link = $event->link;
                            $target = $ac_options['open_new_tab'] ? 'target="_blank"' : '';
                        } else {
                            // Custom url follow the permalink structure
                            $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
                            $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
                            $title_slug = Artsopolis_Calendar_Shortcode::slugify($event->eventName);
                            $link = $parent_link. $url_ext_sign . $title_slug . '&event_id=' . $event->eventID. ( self::$fid ? '&fid='. self::$fid : '' );
                            $target = '';
                        }

                        $desc = strip_tags( str_replace('<br />', '<br/>', html_entity_decode($event->eventDescription)) ) ;
                        $short = explode( ' ',  $desc );

                        ?>
                        <li>
                            <div style="background:url(<?php echo $event_img ?>) center center; background-repeat: no-repeat; background-size:cover;" class="slider-pic <?php echo $imgStyle; ?>">    </div>
                            <div class="slider-content" <?php echo $spotlightBgColor ? 'style="background: '.$spotlightBgColor.' "' : '' ?>>
                                <h2 class="blog-ttl"><a <?php echo $target ?> href="<?php echo $link ?>"><?php echo $event->eventName; ?></a></h2>
                                <p class="meta auth"><span class="eli_span eli_title">Presented by </span><?php echo $event->orgName. ', '. $event->venueName.', '.$event->venueCity; ?></p>
                                <p class="desc"><?php echo implode(' ', array_slice( $short, 0, 30 )); ?></p><a href="<?php echo $link ?>" <?php echo $target ?> class="vmore">View more</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="silder-footer art-calendar-silder-footer"> </div>
            </div>
            <div class="loader"><a><i class="fa fa-spinner fa-spin fa-3x"></i></a></div>
        </div>
    <?php endif; ?>


    <br/>
    <div class="eli_wrap-inner">

        <div class="eli_content-can-filter">
            <div class="eli_content-can-filter-inner">
                <div class="eli_content">
                    <div class="eli_content-inner ">
                        <?php if ( ( ! isset( $ac_options['display_search_bar'] ) ) || ( isset( $ac_options['display_search_bar'] ) && $ac_options['display_search_bar'] == '1' ) ) : ?>
                            <div id="artsopolis-calendar-filter" class="eli_filter eli_clearfix" style="background-color: <?php echo isset($ac_options['settings_display_color']) ? $ac_options['settings_display_color']: '#e7e7e7'; ?> !important">
                                <div class="eli_filter-inner">
                                    <div class="eli_filter-item eli_inner-full">
                                        <input name="keyword" class="eli_input eli_keyword eli_m-r-10" id="keyword" placeholder="Keyword" />

                                        <?php
                                        if(! empty($ac_category)): ?>
                                            <div class="eli_category-filter eli_filter-item">
                                                <select class="eli_select eli_m-r-10 " name="category" placeholder="Category" id="filter-by-category">
                                                    <option value=""> Category </option>

                                                    <?php
                                                    //var_dump($ac_options['category']);exit;
                                                    $sub_prefix = '';
                                                    $category_list = array ();
                                                    $defaultCategory = self::checkDefaultCategory($ac_category, $ac_options['default-category-filter']);
                                                    foreach($ac_category as $key => $val): $category_list[] = $key;
                                                        $checked = ($key == $defaultCategory) ? 'selected' : '';
                                                        $sub_prefix = '';
                                                        if (isset($val['name']) && $val['name']): $sub_prefix = '-- ';?>
                                                            <option value="<?php echo $key ?>[+]<?php echo $val['name']; ?>" <?php echo $checked; ?>> <?php echo $val['name']; ?></option>
                                                        <?php endif; ?>

                                                        <?php
                                                        // Display the list subs cat
                                                        if (! isset($val['subs']) && ! isset( $val['subs'] )) continue;
                                                        foreach ($val['subs'] as $key => $sub_name) :$category_list[] = $key;
                                                            $checked = ($key == $defaultCategory) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?php echo $key ?>[+]<?php echo $sub_name; ?>" <?php echo $checked; ?>><?php echo $sub_prefix. $sub_name; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endforeach; ?>
                                                </select>

                                                <input value="<?php echo implode(',', $category_list); ?>" type="hidden" name="category-list" class="eli_input" id="category-list" />
                                                <input type="hidden" name="tags-category" class="eli_input" id="tags-category" category-name="" />
                                            </div>
                                        <?php endif; ?>

                                        <?php if(isset($ac_filter_venue) && $ac_filter_venue == 1): ?>
                                            <div class="eli_venue-filter eli_filter-item">
                                                <select name="venue" placeholder="Venue" id="filter-by-venue" class="eli_select">
                                                    <option value=""> Venue </option>
                                                    <?php foreach ($filterVenues as $venue) : ?>
                                                        <option value="<?php echo $venue['venueID']; ?>"><?php echo $venue['venueName']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($ac_filter_location == 1): ?>
                                            <div class="eli_location-filter eli_filter-item">
                                                <select name="location" placeholder="Location" id="filter-by-location" class="eli_select">
                                                    <option value=""> Location </option>
                                                    <?php foreach ($locations as $location) : ?>
                                                        <option value="<?php echo $location; ?>"><?php echo $location; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($ac_filter_date == 1 || !$isNotMonthView): ?>
                                            <div class="eli_date-filter eli_filter-item <?php echo $hideSearchElmClass ?>">
                                                <div class="eli_date-filter-from-to">
                                                    <div class="eli_date-filter-from-wrapper">
                                                        <input type="text" name="from_date" placeholder="Start date" class="eli_input eli_from-date eli_m-r-10 " id="artsopolis-calendar-filter-from-date" />
                                                    </div>
                                                    <div class="eli_date-filter-to-wrapper">
                                                        <input type="text" name="to_date" placeholder="End date" class="eli_input eli_end-date" id="artsopolis-calendar-filter-to-date"/>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="eli-btn-filter eli_filter-item <?php echo $hideSearchElmClass ?>">
                                            <div class="eli_date-util">
                                                <?php if($ac_filter_date == 1): ?>
                                                    <a href="javascript:void(0);" id="artsopolis-calendar-choice-today"
                                                       data-date-from=""
                                                       data-date-to=""
                                                       class="eli_a eli_button eli_button-gray eli_ac-btn-filter">Today</a>
                                                    <a href="javascript:void(0);" id="artsopolis-calendar-choice-tomorrow"
                                                       data-date-from=""
                                                       data-date-to=""
                                                       class="eli_a eli_button eli_button-gray eli_ac-btn-filter">Tomorrow</a>
                                                    <a href="javascript:void(0);" id="artsopolis-calendar-choice-weekend"
                                                       data-date-from=""
                                                       data-date-to=""
                                                       class="eli_a eli_button eli_button-gray eli_ac-btn-filter">This Weekend</a>
                                                <?php endif; ?>
                                                <a href="javascript:void(0);" id="reset" data-href="<?php echo (get_site_url().'/'. $ac_options['calendar_slug'])?>"
                                                   data-event-detail="<?php echo !empty( $_GET['event_id'] ) ? $_GET['event_id'] : '' ?>"
                                                   class="eli_a eli_button eli_button-gray">View All Events</a>

                                            </div>

                                        </div>


                                    </div>
                                    <div class="eli_filter-item eli_inner-full">
                                        <div class="eli_date-filter eli_filter-item ">
                                            <a href="javascript:void(0);" class="ac-btn-search eli_a eli_button eli_button-gray">Search</a>
                                            <a href="javascript:void(0);" class="eli_a eli_button eli_button-gray ac-btn-reset">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            else:
                                /**
                                 * Search bar is disabled
                                 * Start date and end date must be available as hidden HTML element for month view. In this case,
                                 * month view will get start date and end date for processing data
                                 */
                        ?>
                            <div id="artsopolis-calendar-filter" class="eli_filter eli_clearfix" style="display: none;">
                                <div class="eli_filter-inner">
                                    <div class="eli_date-filter eli_filter-item">
                                        <div class="eli_date-filter-from-to">
                                            <div class="eli_date-filter-from-wrapper">
                                                <input type="text" name="from_date" placeholder="Start date" class="eli_input eli_from-date eli_m-r-10 " id="artsopolis-calendar-filter-from-date" />
                                            </div>
                                            <div class="eli_date-filter-to-wrapper">
                                                <input type="text" name="to_date" placeholder="End date" class="eli_input eli_end-date" id="artsopolis-calendar-filter-to-date"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- #end filter -->


                        <?php include_once 'display-control.php'; ?>

                        <?php
                        if(! empty($ac_options['title'])): ?>
                            <div class="eli_produce-wrap">
                                <?php
                                if ( (isset($ac_options['not_display_title']) && $ac_options['not_display_title'] != 1) || !isset($ac_options['not_display_title']) ):
                                    ?>
                                    <div class="eli_produce-wrap-inner">
                                        <h2 class="eli_h2 calendar-event-main-title"><?php echo $ac_options['title'] ?></h2>
                                    </div>
                                <?php endif; ?>

                            </div>

                        <?php endif; ?>
                        <?php if ($logo_position[0] == 't' || $logo_position[0] == 'tb'): ?>
                            <div style="text-align: <?php echo isset($logo_position[1]) && $logo_position[1] ? $logo_position[1] : 'right' ?>">
                                <a href="<?php echo isset( $ac_options['main_logo_link_to'] ) ? $ac_options['main_logo_link_to'] : '';  ?>" target="_blank">
                                    <?php if (  $ac_options['plugin_logo_url'] ): ?>
                                        <img  src="<?php echo $ac_options['plugin_logo_url'];  ?>" />
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="eli_powered-by"><?php echo $ac_options['content']; ?></div>

                        <?php if ( isset( $_GET['event_id'] ) && $_GET['event_id'] ): ?>
                            <div id="artsopolis-calendar-detail-event">
                                <?php echo $html_events; ?>
                            </div>
                        <?php endif; ?>

                        <?php include dirname(__FILE__) . '/view/main-list.php';  ;?>

                    </div>
                </div>
            </div><!-- #content-can-filter-inner-->
        </div><!-- #content-can-filter-->
    </div>
</div><!-- #wrap -->
<?php if ($logo_position[0] == 'b' || $logo_position[0] == 'tb'): ?>
    <div style="text-align: <?php echo isset($logo_position[1]) && $logo_position[1] ? $logo_position[1] : 'right' ?>">
        <a href="<?php echo isset( $ac_options['main_logo_link_to'] ) ? $ac_options['main_logo_link_to'] : '';  ?>" target="_blank">
            <?php if (  $ac_options['plugin_logo_url']  ): ?>
                <img src="<?php echo $ac_options['plugin_logo_url'];  ?>" />
            <?php endif; ?>
        </a>
    </div>
<?php endif; ?>

<input type="hidden" id="ac-fid" value="<?php echo $fid ?>" />
<input type="hidden" name="ac_view" value="<?php echo isset($_GET['view']) ? $_GET['view'] : $default_view ?>" >