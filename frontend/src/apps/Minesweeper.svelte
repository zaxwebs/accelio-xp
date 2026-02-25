<script>
    import { Smile, Frown, Meh } from "lucide-svelte";

    let rows = 9;
    let cols = 9;
    let totalMines = 10;
    let board = [];
    let revealed = [];
    let flagged = [];
    let gameOver = false;
    let gameWon = false;
    let minesLeft = totalMines;
    let firstClick = true;
    let startTime = 0;
    let timer = 0;
    let timerInterval = null;

    initBoard();

    function initBoard() {
        board = Array(rows)
            .fill(null)
            .map(() => Array(cols).fill(0));
        revealed = Array(rows)
            .fill(null)
            .map(() => Array(cols).fill(false));
        flagged = Array(rows)
            .fill(null)
            .map(() => Array(cols).fill(false));
        gameOver = false;
        gameWon = false;
        minesLeft = totalMines;
        firstClick = true;
        timer = 0;
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = null;
    }

    function placeMines(safeR, safeC) {
        let placed = 0;
        while (placed < totalMines) {
            const r = Math.floor(Math.random() * rows);
            const c = Math.floor(Math.random() * cols);
            if (
                board[r][c] !== -1 &&
                !(Math.abs(r - safeR) <= 1 && Math.abs(c - safeC) <= 1)
            ) {
                board[r][c] = -1;
                placed++;
            }
        }
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                if (board[r][c] === -1) continue;
                let count = 0;
                for (let dr = -1; dr <= 1; dr++) {
                    for (let dc = -1; dc <= 1; dc++) {
                        const nr = r + dr,
                            nc = c + dc;
                        if (
                            nr >= 0 &&
                            nr < rows &&
                            nc >= 0 &&
                            nc < cols &&
                            board[nr][nc] === -1
                        )
                            count++;
                    }
                }
                board[r][c] = count;
            }
        }
        board = board;
    }

    function reveal(r, c) {
        if (gameOver || gameWon || flagged[r][c] || revealed[r][c]) return;

        if (firstClick) {
            firstClick = false;
            placeMines(r, c);
            startTime = Date.now();
            timerInterval = setInterval(() => {
                timer = Math.floor((Date.now() - startTime) / 1000);
            }, 1000);
        }

        revealed[r][c] = true;

        if (board[r][c] === -1) {
            gameOver = true;
            revealAll();
            if (timerInterval) clearInterval(timerInterval);
            return;
        }

        if (board[r][c] === 0) {
            for (let dr = -1; dr <= 1; dr++) {
                for (let dc = -1; dc <= 1; dc++) {
                    const nr = r + dr,
                        nc = c + dc;
                    if (nr >= 0 && nr < rows && nc >= 0 && nc < cols)
                        reveal(nr, nc);
                }
            }
        }

        revealed = revealed;
        checkWin();
    }

    function toggleFlag(e, r, c) {
        e.preventDefault();
        if (gameOver || gameWon || revealed[r][c]) return;
        flagged[r][c] = !flagged[r][c];
        minesLeft = totalMines - flagged.flat().filter(Boolean).length;
        flagged = flagged;
    }

    function revealAll() {
        for (let r = 0; r < rows; r++)
            for (let c = 0; c < cols; c++) revealed[r][c] = true;
        revealed = revealed;
    }

    function checkWin() {
        let unrevealedSafe = 0;
        for (let r = 0; r < rows; r++)
            for (let c = 0; c < cols; c++)
                if (!revealed[r][c] && board[r][c] !== -1) unrevealedSafe++;
        if (unrevealedSafe === 0) {
            gameWon = true;
            if (timerInterval) clearInterval(timerInterval);
        }
    }

    function newGame() {
        initBoard();
    }

    const numColors = [
        "",
        "#0100FE",
        "#017F01",
        "#FE0000",
        "#01007F",
        "#810102",
        "#008080",
        "#000",
        "#808080",
    ];
</script>

<div class="minesweeper">
    <div class="ms-menu-bar">
        <span class="ms-menu-item">Game</span>
        <span class="ms-menu-item">Help</span>
    </div>

    <div class="ms-container">
        <div class="ms-header">
            <div class="ms-counter">
                {String(Math.max(0, minesLeft)).padStart(3, "0")}
            </div>
            <button class="ms-face" on:click={newGame}>
                {#if gameOver}
                    <Frown size={20} color="#FFD700" />
                {:else if gameWon}
                    <Smile size={20} color="#FFD700" />
                {:else}
                    <Meh size={20} color="#FFD700" />
                {/if}
            </button>
            <div class="ms-counter">
                {String(Math.min(999, timer)).padStart(3, "0")}
            </div>
        </div>

        <div class="ms-board">
            {#each Array(rows) as _, r}
                <div class="ms-row">
                    {#each Array(cols) as _, c}
                        <button
                            class="ms-cell"
                            class:revealed={revealed[r][c]}
                            class:mine={revealed[r][c] && board[r][c] === -1}
                            class:flagged={flagged[r][c] && !revealed[r][c]}
                            on:click={() => reveal(r, c)}
                            on:contextmenu={(e) => toggleFlag(e, r, c)}
                        >
                            {#if flagged[r][c] && !revealed[r][c]}
                                🚩
                            {:else if revealed[r][c]}
                                {#if board[r][c] === -1}
                                    💣
                                {:else if board[r][c] > 0}
                                    <span
                                        style="color: {numColors[board[r][c]]}"
                                        >{board[r][c]}</span
                                    >
                                {/if}
                            {/if}
                        </button>
                    {/each}
                </div>
            {/each}
        </div>
    </div>
</div>

<style>
    .minesweeper {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
    }

    .ms-menu-bar {
        display: flex;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
    }
    .ms-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .ms-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .ms-container {
        padding: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .ms-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        max-width: 280px;
        padding: 4px 6px;
        background: var(--xp-button-face);
        border: 2px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
        margin-bottom: 6px;
    }

    .ms-counter {
        background: #000;
        color: #f00;
        font-family: "Courier New", monospace;
        font-size: 20px;
        font-weight: 700;
        padding: 2px 6px;
        min-width: 50px;
        text-align: center;
        letter-spacing: 2px;
    }

    .ms-face {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--xp-button-face);
        border: 2px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-dark-shadow)
            var(--xp-button-dark-shadow) var(--xp-button-highlight);
        cursor: pointer;
        padding: 0;
    }
    .ms-face:active {
        border-color: var(--xp-button-dark-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-dark-shadow);
    }

    .ms-board {
        border: 2px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
    }

    .ms-row {
        display: flex;
    }

    .ms-cell {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-shadow)
            var(--xp-button-shadow) var(--xp-button-highlight);
        background: var(--xp-button-face);
        padding: 0;
        font-family: "Segoe UI", Tahoma, sans-serif;
    }
    .ms-cell.revealed {
        border: 1px solid #c0c0c0;
        background: #d0d0d0;
    }
    .ms-cell.mine {
        background: #ff0000;
    }
</style>
