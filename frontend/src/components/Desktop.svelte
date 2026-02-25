<script>
    import { Monitor, Trash2, FolderOpen, Globe } from "lucide-svelte";
    import { windows } from "../stores/windows.js";

    const desktopIcons = [
        {
            name: "My Computer",
            icon: Monitor,
            appName: "MyComputer",
            color: "#D4A843",
        },
        { name: "Recycle Bin", icon: Trash2, appName: null, color: "#7B8D9E" },
        {
            name: "My Documents",
            icon: FolderOpen,
            appName: "MyDocuments",
            color: "#E8C94A",
        },
        {
            name: "Internet Explorer",
            icon: Globe,
            appName: "InternetExplorer",
            color: "#2196F3",
        },
    ];

    let contextMenu = null;
    let selectedIcon = null;

    function openApp(icon) {
        if (!icon.appName) return;
        windows.open(icon.appName, icon.name, icon.icon, {});
    }

    function handleDoubleClick(icon) {
        openApp(icon);
    }

    function handleIconClick(e, icon) {
        e.stopPropagation();
        selectedIcon = icon.name;
        contextMenu = null;
    }

    function handleDesktopClick() {
        selectedIcon = null;
        contextMenu = null;
    }

    function handleContextMenu(e) {
        e.preventDefault();
        contextMenu = { x: e.clientX, y: e.clientY };
    }

    function closeContextMenu() {
        contextMenu = null;
    }

    function handleRefresh() {
        contextMenu = null;
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
<div
    class="desktop"
    on:click={handleDesktopClick}
    on:contextmenu={handleContextMenu}
>
    <div class="icon-grid">
        {#each desktopIcons as icon}
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div
                class="desktop-icon"
                class:selected={selectedIcon === icon.name}
                on:click={(e) => handleIconClick(e, icon)}
                on:dblclick={() => handleDoubleClick(icon)}
            >
                <div class="icon-image">
                    <svelte:component
                        this={icon.icon}
                        size={32}
                        color={icon.color}
                        strokeWidth={1.5}
                    />
                </div>
                <span class="icon-label">{icon.name}</span>
            </div>
        {/each}
    </div>

    {#if contextMenu}
        <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
        <div
            class="xp-context-menu"
            style="left: {contextMenu.x}px; top: {contextMenu.y}px"
            on:click|stopPropagation
        >
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                Arrange Icons By
            </div>
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                Refresh
            </div>
            <div class="xp-context-menu-separator"></div>
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                Paste
            </div>
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                Paste Shortcut
            </div>
            <div class="xp-context-menu-separator"></div>
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                New ▸
            </div>
            <div class="xp-context-menu-separator"></div>
            <!-- svelte-ignore a11y-no-static-element-interactions a11y-click-events-have-key-events -->
            <div class="xp-context-menu-item" on:click={handleRefresh}>
                Properties
            </div>
        </div>
    {/if}
</div>

<style>
    .desktop {
        width: 100%;
        height: calc(100% - 36px);
        background: linear-gradient(
            135deg,
            #245edc 0%,
            #3a6ea5 30%,
            #4a8bc2 60%,
            #71b3e0 100%
        );
        position: absolute;
        top: 0;
        left: 0;
    }

    .icon-grid {
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        gap: 4px;
        padding: 12px;
        height: 100%;
        align-content: flex-start;
    }

    .desktop-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 75px;
        padding: 6px 4px;
        cursor: pointer;
        border-radius: 3px;
        gap: 4px;
    }
    .desktop-icon:hover {
        background: rgba(255, 255, 255, 0.08);
    }
    .desktop-icon.selected {
        background: rgba(49, 106, 197, 0.5);
        outline: 1px dotted rgba(255, 255, 255, 0.7);
    }

    .icon-image {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: drop-shadow(1px 2px 2px rgba(0, 0, 0, 0.4));
    }

    .icon-label {
        color: white;
        font-size: 11px;
        text-align: center;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        word-wrap: break-word;
        max-width: 70px;
        line-height: 1.2;
    }
</style>
