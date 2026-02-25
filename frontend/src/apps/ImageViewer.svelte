<script>
    import { onMount } from "svelte";
    import {
        ChevronLeft,
        ChevronRight,
        ZoomIn,
        ZoomOut,
        RotateCcw,
        RotateCw,
        Trash2,
        Printer,
        Copy,
        Maximize,
        X,
    } from "lucide-svelte";
    import { getImageUrl, getDocument, listDocuments } from "../lib/api.js";

    export let windowId = null;
    export let documentId = null;

    let imageSrc = "";
    let fileName = "";
    let zoom = 100;
    let rotation = 0;
    let imageList = [];
    let currentIndex = 0;
    let loading = true;
    let naturalWidth = 0;
    let naturalHeight = 0;
    let canvasEl;

    onMount(async () => {
        try {
            const res = await listDocuments();
            if (res.ok) {
                imageList = res.data.filter((d) => d.type === "image");
                if (documentId) {
                    currentIndex = imageList.findIndex(
                        (d) => d.id === documentId,
                    );
                    if (currentIndex === -1) currentIndex = 0;
                }
                if (imageList.length > 0) {
                    await loadImage(imageList[currentIndex]);
                }
            }
        } catch (e) {
            console.error("Failed to load images:", e);
        }
        loading = false;
    });

    async function loadImage(doc) {
        fileName = doc.name;
        imageSrc = getImageUrl(doc.id);
        rotation = 0;

        // Load image to get natural dimensions, then best-fit
        const img = new window.Image();
        img.onload = () => {
            naturalWidth = img.naturalWidth;
            naturalHeight = img.naturalHeight;
            bestFit();
        };
        img.src = imageSrc;
    }

    function prevImage() {
        if (imageList.length < 2) return;
        currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
        loadImage(imageList[currentIndex]);
    }

    function nextImage() {
        if (imageList.length < 2) return;
        currentIndex = (currentIndex + 1) % imageList.length;
        loadImage(imageList[currentIndex]);
    }

    function zoomIn() {
        zoom = Math.min(zoom + 25, 500);
    }
    function zoomOut() {
        zoom = Math.max(zoom - 25, 10);
    }
    function actualSize() {
        zoom = 100;
        rotation = 0;
    }
    function bestFit() {
        if (!canvasEl || !naturalWidth || !naturalHeight) {
            zoom = 100;
            return;
        }
        const cw = canvasEl.clientWidth - 16;
        const ch = canvasEl.clientHeight - 16;
        const scaleW = cw / naturalWidth;
        const scaleH = ch / naturalHeight;
        zoom = Math.round(Math.min(scaleW, scaleH, 1) * 100);
    }
    function rotateCW() {
        rotation = (rotation + 90) % 360;
    }
    function rotateCCW() {
        rotation = (rotation - 90 + 360) % 360;
    }

    $: displayWidth = Math.round((naturalWidth * zoom) / 100);
    $: displayHeight = Math.round((naturalHeight * zoom) / 100);
</script>

