<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="#" onclick="new_language(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_language'); ?>
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php if (count($languages) > 0) { ?>
                            <table class="table dt-table" data-order-col="1" data-order-type="asc">
                                <thead>
                                <th><?php echo _l('id'); ?></th>
                                <th><?php echo _l('name'); ?></th>
                                <th><?php echo _l('options'); ?></th>
                                </thead>
                                <tbody>
                                <?php foreach ($languages as $language) { ?>
                                    <tr>
                                        <td>
                                            <?php echo $language['id']; ?>
                                        </td>
                                        <td>
                                            <a href="#"
                                               onclick="edit_language(this,<?php echo $language['id']; ?>);return false;"
                                               data-name="<?php echo $language['name']; ?>"><?php echo $language['name']; ?></a>
                                            <br />
                                        </td>
                                        <td>
                                            <div class="tw-flex tw-items-center tw-space-x-3">
                                                <a href="#"
                                                   onclick="edit_language(this,<?php echo $language['id']; ?>);return false;"
                                                   data-name="<?php echo $language['name']; ?>"
                                                   class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                                                    <i class="fa-regular fa-pen-to-square fa-lg"></i>
                                                </a>
                                                <a href="<?php echo admin_url('leads_customization/delete_language/' . $language['id']); ?>"
                                                   class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                                                    <i class="fa-regular fa-trash-can fa-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="no-margin"><?php echo _l('language_not_found'); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once(APPPATH . '../modules/leads_customization/views/language.php'); ?>
<?php init_tail(); ?>
</body>

</html>