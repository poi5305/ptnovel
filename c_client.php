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
        echo file_get_contents("v_index.html");
    }

    public function getBookList($page = 1, $limit = 20) {
        $offset = min(0, ($page - 1) * $limit);
        $dbBooks = $this->db->getBookList($offset, $limit);
        echo json_encode($dbBooks);
    }

    public function getBookInfo($bookId) {
        PtFile::printBook($bookId, 1, 1);
    }

    public function deleteBook($bookId) {
        // Do not implement
    }

    public function downloadBook($bookId, $page = 0, $limit = 0) {
        $dbBooks = $this->db->getBookById((int) $bookId);
        if (count($dbBooks) == 0) {
            // TODO: error msg
            return;
        }
        $dbBook = array_shift($dbBooks);

        header("Content-type: text/plain");
        header("Content-type: text/plain; charset=UTF-16");
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . urlencode($dbBook["name"]."txt"));
        
        echo iconv("UTF-8", "UTF-16", $dbBook["name"]."\n\n");
        echo iconv("UTF-8", "UTF-16", "程式作者：Andy\n\n");
        echo iconv("UTF-8", "UTF-16", "Class: {$dbBook['class']}\n");
        echo iconv("UTF-8", "UTF-16", "Pages: {$dbBook['pages']}\n");
        echo iconv("UTF-8", "UTF-16", "Looks: {$dbBook['looks']}\n");

        $page = max(1, $page);
        $limit = $limit == 0 ? $dbBook["pages"] : $limit;

        PtFile::printBook($bookId, $page, $limit);
    }

    public function searchBook($name, $page = 1, $limit = 20) {
        $offset = min(0, ($page - 1) * $limit);
        $dbBooks = $this->db->searchBooks($name, $offset, $limit);
        echo json_encode($dbBooks);
    }

}

?>