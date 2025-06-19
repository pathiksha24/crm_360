<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<div class="panel-group">

    <?php
    $this->load->model('staff_model');
    $admins = $this->staff_model->get('',['admin' => 1]);
    ?>
    <label for="leads_unassigned_admins"><?php echo _l('leads_unassigned_admins'); ?></label>
    <select id="leads_unassigned_admins[]" name="leads_unassigned_admins[]" class="selectpicker" data-width="100%" data-none-selected-text="Admins" multiple="1" data-live-search="true" tabindex="-98">
        <option value=""></option>
        <?php foreach ($admins as $admin){
            $admin_full_name = get_staff_full_name($admin['staffid']);
            $selected = '';
            if (has_access_to_unassigned_leads($admin['staffid'])) $selected = 'selected';

            echo '<option value="'.$admin['staffid'].'" '.$selected.'>'.$admin_full_name.'</option>';
        } ?>
    </select>


</div>