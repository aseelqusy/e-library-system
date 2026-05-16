/**
 * Luminara Library — Music Player
 * Floating mini player with random playlist, ambient mode
 */

const MusicPlayer = (() => {
    const STORAGE_KEY = 'luminara_music_prefs';

    const playlist = [
        { title: 'Quiet Pages',          artist: 'Ambient Library',   file: 'quiet-pages.mp3' },
        { title: 'Rain on Bookshelf',    artist: 'LoFi Reads',       file: 'rain-bookshelf.mp3' },
        { title: 'Candlelight Study',    artist: 'Study Beats',      file: 'candlelight-study.mp3' },
        { title: 'The Reading Room',     artist: 'Café Ambience',    file: 'reading-room.mp3' },
        { title: 'Chapter Drift',        artist: 'Chill Keys',       file: 'chapter-drift.mp3' },
        { title: 'Midnight Library',     artist: 'Dream Waves',      file: 'midnight-library.mp3' },
        { title: 'Soft Ink Flow',        artist: 'Piano Whispers',   file: 'soft-ink-flow.mp3' },
        { title: 'Between the Lines',    artist: 'Ambient Library',  file: 'between-lines.mp3' },
    ];

    let state = {
        playing: false,
        currentIndex: 0,
        volume: 0.5,
        muted: false,
        collapsed: true,
        ambient: false,
        progress: 0,
    };

    let player = null;
    let progressInterval = null;

    function loadPrefs() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const prefs = JSON.parse(saved);
                state.volume = prefs.volume ?? 0.5;
                state.muted = prefs.muted ?? false;
                state.ambient = prefs.ambient ?? false;
                state.collapsed = prefs.collapsed ?? true;
            }
        } catch(e) { /* ignore */ }
    }

    function savePrefs() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                volume: state.volume,
                muted: state.muted,
                ambient: state.ambient,
                collapsed: state.collapsed,
            }));
        } catch(e) { /* ignore */ }
    }

    function buildDOM() {
        player = document.createElement('div');
        player.className = 'music-player' + (state.collapsed ? ' collapsed' : '');
        if (state.ambient) player.classList.add('ambient-active');
        player.setAttribute('role', 'region');
        player.setAttribute('aria-label', 'Music Player');

        const track = playlist[state.currentIndex];

        player.innerHTML = `
            <button class="player-toggle" aria-label="Toggle music player" title="Toggle music player">♫</button>
            <div class="player-header">
                <div class="track-info">
                    <div class="track-name">${escapeHtml(track.title)}</div>
                    <div class="track-artist">${escapeHtml(track.artist)}</div>
                </div>
            </div>
            <div class="player-body">
                <div class="progress-bar" role="slider" aria-label="Track progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="progress-fill"></div>
                </div>
                <div class="time-display">
                    <span class="time-current">0:00</span>
                    <span class="time-total">3:45</span>
                </div>
                <div class="player-controls">
                    <button class="ctrl-btn shuffle-btn" aria-label="Shuffle" title="Shuffle">⇄</button>
                    <button class="ctrl-btn prev-btn" aria-label="Previous" title="Previous">⏮</button>
                    <button class="ctrl-btn play-btn" aria-label="Play" title="Play">▶</button>
                    <button class="ctrl-btn next-btn" aria-label="Next random" title="Next random">⏭</button>
                    <button class="ctrl-btn repeat-btn" aria-label="Repeat" title="Repeat">⟳</button>
                </div>
                <div class="volume-row">
                    <button class="volume-btn" aria-label="Toggle mute">${state.muted ? '🔇' : '🔊'}</button>
                    <input type="range" class="volume-slider" min="0" max="100" value="${state.muted ? 0 : state.volume * 100}" aria-label="Volume">
                </div>
                <label class="ambient-toggle">
                    <input type="checkbox" ${state.ambient ? 'checked' : ''}> Ambient glow mode
                </label>
            </div>
        `;

        document.body.appendChild(player);
        bindEvents();
    }

    function bindEvents() {
        // Toggle collapse
        player.querySelector('.player-toggle').addEventListener('click', e => {
            e.stopPropagation();
            state.collapsed = !state.collapsed;
            player.classList.toggle('collapsed', state.collapsed);
            savePrefs();
        });

        // Expand on click when collapsed
        player.addEventListener('click', e => {
            if (state.collapsed && e.target.closest('.player-toggle')) return;
            if (state.collapsed) {
                state.collapsed = false;
                player.classList.remove('collapsed');
                savePrefs();
            }
        });

        // Play / Pause
        player.querySelector('.play-btn').addEventListener('click', togglePlay);

        // Next
        player.querySelector('.next-btn').addEventListener('click', () => {
            nextRandom();
        });

        // Previous
        player.querySelector('.prev-btn').addEventListener('click', () => {
            state.currentIndex = (state.currentIndex - 1 + playlist.length) % playlist.length;
            updateTrack();
        });

        // Shuffle (same as next random)
        player.querySelector('.shuffle-btn').addEventListener('click', () => {
            nextRandom();
        });

        // Progress bar click
        player.querySelector('.progress-bar').addEventListener('click', e => {
            const rect = e.currentTarget.getBoundingClientRect();
            const pct = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            state.progress = pct * 100;
            updateProgressDisplay();
        });

        // Volume
        const volumeSlider = player.querySelector('.volume-slider');
        volumeSlider.addEventListener('input', e => {
            state.volume = parseInt(e.target.value) / 100;
            state.muted = state.volume === 0;
            player.querySelector('.volume-btn').textContent = state.muted ? '🔇' : '🔊';
            savePrefs();
        });

        // Volume mute toggle
        player.querySelector('.volume-btn').addEventListener('click', () => {
            state.muted = !state.muted;
            player.querySelector('.volume-btn').textContent = state.muted ? '🔇' : '🔊';
            volumeSlider.value = state.muted ? 0 : state.volume * 100;
            savePrefs();
        });

        // Ambient mode
        player.querySelector('.ambient-toggle input').addEventListener('change', e => {
            state.ambient = e.target.checked;
            player.classList.toggle('ambient-active', state.ambient);
            savePrefs();
        });
    }

    function togglePlay() {
        state.playing = !state.playing;
        const btn = player.querySelector('.play-btn');
        btn.textContent = state.playing ? '⏸' : '▶';
        btn.setAttribute('aria-label', state.playing ? 'Pause' : 'Play');
        btn.setAttribute('title', state.playing ? 'Pause' : 'Play');

        if (state.playing) {
            startProgress();
        } else {
            stopProgress();
        }
    }

    function startProgress() {
        stopProgress();
        progressInterval = setInterval(() => {
            state.progress += 0.15;
            if (state.progress >= 100) {
                state.progress = 0;
                nextRandom();
                return;
            }
            updateProgressDisplay();
        }, 50);
    }

    function stopProgress() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
    }

    function updateProgressDisplay() {
        const fill = player.querySelector('.progress-fill');
        if (fill) fill.style.width = state.progress + '%';

        const totalSec = 225; // 3:45
        const currentSec = Math.floor((state.progress / 100) * totalSec);
        const currentMin = Math.floor(currentSec / 60);
        const currentRemSec = currentSec % 60;

        const timeCurrent = player.querySelector('.time-current');
        if (timeCurrent) {
            timeCurrent.textContent = `${currentMin}:${String(currentRemSec).padStart(2, '0')}`;
        }
    }

    function nextRandom() {
        let next;
        do {
            next = Math.floor(Math.random() * playlist.length);
        } while (next === state.currentIndex && playlist.length > 1);

        state.currentIndex = next;
        state.progress = 0;
        updateTrack();
    }

    function updateTrack() {
        const track = playlist[state.currentIndex];
        player.querySelector('.track-name').textContent = track.title;
        player.querySelector('.track-artist').textContent = track.artist;
        state.progress = 0;
        updateProgressDisplay();

        if (state.playing) {
            stopProgress();
            startProgress();
        }
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function init() {
        loadPrefs();
        // Randomize starting track
        state.currentIndex = Math.floor(Math.random() * playlist.length);
        buildDOM();
    }

    document.addEventListener('DOMContentLoaded', init);

    return { togglePlay, nextRandom };
})();
