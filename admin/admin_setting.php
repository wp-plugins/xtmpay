<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


if(isset($_GET['tab'])){
$tmtab=sanitize_text_field($_GET['tab']);
}else{
  $tmtab='';
}

if(!empty($_POST)){

if(!get_option('xtmpay_merchant_id')){
echo '<meta http-equiv="refresh" content="1;URL=admin.php?page=tmpay-setting">';
}



$tmpay_merchant_id=sanitize_text_field($_POST['xtmpay_merchant_id']);
$tmpay_licensekey=sanitize_text_field($_POST['xtmpay_licensekey']);
$tmpay_noticetext_text=sanitize_text_field($_POST['xtmpay_noticetext']);


update_option('xtmpay_merchant_id', $tmpay_merchant_id);
update_option('xtmpay_licensekey', $tmpay_licensekey);

tmpay_update('xtmpay_noticetext', iconv(mb_detect_encoding($tmpay_noticetext_text, mb_detect_order(), true), "UTF-8", $tmpay_noticetext_text));

}



?>


<style type="text/css">
.help_merchant_id {
    float:right;
    position:relative;
    width: 30px;
    margin-right: 10px;
}
.help_merchant_id:hover .help_image{
    display:block;
}

.help_image {
position:absolute;
	background-image: url('<?php echo plugins_url( 'images/tmpay_merchantid.jpg', __FILE__ ); ?>');
    top:-10px;
    left:-80px;
    width:218px;
    height:344px;
	display: none;
    z-index:999;
	border-style: solid;
    border-width: 1px;
}


.help_licensekey {
    float:right;
    position:relative;
    width: 30px;
    margin-right: 10px;
}
.help_licensekey:hover .help_licensekey_popup{
    display:block;
}
.help_licensekey_popup {
	background-color: #FFFFFF;
	position: absolute;
	top: -10px;
	left: -80px;
	width: 218px;
	height: 350px;
	display: none;
	z-index: 999;
	border-style: solid;
	border-width: 1px;
	padding-top: 5px;
	padding-right: 5px;
	padding-left: 5px;
	padding-bottom: 5px;
	font-size: 11px;
}

.tmlicense_validation p{
	margin-top: auto;
	margin-bottom: auto;
}
<?php if(get_option('xtmlicensekey_type')=="pro"){ ?>
.tmlicense_validation {
  background: #F4EFCC;
  padding: 0 10px;
  color: #787D48;
  display: block;
  border: 1px solid #e5e5e5;
}
<?php }else if(get_option('xtmlicensekey_type')){ ?>
.tmlicense_validation {
  background: #E5E5E5;
  padding: 0 10px;
  color: #75BE9F;
  display: block;
  border: 1px solid #e5e5e5;
}
<?php }else{ ?>
.tmlicense_validation {
  display: none;
}
<?php }?>

.truemoney{
	font-size:11px;
	}
.truemoney_red{
		color: #FF0004;
	font-weight: bold;
}
</style>
<div class="wrap">
<div class="postbox" >

<h2 class="hndle" style="padding: 10px 12px;display: block;font-weight: 600;"><span>TMPAY | Setting</span> </h2>
<div class="inside truemoney">

<ul class="nav nav-tabs">
  <li role="presentation" <?php if(empty($tmtab) OR $tmtab=="setting"){echo 'class="active"'; } ?>><a href="admin.php?page=tmpay-setting&tab=setting">General Setting</a></li>
    <li role="presentation" <?php if(isset($tmtab) && ($tmtab=="about")){echo 'class="active"'; } ?>><a href="admin.php?page=tmpay-setting&tab=about">About</a></li>
