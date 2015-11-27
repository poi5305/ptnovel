<?php


class Controller{

    public $defaultObject = "";
    public $defaultMethod = "index";

    public $path;
    public $query;
    
    private $cmdObject;
    private $cmdMethod;
    private $cmdArgs;

    public function Controller() {
        if (PHP_SAPI == "cli") {
            // cli mode
            if ($_SERVER["argc"] > 1) {
                $cmd = explode("?", $_SERVER["argv"][1]);
                $this->path = @$cmd[0];
                $this->query = @$cmd[1];
            }
        } else {
            // url mode or other
            $this->path = $_SERVER["PATH_INFO"];
            $this->query = $_SERVER["QUERY_STRING"];
        }
        $this->parseCommand();
        $this->doCommand();
    }

    public function parseCommand() {
        $this->cmdObject = $this->defaultObject;
        $this->cmdMethod = $this->defaultMethod;
        $this->cmdArgs = array();

        if ($this->path == "") {
            return;
        }
        $this->path = trim($this->path, "/");
        $args = explode("/", $this->path);

        if (count($args) > 0) {
            $this->cmdObject = array_shift($args);
        } else {
            $this->cmdObject = $this->defaultObject;
        }
        if (count($args) > 0) {
            $this->cmdMethod = array_shift($args);
        } else {
            $this->cmdMethod = $this->defaultMethod;
        }
        $this->cmdArgs = $args;
    }

    public function doCommand() {
        $isSuccess = false;
        if (class_exists($this->cmdObject)) {
            $obj = new $this->cmdObject;
            if (method_exists($obj, $this->cmdMethod)) {
                $isSuccess = true;
                call_user_func_array(array($obj, $this->cmdMethod), $this->cmdArgs);
            }
        }
        if (!$isSuccess) {
            echo "Class or method do not exists: \n";
            $this->printCmd();
        }
    }

    public function printCmd() {
        echo "Query: " . $this->path . " : " . $this->query . "\n";
        echo "Cmd: {$this->cmdObject}->{$this->cmdMethod}\n";
    }

}

?>