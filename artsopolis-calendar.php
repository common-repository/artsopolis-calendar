<?php

/**
 * @package Artsopolis Calendar plugin
 * @version 3.4.5
 */
/*
  Plugin Name: Artsopolis Calendar
  Plugin URI: http://wordpress.org/plugins/artsopolis-calendar/
  Description: Artsopolis Calendar for network members
  Author: Artsopolis
  Author URI: www.artsopolis.com
  Version: 3.4.5
 */

/* Remove options when uninstall */
function ac_uninstall() {

    $option_arr = @unserialize( get_option( AC_PLUGIN_OPTION_ARR_KEYS ) );
    delete_option( ARTSOPOLIS_CALENDAR_OPTIONS );

    if ( $option_arr ) {
        foreach ( $option_arr as $op ) {
            delete_option( Artsopolis_Calendar_API::get_option_key( $op ) );
        }
    }
}

register_uninstall_hook(__FILE__,'ac_uninstall');

if (! class_exists("Artsopolis_Calendar")) {

    class Artsopolis_Calendar {

        private static $is_page_shortcode = false;

        public static function init() {
            register_activation_hook(__FILE__, array(__CLASS__, 'ac_active'));
            register_deactivation_hook(__FILE__, array(__CLASS__, 'ac_deactive'));
            add_action('init', array(__CLASS__, 'register_scripts'));
            add_action('wp_footer', array(__CLASS__, 'print_scripts'));
            add_action('wp_enqueue_scripts', array(__CLASS__, 'callStyles'));
            add_action( 'widgets_init', array(__CLASS__, 'load_widgets') );

            add_action('init', array(__CLASS__, 'plugin_updater'));
        }

        public static function plugin_updater() {
            $version = get_option( AC_VERSION, false );
            $current_version = self::plugin_get_version();

            if ( $version === false || $version != $current_version ) {
                self::init_option();
                update_option( AC_VERSION, $current_version );
            }
        }

        /**
         * Returns current plugin version.
         *
         * @return string Plugin version
         */
        public static function plugin_get_version() {
            if ( ! function_exists( 'get_plugins' ) )
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
            $plugin_file = basename( ( __FILE__ ) );
            return $plugin_folder[$plugin_file]['Version'];
        }

        public static function ac_active() {
            self::init_option();
        }

        public static function init_option() {
            /* Only set when the first time install plugin */
            $option_arr = get_option( AC_PLUGIN_OPTION_ARR_KEYS, false );
            $first_opt  = get_option( ARTSOPOLIS_CALENDAR_OPTIONS, false);

            if ( $first_opt === false ) {
                update_option( ARTSOPOLIS_CALENDAR_OPTIONS, array(
                    'title'                      => '',
                    'feed_url'                   => '',
                    'settings_display_color'     => '#000000',
                    'settings_display_order'     => 'START-DATE-ASC',
                    'filter_date'                => '1',
                    'filter_location'            => '1',
                    'filter_price'               => '1',
                    'feed_valid'                 => '0',
                    'has_changed'                => 0,
                    'content'                    => '',
                    'category'                   => '',
                    'category_xml_feed_url'      => '',
                    'all_category_xml_feed_url'      => '',
                    'details_link_to'			 => 1, // Default link to source, 0 link to plugin,
                    'open_new_tab'			     => 0,
                    'all_cats'                   => '', // Save all categories to get the list tags in the list events,
                    'category_valid'             => '',
                    'all_category_valid'             => '',
                    'calendar_slug'              => '',
                    'teaser_widget_logo_url'     => '',
                    'teaser_widget_logo_position'=> TEASER_WIDGET_DEFAULT_LOGO_POSITION,
                    'plugin_logo_url'            => '',
                    'plugin_logo_position'       => AC_PLUGIN_DEFAULT_LOGO_POSITION,
                    'display_search_bar'         => 1,
                    'featured_only'              => 0,
                    'teaser_widget_logo_link_to' => '',
                    'drop_shadow_style'          => 0,
                    'active_override_css'        => 0,
                    'override_css_version'      => 1.0,
                    'disable_event_image'      => 0,
                    'enable_map_section'       => 1,
                    'ac_google_map_key'        => '',
                    'enable_hover_effect'      => 1,
                    'display-all-associated-categories'      => 0
                ));
            }

            if ( $option_arr === false ) {
                $option_arr = array('');
            } else {
                $option_arr = @unserialize( $option_arr );
                $option_arr = array_diff( $option_arr , array( 0 ) );
                $option_arr[] = '';
            }

            update_option( AC_PLUGIN_OPTION_ARR_KEYS, serialize( array_unique( $option_arr ) ) );
        }

        public function ac_deactive() {
            // Do nothing
        }

        public static function register_scripts() {
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_register_style('jquery-ui.css', plugins_url('artsopolis-calendar/css/jquery-ui.css'));

            wp_register_script('simple-pagination-js', plugins_url('js/jquery.simplePagination.js', __FILE__), array('jquery'), '1.0', true);

            // For gmap
            if ( isset( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] ) {
                remove_action('wp_head', 'rel_canonical');
                wp_register_script('artsopolis-calendar-gmap', plugins_url('/artsopolis-calendar/js/artsopolis-calendar-gmap.js'), array());
            }

            wp_register_script('artsopolis-calendar-js', plugins_url('artsopolis-calendar/js/artsopolis-calendar.js'), array(), AC_JS_VERSION);
            wp_register_script('artsopolis-calendar-ex-plugin-js', plugins_url('artsopolis-calendar/js/plugins/ex-plugins.js'), array(), AC_JS_VERSION);

            wp_register_script('jquery-datatable', plugins_url('artsopolis-calendar/js/plugin.min.js'));

            wp_localize_script('artsopolis-calendar-js', 'artsopolis_calendar_obj', array(
                'calendar_src' => plugins_url('artsopolis-calendar/img/date-button.gif'),
                'admin_url'    => admin_url('admin-ajax.php'),
            ));

            // Register angular js for month view
            self::registerScriptsForMonth();

            wp_register_style('ac-app-css', plugins_url('artsopolis-calendar/css/app.min.css'), array(), AC_CSS_VERSION);
        }

        public static function registerScriptsForMonth() {
            wp_register_script('artsopolis-calendar-moment-js', plugins_url('artsopolis-calendar/js/plugins/moment.min.js'));
            wp_register_script('artsopolis-calendar-angular-js', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.4.2/angular.min.js');
            wp_register_script('artsopolis-calendar-month-view-js', plugins_url('artsopolis-calendar/js/angularjs/month.js'), array(), AC_JS_VERSION);
        }

        public static function printScriptsForMonth() {
            wp_print_scripts('artsopolis-calendar-moment-js');
            wp_print_scripts('artsopolis-calendar-angular-js');
            wp_print_scripts('artsopolis-calendar-month-view-js');
        }

        public static function setPageShortcode($boolValue) {
            self::$is_page_shortcode = $boolValue;
        }

        public static function callStyles(){
            wp_enqueue_style('jquery-ui.css', plugins_url('artsopolis-calendar/css/jquery-ui.css'));
            if(WP_DEBUG) {
                wp_enqueue_style('ac-app-css', plugins_url('artsopolis-calendar/public/css/app.css'));
            } else {
                wp_enqueue_style('ac-app-css', plugins_url('artsopolis-calendar/public/css/app.min.css'), array(), AC_CSS_VERSION);
            }
        }

        public static function print_scripts() {

            if(!self::$is_page_shortcode) return;

            wp_print_styles('jquery-ui.css');
            wp_print_styles('ac-app-css');

            wp_print_scripts('artsopolis-calendar-ex-plugin-js');
            wp_print_scripts('artsopolis-calendar-js');
            wp_print_scripts('jquery-datatable');
            wp_print_scripts('simple-pagination-js');

            if ( isset( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] ) {
                wp_print_scripts('artsopolis-calendar-gmap');
            }

            self::printScriptsForMonth();
        }

        public static function load_widgets () {

            // register the events widget
            register_widget( 'artsopolis_calendar_teaser_widget' );
        }
    }
}

Artsopolis_Calendar::init();

if (! class_exists('Artsopolis_Calendar_API')) {
    class Artsopolis_Calendar_API {
        public static $feed_url,
            $categories_url = '',
            $option_key = '',
            $xml_file_path = '',
            $fid = '';

        /**
         * Get content from url
         */
        public static function get_request_content($url) {
            $request = new WP_Http;

            $content = $request->request($url, array(
                'timeout' => TIMEOUT_REQUEST_GET_XML_CONTENT,
                'sslverify' => false, // fix the issue https://git.elidev.info/nhanlt/apollo-theme/-/issues/49#note_4800
            ));

            if (is_wp_error($content)) {
                return false;
            }

            return $content['body'];
        }

        public static function get_cdata_xml($cdata) {
            $event_date_times = $cdata->eventDatesTimes->datetime;
            $event_times = array();

            if ($event_date_times) {
                foreach ($event_date_times as $item) {
                    $date = (string) $item->date;
                    $time = (string) $item->time;
                    $endTime = (string) $item->end_time;
                    $endTime = !empty($endTime) ? date('H:i',strtotime($endTime)) : '';
                    $timestamp = (int) $item->timestamp;
                    if (trim($date)  && trim($time)) {
                        $event_times[] = (object) array(
                            'date'  => $date,
                            'time'  => $time,
                            'end_time' => $endTime,
                            'timestamp' => $timestamp // @ticket #25103: Git-248 Calendar plugin date display option
                        );
                    }
                }
            }

            return (object) array(
                'orgImage'          => (string) $cdata->orgImage,
                'eventImage'        => (string) $cdata->eventImage,
                'eventName'         => (string) $cdata->eventName,
                'eventDateBegin'    => (string) $cdata->eventDateBegin,
                'eventDateEnd'      => (string) $cdata->eventDateEnd,
                'eventDatesTimes'   => (string) $cdata->eventDatesTimes,
                'orgName'           => (string) $cdata->orgName,
                'eventUrl'          => (string) $cdata->eventUrl,
                'websiteUrl'        => (string) $cdata->websiteUrl,
                'venueID'           => (string) $cdata->venueID,
                'venueName'         => (string) $cdata->venueName,
                'venueAddress1'     => (string) $cdata->venueAddress1,
                'venueAddress2'     => (string) $cdata->venueAddress2,
                'venueCity'         => (string) $cdata->venueCity,
                'venueState'        => (string) $cdata->venueState,
                'venueZip'          => (string) $cdata->venueZip,
                'venueLatitude'     => (string) $cdata->venueLatitude,
                'venueLongitude'    => (string) $cdata->venueLongitude,
                'eventPhone1'       => (string) $cdata->eventPhone1,
                'eventPhone2'       => (string) $cdata->eventPhone2,
                'eventEmail'        => (string) $cdata->eventEmail,
                'eventTicketUrl'    => (string) $cdata->eventTicketUrl,
                'eventStartTime'    => (string) $cdata->eventStartTime,
                'eventType'         => (string) $cdata->eventType,
                'tags'              => (string) $cdata->tags,
                'eventDescription'  => (string) $cdata->eventDescription,
                'link'              => (string) $cdata->link,
                'eventID'           => (string) $cdata->eventID,
                'event_dates_times' => $event_times,
                'eventTicketInfo'   => (string) $cdata->eventTicketInfo,
                'discountUrl'       => (string) $cdata->discountUrl,
                'categories'        => (string) $cdata->categories,
                'ongoing'           => (string) $cdata->ongoing,
                'ticketPhone'       => (string) $cdata->ticketPhone,
            );
        }

        private static function _create_dir( $path ) {

            if ( ! is_dir( $path ) ) {
                mkdir( $path );
            }

            if ( ! is_writable( $path ) ) {
                @chmod( $path, 0777 );
            }
        }

        /**
         * Save xml file
         * @author: vulh
         * return void
         */
        public static function save_xml_data() {
            $data = self::get_request_content(self::$feed_url);

            if ($data) {

                self::_create_dir( CALENDAR_UPLOAD_DIR );
                self::_create_dir( CALENDAR_UPLOAD_DIR. '/'. ac_get_current_domain() );

                // Process xml file
                if (! is_writable( self::$xml_file_path )) {
                    @chmod(self::$xml_file_path, 0777);
                }

                $result = @file_put_contents( self::$xml_file_path, $data);
                if ($result  === false) {
                    exit('Please set write permission for '.CALENDAR_UPLOAD_DIR.'/'.ac_get_current_domain().' folder');
                }
            } else if (!is_admin()) {
                exit('Please re-check plugin configuration <a href="/wp-admin/admin.php?page=admin-artsopolis-calendar">Click here</a>');
            }
        }

        /**
         * Check can override the xml file
         * @author: vulh
         * return boolean
         */
        public static function check_can_override_xml_file($hour) {
            $ac_options = get_option( self::$option_key );

            $has_change_api = $ac_options['has_changed'];

            $filename = self::$xml_file_path;
            $time_create = @filemtime($filename);
            $can_modify_time = $time_create + $hour * 3600;

            // Update option
            if ($ac_options['has_changed']) {
                $ac_options['has_changed'] = 0;
                update_option( self::$option_key, $ac_options);
            }

            return ! file_exists($filename) || time() > $can_modify_time || $has_change_api || ! $ac_options['feed_url'];
        }

        public static function get_categories() {
            $data = @simplexml_load_string(self::get_request_content(self::$categories_url));
            $result = array();

            if ( ! is_object( $data ) ) {
                return array();
            }

            $cats = $data->cat_id;
            $sub_cats = $data->subcat_ids;

            $active = isset($data->active) ? $data->active : 1;

            if ( !empty($cats) ) {
                foreach ($cats as $cat) {
                    $cat = explode('_', (string) $cat);
                    $result[$cat[0]]['name'] = $cat[2];
                    $result[$cat[0]]['active'] = $active;
                    $result[$cat[0]]['key']  = $cat[0]. '_'. $cat[1];
                    $result[$cat[0]]['subcats'] = array();
                }
            }

            if ( ! empty( $sub_cats ) ) {
                foreach ($sub_cats as $sub_cat) {
                    if(empty($sub_cat->subcat_id)) continue;

                    foreach ($sub_cat->subcat_id as $sub_cat_id) {
                        $sub_cat_id = explode('_', $sub_cat_id);

                        $result[$sub_cat_id[0]]['subcats'][] = $sub_cat_id[2].'[+]'.$sub_cat_id[0].'_'.$sub_cat_id[1];
                    }

                    $result[$sub_cat_id[0]]['subcats'] = self::sort_sub_category($result[$sub_cat_id[0]]['subcats']);
                }
            }

            $result = self::sort_parent_category($result);

            return $result;
        }

        public static function sort_parent_category(&$result) {
            usort($result, 'ac_admin_parent_sort_by_alpha');
            return $result;
        }

        public static function sort_sub_category(&$result) {
            usort($result, 'ac_admin_sub_sort_by_alpha');
            $after_sort = array();
            foreach ($result as $item) {
                $arr = explode('[+]', $item);
                @$after_sort[$arr[1]] = $arr[0];
            }
            return $after_sort;
        }

        public static function get_option_key( $fid = '' ) {
            return ARTSOPOLIS_CALENDAR_OPTIONS. self::get_geed_id( $fid );
        }

        public static function get_geed_id( $fid = '' ) {
            if ( $fid === 0 ) return '';

            if ( $fid ) return '_'. intval( $fid );

            return isset( $_REQUEST['fid'] ) && $_REQUEST['fid'] ? '_'. $_REQUEST['fid'] : '';
        }

        public static function get_feature_events_key( $fid ) {
            return AC_FEATURED_EVENTS. self::get_geed_id( $fid );
        }

        public static function get_spotlight_events_key( $fid ) {
            return AC_SPOTLIGHT_EVENTS. self::get_geed_id( $fid );
        }

        public static function init_data( $fid ) {
            self::$option_key = self::get_option_key($fid);
            self::$xml_file_path = self::get_xml_fullpath( $fid );
            self::$fid = $fid;
        }

        public static function get_xml_fullpath( $fid ) {
            return XML_FILE_PATH. '/'. XML_BASE_NAME. self::get_geed_id( $fid ). '.xml';
        }
    }
}

require_once(dirname(__FILE__). '/function.php');
require(dirname(__FILE__). '/config.php');
require_once(dirname(__FILE__). '/widgets/teaser/teaser.php');
require_once(dirname(__FILE__). '/frontend/shortcode.php');

if (is_admin()) {
    require_once(dirname(__FILE__). '/admin/admin.php');
    
    /* Active settings link in Plugins Listing page*/
    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'ac_add_settings_link', 10);

    function ac_add_settings_link($links) {
        $settings_link = '<a href="plugins.php?page=admin-artsopolis-calendar">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }
}