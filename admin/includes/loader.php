<div id="rj-global-loader" class="rj-loader-overlay">
    <div class="rj-spinner-wrapper">
        <div class="rj-spinner outer"></div>
        <div class="rj-spinner inner"></div>

        <div class="rj-loader-text">RJ</div>
    </div>
</div>

<style>
/* ─── GLOBAL SPINNING LOADER ─────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&display=swap');

.rj-loader-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background-color: #050608;
    /* Deep RJ Motorworld dark theme */
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.rj-loader-overlay.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.rj-spinner-wrapper {
    position: relative;
    width: 76px;
    height: 76px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rj-spinner {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid transparent;
}

/* Outer ring - spins clockwise */
.rj-spinner.outer {
    border-top-color: #c9a84c;
    /* RJ Gold */
    border-right-color: rgba(201, 168, 76, 0.15);
    /* Faint gold track */
    border-bottom-color: rgba(201, 168, 76, 0.15);
    border-left-color: rgba(201, 168, 76, 0.15);
    animation: rj-spin-clockwise 1.2s linear infinite;
}

/* Inner ring - spins counter-clockwise */
.rj-spinner.inner {
    inset: 8px;
    /* Slightly smaller than outer ring */
    border-bottom-color: #e2c97e;
    /* Lighter gold */
    border-left-color: rgba(226, 201, 126, 0.1);
    border-top-color: rgba(226, 201, 126, 0.1);
    border-right-color: rgba(226, 201, 126, 0.1);
    animation: rj-spin-counter 0.9s linear infinite;
}

.rj-loader-text {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 600;
    color: #e8e2d6;
    letter-spacing: -1px;
    margin-right: 1px;
    /* Optical centering for the negative letter spacing */
    animation: rj-pulse-text 2s ease-in-out infinite;
    z-index: 2;
}

/* Animations */
@keyframes rj-spin-clockwise {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

@keyframes rj-spin-counter {
    0% {
        transform: rotate(360deg);
    }

    100% {
        transform: rotate(0deg);
    }
}

@keyframes rj-pulse-text {

    0%,
    100% {
        opacity: 0.5;
        filter: drop-shadow(0 0 0 rgba(201, 168, 76, 0));
    }

    50% {
        opacity: 1;
        filter: drop-shadow(0 0 8px rgba(201, 168, 76, 0.4));
    }
}
</style>

<script>
(function() {
    const loader = document.getElementById('rj-global-loader');
    if (!loader) return;

    // Function to hide the loader smoothly
    const hideLoader = () => {
        loader.classList.add('hidden');
    };

    // Listen for the page to fully load all assets (images, CSS, etc.)
    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader);
    }

    // Fallback: Force hide the loader after 6 seconds so the user is never permanently stuck
    setTimeout(hideLoader, 6000);
})();
</script>