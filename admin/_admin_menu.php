<ul class="artsopolis-calendar-tabs-menu">
    <li <?php echo $_REQUEST['page'] == 'artsopolis-calendar-update-option' || (
        $_REQUEST['page'] == 'admin-artsopolis-calendar' || $_REQUEST['page'] == 'artsopolis-calendar-update-config'
        ) ? 'class="active"':'' ?> ><a href="plugins.php?page=admin-artsopolis-calendar">Artsopolis Calendar</a></li>
    <li <?php echo $_REQUEST['page'] == 'artsopolis-calendar-featured-events' ? 'class="active"':'' ?> ><a href="admin.php?page=artsopolis-calendar-featured-events">Featured & Spotlight Events</a></li>
</ul>