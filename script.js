const blogPosts = [
    {
        id: 1,
        title: "How running 10 miles a day changed my life forever",
        author: "John Doe",
        avatar: "images/avatar.jpg",
        date: "2024-01-01",
        image: "images/running-guy.jpg",
        caption: "Running can change your life.",
        content: "When addiction got the best of me, I chose a lifestyle that transformed everything...",
        summary: "When addiction got a better of me, I have chosen a lifestyle which changed me forever.",
        category: "featured"
    },
    {
        id: 2,
        title: "Quick peek inside of my little garden",
        author: "Jennifer Farrah",
        avatar: "images/avatar.jpg",
        date: "2023-12-11",
        image: "images/gardening.jpg",
        caption: "My peaceful green retreat.",
        content: "Who doesn't like relaxing surrounded by the greenery of self-grown vegetables...",
        summary: "Who does not like to relax with the scenery of fresh vegetables you cared for.",
        category: "featured"
    },
    {
        id: 3,
        title: "How I beat David Goggins by eating cereal",
        author: "Anonymous",
        avatar: "images/avatar.jpg",
        date: "2024-02-01",
        image: "images/david-goggings.jpg",
        caption: "Yes, cereal really helped.",
        content: "Nobody believed it until I showed them the truth about cereal power...",
        summary: "Nobody believed me until I showed the results of eating better.",
        category: "featured"
    },
    {
        id: 4,
        title: "How I won by playing the objective",
        author: "GamerX",
        avatar: "images/avatar.jpg",
        date: "2024-02-05",
        image: "images/objective.jpg",
        caption: "Focus wins games.",
        content: "Step 1, keep your mind clear and focus...",
        summary: "Step 1, keep your mind clear and focus.",
        category: "latest"
    },
    {
        id: 5,
        title: "This cooking recipe changed my life",
        author: "Chef Gordon",
        avatar: "images/avatar.jpg",
        date: "2024-02-08",
        image: "images/gordon.jpg",
        caption: "The sauce makes the dish.",
        content: "With a small amount of this secret sauce, anything is possible...",
        summary: "With a small amount of this secret sauce, anything is possible.",
        category: "latest"
    },
    {
        id: 6,
        title: "How this Yu-Gi-Oh deck boosted my wins",
        author: "Yami",
        avatar: "images/avatar.jpg",
        date: "2024-02-10",
        image: "images/yugio.jpg",
        caption: "Believe in the heart of the cards.",
        content: "My win percentage doubled after using these cards...",
        summary: "My win percentage doubled after getting those cards.",
        category: "latest"
    }
];

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

        if (page.startsWith('blog')) {
            const blogId = new URLSearchParams(window.location.hash.split('?')[1]).get('id');
            renderBlogPage(blogId);
            return;
        }

        if (page === 'featured') {
            renderBlogSections();
            return;
        }

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

function renderBlogPage(blogId) {
    const post = blogPosts.find(p => p.id === Number(blogId));
    const container = document.getElementById("container");

    if (!post) {
        container.innerHTML = "<p>Blog post not found.</p>";
        document.title = "Blog Not Found | LifeLogs";
        return;
    }

    container.innerHTML = `
        <div class="container mt-5 mb-5">
            <div class="row">
                <div class="col-lg-8">
                    <article class="blog-post">
                        <header class="blog-post-header">
                            <h1 class="title">${post.title}</h1>
                            <div class="meta">
                                <a href="#"><img src="${post.avatar}" alt="Author's avatar" class="author-avatar"></a>
                                <p>Posted by <a href="#">${post.author}</a> on <time datetime="${post.date}">${post.date}</time></p>
                            </div>
                        </header>
                        <figure class="blog-post-image">
                            <img src="${post.image}" alt="Blog image" class="img-fluid">
                            <figcaption>${post.caption}</figcaption>
                        </figure>
                        <section class="blog-post-content">
                            <p>${post.content}</p>
                        </section>
                    </article>
                </div>
            </div>
        </div>`;
    document.title = `${post.title} | LifeLogs`;
}


function renderBlogSections() {
    const container = document.getElementById("container");

    const featured = blogPosts.filter(post => post.category === "featured");
    const latest = blogPosts.filter(post => post.category === "latest");

    const renderCards = posts => posts.map(post => `
        <div class="col-md-4 mb-3">
            <div class="card">
                <img class="card-img-top" src="${post.image}" alt="Story Image">
                <div class="card-body">
                    <h5 class="card-title">${post.title}</h5>
                    <p class="card-text">${post.summary}</p>
                    <a href="#/blog?id=${post.id}" class="btn btn-success">Read More</a>
                </div>
            </div>
        </div>
    `).join('');

    container.innerHTML = `
        <section class="featured-stories">
            <div class="container py-5">
                <h2 class="text-center mb-4">Featured Stories</h2>
                <div class="row">
                    ${renderCards(featured)}
                </div>
            </div>
        </section>

        <section class="latest-entries bg-light">
            <div class="container py-5">
                <h2 class="text-center mb-4">Latest Entries</h2>
                <div class="row">
                    ${renderCards(latest)}
                </div>
            </div>
        </section>
    `;

    document.title = "Featured | LifeLogs";
}