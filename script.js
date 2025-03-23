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
        category: "featured",
        tag: "lifestyle",
        likes: 12,
        dislikes: 2
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
        category: "featured",
        tag: "home",
        likes: 12,
        dislikes: 2
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
        category: "featured",
        tag: "funny",
        likes: 12,
        dislikes: 2
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
        category: "latest",
        tag: "gaming",
        likes: 12,
        dislikes: 2
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
        category: "latest",
        tag: "cooking",
        likes: 12,
        dislikes: 2
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
        category: "latest",
        tag: "gaming",
        likes: 12,
        dislikes: 2
    }
];

window.onload = function () {
    function navigateTo(page) {
        window.location.hash = `#/${page}`;
    }

    function handleNavigation() {
        const pageName = window.location.hash.replace('#/', '') || 'featured';
        loadPage(pageName);
    }

    function loadPage(page) {
        const container = document.getElementById("container");
        const basePath = window.location.href.includes('localhost') ? '/LifeLogs2025/' : '/';
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

        if (page === 'create-blog') {
            attachCreateBlogListener();
        }

        if (page === 'all-blogs') {
            fetch('all-blogs.html')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('container').innerHTML = html;
                    renderAllBlogsPage();
                    document.title = "All Blogs | LifeLogs2025";
                });
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
            document.title = page.charAt(0).toUpperCase() + page.slice(1) + " | LifeLogs2025";
            if (page === 'login' || page === 'registration') {
                attachFormListener(page);
            }
            if (page === 'profile') {
                setTimeout(renderProfilePage, 0);
            }
            attachLogoutListener();
            attachCreateBlogListener();
        }).catch(error => {
            console.error('Failed to load page', error);
            container.innerHTML = 'Error loading page.';
        });
    }

    document.querySelectorAll("a.nav-link, a[href^='#']").forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const pageName = this.getAttribute("href").replace('#/', '');
            navigateTo(pageName);
        });
    });

    window.addEventListener('hashchange', handleNavigation);
    handleNavigation();

    document.addEventListener("click", function (e) {
        if (e.target.closest(".like-btn")) {
            const id = e.target.closest(".like-btn").dataset.id;
            console.log(`Like clicked on blog ${id}`);
        }
    
        if (e.target.closest(".dislike-btn")) {
            const id = e.target.closest(".dislike-btn").dataset.id;
            console.log(`Dislike clicked on blog ${id}`);
        }
    });

    function attachFormListener(page) {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                // Later we'll send this to backend with fetch
                alert(`${page === 'login' ? 'Login' : 'Registration'} successful`);
                navigateTo('profile');
            });
        }
    }

    function attachCreateBlogListener() {
        const form = document.getElementById("createBlogForm");
        if (!form) return;
    
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const title = form.blogTitle.value.trim();
            const description = form.blogDescription.value.trim();
            const category = form.blogCategory.value;
    
            if (!title || !description || !category) {
                alert("Please fill in all fields.");
                return;
            }
    
            const newPost = {
                id: blogPosts.length + 1,
                title,
                author: "John Doe",
                avatar: "images/avatar.jpg",
                date: new Date().toISOString().split('T')[0],
                image: "images/default.jpg",
                caption: "New blog post",
                content: description,
                summary: description.length > 120 ? description.slice(0, 120) + "..." : description,
                category, // featured/latest
                tag,
                likes: 0,
                dislikes: 0
            };
    
            blogPosts.unshift(newPost);
            alert("Blog published!");
            window.location.hash = "#/profile";
        });
    }

    function attachLogoutListener() {
        const logoutButton = document.getElementById('logoutButton');
        if (logoutButton) {
            logoutButton.addEventListener('click', function () {
                alert('Logged out');
                navigateTo('login');
            });
        }
    }
};

function renderBlogPage(blogId) {
    const post = blogPosts.find(p => p.id === Number(blogId));
    const container = document.getElementById("container");

    if (!post) {
        container.innerHTML = "<p>Blog post not found.</p>";
        document.title = "Blog Not Found | LifeLogs2025";
        return;
    }

    container.innerHTML = `
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-lg-8">
                <article class="blog-post">
                    <header class="blog-post-header">
                        <h1 class="title">${post.title}</h1>
                        <div class="meta d-flex align-items-center">
                            <img src="${post.avatar}" alt="Author's avatar" class="author-avatar mr-2">
                            <div>
                                <p class="mb-0">
                                    Posted by <a href="#">${post.author}</a>
                                </p>
                                <small><time datetime="${post.date}">${post.date}</time></small>
                            </div>
                        </div>
                    </header>

                    <figure class="blog-post-image mt-4">
                        <img src="${post.image}" alt="Blog image" class="img-fluid w-100">
                        <figcaption class="mt-2">${post.caption}</figcaption>
                    </figure>

                    <section class="blog-post-content mt-4">
                        <p>${post.content}</p>
                        <hr>
                        <p>
                            <span class="badge ${getTagBadgeColor(post.tag)}">#${post.tag}</span>
                        </p>
                        <p>
                            <button class="btn btn-light btn-sm like-btn" data-id="${post.id}">
                                <img src="images/icons/like.svg" alt="Like" style="width: 18px;"> ${post.likes}
                            </button>
                            <button class="btn btn-light btn-sm dislike-btn" data-id="${post.id}">
                                <img src="images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${post.dislikes}
                            </button>
                        </p>
                    </section>
                </article>
            </div>
        </div>
    </div>
`;
document.title = `${post.title} | LifeLogs2025`;
}

