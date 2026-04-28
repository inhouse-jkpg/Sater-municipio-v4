<div class="wrap">
    <form action="options.php" method="post">
        <h1><?= esc_html__(get_admin_page_title()) ?></h1>
        <?php if (mediaflow_has_env()): ?>
            <p>Please note that the settings are configured using environment variables. Remove the environment variables to make changes to the settings on this page.</p>
        <?php endif; ?>
        <?php settings_fields('mediaflow'); ?>
        <?php do_settings_sections('mediaflow'); ?>
        <?php submit_button(__('Save Changes')); ?>
    </form>
</div>
