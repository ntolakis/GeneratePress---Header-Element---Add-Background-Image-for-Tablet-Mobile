/**
 * Add metaboxes for tablet and mobile featured images
 */
function add_custom_featured_image_metaboxes() {
    add_meta_box(
        'tablet_featured_image',
        __('Tablet Featured Image', 'generatepress_child'),
        'tablet_featured_image_callback',
        ['post', 'page'], // Add to post, page, and product post types
        'side',
        'low'
    );

    add_meta_box(
        'mobile_featured_image',
        __('Mobile Featured Image', 'generatepress_child'),
        'mobile_featured_image_callback',
        ['post', 'page'], // Add to post, page, and product post types
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'add_custom_featured_image_metaboxes');

/**
 * Callback function for tablet featured image metabox
 *
 * @param WP_Post $post The post object.
 */
function tablet_featured_image_callback($post) {
    $tablet_image_id = get_post_meta($post->ID, '_tablet_featured_image_id', true);
    wp_nonce_field('save_tablet_featured_image', 'tablet_featured_image_nonce');
    ?>
    <div id="tablet_featured_image_container">
        <?php if ($tablet_image_id) : ?>
            <?php echo wp_get_attachment_image($tablet_image_id, 'thumbnail'); ?>
        <?php endif; ?>
    </div>
    <input type="hidden" id="tablet_featured_image_id" name="tablet_featured_image_id" value="<?php echo esc_attr($tablet_image_id); ?>">
    <button type="button" class="button" id="upload_tablet_featured_image_button"><?php _e('Upload/Add image', 'generatepress_child'); ?></button>
    <button type="button" class="button" id="remove_tablet_featured_image_button"><?php _e('Remove image', 'generatepress_child'); ?></button>
    <script>
        jQuery(document).ready(function($) {
            var frame;
            $('#upload_tablet_featured_image_button').on('click', function(event) {
                event.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Select or Upload Media',
                    button: {
                        text: 'Use this media'
                    },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#tablet_featured_image_id').val(attachment.id);
                    $('#tablet_featured_image_container').html('<img src="' + attachment.sizes.thumbnail.url + '">');
                });
                frame.open();
            });
            $('#remove_tablet_featured_image_button').on('click', function(event) {
                event.preventDefault();
                $('#tablet_featured_image_id').val('');
                $('#tablet_featured_image_container').html('');
            });
        });
    </script>
    <?php
}

/**
 * Callback function for mobile featured image metabox
 *
 * @param WP_Post $post The post object.
 */
function mobile_featured_image_callback($post) {
    $mobile_image_id = get_post_meta($post->ID, '_mobile_featured_image_id', true);
    wp_nonce_field('save_mobile_featured_image', 'mobile_featured_image_nonce');
    ?>
    <div id="mobile_featured_image_container">
        <?php if ($mobile_image_id) : ?>
            <?php echo wp_get_attachment_image($mobile_image_id, 'thumbnail'); ?>
        <?php endif; ?>
    </div>
    <input type="hidden" id="mobile_featured_image_id" name="mobile_featured_image_id" value="<?php echo esc_attr($mobile_image_id); ?>">
    <button type="button" class="button" id="upload_mobile_featured_image_button"><?php _e('Upload/Add image', 'generatepress_child'); ?></button>
    <button type="button" class="button" id="remove_mobile_featured_image_button"><?php _e('Remove image', 'generatepress_child'); ?></button>
    <script>
        jQuery(document).ready(function($) {
            var frame;
            $('#upload_mobile_featured_image_button').on('click', function(event) {
                event.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Select or Upload Media',
                    button: {
                        text: 'Use this media'
                    },
                    multiple: false
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#mobile_featured_image_id').val(attachment.id);
                    $('#mobile_featured_image_container').html('<img src="' + attachment.sizes.thumbnail.url + '">');
                });
                frame.open();
            });
            $('#remove_mobile_featured_image_button').on('click', function(event) {
                event.preventDefault();
                $('#mobile_featured_image_id').val('');
                $('#mobile_featured_image_container').html('');
            });
        });
    </script>
    <?php
}

