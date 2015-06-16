<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//Kill magic quotes.  Can't unserialize POST variable otherwise
if ( get_magic_quotes_gpc() ) {
    $process = array( &$_GET, &$_POST, &$_COOKIE, &$_REQUEST );
    while ( list($key, $val) = each( $process ) ) {
        foreach ( $val as $k => $v ) {
            unset( $process[$key][$k] );
            if ( is_array( $v ) ) {
                $process[$key][stripslashes( $k )] = $v;
                $process[] = &$process[$key][stripslashes( $k )];
            } else {
                $process[$key][stripslashes( $k )] = stripslashes( $v );
            }
        }
    }
    unset( $process );
}


function tmpay($option)
{
	global $wpdb;
	$tmpaydbst = $wpdb->prefix . TMPAY::TABLE_SETTING;
	$tmpayoption =  $wpdb->get_row($wpdb->prepare( 'SELECT * FROM '.$tmpaydbst.' WHERE tmcode = %s ',$option));
	if(!empty($tmpayoption->tmvalue)){
	return $tmpayoption->tmvalue;
    }else{
    	return false;
    }
}

function tmpay_insert($option,$value)
{
	global $wpdb;
	$tmpaydbst = $wpdb->prefix . TMPAY::TABLE_SETTING;
	if($wpdb->query($wpdb->prepare( 'INSERT INTO '.$tmpaydbst.' (tmcode,tmvalue) VALUES (%s, %s )',$option,$value ))){
	return TRUE;
	}
}

function tmpay_update($option,$value)
{
	global $wpdb;
	$tmpaydbst = $wpdb->prefix . TMPAY::TABLE_SETTING;
	if($wpdb->query($wpdb->prepare( 'UPDATE '.$tmpaydbst.' SET tmvalue =  %s WHERE tmcode = %s ',$value,$option))){
	return TRUE;
	}
}

///////////////////////////////////////////////////////

function truemoney_ajax_update($name,$value,$pk)
{
	global $wpdb;
	$truemoneydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
	if($wpdb->query($wpdb->prepare( 'UPDATE '.$truemoneydb.' SET '.$name.' =  %s WHERE card_id = %d ',$value,$pk))){
	return  array("status" => "ok","pk" => $pk,"name" => $name,"value" => $value);
	}
}



function card_status($cstatus=0){
switch($cstatus)
			{
	            case 0:
					$cardststus=TMPAY::STATUS_0;
				break;
				case 1:
					$cardststus=TMPAY::STATUS_1;
				break;
				case 2:
					$cardststus=TMPAY::STATUS_2;
				break;
				case 3:
					$cardststus=TMPAY::STATUS_3;
				break;
				case 4:
					$cardststus=TMPAY::STATUS_4;
				break;
				case 5:
					$cardststus=TMPAY::STATUS_5;
				break;
			}
			return $cardststus;
}

function misc_parsestring($text,$allowchr='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
	if(empty($allowchr))
		$allowchr = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	if(empty($text)) return FALSE;
	$size = strlen($text);
	for($i=0; $i < $size; $i++) {
		$tmpchr = substr($text, $i , 1);
		if(strpos($allowchr,$tmpchr) === FALSE) 
			return FALSE;
	}
	return TRUE;
}

function refill_countcards($queryxx)
{
	global $wpdb;
	$tmpaydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
	$result = 'SELECT COUNT( * ) FROM '.$tmpaydb.' WHERE password = %s';
	$row = $wpdb->get_var($wpdb->prepare( $result,$queryxx));
    return $row[0];

}

function refill_getcards($userid,$count=20)
{
	global $wpdb;
	$tmpaydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
	$cards = array();
	$cards =  $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.$tmpaydb.' WHERE user_id = %d ORDER BY card_id DESC LIMIT %d',$userid,$count));

	return $cards;
}

