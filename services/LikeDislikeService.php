<?php
require_once __DIR__ . '/../dao/LikeDislikeDao.php';

class LikeDislikeService {
    private $likeDislikeDao;

    public function __construct() {
        $this->likeDislikeDao = new LikeDislikeDao();
    }

    public function addLike($blog_id, $user_id) {
        // Provjera da li korisnik već ima reakciju na blog
        $existing_reaction = $this->likeDislikeDao->getUserReaction($blog_id, $user_id);
        
        if ($existing_reaction) {
            if ($existing_reaction['is_like'] == 1) {
                // Ako je već lajkovao, ukloni lajk
                return $this->likeDislikeDao->removeReaction($blog_id, $user_id);
            } else {
                // Ako je dislajkovao, promijeni u lajk
                return $this->likeDislikeDao->addOrUpdate($blog_id, $user_id, 1);
            }
        }

        return $this->likeDislikeDao->addOrUpdate($blog_id, $user_id, 1);
    }

    public function addDislike($blog_id, $user_id) {
        // Provjera da li korisnik već ima reakciju na blog
        $existing_reaction = $this->likeDislikeDao->getUserReaction($blog_id, $user_id);
        
        if ($existing_reaction) {
            if ($existing_reaction['is_like'] == 0) {
                // Ako je već dislajkovao, ukloni dislajk
                return $this->likeDislikeDao->removeReaction($blog_id, $user_id);
            } else {
                // Ako je lajkovao, promijeni u dislajk
                return $this->likeDislikeDao->addOrUpdate($blog_id, $user_id, 0);
            }
        }

        return $this->likeDislikeDao->addOrUpdate($blog_id, $user_id, 0);
    }

    public function getUserReaction($blog_id, $user_id) {
        return $this->likeDislikeDao->getUserReaction($blog_id, $user_id);
    }

    public function getLikesCount($blog_id) {
        $counts = $this->likeDislikeDao->getReactionCounts($blog_id);
        return $counts['likes'] ?? 0;
    }

    public function getDislikesCount($blog_id) {
        $counts = $this->likeDislikeDao->getReactionCounts($blog_id);
        return $counts['dislikes'] ?? 0;
    }

    public function getReactionsByBlogId($blog_id) {
        return $this->likeDislikeDao->getReactionsByBlogId($blog_id);
    }
}
?> 
?> 