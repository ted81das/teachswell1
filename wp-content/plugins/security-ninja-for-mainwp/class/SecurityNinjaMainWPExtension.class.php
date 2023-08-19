<?php

class SecurityNinjaMainWPExtension
{

	public function __construct()
	{
		add_action('init', array(&$this, 'init'));
		add_action('admin_init', array(&$this, 'admin_init'));
	}



	/*
	/**
	* Create your extension page
	*
	* @author	Lars Koudal
	* @since	v0.0.1
	* @version	v1.0.0	Thursday, March 24th, 2022.
	* @access	public
	* @return	void
	*/
	public static function render_page()
	{
		global $security_ninja_mainwp_extension_activator;
		$websites = apply_filters('mainwp_getsites', $security_ninja_mainwp_extension_activator->get_child_file(), $security_ninja_mainwp_extension_activator->get_child_key(), null);

?>
		<div class="secnin-content-wrapper" style="padding:20px;">
			<table class="ui table" cellspacing="0" id="security-ninja">
				<thead>
					<tr role="row">

						<th id="cb" class="manage-cb-column check-column collapsing no-sort sorting_disabled" rowspan="1" colspan="1" data-column-index="0" style="width: 25.9943px;" aria-label="">
							<div class="ui checkbox"><input id="cb-select-all-top" type="checkbox" tabindex="0" class="hidden"><label></label></div>
						</th>

						<th id="url" class="manage-url-column sorting_asc" tabindex="0" aria-controls="mainwp-manage-sites-table" rowspan="1" colspan="1" style="width: 166.499px;" aria-label="URL: activate to sort column descending" aria-sort="ascending">URL</th>


						<th id="login" class="manage-login-column no-sort" rowspan="1" colspan="1" data-column-index="3" style="width: 20.0142px;" aria-label=""><i class="sign in alternate icon"></i></th>

						<th class="manage-vulns-column sorting no-sort" rowspan="1" colspan="1">Vulnerabilities</th>

						<th class="manage-score-column sorting no-sort">Security Score</th>

						<th class="manage-score-column sorting no-sort">Secret Access Link</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!is_array($websites)) {
					?>
						<tr>
							<td colspan="2">No info</td>
						</tr>
						<?php
					}

					if (is_array($websites)) {
						foreach ($websites as $we) {

						?>
							<tr class="secninrow" data-website="<?php echo esc_attr($we['id']); ?>" data-snload="0">

								<td class="check-column">
									<div class="ui checkbox" data-tooltip="Select this site." data-position="right center" data-inverted="">
										<input type="checkbox" id="cb-select-<?php echo esc_attr($we['id']); ?>" value="<?php echo esc_attr($we['id']); ?>" name="" tabindex="0" class="hidden"><label></label>
									</div>
								</td>


								<td><a href="<?php echo esc_url($we['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($we['name']); ?></a></td>
								<td>
									<a href="admin.php?page=SiteOpen&amp;newWindow=yes&amp;websiteid=<?php echo esc_attr($we['id']); ?>&location=<?php echo base64_encode( 'admin.php?page=wf-sn'); ?>&amp;_opennonce=<?php echo wp_create_nonce( 'mainwp-admin-nonce' ); ?>" data-tooltip="Jump to the site Security Ninja Dashboard" data-position="right center" data-inverted="" class="open_newwindow_wpadmin" target="_blank"><i class="sign in icon"></i></a>
								</td>

								<td class="secnin-vulns"><span class="spinner is-active"></span></td>

								<td class="secnin-scores">
									<span class="secnin-score"><span class="spinner is-active"></span></span>
									<span class="secnin-good secnin-score-details">-</span>
									<span class="secnin-warning secnin-score-details">-</span>
									<span class="secnin-bad secnin-score-details">-</span>
								</td>
								<td class="secnin-secret-access"></td>
							</tr>
					<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>
<?php
	}



	/**
	 * get_childkey.
	 *
	 * @author   Lars Koudal
	 * @since    v0.0.1
	 * @version  v1.0.0  Thursday, March 24th, 2022.
	 * @access   public
	 * @return   mixed
	 */
	public static function get_childkey()
	{
		// retrieves the childkey as a function that we can call, inside of the class
		global $child_enabled;
		$child_enabled = apply_filters('mainwp_extension_enabled_check', __FILE__);
		if (!$child_enabled) {
			return;
		}
		$child_key = $child_enabled['key'];
		return $child_key;
	}
}
