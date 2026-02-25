<script>
    import {
        ArrowLeft,
        ArrowRight,
        RotateCw,
        Home,
        Star,
        Globe,
        Search,
    } from "lucide-svelte";

    let url = "https://en.wikipedia.org/wiki/Windows_XP";
    let inputUrl = url;
    let loading = false;
    let iframeSrc = url;

    function navigate() {
        let target = inputUrl.trim();
        if (!target.startsWith("http://") && !target.startsWith("https://")) {
            target = "https://" + target;
        }
        url = target;
        inputUrl = target;
        iframeSrc = target;
        loading = true;
    }

    function handleKeydown(e) {
        if (e.key === "Enter") navigate();
    }

    function goHome() {
        inputUrl = "https://en.wikipedia.org/wiki/Windows_XP";
        navigate();
    }

    function refresh() {
        iframeSrc = "";
        setTimeout(() => {
            iframeSrc = url;
        }, 50);
    }

    function onLoad() {
        loading = false;
    }
</script>

<div class="ie">
    <div class="ie-menu-bar">
        <span class="ie-menu-item">File</span>
        <span class="ie-menu-item">Edit</span>
        <span class="ie-menu-item">View</span>
        <span class="ie-menu-item">Favorites</span>
        <span class="ie-menu-item">Tools</span>
        <span class="ie-menu-item">Help</span>
    </div>

    <div class="ie-toolbar">
        <button class="ie-nav-btn" title="Back">
            <ArrowLeft size={16} />
        </button>
        <button class="ie-nav-btn" title="Forward">
            <ArrowRight size={16} />
        </button>
        <button class="ie-nav-btn" on:click={refresh} title="Refresh">
            <RotateCw size={14} />
        </button>
        <button class="ie-nav-btn" on:click={goHome} title="Home">
            <Home size={16} />
        </button>
        <button class="ie-nav-btn" title="Search">
            <Search size={14} />
        </button>
        <button class="ie-nav-btn" title="Favorites">
            <Star size={14} />
        </button>
    </div>

    <div class="ie-address-row">
        <span class="ie-address-label">Address</span>
        <div class="ie-address-input-wrap">
            <Globe size={14} color="#2196F3" />
            <input
                type="text"
                class="ie-address-input"
                bind:value={inputUrl}
                on:keydown={handleKeydown}
            />
        </div>
        <button class="ie-go-btn" on:click={navigate}>Go</button>
    </div>

    <div class="ie-content">
        {#if loading}
            <div class="ie-loading">Loading...</div>
        {/if}
        <iframe
            src={iframeSrc}
            title="Internet Explorer"
            class="ie-iframe"
            on:load={onLoad}
            sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
        ></iframe>
    </div>

    <div class="ie-status-bar">
        <span>{loading ? "Loading..." : "Done"}</span>
        <Globe size={12} color="#666" />
    </div>
</div>

<style>
    .ie {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .ie-menu-bar {
        display: flex;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .ie-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .ie-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .ie-toolbar {
        display: flex;
        align-items: center;
        gap: 2px;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }

    .ie-nav-btn {
        width: 30px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--xp-button-face);
        border: 1px solid transparent;
        cursor: pointer;
        color: #333;
        padding: 0;
    }
    .ie-nav-btn:hover {
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
    }

    .ie-address-row {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .ie-address-label {
        font-size: 11px;
        color: #333;
        min-width: 45px;
    }
    .ie-address-input-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 1px 4px;
        background: #fff;
        border: 1px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
        height: 22px;
    }
    .ie-address-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 11px;
        font-family: var(--xp-font);
        background: transparent;
    }
    .ie-go-btn {
        font-size: 11px;
        padding: 2px 12px;
        background: var(--xp-button-face);
        border: 1px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
        cursor: pointer;
        height: 22px;
        font-family: var(--xp-font);
    }

    .ie-content {
        flex: 1;
        position: relative;
        overflow: hidden;
    }
    .ie-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 14px;
        color: #666;
        z-index: 1;
    }
    .ie-iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .ie-status-bar {
        height: 22px;
        background: var(--xp-window-bg);
        border-top: 1px solid var(--xp-button-shadow);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8px;
        font-size: 10px;
        color: #666;
    }
</style>
