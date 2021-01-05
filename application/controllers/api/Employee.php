<?php
require APPPATH . '/libraries/REST_Controller.php';
/**
 * Created by PhpStorm.
 * User: nahid
 * Date: 05/01/21
 * Time: 11:30
 */
class Employee extends \Restserver\Libraries\REST_Controller
{

    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Accept");
        $this->load->model('Employees');
    }

    public function index_get($id=0)
    {
        if(empty($id))
        {
            $data['employees']=$this->Employees->getAllEmployees();
        }else
        {
            $data['employee']=$this->Employees->getEmployee($id);
        }
        $this->response($data, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('image')) {
            $error = array('error' => $this->upload->display_errors());
        } else {
            $data = $this->upload->data();
        }

        $this->Employees->add_employee($data['file_name']);
    }

    public function index_put()
    {
        $this->Employees->update($_POST['id'],"");
    }

}