<script>
    import { onMount } from "svelte";
    import {
        FileText,
        Save,
        FolderOpen,
        FilePlus,
        Scissors,
        Copy,
        Clipboard,
    } from "lucide-svelte";
    import { windows } from "../stores/windows.js";
    import {
        getDocument,
        createDocument,
        updateDocument,
        listDocuments,
    } from "../lib/api.js";

    export let windowId = null;
    export let documentId = null;

    let content = "";
    let fileName = "Untitled";
    let currentDocId = documentId;
    let menuOpen = null;
    let showFilePicker = false;
    let fileList = [];
    let dirty = false;
    let saving = false;

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

    onMount(async () => {
        if (documentId) {
            await loadDocument(documentId);
        }
    });

    async function loadDocument(id) {
        try {
            const res = await getDocument(id);
            if (res.ok) {
                content = res.data.content || "";
                fileName = res.data.name;
                currentDocId = res.data.id;
                dirty = false;
                updateTitle();
            }
        } catch (e) {
            console.error("Failed to load document:", e);
        }
    }

    function updateTitle() {
        if (windowId) {
            windows.focus(windowId);
        }
    }

    function markDirty() {
        dirty = true;
    }

    async function handleSave() {
        if (saving) return;
        saving = true;

        try {
            if (currentDocId) {
                await updateDocument(currentDocId, {
                    name: fileName,
                    content,
                });
            } else {
                const name = prompt("Save as:", fileName || "Untitled.txt");
                if (!name) {
                    saving = false;
                    return;
                }
                fileName = name;
                const res = await createDocument({
                    name,
                    type: "text",
                    content,
                });
                if (res.ok) {
                    currentDocId = res.data.id;
                }
            }
            dirty = false;
        } catch (e) {
            console.error("Failed to save:", e);
            alert("Failed to save document.");
        }
        saving = false;
    }

    async function handleSaveAs() {
        const name = prompt("Save as:", fileName || "Untitled.txt");
        if (!name) return;

        saving = true;
        try {
            fileName = name;
            const res = await createDocument({ name, type: "text", content });
            if (res.ok) {
                currentDocId = res.data.id;
                dirty = false;
            }
        } catch (e) {
            console.error("Failed to save:", e);
            alert("Failed to save document.");
        }
        saving = false;
    }

    async function handleOpen() {
        try {
            const res = await listDocuments();
            if (res.ok) {
                fileList = res.data.filter((d) => d.type === "text");
                showFilePicker = true;
            }
        } catch (e) {
            console.error("Failed to list documents:", e);
        }
    }

    async function pickFile(doc) {
        showFilePicker = false;
        await loadDocument(doc.id);
    }

    function handleMenuAction(menu, item) {
        if (item === "New") {
            content = "";
            fileName = "Untitled";
            currentDocId = null;
            dirty = false;
        } else if (item === "Save") {
            handleSave();
        } else if (item === "Save As...") {
            handleSaveAs();
        } else if (item === "Open...") {
            handleOpen();
        } else if (item === "Exit") {
            if (windowId) windows.close(windowId);
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
        on:input={markDirty}
        spellcheck="false"
        placeholder=""
    ></textarea>
    <div class="status-bar">
        <span>{fileName}{dirty ? " •" : ""}</span>
        {#if saving}
            <span>Saving...</span>
        {/if}
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
                    <div class="fp-empty">No text documents saved yet.</div>
                {:else}
                    {#each fileList as doc}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div
                            class="fp-item"
                            on:dblclick={() => pickFile(doc)}
                            on:click={() => pickFile(doc)}
                        >
                            <FileText size={16} color="#6B8E9B" />
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
        justify-content: space-between;
        padding: 0 8px;
        font-size: 10px;
        color: #666;
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
