window.onload = function() {
    ensureAdminUser();
    let currentUser = JSON.parse(localStorage.getItem('currentUser'));

    function navigateTo(page) {
        window.location.hash = `#/${page}`;
    }

    function handleNavigation() {
        const pageName = window.location.hash.replace('#/', '') || 'featured';
        let currentUser = JSON.parse(localStorage.getItem('currentUser'));
    
        if (pageName === 'profile' && !currentUser) {
            navigateTo('login');
        } else {
            loadPage(pageName);
        }
    }
    function loadPage(page) {
        const container = document.getElementById("container");
        const basePath = window.location.href.includes('localhost') ? '/LifeLogs/' : '/';
        const url = `${basePath}${page}.html`;
    
        fetch(url).then(response => {
            if (response.ok) {
                return response.text();
            } else {
                return fetch(`${basePath}404.html`).then(response => response.ok ? response.text() : 'Page not found');
            }
        }).then(html => {
            container.innerHTML = html;
            document.title = page.charAt(0).toUpperCase() + page.slice(1) + " | LifeLogs";
            if (page === 'login' || page === 'registration') {
                attachFormListener(page);
            }
            attachLogoutListener(); 
        }).catch(error => {
            console.error('Failed to load page', error);
            container.innerHTML = 'Error loading page.';
        });
    }

    document.querySelectorAll("a.nav-link, a[href^='#']").forEach(link => {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            const pageName = this.getAttribute("href").replace('#/', '');
            navigateTo(pageName);
        });
    });

    window.addEventListener('hashchange', handleNavigation);
    handleNavigation();

    function handleAuth(page, userDetails) {
        let users = JSON.parse(localStorage.getItem('users')) || [];
        let userExists = users.some(user => user.username === userDetails.username);
    
        if (page === 'login') {
            let validLogin = users.some(user => user.username === userDetails.username && user.password === userDetails.password);
            if (validLogin) {
                localStorage.setItem('currentUser', JSON.stringify({username: userDetails.username, email: userDetails.email}));
                alert('Login successful');
                navigateTo('profile'); // Navigate to profile page after login
            } else {
                alert('Invalid credentials');
            }
        } else if (page === 'registration') {
            if (!userExists) {
                users.push(userDetails);
                localStorage.setItem('users', JSON.stringify(users));
                alert('Registration successful. Please log in.');
                navigateTo('login');
            } else {
                alert('User already exists.');
            }
        }
    }


    function ensureAdminUser() {
        let users = JSON.parse(localStorage.getItem('users')) || [];
        let adminExists = users.some(user => user.username === 'admin');
        if (!adminExists) {
            users.push({username: 'admin', password: 'admin', email: 'admin@admin.org'});
            localStorage.setItem('users', JSON.stringify(users));
        }
    }

    function attachFormListener(page) {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                const userDetails = {
                    username: formData.get('username'),
                    password: formData.get('password'),
                    email: page === 'registration' ? formData.get('email') : null 
                };
                handleAuth(page, userDetails);
            });
        }
    }

    function attachLogoutListener() {
        const logoutButton = document.getElementById('logoutButton');
        if (logoutButton) {
            logoutButton.addEventListener('click', function() {
                localStorage.removeItem('currentUser'); // Clear current user session
                navigateTo('login'); // Redirect to login page
            });
        }
    }
    
};
