import { renderBlogPage, renderBlogSections, renderAllBlogsPage } from './ui.js';
import { getToken } from './auth.js';

export let blogPosts = [];

function formatImagePath(path) {
    console.log("Formatiranje putanje:", path);
    if (typeof path !== 'string') {
        return path;
    }
    path = path.replace(/\\/g, '/');
    return path;
}

export async function initializeBlog() {
    const token = getToken();
    console.log("[Blog.js initializeBlog] Token dobijen iz getToken():", token ? token.substring(0, 20) + '...' : 'NULL ili PRAZAN');

    const fetchHeaders = {};
    if (token) {
        fetchHeaders['Authorization'] = `Bearer ${token}`;
    }
    console.log("[Blog.js initializeBlog] Headers koji se šalju:", JSON.stringify(fetchHeaders));

    try {
        const baseUrl = '/LifeLogs2025/api/blogs';

        const [featuredResponse, latestResponse] = await Promise.all([
            fetch(`${baseUrl}?action=featured`, { headers: fetchHeaders }),
            fetch(`${baseUrl}?action=latest`, { headers: fetchHeaders })
        ]);

        const featuredBlogsData = await featuredResponse.json();
        const latestBlogsData = await latestResponse.json();

        console.log("Originalni featured blogovi sa servera:", featuredBlogsData);
        console.log("Originalni latest blogovi sa servera:", latestBlogsData);

        const featuredArray = Array.isArray(featuredBlogsData) ? featuredBlogsData : (featuredBlogsData && Array.isArray(featuredBlogsData.data) ? featuredBlogsData.data : []);
        const latestArray = Array.isArray(latestBlogsData) ? latestBlogsData : (latestBlogsData && Array.isArray(latestBlogsData.data) ? latestBlogsData.data : []);

        console.log("Featured array za mapiranje:", featuredArray);
        console.log("Latest array za mapiranje:", latestArray);

        blogPosts = [...featuredArray, ...latestArray].map(post => {
            if (!post) return null;
            const imageUrl = post.image_url || post.image;
            const authorAvatar = post.author_avatar || post.avatar;
            const authorName = post.username || post.author;
            const postDate = post.created_at ? String(post.created_at).split(' ')[0] : new Date().toISOString().split('T')[0];

            const formattedPost = {
                ...post,
                image_url: formatImagePath(imageUrl),
                author_avatar: formatImagePath(authorAvatar),
                image: formatImagePath(imageUrl),
                avatar: formatImagePath(authorAvatar),
                author: authorName,
                date: postDate
            };
            return formattedPost;
        }).filter(post => post !== null);
        
        console.log("Mapirani blog postovi za renderovanje:", blogPosts);
        
        renderBlogSections(featuredArray, latestArray);
    } catch (error) {
        console.error('[Blog.js initializeBlog] Error fetching blogs:', error);
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
    const token = getToken();
    console.log(`[Blog.js getBlogPostById(${id})] Token dobijen iz getToken():`, token ? token.substring(0, 20) + '...' : 'NULL ili PRAZAN');
    
    const fetchHeaders = {};
    if (token) {
        fetchHeaders['Authorization'] = `Bearer ${token}`;
    }
    console.log(`[Blog.js getBlogPostById(${id})] Headers koji se šalju:`, JSON.stringify(fetchHeaders));

    try {
        const response = await fetch(`/LifeLogs2025/api/blogs/${id}`, { headers: fetchHeaders });
        if (!response.ok) {
            console.error('API responded with status', response.status);
            try {
                const errorData = await response.json();
                console.error('Error data from API:', errorData);
                alert(`Error fetching blog: ${errorData.message || response.statusText}`);
            } catch (e) {
                alert(`Error fetching blog: ${response.statusText}`);
            }
            return null;
        }
        const blogDataFromServer = await response.json();
        
        const blogData = blogDataFromServer.data || blogDataFromServer;
        
        if (!blogData) {
            console.error(`[Blog.js getBlogPostById(${id})] No blog data found after parsing JSON.`);
            return null;
        }

        const imageUrl = blogData.image_url || blogData.image;
        const authorAvatar = blogData.author_avatar || blogData.avatar;
        const authorName = blogData.username || blogData.author;
        const postDate = blogData.created_at ? String(blogData.created_at).split(' ')[0] : new Date().toISOString().split('T')[0];

        return {
            ...blogData,
            image_url: formatImagePath(imageUrl),
            author_avatar: formatImagePath(authorAvatar),
            image: formatImagePath(imageUrl),
            avatar: formatImagePath(authorAvatar),
            author: authorName,
            date: postDate
        };
    } catch (error) {
        console.error(`[Blog.js getBlogPostById(${id})] Error fetching blog post:`, error);
        alert('An error occurred while fetching the blog post.');
        return null;
    }
}

export function getBlogPostsByCategory(category) {
    return blogPosts.filter(post => post.category === category);
}

export function getBlogPostsByAuthor(author) {
    return blogPosts.filter(post => post.username === author);
} 