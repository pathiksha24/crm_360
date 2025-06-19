<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php if (has_permission('custom_links', '', 'create') || (has_permission('custom_links', '', 'edit') && isset($link))) { ?>
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin inline-block">
                                <?php
                                if (isset($link))
                                    echo html_escape(_l('mcl_edit_custom_links', $link['title']));
                                else
                                    echo html_escape(_l('mcl_add_custom_links'));
                                ?>
                            </h4>
                            <hr class="hr-panel-heading"/>
                            <?php echo form_open('', ["method" => "post", "id" => "custom_links_form"]); ?>
                            <?php if (isset($link)) {
                                echo form_hidden('id', $link['id']);
                            } ?>

                            <div class="form-group">
                                <label for="main_setup0"><?php echo _l('mcl_select_menu'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="main_setup" id="main_setup0"
                                           value="0" <?php if (isset($link) && $link['main_setup'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="main_setup0"><?php echo _l('mcl_main_menu'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="main_setup" id="main_setup1"
                                           value="1" <?php if (isset($link) && $link['main_setup'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="main_setup1"><?php echo _l('mcl_setup_menu'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="main_setup" id="main_setup2"
                                           value="2" <?php if (isset($link) && $link['main_setup'] == "2") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="main_setup2"><?php echo _l('mcl_client_menu'); ?></label>
                                </div>
                            </div>
                            <?php
                            $value = isset($link) ? $link['title'] : '';
                            echo render_input('title', _l('mcl_link_title'), $value);
                            ?>

                            <?php
                            $value = isset($link) ? $link['parent_id'] : '';
                            ?>

                            <div class="form-group main_menu_items hide" app-field-wrapper="parent_id">
                                <label for="main_parent_id" class="control-label"><?php echo _l('mcl_parent_menu'); ?></label>
                                <select id="main_parent_id" name="parent_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true" tabindex="-98" disabled>
                                        <option value=""></option>
                                    <?php foreach($main_menu_items as $menu_item){ ?>
                                        <option value="<?php echo html_escape($menu_item['slug']); ?>" <?php if($value == $menu_item['slug']) echo 'selected'; ?>><?php echo html_escape($menu_item['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group setup_menu_items hide" app-field-wrapper="parent_id">
                                <label for="setup_parent_id" class="control-label"><?php echo _l('mcl_parent_menu'); ?></label>
                                <select id="setup_parent_id" name="parent_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true" tabindex="-98" disabled>
                                        <option value=""></option>
                                    <?php foreach($setup_menu_items as $menu_item){ ?>
                                        <option value="<?php echo html_escape($menu_item['slug']); ?>" <?php if($value == $menu_item['slug']) echo 'selected'; ?>><?php echo html_escape($menu_item['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="external_internal0"><?php echo _l('mcl_external_internal'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="external_internal" id="external_internal0"
                                           value="0" <?php if (isset($link) && $link['external_internal'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="external_internal0"><?php echo _l('mcl_internal_link'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="external_internal" id="external_internal1"
                                           value="1" <?php if (isset($link) && $link['external_internal'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="external_internal1"><?php echo _l('mcl_external_link'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="external_internal" id="external_internal2"
                                           value="2" <?php if (isset($link) && $link['external_internal'] == "2") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="external_internal2"><?php echo _l('mcl_hash_link'); ?></label>
                                </div>
                            </div>

                            <div class="form-group http_protocol hide">
                                <label for="http_protocol0"><?php echo _l('mcl_http_protocol'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="http_protocol" id="http_protocol0"
                                           value="0" <?php if (isset($link) && $link['http_protocol'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="http_protocol0"><?php echo _l('mcl_http'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="http_protocol" id="http_protocol1"
                                           value="1" <?php if (isset($link) && $link['http_protocol'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="http_protocol1"><?php echo _l('mcl_https'); ?></label>
                                </div>
                            </div>
                            <?php
                            $value = isset($link) ? $link['href'] : '';
                            ?>
                            <div class="form-group form_link hide" app-field-wrapper="href">
                                <label for="href"><?php echo _l('mcl_link'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><span
                                                id="internal_link_prefix"><?php echo html_escape(site_url()); ?></span><span
                                                id="external_link_prefix" class="hide">http://</span></span>
                                    <input type="text" name="href" class="form-control" id="href"
                                           value="<?php echo html_escape($value); ?>">
                                </div>
                            </div>
                            <?php
                            $value = isset($link) ? $link['position'] : '';
                            echo render_input('position', _l('mcl_position'), $value);
                            ?>

                            <?php
                            $value = isset($link) ? $link['badge'] : '';
                            echo render_input('badge', _l('mcl_badge'), $value, 'text', ["maxlength" => "63"], [], ' form-field-badge hide');
                            ?>

                            <div class="form-field-badge hide">
                                <?php
                                $value = isset($link) ? $link['badge_color'] : '';
                                echo render_color_picker('badge_color', _l('mcl_badge_color'), $value);
                                ?>
                            </div>

                            <?php
                            $value = isset($link) ? $link['icon'] : '';
                            ?>
                            <div class="form-group form-icon hide">
                                <label for="icon-new"><?php echo _l('mcl_icon'); ?></label>
                                <div class="input-group">
                                    <input type="text" name="icon" value="<?php echo html_escape($value); ?>"
                                           class="form-control icon-picker" id="icon-new">
                                    <span class="input-group-addon"><i class="fa"></i></span>
                                </div>
                            </div>

                            <div class="form-group form-show-iframe">
                                <label for="show_in_iframe0"><?php echo _l('mcl_show_in_iframe'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="show_in_iframe" id="show_in_iframe1"
                                           value="1" <?php if (isset($link) && $link['show_in_iframe'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="show_in_iframe1"><?php echo _l('settings_yes'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="show_in_iframe" id="show_in_iframe0"
                                           value="0" <?php if (isset($link) && $link['show_in_iframe'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="show_in_iframe0"><?php echo _l('settings_no'); ?></label>
                                </div>
                            </div>

                            <div class="form-group form-blank hide">
                                <label for="open_in_blank0"><?php echo _l('mcl_open_in_new_window'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="open_in_blank" id="open_in_blank1"
                                           value="1" <?php if (isset($link) && $link['open_in_blank'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="open_in_blank1"><?php echo _l('settings_yes'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="open_in_blank" id="open_in_blank0"
                                           value="0" <?php if (isset($link) && $link['open_in_blank'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="open_in_blank0"><?php echo _l('settings_no'); ?></label>
                                </div>
                            </div>

                            <div class="form-group form-require-login hide">
                                <label for="require_login0"><?php echo _l('mcl_require_client_login'); ?></label><br/>
                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="require_login" id="require_login1"
                                           value="1" <?php if (isset($link) && $link['require_login'] == "1") {
                                        echo 'checked';
                                    } ?>>
                                    <label for="require_login1"><?php echo _l('settings_yes'); ?></label>
                                </div>

                                <div class="radio radio-inline radio-primary">
                                    <input type="radio" name="require_login" id="require_login0"
                                           value="0" <?php if (isset($link) && $link['require_login'] == "0" || !isset($link)) {
                                        echo 'checked';
                                    } ?>>
                                    <label for="require_login0"><?php echo _l('settings_no'); ?></label>
                                </div>
                            </div>

                            <?php
                            $value = isset($link) ? explode(",", $link['users']) : '';
                            $class = '';
                            if ($staff_ajax)
                                $class = 'ajax-search';
                            echo render_select('users[]', $staff, ["staffid", ["firstname", 'lastname']], _l('mcl_restrict_staff'), '', ["multiple" => true, "data-none-selected-text" => _l("mcl_show_to_all_staff")], [], 'form-field-users hide', $class, false)
                            ?>

                            <hr class="hr-panel-heading"/>
                            <?php if (isset($link)) { ?>
                                <a href="<?php echo html_escape(admin_url('custom_links')); ?>"
                                   class="btn btn-primary pull-left mright5"><?php echo _l('add_new'); ?></a>
                                <a href="<?php echo html_escape(admin_url('custom_links')); ?>"
                                   class="btn btn-primary pull-right mright5"><?php echo _l('cancel'); ?></a>
                            <?php } ?>
                            <button type="submit"
                                    class="btn btn-primary pull-right mright5"><?php echo _l('save'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-6">
                <div class="panel_s" id="custom-links-list">
                    <div class="panel-body">
                        <h4 class="no-margin inline-block">
                            <?php echo html_escape($title); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>
                        <?php if (isset($main_links) && count($main_links) > 0) { ?>
                            <p class="mtop15 text-primary link-headings"><?php echo _l('mcl_main_menu'); ?></p>
                            <div class="dd active">
                                <ol class="dd-list" id="main-links-list">
                                    <?php foreach ($main_links as $link) { ?>
                                        <li class="dd-item dd3-item main"
                                            data-id="<?php echo html_escape($link['id']); ?>">
                                            <div class="dd3-content">
                                                <span class="dd-icon"><?php if (!empty($link['icon'])) echo '<i class="fa ' . $link['icon'] . '"></i>'; ?></span>
                                                <?php echo _l($link['title'], '', false); ?>
                                                <?php if (has_permission('custom_links', '', 'delete')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/delete/' . $link['id']); ?>"
                                                       onclick="return confirm('<?php echo _l('mcl_confirm_delete_link'); ?>');"
                                                       class="text-muted pull-right text-danger mleft10"><i
                                                                class="fa fa-trash"></i></a>
                                                <?php } ?>
                                                <?php if (has_permission('custom_links', '', 'edit')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/link/' . $link['id']); ?>"
                                                       class="text-muted pull-right"><i class="fa fa-pencil"></i></a>
                                                <?php } ?>
                                            </div>
                                        </li>
                                        <?php if(isset($link['children'])){ ?>
                                        <ol class="dd-list">
                                            <?php foreach ($link['children'] as $child){ ?>
                                                <li class="dd-item dd3-item main"
                                                    data-id="<?php echo html_escape($child['id']); ?>">
                                                    <div class="dd3-content">
                                                        <span class="dd-icon"><?php if (!empty($child['icon'])) echo '<i class="fa ' . $child['icon'] . '"></i>'; ?></span>
                                                        <?php echo _l($child['title'], '', false); ?>
                                                        <?php if (has_permission('custom_links', '', 'delete')) { ?>
                                                            <a href="<?php echo admin_url('custom_links/delete/' . $child['id']); ?>"
                                                               onclick="return confirm('<?php echo _l('mcl_confirm_delete_link'); ?>');"
                                                               class="text-muted pull-right text-danger mleft10"><i
                                                                        class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                        <?php if (has_permission('custom_links', '', 'edit')) { ?>
                                                            <a href="<?php echo admin_url('custom_links/link/' . $child['id']); ?>"
                                                               class="text-muted pull-right"><i class="fa fa-pencil"></i></a>
                                                        <?php } ?>
                                                    </div>
                                                </li>
                                            <?php } ?>
                                        </ol>
                                        <?php } ?>
                                    <?php } ?>
                                </ol>
                            </div>
                        <?php }
                        if (isset($setup_links) && count($setup_links) > 0) { ?>
                            <div class="clearfix"></div>
                            <p class="mtop15 text-primary link-headings"><?php echo _l('mcl_setup_menu'); ?></p>
                            <div class="dd active">
                                <ol class="dd-list" id="setup-links-list">
                                    <?php foreach ($setup_links as $link) { ?>
                                        <li class="dd-item dd3-item main"
                                            data-id="<?php echo html_escape($link['id']); ?>">
                                            <div class="dd3-content">
                                                <span class="dd-icon"><?php if (!empty($link['icon'])) echo '<i class="fa ' . $link['icon'] . '"></i>'; ?></span>
                                                <?php echo _l($link['title'], '', false); ?>
                                                <?php if (has_permission('custom_links', '', 'delete')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/delete/' . $link['id']); ?>"
                                                       onclick="return confirm('<?php echo _l('mcl_confirm_delete_link'); ?>');"
                                                       class="text-muted pull-right text-danger mleft10"><i
                                                                class="fa fa-trash"></i></a>
                                                <?php } ?>
                                                <?php if (has_permission('custom_links', '', 'edit')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/link/' . $link['id']); ?>"
                                                       class="text-muted pull-right"><i class="fa fa-pencil"></i></a>
                                                <?php } ?>
                                            </div>
                                        </li>

                                        <?php if(isset($link['children'])){ ?>
                                            <ol class="dd-list">
                                                <?php foreach ($link['children'] as $child){ ?>
                                                    <li class="dd-item dd3-item main"
                                                        data-id="<?php echo html_escape($child['id']); ?>">
                                                        <div class="dd3-content">
                                                            <span class="dd-icon"><?php if (!empty($child['icon'])) echo '<i class="fa ' . $child['icon'] . '"></i>'; ?></span>
                                                            <?php echo _l($child['title'], '', false); ?>
                                                            <?php if (has_permission('custom_links', '', 'delete')) { ?>
                                                                <a href="<?php echo admin_url('custom_links/delete/' . $child['id']); ?>"
                                                                   onclick="return confirm('<?php echo _l('mcl_confirm_delete_link'); ?>');"
                                                                   class="text-muted pull-right text-danger mleft10"><i
                                                                            class="fa fa-trash"></i></a>
                                                            <?php } ?>
                                                            <?php if (has_permission('custom_links', '', 'edit')) { ?>
                                                                <a href="<?php echo admin_url('custom_links/link/' . $child['id']); ?>"
                                                                   class="text-muted pull-right"><i class="fa fa-pencil"></i></a>
                                                            <?php } ?>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ol>
                                        <?php } ?>
                                    <?php } ?>
                                </ol>
                            </div>
                        <?php }
                        if (isset($client_links) && count($client_links) > 0) { ?>
                            <div class="clearfix"></div>
                            <p class="mtop15 text-primary link-headings"><?php echo _l('mcl_client_menu'); ?></p>
                            <div class="dd active">
                                <ol class="dd-list" id="setup-links-list">
                                    <?php foreach ($client_links as $link) { ?>
                                        <li class="dd-item dd3-item main"
                                            data-id="<?php echo html_escape($link['id']); ?>">
                                            <div class="dd3-content">
                                                <span class="dd-icon"><?php if (!empty($link['icon'])) echo '<i class="fa ' . $link['icon'] . '"></i>'; ?></span>
                                                <?php echo _l($link['title'], '', false); ?>
                                                <?php if (has_permission('custom_links', '', 'delete')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/delete/' . $link['id']); ?>"
                                                       onclick="return confirm('<?php echo _l('mcl_confirm_delete_link'); ?>');"
                                                       class="text-muted pull-right text-danger mleft10"><i
                                                                class="fa fa-trash"></i></a>
                                                <?php } ?>
                                                <?php if (has_permission('custom_links', '', 'edit')) { ?>
                                                    <a href="<?php echo admin_url('custom_links/link/' . $link['id']); ?>"
                                                       class="text-muted pull-right"><i class="fa fa-pencil"></i></a>
                                                <?php } ?>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ol>
                            </div>
                        <?php }
                        if (
                            (!isset($main_links) || count($main_links) == 0)
                            && (!isset($setup_links) || count($setup_links) == 0)
                            && (!isset($client_links) || count($client_links) == 0)
                        ) { ?>
                            <div class="alert alert-warning"><?php echo _l('mcl_no_ling_msg'); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php if (isset($link) && !empty($link['users'])) { ?>
    <script>
        $(document).ready(function () {
            $("[name='users[]']").selectpicker("val", <?php echo json_encode(explode(",", $link['users'])); ?>);
        })
    </script>
<?php } ?>
</body>
</html>
