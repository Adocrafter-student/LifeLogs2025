<?php
require_once __DIR__ . '/BaseDao.php';

class TagDao extends BaseDao {
    
    public function __construct() {
        parent::__construct("tags");
    }

    /**
     * Get or create a tag by name
     */
    public function getOrCreate($name) {
        $tag = $this->getTagByName($name);
        if (!$tag) {
            $tag = $this->add(['name' => $name]);
        }
        return $tag;
    }

    /**
     * Get tag by name
     */
    public function getTagByName($name) {
        $stmt = $this->connection->prepare("SELECT * FROM tags WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get tags for a blog
     */
    public function getTagsForBlog($blogId) {
        $stmt = $this->connection->prepare("
            SELECT t.* FROM tags t 
            JOIN blog_tags bt ON t.id = bt.tag_id 
            WHERE bt.blog_id = :blog_id
        ");
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add tags to a blog
     */
    public function addTagsToBlog($blogId, $tagIds) {
        $values = [];
        $params = [];
        foreach ($tagIds as $index => $tagId) {
            $values[] = "(:blog_id, :tag_id_$index)";
            $params[":tag_id_$index"] = $tagId;
        }
        
        $sql = "INSERT INTO blog_tags (blog_id, tag_id) VALUES " . implode(',', $values);
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':blog_id', $blogId);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Remove tags from a blog
     */
    public function removeTagsFromBlog($blogId) {
        $stmt = $this->connection->prepare("DELETE FROM blog_tags WHERE blog_id = :blog_id");
        $stmt->bindParam(':blog_id', $blogId);
        return $stmt->execute();
    }

    /**
     * Remove a specific tag from a blog
     */
    public function removeTagFromBlog($blogId, $tagId) {
        $stmt = $this->connection->prepare("DELETE FROM blog_tags WHERE blog_id = :blog_id AND tag_id = :tag_id");
        $stmt->bindParam(':blog_id', $blogId);
        $stmt->bindParam(':tag_id', $tagId);
        return $stmt->execute();
    }

    /**
     * Get popular tags
     */
    public function getPopularTags($limit = 10) {
        $stmt = $this->connection->prepare("
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