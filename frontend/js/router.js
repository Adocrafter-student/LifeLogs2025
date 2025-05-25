import { loadPage } from './ui.js';
import { isAuthenticated } from './auth.js';

// Lista zaštićenih ruta koje zahtijevaju autentifikaciju
const protectedRoutes = ['profile', 'create-blog', 'my-blogs'];

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
    if (protectedRoutes.includes(page) && !isAuthenticated()) {
        window.location.hash = '#/login';
        return;
    }
    
    window.location.hash = `#/${page}`;
}

function handleNavigation() {
    const pageName = window.location.hash.replace('#/', '') || 'featured';
    
    if (protectedRoutes.includes(pageName) && !isAuthenticated()) {
        window.location.hash = '#/login';
        return;
    }
    
    loadPage(pageName);
} 