<?php $user=$this->session->userdata('system.user');	?>
<style>
    #msg {display:none; position:abstableute; z-index:200; background:url(<?php echo base_url()?>media/images/msg_arrow.gif) left center no-repeat; padding-left:7px}
    #msgcontent {display:block; background:#f3e6e6; border:2px stableid #924949; border-left:none; padding:5px; min-width:150px; max-width:250px}
</style>
<script src="<?php echo base_url()?>media/js/livevalidation_standalone.compressed.js"></script>
<script src="<?php echo base_url()?>media/js/messages.js"></script>
<script type="text/javascript">
    function validate(form)
    {
        var cbo_year = form.cbo_year.value;
        var cbo_month = form.cbo_month.value;
        <?php if(isset($user['is_head_office']) && $user['is_head_office']==1) { ?>
        var cbo_report_level = form.cbo_report_level.value;
        var cbo_branch = form.cbo_branch.value;
        var cbo_area = form.cbo_area.value;
        var cbo_zone = form.cbo_zone.value;
        var cbo_region = form.cbo_region.value;
        <?php } ?>
        if(cbo_month == "") {
            inlineMsg('cbo_month','<strong>Error</strong><br />You must select a month.',2);
            return false;
        }
        else if(cbo_year == "") {
            inlineMsg('cbo_year','<strong>Error</strong><br />You must select a year.',2);
            return false;
        }
        <?php if(isset($user['is_head_office']) && $user['is_head_office']==1) { ?>
        else if(cbo_report_level == '1' && cbo_branch == "") {
            inlineMsg('cbo_branch','<strong>Error</strong><br />You must select a branch.',2);
            return false;
        } else if(cbo_report_level == '2' && cbo_area == "") {
            //alert(cbo_report_level);
            inlineMsg('cbo_area','<strong>Error</strong><br />You must select a area.',2);
            return false;
        } else if(cbo_report_level == '3' && cbo_zone == "") {
            inlineMsg('cbo_zone','<strong>Error</strong><br />You must select a zone.',2);
            return false;
        } else if(cbo_report_level == '4' && cbo_region == "") {
            inlineMsg('cbo_region','<strong>Error</strong><br />You must select a region.',2);
            return false;
        }
        <?php } ?>
    }
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#cbo_branch_all').show();
        $('#cbo_area_all').hide();
        $('#cbo_zone_all').hide();
        $('#cbo_region_all').hide();
        $('#cbo_report_level').attr('value',"1");
        $("#cbo_report_level").change(
            function()
            {
                var selected_type_id = $("#cbo_report_level").val();
                //alert(selected_type_id);
                if(selected_type_id == 1){
                    $('#cbo_branch_all').show();
                    $('#cbo_area_all').hide();
                    $('#cbo_zone_all').hide();
                    $('#cbo_region_all').hide();
                }else if(selected_type_id == 2){
                    $('#cbo_branch_all').hide();
                    $('#cbo_area_all').show();
                    $('#cbo_zone_all').hide();
                    $('#cbo_region_all').hide();
                }else if(selected_type_id == 3){
                    $('#cbo_branch_all').hide();
                    $('#cbo_area_all').hide();
                    $('#cbo_zone_all').show();
                    $('#cbo_region_all').hide();
                }else if(selected_type_id == 4){
                    $('#cbo_branch_all').hide();
                    $('#cbo_area_all').hide();
                    $('#cbo_zone_all').hide();
                    $('#cbo_region_all').show();
                }else{
                    $('#cbo_branch_all').show();
                    $('#cbo_area_all').hide();
                    $('#cbo_zone_all').hide();
                    $('#cbo_region_all').hide();
                }
            }
        );

    });
</script>

