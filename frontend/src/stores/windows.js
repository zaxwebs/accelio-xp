import { writable, derived } from 'svelte/store';

let nextId = 1;
let nextZIndex = 100;

function createWindowStore() {
    const { subscribe, update, set } = writable([]);

    return {
        subscribe,
        open(appName, title, icon, props = {}) {
            const id = nextId++;
            const zIndex = nextZIndex++;
            const windowDef = {
                id,
                appName,
                title,
                icon,
                zIndex,
                minimized: false,
                maximized: false,
                x: 80 + (id % 8) * 30,
                y: 40 + (id % 6) * 30,
                width: props.width || 640,
                height: props.height || 480,
                props,
            };
            update(wins => [...wins, windowDef]);
            return id;
        },
        close(id) {
            update(wins => wins.filter(w => w.id !== id));
        },
        minimize(id) {
            update(wins => wins.map(w => w.id === id ? { ...w, minimized: true } : w));
        },
        restore(id) {
            const z = nextZIndex++;
            update(wins => wins.map(w => w.id === id ? { ...w, minimized: false, zIndex: z } : w));
        },
        toggleMaximize(id) {
            update(wins => wins.map(w => w.id === id ? { ...w, maximized: !w.maximized } : w));
        },
        focus(id) {
            const z = nextZIndex++;
            update(wins => wins.map(w => w.id === id ? { ...w, zIndex: z, minimized: false } : w));
        },
        updatePosition(id, x, y) {
            update(wins => wins.map(w => w.id === id ? { ...w, x, y } : w));
        },
        updateSize(id, width, height) {
            update(wins => wins.map(w => w.id === id ? { ...w, width, height } : w));
        },
        closeAll() {
            set([]);
        },
    };
}

export const windows = createWindowStore();
export const activeWindowId = derived(windows, $wins => {
    if ($wins.length === 0) return null;
    const visible = $wins.filter(w => !w.minimized);
    if (visible.length === 0) return null;
    return visible.reduce((a, b) => a.zIndex > b.zIndex ? a : b).id;
});
