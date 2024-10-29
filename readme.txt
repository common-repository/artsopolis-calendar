=== Artsopolis Calendar ===
Contributors: nhanlt, vulh, jef
Tags: Artsopolis, calendar, events calendar
Stable tag: 3.4.5
Requires at least: 4.7
Tested up to: 6.0


Artsopolis Calendar provides a searchable calendar of events for Artsopolis Network member content syndication partners.

== Description ==

Artsopolis Calendar provides a searchable calendar of events for Artsopolis Network member content syndication partners.

This plugin requires a valid API feed URL as issued by an approved Artsopolis Network member. The plugin will NOT display calendar content without a validated feed.

== Installation ==

1. Install the Artsopolis plugin either via the WordPress.org plugin directory, or by uploading the files to your server. Use the "Plugins" tab located in the left-side navbar of the Dashboard. Select "Add New" from the "Plugins" tab and enter "Artsopolis" in the Search box. Then click on "Install now" link. After download is complete, click on "Activate Plugin".

2. After installing the plugin contact your Artsopolis Network member to obtain a valid API feed URL.

3. Select "Plugins" > "Artsopolis Calendar" from the left column. On the calendar configuration form enter the event and category feed URLs provided to you by the Artsopolis Network member. Leave the Title and Body Text areas blank on this page.

4. In the "Artsopolis Calendar slug" field enter the page slug where you will be displaying the calendar. For example: "/calendar/" or "/events/"

5. Under "Display Settings" you can select the sort order for the event listings and a search bar background color.

6. In the two logo upload fields you can upload a 'powered by' or other type of sponsor logo along with a link. You can position where the logo displays on the main calendar page, as well as the 'teaser' widget.

