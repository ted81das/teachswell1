<?php

class Mainwp_WPvivid_Extension_Setting
{
    private static $order = '';
    private static $orderby = '';

    static function mwp_wpvivid_get_getdata_authed( $website, $paramValue, $paramName = 'where', $open_location = '' ) {
        $params = array();
        if ( $website && '' != $paramValue ) {
            $nonce = rand( 0, 9999 );
            if ( (0 == $website->nossl) && function_exists( 'openssl_verify' ) ) {
                $nossl = 0;
                openssl_sign( $paramValue . $nonce, $signature, base64_decode( $website->privkey ) );
            } else {
                $nossl = 1;
                $signature = md5( $paramValue . $nonce . $website->nosslkey );
            }
            $signature = base64_encode( $signature );

            $params = array(
                'login_required' => 1,
                'user' => $website->adminname,
                'mainwpsignature' => rawurlencode( $signature ),
                'nonce' => $nonce,
                'nossl' => $nossl,
                'open_location' => $open_location,
                $paramName => rawurlencode( $paramValue ),
            );
        }

        $url = (isset( $website->siteurl ) && $website->siteurl != '' ? $website->siteurl : $website->url);
        $url .= (substr( $url, -1 ) != '/' ? '/' : '');
        $url .= '?';

        foreach ( $params as $key => $value ) {
            $url .= $key . '=' . $value . '&';
        }

        return rtrim( $url, '&' );
    }

    static public function open_site(){
        global $mainwp_wpvivid_extension_activator;
        $id = '';
        if(isset($_GET['websiteid'])) {
            $id = sanitize_text_field($_GET['websiteid']);
        }
        $websites = apply_filters('mainwp_getdbsites', $mainwp_wpvivid_extension_activator->childFile, $mainwp_wpvivid_extension_activator->childKey, array($id), array());
        $website = null;
        if ( $websites && is_array( $websites ) ) {
            $website = current( $websites );
        }
        $open_location = '';
        if ( isset( $_GET['open_location'] ) ) {
            $open_location = sanitize_text_field($_GET['open_location']);
        }
        ?>
        <div style="font-size: 30px; text-align: center; margin-top: 5em;"><?php _e('You will be redirected to your website immediately.'); ?></div>
        <form method="POST" action="<?php esc_attr_e(Mainwp_WPvivid_Extension_Setting::mwp_wpvivid_get_getdata_authed( $website, 'index.php', 'where', $open_location )); ?>" id="redirectForm"></form>
        <?php
    }

    static public function mwp_wpvivid_check_report($website_id, $is_pro, $website_name){
        global $mainwp_wpvivid_extension_activator;
        $mainwp_wpvivid_extension_activator->render_check_report_page($website_id, $is_pro, $website_name);
    }

