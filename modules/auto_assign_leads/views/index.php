<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <h3><?php echo $title; ?></h3>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-filter"></i> Filters <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="#" id="newFilterMenuItem">New Filter</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li class="dropdown-header">Saved Filters</li>
                                      <?php 
                                $limit = 5; // Number of items to show initially
                                $total = count($saved_assignments);
                                ?>

                                <?php if ($total > 0) : ?>
                                    <?php foreach ($saved_assignments as $index => $assignment) : ?>
                                        <li class="<?= $index >= $limit ? 'extra-filter' : '' ?>" style="<?= $index >= $limit ? 'display:none;' : '' ?>">
                                            <a href="#" class="saved-filter-item" data-service-id="<?= $assignment['serviceid'] ?>">
                                                <?= htmlspecialchars($assignment['service_name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>

                                    <?php if ($total > $limit) : ?>
                                        <li>
                                            <button type="button" id="showMoreFilters" class="btn btn-link">More...</button>
                                        </li>
                                    <?php endif; ?>

                                <?php else : ?>
                                    <li>
                                        <a href="#">No saved filters, get started by creating a new filter.</a>
                                    </li>
                                <?php endif; ?>

                                </ul>
                            </div>
                        </div>
                        <hr>
                        <div class="_buttons">
                            <?php if (is_admin()) {
                                echo form_open(admin_url('auto_assign_leads/auto_assign'), ['id' => 'auto_assign__form']);
                            ?>
                                <!-- <div class="col-md-2">
                                    </?php echo render_select('staff_ids[]', $staff, ['staffid', ['firstname', 'lastname']], 'staff', '', ['data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div>
                                <div class="col-md-2">
                                    </?php echo render_select('service_ids[]', $services, ['id', 'name'], 'services', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div> -->
                               <div class="col-md-2">
                                <label for="service_ids"><?= _l('services') ?></label>
                                <select name="service_ids[]" id="service_ids" multiple="multiple" class="selectpicker" data-width="100%" data-none-selected-text="Select Service" data-live-search="true">
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['id'] ?>"><?= $service['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                       <div class="col-md-2">
                        <label for="staff_ids"><?= _l('staff') ?></label>
                        <select
                            name="staff_ids[]"
                            id="staff_ids"
                            multiple="multiple"
                            class="selectpicker"
                            data-width="100%"
                            data-none-selected-text="<?= _l('select_staff') ?>">
                        </select>
                    </div>


                                <div class="col-md-2">
                                    <?php echo render_select('source_ids[]', $sources, ['id', 'name'], 'sources', '', ['data-width' => '100%', 'data-none-selected-text' => _l('sources'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_input('max_leads_to_distribute', 'max_leads_to_distribute', get_option('max_leads_to_distribute'), 'number'); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_input('max_leads_for_each_staff', 'max_leads_for_each_staff', get_option('max_leads_for_each_staff'), 'number'); ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" style="margin-top: 24px" class="btn btn-success _delete">Auto Assign</button>
                                </div>
                            <?php echo form_close();
                            } ?>
                            <?php if(get_option('auto_assign_leads')){ echo form_open(admin_url('auto_assign_leads/revert_last_auto_assign'), ['id' => 'auto_assign__form']); ?>
                                <div class="col-md-1">
                                    <button type="submit" style="margin-top: 24px;margin-left: 10px" class="btn btn-danger _delete">Undo</button>
                                </div>
                            <?php echo form_close(); } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <div class="col-md-3 leads-filter-column">
                            <?php echo render_date_input('lead_log_date', 'utility_activity_log_filter_by_date', '', array(), array(), '', 'activity-log-date'); ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <table class="table table-auto_assign_leads dt-table scroll-responsive">
                            <thead>
                                <th><?php echo htmlspecialchars(_l('staff')); ?></th>
                                <th><?php echo htmlspecialchars(_l('service')); ?></th>
                                <th><?php echo htmlspecialchars(_l('total_assigned_leads')); ?></th>
                                <th><?php echo htmlspecialchars(_l('date')); ?></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- filter modal starts -->
 
<div class="modal fade" id="newFilterModal" tabindex="-1" role="dialog" aria-labelledby="newFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newFilterModalLabel">Manage Assignments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#create_assignment_tab" aria-controls="create_assignment_tab" role="tab" data-toggle="tab">Create New Assignment</a>
                    </li>
                    <li role="presentation">
                        <a href="#view_assignments_tab" aria-controls="view_assignments_tab" role="tab" data-toggle="tab">View All Assignments</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="create_assignment_tab">
                        <?php echo form_open(admin_url('auto_assign_leads/save_new_filter'), ['id' => 'new_filter_form']); ?>
                        <div class="modal-body"> <div class="form-group">
                                <?php
                                echo render_select(
                                    'modal_service_ids[]',
                                    $services,
                                    ['id', 'name'],
                                    'Service',
                                    '',
                                    [
                                        'data-width'           => '100%',
                                        'data-none-selected-text' => 'Select Service',
                                        'multiple'             => true
                                    ],
                                    [],
                                    'no-mbot',
                                    '',
                                    false
                                );
                                ?>
                            </div>
                            <div class="form-group">
                                <?php
                                echo render_select(
                                    'modal_staff_ids[]',
                                    $staff,
                                    ['staffid', ['firstname', 'lastname']],
                                    'Staff Name',
                                    '',
                                    [
                                        'data-width'           => '100%',
                                        'data-none-selected-text' => 'Select Staff',
                                        'multiple'             => true
                                    ],
                                    [],
                                    'no-mbot',
                                    '',
                                    false
                                );
                                ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Assignment</button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="view_assignments_tab">
                        <div class="clearfix tw-py-4"></div>
                        <table class="table dt-table scroll-responsive" id="assignmentsInModalTable">
                            <thead>
                                <tr>
                                    <th><?php echo htmlspecialchars(_l('staff')); ?></th>
                                    <th><?php echo htmlspecialchars(_l('service')); ?></th>
                                    <th><?php echo htmlspecialchars(_l('status')); ?></th>
                                    <th><?php echo htmlspecialchars(_l('date')); ?></th>
                                    <th><?php echo htmlspecialchars(_l('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($saved_assignments)) { ?>
                                    <?php foreach ($saved_assignments as $assignment) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assignment['staffname']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['service_name']); ?></td>
                                            <td>
                                                <div class="onoffswitch">
                                                    <input type="checkbox" id="status_<?php echo $assignment['id']; ?>"
                                                           name="onoffswitch" class="onoffswitch-checkbox assignment-status-toggle"
                                                           data-id="<?php echo $assignment['id']; ?>"
                                                           <?php echo ($assignment['status'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="onoffswitch-label" for="status_<?php echo $assignment['id']; ?>"></label>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars(_dt($assignment['created_date'])); ?></td>
                                            <td class="text-center">
                                                <a href="#" class="delete-assignment" 
                                                data-id="<?php echo $assignment['id']; ?>"
                                                title="<?php echo _l('delete'); ?>">
                                                <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                            
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No staff-service assignments found.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- filter modal ends -->
<?php init_tail(); ?>

<script>
    $(function() {

        $('.table-auto_assign_leads').DataTable().destroy();
        initDataTable('.table-auto_assign_leads', admin_url + 'auto_assign_leads/table');

        let rules = {
            'staff_ids[]': 'required',
            'service_ids[]': 'required',
            'source_ids[]': 'required',
            'max_leads_to_distribute': 'required',
            'max_leads_for_each_staff': 'required',
        }

        appValidateForm($('#auto_assign__form'), rules);

    });

    $("body").on("change", 'input[name="lead_log_date"]', function() {

        $('.table-auto_assign_leads').DataTable().destroy();

        var ActivityLogServerParams = [];
        ActivityLogServerParams["lead_log_date"] = '[name="lead_log_date"]';

        initDataTable('.table-auto_assign_leads', admin_url + 'auto_assign_leads/table', [], [], ActivityLogServerParams, [1, 'desc']);
    });
    // JavaScript to open the modal when "New Filter" is clicked
    $(document).on('click', '#newFilterMenuItem', function(e) {
        e.preventDefault();
        $('#newFilterModal').modal('show');
    });
    // NEW: Handle status toggle change for assignments
$(document).on('change', '.assignment-status-toggle', function() {
    var assignmentId = $(this).data('id');
    var newStatus = $(this).is(':checked') ? 1 : 0;
    var $toggle = $(this); // Store reference to the toggle

    // Perform AJAX request to update the status in the database
    $.ajax({
        url: admin_url + 'auto_assign_leads/update_assignment_status',
        type: 'POST',
        data: {
            id: assignmentId,
            status: newStatus,
            // IMPORTANT: Include your CSRF token if your CI application uses it
            // '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json', // Expect JSON response from the controller
        success: function(response) {
            if (response.success) {
                alert_float('success', response.message);
            } else {
                alert_float('danger', response.message);
                // Revert the toggle state on AJAX error
                $toggle.prop('checked', !newStatus);
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'An error occurred: ' + error);
            // Revert the toggle state on AJAX error
            $toggle.prop('checked', !newStatus);
            console.error("AJAX Error:", status, error, xhr.responseText);
        }
    });
});
// delete
$(document).on('click', '.delete-assignment', function(e) {
    e.preventDefault();
    var assignmentId = $(this).data('id');
    var $row = $(this).closest('tr');
    
    // Confirmation dialog
    if (confirm('Are you sure you want to delete this assignment?')) {
        $.post(admin_url + 'auto_assign_leads/delete_assignment', {
            id: assignmentId,
            [csrfData.token_name]: csrfData.hash
        }, function(response) {
            response = JSON.parse(response);
            if (response.success) {
                // Remove row from table
                $row.remove();
                
                // Show success message
                alert_float('success', response.message);
                
                // If no more rows, show empty message
                if ($('#assignmentsInModalTable tbody tr').not('.no-assignments').length === 0) {
                    $('#assignmentsInModalTable tbody').html(
                        '<tr class="no-assignments"><td colspan="5" class="text-center">No staff-service assignments found.</td></tr>'
                    );
                }
            } else {
                alert_float('danger', response.message);
            }
        });
    }
});
$('#service_ids').on('change', function () {
    let serviceIds = $(this).val(); // array of selected IDs

    console.log('Selected Service IDs:', serviceIds); // Debug: selected service IDs

    if (!serviceIds || serviceIds.length === 0) {
        $('#staff_ids').empty().selectpicker('refresh');
        return;
    }

    $.ajax({
        url: admin_url + 'auto_assign_leads/get_staff_by_services',
        type: 'POST',
        data: { service_ids: serviceIds },
        dataType: 'json',
        success: function (response) {
            console.log('Staff Response from Server:', response); // Debug: check what server returned

            $('#staff_ids').empty();

            $.each(response, function (index, staff) {
                $('#staff_ids').append(
                    $('<option>', {
                        value: staff.staffid,
                        text: staff.staffname,
                        selected: true
                    })
                );
            });

            $('#staff_ids').selectpicker('refresh');
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error); // Debug AJAX error
            console.error('Server response:', xhr.responseText); // Show full server error if 500
        }
    });
});


 
</script>

<!-- more button for saved filters -->
<script>
jQuery(document).ready(function($) {
    $('#showMoreFilters').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.extra-filter').slideDown();
        $(this).parent().remove();
    });
});

</script>
