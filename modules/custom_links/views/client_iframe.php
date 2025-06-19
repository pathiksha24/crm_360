<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
	<div class="col-md-12 section-custom-links-iframe">
        <div class="panel_s">
            <div class="panel-body">
                <iframe id="custom-links-iframe" title="<?php echo html_escape($link['title']); ?>" width="100%" height="700" src="<?php echo html_escape($href); ?>">
                </iframe>
            </div>
        </div>
    </div>
</div>
