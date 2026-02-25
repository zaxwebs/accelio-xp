<script>
    import { onMount } from "svelte";
    import {
        Pencil,
        Eraser,
        Square,
        Circle,
        Minus,
        Image,
    } from "lucide-svelte";
    import { windows } from "../stores/windows.js";
    import {
        uploadImage,
        getImageUrl,
        getDocument,
        listDocuments,
    } from "../lib/api.js";

    export let windowId = null;
    export let documentId = null;

    let canvas;
    let ctx;
    let drawing = false;
    let color = "#000000";
    let brushSize = 3;
    let tool = "pencil"; // pencil, eraser, line, rect
    let lastX = 0;
    let lastY = 0;
    let startX = 0;
    let startY = 0;

    let currentDocId = documentId;
    let fileName = "Untitled";
    let menuOpen = null;
    let showFilePicker = false;
    let fileList = [];
    let saving = false;

    const colors = [
        "#000000",
        "#808080",
        "#800000",
        "#808000",
        "#008000",
        "#008080",
        "#000080",
        "#800080",
        "#FFFFFF",
        "#C0C0C0",
        "#FF0000",
        "#FFFF00",
        "#00FF00",
        "#00FFFF",
        "#0000FF",
        "#FF00FF",
        "#FF8000",
        "#80FF00",
        "#00FF80",
        "#0080FF",
        "#8000FF",
        "#FF0080",
        "#C08040",
        "#FFE0C0",
    ];

    const brushSizes = [1, 2, 3, 5, 8, 12];

    const fileMenuItems = ["New", "Open...", "Save", "Save As...", "-", "Exit"];

    onMount(async () => {
        ctx = canvas.getContext("2d");
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (documentId) {
            await loadImage(documentId);
        }
    });

    async function loadImage(id) {
        try {
            const res = await getDocument(id);
            if (res.ok) {
                fileName = res.data.name;
                currentDocId = res.data.id;

                const img = new window.Image();
                img.crossOrigin = "anonymous";
                img.onload = () => {
                    ctx.fillStyle = "#FFFFFF";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0);
                };
                img.src = getImageUrl(id);
            }
        } catch (e) {
            console.error("Failed to load image:", e);
        }
    }

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };
    }

    function startDraw(e) {
        drawing = true;
        const pos = getPos(e);
        lastX = pos.x;
        lastY = pos.y;
        startX = pos.x;
        startY = pos.y;

        if (tool === "pencil" || tool === "eraser") {
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }
    }

    function draw(e) {
        if (!drawing) return;
        const pos = getPos(e);

        if (tool === "pencil") {
            ctx.strokeStyle = color;
            ctx.lineWidth = brushSize;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        } else if (tool === "eraser") {
            ctx.strokeStyle = "#FFFFFF";
            ctx.lineWidth = brushSize * 3;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        lastX = pos.x;
        lastY = pos.y;
    }

    function endDraw(e) {
        if (!drawing) return;
        const pos = getPos(e);

        if (tool === "line") {
            ctx.beginPath();
            ctx.strokeStyle = color;
            ctx.lineWidth = brushSize;
            ctx.moveTo(startX, startY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        } else if (tool === "rect") {
            ctx.beginPath();
            ctx.strokeStyle = color;
            ctx.lineWidth = brushSize;
            ctx.strokeRect(startX, startY, pos.x - startX, pos.y - startY);
        }

        drawing = false;
    }

    function clearCanvas() {
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    async function handleSave() {
        if (saving) return;
        saving = true;
        try {
            const base64 = canvas.toDataURL("image/png");
            if (!currentDocId) {
                const name = prompt("Save as:", fileName || "Untitled.png");
                if (!name) {
                    saving = false;
                    return;
                }
                fileName = name;
            }
            const res = await uploadImage(
                fileName,
                base64,
                currentDocId || null,
            );
            if (res.ok) {
                currentDocId = res.data.id;
            }
        } catch (e) {
            console.error("Failed to save:", e);
            alert("Failed to save image.");
        }
        saving = false;
    }

    async function handleSaveAs() {
        const name = prompt("Save as:", fileName || "Untitled.png");
        if (!name) return;

        saving = true;
        try {
            fileName = name;
            const base64 = canvas.toDataURL("image/png");
            const res = await uploadImage(name, base64, null);
            if (res.ok) {
                currentDocId = res.data.id;
            }
        } catch (e) {
            console.error("Failed to save:", e);
            alert("Failed to save image.");
        }
        saving = false;
    }

    async function handleOpenDialog() {
        try {
            const res = await listDocuments();
            if (res.ok) {
                fileList = res.data.filter((d) => d.type === "image");
                showFilePicker = true;
            }
        } catch (e) {
            console.error("Failed to list:", e);
        }
    }

    async function pickFile(doc) {
        showFilePicker = false;
        await loadImage(doc.id);
    }

    function handleFileMenuAction(item) {
        if (item === "New") {
            clearCanvas();
            fileName = "Untitled";
            currentDocId = null;
        } else if (item === "Save") {
            handleSave();
        } else if (item === "Save As...") {
            handleSaveAs();
        } else if (item === "Open...") {
            handleOpenDialog();
        } else if (item === "Exit") {
            if (windowId) windows.close(windowId);
        }
        menuOpen = null;
    }

    function toggleMenu(name) {
        menuOpen = menuOpen === name ? null : name;
    }

    function handleBodyClick() {
        menuOpen = null;
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div class="paint" on:click={handleBodyClick}>
    <div class="paint-menu-bar">
        <div class="paint-menu-trigger" class:open={menuOpen === "File"}>
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <span on:click|stopPropagation={() => toggleMenu("File")}>File</span
            >
            {#if menuOpen === "File"}
                <div class="paint-menu-dropdown">
                    {#each fileMenuItems as item}
                        {#if item === "-"}
                            <div class="paint-menu-sep"></div>
                        {:else}
                            <!-- svelte-ignore a11y-no-static-element-interactions -->
                            <div
                                class="paint-menu-dd-item"
                                on:click|stopPropagation={() =>
                                    handleFileMenuAction(item)}
                            >
                                {item}
                            </div>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
        <span class="paint-menu-item">Edit</span>
        <span class="paint-menu-item">View</span>
        <span class="paint-menu-item">Image</span>
        <span class="paint-menu-item">Colors</span>
        <span class="paint-menu-item">Help</span>
    </div>

    <div class="paint-workspace">
        <div class="paint-toolbar">
            <button
                class="tool-btn"
                class:active={tool === "pencil"}
                on:click={() => (tool = "pencil")}
                title="Pencil"
            >
                <Pencil size={16} />
            </button>
            <button
                class="tool-btn"
                class:active={tool === "eraser"}
                on:click={() => (tool = "eraser")}
                title="Eraser"
            >
                <Eraser size={16} />
            </button>
            <button
                class="tool-btn"
                class:active={tool === "line"}
                on:click={() => (tool = "line")}
                title="Line"
            >
                <Minus size={16} />
            </button>
            <button
                class="tool-btn"
                class:active={tool === "rect"}
                on:click={() => (tool = "rect")}
                title="Rectangle"
            >
                <Square size={16} />
            </button>
            <div class="tool-separator"></div>
            <div class="brush-sizes">
                {#each brushSizes as size}
                    <button
                        class="brush-btn"
                        class:active={brushSize === size}
                        on:click={() => (brushSize = size)}
                    >
                        <div
                            class="brush-preview"
                            style="width: {Math.min(
                                size * 2,
                                14,
                            )}px; height: {Math.min(size * 2, 14)}px;"
                        ></div>
                    </button>
                {/each}
            </div>
            <div class="tool-separator"></div>
            <button class="tool-btn" on:click={clearCanvas} title="Clear">
                <span style="font-size: 10px;">CLR</span>
            </button>
        </div>

        <div class="paint-canvas-area">
            <canvas
                bind:this={canvas}
                width="800"
                height="500"
                on:mousedown={startDraw}
                on:mousemove={draw}
                on:mouseup={endDraw}
                on:mouseleave={endDraw}
            ></canvas>
        </div>
    </div>

    <div class="paint-color-bar">
        <div class="color-preview">
            <div class="color-swatch" style="background: {color};"></div>
        </div>
        <div class="color-palette">
            {#each colors as c}
                <button
                    class="palette-color"
                    class:active={color === c}
                    style="background: {c};"
                    on:click={() => (color = c)}
                ></button>
            {/each}
        </div>
        <div class="paint-status">
            <span>{fileName}</span>
            {#if saving}
                <span>Saving...</span>
            {/if}
        </div>
    </div>
</div>

{#if showFilePicker}
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div class="file-picker-overlay" on:click={() => (showFilePicker = false)}>
        <!-- svelte-ignore a11y-no-static-element-interactions -->
        <div class="file-picker" on:click|stopPropagation>
            <div class="fp-title-bar">Open</div>
            <div class="fp-list">
                {#if fileList.length === 0}
                    <div class="fp-empty">No images saved yet.</div>
                {:else}
                    {#each fileList as doc}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div
                            class="fp-item"
                            on:dblclick={() => pickFile(doc)}
                            on:click={() => pickFile(doc)}
                        >
                            <Image size={16} color="#2196F3" />
                            <span>{doc.name}</span>
                        </div>
                    {/each}
                {/if}
            </div>
            <div class="fp-footer">
                <button class="fp-btn" on:click={() => (showFilePicker = false)}
                    >Cancel</button
                >
            </div>
        </div>
    </div>
{/if}

<style>
    .paint {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .paint-menu-bar {
        display: flex;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .paint-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .paint-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .paint-menu-trigger {
        position: relative;
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .paint-menu-trigger:hover,
    .paint-menu-trigger.open {
        background: var(--xp-selection);
        color: white;
    }

    .paint-menu-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #808080;
        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        min-width: 180px;
        z-index: 100;
        padding: 2px 0;
    }

    .paint-menu-dd-item {
        padding: 3px 24px 3px 28px;
        font-size: 11px;
        cursor: pointer;
        color: #000;
    }
    .paint-menu-dd-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .paint-menu-sep {
        border-top: 1px solid #c0c0c0;
        margin: 2px 2px;
    }

    .paint-workspace {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .paint-toolbar {
        width: 50px;
        background: var(--xp-window-bg);
        border-right: 1px solid var(--xp-button-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 4px 2px;
        gap: 2px;
    }

    .tool-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--xp-button-face);
        border: 1px solid transparent;
        cursor: pointer;
        padding: 0;
        color: #333;
    }
    .tool-btn:hover {
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
    }
    .tool-btn.active {
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
        background: #d4d0c0;
    }

    .tool-separator {
        width: 80%;
        height: 1px;
        background: var(--xp-button-shadow);
        margin: 4px 0;
    }

    .brush-sizes {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .brush-btn {
        width: 24px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #ccc;
        cursor: pointer;
        padding: 0;
    }
    .brush-btn.active {
        background: var(--xp-selection);
    }
    .brush-preview {
        background: #000;
        border-radius: 50%;
    }
    .brush-btn.active .brush-preview {
        background: #fff;
    }

    .paint-canvas-area {
        flex: 1;
        overflow: auto;
        background: #808080;
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        padding: 4px;
    }

    canvas {
        background: #fff;
        cursor: crosshair;
        box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .paint-color-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 8px;
        background: var(--xp-window-bg);
        border-top: 1px solid var(--xp-button-shadow);
    }

    .color-preview {
        display: flex;
        align-items: center;
    }
    .color-swatch {
        width: 24px;
        height: 24px;
        border: 2px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
    }

    .color-palette {
        display: flex;
        flex-wrap: wrap;
        gap: 1px;
        max-width: 360px;
    }

    .palette-color {
        width: 16px;
        height: 16px;
        border: 1px solid #808080;
        cursor: pointer;
        padding: 0;
    }
    .palette-color.active {
        border: 2px solid #000;
        outline: 1px solid #fff;
    }

    .paint-status {
        margin-left: auto;
        font-size: 10px;
        color: #666;
        display: flex;
        gap: 8px;
    }

    /* File Picker Dialog */
    .file-picker-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }

    .file-picker {
        width: 360px;
        background: var(--xp-window-bg);
        border: 2px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
        box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    }

    .fp-title-bar {
        background: linear-gradient(
            180deg,
            #0a246a 0%,
            #3a6ea5 50%,
            #0a246a 100%
        );
        color: white;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 8px;
    }

    .fp-list {
        max-height: 250px;
        overflow-y: auto;
        padding: 4px;
        background: #fff;
        margin: 8px;
        border: 1px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
    }

    .fp-empty {
        padding: 16px;
        text-align: center;
        color: #999;
        font-size: 11px;
    }

    .fp-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 8px;
        font-size: 11px;
        cursor: pointer;
    }
    .fp-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .fp-footer {
        display: flex;
        justify-content: flex-end;
        padding: 4px 8px 8px;
    }
    .fp-btn {
        font-size: 11px;
        padding: 3px 16px;
        background: var(--xp-button-face);
        border: 2px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
        cursor: pointer;
        font-family: var(--xp-font);
    }
    .fp-btn:hover {
        background: #e0ddd0;
    }
</style>
