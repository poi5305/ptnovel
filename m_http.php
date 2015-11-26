<?php

include_once("simple_html_dom.php");

interface PtHttp {

    public static function login();

    public static function getSearchListPage($search);

    public static function getBookListPage($page);

    public static function getBookContentPage($bookId, $page);

}

class PtHttpUtil {

    public static function getDomFromUrl($url) {
        return file_get_html($url);
    }

}

class PtHttpCk101 implements PtHttp {

    const WEB_URL = "http://ck101.com";
    const FORUM_URL = "forum-237-%d.html";
    const THREAD_URL = "thread-%d-%d-1.html";

    public static function login() {}

    public static function getSearchListPage($search) {}

    public static function getBookListPage($page) {
        $url = self::WEB_URL . "/" . sprintf(self::FORUM_URL, $page);
        return PtHttpUtil::getDomFromUrl($url);
    }

    public static function getBookContentPage($bookId, $page) {
        $url = self::WEB_URL . "/" . sprintf(self::THREAD_URL, $bookId, $page);
        return PtHttpUtil::getDomFromUrl($url);
    }

}

class PtHttpCk101Test {

    public function PtHttpCk101Test() {
        $this->testGetBookListPage();
        $this->testgetBookContentPage();
    }

    public function testGetBookListPage() {
        PtHttpCk101::getBookListPage(1);
    }

    public function testgetBookContentPage() {
        PtHttpCk101::getBookContentPage(3347713, 1);
    }

}

//$test = new PtHttpCk101Test();

?>