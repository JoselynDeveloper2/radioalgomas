<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['streamUrl' => null, 'width' => '100%', 'height' => '315']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['streamUrl' => null, 'width' => '100%', 'height' => '315']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    use App\Models\Player;
    use App\Models\PlayerUrl;
    
    // Get stream URL with fallback system
    $useProxy = false;
    if (!$streamUrl) {
        // Try new PlayerUrl system first
        $activePlayerUrl = PlayerUrl::getActive();
        if ($activePlayerUrl) {
            $originalUrl = $activePlayerUrl->url;
            // Use original URL directly with enhanced CORS handling
            $streamUrl = $originalUrl;
            $useProxy = false; // Let Video.js handle CORS with configuration
        } else {
            // Fallback to old Player system
            $streamUrl = Player::getActiveStreamUrl();
        }
    }
    
    // Additional metadata for enhanced player
    $playerMetadata = [];
    if ($activePlayerUrl ?? false) {
        $playerMetadata = [
            'name' => $activePlayerUrl->name,
            'last_tested' => $activePlayerUrl->last_tested_at?->diffForHumans(),
            'response_time' => $activePlayerUrl->test_response_time,
            'original_url' => $activePlayerUrl->url,
            'using_proxy' => $useProxy,
        ];
    }
?>

<div class="video-player-container mb-8">
    <div class="wrapper">
        <div class="videocontent">
            <video 
                id="videojs_player" 
                class="video-js vjs-default-skin vjs-16-9" 
                controls 
                preload="auto" 
                width="<?php echo e($width); ?>" 
                height="<?php echo e($height); ?>" 
                autoplay
                muted
                playsinline
                crossorigin="anonymous"
                data-setup='{}'>
                <p class="vjs-no-js">
                    Para ver este video necesitas activar JavaScript y considerar actualizar a un
                    <a href="https://videojs.com/html5-video-support/" target="_blank">
                        navegador que soporte HTML5 video
                    </a>.
                </p>
            </video>
        </div>
    </div>
</div>

<?php $__env->startPush('head'); ?>
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet" />
    <style>
        .video-player-container .wrapper {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        
        .video-player-container .videocontent {
            position: relative;
            width: 100%;
        }
        
        .video-js {
            width: 100% !important;
            height: auto !important;
        }
        
        .video-js .vjs-tech {
            width: 100% !important;
            height: auto !important;
        }
        
        @media (max-width: 768px) {
            .video-player-container {
                margin-bottom: 1rem;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script>
        (function() {
            // Avoid multiple initializations
            if (window.tucanaltv_player_initialized) {
                return;
            }
            
            window.tucanaltv_player_initialized = true;

            function initializePlayer() {
                const playerElement = document.getElementById('videojs_player');
                if (!playerElement || typeof videojs === 'undefined') {
                    return;
                }

                // Dispose previous player if exists
                if (window.tucanaltv_player) {
                    try {
                        window.tucanaltv_player.dispose();
                    } catch (e) {
                        console.log('Error disposing previous player:', e);
                    }
                    window.tucanaltv_player = null;
                }

                // Enhanced configuration for external HLS streams
                const player = videojs('videojs_player', {
                    controls: true,
                    autoplay: true,
                    preload: 'auto',
                    muted: true,
                    fluid: false,
                    responsive: true,
                    liveui: true,
                    // Advanced HTML5 configuration for external streams
                    html5: {
                        vhs: {
                            overrideNative: true,
                            withCredentials: false,
                            useCueTags: false,
                            // Handle CORS issues with external playlists
                            xhr: {
                                beforeRequest: function(options) {
                                    // Don't send credentials
                                    options.withCredentials = false;
                                    return options;
                                }
                            }
                        },
                        nativeAudioTracks: false,
                        nativeVideoTracks: false
                    },
                    techOrder: ['html5'],
                    sources: [],
                    // Experimental: disable some features that might cause CORS issues
                    experimentalSvgIcons: false
                });

                player.ready(function() {
                    console.log('TuCanalTV Player Ready');
                    <?php if(!empty($playerMetadata)): ?>
                        console.log('Stream Info:', {
                            name: '<?php echo e($playerMetadata['name'] ?? ''); ?>',
                            lastTested: '<?php echo e($playerMetadata['last_tested'] ?? ''); ?>',
                            responseTime: '<?php echo e($playerMetadata['response_time'] ?? ''); ?>ms',
                            originalUrl: '<?php echo e($playerMetadata['original_url'] ?? ''); ?>',
                            usingProxy: <?php echo e($playerMetadata['using_proxy'] ? 'true' : 'false'); ?>,
                            streamUrl: '<?php echo e($streamUrl); ?>'
                        });
                    <?php endif; ?>
                    
                    // Set source like in original project
                    player.src({
                        src: '<?php echo e($streamUrl); ?>',
                        type: 'application/x-mpegURL',
                        label: 'HD',
                        res: 1080
                    });

                    // Ensure muted for autoplay
                    player.muted(true);
                });

                // Handle play event like original
                player.on('play', function() {
                    if (!player.currentTime() === 0) {
                        player.src('<?php echo e($streamUrl); ?>');
                    }
                });

                // Enhanced error handling with different retry strategies
                let retryCount = 0;
                const maxRetries = 3;
                
                player.on('error', function(event) {
                    const error = player.error();
                    console.log('Stream error (attempt ' + (retryCount + 1) + '):', {
                        code: error?.code,
                        message: error?.message,
                        type: error?.type
                    });
                    
                    if (retryCount < maxRetries) {
                        retryCount++;
                        
                        setTimeout(function() {
                            console.log('Retrying stream with attempt ' + retryCount + '...');
                            
                            // Clear any existing error
                            player.error(null);
                            
                            // Different retry strategies based on attempt
                            if (retryCount === 1) {
                                // First retry: just reload the source
                                player.src({
                                    src: '<?php echo e($streamUrl); ?>',
                                    type: 'application/x-mpegURL'
                                });
                            } else if (retryCount === 2) {
                                // Second retry: try with different type hint
                                player.src({
                                    src: '<?php echo e($streamUrl); ?>',
                                    type: 'application/vnd.apple.mpegurl'
                                });
                            } else {
                                // Final retry: minimal config
                                player.src('<?php echo e($streamUrl); ?>');
                            }
                            
                            player.load();
                        }, 3000 + (retryCount * 2000)); // Increasing delay
                    } else {
                        console.error('Max retries reached. Stream playback failed.');
                        
                        // Show user-friendly error message
                        const errorDisplay = player.createEl('div', {
                            className: 'vjs-error-display',
                            innerHTML: '<p>Error al cargar el stream. Verifique su conexión a internet.</p>'
                        });
                        player.el().appendChild(errorDisplay);
                    }
                });

                // Event listeners for debugging and monitoring
                player.on('loadstart', function() {
                    console.log('Stream loading started');
                    retryCount = 0; // Reset retry count on successful load start
                });

                player.on('canplay', function() {
                    console.log('Stream can start playing');
                });

                player.on('playing', function() {
                    console.log('Stream is playing');
                });

                player.on('waiting', function() {
                    console.log('Stream is buffering...');
                });

                player.on('stalled', function() {
                    console.log('Stream stalled');
                });

                // Store globally
                window.tucanaltv_player = player;
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializePlayer);
            } else {
                initializePlayer();
            }
        })();
    </script>
<?php $__env->stopPush(); ?><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/components/video-player.blade.php ENDPATH**/ ?>