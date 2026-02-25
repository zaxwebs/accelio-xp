<script>
    import {
        FileText,
        Save,
        FolderOpen,
        FilePlus,
        Scissors,
        Copy,
        Clipboard,
    } from "lucide-svelte";

    let content = "";
    let fileName = "Untitled";
    let menuOpen = null;

    const menus = {
        File: ["New", "Open...", "Save", "Save As...", "-", "Exit"],
        Edit: [
            "Undo",
            "-",
            "Cut",
            "Copy",
            "Paste",
            "Delete",
            "-",
            "Select All",
        ],
        Format: ["Word Wrap", "Font..."],
        Help: ["About Notepad"],
    };

    function handleMenuAction(menu, item) {
        if (item === "New") {
            content = "";
            fileName = "Untitled";
        } else if (item === "Select All") {
            const ta = document.querySelector(".notepad-textarea");
            if (ta) ta.select();
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
<div class="notepad" on:click={handleBodyClick}>
    <div class="menu-bar">
        {#each Object.keys(menus) as menu}
            <div class="menu-trigger" class:open={menuOpen === menu}>
                <!-- svelte-ignore a11y-no-static-element-interactions -->
                <span on:click|stopPropagation={() => toggleMenu(menu)}
                    >{menu}</span
                >
                {#if menuOpen === menu}
                    <div class="menu-dropdown">
                        {#each menus[menu] as item}
                            {#if item === "-"}
                                <div class="menu-sep"></div>
                            {:else}
                                <!-- svelte-ignore a11y-no-static-element-interactions -->
                                <div
                                    class="menu-item"
                                    on:click|stopPropagation={() =>
                                        handleMenuAction(menu, item)}
                                >
                                    {item}
                                </div>
                            {/if}
                        {/each}
                    </div>
                {/if}
            </div>
        {/each}
    </div>
    <textarea
        class="notepad-textarea"
        bind:value={content}
        spellcheck="false"
        placeholder=""
    ></textarea>
    <div class="status-bar">
        <span>Ln 1, Col 1</span>
    </div>
</div>

<style>
    .notepad {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .menu-bar {
        display: flex;
        padding: 0 2px;
        background: var(--xp-window-bg);
        border-bottom: 1px solid var(--xp-button-shadow);
        height: 22px;
        align-items: center;
    }

    .menu-trigger {
        position: relative;
        padding: 2px 8px;
        font-size: 11px;
        cursor: pointer;
    }
    .menu-trigger:hover,
    .menu-trigger.open {
        background: var(--xp-selection);
        color: white;
    }

    .menu-dropdown {
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

    .menu-item {
        padding: 3px 24px 3px 28px;
        font-size: 11px;
        cursor: pointer;
        color: #000;
    }
    .menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .menu-sep {
        border-top: 1px solid #c0c0c0;
        margin: 2px 2px;
    }

    .notepad-textarea {
        flex: 1;
        border: none;
        outline: none;
        resize: none;
        font-family: "Lucida Console", "Courier New", monospace;
        font-size: 13px;
        padding: 4px;
        background: #fff;
        line-height: 1.4;
    }

    .status-bar {
        height: 20px;
        background: var(--xp-window-bg);
        border-top: 1px solid var(--xp-button-shadow);
        display: flex;
        align-items: center;
        padding: 0 8px;
        font-size: 10px;
        color: #666;
    }
</style>
