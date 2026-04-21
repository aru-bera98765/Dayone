<?php

/**

 * Template Name: Leads Dashboard

 */



get_header('dashboard'); // adjust if needed



global $wpdb;

$properties_table = $wpdb->prefix . 'pr_properties';

$owners_table = $wpdb->prefix . 'pr_owners';



// Get zip codes from custom post type 'mmp_zipcode'

$zip_options = get_posts(array(

    'post_type'      => 'mmp_zipcode',

    'posts_per_page' => -1,

    'orderby'        => 'title',

    'order'          => 'ASC',

));

?>



<section class="content_body">

    <div class="filter_wrapper">

        <form method="GET" id="lead-filter-form" action="">

            <!-- Zip Code Filter -->

            <div class="select_custom">

                <label for="zip_select">Filter by Zip Code:</label>

                <select id="zip_select" name="zipcode">

                    <option value="">All Zip Codes</option>

                    <?php foreach ($zip_options as $zip_post): ?>

                    <option value="<?php echo esc_attr($zip_post->post_title); ?>"
                        <?php selected(isset($_GET['zipcode']) ? $_GET['zipcode'] : '', $zip_post->post_title); ?>>

                        <?php echo esc_html($zip_post->post_title); ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>




            <!-- Date Range Filter with jQuery Datepicker -->


            <div class="date_filter">
                <div class="date">

                    <label for="date_from">From Date:</label>

                    <input type="text" id="date_from" name="date_from" class="datepicker"
                        value="<?php echo isset($_GET['date_from']) ? esc_attr($_GET['date_from']) : ''; ?>"
                        placeholder="dd-mm-yy" autocomplete="off">
                </div>

                <div class="date">
                    <label for="date_to">To Date:</label>

                    <input type="text" id="date_to" name="date_to" class="datepicker"
                        value="<?php echo isset($_GET['date_to']) ? esc_attr($_GET['date_to']) : ''; ?>"
                        placeholder="dd-mm-yy" autocomplete="off">
                </div>

            </div>

            <div class="btnsGrp">
                <button type="submit" class="glb_btn">Apply Filters</button>

                <a href="<?php echo get_permalink(); ?>" class="glb_btn white">Reset</a>
            </div>


        </form>

    </div>



    <div class="table_container">

        <table class="custom_table" id="leads-table">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Property Address</th>

                    <th>Lead Date</th>

                    <th>Contact Name</th>

                    <th>Email</th>

                    <th>Phone</th>

                    <th class="text_center">Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php

                // Build WHERE clause for leads (lead_status = 1)

                $where_conditions = array('p.lead_status = 1');

                $query_params = array();



                // Zip filter

                if (!empty($_GET['zipcode'])) {

                    $where_conditions[] = 'p.zip_five = %s';

                    $query_params[] = sanitize_text_field($_GET['zipcode']);

                }



                // Date range filter (inputs are expected in YYYY-MM-DD format)

                if (!empty($_GET['date_from'])) {

                    $where_conditions[] = 'DATE(p.fetched_at) >= %s';

                    $query_params[] = sanitize_text_field($_GET['date_from']);

                }

                if (!empty($_GET['date_to'])) {

                    $where_conditions[] = 'DATE(p.fetched_at) <= %s';

                    $query_params[] = sanitize_text_field($_GET['date_to']);

                }



                $where_sql = implode(' AND ', $where_conditions);



                // Query: get each property once, and the first owner (primary first, then any)

                $sql = "

                    SELECT 

                        p.radar_id,

                        p.address,

                        p.city,

                        p.state,

                        p.zip_five,

                        p.fetched_at,

                        (SELECT o.first_name FROM {$owners_table} o 

                         WHERE o.radar_id = p.radar_id 

                         ORDER BY o.is_primary DESC, o.id ASC LIMIT 1) as first_name,

                        (SELECT o.last_name FROM {$owners_table} o 

                         WHERE o.radar_id = p.radar_id 

                         ORDER BY o.is_primary DESC, o.id ASC LIMIT 1) as last_name,

                        (SELECT o.email FROM {$owners_table} o 

                         WHERE o.radar_id = p.radar_id 

                         ORDER BY o.is_primary DESC, o.id ASC LIMIT 1) as email,

                        (SELECT o.phone FROM {$owners_table} o 

                         WHERE o.radar_id = p.radar_id 

                         ORDER BY o.is_primary DESC, o.id ASC LIMIT 1) as phone

                    FROM {$properties_table} p

                    WHERE {$where_sql}

                    ORDER BY p.fetched_at DESC

                ";



                if (!empty($query_params)) {

                    $sql = $wpdb->prepare($sql, $query_params);

                }

                $results = $wpdb->get_results($sql);



                if ($results) {

                    $serial = 1;

                    foreach ($results as $row) {

                        $email = !empty($row->email) ? $row->email : 'NA';

                        $phone = !empty($row->phone) ? $row->phone : 'NA';

                        $full_address = trim($row->address . ', ' . $row->city . ', ' . $row->state . ' ' . $row->zip_five);

                        $full_address = !empty($full_address) ? $full_address : 'NA';

                        $contact_name = trim($row->first_name . ' ' . $row->last_name);

                        $contact_name = !empty($contact_name) ? $contact_name : 'NA';

                        ?>

                <tr data-radar-id="<?php echo esc_attr($row->radar_id); ?>">

                    <td class="s_no"><?php echo $serial++; ?></td>

                    <td><?php echo esc_html($full_address); ?></td>

                    <td class="date"><?php echo date_i18n(get_option('date_format'), strtotime($row->fetched_at)); ?>
                    </td>

                    <td><?php echo esc_html($contact_name); ?></td>

                    <td class="email">

                        <div class="copy_wrapper">

                            <span class="copy_text"><?php echo esc_html($email); ?></span>

                            <?php if ($email !== 'NA'): ?>

                            <button class="copy_btn" data-copy="<?php echo esc_attr($email); ?>"><i
                                    class="fa-solid fa-copy"></i></button>

                            <?php endif; ?>

                        </div>

                    </td>

                    <td>

                        <div class="copy_wrapper">

                            <span class="copy_text"><?php echo esc_html($phone); ?></span>

                            <?php if ($phone !== 'NA'): ?>

                            <button class="copy_btn" data-copy="<?php echo esc_attr($phone); ?>"><i
                                    class="fa-solid fa-copy"></i></button>

                            <?php endif; ?>

                        </div>

                    </td>

                    <td>

                        <div class="action_btns">

                            <button class="btn_view"
                                data-radar-id="<?php echo esc_attr($row->radar_id); ?>">View</button>

                            <button class="btn_edit convert-customer"
                                data-radar-id="<?php echo esc_attr($row->radar_id); ?>">Convert Customer</button>

                        </div>

                    </td>

                </tr>

                <?php

                    }

                } else {

                    echo '<tr><td colspan="7">No leads found.</td></tr>';

                }

                ?>

            </tbody>

        </table>

    </div>

