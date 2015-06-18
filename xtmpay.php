<?php
/*
Plugin Name: XTMPAY Gateway
URI: http://iamzer.com
Description: A TrueMoney payment gateway (TrueMoney cash Card). [ Service by tmpay.net ] 
Version: 1.1
Author: Alongkorn Khaoto
Author URI: http://www.iamzer.com
License: GPL2
*/
session_start();

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
//$wpdb->show_errors();

define( 'TMPAY__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TMPAY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


require_once TMPAY__PLUGIN_DIR . '/inc/tmpay_globals.class.php';
require_once TMPAY__PLUGIN_DIR . '/inc/functions.php';



register_activation_hook(__FILE__,'tmpay_x745'); 
register_deactivation_hook( __FILE__, 'tmpay_x733' );


function tmpay_x745() {

  global $wpdb;
  global $tmpay_db_version;


$stripe_tmpay_settings = $wpdb->prefix . TMPAY::TABLE_SETTING;
$TMPAYDB = $wpdb->get_row($wpdb->prepare( "SELECT * FROM $stripe_tmpay_settings"));
if(!isset($TMPAYDB->id)){

    $sql = "CREATE TABLE " . $stripe_tmpay_settings . " (
          tm_id bigint(20) NOT NULL AUTO_INCREMENT,
          tmcode VARCHAR(64) NOT NULL,
          tmvalue longtext NOT NULL,
          UNIQUE KEY tm_id (tm_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    add_option("xtmpay_merchant_id",'');
    add_option("xtmpay_licensekey",'');
    add_option("xtmlicensekey_type",'');

    tmpay_insert("vip_value",'50');
    tmpay_insert("xtmpay_noticetext",'');

}


$stripe_user_settings = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
$myTMpayDB = $wpdb->get_row($wpdb->prepare( "SELECT * FROM $stripe_user_settings"));
if(!isset($myTMpayDB->card_id)){

    $sql = "CREATE TABLE " . $stripe_user_settings . " (
          card_id MEDIUMINT( 6 ) NOT NULL AUTO_INCREMENT,
          password VARCHAR(14) NOT NULL,
          user_id MEDIUMINT( 9 ) NOT NULL,
          amount MEDIUMINT( 4 ) NOT NULL,
          status TINYINT( 1 ) NOT NULL,
          added_time DATETIME,
          PRIMARY KEY card_id (card_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    update_option("tmpay_db_version", TMPAY::DB_VERSION);
}
        $new_page_title = 'TrueMoney';
        $new_page_content = '<br> [xtmpay]';
        $new_page_template = ''; 
        $page_check = get_page_by_title($new_page_title);
        $new_page = array(
                'post_type' => 'page',
                'post_title' => $new_page_title,
                'post_content' => $new_page_content,
                'post_status' => 'publish',
                'post_author' => 1,
        );
        if(!isset($page_check->ID)){
                $new_page_id = wp_insert_post($new_page);
                if(!empty($new_page_template)){
                        update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
                }
        }

}

function tmpay_x733() {
global $wpdb;
$tableTMpay = $wpdb->prefix . TMPAY::TABLE_SETTING;
$tableTruemoney = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
$wpdb->query( "DROP TABLE $tableTMpay" );
$wpdb->query( "DROP TABLE $tableTruemoney" );
}

function tmpay_scripts_plugin()
{
        wp_enqueue_style( 'tmpay-comb', plugins_url( '/xtmpay/css/bootstrap-combined.min.css' ) );
        wp_enqueue_style( 'tmpay-ed', plugins_url( '/xtmpay/css/bootstrap2-editable.css' ) );
}
if(isset($_GET['page'])){
$chkadminpage=sanitize_text_field($_GET['page']);
}else{
  $chkadminpage='';
}
if ($chkadminpage == 'tmpay-truemoney' OR $chkadminpage == 'tmpay-setting'){
add_action( 'admin_enqueue_scripts', 'tmpay_scripts_plugin' );
}


//////////////////////// XTMPAY JS/TMPAY
add_filter('query_vars','xtmpay_add_trigger');
function xtmpay_add_trigger($vars) {
    $vars[] = 'xtmpay';
    return $vars;
}

function xtmpay_rewrite()
{
    add_rewrite_rule('xtm-([^/]*)/?', 'index.php?xtmpay=$matches[1]&', 'top');
    flush_rewrite_rules();
}
add_action("init", "xtmpay_rewrite");

add_action('template_redirect', 'xtmpay_trigger_check');
function xtmpay_trigger_check() {


////////// Reply From TMPAY Sever
if(sanitize_text_field(get_query_var('xtmpay')) == "chk") {
if($_SERVER['REMOTE_ADDR'] != TMPAY::SERVERIP) die('ERROR|ACCESS_DENIED');
else
{

 $password = sanitize_text_field($_GET['password']);
 $amount = sanitize_text_field($_GET['amount']);
 $status = sanitize_text_field($_GET['status']);

if (misc_parsestring($password,'0123456789') == FALSE ){
  die('ERROR|ERROR_PWD');
}

if (misc_parsestring($amount,'0123456789') == FALSE ){
  die('ERROR|ERROR_AMOUNT');
}

if (misc_parsestring($status,'0123456789') == FALSE ){
  die('ERROR|ERROR_STATUS');
}
  global $_CONFIG;
  global $wpdb;
  $tmpaydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
  $card_result = $wpdb->get_row($wpdb->prepare( 'SELECT card_id,user_id FROM '.$tmpaydb.' WHERE password= %s AND status=0 LIMIT 1',$password));
  $rowcount = $wpdb->num_rows;
  

  if($rowcount == 1)
  {
    if($_CONFIG['tmpay']['amount'][$amount]>=tmpay('vip_value')){
      update_user_meta( $card_result->user_id, 'xvip', 'yes' );
    }
    $wpdb->query($wpdb->prepare( 'UPDATE '.$tmpaydb.' SET amount= '.intval($_CONFIG['tmpay']['amount'][$amount]).',status= %d WHERE card_id= %d ',$status,$card_result->card_id));
    if($amount > 0)
    {
      echo 'SUCCEED|UID=' . $card_result->user_id . '|TYPE=DONATE';
    }
    else
    {
      echo 'ERROR|AMOUNT_0';
    }
  }
  else
  {
    echo 'ERROR|USED_CARD';
  }

}


    die();
}



}
////////////////////////// XTMPAY JS/TMPAY


if ( is_admin() ){

add_action('admin_menu', 'tmpay_admin_menu');
function tmpay_admin_menu() {
add_menu_page('XTMPAY Setting', 'XTMPAY', 'administrator','tmpay-setting', 'tmpay_admin_html_page', 'dashicons-store','90');
add_submenu_page('tmpay-setting','TrueMoney','TrueMoney','administrator','tmpay-truemoney','tmpay_card_html_page'); 

}


add_filter('plugin_action_links', 'tmpay_plugin_action_links', 10, 2);
function tmpay_plugin_action_links($links, $file) {
    static $this_plugin;
    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=tmpay-setting">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

}

add_action( 'admin_bar_menu', 'tmpay_menu_bar', 999 );
function tmpay_menu_bar($wp_admin_bar) {
   global $wpdb;
   global $wp_admin_bar;



    $wp_admin_bar->add_node(array(
    'id'    => 'tm-user-info',
    'title' => '[+] Donate ',
    'href'  => site_url().'/truemoney'
    ));
     if ( current_user_can( 'manage_options' ) ) {
    $wp_admin_bar->add_node(array(
    'id'    => 'tm-setting',
    'title' => __('<img src="'.TMPAY__PLUGIN_URL.'/images/setting.png" style="vertical-align:middle;margin-right:5px;margin-top:-3px;margin-left:-5px;" alt="tmpay setting" title="tmpay setting" /> ตั้งค่า XTMPAY'),
    'href'  => admin_url('admin.php?page=tmpay-setting')
    ));
     }

  }



function user_vip_fields( $user ) {
  if ( current_user_can( 'manage_options') ){
    $user_vip = get_the_author_meta( 'xvip', $user->ID );
    ?>
    <table class="form-table">
        <tr>
            <th>สมาชิก VIP:</th>
            <td>
            <p><label for="uservip">
                <input
                    id="uservip"
                    name="uservip"
                    type="checkbox"
                    value="yes"
                    <?php if ( $user_vip=="yes" ) echo ' checked="checked"'; ?> /> (ติกเครื่องหมายถูก คือเป็นสมาชิก VIP)
            </label></p>
            </td>
        </tr>
    </table>
    <?php
}
}
add_action( 'show_user_profile', 'user_vip_fields' );
add_action( 'edit_user_profile', 'user_vip_fields' );

    function user_vip_fields_save( $user_id ) {

    if ( current_user_can( 'manage_options') ){


        if(sanitize_text_field($_POST['uservip'])=="yes"){
        $vipopt="yes";
        }else{
        $vipopt="no";
        }
        update_user_meta( $user_id, 'xvip', $vipopt );
      }
    }
    add_action( 'personal_options_update', 'user_vip_fields_save' );
    add_action( 'edit_user_profile_update','user_vip_fields_save' );




function xtmpay_add_user_vip_column( $columns ) {

 $columns['vip'] = __( 'สมาชิก VIP', 'theme' );
 return $columns;

}
add_filter( 'manage_users_columns', 'xtmpay_add_user_vip_column' );

function xtmpay_show_user_vip_data( $value, $column_name, $user_id ) {

 if( 'vip' == $column_name ) {
   return get_user_meta( $user_id, 'xvip', true );
 }

}
add_action( 'manage_users_custom_column', 'xtmpay_show_user_vip_data', 10, 3 );



function vip_shortcode($atts, $content = null) {
$xtmvipuser=get_user_meta( get_current_user_id(), 'xvip', true );
if($xtmvipuser=="yes"){
return $content;
}else{
return '<div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px;
      border: 1px solid #dedede;  padding: 25px 50px; margin: 20px 20px;">
        <div>
      <h1>สำหรับสมาชิก VIP เท่านั้น!</h1>
      <p >กรุณาสมัคร <a href="/truemoney">สมาชิก VIP</a> <br>
    
      </p><br><br>
      <a href="."><input type="submit" name="button" id="button" value="กลับหน้าหลัก" /></a>
      </div>
      </div>';
}


}
add_shortcode('vip', 'vip_shortcode');





function tmpay_shortcode($atts, $content = null) {
extract(shortcode_atts(array(
'id' => '',
'type' => '0'
), $atts));

$tmpay_user_content='';
$sessionrefill=sanitize_text_field($_SESSION['can_refill']);
$truemoney_pwd=sanitize_text_field($_POST['truemoney_password']);


if ( is_user_logged_in() ) {
if(isset($_POST['truemoney_password']) && $sessionrefill == true)
  {
    sleep(3);

    $_SESSION['can_refill'] = false;
    if (misc_parsestring($truemoney_pwd,'0123456789') == FALSE || strlen($truemoney_pwd) != 14)
    {
      $tmpay_user_content='
      <meta http-equiv="refresh" content="5;URL=?' . mt_rand() . '">
      <div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px; border: 1px solid #dedede; padding: 25px 50px; margin: 20px 20px;"><p style="margin-bottom: 0px;"><span class="dashicons dashicons-no"></span> รหัสบัตรเงินสดที่ระบุมีรูปแบบที่ไม่ถูกต้อง<br />
          <span class="dashicons dashicons-backup"></span> (กรุณารอสักครู่ ...)</p></div>';
    }
    else if (refill_countcards($truemoney_pwd) >= 1)
    {
      $tmpay_user_content='
      <meta http-equiv="refresh" content="5;URL=?' . mt_rand() . '">
      <div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px; border: 1px solid #dedede; padding: 25px 50px; margin: 20px 20px;"><p style="margin-bottom: 0px;"><span class="dashicons dashicons-no"></span> รหัสบัตรเงินสดที่ระบุมีถูกใช้งานไปแล้ว<br />
          <span class="dashicons dashicons-backup"></span> (กรุณารอสักครู่ ...)</p></div>';
    }
    else
    {
      if(($tmpay_ret = refill_sendcard(get_current_user_id(),$truemoney_pwd)) !== TRUE)
      {
        $tmpay_user_content='
        <meta http-equiv="refresh" content="5;URL=?' . mt_rand() . '">
        <div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px; border: 1px solid #dedede; padding: 25px 50px; margin: 20px 20px;"><p style="margin-bottom: 0px;"><span class="dashicons dashicons-no"></span> ขออภัย ขณะนี้ระบบ ขัดข้อง กรุณาติดต่อ Admin (' . $tmpay_ret . ')<br />
          <span class="dashicons dashicons-backup"></span> (กรุณารอสักครู่ ...)</p></div>';
      }
      else
      { 
        $tmpay_user_content='
        <meta http-equiv="refresh" content="5;URL=?' . mt_rand() . '">
        <div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px; border: 1px solid #dedede; padding: 25px 50px; margin: 20px 20px;"><p style="margin-bottom: 0px;"><span class="dashicons dashicons-yes"></span> ได้รับข้อมูลบัตรเงินสดเรียบร้อย กรุณารอการตรวจสอบจากระบบ<br />
          <span class="dashicons dashicons-backup"></span> (กรุณารอสักครู่ ...)</p></div>';
      }
    }


  }
  else
  { 
    $_SESSION['can_refill'] = true;
    $cards = refill_getcards(get_current_user_id(),5);

    $tmpay_user_content.='
     <form id="form1" name="form1" method="post" action="">
      <table border="0" align="center" cellpadding="5" cellspacing="2" width="100%">
      <tr>
        <td align="right">รหัสบัตรเงินสด</td>
        <td align="left"><input name="truemoney_password" type="text" id="truemoney_password" size="20" maxlength="14" /></td>
      </tr>
      <tr>
        <td colspan="2" align="left" class="text_mini">
<br><div class="content">'. html_entity_decode(stripslashes(nl2br(tmpay('xtmpay_noticetext')))).'</div>
<br>
</td>
      </tr>
      <tr>
        <td colspan="2" align="center" style="text-align:center;"><input type="submit" name="button" id="button" value="Refill" /></td>
      </tr>
      </table>
    </form>';
        if(!empty($cards))
    { 
      $tmpay_user_content.='
      <table border="0" align="center" cellpadding="5" cellspacing="2" width="100%">
        <tr>
        <td align="center" bgcolor="#333333"><strong><font color="#ffffff">รหัสบัตรเงินสด</font></strong></td>
        <td align="center" bgcolor="#333333"><strong><font color="#ffffff">มูลค่า</font></strong></td>
        <td align="center" bgcolor="#333333"><strong><font color="#ffffff">สถานะ</font></strong></td>
        <td align="center" bgcolor="#333333"><strong><font color="#ffffff">เวลาที่เพิ่มเข้าระบบ</font></strong></td>
        </tr>';
       foreach($cards as $val)
      { 
        $tmpay_user_content.='
          <tr>
          <td align="center">' . $val->password . '</td>
          <td align="center">' . $val->amount. ' บาท</td>
          <td align="center">' . card_status($val->status) . '</td>
          <td align="center">' . $val->added_time . '</td>
          </tr>';
      }
      $tmpay_user_content.='
       </table>';
    }
  }

}else{
$tmpay_user_content='<div  style="border-radius: 20px 20px 20px 20px;
      -moz-border-radius: 20px 20px 20px 20px;
      -webkit-border-radius: 20px 20px 20px 20px;
      border: 1px solid #dedede;  padding: 25px 50px; margin: 20px 20px;">
        <div>
      <h1>กรุณเข้าสู่ระบบ!</h1>
      <p >กรุณาสมัครสมาชิก หรือ เข้าสู่ระบบเพื่อทำรายการ<br>
    
      </p><br><br>
      <a href="."><input type="submit" name="button" id="button" value="กลับหน้าหลัก" /></a>
      </div>
      </div>';
}

return  $tmpay_user_content;
}
add_shortcode('xtmpay', 'tmpay_shortcode');


$tmpay_option = get_option('xtmpay_merchant_id');
if($tmpay_option == ''){
function tmpay_admin_notice(){
	?>
	<div class="error">
		<p> <?php _e('<h1>คำเตือน!</h1> กรุณาตั้งค่า Merchant ID สำหรับ <a href="admin.php?page=tmpay-setting" ><strong>XTMPAY Gateway</strong></a> plugin','XTMPAY_TEXTDOMAIN'); ?></p>
	</div>
	<?php
}
add_action('admin_notices', 'tmpay_admin_notice');
}


function tmpay_admin_html_page() {
    include TMPAY__PLUGIN_DIR . '/admin/admin_setting.php';
}

function tmpay_card_html_page() {
    include TMPAY__PLUGIN_DIR . '/admin/admin_truemoney.php';
}



?>