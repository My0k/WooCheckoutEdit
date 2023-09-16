<?php
/*
Plugin Name: Checkout Fields Manager
Description: Manage WooCommerce Checkout Fields
Version: 1.0
Author: My0k
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function wc_cfm_settings_page() {
    ?>
    <div class="wrap">
        <h2>Checkout Fields Manager</h2>
        <p>Use this interface to manage WooCommerce checkout fields. Toggle activity, mark fields as required, change their labels, and set default values.</p>

        <form method="post" action="">
            <table class="form-table">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Active</th>
                        <th>Required</th>
                        <th>Label</th>
                        <th>Default Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $fields = WC()->checkout->get_checkout_fields();

                    foreach ($fields as $section => $fieldGroup) {
                        foreach ($fieldGroup as $key => $fieldData) {
                            $isActive = get_option($key . "_active", true);
                            $isRequired = isset($fieldData['required']) ? $fieldData['required'] : false;
                            $label = get_option($key . "_label", $fieldData['label']);
                            $defaultValue = get_option($key . "_default", isset($fieldData['default']) ? $fieldData['default'] : '');

                            echo '<tr>';
                            echo '<td>' . $section . ' - ' . $key . '</td>';
                            echo '<td><input type="checkbox" name="' . $key . '_active" value="1" ' . checked($isActive, true, false) . '></td>';
                            echo '<td><input type="checkbox" name="' . $key . '_required" value="1" ' . checked($isRequired, true, false) . '></td>';
                            echo '<td><input type="text" name="' . $key . '_label" value="' . esc_attr($label) . '"></td>';
                            echo '<td><input type="text" name="' . $key . '_default" value="' . esc_attr($defaultValue) . '"></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="wc_cfm_save" class="button-primary" value="Save Changes" />
                <input type="submit" name="wc_cfm_reset" class="button-secondary" value="Set Back to Default" />
            </p>
        </form>

        <h3>Latest Log</h3>
        <pre><?php echo wc_cfm_get_latest_log(); ?></pre>
    </div>
    <?php
}

function wc_cfm_settings_menu() {
    add_submenu_page('woocommerce', 'Checkout Fields Manager', 'Checkout Fields Manager', 'manage_woocommerce', 'wc-checkout-fields', 'wc_cfm_settings_page');
}

add_action('admin_menu', 'wc_cfm_settings_menu');

function wc_cfm_write_log($log_msg) {
    $log_filename = WP_CONTENT_DIR . "/wc_cfm_log";
    if (!file_exists($log_filename)) {
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename . '/log_' . date('d-M-Y') . '.log';
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}

function wc_cfm_get_latest_log() {
    $log_filename = WP_CONTENT_DIR . "/wc_cfm_log/log_" . date('d-M-Y') . '.log';

    if (file_exists($log_filename)) {
        return file_get_contents($log_filename);
    } else {
        return "No log entries for today.";
    }
}

function wc_cfm_save_settings() {
    if (isset($_POST['wc_cfm_save'])) {
        $fields = WC()->checkout->get_checkout_fields();
        
        foreach ($fields as $section => $fieldGroup) {
            foreach ($fieldGroup as $key => $fieldData) {
                update_option($key . "_active", isset($_POST[$key . '_active']));
                update_option($key . "_required", isset($_POST[$key . '_required']));
                update_option($key . "_label", sanitize_text_field($_POST[$key . '_label']));
                update_option($key . "_default", sanitize_text_field($_POST[$key . '_default']));
            }
        }
    }

    if (isset($_POST['wc_cfm_reset'])) {
        wc_cfm_reset_to_defaults();
    }
}

add_action('admin_init', 'wc_cfm_save_settings');

function wc_cfm_get_default_checkout_fields() {
    return array(
        'billing' => array(
            'billing_first_name' => array(
                'label'    => 'First Name',
                'required' => true
            ),
            'billing_last_name' => array(
                'label'    => 'Last Name',
                'required' => true
            ),
            'billing_company' => array(
                'label'    => 'Company',
                'required' => false
            ),
            'billing_address_1' => array(
                'label'    => 'Address 1',
                'required' => true
            ),
            'billing_address_2' => array(
                'label'    => 'Address 2',
                'required' => false
            ),
            'billing_city' => array(
                'label'    => 'City',
                'required' => true
            ),
            'billing_postcode' => array(
                'label'    => 'Postcode',
                'required' => true
            ),
            'billing_country' => array(
                'label'    => 'Country',
                'required' => true
            ),
            'billing_state' => array(
                'label'    => 'State',
                'required' => true
            ),
            'billing_email' => array(
                'label'    => 'Email',
                'required' => true
            ),
            'billing_phone' => array(
                'label'    => 'Phone',
                'required' => true
            ),
        ),
        'shipping' => array(
            'shipping_first_name' => array(
                'label'    => 'First Name',
                'required' => true
            ),
            'shipping_last_name' => array(
                'label'    => 'Last Name',
                'required' => true
            ),
            'shipping_company' => array(
                'label'    => 'Company',
                'required' => false
            ),
            'shipping_address_1' => array(
                'label'    => 'Address 1',
                'required' => true
            ),
            'shipping_address_2' => array(
                'label'    => 'Address 2',
                'required' => false
            ),
            'shipping_city' => array(
                'label'    => 'City',
                'required' => true
            ),
            'shipping_postcode' => array(
                'label'    => 'Postcode',
                'required' => true
            ),
            'shipping_country' => array(
                'label'    => 'Country',
                'required' => true
            ),
            'shipping_state' => array(
                'label'    => 'State',
                'required' => true
            ),
        ),
        'order' => array(
            'order_comments' => array(
                'label'    => 'Order Notes',
                'required' => false
            )
        )
    );
}


function wc_cfm_reset_to_defaults() {
    wc_cfm_write_log("Reset function called.");

    $default_fields = wc_cfm_get_default_checkout_fields();

    foreach ($default_fields as $field_category => $fields) {
        foreach ($fields as $key => $field) {
            wc_cfm_write_log("Resetting default for key: $key");
            delete_option($key . "_active");
            delete_option($key . "_required");
            delete_option($key . "_label");
            delete_option($key . "_default");
        }
    }
}

function wc_cfm_custom_checkout_fields($fields) {
    foreach ($fields as $section => $fieldGroup) {
        foreach ($fieldGroup as $key => $fieldData) {
            if (!get_option($key . "_active", true)) {
                unset($fields[$section][$key]);
            } else {
                if (get_option($key . "_required")) {
                    $fields[$section][$key]['required'] = true;
                } else {
                    $fields[$section][$key]['required'] = false;
                }

                if ($label = get_option($key . "_label")) {
                    $fields[$section][$key]['label'] = $label;
                }

                if ($defaultValue = get_option($key . "_default")) {
                    $fields[$section][$key]['default'] = $defaultValue;
                }
            }
        }
    }

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'wc_cfm_custom_checkout_fields');

?>
