/*
 * OKVideo by OKFocus v2.3.2
 * http://okfoc.us
 *
 * Copyright 2014, OKFocus
 * Licensed under the MIT license.
 *
 */

var player, OKEvents, options, vPlayer, vYouTube;

define([
    'jquery',
    'prVimeoPlayer',
    'youTubeIFrame',
    'domReady!'
], function ($, vimeoPlayer, YT) {
    "use strict";

    vYouTube = YT;
    vPlayer = vimeoPlayer;
    var BLANK_GIF = "data:image/gif;base64,R0lGODlhAQABAPABAP///wAAACH5BAEKAAAALAAAAAABAAEAAAICRAEAOw%3D%3D";
    $.okvideo = function (options) {
        // if the option var was just a string, turn it into an object
        if (typeof options !== 'object') {
            options = {'video': options};
        }

        var base = this;

        // kick things off
        base.init = function () {
            base.options = $.extend({}, $.okvideo.options, options);

            // support older versions of okvideo
            if (base.options.video === null) {
                base.options.video = base.options.source;
            }

            base.setOptions();
            var target;

            if (typeof base.options.newtarget == 'undefined') {
                base.options.newtarget = "";
                target = base.options.target || $('body');
            } else {
                target = $('#' + base.options.newtarget)
            }

            var position = target[0] == $('body')[0] ? 'fixed' : 'absolute';

            target.css({position: 'relative'});
            var zIndex = base.options.controls === 3 ? -999 : "auto";
            var mask = '<div id="okplayer-mask' + base.options.newtarget + '" style="position:' + position + ';left:0;top:0;overflow:hidden;z-index:-998;height:100%;width:100%;background-repeat: no-repeat; background-position: center;background-size: cover;"></div>';
            target.append(mask);
            var maskBackground = BLANK_GIF;

            if (OKEvents.utils.isMobile()) {
                if (typeof base.options.prSmallImage != 'undefined') {
                    maskBackground = base.options.prSmallImage;
                } else if (typeof base.options.splashPageMobile != 'undefined') {
                    maskBackground = base.options.splashPageMobile;
                }

                target.append('<div id="okplayer' + base.options.newtarget + '" style="position:' + position + ';left:0;top:0;overflow:hidden;z-index:' + zIndex + ';height:100%;width:100%;"></div>');
            } else {
                if (base.options.adproof === 1) {
                    target.append('<div id="okplayer' + base.options.newtarget + '" style="position:' + position + ';left:-10%;top:-10%;overflow:hidden;z-index:' + zIndex + ';height:120%;width:120%;"></div>');
                } else {
                    target.append('<div id="okplayer' + base.options.newtarget + '" style="position:' + position + ';left:0;top:0;overflow:hidden;z-index:' + zIndex + ';height:100%;width:100%;"></div>');
                }

                if (typeof base.options.prImage != 'undefined') {
                    maskBackground = base.options.prImage;
                }
            }

            $("#okplayer-mask" + base.options.newtarget).css("background-image", "url(" + maskBackground + ")");

            if (base.options.playlist.list === null) {
                if (base.options.video.provider === 'youtube') {
                    base.loadYouTubeAPI();
                } else if (base.options.video.provider === 'vimeo') {
                    base.options.volume /= 100;
                    base.loadVimeoAPI();
                }
            } else {
                base.loadYouTubeAPI();
            }
        };

        // clean the options
        base.setOptions = function () {
            // exchange 'true' for '1' and 'false' for 3
            for (var key in this.options) {
                if (this.options[key] === true) {
                    this.options[key] = 1;
                }
                if (this.options[key] === false) {
                    this.options[key] = 3;
                }
            }

            if (base.options.playlist.list === null) {
                base.options.video = base.determineProvider();
            }

            // pass options to the window
            if (typeof options.newtarget == 'undefined') {
                options.newtarget = "";
            }

            $(window).data('okoptions' + options.newtarget.replace('-', '').toLowerCase(), base.options);
        };

        // load the youtube api
        base.loadYouTubeAPI = function () {
            if (vYouTube && typeof vYouTube.Player === 'function') {
                onYouTubePlayerAPIReadyTwo(base.options.newtarget);
                return;
            }

            var callback = onYouTubePlayerAPIReadyTwo.bind(null, base.options.newtarget);

            var waitNumber = 0;
            var intervalId = setInterval(function () {
                waitNumber++;
                if (vYouTube && typeof vYouTube.Player === 'function') {
                    callback();
                    clearInterval(intervalId);
                } else if (waitNumber > 20) {
                    clearInterval(intervalId);
                }
            }, 50);
        };

        base.loadYouTubePlaylist = function () {
            player.loadPlaylist(base.options.playlist.list, base.options.playlist.index, base.options.playlist.startSeconds, base.options.playlist.suggestedQuality);
        };

        // load the vimeo api by replacing the div with an iframe and loading js
        base.loadVimeoAPI = function () {
            $('#okplayer' + base.options.newtarget).replaceWith(function () {
                return '<iframe src="//player.vimeo.com/video/' + base.options.video.id + '?title=0&byline=0&portrait=0&playbar=0&loop=' + base.options.loop + '&autoplay=' + (base.options.autoplay === 1 ? 1 : 0) + '&player_id=okplayer&background=1" frameborder="0" style="' + $(this).attr('style') + 'background-color:black;" id="' + $(this).attr('id') + '"></iframe>';
            });

            var callback = base.options.autoplay === 1
                ? vimeoPlayerAutoPlayReady.bind(null, base.options.newtarget)
                : vimeoPlayerReady.bind(null, base.options.newtarget);

            setTimeout(callback, 500);
        };

        // insert js into the head and exectue a callback function
        base.insertJS = function (src, callback) {
            var tag = document.createElement('script');

            if (callback) {
                if (tag.readyState) {  //IE
                    tag.onreadystatechange = function () {
                        if (tag.readyState === "loaded" ||
                            tag.readyState === "complete") {
                            tag.onreadystatechange = null;
                            callback();
                        }
                    };
                } else {
                    tag.onload = function () {
                        callback();
                    };
                }
            }
            tag.src = src;
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        };

        // is it from youtube or vimeo?
        base.determineProvider = function () {
            var a = document.createElement('a');
            a.href = base.options.video;

            if (/youtube.com/.test(base.options.video)) {
                return { "provider" : "youtube", "id" : a.href.slice(a.href.indexOf('v=') + 2).toString() };
            } else if (/vimeo.com/.test(base.options.video)) {
                return { "provider" : "vimeo", "id" : a.href.split('/')[3].toString() };
            } else if (/[-A-Za-z0-9_]+/.test(base.options.video)) {
                var id = new String(base.options.video.match(/[-A-Za-z0-9_]+/));
                if (id.length == 11) {
                    return { "provider" : "youtube", "id" : id.toString() };
                } else {
                    for (var i = 0; i < base.options.video.length; i++) {
                        if (typeof parseInt(base.options.video[i]) !== "number") {
                            throw 'not vimeo but thought it was for a sec';
                        }
                    }
                    return { "provider" : "vimeo", "id" : base.options.video };
                }
            } else {
                throw "OKVideo: Invalid video source";
            }
        };

        base.init();
    };

    $.okvideo.options = {
        source: null, // Deprecate dis l8r
        video: null,
        playlist: { // eat ur heart out @brokyo
            list: null,
            index: 0,
            startSeconds: 0,
            suggestedQuality: "default" // options: small, medium, large, hd720, hd1080, highres, default
        },
        disableKeyControl: 1,
        captions: 0,
        loop: 1,
        hd: 1,
        volume: 0,
        adproof: false,
        unstarted: null,
        onFinished: null,
        onReady: null,
        onPlay: null,
        onPause: null,
        buffering: null,
        controls: false,
        autoplay: true,
        annotations: true,
        cued: null
    };

    $.fn.okvideo = function (options) {
        options.target = this;
        return this.each(function () {
            (new $.okvideo(options));
        });
    };
});

