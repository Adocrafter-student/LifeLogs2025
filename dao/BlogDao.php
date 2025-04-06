<?php
require_once __DIR__ . '/BaseDao.php';

class BlogDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("blogs");
    }

    /**
     * Get featured blog posts with user information
     */
    public function getFeaturedBlogs() {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, u.avatar_url as author_avatar 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.category = 'featured' 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get latest blog posts with user information
     */
    public function getLatestBlogs() {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, u.avatar_url as author_avatar 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.category = 'latest' 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get blog posts by category
     */
    public function getBlogsByCategory($category) {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, u.avatar_url as author_avatar 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.category = :category 
            ORDER BY b.created_at DESC
        ");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get blog posts by user ID
     */
    public function getBlogsByUserId($userId) {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, u.avatar_url as author_avatar 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.user_id = :userId 
            ORDER BY b.created_at DESC
        ");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get blog post by ID with user information
     */
    public function getBlogWithUser($id) {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.username, u.avatar_url as author_avatar 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 