<?php

defined('BASEPATH') or exit('No direct script access allowed');
/** @var string $title */

init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo $title ?></h4>
				<?php
				if ( ! $is_activated)
				{ ?>
					<?php
					echo form_open(
						admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'),
						['id' => 'zegaware-activate-license']
					); ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <input type="hidden" id="license_type" name="license_type" value="active">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_name" class="control-label"><?php echo _l('zegaware_customer_name') ?>*</label>
                                        <input type="text" id="customer_name" name="customer_name" class="form-control"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_email" class="control-label"><?php echo _l('zegaware_customer_email') ?>*</label>
                                        <input type="email" id="customer_email" name="customer_email"
                                               class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_envato_username" class="control-label"><?php echo _l('zegaware_customer_envato_username') ?></label>
                                        <input type="text" id="customer_envato_username" name="customer_envato_username"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="license_key" class="control-label"><?php echo _l('zegaware_license_key') ?>*</label>
                                        <input type="text" id="license_key" name="license_key" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="panel-footer text-right">
                            <button class="btn btn-primary" type="submit">
								<?php
								echo _l('zegaware_license_activate'); ?>
                            </button>
                        </div>
                    </div>
					<?php
					echo form_close(); ?>
					<?php
				} else
				{ ?>
					<?php
					echo form_open(
						admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/license'),
						['id' => 'zegaware-remove-license']
					); ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <input type="hidden" id="license_type" name="license_type" value="remove">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <strong class="control-label"><?php echo _l('zegaware_your_name') ?>:</strong>
                                        <span><?php echo $license_name ?></span>
                                    </div>
                                    <div class="form-group">
                                        <strong class="control-label"><?php echo _l('zegaware_your_email') ?>:</strong>
                                        <span><?php echo $license_email ?></span>
                                    </div>
                                    <div class="form-group">
                                        <strong class="control-label"><?php echo _l('zegaware_your_license') ?>
                                            :</strong>
                                        <span><?php echo $license_key ?></span>
                                    </div>
                                    <div class="form-group">
                                        <strong class="control-label"><?php echo _l('zegaware_activated_at') ?>
                                            :</strong>
                                        <span><?php echo $activated_date ? $activated_date->format(
												'Y-m-d H:i:s'
											) : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer text-right">
                            <input type="hidden" name="type" value="remove"/>
                            <button class="btn btn-danger" type="submit">
								<?php
								echo _l('zegaware_remove_license'); ?>
                            </button>
                        </div>
                    </div>

					<?php
					echo form_close(); ?>

					<?php
				} ?>
            </div>
        </div>
    </div>
</div>
<?php
init_tail(); ?>
</body>

</html>