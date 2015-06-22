<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
	define("XTMPAY_TEXTDOMAIN","XTMPAY");

	class TMPAY{

		const SHOW_DEBUG = false;
		const NAME = 'XTMPAY GATEWAY';
		const VERSION = '1.1';
		const DB_VERSION = '1.00';
		const SERVERIP = "203.146.127.112";
		const APIURL = "http://api.iamzer.com";
		const TABLE_TRUEMONEY = "xtmpay_truemoney";
		const TABLE_SETTING = "xtmpay_setting";

		
		const STATUS_0 = 'รอการตรวจสอบ';
	    const STATUS_1 = '<font color="green">การเติมเงินสำเร็จ</font>';
		const STATUS_3 = '<font color="red">บัตรเงินสดถูกใช้ไปแล้ว</font>';
		const STATUS_4 = '<font color="red">รหัสบัตรเงินสดไม่ถูกต้อง</font>';
		const STATUS_5 = '<font color="red">เป็นบัตร Truemove (ไม่ใช่บัตร TrueMoney)</font>';


	}

   define( 'TMPAY_CREDIT', '<br><strong>'.TMPAY::NAME.' '.TMPAY::VERSION.'</strong> &nbsp; by <a href="http://www.iamzer.com/" target="_blank" ><strong> Alongkorn Khaoto</strong></a> | iamzer@live.com ( ผู้พัฒนาไม่มีส่วนเกี่ยวข้องกับ TMPAY.NET )<br>
<a href="https://www.tmpay.net/" target="_blank" > <strong>TMPAY.NET</strong> </a> เป็นผู้ให้บริการช่วยตรวจสอบและตัดยอดเงินจากบัตรเงินสดทรูมันนี่ ไม่มีส่วนเกี่ยวข้องกับ บริษัท ทรูมันนี่ จำกัด แต่อย่างใด.' );

// trurmove card price (do not edit)
$_CONFIG['tmpay']['amount'][0] = 0;
$_CONFIG['tmpay']['amount'][1] = 50;
$_CONFIG['tmpay']['amount'][2] = 90;
$_CONFIG['tmpay']['amount'][3] = 150;
$_CONFIG['tmpay']['amount'][4] = 300;
$_CONFIG['tmpay']['amount'][5] = 500;
$_CONFIG['tmpay']['amount'][6] = 1000;


?>