<?php 

namespace App\Controller;

class Home {

    public function __construct ()
    {
        echo "HI";
    }

    public function index($id=null)
    {
        var_dump($id);
        return "INDEX.html";
    }
}