<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <iframe id="custom-links-iframe" title="<?php echo html_escape($link['title']); ?>" width="100%" height="100%" src="<?php echo html_escape($href); ?>">
    </iframe>
</div>

<?php init_tail(); ?>
</body>
</html>
