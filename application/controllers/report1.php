<?php

/**
 * PO MIS Reports Controller Class.
 * @pupose		PO MIS Reports information
 *
 * @filesource	./system/application/Controllers/po_mis_reports.php
 * @package		microfin
 * @subpackage	microfin.system.application.models.po_mis_reports
 * @version      $Revision: 1 $
 * @author       $Author: S. Abdul Matin $
 * @modified By      Farzana Rahman $
 * @lastmodified     $Date: 2017-11-28 $
 */
class Po_mis_reports extends MY_Controller {

    var $general_configuration = array();

    function __construct() {
        parent::__construct();
        //Loading Helper Class
        $this->load->helper(array('form', 'jquery'));
        if($this->db->hostname==DB_SERVER_25){
            $group=strtolower(SITE_NAME)."_replica";
            $replication_connection=$this->load->database($group,true);
            if(!empty($replication_connection)){
                $this->db=$replication_connection;
            }
        }
        //Loading the model. The first param-> Model Name, 2nd Param: Custom Name, 3rd Param: AutoLoad Database. If 3rd parameter is not used, you have to load the database manually
        $this->load->model(array('Po_mis_report', 'Po_branch', 'Po_region', 'Po_area', 'Po_zone', 'Report', 'Po_funding_organization', 'Loan_product', 'Config_non_cash_ft_ledger_code','Register_topsheet'), '', TRUE);
        $this->general_configuration = $this->Config_general->read('report_signature');
    }

    /**
     * Action for default POMIS report list view page
     * @uses     Creating POMIS report list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Matin
     */
    function index() {
        $data['headline'] = 'PKSF-POMIS Report List';
        $this->layout->view('/po_mis_reports/index', $data);
    }

    //POMIS Report -1
    /**
     * Action for default POMIS report 1 list view page
     * @uses     Creating POMIS report 1 list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Matin
     */
    function po_mis_1_index() {
        $data = $this->_load_combo_data();
        $data['headline'] = $this->lang->line('headline_pomis1_report');
        $data['title'] = $this->lang->line('title_pomis1_report');
        $data['general_config'] = $this->Config_general->read('saving');
        $this->layout->view('/po_mis_reports/po_mis_1_index', $data);
    }


    /**
     * Ajax function for POMIS report-1 generation
     * @uses     POMIS report-1 generation
     * @access	public
     * @param    void
     * @return	void
     * @author   Anis Alamgir
     */
    function ajax_po_mis_1_report() {
        if ($_POST) {
            $this->_prepare_validation();
            $data = $this->_get_posted_data();
            if ($this->form_validation->run() === TRUE) {
                $data['headline'] = $this->lang->line('headline_pomis1_report');
                $data['title'] = $this->lang->line('title_pomis1_report');
                $data['reporting_date'] = date("F j, Y");
                $data['month'] = $data['month_name'];
                $data['year'] = $data['year_name'];
                //echo "<pre>";print_r($data);
                $last_day_of_the_month = date('Y-m-t', strtotime("{$data['year']}-{$data['month']}-01"));
                $data = $this->get_report_level_wise_branch_information($data['report_level'], $data['branch_id'], $data['area_id'], $data['zone_id'], $data['region_id'], $data);
                $data['branch_member_info'] = $this->Po_mis_report->get_branch_member_information($data['branch_id'], $data['month'], $data['year'], $data['loan_report_type'], $data['funding_organization_id']);
                if($data['loan_report_type'] == 1)
                {
                    if(isset($data['branch_member_info']['optional_product_member_list'])){
                        $data['optional_product_member_list']=$data['branch_member_info']['optional_product_member_list'];
                    }
                    unset($data['branch_member_info']['optional_product_member_list']);
                }
                $data['branch_member_total'] = $this->Po_mis_report->get_branch_member_total($data['branch_id'], $data['month'], $data['year'], $data['funding_organization_id']);
                $data['savings_statement'] = $this->Po_mis_report->get_savings_statement_information($data['branch_id'], $data['month'], $data['year'], $data['loan_report_type'], $data['funding_organization_id'], $data['is_saving_category_wise']);
                if ($data['is_saving_category_wise']) {
                    $this->load->model('Saving_product_category');
                    $data['saving_products'] = $this->Saving_product_category->read();
                } else {
                    $data['saving_products'] = $this->Po_mis_report->get_saving_products();
                }
//                                echo "<pre>"; print_r($data['saving_products']); die;
                $data['independent_branches'] = $this->Po_mis_report->get_independent_branches_info($last_day_of_the_month, $data['branch_id'], $data['funding_organization_id']);
                $is_archived= $this->Po_mis_report->simple_read("archive_info", array('id'=>1), "id");

                $data['is_archived']=false;
//
                if(isset($is_archived->id)) {
                    $data['is_archived']=true;
                    $data['independent_branches_archive'] = $this->Po_mis_report->get_independent_branches_info($last_day_of_the_month, $data['branch_id'], $data['funding_organization_id'],true);
                }
                $data['optional_loan'] = $this->Loan_product->get_loan_product_information(0);
//                echo "<pre>"; print_r($data);
//                echo "<pre>"; print_r($data);
                if (($data['report_level'] == 1 && ($data['branch_id'] == -1 || !is_numeric($data['branch_id'])) ) || ($data['report_level'] == 2) || ($data['report_level'] == 3) || ($data['report_level'] == 4)) {
                    $data['row']=$this->ajax_get_pomis_error(0, $data['year'], $data['month'], $data['report_level'], $data['branch_id'], isset($data['area_id']) ? $data['area_id'] : NULL, isset($data['area_id']) ? $data['zone_id'] : NULL, isset($data['region_id']) ? $data['region_id'] : NULL);

                    // echo "<pre>"; print_r($data['row']);die;
                }
//                echo "<pre>";print_r($data);die;
                $this->load->view('/po_mis_reports/po_mis_1_report', $data);
            } else {
                $data['errors'][] = 'Please enter proper branch, date from and date to';
                $this->load->view('/reports/report_message', $data);
            }
        }
    }

