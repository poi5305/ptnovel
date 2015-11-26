<?php

class PtFile {

    const BOOK_DIR = "books";

    public function PtFile() {

    }

    public static function saveBook($bookId, $page, &$text) {
        $bookDir = PtFile::getBookPath($bookId);
        $bookPath = PtFile::getBookPath($bookId, $page);
        if (!file_exists($bookDir)) {
            mkdir($bookDir, 0777);
        }
        $fp = fopen("$bookPath", "w");
        // save and convert to utf-16 for some reason
        fputs($fp, iconv("UTF-8", "UTF-16", $text));
        fclose($fp);
    }

    public static function deleteBook($bookId) {
        $bookDir = $this->getBookPath($bookId);
        PtFile::removeDir($bookDir);
    }

    public static function printBook($bookId, $page_from, $page_end)
    {
        for ($i = $page_from; $i <= $page_end; $i++) {
            $bookPath = PtFile::getBookPath($bookId, $i);
            echo file_get_contents($bookPath);
        }
    }

    public static function removeDir($directory)
    {
        foreach (glob("{$directory}/*") as $file)
        {
            if (is_dir($file)) { 
                PtFile::removeDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($directory);
    }

    private static function getBookPath($bookId, $page = "") {
        if ($page == "") {
            return self::BOOK_DIR . "/$bookId";
        } else {
            return self::BOOK_DIR . "/$bookId/$page.txt";
        }
    }

}

?>