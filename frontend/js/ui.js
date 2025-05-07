import { blogPosts, getBlogPostById, getBlogPostsByCategory, getBlogPostsByAuthor } from './blog.js';
import { navigateTo } from './router.js';

export function initializeUI() {
    attachCreateBlogListener();
}

export function loadPage(page) {
    const container = document.getElementById("container");
    const basePath = window.location.href.includes('localhost') ? '/LifeLogs2025/' : '/';
    const url = `${basePath}frontend/views/${page}.html`;

    if (page.startsWith('blog')) {
        const blogId = new URLSearchParams(window.location.hash.split('?')[1]).get('id');
        renderBlogPage(blogId);
        return;
    }

    if (page === 'featured') {
        renderBlogSections();
        return;
    }

    if (page === 'all-blogs') {
        fetch(`${basePath}frontend/views/all-blogs.html`)
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
            return fetch(`${basePath}frontend/views/404.html`).then(response => response.ok ? response.text() : 'Page not found');
        }
    }).then(html => {
        container.innerHTML = html;
        document.title = page.charAt(0).toUpperCase() + page.slice(1) + " | LifeLogs2025";
        if (page === 'profile') {
            setTimeout(renderProfilePage, 0);
        }
    }).catch(error => {
        console.error('Failed to load page', error);
        container.innerHTML = 'Error loading page.';
    });
}

function attachCreateBlogListener() {
    const form = document.getElementById("createBlogForm");
    if (!form) return;

    form.addEventListener("submit", function(e) {
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
            avatar: "frontend/static-assets/images/avatar.jpg",
            date: new Date().toISOString().split('T')[0],
            image: "frontend/static-assets/images/default.jpg",
            caption: "New blog post",
            content: description,
            summary: description.length > 120 ? description.slice(0, 120) + "..." : description,
            category,
            tag: "general",
            likes: 0,
            dislikes: 0
        };

        blogPosts.unshift(newPost);
        alert("Blog published!");
        navigateTo("profile");
    });
}

export async function renderBlogPage(id) {
    const blogPost = await getBlogPostById(id);
    const container = document.getElementById("container");
    
    if (!blogPost) {
        container.innerHTML = "<p>Blog post not found.</p>";
        document.title = "Blog Not Found | LifeLogs2025";
        return;
    }

    container.innerHTML = `
    <div class="container mt-5 mb-5">
        <div class="row">
            <!-- Blog Content -->
            <div class="col-lg-8">
                <article class="blog-post">
                    <header class="blog-post-header">
                        <h1 class="title">${blogPost.title}</h1>
                        <div class="meta">
                            <a href="#"><img src="${blogPost.avatar}" alt="Author's avatar" class="author-avatar"></a>
                            <p>
                                Posted by <a href="#">${blogPost.author}</a>
                                on <time datetime="${blogPost.date}">${blogPost.date}</time>
                            </p>
                        </div>
                    </header>
                    <figure class="blog-post-image">
                        <img src="${blogPost.image}" alt="Blog image" class="img-fluid">
                        <figcaption>${blogPost.caption}</figcaption>
                    </figure>
                    <section class="blog-post-content">
                        <p>${blogPost.content}</p>
                        <hr>
                        <p><strong>Tag:</strong> <span class="badge ${getTagBadgeColor(blogPost.tag)}">#${blogPost.tag}</span></p>
                        <p>
                            <button class="btn btn-sm btn-light like-btn" data-id="${blogPost.id}">
                                <img src="frontend/static-assets/images/icons/like.svg" alt="Like" style="width: 18px;"> ${blogPost.likes}
                            </button>
                            <button class="btn btn-sm btn-light dislike-btn" data-id="${blogPost.id}">
                                <img src="frontend/static-assets/images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${blogPost.dislikes}
                            </button>
                            <button class="btn btn-sm btn-light disabled ml-2">
                                <img src="frontend/static-assets/images/icons/comment.svg" alt="Comment" style="width: 18px;"> 3
                            </button>
                        </p>
                    </section>
                </article>
            </div>

            <!-- Comments Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm p-3 mb-4">
                    <h5 class="mb-3">Comments</h5>
                    <div class="media mb-3">
                        <img src="frontend/static-assets/images/avatar.jpg" alt="User" class="mr-3 rounded-circle" width="40">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Alice</h6>
                            Really inspiring story, thanks for sharing!
                        </div>
                    </div>
                    <div class="media mb-3">
                        <img src="frontend/static-assets/images/avatar.jpg" alt="User" class="mr-3 rounded-circle" width="40">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Bob</h6>
                            This gave me some motivation to start again.
                        </div>
                    </div>
                    <div class="media mb-3">
                        <img src="frontend/static-assets/images/avatar.jpg" alt="User" class="mr-3 rounded-circle" width="40">
                        <div class="media-body">
                            <h6 class="mt-0 mb-1">Charlie</h6>
                            Would love to hear more about your process.
                        </div>
                    </div>
                </div>

                <!-- Leave a Comment Form -->
                <div class="card shadow-sm p-3">
                    <h5 class="mb-3">Leave a Comment</h5>
                    <form>
                        <div class="form-group">
                            <label for="commentText">Comment</label>
                            <textarea class="form-control" id="commentText" rows="3" placeholder="Your comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm" disabled>Submit (Coming Soon)</button>
                    </form>
                </div>
            </div>
        </div>
    </div>`;

    document.title = `${blogPost.title} | LifeLogs2025`;
}

export function renderBlogSections() {
    const container = document.getElementById("container");
    const featured = getBlogPostsByCategory("featured");
    const latest = getBlogPostsByCategory("latest");
    console.log('🖌 renderBlogSections()', featured.length, latest.length);


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
                                    <img src="frontend/static-assets/images/icons/like.svg" alt="Like" style="width: 18px;"> ${post.likes}
                                </button>
                                <button class="btn btn-light btn-sm dislike-btn" data-id="${post.id}">
                                    <img src="frontend/static-assets/images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${post.dislikes}
                                </button>
                                <button class="btn btn-sm btn-light disabled ml-2">
                                    <img src="frontend/static-assets/images/icons/comment.svg" alt="Comment" style="width: 18px;"> 3
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

export function renderAllBlogsPage() {
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
                                <img src="frontend/static-assets/images/icons/like.svg" alt="Like" style="width: 18px;"> ${post.likes}
                            </button>
                            <button class="btn btn-light btn-sm dislike-btn" data-id="${post.id}">
                                <img src="frontend/static-assets/images/icons/dislike.svg" alt="Dislike" style="width: 18px;"> ${post.dislikes}
                            </button>
                        </div>
                        <a href="#/blog?id=${post.id}" class="btn btn-success btn-sm">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    `).join("");
}

function renderProfilePage() {
    // TEMP: to be replaced with backend data
    const username = "John Doe";
    const email = "john@example.com";
    const bio = "I write to remember, I share to inspire.";
    const avatar = "frontend/static-assets/images/avatar.jpg"; 

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
    const userBlogs = getBlogPostsByAuthor(username);
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