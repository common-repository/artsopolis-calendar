AC_GLOBAL = {
    'loading': {
        'message': '<a><i class="fa fa-spinner fa-spin fa-3x"></i></a>',
        'image_style': '{ "border": "none", "background": "none" }',
    },
    'ajax_url': '/wp-admin/admin-ajax.php'
}


acSearchFn = function (page, repagination, this_weekend, no_reset_category,output_type) {

    this.is_first_tab = jQuery('.eli_content-inner .tabs .tab-links').children().first().hasClass( 'active' );
    this.container_row = this.is_first_tab ? jQuery("#artsopolis-calendar-list-feed") : jQuery(".tabs .tab-content #ongoing #ongoing-container");
    this.this_weekend = this_weekend;
    this.page = page;
    this.repagination = repagination;
    this.no_reset_category = no_reset_category;
    this.output_type = output_type;
    this.view = jQuery('[name=ac_view]').val()
    this.$startDate = jQuery("#artsopolis-calendar-filter-from-date");
    this.$endDate = jQuery("#artsopolis-calendar-filter-to-date");
    this.$triggerMonthViewSearching = jQuery('#ac-month-view-search-btn')

    if (this.view == 'month') {
        this.output_type = 'json';
    }


    this.initSearch = function() {
        // Reset the tags-category val if click on the filter box

        if (this.no_reset_category == undefined) {
            if (jQuery('#tags-category') != undefined) {
                jQuery('#tags-category').val('');
            }
        }

        // Check has this weekend
        if ((this.this_weekend == undefined || ! this.this_weekend) && jQuery('#artsopolis-calendar-choice-weekend').hasClass('eli_active')) {
            this.this_weekend = 1;
        }

        /* Get condition for filter */
        var from_date   = this.$startDate.val(),
            to_date     = this.$endDate.val(),
            location    = jQuery("#filter-by-location").val(),
            keyword     = jQuery("#keyword").val(),
            category    = jQuery('#filter-by-category').val(),
            tags_category  = jQuery('#tags-category').val(),
            category_list  = jQuery('#category-list').val(), // Click on the tags category
            fid = jQuery( '#ac-fid' ).val();

        // It mean you click on the tags category
        if (tags_category) {

            if (jQuery.inArray(tags_category, category_list.split(',')) != -1) {
                category = tags_category+ '[+]'+ jQuery('#tags-category').attr('category-name');
                jQuery('#filter-by-category').val(category);
            } else {
                category = '-1';
                jQuery('#filter-by-category').val('')
            }
        }

        var venueElement = jQuery('#filter-by-venue');
        var venue = '';
        if (venueElement.length > 0) {
            venue = venueElement.val();
        }

        to_date     = to_date   === undefined ? '' : to_date;
        from_date   = from_date === undefined ? '' : from_date;
        location    = location  === undefined ? '' : location;
        keyword     = keyword === undefined ? '' : keyword;
        category    = category === undefined ? '' : category;

        return {
            action: 'ac_get_feed',
            page: page,
            from_date: from_date,
            to_date: to_date,
            location: location,
            repagination: this.repagination,
            keyword: keyword,
            this_weekend: typeof(this_weekend) != 'undefined' && this_weekend == true ? 1:0,
            category: category,
            venue: venue,
            first_tab: this.is_first_tab,
            fid: fid,
            output_type: this.output_type,
            view: this.view
        };
    };


    this.setDateTimeData = function(start, end) {
        this.$startDate.val(start);
        this.$endDate.val(end);
    }

    this.doSearch = function() {
        var $parent = this;
        /* ajax call */
        jQuery.ajax({
            url: artsopolis_calendar_obj.admin_url,
            data: this.initSearch(),
            beforeSend: function() {
                $parent.loading( $parent.container_row );

            },
            dataType: 'html',
            success: function(data) {

                if ( jQuery( '#artsopolis-calendar-detail-event' ) ) {
                    jQuery( '#artsopolis-calendar-tabs-events' ).removeClass('eli_hidden');
                    jQuery( '#artsopolis-calendar-detail-event' ).hide();
                }

                $parent.hideLoading();

                /* This outputs the result of the ajax request */
                data = jQuery.parseJSON( data );
                $parent.container_row.html(data.html);

                if ( ! $parent.is_first_tab ) {
                    artsopolis_calendar_paging_second_tab.total_event = data.total;
                    artsopolis_calendar_paging_second_tab.page_size = data.page_size;
                } else {
                    artsopolis_calendar_paging.total_event = data.total;
                    artsopolis_calendar_paging.page_size = data.page_size;
                }

                if($parent.repagination ==='yes') {
                    $parent.showPaging();
                }

                jQuery('.ac-search-bkl.ac-type-bkl').show();
                jQuery('.eli_h2.calendar-event-main-title').show();

                if (not_scroll) {
                    return false;
                }

                /* scroll to top */
                jQuery("html, body").animate({ scrollTop:  jQuery(".artsopolis-calendar-frontend .eli_content-can-filter").offset().top }, 'slow');

            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        }); /*// End ajax*/
    }

    var $body = jQuery('body');

    this.loading = function ($jmain_element) {
        var $r_left = 0,
            $r_top = 0,
            $r_width = 0,
            $r_height = 0,

            /* caculate position to show loading */
            $screen_width = jQuery(window).width(),
            $screen_height = jQuery(window).height(),

            $c_left =$jmain_element.position().left,
            $c_width = $jmain_element.outerWidth(),

            $c_top = $jmain_element.position().top,
            $c_height = $jmain_element.outerHeight(),

            $screen_top_on_d = document.documentElement.scrollTop || document.body.scrollTop,
            $screen_left_on_d = document.documentElement.scrollLeft || document.body.scrollLeft;

        $r_left = $c_left;
        $r_top = $screen_top_on_d - $jmain_element.offset().top
        $r_width = $c_width;

        $r_height = $c_height;

        if($screen_top_on_d < $c_top && $screen_top_on_d + $screen_width > $c_top && $screen_top_on_d + $screen_width < $c_top + $c_height) {
            $r_height = $screen_top_on_d + $screen_height - $jmain_element.offset().top;
        }
        else if($screen_top_on_d >= $c_top && $screen_top_on_d + $screen_width >= $c_top && $screen_top_on_d + $screen_width <= $c_top + $c_height) {
            $r_height = $screen_height;
        }
        else if($screen_top_on_d >= $c_top && $screen_top_on_d + $screen_width >= $c_top && $screen_top_on_d + $screen_width > $c_top + $c_height) {
            $r_height = $jmain_element.offset().top + $c_height - $screen_top_on_d;
        }

        var _blockUI = {
            message: AC_GLOBAL.loading.message,
            css: jQuery.parseJSON(AC_GLOBAL.loading.image_style)
        };

        jQuery(window).block(_blockUI);
    }

    this.hideLoading = function() {
        setTimeout(function(){$body.unblock()}, 100)
    }

    /*
     * This function will be called when you click on pagination
     **/
    this.showPaging = function() {

        if (this.isMonthView()) {
            return;
        }

        var paging = this.is_first_tab ? jQuery('#artsopolis-calendar-pagination') : jQuery('#artsopolis-calendar-pagination-second-tab'),
            pagination = this.is_first_tab ? artsopolis_calendar_paging : artsopolis_calendar_paging_second_tab;

        if(! pagination.total_event) {
            paging.addClass('eli_hidden');
            return false;
        }

        paging.removeClass('eli_hidden');
        paging.pagination_artsopolis({
            items:          pagination.total_event,
            itemsOnPage:    pagination.page_size,
            cssStyle:       'light-theme',
            displayedPages: 3,
            edges:          1,
            onPageClick : function(pageNumber, event) {
                sessionStorage.theCurrentPage = pageNumber ? pageNumber : 1;
                not_scroll = false;
                var $searchIns = new acSearchFn(pageNumber, 'no', jQuery('#artsopolis-calendar-choice-weekend').hasClass('eli_active'));
                $searchIns.doSearch();
            },
            onInit: function() {
                let curPage = sessionStorage.theCurrentPage;
                if (curPage !== 1 && curPage !== null && typeof curPage !== 'undefined' && (window.location.href.indexOf("#page-") > -1)) {
                    paging.pagination('selectPage', curPage);
                    paging._selectPage.call(this, curPage);
                }
                // Callback triggered immediately after initialization
            }
        });
    }

    this.isMonthView = function() {
        return this.view == 'month';
    }

};


/* Initialize search by keyword */
var delay       = 500, // Delay key up search event
    is_loading  = false, // For the delay search event
    is_dirty    = false, // For the delay search event
    not_scroll  = false, // For the scroll of artsopolis_calendar_update_page function
    is_loaded_on_going_tab = false, // If this tab has already loaded, not load it again
    is_resetted = false; // If user click on Reset Button, when change tab we need to reset search

(function($) {
    $(function() {


        $(window).on('load', function() {
            $('.artsopolis-calendar-slider').flexslider({
                controlsContainer: ".art-calendar-silder-footer"
            });

            /**
             * @ticket #22311: [CF] Artsopolis Calendar - Jump the page back down to the start of the top of the plugin - item 7
             */
            if($('.artsopolis-calendar-frontend.page-detail-calendar-event').length) {
                $('body, html').animate({
                    scrollTop: $('.page-detail-calendar-event').offset().top - 100
                }, 500)
            }
        });

        $('body').on('hover, click', '#artsopolis-calendar-tabs-events .ico-date', function () {
            ele = $(this).parent().find('.div-two .show-events');
            var totalHeightChilds = 0;
            $(ele).find(".item").each(function(i1, t1){
                totalHeightChilds += $(t1).height();
            });
            if(totalHeightChilds >= $(ele).height()) {
                $(ele).find(".item-last").addClass('show-tablet');
            } else {
                $(ele).find(".item-last").removeClass('show-tablet')
            }
        });



        if($('.artsopolis-calendar-slider').length){
            $('.artsopolis-calendar-slider .flexslider').css('opacity',0);
            $('.loader').css('display','block');
            $('.loader').css('position','absolute');
            $('.loader').css('top','40%');
            $('.loader').css('left','46%');
            setTimeout(function(){
                $('.loader').fadeOut(function(){
                    $('.artsopolis-calendar-slider .flexslider').animate({opacity:1});
                });
            }, 1000);
        }


        // Set today
        var today       = new Date(),
            y_today     = today.getFullYear(),
            _m_today    = today.getMonth() + 1,
            m_today     = _m_today < 10 ? '0'+ _m_today : _m_today ,
            d_today     = parseInt(today.getDate()) < 10 ? '0'+ today.getDate() : today.getDate();

        $('#artsopolis-calendar-choice-today').attr('data-date-from', y_today +'-'+ m_today+'-'+ d_today);
        $('#artsopolis-calendar-choice-today').attr('data-date-to', y_today +'-'+ m_today+'-'+ d_today);

        // Set tomorrow
        var currentDate = new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
            d_tm        = currentDate.getDate() < 10 ? '0'+ currentDate.getDate() : currentDate.getDate(),
            _m_tm       = currentDate.getMonth() + 1,
            m_tm        = _m_tm < 10 ? '0'+ _m_tm : _m_tm,
            y_tm        = currentDate.getFullYear();

        $('#artsopolis-calendar-choice-tomorrow').attr('data-date-from', y_tm +'-'+ m_tm +'-'+d_tm);
        $('#artsopolis-calendar-choice-tomorrow').attr('data-date-to', y_tm +'-'+ m_tm +'-'+d_tm);

        // Set this weekend
        var sun_of_week = today.getDay() == 0 ? today : new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay() + 7),
            d_sun       = sun_of_week.getDate() < 10 ? '0'+ sun_of_week.getDate() : sun_of_week.getDate(),
            _m_sun      = sun_of_week.getMonth() + 1,
            m_sun       = _m_sun < 10 ? '0'+ _m_sun : _m_sun,
            y_sun       = sun_of_week.getFullYear(),
            sat_of_week = today.getDay() == 0 ? new Date(new Date().getTime() - 24 * 60 * 60 * 1000) :
                    new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay() + 6),
            d_sat       = sat_of_week.getDate() < 10 ? '0'+ sat_of_week.getDate() : sat_of_week.getDate(),
            _m_sat      = sat_of_week.getMonth() + 1,
            m_sat       = _m_sat < 10 ? '0'+ _m_sat : _m_sat,
            y_sat       = sat_of_week.getFullYear();


        $('#artsopolis-calendar-choice-weekend').attr('data-date-from', y_sat +'-'+ m_sat +'-'+ d_sat);
        $('#artsopolis-calendar-choice-weekend').attr('data-date-to', y_sun +'-'+ m_sun +'-'+ d_sun);

        $('#current-upcoming').on( 'click', '.artsopolis-calendar-search-again', reset_search);
        $('#ongoing-container').on( 'click', '.artsopolis-calendar-search-again', reset_search);

        $('#artsopolis-calendar-filter #reset').on('click', function() {
            var isDetailPage = $(this).data('event-detail');
            if(!isDetailPage) {
                reset_search();
                is_resetted = true;
            }
            else{
                window.location.href = $(this).data('href');
            }
        });

        /* Initialize date time picker*/
        $("#artsopolis-calendar-filter-from-date").datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: 'button',
            buttonImage: artsopolis_calendar_obj.calendar_src,
            buttonImageOnly: true,
            beforeShow: function( input, inst){
                $(inst.dpDiv).addClass('ui-datepicker-artsopolis');
            }
        });

        $("#artsopolis-calendar-filter-to-date").datepicker({
            dateFormat: 'yy-mm-dd',
            showOn: 'button',
            buttonImage: artsopolis_calendar_obj.calendar_src,
            buttonImageOnly: true,
            beforeShow: function( input, inst){
                $(inst.dpDiv).addClass('ui-datepicker-artsopolis');
            }
        });
        /********************************************************

        /* PAGINATION */
        if (typeof(artsopolis_calendar_paging) !== 'undefined') {
            var $searchIns = new acSearchFn();
            $searchIns.showPaging();
        }
        /********************************************************/


        /* Expand and collapse dates*/
        $( '.artsopolis-calendar-frontend' ).on('click', '.eli_expand-more-dates',artsopolis_calendar_show_more_dates);
        $( '.artsopolis-calendar-frontend' ).on('click', '.eli_collapse-more-dates',artsopolis_calendar_show_more_dates);
        /********************************************************/

        $('#artsopolis-calendar-filter-from-date').bind('change', select_filter_from_date);

        $('#artsopolis-calendar-filter-to-date').bind('change', select_filter_to_date);

        $('#artsopolis-calendar-choice-today').bind('click', artsopolis_calendar_filter_choice_time);
        $('#artsopolis-calendar-choice-tomorrow').bind('click', artsopolis_calendar_filter_choice_time);
        $('#artsopolis-calendar-choice-weekend').bind('click', artsopolis_calendar_filter_choice_time);
        /********************************************************/

        /* Expend collapse summary */
        $( '.artsopolis-calendar-frontend' ).on('click', '.eli_expand-summary', artsopolis_calendar_show_hide_summary);
        $( '.artsopolis-calendar-frontend' ).on('click', '.eli_less-summary', artsopolis_calendar_show_hide_summary);
        /********************************************************/

        /* Handle click on the tags */
        $( '#artsopolis-calendar-list-feed' ).on('click', '.eli_tags a.eli_a', active_filter_tags);
        $( '#ongoing-container' ).on('click', '.eli_tags a.eli_a', active_filter_tags);
        /********************************************************/

        /* Get data of second tab */
        $( '.eli_content-inner .tabs .tab-links #ongoing-tab' ).on( 'click', function () {

            // Only load data for second tab in the first time
            if ( ! is_loaded_on_going_tab || check_has_filter() ) {

                var $searchIns = new acSearchFn(1, 'yes');
                $searchIns.doSearch();

                if ( ! is_loaded_on_going_tab ) is_loaded_on_going_tab = true;

            }
        });

        // Get data on first tab clickable
        if ( $( '.eli_content-inner .tabs li' ).first().on('click', function() {
            if ( check_has_filter() ) {
                var $searchIns = new acSearchFn(1, 'yes');
                $searchIns.doSearch();

            }
        }))

        /* Handle tabs */
        $( '.eli_content-inner .tabs .tab-links a' ).on( 'click', function (e) {
            var currentAttrValue = $(this).attr('data-id');

            // Show/Hide Tabs
            $('.tabs #' + currentAttrValue).show().siblings().hide();

            // Change/remove current tab to active
            $(this).parent('li').addClass('active').siblings().removeClass('active');

            e.preventDefault();
        });

        /**
         * @ticket #22307: [CF] Artsopolis Calendar - Add a "Search" button and "Reset" button to the search bar - item 2
         */
        $('#artsopolis-calendar-filter .ac-btn-search').off('click').on('click', function(){
            // Do month view searching
            var isMonthView = triggerSearchForMonthView();
            if (isMonthView) {
                return;
            }

            // Do the normal searching
            not_scroll = true;
            var $searchIns = new acSearchFn(1, 'yes');
            $searchIns.doSearch();
        });

        /**
         * @ticket #22307: [CF] Artsopolis Calendar - Add a "Search" button and "Reset" button to the search bar - item 2
         */
        $('#artsopolis-calendar-filter .ac-btn-reset').off('click').on('click', function(){
            reset_search();
            is_resetted = true;
        });

    });

    /**
     * Trigger search function that can trick an action from outside of the calendar component
     *  @return boolean
     * */
    function triggerSearchForMonthView() {

        var isMonthView = $('#ac-current-view').val() == 'month';

        // Trigger searching
        if (isMonthView) {
            $('#ac-month-view-search-btn').trigger('click');
        }

        return isMonthView;
    };

    /*
     * This function will be called when you click on the more or less summary
     **/
    function artsopolis_calendar_show_hide_summary() {
        var $this = $(this),
            parent_row = $this.parents('div.eli_summary'),
            sum_full = parent_row.find('div.eli_summary-full'),
            sum_short = parent_row.find('div.eli_summary-short');

        if ($this.hasClass('eli_expand-summary')) {
            sum_full.removeClass('eli_hidden')
            sum_short.addClass('eli_hidden');
            return;
        }

        sum_full.addClass('eli_hidden')
        sum_short.removeClass('eli_hidden');

    }

    /**
     * This function will be called when click on the more date or less date
     * */
    function artsopolis_calendar_show_more_dates() {
        not_scroll = true;
        var $this = $(this);
        $this.addClass('eli_hidden');
        if ($this.hasClass('eli_expand-more-dates')) {
            $this.next('.eli_more-date').removeClass('eli_hidden');
            $this.next('.eli_more-date').children().removeClass('eli_hidden');
        } else {
            $this.parent().prev('.eli_expand-more-dates').removeClass('eli_hidden');
            $this.parent().addClass('eli_hidden');
        }
    }

    /**
     * This function will be called when click on the time picker
     * */
    function artsopolis_calendar_filter_choice_time () {
        not_scroll = true;
        var $this = $(this);
        $('.eli_ac-btn-filter').removeClass('eli_active');
        $this.addClass('eli_active');

        $("#artsopolis-calendar-filter-from-date").val($this.attr('data-date-from'));
        $("#artsopolis-calendar-filter-to-date").val($this.attr('data-date-to'));
    }


    function select_filter_to_date() {
        remove_filter_class_time_button();

        var from = $('#artsopolis-calendar-filter-from-date'),
            to_val = $(this).val().replace('-', '').replace('-', ''),
            from_val = from.val().replace('-', '').replace('-', '');

        if (parseInt(to_val) < parseInt(from_val)) {
            from.val($(this).val());
        }
    }

    function select_filter_from_date() {
        remove_filter_class_time_button();

        var to = $('#artsopolis-calendar-filter-to-date'),
            from_val = $(this).val().replace('-', '').replace('-', ''),
            to_val = to.val().replace('-', '').replace('-', '');

        if (to_val === "" || parseInt(to_val) < parseInt(from_val)) {
            to.val($(this).val());
        }
    }

    function active_filter_tags() {
        var category = $(this).attr('category');
        $('#tags-category').val(category);
        $('#tags-category').attr('category-name', $(this).attr('category-name'));
        debugger
        var $searchIns = new acSearchFn(1, 'yes', '', 1);
        $searchIns.doSearch();
    }

    function remove_filter_class_time_button () {
        $('#artsopolis-calendar-choice-weekend').removeClass('eli_active');
        $('#artsopolis-calendar-choice-tomorrow').removeClass('eli_active');
        $('#artsopolis-calendar-choice-today').removeClass('eli_active');
    }

    function reset_search() {
        $('#artsopolis-calendar-filter #keyword').val('');
        $('#artsopolis-calendar-filter #filter-by-category').val('');
        $('#artsopolis-calendar-filter #filter-by-location').val('');
        $('#artsopolis-calendar-filter #artsopolis-calendar-filter-from-date').val('');
        $('#artsopolis-calendar-filter #artsopolis-calendar-filter-to-date').val('');
        $('#artsopolis-calendar-filter #artsopolis-calendar-choice-today').removeClass('eli_active');
        $('#artsopolis-calendar-filter #artsopolis-calendar-choice-tomorrow').removeClass('eli_active');
        $('#artsopolis-calendar-filter #artsopolis-calendar-choice-weekend').removeClass('eli_active');
        var $searchIns = new acSearchFn(1, 'yes');
        $searchIns.doSearch();
    };

    function check_has_filter() {
         var _is_resetted = is_resetted;
         is_resetted = false;
         return $("#artsopolis-calendar-filter-from-date").val()
                 || $("#artsopolis-calendar-filter-to-date").val()
                 || $("#filter-by-location").val()
                 || $("#keyword").val()
                 || $('#filter-by-category').val()
                 || $('#tags-category').val() || _is_resetted;
    }

})(jQuery);