7. Click "Save Changes." Once your changes are saved add the following snippet to the calendar page you created: [artsopolis-calendar-plugin fid=#]. Replace "#" with the appropriate ID number from the plugin you created and make sure to include the brackets.

8. This plugin allows you to add more than one calendar to your website. If you would like to add more than one calendar you will need to set up a separate page and add the same code snippet: [artsopolis-calendar-plugin fid=#]. Replace "#" with the appropriate ID number for any subsequent calendars you create. For example, your first calendar may be [artsopolis-calendar-plugin fid=0]; and then your second calendar may be [artsopolis-calendar-plugin fid=1]. Each one can use a different selection of filters (i.e. one for just Music events and another for just Family-Friendly events).

9. Other system requirements: *Write permission of goldstar/xml directory is required. *cURL extension is required. *simplexml extension is required. *json extension is required. *openssl extension is required.

10. You can also add a "teaser" widget to promote 1-15 featured events on the right column of your website. To add the featured event teaser widget, on the main calendar plugin admin configuration form enter the slug of the page you are adding the main Artsopolis plugin to. IMPORTANT: If the slug you enter for the widget does not match the slug of the page you have added the main Artsopolis plugin to the widget will NOT display. For more information about slugs, you may wish to visit this page: http://codex.wordpress.org/Glossary#Slug

9. From the main Artsopolis plugin Settings admin form select the "Featured Events" tab to select the specific events you would like to feature in the widget.  This tab is located at the top of form next to the "Configuration" tab just above the "Add New Post" field.

10. Next, select Appearance > Widget from the left column and then drag the "Artsopolis Calendar Teaser" widget to main sidebar position you prefer. Once positioned click to open the widget's display settings. You should enter a maximum number of events to be featured (between 1 and 15). You may also select various font size and color attributes to customize the widget's appearance.

11. Once you have completed the widget's display settings click "Save". Please note that the widget will only display those events that have been manually selected and will not auto fill events. So, once all selected featured events have expired, if no other events have been selected, the widget will automatically be suppressed from view.



== Screenshots ==

1. Admin configuration

2. Frontend of plugin



== Changelog ==


= 1.0 =
* Provide list of events
* Provide keyword, category, date and location filtering
* Detail event page can access internal plugin or external depend on backend configuration


= 1.1 =
* Fix layout


= 1.2 =
* Filter events by tag
* Optimize performance


= 1.3 =
* Update plugin's css to run compatible with most of themes


= 1.3.2 =
* Allow user to active "teaser widget" that list feature events in sidebar


= 1.3.3 =
* Optimize style "teaser widget"
* Allow upload logo and configure position for plugin and widget
* More configuration for "teaser widget"


= 1.3.4 =
* Fix css for "teaser widget"


= 1.3.5 =
* Allow configure rounded/squared corners title bar of "teaser widget"


= 1.4 =
* Fix bug subcat is empty
* Separate Current & Upcoming and Ongoing events tabs


= 2.0 =
* Allow multi feeds
* Allow multi teaser widgets
* Allow site admin to config which teaser widget will associate with specific feed
* Sort by "Start Date" option will be sorted by first date in bunch of "upcomming date" instead of real "Start Date"
= 2.0.1 =
* Bug fixes
= 2.0.2 =
* Bug fix on line 280 of js/artsopolis-calendar-admin.js file
= 2.0.3 =
* Bug fix for incorrectly parsed html entities* Added .jpeg extension to allowed image types
= 3.0 =
* New spotlight block, featured event selection logic, compatibility adjustments with new Artsopolis WordPress theme
= 3.0.1 =
* Allow to display image which has jpg, jpeg, and png extensions, add eventPhone1 field, add eventEmail field, eventTicketUrl field, date bug fixes
= 3.0.2 =
* Bug fixes
= 3.0.3 =
* Change sort order so that any event that does NOT have individual date and time info in these fields are moved to the bottom (last page) of the main listing
= 3.0.4 =
* Display modifications and bug fixes
= 3.0.5 =
* Use official website url for the Website URL in the detail event page; if that url is not available then link to the event detail page on source calendar site.
 * Remove 'No Information' in the Admission block
 * Rename the button "Reset" to "View All" since it will take the user back to the main list of all events.
 * Remove the Venue Phone number in the Location block
 * Change the Contact email to Ticket email in the Admission block
= 3.0.6 =
* Fix timezone
 * Add configuration for display Feed title in the Front-end


= 3.0.7 =
* Fix for Facebook sharing URL
= 3.0.8 =
* Limit description
 * Options to ON/OFF facebook, Twitter buttons

= 3.0.9 =
* Character encoding fixes
= 3.1.0 =
* Remote apply filter of the_content hook * Fix load plugin resources in all admin pages

= 3.1.1 =
* Modify "Event Website" URL routing logic on event detail page
= 3.1.2 =
* Added option for square event images
= 3.1.3 =
* Extend option for square event images to appear on event detail page
= 3.1.4 =
* Extend option for square event images to appear in spotlight slider* Option to control number of event rows in feature and spotlight selection form
= 3.1.5 =
* Fix incorrect date display
= 3.1.6 =
* Fix javascript error in admin
= 3.1.7 =
* Suppressing the 'Ongoing' tab if there are no ongoing events available
* All of the Buy Tickets button are linking to the plugin main page
* Fix truncate error

= 3.1.8 =
* Fix feed URL validation bug

= 3.1.9 =
* Fix missing line breaks in their description text
* Add new month and tile view types

= 3.1.10 =
* Fix missing page slug


= 3.1.11 =
* Fix conflicting JS code is caused cannot hide the loading icon


= 3.1.12 =
* Modify the date time format to avoid duplicated text

= 3.1.13 =
* Tested up to Wordpress 4.9.4 version

= 3.1.14 =
* Fix tile view column style issue

= 3.1.15 =
* Tested up to Wordpress 5.2.2 version* Added new override style sheet *Added new drop shadow option for tile view on listing pages *Added target_blank option for linking events externally

= 3.1.16 =
* Text label edit

= 3.1.17 =
* Add option to suppress event images

= 3.1.18 =
* Add option to turn off the map section on event detail page
* Add option to add the Google Map API key

= 3.1.19 =
* Filter by Venue
* Turn on/off hover effect
* Modify View All Events button
* Add default category filter
* Add option for default category filter
* Add Search and Reset button to the search bar
* Remove date search fields from month grid view

= 3.2.0 =
* Fix deprecated js and css fancybox files

= 3.2.1 =
* Add option to display all associated event categories in search filter

= 3.2.2 =
* Update missing event image png icon
* Fix square image display in tile view

= 3.2.3 =
* Fix back page redirect

= 3.2.4 =
* Fix javascript conflict
* Suppress redundant error message

= 3.2.5 =
* Suppress redundant meta description

= 3.2.6 =
* shortcode.php file fix

= 3.2.7 =
* shortcode.php file redeploy

= 3.2.8 =
* Add event slug in the URL
* Display square images (listing, tile, detail) if admin selects Square Image option

= 3.2.9 =
* Fix js pagination issue

= 3.3.0 =
* Fix error in featured-event admin page
* Fix duplicate admin menu position to Plugins menu only
* Fix issue of different openssl version https://wordpress.org/support/topic/curl-error-60-ssl-certificate-problem-certificate-has-expired-9/

= 3.3.1 =
* Fixed syntax error in frontend/shortcode.php file

= 3.3.2 =
* Suppressed error messaging

= 3.3.3 =
* Shorcode file update

= 3.4.0 =
* Add Next Active Date option to Default display order
* Order the event listing base on new option

= 3.4.1 =
* Fixed date display

= 3.4.2 =
* Additional date display fixes

= 3.4.3 =
* Fixed syntax error

= 3.4.4 =
* Suppressed warning message

= 3.4.5 =
* Fixed pagination issue