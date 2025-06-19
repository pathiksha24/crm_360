<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<div class="panel-group">
    <?php
        echo render_textarea('client_nationalities', 'client_nationalities', implode(',', json_decode(get_option('team_daily_form_client_nationalities'))));
        echo render_textarea('services', 'services', implode(',', json_decode(get_option('team_daily_form_services'))));
        echo render_textarea('sources', 'sources', implode(',', json_decode(get_option('team_daily_form_sources'))));
        echo render_textarea('cities', 'cities', implode(',', json_decode(get_option('team_daily_form_cities'))));
    ?>
</div>