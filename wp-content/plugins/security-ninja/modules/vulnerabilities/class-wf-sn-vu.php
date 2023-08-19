<?php

namespace WPSecurityNinja\Plugin;

if ( !function_exists( 'add_action' ) ) {
    die( 'Please don\'t open this file directly!' );
}
define( 'WF_SN_VU_OPTIONS_NAME', 'wf_sn_vu_settings_group' );
define( 'WF_SN_VU_OPTIONS_KEY', 'wf_sn_vu_settings' );
define( 'WF_SN_VU_RESULTS_KEY', 'wf_sn_vu_results' );
define( 'WF_SN_VU_VULNS', 'wf_sn_vu_vulns' );
define( 'WF_SN_VU_VULNS_NOTICE', 'wf_sn_vu_vulns_notice' );
define( 'WF_SN_VU_OUTDATED', 'wf_sn_vu_outdated' );
define( 'WF_SN_VU_LAST_EMAIL', 'wf_sn_vu_last_email' );
// Last time an email was sent
class Wf_Sn_Vu
{
    public static  $options ;
    public static  $vu_api_url = 'https://wpsecurityninja.sfo2.cdn.digitaloceanspaces.com/vulnerabilities.json' ;
    /**
     * init plugin
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Tuesday, January 12th, 2021.
     * @access	public static
     * @return	void
     */
    public static function init()
    {
        self::$options = self::get_options();
        add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
        add_filter( 'sn_tabs', array( __CLASS__, 'sn_tabs' ) );
        add_action( 'admin_notices', array( __CLASS__, 'admin_notice_vulnerabilities' ) );
        add_action( 'init', array( __CLASS__, 'schedule_cron_jobs' ) );
        add_action( 'do_action_secnin_update_vuln_list', array( __CLASS__, 'update_vuln_list' ) );
        add_action(
            'upgrader_process_complete',
            array( __CLASS__, 'do_action_upgrader_process_complete' ),
            10,
            2
        );
        add_action( 'delete_theme', array( __CLASS__, 'do_action_upgrader_process_complete' ) );
        add_action( 'delete_plugin', array( __CLASS__, 'do_action_upgrader_process_complete' ) );
    }
    
    /**
     * do_action_upgrader_process_complete.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, May 13th, 2022.
     * @access	public static
     * @return	void
     */
    public static function do_action_upgrader_process_complete()
    {
        $options = self::get_options();
        // No update if feature disabled
        
        if ( $options['enable_vulns'] ) {
            // deletes the transient before checking again
            delete_transient( 'wf_sn_return_vulnerabilities' );
            // Updates the vuln list
            self::return_vulnerabilities();
        }
    
    }
    
    /**
     * get_options.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	mixed
     */
    public static function get_options()
    {
        $options = get_option( WF_SN_VU_OPTIONS_NAME );
        $defaults = array();
        if ( !$options ) {
            $options = array(
                'enable_vulns'              => 1,
                'enable_outdated'           => 0,
                'enable_admin_notification' => 1,
                'enable_email_notice'       => 0,
                'email_notice_recipient'    => '',
            );
        }
        $return = array_merge( $defaults, $options );
        return $return;
    }
    
    /**
     * Register settings on admin init
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	void
     */
    public static function admin_init()
    {
        register_setting( WF_SN_VU_OPTIONS_NAME, WF_SN_VU_OPTIONS_NAME, array( __CLASS__, 'sanitize_settings' ) );
    }
    
    /**
     * Schedule cron jobs
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	void
     */
    public static function schedule_cron_jobs()
    {
        if ( !wp_next_scheduled( 'do_action_secnin_update_vuln_list' ) ) {
            wp_schedule_event( time() + 10, 'daily', 'do_action_secnin_update_vuln_list' );
        }
    }
    
    /**
     * Tab filter
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @param	mixed	$tabs	
     * @return	mixed
     */
    public static function sn_tabs( $tabs )
    {
        $vuln_tab = array(
            'id'       => 'sn_vuln',
            'class'    => '',
            'label'    => __( 'Vulnerabilities', 'security-ninja' ),
            'callback' => array( __CLASS__, 'render_vuln_page' ),
        );
        // Add number of vulns to the tab list
        $options = self::get_options();
        // Check if notification bubles enabled.
        
        if ( $options['enable_admin_notification'] ) {
            $return_vuln_count = self::return_vuln_count();
            if ( $return_vuln_count ) {
                $vuln_tab['count'] = $return_vuln_count;
            }
        }
        
        $done = 0;
        $tabcount = count( $tabs );
        for ( $i = 0 ;  $i < $tabcount ;  $i++ ) {
            
            if ( 'sn_vuln' === $tabs[$i]['id'] ) {
                $tabs[$i] = $vuln_tab;
                $done = 1;
                break;
            }
        
        }
        if ( !$done ) {
            $tabs[] = $vuln_tab;
        }
        return $tabs;
    }
    
    /**
     * Strips http:// or https://
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @global
     * @param	string	$url	Default: ''
     * @return	mixed
     */
    public static function remove_http( $url = '' )
    {
        if ( 'http://' === $url || 'https://' === $url ) {
            return $url;
        }
        $matches = substr( $url, 0, 7 );
        
        if ( 'http://' === $matches ) {
            $url = substr( $url, 7 );
        } else {
            $matches = substr( $url, 0, 8 );
            if ( 'https://' === $matches ) {
                $url = substr( $url, 8 );
            }
        }
        
        return $url;
    }
    
