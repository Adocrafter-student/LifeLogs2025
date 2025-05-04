<?php
require_once __DIR__ . '/../dao/BlogDao.php';

class BlogService {
    private $blogDao;

    public function __construct() {
        $this->blogDao = new BlogDao();
    }

    public function createBlog($user_id, $title, $summary, $content, $image_url = null, $caption = null, $category = null, $tag = null) {
        // Validacija
        if (empty($title) || empty($summary) || empty($content)) {
            throw new Exception("Title, summary and content are required");
        }

        if (strlen($title) > 200) {
            throw new Exception("Title cannot be longer than 200 characters");
        }

        if (strlen($summary) > 500) {
            throw new Exception("Summary cannot be longer than 500 characters");
        }

        return $this->blogDao->add([
            'user_id' => $user_id,
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'image_url' => $image_url,
            'caption' => $caption,
            'category' => $category,
            'tag' => $tag
        ]);
    }

    public function getBlogById($id) {
        return $this->blogDao->getBlogWithUser($id);
    }

    public function getFeaturedBlogs() {
        return $this->blogDao->getFeaturedBlogs();
    }

    public function getLatestBlogs() {
        return $this->blogDao->getLatestBlogs();
    }

    public function getBlogsByUserId($user_id) {
        return $this->blogDao->getBlogsByUserId($user_id);
    }

    public function updateBlog($id, $title = null, $summary = null, $content = null, $image_url = null, $caption = null, $category = null, $tag = null) {
        $updates = [];
        
        if ($title !== null) {
            if (strlen($title) > 200) {
                throw new Exception("Naslov ne može biti duži od 200 karaktera");
            }
            $updates['title'] = $title;
        }
        
        if ($summary !== null) {
            if (strlen($summary) > 500) {
                throw new Exception("Sažetak ne može biti duži od 500 karaktera");
            }
            $updates['summary'] = $summary;
        }
        
        if ($content !== null) {
            $updates['content'] = $content;
        }
        
        if ($image_url !== null) {
            $updates['image_url'] = $image_url;
        }
        
        if ($caption !== null) {
            $updates['caption'] = $caption;
        }
        
        if ($category !== null) {
            $updates['category'] = $category;
        }
        
        if ($tag !== null) {
            $updates['tag'] = $tag;
        }

        return $this->blogDao->update($id, $updates);
    }

    public function deleteBlog($id) {
        return $this->blogDao->delete($id);
    }

    public function searchBlogs($query) {
        return $this->blogDao->searchBlogs($query);
    }

    public function getBlogsByCategory($category) {
        return $this->blogDao->getBlogsByCategory($category);
    }

    public function getBlogsByTag($tag) {
        return $this->blogDao->getBlogsByTag($tag);
    }
}
?> 