function vimeoPlayerAutoPlayReady(intOpt)
{
    options = jQuery(window).data('okoptions' + intOpt.replace('-', '').toLowerCase());
    (function (options, intOpt) {
        var iframe = jQuery('#okplayer' + intOpt);
        var player = new vPlayer(iframe);
        player.setVolume(0).then(function () {
            player.play()
        });
    })(options, intOpt);
}

// vimeo player ready
function vimeoPlayerReady(intOpt)
{
    var WAIT_PLAY = 'waitPlay';
    var PLAY = 'play';
    var WAIT_PAUSE = 'waitPause';
    var PAUSE = 'pause';

    options = jQuery(window).data('okoptions' + intOpt.replace('-', '').toLowerCase());
    (function (options, intOpt) {
        var iframe = jQuery('#okplayer' + intOpt);
        var player = new vPlayer(iframe);

        var state = {
            next: PLAY, // can be 'pause', 'play'
            current: PAUSE,
            muted: false,
            isReady: false
        };

        var iframeWrap = jQuery('#' + intOpt);
        iframeWrap.on('mouseover', function () {
            if (!OKEvents.utils.isMobile()) {
                iframeWrap.find('#okplayer-mask' + intOpt).hide();
            }

            if (state.muted) {
                tryPlayVimeoVideo(player, state);
            } else {
                player.setVolume(0).then(function () {
                    state.muted = true;
                    if (PLAY === state.next) {
                        tryPlayVimeoVideo(player, state);
                    }
                });
            }
        });

        iframeWrap.on('mouseout', function () {
            tryPauseVimeoVideo(player, state);
        });

        // hide player until Vimeo hides controls...
        window.setTimeout(function () {
            jQuery('#okplayer' + intOpt).css('visibility', 'visible');
        }, 2000);


    })(options, intOpt);

    function tryPlayVimeoVideo(player, state)
    {
        switch (state.current) {
            case WAIT_PAUSE:
                state.next = PLAY;
                break;
            case PAUSE:
                state.next = PLAY;
                if (! state.isReady) {
                    state.current = WAIT_PLAY;
                }
                player.play().then(function () {
                    state.isReady = true;
                    state.current = PLAY;
                    if (PAUSE === state.next) {
                        player.pause().then(function () {
                            state.current = PAUSE;
                        });
                    }
                });
                break;
        }
    }

    function tryPauseVimeoVideo(player, state)
    {
        switch (state.current) {
            case WAIT_PLAY:
                state.next = PAUSE;
                break;
            case PLAY:
                state.next = PAUSE;
                player.pause().then(function () {
                    state.current = PAUSE;
                    if (PLAY === state.next) {
                        tryPlayVimeoVideo(player, state);
                    }
                });
                break;
        }
    }
}

