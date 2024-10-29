moment.locale('es');
moment.locale('es', {
    monthsShort : [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec"
    ]
});

if ( typeof angular != "undefined" ) {
    jQuery.fn._calendar = {};
    jQuery.fn._calendar.sstart = " ";
    jQuery.fn._calendar.send = " ";
    var list_msg = jQuery('#list-msg-calendar');
    var calendar_header = '<div class="ac-month-bar">' +
            '<span class="start_month month-view-title">Events for {{sdm.format("MMMM")}} {{edm.format("YYYY")}}</span>' +
            '<div class="today hidden"> <span>'+list_msg.data('today')+'</span></div>';

    var calendar_month_links = '<div class="next-month">' +
            '<div class="prev-b" id="_apl_ca_prev" ng-click="previous()">{{prevMonth}}</div>' +
            '<div class="next-b" id="_apl_ca_next" ng-click="next()">{{nextMonth}}</div>' +
            '<input class="eli_hidden" type="button" ng-click="search()" name="ac-month-view-search-btn" id="ac-month-view-search-btn" value="Search" />' +
            '' +
            '</div>'
        ;

    var calendar_week = '<div class="ac-cl-head full">' +
        '<div class="ac-day sun">'+list_msg.data('sun')+'</div>' +
        '<div class="ac-day">'+list_msg.data('mon')+'</div>' +
        '<div class="ac-day">'+list_msg.data('tue')+'</div>' +
        '<div class="ac-day">'+list_msg.data('wed')+'</div>' +
        '<div class="ac-day">'+list_msg.data('thu')+'</div>' +
        '<div class="ac-day">'+list_msg.data('fri')+'</div>' +
        '<div class="ac-day">'+list_msg.data('sat')+'</div>' +
        '</div>';

    calendar_week += '<div class="ac-cl-head short">' +
        '<div class="ac-day sun">'+list_msg.data('shortSun')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortMon')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortTue')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortWed')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortThu')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortFri')+'</div>' +
        '<div class="ac-day">'+list_msg.data('shortSat')+'</div>' +
        '</div>';

    var calendar_day = '<div class="ac-cl-date">' +
        '<div class="date-week" ng-repeat="week in weeks"> ' +
        '<div class="ac-date-cell" data-date="{{day.date_value}}" ng-class="{ today: day.isToday, disable: day.outrange, empty: !eventtimes[day.date_value], \'different-month\': !day.isCurrentMonth, selected: day.date.isSame(selected) }" ng-repeat="day in week.days">' +
        '<div class="ac-day">' +
        '<span><span class="max-768">{{day.name}}</span> {{day.number}}</span> ' +
        '</div>' +

        '<div class="contain-vm-large event"> ' +
        '<ul class="ac-cl-event-list">' +
        '<li class="evt-itm" ng-repeat="eventtime in eventtimes[day.date_value] |  orderBy:orderTime | limitTo:acLimit"><div class="evt-itm-ct"><a href="{{ eventtime.viewLink }}" target="{{ eventtime.viewTargetLink }}"><span class="from">{{eventtime.eventName}}</span></a></div></li>' +
        '</ul> ' +
        '</div>' +


        '<div class="contain-vm-small event"> ' +
        '<ul class="ac-cl-event-list ">' +
        '<li class="evt-itm" ng-repeat="eventtime in eventtimes[day.date_value] |  orderBy:orderTime "><div class="evt-itm-ct"><a href="{{ eventtime.viewLink }}" target="{{ eventtime.viewTargetLink }}"><span class="from">{{eventtime.eventName}}</span></a></div></li>' +
        '</ul> ' +
        '</div>' +

        '<span ng-class="{viewmore: true, hidden: !moreThanThree(eventtimes[day.date_value])}" ng-click="viewmore(eventtimes[day.date_value], $event)">' + list_msg.data('more') + ' {{eventtimes[day.date_value].length}} ' + list_msg.data('label-events') + '</span>' +

        '</div>' +
        '</div>' +
        '</div>';

        var calendar_2 = '<div class="ac-cl-head">' +
        '<div class="ac-day sun">'+list_msg.data('sun')+'</div>' +
        '<div class="ac-day">'+list_msg.data('mon')+'</div>' +
        '<div class="ac-day">'+list_msg.data('tue')+'</div>' +
        '<div class="ac-day">'+list_msg.data('wed')+'</div>' +
        '<div class="ac-day">'+list_msg.data('thu')+'</div>' +
        '<div class="ac-day">'+list_msg.data('fri')+'</div>' +
        '<div class="ac-day">'+list_msg.data('sat')+'</div>' +
        '</div>' +
        '<div class="ac-cl-date">' +
        '<div class="ac-date-cell" ng-class="{ disable: disableDayInCa2(key, enableDays) }" ng-repeat=" (key, d) in specialc.days  " data-date="{{key}}">' +
        '<div class="ac-day"><span ng-class="{dName: true, sun: key === \'SUN\' }">{{d}}</span></div>' +
        '<div class="event">' +
        '<ul class="ac-cl-event-list ">' +
        '<li class="evt-itm" ng-repeat="t in specialc.eventtimes[key] | orderBy:orderTime "><div class="evt-itm-ct"><a href="javascript:void(0);"><span class="from">{{t.time_from | timeampm}}</span><span ng-class="{\'hidden\': isnulltt(t.time_to)}" >-</span><span class="to">{{t.time_to | timeampm}}</span></a></div></li>' +
        '</ul>' +
        '</div>' +
        '</div>' +
        '</div>';


    var _lB = '<div class="light-box" onclick="javascript:void(0);"></div>';

    var calendar_t = calendar_header + calendar_month_links + calendar_week + calendar_day + '<div>' + calendar_month_links + '</div>';



    jQuery.fn._calendar.mydefere = {
        then: function callback(callback) {
            callback();
        }
    };
    (function($) {

        /**
         * Double check wrapper width to hide/show the calendar title bar
         * */
        $(document).ready(function() {
            var wrapper = jQuery('#artsopolis-calendar-tabs-events');
            if (wrapper.width() <= 768) {
                $('.ac-cl-head.full').hide();
                $('.ac-cl-head.short').show();
            }
            else {
                $('.ac-cl-head.full').show();
                $('.ac-cl-head.short').hide();
            }
        });


        jQuery.fn._calendar.closeModal = function(){
            $(document).on('click', '.light-box', function(e){

                e.stopPropagation();

                if($('.box').length){
                    $('.closepopup').trigger('click');
                }
                if($('.viewmore-box').length){
                    $('.closeviewmore').trigger('click');
                }
                if($('.boxEdit').length){
                    $('.closepopup').trigger('click');
                }
            });
        };

        jQuery.fn._calendar.findPos = function (obj, offsetpclass) {
            var left = 0 , top = 0;

            if (obj.offsetParent) {
                do {
                    left += obj.offsetLeft;
                    top += obj.offsetTop;

                } while ((obj = obj.offsetParent) && !obj.classList.contains(offsetpclass));
            }

            return {left: left, top: top};
        };

        jQuery.fn._calendar.closeModal();
    })(jQuery);


    var app = angular.module("dashboard", []).filter('timeampm', function() {
        return function(input) {
            input = input || '';

            if(input === '')
                return '';

            var ai = input.split(':');
            var out = [];

            if(parseInt(ai[0], 10) < 12 )
            {
                if(ai[0] === '00') {
                    out.push(12);
                }
                else {
                    out.push(ai[0]);
                }

                out.push(ai[1]);
                out.push(list_msg.data('am'));
            }
            else {
                if(ai[0] === '12') {
                    out.push(12);
                }
                else {
                    out.push(ai[0] - 12);
                }

                out.push(ai[1]);
                out.push(list_msg.data('pm'));
            }

            var _strTime = out.pop(2);
            return out.join(':')+ ' '+ _strTime;
        };
    });

    app.controller("calendar", function($scope) {
        $scope.day = moment();
    });


    app.service(
        "eventTimeService",
        function( $http, $q ) {

            // Return public API.
            return({
                getAllEventTime: getAllEventTime,
            });

            // ---
            // PUBLIC METHODS.
            // ---

            function getAllEventTime(scope, event_id) {


                var currentMonth = scope.month.month();
                var currentYear = scope.month.year();

                var datePrevNextData = acDatePrevNextData(currentMonth, currentYear);
                var prevDate = moment([currentYear, datePrevNextData.prevMonth]);
                var nextDate = moment([datePrevNextData.nextYear, datePrevNextData.nextMonth]);
                scope.prevMonth = prevDate.format('MMMM');
                scope.nextMonth = nextDate.format('MMMM');


                var $searchInstance  = new acSearchFn(1, 'yes', '', '', 'json');

                var _blockUI = {
                    message: AC_GLOBAL.loading.message,
                    css: jQuery.parseJSON(AC_GLOBAL.loading.image_style)
                };
                jQuery(window).block(_blockUI);
                var request = $http({
                    method: "get",
                    url: AC_GLOBAL.ajax_url,
                    params: $searchInstance.initSearch()

                });

                return( request.then( function(res) {
                    scope.eventtimes = res.data.html;
                    jQuery(window).unblock();
                }, handleError ) );
            }

            // I transform the error response, unwrapping the application dta from
            // the API response payload.
            function handleError( response ) {

                // The API response from the server should be returned in a
                // nomralized format. However, if the request was not handled by the
                // server (or what not handles properly - ex. server error), then we
                // may have to normalize it on our end, as best we can.
                if (
                    ! angular.isObject( response.data ) ||
                    ! response.data.message
                ) {

                    return( $q.reject(list_msg.data('unknow-error')) );

                }

                if (response.data.error_type == 'nonce') {
                    alert(response.data.message);
                }

                // Otherwise, use expected error message.
                return( $q.reject( response.data.message ) );

            }


            // I transform the successful response, unwrapping the application data
            // from the API response payload.
            function handleSuccess( response ) {

                return( response.data );

            }

        }
    );


    app.directive('resize', function($window) {
        return {
            link: function(scope) {
                angular.element($window).on('resize', function(e) {
                    // Namespacing events with name of directive + event to avoid collisions
                    scope.$broadcast('resize::resize');
                });
            }
        }
    });


    app.directive("calendar", ['$compile', 'eventTimeService', function($compile, eventTimeService) {

        return {
            restrict: "E",

            scope: {
                selected: "=",
                eid: "@eid",
                sd: "@sd",
                ed: "@ed",
                dws: "@dws"
            },
            link: function(scope, elem, attrs) {
                if(attrs.type === '2') {
                    var days_of_week = scope.dws.split(',');
                    var arrEnableDay = [];
                    var dow = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
                    elem.replaceWith($compile(calendar_2)(scope));
                    scope.type = '2';
                    scope.specialc = [];
                    scope.specialc.days = { 'SUN' : list_msg.data('sun'),
                        'MON' : list_msg.data('mon'),
                        'TUE' : list_msg.data('tue'),
                        'WED' : list_msg.data('wed'),
                        'THU' : list_msg.data('thu'),
                        'FRI' : list_msg.data('fri'),
                        'SAT' : list_msg.data('sat') };
                    scope.specialc.eventtimes = [];
                    jQuery.each(days_of_week, function( index, value ) {
                        arrEnableDay[index] = dow[value];
                    });
                    scope.enableDays =  arrEnableDay;
                }
                else {
                    elem.replaceWith($compile(calendar_t)(scope));
                    scope.type = '1';
                }

                scope.eventtimes = [];
                scope.sdm = moment(scope.sd, "YYYY-MM-DD");
                scope.sdm_month = scope.sdm.format('MMMM');
                scope.edm = moment(scope.ed, "YYYY-MM-DD");
                scope.selected = scope.sdm;
                scope.month = scope.selected.clone();
                scope.acLimit = 3;
                scope.checkExistEvent = function (eventtimes, v2) {
                    var isexist = false;
                    jQuery.each(eventtimes , function(_, v) {
                        if(v.time_from == v2.time_from && v.time_to == v2.time_to) {
                            isexist = true;
                            return false;
                        }
                    });

                    return isexist;
                };

                scope.moreThanThree = function(eventtimes) {
                    return typeof eventtimes !== "undefined" && (eventtimes.length > scope.acLimit);
                };

                scope.orderTime = function(eventtime) {

                    return eventtime.time_from;
                };

                scope.sameYear = function() {
                    return scope.sdm.format("YYYY") === scope.edm.format("YYYY");
                };

                scope.checkUnnormalEventTime = function(date_from, date_to, time_from, time_to) {

                    var d1 = moment(date_from, 'YYYY-MM-DD');
                    var d2 = moment(date_to, 'YYYY-MM-DD');

                    var dta = d2.diff(d1, 'day');

                    var et = '';
                    var arr_error = [];
                    for(var i = 0; i <= dta; i++) {
                        var iday = d1.clone();
                        iday.add(i, 'days');

                        var _sdate =  iday.format('YYYY-MM-DD');

                        if(typeof scope.eventtimes[_sdate] === 'undefined')
                            continue;

                        et = scope.eventtimes[_sdate];

                        jQuery.each(et, function(_, v) {

                            if(v.time_from >= time_from && v.time_to <= time_to ) {
                                arr_error.push(_sdate);
                                return false;
                            }
                        });

                    }


                    return arr_error;
                };

                scope.haveInValid = function(_sdate) {

                    if(typeof scope.eventtimes !== 'undefined' && typeof scope.eventtimes[_sdate] === 'undefined')
                        return false;
                    var et = scope.eventtimes[_sdate];

                    if(et.length === 1) return false;

                    var isVaid = false;
                    for(var i =0 ;i < et.length; i++) {
                        for(var j = i+1; j < et.length; j++) {

                            if(et[i].time_from >= et[j].time_from && et[i].time_to <= et[j].time_to || et[i].time_from <= et[j].time_from && et[i].time_to >= et[j].time_to) {
                                return true;
                            }
                        }
                    }

                    return isVaid;
                };

                scope.mbUpdateSpecial = function() {

                    if(scope.type === '2') {
                        var d1 = scope.sdm.clone().add('1', 'w');
                        for(var i = 0; i < 7; i++) {
                            var n = d1.clone().add(i, 'days')
                                , w = n.format('ddd').toUpperCase()
                                , sn = n.format('YYYY-MM-DD')
                                ;
                            scope.specialc.eventtimes[w] = scope.eventtimes[sn];

                        }
                    }
                };

                /**
                 * Get events date time
                 * */
                eventTimeService.getAllEventTime(scope, scope.eid).then(function() {
                    renderT(scope, jQuery);
                }).then(function() {
                    scope.mbUpdateSpecial();
                });


            }
        };


        function getPrevClosestSundayInWeek(md) {
            var _md = md.clone();
            var _n = _md.format("dd").substring(0, 2).toUpperCase();
            if(_n === 'SU') {
                return md;
            }

            return getPrevClosestSundayInWeek(_md.add(-1, 'day'));
        }

        function getNextClosestSaturdayInWeek(md) {
            var _md = md.clone();
            var _n = _md.format("dd").substring(0, 2).toUpperCase();
            if(_n === 'SA') {
                return md;
            }

            return getNextClosestSaturdayInWeek(_md.add(1, 'day'));
        }

        function renderT(scope, $) {

            var start = getPrevClosestSundayInWeek(scope.sdm);
            var end = getNextClosestSaturdayInWeek(scope.edm);

            _buildMonth(scope, start, end);

            scope.select = clickListener;
            scope.disableDayInCa2 = disableListener;

            scope.viewmore = function(events, $e) {

                var $calendar_container = jQuery('.calendar-container');
                var $body = $('body');
                var $this = jQuery($e.target);
                var _pos = jQuery.fn._calendar.findPos($this[0], 'calendar-container');
                var _cur_top = _pos.top + jQuery('.calendar-container').position().top;
                var _cur_left = _pos.left + jQuery('.calendar-container').position().left;

                $body.append(_lB);

                var $vmb = $('.viewmore-box');
                var cell_width = $this.closest('.ac-date-cell').outerWidth();
                $vmb.css('display','block');
                $vmb.fadeIn(1000, 'linear');
                _pos_top = _cur_top + 25;
                _pos_left = _cur_left - ($this.width()/2 ) + (cell_width > 120 ? 30 : 0);

                if(_pos_left  + $vmb.width() >  $(".artsopolis-calendar-frontend").width()){
                    $vmb.find(".arrow-box").css({'left': 'unset', 'right': 0});
                    _pos_left = _pos_left - $vmb.width() / 2 - (cell_width > 120 ? 30 : 0);
                } else {
                    $vmb.find(".arrow-box").attr('style','');
                }
                console.log(cell_width, $(".artsopolis-calendar-frontend").width(),
                    $vmb.width(), _pos_left);
                $vmb.attr('style','display: block; position: absolute !important; top: ' + _pos_top + 'px !important; left: ' + _pos_left + "px !important");

                if(typeof scope.current_edit_date !== 'undefined') {
                    // move back
                    jQuery('[data-date="' + scope.current_edit_date + '"]').find('.contain-vm-small').append(scope._eventList);
                }

                scope._eventList = $this.parents('.ac-date-cell').find('.contain-vm-small .ac-cl-event-list ');

                scope.current_edit_date = $this.parents(".ac-date-cell").data('date');
                $('.viewmore-box .ac-cl-event-list ').remove();
                $vmb.append(scope._eventList);
            };

            scope.isnulltt = function(time_to) {
                return time_to === "";
            };


            /**
             * Searching
             *
             * */
            scope.search = function () {

                var searchIns = new acSearchFn();
                searchIns.setDateTimeData(scope.sd, scope.ed);

                eventTimeService.getAllEventTime(scope, scope.eid).then(function() {
                    renderT(scope, jQuery);
                }).then(function() {
                    scope.mbUpdateSpecial();
                });

                jQuery.fn._calendar.sstart = " ";
                jQuery.fn._calendar.send = " ";
            }


            /**
             * Next month
             *
             * */
            scope.next = function() {

                if(jQuery("#_apl_ca_next").hasClass('disable')) {
                    return;
                }

                var next = scope.month.clone();
                _removeTime(next.month(next.month()+1).date(1));

                var currentMonth = scope.month.month();
                var currentYear = scope.month.year();
                var datePrevNextData = acDatePrevNextData(currentMonth, currentYear);

                var startDate = moment([datePrevNextData.nextYear, datePrevNextData.nextMonth]);
                var endDate = moment(startDate).endOf('month');

                scope.resetScopes(startDate, endDate);

                var searchIns = new acSearchFn();
                searchIns.setDateTimeData(scope.sd, scope.ed);

                eventTimeService.getAllEventTime(scope, scope.eid).then(function() {
                    renderT(scope, jQuery);
                }).then(function() {
                    scope.mbUpdateSpecial();
                });

                jQuery.fn._calendar.sstart = " ";
                jQuery.fn._calendar.send = " ";

            };

            scope.previous = function() {

                if(jQuery("#_apl_ca_prev").hasClass('disable')) {
                    return;
                }

                var previous = scope.month.clone();
                _removeTime(previous.month(previous.month()-1).date(1));

                var currentMonth = scope.month.month();
                var currentYear = scope.month.year();
                var datePrevNextData = acDatePrevNextData(currentMonth, currentYear);

                var startDate = moment([datePrevNextData.prevYear, datePrevNextData.prevMonth]);
                var endDate = moment(startDate).endOf('month');

                scope.resetScopes(startDate, endDate);

                var searchIns = new acSearchFn();
                searchIns.setDateTimeData(scope.sd, scope.ed);

                eventTimeService.getAllEventTime(scope, scope.eid).then(function() {
                    renderT(scope, jQuery);
                }).then(function() {
                    scope.mbUpdateSpecial();
                });

            };



            /**
             * Reset scopes
             * */
            scope.resetScopes = function(startDate, endDate) {
                scope.month = startDate;
                scope.sdm = startDate;
                scope.edm = endDate;
                scope.sd = startDate.format('YYYY-MM-DD');
                scope.ed = endDate.format('YYYY-MM-DD');
            };

            (function($) {
                var _blockUI = {
                    message: AC_GLOBAL.loading.message,
                    css: $.parseJSON(AC_GLOBAL.loading.image_style)
                };

                var $body = $('body');

                $body.on('click', '.closepopup', function(e){
                    var $parent = $(this).parent();
                    $parent.remove();
                    $('.light-box').remove();
                    $('.ac-date-cell').removeClass('add-event');
                });

                $body.on('click', '.closeviewmore', function(){
                    $('.viewmore-box').css('display','none');
                    $('.light-box').remove();
                    $('.ac-date-cell').removeClass('add-event');
                });

                $(window).on('resize', function(e) {
                    if(typeof scope.current_edit_date !== 'undefined') {
                        // move back
                        jQuery('[data-date="' + scope.current_edit_date + '"]').find('.contain-vm-small').append(scope._eventList);
                    }
                });
            })(jQuery);
        }

        function _removeTime(date) {
            return date.day(0).hour(0).minute(0).second(0).millisecond(0);
        }

        function _buildMonth(scope, start, end) {
            scope.weeks = [];
            var done = false, date = start.clone(), monthIndex = scope.month.month();
            while (!done) {
                scope.weeks.push({ days: _buildWeek(date.clone(), monthIndex, scope) });
                date.add(1, "w");

                done = date.diff(end) >= 0;
            }
        }


        function _buildWeek(date, month, scope) {
            var days = [];
            var days_of_week = scope.dws.split(',');
            for (var i = 0; i < 7; i++) {
                var number = date.date();

                if(date.date() === 1 || date.diff(scope.sdm) === 0) {
                    number = date.format('MMM') + ' ' + number;
                }

                days.push({
                    name: date.format("dd").substring(0, 2).toUpperCase(),
                    number: number,
                    isCurrentMonth: date.month() === month,
                    isToday: date.isSame(new Date(), "day"),
                    date: date,
                    outrange: !(date.month() === month),
                    date_value: date.format("YYYY-MM-DD")
                });
                date = date.clone();
                date.add(1, "d");
            }
            return days;
        }



        function clickListener(day, e) {
            (function($) {
                var $this = $(e.target), $body = $('body');
                var _eleTarget = e.target.className;

                if(_eleTarget.indexOf('date-cell') === -1 && (_eleTarget.indexOf("num") > -1 || _eleTarget.indexOf("sun") > -1 || _eleTarget.indexOf("dName sun") > -1 || _eleTarget.indexOf("dName") > -1 || _eleTarget.indexOf("day")> -1 || _eleTarget.indexOf("event")> -1)){
                    $this = $this.parents('.ac-date-cell');
                    _eleTarget = $this.attr('class');
                }

                if($this.hasClass('disable') || $this.hasClass('shift-selected')) return;

                if(!e.shiftKey){

                    _selectedDay = $(e.target);

                    var _cur_left = $this.offset().left;
                    var _cur_top = $this.offset().top;


                    if(_eleTarget.indexOf("date-cell") > -1 || _eleTarget.indexOf("num") > -1 || _eleTarget.indexOf("sun") > -1 || _eleTarget.indexOf("dName sun") > -1 || _eleTarget.indexOf("dName") > -1){
                        $this.addClass('add-event');
                        $body.append(_lB);



                        var $box = $('.box');
                        $box.css('display','block');
                        $box.fadeIn(1000, 'linear');
                        if($(window).width()>480){
                            $box.css('top',_cur_top-30);
                            if(($(window).width()- (_cur_left+30))>290){
                                $box.css('left',_cur_left+30);
                            }else{
                                $box.css('left',_cur_left);
                            }
                        }else{
                            $box.css('top',_cur_top-30);
                            $box.css('left',($(window).width()/2)-140);
                        }

                    }
                }else if(e.shiftKey){

                    var _start = jQuery.fn._calendar.sstart;
                    var _end = jQuery.fn._calendar.send;

                    if(_eleTarget.indexOf('viewmore') !== -1 || _eleTarget.indexOf('date-cell') === -1 ){
                        $this = $this.parents('.ac-date-cell');
                    }

                    var week_index = $this.parent('.date-week').index();

                    var shift_press = $this.index() + week_index * 7;
                    if(jQuery.fn._calendar.sstart === " "){
                        _start = shift_press + 1;
                    }else{
                        _end = shift_press + 1;
                    }

                    $this.addClass('shift-selected');

                    if((_start !==" ") && (_end !==" ")){

                        if((_end - _start) > 0){
                            for(var i = _start ; i < _end; i ++ ){
                                $('.ac-date-cell:eq('+i+')').addClass('shift-selected');
                            }

                        }else if(_end !== " "){
                            for(var j = _start ; j > _end; j--){
                                $('.ac-date-cell:eq('+(j-1)+')').addClass('shift-selected');
                            }
                        }
                    }

                    jQuery.fn._calendar.sstart = _start;
                    jQuery.fn._calendar.send = _end;
                }
            })(jQuery);
        }

        function disableListener(day,enableDays){
            if(jQuery.inArray( day, enableDays ) != -1){
                return false;
            }else{
                return true;
            }
        }
    }]);
}


function acDatePrevNextData(currentMonth, currentYear)
{
    var prevMonth, nextMonth, nextYear, prevYear;
    if (currentMonth == 11) {
        prevMonth = 10;
        nextMonth = 0;
        nextYear = currentYear + 1;
        prevYear = currentYear;
    }
    else if (currentMonth == 0) {
        prevMonth = 11;
        nextMonth = 1;
        nextYear = currentYear;
        prevYear = currentYear - 1;
    }
    else {
        prevMonth = currentMonth - 1;
        nextMonth = currentMonth + 1;
        nextYear = currentYear;
        prevYear = currentYear;
    }
    return {
        prevMonth: prevMonth,
        nextMonth: nextMonth,
        nextYear: nextYear,
        prevYear: prevYear,
    };
}