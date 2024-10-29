<?php
    if (!isset($_GET['view'])) {
        $default_view = isset($ac_options['settings_default_view']) ? $ac_options['settings_default_view'] : 'default';
    } else {
        $default_view = $_GET['view'];
    }
    $isNotDetailPage = !isset($_GET['event_id']) || $_GET['event_id'] == '';
    $slugURL = isset($ac_options['calendar_slug']) ? $ac_options['calendar_slug'] : '';
?>

<div class="ac-search-bkl ac-type-bkl" style="<?php echo $isNotDetailPage ? '' : 'display: none' ?>">
    <nav class="ac-type">
        <ul>
            <li class="" ><span><?php _e('Display', 'apollo'); ?></span></li>
            <li class="<?php echo ($default_view == 'month') ? 'current' : ''; ?>" ><a href="<?php echo (get_home_url() . '/'.$slugURL.'/?view=month'); ?>"><i class="fa fa-calendar fa-2x"></i></a></li>
            <li class="<?php echo ($default_view == 'tile') ? 'current' : ''; ?>" ><a href="<?php echo (get_home_url() . '/'.$slugURL.'/?view=tile'); ?>"><i class="fa fa-th fa-2x"></i></a></li>
            <li class="<?php echo ($default_view == 'default'  || $default_view == 'list') ? 'current' : ''; ?>"><a href="<?php echo (get_home_url() . '/'.$slugURL.'/?view=list'); ?>"><i class="fa fa-bars fa-2x"></i></a></li>
        </ul>
    </nav>
</div>