    static public function renderSetting()
    {
        if ( isset( $_GET['action'] ) && 'mwpWPvividOpenSite' == sanitize_text_field($_GET['action'])) {
            self::open_site();
            return;
        }

        if(isset($_GET['check_report']) && isset($_GET['website_id']) && isset($_GET['pro']) && isset($_GET['website_name']))
        {
            $website_id = sanitize_text_field($_GET['website_id']);
            $is_pro = $_GET['pro'];
            $website_name = sanitize_text_field($_GET['website_name']);
            self::mwp_wpvivid_check_report($website_id, $is_pro, $website_name);
            return;
        }
        
        global $mainwp_wpvivid_extension_activator;

        $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites();

        $current_tab = 'dashboard';
        $dashboard_class='';
        $schedules_class='';
        $remote_class='';
        $settings_class='';
        $install_pro_class='';
        $login_pro_class='';
        $menu_cap_class='';
        $white_label_class='';
        if ( isset( $_GET['tab'] ) && ( 'settings' == sanitize_text_field($_GET['tab'])))
        {
            $current_tab = 'settings';
            $settings_class='active';
        }
        else if ( isset( $_GET['tab'] ) && ('schedules' == sanitize_text_field($_GET['tab'])))
        {
            $current_tab = 'schedules';
            $schedules_class='active';
        }
        else if ( isset( $_GET['tab'] ) && ('remote' == sanitize_text_field($_GET['tab'])))
        {
            $current_tab = 'remote';
            $remote_class='active';
        }
        else if( isset( $_GET['tab'] ) && ('install' == sanitize_text_field($_GET['tab'])) )
        {
            $current_tab = 'install';
            $install_pro_class='active';
        }
        else if( isset( $_GET['tab'] ) && ('login' == sanitize_text_field($_GET['tab'])) )
        {
            $current_tab = 'login';
            $login_pro_class = 'active';
        }
        else if( isset( $_GET['tab'] ) && ('menu' == sanitize_text_field($_GET['tab'])) )
        {
            $current_tab = 'menu';
            $menu_cap_class = 'active';
        }
        else if( isset( $_GET['tab'] ) && ('white_label' == sanitize_text_field($_GET['tab'])) )
        {
            $current_tab = 'white_label';
            $white_label_class = 'active';
        }
        else
        {
            $dashboard_class='active';
        }

        $switch_pro_page = $mainwp_wpvivid_extension_activator->get_global_switch_pro_setting_page();
        if($switch_pro_page !== false){
            if(intval($switch_pro_page) === 1){
                $mainwp_wpvivid_extension_activator->set_global_switch_pro_setting_page(0);
                $login_options = $mainwp_wpvivid_extension_activator->get_global_login_addon();
                if($login_options === false || !isset($login_options['wpvivid_pro_account'])){
                    $current_tab = 'login';
                    $login_pro_class = 'active';
                }
            }
        }

        $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
        $dashboard_link = '<a id="mwp_wpvivid_dashboard_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=dashboard" class="item '.esc_attr($dashboard_class).'">WPvivid Backup Dashboard</a>';
        $scheduled_link = '<a id="mwp_wpvivid_scheduled_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=schedules" class="item '.esc_attr($schedules_class).'">Schedule</a>';
        $backups_href = '<a id="mwp_wpvivid_remote_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=remote" class="item '.esc_attr($remote_class).'">Remote</a>';
        $settings_href = '<a id="mwp_wpvivid_setting_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=settings" class="item '.esc_attr($settings_class).'">Setting</a>';
        $install_href = '<a id="mwp_wpvivid_install_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=install" class="item '.esc_attr($install_pro_class).'">Install</a>';
        if($select_pro){
            $login_href = '<a id="mwp_wpvivid_login_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=login" class="item '.esc_attr($login_pro_class).'">Login</a>';
            $menu_href = '<a id="mwp_wpvivid_menu_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=menu" class="item '.esc_attr($menu_cap_class).'">Modules</a>';
            $white_label_href = '<a id="mwp_wpvivid_white_label_tab_lnk" href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=white_label" class="item '.esc_attr($white_label_class).'">White Label</a>';
        }
        else{
            $login_href = '';
            $menu_href = '';
            $white_label_href = '';
        }

        $is_first = '0';
        $sync_first = $mainwp_wpvivid_extension_activator->get_global_first_init();
        if($sync_first === 'first'){
            $is_first = '1';
            $mainwp_wpvivid_extension_activator->set_global_first_init('not first');
        }

        ?>
        <div class="ui labeled icon inverted menu mainwp-sub-submenu">
            <?php echo __($dashboard_link);echo __($scheduled_link); echo __($backups_href); echo __($settings_href); echo __($login_href); echo __($menu_href); echo __($white_label_href); ?>
        </div>
        <?php if ($current_tab == 'dashboard'){ ?>
        <div style="background-color: #fff;">
            <div id="mwp_wpvivid_dashboard_tab">
                <?php
                $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
                $mainwp_wpvivid_extension_activator->dashboard->set_dashboard_info($select_pro);
                $mainwp_wpvivid_extension_activator->dashboard->render();
                ?>
            </div>
        </div>
        <!--<?php self::gen_select_sites(); ?>
        <div id="mwp_wpvivid_dashboard_tab" style="margin: 20px; background-color: #fff;">
            <?php self::get_dashboard_tab($websites_with_plugin); ?>
        </div>-->
    <?php } ?>
        <?php if ($current_tab == 'settings'){ ?>
        <div style="background-color: #fff;">
            <div id="mwp_wpvivid_settings_tab">
                <?php
                $setting=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings', array());
                $setting_addon=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings_addon', array());
                $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
                $mainwp_wpvivid_extension_activator->setting->set_setting_info($setting, $setting_addon, $select_pro);
                $mainwp_wpvivid_extension_activator->setting->render(true, true);
                ?>
            </div>
        </div>
    <?php } ?>
        <?php if ($current_tab == 'schedules'){ ?>
        <div style="background-color: #fff;">
            <div id="mwp_wpvivid_scheduled_tab">
                <?php
                $schedule=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule', array());
                $schedule_addon=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_addon', array());
                $custom_setting=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('backup_custom_setting', array());
                $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
                $mainwp_wpvivid_extension_activator->schedule->set_schedule_info($schedule, $schedule_addon, $custom_setting, 0, $select_pro);
                $mainwp_wpvivid_extension_activator->schedule->render(true, true);
                ?>
            </div>
        </div>
    <?php } ?>
        <?php if ($current_tab == 'remote'){ ?>
        <div style="background-color: #fff;">
            <div id="mwp_wpvivid_remote_tab">
                <?php
                $remote=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote', array());
                $remote_addon=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
                $mainwp_wpvivid_extension_activator->remote_page->set_remote_info($remote, $remote_addon, $select_pro);
                $mainwp_wpvivid_extension_activator->remote_page->render(true, true);
                ?>
            </div>
        </div>
    <?php } ?>
        <?php
        if($current_tab == 'install'){
            ?>
            <?php self::gen_select_install_sites(); ?>
            <div id="mwp_wpvivid_install_tab" style="margin: 20px; background-color: #fff;">
                <?php self::get_install_tab($websites_with_plugin); ?>
            </div>
            <?php
        }
        ?>
        <?php
        if($current_tab == 'login'){
            ?>
            <div style="background-color: #fff;">
                <div id="mwp_wpvivid_login_tab" style="margin: 20px; background-color: #fff;">
                    <?php
                    $mainwp_wpvivid_extension_activator->login->render();
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if($current_tab == 'menu'){
            ?>
            <div style="background-color: #fff;">
                <div id="mwp_wpvivid_menu_tab">
                    <?php
                    $capability_addon = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('menu_capability', array());
                    if(empty($capability_addon)){
                        $capability_addon = array();
                        $capability_addon['menu_manual_backup'] = '1';
                        $capability_addon['menu_export_site'] = '1';
                        $capability_addon['menu_import_site'] = '1';
                        $capability_addon['menu_backup_schedule'] = '1';
                        $capability_addon['menu_backup_restore'] = '1';
                        $capability_addon['menu_cloud_storage'] = '1';
                        $capability_addon['menu_image_optimization'] = '1';
                        $capability_addon['menu_unused_image_cleaner'] = '1';
                        $capability_addon['menu_export_import'] = '1';
                        $capability_addon['menu_role_capabilities'] = '1';
                        $capability_addon['menu_setting'] = '1';
                        $capability_addon['menu_debug'] = '1';
                        //$capability_addon['menu_tools'] = '1';
                        $capability_addon['menu_pro_page'] = '1';
                    }

                    if(!isset($capability_addon['menu_manual_backup']))
                    {
                        $capability_addon = array();
                        $capability_addon['menu_manual_backup'] = '1';
                        $capability_addon['menu_export_site'] = '1';
                        $capability_addon['menu_import_site'] = '1';
                        $capability_addon['menu_backup_schedule'] = '1';
                        $capability_addon['menu_backup_restore'] = '1';
                        $capability_addon['menu_cloud_storage'] = '1';
                        $capability_addon['menu_image_optimization'] = '1';
                        $capability_addon['menu_unused_image_cleaner'] = '1';
                        $capability_addon['menu_export_import'] = '1';
                        $capability_addon['menu_role_capabilities'] = '1';
                        $capability_addon['menu_setting'] = '1';
                        $capability_addon['menu_debug'] = '1';
                        //$capability_addon['menu_tools'] = '1';
                        $capability_addon['menu_pro_page'] = '1';
                    }

                    $mainwp_wpvivid_extension_activator->capability->set_capability_info($capability_addon);
                    $mainwp_wpvivid_extension_activator->capability->render(true, true);
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if($current_tab == 'white_label'){
            ?>
            <div style="background-color: #fff;">
                <div id="mwp_wpvivid_white_label_tab">
                    <?php
                    $white_label_addon = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('white_label_setting', array());
                    if(empty($white_label_addon)){
                        $white_label_addon = array();
                    }
                    $mainwp_wpvivid_extension_activator->white_label->set_white_label_info($white_label_addon);
                    $mainwp_wpvivid_extension_activator->white_label->render(true, true);
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <script>
            var is_first = '<?php echo $is_first; ?>';
            if(is_first === '1'){
                var descript = 'WPvivid Backup Pro is detected to be installed in your child sites. Do you want to automatically switch to the settings of WPvivid Backup Pro? You can switch to the settings of WPvivid backup free version manually later.';
                var ret = confirm(descript);
                if(ret === true) {
                    mwp_wpvivid_auto_switch_pro_setting(1);
                }
            }

            function mwp_wpvivid_auto_switch_pro_setting(pro_setting){
                var ajax_data = {
                    'action': 'mwp_wpvivid_switch_pro_setting',
                    'pro_setting': pro_setting
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            location.reload();
                        }
                        else {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public static function gen_select_install_sites() {
        ?>
        <div class="mainwp-actions-bar">
            <div class="ui grid">
                <div class="ui two column row">
                    <div class="column">
                        <select class="ui dropdown" id="mwp_wpvivid_plugin_action">
                            <option value="install-selected"><?php _e( 'Install the selected plugins', 'mainwp-pagespeed-extension' ); ?></option>
                        </select>
                        <input type="button" value="<?php _e( 'Apply' ); ?>" class="ui basic button action" id="mwp_wpvivid_plugin_doaction_btn">
                        <?php do_action( 'mainwp_updraftplus_actions_bar_right' ); ?>
                    </div>
                    <div class="right aligned column">
                        <?php do_action( 'mainwp_updraftplus_actions_bar_right' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function get_install_tab($websites_with_plugin){
        $selected_group=0;
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) )
        {
            $selected_group = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }
        ?>
        <table class="ui single line table" id="mwp_wpvivid_install_table">
            <thead>
            <tr>
                <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                <th><?php _e('Site'); ?></th>
                <th><?php _e('Status'); ?></th>
            </tr>
            </thead>
            <tbody id="the-mwp-wpvivid-list">
            <?php
            if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 )
            {
                self::get_install_websites_row($websites_with_plugin,$selected_group);
            }
            else {
                _e( '<tr><td colspan="9">No websites were found with the WPvivid Backup plugin installed.</td></tr>' );
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                <th><?php _e('Site'); ?></th>
                <th><?php _e('Status'); ?></th>
            </tr>
            </tfoot>
        </table>
        <script>
            jQuery( '#mwp_wpvivid_install_table' ).DataTable( {
                "columnDefs": [ { "orderable": false, "targets": "no-sort" } ],
                "order": [ [ 1, "asc" ] ],
                "language": { "emptyTable": "No websites were found with the WPvivid Backup plugin installed." },
                "drawCallback": function( settings ) {
                    jQuery('#mwp_wpvivid_install_table .ui.checkbox').checkbox();
                    jQuery( '#mwp_wpvivid_install_table .ui.dropdown').dropdown();
                },
            } );
        </script>
        <?php
    }

    public static function get_install_websites_row($websites,$selected_group=0){
        foreach ( $websites as $website )
        {
            if(isset($website['install'])){
                $need_install = intval($website['install']) === 1 ? '' : 'need';
                $status = intval($website['install']) === 1 ? 'Has been installed' : 'Not install';
            }
            else{
                $need_install = 'need';
                $status = 'Not install';
            }
            $website_id = $website['id'];
            ?>
            <tr class="<?php esc_attr_e($need_install); ?>" website-id="<?php esc_attr_e($website_id); ?>">
                <td class="check-column"><span class="ui checkbox"><input type="checkbox" name="checked[]"></span></td>
                <td class="website-name"><a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes( $website['name'] )); ?></a></td>
                <td><span class="installing"></span><span class="mwp-wpvivid-status"><?php _e($status); ?></span></td>
            </tr>
            <?php
        }
    }

    public static function gen_select_sites() {
        ?>
        <div class="mainwp-actions-bar">
            <div class="ui grid">
                <div class="ui two column row">
                    <div class="column">
                        <select class="ui dropdown" id="mwp_wpvivid_plugin_action">
                            <option value="update-selected"><?php _e( 'Update the selected plugins', 'mainwp-pagespeed-extension' ); ?></option>
                        </select>
                        <input type="button" value="<?php _e( 'Apply' ); ?>" class="ui basic button action" id="mwp_wpvivid_plugin_doaction_btn">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    static public function get_dashboard_tab($websites_with_plugin)
    {
        self::$order = '';
        self::$orderby = '';

        $site_order='desc';
        $url_order='desc';
        $version_order='desc';
        $setting_order='desc';
        if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) )
        {
            self::$orderby = sanitize_text_field($_GET['orderby']);
        }
        if ( isset( $_GET['order'] ) && ! empty( $_GET['order'] ) )
        {
            self::$order = sanitize_text_field($_GET['order']);
        }

        if(!empty(self::$order)&&!empty(self::$orderby))
        {
            if(self::$orderby=='site'&&$site_order==self::$order)
            {
                $site_order='asc';
            }
            if(self::$orderby=='url'&&$url_order==self::$order)
            {
                $url_order='asc';
            }
            if(self::$orderby=='version'&&$version_order==self::$order)
            {
                $version_order='asc';
            }
            if(self::$orderby=='setting'&&$setting_order==self::$order)
            {
                $setting_order='asc';
            }
        }
        if(!empty(self::$order)&&!empty(self::$orderby))
        {
            usort( $websites_with_plugin, array( 'Mainwp_WPvivid_Extension_Setting', 'sort_websites' ) );
        }
        global $mainwp_wpvivid_extension_activator;
        $selected_group=0;
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) )
        {
            $selected_group = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }
        $groups = apply_filters( 'mainwp_getgroups',$mainwp_wpvivid_extension_activator->childFile, $mainwp_wpvivid_extension_activator->childKey, null );

        $search = (isset( $_GET['search'] ) && ! empty( $_GET['search'] )) ? trim( sanitize_text_field($_GET['search']) ) : '';

        $has_update = false;
        foreach ( $websites_with_plugin as $website ) {
            $website_id = $website['id'];
            $class_active = (isset($website['active']) && !empty($website['active'])) ? '' : 'negative';
            if ($website['pro']) {
                $need_update = $mainwp_wpvivid_extension_activator->get_need_update($website_id);
                $class_update = $need_update == '1' ? 'warning' : '';
            } else {
                $class_update = (isset($website['upgrade'])) ? 'warning' : '';
            }
            $class_update = ( 'negative' == $class_active ) ? 'negative' : $class_update;
            if($class_update === 'warning'){
                $has_update = true;
            }
        }

        $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
        if($select_pro){
            if($has_update){
                ?>
                <div class="notice notice-warning is-dismissible inline" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>There are plugins available to update. Select the checkboxes of websites in list and click on Apply button to start updating.</p>
                    <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
                <?php
            }
            ?>

            <table class="ui single line table" id="mwp_wpvivid_sites_table">
                <thead>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </thead>
                <tbody id="the-mwp-wpvivid-list">
                <?php
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 )
                {
                    self::get_websites_row($websites_with_plugin,$selected_group);
                }
                else {
                    _e( '<tr><td colspan="9">No websites were found with the WPvivid Backup plugin installed.</td></tr>' );
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </tfoot>
            </table>
            <?php
        }
        else{
            ?>
            <div>
                wpvivid free
            </div>
            <?php
        }
        ?>

        <script>
            jQuery( '#mwp_wpvivid_sites_table' ).DataTable( {
                "columnDefs": [ { "orderable": false, "targets": "no-sort" } ],
                "order": [ [ 1, "asc" ] ],
                "language": { "emptyTable": "No websites were found with the WPvivid Backup plugin installed." },
                "drawCallback": function( settings ) {
                    jQuery('#mwp_wpvivid_sites_table .ui.checkbox').checkbox();
                    jQuery( '#mwp_wpvivid_sites_table .ui.dropdown').dropdown();
                },
            } );

            function mwp_wpvivid_active_plug(obj)
            {
                var parent = obj.parent().parent();
                var slug = parent.attr( 'plugin-slug' );
                var site_id = parent.attr( 'website-id' );

                var ajax_data = {
                    'action': 'mwp_wpvivid_active_plugin',
                    'websiteId': site_id,
                    'plugins[]': [slug]
                };
                obj.attr('class', '');
                obj.html('Activating...');
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    try{
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray.error!== undefined)
                        {
                            obj.html( '<font color="red">' + jsonarray.error + '</font>' );
                        }
                        else if (jsonarray.result!== undefined)
                        {
                            obj.html( 'WPvivid Backup plugin has been activated' );
                        }
                    }
                    catch (err) {
                        alert(err);
                        obj.attr('class', 'mwp_wpvivid_active_plugin');
                        obj.html('Activate WPvivid Backup plugin');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                    obj.attr('class', 'mwp_wpvivid_active_plugin');
                    obj.html('Activate WPvivid Backup plugin');
                });
            }
            function mwp_wpvivid_upgrade_plug(obj)
            {
                var parent = obj.parent().parent();
                var slug = parent.attr( 'plugin-slug' );
                var site_id = parent.attr( 'website-id' );
                var ajax_data = {
                    'action': 'mwp_wpvivid_upgrade_plugin',
                    'websiteId': site_id,
                    'type':'plugin',
                    'slugs[]': [slug]
                };
                obj.attr('class', '');
                obj.html('Upgrading...');
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    try{
                        if(data.error!== undefined)
                        {
                            obj.html( '<font color="red">' + data.error + '</font>' );
                        }
                        else
                        {
                            obj.html( 'WPvivid Backup plugin has been updated' );
                        }
                    }
                    catch (err) {
                        alert(err);
                        obj.attr('class', 'mwp_wpvivid_upgrade_plugin');
                        obj.html('Update WPvivid Backup plugin');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                    obj.attr('class', 'mwp_wpvivid_upgrade_plugin');
                    obj.html('Update WPvivid Backup plugin');
                });
            }
            jQuery( '.mwp_wpvivid_active_plugin' ).on('click', function ()
            {
                if(jQuery( this ).attr('class')==='mwp_wpvivid_active_plugin')
                    mwp_wpvivid_active_plug( jQuery( this ), false );
                return false;
            });
            jQuery( '.mwp_wpvivid_upgrade_plugin' ).on('click', function ()
            {
                if(jQuery( this ).attr('class')==='mwp_wpvivid_upgrade_plugin')
                    mwp_wpvivid_upgrade_plug( jQuery( this ), false );
                return false;
            });
        </script>
        <?php
    }

    static public function get_websites_row($websites,$selected_group=0)
    {
        global $mainwp_wpvivid_extension_activator;
        $plugin_name = 'WPvivid Backup';
        foreach ( $websites as $website )
        {
            $website_id = $website['id'];
            if($website['individual']) {
                $individual='Individual';
            }
            else {
                $individual='General';
            }
            $class_active = ( isset( $website['active'] ) && ! empty( $website['active'] ) ) ? '' : 'negative';
            if($website['pro']){
                $need_update = $mainwp_wpvivid_extension_activator->get_need_update($website_id);
                $class_update = $need_update == '1' ? 'warning' : '';
                $latest_version = $mainwp_wpvivid_extension_activator->get_latest_version($website_id);
                if($latest_version == ''){
                    $latest_version = $mainwp_wpvivid_extension_activator->get_current_version($website_id);
                }
            }
            else {
                $class_update = (isset($website['upgrade'])) ? 'warning' : '';
                $latest_version = (isset($website['upgrade']['new_version'])) ? $website['upgrade']['new_version'] : $website['version'];
            }
            $class_update = ( 'negative' == $class_active ) ? 'negative' : $class_update;
            $plugin_slug = ( isset( $website['slug'] ) ) ? $website['slug'] : '';

            ?>
            <tr class="<?php esc_attr_e($class_active.' '.$class_update); ?>" website-id="<?php esc_attr_e($website_id); ?>" plugin-name="<?php esc_attr_e($plugin_name); ?>" plugin-slug="<?php esc_attr_e($plugin_slug); ?>" is-pro="<?php esc_attr_e($website['pro']); ?>" version="<?php esc_attr_e(isset($website['version']) ? $website['version'] : ''); ?>" latest-version="<?php esc_attr_e($latest_version); ?>">
                <td class="check-column"><span class="ui checkbox"><input type="checkbox" name="checked[]"></span></td>
                <td class="website-name"><a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes( $website['name'] )); ?></a></td>
                <td><a href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php esc_attr_e($website_id); ?>&_opennonce=<?php echo wp_create_nonce( 'mainwp-admin-nonce' ); ?>" target="_blank"><i class="sign in icon"></i></a></td>
                <td><a href="<?php esc_attr_e($website['url']); ?>" target="_blank"><?php _e($website['url']); ?></a></td>
                <td><a onclick="mwp_wpvivid_check_report('<?php esc_attr_e($website['id']); ?>', '<?php esc_attr_e($website['pro']); ?>', '<?php esc_attr_e($website['name']) ?>');" style="cursor: pointer;">Report</a></td>
                <td><span class="updating"></span><span class="mwp-wpvivid-current-version">
                    <?php
                    if($website['pro']){
                        $version = $mainwp_wpvivid_extension_activator->get_current_version($website_id);
                        _e($version.' (WPvivid Backup Pro)');
                    }
                    else{
                        $version = isset($website['version']) ? $website['version'] : '';
                        _e($version.' (WPvivid Backup)');
                    }
                    ?>
                    </span>
                </td>
                <td>
                    <span class="mwp-wpvivid-status">
                    <?php
                    if($website['pro']){
                        $need_update = $mainwp_wpvivid_extension_activator->get_need_update($website_id);
                        _e($need_update == '1' ? 'New version available' : 'Latest version');
                    }
                    else{
                        _e(isset($website['upgrade']) ? 'New version available' : 'Latest version');
                    }
                    ?>
                    </span>
                </td>
                <td><span><?php _e($individual); ?></span></td>
                <td><span><a href="admin.php?page=ManageSitesWPvivid&id=<?php esc_attr_e($website_id); ?>"><i class="fa fa-hdd-o"></i> <?php _e( 'Backup Now', 'mainwp-wpvivid-extension' ); ?></a></span></td>
            </tr>
            <?php
        }
        ?>
        <script>
            function mwp_wpvivid_check_report(website_id, is_pro, website_name){
                window.location.href = window.location.href + "&check_report=1&website_id="+website_id+"&pro="+is_pro+"&website_name="+website_name;
            }
        </script>
        <?php
    }

    static public function sort_websites($a, $b)
    {
        if ( 'version' == self::$orderby )
        {
            $a = $a['version'];
            $b = $b['version'];
            $cmp = version_compare( $a, $b );
        } else if ( 'url' == self::$orderby ) {
            $a = $a['url'];
            $b = $b['url'];
            $cmp = strcmp( $a, $b );
        } else if ( 'setting' == self::$orderby ) {
            $a = $a['individual'];
            $b = $b['individual'];
            $cmp = $a - $b;
        } else {
            $a = $a['name'];
            $b = $b['name'];
            $cmp = strcmp( $a, $b );
        }
        if ( 0 == $cmp ) {
            return 0; }

        if ( 'desc' == self::$order )
        {
            return ($cmp > 0) ? -1 : 1;
        } else {
            return ($cmp > 0) ? 1 : -1;
        }
    }
}