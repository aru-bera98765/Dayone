<?php
/**
 * Template name: My account
 */

get_header('dashboard');

// Ensure user is logged in
if (!is_user_logged_in()) {
    echo '<p>Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to view your account.</p>';
    get_footer('dashboard');
    exit;
}

$user_id = get_current_user_id();
$user    = wp_get_current_user();

// Get current values
$full_name = esc_attr($user->display_name);
$email     = esc_attr($user->user_email);
$phone     = esc_attr(get_user_meta($user_id, 'phone', true));
$company   = esc_attr(get_user_meta($user_id, 'company', true));
$services  = esc_attr(get_user_meta($user_id, 'services', true));
$address   = esc_textarea(get_user_meta($user_id, 'address', true));
?>

<!-- Container for AJAX messages -->


<section class="content_body">
    <div class="account_container">
        <div class="form_header">
            <h3>My Account</h3>
            <p>Update your personal information and profile details.</p>
        </div>

        <!-- PROFILE UPDATE FORM -->
        <form id="profile-update-form" class="account_form" method="post">
            <div class="form_row">
                <div class="form_group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $full_name; ?>" placeholder="Enter Your name">
                </div>
                <div class="form_group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo $email; ?>" readonly disabled>
                    <!-- readonly + disabled prevents changes, server ignores it -->
                </div>
                <div class="form_group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo $phone; ?>" placeholder="Phone Number">
                </div>
                <div class="form_group">
                    <label>Company Name</label>
                    <input type="text" name="company" value="<?php echo $company; ?>" placeholder="Company Name">
                </div>
                <div class="form_group">
                    <label>Service</label>
                    <input type="text" name="services" value="<?php echo $services; ?>" placeholder="Service">
                </div>
                <div class="form_group full_width">
                    <label>Address</label>
                    <textarea name="address" placeholder="Address"><?php echo $address; ?></textarea>
                </div>
                <div class="form_actions">
                    <?php wp_nonce_field('partner_profile_nonce', 'profile_nonce'); ?>
                    <input type="submit" class="glb_btn" value="Save Changes">
                    
                    <div id="account-updt-message" class="account-notice" style="display:none;"></div>
                </div>
            </div>
        </form>

        <div class="form_header password_header">
            <h3>Change Password</h3>
            <p>Ensure your account is using a long, random password to stay secure.</p>
        </div>

        <!-- PASSWORD CHANGE FORM -->
        <form id="password-change-form" class="account_form" method="post">
            <div class="form_row">
                <div class="form_group">
                    <label>New Password</label>
                    <div class="input_wrapper">
                        <input type="password" name="new_password" class="pass_input" placeholder="Enter new password">
                        <span class="toggle_view"><i class="fa-solid fa-eye"></i></span>
                    </div>
                </div>
                <div class="form_group">
                    <label>Confirm Password</label>
                    <div class="input_wrapper">
                        <input type="password" name="confirm_password" class="pass_input" placeholder="Confirm new password">
                        <span class="toggle_view"><i class="fa-solid fa-eye"></i></span>
                    </div>
                </div>
            </div>
            <div class="form_actions">
                <?php wp_nonce_field('partner_password_nonce', 'password_nonce'); ?>
                <input type="submit" class="glb_btn" value="Change Password">
                <div id="pass-updt-message" class="account-notice" style="display:none;"></div>
            </div>
        </form>
    </div>
</section>

<?php get_footer('dashboard'); ?>


<script>
    jQuery(document).ready(function($) {
    // Handle Profile Update form submission
    $('#profile-update-form').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        formData += '&action=partner_update_profile';

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#profile-update-form button[type="submit"], #profile-update-form input[type="submit"]').prop('disabled', true).val('Saving...');
                $('#account-updt-message').hide().removeClass('success error').html('');
            },
            success: function(response) {
                var messageClass = response.success ? 'success' : 'error';
                var messageText = response.data.message;
                $('#account-updt-message').addClass(messageClass).html('<p>' + messageText + '</p>').show();
                if (response.success) {
                    // Optionally update any displayed name elsewhere on page
                }
            },
            error: function() {
                $('#account-updt-message').addClass('error').html('<p>An error occurred. Please try again.</p>').show();
            },
            complete: function() {
                $('#profile-update-form button[type="submit"], #profile-update-form input[type="submit"]').prop('disabled', false).val('Save Changes');
            }
        });
    });

   // Handle Password Change form submission
$('#password-change-form').on('submit', function(e) {
    e.preventDefault();

    var formData = $(this).serialize();
    formData += '&action=partner_change_password';

    $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('#password-change-form button[type="submit"], #password-change-form input[type="submit"]')
                .prop('disabled', true).val('Changing...');
            $('#pass-updt-message').hide().removeClass('success error').html('');
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                $('#pass-updt-message').addClass('success').html('<p>' + response.data.message + '</p>').show();
                
                // Redirect after a short delay (so user sees the message)
                if (response.data.redirect_url) {
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 2000); // 2 seconds delay
                }
            } else {
                $('#pass-updt-message').addClass('error').html('<p>' + response.data.message + '</p>').show();
                // Re-enable submit button on error
                $('#password-change-form button[type="submit"], #password-change-form input[type="submit"]')
                    .prop('disabled', false).val('Change Password');
            }
        },
        error: function() {
            $('#pass-updt-message').addClass('error').html('<p>An error occurred. Please try again.</p>').show();
            $('#password-change-form button[type="submit"], #password-change-form input[type="submit"]')
                .prop('disabled', false).val('Change Password');
        }
    });
});

    // // Toggle password visibility (if you have eye icons)
    // $('.toggle_view').on('click', function() {
    //     var input = $(this).siblings('input');
    //     var type = input.attr('type') === 'password' ? 'text' : 'password';
    //     input.attr('type', type);
    //     $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    // });
});
</script>