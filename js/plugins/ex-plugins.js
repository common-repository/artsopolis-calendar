/**
 * Created by tuanphp on 27/10/2014.
 */

/* ========================================================================
 * PYTHWEB: MORE.js v1.1.0
 * Requires jQuery v1.7 or later
 * Requires jQuery blockUI plugin or Later
 *
 * Examples at: http://pythweb.com
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * ======================================================================== */

+function ($) {
    'use strict';

    // More CLASS DEFINITION
    // =========================

    var More = function (element, options) {
        this.$element    = $(element).on('click', $.proxy(this.more, this));
        this.options     = options;
    };

    More.VERSION  = '1.1.0';

    More.DEFAULTS = {
        "sourcetype": "url",
        "holder": "#moreContainer",
        "methodinsert": "after"
    };

    More.prototype.more = function() {
        var _this = this,
            options = this.options;
        this.$holder     = $(this.options.holder);

        var _blockUI = {
            message: this.options.blockuihtml,
            css: this.options.blockuicss
        };

        var _container = window;
        if(this.options.container !== undefined) {
            _container = this.options.container;
        }
        $(_container).block(_blockUI);

        $(document).ajaxStop(function() {
            $(_container).unblock(_blockUI);
        });
        switch(this.options.sourcetype) {
            case 'url':
                this.options.sourcedata = _this.$element.data('sourcedata');
                $.post(this.options.sourcedata, function( data ) {
                    if(data.url === undefined || data.url === "") {
                        console.log('To Using this plugin you must return next page url');
                        return;
                    }
                    _this.$holder[_this.options.methodinsert]( data.html );
                    _this.$element.data('sourcedata', data.url);

                    if(data.have_more !== undefined && data.have_more === false) {
                        _this.$element.hide();
                    }

                    $('body').trigger( 'viewmore', { 'have_more' : data.have_more, 'containerListItems' : _container } );
                }).
                done(function() {

                }).
                fail(function() {

                }).
                always(function(){

                });

                this.$holder[this.options.methodinsert]()
                break;
            default:
                break;
        }
    }

    function Plugin(option) {
        return this.each(function () {
            var $this   = $(this)
            var data    = $this.data('bs.more')
            var options = $.extend({}, More.DEFAULTS, $this.data(), typeof option == 'object' && option)

            if (!data) $this.data('bs.more', (data = new More(this, options)))
        })
    }

    $.fn.more             = Plugin
    $.fn.more.Constructor = More

    $(window).on('load', function () {
        $('[data-ride="ap-more"]').each(function () {
            var $more = $(this)
            Plugin.call($more, $more.data())
        })
    })

}(jQuery);

/*!
 * jQuery blockUI plugin
 * Version 2.66.0-2013.10.09
 * Requires jQuery v1.7 or later
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to Amir-Hossein Sobhi for some excellent contributions!
 */