    //POMIS Report -2
    /**
     * Action for default POMIS report 2 list view page
     * @uses     Creating POMIS report 2 list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Matin
     */
    function po_mis_2_index() {
        $data = $this->_load_combo_data();
        $data['headline'] = $this->lang->line('headline_pomis2_report');
        $data['title'] = $this->lang->line('title_pomis2_report');
        $this->layout->view('/po_mis_reports/po_mis_2_index', $data);
    }

    /**
     * Ajax function for POMIS report-2 generation
     * @uses     POMIS report-2 generation
     * @access	public
     * @param    void
     * @return	void
     * @author   Anis Alamgir
     * @ModifiedBy  :  Md Nafiz AL Ifat
     * @Modified date: 2017-06-05
     */
    function ajax_po_mis_2_report() {
        if ($_POST) {
            $this->load->model(array('Process_month_end'), '', TRUE);
            $this->_prepare_validation();
            $data = $this->_get_posted_data();
            if ($this->form_validation->run() === TRUE) {
                $report_type = ($data['cbo_service_charge'] == 'Yes') ? "with ".$this->lang->line('label_service_charge') : "without ".$this->lang->line('label_service_charge');
                $data['reporting_date'] = date("F j, Y");
                $data['headline'] = $this->lang->line('headline_pomis2_report')."&nbsp;($report_type)";
                $data['title'] = $this->lang->line('headline_pomis2_report')."&nbsp;($report_type)";
                $month = $data['month'] = $data['month_name'];
                $year = $data['year'] = $data['year_name'];
                $data= $this->get_report_level_wise_branch_information($data['report_level'], $data['branch_id'], $data['area_id'], $data['zone_id'], $data['region_id'], $data);
                $last_day_of_the_month = date("t", strtotime("$year-$month-01"));
                $end_of_the_month = "$year-$month-$last_day_of_the_month";
                $start_of_the_month = "$year-$month-01";
                $data['is_fraction_contain'] = $data['cbo_is_fraction_contain'];
                $data['branch_id'] = $data['branch_id'];
                $data['loan_info'] = $this->Po_mis_report->get_loan_information($data['year'], $data['month'], $data['branch_id'], $data['cbo_service_charge'], $data['loan_report_type'], $data['funding_organization_id']);
                $old_date = date("Y-m-d", strtotime('-1 months', strtotime($data['year'] . "-" . $data['month'] . "-01")));
                $old_month = date('m', strtotime($old_date));
                $old_year = date('Y', strtotime($old_date));
                //***************** For archive
                if(strlen($data['branch_id'])==1){
                    $archive_branch_id = $data['branch_id'];
                }else{
                    $archive_branch_id = -1;
                }
                $closing_current_fully_paid_is_archive = $this->Po_mis_report->is_archived($end_of_the_month);
                $opening_is_archive = $this->Po_mis_report->is_archived($start_of_the_month);
                $data['old_loan_info'] = $this->Po_mis_report->get_loan_information($old_year, $old_month, $data['branch_id'], $data['cbo_service_charge'], $data['loan_report_type'], $data['funding_organization_id']);
                $data['branch_borrower_total'] = $this->Po_mis_report->get_branch_borrower_total($data['branch_id'], $data['month'], $data['year'], $data['funding_organization_id'],$opening_is_archive);
                $data['total_only_optional_loanee'] = $this->Po_mis_report->get_only_optional_total_loanee($data['branch_id'], null, $end_of_the_month, true, false, null, null, $data['funding_organization_id'],$closing_current_fully_paid_is_archive);
                $data['total_optional_opening_borrower'] = $this->Po_mis_report->get_only_optional_total_loanee($data['branch_id'], $start_of_the_month, null, true, false, null, null, $data['funding_organization_id']);
                $data['total_optional_current_borrower'] = $this->Po_mis_report->get_only_optional_total_loanee($data['branch_id'], $start_of_the_month, $end_of_the_month, true, false, null, null, $data['funding_organization_id'],$closing_current_fully_paid_is_archive);
                $data['total_optional_fully_paid_borrower'] = $this->Po_mis_report->get_only_optional_total_loanee($data['branch_id'], $start_of_the_month, $end_of_the_month, false, false, null, null, $data['funding_organization_id'],$closing_current_fully_paid_is_archive);
                $data['grand_total_loanee_multiple_disburse_fullypaid'] = $this->Po_mis_report->get_branch_wise_loanee_multiple_disburse_fullypaid($data['branch_id'],$data['month'],$data['year']);
                if (($data['report_level'] == 1 && ($data['branch_id'] == -1 || !is_numeric($data['branch_id'])) ) || ($data['report_level'] == 2) || ($data['report_level'] == 3) || ($data['report_level'] == 4)) {
                    //$this->ajax_get_pomis_error(0, $data['year'], $data['month'], $data['report_level'], $data['branch_id'], isset($data['area_id']) ? $data['area_id'] : NULL, isset($data['area_id']) ? $data['zone_id'] : NULL, isset($data['region_id']) ? $data['region_id'] : NULL);
                }
//                echo "<pre>here"; print_r($data); die;
                $this->load->view('/po_mis_reports/po_mis_2_report', $data);
            } else {
                $data['errors'][] = 'Please enter proper branch, date from and date to';
                $this->load->view('/reports/report_message', $data);
            }
        }
    }

