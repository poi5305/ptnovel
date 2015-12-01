<?php

include_once("m_controller.php");
include_once("c_server.php");
include_once("c_client.php");

$controller = new Controller();

/*

Server Usage:
    php index.php ptserver/updateBookList/1
    php index.php ptserver/updateBookById/1082175
    php index.php ptserver/updateAllBooks
    php index.php ptserver/updateAllBooks/20
    http://localhost/ptnovel/ptserver/updateBookList/1
    http://localhost/ptnovel/ptserver/updateBookById/1082175
    http://localhost/ptnovel/ptserver/updateAllBooks
Client Usage:
    php index.php client/getBookList/1 

*/



?>