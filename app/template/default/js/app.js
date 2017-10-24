/**
 * Module - loader
 *
 * @usage:  load.script('/load/js/test.js', function(){});
 */
/* global jQuery */

var load = (function() {
    var script = function(url, callback) {
        jQuery(function() {
            callback = (typeof callback != 'undefined') ? callback : {};
            jQuery.ajax({
                url: url,
                dataType: "script",
                cache: true,
                async: true
            }).then(callback);
        });
    };
    return {
        script: script
    };
})();

/**
 * Module - timers handler
 *
 * Polling run by timers become troublesome when your loading content via ajax,
 * as new content comes in global timers wont stop for previous content,
 * this then can causes issues.
 *
 * This is to store timer ids which then can be stopped.
 *
 * @usage:  load.script('/load/js/timers.js', function(){});
 */
var timers = (function() {
    var timers = new Array();

    var add = function(timer_id) {
        timers.push(timer_id);
    };

    var stopAll = function(timer_id) {
        // clear all timers
        for (var i = 0; i < timers.length; i++) {
            clearTimeout(timers[i]);
        }
    };

    return {
        add: add,
        stopAll: stopAll,
    };
})();

/**
 * Module - app
 */

/* global $, timers, pollTimer, debounce */

window.app = (function() {

    /**
     * Init construct
     */
    var init = function() {

        //app event handlers
        events();

        $.xhrPool = [];
        $.xhrPool.abortAll = function() {
            $(this).each(function(i, jqXHR) { //  cycle through list of recorded connection
                jqXHR.abort(); //  aborts connection
                $.xhrPool.splice(i, 1); //  removes from list by index
            });
        };

        var oldbeforeunload = window.onbeforeunload;

        window.onbeforeunload = function() {
            var r = oldbeforeunload ? oldbeforeunload() : undefined;
            if (r == undefined) {
                $.xhrPool.abortAll();
            }
            return r;
        };
    };

    var debounce = function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            }, wait);
            if (immediate && !timeout) func.apply(context, args);
        };
    };


    /**
     * Ajax load content into (.ajax-container)
     * ajax_load('/');
     */
    var ajax_load = function(url, eml, noStateChange, callback) {

        if (!eml) {
            eml = '.ajax-container';
        }

        if (!noStateChange) {
            noStateChange = false;
        }

        if (typeof pollTimer != 'undefined') {
            clearTimeout(pollTimer);
        }

        $.ajax({
            url: url,
            cache: false,
            beforeSend: function(jqXHR) {
                $.xhrPool.push(jqXHR);
            },
            complete: function(jqXHR) {
                var i = $.xhrPool.indexOf(jqXHR);
                if (i > -1) $.xhrPool.splice(i, 1);
            },
            success: function(response, status, request) {

                if (!noStateChange) {
                    window.history.pushState({
                        url: url
                    }, "", url);
                }

                // double or clicking too fast, just refresh page
                if (response == 'Invalid CSRF token') {
                    window.location = url;
                }
                else {
                    $(eml).replaceWith($('<div />').html(response).find(eml)[0]);

                    // re attach events
                    events();
                }
            },
            error: function() {
                $.xhrPool.abortAll();
                timers.stopAll();
            }
        });
    };

    /**
     * Popup Window handler
     * With jQuery attached event handler: [data-type="popup"]
     * <a href="javascript:;" data-type="popup" data-url="/path/to/resource" data-name="Popup Title">...</a>
     */
    var popup = function(url, t, w, h) {
        var screenLeft = (window.screenLeft != undefined) ? window.screenLeft : screen.left,
            screenTop = (window.screenTop != undefined) ? window.screenTop : screen.top,
            width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width,
            height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height,
            left = ((width / 2) - (w / 2)) + screenLeft,
            top = ((height / 2) - (h / 2)) + screenTop,
            popupWindow = window.open(url, t, 'toolbar=yes, menubar=yes, scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        popupWindow.blur();
        window.focus();
        //if (window.focus) {
        //    popupWindow.focus();
        //}
    };

    /**
     * Event handlers
     */
    var events = function() {

        /**
         * Attach on click event to open popup window to data-type="popup" elements
         */
        $(document).find('[data-type="popup"]').off('click').on('click', function(e) {
            popup($(this).data('url'), $(this).data('name'), 1024, 768);
        });

        /**
         * Bootstrap tooltips
         */
        $(document).find('[data-toggle="tooltip"]').tooltip();

        /**
         * Browser back button
         */
        $(window).on("popstate", function() {
            // check there is a history state
            if (window.history.state && window.history.state.url) {
                var url = window.history.state.url.split('#')[0];
                $.ajax({
                    url: url,
                    dataType: "html",
                    success: function(data) {
                        $('.ajax-container').replaceWith($('<div />').html(data).find('.ajax-container')[0]);
                    }
                });
            }
        });

        /**
         * AJAX links event handler
         */
        $(document).find('.ajax-link').off('click').on('click', function(e) {

            //return;
            var link = $(this);
            
            // top menu links active
            if (link.hasClass('nav-link')) {
                $('.nav-link').removeClass('active');
            }
            
            // side menu links active
            if (link.parent().is('li')) {
                link.parent().siblings().removeClass('active');
            }
            link.parent().addClass('active');

            e.preventDefault();
            // stop all timers
            timers.stopAll();

            // call ajax load function
            ajax_load($(this).prop('href'), '.ajax-container', $(this).data('keep-state'));
        });

        /**
         * attach AJAX modal links event handler
         */
        $(document).find('.ajax-modal-link').off('click').on('click', function(e) {
            e.preventDefault();
            // stop all timers
            timers.stopAll();
            // call ajax load function
            ajax_load($(this).prop('href'), '.modal-content', $(this).data('keep-state'));
        });

        /**
         * AJAX modal event handler
         */
        $(document).find('.ajax-modal').off('click').on('click', function(e) {
            e.preventDefault();

            var modal = '.modal-content';

            var default_content = '' +
                '<div class="modal-header">' +
                '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>' +
                '    <h4 class="modal-title">' +
                '        Loading...' +
                '    </h4>' +
                '</div>' +
                '<div class="modal-body">' +
                '    <p class="slow-warning">Please wait...</p>' +
                '</div>';

            $(modal).html(default_content);

            setTimeout(function() {
                if ($(document).find('.slow-warning').length > 0) {
                    $(document).find('.slow-warning').html('Content failed to load, please refresh your browser and try again.');
                }
            }, 5000);

            var dialog_size = $(this).data('size');

            var request = $.ajax({
                url: $(this).data('url'),
                method: "GET",
                dataType: "html",
                cache: false
            });

            request.done(function(data) {
                var modal = '.modal-content';

                $(modal).replaceWith($('<div />').html(data).find(modal)[0]);

                if (dialog_size == 'modal-lg') {
                    $(modal).parent().removeClass('modal-sm modal-md modal-lg').addClass('modal-lg');
                }
                else if (dialog_size == 'modal-sm') {
                    $(modal).parent().removeClass('modal-sm modal-md modal-lg').addClass('modal-sm');
                }
                else {
                    $(modal).parent().removeClass('modal-sm modal-md modal-lg').addClass('modal-md');
                }

                /**
                 * attach AJAX modal links event handler
                 */
                $(document).find('.ajax-modal-link').off('click').on('click', function(e) {
                    e.preventDefault();
                    // stop all timers
                    timers.stopAll();
                    // call ajax load function
                    ajax_load($(this).prop('href'), '.modal-content', $(this).data('keep-state'));
                });
            });

            request.fail(function(jqXHR, textStatus) {
                console.log('modal failed to load', textStatus);
                timers.stopAll();
            });
        });

    };

    return {
        init: init,
        ajax_load: ajax_load,
        events: events,
        popup: popup
    };
})();

$(document).ready(function() {
    window.app.init();
});