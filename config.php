<?php
$_upload_dir = WP_CONTENT_DIR. '/uploads';

// Default value for Teaser Widget
define('TEASER_WIDGET_DEFAULT_TITLE', 'Featured Events');
define('TEASER_WIDGET_DEFAULT_NUM_DISPLAY', 5);
define('TEASER_WIDGET_DEFAULT_BG_COLOR', '#ffffff');
define('TEASER_WIDGET_DEFAULT_TITLE_COLOR', '#000000');
define('TEASER_WIDGET_DEFAULT_EVENT_TITLE_SIZE', 12);
define('TEASER_WIDGET_DEFAULT_EVENT_DATE_SIZE', 11);
define('TEASER_WIDGET_DEFAULT_LOGO_POSITION', 'b_right');
define('TEASER_WIDGET_DEFAULT_ROUNDED_CORNER_RADIUS', 5);

// Config for the xml process data
define('CALENDAR_UPLOAD_DIR', $_upload_dir . '/artsopolis-calendar' );

define( 'XML_BASE_NAME', 'artsopolis-calendar' );
define('XML_FILE_PATH', CALENDAR_UPLOAD_DIR. '/'. ac_get_current_domain() );
define('OVERRIDE_TIME_XML_FILE', 1);  // The time after creating the file for auto overriding xml feed file
define('TIMEOUT_REQUEST_GET_XML_CONTENT', 1000);

define('FRONT_END_PAGE_SIZE', 21);
define('DELIMITER_DATE', '-');

define('AC_PLUGIN_DEFAULT_LOGO_POSITION', 't_right');

define( 'AC_PLUGIN_OPTION_ARR_KEYS', 'ac_plugin_option_arr_keys' );
define( 'ARTSOPOLIS_CALENDAR_OPTIONS', 'artsopolis_calendar_options' );
define( 'AC_SHORTCODE_KEY', 'artsopolis-calendar-plugin' );
define( 'AC_FEATURED_EVENTS', 'artsopolis_calendar_featured_events' );
define( 'AC_SPOTLIGHT_EVENTS', 'artsopolis_calendarspotlight_events' );
define( 'AC_VERSION', 'artsopolis_calendar_version' );

define( 'AC_LIMIT_DESC_LISTING', 250 );
define( 'AC_LIMIT_DESC_DETAIL', 500 );

define( 'AC_ADMIN_FEATURED_EVENT_PERPAGE', 100 );

define( 'AC_CSS_VERSION', '2.4' );
define( 'AC_JS_VERSION', '2.4' );