</section>



<!-- Modal for Property Details -->

<div id="property-modal" class="modal" style="display:none;">

    <div class="modal-content">

        <span class="close-modal">&times;</span>

        <h3>Property Details</h3>

        <div id="modal-property-info"></div>

        <h4>All Owners</h4>

        <div id="modal-owners-list"></div>

    </div>

</div>



<!-- Custom Confirmation Modal for Convert to Customer -->

<div id="confirm-modal" class="modal" style="display:none;">

    <div class="modal-content confirm-modal-content">

        <span class="close-confirm-modal">&times;</span>

        <h3>Are you sure?</h3>

        <p>You want to mark this property as customer?</p>

        <div class="confirm-buttons">

            <button id="confirm-yes" class="glb_btn">Yes</button>

            <button id="confirm-no" class="glb_btn cancel">No</button>

        </div>

    </div>

</div>



<style>
/* same as before plus datepicker styling */

.modal {

    position: fixed;

    z-index: 1000;

    left: 0;
    top: 0;

    width: 100%;
    height: 100%;

    background-color: rgba(0, 0, 0, 0.6);

}

.modal-content {

    background-color: #fff;

    margin: 5% auto;

    padding: 20px;

    width: 80%;

    max-width: 800px;

    border-radius: 8px;

    position: relative;

}

.confirm-modal-content {

    max-width: 400px;

    text-align: center;

}

.close-modal,
.close-confirm-modal {

    position: absolute;

    right: 20px;

    top: 10px;

    font-size: 28px;

    cursor: pointer;

}

.copy_wrapper {

    display: flex;

    align-items: center;

    gap: 5px;

}

