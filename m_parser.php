<?php

include_once("simple_html_dom.php");
include_once("s_book.php");

class PtParser {

    public function PtParser() {

    }

    public static function parseBookFromThread(&$html) {
        $postlist = $html->find("div[id='postlist']", 0);
        $class = $postlist->find("h2",0)->plaintext;
        $name = $postlist->find("h1",0)->plaintext;

        $url = trim($html->find("link", 0)->href);
        list($id, $current_pages) = sscanf($url, "http://ck101.com/thread-%d-%d-1.html");

        $looks = $posts = $likes = 0;
        $bookInfo = trim($html->find("div.authMsg", 0)->plaintext);
        preg_match("/.+查看\ (\d*).+回覆\ (\d*).+感謝\ (\d*)/", str_replace("\n", "", $bookInfo), $numbers);
        if (count($numbers) >= 4) {
            list($match, $looks, $posts, $likes) = $numbers;
        }

        $pages = floor($posts/10) + 1;
        $info = strstr($name, "已完") ? 1 : 0;
        $source = "ck101";

        return new Book($id, $name, $class, $posts, $pages,
                $current_pages, $looks, $likes, $info, $source);
    }

    public static function parseBooksFromForum(&$html) {
        $books = array();
        $threads = $html->find("*[id^=normalthread]");
        foreach($threads as $thread) {
            $bookTitle = $thread->find("div.blockTitle", 0);
            $numbers = $thread->find(".num", 0)->plaintext;
            
            $id = $thread->tid;
            $class = $bookTitle->find("em", 0)->plaintext;
            $name = $bookTitle->find("a", 1)->plaintext;

            list($likes, $posts, $looks) = array_values(array_filter(explode(" ", $numbers), function(&$v) {
                $v = str_replace("/", "", $v);
                $v = str_replace("感謝", "", $v);
                $v = trim($v);
                return $v != "" && $v != "\n";
            }));

            $likes = PtParser::stringToInt($likes);
            $posts = PtParser::stringToInt($posts);
            $looks = PtParser::stringToInt($looks);
            $pages = floor($posts/10) + 1;
            $current_pages = 0;
            $info = strstr($name, "已完") ? 1 : 0;
            $source = "ck101";

            $books[] = new Book($id, $name, $class, $posts, $pages,
                $current_pages, $looks, $likes, $info, $source);
        }
        return $books;
    }

    public static function page_url_to_info($page_url)
    {
        $url = explode("-",$page_url);
        return Array("novel_id"=>$url[1], "novel_page"=>$url[2]);
    }

    public static function stringToInt($s) {
        $s = str_replace("k", "00", $s);
        $s = str_replace("m", "00000", $s);
        $s = str_replace(".", "", $s);
        return +$s;
    }

}

class PtParserTest {

    var $parser;

    public function PtParserTest() {
        $this->parser = new PtParser();
        $this->testParserBooksFromForum();
        $this->testParseBookFromThread();
    }

    public function testParserBooksFromForum() {
        $html = file_get_html("test/forum.html");
        $r = PtParser::parseBooksFromForum($html);
        //print_r($r);
    }

    public function testParseBookFromThread() {
        $html = file_get_html("test/thread.html");
        $r = PtParser::parseBookFromThread($html);
        print_r($r);
    }

}

$test = new PtParserTest();

?>