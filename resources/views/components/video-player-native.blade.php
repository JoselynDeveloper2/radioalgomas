@props(['streamUrl' => null, 'width' => '100%', 'height' => '315'])

@php
    use App\Models\Player;
    $streamUrl = $streamUrl ?: Player::getActiveStreamUrl();
@endphp

<div class="video-player-container mb-8">
    <div class="wrapper">
        <div class="videocontent">
            <video 
                id="native_video_player" 
                class="w-full h-auto rounded-lg"
                controls 
                autoplay 
                muted 
                playsinline
                preload="auto"
                style="width: {{ $width }}; height: {{ $height }};"
                onloadstart="console.log('Native video loading...')"
                oncanplay="console.log('Native video can play')"
                onerror="console.log('Native video error:', this.error)">
                <source src="{{ $streamUrl }}" type="application/x-mpegURL">
                <source src="{{ $streamUrl }}" type="video/mp4">
                <p class="text-center text-gray-500 p-4">
                    Tu navegador no soporta streaming de video HTML5.
                    <br>
                    <a href="{{ $streamUrl }}" target="_blank" class="text-blue-600 underline">
                        Abrir stream en nueva ventana
                    </a>
                </p>
            </video>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('native_video_player');
    if (video) {
        // Try to play when loaded
        video.addEventListener('loadedmetadata', function() {
            console.log('Video metadata loaded');
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    console.log('Native autoplay successful');
                }).catch(function(error) {
                    console.log('Native autoplay failed:', error);
                });
            }
        });

        // Retry on error
        video.addEventListener('error', function(e) {
            console.log('Native video error:', e);
            setTimeout(function() {
                console.log('Retrying native video...');
                video.load();
            }, 3000);
        });

        // Log events
        video.addEventListener('playing', function() {
            console.log('Native video is playing');
        });
    }
});
</script>