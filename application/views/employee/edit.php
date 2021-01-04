
<div class="col-md-6 col-md-offset-3">

    <?php echo validation_errors(); ?>

    <?php
    $options=explode(',',$employee_info->options);
    ?>
    <form class="form-horizontal" enctype="multipart/form-data" method="post" action="<?php echo base_url('update-employee/'.$employee_info->id)?>">
        <div class="form-group">
            <label class="control-label col-sm-3" for="fname">First Name: *</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="<?php echo $employee_info->fname; ?>" id="fname" placeholder="First Name" name="fname">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="lname">Last Name:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="<?php echo $employee_info->lname; ?>" id="lname" placeholder="Last Name" name="lname">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="email">Email: *</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" value="<?php echo  $employee_info->email;; ?>" placeholder="Email" name="email">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="phone">Phone:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="phone" value="<?php echo $employee_info->phone?>" placeholder="Phone" name="phone">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="age">Age:</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="age" value="<?php  echo $employee_info->age; ?>" placeholder="Age" name="age">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="gender">Gender:</label>
            <div class="col-sm-9">
                <div class="radio">
                    <label><input type="radio" value="male" name="gender" <?php echo set_radio('gender',  $employee_info->gender, TRUE); ?>>Male</label>
                </div>
                <div class="radio">
                    <label><input type="radio" value="female" <?php echo set_radio('gender',$employee_info->gender, TRUE); ?> name="gender">Female</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="type">Type:</label>
            <div class="col-sm-9">
                <select name="type" id="type" class="form-control">
                    <option value="">Select Type</option>
                    <option value="1" <?php echo set_select('type',$employee_info->type , TRUE); ?>>General</option>
                    <option value="2" <?php echo set_select('type',$employee_info->type , TRUE); ?>>Regular</option>
                    <option value="3" <?php echo set_select('type',$employee_info->type , TRUE); ?>>Temporary</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="dob">DOB:</label>
            <div class="col-sm-9">
                <input type="date" class="form-control" value="<?php echo $employee_info->dob; ?>" id="dob" name="dob">
            </div>
        </div>
        <div class="form-group hidden">
            <label class="control-label col-sm-3" for="image">Image:</label>
            <?php if(!empty($employee_info->image)){?>
                <img style="height:5%;width: 5%" src="<?=base_url('/uploads/').$employee_info->image?>"/>
            <?php }?>
            <div class="col-sm-8">
                <input type="file" class="form-control" id="image" value="<?=$employee_info->image?>" name="image">
            </div>
            <input type="hidden" name="old_image" value="<?=$employee_info->image?>">
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="image">Select Options:</label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <label><input name="options[]" <?=in_array('1',$options)?'checked':''?>  type="checkbox" value="1">Option 1</label>
                </div>
                <div class="checkbox">
                    <label><input  name="options[]" <?=in_array('2',$options)?'checked':''?> type="checkbox" value="2">Option 2</label>
                </div>
                <div class="checkbox">
                    <label><input name="options[]" <?=in_array('3',$options)?'checked':''?> type="checkbox" value="3">Option 3</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary pull-right">Update</button>
            </div>
        </div>
    </form>
</div>
