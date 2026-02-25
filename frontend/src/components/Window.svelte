<script>
    import { windows, activeWindowId } from "../stores/windows.js";
    import { X, Minus, Square, Maximize2 } from "lucide-svelte";

    export let windowData;
    export let isActive = false;

    let dragging = false;
    let resizing = false;
    let dragOffset = { x: 0, y: 0 };
    let resizeStart = { x: 0, y: 0, w: 0, h: 0 };

    function handleMouseDown(e) {
        windows.focus(windowData.id);
    }

    function startDrag(e) {
        if (windowData.maximized) return;
        e.preventDefault();
        dragging = true;
        dragOffset = {
            x: e.clientX - windowData.x,
            y: e.clientY - windowData.y,
        };
        window.addEventListener("mousemove", onDrag);
        window.addEventListener("mouseup", stopDrag);
    }

    function onDrag(e) {
        if (!dragging) return;
        windows.updatePosition(
            windowData.id,
            e.clientX - dragOffset.x,
            e.clientY - dragOffset.y,
        );
    }

    function stopDrag() {
        dragging = false;
        window.removeEventListener("mousemove", onDrag);
        window.removeEventListener("mouseup", stopDrag);
    }

    function startResize(e) {
        if (windowData.maximized) return;
        e.preventDefault();
        e.stopPropagation();
        resizing = true;
        resizeStart = {
            x: e.clientX,
            y: e.clientY,
            w: windowData.width,
            h: windowData.height,
        };
        window.addEventListener("mousemove", onResize);
        window.addEventListener("mouseup", stopResize);
    }

    function onResize(e) {
        if (!resizing) return;
        const newW = Math.max(200, resizeStart.w + (e.clientX - resizeStart.x));
        const newH = Math.max(150, resizeStart.h + (e.clientY - resizeStart.y));
        windows.updateSize(windowData.id, newW, newH);
    }

    function stopResize() {
        resizing = false;
        window.removeEventListener("mousemove", onResize);
        window.removeEventListener("mouseup", stopResize);
    }

    function handleClose() {
        windows.close(windowData.id);
    }

    function handleMinimize() {
        windows.minimize(windowData.id);
    }

    function handleMaximize() {
        windows.toggleMaximize(windowData.id);
    }

    function handleTitleDblClick() {
        windows.toggleMaximize(windowData.id);
    }

    $: style = windowData.maximized
        ? `left: 0; top: 0; width: 100%; height: calc(100% - 36px); z-index: ${windowData.zIndex};`
        : `left: ${windowData.x}px; top: ${windowData.y}px; width: ${windowData.width}px; height: ${windowData.height}px; z-index: ${windowData.zIndex};`;

    $: hidden = windowData.minimized;
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div
    class="window"
    class:active={isActive}
    class:hidden
    class:maximized={windowData.maximized}
    {style}
    on:mousedown={handleMouseDown}
>
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div
        class="title-bar"
        on:mousedown={startDrag}
        on:dblclick={handleTitleDblClick}
    >
        <span class="title-text">{windowData.title}</span>
        <div class="title-buttons">
            <button
                class="title-btn minimize"
                on:mousedown|stopPropagation
                on:click={handleMinimize}
                title="Minimize"
            >
                <Minus size={10} strokeWidth={2.5} />
            </button>
            <button
                class="title-btn maximize"
                on:mousedown|stopPropagation
                on:click={handleMaximize}
                title="Maximize"
            >
                {#if windowData.maximized}
                    <Square size={9} strokeWidth={2} />
                {:else}
                    <Maximize2 size={10} strokeWidth={2} />
                {/if}
            </button>
            <button
                class="title-btn close"
                on:mousedown|stopPropagation
                on:click={handleClose}
                title="Close"
            >
                <X size={12} strokeWidth={2.5} />
            </button>
        </div>
    </div>

    <div class="window-body">
        <slot />
    </div>

    {#if !windowData.maximized}
        <!-- svelte-ignore a11y-no-static-element-interactions -->
        <div class="resize-handle" on:mousedown={startResize}></div>
    {/if}
</div>

<style>
    .window {
        position: absolute;
        background: var(--xp-window-bg);
        border-radius: 8px 8px 0 0;
        box-shadow:
            2px 2px 10px rgba(0, 0, 0, 0.35),
            0 0 0 1px rgba(0, 60, 180, 0.5);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-width: 200px;
        min-height: 150px;
    }
    .window.hidden {
        display: none;
    }
    .window.maximized {
        border-radius: 0;
    }

    .title-bar {
        height: 30px;
        background: linear-gradient(
            180deg,
            #0f6ff5 0%,
            #0a58e0 15%,
            #0854d8 30%,
            #0650d0 55%,
            #0a4ebf 70%,
            #0e56c3 85%,
            #1260d0 100%
        );
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 4px 0 8px;
        cursor: default;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid #003da0;
    }
    .window.maximized .title-bar {
        border-radius: 0;
    }
    .window:not(.active) .title-bar {
        background: linear-gradient(
            180deg,
            #9bafe0 0%,
            #8ba4d6 15%,
            #7b9acf 30%,
            #7693c5 55%,
            #7a96c0 70%,
            #8ea6cc 85%,
            #a0b5d8 100%
        );
    }

    .title-text {
        color: white;
        font-size: 12px;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        padding-right: 8px;
    }

    .title-buttons {
        display: flex;
        gap: 2px;
    }

    .title-btn {
        width: 22px;
        height: 22px;
        border: none;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        color: white;
    }

    .title-btn.minimize,
    .title-btn.maximize {
        background: linear-gradient(
            180deg,
            #3c8fef 0%,
            #2970d4 50%,
            #2060c0 100%
        );
        border: 1px solid rgba(0, 0, 0, 0.2);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
    .title-btn.minimize:hover,
    .title-btn.maximize:hover {
        background: linear-gradient(
            180deg,
            #5aa0f0 0%,
            #4085dd 50%,
            #3575cc 100%
        );
    }
    .title-btn.minimize:active,
    .title-btn.maximize:active {
        background: linear-gradient(
            180deg,
            #2060c0 0%,
            #1850a8 50%,
            #1040a0 100%
        );
    }

    .title-btn.close {
        background: linear-gradient(
            180deg,
            #e87161 0%,
            #d4503f 50%,
            #c63b28 100%
        );
        border: 1px solid rgba(0, 0, 0, 0.2);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
    .title-btn.close:hover {
        background: linear-gradient(
            180deg,
            #f08878 0%,
            #e06050 50%,
            #d04838 100%
        );
    }
    .title-btn.close:active {
        background: linear-gradient(
            180deg,
            #c03020 0%,
            #a82818 50%,
            #901810 100%
        );
    }

    .window-body {
        flex: 1;
        overflow: auto;
        position: relative;
        background: var(--xp-window-bg);
    }

    .resize-handle {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 16px;
        height: 16px;
        cursor: nwse-resize;
        background: linear-gradient(
            135deg,
            transparent 30%,
            var(--xp-button-shadow) 30%,
            var(--xp-button-shadow) 40%,
            transparent 40%,
            transparent 50%,
            var(--xp-button-shadow) 50%,
            var(--xp-button-shadow) 60%,
            transparent 60%,
            transparent 70%,
            var(--xp-button-shadow) 70%,
            var(--xp-button-shadow) 80%,
            transparent 80%
        );
    }
</style>
