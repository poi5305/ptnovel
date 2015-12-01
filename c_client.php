<?php

include_once("s_book.php");
include_once("m_database.php");
include_once("m_file.php");

class Client {

    public function Client() {
        $this->db = new Sqlite3Db();
        $this->db->init();
    }

    public function index() {

    }

    public function getBookList($page, $limit = 20) {
        $offset = min(0, ($page - 1) * $limit);
        $dbBooks = $this->db->getBookList($offset, $limit);
        echo json_encode($dbBooks);
    }

    public function getBookInfo($bookId) {
        echo PtFile::printBook($bookId, 1, 1);
    }

    public function deleteBook($bookId) {

    }

    public function downloadBook($page = 0, $limit = 0) {

    }

    public function searchBook($name) {

    }

}

?>