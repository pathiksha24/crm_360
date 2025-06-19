<?php defined('BASEPATH') or exit('No direct script access allowed');


class Leads_webhook extends ClientsController{

    public function index()
    {
        file_put_contents('test.html', '');
    }

}