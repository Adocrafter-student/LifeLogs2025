<?php
require_once __DIR__ . '/BaseDao.php';

class TagDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("tags");
    }

    /**
     * Get or create tag by name
     */
    public function getOrCreate($name) {
        // First try to get existing tag
        $stmt = $this->conn->prepare("SELECT * FROM tags WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tag) {
            return $tag['id'];
        }
        
        // If not exists, create new
        return $this->add(['name' => $name]);
    }

    /**
     * Get tags for a blog post
     */
    public function getTagsForBlog($blogId) {
        $stmt = $this->conn->prepare("
            SELECT t.* 
            FROM tags t 
            JOIN blog_tags bt ON t.id = bt.tag_id 
            WHERE bt.blog_id = :blog_id
        ");
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add tags to a blog post
     */
    public function addTagsToBlog($blogId, $tagIds) {
        $values = array_map(function($tagId) use ($blogId) {
            return "($blogId, $tagId)";
        }, $tagIds);
        
        $stmt = $this->conn->prepare("
            INSERT INTO blog_tags (blog_id, tag_id) 
            VALUES " . implode(',', $values)
        );
        
        return $stmt->execute();
    }

    /**
     * Remove all tags from a blog post
     */
    public function removeTagsFromBlog($blogId) {
        $stmt = $this->conn->prepare("DELETE FROM blog_tags WHERE blog_id = :blog_id");
        $stmt->bindParam(':blog_id', $blogId);
        return $stmt->execute();
    }

    /**
     * Get popular tags with count
     */
    public function getPopularTags($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT t.*, COUNT(bt.blog_id) as usage_count 
            FROM tags t 
            LEFT JOIN blog_tags bt ON t.id = bt.tag_id 
            GROUP BY t.id 
            ORDER BY usage_count DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 