<div class="photo-viewer">
    <div class="pv-canvas" bind:this={canvasEl}>
        {#if loading}
            <div class="pv-placeholder">Loading...</div>
        {:else if imageSrc && naturalWidth}
            <div class="pv-image-wrapper">
                <img
                    src={imageSrc}
                    alt={fileName}
                    class="pv-image"
                    width={displayWidth}
                    height={displayHeight}
                    style="transform: rotate({rotation}deg);"
                />
            </div>
        {:else if imageSrc}
            <div class="pv-placeholder">Loading...</div>
        {:else}
            <div class="pv-placeholder">No images to display</div>
        {/if}
    </div>

    <div class="pv-toolbar">
        <div class="pv-toolbar-inner">
            <!-- Previous / Next -->
            <div class="pv-btn-group">
                <button
                    class="pv-btn"
                    on:click={prevImage}
                    disabled={imageList.length < 2}
                    title="Previous Image (Left Arrow)"
                >
                    <ChevronLeft size={18} />
                </button>
                <button
                    class="pv-btn"
                    on:click={nextImage}
                    disabled={imageList.length < 2}
                    title="Next Image (Right Arrow)"
                >
                    <ChevronRight size={18} />
                </button>
            </div>

            <div class="pv-separator"></div>

            <!-- Zoom -->
            <div class="pv-btn-group">
                <button class="pv-btn" on:click={bestFit} title="Best Fit">
                    <Maximize size={16} />
                </button>
                <button
                    class="pv-btn"
                    on:click={actualSize}
                    title="Actual Size"
                >
                    <span class="pv-btn-text">1:1</span>
                </button>
                <button class="pv-btn" on:click={zoomIn} title="Zoom In">
                    <ZoomIn size={16} />
                </button>
                <button class="pv-btn" on:click={zoomOut} title="Zoom Out">
                    <ZoomOut size={16} />
                </button>
            </div>

            <div class="pv-separator"></div>

            <!-- Rotate -->
            <div class="pv-btn-group">
                <button
                    class="pv-btn"
                    on:click={rotateCW}
                    title="Rotate Clockwise"
                >
                    <RotateCw size={16} />
                </button>
                <button
                    class="pv-btn"
                    on:click={rotateCCW}
                    title="Rotate Counter-Clockwise"
                >
                    <RotateCcw size={16} />
                </button>
            </div>

            <div class="pv-separator"></div>

            <!-- Actions -->
            <div class="pv-btn-group">
                <button class="pv-btn" disabled title="Print">
                    <Printer size={16} />
                </button>
                <button class="pv-btn" disabled title="Copy To...">
                    <Copy size={16} />
                </button>
                <button class="pv-btn" disabled title="Delete">
                    <Trash2 size={16} />
                </button>
            </div>
        </div>

        <div class="pv-status">
            {#if imageList.length > 0}
                <span class="pv-counter"
                    >{currentIndex + 1} of {imageList.length}</span
                >
            {/if}
            <span class="pv-filename">{fileName}</span>
            <span class="pv-zoom">{zoom}%</span>
        </div>
    </div>
</div>

<style>
    .photo-viewer {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #1e1e2e;
        font-family: var(--xp-font);
    }

    /* === Canvas area === */
    .pv-canvas {
        flex: 1;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(
            135deg,
            #1a1a2e 0%,
            #16213e 50%,
            #0f3460 100%
        );
        position: relative;
    }

    /* XP-style scrollbars */
    .pv-canvas::-webkit-scrollbar {
        width: 17px;
        height: 17px;
    }
    .pv-canvas::-webkit-scrollbar-track {
        background: #ece9d8;
    }
    .pv-canvas::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #ece9d8 0%, #c8c4b8 100%);
        border: 1px solid;
        border-color: #fff #808080 #808080 #fff;
    }
    .pv-canvas::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #d8d4c8 0%, #b8b4a8 100%);
    }
    .pv-canvas::-webkit-scrollbar-button {
        background: #ece9d8;
        border: 1px solid;
        border-color: #fff #808080 #808080 #fff;
        width: 17px;
        height: 17px;
    }
    .pv-canvas::-webkit-scrollbar-corner {
        background: #ece9d8;
    }

    .pv-image-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 100%;
        min-height: 100%;
        padding: 8px;
    }

    .pv-image {
        display: block;
        transition: none;
        image-rendering: auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    }

    .pv-placeholder {
        color: #8888aa;
        font-size: 13px;
        user-select: none;
    }

    /* === Bottom toolbar === */
    .pv-toolbar {
        background: linear-gradient(180deg, #f0f0f0 0%, #d4d0c8 100%);
        border-top: 1px solid #fff;
        padding: 0;
        flex-shrink: 0;
    }

    .pv-toolbar-inner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        padding: 6px 12px 2px;
    }

    .pv-btn-group {
        display: flex;
        gap: 1px;
    }

    .pv-btn {
        width: 34px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 3px;
        cursor: pointer;
        padding: 0;
        color: #333;
        transition: all 0.1s;
        font-family: var(--xp-font);
    }
    .pv-btn:hover:not(:disabled) {
        background: #fff;
        border-color: #a0a0b0 #808090 #808090 #a0a0b0;
        box-shadow: inset 0 1px 0 #fff;
    }
    .pv-btn:active:not(:disabled) {
        background: #d0d0d8;
        border-color: #808090 #a0a0b0 #a0a0b0 #808090;
    }
    .pv-btn:disabled {
        opacity: 0.35;
        cursor: default;
    }

    .pv-btn-text {
        font-size: 11px;
        font-weight: 700;
        color: #444;
        line-height: 1;
    }

    .pv-separator {
        width: 1px;
        height: 24px;
        background: #b0b0b8;
        margin: 0 6px;
    }

    /* === Status bar === */
    .pv-status {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 3px 12px 5px;
        font-size: 11px;
        color: #555;
        border-top: 1px solid #c8c4bc;
    }

    .pv-counter {
        color: #333;
        font-weight: 600;
    }

    .pv-filename {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pv-zoom {
        color: #777;
        white-space: nowrap;
    }
</style>