;(function() {
    /*jshint eqeqeq:false curly:false latedef:false */
    "use strict";

    function setup($) {
        $.fn._fadeIn = $.fn.fadeIn;

        var noOp = $.noop || function() {};

        // this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
        // confusing userAgent strings on Vista)
        var msie = /MSIE/.test(navigator.userAgent);
        var ie6  = /MSIE 6.0/.test(navigator.userAgent) && ! /MSIE 8.0/.test(navigator.userAgent);
        var mode = document.documentMode || 0;
        var setExpr = $.isFunction( document.createElement('div').style.setExpression );

        // global $ methods for blocking/unblocking the entire page
        $.blockUI   = function(opts) { install(window, opts); };
        $.unblockUI = function(opts) { remove(window, opts); };

        // convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
        $.growlUI = function(title, message, timeout, onClose) {
            var $m = $('<div class="growlUI"></div>');
            if (title) $m.append('<h1>'+title+'</h1>');
            if (message) $m.append('<h2>'+message+'</h2>');
            if (timeout === undefined) timeout = 3000;

            // Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
            var callBlock = function(opts) {
                opts = opts || {};

                $.blockUI({
                    message: $m,
                    fadeIn : typeof opts.fadeIn  !== 'undefined' ? opts.fadeIn  : 700,
                    fadeOut: typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
                    timeout: typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
                    centerY: false,
                    showOverlay: false,
                    onUnblock: onClose,
                    css: $.blockUI.defaults.growlCSS
                });
            };

            callBlock();
            var nonmousedOpacity = $m.css('opacity');
            $m.mouseover(function() {
                callBlock({
                    fadeIn: 0,
                    timeout: 30000
                });

                var displayBlock = $('.blockMsg');
                displayBlock.stop(); // cancel fadeout if it has started
                displayBlock.fadeTo(300, 1); // make it easier to read the message by removing transparency
            }).mouseout(function() {
                $('.blockMsg').fadeOut(1000);
            });
            // End konapun additions
        };

        // plugin method for blocking element content
        $.fn.block = function(opts) {
            if ( this[0] === window ) {
                $.blockUI( opts );
                return this;
            }
            var fullOpts = $.extend({}, $.blockUI.defaults, opts || {});
            this.each(function() {
                var $el = $(this);
                if (fullOpts.ignoreIfBlocked && $el.data('blockUI.isBlocked'))
                    return;
                $el.unblock({ fadeOut: 0 });
            });

            return this.each(function() {
                if ($.css(this,'position') == 'static') {
                    this.style.position = 'relative';
                    $(this).data('blockUI.static', true);
                }
                this.style.zoom = 1; // force 'hasLayout' in ie
                install(this, opts);
            });
        };

        // plugin method for unblocking element content
        $.fn.unblock = function(opts) {
            if ( this[0] === window ) {
                $.unblockUI( opts );
                return this;
            }
            return this.each(function() {
                remove(this, opts);
            });
        };

        $.blockUI.version = 2.66; // 2nd generation blocking at no extra cost!

        // override these in your code to change the default behavior and style
        $.blockUI.defaults = {
            // message displayed when blocking (use null for no message)
            message:  '<h1>Please wait...</h1>',

            title: null,		// title string; only used when theme == true
            draggable: true,	// only used when theme == true (requires jquery-ui.js to be loaded)

            theme: false, // set to true to use with jQuery UI themes

            // styles for the message when blocking; if you wish to disable
            // these and use an external stylesheet then do this in your code:
            // $.blockUI.defaults.css = {};
            css: {
                padding:	0,
                margin:		0,
                width:		'30%',
                top:		'40%',
                left:		'35%',
                textAlign:	'center',
                color:		'#000',
                border:		'3px solid #aaa',
                backgroundColor:'#fff',
                cursor:		'wait'
            },

            // minimal style set used when themes are used
            themedCSS: {
                width:	'30%',
                top:	'40%',
                left:	'35%'
            },

            // styles for the overlay
            overlayCSS:  {
                backgroundColor:	'#000',
                opacity:			0.6,
                cursor:				'wait'
            },

            // style to replace wait cursor before unblocking to correct issue
            // of lingering wait cursor
            cursorReset: 'default',

            // styles applied when using $.growlUI
            growlCSS: {
                width:		'350px',
                top:		'10px',
                left:		'',
                right:		'10px',
                border:		'none',
                padding:	'5px',
                opacity:	0.6,
                cursor:		'default',
                color:		'#fff',
                backgroundColor: '#000',
                '-webkit-border-radius':'10px',
                '-moz-border-radius':	'10px',
                'border-radius':		'10px'
            },

            // IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
            // (hat tip to Jorge H. N. de Vasconcelos)
            /*jshint scripturl:true */
            iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',

            // force usage of iframe in non-IE browsers (handy for blocking applets)
            forceIframe: false,

            // z-index for the blocking overlay
            baseZ: 1000,

            // set these to true to have the message automatically centered
            centerX: true, // <-- only effects element blocking (page block controlled via css above)
            centerY: true,

            // allow body element to be stetched in ie6; this makes blocking look better
            // on "short" pages.  disable if you wish to prevent changes to the body height
            allowBodyStretch: true,

            // enable if you want key and mouse events to be disabled for content that is blocked
            bindEvents: true,

            // be default blockUI will supress tab navigation from leaving blocking content
            // (if bindEvents is true)
            constrainTabKey: true,

            // fadeIn time in millis; set to 0 to disable fadeIn on block
            fadeIn:  200,

            // fadeOut time in millis; set to 0 to disable fadeOut on unblock
            fadeOut:  400,

            // time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
            timeout: 0,

            // disable if you don't want to show the overlay
            showOverlay: true,

            // if true, focus will be placed in the first available input field when
            // page blocking
            focusInput: true,

            // elements that can receive focus
            focusableElements: ':input:enabled:visible',

            // suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
            // no longer needed in 2012
            // applyPlatformOpacityRules: true,

            // callback method invoked when fadeIn has completed and blocking message is visible
            onBlock: null,

            // callback method invoked when unblocking has completed; the callback is
            // passed the element that has been unblocked (which is the window object for page
            // blocks) and the options that were passed to the unblock call:
            //	onUnblock(element, options)
            onUnblock: null,

            // callback method invoked when the overlay area is clicked.
            // setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
            onOverlayClick: null,

            // don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
            quirksmodeOffsetHack: 4,

            // class name of the message block
            blockMsgClass: 'blockMsg',

            // if it is already blocked, then ignore it (don't unblock and reblock)
            ignoreIfBlocked: false
        };

        // private data and functions follow...

        var pageBlock = null;
        var pageBlockEls = [];

        function install(el, opts) {
            var css, themedCSS;
            var full = (el == window);
            var msg = (opts && opts.message !== undefined ? opts.message : undefined);
            opts = $.extend({}, $.blockUI.defaults, opts || {});

            if (opts.ignoreIfBlocked && $(el).data('blockUI.isBlocked'))
                return;

            opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
            css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
            if (opts.onOverlayClick)
                opts.overlayCSS.cursor = 'pointer';

            themedCSS = $.extend({}, $.blockUI.defaults.themedCSS, opts.themedCSS || {});
            msg = msg === undefined ? opts.message : msg;

            // remove the current block (if there is one)
            if (full && pageBlock)
                remove(window, {fadeOut:0});

            // if an existing element is being used as the blocking content then we capture
            // its current place in the DOM (and current display style) so we can restore
            // it when we unblock
            if (msg && typeof msg != 'string' && (msg.parentNode || msg.jquery)) {
                var node = msg.jquery ? msg[0] : msg;
                var data = {};
                $(el).data('blockUI.history', data);
                data.el = node;
                data.parent = node.parentNode;
                data.display = node.style.display;
                data.position = node.style.position;
                if (data.parent)
                    data.parent.removeChild(node);
            }

            $(el).data('blockUI.onUnblock', opts.onUnblock);
            var z = opts.baseZ;

            // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
            // layer1 is the iframe layer which is used to supress bleed through of underlying content
            // layer2 is the overlay layer which has opacity and a wait cursor (by default)
            // layer3 is the message content that is displayed while blocking
            var lyr1, lyr2, lyr3, s;
            if (msie || opts.forceIframe)
                lyr1 = $('<iframe class="blockUI" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="'+opts.iframeSrc+'"></iframe>');
            else
                lyr1 = $('<div class="blockUI" style="display:none"></div>');

            if (opts.theme)
                lyr2 = $('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:'+ (z++) +';display:none"></div>');
            else
                lyr2 = $('<div class="blockUI blockOverlay" style="z-index:'+ (z++) +';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');

            if (opts.theme && full) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:'+(z+10)+';display:none;position:fixed">';
                if ( opts.title ) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(opts.title || '&nbsp;')+'</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            }
            else if (opts.theme) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:'+(z+10)+';display:none;position:absolute">';
                if ( opts.title ) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">'+(opts.title || '&nbsp;')+'</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            }
            else if (full) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:'+(z+10)+';display:none;position:fixed"></div>';
            }
            else {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:'+(z+10)+';display:none;position:absolute"></div>';
            }
            lyr3 = $(s);

            // if we have a message, style it
            if (msg) {
                if (opts.theme) {
                    lyr3.css(themedCSS);
                    lyr3.addClass('ui-widget-content');
                }
                else
                    lyr3.css(css);
            }

            // style the overlay
            if (!opts.theme /*&& (!opts.applyPlatformOpacityRules)*/)
                lyr2.css(opts.overlayCSS);
            lyr2.css('position', full ? 'fixed' : 'absolute');

            // make iframe layer transparent in IE
            if (msie || opts.forceIframe)
                lyr1.css('opacity',0.0);

            //$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
            var layers = [lyr1,lyr2,lyr3], $par = full ? $('body') : $(el);
            $.each(layers, function() {
                this.appendTo($par);
            });

            if (opts.theme && opts.draggable && $.fn.draggable) {
                lyr3.draggable({
                    handle: '.ui-dialog-titlebar',
                    cancel: 'li'
                });
            }

            // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
            var expr = setExpr && (!$.support.boxModel || $('object,embed', full ? null : el).length > 0);
            if (ie6 || expr) {
                // give body 100% height
                if (full && opts.allowBodyStretch && $.support.boxModel)
                    $('html,body').css('height','100%');

                // fix ie6 issue when blocked element has a border width
                if ((ie6 || !$.support.boxModel) && !full) {
                    var t = sz(el,'borderTopWidth'), l = sz(el,'borderLeftWidth');
                    var fixT = t ? '(0 - '+t+')' : 0;
                    var fixL = l ? '(0 - '+l+')' : 0;
                }

                // simulate fixed position
                $.each(layers, function(i,o) {
                    var s = o[0].style;
                    s.position = 'absolute';
                    if (i < 2) {
                        if (full)
                            s.setExpression('height','Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:'+opts.quirksmodeOffsetHack+') + "px"');
                        else
                            s.setExpression('height','this.parentNode.offsetHeight + "px"');
                        if (full)
                            s.setExpression('width','jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"');
                        else
                            s.setExpression('width','this.parentNode.offsetWidth + "px"');
                        if (fixL) s.setExpression('left', fixL);
                        if (fixT) s.setExpression('top', fixT);
                    }
                    else if (opts.centerY) {
                        if (full) s.setExpression('top','(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                        s.marginTop = 0;
                    }
                    else if (!opts.centerY && full) {
                        var top = (opts.css && opts.css.top) ? parseInt(opts.css.top, 10) : 0;
                        var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + '+top+') + "px"';
                        s.setExpression('top',expression);
                    }
                });
            }

            // show the message
            if (msg) {
                if (opts.theme)
                    lyr3.find('.ui-widget-content').append(msg);
                else
                    lyr3.append(msg);
                if (msg.jquery || msg.nodeType)
                    $(msg).show();
            }

            if ((msie || opts.forceIframe) && opts.showOverlay)
                lyr1.show(); // opacity is zero
            if (opts.fadeIn) {
                var cb = opts.onBlock ? opts.onBlock : noOp;
                var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
                var cb2 = msg ? cb : noOp;
                if (opts.showOverlay)
                    lyr2._fadeIn(opts.fadeIn, cb1);
                if (msg)
                    lyr3._fadeIn(opts.fadeIn, cb2);
            }
            else {
                if (opts.showOverlay)
                    lyr2.show();
                if (msg)
                    lyr3.show();
                if (opts.onBlock)
                    opts.onBlock();
            }

            // bind key and mouse events
            bind(1, el, opts);

            if (full) {
                pageBlock = lyr3[0];
                pageBlockEls = $(opts.focusableElements,pageBlock);
                if (opts.focusInput)
                    setTimeout(focus, 20);
            }
            else
                center(lyr3[0], opts.centerX, opts.centerY);

            if (opts.timeout) {
                // auto-unblock
                var to = setTimeout(function() {
                    if (full)
                        $.unblockUI(opts);
                    else
                        $(el).unblock(opts);
                }, opts.timeout);
                $(el).data('blockUI.timeout', to);
            }
        }

        // remove the block
        function remove(el, opts) {
            var count;
            var full = (el == window);
            var $el = $(el);
            var data = $el.data('blockUI.history');
            var to = $el.data('blockUI.timeout');
            if (to) {
                clearTimeout(to);
                $el.removeData('blockUI.timeout');
            }
            opts = $.extend({}, $.blockUI.defaults, opts || {});
            bind(0, el, opts); // unbind events

            if (opts.onUnblock === null) {
                opts.onUnblock = $el.data('blockUI.onUnblock');
                $el.removeData('blockUI.onUnblock');
            }

            var els;

            /**
             * @ticket #14336
             * */
            var currentEL;

            if (full) {
                els = $('body').children().filter('.blockUI').add('body > .blockUI');
                currentEL = $('body');
            }
            else {
                els = $el.find('>.blockUI');
                currentEL = $el;
            }

            /**
             * @vulh fix can not find blockUI class
             * @ticket #14336
             * */
            if (!els.length) {
                els = currentEL.find('.blockUI');
            }

            // fix cursor issue
            if ( opts.cursorReset ) {
                if ( els.length > 1 )
                    els[1].style.cursor = opts.cursorReset;
                if ( els.length > 2 )
                    els[2].style.cursor = opts.cursorReset;
            }

            if (full)
                pageBlock = pageBlockEls = null;

            if (opts.fadeOut) {
                count = els.length;
                els.stop().fadeOut(opts.fadeOut, function() {
                    if ( --count === 0)
                        reset(els,data,opts,el);
                });
            }
            else
                reset(els, data, opts, el);
        }

        // move blocking element back into the DOM where it started
        function reset(els,data,opts,el) {
            var $el = $(el);
            if ( $el.data('blockUI.isBlocked') )
                return;

            els.each(function(i,o) {
                // remove via DOM calls so we don't lose event handlers
                if (this.parentNode)
                    this.parentNode.removeChild(this);
            });

            if (data && data.el) {
                data.el.style.display = data.display;
                data.el.style.position = data.position;
                if (data.parent)
                    data.parent.appendChild(data.el);
                $el.removeData('blockUI.history');
            }

            if ($el.data('blockUI.static')) {
                $el.css('position', 'static'); // #22
            }

            if (typeof opts.onUnblock == 'function')
                opts.onUnblock(el,opts);

            // fix issue in Safari 6 where block artifacts remain until reflow
            var body = $(document.body), w = body.width(), cssW = body[0].style.width;
            body.width(w-1).width(w);
            body[0].style.width = cssW;
        }

        // bind/unbind the handler
        function bind(b, el, opts) {
            var full = el == window, $el = $(el);

            // don't bother unbinding if there is nothing to unbind
            if (!b && (full && !pageBlock || !full && !$el.data('blockUI.isBlocked')))
                return;

            $el.data('blockUI.isBlocked', b);

            // don't bind events when overlay is not in use or if bindEvents is false
            if (!full || !opts.bindEvents || (b && !opts.showOverlay))
                return;

            // bind anchors and inputs for mouse and key events
            var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
            if (b)
                $(document).bind(events, opts, handler);
            else
                $(document).unbind(events, handler);

            // former impl...
            //		var $e = $('a,:input');
            //		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
        }

        // event handler to suppress keyboard/mouse events when blocking
        function handler(e) {
            // allow tab navigation (conditionally)
            if (e.type === 'keydown' && e.keyCode && e.keyCode == 9) {
                if (pageBlock && e.data.constrainTabKey) {
                    var els = pageBlockEls;
                    var fwd = !e.shiftKey && e.target === els[els.length-1];
                    var back = e.shiftKey && e.target === els[0];
                    if (fwd || back) {
                        setTimeout(function(){focus(back);},10);
                        return false;
                    }
                }
            }
            var opts = e.data;
            var target = $(e.target);
            if (target.hasClass('blockOverlay') && opts.onOverlayClick)
                opts.onOverlayClick(e);

            // allow events within the message content
            if (target.parents('div.' + opts.blockMsgClass).length > 0)
                return true;

            // allow events for content that is not being blocked
            return target.parents().children().filter('div.blockUI').length === 0;
        }

        function focus(back) {
            if (!pageBlockEls)
                return;
            var e = pageBlockEls[back===true ? pageBlockEls.length-1 : 0];
            if (e)
                e.focus();
        }

        function center(el, x, y) {
            var p = el.parentNode, s = el.style;
            var l = ((p.offsetWidth - el.offsetWidth)/2) - sz(p,'borderLeftWidth');
            var t = ((p.offsetHeight - el.offsetHeight)/2) - sz(p,'borderTopWidth');
            if (x) s.left = l > 0 ? (l+'px') : '0';
            if (y) s.top  = t > 0 ? (t+'px') : '0';
        }

        function sz(el, p) {
            return parseInt($.css(el,p),10)||0;
        }

    }


    /*global define:true */
    if (typeof define === 'function' && define.amd && define.amd.jQuery) {
        define(['jquery'], setup);
    } else {
        setup(jQuery);
    }

})();