    //POMIS Report -2b
    /**
     * Action for default POMIS report 2 list view page
     * @uses     Creating POMIS report 2 list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Matin
     * @updated: Asif Raihan
     */
    function po_mis_2A_index() {
        $data = $this->_load_combo_data();
        $data['headline'] = $this->lang->line('headline_pomis2a_report');
        $data['title'] = $this->lang->line('title_pomis2a_report');
        $this->layout->view('/po_mis_reports/po_mis_2A_index', $data);
    }

    /**
     * Ajax function for POMIS report-2 generation
     * @uses     POMIS report-2 generation
     * @access	public
     * @param    void
     * @return	void
     * @author   Anis Alamgir
     * @updated: Asif Raihan
     */
    function ajax_po_mis_2A_report() {
        if ($_POST) {
            $this->_prepare_validation();
            $data = $this->_get_posted_data();
            if ($this->form_validation->run() === TRUE) {

                $report_type = ($data['cbo_service_charge'] == 'Yes') ? "with ".$this->lang->line('label_service_charge') : "without ".$this->lang->line('label_service_charge');
                $data['reporting_date'] = date("F j, Y");
                $data['headline'] = $this->lang->line('headline_pomis2a_report')."&nbsp;($report_type)";
                $data['title'] = $this->lang->line('title_pomis2a_report')."&nbsp;($report_type)";

                $month = $data['month'] = $data['month_name'];
                $year = $data['year'] = $data['year_name'];
                $data= $this->get_report_level_wise_branch_information($data['report_level'], $data['branch_id'], $data['area_id'], $data['zone_id'], $data['region_id'], $data);
                $last_day_of_the_month = date("t", strtotime("$year-$month-01"));
                $end_of_the_month = "$year-$month-$last_day_of_the_month";
                $start_of_the_month = "$year-$month-01";
                $data['is_fraction_contain'] = $data['cbo_is_fraction_contain'];
                $data['branch_id'] = $data['branch_id'];
                //$data['loan_info'] = $this->Po_mis_report->get_loan_information($data['year'],$data['month'],$data['branch_id'],$data['cbo_service_charge'],$data['loan_report_type'],$data['funding_organization_id']);
                $data['branch_borrower_total'] = $this->Po_mis_report->get_branch_borrower_total($data['branch_id'], $data['month'], $data['year'], $data['funding_organization_id']);
                $data['loan_due_info'] = $this->Po_mis_report->get_loan_due_information($data['year'], $data['month'], $data['branch_id'], $data['cbo_service_charge'], $data['loan_report_type'], $data['funding_organization_id']);
                //echo "<pre>";print_r($data['loan_due_info']);die;
                if (($data['report_level'] == 1 && ($data['branch_id'] == -1 || !is_numeric($data['branch_id'])) ) || ($data['report_level'] == 2) || ($data['report_level'] == 3) || ($data['report_level'] == 4)) {
                    $this->ajax_get_pomis_error(0, $data['year'], $data['month'], $data['report_level'], $data['branch_id'], isset($data['area_id']) ? $data['area_id'] : NULL, isset($data['area_id']) ? $data['zone_id'] : NULL, isset($data['region_id']) ? $data['region_id'] : NULL);
                }

                $this->load->view('/po_mis_reports/po_mis_2A_report', $data);
            } else {
                $data['errors'][] = 'Please enter proper branch, date from and date to';
                $this->load->view('/reports/report_message', $data);
            }
        }
    }

    //POMIS Report -3
    /**
     * Action for default POMIS report 3 list view page
     * @uses     Creating POMIS report 3 list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Matin
     */
    function po_mis_3_index() {
        $data = $this->_load_combo_data();
        $data['headline'] = $this->lang->line('headline_pomis3_report');
        $data['title'] = $this->lang->line('title_pomis3_report');
        $this->layout->view('/po_mis_reports/po_mis_3_index', $data);
    }

