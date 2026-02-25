<script>
    import { onMount, tick } from "svelte";

    let lines = [
        "Microsoft Windows XP [Version 5.1.2600]",
        "(C) Copyright 1985-2001 Microsoft Corp.",
        "",
    ];
    let inputLine = "";
    let currentDir = "C:\\Documents and Settings\\User>";
    let terminalEl;
    let inputEl;

    const commands = {
        help: () => [
            "Available commands:",
            "  HELP      - Show this help",
            "  CLS       - Clear screen",
            "  DIR       - List directory",
            "  DATE      - Show date",
            "  TIME      - Show time",
            "  VER       - Show version",
            "  ECHO      - Echo text",
            "  COLOR     - Change colors",
            "  TITLE     - Set title",
            "  IPCONFIG  - Show IP config",
            "  SYSTEMINFO - Show system info",
        ],
        cls: () => {
            lines = [];
            return [];
        },
        dir: () => [
            " Volume in drive C has no label.",
            " Volume Serial Number is A1B2-C3D4",
            "",
            " Directory of C:\\Documents and Settings\\User",
            "",
            "02/25/2026  08:15 PM    <DIR>          .",
            "02/25/2026  08:15 PM    <DIR>          ..",
            "02/25/2026  08:15 PM    <DIR>          Desktop",
            "02/25/2026  08:15 PM    <DIR>          My Documents",
            "02/25/2026  08:15 PM    <DIR>          Favorites",
            "02/25/2026  08:15 PM    <DIR>          Start Menu",
            "               0 File(s)              0 bytes",
            "               6 Dir(s)  31,200,000,000 bytes free",
        ],
        date: () => [
            `The current date is: ${new Date().toLocaleDateString("en-US", { weekday: "short", year: "numeric", month: "2-digit", day: "2-digit" })}`,
        ],
        time: () => [
            `The current time is: ${new Date().toLocaleTimeString("en-US")}`,
        ],
        ver: () => ["", "Microsoft Windows XP [Version 5.1.2600]", ""],
        ipconfig: () => [
            "",
            "Windows IP Configuration",
            "",
            "Ethernet adapter Local Area Connection:",
            "",
            "   Connection-specific DNS Suffix  . :",
            "   IP Address. . . . . . . . . . . . : 192.168.1.100",
            "   Subnet Mask . . . . . . . . . . . : 255.255.255.0",
            "   Default Gateway . . . . . . . . . : 192.168.1.1",
        ],
        systeminfo: () => [
            "",
            "Host Name:                 DESKTOP-XP",
            "OS Name:                   Microsoft Windows XP Professional",
            "OS Version:                5.1.2600 Service Pack 3 Build 2600",
            "OS Manufacturer:           Microsoft Corporation",
            "System Type:               X86-based PC",
            "Total Physical Memory:     2,048 MB",
            "Available Physical Memory: 1,234 MB",
        ],
    };

    function processCommand(input) {
        const trimmed = input.trim();
        lines = [...lines, currentDir + trimmed];

        if (!trimmed) {
            return;
        }

        const parts = trimmed.split(/\s+/);
        const cmd = parts[0].toLowerCase();
        const args = parts.slice(1).join(" ");

        if (cmd === "echo") {
            lines = [...lines, args || ""];
        } else if (commands[cmd]) {
            const output = commands[cmd]();
            lines = [...lines, ...output];
        } else {
            lines = [
                ...lines,
                `'${parts[0]}' is not recognized as an internal or external command,`,
                "operable program or batch file.",
            ];
        }

        lines = [...lines, ""];
    }

    async function handleKeydown(e) {
        if (e.key === "Enter") {
            processCommand(inputLine);
            inputLine = "";
            await tick();
            if (terminalEl) {
                terminalEl.scrollTop = terminalEl.scrollHeight;
            }
        }
    }

    function focusInput() {
        if (inputEl) inputEl.focus();
    }

    onMount(() => {
        focusInput();
    });
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div class="cmd" bind:this={terminalEl} on:click={focusInput}>
    <div class="cmd-output">
        {#each lines as line}
            <div class="cmd-line">{line}</div>
        {/each}
    </div>
    <div class="cmd-input-row">
        <span class="cmd-prompt">{currentDir}</span>
        <input
            type="text"
            class="cmd-input"
            bind:this={inputEl}
            bind:value={inputLine}
            on:keydown={handleKeydown}
            spellcheck="false"
        />
    </div>
</div>

<style>
    .cmd {
        height: 100%;
        background: #000;
        color: #c0c0c0;
        font-family: "Lucida Console", "Courier New", monospace;
        font-size: 13px;
        padding: 4px;
        overflow-y: auto;
        cursor: text;
        line-height: 1.3;
    }

    .cmd-line {
        white-space: pre-wrap;
        word-break: break-all;
    }

    .cmd-input-row {
        display: flex;
        align-items: center;
    }

    .cmd-prompt {
        white-space: nowrap;
    }

    .cmd-input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #c0c0c0;
        font-family: "Lucida Console", "Courier New", monospace;
        font-size: 13px;
        padding: 0;
        caret-color: #c0c0c0;
    }
</style>
