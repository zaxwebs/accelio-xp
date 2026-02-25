<script>
    import {
        ArrowLeft,
        ArrowRight,
        RotateCw,
        Home,
        Star,
        Globe,
        Search,
        X,
    } from "lucide-svelte";

    const HOMEPAGE = "https://en.wikipedia.org/wiki/Windows_XP";
    const PROXY_BASE = "/api/proxy?url=";

    let url = HOMEPAGE;
    let inputUrl = url;
    let loading = false;
    let iframeSrc = PROXY_BASE + encodeURIComponent(url);

    // History stack
    let history = [url];
    let historyIndex = 0;

    function proxyUrl(rawUrl) {
        return PROXY_BASE + encodeURIComponent(rawUrl);
    }

    function navigate() {
        let target = inputUrl.trim();
        if (!target) return;
        if (!target.startsWith("http://") && !target.startsWith("https://")) {
            target = "https://" + target;
        }
        url = target;
        inputUrl = target;
        iframeSrc = proxyUrl(target);
        loading = true;

        // Push to history (trim forward entries)
        if (historyIndex < history.length - 1) {
            history = history.slice(0, historyIndex + 1);
        }
        history = [...history, target];
        historyIndex = history.length - 1;
    }

    function handleKeydown(e) {
        if (e.key === "Enter") navigate();
    }

    function goHome() {
        inputUrl = HOMEPAGE;
        navigate();
    }

    function refresh() {
        loading = true;
        iframeSrc = "";
        setTimeout(() => {
            iframeSrc = proxyUrl(url);
        }, 50);
    }

    function goBack() {
        if (historyIndex <= 0) return;
        historyIndex--;
        url = history[historyIndex];
        inputUrl = url;
        iframeSrc = proxyUrl(url);
        loading = true;
    }

    function goForward() {
        if (historyIndex >= history.length - 1) return;
        historyIndex++;
        url = history[historyIndex];
        inputUrl = url;
        iframeSrc = proxyUrl(url);
        loading = true;
    }

    function onLoad() {
        loading = false;

        // Try to detect navigation inside iframe to update address bar
        try {
            const iframe = document.querySelector(".ie-iframe");
            if (iframe && iframe.contentWindow) {
                const loc = iframe.contentWindow.location;
                if (loc.pathname === "/api/proxy") {
                    const params = new URLSearchParams(loc.search);
                    const navigatedUrl = params.get("url");
                    if (navigatedUrl && navigatedUrl !== url) {
                        url = navigatedUrl;
                        inputUrl = navigatedUrl;
                        // Push to history
                        if (historyIndex < history.length - 1) {
                            history = history.slice(0, historyIndex + 1);
                        }
                        history = [...history, navigatedUrl];
                        historyIndex = history.length - 1;
                    }
                }
            }
        } catch (e) {
            // Cross-origin — ignore silently
        }
    }

    $: canGoBack = historyIndex > 0;
    $: canGoForward = historyIndex < history.length - 1;
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
        <button
            class="ie-nav-btn"
            title="Back"
            on:click={goBack}
            disabled={!canGoBack}
        >
            <ArrowLeft size={16} />
        </button>
        <button
            class="ie-nav-btn"
            title="Forward"
            on:click={goForward}
            disabled={!canGoForward}
        >
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
            <div class="ie-loading">
                <div class="ie-loading-bar"></div>
            </div>
        {/if}
        <iframe
            src={iframeSrc}
            title="Internet Explorer"
            class="ie-iframe"
            on:load={onLoad}
        ></iframe>
    </div>

    <div class="ie-status-bar">
        <span>{loading ? "Opening page..." : "Done"}</span>
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
    .ie-nav-btn:hover:not(:disabled) {
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
    }
    .ie-nav-btn:disabled {
        opacity: 0.4;
        cursor: default;
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
    .ie-go-btn:hover {
        background: #e8e4d8;
    }

    .ie-content {
        flex: 1;
        position: relative;
        overflow: hidden;
    }
    .ie-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 2;
        background: #e0e0e0;
        overflow: hidden;
    }
    .ie-loading-bar {
        width: 30%;
        height: 100%;
        background: linear-gradient(90deg, #0078d4, #00a2ff, #0078d4);
        animation: ie-progress 1.2s ease-in-out infinite;
    }
    @keyframes ie-progress {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(400%);
        }
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