    /**
     * Updates the vulnerability list.
     * Creates the folder if necessary.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.	
     * @version	v1.0.1	Sunday, September 4th, 2022.
     * @access	public static
     * @return	void
     */
    public static function update_vuln_list()
    {
        $options = self::get_options();
        // No update if feature disabled
        if ( !$options['enable_vulns'] ) {
            return false;
        }
        // Get list of vulnerabilities from API
        $request_url = self::$vu_api_url;
        $request_url = add_query_arg( 'ver', wf_sn::$version, $request_url );
        $response = wp_remote_get( $request_url );
        
        if ( !is_wp_error( $response ) ) {
            $oldcount = false;
            $newcount = false;
            $old_data = get_option( WF_SN_VU_VULNS );
            $oldcount = 0;
            if ( $old_data ) {
                $oldcount = self::return_known_vuln_count();
            }
            $body = wp_remote_retrieve_body( $response );
            $sn_vulns_data = json_decode( $body );
            $sn_vulns_data->timestamp = time();
            update_option( WF_SN_VU_VULNS, $sn_vulns_data, false );
            $newcount = self::return_known_vuln_count();
            
            if ( $oldcount && $newcount ) {
                $diff = $newcount - $oldcount;
                
                if ( 0 < $diff ) {
                    $message = '';
                    $message = esc_html( sprintf( _n(
                        '%s vulnerability added.',
                        '%s new vulnerabilites added.',
                        $diff,
                        'security-ninja'
                    ), number_format_i18n( $diff ) ) );
                    $message .= ' ' . sprintf(
                        // translators: Shows how many vulnerabilities are known and when list was updated
                        esc_html__( 'Vulnerability list contains %1$s known vulnerabilities. Last updated %2$s', 'security-ninja' ),
                        esc_html( number_format_i18n( $newcount ) ),
                        esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $sn_vulns_data->timestamp ) )
                    ) . '';
                    update_option( WF_SN_VU_VULNS_NOTICE, $message, false );
                }
            
            }
        
        } else {
        }
        
        $options = self::get_options();
        $enable_email_notice = $options['enable_email_notice'];
        // *** EMAIL WARNINGS - CHECK if an email should be sent...
        $vulns = self::return_vulnerabilities();
        
        if ( $vulns && $enable_email_notice ) {
            $last_email_sent = get_option( WF_SN_VU_LAST_EMAIL, false );
            $send_email = false;
            
            if ( $last_email_sent ) {
                $difference_in_seconds = time() - $last_email_sent;
                if ( $difference_in_seconds > DAY_IN_SECONDS ) {
                    $send_email = true;
                }
            }
            
            
            if ( $send_email ) {
                // Ready to send email
                $email_notice_recipient = $options['email_notice_recipient'];
                $message_content = sprintf(
                    // translators: Shows how many vulnerabilities are known and when list was updated
                    esc_html__( 'Security Ninja has detected vulnerabilities on your website, %1$s', 'security-ninja' ),
                    wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES )
                ) . '';
                if ( isset( $vulns['plugins'] ) ) {
                    foreach ( $vulns['plugins'] as $key => $vu ) {
                        $message_content .= __( 'Plugin', 'security-ninja' ) . ' ' . $vu['name'] . PHP_EOL;
                        $message_content .= $vu['desc'] . PHP_EOL;
                        if ( $vu['CVE_ID'] ) {
                            $message_content .= 'ID: ' . $vu['CVE_ID'] . PHP_EOL;
                        }
                        $message_content .= PHP_EOL;
                    }
                }
                if ( isset( $vulns['themes'] ) ) {
                    foreach ( $vulns['themes'] as $key => $vu ) {
                        $message_content .= __( 'Theme', 'security-ninja' ) . ' ' . $vu['name'] . PHP_EOL;
                        $message_content .= $vu['desc'] . PHP_EOL;
                        if ( $vu['CVE_ID'] ) {
                            $message_content .= 'ID: ' . $vu['CVE_ID'] . PHP_EOL;
                        }
                        $message_content .= PHP_EOL;
                    }
                }
                
                if ( $message_content != '' ) {
                    $message_content .= __( 'View all vulnerabilities:', 'security-ninja' ) . ' ' . esc_url( admin_url( 'admin.php?page=wf-sn#sn_vuln' ) ) . PHP_EOL;
                    $message_content .= PHP_EOL;
                    $message_content .= __( 'Thank you for using WP Security Ninja', 'security-ninja' ) . ' - https://wpsecurityninja.com/' . PHP_EOL;
                }
                
                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                $sendresult = wp_mail(
                    $email_notice_recipient,
                    __( 'Vulnerabilities detected on', 'security-ninja' ) . ' ' . wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
                    $message_content,
                    $headers
                );
                update_option( WF_SN_VU_LAST_EMAIL, time(), false );
            }
        
        }
    
    }
    
    /**
     * Check if an array is a multidimensional array.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @global
     * @param	mixed	$x	
     * @return	boolean
     */
    public static function is_multi_array( $x )
    {
        if ( count( array_filter( $x, 'is_array' ) ) > 0 ) {
            return true;
        }
        return false;
    }
    
    /**
     * Convert an object to an array.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @global
     * @param	mixed	$object	The object to convert
     * @return	mixed
     */
    public static function object_to_array_map( $object )
    {
        if ( !is_object( $object ) && !is_array( $object ) ) {
            return $object;
        }
        return array_map( array( __CLASS__, 'object_to_array' ), (array) $object );
    }
    
    /**
     * Check if a value exists in the array/object.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @global
     * @param	mixed  	$needle  	The value that you are searching for
     * @param	mixed  	$haystack	The array/object to search
     * @param	boolean	$strict  	Whether to use strict search or not
     * @return	boolean
     */
    public static function search_for_value( $needle, $haystack, $strict = true )
    {
        $haystack = self::object_to_array( $haystack );
        if ( is_array( $haystack ) ) {
            
            if ( self::is_multi_array( $haystack ) ) {
                // Multidimensional array
                foreach ( $haystack as $subhaystack ) {
                    if ( self::search_for_value( $needle, $subhaystack, $strict ) ) {
                        return true;
                    }
                }
            } elseif ( array_keys( $haystack ) !== range( 0, count( $haystack ) - 1 ) ) {
                // Associative array
                foreach ( $haystack as $key => $val ) {
                    
                    if ( $needle === $val && !$strict ) {
                        return true;
                    } elseif ( $needle === $val && $strict ) {
                        return true;
                    }
                
                }
                return false;
            } else {
                // Normal array
                
                if ( $needle === $haystack && !$strict ) {
                    return true;
                } elseif ( $needle === $haystack && $strict ) {
                    return true;
                }
            
            }
        
        }
        return false;
    }
    
    /**
     * object_to_array.
     * Ref: https://stackoverflow.com/questions/4345554/convert-a-php-object-to-an-associative-array
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Thursday, July 22nd, 2021.
     * @param	mixed	$data	
     * @return	mixed
     */
    public static function object_to_array( $data )
    {
        
        if ( is_array( $data ) || is_object( $data ) ) {
            $result = [];
            foreach ( $data as $key => $value ) {
                $result[$key] = ( is_array( $data ) || is_object( $data ) ? self::object_to_array( $value ) : $value );
            }
            return $result;
        }
        
        return $data;
    }
    
    /**
     * Return list of known vulnerabilities from the website, checking installed plugins and WordPress version against list from API.
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.	
     * @version	v1.0.1	Friday, May 13th, 2022.
     * @access	public static
     * @return	void
     */
    public static function return_vulnerabilities()
    {
        // Note - transient is deleted when updating settings.
        
        if ( false === ($found_vulnerabilities = get_transient( 'wf_sn_return_vulnerabilities' )) ) {
            global  $wp_version ;
            wf_sn::timerstart( 'scan_for_vulns' );
            $vuln_plugin_arr = false;
            $installed_plugins = false;
            $options = self::get_options();
            
            if ( $options['enable_vulns'] ) {
                $vulns = get_option( WF_SN_VU_VULNS );
                
                if ( !$vulns ) {
                    self::update_vuln_list();
                    $vulns = get_option( WF_SN_VU_VULNS );
                }
                
                // offers problem here on free version? memory issue, maxes out 256mb
                $vuln_plugin_arr = self::object_to_array( $vulns->plugins );
                $installed_plugins = get_plugins();
            }
            
            // Tests for plugin problems
            
            if ( $installed_plugins && $vuln_plugin_arr ) {
                $found_vulnerabilities = array();
                foreach ( $installed_plugins as $key => $ap ) {
                    $lookup_id = strtok( $key, '/' );
                    $findplugin = array_search( $lookup_id, array_column( $vuln_plugin_arr, 'slug' ), true );
                    
                    if ( $findplugin ) {
                        if ( isset( $vuln_plugin_arr[$findplugin]['versionEndExcluding'] ) && '' !== $vuln_plugin_arr[$findplugin]['versionEndExcluding'] ) {
                            // check #1 - versionEndExcluding
                            
                            if ( version_compare( $ap['Version'], $vuln_plugin_arr[$findplugin]['versionEndExcluding'], '<' ) ) {
                                $description = '';
                                if ( isset( $vuln_plugin_arr[$findplugin]['description'] ) ) {
                                    $description = $vuln_plugin_arr[$findplugin]['description'];
                                }
                                $found_vulnerabilities['plugins'][$lookup_id] = array(
                                    'name'                => $ap['Name'],
                                    'desc'                => $description,
                                    'installedVersion'    => $ap['Version'],
                                    'versionEndExcluding' => $vuln_plugin_arr[$findplugin]['versionEndExcluding'],
                                    'CVE_ID'              => $vuln_plugin_arr[$findplugin]['CVE_ID'],
                                    'refs'                => $vuln_plugin_arr[$findplugin]['refs'],
                                );
                            }
                        
                        }
                        // Checks via the versionImpact method
                        if ( isset( $vuln_plugin_arr[$findplugin]['versionImpact'] ) && '' !== $vuln_plugin_arr[$findplugin]['versionImpact'] ) {
                            
                            if ( version_compare( $ap['Version'], $vuln_plugin_arr[$findplugin]['versionImpact'], '<=' ) ) {
                                $found_vulnerabilities['plugins'][$lookup_id] = array(
                                    'name'             => $ap['Name'],
                                    'desc'             => $vuln_plugin_arr[$findplugin]['description'],
                                    'installedVersion' => $ap['Version'],
                                    'versionImpact'    => $vuln_plugin_arr[$findplugin]['versionImpact'],
                                    'CVE_ID'           => $vuln_plugin_arr[$findplugin]['CVE_ID'],
                                    'refs'             => $vuln_plugin_arr[$findplugin]['refs'],
                                );
                                if ( isset( $vuln_plugin_arr[$findplugin]['recommendation'] ) ) {
                                    $found_vulnerabilities['plugins'][$lookup_id]['recommendation'] = $vuln_plugin_arr[$findplugin]['recommendation'];
                                }
                            }
                        
                        }
                    }
                
                }
            }
            
            // ------------ Find WordPress vulnerabilities ------------
            $wordpressarr = false;
            if ( isset( $vulns->wordpress ) ) {
                $wordpressarr = self::object_to_array( $vulns->wordpress );
            }
            $lookup_id = 0;
            if ( $wordpressarr ) {
                foreach ( $wordpressarr as $key => $wpvuln ) {
                    
                    if ( version_compare( $wp_version, $wpvuln['versionEndExcluding'], '<' ) ) {
                        $desc = '';
                        if ( isset( $wpvuln['description'] ) ) {
                            $desc = $wpvuln['description'];
                        }
                        $found_vulnerabilities['wordpress'][$lookup_id] = array(
                            'desc'                => $desc,
                            'versionEndExcluding' => $wpvuln['versionEndExcluding'],
                            'CVE_ID'              => $wpvuln['CVE_ID'],
                        );
                        if ( isset( $wpvuln['recommendation'] ) ) {
                            $found_vulnerabilities['wordpress'][$lookup_id]['recommendation'] = $wpvuln['recommendation'];
                        }
                        $lookup_id++;
                    }
                
                }
            }
            // Find vulnerable themes
            // Build new empty Array to store the themes
            $themes = array();
            // Loads theme data
            $all_themes = wp_get_themes();
            // Build theme data manually
            foreach ( $all_themes as $theme ) {
                $themes[$theme->stylesheet] = array(
                    'Name'      => $theme->get( 'Name' ),
                    'Author'    => $theme->get( 'Author' ),
                    'AuthorURI' => $theme->get( 'AuthorURI' ),
                    'Version'   => $theme->get( 'Version' ),
                    'Template'  => $theme->get( 'Template' ),
                    'Status'    => $theme->get( 'Status' ),
                );
            }
            $vuln_theme_arr = false;
            if ( isset( $vulns->themes ) ) {
                $vuln_theme_arr = self::object_to_array( $vulns->themes );
            }
            
            if ( $themes && $vuln_theme_arr ) {
                foreach ( $themes as $key => $ap ) {
                    $findtheme = array_search( $key, array_column( $vuln_theme_arr, 'slug' ), true );
                    
                    if ( $findtheme !== false ) {
                        // $Matched theme array with details
                        $matched = $vuln_theme_arr[$findtheme];
                        if ( isset( $matched['versionEndExcluding'] ) && '' !== $vuln_theme_arr[$findtheme]['versionEndExcluding'] ) {
                            // check #1 - versionEndExcluding
                            
                            if ( version_compare( $ap['Version'], $matched['versionEndExcluding'], '<' ) ) {
                                $desc = '';
                                if ( isset( $matched['description'] ) ) {
                                    $desc = $matched['description'];
                                }
                                $found_vulnerabilities['themes'][$key] = array(
                                    'name'                => $ap['Name'],
                                    'desc'                => $desc,
                                    'installedVersion'    => $ap['Version'],
                                    'versionEndExcluding' => $matched['versionEndExcluding'],
                                    'CVE_ID'              => $matched['CVE_ID'],
                                    'refs'                => $matched['refs'],
                                );
                            }
                        
                        }
                    }
                
                }
                // 2 - Lookup child themes (look by Template value) @todo!
            }
            
            $scan_time = wf_sn::timerstop( 'scan_for_vulns' );
            if ( $scan_time ) {
                $found_vulnerabilities['scan_time'] = $scan_time;
            }
            if ( $found_vulnerabilities ) {
                set_transient( 'wf_sn_return_vulnerabilities', $found_vulnerabilities, 24 * 60 * 60 );
            }
        }
        
        
        if ( $found_vulnerabilities ) {
            return $found_vulnerabilities;
        } else {
            return false;
        }
    
    }
    
    /**
     * Gets list of WordPress from official API and their security status
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.	
     * @version	v1.0.1	Wednesday, January 13th, 2021.
     * @access	public static
     * @return	mixed
     */
    public static function get_wp_ver_status()
    {
        // returns false if module disabled
        $options = self::get_options();
        if ( !$options['enable_vulns'] ) {
            return false;
        }
        $wp_vers_status = get_transient( 'wp_vers_status' );
        
        if ( false === $wp_vers_status ) {
            $request_url = 'https://api.wordpress.org/core/stable-check/1.0/';
            $response = wp_remote_get( $request_url );
            
            if ( !is_wp_error( $response ) ) {
                $body = wp_remote_retrieve_body( $response );
                $wp_vers_status = json_decode( $body );
            }
            
            set_transient( 'wp_vers_status', $wp_vers_status, 12 * HOUR_IN_SECONDS );
        }
        
        return $wp_vers_status;
    }
    
    /**
     * Returns the number of known vulnerabilities
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Tuesday, July 6th, 2021.
     * @access	public static
     * @global
     * @return	int
     */
    public static function return_known_vuln_count()
    {
        $vulns = get_option( WF_SN_VU_VULNS );
        if ( !$vulns ) {
            return false;
        }
        $plugin_vulns_count = count( $vulns->plugins );
        $theme_vulns_count = count( $vulns->themes );
        $wp_vulns_count = count( $vulns->wordpress );
        $total_vulnerabilities = $plugin_vulns_count + $wp_vulns_count + $theme_vulns_count;
        return $total_vulnerabilities;
    }
    
    /**
     * Returns number of known vulnerabilities across all types
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	mixed
     */
    public static function return_vuln_count()
    {
        $vulnerabilities = self::return_vulnerabilities();
        if ( !$vulnerabilities ) {
            return false;
        }
        $total_vulnerabilities = 0;
        if ( isset( $vulnerabilities['plugins'] ) ) {
            $total_vulnerabilities = $total_vulnerabilities + count( $vulnerabilities['plugins'] );
        }
        if ( isset( $vulnerabilities['themes'] ) ) {
            $total_vulnerabilities = $total_vulnerabilities + count( $vulnerabilities['themes'] );
        }
        if ( isset( $vulnerabilities['wordpress'] ) ) {
            $total_vulnerabilities = $total_vulnerabilities + count( $vulnerabilities['wordpress'] );
        }
        return $total_vulnerabilities;
    }
    
    /**
     * Renders vulnerability tab
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.	
     * @version	v1.0.1	Tuesday, January 10th, 2023.
     * @access	public static
     * @return	void
     */
    public static function render_vuln_page()
    {
        // @todo - change to load via AJAX
        global  $wp_version ;
        $options = self::get_options();
        
        if ( $options['enable_vulns'] ) {
            // Get the list of vulnerabilities
            $vulnerabilities = self::return_vulnerabilities();
            $vulns = get_option( WF_SN_VU_VULNS );
            
            if ( !$vulns ) {
                self::update_vuln_list();
                $vulns = get_option( WF_SN_VU_VULNS );
            }
            
            $plugin_vulns_count = count( $vulns->plugins );
            $theme_vulns_count = count( $vulns->themes );
            $wp_vulns_count = count( $vulns->wordpress );
            $total_vulnerabilities = $plugin_vulns_count + $wp_vulns_count + $theme_vulns_count;
            // Used for the output of WordPress version being used
            $wp_status = '';
        }
        
        ?>
		<div class="submit-test-container">

			<div class="card">
				<h3><?php 
        esc_html_e( 'Vulnerability Scanner', 'security-ninja' );
        ?></h3>
				<p><?php 
        echo  esc_html__( 'Warns you of any known vulnerabilities in the plugins and themes you have installed.', 'security-ninja' ) . ' <a href="' . esc_url( wf_sn::generate_sn_web_link( 'help_improve', '/docs/vulnerabilities/scanner/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Click here to learn more', 'security-ninja' ) . '</a>' ;
        ?></p>
				<?php 
        
        if ( isset( $vulnerabilities['wordpress'] ) or isset( $vulnerabilities['plugins'] ) or isset( $vulnerabilities['themes'] ) ) {
            ?>
					<h2><?php 
            esc_html_e( 'Vulnerabilities found on your system!', 'security-ninja' );
            ?></h2>

					<?php 
            
            if ( isset( $vulnerabilities['wordpress'] ) ) {
                $get_wp_ver_status = self::get_wp_ver_status();
                
                if ( isset( $get_wp_ver_status->{$wp_version} ) ) {
                    
                    if ( 'insecure' === $get_wp_ver_status->{$wp_version} ) {
                        $wp_status = 'This version of WordPress (' . $wp_version . ') is considered <strong>INSECURE</strong>. You should upgrade as soon possible.';
                        // @i8n @todo
                    }
                    
                    
                    if ( 'outdated' === $get_wp_ver_status->{$wp_version} ) {
                        $wp_status = 'This version of WordPress (' . $wp_version . ') is considered <strong>OUTDATED</strong>. You should upgrade as soon possible.';
                        // @i8n
                    }
                
                }
                
                // @i8n BELOW
                ?>

						<div class="vuln vulnwordpress">
							<p>You are running WordPress version <?php 
                echo  esc_html( $wp_version ) ;
                ?> and there are known vulnerabilities that
								have been fixed in later versions. You should upgrade WordPress as soon as possible.</p>

							<?php 
                
                if ( '' !== $wp_status ) {
                    ?>
								<div class="vulnrecommendation">
									<h2>
										<?php 
                    echo  $wp_status ;
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
									</h2>
								</div>
							<?php 
                }
                
                ?>

							<p><?php 
                _e( 'Known vulnerabilities', 'security-ninja' );
                ?></p>

							<?php 
                foreach ( $vulnerabilities['wordpress'] as $key => $wpvuln ) {
                    
                    if ( isset( $wpvuln['versionEndExcluding'] ) ) {
                        ?>
									<h3><span class="dashicons dashicons-warning"></span> <?php 
                        echo  esc_html( 'WordPress ' . $wpvuln['CVE_ID'] ) ;
                        ?></h3>
									<div class="wrap-collabsible">
										<input id="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="toggle" type="checkbox">
										<label for="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="lbl-toggle">Details</label>
										<div class="collapsible-content">
											<div class="content-inner">
												<?php 
                        
                        if ( isset( $wpvuln['desc'] ) && '' !== $wpvuln['desc'] ) {
                            ?>
													<p class="vulndesc"><?php 
                            echo  esc_html( $wpvuln['desc'] ) ;
                            ?></p>
												<?php 
                        }
                        
                        ?>
												<p class="vulnDetails">
													<?php 
                        printf(
                            /* translators: 1: WordPress version */
                            __( 'Fixed in WordPress version %1$s', 'security-ninja' ),
                            esc_html( $wpvuln['versionEndExcluding'] )
                        );
                        ?>
												</p>
												<?php 
                        
                        if ( isset( $wpvuln['CVE_ID'] ) && '' !== $wpvuln['CVE_ID'] ) {
                            // @i8n below
                            ?>
													<p><span class="nvdlink">More details: <a href="<?php 
                            echo  esc_url( 'https://nvd.nist.gov/vuln/detail/' . $wpvuln['CVE_ID'] ) ;
                            ?>" target="_blank" rel="noopener">Read more about <?php 
                            echo  esc_html( $wpvuln['CVE_ID'] ) ;
                            ?></a></span></p>
												<?php 
                        }
                        
                        ?>
											</div>
										</div>
									</div>


							<?php 
                    }
                
                }
                ?>
						</div><!-- .vuln vulnwordpress -->
					<?php 
            }
            
            // display list of vulns in plugins
            
            if ( isset( $vulnerabilities['plugins'] ) ) {
                // @i8n below
                ?>
						<p><?php 
                _e( 'You should upgrade to latest version or find a different plugin as soon as possible.', 'security-ninja' );
                ?></p>
						<?php 
                foreach ( $vulnerabilities['plugins'] as $key => $found_vuln ) {
                    ?>
							<div class="card vulnplugin">
								<h3><span class="dashicons dashicons-warning"></span> Plugin: <?php 
                    echo  esc_html( $found_vuln['name'] ) ;
                    ?> <span class="ver">v. <?php 
                    echo  esc_html( $found_vuln['installedVersion'] ) ;
                    ?></span></h3>
								<?php 
                    
                    if ( isset( $found_vuln['versionEndExcluding'] ) ) {
                        $searchurl = admin_url( 'plugins.php?s=' . rawurlencode( $found_vuln['name'] ) . '&plugin_status=all' );
                        ?>
									<div class="vulnrecommendation">
										<p><?php 
                        $searchurl = filter_var( $searchurl, FILTER_SANITIZE_URL );
                        printf(
                            // translators: Recommendation to update particular plugin to specific version
                            __( '<a href="%1$s">Update %2$s to minimum version %3$s</a>', 'security-ninja' ),
                            $searchurl,
                            esc_html( $found_vuln['name'] ),
                            esc_html( $found_vuln['versionEndExcluding'] )
                        );
                        ?></p>
									</div>
								<?php 
                    } elseif ( isset( $found_vuln['recommendation'] ) && '' !== $found_vuln['recommendation'] ) {
                        ?>
									<div class="vulnrecommendation">
										<p><strong><?php 
                        echo  esc_html( $found_vuln['recommendation'] ) ;
                        ?></strong></p>
									</div>
								<?php 
                    }
                    
                    
                    if ( isset( $found_vuln['desc'] ) || isset( $found_vuln['refs'] ) ) {
                        ?>
									<div class="wrap-collabsible">
										<input id="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="toggle" type="checkbox">
										<label for="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="lbl-toggle">Details</label>
										<div class="collapsible-content">
											<div class="content-inner">
												<?php 
                        
                        if ( isset( $found_vuln['desc'] ) && '' !== $found_vuln['desc'] ) {
                            ?>
													<p class="vulndesc"><?php 
                            echo  esc_html( $found_vuln['desc'] ) ;
                            ?></p>
													<?php 
                        }
                        
                        
                        if ( isset( $found_vuln['refs'] ) && '' !== $found_vuln['refs'] ) {
                            $refs = json_decode( $found_vuln['refs'] );
                            
                            if ( is_array( $refs ) ) {
                                ?>
														<h4><?php 
                                _e( 'Read more:', 'security-ninja' );
                                ?></h4>
														<ul>
															<?php 
                                
                                if ( isset( $found_vuln['CVE_ID'] ) && '' !== $found_vuln['CVE_ID'] ) {
                                    ?>
																<li><a href="<?php 
                                    echo  esc_url( 'https://nvd.nist.gov/vuln/detail/' . $found_vuln['CVE_ID'] ) ;
                                    ?>" target="_blank" class="exlink" rel="noopener"><?php 
                                    echo  esc_attr( $found_vuln['CVE_ID'] ) ;
                                    ?></a></li>
															<?php 
                                }
                                
                                foreach ( $refs as $ref ) {
                                    ?>
																<li><a href="<?php 
                                    echo  esc_url( $ref->url ) ;
                                    ?>" target="_blank" class="exlink" rel="noopener"><?php 
                                    echo  esc_html( self::remove_http( $ref->name ) ) ;
                                    ?></a></li>
															<?php 
                                }
                                ?>
														</ul>
												<?php 
                            }
                        
                        }
                        
                        ?>
											</div>
										</div>
									</div>
								<?php 
                    }
                    
                    ?>
							</div><!-- .vuln .vulnplugin -->
						<?php 
                }
            }
            
            // end plugins
            // display list of vulns in themes
            
            if ( isset( $vulnerabilities['themes'] ) ) {
                // @i8n below
                ?>

						<p><?php 
                _e( 'Warning - Vulnerable themes found! Note: comparison is made by folder name. Please verify the theme before deleting.', 'security-ninja' );
                ?></p>

						<?php 
                foreach ( $vulnerabilities['themes'] as $key => $found_vuln ) {
                    ?>
							<div class="card vulnplugin">
								<h3><span class="dashicons dashicons-warning"></span> Theme: <?php 
                    echo  esc_html( $found_vuln['name'] ) ;
                    ?> <span class="ver">v. <?php 
                    echo  esc_html( $found_vuln['installedVersion'] ) ;
                    ?></span></h3>

								<?php 
                    
                    if ( isset( $found_vuln['versionEndExcluding'] ) ) {
                        $searchurl = admin_url( 'plugins.php?s=' . rawurlencode( $found_vuln['name'] ) . '&plugin_status=all' );
                        ?>
									<div class="vulnrecommendation">
										<p><?php 
                        $searchurl = filter_var( $searchurl, FILTER_SANITIZE_URL );
                        printf(
                            // translators: Recommendation to update particular plugin to specific version
                            __( '<a href="%1$s">Update %2$s to minimum version %3$s</a>', 'security-ninja' ),
                            $searchurl,
                            esc_html( $found_vuln['name'] ),
                            esc_html( $found_vuln['versionEndExcluding'] )
                        );
                        ?></p>
									</div>
								<?php 
                    } elseif ( isset( $found_vuln['recommendation'] ) && '' !== $found_vuln['recommendation'] ) {
                        ?>
									<div class="vulnrecommendation">
										<p><strong><?php 
                        echo  esc_html( $found_vuln['recommendation'] ) ;
                        ?></strong></p>
									</div>
								<?php 
                    }
                    
                    
                    if ( isset( $found_vuln['desc'] ) || isset( $found_vuln['refs'] ) ) {
                        ?>
									<div class="wrap-collabsible">
										<input id="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="toggle" type="checkbox">
										<label for="collapsible-<?php 
                        echo  esc_attr( $key ) ;
                        ?>" class="lbl-toggle"><?php 
                        _e( 'Details', 'security-ninja' );
                        ?></label>
										<div class="collapsible-content">
											<div class="content-inner">
												<?php 
                        
                        if ( isset( $found_vuln['desc'] ) && '' !== $found_vuln['desc'] ) {
                            ?>
													<p class="vulndesc"><?php 
                            echo  esc_html( $found_vuln['desc'] ) ;
                            ?></p>
												<?php 
                        }
                        
                        ?>
												<?php 
                        
                        if ( isset( $found_vuln['refs'] ) && '' !== $found_vuln['refs'] ) {
                            $refs = json_decode( $found_vuln['refs'] );
                            
                            if ( is_array( $refs ) ) {
                                ?>
														<h4><?php 
                                _e( 'Read more', 'security-ninja' );
                                ?>:</h4>
														<ul>
															<?php 
                                
                                if ( isset( $found_vuln['CVE_ID'] ) && '' !== $found_vuln['CVE_ID'] ) {
                                    ?>
																<li><a href="<?php 
                                    echo  esc_url( 'https://nvd.nist.gov/vuln/detail/' . $found_vuln['CVE_ID'] ) ;
                                    ?>" target="_blank" class="exlink" rel="noopener"><?php 
                                    echo  esc_attr( $found_vuln['CVE_ID'] ) ;
                                    ?></a></li>
															<?php 
                                }
                                
                                foreach ( $refs as $ref ) {
                                    ?>
																<li><a href="<?php 
                                    echo  esc_url( $ref->url ) ;
                                    ?>" target="_blank" class="exlink" rel="noopener"><?php 
                                    echo  esc_html( self::remove_http( $ref->name ) ) ;
                                    ?></a></li>
															<?php 
                                }
                                ?>
														</ul>
												<?php 
                            }
                        
                        }
                        
                        ?>
											</div>
										</div>
									</div>
								<?php 
                    }
                    
                    ?>
							</div><!-- .vuln .vulnplugin -->
						<?php 
                }
            }
            
            // end themes
        } else {
            $options = self::get_options();
            // No update if feature disabled
            
            if ( !$options['enable_vulns'] ) {
                ?>
						<h3><?php 
                _e( 'The vulnerability scanner is disabled', 'security-ninja' );
                ?></h3>
					<?php 
            } else {
                ?>
						<h3><?php 
                esc_html_e( 'Great, no known vulnerabilities found on your website', 'security-ninja' );
                ?></h3>
				<?php 
            }
        
        }
        
        ?>
			</div>
			<div class="card">
				<form method="post" action="options.php">
					<?php 
        settings_fields( WF_SN_VU_OPTIONS_NAME );
        ?>
					<h3 class="ss_header"><?php 
        _e( 'Settings', 'security-ninja' );
        ?></h3>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="wf_sn_vu_settings_group_enable_vulns"><?php 
        _e( 'Vulnerability scanning', 'security-ninja' );
        ?></label></th>
								<td class="sn-cf-options">
									<?php 
        Wf_Sn::create_toggle_switch( WF_SN_VU_OPTIONS_NAME . '_enable_vulns', array(
            'value'       => 1,
            'saved_value' => $options['enable_vulns'],
            'option_key'  => WF_SN_VU_OPTIONS_NAME . '[enable_vulns]',
        ) );
        ?>
									<p class="description"><?php 
        _e( 'Checking for known vulnerabilites.', 'security-ninja' );
        ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="wf_sn_vu_settings_group_enable_admin_notification"><?php 
        _e( 'Admin counter', 'security-ninja' );
        ?></label></th>
								<td class="sn-cf-options">
									<?php 
        Wf_Sn::create_toggle_switch( WF_SN_VU_OPTIONS_NAME . '_enable_admin_notification', array(
            'saved_value' => $options['enable_admin_notification'],
            'option_key'  => WF_SN_VU_OPTIONS_NAME . '[enable_admin_notification]',
        ) );
        ?>
									<p class="description"><?php 
        _e( 'Disable warning notice in admin pages.', 'security-ninja' );
        ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="wf_sn_vu_settings_group_enable_email_notice"><?php 
        _e( 'Email warnings', 'security-ninja' );
        ?></label></th>
								<td class="sn-cf-options">
									<?php 
        Wf_Sn::create_toggle_switch( WF_SN_VU_OPTIONS_NAME . '_enable_email_notice', array(
            'saved_value' => $options['enable_email_notice'],
            'option_key'  => WF_SN_VU_OPTIONS_NAME . '[enable_email_notice]',
        ) );
        ?>
									<p class="description"><?php 
        _e( 'Enable email notifications. Only when one or more vulnerabilites are detected.', 'security-ninja' );
        ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><label for="input_id"><?php 
        _e( 'Email recipient', 'security-ninja' );
        ?></label></th>
								<td>
									<input name="<?php 
        echo  esc_attr( WF_SN_VU_OPTIONS_NAME ) ;
        ?>[email_notice_recipient]" type="text" value="<?php 
        echo  esc_attr( $options['email_notice_recipient'] ) ;
        ?>" class="regular-text" placeholder="">
									<p class="description"><?php 
        _e( 'Who should get the warning? The system will send an email when a vulnerability is
										detected. Maximum one email per day.', 'security-ninja' );
        ?></p>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<p class="submit"><input type="submit" value="<?php 
        _e( 'Save Changes', 'security-ninja' );
        ?>" class="input-button button-primary" name="Submit" />

								</td>
							</tr>
						</tbody>
					</table>

				</form>
			</div><!-- .card -->
			<?php 
        
        if ( $options['enable_vulns'] ) {
            ?>
				<p>
					<?php 
            printf(
                // translators: Shows how many vulnerabilities are known and when list was updated
                esc_html__( 'Vulnerability list contains %1$s known vulnerabilities. Last updated %2$s (%3$s)', 'security-ninja' ),
                esc_html( number_format_i18n( $total_vulnerabilities ) ),
                esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $vulns->timestamp ) ),
                esc_html( human_time_diff( $vulns->timestamp, time() ) . ' ' . __( 'ago', 'security-ninja' ) )
            );
            ?>
				</p>

			<?php 
        }
        
        ?>

		</div>
		<?php 
    }
    
    /**
     * Display warning if test were never run
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	void
     */
    public static function admin_notice_vulnerabilities()
    {
        global  $current_screen ;
        // dont show on the wizard page
        if ( strpos( $current_screen->id, 'security-ninja-wizard' ) !== false ) {
            return false;
        }
        if ( !\PAnD::is_admin_notice_active( 'dismiss-vulnerabilities-notice-1' ) || wf_sn::is_plugin_page() ) {
            return;
        }
        $tests = get_option( WF_SN_VU_RESULTS_KEY );
        $found_plugin_vulnerabilities = self::return_vulnerabilities();
        
        if ( $found_plugin_vulnerabilities ) {
            $total = 0;
            if ( isset( $found_plugin_vulnerabilities['plugins'] ) ) {
                $total = $total + count( $found_plugin_vulnerabilities['plugins'] );
            }
            if ( isset( $found_plugin_vulnerabilities['wordpress'] ) ) {
                $total = $total + count( $found_plugin_vulnerabilities['wordpress'] );
            }
            if ( isset( $found_plugin_vulnerabilities['themes'] ) ) {
                $total = $total + count( $found_plugin_vulnerabilities['themes'] );
            }
            if ( 0 === $total ) {
                return;
            }
            ?>
			<div data-dismissible="dismiss-vulnerabilities-notice-1" class="secnin-notice notice notice-error is-dismissible" id="sn_vulnerability_warning_dismiss">

				<h3><span class="dashicons dashicons-warning"></span>
					<?php 
            // translators: Shown if one or multiple vulnerabilities found
            echo  esc_html( sprintf( _n(
                'You have %s known vulnerability on your website!',
                'You have %s known vulnerabilities on your website!',
                $total,
                'security-ninja'
            ), number_format_i18n( $total ) ) ) ;
            ?>
				</h3>
				<p>
					<?php 
            printf( 'Visit the <a href="%s">Vulnerabilities tab</a> for more details.', esc_url( admin_url( 'admin.php?page=wf-sn#sn_vuln' ) ) );
            ?>
					- <a href="#" class="dismiss-this"><?php 
            esc_html_e( 'Dismiss warning for 24 hours.', 'security-ninja' );
            ?></a></p>
			</div>
<?php 
        }
    
    }
    
    /**
     * Plugin activation routines
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.
     * @access	public static
     * @return	void
     */
    public static function activate()
    {
        // Download the vulnerability list for the first time
        self::update_vuln_list();
    }
    
    /**
     * Sanitize settings on save
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Sunday, January 3rd, 2021.
     * @access	public static
     * @param	mixed	$values	values to sanitize
     * @return	mixed
     */
    public static function sanitize_settings( $values )
    {
        if ( !is_array( $values ) ) {
            $values = array();
        }
        $old_options['enable_vulns'] = 0;
        $old_options['enable_outdated'] = 0;
        $old_options['enable_admin_notification'] = 0;
        $old_options['enable_email_notice'] = 0;
        $old_options['email_notice_recipient'] = '';
        foreach ( $values as $key => $value ) {
            switch ( $key ) {
                case 'enable_vulns':
                case 'enable_outdated':
                case 'enable_admin_notification':
                case 'enable_email_notice':
                    $values[$key] = intval( $value );
                    break;
                case 'email_notice_recipient':
                    $values[$key] = sanitize_text_field( $value );
                    break;
            }
        }
        $return = array_merge( $old_options, $values );
        // Delete the transient when saving
        delete_transient( 'wf_sn_return_vulnerabilities' );
        return $return;
    }
    
    /**
     * Routines that run on deactivation
     *
     * @author	Lars Koudal
     * @since	v0.0.1
     * @version	v1.0.0	Friday, January 1st, 2021.	
     * @version	v1.0.1	Saturday, November 19th, 2022.
     * @access	public static
     * @return	void
     */
    public static function deactivate()
    {
        $centraloptions = Wf_Sn::get_options();
        if ( !isset( $centraloptions['remove_settings_deactivate'] ) ) {
            return;
        }
        
        if ( $centraloptions['remove_settings_deactivate'] ) {
            delete_option( WF_SN_VU_RESULTS_KEY );
            delete_option( WF_SN_VU_VULNS );
            delete_option( WF_SN_VU_OUTDATED );
            delete_option( WF_SN_VU_OPTIONS_KEY );
            delete_option( WF_SN_VU_VULNS_NOTICE );
            delete_option( WF_SN_VU_LAST_EMAIL );
        }
    
    }

}
// setup environment when activated
register_activation_hook( WF_SN_BASE_FILE, array( __NAMESPACE__ . '\\Wf_Sn_Vu', 'activate' ) );
// hook everything up
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\\Wf_Sn_Vu', 'init' ) );
// when deativated clean up
register_deactivation_hook( WF_SN_BASE_FILE, array( __NAMESPACE__ . '\\Wf_Sn_Vu', 'deactivate' ) );