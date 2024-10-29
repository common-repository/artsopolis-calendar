<div id="artsopolis-calendar-tabs-events" class="tabs <?php if ( isset( $_GET['event_id'] ) && $_GET['event_id'] ) { echo ' eli_hidden '; $html_events = ''; } ?>">

    <ul class="tab-links">
        <li class="active"><a data-id="current-upcoming" href="#current-upcoming">Current & Upcoming</a></li>

        <?php if (isset($ongoingData) && $ongoingData): ?>
            <li id="ongoing-tab"><a data-id="ongoing" href="#ongoing">Ongoing</a></li>
        <?php endif; ?>
    </ul>


    <div class="tab-content">
        <div id="current-upcoming" class="tab active">
            <?php $isSquareLayout = ($ac_options['img_style'] == 'square') ? 'the-square-layout' : '';?>
            <div <?php if (! isset($_GET['event_id'])) echo 'class="eli_list '.$isSquareLayout.'"'; ?> id="artsopolis-calendar-list-feed">
                <?php echo $html_events; ?>
            </div>

            <!-- Only required it in the list events -->
            <div class="eli_pagination" id="artsopolis-calendar-pagination"></div>
            <script type="text/javascript">
                var artsopolis_calendar_paging = {
                    total_event: <?php echo isset($total_event) && $total_event ? $total_event : 0; ?>,
                    page_size: <?php echo isset($page_size) && $page_size ? $page_size : 0; ?>
                };
            </script>

        </div>

        <div id="ongoing" class="tab">
            <div id="ongoing-container" <?php if (! isset($_GET['event_id'])) echo 'class="eli_list '.$isSquareLayout.'"'; ?> >
                <?php echo $html_events; ?>
            </div>

            <!-- Only required it in the list events -->
            <div class="eli_pagination" id="artsopolis-calendar-pagination-second-tab"></div>
            <script type="text/javascript">
                var artsopolis_calendar_paging_second_tab = {
                    total_event: 0,
                    page_size: artsopolis_calendar_paging.page_size
                };
            </script>
        </div>

    </div>
</div>