    /**
     * Ajax function for POMIS report-3 generation
     * @uses     POMIS report-3 generation
     * @access	public
     * @param    void
     * @return	void
     * @author   Anis Alamgir
     * @modified by    Farzana Rahman
     * @modified date  2017-10-24
     */
    function ajax_po_mis_3_report() {
        if ($_POST) {
            $this->_prepare_validation();
            $data = $this->_get_posted_data();
            if ($this->form_validation->run() === TRUE) {

                $report_type = ($data['cbo_service_charge'] == 'Yes') ? "with ".$this->lang->line('label_service_charge') : "without ".$this->lang->line('label_service_charge');
                $data['reporting_date'] = date("F j, Y");
                $data['headline'] = $this->lang->line('headline_pomis3_report')."&nbsp;($report_type)";
                $data['title'] = $this->lang->line('title_pomis3_report')."&nbsp;$report_type)";
                $data['month'] = $data['month_name'];
                $data['year'] = $data['year_name'];
                $data= $this->get_report_level_wise_branch_information($data['report_level'], $data['branch_id'], $data['area_id'], $data['zone_id'], $data['region_id'], $data);
                $data['is_fraction_contain'] = $data['cbo_is_fraction_contain'];
                $data['branch_id'] = $data['branch_id'];
                //echo $data['month'];die;
                $data['loan_info'] = $this->Po_mis_report->get_over_due_loan_information($data['year'], $data['month'], $data['branch_id'], $data['cbo_service_charge'], $data['loan_report_type'], $data['funding_organization_id'], $data['cbo_is_fraction_contain']);
                //echo "<pre>";print_r($data['loan_info']);die;
                $data['branch_borrower_total'] = $this->Po_mis_report->get_branch_borrower_total($data['branch_id'], $data['month'], $data['year'], $data['funding_organization_id']);
                ///echo "<pre>";print_r($data['branch_borrower_total']);die;
                $data['product'] = $this->Po_mis_report->get_branch_wise_product($data['branch_id'], $data['month'], $data['year']);

                // For area, zone, or region branch staff
                $report_level_arr = array(2=>'area_id',3=>'zone_id',4=>'region_id');

                if(isset($report_level_arr[$data['report_level']])){
                    $data['branch_id'] = $data['branch_id'].",{$data['report_level_branch_id']}";
                }

                $data['funding_org_type'] = $this->Po_mis_report->format_array("po_funding_organizations","","id","id,funding_org_type");
                $data['statement_of_workingarea'] = $this->Po_mis_report->get_statement_of_workinarea($data['branch_id'], $data['month'], $data['year'], $data['loan_report_type'], $data['funding_organization_id']);
                $data['staff_info'] = $this->Po_mis_report->get_statement_of_employee($data['branch_id'], $data['month'], $data['year'], $data['loan_report_type'], $data['funding_organization_id']);
                $data['user'] = $this->session->userdata('system.user');
                if (($data['report_level'] == 1 && ($data['branch_id'] == -1 || !is_numeric($data['branch_id'])) ) || ($data['report_level'] == 2) || ($data['report_level'] == 3) || ($data['report_level'] == 4)) {
                    $this->ajax_get_pomis_error(0, $data['year'], $data['month'], $data['report_level'], $data['branch_id'], isset($data['area_id']) ? $data['area_id'] : NULL, isset($data['area_id']) ? $data['zone_id'] : NULL, isset($data['region_id']) ? $data['region_id'] : NULL);
                }
                $this->load->view('/po_mis_reports/po_mis_3_report_new', $data);
            } else {
                $data['errors'][] = 'Please enter proper branch, date from and date to';
                $this->load->view('/reports/report_message', $data);
            }
        }
    }

    //POMIS Report -3
    /**
     * Action for default POMIS report 3 list view page
     * @uses     Creating POMIS report 3 list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   farzana rahman
     * @created date: 2017-06-06
     */
    function po_mis_3A_index() {
        $data = $this->_load_combo_data();
        $data['headline'] = $this->lang->line('headline_pomis3a_report');
        $data['title'] = $this->lang->line('title_pomis3a_report');
        $this->layout->view('/po_mis_reports/po_mis_3A_index', $data);
    }

    /**
     * Ajax function for POMIS report-3 generation
     * @uses        POMIS report-3 generation
     * @access      public
     * @param       void
     * @return	void
     * @author      Farzana Rahman
     * @created date 2017-11-28
     */
    function ajax_po_mis_3A_report() {
        $data = $this->_get_posted_data();
        $data['branch_id'] = $data['area_id'] = $data['zone_id'] = $data['region_id'] = 0;
        $levels = array('1' => 'branch_id', '2' => 'area_id', 3 => 'zone_id', 4 => 'region_id');
        $data[$levels[$data['report_level']]] = $this->input->post('cbo_selected_report_level');
        if($data['report_level'] == 1){
            $branch_id = $data['branch_id'];
        }else if($data['report_level'] == 2){
            $area = $this->Po_mis_report->simple_read('po_areas',"id = {$data['area_id']}",'name,code,branch_id');
            $branch_id = $area->branch_id;
        }else if($data['report_level'] == 3){
            $zone = $this->Po_mis_report->simple_read('po_zones',"id = {$data['zone_id']}",'name,code,branch_id');
            $branch_id = $zone->branch_id;
        }else if($data['report_level'] == 4){
            $region = $this->Po_mis_report->simple_read('po_regions',"id = {$data['region_id']}",'name,code,branch_id');
            $branch_id = $region->branch_id;
        }
        $data['branch_info'] = $this->Po_mis_report->simple_read('po_branches',"id = {$branch_id}",'name,code,address');

        $data['headline'] = $this->lang->line('headline_pomis3a_report');
        $data['title'] = $this->lang->line('title_pomis3a_report');

        if ($_POST) {
            $load_data = $this->_load_combo_data();
            $data['selected_report_level'] = $load_data['selected_report_level'];
            $data['month'] = $load_data['month_options'][$data['month_name']];
            $branch_id_list = $this->Register_topsheet->_get_branch_id_list($data);
            if(empty($branch_id_list)){
                $this->load->view('/reports/no_data_found', $data);
            }else if($data['loan_report_type']==''){
                $data['errors'][] = 'Please select loan options';
                $this->load->view('/reports/report_message', $data);
            }else{
                $data['previous_one_year'] = $data['year_name'] - 1;
                if ($data['month_name'] == '06') {
                    $data['previous_one_year_month'] = '1';
                    $data['previous_year'] = $data['year_name'] - 1;
                    $data['previous_month'] = '12';
                    $data['current_month'] = $data['month_name'];
                    $data['current_year'] = $data['year_name'];
                } else {
                    $data['previous_one_year_month'] = '7';
                    $data['previous_year'] = $data['year_name'];
                    $data['previous_month'] = '6';
                    $data['current_month'] = $data['month_name'];
                    $data['current_year'] = $data['year_name'];
                }
                if($data['loan_report_type']==0){
                    $this->db->order_by('short_name');
                    $data['product'] = $this->Po_mis_report->format_array('loan_products','','id','id,short_name');
                }else{
                    $data['product'] = array(1=>'Jagoron',2=>'Agrosor',3=>'Buniad',4=>'Others');
                    asort($data['product']);
                }

                $data['employment_information'] = $this->Po_mis_report->get_employment_informations($branch_id_list,$data,$data['funding_organization_id']);

                $this->load->view('/po_mis_reports/po_mis_3A_report_new', $data);
            }

        }

    }