function renderBlogSections() {
    const container = document.getElementById("container");

    const featured = blogPosts.filter(post => post.category === "featured");
    const latest = blogPosts.filter(post => post.category === "latest");

    const renderCards = posts => posts.map(post => {
        const maxLength = 120;
        const summary = post.summary.length > maxLength
            ? post.summary.slice(0, maxLength).trim() + "..."
            : post.summary;
    
        return `
            <div class="col-md-4 d-flex">
                <div class="card blog-card h-100 flex-fill">
                    <img class="card-img-top" src="${post.image}" alt="Story Image">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${post.title}</h5>
                        <span class="badge mb-2 ${getTagBadgeColor(post.tag)}">#${post.tag}</span>
                        <p class="card-text flex-grow-1">${summary}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button class="btn btn-light btn-sm like-btn" data-id="${post.id}">
                                    <img src="images/icons/like.svg" alt="Like" style="width: 18px;"> ${post.likes}
                                </button>
                                <button class="btn btn-light btn-sm dislike-btn" data-id="${post.id}">
                                    <img src="images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${post.dislikes}
                                </button>
                            </div>
                            <a href="#/blog?id=${post.id}" class="btn btn-success btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

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

    document.title = "Featured | LifeLogs2025";
}

function renderProfilePage() {
    // TEMP: to be replaced with backend data
    const username = "John Doe";
    const email = "john@example.com";
    const bio = "I write to remember, I share to inspire.";
    const avatar = "images/avatar.jpg"; 

    const u = document.getElementById('profileUsername');
    const e = document.getElementById('profileEmail');
    const b = document.getElementById('profileBio');
    const img = document.getElementById('profilePicture');

    if (u && e && b && img) {
        u.innerText = username;
        e.innerText = email;
        b.innerText = bio;
        img.src = avatar;
        img.alt = `${username}'s profile picture`;
    }

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const bioInput = document.getElementById('bio');

    if (usernameInput && emailInput && bioInput) {
        usernameInput.value = username;
        emailInput.value = email;
        bioInput.value = bio;
    }

    // Show user's blogs
    const userBlogs = blogPosts.filter(b => b.author === username);
    const blogList = document.getElementById('userBlogs');

    if (blogList) {
        blogList.innerHTML = userBlogs.length
            ? userBlogs.map(post => `
                <a href="#/blog?id=${post.id}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">${post.title}</h5>
                        <small class="text-muted">${post.date}</small>
                    </div>
                    <p class="mb-1">${post.summary}</p>
                    <small class="text-muted d-block">
                        <a href="#/user?id=${encodeURIComponent(post.author)}" class="text-muted">by ${post.author}</a>
                    </small>
                </a>
            `).join('')
            : `<p class="text-muted">You haven't written any blogs yet.</p>`;
    }
}


function getTagBadgeColor(tag) {
    switch (tag.toLowerCase()) {
        case "tech": return "badge-primary";
        case "lifestyle": return "badge-success";
        case "funny": return "badge-warning text-dark";
        case "home": return "badge-info";
        case "gaming": return "badge-danger";
        case "cooking": return "badge-dark";
        case "inspiration": return "badge-light text-dark";
        default: return "badge-secondary";
    }
}

function renderAllBlogsPage() {
    const blogList = document.getElementById("allBlogList");
    if (!blogList) return;

    blogList.innerHTML = blogPosts.map(post => `
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <img class="card-img-top" src="${post.image}" alt="${post.title}">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">${post.title}</h5>
                    <span class="badge ${getTagBadgeColor(post.tag)} mb-2">#${post.tag}</span>
                    <p class="card-text text-truncate-3">${post.summary}</p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-light btn-sm like-btn" data-id="${post.id}">
                                <img src="images/icons/like.svg" alt="Like" style="width: 18px;"> ${post.likes}
                            </button>
                            <button class="btn btn-light btn-sm dislike-btn" data-id="${post.id}">
                                <img src="images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${post.dislikes}
                            </button>
                        </div>
                        <a href="#/blog?id=${post.id}" class="btn btn-success btn-sm">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    `).join("");
}