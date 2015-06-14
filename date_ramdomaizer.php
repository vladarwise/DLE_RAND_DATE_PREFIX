<?php

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}
if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

if ( file_exists( ROOT_DIR . '/language/' . $selected_language . '/adminlogs.lng' ) ) {
	require_once (ROOT_DIR . '/language/' . $selected_language . '/adminlogs.lng');
}

$row = $db->super_query("SELECT SQL_CALC_FOUND_ROWS * FROM " . PREFIX . "_admin_sections WHERE name = 'date_ramdomaizer' LIMIT 1");
if (is_null($row)) {
	$db->super_query("INSERT INTO `" . PREFIX . "_admin_sections` (`name`, `title`, `descr`, `icon`, `allow_groups`) VALUES ('date_ramdomaizer', 'Рандомизация дат публикаций', 'Модуль позволяет рандомизировать дату публикаций на сайте', 'refresh.png', '1');");
}



if($_POST['randomize_post']=="rand"){
	$date_after = strtotime($_REQUEST['date_after']);
	$date_before = strtotime($_REQUEST['date_before']);
	
	$allow_reiting = $_REQUEST['allow_reiting'];
	if($allow_reiting == 1){
		$db->query( "TRUNCATE TABLE ". USERPREFIX ."_post_extras ;" );	
	}
	
	$id_news_plus = $_REQUEST['id_news_plus'];
	
	
	$prefix_before = $_REQUEST['prefix_before'];
	$prefix_before_mas = explode(" ", $prefix_before);
	
	$prefix_after = $_REQUEST['prefix_after'];
	$prefix_after_mas = explode(" ", $prefix_after);
	
	$id_news_title = array();
	$db->query( "SELECT id,alt_name FROM " . USERPREFIX . "_post ORDER BY id DESC" );
	while ($row = $db->get_row()) {
		$id_news_title[] = array($row[id],$row[alt_name]);
	}
	$log_text = "\t\t\t\t\t\t LOG OF ACTION ".count($id_news_title)." NEWS UPDATE\n";
	$log_text.= "ID\t\t\t\t";
	$log_text.= "ALT_NAME\t\t\t\t\t\t\t\t\t";
	$log_text.= "DATE\n";
	foreach($id_news_title as $val){
		$r_date = mt_rand($date_after,$date_before);
		$thistime = date( "Y-m-d H:i:s", $r_date );
		
		$rand_keys_before = array_rand($prefix_before_mas);
		$rand_keys_after = array_rand($prefix_after_mas);
		
		$val[1] = $prefix_before_mas[$rand_keys_before].$val[1].$prefix_after_mas[$rand_keys_after];
		$val[1] = str_replace(" ","",$val[1]);
		$id_news_finish = $val[0];
		if($id_news_plus>0){
			$id_news_finish = $val[0]+$id_news_plus;
			$db->query( "UPDATE  " . USERPREFIX . "_post SET  `date` =  '".$thistime."', alt_name = '".$val[1]."', id = '".$id_news_finish."' WHERE  `id` =". $val[0] .";" );
		}else{
			$db->query( "UPDATE  " . USERPREFIX . "_post SET  `date` =  '".$thistime."', alt_name = '". $val[1] ."' WHERE  `id` =". $val[0] .";" );
		}
		
		if($allow_reiting == 1){
			$db->query( "INSERT INTO ".USERPREFIX."_post_extras (`eid`, `news_id`, `news_read`, `allow_rate`, `rating`, `vote_num`, `votes`, `view_edit`, `disable_index`, `related_ids`, `access`, `editdate`, `editor`, `reason`, `user_id`) VALUES (NULL, '".$id_news_finish."', '0', '1', '0', '0', '0', '0', '0', '', '', '0', '', '', '0');" );	
		}
		$log_text.= $val[0]."\t\t\t\t";
		$log_text.= $val[1]."\t\t\t\t\t\t\t\t\t";
		$log_text.= $thistime."\n";
	}
	
	

}
$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );
$news_in_db = $row['count'];

$newdate = $_POST['newdate1'];
$newsdate = strtotime($newdate);
//$thistime = date( "Y-m-d H:i:s", $newsdate );


$msg = <<<HTML
	<form action="$PHP_SELF?mod=date_ramdomaizer" method="post" class="form-horizontal">
		<div width="100%" class="text-left">
			<div class="form-group">
			  <label class="control-label col-lg-2">Новостей в базе {$rand_keys[0]}</label>
			  <div class="col-lg-10"> {$news_in_db} </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2">Даты от</label>
			  <div class="col-lg-10">
				<input data-rel="calendar" type="text" name="date_after" size="20" value="{$_POST['date_after']}"> до <input data-rel="calendar" type="text" name="date_before" size="20" value="{$_POST['date_before']}"> 
			  </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2">Включить рейтинг для новостей</label>
			  <div class="col-lg-10">
				<input style="position: absolute; opacity: 0;" class="icheck" name="allow_reiting" value="1" checked="" type="checkbox">
			  </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2">Смена ID новостей</label>
			  <div class="col-lg-10">
				+ <input type="text" name="id_news_plus" size="18" value="{$_POST['id_news_plus']}"> к ID
			  </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2">Префикс до</label>
			  <div class="col-lg-10">
				<textarea rows="4" style="width:100%;" name="prefix_before">{$_POST['prefix_before']}</textarea>
			  </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2">Префикс после</label>
			  <div class="col-lg-10">
				<textarea rows="4" style="width:100%;" name="prefix_after">{$_POST['prefix_after']}</textarea>
			  </div>
			</div>
			<div class="form-group">
			  <label class="control-label col-lg-2"></label>
			  <div class="col-lg-10">
				<input type="submit" value="Начать" class="btn btn-green">
			  </div>
			</div>
			
			<input type="hidden" name="randomize_post" value="rand">
			<br><br>
			<div class="form-group">
			  <textarea rows="10" cols="45" style="width:95%;margin-left:2.5%;margin-right:2.5%;" name="log">{$log_text}</textarea>
			  
			</div>
		</div>
	</form>
HTML;
			
// Messages
msg( "wordfilter1", "Рандомизация дат публикаций", $msg );




echofooter();
?>