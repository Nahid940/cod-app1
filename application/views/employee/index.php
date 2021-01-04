<div class="col-md-12">
    <?php if($this->session->flashdata('message')): ?>
        <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
    <?php endif; ?>

    <?php if($this->session->flashdata('danger-message')): ?>
        <div class="alert alert-warning"><?php echo $this->session->flashdata('danger-message'); ?></div>
    <?php endif; ?>
    <a href="<?=base_url('employee/create')?>" class="btn btn-primary pull-right"><i class="fa fa-plus"></i></a>
    <table class="table">
        <thead>
            <tr>
                <th>Sl</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Image</th>
                <th>DOB</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i=1;
        if(!empty($employees_data)) {
            foreach ($employees_data as $emply) {
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $emply->fname . " " . $emply->lname ?></td>
                    <td><?= $emply->email ?></td>
                    <td><?= $emply->phone ?></td>
                    <td><?= $emply->age ?></td>
                    <td><?= $emply->gender == 1 ? 'Male' : 'Female' ?></td>
                    <td><img style="height:5%" src="<?= base_url('/uploads/') . $emply->image ?>" alt=""></td>
                    <td><?= date('d-m-Y',strtotime($emply->dob)) ?></td>
                    <td><?php
                        if ($emply->type == 1) {
                            echo "General";
                        } else if ($emply->type == 2) {
                            echo "Regular";
                        } else if ($emply->type == 3) {
                            echo "Temporary";
                        }
                        ?>
                    </td>
                    <td>
                        <!--<a href=""><i class="fa fa-edit btn btn-info btn-xs text-info"></i></a>-->
                        <form method="DELETE" action="<?php echo base_url('employee/delete/' . $emply->id); ?>">
                            <a class="btn btn-primary btn-xs" href="<?= base_url('employee/edit/' . $emply->id) ?>">
                                <i class=" fa fa-edit"></i></a>
                            <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php }
        }else{
        ?>
            <p class="text-danger">No Data Found !!</p>
        <?php }?>
        </tbody>
    </table>
</div>