<?php
//Branch list
$branches_options['-1'] = 'All';
foreach($branches_info as $branches_info)
{
    $branches_options[$branches_info->branch_id]=$branches_info->branch_code."-".$branches_info->branch_name;
}
//Region list
//	$region_options[''] = '--------SELECT--------';
//	foreach($regions as $region)
//	{
//		$region_options[$region->id]=$region->name;
//	}
//	//Zone list
//	$zone_options[''] = '--------SELECT--------';
//	foreach($zones as $zone)
//	{
//		$zone_options[$zone->id]=$zone->name;
//	}
//	//Area list
//	$area_options[''] = '--------SELECT--------';
//	foreach($areas as $area)
//	{
//		$area_options[$area->id]=$area->name;
//	}
//Month list
$month_options = array();
foreach($months_info as $key => $value)
{
    $month_options[$key] = $value;
}
//Year list
$year_options = array();
foreach($year_info as $key => $value)
{
    $year_options[$key] = $value;
}
$loan_types = array(0=>'Loan Product',1=>'Loan Product Category');
//branch current date selection
$month = "";
$year = "";
if(isset($current_date) && !empty($current_date)) {
    $year = date("Y",strtotime("-1 month $current_date"));
    $month = date("m",strtotime("-1 month $current_date"));
}


