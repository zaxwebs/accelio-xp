<script>
    import {
        HardDrive,
        Disc,
        FolderOpen,
        Monitor,
        Printer,
        Settings,
    } from "lucide-svelte";
    import { windows } from "../stores/windows.js";

    const drives = [
        {
            name: "Local Disk (C:)",
            icon: HardDrive,
            size: "74.5 GB",
            free: "31.2 GB",
            color: "#D4A843",
        },
        {
            name: "Local Disk (D:)",
            icon: HardDrive,
            size: "148.0 GB",
            free: "98.7 GB",
            color: "#D4A843",
        },
        {
            name: "DVD Drive (E:)",
            icon: Disc,
            size: "",
            free: "",
            color: "#999",
        },
    ];

    const folders = [
        { name: "Shared Documents", icon: FolderOpen, color: "#E8C94A" },
        { name: "User's Documents", icon: FolderOpen, color: "#E8C94A" },
    ];

    const systemItems = [
        { name: "Control Panel", icon: Settings, color: "#666" },
        { name: "Printers and Faxes", icon: Printer, color: "#666" },
    ];

    let selected = null;

    function selectItem(name) {
        selected = name;
    }

    function getBarWidth(size, free) {
        if (!size || !free) return 0;
        const total = parseFloat(size);
        const used = total - parseFloat(free);
        return (used / total) * 100;
    }
</script>

<div class="my-computer">
    <div class="mc-menu-bar">
        <span class="mc-menu-item">File</span>
        <span class="mc-menu-item">Edit</span>
        <span class="mc-menu-item">View</span>
        <span class="mc-menu-item">Favorites</span>
        <span class="mc-menu-item">Tools</span>
        <span class="mc-menu-item">Help</span>
    </div>

    <div class="mc-toolbar">
        <button class="mc-tool-btn">← Back</button>
        <button class="mc-tool-btn">→</button>
        <button class="mc-tool-btn">↑</button>
        <div class="mc-address-bar">
            <span class="mc-address-label">Address</span>
            <div class="mc-address-input">
                <Monitor size={14} color="#D4A843" />
                <span>My Computer</span>
            </div>
        </div>
    </div>

    <div class="mc-body">
        <div class="mc-sidebar">
            <div class="mc-sidebar-section">
                <div class="mc-sidebar-header">System Tasks</div>
                <div class="mc-sidebar-item">View system information</div>
                <div class="mc-sidebar-item">Add or remove programs</div>
                <div class="mc-sidebar-item">Change a setting</div>
            </div>
            <div class="mc-sidebar-section">
                <div class="mc-sidebar-header">Other Places</div>
                <div class="mc-sidebar-item">My Network Places</div>
                <div class="mc-sidebar-item">My Documents</div>
                <div class="mc-sidebar-item">Shared Documents</div>
                <div class="mc-sidebar-item">Control Panel</div>
            </div>
        </div>

        <div class="mc-content">
            <div class="mc-section-header">Hard Disk Drives</div>
            <div class="mc-grid">
                {#each drives as drive}
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <div
                        class="mc-item"
                        class:selected={selected === drive.name}
                        on:click={() => selectItem(drive.name)}
                        on:dblclick={() => {}}
                    >
                        <svelte:component
                            this={drive.icon}
                            size={32}
                            color={drive.color}
                            strokeWidth={1.5}
                        />
                        <div class="mc-item-info">
                            <span class="mc-item-name">{drive.name}</span>
                            {#if drive.size}
                                <div class="mc-drive-bar">
                                    <div
                                        class="mc-drive-used"
                                        style="width: {getBarWidth(
                                            drive.size,
                                            drive.free,
                                        )}%"
                                    ></div>
                                </div>
                                <span class="mc-item-detail"
                                    >{drive.free} free of {drive.size}</span
                                >
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>

            <div class="mc-section-header">Files Stored on This Computer</div>
            <div class="mc-grid">
                {#each folders as folder}
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <div
                        class="mc-item"
                        class:selected={selected === folder.name}
                        on:click={() => selectItem(folder.name)}
                    >
                        <svelte:component
                            this={folder.icon}
                            size={32}
                            color={folder.color}
                            strokeWidth={1.5}
                        />
                        <div class="mc-item-info">
                            <span class="mc-item-name">{folder.name}</span>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </div>

    <div class="mc-status-bar">
        <span>My Computer</span>
    </div>
</div>

<style>
    .my-computer {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .mc-menu-bar {
        display: flex;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .mc-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .mc-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .mc-toolbar {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
        background: var(--xp-window-bg);
    }

    .mc-tool-btn {
        font-size: 11px;
        padding: 2px 8px;
        background: var(--xp-button-face);
        border: 1px solid transparent;
        cursor: pointer;
        height: 22px;
        font-family: var(--xp-font);
    }
    .mc-tool-btn:hover {
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
    }

    .mc-address-bar {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-left: 8px;
    }
    .mc-address-label {
        font-size: 11px;
        color: #333;
    }
    .mc-address-input {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 2px 4px;
        background: #fff;
        border: 1px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
        font-size: 11px;
        height: 22px;
    }

    .mc-body {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .mc-sidebar {
        width: 200px;
        background: linear-gradient(180deg, #6b89d4 0%, #4f72c2 100%);
        padding: 8px;
        overflow-y: auto;
        border-right: 1px solid #3060b0;
    }

    .mc-sidebar-section {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 6px;
        padding: 8px;
        margin-bottom: 8px;
    }
    .mc-sidebar-header {
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
    }
    .mc-sidebar-item {
        font-size: 11px;
        color: #d0dfff;
        padding: 2px 4px;
        cursor: pointer;
        text-decoration: underline;
    }
    .mc-sidebar-item:hover {
        color: #fff;
    }

    .mc-content {
        flex: 1;
        padding: 8px 12px;
        overflow-y: auto;
        background: #fff;
    }

    .mc-section-header {
        font-size: 11px;
        font-weight: 700;
        color: #215dc6;
        padding: 8px 0 4px 0;
        border-bottom: 1px solid #d0d0d0;
        margin-bottom: 8px;
    }

    .mc-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .mc-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 6px 10px;
        cursor: pointer;
        border-radius: 3px;
        width: 200px;
    }
    .mc-item:hover {
        background: #e8f0ff;
    }
    .mc-item.selected {
        background: var(--xp-selection);
        color: white;
    }

    .mc-item-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .mc-item-name {
        font-size: 11px;
        font-weight: 700;
    }
    .mc-item-detail {
        font-size: 10px;
        color: #666;
    }
    .mc-item.selected .mc-item-detail {
        color: #d0dfff;
    }

    .mc-drive-bar {
        width: 120px;
        height: 10px;
        background: #fff;
        border: 1px solid #808080;
        border-radius: 1px;
    }
    .mc-drive-used {
        height: 100%;
        background: linear-gradient(180deg, #3090ff 0%, #2070e0 100%);
    }

    .mc-status-bar {
        height: 22px;
        background: var(--xp-window-bg);
        border-top: 1px solid var(--xp-button-shadow);
        display: flex;
        align-items: center;
        padding: 0 8px;
        font-size: 10px;
        color: #666;
    }
</style>
