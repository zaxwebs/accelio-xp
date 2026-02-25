# Windows XP Clone

A fully functional Windows XP desktop clone built with **Svelte**, **Lucide** icons, and powered by the **Accelio** PHP framework — with **persistent storage** via SQLite.

## Features

- 🖥️ **Full desktop experience** — boot screen → login → desktop with XP "Bliss" wallpaper
- 🪟 **Window management** — drag, resize, minimize, maximize, close, z-index focus
- 📋 **Taskbar** — Start button, open window buttons, system tray, live clock
- 🟢 **Start Menu** — two-column layout with pinned programs, right panel, and power options
- 🖱️ **Desktop icons** — My Computer, Recycle Bin, My Documents, Internet Explorer
- 📁 **Right-click context menu** on the desktop
- 💾 **Persistent storage** — save and open files in Notepad and Paint via SQLite

### Built-in Applications

| App | Description |
|-----|-------------|
| **Notepad** | Text editor with Save, Open, Save As via API |
| **Calculator** | Standard calculator with full arithmetic and memory |
| **Minesweeper** | Classic 9×9 game with flagging, flood-fill, timer |
| **Paint** | Canvas drawing with save/open — pencil, eraser, line, rectangle, 24 colors |
| **My Documents** | File explorer for saved documents with sidebar, toolbar, and status bar |
| **Photo Viewer** | Windows XP Picture and Fax Viewer with zoom, rotate, prev/next, XP scrollbars |
| **My Computer** | File Explorer with drives, usage bars, sidebar |
| **Internet Explorer** | Address bar + iframe browser |
| **Command Prompt** | Terminal with `dir`, `help`, `ver`, `ipconfig`, `systeminfo`, `cls`, `echo` |

### XP Luna Theme

Classic blue title bar gradients, 3D buttons, styled scrollbars, Tahoma/Segoe UI fonts, green Start button — the full Luna aesthetic.

## Quick Start

### Development

```bash
npm install --prefix frontend
npm run dev
```

Open **http://localhost:5173/**

### Production (via Accelio)

```bash
npm run build
composer install
composer serve
```

Open **http://localhost:8080/**

## Project Structure

```
frontend/
  src/
    App.svelte                 Root orchestrator (boot → login → desktop)
    lib/api.js                 API client for document persistence
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
      Notepad.svelte           Text editor with persistence
      Calculator.svelte
      Minesweeper.svelte
      Paint.svelte             Canvas drawing with image persistence
      MyDocuments.svelte       File explorer for saved documents
      ImageViewer.svelte       XP Photo Viewer
      MyComputer.svelte
      InternetExplorer.svelte
      CommandPrompt.svelte

src/
  Core/Database.php            SQLite singleton with auto-migration
  Http/CorsMiddleware.php      CORS middleware for Vite dev server

routes/web.php                 API routes for document CRUD + image upload
storage/images/                Saved Paint images
public/build/                  Production build output
```

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/documents` | List all documents |
| `GET` | `/api/documents/{id}` | Get single document |
| `POST` | `/api/documents` | Create document |
| `PUT` | `/api/documents/{id}` | Update document |
| `DELETE` | `/api/documents/{id}` | Delete document |
| `POST` | `/api/documents/upload` | Upload image (base64 PNG) |
| `GET` | `/api/documents/{id}/image` | Serve saved image |

## Tech Stack

- **Frontend** — [Svelte 5](https://svelte.dev/) + [Vite](https://vite.dev/)
- **Icons** — [Lucide Svelte](https://lucide.dev/)
- **Backend** — [Accelio](https://github.com/zaxwebs/accelio) (PHP 8.3+)
- **Database** — SQLite (auto-created on first use)

## Requirements

- Node.js 18+, npm
- PHP 8.3+, Composer (for production serving)

## License

MIT
