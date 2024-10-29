<?php

if (! class_exists("Artsopolist_Calendar_Admin")) {

    class Artsopolist_Calendar_Admin extends Artsopolis_Calendar_API {

        public static $options = array();

        public static function init() {

            add_action('admin_menu', array(__CLASS__, 'ac_admin_menu_plugin'));

            if (defined( 'DOING_AJAX' )) {
                add_action('wp_ajax_ac_check_valid_feed_url', array(__CLASS__, 'ac_check_valid_feed_url'));
                add_action('wp_ajax_ac_check_valid_category_xml_url', array(__CLASS__, 'ac_check_valid_category_xml_url'));
                add_action('wp_ajax_ac_check_valid_all_category_xml_url', array(__CLASS__, 'ac_check_valid_all_category_xml_url'));
                add_action('wp_ajax_ac_get_territories', array(__CLASS__, 'ac_get_territories'));
                add_action('wp_ajax_ac_delete_image_by_url', array(__CLASS__, 'ac_delete_image_by_url'));
            }

            if (!self::isCalendarPage()) return false;

            add_action('admin_init', array(__CLASS__, 'ac_register_options'));
            add_action('admin_init', array(__CLASS__, 'register_scripts'));
            add_action('admin_footer', array(__CLASS__, 'print_scripts'));
            add_action('admin_print_scripts', array(__CLASS__, 'do_jslibs'));

            add_action('admin_head', array(__CLASS__, 'add_admin_files'));
            add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_style' ));

            //add_action( 'update_option_'.self::get_option_key().'', array( __CLASS__, 'update_feeds' ) );

        }

        static function isCalendarPage() {
            return (
                isset($_GET['page']) && ($_GET['page'] == 'artsopolis-calendar-update-config'
                    || $_GET['page'] == 'artsopolis-calendar-featured-events'
                    || $_GET['page'] == 'admin-artsopolis-calendar')
            )
            ||
            ( isset($_POST['option_page']) && $_POST['option_page'] == 'artsopolis-calendar-group' );
        }

        public static function update_feeds() {
            $feeds = @unserialize( get_option( AC_PLUGIN_OPTION_ARR_KEYS ) );
            if ( ! $feeds ) {
                $feeds = array();
            }

            $feeds[] = intval( str_replace( '_', '', self::get_geed_id()) );

            if ($feeds) {
                foreach($feeds as $i => $f) {
                    if ($f === '') {
                        $feeds[$i] = 0;
                        break;
                    }
                }
            }

            update_option( AC_PLUGIN_OPTION_ARR_KEYS , serialize( array_unique( $feeds ) ) );
        }

        public static function enqueue_admin_style() {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style('artsopolis-calendar.css', plugins_url('artsopolis-calendar/css/artsopolis-calendar.css'));
            wp_enqueue_style('ac-jquery-fancybox.css', plugins_url('artsopolis-calendar/css/ac-jquery-fancybox.css'));

            /**
             * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
             */
            wp_register_script( 'ac-codemirror-main-js' , plugins_url('artsopolis-calendar/js/codemirror/codemirror.js'), array( 'jquery' ));
            wp_register_script('ac-codemirror-css-js', plugins_url( 'artsopolis-calendar/js/codemirror/css.js'), array('jquery'));
            wp_enqueue_script('ac-codemirror-main-js');
            wp_enqueue_script('ac-codemirror-css-js');
            wp_enqueue_style( 'ac-codemirror-main-css', plugins_url( 'artsopolis-calendar/css/codemirror/codemirror.css'), array());
        }

        public static function ac_admin_menu_plugin() {

//            add_menu_page('Artsopolis Calendar', 'Artsopolis Calendar', 'administrator', 'admin-artsopolis-calendar', array(__CLASS__,  'all_feeds'));

            // Add a submenu of admin menu:
            add_submenu_page('admin-artsopolis-calendar', '', 'Featured Events', 'administrator', 'artsopolis-calendar-featured-events', array(__CLASS__, 'featured_events'));
            //add_submenu_page('admin-artsopolis-calendar', 'Artsopolis Calendar Options', 'Configuration', 'administrator', 'artsopolis-calendar-config', array(__CLASS__,  'all_feeds'));

            add_submenu_page('admin-artsopolis-calendar', 'Artsopolis Calendar Options', 'Configuration', 'administrator', 'artsopolis-calendar-update-config', array(__CLASS__,  'ac_admin_plugin_options'));

            add_plugins_page('Artsopolis Calendar Options', 'Artsopolis Calendar', 'manage_options', 'admin-artsopolis-calendar', array(__CLASS__,  'all_feeds'));
        }

        public static function ac_admin_plugin_options() {

            if (! current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions access this page.'));
            }

            /*Define ajax url admin*/
            $admin_url = admin_url( 'admin-ajax.php' );

            $option_key = self::get_option_key();
            $_category = self::get_value_field( 'category' );
            $category_opts = $_category ? $_category : array();

            $arr_filters = Artsopolis_Calendar_Shortcode::$arr_filters;
            $arr_views = Artsopolis_Calendar_Shortcode::$arr_views;
            $arr_filter_settings = array(
                'Date',
                'Location',
                'Venue'
            );
            if (self::get_value_field( 'display-all-associated-categories' )==1) {
                self::$categories_url = self::get_value_field( 'all_category_xml_feed_url' );
            } else self::$categories_url = self::get_value_field( 'category_xml_feed_url' );


            $categories = self::get_categories();

            // Update the xml file
            self::init_data( $_GET['fid'] );

            // Get options from the settings
            $ac_options = get_option( self::get_option_key($_GET['fid']), false );
            if ( $ac_options ) {
                extract($ac_options, EXTR_PREFIX_ALL, 'ac');
                self::$feed_url = $ac_options['feed_url'];
                Artsopolis_Calendar_API::save_xml_data();
            }

            include dirname(__FILE__) . '/_admin_template.php';
        }

        /**
         * Register settings options
         */
        public static function ac_register_options() {
            register_setting( 'artsopolis-calendar-group', self::get_option_key(), array(__CLASS__, 'ac_validate_options'));
        }

        public static function get_shortcode( $fid ) {
            return $fid ? '['.AC_SHORTCODE_KEY." fid=$fid]" : '[' .AC_SHORTCODE_KEY. ']';
        }

        public static function get_value_field( $key, $default = false ) {

            if ( ! self::$options ) {
                self::$options = get_option( self::get_option_key() );
            }

            if (!isset(self::$options[$key]) && $default) return $default;

            return isset( self::$options[$key] ) ? self::$options[$key] : '';
        }

        public static function the_value_field( $key ) {
            echo self::get_value_field( $key );
        }

        /**
         * Validate form
         */
        public static function ac_validate_options($input) {

            self::update_feeds();

            /**
             * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
             */
            if (isset($input['override_css'])) {
                self::saveOverrideCss($input['override_css']);
                unset($input['override_css']);
            }

            return $input;
        }

        /**
         * @param $overrideCSs
         */
        public static function saveOverrideCss($overrideCSs){
            /**
             * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
             */
            $id =  isset( $_REQUEST['fid'] ) && $_REQUEST['fid'] ? $_REQUEST['fid'] : 0;

            $overrideCssFolder = XML_FILE_PATH . '/override-css';
            if ( !is_dir($overrideCssFolder ) ) {
                mkdir( $overrideCssFolder );
            }

            if ( !is_writable( $overrideCssFolder ) ) {
                @chmod( $overrideCssFolder, 0777 );
            }

            $overrideCssPath = getPathOverrideCss($id);

            if (file_exists($overrideCssPath) && ! is_writable($overrideCssPath)) {
                @chmod($overrideCssPath, 0777);
            }

            file_put_contents($overrideCssPath, $overrideCSs);
        }

        /**
         * Function to register the javascript and style
         */
        public static function register_scripts () {
            wp_register_script('ac-jquery-fancybox.js', plugins_url('artsopolis-calendar/js/ac-jquery-fancybox.js'));
            wp_register_script('artsopolis-calendar-admin.js', plugins_url('artsopolis-calendar/js/artsopolis-calendar-admin.js'));

        }

        /**
         * Function to print the javascript and style registered
         */
        public static function print_scripts() {
            wp_print_scripts('ac-jquery-fancybox.js');
            wp_print_scripts('artsopolis-calendar-admin.js');
        }

        public static function do_jslibs() {
            wp_enqueue_script('editor');
            wp_enqueue_script('thickbox');
            wp_enqueue_script('wp-color-picker');
        }

        public static function add_admin_files () {

            wp_enqueue_style('jquery-table-style', plugins_url('artsopolis-calendar/css/jquery.dataTables.css'));

            wp_register_script( 'jquery-data-table-js' , plugins_url('artsopolis-calendar/js/jquery.dataTables.js'), array( 'jquery' ));
            wp_enqueue_script('jquery-data-table-js');

        }

        public static function ac_check_valid_feed_url() {
            if (! isset($_REQUEST['feed_url']) ) {
                echo 0;
                exit;
            }

            self::$feed_url = $_REQUEST['feed_url'];

            $xml = @simplexml_load_string(self::get_request_content(self::$feed_url));
            if($xml) {
                $events = @$xml->xpath('event');
                echo count($events) > 0 ? 1 : 0;
                exit;
            }

            echo 0;
            exit;
        }

        public static function ac_check_valid_category_xml_url() {

            $fid = isset( $_REQUEST['fid'] ) ? $_REQUEST['fid'] : '';
            $option_key = self::get_option_key( $fid );

            if (! isset($_REQUEST['category_xml_feed_url']) && ! isset($_REQUEST[''.$option_key.'[category_xml_feed_url]'])) {
                echo 0;
                exit;
            }

            if (defined( 'DOING_AJAX' ) && DOING_AJAX) {
                self::$categories_url = $_REQUEST['category_xml_feed_url'];
            } else {
                self::$categories_url = $_REQUEST[''.$option_key.'[category_xml_feed_url]'];
            }

            $categories = self::get_categories();

            if (empty($categories)) {
                exit('');
            }

            $category_opts = self::get_value_field( 'category' );

            // Render html and return
            ob_start();
            include dirname(__FILE__). '/_category-template.php';
            $html = ob_get_contents();
            ob_end_clean();

            echo $html;
            exit;
        }

        public static function ac_check_valid_all_category_xml_url() {

            $fid = isset( $_REQUEST['fid'] ) ? $_REQUEST['fid'] : '';
            $option_key = self::get_option_key( $fid );

            if (! isset($_REQUEST['all_category_xml_feed_url']) && ! isset($_REQUEST[''.$option_key.'[all_category_xml_feed_url]'])) {
                echo 0;
                exit;
            }

            if (defined( 'DOING_AJAX' ) && DOING_AJAX) {
                self::$categories_url = $_REQUEST['all_category_xml_feed_url'];
            } else {
                self::$categories_url = $_REQUEST[''.$option_key.'[all_category_xml_feed_url]'];
            }

            $categories = self::get_categories();

            if (empty($categories)) {
                exit('');
            }

            $category_opts = self::get_value_field( 'category' );

            // Render html and return
            ob_start();
            include dirname(__FILE__). '/_category-template.php';
            $html = ob_get_contents();
            ob_end_clean();

            echo $html;
            exit;
        }

        public static function ac_get_territories() {
            exit;
        }

        public static function featured_events() {

            $ac_options = get_option( self::get_option_key() );
            $fid = isset( $_REQUEST['fid'] ) ? $_REQUEST['fid'] : '';

            $selected_events_key = self::get_feature_events_key( $fid );
            $selected_events = get_option( $selected_events_key );

            $spotlight_events_key = self::get_spotlight_events_key( $fid );
            $spotlight_events = get_option( $spotlight_events_key );

            if ( isset( $_REQUEST['submit'] ) ) {
                if ( ! $selected_events ) $selected_events = array();

                $selected_post = isset( $_REQUEST['event_ids'] ) && $_REQUEST['event_ids'] ? $_REQUEST['event_ids'] : array();
                $all_page_event_ids = $_REQUEST['all_event_ids'];

                // Get events not selected in current page
                $event_not_selected = array_unique(array_diff($all_page_event_ids, $selected_post));

                // Remove the event in the selected events that contained in the event_not_selected
                $_selected_events = array();
                foreach ($selected_events as $event_id) {
                    if (! in_array($event_id, $event_not_selected)) {

                        $_selected_events[] = $event_id;
                    }
                }

                $selected_events = array_unique( array_merge( $selected_post, $_selected_events ) );
                update_option($selected_events_key, $selected_events);


                // Save spotlight
                if ( isset( $_REQUEST['submit'] ) ) {
                    if (!$spotlight_events) $spotlight_events = array();

                    $spotlight_post = isset($_REQUEST['spotlight_event_ids']) && $_REQUEST['spotlight_event_ids'] ? $_REQUEST['spotlight_event_ids'] : array();
                    $all_page_event_ids = $_REQUEST['all_event_ids'];

                    // Get events not selected in current page
                    $event_not_spotlight = array_unique(array_diff($all_page_event_ids, $spotlight_post));

                    // Remove the event in the selected events that contained in the event_not_selected
                    $_spotlight_events = array();
                    foreach ($spotlight_events as $event_id) {
                        if (!in_array($event_id, $event_not_spotlight)) {

                            $_spotlight_events[] = $event_id;
                        }
                    }

                    $spotlight_events = array_unique(array_merge($spotlight_post, $_spotlight_events));
                    update_option($spotlight_events_key, $spotlight_events);
                }
            }

            self::init_data($fid);

            $events = Artsopolis_Calendar_Shortcode::get_list_events_data(array());

            ob_start();
            include dirname(__FILE__). '/_featured_events.php';
            $html = ob_get_contents();
            ob_clean();
            echo $html;
        }

        public static function all_feeds() {

            $feeds = @unserialize( get_option( AC_PLUGIN_OPTION_ARR_KEYS ) );

            if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove' && $feeds ) {

                if ( $feeds && count( $feeds ) == 1 ) {
                    delete_option( AC_PLUGIN_OPTION_ARR_KEYS );
                } else {
                    $feeds = array_diff( $feeds , array( str_replace( '_' , '', self::get_geed_id()) ) );
                    update_option( AC_PLUGIN_OPTION_ARR_KEYS , @serialize( $feeds ));
                }

                delete_option( self::get_option_key() );
                delete_option( self::get_feature_events_key(self::get_geed_id()) );


            }

            ob_start();
            include dirname(__FILE__). '/_admin-feeds.php';
            $html = ob_get_contents();
            ob_clean();
            echo $html;
        }

        public static function ac_delete_image_by_url() {
            global $wpdb;
            $ac_options = get_option('artsopolis_calendar_options');

            $image_url = $_REQUEST['image_url'];
            $opt_name = $_REQUEST['opt_name'];

            if (! $image_url) {
                exit(0);
            }

            if ( $opt_name && isset($ac_options[$opt_name]) ) {

                $ac_options[$opt_name] = '';
                update_option('artsopolis_calendar_options', $ac_options);
            }

            exit(1);
        }

    }

    Artsopolist_Calendar_Admin::init();
}