function refill_sendcard($user_id,$password)
{
	global $_CONFIG;		
	global $wpdb;

    //$tmp_redirect_url=TMPAY__PLUGIN_URL."cc-tmpay.php";
    $tmp_redirect_url=site_url()."/xtm-chk/";
	$curl = curl_init('https://www.tmpay.net/TPG/backend.php?merchant_id=' .get_option('xtmpay_merchant_id') . '&password=' . $password . '&resp_url=' . $tmp_redirect_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $curl_content = curl_exec($curl);
		if($curl_content === false)
		{
			return curl_error($curl);
		}
	if(strpos($curl_content,'SUCCEED') !== FALSE)
	{

		$tmpaydb = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;
		$wpdb->query($wpdb->prepare( 'INSERT INTO '.$tmpaydb.' (card_id,password,user_id,amount,status,added_time) VALUES (NULL,%s,%d,0,0,NOW())',$password,$user_id));
		return TRUE;

	}
	else return $curl_content;
}


class Paginator{
	var $items_per_page;
	var $items_total;
	var $current_page;
	var $num_pages;
	var $mid_range;
	var $low;
	var $high;
	var $limit;
	var $return;
	var $default_ipp;
	var $querystring;
	var $url_next;

	function Paginator()
	{
		$this->current_page = 1;
		$this->mid_range = 7;
		$this->items_per_page = $this->default_ipp;
		$this->url_next = $this->url_next;
	}
	function paginate()
	{

		if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) $this->items_per_page = $this->default_ipp;
		$this->num_pages = ceil($this->items_total/$this->items_per_page);

		if($this->current_page < 1 Or !is_numeric($this->current_page)) $this->current_page = 1;
		if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;
		$prev_page = $this->current_page-1;
		$next_page = $this->current_page+1;


		if($this->num_pages > 10)
		{
			$this->return = ($this->current_page != 1 And $this->items_total >= 10) ? "<li><a href=\"".$this->url_next.$this->$prev_page."\">&laquo; Previous</a> </li>":"<li><a href=\"#\">&laquo; Previous</a> </li>";

			$this->start_range = $this->current_page - floor($this->mid_range/2);
			$this->end_range = $this->current_page + floor($this->mid_range/2);

			if($this->start_range <= 0)
			{
				$this->end_range += abs($this->start_range)+1;
				$this->start_range = 1;
			}
			if($this->end_range > $this->num_pages)
			{
				$this->start_range -= $this->end_range-$this->num_pages;
				$this->end_range = $this->num_pages;
			}
			$this->range = range($this->start_range,$this->end_range);

			for($i=1;$i<=$this->num_pages;$i++)
			{
				if($this->range[0] > 2 And $i == $this->range[0]) $this->return .= " ... ";
				if($i==1 Or $i==$this->num_pages Or in_array($i,$this->range))
				{
					$this->return .= ($i == $this->current_page And $_GET['Page'] != 'All') ? "<li class=\"current\" >$i</li> ":"<li><a title=\"Go to page $i of $this->num_pages\" href=\"".$this->url_next.$i."\">$i</a> </li>";
				}
				if($this->range[$this->mid_range-1] < $this->num_pages-1 And $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
			}
			$this->return .= (($this->current_page != $this->num_pages And $this->items_total >= 10) And ($_GET['Page'] != 'All')) ? "<li><a href=\"".$this->url_next.$next_page."\">Next &raquo;</a></li>\n":"<li><a href=\"#\">&raquo; Next</a></li>\n";
		}
		else
		{
			for($i=1;$i<=$this->num_pages;$i++)
			{
				$this->return .= ($i == $this->current_page) ? "<li class=\"current\">$i</li>":"<li><a href=\"".$this->url_next.$i."\">$i</a> </li>";
			}
		}
		$this->low = ($this->current_page-1) * $this->items_per_page;
		$this->high = ($_GET['ipp'] == 'All') ? $this->items_total:($this->current_page * $this->items_per_page)-1;
		$this->limit = ($_GET['ipp'] == 'All') ? "":" LIMIT $this->low,$this->items_per_page";
	}

	function display_pages()
	{
		return $this->return;
	}
}




?>