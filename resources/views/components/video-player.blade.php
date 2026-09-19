@props(['streamUrl' => null, 'width' => '100%', 'height' => '315'])

@php
    use App\Models\Player;
    use App\Models\PlayerUrl;

    // Get stream URL with fallback system
    $useProxy = false;
    $activePlayerUrl = null;
    if (!$streamUrl) {
        // Try new PlayerUrl system first
        $activePlayerUrl = PlayerUrl::getActive();
        if ($activePlayerUrl) {
            $originalUrl = $activePlayerUrl->url;
            // Use original URL directly with enhanced CORS handling
            $streamUrl = $originalUrl;
            $useProxy = false; // Let Video.js handle CORS with configuration
        } else {
            // Fallback to old Player system (solo si hay un registro activo en BD)
            $activePlayer = Player::where('is_active', true)->first();
            $streamUrl = $activePlayer?->stream_url;
        }
    }

    // Si no hay stream activo en BD, reproducir cualquier .mp4 local de public/video/
    $isLocalVideo = false;
    if (!$streamUrl) {
        $localVideos = glob(public_path('video/*.mp4')) ?: [];
        if (!empty($localVideos)) {
            sort($localVideos);
            $streamUrl = asset('video/' . basename($localVideos[0]));
            $isLocalVideo = true;
        }
    }

    // Detectar el tipo de fuente: .mp4 -> video/mp4, resto -> HLS
    $streamPath = $streamUrl ? (parse_url($streamUrl, PHP_URL_PATH) ?: $streamUrl) : '';
    $isMp4 = $streamUrl && str_ends_with(strtolower($streamPath), '.mp4');
    $sourceType = $isMp4 ? 'video/mp4' : 'application/x-mpegURL';

    // Additional metadata for enhanced player
    $playerMetadata = [];
    if ($activePlayerUrl) {
        $playerMetadata = [
            'name' => $activePlayerUrl->name,
            'last_tested' => $activePlayerUrl->last_tested_at?->diffForHumans(),
            'response_time' => $activePlayerUrl->test_response_time,
            'original_url' => $activePlayerUrl->url,
            'using_proxy' => $useProxy,
        ];
    }
@endphp

<div class="video-player-container mb-8">
    <div class="wrapper">
        <div class="videocontent">
            <video 
                id="videojs_player" 
                class="video-js vjs-default-skin vjs-16-9" 
                controls 
                preload="auto" 
                width="{{ $width }}" 
                height="{{ $height }}" 
                autoplay
                muted
                loop
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

@push('head')
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet" />
    <style>
        .video-player-container .wrapper {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Caja fija 16:9: el tamaño del contenedor NO depende de la
           resolución de la fuente (HLS 16:9, mp4 cuadrado, vertical...). */
        .video-player-container .videocontent {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            overflow: hidden;
        }

        @supports not (aspect-ratio: 16 / 9) {
            .video-player-container .videocontent::before {
                content: '';
                display: block;
                padding-top: 56.25%;
            }
        }

        .video-player-container .video-js {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
            max-width: 100%;
        }

        /* El video se ajusta dentro de la caja sin deformarla ni desbordarla */
        .video-player-container .video-js .vjs-tech,
        .video-player-container .video-js video {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
            background: #000;
        }

        .video-player-container .video-js .vjs-poster {
            background-size: contain;
        }

        @media (max-width: 768px) {
            .video-player-container {
                margin-bottom: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
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

                // Fuente activa resuelta en PHP (stream HLS de BD o .mp4 local)
                const streamUrl = @json($streamUrl);
                const sourceType = @json($sourceType);
                const isMp4 = {{ $isMp4 ? 'true' : 'false' }};

                if (!streamUrl) {
                    console.warn('TuCanalTV: no hay stream activo ni video local en public/video/');
                    return;
                }

                // Enhanced configuration for external HLS streams
                const player = videojs('videojs_player', {
                    controls: true,
                    autoplay: true,
                    preload: 'auto',
                    muted: true,
                    loop: true,
                    // fill: el player ocupa la caja 16:9 del contenedor,
                    // en vez de dimensionarse según la resolución de la fuente
                    fluid: false,
                    fill: true,
                    responsive: true,
                    liveui: !isMp4,
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
                    @if(!empty($playerMetadata))
                        console.log('Stream Info:', {
                            name: '{{ $playerMetadata['name'] ?? '' }}',
                            lastTested: '{{ $playerMetadata['last_tested'] ?? '' }}',
                            responseTime: '{{ $playerMetadata['response_time'] ?? '' }}ms',
                            originalUrl: '{{ $playerMetadata['original_url'] ?? '' }}',
                            usingProxy: {{ $playerMetadata['using_proxy'] ? 'true' : 'false' }},
                            streamUrl: streamUrl
                        });
                    @endif

                    // Set source like in original project
                    player.src({
                        src: streamUrl,
                        type: sourceType,
                        label: 'HD',
                        res: 1080
                    });

                    // Ensure muted + loop for autoplay
                    player.muted(true);
                    player.loop(true);
                    player.play()?.catch(function(e) {
                        console.log('Autoplay bloqueado por el navegador:', e);
                    });
                });

                // Bucle infinito: reiniciar si el navegador ignora el atributo loop
                player.on('ended', function() {
                    player.currentTime(0);
                    player.play()?.catch(function() {});
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
                                    src: streamUrl,
                                    type: sourceType
                                });
                            } else if (retryCount === 2) {
                                // Second retry: try with different type hint
                                player.src({
                                    src: streamUrl,
                                    type: isMp4 ? 'video/mp4' : 'application/vnd.apple.mpegurl'
                                });
                            } else {
                                // Final retry: minimal config
                                player.src(streamUrl);
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
@endpush