/*!
 * Retina.js v1.3.0
 *
 * Copyright 2014 Imulus, LLC
 * Released under the MIT license
 *
 * Retina.js is an open source script that makes it easy to serve
 * high-resolution images to devices with retina displays.
 */

(function() {
    var root = (typeof exports === 'undefined' ? window : exports);
    var config = {
        // An option to choose a suffix for 2x images
        retinaImageSuffix : '@2x',

        // Ensure Content-Type is an image before trying to load @2x image
        // https://github.com/imulus/retinajs/pull/45)
        check_mime_type: true,

        // Resize high-resolution images to original image's pixel dimensions
        // https://github.com/imulus/retinajs/issues/8
        force_original_dimensions: false,
        isApplyRetina: function(img) {

            // Allway load 2x on retina screen for max sportlight
            var isSportlight = jQuery(img).closest('.main-slider').length;

            return false;
        }
    };

    function Retina() {}

    root.Retina = Retina;

    Retina.configure = function(options) {
        if (options === null) {
            options = {};
        }

        for (var prop in options) {
            if (options.hasOwnProperty(prop)) {
                config[prop] = options[prop];
            }
        }
    };

    Retina.init = function(context) {
        if (context === null) {
            context = root;
        }

        var existing_onload = context.onload || function(){};

        context.onload = function() {
            var images = document.getElementsByTagName('img'), retinaImages = [], i, image;
            for (i = 0; i < images.length; i += 1) {
                image = images[i];
                if (root.Retina.isApplyRetina(image)) {
                    retinaImages.push(new RetinaImage(image));
                }
            }
            existing_onload();
        };
    };

    Retina.isApplyRetina = typeof config['isApplyRetina'] !== 'undefined' ? config['isApplyRetina'] :  function (image) {
        return !!!image.getAttributeNode('data-no-retina');
    };

    Retina.isRetina = typeof config['isRetina'] !== 'undefined' ? config['isRetina'] : function(){
        var mediaQuery = '(-webkit-min-device-pixel-ratio: 1.5), (min--moz-device-pixel-ratio: 1.5), (-o-min-device-pixel-ratio: 3/2), (min-resolution: 1.5dppx)';

        if (root.devicePixelRatio > 1) {
            return true;
        }

        if (root.matchMedia && root.matchMedia(mediaQuery).matches) {
            return true;
        }

        return false;
    };


    var regexMatch = /\.\w+$/;
    function suffixReplace (match) {
        return config.retinaImageSuffix + match;
    }

    function RetinaImagePath(path, at_2x_path) {
        this.path = path || '';
        if (typeof at_2x_path !== 'undefined' && at_2x_path !== null) {
            this.at_2x_path = at_2x_path;
            this.perform_check = false;
        } else {
            if (undefined !== document.createElement) {
                var locationObject = document.createElement('a');
                locationObject.href = this.path;
                locationObject.pathname = locationObject.pathname.replace(regexMatch, suffixReplace);
                this.at_2x_path = locationObject.href;
            } else {
                var parts = this.path.split('?');
                parts[0] = parts[0].replace(regexMatch, suffixReplace);
                this.at_2x_path = parts.join('?');
            }
            this.perform_check = true;
        }
    }

    root.RetinaImagePath = RetinaImagePath;

    RetinaImagePath.confirmed_paths = [];

    RetinaImagePath.prototype.is_external = function() {
        return !!(this.path.match(/^https?\:/i) && !this.path.match('//' + document.domain) );
    };

    RetinaImagePath.prototype.check_2x_variant = function(callback) {
        var http, that = this;
        if (this.is_external()) {
            return callback(false);
        } else if (!this.perform_check && typeof this.at_2x_path !== 'undefined' && this.at_2x_path !== null) {
            return callback(true);
        } else if (this.at_2x_path in RetinaImagePath.confirmed_paths) {
            return callback(true);
        } else {
            http = new XMLHttpRequest();
            http.open('HEAD', this.at_2x_path);
            http.onreadystatechange = function() {
                if (http.readyState !== 4) {
                    return callback(false);
                }

                if (http.status >= 200 && http.status <= 399) {
                    if (config.check_mime_type) {
                        var type = http.getResponseHeader('Content-Type');
                        if (type === null || !type.match(/^image/i)) {
                            return callback(false);
                        }
                    }

                    RetinaImagePath.confirmed_paths.push(that.at_2x_path);
                    return callback(true);
                } else {
                    return callback(false);
                }
            };
            http.send();
        }
    };


    function RetinaImage(el) {
        this.el = el;
        this.path = new RetinaImagePath(this.el.getAttribute('src'), this.el.getAttribute('data-at2x'));
        var that = this;
        this.path.check_2x_variant(function(hasVariant) {
            if (hasVariant) {
                that.swap();
            }
        });
    }

    root.RetinaImage = RetinaImage;

    RetinaImage.prototype.swap = function(path) {
        if (typeof path === 'undefined') {
            path = this.path.at_2x_path;
        }

        var that = this;
        function load() {
            if (! that.el.complete) {
                setTimeout(load, 5);
            } else {
                if (config.force_original_dimensions) {
                    that.el.setAttribute('width', that.el.offsetWidth);
                    that.el.setAttribute('height', that.el.offsetHeight);
                }

                that.el.setAttribute('src', path);
            }
        }
        load();
    };

    // ThienLD : auto trigger Retina init
    jQuery(document).ready(function () {
        if (typeof Retina !== 'undefined' && Retina.isRetina()) {
            Retina.init(null);
        }
    });

})();

