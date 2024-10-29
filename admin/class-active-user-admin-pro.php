<?php

/**
* The admin-specific functionality of the plugin.
*
*/
class Active_User_Admin {

	/**
	* The ID of this plugin.
	*
	* @since    1.0.0
	* @access   private
	* @var      string    $active_user    The ID of this plugin.
	*/
	private $active_user;

	/**
	* The version of this plugin.
	*
	* @since    1.0.0
	* @access   private
	* @var      string    $version    The current version of this plugin.
	*/
	private $version;

	/**
	* Initialize the class and set its properties.
	*
	* @since    1.0.0
	* @param      string    $active_user       The name of this plugin.
	* @param      string    $version    The version of this plugin.
	*/
	public function __construct( $active_user, $version ) {

		$this->active_user = $active_user;
		$this->version = $version;
		add_action( 'wp_loaded', array($this, 'process_deletions' ));
		add_action( 'shutdown', array($this, 'process_messages' ));
	}

	/**
	* Register the stylesheets for the admin area.
	*
	* @since    1.0.0
	*/
	public function enqueue_styles() {

		wp_enqueue_style( $this->active_user, plugin_dir_url( __FILE__ ) . 'css/active-user-admin.css', array(), $this->version, 'all' );

	}

	/**
	* Register the JavaScript for the admin area.
	*
	* @since    1.0.0
	*/
	public function enqueue_scripts() {

		// wp_enqueue_script( $this->active_user, plugin_dir_url( __FILE__ ) . 'js/active_user-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	* Set up the admin page menu link.
	*
	* @since    1.0.0
	*/
	public function add_menu_page() {

		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 47 43.45"><path fill="black" d="M46.91,20.12a1,1,0,0,0-.9-.57H43.08a21.71,21.71,0,1,0-2.54,14.82,2,2,0,0,0-3.42-2A17.79,17.79,0,1,1,39,19.55h-2.9a1,1,0,0,0-.9.57,1,1,0,0,0,.14,1.05l4.94,5.93a1,1,0,0,0,.76.35,1,1,0,0,0,.75-.35l4.94-5.93A1,1,0,0,0,46.91,20.12Z" transform="translate(0 -1.77)"/><path fill="black" d="M21.6,21.67c-3.41,0-4.1-5.17-4.1-5.17-.41-2.69.82-5.72,4.05-5.72s4.48,3,4.08,5.72C25.63,16.5,25,21.67,21.6,21.67Zm0,3.86,4.09-2.73a7.23,7.23,0,0,1,6.79,6.8v3.74A42.39,42.39,0,0,1,21.6,35a41.49,41.49,0,0,1-10.88-1.7V29.6a6.89,6.89,0,0,1,6.72-6.73Z" transform="translate(0 -1.77)"/></svg>');

		add_menu_page(
			__( 'Active User Settings', 'active-user' ),
			__( 'Active User', 'active-user' ),
			'manage_options',
			'active-user',
			array( $this, 'display_menu_page' ),
			$icon_svg
		);

		add_submenu_page(
			'active-user',
			__( 'Settings', 'active-user' ),
			__( 'Settings', 'active-user' ),
			'manage_options',
			'active-user',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'active-user',
			__( 'Active User Table', 'active-user' ),
			__( 'Active User Table', 'active-user' ),
			'manage_options',
			'active-user-table',
			array( $this, 'display_last_activity_page' )
		);

		add_submenu_page(
			'active-user',
			__( 'Deletion Queue', 'active-user' ),
			__( 'Deletion Queue', 'active-user' ),
			'manage_options',
			'active-user-queue',
			array( $this, 'display_deletion_queue' )
		);
	}

	/**
	* Display the admin page.
	*
	* @since    1.0.0
	*/
	public function display_menu_page() {

	}

	/**
	* Display the admin page.
	*
	* @since    1.0.0
	*/
	public function display_settings_page() { ?>

		<div class="wrap">
			<h1><?php _e( 'Active User Settings', 'active-user' ) ?></h1>
			<!-- <h3><?php _e( 'Add the main description here', 'active-user' ) ?></h3> -->

			<!-- Contain in a DIV for future use -->
			<div class="wpau-admin-container">
				<?php
				$premium = new Active_User_Premium();
				$premium->action_screen();
				?>
				<form method="post" action="options.php">
					<?php
					do_settings_sections( 'purge_section' );
					settings_fields( 'purge_section' );
					submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	* Display the user last activity page.
	*
	* @since    1.0.0
	*/
	public function display_last_activity_page() {

		$current_user = wp_get_current_user();


		if( !empty( $_REQUEST['user']) ) {

			global $wpdb;

			$userids = array_map( 'intval', (array) $_REQUEST['user'] );
			$users_have_content = false;

			$comma_separated = implode(",", $userids);
			if ( $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_author IN($comma_separated) LIMIT 1 ") ) {
				$users_have_content = true;
			} elseif ( $wpdb->get_var( "SELECT link_id FROM {$wpdb->links} WHERE link_owner IN($comma_separated) LIMIT 1 " ) ) {
				$users_have_content = true;
			}

			?>

			<form method="post" name="updateusers" id="updateusers" >
				<?php wp_nonce_field( 'delete-users' ); ?>

				<div class="wrap">
					<h1><?php _e( 'Delete Users' ); ?></h1>
					<?php if ( isset( $_REQUEST['error'] ) ) { ?>
						<div class="error">
							<p><strong><?php _e( 'ERROR:' ); ?></strong> <?php _e( 'Please select an option.' ); ?></p>
						</div> <?php
					}

					if ( 1 == count( $userids ) ) : ?>
					<p><?php _e( 'You have specified this user for deletion:' ); ?></p> <?php
					else : ?>
					<p><?php _e( 'You have specified these users for deletion:' ); ?></p> <?php
				endif; ?>

				<ul>
					<?php
					$go_delete = 0;
					foreach ( $userids as $id ) {
						$user = get_userdata( $id );
						if ( $id == $current_user->ID ) {
							/* translators: 1: user id, 2: user login */
							echo '<li>' . sprintf( __( 'ID #%1$s: %2$s <strong>The current user will not be deleted.</strong>' ), $id, $user->user_login ) . "</li>\n";
						} else {
							/* translators: 1: user id, 2: user login */
							echo '<li><input type="hidden" name="users[]" value="' . esc_attr( $id ) . '" />' . sprintf( __( 'ID #%1$s: %2$s' ), $id, $user->user_login ) . "</li>\n";
							$go_delete++;
						}
					}
					?>
				</ul>
				<?php
				if ( $go_delete ) :

					if ( ! $users_have_content ) { ?>
						<input type="hidden" name="delete_option" value="delete" /> 	<?php
					} else {
						if ( 1 == $go_delete ) { ?>
							<fieldset><p><legend><?php _e( 'What should be done with content owned by this user?' ); ?></legend></p> <?php
						} else { ?>
							<fieldset><p><legend><?php _e( 'What should be done with content owned by these users?' ); ?></legend></p> <?php
						} ?>
						<ul style="list-style:none;">
							<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" />
								<?php _e( 'Delete all content.' ); ?></label></li>
								<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" /> <?php
								echo '<label for="delete_option1">' . __( 'Attribute all content to:' ) . '</label> ';
								wp_dropdown_users(
									array(
										'name'    => 'reassign_user',
										'exclude' => array_diff( $userids, array( $current_user->ID ) ),
										'show'    => 'display_name_with_login',
									)
								);
								?>
							</li>
						</ul></fieldset>
						<?php
					}
					/**
					* Fires at the end of the delete users form prior to the confirm button.
					* Follows similar hook in core.
					*
					* @since 1.0.0
					*
					* @param WP_User $current_user WP_User object for the current user.
					* @param int[]   $userids      Array of IDs for users being deleted.
					*/
					do_action( 'wpau_delete_user_form', $current_user, $userids );
					?>
					<input type="hidden" name="action" value="dodelete" />
					<?php submit_button( __( 'Confirm Deletion' ), 'primary', 'dodelete' ); ?>
				<?php else : ?>
					<p><?php _e( 'There are no valid users selected for deletion.' ); ?></p>
				<?php endif; ?>
			</div>
		</form>
		<?php
	} else { ?>

		<div class="wrap">
			<h1><?php _e( 'User Activity Table', 'active-user' ) ?></h1>
			<p><?php _e( 'You can use this screen to find out when your members were last on your site. If it has been too long, use their email address to find out where they have been, or delete them entirely.', 'active-user' ) ?></p>
			<form id="wpau-last-activity-table" method="post">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php
				$table = new Active_User_Table();
				$table->prepare_items();
				$table->views();
				$table->display(); ?>
			</form>
		</div>
		<?php
	}
}

/**
* Display the deletion queue page.
*
* @since    1.0.0
*/
public function display_deletion_queue() {


	$current_user = wp_get_current_user();


	if( !empty( $_REQUEST['user']) ) {

		global $wpdb;

		$userids = array_map( 'intval', (array) $_REQUEST['user'] );
		$users_have_content = false;

		$comma_separated = implode(",", $userids);
		if ( $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_author IN($comma_separated) LIMIT 1 ") ) {
			$users_have_content = true;
		} elseif ( $wpdb->get_var( "SELECT link_id FROM {$wpdb->links} WHERE link_owner IN($comma_separated) LIMIT 1 " ) ) {
			$users_have_content = true;
		}

		?>

		<form method="post" name="updateusers" id="updateusers" >
			<?php wp_nonce_field( 'delete-users' ); ?>

			<div class="wrap">
				<h1><?php _e( 'Delete Users' ); ?></h1>
				<?php if ( isset( $_REQUEST['error'] ) ) { ?>
					<div class="error">
						<p><strong><?php _e( 'ERROR:' ); ?></strong> <?php _e( 'Please select an option.' ); ?></p>
					</div> <?php
				}

				if ( 1 == count( $userids ) ) : ?>
				<p><?php _e( 'You have specified this user for deletion:' ); ?></p> <?php
				else : ?>
				<p><?php _e( 'You have specified these users for deletion:' ); ?></p> <?php
			endif; ?>

			<ul>
				<?php
				$go_delete = 0;
				foreach ( $userids as $id ) {
					$user = get_userdata( $id );
					if ( $id == $current_user->ID ) {
						/* translators: 1: user id, 2: user login */
						echo '<li>' . sprintf( __( 'ID #%1$s: %2$s <strong>The current user will not be deleted.</strong>' ), $id, $user->user_login ) . "</li>\n";
					} else {
						/* translators: 1: user id, 2: user login */
						echo '<li><input type="hidden" name="users[]" value="' . esc_attr( $id ) . '" />' . sprintf( __( 'ID #%1$s: %2$s' ), $id, $user->user_login ) . "</li>\n";
						$go_delete++;
					}
				}
				?>
			</ul>
			<?php
			if ( $go_delete ) :

				if ( ! $users_have_content ) { ?>
					<input type="hidden" name="delete_option" value="delete" /> 	<?php
				} else {
					if ( 1 == $go_delete ) { ?>
						<fieldset><p><legend><?php _e( 'What should be done with content owned by this user?' ); ?></legend></p> <?php
					} else { ?>
						<fieldset><p><legend><?php _e( 'What should be done with content owned by these users?' ); ?></legend></p> <?php
					} ?>
					<ul style="list-style:none;">
						<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" />
							<?php _e( 'Delete all content.' ); ?></label></li>
							<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" /> <?php
							echo '<label for="delete_option1">' . __( 'Attribute all content to:' ) . '</label> ';
							wp_dropdown_users(
								array(
									'name'    => 'reassign_user',
									'exclude' => array_diff( $userids, array( $current_user->ID ) ),
									'show'    => 'display_name_with_login',
								)
							);
							?>
						</li>
					</ul></fieldset>
					<?php
				}
				/**
				* Fires at the end of the delete users form prior to the confirm button.
				* Follows similar hook in core.
				*
				* @since 1.0.0
				*
				* @param WP_User $current_user WP_User object for the current user.
				* @param int[]   $userids      Array of IDs for users being deleted.
				*/
				do_action( 'wpau_delete_user_form', $current_user, $userids );
				?>
				<input type="hidden" name="action" value="dodelete" />
				<h4 style="color: red;">Caution! You are confirming deletion of the inactive users above. This action is irreversible.</h4>
				<?php submit_button( __( 'Confirm Deletion' ), 'primary', 'dodelete' ); ?>
			<?php else : ?>
				<p><?php _e( 'There are no valid users selected for deletion.' ); ?></p>
			<?php endif; ?>
		</div>
	</form>
	<?php
} else {

	$premium = new Active_User_Premium();

	?>

	<div class="wrap">
		<h1><?php _e( 'Active User Deletion Queue', 'active-user' ) ?></h1>
		<p><?php _e( "This page provides a way to quickly see who is inactive on your site. It does not show anyone who is currently in a grace period. Further explanation can be found on the plugin homepage ", 'active-user' ) ?><a href="https://www.bouncingsprout.com/plugins/active-user">here</a>.</p>

		<?php
		// Check first to see if there actually are any inactive users. Inactive users who are in grace should not be considered inactive until the grace period expires.
		$inactive_users = $premium->get_inactive_users_not_grace();
		if ( empty($inactive_users) ) {
			echo '<p>' . __( "There are no inactive users who meet the criteria.", 'active-user' ) . '</p>';
		} else { ?>
			<!-- Create a form so we can delete any users found -->
			<form id="wpau-deletion-queue"  method="post">
				<?php
				$inactive_users = $premium->get_inactive_users_not_grace();
				echo '<p>' . __( "The following users meet the criteria for being 'inactive' based on your settings.", 'active-user' ) . '</p><ul>';
				foreach ( $inactive_users as $inactive_user ) {
					$user = get_userdata( $inactive_user->user_id );
					$timestamp = $premium->get_last_activity( $user->ID );
					$timestamp_f = $timestamp->format('Y-m-d H:i:s');
					if ( !empty( $user ) ) {
						echo '<li><input type="hidden" name="user[]" value="' . esc_attr( $user->ID ) . '" />' . sprintf( __( 'ID #%1$s: %2$s' ), $user->ID, $user->user_login ) . " (last active: " . $timestamp_f . ")</li>\n";
					}
				}
				?></ul>
				<?php submit_button( __( 'Delete these inactive users' ), 'primary', 'delete' ); ?>
			</form>
		<?php }
		echo '</div>';
	}
}

/**
* Process the deletions.
*
* @since    1.0.0
*/
public function process_deletions() {
	if( !empty( $_REQUEST['delete_option']) ) {
		require_once(ABSPATH.'wp-admin/includes/user.php');
		if ( is_multisite() ) {
			wp_die( __( 'User deletion is not allowed from this screen.' ), 400 );
		}

		check_admin_referer( 'delete-users' );

		$redirect = 'admin.php?page=active-user-table';

		$userids = array_map( 'intval', (array) $_REQUEST['users'] );

		if ( ! current_user_can( 'delete_users' ) ) {
			wp_die( __( 'Sorry, you are not allowed to delete users.' ), 403 );
		}
		$nonce = wp_unslash( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'delete-users' ) ) {
			wp_die( __( "Sorry, you can't do that right now, hacker dude" ), 403 );
		} else {
			$updated       = 'del';
			$delete_count = 0;

			foreach ( $userids as $id ) {
				if ( ! current_user_can( 'delete_user', $id ) ) {
					wp_die( __( 'Sorry, you are not allowed to delete that user.' ), 403 );
				}

				if ( $id == $current_user->ID ) {
					$updated = 'err_admin_del';
					continue;
				}
				switch ( $_REQUEST['delete_option'] ) {
					case 'delete':
					wp_delete_user( $id );
					break;
					case 'reassign':
					wp_delete_user( $id, $_REQUEST['reassign_user'] );
					break;
				}
				++$delete_count;
			}

			$redirect = add_query_arg(
				array(
					'delete_count' => $delete_count,
					'update'       => $updated
				),
				$redirect
			);
			wp_redirect( $redirect );
			exit();
		}
	}
}

/**
* Process the admin messages.
*
* @since    1.0.0
*/
public function process_messages() {


	$messages = array();
	if ( !empty( $_REQUEST['update'] ) ) :
		switch ( $_REQUEST['update'] ) {
			case 'del':
			case 'del_many':
			$delete_count = isset( $_REQUEST['delete_count'] ) ? (int) $_REQUEST['delete_count'] : 0;
			if ( 1 == $delete_count ) {
				$message = __( 'User deleted.' );
			} else {
				$message = _n( '%s user deleted.', '%s users deleted.', $delete_count );
			}
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $delete_count ) ) . '</p></div>';
			break;
			break;
			case 'err_admin_del':
			$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __( 'You can&#8217;t delete the current user.' ) . '</p></div>';
			$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'Other users have been deleted.' ) . '</p></div>';
			break;
		}
	endif;
	?>

	<?php if ( isset( $errors ) && is_wp_error( $errors ) ) : ?>
		<div class="error">
			<ul>
				<?php
				foreach ( $errors->get_error_messages() as $err ) {
					echo "<li>$err</li>\n";
				}
				?>
			</ul>
		</div>
		<?php
	endif;

	if ( ! empty( $messages ) ) {
		foreach ( $messages as $msg ) {
			echo $msg;
		}
	}
}

