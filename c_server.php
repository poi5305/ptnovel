<?php

include_once("simple_html_dom.php");
include_once("s_book.php");
include_once("m_database.php");
include_once("m_http.php");
include_once("m_file.php");
include_once("m_parser.php");


class PtServer {

    const DEBUG = true;
    const QUERY_SLEEP = 3;

    private $db;

    public function PtServer() {
        $this->db = new Sqlite3Db();
        $this->db->init();
    }

    public function updateBookList($fromPage = 1) {
        $page = $fromPage;
        $maxPage = 0;
        do {
            $this->d("parsing forum page $page ...");
            $html = PtHttpCk101::getBookListPage($page);
            $bookList = PtParserCk101::parseBooksFromForum($html);
            $maxPage = PtParserCk101::parseForumPages($html);
            $this->updateBookListImpl($bookList);
            $this->d("update list finish " . count($bookList) . " books");
            sleep(self::QUERY_SLEEP);
        } while (++$page <= $maxPage);
    }

    public function updateBookListImpl(&$bookList) {
        foreach ($bookList as &$book) {
            $dbBooks = $this->db->getBookById($book->id);
            if (count($dbBooks) == 0) {
                // book not exists, add one
                $this->db->addNewBook(Book::toArray($book));
                $this->d("Add a new book. {$book->name}");
            } else {
                // book already exists, update it
                $this->db->editBook(Book::toArray($book));
                $this->d("Update a book. {$book->name}");
            }
        }
    }

    public function d($msg) {
        if (self::DEBUG) {
            echo "DEBUG: $msg\n";
        }
    }

    public function __destruct() {
        $this->db->close();
    }

}

$server = new PtServer();
$server->updateBookList(23);

?>