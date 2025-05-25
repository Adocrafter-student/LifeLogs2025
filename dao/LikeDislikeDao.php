<?php
require_once __DIR__ . '/BaseDao.php';

class LikeDislikeDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("likes_dislikes");
    }

    /**
     * Add or update like/dislike
     */
    public function addOrUpdate($blogId, $userId, $isLike) {
        $stmt = $this->connection->prepare("
            INSERT INTO likes_dislikes (blog_id, user_id, is_like) 
            VALUES (:blog_id, :user_id, :is_like)
            ON DUPLICATE KEY UPDATE is_like = :is_like
        ");
        
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':is_like', $isLike);
        
        return $stmt->execute();
    }

    /**
     * Get user's reaction to a blog post
     */
    public function getUserReaction($blogId, $userId) {
        $stmt = $this->connection->prepare("
            SELECT is_like 
            FROM likes_dislikes 
            WHERE blog_id = :blog_id AND user_id = :user_id
        ");
        
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get like/dislike counts for a blog post
     */
    public function getReactionCounts($blogId) {
        $stmt = $this->connection->prepare("
            SELECT 
                SUM(CASE WHEN is_like = 1 THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN is_like = 0 THEN 1 ELSE 0 END) as dislikes
            FROM likes_dislikes 
            WHERE blog_id = :blog_id
        ");
        
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Remove user's reaction to a blog post
     */
    public function removeReaction($blogId, $userId) {
        $stmt = $this->connection->prepare("
            DELETE FROM likes_dislikes 
            WHERE blog_id = :blog_id AND user_id = :user_id
        ");
        
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Get all reactions for a blog post with user details
     */
    public function getReactionsByBlogId($blogId) {
        $stmt = $this->connection->prepare("
            SELECT ld.*, u.username, u.avatar_url as user_avatar 
            FROM likes_dislikes ld 
            JOIN users u ON ld.user_id = u.id 
            WHERE ld.blog_id = :blog_id
        ");
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 