<?php
require_once('../../includes/classes/core.php');
include('../../includes/classes/log.class.php');


$log 		= new log();
$action		= $_REQUEST['act'];
$error		= '';
$data		= '';



$rand_file	= $_REQUEST['rand_file'];
$file		= $_REQUEST['file_name'];


switch ($action) {
	case 'get_add_page':
		$page		= GetPage();
		$data		= array('page'	=> $page);

		break;
	case 'get_edit_page':
		$source_id	= $_REQUEST['id'];
		$page		= GetPage(Getsource($source_id));
		$data		= array('page'	=> $page);

		break;
	case 'get_list' :
		$count	= $_REQUEST['count'];
		$hidden	= $_REQUEST['hidden'];
			
		$rResult = mysql_query("SELECT 	mail.id,
										mail.`subject`
								FROM 	mail
							    WHERE 	mail.actived=1");

		$data = array(
				"aaData"	=> array()
		);

		while ( $aRow = mysql_fetch_array( $rResult ) )
		{
			$row = array();
			for ( $i = 0 ; $i < $count ; $i++ )
			{
				/* General output */
				$row[] = $aRow[$i];
				if($i == ($count - 1)){
					$row[] = '<div class="callapp_checkbox">
                                  <input type="checkbox" id="callapp_checkbox_'.$aRow[$hidden].'" name="check_'.$aRow[$hidden].'" value="'.$aRow[$hidden].'" class="check" />
                                  <label for="callapp_checkbox_'.$aRow[$hidden].'"></label>
                              </div>';
				}
			}
			$data['aaData'][] = $row;
		}

		break;
	case 'save_source':
		$source_id 	 = $_REQUEST['id'];
		$source_name = $_REQUEST['SUBJECT'];
		$content   	 = $_REQUEST['content'];



		if($source_name != ''){
			if ($source_id == '') {
					if(!ChecksourceExist($source_name, $source_id)){
						Addsource($source_name,$content);
					} else {
						$error = '"' . $source_name . '" უკვე არის სიაში!';
					}
			}else {
				Savesource($source_id, $source_name,$content);
			}
		}
		
		break;
	case 'disable':
		$source_id	= $_REQUEST['id'];
		Disablesource($source_id);

		break;
	case 'delete_file':
		$delete_id = $_REQUEST['delete_id'];
		$edit_id   = $_REQUEST['edit_id'];
		
		
		$increm = mysql_query("UPDATE `file`
								 SET `actived`='0'
							   WHERE `name`='$edit_id' OR file.id = $delete_id");
		
		$increm = mysql_query("SELECT  	file.`name`,
										file.`rand_name`,
										file.`id`
								FROM 	`mail_detail`
								LEFT JOIN file ON mail_detail.file_id=file.id
								WHERE   mail_detail.`mail_id` = 1 AND file.actived=$edit_id");
		
		
				$data1 = '';
	
		while($increm_row = mysql_fetch_assoc($increm)){
			  $data1 .='<tr style="border-bottom: 1px solid #85b1de;">
					         <td style="width:110px; display:block;word-wrap:break-word;">'.$increm_row['name'].'</td>													 
					         <td style=" width: 18px;"><button type="button" value="media/uploads/file/'.$increm_row['name'].'" style="cursor:pointer; border:none; margin-top:5%; display:block; height:16px;   margin-right: 0px; width:16px; background:none;background-image:url(\'media/images/get.png\');" id="download_name" value="'.$increm_row[rand_name].'"> </td>
					         <td style=" width: 18px;"><button type="button" value="'.$increm_row['id'].'" style="cursor:pointer; border:none; margin-top:5%; display:block; height:16px; width:16px;   margin-right: 0px; background:none; background-image:url(\'media/images/x.png\');" id="delete"></button></td>
				        </tr>';
		}
	
		$data = array('page' => $data1);
	
		break;
	case 'up_now':
		$user     = $_SESSION['USERID'];
		$edit_id1 = $_REQUEST['edit_id'];
		$edit_id2 = increment('mail');
		
		if ($edit_id1==''){$edit_id=$edit_id2;}else{$edit_id = $edit_id1;}
	
		if($rand_file != ''){mysql_query("INSERT INTO 	`file`
											 (`user_id`, `name`, `rand_name`)
											VALUES
											 ('$user', '$file', '$rand_file');");
		
		$file_id = mysql_insert_id();
		
		
								mysql_query("INSERT INTO `mail_detail` 
											 (`user_id`, `mail_id`, `file_id`, `actived`) 
											VALUES 
											 ('$user', '$edit_id', '$file_id', '1')");
		}
		$increm = mysql_query("	SELECT  file.`name`,
										file.`rand_name`,
										file.`id`
								FROM 	`mail_detail`
								LEFT JOIN file ON mail_detail.file_id=file.id
								WHERE   `mail_id` = $edit_id and file.actived");
		$data1 = '';
	
		while($increm_row = mysql_fetch_assoc($increm))	{
				
			$data1 .='	<tr style="border-bottom: 1px solid #85b1de;">
				          <td style="width:110px; display:block;word-wrap:break-word;">'.$increm_row['name'].'</td>													 
				          <td style=" width: 18px;"><button type="button" value="media/uploads/file/'.$increm_row['name'].'" style="cursor:pointer; border:none; margin-top:5%; display:block; height:16px;   margin-right: 0px; width:16px; background:none;background-image:url(\'media/images/get.png\');" id="download_name" value="'.$increm_row[rand_name].'"> </td>
				          <td style=" width: 18px;"><button type="button" value="'.$increm_row['id'].'" style="cursor:pointer; border:none; margin-top:5%; display:block; height:16px; width:16px;   margin-right: 0px; background:none; background-image:url(\'media/images/x.png\');" id="delete"></button></td>
				        </tr>';
		}
		$data = array('page' => $data1);
	
		break;
	default:
		$error = 'Action is Null';
}

$data['error'] = $error;

echo json_encode($data);


/* ******************************
 *	Category Functions
* ******************************
*/

function Addsource($source_name, $content){	
	
	$user    = $_SESSION['USERID'];

	mysql_query("INSERT INTO `mail`
							(`subject`, `body`, `user_id`, `actived`)
						VALUES 		
							('$source_name', '$content', '$user', '1')");

}

function Savesource($source_id, $source_name, $content){
	
	$user_id	= $_SESSION['USERID'];
	mysql_query("	UPDATE `mail`
					SET    `subject` = '$source_name',
						   `body`='$content'
					WHERE  `id` = $source_id");
}

function Disablesource($source_id){
	
	mysql_query("	UPDATE `mail`
					SET    `actived` = 0
					WHERE  `id` = $source_id");
	
}

function ChecksourceExist($source_name){
	
	$res = mysql_fetch_assoc(mysql_query("	SELECT `id`
											FROM   `mail`
											WHERE  `subject` = '$source_name' && `actived` = 1"));
	if($res['id'] != ''){return true;}
	
	
	return false;
}


function Getsource($source_id){
	$res = mysql_fetch_assoc(mysql_query("	SELECT  mail.`id`,
													mail.`subject`,
													mail.`body`
											FROM    `mail`
											WHERE   mail.`id` = $source_id" ));

	return $res;
}

function GetPage($res = ''){
	
	if ($res[id]=='') {$hidde=increment('mail');}else {$hidde=$res[id];}
	
	$increm = mysql_query("	SELECT  file.`name`,
									file.`rand_name`,
									file.`id`
							FROM 	`mail_detail`
							LEFT JOIN file ON mail_detail.file_id=file.id
							WHERE   mail_detail.`mail_id` =  $res[id] AND file.actived=1");
	$data = '
	<div id="dialog-form">
	   		<table class="dialog-form-table">
				<tr>
					<td style="width: 90px;"><label for="d_number">სათაური:</label></td>
					<td>
						<input type="text" style="width: 443px !important;" id="SUBJECT" class="idle address" onblur="this.className=\'idle address\'" onfocus="this.className=\'activeField address\'" value="' . $res['subject'] . '" />
					</td>
				</tr>
			</table>
			<table class="dialog-form-table">
				<tr>
					<td>	
						<textarea id="input" style="width:400px; height:200px">' . $res['body'] . '</textarea>
					</td>
			   </tr>	
			</table>
			<div style="text-align: center;vertical-align: middle;float: left;width: 506px;"><button id="choose_button" style="cursor: pointer;background: none;border: none;width: 100%;height: 25px;padding: 0;margin: 0;">აირჩიეთ ფაილი</button><input style="display:none;" type="file" name="choose_file" id="choose_file"></div>
			<div id="paste_files1">';
        	while ($file_body = mysql_fetch_array($increm)) {
        	    $data .= '<div id="first_div">'.$file_body[0].'</div>
                	        <div id="two_div">'.$file_body[1].'</div>
                	        <div id="tree_div" onclick="download_file(\''.$file_body[2].'\',\''.$file_body[1].'\')">ჩამოტვირთვა</div>
                	            <div id="for_div" onclick="delete_file1(\''.$file_body[2].'\')">-</div>';
        	}
                                   $data .='</div>
			
			<!-- ID -->
	 		<input type="hidden" id="mail_hidde_id" value="' . $hidde . '" />
			<input type="hidden" id="source_id" value="' . $res['id'] . '" />
        </fieldset>
    </div>
    ';
	return $data;
}
function increment($table){

	$result   		= mysql_query("SHOW TABLE STATUS LIKE '$table'");
	$row   			= mysql_fetch_array($result);
	$increment   	= $row['Auto_increment'];
	$next_increment = $increment+1;
	mysql_query("ALTER TABLE '$table' AUTO_INCREMENT=$next_increment");

	return $increment;
}
?>

