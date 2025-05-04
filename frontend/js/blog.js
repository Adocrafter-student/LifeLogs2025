import { renderBlogPage, renderBlogSections, renderAllBlogsPage } from './ui.js';

export let blogPosts = [];

function formatImagePath(path) {
    console.log("Formatiranje putanje:", path);
    path = path.replace(/\\/g, '/');
    return path;
}

export async function initializeBlog() {
    try {
        const [featuredResponse, latestResponse] = await Promise.all([
            fetch('api/blogs?action=featured'),
            fetch('api/blogs?action=latest')
        ]);

        const featuredBlogs = await featuredResponse.json();
        const latestBlogs = await latestResponse.json();

        

        console.log("Originalni blog postovi:", [...featuredBlogs, ...latestBlogs]);

        // Formatiraj putanje slika
        blogPosts = [...featuredBlogs, ...latestBlogs].map(post => {
            const formattedPost = {
                ...post,
                image_url: formatImagePath(post.image_url),
                author_avatar: formatImagePath(post.author_avatar),
                image: formatImagePath(post.image_url),
                avatar: formatImagePath(post.author_avatar),
                author: post.username,
                date: post.created_at.split(' ')[0]
            };
            console.log("Formatirani post:", formattedPost);
            return formattedPost;
        });
        
        renderBlogSections(featuredBlogs, latestBlogs);
    } catch (error) {
        console.error('Error fetching blogs:', error);
    }

    document.addEventListener("click", function(e) {
        if (e.target.closest(".like-btn")) {
            const id = e.target.closest(".like-btn").dataset.id;
            console.log(`Like clicked on blog ${id}`);
        }
    
        if (e.target.closest(".dislike-btn")) {
            const id = e.target.closest(".dislike-btn").dataset.id;
            console.log(`Dislike clicked on blog ${id}`);
        }
    });
}

export async function getBlogPostById(id) {
    try {
        const response = await fetch(`/LifeLogs2025/api/blogs/${id}`);
        if (!response.ok) {
            console.error('API responded with status', response.status);
            return null;
        }
        const blog = await response.json();
        
        return {
            ...blog,
            image_url: formatImagePath(blog.image_url),
            author_avatar: formatImagePath(blog.author_avatar),
            image: formatImagePath(blog.image_url),
            avatar: formatImagePath(blog.author_avatar),
            author: blog.username,
            date: blog.created_at.split(' ')[0]
        };
    } catch (error) {
        console.error('Error fetching blog post:', error);
        return null;
    }
}

export function getBlogPostsByCategory(category) {
    return blogPosts.filter(post => post.category === category);
}

export function getBlogPostsByAuthor(author) {
    return blogPosts.filter(post => post.username === author);
} 