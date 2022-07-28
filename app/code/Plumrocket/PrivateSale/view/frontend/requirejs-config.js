var config = {
    map: {
        '*': {
            'privatesalePreviewPanel': 'Plumrocket_PrivateSale/js/previewPanel',
            'privatesaleHomepage': 'Plumrocket_PrivateSale/js/homepage',
            'privatesaleEvent': 'Plumrocket_PrivateSale/js/event',
            'privatesaleSplashPage': 'Plumrocket_PrivateSale/js/splashpage',
            'prCarousel': 'Plumrocket_PrivateSale/js/prCarousel',
            'okvideo': 'Plumrocket_PrivateSale/js/lib/okvideo',
            'prVimeoPlayer': 'Plumrocket_PrivateSale/js/lib/vimeo-player.min',
        }
    },
    paths: {
        youTubeIFrame: 'https://www.youtube.com/player_api?noext'
    },
    shim: {
        youTubeIFrame: {
            exports: 'YT'
        }
    }
};
