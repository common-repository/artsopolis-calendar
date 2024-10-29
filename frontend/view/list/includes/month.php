<div class="events" id="ac-add-event-calendar" ng-app="dashboard">
    <div class="calendar-container" ng-controller="calendar">
        <calendar data-dws="4" selected="day" data-type="1" data-eid = "124" data-sd="<?php echo $arr_filter['from_date'] ?>" data-ed = "<?php echo $arr_filter['to_date'] ?>"></calendar>
    </div> <!-- end calendar -->
    <div class="viewmore-box">
        <img src="<?php echo plugins_url(). "/artsopolis-calendar" ?>/img/viewmore-bg.png" class="arrow-box">
        <div class="closeviewmore"><img src="<?php echo plugins_url()."/artsopolis-calendar" ?>/img/close-ico.png"></div>
    </div>
</div>

<input type="hidden" id="list-msg-calendar"
       data-today ="<?php _e('TODAY','apollo') ?>"

       data-mon ="<?php _e('MONDAY','apollo') ?>"
       data-tue ="<?php _e('TUESDAY','apollo') ?>"
       data-wed ="<?php _e('WEDNESDAY','apollo') ?>"
       data-thu ="<?php _e('THURSDAY','apollo') ?>"
       data-fri ="<?php _e('FRIDAY','apollo') ?>"
       data-sat ="<?php _e('SATURDAY','apollo') ?>"
       data-sun ="<?php _e('SUNDAY','apollo') ?>"

       data-short-mon ="<?php _e('MON','apollo') ?>"
       data-short-tue ="<?php _e('TUE','apollo') ?>"
       data-short-wed ="<?php _e('WED','apollo') ?>"
       data-short-thu ="<?php _e('THU','apollo') ?>"
       data-short-fri ="<?php _e('FRI','apollo') ?>"
       data-short-sat ="<?php _e('SAT','apollo') ?>"
       data-short-sun ="<?php _e('SUN','apollo') ?>"
       data-am  ="<?php _e('AM','apollo') ?>"
       data-pm  ="<?php _e('PM','apollo') ?>"
       data-more  ="<?php _e('View All ','apollo') ?>"
       data-label-events  ="<?php _e('Events &#187;','apollo') ?>"
/>