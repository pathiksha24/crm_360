<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2 sm:tw-mb-4">
                    <a href="#" onclick="init_lead(); return false;"
                       class="btn btn-primary mright5 pull-left display-block">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_lead'); ?>
                    </a>
                    <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>"
                           class="btn btn-primary pull-left display-block hidden-xs">
                            <i class="fa-solid fa-upload tw-mr-1"></i>
                            <?php echo _l('import_leads'); ?>
                        </a>
                    <?php } ?>
                    <div class="row">
                        <div class="col-sm-5 ">
                            <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                               data-title="<?php echo _l('leads_summary'); ?>" data-placement="top"
                               onclick="slideToggle('.leads-overview'); return false;"><i
                                        class="fa fa-bar-chart"></i></a>
                            <a href="<?php echo admin_url('leads_customization/admin_leads/switch_kanban/' . $switch_kanban); ?>"
                               class="btn btn-default mleft5 hidden-xs" data-toggle="tooltip" data-placement="top"
                               data-title="<?php echo $switch_kanban == 1 ? _l('leads_switch_to_kanban') : _l('switch_to_list_view'); ?>">
                                <?php if ($switch_kanban == 1) { ?>
                                    <i class="fa-solid fa-grip-vertical"></i>
                                <?php } else { ?>
                                    <i class="fa-solid fa-table-list"></i>
                                <?php }; ?>
                            </a>
                        </div>
                        <div class="col-sm-4 col-xs-12 pull-right leads-search">
