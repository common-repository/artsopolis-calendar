<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '_admin_menu.php';

$feeds = ($feedSettingData = get_option( AC_PLUGIN_OPTION_ARR_KEYS )) ? @unserialize($feedSettingData) : false;
$new_id = $feeds ? intval(max( $feeds )) + 1 : '';
?>
<form method="post" action="<?php admin_url() ?>admin.php?page=artsopolis-calendar-update-config&fid=<?php echo $new_id ?>" >
<div id="ac-admin-all-feeds" class="wrap clear"><style>input[type='text'] { width:200px; padding:4px; } </style>
    <input type="hidden" id="artsopolis-calendar-selected-events" name="artsopolis-calendar-selected-events" value="" />
    <fieldset>
	<legend><h2>Feeds Listing</h2></legend>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="Add New Feed">
	<hr/>
    
        <p id="processing-artsopolis-calendar">Processing ...</p>
        <table style="visibility: hidden" cellpadding="0" cellspacing="0" border="0" class="display" id="artsopolis-calendar-featured-events" width="100%">
            <thead>
                <tr>
                    <th style="display: none"></th>
                    <th>Title</th>
                    
                    <th style="width: 235px;">Shortcode</th>
                    <th style="width: 140px;"></th>
                    <th style="width: 40px;"></th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
                
            <?php

            if( $feeds ) {
                foreach ( $feeds as $f ) {
                $option_key = self::get_option_key( $f );
                $option = get_option( $option_key );
                if ( ! $option ) continue;
            ?>
                    <tr class="odd gradeX">
                        <td style="display: none">
                            <input name="event_ids[]" value="" type="checkbox" />
                            <input name="all_event_ids[]" value="" type="hidden" />
                        </td>
                        <td><a href="<?php echo admin_url() ?>admin.php?page=artsopolis-calendar-update-config&fid=<?php echo $f ?>">
                            <?php echo $option['title'] ? $option['title'] : 'No title' ?></a></td>
                        <td class="ac-auto-copy-shortcode"><?php echo self::get_shortcode( $f ) ?></td>
                        <td style="text-align:center"><a href="<?php echo admin_url() ?>admin.php?page=artsopolis-calendar-featured-events&fid=<?php echo $f ?>">Featured Events</a></td>
                        <td><a href="<?php echo admin_url() ?>admin.php?page=artsopolis-calendar-update-config&fid=<?php echo $f ?>">Edit</a></td>
                        <td><a onclick="return confirm('Are you sure to remove this feed ?');" href="<?php echo admin_url() ?>plugins.php?page=admin-artsopolis-calendar&fid=<?php echo $f ?>&action=remove">Remove</a></td>
                    </tr>

            <?php
                }
            } ?>

            <tbody></tbody>        

        </table>
      
        
	</fieldset>
</div>

<input type="hidden" id="ac-feeds" value="1" />
<form>