// youtube player ready
function onYouTubePlayerAPIReadyTwo(intOpt)
{
    options = jQuery(window).data('okoptions' + intOpt.replace('-', '').toLowerCase());
    var prYouTubeClass = jQuery('#' + intOpt);
    if (prYouTubeClass) {
        prYouTubeClass.addClass('pr-youtube-video');
    }

    player = new vYouTube.Player('okplayer' + intOpt, {
        videoId: options.video ? options.video.id : null,
        playerVars: {
            'autohide': 1,
            'autoplay': options.autoplay,
            'disablekb': options.keyControls,
            'cc_load_policy': options.captions,
            'controls': options.controls,
            'enablejsapi': 1,
            'fs': 0,
            'mute': 1,
            'modestbranding': 1,
            'origin': window.location.origin || (window.location.protocol + '//' + window.location.hostname),
            'iv_load_policy': options.annotations,
            'host': window.location.protocol + '//' + 'www.youtube.com',
            'loop': options.loop,
            'showinfo': 0,
            'rel': 0,
            'wmode': 'opaque',
            'hd': options.hd
        },
        events: {
            'onReady': function (event) {
                var iframeWrap = jQuery('#' + intOpt);
                iframeWrap.on('mouseover', function () {
                    if (!OKEvents.utils.isMobile()) {
                        iframeWrap.find('#okplayer-mask' + intOpt).hide();
                    }
                    event.target.playVideo();
                    iframeWrap.on('mouseout', function () {
                        event.target.pauseVideo()
                    });
                });
            },
            'onStateChange': OKEvents.yt.onStateChange,
            'onError': OKEvents.yt.error
        }
    });
}

OKEvents = {
    yt: {
        ready: function (event) {
            event.target.setVolume(options.volume);
            if (options.autoplay === 1) {
                if (options.playlist.list) {
                    player.loadPlaylist(options.playlist.list, options.playlist.index, options.playlist.startSeconds, options.playlist.suggestedQuality);
                } else {
                    event.target.playVideo();
                }
            }
            OKEvents.utils.isFunction(options.onReady) && options.onReady();
        },
        onStateChange: function (event) {
            switch (event.data) {
                case -1:
                    OKEvents.utils.isFunction(options.unstarted) && options.unstarted();
                    break;
                case 0:
                    OKEvents.utils.isFunction(options.onFinished) && options.onFinished();
                    options.loop && event.target.playVideo();
                    break;
                case 1:
                    OKEvents.utils.isFunction(options.onPlay) && options.onPlay();
                    break;
                case 2:
                    OKEvents.utils.isFunction(options.onPause) && options.onPause();
                    break;
                case 3:
                    OKEvents.utils.isFunction(options.buffering) && options.buffering();
                    break;
                case 5:
                    OKEvents.utils.isFunction(options.cued) && options.cued();
                    break;
                default:
                    throw "OKVideo: received invalid data from YT player.";
            }
        },
        error: function (event) {
            throw event;
        }
    },
    v: {
        onReady: function () {
            OKEvents.utils.isFunction(options.onReady) && options.onReady();
        },
        onPlay: function () {
            if (! OKEvents.utils.isMobile()) {
                player.api('setVolume', options.volume);
            }
            OKEvents.utils.isFunction(options.onPlay) && options.onPlay();
        },
        onPause: function () {
            OKEvents.utils.isFunction(options.onPause) && options.onPause();
        },
        onFinish: function () {
            OKEvents.utils.isFunction(options.onFinish) && options.onFinish();
        }
    },
    utils: {
        isFunction: function (func) {
            return typeof func === 'function';
        },
        isMobile: function () {
            return !!navigator.userAgent.match(/(iPhone|iPod|iPad|Android|BlackBerry)/);
        }
    }
};
