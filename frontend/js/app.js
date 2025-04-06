import { initializeRouter } from './router.js';
import { initializeBlog } from './blog.js';
import { initializeAuth } from './auth.js';
import { initializeUI } from './ui.js';

window.onload = function() {
    initializeRouter();
    initializeBlog();
    initializeAuth();
    initializeUI();
}; 