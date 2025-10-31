<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?= form_open($this->uri->uri_string(), ['id' => 'article-form']); ?>
        <div class="tw-max-w-4xl tw-mx-auto">
            <div class="tw-flex tw-justify-between tw-mb-2">
                <div>
                    <h4 class="tw-my-0 tw-text-lg tw-font-bold tw-text-neutral-700">
                        <?= e($title); ?>
                    </h4>
                    <?php if (isset($article)) { ?>
                    <small>
                        <?php if ($article->staff_article == 1) { ?>
                        <a href="<?= admin_url('knowledge_base/view/' . $article->slug); ?>"
                            target="_blank"><?= admin_url('knowledge_base/view/' . $article->slug); ?></a>
                        <?php } else { ?>
                        <a href="<?= site_url('knowledge-base/article/' . $article->slug); ?>"
                            target="_blank"><?= site_url('knowledge-base/article/' . $article->slug); ?></a>
                        <?php } ?>
                    </small>
                    <br />
                    <small>
                        <span
                            class="tw-font-medium"><?= _l('article_total_views'); ?>:</span>
                        <?= total_rows(db_prefix() . 'views_tracking', ['rel_type' => 'kb_article', 'rel_id' => $article->articleid]);
                        ?>
                    </small>
                    <?php } ?>
                </div>
                <?php if (isset($article)) { ?>
                <div class="tw-self-start tw-space-x-1">
                    <?php if (staff_can('create', 'knowledge_base')) { ?>
                    <a href="<?= admin_url('knowledge_base/article'); ?>"
                        class="btn btn-primary"><?= _l('kb_article_new_article'); ?></a>
                    <?php } ?>
                    <?php if (staff_can('delete', 'knowledge_base')) { ?>
                    <a href="<?= admin_url('knowledge_base/delete_article/' . $article->articleid); ?>"
                        class="btn btn-default _delete">
                        <i class="fa-regular fa-trash-can"></i>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>

            <div class="panel_s">
                <div class="panel-body">
                    <?php $value = (isset($article) ? $article->subject : ''); ?>
                    <?php $attrs = (isset($article) ? [] : ['autofocus' => true]); ?>
                    <?= render_input('subject', 'kb_article_add_edit_subject', $value, 'text', $attrs); ?>
                    <?php if (isset($article)) {
                        echo render_input('slug', 'kb_article_slug', $article->slug, 'text');
                    } ?>
                    <?php $value = (isset($article) ? $article->articlegroup : ''); ?>
                    <?php if (staff_can('create', 'knowledge_base')) {
                        echo render_select_with_input_group('articlegroup', get_kb_groups(), ['groupid', 'name'], 'kb_article_add_edit_group', $value, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_kb_group();return false;"><i class="fa fa-plus"></i></a></div>');
                    } else {
                        echo render_select('articlegroup', get_kb_groups(), ['groupid', 'name'], 'kb_article_add_edit_group', $value);
                    }
?>
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" id="staff_article" name="staff_article" <?php if (isset($article) && $article->staff_article == 1) {
                            echo 'checked';
                        } ?>>
                        <label
                            for="staff_article"><?= _l('internal_article'); ?></label>
                    </div>
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" id="disabled" name="disabled" <?php if (isset($article) && $article->active_article == 0) {
                            echo 'checked';
                        } ?>>
                        <label
                            for="disabled"><?= _l('kb_article_disabled'); ?></label>
                    </div>
                    <p class="bold">
                        <?= _l('kb_article_description'); ?>
                    </p>
                    <?php $contents = '';
if (isset($article)) {
    $contents = $article->description;
} ?>
                    <?= render_textarea('description', '', $contents, [], [], '', 'tinymce tinymce-manual'); ?>
                    <div class="tw-mt-2">
  <button type="button" class="btn btn-default" id="kb-upload-image">
    <i class="fa fa-upload"></i> <?= _l('upload'); ?> <?= _l('file'); ?>
  </button>
  <!-- <input type="file" id="kb-image-input" accept="image/*" style="display:none"> -->
  <input type="file" id="kb-image-input" accept="*" style="display:none">

</div>

                </div>
                <?php if ((staff_can('create', 'knowledge_base') && ! isset($article)) || staff_can('edit', 'knowledge_base') && isset($article)) { ?>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <?= _l('submit'); ?>
                    </button>
                </div>
                <?php } ?>
            </div>
        </div>

    </div>
    <?= form_close(); ?>
</div>
<?php $this->load->view('admin/knowledge_base/group'); ?>
<?php init_tail(); ?>
<script>
$(function () {
  init_editor('#description', {
    toolbar_sticky: true,
    menubar: 'file edit view format tools table',
    toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | removeformat'
  });

  $('#kb-upload-image').on('click', function () {
    $('#kb-image-input').trigger('click');
  });

  $('#kb-image-input').on('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;

    const fd = new FormData();
    fd.append('file', file);

    // --- CSRF (Perfex/CI) ---
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    fd.append(csrfName, csrfHash);
    // -------------------------

    $.ajax({
      url: admin_url + 'knowledge_base/upload_image',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function (res, status, xhr) {
        try {
          const obj = typeof res === 'string' ? JSON.parse(res) : res;
          if (obj.location) {
            tinymce.get('description').insertContent('<img src="' + obj.location + '" alt="' + (file.name || '') + '">');
          } else {
            alert(obj.error || 'Image upload failed');
          }
        } catch (e) {
          alert('Unexpected response from server.');
        }
      },
      error: function (xhr) {
        // Show actual server message if present
        let msg = 'Upload failed.';
        try {
          const obj = JSON.parse(xhr.responseText || '{}');
          if (obj.error) msg = obj.error;
        } catch (e) {}
        alert(msg);
      }
    });

    $(this).val('');
  });

  appValidateForm($('#article-form'), {
    subject: 'required',
    articlegroup: 'required'
  });
});
</script>

</body>

</html>