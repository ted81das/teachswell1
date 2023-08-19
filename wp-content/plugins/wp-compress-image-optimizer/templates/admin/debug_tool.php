<?php global $wps_ic, $wpdb;

if (!empty($_POST['wps_settings'])) {
  $settings = stripslashes($_POST['wps_settings']);
  $settings = json_decode($settings, true, JSON_UNESCAPED_SLASHES);
  if (is_array($settings)) {
    update_option(WPS_IC_SETTINGS, $settings);
  }
}

if (!empty($_GET['delete_option'])) {
  delete_option($_GET['delete_option']);
}

if (!empty($_GET['debug_img'])) {
  $imageID = $_GET['debug_img'];
  $debug = get_post_meta($imageID, 'ic_debug', true);
  if (!empty($debug)) {
    foreach ($debug as $i => $msg) {
      echo $msg . '<br/>';
    }
  }
  die();
}

//list of api endpoints
$servers = [
	'auto' => 'Auto',
	'vancouver.zapwp.net' => 'Canada',
	'nyc.zapwp.net' => 'New York',
	'la2.zapwp.net' => 'LA2',
	'singapore.zapwp.net' => 'Singapore',
	'dallas.zapwp.net' => 'Dallas',
	'sydney.zapwp.net' => 'Sydney',
	'india.zapwp.net' => 'India',
	'frankfurt.zapwp.net' => 'Germany'
];

if (!empty($_POST['local_server'])){
    $local_server = $_POST['local_server'];
    update_option('wps_ic_force_local_server', $local_server);
} else {
    $local_server = get_option('wps_ic_force_local_server');
    if ($local_server === false || empty($local_server)){
	    $local_server = 'auto';
    }
}

?>

<div style="display: none;" id="compress-test-results" class="ic-test-results">
    <textarea id="compress-test-results-textarea" style="visibility: hidden;opacity: none;"></textarea>
    <div class="results-inner">
        <span class="ic-terminal-dot blink"><span></span></span>
    </div>
    <a href="#" class="copy-debug">Copy Debug Results</a>
</div>