    //POMIS Report - 5(a)
    /**
     * Action for default POMIS report 5(a) list view page
     * @uses     Creating POMIS report 5(a) list view page
     * @access	public
     * @param    void
     * @return	void
     * @author   Mahbub Titu
     */
    function po_mis_5a_index() {
        // $this->output->enable_profiler(true);
        $this->load->model(array('Acc_voucher'));
        //$this->load->helper(array('form','session','url'));
        $data = $this->_load_combo_data();
        //echo "<pre>";print_r($data);die;
        $data['headline'] = $this->lang->line('headline_pomis5a_report');
        $data['title'] = $this->lang->line('title_pomis5a_report');
        $data['funding_organization'] = $this->Po_funding_organization->get_funding_organization_list('0,1');
        $this->layout->view('/po_mis_reports/po_mis_5a_index', $data);
    }

    /**
     * Ajax function for POMIS report-5(a) generation
     * @uses     POMIS report-5(a) generation
     * @access	public
     * @param    void
     * @return	void
     * @author   Mahbub Titu
     */
    function ajax_po_mis_5a_report() {
        //$this->output->enable_profiler(TRUE);
        if ($_POST) {
            if($this->input->post('cbo_report_level') == 1 && !in_array($this->input->post('cbo_branch'), array(-1,-2))){
                $is_active_branch = $this->Po_branch->is_active_branch($this->input->post('cbo_branch'),$this->input->post('AsOnDate'));
                if(!$is_active_branch){
                    echo "<div class='warning' style='margin: 10px auto;'>Inactive Or Closed Branch!</div>";
                    redirect('/po_mis_reports/po_mis_5a_index/', 'refresh');
                }
            }
            $this->_prepare_validation();
            $data = $this->_get_posted_data();
            $data['cbo_branch'] = $this->input->post('cbo_branch');
            $data['is_fraction_contain'] = $this->input->post('cbo_is_fraction_contain');
            //	if ($this->form_validation->run() === TRUE){
            $data['headline'] = $this->lang->line('headline_note_bs_pomis5a_report');
            $data['title'] = $this->lang->line('title_pomis5a_report');
            $data['AsOnDate'] = $data['AsOnDate'];
            $data['report_options'] = $this->input->post('cbo_report_options');
            $data['loan_options'] = $this->input->post('cbo_loan_options');

            if ($data['branch_id'] == -1 || $data['branch_id'] == -2) {
                $data['branch_info']['name'] = 'All(Head Office)';
                $data['branch_info']['code'] = null;
                $data['branch_info']['address'] = '';
            } elseif (!is_numeric($data['branch_id'])) {
                $data['branch_info']['name'] = 'All';
                $data['branch_info']['code'] = null;
                $data['branch_info']['address'] = '';
            } else {
                $data['branch_info'] = $this->Po_mis_report->get_branches_info($data['branch_id']);
            }
            if ($data['branch_id'] == -2) {
                $branch_list = $this->Po_branch->get_branch_list_string(NULL, TRUE, 'code', 'B');
                $data['branch_id'] = $branch_list;
            } else if ($data['branch_id'] == -1) {
                $branch_list = $this->Po_branch->get_branch_list_string(NULL, TRUE, 'code');
                $data['branch_id'] = $branch_list;
            }
            if ($_POST['cbo_report_level'] == 2) {
                $branch_list = $this->Po_area->read($this->input->post('cbo_area'));
                $data['branch_info']['name'] = $branch_list->name;
                $data['branch_info']['address'] = '';
                $branch_list = explode(",", $branch_list->branch_list);
                $branch_list = join(",", $branch_list);
                $data['branch_id'] = $branch_list;
                $data['cbo_branch'] = "";
                // echo "<pre>";print_r($branch_list);die;
            } else if ($_POST['cbo_report_level'] == 3) {
                $area_list = $this->Po_zone->read($this->input->post('cbo_zone'));
                $data['branch_info']['name'] = $area_list->name;
                $data['branch_info']['address'] = '';
                // echo "<pre>";print_r($area_list);die;
                $area_list = explode(",", $area_list->area_list);
                $branch_list = array();
                foreach ($area_list as $area) {
                    $branches = $this->Po_area->read($area);
                    $branches = explode(",", $branches->branch_list);
                    for ($i = 0; $i < count($branches); $i++) {
                        $branch_list[] = $branches[$i];
                    }
                }
                $branch_list = join(",", $branch_list);
                $data['branch_id'] = $branch_list;
                $data['cbo_branch'] = "";
            } else if ($_POST['cbo_report_level'] == 4) {
                $zone_list = $this->Po_region->read($this->input->post('cbo_region'));
                $data['branch_info']['name'] = $zone_list->name;
                $data['branch_info']['address'] = '';
                $zone_list = explode(",", $zone_list->zone_list);
                $branch_list = array();
                foreach ($zone_list as $zone) {
                    $area_list = $this->Po_zone->read($zone);
                    $area_list = explode(",", $area_list->area_list);
                    foreach ($area_list as $area) {
                        $branches = $this->Po_area->read($area);
                        if (!empty($branches)) {
                            $branches = explode(",", $branches->branch_list);
                            foreach ($branches as $branch) {
                                $branch_list[] = $branch;
                            }
                        }
                    }
                }
                $branch_list = join(",", $branch_list);
                $data['branch_id'] = $branch_list;
                $data['cbo_branch'] = "";
            }
            $data['branch_id'] = $data['branch_id'];
            $user = $this->session->userdata('system.user');
            $branch_type = $user['branch_type'];
            if (($branch_type != 'B') && ($user['is_head_office'] != 1) && ($data['cbo_branch'] == -1)) {
                $data['cbo_branch'] = "";
            }

            //echo $data['branch_id'];die;
            if($user['login']=='ds.developer' || $user['login']=='ds.support2'){
                $this->load->model('po_mis_report_test');
                $data['report_data'] = $this->po_mis_report_test->get_notes_to_balance_sheet($data['branch_id'], $data['cbo_branch'], $data['is_fraction_contain'], $data['project_id'], $data['AsOnDate'],null,$data['loan_options']);
            }else{
                $data['report_data'] = $this->Po_mis_report->get_notes_to_balance_sheet($data['branch_id'], $data['cbo_branch'], $data['is_fraction_contain'], $data['project_id'], $data['AsOnDate'],null,$data['loan_options']);
            }
            $this->load->model('Loan_product_category');
            $data['funding_organization'] = $this->Po_funding_organization->get_fo_information();
            $data['loan_product_category'] = $this->Loan_product_category->get_loan_product_category_list();

            //for po_mis report error
            if (($_POST['cbo_report_level'] == 1 && $_POST['cbo_branch'] == -1 ) || ($_POST['cbo_report_level'] == 2) || ($_POST['cbo_report_level'] == 3) || ($_POST['cbo_report_level'] == 4)) {
                $this->ajax_get_pomis_error(0, date('Y', strtotime($data['AsOnDate'])), date('m', strtotime($data['AsOnDate'])), $_POST['cbo_report_level'], NULL, (isset($_POST['cbo_area']) && $_POST['cbo_area'] != "") ? $_POST['cbo_area'] : NULL, (isset($_POST['cbo_zone']) && $_POST['cbo_zone'] != "") ? $_POST['cbo_zone'] : NULL, (isset($_POST['cbo_region']) && $_POST['cbo_region'] != "") ? $_POST['cbo_region'] : NULL);
            }
            //end po_mis report error
            if ($data['report_options'] == 'S') {       //echo "<pre>"; print_r($data); echo "</pre>";
                $this->load->view('/po_mis_reports/po_mis_5a_report_savings_productwise', $data);
            } else {
                $this->load->view('/po_mis_reports/po_mis_5a_report_loan_productwise', $data);
            }
        }
    }