/**
 * simplePagination.js v1.6
 * A simple jQuery pagination plugin.
 * http://flaviusmatis.github.com/simplePagination.js/
 *
 * Copyright 2012, Flavius Matis
 * Released under the MIT license.
 * http://flaviusmatis.github.com/license.html
 */

(function($){

    var methods = {
        init: function(options) {
            var o = $.extend({
                items: 1,
                itemsOnPage: 1,
                pages: 0,
                displayedPages: 5,
                edges: 2,
                currentPage: 0,
                hrefTextPrefix: '#page-',
                hrefTextSuffix: '',
                prevText: 'Prev',
                nextText: 'Next',
                ellipseText: '&hellip;',
                cssStyle: 'light-theme',
                labelMap: [],
                selectOnClick: true,
                nextAtFront: false,
                invertPageOrder: false,
                useStartEdge : true,
                useEndEdge : true,
                onPageClick: function(pageNumber, event) {
                    // Callback triggered when a page is clicked
                    // Page number is given as an optional parameter
                },
                onInit: function() {
                    // Callback triggered immediately after initialization
                }
            }, options || {});

            var self = this;

            o.pages = o.pages ? o.pages : Math.ceil(o.items / o.itemsOnPage) ? Math.ceil(o.items / o.itemsOnPage) : 1;
            if (o.currentPage)
                o.currentPage = o.currentPage - 1;
            else
                o.currentPage = !o.invertPageOrder ? 0 : o.pages - 1;
            o.halfDisplayed = o.displayedPages / 2;

            this.each(function() {
                self.addClass(o.cssStyle + ' simple-pagination').data('pagination', o);
                methods._draw.call(self);
            });

            o.onInit();

            return this;
        },

        selectPage: function(page) {
            methods._selectPage.call(this, page - 1);
            return this;
        },

        prevPage: function() {
            var o = this.data('pagination');
            if (!o.invertPageOrder) {
                if (o.currentPage > 0) {
                    methods._selectPage.call(this, o.currentPage - 1);
                }
            } else {
                if (o.currentPage < o.pages - 1) {
                    methods._selectPage.call(this, o.currentPage + 1);
                }
            }
            return this;
        },

        nextPage: function() {
            var o = this.data('pagination');
            if (!o.invertPageOrder) {
                if (o.currentPage < o.pages - 1) {
                    methods._selectPage.call(this, o.currentPage + 1);
                }
            } else {
                if (o.currentPage > 0) {
                    methods._selectPage.call(this, o.currentPage - 1);
                }
            }
            return this;
        },

        getPagesCount: function() {
            return this.data('pagination').pages;
        },

        getCurrentPage: function () {
            return this.data('pagination').currentPage + 1;
        },

        destroy: function(){
            this.empty();
            return this;
        },

        drawPage: function (page) {
            var o = this.data('pagination');
            o.currentPage = page - 1;
            this.data('pagination', o);
            methods._draw.call(this);
            return this;
        },

        redraw: function(){
            methods._draw.call(this);
            return this;
        },

        disable: function(){
            var o = this.data('pagination');
            o.disabled = true;
            this.data('pagination', o);
            methods._draw.call(this);
            return this;
        },

        enable: function(){
            var o = this.data('pagination');
            o.disabled = false;
            this.data('pagination', o);
            methods._draw.call(this);
            return this;
        },

        updateItems: function (newItems) {
            var o = this.data('pagination');
            o.items = newItems;
            o.pages = methods._getPages(o);
            this.data('pagination', o);
            methods._draw.call(this);
        },

        updateItemsOnPage: function (itemsOnPage) {
            var o = this.data('pagination');
            o.itemsOnPage = itemsOnPage;
            o.pages = methods._getPages(o);
            this.data('pagination', o);
            methods._selectPage.call(this, 0);
            return this;
        },

        _draw: function() {
            var	o = this.data('pagination'),
                interval = methods._getInterval(o),
                i,
                tagName;

            methods.destroy.call(this);

            tagName = (typeof this.prop === 'function') ? this.prop('tagName') : this.attr('tagName');

            var $panel = this;
            if(o.heading) {
                $(o.heading).appendTo(this);
            }


            if(o.ul === undefined) {
                o.ul = "ul";
            }

            var _ulclass = o.ulclass !== undefined ? "class='" + o.ulclass + "'" : '';
            $panel = $('<' + o.ul +' '+ _ulclass + ' >' + '</' + o.ul + '>').appendTo(this);

            // Generate Prev link
            if (o.prevText) {
                methods._appendItem.call(this, !o.invertPageOrder ? o.currentPage - 1 : o.currentPage + 1, {text: o.prevText, classes: 'prev'});
            }

            // Generate Next link (if option set for at front)
            if (o.nextText && o.nextAtFront) {
                methods._appendItem.call(this, !o.invertPageOrder ? o.currentPage + 1 : o.currentPage - 1, {text: o.nextText, classes: 'next'});
            }

            // Generate start edges
            if (!o.invertPageOrder) {
                if (interval.start > 0 && o.edges > 0) {
                    if(o.useStartEdge) {
                        var end = Math.min(o.edges, interval.start);
                        for (i = 0; i < end; i++) {
                            methods._appendItem.call(this, i);
                        }
                    }
                    if (o.edges < interval.start && (interval.start - o.edges != 1)) {
                        if(o.ellipse) {
                            $panel.append(o.ellipse);
                        }
                        else {
                            $panel.append('<li class="disabled"><span class="ellipse">' + o.ellipseText + '</span></li>');
                        }

                    } else if (interval.start - o.edges == 1) {
                        methods._appendItem.call(this, o.edges);
                    }
                }
            } else {
                if (interval.end < o.pages && o.edges > 0) {
                    if(o.useStartEdge) {
                        var begin = Math.max(o.pages - o.edges, interval.end);
                        for (i = o.pages - 1; i >= begin; i--) {
                            methods._appendItem.call(this, i);
                        }
                    }

                    if (o.pages - o.edges > interval.end && (o.pages - o.edges - interval.end != 1)) {
                        if(o.ellipse) {
                            $panel.append(o.ellipse);
                        }
                        else {
                            $panel.append('<li class="disabled"><span class="ellipse">' + o.ellipseText + '</span></li>');
                        }

                    } else if (o.pages - o.edges - interval.end == 1) {
                        methods._appendItem.call(this, interval.end);
                    }
                }
            }

            // Generate interval links
            if (!o.invertPageOrder) {
                for (i = interval.start; i < interval.end; i++) {
                    methods._appendItem.call(this, i);
                }
            } else {
                for (i = interval.end - 1; i >= interval.start; i--) {
                    methods._appendItem.call(this, i);
                }
            }

            // Generate end edges
            if (!o.invertPageOrder) {
                if (interval.end < o.pages && o.edges > 0) {
                    if (o.pages - o.edges > interval.end && (o.pages - o.edges - interval.end != 1)) {
                        if(o.ellipse !== undefined) {
                            $panel.append(o.ellipse);
                        }
                        else {
                            $panel.append('<li class="disabled"><span class="ellipse">' + o.ellipseText + '</span></li>');
                        }

                    } else if (o.pages - o.edges - interval.end == 1) {
                        methods._appendItem.call(this, interval.end);
                    }
                    if(o.useEndEdge) {
                        var begin = Math.max(o.pages - o.edges, interval.end);
                        for (i = begin; i < o.pages; i++) {
                            methods._appendItem.call(this, i);
                        }
                    }
                }
            } else {
                if (interval.start > 0 && o.edges > 0) {
                    if (o.edges < interval.start && (interval.start - o.edges != 1)) {
                        if(o.ellipse !== undefined) {
                            $panel.append(o.ellipse);
                        }
                        else {
                            $panel.append('<li class="disabled"><span class="ellipse">' + o.ellipseText + '</span></li>');
                        }

                    } else if (interval.start - o.edges == 1) {
                        methods._appendItem.call(this, o.edges);
                    }

                    if(o.useEndEdge) {
                        var end = Math.min(o.edges, interval.start);
                        for (i = end - 1; i >= 0; i--) {
                            methods._appendItem.call(this, i);
                        }
                    }
                }
            }

            // Generate Next link (unless option is set for at front)
            if (o.nextText && !o.nextAtFront) {
                methods._appendItem.call(this, !o.invertPageOrder ? o.currentPage + 1 : o.currentPage - 1, {text: o.nextText, classes: 'next'});
            }
        },

        _getPages: function(o) {
            var pages = Math.ceil(o.items / o.itemsOnPage);
            return pages || 1;
        },

        _getInterval: function(o) {
            return {
                start: Math.ceil(o.currentPage > o.halfDisplayed ? Math.max(Math.min(o.currentPage - o.halfDisplayed, (o.pages - o.displayedPages)), 0) : 0),
                end: Math.ceil(o.currentPage > o.halfDisplayed ? Math.min(o.currentPage + o.halfDisplayed, o.pages) : Math.min(o.displayedPages, o.pages))
            };
        },

        _appendItem: function(pageIndex, opts) {
            var self = this, options, $link, o = self.data('pagination'), $linkWrapper = $('<li></li>'), $ul = self.find(o.ul);

            pageIndex = pageIndex < 0 ? 0 : (pageIndex < o.pages ? pageIndex : o.pages - 1);

            options = {
                text: pageIndex + 1,
                classes: ''
            };

            if (o.labelMap.length && o.labelMap[pageIndex]) {
                options.text = o.labelMap[pageIndex];
            }

            options = $.extend(options, opts || {});

            if (pageIndex == o.currentPage || o.disabled) {
                if (o.disabled) {
                    $linkWrapper.addClass('disabled');
                } else {
                    $linkWrapper.addClass('active');
                }
                if(opts !== undefined) {
                    if(opts.classes === 'prev') {
                        $link = $('<a class="page-link">' + (options.text) + '</a>');
                    }
                    else if(opts.classes === 'next') {
                        $link = $('<a class="page-link">' + (options.text) + '</a>');
                    }
                }
                else {
                    $link = $('<a class="active">' + (options.text) + '</a>');
                }

            } else {
                $link = $('<a href="' + o.hrefTextPrefix + (pageIndex + 1) + o.hrefTextSuffix + '" class="page-link">' + (options.text) + '</a>');
                $link.click(function(event){
                    return methods._selectPage.call(self, pageIndex, event);
                });
            }

            if (options.classes && opts.classes !== 'prev' && opts.classes !== 'next') {
                $link.addClass(options.classes);
            }

            $linkWrapper.append($link);

            if ($ul.length) {
                $ul.append($linkWrapper);
            } else {
                self.append($linkWrapper);
            }
        },

        _selectPage: function(pageIndex, event) {
            var o = this.data('pagination');
            o.currentPage = pageIndex;
            if (o.selectOnClick) {
                methods._draw.call(this);
            }
            return o.onPageClick(pageIndex + 1, event);
        }

    };

    $.fn.pagination = function(method) {

        console.log('z');
        // Method calling logic
        if (methods[method] && method.charAt(0) != '_') {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.pagination');
        }

    };

})(jQuery);

