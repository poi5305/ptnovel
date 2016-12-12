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
        $offset = max(0, ($page - 1) * $limit);
        $dbBooks = $this->db->getBookList($offset, $limit);
        echo json_encode($dbBooks);
    }

    public function getBookInfo($bookId) {
        PtFile::printBook($bookId, 1, 1);
    }

    public function deleteBook($bookId) {
        // Do not implement
    }
    
    public function getBooksInfo() {
	    $result["downloaded"] = $this->db->sql("select count(id) as count, sum(pages) as pages, sum(posts) as posts from books where current_pages == pages")[0];
	    $result["downloading"] = $this->db->sql("select count(id) as count, sum(pages) as pages, sum(posts) as posts from books where current_pages != pages")[0];
	    $result["finishBook"] = $this->db->sql("select count(id) as count, sum(pages) as pages, sum(posts) as posts from books where info == 1")[0];
		echo json_encode($result);
    }

    public function downloadBook($bookId, $page = 0, $limit = 0) {
        $dbBooks = $this->db->getBookById((int) $bookId);
        if (count($dbBooks) == 0) {
            // TODO: error msg
            return;
        }
        $dbBook = array_shift($dbBooks);
        $download_times = @$dbBook["download_times"] + 1;
        $this->db->editBook(array("id" => $dbBook["id"], "download_times" => $download_times));

        header('Content-type:application/force-download');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . urlencode($dbBook["name"]."txt"));
        
        echo iconv("UTF-8", "UTF-16", $dbBook["name"]."\n\n");
        echo iconv("UTF-8", "UTF-16", "程式作者：Andy\n\n");
        echo iconv("UTF-8", "UTF-16", "Class: {$dbBook['class']}\n");
        echo iconv("UTF-8", "UTF-16", "Pages: {$dbBook['pages']}\n");
        echo iconv("UTF-8", "UTF-16", "Posts: {$dbBook['posts']}\n");
        echo iconv("UTF-8", "UTF-16", "Looks: {$dbBook['looks']}\n");

        $page = max(1, $page);
        $limit = $limit == 0 ? $dbBook["pages"] : $limit;

        PtFile::printBook($bookId, $page, $limit);
    }
    
    public function downloadEBook($bookId, $page = 0, $limit = 0) {
        $dbBooks = $this->db->getBookById((int) $bookId);
        if (count($dbBooks) == 0) {
            // TODO: error msg
            return;
        }
        $dbBook = array_shift($dbBooks);
        $download_times = @$dbBook["download_times"] + 1;
		$this->db->editBook(array("id" => $dbBook["id"], "download_times" => $download_times));
        
        $page = max(1, $page);
        $limit = $limit == 0 ? $dbBook["pages"] : $limit;
        
        header('Content-type:application/force-download');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . urlencode($dbBook["name"].".epub"));
        
        include_once("TPEpubCreator.php");
        $epub = new TPEpubCreator();
        $epub->temp_folder = 'tmp/';
        $epub->epub_file = "tmp/$bookId.epub";
        $epub->title = $dbBook["name"];
        
        $epub->creator = 'Andy.ck101';
        $epub->language = 'zh';
        $epub->rights = 'ck101';
        $epub->publisher = 'http://novel.elggum.com';
        
        for ($i = $page; $i <= $limit; $i++) {
            $bookPath = PtFile::getBookPath($bookId, $i);
            if (file_exists($bookPath)) {
                $c = file_get_contents($bookPath);
                $c = iconv("UTF-16", "UTF-8", $c);
                $epub->AddPage($c, false, "$i page");
            }
        }
        if ( ! $epub->error ) {
            $epub->CreateEPUB();
            echo file_get_contents($epub->epub_file);
        } else {
            echo $epub->error;
        }
    }

    public function searchBook($name, $page = 1, $limit = 20) {
        $offset = max(0, ($page - 1) * $limit);
        $dbBooks = $this->db->searchBooks($name, $offset, $limit);
        echo json_encode($dbBooks);
    }

}

?>