</ul>
<br><br>
<p>
<?php if(empty($tmtab) OR $tmtab=="setting"){ ?>
<form method="post" action="">
<?php wp_nonce_field('update-options'); ?>

<table width="600" height="957">
<tr valign="top">
<th colspan="3" valign="middle" scope="row"><img src="<?php echo plugins_url( 'images/truemoney.png', __FILE__ ); ?> "> Merchant ID (รหัสร้านค้า)&nbsp;&nbsp;&nbsp;&nbsp;
<input name="xtmpay_merchant_id" type="text" id="xtmpay_merchant_id"
value="<?php echo get_option('xtmpay_merchant_id'); ?>" />
  (ex.  ML190XXXXX) <span class="help_merchant_id"><img src="<?php echo plugins_url( 'images/help.png', __FILE__ ); ?>" ><div class="help_image" ></div></span></th>
</tr>
<tr valign="top">
  <th height="29" colspan="3" valign="middle" scope="row">                   <span class="dashicons dashicons-post-status"></span>License Key      
    <input name="xtmpay_licensekey" type="text" id="xtmpay_licensekey"
value="<?php echo get_option('xtmpay_licensekey'); ?>" /> 
     <a href="http://iamzer.com/wpplugin-license-keys/" target="_blank"><span class="button button-primary" id="validate_btn">GO PRO</span></a>    <span class="help_licensekey"><img src="<?php echo plugins_url( 'images/help.png', __FILE__ ); ?>" >
    <div class="help_licensekey_popup" ><br><h2><strong>TMPAY GATEWAY PRO</strong><br><span class="dashicons dashicons-post-status"></span>License Key </h2><br>สำหรับผู้ที่สนับสนุน plugin จะสามารถอัพเดทได้โดยอัตโนมัติ ซึ่งตัว plugin จะได้รับการพัฒนาอยู่เสมอ ทำให้คุณไม่พลาด Feature ใหม่ๆ ในเวอร์ชั่นใหม่ๆ รวมถึงการแก้ไข Bug ต่างๆจากทางผู้พัฒนา  <br><br>   สามารถลงทะเบียนผ่านระบบจ่ายเงินอัตโนมัติ และรับ License Key มาใส่ได้ทันที <br>โดยคลิกที่นี่ >> [ <a href="http://iamzer.com/wpplugin-license-keys/" target="_blank">get License Key</a> ]</div></span>
    
    </th>
</tr>
<tr valign="top">
  <th height="29" colspan="3" valign="middle" scope="row"><div class="tmlicense_validation" id="tmlicense_validation">
							<p id="lstype">
								<span class="dashicons dashicons-yes"></span> License Valid for TMPAY	GATEWAY	<?php echo strtoupper(get_option('xtmlicensekey_type')); ?>					</p>
						</div></th>
</tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row">สามารถสนับสนุนปลั๊กอิน ด้วยการซื้อ <a href="http://iamzer.com/wpplugin-license-keys/" target="_blank">license key</a> เพื่อเพิ่มความสามารถให้กับ XTMPAY.</th>
  </tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row">&nbsp;</th>
</tr>
<tr valign="top">
  <th height="37" colspan="3" valign="middle" bgcolor="#F1F1F1" scope="row" ><h3><strong>Notice Footer</strong></h3></th>
</tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row">
<div><?php
$editor_id = 'xtmpay_noticetext';
$content = tmpay('xtmpay_noticetext');
wp_editor( html_entity_decode(stripslashes(nl2br($content))), $editor_id );

 ?></div></th>
</tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row" class="truemoney" >*ข้อความแจ้งเตือนหน้า topup / หรือปล่อยว่างไว้หากไม่ต้องการใช้การแจ้งเตือน</th>
</tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row">&nbsp;</th>
</tr>
<tr valign="top">
  <th colspan="3" valign="middle" scope="row">&nbsp;</th>
</tr>
</table>

<p>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="button" type="submit" value="<?php _e('Update') ?>" />
</p>

</form>
<?php }?> 
<?php if($_GET['tab']=="about"){ 
$tmabout = file('../wp-content/plugins/xtmpay/readme.txt');
foreach($tmabout as $value){
    print $value.'<br/>';
}
?>
<?php } ?>

</p>
<style type="text/css">
a:link {
    text-decoration: none;
}
</style>
<div class="credit" style="font-size:small; color:#6F6F6F;">
<?php echo TMPAY_CREDIT; ?>
</div>
</div>
</div>
</div>
<?php



?>