<?php
    if ($isNotMonthView) {
        require(dirname(__FILE__) . '/list/normal.php');
    }
    else {
        require(dirname(__FILE__) . '/list/month.php');
    }
?>

<input type="hidden" name="ac-current-view" id="ac-current-view" value="<?php echo $isNotMonthView ? 'normal' : 'month' ?>" />