/**
 * alertify
 * An unobtrusive customizable JavaScript notification system
 *
 * @author Fabien Doiron <fabien.doiron@gmail.com>
 * @copyright Fabien Doiron 2013
 * @license MIT <http://opensource.org/licenses/mit-license.php>
 * @link http://fabien-d.github.com/alertify.js/
 * @module alertify
 * @version 0.3.11
 */
(function (global, undefined) {
    "use strict";
    var document = global.document,
        Alertify;

    Alertify = function () {

        var _alertify = {},
            dialogs   = {},
            isopen    = false,
            keys      = { ENTER: 13, ESC: 27, SPACE: 32 },
            queue     = [],
            $, btnCancel, btnClose, btnOK, btnReset, btnResetBack, btnFocus, elCallee, elCover, elDialog, elLog, form, input, getTransitionEvent;

        /**
         * Markup pieces
         * @type {Object}
         */
        dialogs = {
            buttons : {
                holder : "<nav class=\"alertify-buttons\">{{buttons}}</nav>",
                submit : "<button type=\"submit\" class=\"alertify-button alertify-button-ok\" id=\"alertify-ok\">{{ok}}</button>",
                ok     : "<button class=\"alertify-button alertify-button-ok\" id=\"alertify-ok\">{{ok}}</button>",
                cancel : "<button class=\"alertify-button alertify-button-cancel\" id=\"alertify-cancel\">{{cancel}}</button>"
            },
            input   : "<div class=\"alertify-text-wrapper\"><input type=\"text\" class=\"alertify-text\" id=\"alertify-text\"></div>",
            message : "<p class=\"alertify-message\">{{message}}</p>",
            log     : "<article class=\"alertify-log{{class}}\">{{message}}</article>"
        };

        /**
         * Return the proper transitionend event
         * @return {String}    Transition type string
         */
        getTransitionEvent = function () {
            var t,
                type,
                supported   = false,
                el          = document.createElement("fakeelement"),
                transitions = {
                    "WebkitTransition" : "webkitTransitionEnd",
                    "MozTransition"    : "transitionend",
                    "OTransition"      : "otransitionend",
                    "transition"       : "transitionend"
                };

            for (t in transitions) {
                if (el.style[t] !== undefined) {
                    type      = transitions[t];
                    supported = true;
                    break;
                }
            }

            return {
                type      : type,
                supported : supported
            };
        };

        /**
         * Shorthand for document.getElementById()
         *
         * @param  {String} id    A specific element ID
         * @return {Object}       HTML element
         */
        $ = function (id) {
            return document.getElementById(id);
        };

        /**
         * Alertify private object
         * @type {Object}
         */
        _alertify = {

            /**
             * Labels object
             * @type {Object}
             */
            labels : {
                ok     : "OK",
                cancel : "Cancel"
            },

            /**
             * Delay number
             * @type {Number}
             */
            delay : 5000,

            /**
             * Whether buttons are reversed (default is secondary/primary)
             * @type {Boolean}
             */
            buttonReverse : false,

            /**
             * Which button should be focused by default
             * @type {String}	"ok" (default), "cancel", or "none"
             */
            buttonFocus : "ok",

            /**
             * Set the transition event on load
             * @type {[type]}
             */
            transition : undefined,

            /**
             * Set the proper button click events
             *
             * @param {Function} fn    [Optional] Callback function
             *
             * @return {undefined}
             */
            addListeners : function (fn) {
                var hasOK     = (typeof btnOK !== "undefined"),
                    hasCancel = (typeof btnCancel !== "undefined"),
                    hasClose = (typeof btnClose !== "undefined"),
                    hasInput  = (typeof input !== "undefined"),
                    val       = "",
                    self      = this,
                    ok, cancel, common, key, reset;

                // ok event handler
                ok = function (event) {
                    if (typeof event.preventDefault !== "undefined") event.preventDefault();
                    common(event);
                    if (typeof input !== "undefined") val = input.value;
                    if (typeof fn === "function") {
                        if (typeof input !== "undefined") {
                            fn(true, val);
                        }
                        else fn(true);
                    }
                    return false;
                };

                // cancel event handler
                cancel = function (event) {
                    if (typeof event.preventDefault !== "undefined") event.preventDefault();
                    common(event);
                    if (typeof fn === "function") fn(false);
                    return false;
                };

                // common event handler (keyup, ok and cancel)
                common = function (event) {
                    self.hide();
                    self.unbind(document.body, "keyup", key);
                    self.unbind(btnReset, "focus", reset);
                    if (hasOK) self.unbind(btnOK, "click", ok);
                    if (hasCancel) self.unbind(btnCancel, "click", cancel);
                };

                // keyup handler
                key = function (event) {
                    var keyCode = event.keyCode;
                    if ((keyCode === keys.SPACE && !hasInput) || (hasInput && keyCode === keys.ENTER)) ok(event);
                    if (keyCode === keys.ESC && hasCancel) cancel(event);
                };

                // reset focus to first item in the dialog
                reset = function (event) {
                    if (hasInput) input.focus();
                    else if (!hasCancel || self.buttonReverse) btnOK.focus();
                    else btnCancel.focus();
                };

                // handle reset focus link
                // this ensures that the keyboard focus does not
                // ever leave the dialog box until an action has
                // been taken
                this.bind(btnReset, "focus", reset);
                this.bind(btnResetBack, "focus", reset);
                // handle OK click
                if (hasOK) this.bind(btnOK, "click", ok);

                // handle Close click
                if (hasClose) this.bind(btnClose, "click", cancel);

                // handle Cancel click
                if (hasCancel) this.bind(btnCancel, "click", cancel);
                // listen for keys, Cancel => ESC
                this.bind(document.body, "keyup", key);
                if (!this.transition.supported) {
                    this.setFocus();
                }
            },

            /**
             * Bind events to elements
             *
             * @param  {Object}   el       HTML Object
             * @param  {Event}    event    Event to attach to element
             * @param  {Function} fn       Callback function
             *
             * @return {undefined}
             */
            bind : function (el, event, fn) {
                if (typeof el.addEventListener === "function") {
                    el.addEventListener(event, fn, false);
                } else if (el.attachEvent) {
                    el.attachEvent("on" + event, fn);
                }
            },

            /**
             * Use alertify as the global error handler (using window.onerror)
             *
             * @return {boolean} success
             */
            handleErrors : function () {
                if (typeof global.onerror !== "undefined") {
                    var self = this;
                    global.onerror = function (msg, url, line) {
                        self.error("[" + msg + " on line " + line + " of " + url + "]", 0);
                    };
                    return true;
                } else {
                    return false;
                }
            },

            /**
             * Append button HTML strings
             *
             * @param {String} secondary    The secondary button HTML string
             * @param {String} primary      The primary button HTML string
             *
             * @return {String}             The appended button HTML strings
             */
            appendButtons : function (secondary, primary) {
                return this.buttonReverse ? primary + secondary : secondary + primary;
            },

            /**
             * Build the proper message box
             *
             * @param  {Object} item    Current object in the queue
             *
             * @return {String}         An HTML string of the message box
             */
            build : function (item) {
                var html    = "",
                    type    = item.type,
                    message = item.message,
                    css     = item.cssClass || "";

                html += "<div class=\"alertify-dialog\">";
                html += "<a id=\"alertify-resetFocusBack\" class=\"alertify-resetFocus\" href=\"#\">Reset Focus</a>";

                if (_alertify.buttonFocus === "none") html += "<a href=\"#\" id=\"alertify-noneFocus\" class=\"alertify-hidden\"></a>";

                // doens't require an actual form
                if (type === "prompt") html += "<div id=\"alertify-form\">";

                html += "<article class=\"alertify-inner\">";
                html += dialogs.message.replace("{{message}}", message);

                if (type === "prompt") html += dialogs.input;

                html += dialogs.buttons.holder;
                html += "</article>";

                if (type === "prompt") html += "</div>";

                html += "<a id=\"alertify-resetFocus\" class=\"alertify-resetFocus\" href=\"#\">Reset Focus</a>";
                html += "<div id='alertify-close' class=\"alertify-close\"><i class=\"fa fa-times fa-lg\"></i></div>";
                html += "</div>";

                switch (type) {
                    case "confirm":
                        html = html.replace("{{buttons}}", this.appendButtons(dialogs.buttons.cancel, dialogs.buttons.ok));
                        html = html.replace("{{ok}}", this.labels.ok).replace("{{cancel}}", this.labels.cancel);
                        break;
                    case "prompt":
                        html = html.replace("{{buttons}}", this.appendButtons(dialogs.buttons.cancel, dialogs.buttons.submit));
                        html = html.replace("{{ok}}", this.labels.ok).replace("{{cancel}}", this.labels.cancel);
                        break;
                    case "alert":
                        html = html.replace("{{buttons}}", dialogs.buttons.ok);
                        html = html.replace("{{ok}}", this.labels.ok);
                        break;
                    default:
                        break;
                }

                elDialog.className = "alertify alertify-" + type + " " + css;
                elCover.className  = "alertify-cover";
                return html;
            },

            /**
             * Close the log messages
             *
             * @param  {Object} elem    HTML Element of log message to close
             * @param  {Number} wait    [optional] Time (in ms) to wait before automatically hiding the message, if 0 never hide
             *
             * @return {undefined}
             */
            close : function (elem, wait) {
                // Unary Plus: +"2" === 2
                var timer = (wait && !isNaN(wait)) ? +wait : this.delay,
                    self  = this,
                    hideElement, transitionDone;

                // set click event on log messages
                this.bind(elem, "click", function () {
                    hideElement(elem);
                });
                // Hide the dialog box after transition
                // This ensure it doens't block any element from being clicked
                transitionDone = function (event) {
                    event.stopPropagation();
                    // unbind event so function only gets called once
                    self.unbind(this, self.transition.type, transitionDone);
                    // remove log message
                    elLog.removeChild(this);
                    if (!elLog.hasChildNodes()) elLog.className += " alertify-logs-hidden";
                };
                // this sets the hide class to transition out
                // or removes the child if css transitions aren't supported
                hideElement = function (el) {
                    // ensure element exists
                    if (typeof el !== "undefined" && el.parentNode === elLog) {
                        // whether CSS transition exists
                        if (self.transition.supported) {
                            self.bind(el, self.transition.type, transitionDone);
                            el.className += " alertify-log-hide";
                        } else {
                            elLog.removeChild(el);
                            if (!elLog.hasChildNodes()) elLog.className += " alertify-logs-hidden";
                        }
                    }
                };
                // never close (until click) if wait is set to 0
                if (wait === 0) return;
                // set timeout to auto close the log message
                setTimeout(function () { hideElement(elem); }, timer);
            },

            /**
             * Create a dialog box
             *
             * @param  {String}   message        The message passed from the callee
             * @param  {String}   type           Type of dialog to create
             * @param  {Function} fn             [Optional] Callback function
             * @param  {String}   placeholder    [Optional] Default value for prompt input field
             * @param  {String}   cssClass       [Optional] Class(es) to append to dialog box
             *
             * @return {Object}
             */
            dialog : function (message, type, fn, placeholder, cssClass) {
                // set the current active element
                // this allows the keyboard focus to be resetted
                // after the dialog box is closed
                elCallee = document.activeElement;
                // check to ensure the alertify dialog element
                // has been successfully created
                var check = function () {
                    if ((elLog && elLog.scrollTop !== null) && (elCover && elCover.scrollTop !== null)) return;
                    else check();
                };
                // error catching
                if (typeof message !== "string") throw new Error("message must be a string");
                if (typeof type !== "string") throw new Error("type must be a string");
                if (typeof fn !== "undefined" && typeof fn !== "function") throw new Error("fn must be a function");
                // initialize alertify if it hasn't already been done
                this.init();
                check();

                queue.push({ type: type, message: message, callback: fn, placeholder: placeholder, cssClass: cssClass });
                if (!isopen) this.setup();

                return this;
            },

            /**
             * Extend the log method to create custom methods
             *
             * @param  {String} type    Custom method name
             *
             * @return {Function}
             */
            extend : function (type) {
                if (typeof type !== "string") throw new Error("extend method must have exactly one paramter");
                return function (message, wait) {
                    this.log(message, type, wait);
                    return this;
                };
            },

            /**
             * Hide the dialog and rest to defaults
             *
             * @return {undefined}
             */
            hide : function () {
                var transitionDone,
                    self = this;
                // remove reference from queue
                queue.splice(0,1);
                // if items remaining in the queue
                if (queue.length > 0) this.setup(true);
                else {
                    isopen = false;
                    // Hide the dialog box after transition
                    // This ensure it doens't block any element from being clicked
                    transitionDone = function (event) {
                        event.stopPropagation();
                        // unbind event so function only gets called once
                        self.unbind(elDialog, self.transition.type, transitionDone);
                    };
                    // whether CSS transition exists
                    if (this.transition.supported) {
                        this.bind(elDialog, this.transition.type, transitionDone);
                        elDialog.className = "alertify alertify-hide alertify-hidden";
                    } else {
                        elDialog.className = "alertify alertify-hide alertify-hidden alertify-isHidden";
                    }
                    elCover.className  = "alertify-cover alertify-cover-hidden";
                    // set focus to the last element or body
                    // after the dialog is closed
                    elCallee.focus();
                }
            },

            /**
             * Initialize Alertify
             * Create the 2 main elements
             *
             * @return {undefined}
             */
            init : function () {
                // ensure legacy browsers support html5 tags
                document.createElement("nav");
                document.createElement("article");
                document.createElement("section");
                // cover
                if ($("alertify-cover") == null) {
                    elCover = document.createElement("div");
                    elCover.setAttribute("id", "alertify-cover");
                    elCover.className = "alertify-cover alertify-cover-hidden";
                    document.body.appendChild(elCover);
                }
                // main element
                if ($("alertify") == null) {
                    isopen = false;
                    queue = [];
                    elDialog = document.createElement("section");
                    elDialog.setAttribute("id", "alertify");
                    elDialog.className = "alertify alertify-hidden";
                    document.body.appendChild(elDialog);
                }
                // log element
                if ($("alertify-logs") == null) {
                    elLog = document.createElement("section");
                    elLog.setAttribute("id", "alertify-logs");
                    elLog.className = "alertify-logs alertify-logs-hidden";
                    document.body.appendChild(elLog);
                }
                // set tabindex attribute on body element
                // this allows script to give it focus
                // after the dialog is closed
                document.body.setAttribute("tabindex", "0");
                // set transition type
                this.transition = getTransitionEvent();
            },

            /**
             * Show a new log message box
             *
             * @param  {String} message    The message passed from the callee
             * @param  {String} type       [Optional] Optional type of log message
             * @param  {Number} wait       [Optional] Time (in ms) to wait before auto-hiding the log
             *
             * @return {Object}
             */
            log : function (message, type, wait) {
                // check to ensure the alertify dialog element
                // has been successfully created
                var check = function () {
                    if (elLog && elLog.scrollTop !== null) return;
                    else check();
                };
                // initialize alertify if it hasn't already been done
                this.init();
                check();

                elLog.className = "alertify-logs";
                this.notify(message, type, wait);
                return this;
            },

            /**
             * Add new log message
             * If a type is passed, a class name "alertify-log-{type}" will get added.
             * This allows for custom look and feel for various types of notifications.
             *
             * @param  {String} message    The message passed from the callee
             * @param  {String} type       [Optional] Type of log message
             * @param  {Number} wait       [Optional] Time (in ms) to wait before auto-hiding
             *
             * @return {undefined}
             */
            notify : function (message, type, wait) {
                var log = document.createElement("article");
                log.className = "alertify-log" + ((typeof type === "string" && type !== "") ? " alertify-log-" + type : "");
                log.innerHTML = message;
                // append child
                elLog.appendChild(log);
                // triggers the CSS animation
                setTimeout(function() { log.className = log.className + " alertify-log-show"; }, 50);
                this.close(log, wait);
            },

            /**
             * Set properties
             *
             * @param {Object} args     Passing parameters
             *
             * @return {undefined}
             */
            set : function (args) {
                var k;
                // error catching
                if (typeof args !== "object" && args instanceof Array) throw new Error("args must be an object");
                // set parameters
                for (k in args) {
                    if (args.hasOwnProperty(k)) {
                        this[k] = args[k];
                    }
                }
            },

            /**
             * Common place to set focus to proper element
             *
             * @return {undefined}
             */
            setFocus : function () {
                if (input) {
                    input.focus();
                    input.select();
                }
                else btnFocus.focus();
            },

            /**
             * Initiate all the required pieces for the dialog box
             *
             * @return {undefined}
             */
            setup : function (fromQueue) {
                var item = queue[0],
                    self = this,
                    transitionDone;

                // dialog is open
                isopen = true;
                // Set button focus after transition
                transitionDone = function (event) {
                    event.stopPropagation();
                    self.setFocus();
                    // unbind event so function only gets called once
                    self.unbind(elDialog, self.transition.type, transitionDone);
                };
                // whether CSS transition exists
                if (this.transition.supported && !fromQueue) {
                    this.bind(elDialog, this.transition.type, transitionDone);
                }
                // build the proper dialog HTML
                elDialog.innerHTML = this.build(item);
                // assign all the common elements
                btnReset  = $("alertify-resetFocus");
                btnResetBack  = $("alertify-resetFocusBack");
                btnOK     = $("alertify-ok")     || undefined;
                btnClose     = $("alertify-close")     || undefined;
                btnCancel = $("alertify-cancel") || undefined;
                btnFocus  = (_alertify.buttonFocus === "cancel") ? btnCancel : ((_alertify.buttonFocus === "none") ? $("alertify-noneFocus") : btnOK),
                    input     = $("alertify-text")   || undefined;
                form      = $("alertify-form")   || undefined;
                // add placeholder value to the input field
                if (typeof item.placeholder === "string" && item.placeholder !== "") input.value = item.placeholder;
                if (fromQueue) this.setFocus();
                this.addListeners(item.callback);
            },

            /**
             * Unbind events to elements
             *
             * @param  {Object}   el       HTML Object
             * @param  {Event}    event    Event to detach to element
             * @param  {Function} fn       Callback function
             *
             * @return {undefined}
             */
            unbind : function (el, event, fn) {
                if (typeof el.removeEventListener === "function") {
                    el.removeEventListener(event, fn, false);
                } else if (el.detachEvent) {
                    el.detachEvent("on" + event, fn);
                }
            }
        };

        return {
            alert   : function (message, fn, cssClass) { _alertify.dialog(message, "alert", fn, "", cssClass); return this; },
            confirm : function (message, fn, cssClass) { _alertify.dialog(message, "confirm", fn, "", cssClass); return this; },
            extend  : _alertify.extend,
            init    : _alertify.init,
            log     : function (message, type, wait) { _alertify.log(message, type, wait); return this; },
            prompt  : function (message, fn, placeholder, cssClass) { _alertify.dialog(message, "prompt", fn, placeholder, cssClass); return this; },
            success : function (message, wait) { _alertify.log(message, "success", wait); return this; },
            error   : function (message, wait) { _alertify.log(message, "error", wait); return this; },
            set     : function (args) { _alertify.set(args); },
            labels  : _alertify.labels,
            debug   : _alertify.handleErrors
        };
    };

    // AMD and window support
    if (typeof define === "function") {
        define([], function () { return new Alertify(); });
    } else if (typeof global.alertify === "undefined") {
        global.alertify = new Alertify();
    }

}(this));

