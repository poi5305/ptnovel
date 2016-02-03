<?php

interface PtNovelDatabase {

    // open/client to database
    public function init();

    public function close();

    public function getBookList($offset, $limit);

    public function getBookById($getBookById);

    public function searchBooks($name, $offset, $limit);

    public function addNewBook($data);

    public function editBook($book);

    public function deleteBook($bookId);
    
    public function sql($sql);

}

class SqlUtils {
    
    const SQL_GET_LIST = "SELECT * FROM %s LIMIT %d, %d";

    static public function select($table, $offset = 0, $limit = 10) {
        return "SELECT * FROM $table LIMIT $offset, $limit";
    }

    static public function selectWhere($table, $where, $offset = 0, $limit = 10) {
        return "SELECT * FROM $table WHERE $where LIMIT $offset, $limit";
    }

    static public function insert($table, $data = array()) {
        if (count($data) == 0) {
            return;
        }
        $query_value = '';
        $query_fields = '';
        foreach($data as $field => $value){
            $query_value .= " '".$value."' ,";
            $query_fields .= " `".$field."` "." ,";
        }
        $query_value = substr($query_value,0,-1);
        $query_fields = substr($query_fields,0,-1);
        return "INSERT INTO `{$table}` (" . $query_fields . ") VALUES (" . $query_value . ")";
    }

    static public function update($table, $data = array(), $where) {
        $query_limit="";
        if(is_array($where)){
            foreach($where as $f=>$c){
                $query_limit .= " `".$f."` = "." '".$c."' AND";
            }
            $query_limit = substr($query_limit,0,-3);
        }else{
            $query_limit = $where;
        }
        $query_set = '';
        foreach($data as $field => $value){
            $query_set .= " `".$field."` = "." '".$value."' ,";
        }
        $query_set = substr($query_set,0,-1);
        return "UPDATE `{$table}` SET " . $query_set . " WHERE " . $query_limit;
    }

    static public function delete($table, $where) {
        return "DELETE FROM $table WHERE $where";
    }

}

class Sqlite3Db implements PtNovelDatabase {

    const PATH_DATABASE = "books/ptdb.sqlite3";
    const TABLE_BOOKS = "books";
    const SQL_CREATE_TABLE_BOOKS = <<<SQL_BOOKS
        CREATE TABLE IF NOT EXISTS books(
            id INTEGER PRIMARY KEY,
            name TEXT,
            class TEXT,
            posts INTEGER,
            pages INTEGER,
            current_pages INTEGER,
            looks INTEGER,
            likes INTEGER,
            info TEXT,
            source TEXT
        );
SQL_BOOKS;

    var $handle = NULL;

    public function Sqlite3Db() {

    }

    public function init() {
        $this->handle = new SQLite3(self::PATH_DATABASE);
        $this->handle->query(self::SQL_CREATE_TABLE_BOOKS);
    }

    public function close() {
        $this->handle->close();
    }

    public function getBookList($offset, $limit) {
        $sql = SqlUtils::select(self::TABLE_BOOKS, $offset, $limit);
        $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function getBookById($bookId) {
        $sql = SqlUtils::selectWhere(self::TABLE_BOOKS, "id = '$bookId'");
        $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function searchBooks($name, $offset, $limit) {
       $sql = SqlUtils::selectWhere(self::TABLE_BOOKS, "name LIKE '%$name%'", $offset, $limit);
       $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function addNewBook($book = array()) {
        if (count($book) == 0) {
            return 0;
        }
        $sql = SqlUtils::insert(self::TABLE_BOOKS, $book);
        @$this->handle->exec($sql); // maybe already exists
        return $this->handle->lastInsertRowID();
    }

    public function editBook($book = array()) {
        if (count($book) == 0) {
            return 0;
        }
        $bookId = $book['id'];
        if ($bookId == 0) {
            return 0;
        }
        $sql = SqlUtils::update(self::TABLE_BOOKS, $book, "id = '$bookId'");
        $this->handle->exec($sql);
        return $bookId;
    }
    
    public function sql($sql) {
	    $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function deleteBook($bookId) {
        $sql = SqlUtils::delete(self::TABLE_BOOKS, "id = '$bookId'");
        $this->handle->exec($sql);
        return $bookId;
    }

    public function printError($msg) {
        $errorCode = $this->handle->lastErrorCode();
        if ($errorCode != 0) {
            echo $msg . ": " . $errorCode . " " . $this->handle->lastErrorMsg() . "\n";
        }
    }

}

class Sqlite3DbTest {

    var $handle = NULL;

    function Sqlite3DbTest() {
        $this->handle = new Sqlite3Db();
        $this->testInit();
        $this->testGetBookList();
        $this->testGetBookById();
        $this->testAddNewBook();
        $this->testEditBook();
        $this->testDeleteBook();
    }

    function testInit() {
        $this->handle->init();
        $this->handle->printError(__FUNCTION__);
    }

    function testGetBookList() {
        $r = $this->handle->getBookList(0, 10);
        $this->handle->printError(__FUNCTION__);
    }

    function testGetBookById() {
        $r = $this->handle->getBookById(3347713);
        $this->handle->printError(__FUNCTION__);
    }

    function testAddNewBook() {
        $book = array("id" => "3347713",
            "name" => "不幹正事的魔王",
            "class" => "魔王",
            "posts" => "335",
            "pages" => "40",
            "current_pages" => "40",
            "looks" => "115887",
            "likes" => "15",
            "info" => "some info",
            "source" => "ck101");
        $this->handle->addNewBook($book);
        $this->handle->printError(__FUNCTION__);
    }

    function testEditBook() {
        $book = array("id" => "3347713",
            "posts" => "340",
            "current_pages" => "335",
            "looks" => "155887",
            "likes" => "20");
        $this->handle->editBook($book);
        $this->handle->printError(__FUNCTION__);
    }

    function testDeleteBook() {
        $this->handle->deleteBook(3347713);
        $this->handle->printError(__FUNCTION__);
    }

}

//$test = new Sqlite3DbTest();

?>
