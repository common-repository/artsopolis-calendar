<div id="artsopolis-calendar-tabs-events" class="tabs">
    <div class="tab-content">
        <div id="current-upcoming" class="tab active">
            <?php $isSquareLayout = ($ac_options['img_style'] == 'square') ? 'the-square-layout' : ''; ?>
            <div class="<?php echo $isSquareLayout; ?>" id="artsopolis-calendar-list-feed">
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
    </div>
</div>