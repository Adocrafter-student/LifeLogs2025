<?php
class BlogService {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getFeaturedBlogs() {
        $sql = "SELECT b.*, u.username, u.avatar_url as author_avatar 
                FROM blogs b 
                JOIN users u ON b.user_id = u.id 
                WHERE b.category = 'featured' 
                ORDER BY b.created_at DESC";
        
        $result = $this->conn->query($sql);
        $blogs = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $blogs[] = $row;
            }
        }
        
        return $blogs;
    }

    public function getLatestBlogs() {
        $sql = "SELECT b.*, u.username, u.avatar_url as author_avatar 
                FROM blogs b 
                JOIN users u ON b.user_id = u.id 
                WHERE b.category = 'latest' 
                ORDER BY b.created_at DESC";
        
        $result = $this->conn->query($sql);
        $blogs = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $blogs[] = $row;
            }
        }
        
        return $blogs;
    }
}
?> 