.copy_btn {

    background: none;

    border: none;

    cursor: pointer;

}



.filter_wrapper {

    margin-bottom: 20px;

    display: flex;

    gap: 15px;

    flex-wrap: wrap;

    align-items: flex-end;

}

.date_filter input {

    margin: 0 5px;

    padding: 5px 8px;

    border: 1px solid #ccc;

    border-radius: 4px;

}

.glb_btn.reset {

    background: #6c757d;

}

.confirm-buttons {

    margin-top: 20px;

    display: flex;

    gap: 10px;

    justify-content: center;

}

.confirm-buttons .glb_btn {

    background: #0073aa;

    color: white;

    border: none;

    padding: 8px 20px;

    cursor: pointer;

    border-radius: 4px;

}

.confirm-buttons .glb_btn.cancel {

    background: #ccc;

    color: #333;

}

/* Datepicker overrides */

.ui-datepicker {

    background: #fff;

    border: 1px solid #ccc;

    padding: 5px;

    border-radius: 4px;

    font-family: inherit;

}
</style>



<!-- Load jQuery, jQuery UI, and jQuery UI CSS -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">



<script>
jQuery(document).ready(function($) {

    // Date format conversion

    var displayFormat = 'dd-mm-yy';

    var submitFormat = 'yy-mm-dd';



    function convertDateFormat(dateStr, fromFormat, toFormat) {

        if (!dateStr) return '';

        try {

            var parsed = $.datepicker.parseDate(fromFormat, dateStr);

            return $.datepicker.formatDate(toFormat, parsed);

        } catch (e) {

            return dateStr;

        }

    }



    // Initialize datepickers

    $('.datepicker').each(function() {

        var $input = $(this);

        var existingVal = $input.val();

        if (existingVal && /^\d{4}-\d{2}-\d{2}$/.test(existingVal)) {

            var converted = convertDateFormat(existingVal, submitFormat, displayFormat);

            $input.val(converted);

        }

        $input.datepicker({

            dateFormat: displayFormat,

            changeMonth: true,

            changeYear: true,

            showButtonPanel: true

        });

    });



    // Convert to YYYY-MM-DD on submit

    $('#lead-filter-form').on('submit', function() {

        $('.datepicker').each(function() {

            var $input = $(this);

            var displayVal = $input.val();

            if (displayVal && /^\d{2}-\d{2}-\d{4}$/.test(displayVal)) {

                var submitVal = convertDateFormat(displayVal, displayFormat, submitFormat);

                $input.val(submitVal);

            }

        });

    });



    // Copy to clipboard

    $(document).on('click', '.copy_btn', function(e) {

        e.preventDefault();

        var text = $(this).data('copy');

        if (text && text !== 'NA') {

            navigator.clipboard.writeText(text).then(() => alert('Copied: ' + text)).catch(() => alert(
                'Copy failed.'));

        }

    });



    // View modal

    $(document).on('click', '.btn_view', function() {

        var radarId = $(this).data('radar-id');

        var $btn = $(this);

        $btn.text('Loading...').prop('disabled', true);



        $.ajax({

            url: '<?php echo admin_url('admin-ajax.php'); ?>',

            type: 'POST',

            data: {

                action: 'pr_get_property_details',

                radar_id: radarId,

                nonce: '<?php echo wp_create_nonce('pr_ajax_nonce'); ?>'

            },

            dataType: 'json',

            success: function(response) {

                if (response && response.success) {

                    var data = response.data;

                    $('#modal-property-info').html(

                        '<p><strong>Address:</strong> ' + (data.address || 'NA') +
                        '<br>' +

                        '<strong>City:</strong> ' + (data.city || 'NA') + '<br>' +

                        '<strong>Zip:</strong> ' + (data.zip_five || 'NA') + '<br>' +

                        '<strong>State:</strong> ' + (data.state || 'NA') + '<br>' +

                        '<strong>Property Type:</strong> ' + (data.property_type ||
                            'NA') + '<br>' +

                        '<strong>SqFt:</strong> ' + (data.sqft || 'NA') + '<br>' +

                        '<strong>Beds:</strong> ' + (data.beds || 'NA') + '<br>' +

                        '<strong>Baths:</strong> ' + (data.baths || 'NA') + '</p>'

                    );

                    var ownersHtml =
                        '<table class="wp-list-table widefat"><thead><tr><th>Name</th><th>Primary</th><th>Phone</th><th>Email</th></tr></thead><tbody>';

                    if (data.owners && data.owners.length) {

                        $.each(data.owners, function(i, owner) {

                            var isPrimary = (owner.is_primary == 1) ? 'Yes' : 'No';

                            var phoneVal = (owner.phone || 'NA');

                            var emailVal = (owner.email || 'NA');

                            var phoneHtml = (phoneVal !== 'NA') ?
                                '<div class="copy_wrapper"><span class="copy_text">' +
                                phoneVal +
                                '</span><button class="copy_btn" data-copy="' +
                                phoneVal +
                                '"><i class="fa-solid fa-copy"></i></button></div>' :
                                phoneVal;

                            var emailHtml = (emailVal !== 'NA') ?
                                '<div class="copy_wrapper"><span class="copy_text">' +
                                emailVal +
                                '</span><button class="copy_btn" data-copy="' +
                                emailVal +
                                '"><i class="fa-solid fa-copy"></i></button></div>' :
                                emailVal;

                            ownersHtml += '<tr>' +

                                '<td>' + (owner.first_name || '') + ' ' + (owner
                                    .last_name || '') + '</td>' +

                                '<td>' + isPrimary + '</td>' +

                                '<td>' + phoneHtml + '</td>' +

                                '<td>' + emailHtml + '</td>' +

                                '</tr>';

                        });

                    } else {

                        ownersHtml += '<tr><td colspan="4">No owners found.</td></tr>';

                    }

                    ownersHtml += '</tbody></table>';

                    $('#modal-owners-list').html(ownersHtml);

                    $('#property-modal').show();

                } else {

                    alert('Error: ' + ((response && response.data && response.data
                        .message) ? response.data.message : 'Unknown error.'));

                }

            },

            error: function(xhr, status, error) {

                alert('Request failed: ' + status);

                console.error(xhr.responseText);

            },

            complete: function() {

                $btn.text('View').prop('disabled', false);

            }

        });

    });



    // Convert to customer modal logic

    var pendingRadarId = null;

    var pendingButton = null;



    $(document).on('click', '.convert-customer', function(e) {

        e.preventDefault();

        pendingRadarId = $(this).data('radar-id');

        pendingButton = $(this);

        $('#confirm-modal').show();

    });



    $('#confirm-yes').on('click', function() {

        if (!pendingRadarId) return;

        var $btn = pendingButton;

        $('#confirm-modal').hide();



        $btn.text('Converting...').prop('disabled', true);

        $.ajax({

            url: '<?php echo admin_url('admin-ajax.php'); ?>',

            type: 'POST',

            data: {

                action: 'pr_convert_to_customer',

                radar_id: pendingRadarId,

                nonce: '<?php echo wp_create_nonce('pr_ajax_nonce'); ?>'

            },

            dataType: 'json',

            success: function(response) {

                if (response.success) {

                    $btn.closest('tr').fadeOut(function() {
                        $(this).remove();
                    });

                    alert('Converted to customer successfully!');

                } else {

                    alert('Error: ' + (response.data.message || 'Conversion failed.'));

                }

            },

            error: function(xhr, status, error) {

                alert('Request failed: ' + status);

                console.error(xhr.responseText);

            },

            complete: function() {

                $btn.text('Customer').prop('disabled', false);

                pendingRadarId = null;

                pendingButton = null;

            }

        });

    });



    // ========== MODAL CLOSE HANDLERS (FIXED) ==========

    // Property modal close

    $(document).on('click', '.close-modal', function() {

        $('#property-modal').hide();

    });

    $(document).on('click', '#property-modal', function(e) {

        if (e.target === this) $('#property-modal').hide();

    });



    // Confirm modal close

    $(document).on('click', '.close-confirm-modal', function() {

        $('#confirm-modal').hide();

        pendingRadarId = null;

        pendingButton = null;

    });

    $(document).on('click', '#confirm-modal', function(e) {

        if (e.target === this) {

            $('#confirm-modal').hide();

            pendingRadarId = null;

            pendingButton = null;

        }

    });



    // No button in confirm modal

    $('#confirm-no').on('click', function() {

        $('#confirm-modal').hide();

        pendingRadarId = null;

        pendingButton = null;

    });

});
</script>

<?php get_footer('dashboard'); ?>