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
    protected $redis;
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        $this->load->model('Employees');
        $this->redis=new Redis();
        $this->redis->connect('127.0.0.1', 6379);
        $this->redis->auth('mypass123456');
    }

    public function index_get($id=0)
    {
        if(empty($id))
        {
            if(!$this->redis->get('employees'))
            {
                $employees_data=$this->Employees->getAllEmployees();
                $data['employees']=$employees_data;
                $this->redis->set('employees',serialize($employees_data),20);
            }else
            {
                $data['employees']=unserialize($this->redis->get('employees'));
            }
        }else
        {
            $data['employee']=$this->Employees->getEmployee($id);
        }
        $this->response($data, \Restserver\Libraries\REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        if(isset($_POST['update']) && $_POST['update']==true)
        {
            $this->Employees->update($_POST['id'],"");
        }else if(isset($_POST['del']))
        {
            $this->Employees->delete($_POST['id']);
        }
        else
        {
            $config['upload_path']          = './uploads/';
            $config['allowed_types']        = 'gif|jpg|png';
            $config['max_size']             = 100;
            $config['max_width']            = 1024;
            $config['max_height']           = 768;
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('image')){
                $error = array('error' => $this->upload->display_errors());
            } else {
                $data = $this->upload->data();
            }
            $this->Employees->add_employee($data['file_name']);
        }
    }

    public function login_post()
    {
        $this->Employees->login();
    }

}