import { renderBlogPage, renderBlogSections, renderAllBlogsPage } from './ui.js';

export const blogPosts = [
    {
        id: 1,
        title: "How running 10 miles a day changed my life forever",
        author: "John Doe",
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2024-01-01",
        image: "frontend/static-assets/images/running-guy.jpg",
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
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2023-12-11",
        image: "frontend/static-assets/images/gardening.jpg",
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
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2024-02-01",
        image: "frontend/static-assets/images/david-goggings.jpg",
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
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2024-02-05",
        image: "frontend/static-assets/images/objective.jpg",
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
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2024-02-08",
        image: "frontend/static-assets/images/gordon.jpg",
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
        avatar: "frontend/static-assets/images/avatar.jpg",
        date: "2024-02-10",
        image: "frontend/static-assets/images/yugio.jpg",
        caption: "Believe in the heart of the cards.",
        content: "My win percentage doubled after using these cards...",
        summary: "My win percentage doubled after getting those cards.",
        category: "latest",
        tag: "gaming",
        likes: 12,
        dislikes: 2
    }
];

export function initializeBlog() {
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

export function getBlogPostById(id) {
    return blogPosts.find(post => post.id === Number(id));
}

export function getBlogPostsByCategory(category) {
    return blogPosts.filter(post => post.category === category);
}

export function getBlogPostsByAuthor(author) {
    return blogPosts.filter(post => post.author === author);
} 