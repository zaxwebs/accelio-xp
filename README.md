# Windows XP Clone

A fully functional Windows XP desktop clone built with **Svelte**, **Lucide** icons, and powered by the **Accelio** PHP framework.

## Features

- 🖥️ **Full desktop experience** — boot screen → login → desktop with XP "Bliss" wallpaper
- 🪟 **Window management** — drag, resize, minimize, maximize, close, z-index focus
- 📋 **Taskbar** — Start button, open window buttons, system tray, live clock
- 🟢 **Start Menu** — two-column layout with pinned programs, right panel, and power options
- 🖱️ **Desktop icons** — My Computer, Recycle Bin, My Documents, Internet Explorer
- 📁 **Right-click context menu** on the desktop

### Built-in Applications

| App | Description |
|-----|-------------|
| **Notepad** | Text editor with File/Edit/Format menus |
| **Calculator** | Standard calculator with full arithmetic and memory |
| **Minesweeper** | Classic 9×9 game with flagging, flood-fill, timer |
| **Paint** | Canvas drawing — pencil, eraser, line, rectangle, 24 colors |
| **My Computer** | File Explorer with drives, usage bars, sidebar |
| **Internet Explorer** | Address bar + iframe browser |
| **Command Prompt** | Terminal with `dir`, `help`, `ver`, `ipconfig`, `systeminfo`, `cls`, `echo` |

### XP Luna Theme

Classic blue title bar gradients, 3D buttons, styled scrollbars, Tahoma/Segoe UI fonts, green Start button — the full Luna aesthetic.

## Quick Start

### Development

```bash
cd frontend
npm install
npm run dev
```

Open **http://localhost:5173/**

### Production (via Accelio)

```bash
cd frontend
npm run build
cd ..
composer install
composer serve
```

Open **http://localhost:8080/**

## Project Structure

```
frontend/
  src/
    App.svelte                 Root orchestrator (boot → login → desktop)
    stores/windows.js          Window state management
    styles/xp-theme.css        XP Luna blue theme
    components/
      BootScreen.svelte        XP boot animation
      LoginScreen.svelte       User login screen
      Desktop.svelte           Wallpaper, icons, context menu
      Taskbar.svelte           Start button, tray, clock
      StartMenu.svelte         Two-column program menu
      Window.svelte            Draggable/resizable window
    apps/
      Notepad.svelte
      Calculator.svelte
      Minesweeper.svelte
      Paint.svelte
      MyComputer.svelte
      InternetExplorer.svelte
      CommandPrompt.svelte

routes/web.php                 Accelio route serving the SPA
public/build/                  Production build output
```

## Tech Stack

- **Frontend** — [Svelte 5](https://svelte.dev/) + [Vite](https://vite.dev/)
- **Icons** — [Lucide Svelte](https://lucide.dev/)
- **Backend** — [Accelio](https://github.com/zaxwebs/accelio) (PHP 8.3+)

## Requirements

- Node.js 18+, npm
- PHP 8.3+, Composer (for production serving)

## License

MIT