/**
 * Save the metabox data
 *
 * @param int $post_id The ID of the post being saved.
 */
function save_custom_featured_images($post_id) {
    // Verify nonce for tablet featured image
    if (!isset($_POST['tablet_featured_image_nonce']) || !wp_verify_nonce($_POST['tablet_featured_image_nonce'], 'save_tablet_featured_image')) {
        return;
    }
    // Verify nonce for mobile featured image
    if (!isset($_POST['mobile_featured_image_nonce']) || !wp_verify_nonce($_POST['mobile_featured_image_nonce'], 'save_mobile_featured_image')) {
        return;
    }
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save tablet featured image ID
    if (isset($_POST['tablet_featured_image_id'])) {
        update_post_meta($post_id, '_tablet_featured_image_id', sanitize_text_field($_POST['tablet_featured_image_id']));
    } else {
        delete_post_meta($post_id, '_tablet_featured_image_id');
    }

    // Save mobile featured image ID
    if (isset($_POST['mobile_featured_image_id'])) {
        update_post_meta($post_id, '_mobile_featured_image_id', sanitize_text_field($_POST['mobile_featured_image_id']));
    } else {
        delete_post_meta($post_id, '_mobile_featured_image_id');
    }
}
add_action('save_post', 'save_custom_featured_images');

/**
 * Output featured images as data attributes in the page hero
 */
function output_featured_images_in_page_hero() {
    if (is_singular(['post', 'page'])) {
        global $post;
        $default_image_id = get_post_thumbnail_id($post->ID);
        $tablet_image_id = get_post_meta($post->ID, '_tablet_featured_image_id', true);
        $mobile_image_id = get_post_meta($post->ID, '_mobile_featured_image_id', true);
        $default_image_url = $default_image_id ? wp_get_attachment_url($default_image_id) : '';
        $tablet_image_url = $tablet_image_id ? wp_get_attachment_url($tablet_image_id) : '';
        $mobile_image_url = $mobile_image_id ? wp_get_attachment_url($mobile_image_id) : '';
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var pageHero = document.querySelector('.page-hero');
                if (pageHero) {
                    pageHero.setAttribute('data-default-image', '<?php echo esc_js($default_image_url); ?>');
                    pageHero.setAttribute('data-tablet-image', '<?php echo esc_js($tablet_image_url); ?>');
                    pageHero.setAttribute('data-mobile-image', '<?php echo esc_js($mobile_image_url); ?>');
                }
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'output_featured_images_in_page_hero');

/**
 * Enqueue custom JavaScript for resolution-based image replacement
 */
function enqueue_custom_resolution_script() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            function updateHeroImage() {
                var pageHero = document.querySelector('.page-hero');
                if (!pageHero) return;

                var defaultImage = pageHero.getAttribute('data-default-image');
                var tabletImage = pageHero.getAttribute('data-tablet-image');
                var mobileImage = pageHero.getAttribute('data-mobile-image');
                var screenWidth = window.innerWidth;

                if (screenWidth >= 768 && screenWidth <= 1024 && tabletImage && tabletImage !== '') {
                    pageHero.style.backgroundImage = 'url(' + tabletImage + ')';
                } else if (screenWidth >= 360 && screenWidth < 768 && mobileImage && mobileImage !== '') {
                    pageHero.style.backgroundImage = 'url(' + mobileImage + ')';
                } else {
                    pageHero.style.backgroundImage = 'url(' + defaultImage + ')';
                }
            }

            // Initial check
            updateHeroImage();

            // Update on window resize
            window.addEventListener('resize', updateHeroImage);
        });
    </script>
    <?php
}
add_action('wp_footer', 'enqueue_custom_resolution_script');
