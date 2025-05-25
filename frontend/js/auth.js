import { navigateTo } from './router.js';

// Funkcija za čuvanje tokena u localStorage
function saveToken(token) {
    localStorage.setItem('auth_token', token);
}

// Funkcija za dobijanje tokena iz localStorage
export function getToken() {
    return localStorage.getItem('auth_token');
}

// Funkcija za brisanje tokena (logout)
function removeToken() {
    localStorage.removeItem('auth_token');
}

// Funkcija za provjeru da li je korisnik ulogovan
export function isAuthenticated() {
    return !!getToken();
}

// Funkcija za provjeru validnosti tokena
export async function checkToken() {
    const token = getToken();
    if (!token) return false;

    try {
        const response = await fetch('/LifeLogs2025/auth/verify', {
            method: 'GET',
            headers: {
                'Authentication': token
            }
        });
        return response.ok;
    } catch (error) {
        console.error('Token verification error:', error);
        return false;
    }
}

export function initializeAuth() {
    console.log("Auth: initializeAuth called - Script version: " + new Date().toLocaleTimeString()); // Za praćenje verzije skripte

    attachFormListeners();
    attachLogoutListener(); // Prvi pokušaj kačenja pri inicijalizaciji
    attachStatusCheckListener();
    console.log("Auth: Initial listeners attached from initializeAuth");

    window.addEventListener('hashchange', function() {
        const newHash = window.location.hash;
        console.log(`Auth: hashchange event triggered. New hash: ${newHash}`);
        setTimeout(() => {
            console.log(`Auth: Attaching listeners after hashchange for hash: ${newHash} (inside setTimeout 200ms)`);
            attachFormListeners();
            attachLogoutListener(); // Ponovni pokušaj kačenja nakon promjene hash-a
            // attachStatusCheckListener(); 
        }, 200); // Povećano kašnjenje
    });
}

function attachFormListeners() {
    console.log("Auth: attachFormListeners called");
    const loginForm = document.querySelector('#loginForm');
    const registrationForm = document.querySelector('#registrationForm');

    if (loginForm) {
        console.log("Auth: Login form found");
        if (!loginForm.dataset.listenerAttached) { // Sprječava višestruko kačenje
            loginForm.addEventListener('submit', function(e) {
                console.log("Auth: Login form submitted");
                e.preventDefault();
                handleLogin();
            });
            loginForm.dataset.listenerAttached = 'true';
            console.log("Auth: Login form listener ATTACHED");
        } else {
            console.log("Auth: Login form listener ALREADY ATTACHED");
        }
    } else {
        // Ova poruka je OK ako nismo na login stranici
        // console.log("Auth: Login form NOT found on current page"); 
    }

    if (registrationForm) {
        console.log("Auth: Registration form found");
        if (!registrationForm.dataset.listenerAttached) { // Sprječava višestruko kačenje
            registrationForm.addEventListener('submit', function(e) {
                console.log("Auth: Registration form submitted");
                e.preventDefault();
                handleRegistration();
            });
            registrationForm.dataset.listenerAttached = 'true';
            console.log("Auth: Registration form listener ATTACHED");
        } else {
            console.log("Auth: Registration form listener ALREADY ATTACHED");
        }
    } else {
        // Ova poruka je OK ako nismo na registration stranici
        // console.log("Auth: Registration form NOT found on current page");
    }
}

async function handleLogin() {
    console.log("Auth: handleLogin called");
    const emailInput = document.querySelector('#loginEmail');
    const passwordInput = document.querySelector('#loginPassword');
    
    if(!emailInput || !passwordInput) {
        console.error("Auth: Login email or password input not found!");
        return;
    }
    const email = emailInput.value;
    const password = passwordInput.value;

    console.log(`Auth: Attempting login with email: "${email}", password length: ${password.length}`);
    // Za DEBUG svrhe SAMO, možete privremeno logovati i samu lozinku, ali je UKLONITE odmah nakon testiranja!
    // console.log(`Auth: DEBUG LOGIN - Email: "${email}", Password: "${password}"`); 

    try {
        const response = await fetch('/LifeLogs2025/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (response.ok) {
            saveToken(data.token);
            const userResponse = await fetch('/LifeLogs2025/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${data.token}`
                }
            });
            const userData = await userResponse.json();
            localStorage.setItem('user', JSON.stringify(userData));
            navigateTo('profile');
        } else {
            alert(data.message || 'Login failed: ' + response.status);
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('An error occurred during login. Check console for details.');
    }
}

async function handleRegistration() {
    console.log("Auth: handleRegistration called");
    const usernameInput = document.querySelector('#registerUsername');
    const emailInput = document.querySelector('#registerEmail');
    const passwordInput = document.querySelector('#registerPassword');
    const confirmPasswordInput = document.querySelector('#confirmPassword');

    if (!usernameInput || !emailInput || !passwordInput || !confirmPasswordInput) {
        console.error("Auth: One or more registration inputs not found!");
        return;
    }

    const username = usernameInput.value;
    const email = emailInput.value;
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    console.log("Auth: Attempting to register with data:", { username, email, password_length: password.length, confirmPassword_length: confirmPassword.length }); // Logujemo podatke

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }

    try {
        const response = await fetch('/LifeLogs2025/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username, email, password })
        });

        const data = await response.json();

        if (response.ok) {
            alert('Registration successful! Please login.');
            navigateTo('login');
        } else {
            alert(data.message || 'Registration failed: ' + response.status);
        }
    } catch (error) {
        console.error('Registration error:', error);
        alert('An error occurred during registration. Check console for details.');
    }
}

function attachLogoutListener() {
    console.log("Auth: attachLogoutListener CALLED. Current page hash: " + window.location.hash);
    const logoutButton = document.getElementById('logoutButton');
    
    if (logoutButton) {
        console.log("Auth: Logout button FOUND on page.", logoutButton);
        if (!logoutButton.dataset.listenerAttached) {
            logoutButton.addEventListener('click', function(event) {
                event.preventDefault(); // Dobra praksa, za svaki slučaj
                console.log("Auth: Logout button CLICKED!");
                removeToken();
                localStorage.removeItem('user');
                console.log("Auth: Token and user removed from localStorage.");
                navigateTo('login');
                console.log("Auth: Navigated to login.");
            });
            logoutButton.dataset.listenerAttached = 'true';
            console.log("Auth: Logout listener SUCCESSFULLY ATTACHED to button:", logoutButton);
        } else {
            console.log("Auth: Logout listener ALREADY ATTACHED to button:", logoutButton);
        }
    } else {
        console.warn("Auth: Logout button (id='logoutButton') NOT FOUND on current page/DOM state.");
    }
}

function attachStatusCheckListener() {
    const statusButton = document.getElementById('checkStatusButton');
    if (statusButton) {
        if (!statusButton.dataset.listenerAttached) { // Sprječava višestruko kačenje
            statusButton.addEventListener('click', function(e) {
                e.preventDefault();
                // showAuthStatus(); // showAuthStatus() i dalje nije definisana, zakomentarisati ako pravi problem
                console.log("Auth: Check status button clicked (showAuthStatus not implemented in auth.js)");
            });
            statusButton.dataset.listenerAttached = 'true';
            console.log("Auth: Status check listener ATTACHED");
        }
    }
}

export function getAuthHeader() {
    const token = getToken();
    return token ? { 'Authentication': token } : {};
}

export async function checkAuthStatus() {
    const isLoggedIn = await checkToken();
    if (!isLoggedIn) {
        removeToken();
        localStorage.removeItem('user');
    }
    return isLoggedIn;
} 