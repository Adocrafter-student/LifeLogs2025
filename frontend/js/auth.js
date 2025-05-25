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

// Funkcija za dobijanje korisničkih podataka iz localStorage
function getLocalUser() {
    const userString = localStorage.getItem('user');
    try {
        return userString ? JSON.parse(userString) : null;
    } catch (e) {
        console.error("Auth: Error parsing user from localStorage", e);
        localStorage.removeItem('user'); // Ukloni neispravne podatke
        return null;
    }
}

export async function displayUserProfile() {
    console.log("Auth: displayUserProfile called. Current hash:", window.location.hash);
    if (window.location.hash !== '#/profile' && window.location.hash !== '#profile') {
        console.log("Auth: Not on profile page, skipping displayUserProfile.");
        return;
    }

    if (!isAuthenticated()) {
        console.warn("Auth: User not authenticated, cannot display profile.");
        navigateTo('login'); // Preusmjeri na login ako nije autenfikovan
        return;
    }

    const profileUsernameEl = document.getElementById('profileUsername');
    const profileEmailEl = document.getElementById('profileEmail');
    const profileBioEl = document.getElementById('profileBio');
    const profilePictureEl = document.getElementById('profilePicture');

    // Ako elementi za prikaz profila ne postoje na stranici, ne radi ništa
    // Ovo je važno jer se ova funkcija može pozvati i kada profil nije vidljiv
    if (!profileUsernameEl || !profileEmailEl || !profileBioEl || !profilePictureEl) {
        console.log("Auth: Profile display elements not found on the current page. Skipping update.");
        return;
    }

    console.log("Auth: Attempting to display user profile data.");

    let user = getLocalUser();
    console.log("Auth: User data from localStorage:", user);

    if (!user) { // Ako nema u localStorage, dohvati sa servera
        console.log("Auth: User data not in localStorage, fetching from /api/auth/me");
        try {
            const token = getToken();
            if (!token) throw new Error("No token available for /me request");

            const response = await fetch('/LifeLogs2025/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: response.statusText }));
                console.error("Auth: Error fetching user data from /me:", response.status, errorData);
                throw new Error(errorData.message || `Failed to fetch user data: ${response.status}`);
            }
            user = await response.json();
            if (user) {
                localStorage.setItem('user', JSON.stringify(user)); // Sačuvaj svježe podatke
                console.log("Auth: Fetched and saved user data from /me:", user);
            } else {
                console.error("Auth: /me endpoint returned no user data.");
                throw new Error("No user data from /me endpoint");
            }
        } catch (error) {
            console.error("Auth: Failed to fetch user profile from API:", error);
            alert(`Could not load profile data: ${error.message}. Please try logging in again.`);
            removeToken(); // Ukloni token jer je možda neispravan ili sesija istekla
            localStorage.removeItem('user');
            navigateTo('login');
            return;
        }
    }

    if (user) {
        profileUsernameEl.textContent = user.username || 'N/A';
        profileEmailEl.textContent = user.email || 'N/A';
        profileBioEl.textContent = user.bio || 'Tell us about yourself...';
        
        const defaultAvatar = 'images/avatar.jpg';
        profilePictureEl.src = user.avatar_url ? ('/LifeLogs2025/' + user.avatar_url.replace(/^\.\.\//, '')) : defaultAvatar;
        console.log("Auth: Profile elements updated. Avatar src:", profilePictureEl.src);

        // Popunjavanje forme za izmjenu
        const formUsernameEl = document.getElementById('username');
        const formEmailEl = document.getElementById('email');
        const formBioEl = document.getElementById('bio');

        if (formUsernameEl) formUsernameEl.value = user.username || '';
        if (formEmailEl) formEmailEl.value = user.email || '';
        if (formBioEl) formBioEl.value = user.bio || '';
        console.log("Auth: Profile form fields populated.");

    } else {
        console.warn("Auth: No user data available to display on profile page.");
    }
}

export function initializeAuth() {
    console.log("Auth: initializeAuth called - Script version: " + new Date().toLocaleTimeString());

    attachFormListeners();
    attachLogoutListener();
    attachStatusCheckListener();
    console.log("Auth: Initial listeners attached from initializeAuth");

    // Odmah provjeri i prikaži profil ako smo već na #profile i ulogovani
    if ((window.location.hash === '#/profile' || window.location.hash === '#profile') && isAuthenticated()) {
        console.log("Auth: Initial load on profile page and authenticated, calling displayUserProfile.");
        displayUserProfile();
    }

    window.addEventListener('hashchange', function() {
        const newHash = window.location.hash;
        console.log(`Auth: hashchange event triggered. New hash: ${newHash}`);
        setTimeout(() => {
            console.log(`Auth: Attaching listeners after hashchange for hash: ${newHash} (inside setTimeout 200ms)`);
            attachFormListeners();
            attachLogoutListener();
            // attachStatusCheckListener(); 

            // Prikazi profilne podatke ako smo navigirali na profilnu stranicu
            if ((newHash === '#/profile' || newHash === '#profile') && isAuthenticated()) {
                console.log("Auth: Navigated to profile page and authenticated, calling displayUserProfile.");
                displayUserProfile();
            }
        }, 200);
    });
}

function attachFormListeners() {
    console.log("Auth: attachFormListeners called");
    const loginForm = document.querySelector('#loginForm');
    const registrationForm = document.querySelector('#registrationForm');
    const profileForm = document.getElementById('profileForm'); // Dodajemo referencu na profileForm

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

    if (profileForm) {
        console.log("Auth: Profile form found.");
        if (!profileForm.dataset.listenerAttached) {
            profileForm.addEventListener('submit', function(e) {
                console.log("Auth: Profile form submitted.");
                e.preventDefault();
                handleProfileUpdate();
            });
            profileForm.dataset.listenerAttached = 'true';
            console.log("Auth: Profile form listener ATTACHED.");
        } else {
            console.log("Auth: Profile form listener ALREADY ATTACHED.");
        }
    } else {
        // console.log("Auth: Profile form NOT found on current page.");
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
            // Više ne dohvatamo /me ovdje, to će uraditi displayUserProfile kada se navigira na #profile
            // const userResponse = await fetch('/LifeLogs2025/api/auth/me', {
            //     headers: {
            //         'Authorization': `Bearer ${data.token}`
            //     }
            // });
            // const userData = await userResponse.json();
            // localStorage.setItem('user', JSON.stringify(userData));
            
            // Sačuvaj korisnika direktno iz login odgovora ako ga AuthService vraća
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
                console.log("Auth: User data saved to localStorage from login response:", data.user);
            } else {
                // Ako login ne vraća korisnika, moraće se dohvatiti preko /me na profilnoj stranici
                localStorage.removeItem('user'); // Ukloni stare podatke ako postoje
                console.log("Auth: Login response did not include user data. Will be fetched on profile page.");
            }

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

async function handleProfileUpdate() {
    console.log("Auth: handleProfileUpdate called");
    const token = getToken();
    if (!token) {
        alert("Authentication error. Please login again.");
        navigateTo('login');
        return;
    }

    const user = getLocalUser();
    if (!user || !user.id) {
        alert("User data not found. Please try refreshing or logging in again.");
        return;
    }
    const userId = user.id;

    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const bio = document.getElementById('bio').value;
    // Trenutno ne hendlujemo upload avatara, samo postojeća polja

    const updatedData = {
        username: username,
        email: email,
        bio: bio
        // Ako backend ruta zahtijeva password, a mi ga ne šaljemo, to će biti problem.
        // Za sada šaljemo samo ova polja.
    };

    console.log("Auth: Attempting to update profile for user ID:", userId, "with data:", updatedData);

    try {
        const response = await fetch(`/LifeLogs2025/api/users/${userId}`, { // Koristimo PUT /api/users/{id}
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(updatedData) // Šaljemo samo username, email, bio
        });

        const responseData = await response.json().catch(() => null); // Pokušaj parsiranja JSON-a, fallback na null

        if (response.ok && responseData) {
            alert('Profile updated successfully!');
            // Ažuriraj lokalne podatke i prikaži ih ponovo
            localStorage.setItem('user', JSON.stringify(responseData)); // Pretpostavka da API vraća ažuriranog korisnika
            await displayUserProfile(); // Ponovo prikaži podatke da se osvježe i na prikazu i u formi
            console.log("Auth: Profile update successful. Response data:", responseData);
        } else {
            const errorMessage = responseData && responseData.message ? responseData.message : `Failed to update profile. Status: ${response.status}`;
            console.error("Auth: Profile update error - Server response:", responseData, "Status:", response.status);
            alert(errorMessage);
        }
    } catch (error) {
        console.error('Auth: Profile update fetch/network error:', error);
        alert('An error occurred while updating your profile. Please check the console and try again.');
    }
} 