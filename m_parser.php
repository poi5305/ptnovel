<?php

include_once("simple_html_dom.php");
include_once("s_book.php");

interface PtParser {

    public static function parseContentFromThread(&$html);

    public static function parseBookFromThread(&$html);

    public static function parseBooksFromForum(&$html);

    public static function parseForumPages(&$html);

}

class PtParserUtil {

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

class PtParserCk101 implements PtParser{

    public static function parseContentFromThread(&$html) {
        $text = "";
        $posts = $html->find("*[id^=postmessage_]");
        foreach($posts as $post)
        {
            $text .= str_replace("&nbsp;", "", $post->plaintext);
        }
        return $text;
    }

    public static function parseBookFromThread(&$html) {
        if ($html == null) {
	        return null;
        }
        $postlist = $html->find("div[id='postlist']", 0);
        if ($postlist == null) {
	        return null;
        }
        $class = $postlist->find("h2",0)->plaintext;
        $name = $postlist->find("h1",0)->plaintext;

        $url = trim($html->find("link", 0)->href);
        list($id, $current_pages) = sscanf($url, "http://ck101.com/thread-%d-%d-1.html");

        $looks = $posts = $likes = 0;
        $bookInfo = $html->find("div.authMsg", 0);
        
        $looks = 0; $posts = 0; $likes = 0;

        if ($bookInfo != null) {
	        $looks = $bookInfo->find(".viewNum", 0)->plaintext;
			$posts = $bookInfo->find(".replayNum", 0)->plaintext;
			$likes = $bookInfo->find(".thankNum", 0)->plaintext;
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
        foreach($threads as $key => $thread) {
            $bookTitle = $thread->find("div.blockTitle", 0);
            $numbers = $thread->find(".num", 0)->plaintext;
            $id = $thread->tid;
            $class = $bookTitle->find("em", 0)->plaintext;
            $name = $bookTitle->find("a", 1)->plaintext;

            if($class == "[版務公告]") {
                continue;
            }

            list($likes, $posts, $looks) = array_values(array_filter(explode(" ", $numbers), function(&$v) {
                $v = str_replace("/", "", $v);
                $v = str_replace("感謝", "", $v);
                $v = trim($v);
                return $v != "" && $v != "\n";
            }));

            $likes = PtParserUtil::stringToInt($likes);
            $posts = PtParserUtil::stringToInt($posts);
            $looks = PtParserUtil::stringToInt($looks);
            $pages = floor($posts/10) + 1;
            $current_pages = 0;
            $info = strstr($name, "已完") ? 1 : 0;
            $source = "ck101";
            $books[] = new Book($id, $name, $class, $posts, $pages,
                $current_pages, $looks, $likes, $info, $source);
        }
        return $books;
    }

    public static function parseForumPages(&$html) {
        $pages = @$html->find("a.last", 0)->plaintext;
        if ($pages == "") {
            $nxt = $html->find("a.nxt", 0);
            $pages = $nxt == NULL ? 0 : $nxt->prev_sibling()->plaintext;
        }
        $pages = trim(str_replace(".", "", $pages));
        return $pages;
    }

}

class PtParserCk101Test {

    var $parser;

    public function PtParserCk101Test() {
        $this->testParserBooksFromForum();
        $this->testParseBookFromThread();
        $this->testParseForumPages();
    }

    public function testParserBooksFromForum() {
        //$html = file_get_html("test/forum.html");
        // new version 2016/01/20 update
        $search = "</span>\r\n                                  </div>";
        $replace = "</span>\r\n";
        $htmlText = str_replace($search, $replace, file_get_contents("test/forum.html"));
        $html = str_get_html($htmlText);
        $r = PtParserCk101::parseBooksFromForum($html);
        //print_r($r);
    }

    public function testParseBookFromThread() {
        $html = file_get_html("test/thread.html");
        $r = PtParserCk101::parseBookFromThread($html);
        //print_r($r);
    }

    public function testParseForumPages() {
        $html = file_get_html("test/forum.html");
        $pages = PtParserCk101::parseForumPages($html);
        //echo $pages;
    }

}

//$test = new PtParserCk101Test();

?>
