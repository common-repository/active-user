<?php

/**
* The admin-specific functionality of the plugin.
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

		/**
		* This function is provided for demonstration purposes only.
		*
		* An instance of this class should be passed to the run() function
		* defined in Active_User_Loader as all of the hooks are defined
		* in that particular class.
		*
		* The Active_User_Loader will then create the relationship
		* between the defined hooks and the functions defined in this
		* class.
		*/

		wp_enqueue_style( $this->active_user, plugin_dir_url( __FILE__ ) . 'css/active-user-admin.css', array(), $this->version, 'all' );

	}

	/**
	* Register the JavaScript for the admin area.
	*
	* @since    1.0.0
	*/
	public function enqueue_scripts() {

		/**
		* This function is provided for demonstration purposes only.
		*
		* An instance of this class should be passed to the run() function
		* defined in Active_User_Loader as all of the hooks are defined
		* in that particular class.
		*
		* The Active_User_Loader will then create the relationship
		* between the defined hooks and the functions defined in this
		* class.
		*/

		// wp_enqueue_script( $this->active_user, plugin_dir_url( __FILE__ ) . 'js/active-user-admin.js', array( 'jquery' ), $this->version, false );

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
			<h1><?php _e( 'Active User Settings (enabled in the Pro Edition - now available)', 'active-user' ) ?></h1>
			<!-- <h3><?php _e( 'Add the main description here', 'active-user' ) ?></h3> -->

			<!-- Contain in a DIV for future use -->
			<div class="wpau-advert wpau-settings-advert">
				<h3><?php _e( "Active User Pro Edition - Settings", 'active-user' ) ?></h3>

				<div class="wpau-advert-content">
					<img class="wpau-settings-image" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/settings.jpg' ?>" alt="">
					<div class="wpau-advert-content-main">
						<p>Upgrade now to the Pro Edition and receive a full suite of tools to deal with inactive users on your site.</p>
						<ol>
							<li>The inactivity period function is the heart of the plugin. It allows you to set a point at which your users become 'inactive'. In this example, once a user has not logged in (or been active on a BuddyPress site) for 6 months, they fall into this category. Now, with a defined inactivity date, you can quickly filter who has been engaging with your site, shop or community, and who should be removed.</li>
							<li>The grace period is another powerful feature of Active User Pro. Once enabled, as soon as a user becomes inactive, they enter a grace period. This is similar to a cooling-off period, or a function to give a user the benefit of the doubt by giving them a little bit more time to login, before you take further action.</li>
							<li>The length of the grace period, like the inactive period can be set in days, months or years. We recommend setting this to no more than a month.</li>
							<li>Another fantastic feature of the Active User Pro edition is the ability to notify a user once their grace period has commenced. The user is automatically sent an email explaining that they must login before the final date of the grace period, otherwise their account may be removed. Together with the grace period, this function provides another layer of protection to a site administrator, that they have done all they can to warn a user before they are ultimately removed.</li>
						</ol>
						<p>To see all the Pro Edition features, head to the <a href="https://www.bouncingsprout.com/plugins/active-user">plugin homepage</a>, or use the 'Upgrade' link in the menu to upgrade now.</p>
					</div>

				</div>

			</div>
			<div class="wpau-advert wpau-settings-advert">
				<h3><?php _e( "Active User Pro Edition - Deletion Queue", 'active-user' ) ?></h3>

				<div class="wpau-advert-content">
					<img class="wpau-settings-image" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/queue.jpg' ?>" alt="">
					<div class="wpau-advert-content-main">
						<p>The Deletion Queue allows a site administrator to immediately see a list of their inactive users, and remove them in one go. All your site's users are automatically checked to see if they have become inactive every day. If the grace period function is enabled, the user is not added tot he deletion queue until their grace period has expired. Imagine using this queue, once every couple of months, to clean up your inactive users. No more manual deletions. No more wasting time.</p>
						<p>To see all the Pro Edition features, head to the <a href="https://www.bouncingsprout.com/plugins/active-user">plugin homepage</a>, or use the 'Upgrade' link in the menu to upgrade now.</p>
					</div>

				</div>

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
			<p><?php _e( 'You can use this screen to find out when your users were last on your site. If it has been too long, use their email address to find out where they have been, or delete them entirely.', 'active-user' ) ?></p>
			<div class="wpau-advert">
				<h3><?php _e( "Active User Pro Edition - Now Available", 'active-user' ) ?></h3>

				<div class="wpau-advert-content">
					<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/table.jpg' ?>" alt="">
					<div class="wpau-advert-content-main">
						<p>Upgrade now to the Pro Edition and get an enhanced User Activity Table, with some fantastic features. Works directly with the other advanced funtions, such as the ability to set a date at which your users become inactive. You can then remove users who don't meet that criteria, directly from the User Activity Table.</p>
						<ol>
							<li>Filter your results to see only users who are <a href="<?php echo admin_url() . 'admin.php?page=active-user' ?>">inactive</a>, or who are in a <a href="<?php echo admin_url() . 'admin.php?page=active-user' ?>">grace period</a></li>
							<li>Handy addition of user ID</li>
							<li>Users in a <a href="<?php echo admin_url() . 'admin.php?page=active-user' ?>">grace period</a> highlighted yellow</li>
							<li><a href="<?php echo admin_url() . 'admin.php?page=active-user' ?>">Inactive</a> users highlighted red</li>
						</ol>
						<p>To see all the Pro Edition features, head to the <a href="https://www.bouncingsprout.com/plugins/active-user">plugin homepage</a>, or use the 'Upgrade' link in the menu to upgrade now.</p>
					</div>

				</div>

			</div>
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

		$redirect = 'admin.php?page=active-user';

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

}