    //Grab posted data
    /**
     * Action for maping the form data to database fields
     * @uses     To get posted data
     * @access	private
     * @param    void
     * @return	array
     * @author   Matin
     */
    function _get_posted_data() {
        $data = array();
        $data['branch_id'] = $this->input->post('cbo_branch');
        $user = $this->session->userdata('system.user');
        $branch_type = $user['branch_type'];
        if (($branch_type != 'B') && ($user['is_head_office'] != 1) && ($data['branch_id'] == -1)) {
            $region_zone_area_id = $user['branch_type_id'];
            $type_list_info = $this->Po_region->get_branch_zone_area_list_by_region_zone_area_id($region_zone_area_id, $user['branch_type']);
            $data['branch_id'] = $type_list_info['branch_list'];
        }

        $data['funding_organization_id'] = $this->input->post('cbo_funding_organization');
        $data['month_name'] = $this->input->post('cbo_month');
        $data['year_name'] = $this->input->post('cbo_year');
        $data['AsOnDate'] = $this->input->post('AsOnDate');
        $data['cbo_service_charge'] = $this->input->post('cbo_service_charge');
        $data['cbo_is_fraction_contain'] = $this->input->post('cbo_is_fraction_contain');

        $data['project_id'] = $this->input->post('project_id');
        if (isset($_POST['cbo_loan_type'])) {

            $data['loan_report_type'] = $this->input->post('cbo_loan_type');
        }

        $data['report_level'] = $this->input->post('cbo_report_level');
        $data['area_id'] = $this->input->post('cbo_area');
        $data['zone_id'] = $this->input->post('cbo_zone');
        $data['region_id'] = $this->input->post('cbo_region');
        $data['is_saving_category_wise'] = $this->input->post('cbo_is_saving_category_wise');
        return $data;
    }

