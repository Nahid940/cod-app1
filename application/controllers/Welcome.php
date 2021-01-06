<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('Employees');
    }

    public function index()
	{

        $redis=new Redis();
        $redis->connect('127.0.0.1', 6379);
        $key='employee';

        if(!$redis->get('employee'))
        {
            $query_data=$this->Employees->getAllEmployees();
            $lists['employees_data']=$query_data;
            $redis->set($key,serialize($query_data));
            $redis->expire($key, 10);
        }else
        {
            $lists['employees_data']=unserialize($redis->get('employee'));
        }
//        $lists['employees_data']=$this->Employees->getAllEmployees();
        $this->load->view('includes/header');
        $this->load->view('employee/index',$lists);
        $this->load->view('includes/footer');
	}
}