<table id="information-table" class="wp-list-table widefat fixed striped posts">
    <thead>
    <tr>
        <th>Check Name</th>
        <th>Value</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Enable PHP Debug</td>
        <td colspan="3">
            <p>
              <?php
              if (!empty($_GET['php_debug'])) {
                update_option('wps_ic_debug', sanitize_text_field($_GET['php_debug']));
              }

              $debugPhp = get_option('wps_ic_debug');

              if (!$debugPhp || $debugPhp == 'false') {
                echo '<a href="' . admin_url('admin.php?page=' . $wps_ic::$slug . '&view=debug_tool&php_debug=true') . '" class="button-primary" style="margin-right:20px;">Enable</a>';
              } else {
                echo '<a href="' . admin_url('admin.php?page=' . $wps_ic::$slug . '&view=debug_tool&php_debug=false') . '" class="button-primary" style="margin-right:20px;">Disable</a>';
              }
              ?>
                If you are having any sort of issues with our plugin, enabling this option will give you some basic debug output in Console log of your browser.
            </p>
        </td>
    </tr>    <tr>
        <td>Enable JavaScript Debug</td>
        <td colspan="3">
            <p>
              <?php
              if (!empty($_GET['js_debug'])) {
                update_option('wps_ic_js_debug', sanitize_text_field($_GET['js_debug']));
              }

              if (get_option('wps_ic_js_debug') == 'false') {
                echo '<a href="' . admin_url('admin.php?page=' . $wps_ic::$slug . '&view=debug_tool&js_debug=true') . '" class="button-primary" style="margin-right:20px;">Enable</a>';
              } else {
                echo '<a href="' . admin_url('admin.php?page=' . $wps_ic::$slug . '&view=debug_tool&js_debug=false') . '" class="button-primary" style="margin-right:20px;">Disable</a>';
              }
              ?>
                If you are having any sort of issues with our plugin, enabling this option will give you some basic debug output in Console log of your browser.
            </p>
        </td>
    </tr>

    <tr>
        <td>Generate Image JSON</td>
        <td colspan="3">
            <p>
              <?php
              if (!empty($_POST['wpc_image_id'])) {
                $uncompressedImages = array();
                $image_id = sanitize_text_field($_POST['wpc_image_id']);

                global $_wp_additional_image_sizes;

                $default_image_sizes = get_intermediate_image_sizes();

                foreach ($default_image_sizes as $size) {
                  $image_sizes[$size]['width'] = intval(get_option("{$size}_size_w"));
                  $image_sizes[$size]['height'] = intval(get_option("{$size}_size_h"));
                  $image_sizes[$size]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
                }

                if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
                  $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
                }

                $AdditionalSizes = array('full');
                foreach ($AdditionalSizes as $size) {
                  $image_sizes[$size]['width'] = 'full';
                }

                $image_sizes['original']['width'] = 'original';

                foreach ($image_sizes as $sizeName => $sizeData) {
                  if ($sizeName == 'original') {
                    $fileUrl = wp_get_original_image_url($image_id);
                  } else {
                    $fileUrl = wp_get_attachment_image_url($image_id, $sizeName);
                  }

                  //set_transient('wps_ic_compress_' . $image->ID, 'compressing');
                  $uncompressedImages[$image_id][$sizeName] = $fileUrl;
                }

                echo '<p style="max-width: 100%;">';
                echo json_encode($uncompressedImages);
                echo '</p>';
              }
              ?>
            <form method="post" action="#">
                <label>Image ID:</label>
                <input type="text" name="wpc_image_id" value="" placeholder="Image id from Media Library"/>
                <input type="submit" value="Debug"/>
            </form>
            </p>
        </td>
    </tr>

    <tr>
        <td>Generate Ajax Params</td>
        <td colspan="3">
            <p>
              <?php
              $parameters = get_option(WPS_IC_SETTINGS);
              $translatedParameters = array();
              if (isset($parameters['generate_webp'])) {
                $translatedParameters['webp'] = $parameters['generate_webp'];
              }

              if (isset($parameters['retina'])) {
                $translatedParameters['retina'] = $parameters['retina'];
              }

              if (isset($parameters['qualityLevel'])) {
                $translatedParameters['quality'] = $parameters['qualityLevel'];
              }

              if (isset($parameters['preserve_exif'])) {
                $translatedParameters['exif'] = $parameters['preserve_exif'];
              }

              if (isset($parameters['max_width'])) {
                $translatedParameters['max_width'] = $parameters['max_width'];
              } else {
                $translatedParameters['max_width'] = WPS_IC_MAXWIDTH;
              }

              echo json_encode($translatedParameters);
              ?>
            </p>
        </td>
    </tr>

    <tr>
        <td>Thumbnails</td>
        <td colspan="3">
          <?php
          $sizes = get_intermediate_image_sizes();
          echo 'Total Thumbs: ' . count($sizes);
          echo print_r($sizes, true);
          ?>
        </td>
    </tr>
    <tr>
        <td>Paths</td>
        <td colspan="3">
          <?php
          echo 'Debug Log: ' . WPS_IC_DIR . 'debug-log-' . date('d-m-Y') . '.txt';
          echo '<br/>Debug Log URI: <a href="' . WPS_IC_URI . 'debug-log-' . date('d-m-Y') . '.txt">' . WPS_IC_URI . 'debug-log-' . date('d-m-Y') . '.txt' . '</a>';
          ?>
        </td>
    </tr>
    <tr>
        <td>Excluded List</td>
        <td colspan="3">
          <?php
          $excluded = get_option('wps_ic_exclude_list');
          echo print_r($excluded, true);
          ?>
        </td>
    </tr>
    <tr>
        <td>API Key</td>
        <td colspan="3">
          <?php
          $options = get_option(WPS_IC_OPTIONS);
          echo $options['api_key'];
          ?>
        </td>
    </tr>
    <tr>
        <td>CDN Zone Name</td>
        <td>
          <?php
          echo get_option('ic_cdn_zone_name');
          ?>
        </td>
        <td>
            <a href="<?php echo admin_url('options-general.php?page=' . $wps_ic::$slug . '&view=debug_tool&delete_option=ic_cdn_zone_name'); ?>">Delete</a>
        </td>
        <td></td>
    </tr>
    <tr>
        <td>Custom CDN Zone Name</td>
        <td>
          <?php
          echo get_option('ic_custom_cname');
          ?>
        </td>
        <td>
            <a href="<?php echo admin_url('options-general.php?page=' . $wps_ic::$slug . '&view=debug_tool&delete_option=ic_custom_cname'); ?>">Delete</a>
        </td>
        <td></td>
    </tr>

    <tr>
        <td>Plugin Activated</td>
        <td><?php
          if (is_plugin_active('wp-compress-image-optimizer/wp-compress.php')) {
            echo 'Yes';
            $status = 'OK';
          } else {
            echo 'No';
            $status = 'BAD';
          }
          ?></td>
        <td><?php echo $status; ?></td>
        <td>None</td>
    </tr>
    <tr>
        <td>PHP Version</td>
        <td>
          <?php
          $version = phpversion();
          echo $version;
          if (version_compare($version, '7.0', '>=')) {
            $status = 'OK';
          } else {
            $status = 'BAD';
          }
          ?>
        </td>
        <td><?php echo $status; ?></td>
        <td>None</td>
    </tr>
    <tr>
        <td>WP Version</td>
        <td>
          <?php
          $wp_version = get_bloginfo('version');
          echo $wp_version;
          if (version_compare($wp_version, '5.0', '>=')) {
            $status = 'OK';
          } else {
            $status = 'BAD';
          }
          ?>
        </td>
        <td>
          <?php
          echo $status;
          ?>
        </td>
        <td>
            None
        </td>
    </tr>
    <tr>
        <td>Options</td>
        <td colspan="3">
            <button class="wps_copy_button button-primary" data-field="options" style="float:right">Copy text</button>
            <textarea id="wps_options_field" style="width:100%"><?php
              echo json_encode(get_option(WPS_IC_OPTIONS));
              ?>
          </textarea>
        </td>
    </tr>
    <tr>
        <td>Settings</td>
        <td colspan="3">
            <button class="wps_copy_button button-primary" data-field="settings" style="float:right">Copy text</button>
            <form method="post" action="<?php echo admin_url('options-general.php?page=' . $wps_ic::$slug . '&view=debug_tool') ?>">
           <textarea id="wps_settings_field" name="wps_settings" style="width:100%;height:150px;"><?php
             echo json_encode(get_option(WPS_IC_SETTINGS));
             ?>
           </textarea>
                <input type="submit" value="Save Settings" class="button-primary" style="float:right">
            </form>
        </td>
    </tr>
    <tr>
        <td>Local server API</td>
        <td colspan="3">
            <form method="post" action="<?php echo admin_url('options-general.php?page=' . $wps_ic::$slug . '&view=debug_tool') ?>">
                <label for="server">Server:</label>
                <select id="server" name="local_server">
	            <?php
	            foreach ($servers as $value => $label) {
		            $selected = ($local_server == $value) ? 'selected' : '';
		            echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
	            }
	            ?>
                </select>
                <input type="submit" value="Save Server" class="button-primary" style="float:right">
            </form>
        </td>
    </tr>
    </tbody>
</table>


<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('.wps_copy_button').on('click', function () {
            var field = $(this).attr("data-field")
            console.log(field);
            var text = document.getElementById('wps_' + field + '_field');

            // Copy the text inside the text field
            navigator.clipboard.writeText(text.value);

            // Alert the copied text
            alert('Copied to Clipboard');
        })

    });
</script>