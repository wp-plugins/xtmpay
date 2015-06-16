<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


    global $wpdb;
    $tmpaydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
    $cards = array();

if(isset($_GET['del_card'])){
$del_card=intval($_GET['del_card']);
}else{
  $del_card='';
}
if(!empty($del_card)){
    if($wpdb->query( $wpdb->prepare(" DELETE FROM $tmpaydb WHERE card_id = %d ",$del_card ) )){
    }
}

    $card_all = $wpdb->get_results('SELECT * FROM '.$tmpaydb);
    $rowcount = $wpdb->num_rows;


$Per_Page = 50;

if(isset($_GET['Page'])){
$getpnum=intval($_GET['Page']);
}else{
  $getpnum='';
}
if(!empty($getpnum))
{
 $Page = $getpnum;
}else{
$Page=1;
}

$Prev_Page = $Page-1;
$Next_Page = $Page+1;
$Page_Start = (($Per_Page*$Page)-$Per_Page);
if($rowcount<=$Per_Page)
{
    $Num_Pages =1;
}
else if(($rowcount % $Per_Page)==0)
{
    $Num_Pages =($rowcount/$Per_Page) ;
}
else
{
    $Num_Pages =($rowcount/$Per_Page)+1;
    $Num_Pages = (int)$Num_Pages;
}

    $cards =  $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$tmpaydb.' ORDER BY card_id DESC LIMIT %d , %d ',$Page_Start,$Per_Page));

	?>
    <style type="text/css">
.truemoney tbody tr:hover td{
  background-color: #FFFAF0; color: #000;
}

.tm_title:hover {
  background-color: #333333; color: #ffffff;
}

.paginate.pag { text-align: center; }
 
.paginate.pag li { font-weight: bold;}
 
.paginate.pag li a {
  display: block;
  float: left;
  color: #717171;
  background: #e9e9e9;
  text-decoration: none;
  padding: 5px 7px;
  margin-right: 6px;
  border-radius: 3px;
  border: solid 1px #c0c0c0;
  box-shadow: inset 0px 1px 0px rgba(255,255,255, .7), 0px 1px 3px rgba(0,0,0, .1);
  text-shadow: 1px 1px 0px rgba(255,255,255, 0.7);
}
.paginate.pag li a:hover {
  background: #eee;
  color: #555;
}
.paginate.pag li a:active {
  -webkit-box-shadow: inset -1px 2px 5px rgba(0,0,0,0.25);
  -moz-box-shadow: inset -1px 2px 5px rgba(0,0,0,0.25);
  box-shadow: inset -1px 2px 5px rgba(0,0,0,0.25);
}
 
.paginate.pag li.single, .paginate.pag li.current {
  display: block;
  float: left;
  border: solid 1px #c0c0c0;
  padding: 5px 7px;
  margin-right: 6px;
  border-radius: 3px;
  color: #444;
}
li {
  margin-bottom: 0px;
}

</style>
<div class="wrap">
<div class="postbox" >

<h2 class="hndle" style="padding: 10px 12px;display: block;font-weight: 600;"><span>TMPAY | TrueMoney</span> </h2>
<div class="inside" >
<p>
<div>

<?php 

if(!empty($cards))
        {
            echo '
            <table width="100%" border="0" align="center" cellpadding="5" cellspacing="2" class="truemoney">
            <thead>
              <tr><td width="3%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">ID</font></strong></td>
                <td width="10%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">รหัสบัตรเงินสด</font></strong></td>
                <td width="4%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">ลบ</font></strong></td>
                <td width="8%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">Username</font></strong></td>
                <td width="12%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">มูลค่า</font></strong></td>
                <td width="11%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">สถานะ</font></strong></td>
                <td width="10%" align="center" bgcolor="#333333" class="tm_title"><strong><font color="#ffffff">เวลาที่เพิ่มเข้าระบบ</font></strong></td>
              </tr></thead>
    <tbody>';
           //echo "ffff";
$i=0;
             foreach($cards as $val)
            {
$user_info = get_userdata($val->user_id);


$i++;
if($i%2==0)
{
$bg = "#E9E9E9";
}
else
{
$bg = "#FFFFFF";
}

                echo '
                  <tr >
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">' . $val->card_id . '</td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">' . $val->password . '</td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">[ <a href="admin.php?page=tmpay-truemoney&del_card=' . $val->card_id . '" onclick="return confirm(\'Delete Card: ' . $val->password . ' ?\');">del</a> ]</td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">#'.$val->user_id.' <a href="user-edit.php?user_id='.$val->user_id.'">' . $user_info->user_login . '</a></td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">' . number_format($val->amount). ' บาท</td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">' . card_status($val->status) . '</td>
                    <td align="center" bgcolor="'.$bg .'" class="tmtd">' . $val->added_time . '</td>
                  </tr>';
            }
            echo '
             </tbody></table>';
             ?>
<hr>
<div class="pagenv" style="height: 30px; padding-top: 5px; padding-right: 0px; padding-left: 0px; margin-right: auto; margin-left: auto;">

&nbsp;&nbsp;Total <?php echo $rowcount;?> Record 
<ul class="paginate pag clearfix">
<?php
if($rowcount>$Per_Page){
$pages = new Paginator;
$pages->items_total = $rowcount;
$pages->mid_range = 10;
$pages->current_page = $Page;
$pages->default_ipp = $Per_Page;
$pages->url_next = $_SERVER["PHP_SELF"]."?page=tmpay-truemoney&Page=";

$pages->paginate();

echo $pages->display_pages();
}

?>      
</ul>
</div>
<br><br><br>
<?php

}else{ ?>
<br><br><br>
<div style="text-align:center"><h1>ยังไม่มีข้อมูล</h1></div>
<?php } ?>

</div>
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