/**
* Set up the settings section and fields.
*
* @since    1.0.0
*/
public function create_settings() {

	add_settings_section(
		'purge_section',
		__( '', 'active-user'),
		array( $this, 'purge_section_cb'),
		'purge_section'
	);

	// Add fields below

	// add_settings_field(
	// 	'wpau_auto_purge_toggle', // Our field ID
	// 	__( 'Enable Auto Purge:', 'active-user' ), // Our field title
	// 	array( $this, 'wpau_auto_purge_toggle_cb' ), // Our field callback
	// 	'purge_section', // The page our field is going on
	// 	'purge_section' // The section our field is going in
	// );

	add_settings_field(
		'wpau_purge_interval_length', // Our field ID
		__( 'Inactive period', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_interval_length_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section' // The section our field is going in
	);

	add_settings_field(
		'wpau_purge_interval_unit', // Our field ID
		__( '', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_interval_unit_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section'  // The section our field is going in
	);

	add_settings_field(
		'wpau_purge_grace_toggle', // Our field ID
		__( 'Enable grace period', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_grace_toggle_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section' // The section our field is going in
	);

	add_settings_field(
		'wpau_purge_grace_interval_length', // Our field ID
		__( 'Length of grace period', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_grace_interval_length_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section' // The section our field is going in
	);

	add_settings_field(
		'wpau_purge_grace_interval_unit', // Our field ID
		__( '', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_grace_interval_unit_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section' // The section our field is going in
	);

	add_settings_field(
		'wpau_purge_email_member_toggle', // Our field ID
		__( 'Send email', 'active-user' ), // Our field title
		array( $this, 'wpau_purge_email_member_toggle_cb' ), // Our field callback
		'purge_section', // The page our field is going on
		'purge_section' // The section our field is going in
	);

	register_setting( 'purge_section', 'purge_section', array($this, 'wpau_validate' ));
}

/**
* Set up the Purge settings section callback.
*
* @since    1.0.0
*/
public function purge_section_cb() {

	// $html = '<h3>';
	// $html .= __( 'Make changes below', 'active-user' );
	// $html .= '</h3>';
	//
	// echo $html;
}

/**
* Render the Purge toggle checkbox
*
* @since 1.0.0
*/
// public function wpau_auto_purge_toggle_cb() {
// 	$options = get_option('purge_section');
// 	$toggle = isset( $options['wpau_auto_purge_toggle'] ) ? $options['wpau_auto_purge_toggle'] : '0';
// 	echo '<input type="checkbox" id="wpau_auto_purge_toggle" name="purge_section[wpau_auto_purge_toggle]" value="1"' . checked(1, $toggle, false ) . '/>';
// }

/**
* Render the Purge grace period toggle checkbox
*
* @since 1.0.0
*/
public function wpau_purge_grace_toggle_cb() {
	$options = get_option('purge_section');
	$toggle = isset( $options['wpau_purge_grace_toggle'] ) ? $options['wpau_purge_grace_toggle'] : '0';
	echo '<input type="checkbox" id="wpau_purge_grace_toggle" name="purge_section[wpau_purge_grace_toggle]" value="1"' . checked(1, $toggle, false ) . '/>';
}

/**
* Render the Purge interval length
*
* @since 1.0.0
*/
public function wpau_purge_interval_length_cb() {
	$options = get_option('purge_section');
	$arg = isset( $options['wpau_purge_interval_length'] ) ? $options['wpau_purge_interval_length'] : '';
	echo '<input type="number" id="wpau_purge_interval_length" name="purge_section[wpau_purge_interval_length]" value="'. $arg . '" />';
}

/**
* Render the Purge interval unit
*
* @since 1.0.0
*/
public function wpau_purge_interval_unit_cb() {
	$options = get_option( 'purge_section' );
	$options['wpau_purge_interval_unit'] = !empty( $options['wpau_purge_interval_unit'] ) ? $options['wpau_purge_interval_unit'] : 'M';
	$html = '<select id="wpau_purge_interval_unit" name="purge_section[wpau_purge_interval_unit]">';
	$html .= '<option value="D"' . selected( $options['wpau_purge_interval_unit'], 'D', false) . '>Days</option>';
	$html .= '<option value="M"' . selected( $options['wpau_purge_interval_unit'], 'M', false) . '>Months</option>';
	$html .= '<option value="Y"' . selected( $options['wpau_purge_interval_unit'], 'Y', false) . '>Years</option>';
	$html .= '</select>';
	$html .= '<p class="description">' . __('Enter the number of days, months, or years after which a user is considered inactive', 'active_user') . '</p>';

	echo $html;
}

/**
* Render the Purge grace period interval length
*
* @since 1.0.0
*/
public function wpau_purge_grace_interval_length_cb() {
	$options = get_option('purge_section');
	$arg = isset( $options['wpau_purge_grace_interval_length'] ) ? $options['wpau_purge_grace_interval_length'] : '';
	echo '<input type="number" id="wpau_purge_grace_interval_length" name="purge_section[wpau_purge_grace_interval_length]" value="'. $arg . '" />';
}

/**
* Render the Purge grace period interval unit
*
* @since 1.0.0
*/
public function wpau_purge_grace_interval_unit_cb() {
	$options = get_option( 'purge_section' );
	$options['wpau_purge_grace_interval_unit'] = !empty( $options['wpau_purge_grace_interval_unit'] ) ? $options['wpau_purge_grace_interval_unit'] : 'M';
	$html = '<select id="wpau_purge_grace_interval_unit" name="purge_section[wpau_purge_grace_interval_unit]">';
	$html .= '<option value="D"' . selected( $options['wpau_purge_grace_interval_unit'], 'D', false) . '>Days</option>';
	$html .= '<option value="M"' . selected( $options['wpau_purge_grace_interval_unit'], 'M', false) . '>Months</option>';
	$html .= '<option value="Y"' . selected( $options['wpau_purge_grace_interval_unit'], 'Y', false) . '>Years</option>';
	$html .= '</select>';
	$html .= '<p class="description">' . __('Enter the number of days, months, or years the grace period should last', 'active_user') . '</p>';

	echo $html;
}

/**
* Render the email member toggle checkbox
*
* @since 1.0.0
*/
public function wpau_purge_email_member_toggle_cb() {
	$options = get_option('purge_section');
	$toggle = isset( $options['wpau_purge_email_member_toggle'] ) ? $options['wpau_purge_email_member_toggle'] : '0';
	echo '<input type="checkbox" id="wpau_purge_email_member_toggle" name="purge_section[wpau_purge_email_member_toggle]" value="1"' . checked(1, $toggle, false ) . '/>';
	echo '<p class="description">' . __('Tick to send an email to the user when they become inactive', 'active_user') . '</p>';
}

/**
* Render the email member body
*
* @since 1.0.0
*/
public function wpau_purge_email_member_body_cb() {
	$options = get_option('purge_section');
	$placeholder = "You have not been active on the site recently. Please login as soon as you can to prevent your account from being deleted.";
	$arg = !empty( $options['wpau_purge_email_member_body'] ) ? $options['wpau_purge_email_member_body'] : $placeholder;
	echo '<textarea id="wpau_purge_email_member_body" name="purge_section[wpau_purge_email_member_body]" cols="40" rows="10">'. $arg . '</textarea>';
	echo '<p class="description">' . __('You can change the content of the above email here', 'active_user') . '</p>';
}

/**
* Render the email admin toggle checkbox
*
* @since 1.0.0
*/
public function wpau_purge_email_admin_toggle_cb() {
	$options = get_option('purge_section');
	$toggle = isset( $options['wpau_purge_email_admin_toggle'] ) ? $options['wpau_purge_email_admin_toggle'] : '0';
	echo '<input type="checkbox" id="wpau_purge_email_admin_toggle" name="purge_section[wpau_purge_email_admin_toggle]" value="1"' . checked(1, $toggle, false ) . '/>';
	echo '<p class="description">' . __('Email the site administrator when there are inactive members', 'active_user') . '</p>';
}

/**
* Validate our code. Keep everyone safe and happy.
*
* @since 1.0.0
*/
public function wpau_validate( $input ) {

	// Create our array for storing the validated options
	$output = array();

	// Loop through each of the incoming options
	foreach( $input as $key => $value ) {

		// Check to see if the current option has a value. If so, process it.
		if( isset( $input[$key] ) ) {

			// Strip all HTML and PHP tags and properly handle quoted strings
			$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );

		} // end if

	} // end foreach

	// Return the array processing any additional functions filtered by this action
	return apply_filters( 'wpau_validate', $output, $input );

}

}
