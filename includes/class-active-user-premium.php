<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
* The class that looks after the Toolkit's Purge function
*
*/
class Active_User_Premium {

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
  * @param      string    $Active_User       The name of this plugin.
  * @param      string    $version    The version of this plugin.
  */
  public function __construct() {

    if( !wp_next_scheduled( 'purge_cron' ) ) {
      wp_schedule_event( time(), 'daily', 'purge_cron' );
    }

    add_action( 'purge_cron', array($this, 'do_daily_tasks' ));
  }

  /**
  * Initialize the class hooks.
  *
  * @since    1.0.0
  */
  public function init() {
    add_action( 'wp_head', array($this, 'report' ));
  }

  /**
  * Gets the last activity time from BuddyPress.
  * @since 1.0.0
  * @return DateTime
  */
  public function get_last_activity( $user = 0 ) {

    if ( wpau_bp_active() ) {

      $activity = bp_get_user_last_activity($user);

      if (!is_numeric($activity)) {
        $activity = new DateTime( $activity );
      }
    } else {
      if ( !empty( get_user_meta( $user, 'wpau_last_login')) ) {
        $activity = new DateTime( get_user_meta( $user, 'wpau_last_login', true ) );
      } else {
        $activity = null;
      }
    }

    return $activity;
  }

  /**
  * Returns true if user is inactive.
  * @since 1.0.0
  * @param int $user
  * @return boolean
  */
  public function is_inactive( $user ) {

    $this->user = $user;

    $last_active = $this->get_last_activity( $user );

    $now = new DateTime();

    $inactive_interval = $this->get_interval();
    $inactive_date = $now->sub($inactive_interval);

    if ( isset($last_active) && ($last_active < $inactive_date) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
  * Returns true if user is in grace period.
  * @since 1.0.0
  * @param int $user
  * @return boolean
  */
  public function is_in_grace( $user ) {

    $this->user = $user;

    $data = get_user_meta( $user, 'wpau_grace', true);

    if (!empty($data)) {
      if ( $data == 'active') {
        return true;
      } else {
        return false;
      }
    }


  }

  /**
  * Returns an array of all inactive users.
  * @since 1.0.0
  * @return array users.
  */
  public function get_inactive_users() {
    global $wpdb;

    $interval = $this->get_interval();

    $Now = new DateTime();
    $a = $Now->sub( $interval );
    $interval_date = $a->format('Y-m-d H:i:s');

    $data = array();

    if ( wpau_bp_active() ) {
      $activity_table = $wpdb->prefix . 'bp_activity';
      $query = "SELECT $activity_table.user_id FROM $activity_table WHERE $activity_table.date_recorded < '$interval_date' AND $activity_table.type = 'last_activity'";
    } else {
      $usermeta_table = $wpdb->prefix . 'usermeta';
      $query = "SELECT $usermeta_table.user_id FROM $usermeta_table WHERE $usermeta_table.meta_key = 'wpau_last_login' AND  $usermeta_table.meta_value < '$interval_date'";
    }


    $inactive_users = $wpdb->get_results( $query );

    foreach ( $inactive_users as $inactive_user ) {
      $user = get_userdata( $inactive_user->user_id );
      if ( !empty($user) ) {
        $data[] = $inactive_user;
      }
    }

    return $data;
  }

  /**
  * Returns an array of all inactive users who are not currently in grace period.
  * @since 1.0.0
  * @return array users.
  */
  public function get_inactive_users_not_grace() {

    $inactive_users = $this->get_inactive_users();
    $data = array();

    foreach ( $inactive_users as $inactive_user ) {
      $user = get_userdata( $inactive_user->user_id );
      if ( $this->is_in_grace( $user->ID ) == false ) {
        $data[] = $inactive_user;
      }
    }

    return $data;
  }

  /**
  * Returns the count of inactive users.
  * @since 1.0.0
  * @return int Count of users.
  */
  public function get_inactive_count() {

    $data = $this->get_inactive_users();

    return count($data);
  }

  /**
  * Returns whether auto-purge is enabled.
  * @since 1.0.0
  * @return boolean
  */
  public function is_autopurge() {

    $options = get_option('purge_section');

    if ( !isset( $options['wpau_auto_purge_toggle'] ) || empty( $options['wpau_auto_purge_toggle'] ) || $options['wpau_auto_purge_toggle'] == 0 ) {
      return false;
    } else {
      return true;
    }
  }

  /**
  * Returns whether the grace period function is enabled.
  * @since 1.0.0
  * @return boolean
  */
  public function is_grace() {

    $options = get_option('purge_section');

    if ( !isset( $options['wpau_purge_grace_toggle'] ) || empty( $options['wpau_purge_grace_toggle'] ) || $options['wpau_purge_grace_toggle'] == 0 ) {
      return false;
    } else {
      return true;
    }
  }

  /**
  * Returns whether the grace period email function is enabled.
  * @since 1.0.0
  * @return boolean
  */
  public function is_grace_email() {

    $options = get_option('purge_section');

    if ( !isset( $options['wpau_purge_email_member_toggle'] ) || empty( $options['wpau_purge_email_member_toggle'] ) || $options['wpau_purge_email_member_toggle'] == 0 ) {
      return false;
    } else {
      return true;
    }
  }

  /**
  * Returns the length and unit of the purge inactivity period.
  * @since 1.0.0
  * @return DateInterval A DateInterval object.
  */
  public function get_interval() {

    $options = get_option('purge_section');
    $duration = ( (!isset( $options['wpau_purge_interval_length'] )) || (empty( $options['wpau_purge_interval_length'] )) ) ? 0 : $options['wpau_purge_interval_length'];
    $unit = ( (!isset( $options['wpau_purge_interval_unit'] )) || (empty( $options['wpau_purge_interval_unit'] )) ) ? 'D' : $options['wpau_purge_interval_unit'];

    $string = "P" . $duration . $unit;

    $interval = new DateInterval( $string );
    return $interval;
  }

  /**
  * Returns the length and unit of the purge grace period.
  * @since 1.0.0
  * @return DateInterval A DateInterval object.
  */
  public function get_grace_interval() {

    $options = get_option('purge_section');
    $duration = ( (!isset( $options['wpau_purge_grace_interval_length'] )) || (empty( $options['wpau_purge_grace_interval_length'] )) ) ? 0 : $options['wpau_purge_grace_interval_length'];
    $unit = ( (!isset( $options['wpau_purge_grace_interval_unit'] )) || (empty( $options['wpau_purge_grace_interval_unit'] )) ) ? 'D' : $options['wpau_purge_grace_interval_unit'];

    $string = "P" . $duration . $unit;

    $interval = new DateInterval( $string );
    return $interval;
  }

  /**
  * Sends a user an email to advise they are in a grace period.
  * @since 1.0.0
  *
  */
  public function send_grace_email( $user = 0 ) {

    $user_object = get_user_by( 'id', $user );
    $site_name = get_bloginfo();
    $site_url = get_site_url();
    $grace_end_object = get_user_meta( $user, 'wpau_grace_end', true);
    $grace_end = $grace_end_object->format('l jS F Y');

    $to = $user_object->user_email;
    $subject = 'Your account on ' . $site_name;
    $body = 'You have been inactive on ' . $site_name . ' for a while. If you wish to remain a member, please login at ' . $site_url . ' before ' . $grace_end . ' to prevent your account being removed.';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $success = wp_mail( $to, $subject, $body, $headers );
    return $success;
  }

  /**
  * Place user in grace period. Grace period only valid at the point this is set - grace toggling, or grace interval changing will have no effect once grace has been set on a user.
  * @since 1.0.0
  *
  */
  public function set_grace() {

    if ( $this->is_grace() ) {
      $users = $this->get_inactive_users();
      foreach ( $users as $user ) {
        $id = $user->user_id;
        $grace = get_user_meta( $id, 'wpau_grace', true );
        if ( empty( $grace ) ) {
          $now = new DateTimeImmutable();
          $grace_end = $now->add($this->get_grace_interval());
          update_user_meta( intval($id), 'wpau_grace', 'active');
          update_user_meta( intval($id), 'wpau_grace_start', $now);
          update_user_meta( intval($id), 'wpau_grace_end', $grace_end);
          if ( $this->is_grace_email() ) {
            $success = $this->send_grace_email( intval($id) );
            if ( $success ) {
              update_user_meta( intval($id), 'wpau_grace_email_sent', 'Successful' );
            } else {
              update_user_meta( intval($id), 'wpau_grace_email_sent', 'Not Successful' );
            }
          }
        }
      }
    }
  }

  /**
  * Expire a users grace period. Checks grace is active, then loops through all inactive users.
  * If a user is also in a grace period, it tests to see if the grace end date has passed. If so, it sets the
  * grace meta to 'expired' and deletes the two dates.
  * @since 1.0.0
  *
  */
  public function expire() {

    if ( $this->is_grace() ) {

      $users = $this->get_inactive_users();
      foreach ( $users as $user ) {

        $id = $user->user_id;
        $grace = get_user_meta( $id, 'wpau_grace', true );
        if (  $grace == 'active' ) {

          $start = get_user_meta( $id, 'wpau_grace_start', true );
          $end = get_user_meta( $id, 'wpau_grace_end', true );
          if ( !empty($start) && !empty($end) ) {
            $now = new DateTimeImmutable();

            if ( $end < $now) {
              update_user_meta( intval($id), 'wpau_grace', 'expired');
              delete_user_meta( intval($id), 'wpau_grace_start');
              delete_user_meta( intval($id), 'wpau_grace_end');
            }
          } else {
            echo "Stop hacking the database";
          }

        }
      }
    }
  }

  /**
  * Debug.
  * @since 1.0.0
  */
  public function report() {

  }

  /**
  * Processes daily tasks.
  * @since 1.0.0
  *
  */
  public function do_daily_tasks() {

    $this->set_grace();
    $this->expire();
  }

  /**
  * Returns contents of action screen.
  * @since 1.0.0
  * @return mixed
  */
  public function action_screen() {

    $grace = ($this->is_grace()) ? '<span style="color: green; font-weight: bold;">on</span>. Once a user becomes inactive, they will enter a grace period' : '<span style="color: red; font-weight: bold;">off</span>';
    if ( $this->is_grace() ) {
      $email = ($this->is_grace_email()) ? 'They will be automatically emailed at the start of the grace period.' : '';
    } else {
      $email = '';
    }

    ?>

    <div class="wpau-settings-hero">
      <h3>Welcome to the Active User settings page</h3>
      <p>There are <?php echo $this->get_inactive_count() ?> inactive members, based on the settings below. To delete them, use the <a href="<?php get_admin_url() ?>?page=active-user-table&purge_filter=inactive">Active User Table</a> or the <a href="<?php get_admin_url() ?>?page=active-user-queue">Deletion Queue</a>.</p>
      <p>The grace period is turned <?php echo $grace ?>. <?php echo $email ?> If you enable the grace period below, all currently inactive users (regardless of how long they have been inactive) will enter a grace period. This allows you to give a 'final chance' to those members before you delete them. Please ensure the length of grace period is correct before saving changes, as once activated, that user's grace period is permanent.</p>
      <p><?php _e( "Further explanation can be found on the plugin homepage ", 'active-user' ) ?><a href="https://www.bouncingsprout.com/plugins/active-user">here</a>.</p>
    </div>

    <?php
  }

}