<!--                            --><?php //if ($this->session->userdata('leads_kanban_view') == 'true') { ?>
<!--                                <div data-toggle="tooltip" data-placement="top"-->
<!--                                     data-title="--><?php //echo _l('search_by_tags'); ?><!--">-->
<!--                                    --><?php //echo render_input('search', '', '', 'search', ['data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')], [], 'no-margin') ?>
<!--                                </div>-->
<!--                            --><?php //} ?>
                            <?php echo form_hidden('sort_type'); ?>
                            <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="hide leads-overview tw-mt-2 sm:tw-mt-4 tw-mb-4 sm:tw-mb-0">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <?php echo _l('leads_summary'); ?>
                        </h4>
                        <div class="tw-flex tw-flex-wrap tw-flex-col lg:tw-flex-row tw-w-full tw-gap-3 lg:tw-gap-6">
                            <?php
                            foreach ($summary as $status) { ?>
                                <div
                                        class="lg:tw-border-r lg:tw-border-solid lg:tw-border-neutral-300 tw-flex-1 tw-flex tw-items-center last:tw-border-r-0">
                                <span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
                                    <?php
                                    if (isset($status['percent'])) {
                                        echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                    } else {
                                        // Is regular status
                                        echo $status['total'];
                                    }
                                    ?>
                                </span>
                                    <span style="color:<?php echo $status['color']; ?>"
                                          class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>">
                                    <?php echo $status['name']; ?>
                                </span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>
                <div class="<?php echo $isKanBan ? '' : 'panel_s'; ?>">
                    <div class="<?php echo $isKanBan ? '' : 'panel-body'; ?>">
                        <div class="tab-content">
                            <?php
                            if ($isKanBan) { ?>
                                <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                    <div class="kanban-leads-sort">
                                        <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                                        <a href="#" onclick="leads_kanban_sort('dateadded'); return false"
                                           class="dateadded">
                                            <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_datecreated'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('leadorder');return false;"
                                           class="leadorder">
                                            <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                                        </a>
                                        |
                                        <a href="#" onclick="leads_kanban_sort('lastcontact');return false;"
                                           class="lastcontact">
                                            <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                                        </a>
                                    </div>
                                    <div class="row">
                                        <div class="container-fluid leads-kan-ban">
                                            <div id="kan-ban"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row" id="leads-table">
                                    <div class="col-md-12">
                                        <div class="row mbot5">
                                            <div class="col-md-12">
                                                <p class="bold"><?php echo _l('filter_by'); ?></p>
                                            </div>
<!--                                            --><?php //if (has_permission('leads', '', 'view')) { ?>
                                                <div class="col-md-3 leads-filter-column">
                                                    <?php echo render_select('view_assigned', $staff, ['staffid', ['firstname', 'lastname']], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned')], [], 'no-mbot'); ?>
                                                </div>
<!--                                            --><?php //} ?>
                                            <div class="col-md-3 leads-filter-column">
                                                <?php
                                                $selected = [];
                                                if ($this->input->get('status')) {
                                                    $selected[] = $this->input->get('status');
                                                } else {
                                                    foreach ($statuses as $key => $status) {
                                                        if ($status['isdefault'] == 0) {
                                                            $selected[] = $status['id'];
                                                        } else {
                                                            $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
                                                        }
                                                    }
                                                }
                                                echo '<div id="leads-filter-status">';
                                                echo render_select('view_status[]', $statuses, ['id', 'name'], '', $selected, ['data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true], [], 'no-mbot', '', false);
                                                echo '</div>';
                                                ?>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <?php
                                                echo render_select('view_source', $sources, ['id', 'name'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('leads_source')], [], 'no-mbot');
                                                ?>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <div class="select-placeholder">
                                                    <select name="custom_view"
                                                            title="<?php echo _l('additional_filters'); ?>"
                                                            id="custom_view"
                                                            class="selectpicker" data-width="100%">
                                                        <option value=""></option>
                                                        <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                                        <option value="junk"><?php echo _l('lead_junk'); ?></option>
                                                        <option value="public"><?php echo _l('lead_public'); ?></option>
                                                        <option value="contacted_today">
                                                            <?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                                        <option value="created_today"><?php echo _l('created_today'); ?>
                                                        </option>
                                                        <?php if (has_permission('leads', '', 'edit')) { ?>
                                                            <option value="not_assigned"><?php echo _l('leads_not_assigned'); ?>
                                                            </option>
                                                        <?php } ?>
                                                        <?php if (isset($consent_purposes)) { ?>
                                                            <optgroup label="<?php echo _l('gdpr_consent'); ?>">
                                                                <?php foreach ($consent_purposes as $purpose) { ?>
                                                                    <option value="consent_<?php echo $purpose['id']; ?>">
                                                                        <?php echo $purpose['name']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </optgroup>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 leads-filter-column">
                                                <?php
                                                echo render_select('view_service', $services, ['id', 'name'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services')], [], 'no-mbot');
                                                ?>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <?php
                                                echo render_select('view_language', $languages, ['id', 'name'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('languages')], [], 'no-mbot');
                                                ?>
                                            </div>
                                            <?php if (is_admin()){ ?>
                                            <div class="col-md-3 leads-filter-column">
                                                <?php
                                                $has_notes = [
                                                    ['id' => 1, 'value' => 'Yes'],
                                                    ['id' => 2, 'value' => 'No']
                                                ];
                                                echo render_select('has_notes', $has_notes, ['id', 'value'], '', '', ['data-width' => '100%', 'data-none-selected-text' => _l('has_notes')], [], 'no-mbot');
                                                ?>
                                            </div>
                                            <?php } ?>
                                            <div class="col-md-3 leads-filter-column hide">
                                                <div class="form-group" id="report-time">
                                                    <select class="selectpicker" name="months-report" data-width="100%"
                                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                        <option value="today"><?php echo _l('today'); ?></option>
                                                        <option value="" selected><?php echo _l('all'); ?></option>
                                                        <option value="this_week"><?php echo _l('this_week'); ?></option>
                                                        <option value="last_week"><?php echo _l('last_week'); ?></option>
                                                        <option value="this_month"><?php echo _l('this_month'); ?></option>
                                                        <option value="last_month"><?php echo _l('last_month'); ?></option>
                                                        <option value="this_year"><?php echo _l('this_year'); ?></option>
                                                        <option value="last_year"><?php echo _l('last_year'); ?></option>
                                                        <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                                                    </select>
                                                </div>
                                                <div id="date-range" class="hide mbot15">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <?php echo render_date_input('report-from','report_sales_from_date') ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <?php echo render_date_input('report-to','report_sales_to_date') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="hr-panel-separator"/>
                                    </div>
                                    <div class="clearfix"></div>

                                    <div class="col-md-12">
                                     
                                        <a href="#" data-toggle="modal" data-table=".table-leads_new"
                                           data-target="#leads_bulk_actions"
                                           class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                                        <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1"
                                             role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (has_permission('leads', '', 'delete')) { ?>
                                                            <div class="checkbox checkbox-danger">
                                                                <input type="checkbox" name="mass_delete"
                                                                       id="mass_delete">
                                                                <label
                                                                        for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                                            </div>
                                                            <hr class="mass_delete_separator"/>
                                                        <?php } ?>
                                                        <div id="bulk_change">
                                                            <div class="form-group">
                                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                                    <input type="checkbox" name="leads_bulk_mark_lost"
                                                                           id="leads_bulk_mark_lost" value="1">
                                                                    <label for="leads_bulk_mark_lost">
                                                                        <?php echo _l('lead_mark_as_lost'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <?php echo render_select('move_to_status_leads_bulk', $statuses, ['id', 'name'], 'ticket_single_change_status'); ?>
                                                            <?php
                                                            echo render_select('move_to_source_leads_bulk', $sources, ['id', 'name'], 'lead_source');
                                                            echo render_select('move_to_service_leads_bulk', $services, ['id', 'name'], 'lead_service');
                                                            echo render_select('move_to_language_leads_bulk', $languages, ['id', 'name'], 'lead_language');
                                                            echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                                            echo render_select('assign_to_leads_bulk', $staff, ['staffid', ['firstname', 'lastname']], 'leads_dt_assigned');
                                                            ?>
                                                            <div class="form-group">
                                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                                <input type="text" class="tagsinput" id="tags_bulk"
                                                                       name="tags_bulk" value="" data-role="tagsinput">
                                                            </div>
                                                            <hr/>
                                                            <div class="form-group no-mbot">
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                           id="leads_bulk_public" value="public">
                                                                    <label for="leads_bulk_public">
                                                                        <?php echo _l('lead_public'); ?>
                                                                    </label>
                                                                </div>
                                                                <div class="radio radio-primary radio-inline">
                                                                    <input type="radio" name="leads_bulk_visibility"
                                                                           id="leads_bulk_private" value="private">
                                                                    <label for="leads_bulk_private">
                                                                        <?php echo _l('private'); ?>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                                data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                        <a href="#" class="btn btn-primary"
                                                           onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->
                                        <?php

                                        $table_data = [];
                                        $_table_data = [
                                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads_new"><label></label></div>',
                                            [
                                                'name' => _l('the_number_sign'),
                                                'th_attrs' => ['class' => 'toggleable', 'id' => 'th-number'],
                                            ],
                                            [
                                                'name' => _l('leads_dt_name'),
                                                'th_attrs' => ['class' => 'toggleable', 'id' => 'th-name'],
                                            ],
                                        ];
                                        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                            $_table_data[] = [
                                                'name' => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                                'th_attrs' => ['id' => 'th-consent', 'class' => 'not-export'],
                                            ];
                                        }
                                        $_table_data[] = [
                                            'name' => _l('lead_company'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-company'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_email'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-email'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_phonenumber'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-phone'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_lead_value'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-value'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('tags'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-tags'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_assigned'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-assigned'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_status'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-status'],
                                        ];
                                        $_table_data[] = [
                                       'name' => 'Future Enquiry Date', 
                                        'th_attrs' => ['class' => 'toggleable', 'id' => 'th-future-enquiry'],
                                    ];
                                        $_table_data[] = [
                                            'name' => _l('leads_source'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-source'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_service'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-service'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_language'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-language'],
                                        ];
                                        // NEW: Nationality column header (comes from custom field leads_nationality)
                                        $_table_data[] = [
                                            'name' => 'Nationality',
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-nationality'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('whatsapp_number'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-whatsapp_number'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('dateassigned'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last-dateassigned'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_last_contact'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-last-contact'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('changeddate'),
                                            'th_attrs' => ['class' => 'toggleable', 'id' => 'th-changeddate'],
                                        ];
                                        $_table_data[] = [
                                            'name' => _l('leads_dt_datecreated'),
                                            'th_attrs' => ['class' => 'date-created toggleable', 'id' => 'th-date-created'],
                                        ];
                                        foreach ($_table_data as $_t) {
                                            array_push($table_data, $_t);
                                        }
                                        $custom_fields = get_custom_fields('leads', ['show_on_table' => 1]);
                                        foreach ($custom_fields as $field) {
                                            // Skip nationality – it has a dedicated column above
                                            if ($field['slug'] === 'leads_nationality') {
                                                continue;
                                            }
                                            array_push($table_data, [
                                                'name' => $field['name'],
                                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                                            ]);
                                        }
                                    //      $table_data[] = [
                                    //     'name' => _l('lead_notes'),
                                    //     'th_attrs' => ['class' => 'toggleable', 'id' => 'th-lead-notes'],
                                    // ];
                                        $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                                        ?>
                                        <div class="panel-table-full">
                                            <?php
                                            render_datatable(
                                                $table_data,
                                                'leads_new',
                                                ['customizable-table number-index-1'],
                                                [
                                                    'id' => 'table-leads_new',
                                                    'data-last-order-identifier' => 'leads',
                                                    'data-default-order' => get_table_last_order('leads'),
                                                ]
                                            );
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="hidden-columns-table-leads_new" type="text/json">
<?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads_new'); ?>



</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>


<!-- Future Enquiry Modal -->
<div class="modal fade" id="futureEnquiryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="futureEnquiryForm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title"><?php echo _l('Future enquiry'); ?></h4>
        </div>
        <div class="modal-body">
          <?php
            echo render_datetime_input(
            'future_enquiry_date',
            _l('Future Enquiry Date'), // Proper label text
            '',
            ['required' => true]
            );

          ?>
        
          <input type="hidden" name="leadid" id="fe_leadid">
          <input type="hidden" name="status"  id="fe_status" value="68">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
          <button type="submit" class="btn btn-primary" id="fe_save_btn"><?php echo _l('save'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php init_tail(); ?>
<script>
    var openLeadID = '<?php echo $leadid; ?>';
    // var is_admin = '</?php echo is_admin(); ?>';
    var is_admin = <?php echo is_admin() ? 'true' : 'false'; ?>;
    var CURRENT_STAFF_ID = parseInt('<?php echo (int) get_staff_user_id(); ?>', 10);
    var SHOW_COLS_FOR_STAFF = [58, 17, 174, 178, 56, 54];

    $(function () {
        
        leads_kanban();
        $('#leads_bulk_mark_lost').on('change', function () {
            $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
            $('#move_to_status_leads_bulk').selectpicker('refresh')
        });
        $('#move_to_status_leads_bulk').on('change', function () {
            if ($(this).selectpicker('val') != '') {
                $('#leads_bulk_mark_lost').prop('disabled', true);
                $('#leads_bulk_mark_lost').prop('checked', false);
            } else {
                $('#leads_bulk_mark_lost').prop('disabled', false);
            }
        });

        let report_from = $('input[name="report-from"]');
        let report_to = $('input[name="report-to"]');
        let date_range = $('#date-range');

        let LeadsServerParams = {
            custom_view: "[name='custom_view']",
            assigned: "[name='view_assigned']",
            status: "[name='view_status[]']",
            source: "[name='view_source']",
            service: "[name='view_service']",
            language: "[name='view_language']",
            has_notes: "[name='has_notes']",
            report_months: '[name="months-report"]',
            report_from: '[name="report-from"]',
            report_to: '[name="report-to"]',
        };
        let table_leads = $("table.table-leads_new");
        var leadsTableNotSortable = [0];
        var leadsTableNotSearchable = [0, table_leads.find("#th-assigned").index()];
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_leads(LeadsServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_leads(LeadsServerParams);
            }
        });
        $('select[name="months-report"]').on('change', function() {
            var val = $(this).val();
            report_to.attr('disabled', true);
            report_to.val('');
            report_from.val('');
            if (val == 'custom') {
                date_range.addClass('fadeIn').removeClass('hide');
                return;
            } else {
                if (!date_range.hasClass('hide')) {
                    date_range.removeClass('fadeIn').addClass('hide');
                }
            }
            gen_leads(LeadsServerParams);
        });
        initDataTable(
            table_leads,
            admin_url + "leads_customization/admin_leads/table",
            leadsTableNotSearchable,
            leadsTableNotSortable,
            LeadsServerParams,
            [table_leads.find("th.date-created").index(), "desc"]
        );
        $.each(LeadsServerParams, function (i, obj) {
            if (obj != '[name="months-report"]' && obj != '[name="report-from"]' && obj != '[name="report-to"]'){
                $("select" + obj).on("change", function () {
                    $("[name='view_status[]']")
                        .prop("disabled", $(this).val() == "lost" || $(this).val() == "junk")
                        .selectpicker("refresh");

                    table_leads.DataTable().ajax.reload();
                });
            }
        });

        function gen_leads(fnServerParams){
            $('.table.table-leads_new').DataTable().destroy();
            initDataTable('.table.table-leads_new', admin_url + 'leads_customization/admin_leads/table', leadsTableNotSearchable, leadsTableNotSortable, fnServerParams, [table_leads.find("th.date-created").index(), "desc"]);
        }
    });
$(function () {
  $('#futureEnquiryForm').on('submit', function (e) {
    e.preventDefault();

    var $btn = $('#fe_save_btn').prop('disabled', true);
    var payload = {
      leadid: $('#fe_leadid').val(),
      status:  $('#fe_status').val(), // 68
      future_enquiry_date: $('input[name="future_enquiry_date"]').val(),
    };

    if (!payload.future_enquiry_date) {
      alert_float('warning', "<?php echo _l('please_select_date'); ?>");
      $btn.prop('disabled', false);
      return;
    }

    $.post(admin_url + "leads_customization/admin_leads/mark_future_enquiry", payload)
      .done(function () {
        $('#futureEnquiryModal').modal('hide');
        $('input[name="future_enquiry_date"]').val('');
        $("table.table-leads_new").DataTable().ajax.reload(null, false);
        alert_float('success', "<?php echo _l('updated_successfully'); ?>");
      })
      .fail(function (xhr) {
        alert_float('danger', xhr.responseText || 'Error');
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });
});

    function init_lead(id, isEdit) {
        if ($("#task-modal").is(":visible")) {
            $("#task-modal").modal("hide");
        }
        // In case header error
        if (init_lead_modal_data(id, undefined, isEdit)) {
            $("#lead-modal").modal("show");
        }
    }

    // function lead_mark_as(status_id, lead_id) {
    //     var data = {};
    //     let table_leads = $("table.table-leads_new");
    //     data.status = status_id;
    //     data.leadid = lead_id;
    //     $.post(admin_url + "leads/update_lead_status", data).done(function (
    //         response
    //     ) {
    //         table_leads.DataTable().ajax.reload(null, false);
    //     });
    // }
        function lead_mark_as(status_id, lead_id) {
        let table_leads = $("table.table-leads_new");
        // If "Future Enquiry" (id 68) -> open modal instead of default POST
        if (parseInt(status_id, 10) === 68) {
            $('#fe_leadid').val(lead_id);
            $('#fe_status').val(status_id);
            $('#futureEnquiryModal').modal('show');
            return;
        }
        // Otherwise use the default status update
        var data = { status: status_id, 
            leadid: lead_id };
        $.post(admin_url + "leads/update_lead_status", data).done(function () {
            table_leads.DataTable().ajax.reload(null, false);
        });
        }

    function init_lead_modal_data(id, url, isEdit) {
        var requestURL =
            (typeof url != "undefined" ? url : "leads/lead/") +
            (typeof id != "undefined" ? id : "");

        if (isEdit === true) {
            var concat = "?";
            if (requestURL.indexOf("?") > -1) {
                concat += "&";
            }
            requestURL += concat + "edit=true";
        }

        requestGetJSON(requestURL)
            .done(function (response) {
                _lead_init_data(response, id);
                validate_lead_customization_form();
            })
            .fail(function (data) {
                alert_float("danger", data.responseText);
            });
    }

    function leads_bulk_action(event) {
        let table_leads = $("table.table-leads_new");
        if (confirm_delete()) {
            var mass_delete = $("#mass_delete").prop("checked");
            var ids = [];
            var data = {};
            if (mass_delete == false || typeof mass_delete == "undefined") {
                data.lost = $("#leads_bulk_mark_lost").prop("checked");
                data.status = $("#move_to_status_leads_bulk").val();
                data.service = $("#move_to_service_leads_bulk").val();
                data.language = $("#move_to_language_leads_bulk").val();
                data.assigned = $("#assign_to_leads_bulk").val();
                data.source = $("#move_to_source_leads_bulk").val();
                data.last_contact = $("#leads_bulk_last_contact").val();
                data.tags = $("#tags_bulk").tagit("assignedTags");
                data.visibility = $('input[name="leads_bulk_visibility"]:checked').val();

                data.assigned = typeof data.assigned == "undefined" ? "" : data.assigned;
                data.visibility =
                    typeof data.visibility == "undefined" ? "" : data.visibility;

                if (
                    data.status === "" &&
                    data.lost === false &&
                    data.assigned === "" &&
                    data.source === "" &&
                    data.service === "" &&
                    data.language === "" &&
                    data.last_contact === "" &&
                    data.tags.length == 0 &&
                    data.visibility === ""
                ) {
                    return;
                }
            } else {
                data.mass_delete = true;
            }
            var rows = table_leads.find("tbody tr");
            $.each(rows, function () {
                var checkbox = $($(this).find("td").eq(0)).find("input");
                if (checkbox.prop("checked") === true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass("disabled");
            setTimeout(function () {
                $.post(admin_url + "leads_customization/admin_leads/bulk_action", data)
                    .done(function () {
                        window.location.reload();
                    })
                    .fail(function (data) {
                        $("#lead-modal").modal("hide");
                        alert_float("danger", data.responseText);
                    });
            }, 200);
        }
    }

    function initDataTable(selector, url, notsearchable, notsortable, fnserverparams, defaultorder) {
        var table = typeof selector == "string" ? $("body").find("table" + selector) : selector;

        if (table.length === 0) {
            return false;
        }

        fnserverparams = fnserverparams == "undefined" || typeof fnserverparams == "undefined" ? [] : fnserverparams;
        // If not order is passed order by the first column
        if (typeof defaultorder == "undefined") {
            defaultorder = [[0, "asc"]];
        }
        else {
            if (defaultorder.length === 1) {
                defaultorder = [defaultorder];
            }
        }

        var user_table_default_order = table.attr("data-default-order");

        if (!empty(user_table_default_order)) {
            var tmp_new_default_order = JSON.parse(user_table_default_order);
            var new_defaultorder = [];
            for (var i in tmp_new_default_order) {
                // If the order index do not exists will throw errors
                if (
                    table.find("thead th:eq(" + tmp_new_default_order[i][0] + ")").length >
                    0
                ) {
                    new_defaultorder.push(tmp_new_default_order[i]);
                }
            }
            if (new_defaultorder.length > 0) {
                defaultorder = new_defaultorder;
            }
        }

        var length_options = [10, 25, 50, 100, 500];
        var length_options_names = [10, 25, 50, 100, 500];

        app.options.tables_pagination_limit = parseFloat(
            app.options.tables_pagination_limit
        );

        if ($.inArray(app.options.tables_pagination_limit, length_options) == -1) {
            length_options.push(app.options.tables_pagination_limit);
            length_options_names.push(app.options.tables_pagination_limit);
        }

        length_options.sort(function (a, b) {
            return a - b;
        });
        length_options_names.sort(function (a, b) {
            return a - b;
        });

        length_options.push(-1);
        length_options_names.push(app.lang.dt_length_menu_all);
        var tableButtons = $("body").find(".table-btn");
        var table_buttons_options = [];

        if (is_admin){
            var formatExport = {
                body: function (data, row, column, node) {
                    // Fix for notes inline datatables
                    // Causing issues because of the hidden textarea for edit and the content is duplicating
                    // This logic may be extended in future for other similar fixes
                    var newTmpRow = $("<div></div>", data);
                    newTmpRow.append(data);

                    if (newTmpRow.find("[data-note-edit-textarea]").length > 0) {
                        newTmpRow.find("[data-note-edit-textarea]").remove();
                        data = newTmpRow.html().trim();
                    }
                    // Convert e.q. two months ago to actual date
                    var exportTextHasActionDate = newTmpRow.find(".text-has-action.is-date");

                    if (exportTextHasActionDate.length) {
                        data = exportTextHasActionDate.attr("data-title");
                    }

                    if (newTmpRow.find(".row-options").length > 0) {
                        newTmpRow.find(".row-options").remove();
                        data = newTmpRow.html().trim();
                    }

                    if (newTmpRow.find(".table-export-exclude").length > 0) {
                        newTmpRow.find(".table-export-exclude").remove();
                        data = newTmpRow.html().trim();
                    }

                    if (data) {
                        /*       // 300,00 becomes 300.00 because excel does not support decimal as coma
                                var regexFixExcelExport = new RegExp("([0-9]{1,3})(,)([0-9]{" + app.options.decimal_places + ',' + app.options.decimal_places + "})", "gm");
                                // Convert to string because matchAll won't work on integers in case datatables convert the text to integer
                                var _stringData = data.toString();
                                var found = _stringData.matchAll(regexFixExcelExport);
                                if (found) {
                                    data = data.replace(regexFixExcelExport, "$1.$3");
                                }*/
                    }

                    // Datatables use the same implementation to strip the html.
                    var div = document.createElement("div");
                    div.innerHTML = data;
                    var text = div.textContent || div.innerText || "";

                    return text.trim();
                },
            };

            if (
                typeof table_export_button_is_hidden != "function" ||
                !table_export_button_is_hidden()
            ) {
                table_buttons_options.push({
                    extend: "collection",
                    text: app.lang.dt_button_export,
                    className: "btn btn-sm btn-default-dt-options",
                    buttons: [
                        {
                            extend: "excel",
                            text: app.lang.dt_button_excel,
                            footer: true,
                            exportOptions: {
                                columns: [":not(.not-export)"],
                                rows: function (index) {
                                    return _dt_maybe_export_only_selected_rows(index, table);
                                },
                                format: formatExport,
                            },
                        },
                        {
                            extend: "csvHtml5",
                            text: app.lang.dt_button_csv,
                            footer: true,
                            exportOptions: {
                                columns: [":not(.not-export)"],
                                rows: function (index) {
                                    return _dt_maybe_export_only_selected_rows(index, table);
                                },
                                format: formatExport,
                            },
                        },
                        {
                            extend: "pdfHtml5",
                            text: app.lang.dt_button_pdf,
                            footer: true,
                            exportOptions: {
                                columns: [":not(.not-export)"],
                                rows: function (index) {
                                    return _dt_maybe_export_only_selected_rows(index, table);
                                },
                                format: formatExport,
                            },
                            orientation: "landscape",
                            customize: function (doc) {
                                // Fix for column widths
                                var table_api = $(table).DataTable();
                                var columns = table_api.columns().visible();
                                var columns_total = columns.length;
                                var total_visible_columns = 0;

                                for (i = 0; i < columns_total; i++) {
                                    // Is only visible column
                                    if (columns[i] == true) {
                                        total_visible_columns++;
                                    }
                                }

                                setTimeout(function () {
                                    if (total_visible_columns <= 5) {
                                        var pdf_widths = [];
                                        for (i = 0; i < total_visible_columns; i++) {
                                            pdf_widths.push(735 / total_visible_columns);
                                        }

                                        doc.content[1].table.widths = pdf_widths;
                                    }
                                }, 10);

                                if (
                                    app.user_language.toLowerCase() == "persian" ||
                                    app.user_language.toLowerCase() == "arabic"
                                ) {
                                    doc.defaultStyle.font = Object.keys(pdfMake.fonts)[0];
                                }

                                doc.styles.tableHeader.alignment = "left";
                                doc.defaultStyle.fontSize = 10;

                                doc.styles.tableHeader.fontSize = 10;
                                doc.styles.tableHeader.margin = [3, 3, 3, 3];

                                doc.styles.tableFooter.fontSize = 10;
                                doc.styles.tableFooter.margin = [3, 0, 0, 0];

                                doc.pageMargins = [2, 20, 2, 20];
                            },
                        },
                        {
                            extend: "print",
                            text: app.lang.dt_button_print,
                            footer: true,
                            exportOptions: {
                                columns: [":not(.not-export)"],
                                rows: function (index) {
                                    return _dt_maybe_export_only_selected_rows(index, table);
                                },
                                format: formatExport,
                            },
                        },
                    ],
                });
            }
        }
        $.each(tableButtons, function () {
            var b = $(this);
            if (b.length && b.attr("data-table")) {
                if ($(table).is(b.attr("data-table"))) {
                    table_buttons_options.push({
                        text: b.text().trim(),
                        className: "btn btn-sm btn-default-dt-options",
                        action: function (e, dt, node, config) {
                            b.click();
                        },
                    });
                }
            }
        });

        if (!$(table).hasClass("dt-inline")) {
            table_buttons_options.push({
                text: '<i class="fa fa-refresh"></i>',
                className: "btn btn-sm btn-default-dt-options btn-dt-reload",
                action: function (e, dt, node, config) {
                    dt.ajax.reload();
                },
            });
        }
        var dtSettings = {
            language: app.lang.datatables,
            processing: true,
            retrieve: true,
            serverSide: true,
            paginate: true,
            searchDelay: 750,
            bDeferRender: true,
            autoWidth: false,
            dom: "<'row'><'row'<'col-md-7'lB><'col-md-5'f>>rt<'row'<'col-md-4'i><'col-md-8 dataTables_paging'<'#colvis'><'.dt-page-jump'>p>>",
            pageLength: app.options.tables_pagination_limit,
            lengthMenu: [length_options, length_options_names],
            columnDefs: [
                {
                    searchable: false,
                    targets: notsearchable,
                },
                {
                    sortable: false,
                    targets: notsortable,
                },
            ],
            fnDrawCallback: function (oSettings) {
                _table_jump_to_page(this, oSettings);
                if (oSettings.aoData.length === 0) {
                    $(oSettings.nTableWrapper).addClass("app_dt_empty");
                } else {
                    $(oSettings.nTableWrapper).removeClass("app_dt_empty");
                }
            },
            fnCreatedRow: function (nRow, aData, iDataIndex) {
                // If tooltips found
                $(nRow).attr("data-title", aData.Data_Title);
                $(nRow).attr("data-toggle", aData.Data_Toggle);
            },
            initComplete: function (settings, json) {
                var t = this;
                var $btnReload = $(".btn-dt-reload");
                $btnReload.attr("data-toggle", "tooltip");
                $btnReload.attr("title", app.lang.dt_button_reload);
// date assigned , last contact , changed sttaus from new , created , tags visible only for call center 1 anju,durga,call cnter 5,poloumy,julin starts
                // Run after built-ins settle
                setTimeout(function () { enforceVisibilityRule(t); }, 120);
                // Re-apply on every draw (search, paginate, ajax reload, etc.)
                $(t).on('draw.dt', function () {
                setTimeout(function () { enforceVisibilityRule($(t)); }, 0);
                });
                // Re-apply after any column visibility changes
                $(t).on('column-visibility.dt', function () {
                setTimeout(function () { enforceVisibilityRule($(t)); }, 0);
                });
// date assigned , last contact , changed sttaus from new , created , tags visible only for call center 1 anju,durga,call cnter 5,poloumy,julin ends
                var $btnColVis = $(".dt-column-visibility");
                $btnColVis.attr("data-toggle", "tooltip");
                $btnColVis.attr("title", app.lang.dt_button_column_visibility);

                t.wrap('<div class="table-responsive"></div>');

                var dtEmpty = t.find(".dataTables_empty");
                if (dtEmpty.length) {
                    dtEmpty.attr("colspan", t.find("thead th").length);
                }

                // Hide mass selection because causing issue on small devices
                if (
                    is_mobile() &&
                    $(window).width() < 400 &&
                    t.find('tbody td:first-child input[type="checkbox"]').length > 0
                ) {
                    t.DataTable().column(0).visible(false, false).columns.adjust();
                    $("a[data-target*='bulk_actions']").addClass("hide");
                }

                t.parents(".table-loading").removeClass("table-loading");
                t.removeClass("dt-table-loading");
                var th_last_child = t.find("thead th:last-child");
                var th_first_child = t.find("thead th:first-child");
                if (th_last_child.text().trim() == app.lang.options) {
                    th_last_child.addClass("not-export");
                }
                if (th_first_child.find('input[type="checkbox"]').length > 0) {
                    th_first_child.addClass("not-export");
                }
                mainWrapperHeightFix();
            },
            order: defaultorder,
            ajax: {
                url: url,
                type: "POST",
                data: function (d) {
                    if (Array.isArray(d.order)) {
                        d.order = d.order.map(function (order) {
                            var tHead = table.find("thead th:eq(" + order.column + ")");
                            if (tHead.length > 0) {
                                if (tHead[0].dataset.customField == 1) {
                                    order.type = tHead[0].dataset.type;
                                }
                            }
                            return order;
                        });
                    }

                    if (typeof csrfData !== "undefined") {
                        d[csrfData["token_name"]] = csrfData["hash"];
                    }
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                    if (table.attr("data-last-order-identifier")) {
                        d["last_order_identifier"] = table.attr("data-last-order-identifier");
                    }
                },
            },
            buttons: table_buttons_options,
        };


// date assigned , last contact , changed sttaus from new , created , tags visible only for call center 1 anju,durga,call cnter 5,poloumy,julin starts
function enforceVisibilityRule(t) {

 if (SHOW_COLS_FOR_STAFF.indexOf(CURRENT_STAFF_ID) !== -1) return;

  var $table = t && t.jquery ? t : $(t);
  var api = $table.DataTable ? $table.DataTable() : $(t).DataTable();

  var idsToHide = [
    '#th-last-dateassigned', // Date Assigned
    '#th-last-contact',      // Last Contact
    '#th-changeddate',       // Changed Status from New
    '#th-date-created',      // Created
    '#th-tags'               // Tags
  ];

  idsToHide.forEach(function (sel) {
    var $th = $table.find('thead th' + sel);
    if ($th.length) {
      // Use the header node directly to select the column (avoids index mismatch)
      api.column($th).visible(false, false);
    }
  });

  api.columns.adjust();
}
// date assigned , last contact , changed sttaus from new , created , tags visible only for call center 1 anju,durga,call cnter 5,poloumy,julin ends

table = table.dataTable(dtSettings);
        var tableApi = table.DataTable();

        var hiddenHeadings = table.find("th.not_visible");
        var hiddenIndexes = [];

        $.each(hiddenHeadings, function () {
            hiddenIndexes.push(this.cellIndex);
        });

        setTimeout(function () {
            for (var i in hiddenIndexes) {
                tableApi.columns(hiddenIndexes[i]).visible(false, false).columns.adjust();
            }
        }, 10);

        if (table.hasClass("customizable-table")) {
            var tableToggleAbleHeadings = table.find("th.toggleable");
            var invisible = $("#hidden-columns-" + table.attr("id"));
            try {
                invisible = JSON.parse(invisible.text());
            } catch (err) {
                invisible = [];
            }

            $.each(tableToggleAbleHeadings, function () {
                var cID = $(this).attr("id");
                if ($.inArray(cID, invisible) > -1) {
                    tableApi.column("#" + cID).visible(false);
                }
            });

            // For for not blurring out when clicked on the link
            // Causing issues hidden column still to be shown as not hidden because the link is focused
            /* $('body').on('click', '.buttons-columnVisibility a', function() {
                     $(this).blur();
                 });*/
            /*
                        table.on('column-visibility.dt', function(e, settings, column, state) {
                            var hidden = [];
                            $.each(tableApi.columns()[0], function() {
                                var visible = tableApi.column($(this)).visible();
                                var columnHeader = $(tableApi.column($(this)).header());
                                if (columnHeader.hasClass('toggleable')) {
                                    if (!visible) {
                                        hidden.push(columnHeader.attr('id'))
                                    }
                                }
                            });
                            var data = {};
                            data.id = table.attr('id');
                            data.hidden = hidden;
                            if (data.id) {
                                $.post(admin_url + 'staff/save_hidden_table_columns', data).fail(function(data) {
                                    // Demo usage, prevent multiple alerts
                                    if ($('body').find('.float-alert').length === 0) {
                                        alert_float('danger', data.responseText);
                                    }
                                });
                            } else {
                                console.error('Table that have ability to show/hide columns must have an ID');
                            }
                        });*/
        }

        // Fix for hidden tables colspan not correct if the table is empty
        if (table.is(":hidden")) {
            table
                .find(".dataTables_empty")
                .attr("colspan", table.find("thead th").length);
        }

        table.on("preXhr.dt", function (e, settings, data) {
            if (settings.jqXHR) settings.jqXHR.abort();
        });

        return tableApi;
    }

// ==== PHONE FIELD uae strating with 0 or 971 accepting exact 9 char starts
(function () {
  const sel = 'input[name="phonenumber"]';

  function updateTitle($el, value) {
    if (/^(\+?971|0)/.test(value)) {
      $el.attr('title', 'If number starts with +971/971 or 0, it must have exactly 9 digits after the prefix.');
    } else {
      $el.attr('title', 'Enter a valid phone number with country code (e.g., +44XXXXXXX or +91XXXXXXX).');
    }
  }

  function setup($el) {
    $el.attr({
      type: 'tel',
      inputmode: 'numeric',
      autocomplete: 'tel',
      pattern: '^\\+?\\d{5,15}$',
    });
    updateTitle($el, $el.val() || '');
  }

  function clamp(value) {
    value = value.replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '');
    const match971 = value.match(/^(\+?971)(\d*)$/);
    if (match971) return match971[1] + match971[2].slice(0, 9);
    const match0 = value.match(/^(0)(\d*)$/);
    if (match0) return match0[1] + match0[2].slice(0, 9);
    return value;
  }

  // Apply logic only for phone number field
  $(document)
    .on('focus', sel, function () {
      setup($(this));
    })
    .on('input', sel, function () {
      const before = this.value;
      const after = clamp(before);
      if (before !== after) this.value = after;
      updateTitle($(this), this.value);
    })
    .on('blur', sel, function () {
      const val = this.value.trim();
      if (/^(\+?971)\d*$/.test(val) && !/^(\+?971)\d{9}$/.test(val)) {
        alert_float('warning', 'If number starts with +971/971, it must have exactly 9 digits after the prefix.');
        return;
      }
      if (/^(0)\d*$/.test(val) && !/^0\d{9}$/.test(val)) {
        alert_float('warning', 'If number starts with 0, it must have exactly 9 digits after the prefix.');
        return;
      }
    });

  // Initialize immediately for existing phone field
  setup($(sel));
})();
// ==== PHONE FIELD uae strating with 0 or 971 accepting exact 9 char ends
</script>
</body>

</html>