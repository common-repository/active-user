<?php

if( !class_exists( 'WP_List_Table' ) ){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
* The class that extends WP_List_table, to allow us to display a custom table.
*
*/
class Active_User_Table extends WP_List_Table {

  /**
  * Initialize the class and set its properties.
  *
  * @since    1.0.0
  */
  function __construct(){
    global $status, $page;

    //Set parent defaults
    parent::__construct( array(
      'singular' => __( 'user', 'active-user' ), //singular name of the listed records
      'plural'   => __( 'users', 'active-user' ), //plural name of the listed records
      'ajax'      => false        //does this table support ajax?
    ) );
  }

  /**
  * Default column function
  * @since    1.0.0
  * @param array $item A singular item (one full row's worth of data)
  * @param array $column_name The name/slug of the column to be processed
  * @return string Text or HTML to be placed inside the column <td>
  **************************************************************************/
  function column_default($item, $column_name){
    switch($column_name){
      case 'Username':
      case 'Email':
      return $item[$column_name];
      default:
      return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
  }


  /**
  * Set up the first column, with row actions.
  * @since    1.0.0
  *
  * @see WP_List_Table::::single_row_columns()
  * @param array $item A singular item (one full row's worth of data)
  * @return string Text to be placed inside the column <td>
  **************************************************************************/
  function column_user_login($item){

    $page = wp_unslash( $_REQUEST['page'] );
    $nonce = wp_create_nonce( 'row_delete_nonce' );

    $actions = array(
      'delete'    => sprintf('<a href="?page=%s&action=%s&user=%s">Delete</a>', $page, 'delete', $item['ID'] ),
    );

    return sprintf('%1$s %3$s',
    /*$1%s*/ $item['user_login'],
    /*$2%s*/ $item['ID'],
    /*$3%s*/ $this->row_actions($actions)
  );
}

/**
* Set up the email column.
* @since    1.0.0
*
* @see WP_List_Table::::single_row_columns()
* @param array $item A singular item (one full row's worth of data)
* @return string Text to be placed inside the column <td>
**************************************************************************/
function column_user_email($item){

  //Return the title contents
  return sprintf('%1$s',
  /*$1%s*/ $item['user_email']
);
}

/**
* Set up the last activity column.
* @since    1.0.0
*
* @see WP_List_Table::::single_row_columns()
* @param array $item A singular item (one full row's worth of data)
* @return string Text to be placed inside the column <td>
**************************************************************************/
function column_last($item){

  //Return the title contents

  if ( $item['last'] == null ) {
    return 'No Data';
  } else {
    return sprintf('%1$s',
    /*$1%s*/ $item['last']);
  }


}

/**
* Set up the checkbox column.
* @since    1.0.0
*
* @see WP_List_Table::::single_row_columns()
* @param array $item A singular item (one full row's worth of data)
* @return string Text to be placed inside the column <td> (movie title only)
**************************************************************************/
function column_cb($item){
  return sprintf(
    '<input type="checkbox" name="%1$s[]" value="%2$s" />',
    /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label
    /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
  );
}

/**
* Set up the display of columns.
* @since    1.0.0
*
* @see WP_List_Table::::single_row_columns()
* @return array An associative array containing column information.
**************************************************************************/
function get_columns(){
  $title = ( wpau_bp_active() ) ? 'Last Activity' : 'Last Login' ; // BuddyPress uses a different term as it records activity not just login
  $name = ( wpau_bp_active() ) ? 'Member' : 'User' ; // BuddyPress calls its users 'members'
  $columns = array(
    'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
    'user_login'    => $name,
    'last' => $title,
    'user_email'    => 'Email'
  );
  return $columns;
}

/**
* Return if no users fit the criteria.
* @since    1.0.0
*
* @see WP_List_Table::::single_row_columns()
* @return string A message.
**/
public function no_items() {
  _e( 'No users to show.', 'active-user' );
}

/**
* Defines which of our columns are sortable.
* @since    1.0.0
*
* @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
**/
function get_sortable_columns() {
  $sortable_columns = array(
    'user_login'     => array('user_login',true),     //true means it's already sorted
    'last'    => array('last',true)
  );
  return $sortable_columns;
}

/**
* Define bulk actions.
*
* @since    1.0.0
*
**/
function get_bulk_actions() {
  $actions = array(
    'delete'    => 'Delete'
  );
  return $actions;
}

/**
* Our main funtion to process the table.
*
* @global WPDB $wpdb
* @uses $this->_column_headers
* @uses $this->items
* @uses $this->get_columns()
* @uses $this->get_sortable_columns()
* @uses $this->get_pagenum()
* @uses $this->set_pagination_args()
**/
function prepare_items() {
  global $wpdb; // This is used only if making any database queries

  $per_page = 10; // Display 10 records per page

  $columns = $this->get_columns();
  $hidden = array();
  $sortable = $this->get_sortable_columns();

  $this->_column_headers = array($columns, $hidden, $sortable);

  $user_table = $wpdb->prefix . 'users';
  $usermeta_table = $wpdb->prefix . 'usermeta';
  $activity_table = $wpdb->prefix . 'bp_activity';
  $orderby = ( isset( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'user_login';
  $order = ( isset( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'ASC';

  if ( wpau_bp_active() ) {
    $query = "SELECT
    $user_table.ID,
    $user_table.user_login,
    $user_table.user_email,
    $activity_table.date_recorded AS last
    FROM $activity_table JOIN $user_table ON $user_table.ID = $activity_table.user_id
    WHERE component = 'members' AND type = 'last_activity' AND $activity_table.date_recorded ORDER BY $orderby $order";
  } else {
    $query = "SELECT
    $user_table.ID,
    $user_table.user_login,
    $user_table.user_email,
    $usermeta_table.meta_value AS last
    FROM $usermeta_table RIGHT JOIN $user_table ON $user_table.ID = $usermeta_table.user_id AND $usermeta_table.meta_key = 'wpau_last_login' ORDER BY $orderby $order";
  }



  $data = $wpdb->get_results( $query, ARRAY_A );

  $current_page = $this->get_pagenum();

  $total_items = count($data);

  $data = array_slice($data,(($current_page-1)*$per_page),$per_page);

  $this->items = $data;

  $this->set_pagination_args( array(
    'total_items' => $total_items,                  //WE have to calculate the total number of items
    'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
    'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
  ) );
}


}
