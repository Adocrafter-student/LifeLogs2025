<?php

class SearchDao extends BaseDao {
    public function __construct() {
        parent::__construct('search');
    }

    /**
     * Search across blogs, tags and users
     */
    public function search($query, $type = 'all', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        switch($type) {
            case 'blogs':
                return $this->searchBlogs($query, $offset, $limit);
            case 'tags':
                return $this->searchTags($query, $offset, $limit);
            case 'users':
                return $this->searchUsers($query, $offset, $limit);
            default:
                return $this->searchAll($query, $offset, $limit);
        }
    }

    /**
     * Search in blogs by title
     */
    private function searchBlogs($query, $offset, $limit) {
        $stmt = $this->connection->prepare("
            SELECT b.*, u.username as author_username 
            FROM blogs b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.title LIKE :query 
            ORDER BY b.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search in tags
     */
    private function searchTags($query, $offset, $limit) {
        $stmt = $this->connection->prepare("
            SELECT * FROM tags 
            WHERE name LIKE :query 
            ORDER BY name 
            LIMIT :limit OFFSET :offset
        ");
        
        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search in users by username
     */
    private function searchUsers($query, $offset, $limit) {
        $stmt = $this->connection->prepare("
            SELECT * FROM users 
            WHERE username LIKE :query 
            ORDER BY username 
            LIMIT :limit OFFSET :offset
        ");
        
        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search across all content types
     */
    private function searchAll($query, $offset, $limit) {
        $results = [
            'blogs' => $this->searchBlogs($query, $offset, $limit),
            'tags' => $this->searchTags($query, $offset, $limit),
            'users' => $this->searchUsers($query, $offset, $limit)
        ];
        
        return $results;
    }
} 