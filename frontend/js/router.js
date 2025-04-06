import { loadPage } from './ui.js';

export function initializeRouter() {
    document.querySelectorAll("a.nav-link, a[href^='#']").forEach(link => {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            const pageName = this.getAttribute("href").replace('#/', '');
            navigateTo(pageName);
        });
    });

    window.addEventListener('hashchange', handleNavigation);
    handleNavigation();
}

export function navigateTo(page) {
    window.location.hash = `#/${page}`;
}

function handleNavigation() {
    const pageName = window.location.hash.replace('#/', '') || 'featured';
    loadPage(pageName);
} 