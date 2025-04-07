<?php
require_once __DIR__ . '/BaseDao.php';

class CommentDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("comments");
    }

    /**
     * Get comments for a blog post with user information
     */
    public function getCommentsForBlog($blogId) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.username, u.avatar_url 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.blog_id = :blog_id 
            ORDER BY c.created_at DESC
        ");
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new comment
     */
    public function addComment($blogId, $userId, $content) {
        return $this->add([
            'blog_id' => $blogId,
            'user_id' => $userId,
            'content' => $content
        ]);
    }

    /**
     * Get comments by user
     */
    public function getCommentsByUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT c.*, b.title as blog_title 
            FROM comments c 
            JOIN blogs b ON c.blog_id = b.id 
            WHERE c.user_id = :user_id 
            ORDER BY c.created_at DESC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete all comments for a blog post
     */
    public function deleteCommentsForBlog($blogId) {
        $stmt = $this->conn->prepare("DELETE FROM comments WHERE blog_id = :blog_id");
        $stmt->bindParam(':blog_id', $blogId);
        return $stmt->execute();
    }
}
?> 