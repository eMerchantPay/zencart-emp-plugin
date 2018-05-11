<?php
/*
 * Copyright (C) 2018 emerchantpay Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      emerchantpay
 * @copyright   2018 emerchantpay Ltd.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IS_ADMIN_FLAG')) die('Illegal Access');

if (emp_get_is_payment_module_index_action()) {
?>
    <style type="text/css">
        span.emerchantpay-toggle  {
            display: inline-block;
        }

        span.emerchantpay-toggle.toggle-on {
            color: #088A08;
        }

        span.emerchantpay-toggle.toggle-off {
            color: #FA5858;
        }
    </style>
<?php
}

if (emp_get_is_payment_module_edit_action()) {
    $jsPath = "includes/javascript/emerchantpay/";
    $cssPath = "includes/css/emerchantpay/";
    $zenVersion = emp_get_zencart_version();

    echo emp_add_external_resources(
        array(
            "jquery-1.12.3.min.js",
            "bootstrap.min.js",
            "bootstrap.min.css",
            "jquery.number.min.js",
            "bootstrap-checkbox.min.js"
        )
    );
    ?>
    <script type="text/javascript">
        var $emp = $.noConflict();

        $emp(document).ready(function() {
            $emp('input.bootstrap-checkbox').checkboxpicker({
                html: true,
                offLabel: '<span class="glyphicon glyphicon-remove">',
                onLabel: '<span class="glyphicon glyphicon-ok">',
                style: 'btn-group-ms'
            });

            $emp('input.bootstrap-checkbox').change(function() {
                var isChecked = $emp(this).prop('checked');
                $emp(this).parent().find('input[type="hidden"]').val(isChecked);
            });

            $emp('input.form-number-input').number(true, 0, '', '');
        });

    </script>

    <style type="text/css">

        .form-group {
            padding-top: 5pt;
            width: 95%;
            margin: 0 auto;
        }

        .form-group.toggle-container {
            text-align: right;
        }

        .form-control {
            height: 20pt;
            font-size: 8pt;
            width: 100%;
        }

        input.form-control {
            padding: 0 3pt;
        }

        select.form-control {
            padding: 2pt 5pt;
        }

        select.form-control[multiple="multiple"] {
            height: 120pt;
        }

        .btn-group a.btn {
            min-width: 30pt;
        }
    </style>

<?php
}

/**
 * Get External Resources HTML
 * @param array $resourceNames
 * @return string
 */
function emp_add_external_resources($resourceNames)
{
    $html = "";
    foreach ($resourceNames as $key => $resourceName) {
        $html .= emp_add_external_resource($resourceName);
    }
    return $html;
}

/**
 * Get External Resource HTML By Resource Name
 * @param string $resourcePath
 * @return string
 */
function emp_add_external_resource($resourcePath)
{
    $isResourceJavaScript = emp_get_string_ends_with($resourcePath, '.js');

    $includePath =
        "includes/" .
        ($isResourceJavaScript ? "javascript/" : "css/") .
        "emerchantpay/";

    if (emp_get_string_starts_with($resourcePath, 'jquery')) {
        $includePath .= "jQueryExtensions/";
    } elseif (emp_get_string_starts_with($resourcePath, 'bootstrap')) {
        $includePath .= "bootstrap/";
    } elseif (emp_get_string_starts_with($resourcePath, 'font-awesome')) {
        $includePath .= "font-awesome/";
    }

    if ($isResourceJavaScript) {
        return "<script src=\"" . $includePath . $resourcePath ."\"></script>";
    } else {
        return "<link href=\"" . $includePath . $resourcePath . "\" rel=\"stylesheet\" type=\"text/css\" />";
    }
}

/**
 * Check if Current Page is Nodule Esit Page
 * @return bool
 */
function emp_get_is_payment_module_edit_action()
{
    return
        emp_get_is_payment_module_index_action() &&
        isset($_GET['action']) &&
        (strtolower($_GET['action'] == 'edit'));
}

/**
 * Check if Current Page is Module Preview Page
 * @return bool
 */
function emp_get_is_payment_module_index_action()
{
    return
        isset($_GET['set']) &&
        isset($_GET['module']) &&
        (strtolower($_GET['set']) == 'payment') &&
        (
            (strtolower($_GET['module']) == EMERCHANTPAY_CHECKOUT_CODE) ||
            (strtolower($_GET['module']) == EMERCHANTPAY_DIRECT_CODE)
        );
}

/**
 * Gets html attributes by array
 * @param array $attributes
 * @return string
 */
function emp_convert_attributes_array_to_html($attributes)
{
    if (is_array($attributes)) {
        $html = '';

        foreach ($attributes as $key => $value) {
            $html .= sprintf(" %s=\"%s\"", $key, $value);
        }
        return $html;
    }
    return $attributes;
}

/**
 * Determines the version of zencart (Used to include Resources for old versions)
 * @return string
 */
function emp_get_zencart_version()
{
    global $db;

    $version = PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;

    $check_hist_query = "SELECT
                            * from " . TABLE_PROJECT_VERSION . "
                         WHERE project_version_key = 'Zen-Cart Database'
                         ORDER BY project_version_date_applied DESC LIMIT 1";
    $check_hist_details = $db->Execute($check_hist_query);
    if (!$check_hist_details->EOF) {
        $version =
            $check_hist_details->fields['project_version_major'] .
            '.' .
            $check_hist_details->fields['project_version_minor'];
    }

    return $version;
}

/**
 * Get Place Holder for Setting InputBox
 * @param string $key
 * @return null|string
 */
function emp_get_module_setting_placeholder($key)
{
    if (emp_get_string_ends_with($key, "PAGE_TITLE")) {
        return "This name will be displayed on the checkout page";
    } elseif (emp_get_string_ends_with($key, "USERNAME")) {
        return "Enter your Genesis Username here";
    } elseif (emp_get_string_ends_with($key, "PASSWORD")) {
        return "Enter your Genesis Password here";
    } elseif (emp_get_string_ends_with($key, "TOKEN")) {
        return "Enter your Genesis Token here";
    }

    return null;
}

/**
 * Check if string starts with a specific value
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function emp_get_string_starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * Check if string ends with a specific value
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function emp_get_string_ends_with($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
