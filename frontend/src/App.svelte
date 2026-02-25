<script>
  import BootScreen from "./components/BootScreen.svelte";
  import LoginScreen from "./components/LoginScreen.svelte";
  import Desktop from "./components/Desktop.svelte";
  import Taskbar from "./components/Taskbar.svelte";
  import StartMenu from "./components/StartMenu.svelte";
  import Window from "./components/Window.svelte";
  import { windows, activeWindowId } from "./stores/windows.js";

  // Apps
  import Notepad from "./apps/Notepad.svelte";
  import Calculator from "./apps/Calculator.svelte";
  import Minesweeper from "./apps/Minesweeper.svelte";
  import Paint from "./apps/Paint.svelte";
  import MyComputer from "./apps/MyComputer.svelte";
  import MyDocuments from "./apps/MyDocuments.svelte";
  import ImageViewer from "./apps/ImageViewer.svelte";
  import InternetExplorer from "./apps/InternetExplorer.svelte";
  import CommandPrompt from "./apps/CommandPrompt.svelte";

  import "./styles/xp-theme.css";

  const appComponents = {
    Notepad,
    Calculator,
    Minesweeper,
    Paint,
    MyComputer,
    MyDocuments,
    ImageViewer,
    InternetExplorer,
    CommandPrompt,
  };

  let phase = "boot"; // 'boot' | 'login' | 'desktop'
  let startMenuOpen = false;

  function onBootComplete() {
    phase = "login";
  }

  function onLogin() {
    phase = "desktop";
  }

  function toggleStartMenu() {
    startMenuOpen = !startMenuOpen;
  }

  function closeStartMenu() {
    startMenuOpen = false;
  }

  function handleDesktopClick() {
    startMenuOpen = false;
  }
</script>

{#if phase === "boot"}
  <BootScreen on:complete={onBootComplete} />
{:else if phase === "login"}
  <LoginScreen on:login={onLogin} />
{:else}
  <div
    class="xp-desktop-container"
    on:click={handleDesktopClick}
    on:keydown={() => {}}
  >
    <Desktop />

    {#each $windows as win (win.id)}
      <Window windowData={win} isActive={$activeWindowId === win.id}>
        <svelte:component
          this={appComponents[win.appName]}
          {...win.props}
          windowId={win.id}
        />
      </Window>
    {/each}

    {#if startMenuOpen}
      <StartMenu on:close={closeStartMenu} />
    {/if}

    <Taskbar {startMenuOpen} on:toggleStart={toggleStartMenu} />
  </div>
{/if}

<style>
  .xp-desktop-container {
    width: 100vw;
    height: 100vh;
    position: relative;
    overflow: hidden;
  }
</style>
