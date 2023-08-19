	<tr valign="top">
		<th colspan=2>
			<h3><?php _e( 'Braintree Settings', 'rcp' ); ?></h3>
		</th>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_live_merchantId]"><?php _e( 'Live Merchant ID', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[braintree_live_merchantId]" style="width: 300px;"
				   name="rcp_settings[braintree_live_merchantId]"
				   value="<?php if ( isset( $rcp_options['braintree_live_merchantId'] ) ) {
					   echo esc_attr( $rcp_options['braintree_live_merchantId'] );
				   } ?>"/>

			<p class="description"><?php _e( 'Enter your Braintree live merchant ID.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_live_publicKey]"><?php _e( 'Live Public Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="<?php echo  isset( $rcp_options['braintree_live_publicKey'] ) ? 'password' : 'text';  ?>"
				   class="regular-text" id="rcp_settings[braintree_live_publicKey]"
				   style="width: 300px;" name="rcp_settings[braintree_live_publicKey]"
				   value="<?php if ( isset( $rcp_options['braintree_live_publicKey'] ) ) {
						echo esc_attr( $rcp_options['braintree_live_publicKey'] );
				   } ?>"/>

			<button type="button" class="button button-secondary">
				<span toggle="rcp_settings[braintree_live_publicKey]"
					   class="dashicons dashicons-hidden toggle-credentials"></span>
			</button>

			<p class="description"><?php _e( 'Enter your Braintree live public key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_live_privateKey]"><?php _e( 'Live Private Key', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="<?php echo  isset( $rcp_options['braintree_live_privateKey'] ) ? 'password' : 'text';  ?>"
				   class="regular-text" id="rcp_settings[braintree_live_privateKey]"
				   style="width: 300px;" name="rcp_settings[braintree_live_privateKey]"
					 value="<?php if ( isset( $rcp_options['braintree_live_privateKey'] ) ) {
						   echo esc_attr( $rcp_options['braintree_live_privateKey'] );
					 } ?>"/>

			<button type="button" class="button button-secondary">
				<span toggle="rcp_settings[braintree_live_privateKey]"
					   class="dashicons dashicons-hidden toggle-credentials"></span>
			</button>
			<p class="description"><?php _e( 'Enter your Braintree live private key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_live_encryptionKey]"><?php _e( 'Live Client Side Encryption Key', 'rcp' ); ?></label>
		</th>
		<td>
			<?php  if ( ! empty( $rcp_options['braintree_live_encryptionKey'] ) ) : ?>
				<textarea
						class="regular-text"
						id="rcp_settings[braintree_live_encryptionKey]"
						style="width: 300px;height: 100px; display: none"
						name="rcp_settings[braintree_live_encryptionKey]"
				/><?php if ( isset( $rcp_options['braintree_live_encryptionKey'] ) ) { echo esc_attr( trim($rcp_options['braintree_live_encryptionKey'] ) ); } ?></textarea>

				<input
						type="password"
						id="rcp_settings[braintree_live_encryptionKey_input]"
						style="width: 300px;height: 100px; display: inline-block;"
						name="rcp_settings[braintree_live_encryptionKey_input]"
						value="<?php echo isset( $rcp_options['braintree_live_encryptionKey'] ) ? esc_attr( $rcp_options['braintree_live_encryptionKey'] ) : ''	?>"
				/>

				<button type="button" class="button button-secondary">
					<span
							toggle="rcp_settings[braintree_live_encryptionKey]"
							class="dashicons dashicons-visibility toggle-textarea"
							id="rcp_setting_braintree_toggle_live"></span>
				</button>
			<?php else : ?>
				<textarea
						class="regular-text"
						id="rcp_settings[braintree_live_encryptionKey]" style="width: 300px;height: 100px;"
						name="rcp_settings[braintree_live_encryptionKey]"
				/><?php echo isset( $rcp_options['braintree_live_encryptionKey'] )  ? esc_attr( trim($rcp_options['braintree_live_encryptionKey'] ) ) : '';  ?></textarea>

				<input
						type="password"
						id="rcp_settings[braintree_live_encryptionKey_input]"
						style="display:none; width: 300px;height: 100px;"
						name="rcp_settings[braintree_live_encryptionKey_input]"
						value="<?php echo isset( $rcp_options['braintree_live_encryptionKey'] ) ? esc_attr( $rcp_options['braintree_live_encryptionKey'] ) : ''	?>"
				/>
				<button type="button" class="button button-secondary">
					<span toggle="rcp_settings[braintree_live_encryptionKey]"
						  class="dashicons dashicons-hidden toggle-textarea"
						  id="rcp_setting_braintree_toggle_live"></span>
				</button>
			<?php endif; ?>
			<p class="description"><?php _e( 'Enter your Braintree live client side encryption key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_sandbox_merchantId]"><?php _e( 'Sandbox Merchant ID', 'rcp' ); ?></label>
		</th>
		<td>
			<input type="text" class="regular-text" id="rcp_settings[braintree_sandbox_merchantId]"
				   style="width: 300px;" name="rcp_settings[braintree_sandbox_merchantId]"
				   value="<?php if ( isset( $rcp_options['braintree_sandbox_merchantId'] ) ) {
					   echo esc_attr( $rcp_options['braintree_sandbox_merchantId'] );
				   } ?>"/>
			<p class="description"><?php _e( 'Enter your Braintree sandbox merchant ID.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_sandbox_publicKey]"><?php _e( 'Sandbox Public Key', 'rcp' ); ?></label>
		</th>
		<td>
			<?php if ( ! empty( $rcp_options['braintree_sandbox_publicKey'] ) ) : ?>
				<input type="password" class="regular-text" id="rcp_settings[braintree_sandbox_publicKey]"
					   style="width: 300px;" name="rcp_settings[braintree_sandbox_publicKey]"
					   value="<?php if ( isset( $rcp_options['braintree_sandbox_publicKey'] ) ) {
						   echo esc_attr( $rcp_options['braintree_sandbox_publicKey'] );
					   } ?>"/>

				<button type="button" class="button button-secondary">
					<span toggle="rcp_settings[braintree_sandbox_publicKey]"
						  class="dashicons dashicons-visibility toggle-credentials"></span>
				</button>
			<?php else : ?>
				<input type="text" class="regular-text" id="rcp_settings[braintree_sandbox_publicKey]"
					   style="width: 300px;" name="rcp_settings[braintree_sandbox_publicKey]"
					   value="<?php if ( isset( $rcp_options['braintree_sandbox_publicKey'] ) ) {
						   echo esc_attr( $rcp_options['braintree_sandbox_publicKey'] );
					   } ?>"/>

				<button type="button" class="button button-secondary">
					<span toggle="rcp_settings[braintree_sandbox_publicKey]"
						  class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>
			<?php endif; ?>
			<p class="description"><?php _e( 'Enter your Braintree sandbox public key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_sandbox_privateKey]"><?php _e( 'Sandbox Private Key', 'rcp' ); ?></label>
		</th>
		<td>
			<?php if ( ! empty( $rcp_options['braintree_sandbox_privateKey'] ) ) : ?>
				<input type="password" class="regular-text" id="rcp_settings[braintree_sandbox_privateKey]"
					   style="width: 300px;" name="rcp_settings[braintree_sandbox_privateKey]"
					   value="<?php if ( isset( $rcp_options['braintree_sandbox_privateKey'] ) ) {
						   echo esc_attr( $rcp_options['braintree_sandbox_privateKey'] );
					   } ?>"/>

				<button type="button" class="button button-secondary">
					<span toggle="rcp_settings[braintree_sandbox_privateKey]"
						  class="dashicons dashicons-visibility toggle-credentials"></span>
				</button>
			<?php else : ?>
				<input type="text" class="regular-text" id="rcp_settings[braintree_sandbox_privateKey]"
					   style="width: 300px;" name="rcp_settings[braintree_sandbox_privateKey]"
					   value="<?php if ( isset( $rcp_options['braintree_sandbox_privateKey'] ) ) {
						   echo esc_attr( $rcp_options['braintree_sandbox_privateKey'] );
					   } ?>"/>

				<button type="button" class="button button-secondary">
					<span toggle="rcp_settings[braintree_sandbox_privateKey]"
						  class="dashicons dashicons-hidden toggle-credentials"></span>
				</button>
			<?php endif; ?>
			<p class="description"><?php _e( 'Enter your Braintree sandbox private key.', 'rcp' ); ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="rcp_settings[braintree_sandbox_encryptionKey]"><?php _e( 'Sandbox Client Side Encryption Key', 'rcp' ); ?></label>
		</th>
		<td>
			<?php if ( ! empty( $rcp_options['braintree_sandbox_encryptionKey'] ) ) : ?>
				<textarea
						class="regular-text"
						id="rcp_settings[braintree_sandbox_encryptionKey]"
						style="width: 300px;height: 100px; display: none"
						name="rcp_settings[braintree_sandbox_encryptionKey]"
				/><?php if ( isset( $rcp_options['braintree_sandbox_encryptionKey'] ) ) { echo esc_attr( $rcp_options['braintree_sandbox_encryptionKey'] ); } ?></textarea>

				<input
						type="password"
						id="rcp_settings[braintree_sandbox_encryptionKey_input]"
						style="width: 300px; height: 100px; display: inline-block"
						name="rcp_settings[braintree_sandbox_encryptionKey_input]"
						value="<?php if ( isset( $rcp_options['braintree_sandbox_encryptionKey'] ) ) { echo esc_attr( $rcp_options['braintree_sandbox_encryptionKey'] ); } ?>"
				/>
				<button type="button" class="button button-secondary">
				<span
						toggle="rcp_settings[braintree_sandbox_encryptionKey]"
						class="dashicons dashicons-visibility toggle-textarea"
						id="rcp_setting_braintree_toggle_sandbox">
				</span>
				</button>
			<?php else : ?>
				<textarea
						class="regular-text"
						id="rcp_settings[braintree_sandbox_encryptionKey]"
						style="width: 300px;height: 100px;"
						name="rcp_settings[braintree_sandbox_encryptionKey]"
				/><?php if ( isset( $rcp_options['braintree_sandbox_encryptionKey'] ) ) { echo esc_attr( $rcp_options['braintree_sandbox_encryptionKey'] ); } ?></textarea>
				<input
						type="password"
						id="rcp_settings[braintree_sandbox_encryptionKey_input]"
						style="display:none; width: 300px;"
						name="rcp_settings[braintree_sandbox_encryptionKey_input]"
						value="<?php if ( isset( $rcp_options['braintree_sandbox_encryptionKey'] ) ) { echo esc_attr( $rcp_options['braintree_sandbox_encryptionKey'] ); } ?>"
				/>
				<button type="button" class="button button-secondary">
				<span
						toggle="rcp_settings[braintree_sandbox_encryptionKey]"
						class="dashicons dashicons-hidden toggle-textarea"
						id="rcp_setting_braintree_toggle_sandbox">
				</span>
				</button>
			<?php endif; ?>
			<p class="description"><?php _e( 'Enter your Braintree sandbox client side encryption key.', 'rcp' ); ?></p>
		</td>
	</tr>
</table>