    //Combo Data Generation
    /**
     * Prepares data for combo
     * @uses     Loading data to combo
     * @access	private
     * @param    void
     * @return	array
     * @author   Matin
     */
    function _load_combo_data() {

        $user = $this->session->userdata('system.user');
        $general_config = $this->get_general_configuration('loan');
        $data['is_actual_daily_basis_loan_allowed'] = $general_config['is_actual_daily_basis_loan_allowed'];
        $branch_type = $user['branch_type'];
        $data['branch_type'] = $branch_type;
        if (($branch_type != 'B') && ($user['is_head_office'] != 1)) {
            $data['region_zone_area_id'] = $user['branch_type_id'];
            $data['branches_info'] = $this->Po_branch->get_branches_info_by_region_zone_area($data['region_zone_area_id'], FALSE);
            $type_list_info = $this->Po_region->get_branch_zone_area_list_by_region_zone_area_id($data['region_zone_area_id']);
            $area_list = (isset($type_list_info['area_list']) && !empty($type_list_info['area_list'])) ? $type_list_info['area_list'] : null;
            $zone_list = (isset($type_list_info['zone_list']) && !empty($type_list_info['zone_list'])) ? $type_list_info['zone_list'] : null;
        } else if($branch_type == 'B'){
            $current_branch = $this->get_branch_id();
            $data['branches_info'][] = $this->Po_mis_report->simple_read('po_branches',"id = $current_branch","id branch_id,name branch_name,code branch_code,address branch_address");
            $area_list = null;
            $zone_list = null;
        } else{
            $data['branches_info'] = $this->Po_branch->get_branches_info(null, true, 'code');
            $area_list = null;
            $zone_list = null;
        }
        $data['region_options'] = $this->Po_region->get_region_wise_zone_combo();
        $data['zone_options'] = $this->Po_zone->get_zone_wise_area_combo($zone_list);
        $data['area_options'] = $this->Po_area->get_area_wise_branch_combo($area_list);
        if ($branch_type == 'A') {
            $data['report_level'] = array('1' => $this->lang->line('label_branch'));
        } elseif ($branch_type == 'Z') {
            $data['report_level'] = array('1' => $this->lang->line('label_branch'), '2' => $this->lang->line('label_area'));
        } elseif ($branch_type == 'R' || $branch_type == 'L') {
            $data['report_level'] = array('1' => $this->lang->line('label_branch'), '2' => $this->lang->line('label_area'), '3' => $this->lang->line('label_zone'));
        } else {
            $data['report_level'] = array('1' => $this->lang->line('label_branch'), '2' => $this->lang->line('label_area'), '3' => $this->lang->line('label_zone'), '4' => $this->lang->line('label_region'));
        }

        if (isset($data['is_actual_daily_basis_loan_allowed']) && $data['is_actual_daily_basis_loan_allowed']) {
            $data['funding_organization'] = $this->Po_funding_organization->get_funding_organization_list(null, null, true);
        } else {
            $data['funding_organization'] = $this->Po_funding_organization->get_funding_organization_list();
        }
        //echo "<pre>";print_r($data['funding_organization']);echo "</pre>";exit();
        //This function is for listing of months
        if(SITE_NAME == 'sfras') {
            $data['months_info'] = array('' => '--SELECT--','03' => 'March','06' => 'June', '09' => 'September','12' => 'December');
        } else {
            $data['months_info'] = $this->Report->get_months();
        }
        //This function is for listing of year
        $data['year_info'] = $this->Report->get_year_range();
        //This function is for listing of service charge
        $data['service_charge_info'] = array('' => '--Select--', 'Yes' => "with ".$this->lang->line('label_service_charge'), 'No' => 'Without '.$this->lang->line('label_service_charge'));
        // current branch date
        $data['current_date'] = $this->get_current_date();
        //saving product category
        $data['month_options'] = array('06' => "January To June", '12' => "July To December");
        $data['selected_report_level'] = $this->input->post('cbo_selected_report_level');
        $data['user'] = $user;
        return $data;
    }

    /**
     * Action for setting validation rules
     * @uses     Validation rules set up
     * @access	private
     * @param    void
     * @return	void
     * @author   Matin
     */
    function _prepare_validation() {
        //Loading Validation Library to Perform Validation
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        //Setting Validation Rule
        $this->form_validation->set_rules('cbo_branch', $this->lang->line('label_branch'), 'trim|xss_clean|numeric|required');
        $this->form_validation->set_rules('cbo_month', 'Month', 'trim|xss_clean|numeric|required');
        $this->form_validation->set_rules('cbo_year', 'Year', 'trim|xss_clean|numeric|required');
        //$this->form_validation->set_rules('AsOnDate','AsOnDate','trim|xss_clean|required');
        if (isset($_POST['cbo_loan_type'])) {

            $this->form_validation->set_rules('cbo_loan_type', 'Loan Options', 'trim|xss_clean|numeric|required');
        }
    }

