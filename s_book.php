<?php

class Book {

    public $id;
    public $name;
    public $class;
    public $posts;
    public $pages;
    public $current_pages;
    public $looks;
    public $likes;
    public $info;
    public $source;

    public function Book($id, $name, $class, $posts, $pages,
        $current_pages, $looks, $likes, $info, $source) {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->posts = $posts;
        $this->pages = $pages;
        $this->current_pages = $current_pages;
        $this->looks = $looks;
        $this->likes = $likes;
        $this->info = $info;
        $this->source = $source;
    }

    public static function withArray($book) {
        extract($book);
        return new Book($id, $name, $class, $posts, $pages,
            $current_pages, $looks, $likes, $info, $source);
    }

    public static function toArray(Book &$book) {
        return get_object_vars($book);
    }

}

?>