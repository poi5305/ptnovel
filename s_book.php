<?php

class Book {

    public $id;
    public $name;
    public $class;
    public $posts;
    public $current_posts;
    public $looks;
    public $likes;
    public $info;
    public $source;

    public function Book($id, $name, $class, $posts, 
        $current_posts, $looks, $likes, $info, $source) {
        $this->id = $id;
        $this->name = $name;
        $this->class = $class;
        $this->posts = $posts;
        $this->current_posts = $current_posts;
        $this->looks = $looks;
        $this->likes = $likes;
        $this->info = $info;
        $this->source = $source;
    }

    public static function withArray($book) {
        extract($book);
        return new Book($id, $name, $class, $posts, 
            $current_posts, $looks, $likes, $info, $source);
    }

    public static toArray(Book &$book) {
        return get_object_vars($book);
    }

}

?>