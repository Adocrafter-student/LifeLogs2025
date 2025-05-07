<?php
require_once __DIR__ . '/../dao/CommentDao.php';

class CommentService {
    private $commentDao;

    public function __construct() {
        $this->commentDao = new CommentDao();
    }

    public function createComment($blog_id, $user_id, $content) {
        // Validacija
        if (empty($content)) {
            throw new Exception("Comment cannot be empty");
        }

        if (strlen($content) > 1000) {
            throw new Exception("Comment cannot be longer than 1000 characters");
        }

        return $this->commentDao->addComment($blog_id, $user_id, $content);
    }

    public function getCommentById($id) {
        return $this->commentDao->getById($id);
    }

    public function getCommentsByBlogId($blog_id) {
        return $this->commentDao->getCommentsForBlog($blog_id);
    }

    public function updateComment($id, $content) {
        // Validacija
        if (empty($content)) {
            throw new Exception("Comment cannot be empty");
        }

        if (strlen($content) > 1000) {
            throw new Exception("Comment cannot be longer than 1000 characters");
        }

        return $this->commentDao->update($id, ['content' => $content]);
    }

    public function deleteComment($id) {
        return $this->commentDao->delete($id);
    }

    public function getCommentCountByBlogId($blog_id) {
        $comments = $this->commentDao->getCommentsForBlog($blog_id);
        return count($comments);
    }

    public function getCommentsByUserId($user_id) {
        return $this->commentDao->getCommentsByUser($user_id);
    }

    public function deleteCommentsForBlog($blog_id) {
        return $this->commentDao->deleteCommentsForBlog($blog_id);
    }
}
?> 