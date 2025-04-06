<?php
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../dao/BlogDao.php';
require_once __DIR__ . '/../dao/CommentDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/LikeDislikeDao.php';
require_once __DIR__ . '/../dao/TagDao.php';

class DaoTest {
    private $blogDao;
    private $commentDao;
    private $userDao;
    private $likeDislikeDao;
    private $tagDao;

    public function __construct() {
        $this->blogDao = new BlogDao();
        $this->commentDao = new CommentDao();
        $this->userDao = new UserDao();
        $this->likeDislikeDao = new LikeDislikeDao();
        $this->tagDao = new TagDao();
    }

    public function runAllTests() {
        echo "Running tests for DAO classes...\n\n";
        
        $this->testUserDao();
        $this->testBlogDao();
        $this->testCommentDao();
        $this->testLikeDislikeDao();
        $this->testTagDao();
    }

    private function testUserDao() {
        echo "Testing UserDao...\n";
        
        // Tes registration of user
        $userId = $this->userDao->register('testuser', 'test@example.com', 'password123', 'Test bio', 'avatar.jpg');
        echo "Registration of user: " . ($userId ? "Success" : "Failed") . "\n";
        
        // Test login of user
        $user = $this->userDao->login('test@example.com', 'password123');
        echo "Login of user: " . ($user ? "Success" : "Failed") . "\n";
        
        // Test updating profile
        $updateResult = $this->userDao->updateProfile($userId, 'New bio', 'new_avatar.jpg');
        echo "Updating profile: " . ($updateResult ? "Success" : "Failed") . "\n";
        
        echo "\n";
    }

    private function testBlogDao() {
        echo "Testiranje BlogDao...\n";
        
        // Test getting featured blogs
        $featuredBlogs = $this->blogDao->getFeaturedBlogs();
        echo "Getting featured blogs: " . (count($featuredBlogs) > 0 ? "Success" : "Failed") . "\n";
        
        // Test getting latest blogs
        $latestBlogs = $this->blogDao->getLatestBlogs();
        echo "Getting latest blogs: " . (count($latestBlogs) > 0 ? "Success" : "Failed") . "\n";
        
        echo "\n";
    }

    private function testCommentDao() {
        echo "Testiranje CommentDao...\n";
        
        // Test adding comment
        $commentId = $this->commentDao->addComment(1, 1, 'Test komentar');
        echo "Adding comment: " . ($commentId ? "Success" : "Failed") . "\n";
        
        // Test getting comments for blog
        $comments = $this->commentDao->getCommentsForBlog(1);
        echo "Getting comments for blog: " . (count($comments) > 0 ? "Success" : "Failed") . "\n";
        
        echo "\n";
    }

    private function testLikeDislikeDao() {
        echo "Testing LikeDislikeDao...\n";
        
        // Test adding like
        $likeResult = $this->likeDislikeDao->addOrUpdate(1, 1, true);
        echo "Adding like: " . ($likeResult ? "Success" : "Failed") . "\n";
        
        // Test getting reactions
        $reactions = $this->likeDislikeDao->getReactionCounts(1);
        echo "Getting reactions: " . ($reactions ? "Success" : "Failed") . "\n";
        
        echo "\n";
    }

    private function testTagDao() {
        echo "Testiranje TagDao...\n";
        
        // Test getting or creating tag
        $tagId = $this->tagDao->getOrCreate('test-tag');
        echo "Getting/creating tag: " . ($tagId ? "Success" : "Failed") . "\n";
        
        // Test getting popular tags
        $popularTags = $this->tagDao->getPopularTags(5);
        echo "Getting popular tags: " . (count($popularTags) > 0 ? "Success" : "Failed") . "\n";
        
        echo "\n";
    }
}

// Running tests
$test = new DaoTest();
$test->runAllTests();
?> 