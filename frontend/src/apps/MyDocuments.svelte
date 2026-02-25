<script>
    import { onMount } from "svelte";
    import {
        FileText,
        Image,
        Trash2,
        RefreshCw,
        FolderOpen,
    } from "lucide-svelte";
    import { windows } from "../stores/windows.js";
    import { listDocuments, deleteDocument, getImageUrl } from "../lib/api.js";

    let documents = [];
    let loading = true;
    let selected = null;

    onMount(() => {
        loadDocuments();
    });

    async function loadDocuments() {
        loading = true;
        try {
            const res = await listDocuments();
            if (res.ok) {
                documents = res.data;
            }
        } catch (e) {
            console.error("Failed to load documents:", e);
        }
        loading = false;
    }

    function selectDoc(doc) {
        selected = selected === doc.id ? null : doc.id;
    }

    function openDocument(doc) {
        if (doc.type === "image") {
            windows.open("ImageViewer", doc.name, Image, {
                documentId: doc.id,
            });
        } else {
            windows.open("Notepad", doc.name, FileText, {
                documentId: doc.id,
            });
        }
    }

    async function handleDelete() {
        if (selected === null) return;
        const doc = documents.find((d) => d.id === selected);
        if (!doc) return;
        if (!confirm(`Delete "${doc.name}"?`)) return;

        try {
            const res = await deleteDocument(doc.id);
            if (res.ok) {
                selected = null;
                await loadDocuments();
            }
        } catch (e) {
            console.error("Failed to delete:", e);
        }
    }

    function getIcon(type) {
        return type === "image" ? Image : FileText;
    }

    function getIconColor(type) {
        return type === "image" ? "#2196F3" : "#6B8E9B";
    }

    function formatDate(dateStr) {
        if (!dateStr) return "";
        const d = new Date(dateStr + "Z");
        return (
            d.toLocaleDateString() +
            " " +
            d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })
        );
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div class="my-documents">
    <div class="md-menu-bar">
        <span class="md-menu-item">File</span>
        <span class="md-menu-item">Edit</span>
        <span class="md-menu-item">View</span>
        <span class="md-menu-item">Favorites</span>
        <span class="md-menu-item">Tools</span>
        <span class="md-menu-item">Help</span>
    </div>

    <div class="md-toolbar">
        <button class="md-tool-btn" on:click={loadDocuments} title="Refresh">
            <RefreshCw size={14} />
            <span>Refresh</span>
        </button>
        <button
            class="md-tool-btn"
            class:disabled={selected === null}
            on:click={handleDelete}
            title="Delete"
        >
            <Trash2 size={14} />
            <span>Delete</span>
        </button>
        <div class="md-address-bar">
            <span class="md-address-label">Address</span>
            <div class="md-address-input">
                <FolderOpen size={14} color="#E8C94A" />
                <span>My Documents</span>
            </div>
        </div>
    </div>

    <div class="md-body">
        <div class="md-sidebar">
            <div class="md-sidebar-section">
                <div class="md-sidebar-header">File and Folder Tasks</div>
                <div class="md-sidebar-item" on:click={loadDocuments}>
                    Refresh
                </div>
                {#if selected !== null}
                    <div class="md-sidebar-item" on:click={handleDelete}>
                        Delete this file
                    </div>
                {/if}
            </div>
            <div class="md-sidebar-section">
                <div class="md-sidebar-header">Other Places</div>
                <div class="md-sidebar-item">Desktop</div>
                <div class="md-sidebar-item">My Computer</div>
                <div class="md-sidebar-item">My Network Places</div>
            </div>
            <div class="md-sidebar-section">
                <div class="md-sidebar-header">Details</div>
                <div class="md-sidebar-detail">
                    {#if selected !== null}
                        {@const doc = documents.find((d) => d.id === selected)}
                        {#if doc}
                            <strong>{doc.name}</strong><br />
                            Type: {doc.type === "image"
                                ? "PNG Image"
                                : "Text Document"}<br />
                            Modified: {formatDate(doc.updated_at)}
                        {/if}
                    {:else}
                        <span>My Documents</span><br />
                        <span>{documents.length} item(s)</span>
                    {/if}
                </div>
            </div>
        </div>

        <div class="md-content">
            {#if loading}
                <div class="md-loading">Loading...</div>
            {:else if documents.length === 0}
                <div class="md-empty">
                    <FolderOpen size={48} color="#ccc" />
                    <p>This folder is empty</p>
                </div>
            {:else}
                <div class="md-file-list">
                    {#each documents as doc}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div
                            class="md-file-item"
                            class:selected={selected === doc.id}
                            on:click={() => selectDoc(doc)}
                            on:dblclick={() => openDocument(doc)}
                        >
                            <div class="md-file-icon">
                                <svelte:component
                                    this={getIcon(doc.type)}
                                    size={32}
                                    color={getIconColor(doc.type)}
                                    strokeWidth={1.5}
                                />
                            </div>
                            <div class="md-file-info">
                                <span class="md-file-name">{doc.name}</span>
                                <span class="md-file-meta">
                                    {doc.type === "image"
                                        ? "PNG Image"
                                        : "Text Document"}
                                </span>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <div class="md-status-bar">
        <span>{documents.length} object(s)</span>
        {#if selected !== null}
            <span>1 object(s) selected</span>
        {/if}
    </div>
</div>

<style>
    .my-documents {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .md-menu-bar {
        display: flex;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .md-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .md-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .md-toolbar {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
        background: var(--xp-window-bg);
    }

    .md-tool-btn {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        padding: 2px 8px;
        background: var(--xp-button-face);
        border: 1px solid transparent;
        cursor: pointer;
        height: 24px;
        font-family: var(--xp-font);
        color: #333;
    }
    .md-tool-btn:hover {
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
    }
    .md-tool-btn.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .md-address-bar {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-left: 8px;
    }
    .md-address-label {
        font-size: 11px;
        color: #333;
    }
    .md-address-input {
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

    .md-body {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .md-sidebar {
        width: 200px;
        background: linear-gradient(180deg, #6b89d4 0%, #4f72c2 100%);
        padding: 8px;
        overflow-y: auto;
        border-right: 1px solid #3060b0;
    }
    .md-sidebar-section {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 6px;
        padding: 8px;
        margin-bottom: 8px;
    }
    .md-sidebar-header {
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
    }
    .md-sidebar-item {
        font-size: 11px;
        color: #d0dfff;
        padding: 2px 4px;
        cursor: pointer;
        text-decoration: underline;
    }
    .md-sidebar-item:hover {
        color: #fff;
    }
    .md-sidebar-detail {
        font-size: 10px;
        color: #d0dfff;
        line-height: 1.6;
    }

    .md-content {
        flex: 1;
        padding: 8px;
        overflow-y: auto;
        background: #fff;
    }

    .md-loading,
    .md-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #999;
        font-size: 12px;
        gap: 8px;
    }

    .md-file-list {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-content: flex-start;
    }

    .md-file-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 90px;
        padding: 8px 4px;
        cursor: pointer;
        border-radius: 3px;
        gap: 4px;
    }
    .md-file-item:hover {
        background: #e8f0ff;
    }
    .md-file-item.selected {
        background: var(--xp-selection);
        color: white;
    }

    .md-file-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .md-file-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .md-file-name {
        font-size: 11px;
        word-break: break-all;
        line-height: 1.2;
        max-width: 85px;
    }
    .md-file-meta {
        font-size: 9px;
        color: #999;
    }
    .md-file-item.selected .md-file-meta {
        color: #d0dfff;
    }

    .md-status-bar {
        height: 22px;
        background: var(--xp-window-bg);
        border-top: 1px solid var(--xp-button-shadow);
        display: flex;
        align-items: center;
        padding: 0 8px;
        gap: 16px;
        font-size: 10px;
        color: #666;
    }
</style>
