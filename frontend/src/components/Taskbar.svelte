<script>
    import { createEventDispatcher, onMount, onDestroy } from "svelte";
    import { windows, activeWindowId } from "../stores/windows.js";
    import { Volume2, Wifi, Shield } from "lucide-svelte";

    const dispatch = createEventDispatcher();

    export let startMenuOpen = false;

    let clock = "";
    let clockInterval;

    onMount(() => {
        updateClock();
        clockInterval = setInterval(updateClock, 1000);
    });

    onDestroy(() => {
        clearInterval(clockInterval);
    });

    function updateClock() {
        const now = new Date();
        clock = now.toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });
    }

    function handleStartClick(e) {
        e.stopPropagation();
        dispatch("toggleStart");
    }

    function handleTaskbarWindowClick(win) {
        if (win.minimized) {
            windows.restore(win.id);
        } else if ($activeWindowId === win.id) {
            windows.minimize(win.id);
        } else {
            windows.focus(win.id);
        }
    }
</script>

<div class="taskbar">
    <button
        class="start-button"
        class:active={startMenuOpen}
        on:click={handleStartClick}
    >
        <div class="start-flag">
            <div class="sf r"></div>
            <div class="sf g"></div>
            <div class="sf b"></div>
            <div class="sf y"></div>
        </div>
        <span>start</span>
    </button>

    <div class="quick-launch"></div>

    <div class="taskbar-windows">
        {#each $windows as win (win.id)}
            <button
                class="taskbar-window-btn"
                class:active={$activeWindowId === win.id && !win.minimized}
                on:click={() => handleTaskbarWindowClick(win)}
            >
                <span class="taskbar-win-title">{win.title}</span>
            </button>
        {/each}
    </div>

    <div class="system-tray">
        <div class="tray-icons">
            <Shield size={14} color="#fff" />
            <Wifi size={14} color="#fff" />
            <Volume2 size={14} color="#fff" />
        </div>
        <div class="clock">{clock}</div>
    </div>
</div>

<style>
    .taskbar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 36px;
        background: linear-gradient(
            180deg,
            #3168d5 0%,
            #2456c7 4%,
            #1f4dbe 8%,
            #245edc 80%,
            #1b4ab5 95%,
            #183fa0 100%
        );
        display: flex;
        align-items: center;
        z-index: 10000;
        border-top: 1px solid #5c91e8;
        box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.2);
    }

    .start-button {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 2px 14px 2px 8px;
        height: 30px;
        margin: 0 4px;
        background: linear-gradient(
            180deg,
            #3f8f3c 0%,
            #328e2f 30%,
            #2d8b2a 50%,
            #277f24 80%,
            #1e6b1b 100%
        );
        border: none;
        border-radius: 0 8px 8px 0;
        color: white;
        font-size: 13px;
        font-weight: 700;
        font-style: italic;
        font-family: "Segoe UI", Tahoma, sans-serif;
        cursor: pointer;
        letter-spacing: 0.6px;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    .start-button:hover {
        background: linear-gradient(
            180deg,
            #4ba347 0%,
            #3da339 30%,
            #39a035 50%,
            #329430 80%,
            #2a802a 100%
        );
    }
    .start-button.active {
        background: linear-gradient(
            180deg,
            #2a7527 0%,
            #247122 50%,
            #1e5d1b 100%
        );
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.4);
    }

    .start-flag {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1px;
        width: 18px;
        height: 18px;
        transform: rotate(-8deg);
    }
    .sf {
        border-radius: 1px;
    }
    .sf.r {
        background: #f24d2b;
        border-radius: 1px 1px 1px 30%;
    }
    .sf.g {
        background: #69bd46;
        border-radius: 1px 1px 30% 1px;
    }
    .sf.b {
        background: #3f9cdf;
        border-radius: 1px 30% 1px 1px;
    }
    .sf.y {
        background: #ffc20e;
        border-radius: 30% 1px 1px 1px;
    }

    .quick-launch {
        width: 4px;
        height: 24px;
        border-left: 1px solid rgba(255, 255, 255, 0.15);
        border-right: 1px solid rgba(0, 0, 0, 0.15);
        margin: 0 4px;
    }

    .taskbar-windows {
        flex: 1;
        display: flex;
        gap: 2px;
        padding: 0 4px;
        overflow: hidden;
    }

    .taskbar-window-btn {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 2px 10px;
        height: 26px;
        max-width: 180px;
        min-width: 60px;
        background: linear-gradient(
            180deg,
            #3e7ad7 0%,
            #3570cc 50%,
            #2d60b5 100%
        );
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 3px;
        color: white;
        font-size: 11px;
        cursor: pointer;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: var(--xp-font);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }
    .taskbar-window-btn.active {
        background: linear-gradient(
            180deg,
            #1a4faa 0%,
            #1845a0 50%,
            #153c8e 100%
        );
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
        border-color: rgba(0, 0, 0, 0.3);
    }
    .taskbar-window-btn:hover {
        background: linear-gradient(
            180deg,
            #4e8ae5 0%,
            #4580dc 50%,
            #3d70c5 100%
        );
    }

    .taskbar-win-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .system-tray {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 12px;
        height: 100%;
        background: linear-gradient(
            180deg,
            #1290e9 0%,
            #0f7ad4 50%,
            #0c66b0 100%
        );
        border-left: 1px solid rgba(0, 0, 0, 0.15);
    }

    .tray-icons {
        display: flex;
        gap: 6px;
        align-items: center;
        opacity: 0.9;
    }

    .clock {
        color: white;
        font-size: 11px;
        font-weight: 700;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        white-space: nowrap;
    }
</style>
