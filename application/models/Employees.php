<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: nahid
 * Date: 04/01/21
 * Time: 12:02
 */
class Employees extends CI_Model
{
    public function __construct()
    {
        $this->db=$this->load->database('default',TRUE);
    }

    public function getAllEmployees()
    {
        $employees_query=$this->db->get('employees');
        return $employees_query->result();
    }

    public function add_employee($file)
    {
        $data=[
            'fname'=>$this->input->post('fname'),
            'lname'=>$this->input->post('lname'),
            'email'=>$this->input->post('email'),
            'phone'=>$this->input->post('phone'),
            'age'=>$this->input->post('age'),
            'type'=>$this->input->post('type'),
            'gender'=>$this->input->post('gender'),
            'dob'=>$this->input->post('dob'),
            'image'=>$file,
            'options'=>implode(',',$this->input->post('options')),
        ];
        $this->db->insert('employees',$data);
    }

    public function getEmployee($id)
    {
        $employee_query=$this->db->get_where('employees',array('id'=>$id))->row();
        return $employee_query;
    }

    public function update($id,$file)
    {
        $data=[
            'fname'=>$this->input->post('fname'),
            'lname'=>$this->input->post('lname'),
            'email'=>$this->input->post('email'),
            'phone'=>$this->input->post('phone'),
            'age'=>$this->input->post('age'),
            'type'=>$this->input->post('type'),
            'gender'=>$this->input->post('gender'),
            'dob'=>$this->input->post('dob'),
            'image'=>$file,
            'options'=>implode(',',$this->input->post('options')),
        ];
        $this->db->where('id',$id);
        return $this->db->update('employees',$data);
    }

    public function delete($id)
    {
        $this->db->delete('employees', array('id' => $id));
    }

    public function login()
    {


        if( $this->input->post('username')!='' &&
            $this->input->post('password')!='')
        {

            $username=$this->input->post('username');
            $password=md5($this->input->post('password'));

            $que=$this->db->query("select * from users where username='$username' and password='$password'");
            $row = $que->num_rows();

            if($row==1)
            {
                $res=$que->result_array();
                 echo json_encode([
                    'status'=>200,
                     'user'=>$res[0]
                ]);
            }else
            {
                echo json_encode([
                    'status'=>400,
                    'message'=>'Invalid username or password !!'
                ]);
            }
        }else{
            echo json_encode([
                'status'=>400,
                'message'=>'Invalid username or password !!'
            ]);
        }


    }

}