<script>
    import { createEventDispatcher, onMount } from "svelte";

    const dispatch = createEventDispatcher();
    let progress = 0;

    onMount(() => {
        const interval = setInterval(() => {
            progress += Math.random() * 15 + 5;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                setTimeout(() => dispatch("complete"), 400);
            }
        }, 300);

        return () => clearInterval(interval);
    });
</script>

<div class="boot-screen">
    <div class="boot-content">
        <div class="xp-flag">
            <div class="flag-piece red"></div>
            <div class="flag-piece green"></div>
            <div class="flag-piece blue"></div>
            <div class="flag-piece yellow"></div>
        </div>
        <div class="xp-logo">
            <span class="microsoft">Microsoft</span>
            <span class="windows">Windows</span><span class="xp">XP</span>
        </div>
        <div class="progress-container">
            <div class="progress-track">
                <div
                    class="progress-blocks"
                    style="width: {Math.min(progress, 100)}%"
                >
                    <div class="blocks-inner"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="boot-footer">
        <p>Copyright © Microsoft Corporation</p>
    </div>
</div>

<style>
    .boot-screen {
        width: 100vw;
        height: 100vh;
        background: #000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .boot-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .xp-flag {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
        width: 50px;
        height: 50px;
        transform: rotate(-10deg);
        margin-bottom: 8px;
    }
    .flag-piece {
        border-radius: 2px;
    }
    .flag-piece.red {
        background: #f24d2b;
        border-radius: 2px 2px 2px 40%;
    }
    .flag-piece.green {
        background: #69bd46;
        border-radius: 2px 2px 40% 2px;
    }
    .flag-piece.blue {
        background: #3f9cdf;
        border-radius: 2px 40% 2px 2px;
    }
    .flag-piece.yellow {
        background: #ffc20e;
        border-radius: 40% 2px 2px 2px;
    }

    .xp-logo {
        display: flex;
        align-items: baseline;
        gap: 6px;
        margin-bottom: 10px;
    }
    .microsoft {
        color: #fff;
        font-size: 20px;
        font-weight: 300;
        font-family: "Segoe UI", Tahoma, sans-serif;
        letter-spacing: 1px;
    }
    .windows {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        font-family: "Segoe UI", Tahoma, sans-serif;
        font-style: italic;
    }
    .xp {
        color: #ff6600;
        font-size: 36px;
        font-weight: 700;
        font-family: "Segoe UI", Tahoma, sans-serif;
        font-style: italic;
        text-shadow: 0 0 10px rgba(255, 102, 0, 0.5);
    }

    .progress-container {
        margin-top: 20px;
    }
    .progress-track {
        width: 220px;
        height: 20px;
        background: #000;
        border: 1px solid #333;
        border-radius: 3px;
        overflow: hidden;
        padding: 2px;
    }
    .progress-blocks {
        height: 100%;
        background: repeating-linear-gradient(
            90deg,
            #0058e6 0px,
            #0058e6 8px,
            transparent 8px,
            transparent 11px
        );
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .boot-footer {
        position: absolute;
        bottom: 30px;
        color: #666;
        font-size: 10px;
    }
</style>
