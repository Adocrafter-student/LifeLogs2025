<?php
require_once __DIR__ . '/../dao/TagDao.php';

class TagService {
    private $tagDao;

    public function __construct() {
        $this->tagDao = new TagDao();
    }

    public function createTag($name) {
        // Validacija
        if (empty($name)) {
            throw new Exception("Naziv taga ne može biti prazan");
        }

        if (strlen($name) > 50) {
            throw new Exception("Naziv taga ne može biti duži od 50 karaktera");
        }

        // Provjera da li tag već postoji
        if ($this->getTagByName($name)) {
            throw new Exception("Tag već postoji");
        }

        return $this->tagDao->add(['name' => $name]);
    }

    public function getTagById($id) {
        return $this->tagDao->getById($id);
    }

    public function getTagByName($name) {
        return $this->tagDao->getTagByName($name);
    }

    public function getAllTags() {
        return $this->tagDao->getAll();
    }

    public function updateTag($id, $name) {
        if (empty($name)) {
            throw new Exception("Naziv taga ne može biti prazan");
        }

        if (strlen($name) > 50) {
            throw new Exception("Naziv taga ne može biti duži od 50 karaktera");
        }

        return $this->tagDao->update($id, ['name' => $name]);
    }

    public function deleteTag($id) {
        return $this->tagDao->delete($id);
    }

    public function getTagsByBlogId($blog_id) {
        return $this->tagDao->getTagsForBlog($blog_id);
    }

    public function addTagToBlog($blog_id, $tag_id) {
        return $this->tagDao->addTagsToBlog($blog_id, [$tag_id]);
    }

    public function removeTagFromBlog($blog_id, $tag_id) {
        return $this->tagDao->removeTagFromBlog($blog_id, $tag_id);
    }

    public function getOrCreateTag($name) {
        return $this->tagDao->getOrCreate($name);
    }

    public function getPopularTags($limit = 10) {
        return $this->tagDao->getPopularTags($limit);
    }

    public function removeAllTagsFromBlog($blog_id) {
        return $this->tagDao->removeTagsFromBlog($blog_id);
    }
}
?> 