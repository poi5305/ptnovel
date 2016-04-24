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
    public $update_time;

    public function Book($id, $name, $class, $posts, $pages,
        $current_pages, $looks, $likes, $info, $source, $update_time = "") {
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
        $this->update_time = $update_time;
    }

    public static function withArray($book) {
        extract($book);
        return new Book($id, $name, $class, $posts, $pages,
            $current_pages, $looks, $likes, $info, $source, $update_time);
    }

    public static function toArray(Book &$book) {
        // not call get_object_vars for hidding private parameter
        return call_user_func('get_object_vars', $book);
    }

}

?>