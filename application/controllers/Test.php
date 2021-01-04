<?php

/**
 * Created by PhpStorm.
 * User: nahid
 * Date: 04/01/21
 * Time: 10:40
 */
class Test extends CI_Controller
{
    public function getItems()
    {
        $data['item']="item 1";
        return $this->load->view('items/items',$data);
    }
}