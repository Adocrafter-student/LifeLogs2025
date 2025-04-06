import { navigateTo } from './router.js';

export function initializeAuth() {

    window.addEventListener('hashchange', function() {
        attachFormListeners();
        attachLogoutListener();
    });

    // Dodajemo event listenere i za početno učitavanje
    attachFormListeners();
    attachLogoutListener();
}

function attachFormListeners() {
    const loginForm = document.querySelector('#loginForm');
    const registrationForm = document.querySelector('#registrationForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }

    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleRegistration();
        });
    }
}

function handleLogin() {
    // Ovdje će ići stvarna logika za login kada implementiramo backend
    alert('Login successful');
    navigateTo('profile');
}

function handleRegistration() {
    // Ovdje će ići stvarna logika za registraciju kada implementiramo backend
    alert('Registration successful');
    navigateTo('profile');
}

function attachLogoutListener() {
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            alert('Logged out');
            navigateTo('login');
        });
    }
} 