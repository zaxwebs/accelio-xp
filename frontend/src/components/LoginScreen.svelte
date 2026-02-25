<script>
    import { createEventDispatcher } from "svelte";
    import { User } from "lucide-svelte";

    const dispatch = createEventDispatcher();

    function handleLogin() {
        dispatch("login");
    }

    function handleKeydown(e) {
        if (e.key === "Enter") handleLogin();
    }
</script>

<div class="login-screen">
    <div class="login-top-bar">
        <span>To begin, click your user name</span>
    </div>
    <div class="login-center">
        <div class="login-branding">
            <div class="xp-logo-login">
                <span class="microsoft">Microsoft</span>
                <span class="windows">Windows</span><span class="xp">XP</span>
            </div>
        </div>
        <div class="login-users">
            <button
                class="user-card"
                on:click={handleLogin}
                on:keydown={handleKeydown}
            >
                <div class="user-avatar">
                    <User size={48} color="#4E6B8B" />
                </div>
                <div class="user-info">
                    <span class="user-name">User</span>
                </div>
            </button>
        </div>
    </div>
    <div class="login-bottom-bar">
        <div class="turn-off">
            <span>Turn off computer</span>
        </div>
        <div class="windows-version">Windows XP Professional</div>
    </div>
</div>

<style>
    .login-screen {
        width: 100vw;
        height: 100vh;
        background: linear-gradient(
            180deg,
            #003399 0%,
            #0055cc 30%,
            #0066dd 50%,
            #0055cc 70%,
            #003399 100%
        );
        display: flex;
        flex-direction: column;
    }

    .login-top-bar {
        height: 48px;
        background: linear-gradient(180deg, #5b8ad6 0%, #2960bf 100%);
        border-bottom: 2px solid #194da0;
        display: flex;
        align-items: center;
        padding: 0 30px;
        color: white;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .login-center {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 80px;
    }

    .login-branding {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding-right: 40px;
        border-right: 1px solid rgba(255, 255, 255, 0.3);
    }

    .xp-logo-login {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    .microsoft {
        color: #fff;
        font-size: 22px;
        font-weight: 300;
        font-style: normal;
    }
    .windows {
        color: #fff;
        font-size: 32px;
        font-weight: 700;
        font-style: italic;
    }
    .xp {
        color: #ff6600;
        font-size: 40px;
        font-weight: 700;
        font-style: italic;
        text-shadow: 0 0 12px rgba(255, 102, 0, 0.6);
    }

    .login-users {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-left: 40px;
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 10px 24px 10px 10px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.15s;
        color: white;
        font-family: var(--xp-font);
    }
    .user-card:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.35);
    }

    .user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 6px;
        background: linear-gradient(135deg, #d9e4f5, #f5e6d0);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .user-name {
        font-size: 16px;
        font-weight: 700;
    }

    .login-bottom-bar {
        height: 48px;
        background: linear-gradient(180deg, #2960bf 0%, #5b8ad6 100%);
        border-top: 2px solid #194da0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px;
        color: white;
        font-size: 11px;
    }

    .turn-off {
        cursor: pointer;
        padding: 4px 12px;
        border-radius: 3px;
    }
    .turn-off:hover {
        background: rgba(255, 255, 255, 0.15);
    }
</style>
