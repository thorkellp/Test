<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(__('Óheimill aðgangur', 'husfelag-bokhald'));
}

if (isset($_POST['hb_upload_fundargerd']) && check_admin_referer('hb_upload_fundargerd', 'hb_nonce')) {
    $title = sanitize_text_field($_POST['fundargerd_title']);

    $post_id = wp_insert_post(array(
        'post_title'  => $title,
        'post_type'   => 'hb_fundargerd',
        'post_status' => 'publish'
    ));

    if (!is_wp_error($post_id) && !empty($_FILES['fundargerd_file']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('fundargerd_file', $post_id);

        if (!is_wp_error($attachment_id)) {
            $url = wp_get_attachment_url($attachment_id);
            update_post_meta($post_id, 'document_url', esc_url_raw($url));
            echo '<div class="notice notice-success"><p>' . __('Fundargerð skráð', 'husfelag-bokhald') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Villa við skráningu skráar', 'husfelag-bokhald') . '</p></div>';
        }
    }
}
?>
<div class="wrap">
    <h1><?php _e('Hlaða upp fundargerð', 'husfelag-bokhald'); ?></h1>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('hb_upload_fundargerd', 'hb_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="fundargerd_title"><?php _e('Titill', 'husfelag-bokhald'); ?></label></th>
                <td><input type="text" id="fundargerd_title" name="fundargerd_title" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="fundargerd_file"><?php _e('Skjal', 'husfelag-bokhald'); ?></label></th>
                <td><input type="file" id="fundargerd_file" name="fundargerd_file" accept=".pdf,.doc,.docx" required></td>
            </tr>
        </table>
        <?php submit_button(__('Skrá fundargerð', 'husfelag-bokhald'), 'primary', 'hb_upload_fundargerd'); ?>
    </form>
</div>
