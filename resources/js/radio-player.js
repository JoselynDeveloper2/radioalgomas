const MAX_RETRIES = 3;
const STALL_TIMEOUT_MS = 15000;
const BAR_SPACING = 8; // px CSS por barra; la cantidad de barras sigue el ancho disponible

/**
 * Niveles 0–1 por barra a partir del espectro. Reparto por curva de potencia (más barras para
 * medios y agudos, que es donde la música varía) y contraste para que no todo llegue al tope.
 */
export function spectrumLevels(bins, bars) {
    const usable = Math.floor(bins.length * 0.75); // el último cuarto casi no tiene energía
    const levels = new Array(bars);
    for (let i = 0; i < bars; i++) {
        const lo = Math.floor(1 + (usable - 1) * (i / bars) ** 1.5);
        const hi = Math.max(lo + 1, Math.floor(1 + (usable - 1) * ((i + 1) / bars) ** 1.5));
        let peak = 0;
        for (let b = lo; b < hi; b++) peak = Math.max(peak, bins[b]);
        levels[i] = Math.min(0.92, (peak / 255) ** 1.6);
    }
    return levels;
}

export function initRadioPlayer(root) {
    const $ = (selector) => root.querySelector(selector);
    const audio = $('audio');
    const toggleBtn = $('[data-radio-toggle]');
    const muteBtn = $('[data-radio-mute]');
    const volumeInput = $('[data-radio-volume]');
    const volumeLabel = $('[data-radio-volume-label]');
    const statusText = $('[data-radio-status]');
    const errorBox = $('[data-radio-error]');
    const canvas = $('[data-radio-canvas]');
    const shareLabel = $('[data-radio-share-label]');
    const { stream, station, show, artwork } = root.dataset;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let wantPlaying = false;
    let retries = 0;
    let retryTimer = null;
    let stallTimer = null;
    // Sin CORS el visualizador no puede leer el audio: se reintenta sin crossorigin y queda el ecualizador CSS.
    let useCors = true;
    let corsVerified = false;
    let audioCtx = null;
    let analyser = null;
    let frame = null;

    function setState(state, message) {
        root.dataset.state = state;
        statusText.textContent = message;
        errorBox.hidden = state !== 'error';
        const active = state === 'playing' || state === 'loading';
        toggleBtn.setAttribute('aria-label', active ? 'Pausar radio en vivo' : 'Reproducir radio en vivo');
        if ('mediaSession' in navigator) {
            navigator.mediaSession.playbackState = active ? 'playing' : 'paused';
        }
        if (state === 'playing') startVisualizer();
        else stopVisualizer();
    }

    function clearTimers() {
        clearTimeout(retryTimer);
        clearTimeout(stallTimer);
    }

    function disconnect() {
        audio.pause();
        audio.removeAttribute('src');
        audio.load();
    }

    // Cada conexión pide el stream de nuevo para escuchar el directo, no un búfer viejo.
    function connect(message = 'Conectando…') {
        clearTimers();
        if (useCors) audio.crossOrigin = 'anonymous';
        else audio.removeAttribute('crossorigin');
        audio.src = stream;
        setState('loading', message);
        audio.play().catch((error) => {
            if (error.name === 'NotAllowedError') {
                wantPlaying = false;
                setState('idle', 'Pulsa play para escuchar');
            }
        });
    }

    // Sin gesto del usuario el AudioContext nace suspendido; conectar el audio a él lo silenciaría,
    // así que el analizador solo se engancha cuando el contexto ya corre.
    function enableAudioGraph() {
        if (!audioCtx && !reduceMotion && window.AudioContext) {
            audioCtx = new AudioContext();
        }
        audioCtx?.resume().then(attachIfReady).catch(() => {});
    }

    function attachIfReady() {
        if (audioCtx?.state !== 'running' || !audio.crossOrigin || root.dataset.state !== 'playing') return;
        attachAnalyser();
        startVisualizer();
    }

    function play() {
        wantPlaying = true;
        retries = 0;
        enableAudioGraph();
        connect();
    }

    // Chrome solo deja arrancar con sonido a visitantes que ya escuchan seguido; al resto se le muestra el botón.
    function autoplay() {
        wantPlaying = true;
        connect();
        document.addEventListener('pointerdown', enableAudioGraph, { once: true });
    }

    function unmute() {
        if (Number(volumeInput.value) === 0) {
            volumeInput.value = 80;
            applyVolume();
        }
        applyMuted(false);
    }

    function stop(message = 'En pausa') {
        wantPlaying = false;
        clearTimers();
        disconnect();
        setState('idle', message);
    }

    function retry() {
        if (!wantPlaying) return;
        if (retries >= MAX_RETRIES) {
            wantPlaying = false;
            clearTimers();
            disconnect();
            setState('error', 'Sin conexión con la señal');
            return;
        }
        retries += 1;
        setState('loading', `Reconectando… (${retries}/${MAX_RETRIES})`);
        retryTimer = setTimeout(() => connect(`Reconectando… (${retries}/${MAX_RETRIES})`), 1000 * 2 ** retries);
    }

    audio.addEventListener('playing', () => {
        clearTimers();
        retries = 0;
        if (audio.crossOrigin) corsVerified = true;
        setState('playing', 'En directo');
        attachIfReady();
    });

    audio.addEventListener('waiting', () => {
        if (!wantPlaying) return;
        setState('loading', 'Cargando…');
        clearTimeout(stallTimer);
        stallTimer = setTimeout(retry, STALL_TIMEOUT_MS);
    });

    audio.addEventListener('error', () => {
        if (!wantPlaying) return;
        if (useCors && !corsVerified) {
            useCors = false;
            connect();
            return;
        }
        retry();
    });

    audio.addEventListener('ended', retry);

    // Pausa externa: auriculares desconectados, otra app de audio, etc.
    audio.addEventListener('pause', () => {
        if (wantPlaying && root.dataset.state === 'playing') stop();
    });

    toggleBtn.addEventListener('click', () => (wantPlaying ? stop() : play()));
    $('[data-radio-retry]').addEventListener('click', play);

    // Volumen
    const savedVolume = readStorage('radio-volume');
    if (savedVolume !== null) volumeInput.value = savedVolume;

    function applyVolume() {
        audio.volume = volumeInput.value / 100;
        volumeLabel.textContent = `${volumeInput.value}%`;
        volumeInput.setAttribute('aria-valuetext', `${volumeInput.value}%`);
    }

    function applyMuted(muted) {
        audio.muted = muted;
        muteBtn.dataset.muted = String(muted);
        muteBtn.setAttribute('aria-label', muted ? 'Activar sonido' : 'Silenciar');
    }

    volumeInput.addEventListener('input', () => {
        applyVolume();
        writeStorage('radio-volume', volumeInput.value);
        if (Number(volumeInput.value) === 0) applyMuted(true);
        else if (audio.muted) unmute();
    });
    muteBtn.addEventListener('click', () => (audio.muted ? unmute() : applyMuted(true)));
    applyVolume();

    // Compartir
    $('[data-radio-share]').addEventListener('click', async () => {
        const url = `${window.location.origin}/`;
        if (navigator.share) {
            try {
                await navigator.share({ title: station, text: `Escucha ${station} en vivo`, url });
            } catch {}
            return;
        }
        try {
            await navigator.clipboard.writeText(url);
            flashShare('Enlace copiado');
        } catch {
            flashShare('No se pudo copiar');
        }
    });

    function flashShare(message) {
        shareLabel.textContent = message;
        setTimeout(() => (shareLabel.textContent = 'Compartir'), 2000);
    }

    // Controles del sistema (pantalla de bloqueo, teclas multimedia)
    if ('mediaSession' in navigator) {
        navigator.mediaSession.metadata = new MediaMetadata({
            title: show,
            artist: station,
            artwork: [{ src: artwork, sizes: '180x180', type: 'image/png' }],
        });
        navigator.mediaSession.setActionHandler('play', play);
        navigator.mediaSession.setActionHandler('pause', () => stop());
        navigator.mediaSession.setActionHandler('stop', () => stop());
    }

    // Visualizador en tiempo real (Web Audio)
    function attachAnalyser() {
        if (!audioCtx || analyser) return;
        try {
            const source = audioCtx.createMediaElementSource(audio);
            analyser = audioCtx.createAnalyser();
            analyser.fftSize = 256;
            analyser.smoothingTimeConstant = 0.82;
            source.connect(analyser);
            analyser.connect(audioCtx.destination);
            bins = new Uint8Array(analyser.frequencyBinCount);
            root.dataset.visualizer = 'live';
        } catch {
            analyser = null;
        }
    }

    const ctx2d = canvas.getContext('2d');
    let bins = null;

    function resizeCanvas() {
        const ratio = window.devicePixelRatio || 1;
        canvas.width = canvas.clientWidth * ratio;
        canvas.height = canvas.clientHeight * ratio;
    }
    new ResizeObserver(resizeCanvas).observe(canvas);

    function draw() {
        frame = requestAnimationFrame(draw);
        analyser.getByteFrequencyData(bins);
        const { width, height } = canvas;
        const ratio = window.devicePixelRatio || 1;
        const bars = Math.max(24, Math.min(96, Math.floor(width / ratio / BAR_SPACING))) & ~1;
        // Espejo: graves al centro y agudos hacia los lados, crecen desde la línea media.
        const half = spectrumLevels(bins, bars / 2);
        const levels = [...half.slice().reverse(), ...half];
        const slot = width / bars;
        const barWidth = Math.max(ratio, slot * 0.55);
        const middle = height / 2;
        ctx2d.clearRect(0, 0, width, height);
        for (let i = 0; i < bars; i++) {
            const barHeight = Math.max(barWidth, levels[i] * height);
            ctx2d.fillStyle = `rgba(255, 255, 255, ${0.35 + levels[i] * 0.65})`;
            ctx2d.beginPath();
            ctx2d.roundRect(i * slot + (slot - barWidth) / 2, middle - barHeight / 2, barWidth, barHeight, barWidth / 2);
            ctx2d.fill();
        }
    }

    function startVisualizer() {
        if (analyser && !frame) draw();
    }

    function stopVisualizer() {
        cancelAnimationFrame(frame);
        frame = null;
        ctx2d.clearRect(0, 0, canvas.width, canvas.height);
    }

    if (root.dataset.autoplay === 'true' && !navigator.connection?.saveData) {
        autoplay();
    }
}

function readStorage(key) {
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
}

function writeStorage(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch {}
}