/*!
 * Clamp.js 0.5.1
 *
 * Copyright 2011-2013, Joseph Schmitt http://joe.sh
 * Released under the WTFPL license
 * http://sam.zoy.org/wtfpl/
 */

(function(){
    /**
     * Clamps a text node.
     * @param {HTMLElement} element. Element containing the text node to clamp.
     * @param {Object} options. Options to pass to the clamper.
     */
    function clamp(element, options) {
        options = options || {};

        var self = this,
            win = window,
            opt = {
                clamp:              options.clamp || 2,
                useNativeClamp:     typeof(options.useNativeClamp) != 'undefined' ? options.useNativeClamp : true,
                splitOnChars:       options.splitOnChars || ['.', '-', '–', '—', ' '], //Split on sentences (periods), hypens, en-dashes, em-dashes, and words (spaces).
                animate:            options.animate || false,
                truncationChar:     options.truncationChar || '…',
                truncationHTML:     options.truncationHTML
            },

            sty = element.style,
            originalText = element.innerHTML,

            supportsNativeClamp = typeof(element.style.webkitLineClamp) != 'undefined',
            clampValue = opt.clamp,
            isCSSValue = clampValue.indexOf && (clampValue.indexOf('px') > -1 || clampValue.indexOf('em') > -1),
            truncationHTMLContainer;

        if (opt.truncationHTML) {
            truncationHTMLContainer = document.createElement('span');
            truncationHTMLContainer.innerHTML = opt.truncationHTML;
        }


// UTILITY FUNCTIONS __________________________________________________________

        /**
         * Return the current style for an element.
         * @param {HTMLElement} elem The element to compute.
         * @param {string} prop The style property.
         * @returns {number}
         */
        function computeStyle(elem, prop) {
            if (!win.getComputedStyle) {
                win.getComputedStyle = function(el, pseudo) {
                    this.el = el;
                    this.getPropertyValue = function(prop) {
                        var re = /(\-([a-z]){1})/g;
                        if (prop == 'float') prop = 'styleFloat';
                        if (re.test(prop)) {
                            prop = prop.replace(re, function () {
                                return arguments[2].toUpperCase();
                            });
                        }
                        return el.currentStyle && el.currentStyle[prop] ? el.currentStyle[prop] : null;
                    }
                    return this;
                }
            }

            return win.getComputedStyle(elem, null).getPropertyValue(prop);
        }

        /**
         * Returns the maximum number of lines of text that should be rendered based
         * on the current height of the element and the line-height of the text.
         */
        function getMaxLines(height) {
            var availHeight = height || element.clientHeight,
                lineHeight = getLineHeight(element);

            return Math.max(Math.floor(availHeight/lineHeight), 0);
        }

        /**
         * Returns the maximum height a given element should have based on the line-
         * height of the text and the given clamp value.
         */
        function getMaxHeight(clmp) {
            var lineHeight = getLineHeight(element);
            return lineHeight * clmp;
        }

        /**
         * Returns the line-height of an element as an integer.
         */
        function getLineHeight(elem) {
            var lh = computeStyle(elem, 'line-height');
            if (lh == 'normal') {
                // Normal line heights vary from browser to browser. The spec recommends
                // a value between 1.0 and 1.2 of the font size. Using 1.1 to split the diff.
                lh = parseInt(computeStyle(elem, 'font-size')) * 1.2;
            }
            return parseInt(lh);
        }


// MEAT AND POTATOES (MMMM, POTATOES...) ______________________________________
        var splitOnChars = opt.splitOnChars.slice(0),
            splitChar = splitOnChars[0],
            chunks,
            lastChunk;

        /**
         * Gets an element's last child. That may be another node or a node's contents.
         */
        function getLastChild(elem) {

            if (elem.lastChild == null) return "";

            //Current element has children, need to go deeper and get last child as a text node
            if (elem.lastChild.children && elem.lastChild.children.length > 0) {
                return getLastChild(Array.prototype.slice.call(elem.children).pop());
            }
            //This is the absolute last child, a text node, but something's wrong with it. Remove it and keep trying
            else if (!elem.lastChild || !elem.lastChild.nodeValue || elem.lastChild.nodeValue == '' || elem.lastChild.nodeValue == opt.truncationChar) {
                elem.lastChild.parentNode.removeChild(elem.lastChild);
                return getLastChild(element);
            }
            //This is the last child we want, return it
            else {
                return elem.lastChild;
            }
        }

        /**
         * Removes one character at a time from the text until its width or
         * height is beneath the passed-in max param.
         */
        function truncate(target, maxHeight) {
            if (!maxHeight) {return;}

            /**
             * Resets global variables.
             */
            function reset() {
                splitOnChars = opt.splitOnChars.slice(0);
                splitChar = splitOnChars[0];
                chunks = null;
                lastChunk = null;
            }


            var nodeValue = target ? target.nodeValue.replace(opt.truncationChar, '') : "    ";

            //Grab the next chunks
            if (!chunks) {
                //If there are more characters to try, grab the next one
                if (splitOnChars.length > 0) {
                    splitChar = splitOnChars.shift();
                }
                //No characters to chunk by. Go character-by-character
                else {
                    splitChar = '';
                }

                chunks = nodeValue.split(splitChar);
            }

            //If there are chunks left to remove, remove the last one and see if
            // the nodeValue fits.
            if (chunks.length > 1) {
                // console.log('chunks', chunks);
                lastChunk = chunks.pop();
                // console.log('lastChunk', lastChunk);
                applyEllipsis(target, chunks.join(splitChar));
            }
            //No more chunks can be removed using this character
            else {
                chunks = null;
            }

            //Insert the custom HTML before the truncation character
            if (truncationHTMLContainer) {
                target.nodeValue = target.nodeValue.replace(opt.truncationChar, '');
                element.innerHTML = target.nodeValue + ' ' + truncationHTMLContainer.innerHTML + opt.truncationChar;
            }

            //Search produced valid chunks
            if (chunks) {
                /* Check if splitChar is ' ' */
                if (splitOnChars.length >= 0 && splitChar === ' ') {
                    var startLoop = element.clientHeight,
                        i = 0;
                    do {

                        // Fix loop forever
                        i++;
                        if (i > 10 && startLoop == element.clientHeight) {
                            break;
                        }

                        applyEllipsis(target, chunks.join(splitChar) + splitChar + lastChunk);
                        lastChunk = chunks.pop();
                    }
                    while(element.clientHeight > maxHeight);

                    return element.innerHTML;
                }

                //It fits
                if (element.clientHeight < maxHeight) {
                    //There's still more characters to try splitting on, not quite done yet
                    if (splitOnChars.length >= 0 && splitChar != '') {
                        applyEllipsis(target, chunks.join(splitChar) + splitChar + lastChunk);
                        chunks = null;
                    }
                    //Finished!
                    else {
                        return element.innerHTML;
                    }
                }
            }
            //No valid chunks produced
            else {
                //No valid chunks even when splitting by letter, time to move
                //on to the next node
                if (splitChar == '') {
                    applyEllipsis(target, '');
                    target = getLastChild(element);

                    reset();
                }
            }

            //If you get here it means still too big, let's keep truncating
            if (opt.animate) {
                setTimeout(function() {
                    truncate(target, maxHeight);
                }, opt.animate === true ? 10 : opt.animate);
            }
            else {
                return truncate(target, maxHeight);
            }
        }

        function applyEllipsis(elem, str) {
            elem.nodeValue = str + opt.truncationChar;
        }


// CONSTRUCTOR ________________________________________________________________

        if (clampValue == 'auto') {
            clampValue = getMaxLines();
        }
        else if (isCSSValue) {
            clampValue = getMaxLines(parseInt(clampValue));
        }

        var clampedText;
        if (supportsNativeClamp && opt.useNativeClamp) {
            sty.overflow = 'hidden';
            sty.textOverflow = 'ellipsis';
            sty.webkitBoxOrient = 'vertical';
            sty.display = '-webkit-box';
            sty.webkitLineClamp = clampValue;

            if (isCSSValue) {
                sty.height = opt.clamp + 'px';
            }
        }
        else {
            var height = getMaxHeight(clampValue);
            if (height < element.clientHeight) {
                clampedText = truncate(getLastChild(element), height);
            }
        }


        return {
            'original': originalText,
            'clamped': clampedText
        };
    }

    window.$clamp = clamp;
})();