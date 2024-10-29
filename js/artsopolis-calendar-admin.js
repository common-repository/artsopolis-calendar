(function ($) {
    $(function () {

        $('#artsopolis-calendar-info').on('change', '#data-source', function() {
            if ($(this).val() == 'xml') {
                $('#xml-block').show();
                $('#token-block').hide();
            } else {
                $('#xml-block').hide();
                $('#token-block').show();
            }
        });

        $('#artsopolis-calendar-list-categories').on('click', 'input', function() {
            $('input[name^="artsopolis_calendar_options"][name$="[has_changed]"]').val(1);
        });

        $( '#artsopolis-calendar-settings-form input[type="submit"]' ).click( function() {
            var error = false;

            var _arr_urls = $('.artsopolist-calendar-input-url');
            var urlregex = new RegExp(
            "^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");

            $.each( _arr_urls, function(i, v) {

                if ( $( v ).val() && ! urlregex.test( $( v ).val() ) ) {
                    $( '#apollo-notice' ).show();
                    $( v ).addClass( 'apollo-input-error' );
                    $( '[data-id=\"'+$( v ).attr( 'name' ) +'-url\"]' ).show();
                    error = true;
                } else {
                    $( '[data-id="'+$( v ).attr( 'name' ) +'-url"]' ).hide();
                    $( v ).removeClass( 'apollo-input-error' );
                }

            });

            if ( error ) {
                $( '.updated.settings-error' ).hide();
                $( '.error.settings-error' ).show();
                $('html,body').animate({ scrollTop: $('.artsopolis-calendar-tabs-menu').offset().top }, 1000);
                return false;
            }
        });
        $(".artsopolist-calendar-input-url[type=text]").bind( 'keyup change', check_url );
        $( '.artsopolist-calendar-input-url' ).focus(function() {
            if ( ! $(this).val() ) {
                $(this).val('http://');
            }
        });

        $( '#wrapper-category' ).on( 'click', '.sub-row label', function() {
            var checkbox = $( this ).prev();
            if ( ! checkbox.attr('checked' ) ) {
                checkbox.attr( 'checked', 'checked' );
            } else {
                checkbox.removeAttr( 'checked' );
            }
        } );

        $( '#ac-filter-by-feed' ).change( function() {
            window.location.href = $(this).data().href + $(this).val();
        });

        // handle active plugin menu
        if ( ( $( '#ac-feature-event-page' ) && $( '#ac-feature-event-page' ).length )
                || $( '#ac-feed-frm' ) && $( '#ac-feed-frm' ).length ) {
            $( '#menu-plugins' ).addClass('wp-has-current-submenu wp-has-submenu');
            $( '#menu-plugins > a' ).addClass( 'wp-has-current-submenu' );
            $( 'a[href="plugins.php?page=admin-artsopolis-calendar"]' ).parent().addClass( 'current' );
        }
        // Loads the color pickers
	$('.nav-bg-color').wpColorPicker();

        // Handle the fancy box for the logo
        $("a#ac-teaser-widget-logo-link").fancybox({});
        $("a#ac-plugin-logo-link").fancybox({});

        // Upload logo
        artsopolis_calendar_upload();

        // Add sorting for featured events use jQuery Datatable
        add_sorting_data_table();

        $('#artsopolis-calendar-settings-form').submit(check_artsopolis_calendar_form);
        $('#ac-show-list-territory').click(show_territories);

        $('input[name^="artsopolis_calendar_options"][name$="[feed_url]"]').keyup(function (e) {

            // Not ctrl v, ctrl a, f5, tab, ctrl c
            if (e.keyCode != 17 && e.keyCode != 65 && e.keyCode != 67 && e.keyCode != 9 && e.keyCode != 116) {
                clearTimeout($.data(this, 'timer'));

                if (e.keyCode == 13) {
                    artsopolis_calendar_check_feed_url();
                }
                else
                    $(this).data('timer', setTimeout(artsopolis_calendar_check_feed_url, 500));

            }
        });
        $('input[name^="artsopolis_calendar_options]"][name$="[feed_url"]').focus(artsopolis_calendar_check_feed_url_empty);
        $('.artsopolis-calendar-settings-category .hide-show-subcategory').bind('click', hide_show_subcategory);
        $('input[name^="artsopolis_calendar_options"][name$="[category_xml_feed_url]"]').keyup(function (e) {

            // Not ctrl v, ctrl a, f5, tab, ctrl c
            if (e.keyCode != 17 && e.keyCode != 65 && e.keyCode != 67 && e.keyCode != 9 && e.keyCode != 116) {
                clearTimeout($.data(this, 'timer'));
                if (e.keyCode == 13) {
                    artsopolis_calendar_check_category_xml_feed();
                }
                else
                    $(this).data('timer', setTimeout(artsopolis_calendar_check_category_xml_feed, 500));
            }
        });
        $('input[name^="artsopolis_calendar_options"][name$="[all_category_xml_feed_url]"]').keyup(function (e) {

            // Not ctrl v, ctrl a, f5, tab, ctrl c
            if (e.keyCode != 17 && e.keyCode != 65 && e.keyCode != 67 && e.keyCode != 9 && e.keyCode != 116) {
                clearTimeout($.data(this, 'timer'));
                if (e.keyCode == 13) {
                    artsopolis_calendar_check_all_category_xml_feed();
                }
                else
                    $(this).data('timer', setTimeout(artsopolis_calendar_check_all_category_xml_feed, 500));
            }
        });

        catOption.change(function() {
            if (this.value == 1) {
                artsopolis_calendar_check_all_category_xml_feed();
            }
            else if (this.value == 0) {
                artsopolis_calendar_check_category_xml_feed();
            }
        });

        $('#arts-delete-plugin-logo').bind('click', artsopolis_calendar_delete_image_by_url);
        $('#arts-delete-teaser-logo').bind('click', artsopolis_calendar_delete_image_by_url);
        });

    initOverrideCss('artsopolis-calendar-override-css');

    function check_artsopolis_calendar_form() {
        var messages    = '',
            main_focus  = $('input[name^="artsopolis_calendar_options"][name$="[feed_url]"]');

        if ($('input[name^="artsopolis_calendar_options"][name$="[feed_valid]"]').val() == "0") {
            messages += '\n + The XML feed is invalid';
            messages = "Warning:\n" + messages + "\n\n  Do you want to continue ?";
        } else {
            return true;
        }

        if(messages && confirm(messages)) {
            return true;
        }

        if(main_focus){
            main_focus.focus();
        }

        return false;
    }


    function show_territories() {
        var url = artsopolis_calendar_obj.admin_url + '/?action=ac_get_territories'
        tb_show("Territories List", url);
        return false;
    }

    function artsopolis_calendar_check_feed_url(callback) {
        var feed_url = $('input[name^="artsopolis_calendar_options"][name$="[feed_url]"]').val();

        if (feed_url.length < 3 || ! feed_url.length) {
            $('#checking-xml-feed').hide();

            $('.xml-feed .artsopolis-calendar-error').show();
            $('.xml-feed .artsopolis-calendar-success').hide();
            $('input[name^="artsopolis_calendar_options"][name$="[feed_valid]"]').val(0);
            artsopolis_calendar_hide_loading();
            return false;
        }

        $('.xml-feed .artsopolis-calendar-success').hide();
        $('.xml-feed .artsopolis-calendar-error').hide();
        $('#checking-xml-feed').show();
        artsopolis_calendar_show_loading();
        $('#artsopolis-calendar-settings-form #submit').prop('disabled', true);

        $.ajax({
            url: artsopolis_calendar_obj.admin_url,
            type: 'post',
            data: {
                'action': 'ac_check_valid_feed_url',
                'feed_url': encodeURI(feed_url)
            },
            beforeSend: function() {
                $('#checking-xml-feed').attr('is-checking', 1);
            },
            success: function(res) {
                var parent = $('.artsopolis-calendar-status-container'),
                    success = true;
                $('#checking-xml-feed').attr('is-checking', 0);
                if (parseInt(res)) {
                    $('.xml-feed .artsopolis-calendar-error').hide();
                    $('.xml-feed .artsopolis-calendar-success').show();
                    $('input[name^="artsopolis_calendar_options"][name$="[feed_valid]"]').val(1);
                } else {
                    $('.xml-feed .artsopolis-calendar-error').show();
                    $('.xml-feed .artsopolis-calendar-success').hide();
                    $('input[name^="artsopolis_calendar_options"][name$="[feed_valid]"]').val(0);
                    success = false;
                }

                if ($('input[name=feed_hidden]').val() != feed_url) {
                    $('input[name^="artsopolis_calendar_options"][name$="[has_changed]"]').val(1);
                } else {
                    $('input[name^="artsopolis_calendar_options"][name$="[has_changed]"]').val(0);
                }

                if (typeof(callback) === 'function') {
                    callback({
                        success: success
                    });
                }

                parent.show();
                artsopolis_calendar_hide_loading();

                if (! parseInt($('#checking-category-xml').attr('is-checking'))) {
                    $('#artsopolis-calendar-settings-form #submit').prop('disabled', false);
                }

                if (! parseInt($('#checking-all-category-xml').attr('is-checking'))) {
                    $('#artsopolis-calendar-settings-form #submit').prop('disabled', false);
                }

                $('#checking-xml-feed').hide();
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function artsopolis_calendar_check_category_xml_feed(callback) {
        var url = $('input[name^="artsopolis_calendar_options"][name$="[category_xml_feed_url]"]').val();

        if (! url.length) {
            $('.xml-category .artsopolis-calendar-error').show();
            if ($('input[id="display-all-associated-categories"]:checked').val() == 0) {
                $('#wrapper-category').html('');
            }
            $('.xml-category .artsopolis-calendar-success').hide();
            $('input[name^="artsopolis_calendar_options"][name$="[category_valid]"]').val(0);
            return false;
        }

        $.ajax({
            url: artsopolis_calendar_obj.admin_url,
            type: 'post',
            data: {
                'action': 'ac_check_valid_category_xml_url',
                'category_xml_feed_url': encodeURI(url),
                'fid' : $( '#ac-feed-id' ).val(),
            },
            beforeSend: function() {
                $('#checking-category-xml').attr('is-checking', 1);
                $('.xml-category .artsopolis-calendar-success').hide();
                $('.xml-category .artsopolis-calendar-error').hide();
                $('#checking-category-xml').show();
                artsopolis_calendar_show_loading();
                $('#artsopolis-calendar-settings-form #submit').prop('disabled', true);
            },
            success: function(res) {
                var parent = $('.artsopolis-calendar-status-container');
                $('#checking-category-xml').attr('is-checking', 0);
                if ($('input[id="display-all-associated-categories"]:checked').val() == 0) {
                    $('#wrapper-category').html(res);
                }
                if (res) {
                    $('.xml-category .artsopolis-calendar-error').hide();
                    $('.xml-category .artsopolis-calendar-success').show();
                    $('input[name^="artsopolis_calendar_options"][name$="[category_valid]"]').val(1);
                } else {
                    $('.xml-category .artsopolis-calendar-error').show();
                    $('.xml-category .artsopolis-calendar-success').hide();
                    $('input[name^="artsopolis_calendar_options"][name$="[category_valid]"]').val(0);
                }

                parent.show();
                artsopolis_calendar_hide_loading();

                if (! parseInt($('#checking-xml-feed').attr('is-checking'))) {
                    $('#artsopolis-calendar-settings-form #submit').prop('disabled', false);
                }

                $('#checking-category-xml').hide();
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function artsopolis_calendar_check_all_category_xml_feed(callback) {
        var url = $('input[name^="artsopolis_calendar_options"][name$="[all_category_xml_feed_url]"]').val();

        if (! url.length) {
            $('.xml-all-category .artsopolis-calendar-error').show();
            if ($('input[id="display-all-associated-categories"]:checked').val() == 1) {
                $('#wrapper-category').html('');
            }
            $('.xml-all-category .artsopolis-calendar-success').hide();
            $('input[name^="artsopolis_calendar_options"][name$="[all_category_valid]"]').val(0);
            return false;
        }

        $.ajax({
            url: artsopolis_calendar_obj.admin_url,
            type: 'post',
            data: {
                'action': 'ac_check_valid_all_category_xml_url',
                'all_category_xml_feed_url': encodeURI(url),
                'fid' : $( '#ac-feed-id' ).val(),
            },
            beforeSend: function() {
                $('#checking-all-category-xml').attr('is-checking', 1);
                $('.xml-all-category .artsopolis-calendar-success').hide();
                $('.xml-all-category .artsopolis-calendar-error').hide();
                $('#checking-all-category-xml').show();
                artsopolis_calendar_show_loading();
                $('#artsopolis-calendar-settings-form #submit').prop('disabled', true);
            },
            success: function(res) {
                var parent = $('.artsopolis-calendar-status-container');
                $('#checking-all-category-xml').attr('is-checking', 0);
                if ($('input[id="display-all-associated-categories"]:checked').val() == 1) {
                    $('#wrapper-category').html(res);
                }
                if (res) {
                    $('.xml-all-category .artsopolis-calendar-error').hide();
                    $('.xml-all-category .artsopolis-calendar-success').show();
                    $('input[name^="artsopolis_calendar_options"][name$="[all_category_valid]"]').val(1);
                } else {
                    $('.xml-all-category .artsopolis-calendar-error').show();
                    $('.xml-all-category .artsopolis-calendar-success').hide();
                    $('input[name^="artsopolis_calendar_options"][name$="[all_category_valid]"]').val(0);
                }

                parent.show();
                artsopolis_calendar_hide_loading();

                if (! parseInt($('#checking-xml-feed').attr('is-checking'))) {
                    $('#artsopolis-calendar-settings-form #submit').prop('disabled', false);
                }

                $('#checking-all-category-xml').hide();
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function artsopolis_calendar_show_loading() {
        if(document.getElementById("TB_overlay") === null) {
            jQuery("body").append("<div id='TB_overlay'></div><div id='TB_window'></div>");
            jQuery("#TB_overlay").addClass("TB_overlayBG");//use background and opacity
        }
        console.log(imgLoader.src);
        jQuery("body").append("<div id='TB_load'><img src='"+imgLoader.src+"' width='208' /></div>");//add loader to the page
        jQuery('#TB_load').show();//show loader
        jQuery('#TB_load').show();

        jQuery("#TB_overlay").click(tb_remove);
    };

    function artsopolis_calendar_hide_loading() {

	try {
	    tb_remove();
	} catch(e) {
            jQuery("#TB_overlay").remove();
	}
        jQuery("#TB_load").remove();
    };

    function hide_show_subcategory() {
        var elm = $(this).parent().next();
        elm.toggleClass('hidden');

        $(this).html(elm.hasClass('hidden') ? '&#9660;' : '&#9658;');
    }

    function artsopolis_calendar_check_feed_url_empty() {
        if (! $(this).val()) {
            $('input[name^="artsopolis_calendar_options"][name$="[feed_valid]"]').val(0);
            $('input[name^="artsopolis_calendar_options"][name$="[has_changed]"]').val(1);
        }
    }

    function add_sorting_data_table() {
        var selected_event_obj = $('#artsopolis-calendar-selected-events'),
            selected_event = selected_event_obj.val(),

            spotlight_event_obj = $('#artsopolis-calendar-spotlight-events'),
            spotlight_event = spotlight_event_obj.val();

        //if ( ! selected_event_obj.length ) {
        //    return false;
        //}

        if (selected_event == undefined) {
            selected_event = '';
        }

        if (spotlight_event == undefined) {
            spotlight_event = '';
        }

        selected_event = selected_event.split(',');
        spotlight_event = spotlight_event.split(',');

        /* Paging list */
        var $tbl = $("#artsopolis-calendar-featured-events");

        $tbl.dataTable({
            "bProcessing": true,
            "bSort": ! $( '#ac-feeds' ).val(),
            "iDisplayLength": $tbl.data() ? $tbl.data().perPage : 0,
            "fnDrawCallback": function() {
                // in case your overlay needs to be put away automatically you can put it here
                $('#artsopolis-calendar-featured-events').css('visibility', 'inherit');
                $('#processing-artsopolis-calendar').hide();
            },
            "fnCreatedRow": function ( row, data, index ) {

                if ( row.childNodes[1] != undefined ) {

                    var checkbox = row.childNodes[1].children[0],
                        event_id = checkbox.value;

                    if ( typeof event_id != 'undefined' && jQuery.inArray(event_id.toString(), selected_event) != -1 ) {
                        jQuery(checkbox).attr('checked', true);
                    }


                    var spotlight_checkbox = row.childNodes[3].children[0],
                        spotlight_event_id = spotlight_checkbox.value;
                    if ( typeof spotlight_event_id != 'undefined' && jQuery.inArray(spotlight_event_id.toString(), spotlight_event) != -1 ) {
                        jQuery(spotlight_checkbox).attr('checked', true);
                    }

                }

            }
        });
    }

    function artsopolis_calendar_delete_image_by_url() {

        var image       = $(this),
            image_url   = image.attr('image-url');

        if (! image_url) {
            return false;
        }

        if(confirm('Are you sure ?')) {

            $.ajax({
                url: artsopolis_calendar_obj.admin_url,
                type: 'post',
                data: {
                    'action': 'ac_delete_image_by_url',
                    'image_url': image_url,
                    'opt_name': image.attr('opt-name')
                },
                beforeSend: function() {
                    artsopolis_calendar_show_loading();
                    image.hide();
                    image.next('.logo-deleting').show();
                    image.next('.logo-deleting').children().children().hide();
                    image.next().next().children().children().hide();
                },
                success: function(res) {
                    image.prev('input').prev('input').val('');
                    image.addClass('hidden');
                    image.next('.logo-deleting').hide();
                    artsopolis_calendar_hide_loading();
                },
                error: function(errorThrown) {

                }
            });
        }
    }

    function artsopolis_calendar_upload () {
        var formfield, remove_btn, thumb;

        /* user clicks button on custom field, runs below code that opens new window */
        jQuery('.arts-upload-button').click(function() {
            formfield   = jQuery(this).prev('input'); //The input field that will hold the uploaded file url
            remove_btn  = jQuery(this).next('input');
            thumb       = jQuery(this).next('input').next('span').next('div').children().children();
            tb_show('','media-upload.php?TB_iframe=true');

            return false;

        });
        /*
        Please keep these line to use this code snipet in your project
        Developed by oneTarek http://onetarek.com
        */
        //adding my custom function with Thick box close function tb_close() .
        window.old_tb_remove = window.tb_remove;
        window.tb_remove = function() {
            window.old_tb_remove(); // calls the tb_remove() of the Thickbox plugin
            formfield=null;
        };

        // user inserts file into post. only run custom if user started process using the above process
        // window.send_to_editor(html) is how wp would normally handle the received data

        window.original_send_to_editor = window.send_to_editor;
        window.send_to_editor = function(html){

            if (formfield) {

                fileurl = jQuery('img',html).attr('src');
                jQuery(formfield).val(fileurl);
                jQuery(remove_btn).attr('image-url', fileurl);
                remove_btn.removeClass('hidden');
                remove_btn.show();
                remove_btn.attr('disabled', false);
                jQuery(thumb).show();
                jQuery(thumb).attr('src', fileurl);
                jQuery(thumb).parent().attr('href', fileurl);
                tb_remove();
            } else {
                window.original_send_to_editor(html);
            }
        };
    }

    function check_url() {

        var value    = $(this).val(),
            expression = "^(http[s]?:\\/\\/(www\\.)?|ftp:\\/\\/(www\\.)?|www\\.){1}([0-9A-Za-z-\\.@:%_\+~#=]+)+((\\.[a-zA-Z]{2,3})+)(/(.)*)?(\\?(.)*)?",
            regex    = new RegExp( expression ),
            newvalue = value.replace( regex, '' );


        if ( value.match( regex ) == null ) {
            display_tip_error( $( this ), newvalue );
        }
        return this;
    };

    function display_tip_error( elm, newvalue ) {

        elm.val( newvalue );
        if ( elm.parent().find('.ac_error_tip').size() == 0 ) {
                var offset = elm.position();
                elm.after( '<div class="ac_error_tip">This should be a URL format</div>' );
                $('.ac_error_tip')
                        .css('left', offset.left + elm.width() - ( elm.width() / 2 ) - ( $('.ac_error_tip').width() / 2 ) )
                        .css('top', offset.top + elm.height() )
                        .fadeIn('100');
        }
    };

    /**
     * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
     */
    function initOverrideCss(id) {
        $("#" + id).wrap('<div id="article_wrapper_' + id + '"><div class="container_content_codemirror"></div> </div>');
        $('#article_wrapper_' + id).append('<div class="handle-tooldrag" id="tooldrag' + id + '"></div>');
        $('#section-' + id + ' .controls.full').css('width', '100%');
        var editor = CodeMirror.fromTextArea(document.getElementById(id), {
            extraKeys: {"Ctrl-Space": "autocomplete"},
            lineNumbers: true,
            mode: "css",
            autoRefresh:true,
            lineWrapping: true,
        });
        $("#" + id +" ~ .CodeMirror").css('background', '#f8f8f8');
    }

}) (jQuery);