$funding_organization_options['-1'] =' All';
if (!isset($is_actual_daily_basis_loan_allowed) || !$is_actual_daily_basis_loan_allowed) {
    $funding_organization_options['-2'] ='PKSF & Non PKSF';
    $funding_organization_options['-3'] ='CONSOLIDATE';
}
foreach($funding_organization as $fo_row)
{
    $funding_organization_options[$fo_row->id]=$fo_row->name;
}
//echo "<pre>";print_r($funding_organization_options); echo "</pre>";
?>
<?php echo ajax_form_for_report('po_mis_reports/ajax_po_mis_1_report','#report_container',null,array('onsubmit'=>'if(validate(this)==false) return false;'));?>
<div style="border-bottom:solid 0px #dedede;width:100%;float:left;">
    <div class="toggle" style="display:none;width:100%;float:left;display:block;border:solid 0px red;">
        <table border="0" class="reportLayout" width="auto" cellspacing="0px" cellpadding="0">
            <tr>
                <?php if (isset($user['is_head_office']) && $user['is_head_office'] == 1) { ?>
                    <td id="cbo_report_level_all">
                        <label for="cbo_report_level">Report Level:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_report_level', $report_level, set_value('cbo_report_level', (isset($row->report_level) ? $row->report_level : "1")), 'id="cbo_report_level" style="width:110px;"'); ?><?php echo form_error('cbo_report_level'); ?>
                    </td>
                    <td id="cbo_region_all">
                        <label for="cbo_region">Region:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_region', $region_options, set_value('cbo_region', (isset($row->region_id) ? $row->region_id : "")), 'id="cbo_region" style="width:110px;"'); ?><?php echo form_error('cbo_region'); ?>
                    </td>
                    <td id="cbo_zone_all">
                        <label for="cbo_zone"><?php echo $this->lang->line('label_zone'); ?><em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_zone', $zone_options, set_value('cbo_zone', (isset($row->zone_id) ? $row->zone_id : "")), 'id="cbo_zone" style="width:110px;"'); ?><?php echo form_error('cbo_zone'); ?>
                    </td>
                    <td id="cbo_area_all">
                        <label for="cbo_area"><?php echo $this->lang->line('label_area'); ?>:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_area', $area_options, set_value('cbo_area', (isset($row->area_id) ? $row->area_id : "")), 'id="cbo_area" style="width:110px;"'); ?><?php echo form_error('cbo_area'); ?>
                    </td>
                    <td id="cbo_branch_all">
                        <label for="cbo_branch"><?php echo $this->lang->line('label_branch'); ?><em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_branch', $branches_options, set_value('cbo_branch', (isset($row->branch_id) ? $row->branch_id : "")), 'id="cbo_branch" style="width:110px;"'); ?><?php echo form_error('cbo_branch'); ?>
                    </td>
                <?php } else {
                    if($branch_type=='B'){
                        $disabled_branch = "disabled='disabled'";
                        $display = "style ='display:none;'";
                    }else {
                        $disabled_branch = "";
                        $display = "";
                    }
                    ?>
                    <td <?php echo $display; ?> id="cbo_report_level_all">
                        <label for="cbo_report_level">Report Level:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_report_level', $report_level, set_value('cbo_report_level', (isset($row->report_level) ? $row->report_level : "1")), 'id="cbo_report_level" style="width:110px;"'); ?><?php echo form_error('cbo_report_level'); ?>
                    </td>
                    <td  id="cbo_region_all">
                        <label for="cbo_region">Region:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_region', $region_options, set_value('cbo_region', (isset($row->region_id) ? $row->region_id : "")), "id='cbo_region' style='width:110px;'"); ?><?php echo form_error('cbo_region'); ?>
                    </td>
                    <td  id="cbo_zone_all">
                        <label for="cbo_zone">Zone:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_zone', $zone_options, set_value('cbo_zone', (isset($row->zone_id) ? $row->zone_id : "")), "id='cbo_zone' style='width:110px;'"); ?><?php echo form_error('cbo_zone'); ?>
                    </td>
                    <td  id="cbo_area_all">
                        <label for="cbo_area">Area:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_area', $area_options, set_value('cbo_area', (isset($row->area_id) ? $row->area_id : "")), "id='cbo_area' style='width:110px;'"); ?><?php echo form_error('cbo_area'); ?>
                    </td>
                    <td id="cbo_branch_all">
                        <label for="cbo_branch">Branch:<em>&nbsp;</em></label><br>
                        <?php echo form_dropdown('cbo_branch', $branches_options, set_value('cbo_branch', (isset($row->branch_id) ? $row->branch_id : ($branch_type=='B')?$user['branch_id']:"")), "$disabled_branch style='width:110px;'"); ?><?php echo form_error('cbo_branch'); ?>
                        <?php if($branch_type=='B'){ echo form_input(array('name' => 'cbo_branch', 'id' => 'cbo_branch', 'maxlength' => '100', 'class' => 'input_textbox', 'type' => 'Hidden'), set_value('cbo_branch', (isset($row->branch_id) ? $row->branch_id : $user['branch_id']))); } ?>
                        <?php if($branch_type=='B'){ echo form_input(array('name' => 'cbo_report_level', 'id' => 'cbo_report_level', 'maxlength' => '100', 'class' => 'input_textbox', 'type' => 'hidden'), set_value('cbo_report_level', '1')); } ?>
                        <?php echo form_error('cbo_branch'); ?>
                    </td>
                <?php } ?>
                <td>
                    <label for="cbo_month">Month:<em>&nbsp;</em></label><br>
                    <?php echo form_dropdown('cbo_month', $month_options,$month,'id="cbo_month" style="width:80px;"'); ?><?php echo form_error('cbo_month'); ?>
                </td>
                <td>
                    <label for="cbo_year">Year:<em>&nbsp;</em></label><br>
                    <?php echo form_dropdown('cbo_year', $year_options,$year,'id="cbo_year" style="width:50px;"'); ?><?php echo form_error('cbo_year'); ?>
                </td>
                <td>
                    <label for="cbo_loan_type">Loan Options:<em>&nbsp;</em></label><br>
                    <?php echo form_dropdown('cbo_loan_type', $loan_types,set_value('cbo_loan_type',"1"),'id="cbo_loan_type"'); ?><?php echo form_error('cbo_loan_type'); ?>
                </td>
                <?php if($general_config->is_savings_category_system_applicable){ ?>
                    <td>
                        <label for="cbo_loan_type">Show Savings Category Wise:<em>&nbsp;</em></label><br>
                        <?php
                        $saving_category_wise_option=array('1'=>'Yes','0'=>'No');
                        echo form_dropdown('cbo_is_saving_category_wise', $saving_category_wise_option,set_value('cbo_is_saving_category_wise',"1"),'id="cbo_is_saving_category_wise"'); ?><?php echo form_error('cbo_is_saving_category_wise'); ?>
                    </td>
                <?php } ?>
                <!------------------ Issue No: 3829 Start, By Me. Zohurul Islam ------------------------->
                <td>
                    <label for="cbo_funding_organization"><?php echo (isset($is_actual_daily_basis_loan_allowed) && $is_actual_daily_basis_loan_allowed)? 'Division' : 'Funding Organization'; ?>:<em>&nbsp;</em></label><br>
                    <?php echo form_dropdown('cbo_funding_organization', $funding_organization_options,set_value('cbo_funding_organization',"-1"),'id="cbo_funding_organization" style="width:112px;"'); ?><?php echo form_error('cbo_funding_organization'); ?>
                </td>
                <!------------------ Issue No: 3829 End ------------------------------------------------>
                <td><label></label><em>&nbsp;</em></label><br>
                    <?php echo form_submit(array('name'=>'submit','id'=>'submit','class'=>'save_button'),'Show Report');?>
                </td></tr>
        </table>
    </div>
</div>
<?php echo form_close();?>
