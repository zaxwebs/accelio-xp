<script>
    let display = "0";
    let prevOperand = null;
    let operator = null;
    let waitingForOperand = false;

    function inputDigit(digit) {
        if (waitingForOperand) {
            display = digit;
            waitingForOperand = false;
        } else {
            display = display === "0" ? digit : display + digit;
        }
    }

    function inputDecimal() {
        if (waitingForOperand) {
            display = "0.";
            waitingForOperand = false;
            return;
        }
        if (!display.includes(".")) {
            display = display + ".";
        }
    }

    function handleOperator(nextOp) {
        const current = parseFloat(display);

        if (operator && !waitingForOperand) {
            const result = calculate(prevOperand, current, operator);
            display = String(result);
            prevOperand = result;
        } else {
            prevOperand = current;
        }

        operator = nextOp;
        waitingForOperand = true;
    }

    function calculate(a, b, op) {
        switch (op) {
            case "+":
                return a + b;
            case "-":
                return a - b;
            case "*":
                return a * b;
            case "/":
                return b !== 0 ? a / b : 0;
            default:
                return b;
        }
    }

    function handleEquals() {
        if (operator === null || prevOperand === null) return;
        const current = parseFloat(display);
        const result = calculate(prevOperand, current, operator);
        display = String(result);
        prevOperand = null;
        operator = null;
        waitingForOperand = true;
    }

    function clear() {
        display = "0";
        prevOperand = null;
        operator = null;
        waitingForOperand = false;
    }

    function clearEntry() {
        display = "0";
    }

    function backspace() {
        if (display.length > 1) {
            display = display.slice(0, -1);
        } else {
            display = "0";
        }
    }

    function negate() {
        const val = parseFloat(display);
        display = String(-val);
    }

    function sqrt() {
        const val = parseFloat(display);
        display = String(Math.sqrt(val));
    }

    function percent() {
        if (prevOperand !== null) {
            display = String((prevOperand * parseFloat(display)) / 100);
        }
    }

    function inverse() {
        const val = parseFloat(display);
        if (val !== 0) display = String(1 / val);
    }

    const buttons = [
        ["MC", "MR", "MS", "M+"],
        ["Back", "CE", "C", "±"],
        ["7", "8", "9", "/"],
        ["4", "5", "6", "*"],
        ["1", "2", "3", "-"],
        ["0", ".", "=", "+"],
    ];

    function handleButton(btn) {
        if (btn >= "0" && btn <= "9") inputDigit(btn);
        else if (btn === ".") inputDecimal();
        else if (btn === "+" || btn === "-" || btn === "*" || btn === "/")
            handleOperator(btn);
        else if (btn === "=") handleEquals();
        else if (btn === "C") clear();
        else if (btn === "CE") clearEntry();
        else if (btn === "Back") backspace();
        else if (btn === "±") negate();
        else if (btn === "sqrt") sqrt();
        else if (btn === "%") percent();
        else if (btn === "1/x") inverse();
    }
</script>

<div class="calculator">
    <div class="calc-menu-bar">
        <span class="calc-menu-item">Edit</span>
        <span class="calc-menu-item">View</span>
        <span class="calc-menu-item">Help</span>
    </div>
    <div class="calc-display">
        <input type="text" readonly value={display} class="calc-input" />
    </div>
    <div class="calc-buttons">
        {#each buttons as row}
            <div class="calc-row">
                {#each row as btn}
                    <button
                        class="calc-btn"
                        class:wide={btn === "0"}
                        class:operator-btn={["+", "-", "*", "/", "="].includes(
                            btn,
                        )}
                        class:function-btn={[
                            "Back",
                            "CE",
                            "C",
                            "±",
                            "MC",
                            "MR",
                            "MS",
                            "M+",
                        ].includes(btn)}
                        on:click={() => handleButton(btn)}
                    >
                        {btn}
                    </button>
                {/each}
            </div>
        {/each}
    </div>
</div>

<style>
    .calculator {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--xp-window-bg);
        padding: 4px;
    }

    .calc-menu-bar {
        display: flex;
        gap: 4px;
        padding: 2px 4px;
        border-bottom: 1px solid var(--xp-button-shadow);
        margin-bottom: 4px;
    }
    .calc-menu-item {
        font-size: 11px;
        padding: 1px 6px;
        cursor: pointer;
    }
    .calc-menu-item:hover {
        background: var(--xp-selection);
        color: white;
    }

    .calc-display {
        padding: 2px 4px;
        margin-bottom: 6px;
    }

    .calc-input {
        width: 100%;
        text-align: right;
        font-size: 16px;
        font-family: "Segoe UI", Tahoma, sans-serif;
        padding: 4px 6px;
        background: #fff;
        border: 1px solid;
        border-color: var(--xp-button-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-shadow);
        outline: none;
    }

    .calc-buttons {
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
        padding: 0 4px;
    }

    .calc-row {
        display: flex;
        gap: 3px;
        flex: 1;
    }

    .calc-btn {
        flex: 1;
        font-family: var(--xp-font);
        font-size: 12px;
        background: var(--xp-button-face);
        border: 1px solid;
        border-color: var(--xp-button-highlight) var(--xp-button-dark-shadow)
            var(--xp-button-dark-shadow) var(--xp-button-highlight);
        cursor: pointer;
        outline: none;
        min-height: 28px;
        font-weight: 700;
        color: #000;
    }
    .calc-btn:active {
        border-color: var(--xp-button-dark-shadow) var(--xp-button-highlight)
            var(--xp-button-highlight) var(--xp-button-dark-shadow);
    }
    .calc-btn.operator-btn {
        color: #c00;
    }
    .calc-btn.function-btn {
        color: #006;
        font-weight: 400;
        font-size: 10px;
    }
</style>
