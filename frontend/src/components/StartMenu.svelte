<script>
    import { createEventDispatcher } from "svelte";
    import { windows } from "../stores/windows.js";
    import {
        FileText,
        Calculator,
        Bomb,
        Palette,
        Monitor,
        Globe,
        Terminal,
        User,
        LogOut,
        Power,
        Settings,
        HelpCircle,
        Search,
        Play,
        FolderOpen,
    } from "lucide-svelte";

    const dispatch = createEventDispatcher();

    const programs = [
        {
            name: "Internet Explorer",
            icon: Globe,
            appName: "InternetExplorer",
            color: "#2196F3",
            pinned: true,
        },
        {
            name: "Command Prompt",
            icon: Terminal,
            appName: "CommandPrompt",
            color: "#333",
            pinned: true,
        },
        {
            name: "Notepad",
            icon: FileText,
            appName: "Notepad",
            color: "#6B8E9B",
        },
        {
            name: "Calculator",
            icon: Calculator,
            appName: "Calculator",
            color: "#4A7B3F",
        },
        {
            name: "Minesweeper",
            icon: Bomb,
            appName: "Minesweeper",
            color: "#C00",
        },
        { name: "Paint", icon: Palette, appName: "Paint", color: "#E67E22" },
        {
            name: "My Computer",
            icon: Monitor,
            appName: "MyComputer",
            color: "#D4A843",
        },
    ];

    const rightItems = [
        { name: "My Documents", icon: null },
        { name: "My Pictures", icon: null },
        { name: "My Music", icon: null },
        { name: "Control Panel", icon: null },
        { name: "Help and Support", icon: null },
        { name: "Search", icon: null },
        { name: "Run...", icon: null },
    ];

    function openApp(prog) {
        windows.open(prog.appName, prog.name, prog.icon);
        dispatch("close");
    }

    function handleRightItemClick(item) {
        if (item.name === "My Documents") {
            windows.open("MyDocuments", "My Documents", FolderOpen);
            dispatch("close");
        }
    }

    function handleClickOutside(e) {
        dispatch("close");
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div class="start-menu-overlay" on:click={handleClickOutside}>
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div class="start-menu" on:click|stopPropagation>
        <div class="sm-header">
            <div class="sm-user-avatar">
                <User size={36} color="#4E6B8B" />
            </div>
            <span class="sm-user-name">User</span>
        </div>

        <div class="sm-body">
            <div class="sm-left">
                <div class="sm-pinned">
                    {#each programs.filter((p) => p.pinned) as prog}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div class="sm-item" on:click={() => openApp(prog)}>
                            <svelte:component
                                this={prog.icon}
                                size={24}
                                color={prog.color}
                                strokeWidth={1.5}
                            />
                            <div class="sm-item-text">
                                <span class="sm-item-name">{prog.name}</span>
                            </div>
                        </div>
                    {/each}
                </div>
                <div class="sm-separator"></div>
                <div class="sm-all-programs">
                    {#each programs.filter((p) => !p.pinned) as prog}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div class="sm-item" on:click={() => openApp(prog)}>
                            <svelte:component
                                this={prog.icon}
                                size={20}
                                color={prog.color}
                                strokeWidth={1.5}
                            />
                            <div class="sm-item-text">
                                <span class="sm-item-name">{prog.name}</span>
                            </div>
                        </div>
                    {/each}
                </div>
                <div class="sm-separator"></div>
                <div class="sm-all-programs-link">
                    <span>All Programs</span>
                    <Play size={10} color="#333" />
                </div>
            </div>

            <div class="sm-right">
                {#each rightItems as item}
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <div
                        class="sm-right-item"
                        on:click={() => handleRightItemClick(item)}
                    >
                        <span>{item.name}</span>
                    </div>
                {/each}
            </div>
        </div>

        <div class="sm-footer">
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <div class="sm-footer-btn" on:click={() => dispatch("close")}>
                <LogOut size={16} color="#C00" />
                <span>Log Off</span>
            </div>
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <div class="sm-footer-btn" on:click={() => dispatch("close")}>
                <Power size={16} color="#C00" />
                <span>Turn Off Computer</span>
            </div>
        </div>
    </div>
</div>

<style>
    .start-menu-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
    }

    .start-menu {
        position: absolute;
        bottom: 36px;
        left: 0;
        width: 380px;
        background: var(--xp-window-bg);
        border: 2px solid #245edc;
        border-radius: 8px 8px 0 0;
        box-shadow: 2px -2px 8px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .sm-header {
        background: linear-gradient(
            180deg,
            #2a64d0 0%,
            #245edc 50%,
            #1b4ebb 100%
        );
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 6px 6px 0 0;
    }

    .sm-user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 5px;
        background: linear-gradient(135deg, #d9e4f5, #f5e6d0);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .sm-user-name {
        color: white;
        font-size: 13px;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
    }

    .sm-body {
        display: flex;
        min-height: 300px;
    }

    .sm-left {
        flex: 1;
        background: #fff;
        padding: 6px 0;
        display: flex;
        flex-direction: column;
    }

    .sm-right {
        width: 160px;
        background: linear-gradient(180deg, #4b8adb 0%, #3d7bd0 100%);
        padding: 8px 0;
    }

    .sm-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 12px;
        cursor: pointer;
    }
    .sm-item:hover {
        background: var(--xp-selection);
    }
    .sm-item:hover .sm-item-name {
        color: white;
    }

    .sm-item-name {
        font-size: 11px;
        color: #000;
    }

    .sm-separator {
        height: 1px;
        background: #d6d2c2;
        margin: 4px 8px;
    }

    .sm-all-programs {
        flex: 1;
    }

    .sm-all-programs-link {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        padding: 6px 16px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        color: #000;
    }
    .sm-all-programs-link:hover {
        text-decoration: underline;
    }

    .sm-right-item {
        padding: 5px 14px;
        font-size: 11px;
        color: white;
        cursor: pointer;
        font-weight: 700;
    }
    .sm-right-item:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .sm-footer {
        background: linear-gradient(180deg, #4074c9 0%, #345db8 100%);
        padding: 5px 10px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        border-top: 1px solid #2050aa;
    }

    .sm-footer-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        font-size: 11px;
        color: white;
        cursor: pointer;
        border-radius: 3px;
    }
    .sm-footer-btn:hover {
        background: rgba(255, 255, 255, 0.15);
    }
</style>
