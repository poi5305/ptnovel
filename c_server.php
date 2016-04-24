<?php

include_once("simple_html_dom.php");
include_once("s_book.php");
include_once("m_database.php");
include_once("m_http.php");
include_once("m_file.php");
include_once("m_parser.php");

class PtServer {

    const DEBUG = true;
    const QUERY_SLEEP = 6;
    const LOG_FILE = "log.txt";

    private $db;
    private $fpLog;

    public function PtServer() {
	    $this->fpLog = fopen(self::LOG_FILE, 'a');
        $this->db = new Sqlite3Db();
        $this->db->init();
    }

    public function index() {
    }

    public function updateBookList($fromPage = 1) {
        $page = $fromPage;
        $maxPage = 0;
        do {
            $html = PtHttpCk101::getBookListPage($page);
            $bookList = PtParserCk101::parseBooksFromForum($html);
            $maxPage = max($maxPage, PtParserCk101::parseForumPages($html));
            $this->d("parsing forum page $page/$maxPage");
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
                // do not update book here, because information is may not correct
                //$this->db->editBook(Book::toArray($book));
                // TODO: just update some things
                $this->d("Update a book. {$book->name}");
            }
        }
    }

    public function updateAllBooks($offset = 0) {
        $limit = 10;
        $dbBooks = $this->db->getBookList($offset, $limit);
        while (count($dbBooks) > 0) {
            $this->d("UpdateAllBooks, $offset~" . ($offset + $limit) . "/" . count($dbBooks));
            foreach ($dbBooks as &$dbBook) {
                $book = Book::withArray($dbBook);
                $this->updateBook($book);
                sleep(self::QUERY_SLEEP);
            }
            $offset += $limit;
            $dbBooks = $this->db->getBookList($offset, $limit);
        }
    }

    public function updateBookById($bookId) {
        $dbBooks = $this->db->getBookById($bookId);
        if (count($dbBooks) != 0) {
            $book = Book::withArray($dbBooks[0]);
            $this->updateBook($book);
        } else {
            $this->d("Error! Book not exists $bookId");
        }
    }

    public function updateBook(Book &$dbBook) {
        $html = PtHttpCk101::getBookContentPage($dbBook->id, 1);
        $book = PtParserCk101::parseBookFromThread($html);
        if ($book == null) {
	        $this->d("Error! The Book not exists. {$dbBook->id} {$dbBook->name}");
	        return;
        }
        $book->current_pages = max($dbBook->current_pages, 1);

		if ($book->posts == 0) {
			$this->d("Error! Can not parse pages! {$book->id}, {$book->current_pages}/{$book->pages}, {$book->name}");
			return;
		}

        do {
            $this->d("Start to update book {$book->id}, {$book->current_pages}/{$book->pages}, {$book->name}");
            if ($book->current_pages != 1) { // page 1 is already downloaded
                $html = PtHttpCk101::getBookContentPage($dbBook->id, $book->current_pages);
            }
            if ($html == null) {
	            $this->d("Get content text error! Ignore this book");
	            break;
            }
            $text = PtParserCk101::parseContentFromThread($html);
            PtFile::saveBook($book->id, $book->current_pages, $text);
            $this->db->editBook(Book::toArray($book));
            sleep(self::QUERY_SLEEP);
        } while (++$book->current_pages <= $book->pages);
    }

    public function d($msg) {
        if (self::DEBUG) {
            echo "DEBUG: $msg\n";
            fputs($this->fpLog, "DEBUG: $msg\n");
        }
    }

    public function __destruct() {
	    fclose($this->fpLog);
        $this->db->close();
    }

}

//$server = new PtServer();
//$server->updateBookList(1);
//$server->updateBookById(1082175);
//$server->updateAllBooks();

?>