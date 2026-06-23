let sidebar: HTMLElement|null|false = false;
export function hideSidebar() {
    if (getSidebar()) {
        getSidebar()!.style.opacity = '0';
        getSidebar()!.style.pointerEvents = 'none';
    }
}

export function showSidebar() {
    if (getSidebar()) {
        getSidebar()!.style.opacity = '1';
        getSidebar()!.style.pointerEvents = 'auto';
    }
}

function getSidebar() {
    if (sidebar === false) {
        sidebar = document.querySelector('.interface-interface-skeleton__sidebar') as HTMLElement;
    }

    return sidebar;
}