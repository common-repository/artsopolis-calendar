<?php
ob_start();
/**
 * This file will be generator the shortcode
 * It will be extended Artsopolis_Calendar_API
 * in the artsopolis-calendar.php in the root of this plugin
 */

if (!class_exists('Artsopolis_Calendar_Shortcode')) {
    class Artsopolis_Calendar_Shortcode extends Artsopolis_Calendar_API {

        /** @ticket #25103: Git-248 Calendar plugin date display option
         * add Next Active Date option
         */
        public static  $arr_filters = array(
            'START-DATE-ASC' => 'Start Date',
            'END-DATE-ASC'   => 'End Date',
            'ALPHABETA'      => 'Alphabetical (by event title)',
            'NEXT-ACTIVE-DATE'=> 'Next Active Date',
        );

        public static $arr_views = array(
            'default' => 'List',
            'tile' => 'Tile',
            'month' => 'Month',
        );

        private static $_total_event = 0;

        public static function init() {
            // handle the shortcode to display in the frontend
            add_shortcode( AC_SHORTCODE_KEY, array(__CLASS__, 'handle_shortcode'));

            // Register an action for ajax
            add_action( 'wp_ajax_nopriv_ac_get_feed', array(__CLASS__,'ac_get_feed' ));
            add_action('wp_ajax_ac_get_feed', array(__CLASS__, 'ac_get_feed'));

            if (isset($_GET['event_id']) && $_GET['event_id']) {
                add_action('wp_head', array(__CLASS__, 'add_meta_tags_fb'));
            }
        }

        /**
         * @param $id
         * @param $options
         */
        public static function ac_enqueue_override_style($id, $options){
            /**
             * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
             */
            if (isset($options['active_override_css']) && $options['active_override_css'] &&  file_exists(getPathOverrideCss($id) ) ) {
                $version = isset($options['override_css_version']) ? $options['override_css_version'] : 1;
                wp_enqueue_style('ac-override-css', getUrlOverrideCss($id), false, $version);
            }
        }

        public static function handle_shortcode($atts) {
            // Set the current url of the page or post, using for the ajax request when we handle the link for event
            if (!session_id()) {
                session_start();
            }

            /* Only show javascripts on page have shortcode */
            Artsopolis_Calendar::setPageShortcode(true);

            // Only get the base url in the parent page
            if (! isset($_GET['event_id']) && ! isset($_GET['category'])) {
                $_SESSION['artsopolis_calendar_current_url'] = get_site_url(). $_SERVER["REQUEST_URI"];
            }

            extract(shortcode_atts(array(
                'hour'  => isset($atts['hour']) && $atts['hour'] ? $atts['hour'] : OVERRIDE_TIME_XML_FILE,
                'fid'   => isset($atts['fid']) && $atts['fid'] ? $atts['fid'] : '',
            ), $atts));

            self::init_data( $fid );
            // Get options from the settings
            $ac_options = get_option( self::$option_key, false );
            if ( $ac_options === false ) {
                echo 'The plugin option is not exist. Please try to check you config again';
                exit;
            }

            /**
             * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
             */
            self::ac_enqueue_override_style($fid, $ac_options);

            extract($ac_options, EXTR_PREFIX_ALL, 'ac');

            self::$feed_url = $ac_options['feed_url'];

            // Get the category array for filter
            $category_data = self::_process_category_opt( isset( $ac_category ) ? $ac_category : '' );

            if (isset($_GET['event_id']) && $_GET['event_id']) {
                $xml = @simplexml_load_file(self::$xml_file_path);
                $event_id = $_GET['event_id'];
                $event = $xml->xpath("event[eventID=$event_id]");
                $html_events = self::get_detail_event($event);
                $allEvents =  $xml->event;

                /**
                 * @ticket #24413: Git-49 Artsopolis Calendar plugin modifications
                 */
                $the_event = ! empty($event) ? self::get_cdata_xml($event[0]) : '';
                $slug = self::slugify($the_event->eventName) ?? '';
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                // redirect old url to new url (include event_slug)
                if (preg_match('/\?(.*?)=/', $url, $match) == 1) {
                    if ($match[0] == '?event_id=') {
                        $new_url = str_replace("event_id",$slug.'&event_id', $url);
                        header("Location: $new_url");
                    }
                }
            } else {

                if (self::check_can_override_xml_file($hour)) {
                    Artsopolis_Calendar_API::save_xml_data();
                }

                // Filter by category when click on the tags
                if (isset($_GET['category']) && $_GET['category']) {
                    $categories = $_GET['category'];
                }

                // Get the list events and some value for the template
                $arr_filter = array(
                    'page'      => 1,
                    'category'  => isset( $category_data['categories'] ) ? $category_data['categories'] : array(),
                    'first_tab'   => true, // Filter all events has date end less than 2037-01-01 for second tab
                );

                /**
                 * Filter by default category
                 */
                $defaultCategory = self::checkDefaultCategory($ac_category, $ac_options['default-category-filter']);
                if (!empty($defaultCategory)) {
                    $arr_filter['category'] = $defaultCategory;
                }

                // Featured only
                if ( isset($ac_options['featured_only']) && $ac_options['featured_only'] ) {
                    $arr_filter['featured_events'] = get_option( AC_FEATURED_EVENTS. self::get_geed_id( $fid ) );
                }

                $events = self::get_list_events_data($arr_filter);

                // Validate ongoing data
                $ongoingFilter = $arr_filter;
                $ongoingFilter['first_tab'] = false;
                $ongoingFilter['force_ongoing'] = true;
                $ongoingData = self::get_list_events_data($ongoingFilter);

                $total_event = self::$feed_url ? count($events) : 0;

                $page_size = !empty($ac_options['number_item_perpage']) ? $ac_options['number_item_perpage'] : FRONT_END_PAGE_SIZE;

                if (!session_id()) {
                    session_start();
                }

                $html_events = self::get_html_list_events($events, array(
                    'page' => 1,
                    'view' => isset($_GET['view']) ? $_GET['view'] : ''
                ), isset( $category_data['keys'] ) ? $category_data['keys'] : '' );

                $xml = @simplexml_load_file(self::$xml_file_path);
                $allEvents = $xml;
            }

            if ($xml == false || ! $ac_options['feed_valid']) {
                echo 'The feed url is invalid. Please try to check it again';
                exit;
            }

            // Get list location
            $filterVenues = array();
            if (isset($ac_options['filter_venue']) && $ac_options['filter_venue'] == 1) {
                $filterVenues = self::getVenuesData($allEvents);

            }

            // Get list location
            $locations_xml = $xml->xpath('event/venueCity');

            $_locations = array();
            if (! empty($locations_xml)) {
                foreach ($locations_xml as $location) {
                    if ($l = (string) $location) {
                        $_locations[rtrim($l)] = $l;
                    }
                }
            }
            $locations = array_values($_locations);
            asort($locations);

            // Get spotlight events
            $spotlight_events = Artsopolis_Calendar_Shortcode::get_spotlight_events(self::$fid);

            // Render html and return
            ob_start();
            include dirname(__FILE__). '/frontend-template.php';
            $html = ob_get_contents();
            ob_end_clean();
            return $html;

        }

        public static function checkDefaultCategory($ac_category, $defaultCategory) {
            if (!isset($_GET['category'])) {
                foreach ($ac_category as $key => $cat) {
                    if ($key == $defaultCategory) {
                        return $defaultCategory;
                    }
                    if ($cat['subs']) {
                        foreach ($cat['subs'] as $subKey => $subValue) {
                            if ($subKey == $defaultCategory) {
                                return $defaultCategory;
                            }
                        }
                    }
                }
            }
            return '';
        }

        public static function getVenuesData($events){
            $venues = array();

            foreach ($events as $event) {
                if (!empty((string)$event->venueID)) {
                    $venues[(string)$event->venueID] = array(
                        'venueID' => (string)$event->venueID,
                        'venueName' => (string)$event->venueName
                    );
                }
            }

            // sort by venueName
            usort($venues, function ($item1, $item2) {
                if ($item1['venueName'] == $item2['venueName']) return 0;
                return $item1['venueName'] < $item2['venueName'] ? -1 : 1;
            });

            return $venues;
        }

        public static function add_meta_tags_fb() {
            $event_id = $_GET['event_id'];
            $fid = isset( $_GET['fid'] ) ? $_GET['fid'] : '';
            $xml = @simplexml_load_file( self::get_xml_fullpath( $fid ) );

            if (! $xml) {
                return false;
            }

            $event = $xml->xpath("event[eventID=$event_id]");

            if (! empty($event)) {
                $event = self::get_cdata_xml($event[0]);
                if ( !empty($event) && acIsExpiredTime($event->eventDateEnd,$event->event_dates_times) ) return false;
            } else {
                return false;
            }

            $desc = preg_replace("/<.*?>/", "", $event->eventDescription);
            $url = get_site_url(). $_SERVER["REQUEST_URI"];
            echo '
                 <link rel="canonical" href="'.$url.'" />
                 <meta property="og:title" content="'.$event->eventName.'"/>'
                . '<meta property="og:url" content="'.$url.'"/>'
                . '<meta property="og:image" content="'.self::get_event_url($event->eventImage).'"/>'
                . '<meta property="og:description" content="'.substr($desc, 0, 200).'"/>';
        }

        public static function _process_category_opt($ac_category) {

            if (empty($ac_category)) {
                return array();
            }

            $categories = array();
            $cat_keys = array ();
            foreach ($ac_category as $key => $cats) {

                if (!empty($cats['name'])) {
                    $categories[] = $key. '[+]'. ( isset( $cats['name'] ) ? $cats['name'] : '' );
                    $cat_keys[] = $key;
                }

                if (isset($cats['subs']) && $cats['subs']) {
                    foreach ($cats['subs'] as $key => $val) {
                        $categories[] = $key. '[+]'. $val;
                        $cat_keys[] = $key;
                    }
                }
            }

            return array('categories' => $categories, 'keys' => $cat_keys);
        }

        /**
         *    Get the detail event
         * @param $event
         * @return html
         * @internal param int $event_id
         * @author vulh
         *
         */
        public static function get_detail_event($event) {
            $ac_options = get_option( self::$option_key );

            if (! $ac_options['category']) {
                return 'Please select at least a category to display events <a href="/wp-admin/admin.php?page=admin-artsopolis-calendar">Click here</a>';
            }

            $event = ! empty($event) ? self::get_cdata_xml($event[0]) : '';

            // Thienld : custom logic to check expired event
            if ( !empty($event) && acIsExpiredTime($event->eventDateEnd,$event->event_dates_times) ) return "";

            ob_start();
            include dirname(__FILE__) . '/detail-event-template.php';
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        public static function get_list_events_data($arr_filter) {

            $page_size = FRONT_END_PAGE_SIZE;

            if ( ! file_exists( self::$xml_file_path ) || ! file_get_contents( self::$xml_file_path ) ) {
                return array();
            }

            $xml = @simplexml_load_file( self::$xml_file_path );

            if (!$xml) return array();

            $xpath_query = self::_get_xpath_query($arr_filter);

            $events = $xml->xpath($xpath_query);

            // Get options from the settings

            $_arr_key = explode( '_', self::$option_key );
            $fid = $_arr_key ? $_arr_key[count( $_arr_key ) - 1] : '';

            $ac_options = get_option( self::$option_key );

            $settings_display_order = isset($ac_options['settings_display_order']) ?  $ac_options['settings_display_order']: 'START-DATE-ASC';
            $events = self::_sort_events($events, $settings_display_order);

            if (empty($ac_options['category'])) {
                return array();
            }

            return $events;
        }

        public static function _sort_events (&$events , $settings_display_order) {
            switch($settings_display_order) {
                case 'START-DATE-ASC':
                    usort($events, 'ac_sort_by_start_upcomming_time');
                    break;
                case 'END-DATE-ASC':
                    usort($events, 'ac_sort_by_end_date');
                    break;
                case 'ALPHABETA':
                    usort($events, 'ac_sort_by_alpha');
                    break;
                case 'NEXT-ACTIVE-DATE':
                    $events = self::acSortByNextActiveDate($events);
                    break;
                case 'PRICE-HIGHT':
                    usort($events, 'ac_sort_by_price_hight');
                    break;
                case 'PRICE-SLOW':
                    usort($events, 'ac_sort_by_price_slow');
            }

            return $events;

        }

        /** @ticket #25103: Git-248 Calendar plugin date display option
         * git: https://git.elidev.info/nhanlt/apollo-theme/-/issues/248
         * @param $events
         * @return array
         */
        public static function acSortByNextActiveDate($events) {
            $firstEvents = $secondEvents = [];
            $beginTimeOfDay = date('Y-m-d 00:01');
            $endTimeOfDay   = date('Y-m-d 23:59');

            foreach ($events as $key => $event) {
                if ($event->eventDatesTimes->datetime) {
                    $startDate = $event->eventDatesTimes->datetime[0]->timestamp;

                    if($startDate <= strtotime($endTimeOfDay)) {
                        foreach($event->eventDatesTimes->datetime as $date ) {
                            if ($date->timestamp >= strtotime($beginTimeOfDay)) {
                                $event->activeDate = $date->timestamp;
                                break 1;
                            }
                        }
                        $firstEvents[] = $event;
                    } else {
                        $event->activeDate = $event->eventDatesTimes->datetime[0]->timestamp;
                        $secondEvents[] = $event;
                    }
                }
            }
            $events    = array_merge( $firstEvents, $secondEvents );

            return self::_sort_event_listing($events);
        }

        public static function _sort_event_listing(&$events) {
            usort($events, 'ac_sort_by_event_active_date');
            return $events;
        }

        public static function sort_tags(&$tags) {
            usort($tags, 'ac_admin_sub_sort_by_alpha');
            return $tags;
        }

        /**
         * Do xpath query
         * @param $arr_filter
         * @return string
         */
        private static function _get_xpath_query($arr_filter) {
            $key_uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $key_lowercase = 'abcdefghijklmnopqrstuvwxyz';

            $arr_condition = array();

            // Generate featured query
            if (isset($arr_filter['featured_events']) ) {

                if (!$arr_filter['featured_events']) {
                    return false;
                }

                $featuredQuery = array();
                foreach ($arr_filter['featured_events'] as $event_id) {
                    $featuredQuery[] = 'contains(eventID, "'.$event_id.'")';
                }
                $arr_condition[] = ' ('. implode(' or ', $featuredQuery). ') ' ;
            }

            $fromDate = ! empty($arr_filter['from_date']) ? $arr_filter['from_date'] : '';
            $toDate = ! empty($arr_filter['to_date']) ? $arr_filter['to_date'] : '';
            $from = str_replace(DELIMITER_DATE, '', $fromDate);
            $to = str_replace(DELIMITER_DATE, '', $toDate);


            // Change the operator to OR if we select the this weedken
            if ( isset( $arr_filter['this_weedken'] ) && intval($arr_filter['this_weedken']) == 1 ) {

                $arr_condition[] = " (eventDatesTimes/datetime/date_filter = $from or eventDatesTimes/datetime/date_filter = $to )";

            } else {

                if ($from && $to) {
                    $arr_condition[] = " eventDatesTimes/datetime/date_filter[. >= $from and $to >= . ] ";
                }
                else if ($from) {
                    $arr_condition[] = " eventDatesTimes/datetime/date_filter[. >= $from ] ";
                }
                else if ($to) {
                    $arr_condition[] = " eventDatesTimes/datetime/date_filter[ $to >= . ] ";
                }
            }

            /**
             * Do not care about the ongoing filter because
             * we will hide the tab menu in the month view
            */
            if (!isset($arr_filter['view']) || $arr_filter['view'] != 'month') {
                if ( isset( $arr_filter['first_tab'] ) && $arr_filter['first_tab'] ) {
                    $arr_condition[] = " (translate(normalize-space(ongoing), ' ', '') != 'Y' and translate(normalize-space(ongoing), ' ', '') != 'y') ";
                } else {
                    // Only filter event in the front end. So we make it avoid filter in
                    // featured events

                    if ( (is_admin() && $_REQUEST['page'] !== 'artsopolis-calendar-featured-events') || !empty($arr_filter['force_ongoing']) ) {
                        $arr_condition[] = " (translate(normalize-space(ongoing), ' ', '') = 'Y' or translate(normalize-space(ongoing), ' ', '') = 'y') ";
                    }
                }
            }


            if (! empty($arr_filter['location'])) {
                $arr_condition[] = 'venueCity="'.$arr_filter['location'].'"';
            }

            if (!empty($arr_filter['venue'])) {
                $arr_condition[] = 'venueID="'.$arr_filter['venue'].'"';
            }

            if (!empty($arr_filter['keyword'])) {
                $keyword = strtolower($arr_filter['keyword']);
                $arr_condition[] = ' (contains(translate(eventName, "'.$key_uppercase.'", "'.$key_lowercase.'"), "'.$keyword.'")'
                    . ' or contains(translate(orgName, "'.$key_uppercase.'", "'.$key_lowercase.'"), "'.$keyword.'")'
                    . ' or contains(translate(venueName, "'.$key_uppercase.'", "'.$key_lowercase.'"), "'.$keyword.'")'
                    . ' or contains(translate(venueCity, "'.$key_uppercase.'", "'.$key_lowercase.'"), "'.$keyword.'") ) ';
            }

            $category_list_where = '';

            // If don't have any category param in the filter, 
            // get the list categories selected in the backend
            $op = get_option( self::$option_key );
            if (empty($arr_filter['category']) && ! empty($op['category'])) {
                $cat_data = self::_process_category_opt($op['category']);
                $arr_filter['category'] = $cat_data['categories'];
            }

            if (! empty($arr_filter['category'])) {
                if (is_array($arr_filter['category'])) {
                    $arr_categories_list = array();
                    foreach ($arr_filter['category'] as $category) {
                        $cat_arr = explode('[+]', $category);
                        $cat_id     = isset($cat_arr[0]) ? $cat_arr[0] : '';
                        $cat_name   = isset($cat_arr[1]) ? $cat_arr[1] : '';
                        $arr_categories_list[] =  ' ( contains(categories, "'.$cat_id.'") and contains(tags, "'.$cat_name.'") ) ';
                    }
                    $arr_condition[] = '('. implode(' or ', $arr_categories_list) . ')';
                } else {
                    $cat_arr = explode('[+]', $arr_filter['category']);
                    if (isset($cat_arr[0])) {
                        $arr_condition[] = '(contains(categories, "'.$cat_arr[0].'")) ';
                    }

                    if (isset($cat_arr[1])) {
                        $arr_condition[] = '(contains(tags, "'.$cat_arr[1].'")) ';
                    }
                }
            }

            $xpath_query = 'event';
            if (!empty($arr_condition)) {
                $xpath_query .= '[' . implode(" and ", $arr_condition) . ']';
            }

            return $xpath_query;
        }

        /**
         * Render HTML
         * @param $events
         * @param $arr_filter
         * @param array $selected_category
         * @return string
         */
        public static function get_html_list_events($events, $arr_filter, $selected_category = array()) {

            $ac_options = get_option( self::$option_key );
            if (! $ac_options['feed_url']) {
                $total_event = 0;
                $events = array();
                return '';
            }

            $page_size = !empty($ac_options['number_item_perpage']) ? $ac_options['number_item_perpage'] : FRONT_END_PAGE_SIZE;
            $total_event = count($events);
            Artsopolis_Calendar_Shortcode::$_total_event = $total_event;
            $events = array_splice($events, ($arr_filter['page'] - 1) * $page_size, $page_size);

            ob_start();

            // Default view from admin config
            if (empty($_GET['view'])) {
                $view = isset($ac_options['settings_default_view']) ? $ac_options['settings_default_view'] : '';
            }
            else {
                $view = isset($arr_filter['view']) ? $arr_filter['view'] : '';
            }

            switch ($view) {
                case 'tile' :
                    include dirname(__FILE__) . '/view/list/includes/tile.php';
                    break;
                case 'month' :
                    if (empty($arr_filter['from_date'])) {
                        $arr_filter['from_date'] = self::firstDayOfCurrentMonth();
                    }

                    if (empty($arr_filter['to_date'])) {
                        $arr_filter['to_date'] = self::lastDayOfCurrentMonth();
                    }
                    include dirname(__FILE__) . '/view/list/includes/month.php';
                    break;
                default :
                    include dirname(__FILE__) . '/list-events-template.php';
                    break;
            }
            $html = ob_get_contents();
            ob_end_clean();

            return $html;
        }

        /* Define callback ajax function */
        public static function ac_get_feed() {

            // The $_REQUEST contains all the data sent via ajax
            if (isset($_REQUEST['page'])) {
                $page         = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
                $from_date    = isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
                $to_date      = isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';
                $location     = isset( $_REQUEST['location'] ) ? stripslashes($_REQUEST['location']) : '';
                $repagination = isset( $_REQUEST['repagination'] ) ? $_REQUEST['repagination'] : '';
                $keyword      = isset( $_REQUEST['keyword'] ) ? stripslashes($_REQUEST['keyword']) : '';
                $this_weekend = isset( $_REQUEST['this_weekend'] ) ? $_REQUEST['this_weekend'] : '';
                $category     = isset( $_REQUEST['category'] ) ? $_REQUEST['category'] : '';
                $venue        = isset( $_REQUEST['venue'] ) ? $_REQUEST['venue'] : '';
                $first_tab    = isset( $_REQUEST['first_tab'] ) && $_REQUEST['first_tab'] == 'true';
                $fid          = isset( $_REQUEST['fid'] ) && $_REQUEST['fid'] ? $_REQUEST['fid'] : '';
                $outputType   = isset( $_REQUEST['output_type'] ) && $_REQUEST['output_type'] ? $_REQUEST['output_type'] : '';

                self::init_data($fid);

                $arr_filter = array(
                    'page'         => $page,
                    'from_date'    => $from_date,
                    'to_date'      => $to_date,
                    'location'     => $location,
                    'repagination' => $repagination,
                    'keyword'      => $keyword,
                    'this_weedken' => $this_weekend ,
                    'category'     => $category,
                    'venue'        => $venue,
                    'first_tab'    => $first_tab,
                    'view'         => isset( $_REQUEST['view'] ) && $_REQUEST['view'] ? $_REQUEST['view'] : '',
                );

                $ac_options = get_option( self::$option_key );

                if ( isset($ac_options['featured_only']) && $ac_options['featured_only'] ) {
                    $featuredEvents = get_option( AC_FEATURED_EVENTS. self::get_geed_id( $fid ) );
                    $arr_filter['featured_events'] = $featuredEvents;
                }

                // Get the category array for filter
                $category_data = self::_process_category_opt( isset( $ac_options['category'] ) ? $ac_options['category'] : '' );

                switch ($outputType) {
                    case 'json':
                        $output = self::month_view_formatting($arr_filter);
                        break;
                    case 'html':
                    default:
                        $arr_events = self::get_list_events_data($arr_filter);
                        $output = self::get_html_list_events($arr_events, $arr_filter,  isset( $category_data['keys'] ) ? $category_data['keys'] : '' );
                        break;

                }
                echo json_encode( array(
                    'html' => $output,
                    'total' => Artsopolis_Calendar_Shortcode::$_total_event,
                    'page_size' => !empty($ac_options['number_item_perpage']) ? $ac_options['number_item_perpage'] : FRONT_END_PAGE_SIZE
            ) );
            }
            // Always exit function when you call by the ajax
            exit();
        }

        /**
         * Month view formatting
         * @author vulh
         * @param $arr_filter
         * @return array
         * @internal param $events
         */
        public static function month_view_formatting($arr_filter)
        {
            $output = array();

            if (empty($arr_filter['from_date'])) {
                $arr_filter['from_date'] = self::firstDayOfCurrentMonth();
            }

            if (empty($arr_filter['to_date'])) {
                $arr_filter['to_date'] = self::lastDayOfCurrentMonth();
            }

            $startNumberOfTheFirstCell = date('w', strtotime($arr_filter['from_date']));
            $endNumberOfTheFirstCell = 6 - date('w', strtotime($arr_filter['to_date']));

            $start = date('Y-m-d', strtotime($arr_filter['from_date'] . "-$startNumberOfTheFirstCell days"));
            $end = date('Y-m-d', strtotime($arr_filter['to_date'] . "+$endNumberOfTheFirstCell days"));
            $days = ((strtotime($end) - strtotime($start)) / (24*3600)) + 1;

            $ac_options = get_option( self::$option_key, false );


            $date = $start;
            for ($i = 0; $i < $days; $i++) {

                $arr_filter['from_date'] = $arr_filter['to_date'] = $date;
                $events = self::get_list_events_data($arr_filter);

                if ($events) {
                    foreach($events as $event) {
                        $formatted = self::get_cdata_xml($event);

                        if ( !empty($formatted) && acIsExpiredTime($formatted->eventDateEnd,$formatted->event_dates_times) ) continue;

                        $formatted->viewLink = self::get_link($ac_options, $formatted);
                        $formatted->viewTargetLink = self::get_target($ac_options);
                        $output[$date][] = $formatted;
                    }
                }
                $date = date('Y-m-d', strtotime($date . "+1 days"));
            }

            if ($output) {
                foreach($output as $date => &$items) {
                    usort($items, 'ac_sort_ongoing');
                }
            }

            return $output;
        }


        /**
         * Get link of an event
         * @param $ac_options
         * @param $event
         * @return string
         */
        public static function get_link($ac_options, $event)
        {
            if (! $ac_options['details_link_to']) {
                $link = $event->link;
            } else {
                // Custom url follow the permalink structure
                $url_ext_sign = ! get_option('permalink_structure') ? '&' : '?';
                $parent_link = get_site_url().'/'. $ac_options['calendar_slug'];
                $title_slug = self::slugify($event->eventName);
                $link = $parent_link. $url_ext_sign . $title_slug . '&event_id=' . $event->eventID. ( self::$fid ? '&fid='. self::$fid : '' );
            }
            return $link;
        }

        /**
         * Get target link of an event
         * @param $ac_options
         * @return string
        */
        public static function get_target($ac_options)
        {
            if (! $ac_options['details_link_to']) {
                return $ac_options['open_new_tab'] ? 'target="_blank"' : '';
            }

            return '';
        }

        /**
         * Get first date of current month
         * @return string
        */
        public static function firstDayOfCurrentMonth() {
            $query_date = date('Y-m-d');
            $date = new DateTime($query_date);
            $date->modify('first day of this month');
            return $date->format('Y-m-d');
        }

        /**
         * Get last date of current month
         * @return string
         */
        public static function lastDayOfCurrentMonth() {
            $query_date = date('Y-m-d');
            $date = new DateTime($query_date);
            $date->modify('last day of this month');
            return $date->format('Y-m-d');
        }

        public static function get_event_url($url) {
            if ((   ! strpos($url, '.gif')
                    && ! strpos($url, '.png')
                    && ! strpos($url, '.jpg')
                    && ! strpos($url, '.jpeg')
                )

                || strpos($url, 'missing_org') || ! $url)  {
                return plugins_url('/artsopolis-calendar/img/calendar-icon.png');
            }

            // Remove this logic for the new WP version
//            if (strpos($url, '_medium')) {
//                $event_img = str_replace('_medium', '_category', $url);
//            } else {
//                $ext = substr($url, -4);
//                $ext = (strpos($ext, '.') === null ? '.':'').$ext;
//                $event_img = str_replace($ext, '', $url). '_category'. $ext;
//            }

            return $url;
        }

        public static function slugify($text)
        {
            // Strip html tags
            $text=strip_tags($text);
            // Replace non letter or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);
            // Transliterate
            setlocale(LC_ALL, 'en_US.utf8');
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            // Remove unwanted characters
            $text = preg_replace('~[^-\w]+~', '', $text);
            // Trim
            $text = trim($text, '-');
            // Remove duplicate -
            $text = preg_replace('~-+~', '-', $text);
            // Lowercase
            $text = strtolower($text);
            // Check if it is empty
            if (empty($text)) { return 'n-a'; }
            // Return result
            return $text;
        }

        public static function get_featured_events( $fid = '' ) {

            $selected_events = get_option( AC_FEATURED_EVENTS. self::get_geed_id( $fid ) );

            if ( empty($selected_events) ) {
                return array();
            }

            $xml = @simplexml_load_file( self::get_xml_fullpath($fid) );

            if ( ! $xml ) {
                return array();
            }

            $query = array();
            foreach ($selected_events as $event_id) {
                $query[] = 'contains(eventID, "'.$event_id.'")';
            }
            $query = implode(' or ', $query) ;

            $events = $xml->xpath('event['.$query.']');

            return $events;

        }

        public static function get_spotlight_events( $fid = '' ) {

            $spotlight_events = get_option( AC_SPOTLIGHT_EVENTS. self::get_geed_id( $fid ) );

            if ( empty($spotlight_events) ) {
                return array();
            }

            $xml = @simplexml_load_file( self::get_xml_fullpath($fid) );

            if ( ! $xml ) {
                return array();
            }

            $query = array();
            foreach ($spotlight_events as $event_id) {
                $query[] = 'contains(eventID, "'.$event_id.'")';
            }
            $query = implode(' or ', $query) ;

            $events = $xml->xpath('event['.$query.']');

            return $events;

        }

    }
}

Artsopolis_Calendar_Shortcode::init();