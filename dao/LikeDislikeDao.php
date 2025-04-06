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
        $stmt = $this->conn->prepare("
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
        $stmt = $this->conn->prepare("
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
        $stmt = $this->conn->prepare("
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
        $stmt = $this->conn->prepare("
            DELETE FROM likes_dislikes 
            WHERE blog_id = :blog_id AND user_id = :user_id
        ");
        
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }
}
?> 