    /**
     * Action for getting error messege
     * @uses     show the list of branches who has not month end yet
     * @access   private
     * @param    $is_pop_up,$year,$month,$report_lavel,$branch_lists=NULL
     * @return   void
     * @date      2015-05-20
     * @author   Rafiur Rabby
     */
    function ajax_get_pomis_error($is_pop_up, $year, $month, $report_lavel, $branch_lists = NULL, $area_id = NULL, $zone_id = NULL, $region_id = NULL) {
        $branch_list = array();
        $date_to="$year-$month-01";
        $date_to=date('Y-m-t',strtotime($date_to));

        if ($report_lavel == 1) {
            if (!is_numeric($branch_lists)) {
                $branch_lists = ($is_pop_up) ? str_replace('p', ',', $branch_lists) : $branch_lists;
                $branch_lists_all = $this->Po_branch->get_branch_list($branch_lists, false, FALSE,$date_to);
                foreach ($branch_lists_all as $r) {
                    $branch_list[$r->id]['name'] = $r->name;
                    $branch_list[$r->id]['code'] = $r->code;
                }
            } else {
                $branch_lists_all = $this->Po_branch->get_branches_info(null, true, 'code',false,false,$date_to);
                foreach ($branch_lists_all as $r) {
                    $branch_list[$r->branch_id]['name'] = $r->branch_name;
                    $branch_list[$r->branch_id]['code'] = $r->branch_code;
                }
            }
        } elseif ($report_lavel == 2) {
            $area_wise_branch_list = $this->Po_area->get_braches_by_area($area_id);
            $branch_list = $this->Po_mis_report->get_branches_info($area_wise_branch_list, TRUE);
            //echo "<pre>"; print_r($branch_list);
        } elseif ($report_lavel == 3) {
            $zone_id = $zone_id;
            $zones = $this->Po_zone->read($zone_id);
            if (!empty($zones->area_list)) {
                $branch_list = $this->Po_area->get_braches_by_area($zones->area_list);
                $branch_list = $this->Po_mis_report->get_branches_info($branch_list, TRUE);
            }
        } elseif ($report_lavel == 4) {
            $regions = $this->Po_region->read($region_id);
            if (!empty($regions->zone_list)) {
                $area_list = $this->Po_zone->get_areas_by_zone($regions->zone_list);
                $branch_list = $this->Po_area->get_braches_by_area($area_list);
                $branch_list = $this->Po_mis_report->get_branches_info($branch_list, TRUE);
            }
        }
        //echo "<pre>"; print_r($branch_list);
        $error_list = $this->Po_mis_report->branch_list_has_not_month_end_yet(implode(',', array_keys($branch_list)), $month, $year);
        $data = array();
        //echo "<pre>"; print_r($branch_list);
        //echo "<pre>"; print_r($error_list);
        foreach ($branch_list as $key => $r) {
            if (!array_key_exists($key, $error_list)) {
                $data['error'][$key]['name'] = $r['name'];
                $data['error'][$key]['code'] = $r['code'];
            }
        }
        if(!empty($data['error'])){
            ksort($data['error']);
        }

        //echo "<pre>"; print_r($branch_lists); die;
        $data['is_pop_up'] = $is_pop_up;
        $data['branch_lists'] = $branch_lists;
        //echo "<pre>"; print_r($data); die;
        $this->load->view('/po_mis_reports/po_mis_report_error', $data);
    }
    /**
     * @uses     Get report level wise branch information
     * @access	public
     * @param    void
     * @return	void
     * @author   Farzana Rahman
     */
    function get_report_level_wise_branch_information($report_level, $branch_id, $area_id, $zone_id, $region_id, $data) {

        $data['report_level_txt'] = $this->lang->line('label_branch');
        if ($report_level == 1) {
            $data['address_info'] = (($branch_id == -1) || (!is_numeric($branch_id))) ? '' : $this->Po_mis_report->get_branches_info($branch_id);
        } else if ($report_level == 2 && is_numeric($area_id)) {
            $areas = $this->Po_area->read($area_id);
            if (!empty($areas->branch_list)) {
                $data['report_level_txt'] = $this->lang->line('label_area');
                $data['branch_id'] = $areas->branch_list;
                $data['report_level_branch_id'] = $areas->branch_id;
                $data['address_info']['name'] = $areas->name;
                $data['address_info']['code'] = $areas->code;
                $branch_info = $this->Po_mis_report->get_branches_info($areas->branch_id);
                $data['address_info']['address'] = $branch_info['address'];
            }
        } else if ($report_level == 3 && is_numeric($zone_id)) {
            $zones = $this->Po_zone->read($zone_id);
            //echo "<pre>";print_r($zones);
            if (!empty($zones->area_list)) {
                $data['report_level_txt'] = $this->lang->line('label_zone');
                $branch_list = $this->Po_area->get_braches_by_area($zones->area_list);
                $data['branch_id'] = $branch_list;
                $data['report_level_branch_id'] = $zones->branch_id;
                $data['address_info']['name'] = $zones->name;
                $data['address_info']['code'] = $zones->code;
                $branch_info = $this->Po_mis_report->get_branches_info($zones->branch_id);
                $data['address_info']['address'] = $branch_info['address'];
            }
        } else if ($report_level == 4 && is_numeric($region_id)) {
            $regions = $this->Po_region->read($region_id);
            //echo "<pre>";print_r($regions);
            if (!empty($regions->zone_list)) {
                $data['report_level_txt'] = $this->lang->line('label_region');
                $area_list = $this->Po_zone->get_areas_by_zone($regions->zone_list);
                $branch_list = $this->Po_area->get_braches_by_area($area_list);
                //echo "<pre>";print_r($branch_list);die;
                $data['branch_id'] = $branch_list;
                $data['report_level_branch_id'] = $regions->branch_id;
                $data['address_info']['name'] = $regions->name;
                $data['address_info']['code'] = $regions->code;
                $branch_info = $this->Po_mis_report->get_branches_info($regions->branch_id);
                $data['address_info']['address'] = $branch_info['address'];
            }
        } else {
            $data['address_info'] = (($branch_id == -1) || (!is_numeric($branch_id))) ? '' : $this->Po_mis_report->get_branches_info($branch_id);
        }

        return $data;
    }

}
