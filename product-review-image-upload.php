<?php
/*
Plugin Name: Product Review Image Upload
Description: Allows customers to upload images when reviewing products.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the necessary hooks
add_filter('comment_form_field_comment', 'pri_modify_comment_form');
add_action('comment_post', 'pri_save_review_image');
add_filter('comment_text', 'pri_display_review_image');

// Modify the comment form to include the image upload field
function pri_modify_comment_form($comment_field) {
    if (is_product()) {
        ob_start();
        ?>
<p class="comment-form-review-image">
    <label for="review_image"><?php _e('Upload Image'); ?></label>
    <input type="file" name="review_image" id="review_image">
</p>
<?php
        wp_nonce_field('pri_review_image_upload', 'pri_review_image_upload_nonce');
        $comment_field .= ob_get_clean();
    }
    return $comment_field;
}

// Save uploaded image
function pri_save_review_image($comment_id) {
    if (isset($_POST['pri_review_image_upload_nonce']) && wp_verify_nonce($_POST['pri_review_image_upload_nonce'], 'pri_review_image_upload')) {
        if (isset($_FILES['review_image']) && !empty($_FILES['review_image']['name'])) {
            $upload = wp_handle_upload($_FILES['review_image'], ['test_form' => false]);
            if (!isset($upload['error']) && isset($upload['url'])) {
                add_comment_meta($comment_id, 'review_image', $upload['url']);
            }
        }
    }
}

// Display the uploaded image in the review
function pri_display_review_image($comment_text) {
    $comment_id = get_comment_ID();
    $review_image = get_comment_meta($comment_id, 'review_image', true);
    if ($review_image) {
        $comment_text .= '<p><img src="' . esc_url($review_image) . '" alt="' . __('Review Image') . '" style="max-width:100%; height:auto;"></p>';
    }
    return $comment_text;
}