
<div class="col-md-6 col-md-offset-3">
    <?php echo validation_errors() ?>
    <form class="form-horizontal" enctype="multipart/form-data" method="post" action="<?php echo base_url('create-employee')?>">
        <div class="form-group">
            <label class="control-label col-sm-3" for="fname">First Name: *</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="<?php echo set_value('fname'); ?>" id="fname" placeholder="First Name" name="fname">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="lname">Last Name:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" value="<?php echo set_value('lname'); ?>" id="lname" placeholder="Last Name" name="lname">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="email">Email: *</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" value="<?php echo set_value('email'); ?>" placeholder="Email" name="email">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="phone">Phone:</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="phone" placeholder="Phone" name="phone">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="age">Age:</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="age" value="<?php echo set_value('age'); ?>" placeholder="Age" name="age">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="gender">Gender:</label>
            <div class="col-sm-9">
                <div class="radio">
                    <label><input type="radio" value="male" name="gender" <?php echo set_radio('gender', 'male', TRUE); ?>>Male</label>
                </div>
                <div class="radio">
                    <label><input type="radio" value="female" <?php echo set_radio('gender', 'female', TRUE); ?> name="gender">Female</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="type">Type:</label>
            <div class="col-sm-9">
                <select name="type" id="type" class="form-control">
                    <option value="">Select Type</option>
                    <option value="1"  <?php echo set_select('type', 1, TRUE); ?>>General</option>
                    <option value="2"  <?php echo set_select('type', 2, TRUE); ?>>Regular</option>
                    <option value="3"  <?php echo set_select('type', 3, TRUE); ?>>Temporary</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="dob">DOB:</label>
            <div class="col-sm-9">
                <input type="date" class="form-control" value="<?php echo set_value('dob'); ?>" id="dob" name="dob">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="image">Select Options:</label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <label><input name="options[]" type="checkbox" value="1">Option 1</label>
                </div>
                <div class="checkbox">
                    <label><input  name="options[]" type="checkbox" value="2">Option 2</label>
                </div>
                <div class="checkbox">
                    <label><input name="options[]" type="checkbox" value="3">Option 3</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3" for="image">Image:</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="image" name="image">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary pull-right">Submit</button>
            </div>
        </div>
    </form>
</div>
