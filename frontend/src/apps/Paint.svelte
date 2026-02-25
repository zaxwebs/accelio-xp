<script>
    import { onMount } from "svelte";
    import { Pencil, Eraser, Square, Circle, Minus } from "lucide-svelte";

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

    onMount(() => {
        ctx = canvas.getContext("2d");
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    });

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
</script>

<div class="paint">
    <div class="paint-menu-bar">
        <span class="paint-menu-item">File</span>
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
    </div>
</div>

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
</style>
