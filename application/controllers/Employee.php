<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: nahid
 * Date: 04/01/21
 * Time: 11:12
 */
class Employee extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Employees');
        $this->load->library('session');
    }

    public function index()
    {
        $lists['employees_data']=$this->Employees->getAllEmployees();
        $this->load->view('includes/header');
        $this->load->view('employee/index',$lists);
        $this->load->view('includes/footer');
    }

    public function create()
    {
        $this->load->view('includes/header');
        $this->load->view('employee/create');
        $this->load->view('includes/footer');
    }

    public function save()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('fname','First Name','required');
        $this->form_validation->set_rules('email','Email','required|valid_email');
        $this->form_validation->set_rules('phone','Phone','required');

        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 100;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('image')) {
            $error = array('error' => $this->upload->display_errors());
            $this->session->set_flashdata('danger-message', implode(',',$error));
            redirect(base_url('employee-index'));
        } else {
            $data = $this->upload->data();
        }

        if ($this->form_validation->run() == FALSE)
        {
            $this->session->set_flashdata('danger-message', "Invalid submission!");
            redirect(base_url('employee-index'));
        }else
        {
            $this->Employees->add_employee($data['file_name']);
        }
        $this->session->set_flashdata('message', 'Employee added successfully!!');
        redirect(base_url('employee-index'));
    }

    public function show($id)
    {
        return $this->load->view('employee/show');
    }

    public function edit($id)
    {
        $employee_info['employee_info']=$this->Employees->getEmployee($id);
        $this->load->view('includes/header');
        $this->load->view('employee/edit',$employee_info);
        $this->load->view('includes/footer');
    }

    public function update($id)
    {
        $this->Employees->update($id,"");
        redirect(base_url('employee-index'));
    }

    public function delete($id)
    {
        $this->Employees->delete($id);
        $this->session->set_flashdata('danger-message', 'Employee info removed!!');
        redirect(base_url('employee-index'));
    }
}