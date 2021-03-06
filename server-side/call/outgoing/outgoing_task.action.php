<?php
// MySQL Connect Link
require_once('../../../includes/classes/core.php');
 
// Main Strings
$action                     = $_REQUEST['act'];
$error                      = '';
$data                       = '';
$user_id	                = $_SESSION['USERID'];

$task_id                = $_REQUEST['id'];
$task_type_id			= $_REQUEST['task_type_id'];
$task_start_date		= $_REQUEST['task_start_date'];
$task_end_date			= $_REQUEST['task_end_date'];
$task_departament_id	= $_REQUEST['task_departament_id'];
$task_recipient_id		= $_REQUEST['task_recipient_id'];
$task_priority_id		= $_REQUEST['task_priority_id'];
$task_controler_id		= $_REQUEST['task_controler_id'];
$task_status_id		    = $_REQUEST['task_status_id'];
$task_description		= $_REQUEST['task_description'];
$task_note			    = $_REQUEST['task_note'];
$task_answer            = $_REQUEST['task_answer'];

switch ($action) {
	case 'get_add_page':
		$page		= GetPage('','');
		$data		= array('page'	=> $page);

		break;
	case 'get_edit_page':
		$page		= GetPage(GetTask($task_id));
		$data		= array('page'	=> $page);

		break;
	case 'get_list':
        $count = 		$_REQUEST['count'];
		$hidden = 		$_REQUEST['hidden'];
		$operator = $_REQUEST['operator'];
		if($operator == 0 || $operator == ''){
		    $operator_fillter = '';
		}else{
		    $operator_fillter = "AND outgoing_campaign_detail.responsible_person_id = $operator";
		}
		

	  	$rResult = mysql_query("SELECT 	`task`.`id`,
                        				`task`.`id`,
                        				`task_start_date`,
                        				`task_end_date`,
                        				`task_type`.`name`,
                        				`department`.`name`,
                        				`recipient_ps`.`name`,
                        				`controler_ps`.`name`,
                        				`former_ps`.`name`,
                        				`priority`.`name`,
                        				`task_description`,
                        				`task_note`
                                FROM 	`task`
                                JOIN	`task_type` ON task.task_type_id = task_type.id
                                JOIN	`department` ON task.task_departament_id = department.id
                                JOIN	`users` AS recipient ON task.task_recipient_id = recipient.id
                                JOIN	`user_info` AS recipient_ps ON recipient.id = recipient_ps.user_id
                                JOIN	`users` AS controler ON task.task_controler_id = controler.id
                                JOIN	`user_info` AS controler_ps ON controler.id = controler_ps.user_id
                                JOIN	`users` AS former ON task.user_id = former.id
                                JOIN	`user_info` AS former_ps ON former.id = former_ps.user_id
                                JOIN	`priority` ON priority.id = task.task_priority_id
                                WHERE 	`task`.`actived` = 1 AND task_status_id = $task_status_id");
		
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
                                  <input type="checkbox" id="callapp_checkbox_task_'.$aRow[$hidden].'" name="check_'.$aRow[$hidden].'" value="'.$aRow[$hidden].'" class="check" />
                                  <label for="callapp_checkbox_task_'.$aRow[$hidden].'"></label>
                              </div>';
				}
			}
			$data['aaData'][] = $row;
		}
	
	    break;
    case 'save_task':
		if($task_id == ''){
		    mysql_query("INSERT INTO `task`
            		    (`user_id`,  `task_recipient_id`, `task_controler_id`, `task_date`, `task_start_date`, `task_end_date`, `task_departament_id`, `task_type_id`, `task_priority_id`, `task_description`, `task_note`, `task_status_id`)
            		    VALUES
            		    ('$user_id', '$task_recipient_id', '$task_controler_id', NOW(), '$task_start_date', '$task_end_date', '$task_departament_id', '$task_type_id', '$task_priority_id', '$task_description', '$task_note', '$task_status_id');");
		}else{
		    mysql_query("UPDATE `task` SET
                                `task_status_id`='$task_status_id',
		                        `ch_st_date`=NOW(),
		                        `task_answer`='$task_answer'
                         WHERE  `id`='$task_id';");
		}
		
        break;
    case 'disable':
	    mysql_query("UPDATE `task` SET
	                        `actived`='0'
                     WHERE  `id`='$task_id';");
	
    break;
	default:
		$error = 'Action is Null';
}

$data['error'] = $error;

echo json_encode($data);


/* ******************************
 *	Request Functions
* ******************************
*/

function getStatusTask($id){

    $req = mysql_query("    SELECT 	`id`,
                                    `name`
                            FROM    `task_status`
                            WHERE   `actived` = 1 AND `type` = 2");

    $data .= '<option value="0">-----</option>';
    while( $res = mysql_fetch_assoc($req)){
        if($id == $res[id]){
            $data .= '<option value="' . $res['id'] . '" selected>' . $res['name'] . '</option>';
        }else{
            $data .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
        }
    }

    return $data;
}

function GetPriority($id){

    $req = mysql_query("    SELECT 	`id`,
                                    `name`
                            FROM    `priority`
                            WHERE   `actived` = 1");

    $data .= '<option value="0">-----</option>';
    while( $res = mysql_fetch_assoc($req)){
        if($id == $res[id]){
            $data .= '<option value="' . $res['id'] . '" selected>' . $res['name'] . '</option>';
        }else{
            $data .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
        }
    }

    return $data;
}

function GetDepartament($id){

    $req = mysql_query("    SELECT 	`id`,
                                    `name`
                            FROM    `department`
                            WHERE   `actived` = 1");

    $data .= '<option value="0">-----</option>';
    while( $res = mysql_fetch_assoc($req)){
        if($id == $res[id]){
            $data .= '<option value="' . $res['id'] . '" selected>' . $res['name'] . '</option>';
        }else{
            $data .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
        }
    }

    return $data;
}

function GetTaskType($id){

    $req = mysql_query("    SELECT 	`id`,
                                    `name`
                            FROM    `task_type`
                            WHERE   `actived` = 1");

    $data .= '<option value="0">-----</option>';
    while( $res = mysql_fetch_assoc($req)){
        if($id == $res[id]){
            $data .= '<option value="' . $res['id'] . '" selected>' . $res['name'] . '</option>';
        }else{
            $data .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
        }
    }

    return $data;
}

function getUsers($id){
    $req = mysql_query("SELECT 	    `users`.`id`,
                                    `user_info`.`name`
                        FROM 		`users`
                        JOIN 		`user_info` ON `users`.`id` = `user_info`.`user_id`
                        WHERE		`users`.`actived` = 1");
    
    $data .= '<option value="0">-----</option>';
    while( $res = mysql_fetch_assoc($req)){
        if($id == $res[id]){
            $data .= '<option value="' . $res['id'] . '" selected>' . $res['name'] . '</option>';
        }else{
            $data .= '<option value="' . $res['id'] . '">' . $res['name'] . '</option>';
        }
    }

    return $data;
}


function GetTask($task_id)
{
	$res = mysql_fetch_assoc(mysql_query("SELECT 	task.`id`,
                                    				task.`task_date`,
                                    				task.`task_start_date`,
                                    				task.`task_end_date`,
                                    				task.`task_type_id`,
                                    				task.`task_departament_id`,
                                    				task.`task_recipient_id`,
                                    				task.`task_controler_id`,
                                    				task.`user_id`,
                                    				task.`task_priority_id`,
                                    				task.`task_status_id`,
                                    				task.`task_description`,
                                    				task.`task_note`,
                                    				task.`task_answer`,
                                    				task.ch_st_date,
                                    				`outgoing_campaign_detail`.`id` AS `out_id`,
                                    				`outgoing_campaign_detail`.`update_date`,
                                    				`outgoing_campaign_detail`.`status`,
                                    				`outgoing_campaign_detail`.`call_comment`,
                                    				`phone_base_detail`.`phone1`,
                                    				`phone_base_detail`.`phone2`,
                                    				`phone_base_detail`.`firstname`,
                                    				`phone_base_detail`.`lastname`,
                                    				`phone_base_detail`.`pid`,
                                    				`phone_base_detail`.`address1`,
                                    				`phone_base_detail`.`address2`,
                                    				`phone_base_detail`.`age`,
                                    				`phone_base_detail`.`activities`,
                                    				`phone_base_detail`.`born_date`,
                                    				`phone_base_detail`.`client_name`,
                                    				`phone_base_detail`.`id_code`,
                                    				`phone_base_detail`.`info1`,
                                    				`phone_base_detail`.`info2`,
                                    				`phone_base_detail`.`info3`,
                                    				`phone_base_detail`.`mail1`,
                                    				`phone_base_detail`.`mail2`,
                                    				`phone_base_detail`.`note`,
                                    				`phone_base_detail`.`sex`,
	                                                `task`.`incomming_call_id`,
                                    				personal_info.`client_person_number`,
                                    				personal_info.`client_person_lname`,
                                    				personal_info.`client_person_fname`,
                                    				personal_info.`client_person_phone1`,
                                    				personal_info.`client_person_phone2`,
                                    				personal_info.`client_person_mail1`,
                                    				personal_info.`client_person_mail2`,
                                    				personal_info.`client_person_note`,
                                    				personal_info.`client_person_addres1`,
                                    				personal_info.`client_person_addres2`,
                                    				personal_info.`client_number`,
                                    				personal_info.`client_name` AS client_name1,
                                    				personal_info.`client_phone1`,
                                    				personal_info.`client_phone2`,
                                    				personal_info.`client_mail1`,
                                    				personal_info.`client_mail2`,
                                    				personal_info.`client_addres1`,
                                    				personal_info.`client_addres2`,
	                                                personal_info.`client_status`,
                                    				personal_info.`client_note`
                                        FROM 		`task`                                                                       
                                        LEFT JOIN 	`outgoing_campaign_detail` ON `task`.`outgoing_id` = `outgoing_campaign_detail`.`id`
                                        LEFT JOIN 	`phone_base_detail` ON outgoing_campaign_detail.phone_base_detail_id = phone_base_detail.id
                                        LEFT JOIN 	`incomming_call` ON `task`.`incomming_call_id` = `incomming_call`.`id`
                                        LEFT JOIN	`personal_info` ON personal_info.incomming_call_id = incomming_call.id
                                        WHERE 	    `task`.`actived` = 1 AND task.id = $task_id"));
	return $res;
}

function GetPage($res)
{
    $dis = '';
    $checked = '';
    if($res != ''){
        $dis='disabled';
    }else{
        $checked = 'checked';
    }
    if($res != '' && $res[client_status] == 1){
        $data .= "<script>client_status('pers')</script>";
    }elseif ($res != '' && $res[client_status] == 2){
        $data .= "<script>client_status('iuri')</script>";
    }
	$data  .= '
	<div id="dialog-form">
	    <fieldset style="width: 430px;  float: left;">
	       <legend>ძირითადი ინფორმაცია</legend>
	       <table>
	               <tr>
	                   <td><label for="task_type_id">დავალების ტიპი</label></td>
	               </tr>
	               <tr>
	                   <td colspan=2><select style="width: 425px;" id="task_type_id" '.(($res[id]=='')?"":"disabled").'>'.GetTaskType($res['task_type_id']).'</select></td>
	               </tr>
	               <tr>
	                   <td><label for="task_start_date">პერიოდი</label></td>
	               </tr>	              
	               <tr>
	                   <td style="width: 220px;"><input style="float: left;" id="task_start_date1" type="text" value="'.$res['task_start_date'].'" '.(($res[id]=='')?"":"disabled").'><label for="task_start_date" style="float: left;margin-top: 7px;margin-left: 2px;">-დან</label></td>
	                   <td><input style="float: left;" id="task_end_date1" type="text" value="'.$res['task_end_date'].'" '.(($res[id]=='')?"":"disabled").'><label for="task_end_date" style="float: left;margin-top: 7px;margin-left: 2px;">-მდე</label></td>
	               </tr>
	               <tr>
	                   <td><label for="task_departament_id">განყოფილება</label></td>
	               </tr>
	               <tr>
	                   <td colspan=2><select style="width: 425px;" id="task_departament_id" '.(($res[id]=='')?"":"disabled").'>'.GetDepartament($res['task_departament_id']).'</select></td>
	               </tr>
	               <tr>
	                   <td><label for="task_recipient_id">ადრესატი</label></td>
	                   <td><label for="task_controler_id">მაკონტროლებელი</label></td>
	               </tr>	              
	               <tr>
	                   <td><select style="width: 174px;" id="task_recipient_id" '.(($res[id]=='')?"":"disabled").'>'.getUsers($res['task_recipient_id']).'</select></td>
	                   <td><select style="width: 174px;" id="task_controler_id" '.(($res[id]=='')?"":"disabled").'>'.getUsers($res['task_controler_id']).'</select></td>
	               </tr>
	               <tr>
	                   <td><label for="task_priority_id">პრიორიტეტი</label></td>
	                   <td><label for="task_status_id">სტატუსი</label></td>
	               </tr>	              
	               <tr>
	                   <td><select style="width: 174px;" id="task_priority_id" '.(($res[id]=='')?"":"disabled").'>'.GetPriority($res['task_priority_id']).'</select></td>
	                   <td><select style="width: 174px;" id="task_status_id" '.(($res[id]=='')?"":"").'>'.getStatusTask($res['task_status_id']).'</select></td>
	               </tr>
	               <tr>
	                   <td><label for="task_description">აღწერა</label></td>
	               </tr>
	               <tr>
	                   <td colspan=2><textarea style="resize: vertical;width: 419px;" id="task_description" '.(($res[id]=='')?"":"disabled").'>'.$res['task_description'].'</textarea></td>
	               </tr>
	               <tr>
	                   <td><label for="task_note">შენიშვნა</label></td>
	               </tr>
	               <tr>
	                   <td colspan=2><textarea style="resize: vertical;width: 419px;" id="task_note" '.(($res[id]=='')?"":"disabled").'>'.$res['task_note'].'</textarea></td>
	               </tr>
	               <tr>
	                   <td><label for="task_answer">პასუხი</label></td>
	               </tr>
	               <tr>
	                   <td colspan=2><textarea style="resize: vertical;width: 419px;" id="task_answer" '.(($res[id]=='')?"disabled":"").'>'.$res['task_answer'].'</textarea></td>
	               </tr>
	            </table>
	    </fieldset>
	    
        <div id="side_menu" style="float: left;height: 485px;width: 80px;margin-left: 10px; background: #272727; color: #FFF;margin-top: 6px;">
	       <spam class="info" style="display: block;padding: 10px 5px;  cursor: pointer;" onclick="show_right_side(\'info\')"><img style="padding-left: 22px;padding-bottom: 5px;" src="media/images/icons/info.png" alt="24 ICON" height="24" width="24"><div style="text-align: center;">ინფო</div></spam>
	       <spam class="record" style="display: block;padding: 10px 5px;  cursor: pointer;" onclick="show_right_side(\'record\')"><img style="padding-left: 22px;padding-bottom: 5px;" src="media/images/icons/record.png" alt="24 ICON" height="24" width="24"><div style="text-align: center;">ჩანაწერი</div></spam>
	       <spam class="file" style="display: block;padding: 10px 5px;  cursor: pointer;" onclick="show_right_side(\'file\')"><img style="padding-left: 22px;padding-bottom: 5px;" src="media/images/icons/file.png" alt="24 ICON" height="24" width="24"><div style="text-align: center;">ფაილი</div></spam>
	    </div>
	    
	    <div style="width: 615px;float: left;margin-left: 10px;" id="right_side">
            <fieldset style="display:none;" id="info">
                <legend>მომართვის ავტორი</legend>
	            <span class="hide_said_menu">x</span>';
                
	    if($res['incomming_call_id'] == ''){
	        
	    
        	    $data .='<div id="pers">
	               <table class="margin_top_10">
                           <tr>
                               <td '.(($res['pid'] == '')?'style="display:none;"':'').'><label for="client_person_number">პირადი ნომერი</label></td>
                               <td '.(($res['id_code'] == '')?'style="display:none;"':'').'><label for="client_number">საიდენტ. ნომერი</label></td>
                           </tr>
                           <tr>
                               <td '.(($res['pid'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_number" type="text" value="'.$res['pid'].'"></td>
                               <td '.(($res['id_code'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_number" type="text" value="'.$res['id_code'].'"></td>
                           </tr>
                            <tr>
                                <td '.(($res['firstname'] == '')?'style="display:none;"':'').'><label for="client_lname">სახელი</label></td>
	                            <td '.(($res['lastname'] == '')?'style="display:none;"':'').'><label for="client_person_fname">გვარი</label></td>
                            </tr>
    	                    <tr>
                                <td '.(($res['firstname'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_lname" type="text" value="'.$res['firstname'].'"></td>
	                            <td '.(($res['lastname'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_fname" type="text" value="'.$res['lastname'].'"></td>
                            </tr>
                        
                            <tr>
                                <td '.(($res['phone1'] == '')?'style="display:none;"':'').'><label for="client_person_phone1">ტელეფონი 1</label></td>
        	                    <td '.(($res['phone2'] == '')?'style="display:none;"':'').'><label for="client_person_phone2">ტელეფონი 2</label></td>
                            </tr>
    	                    <tr>
                                <td '.(($res['phone1'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_phone1" type="text" value="'.$res['phone1'].'"></td>
        	                    <td '.(($res['phone2'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_phone2" type="text" value="'.$res['phone2'].'"></td>
                            </tr>
    	                    <tr>
                                <td '.(($res['mail1'] == '')?'style="display:none;"':'').'><label for="client_person_mail1">ელ-ფოსტა 1</label></td>
        	                    <td '.(($res['mail2'] == '')?'style="display:none;"':'').'><label for="client_person_mail2">ელ-ფოსტა 2</label></td>
                            </tr>
    	                    <tr>
                                <td '.(($res['mail1'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_mail1" type="text" value="'.$res['mail1'].'"></td>
        	                    <td '.(($res['mail2'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_mail2" type="text" value="'.$res['mail2'].'"></td>
                            </tr>
	                        <tr>
                                <td '.(($res['address1'] == '')?'style="display:none;"':'').'><label for="client_person_addres1">მისამართი 1</label></td>
        	                    <td '.(($res['address2'] == '')?'style="display:none;"':'').'><label for="client_person_addres2">მისამართი 2</label></td>
                            </tr>
    	                    <tr>
                                <td '.(($res['address1'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_addres1" type="text" value="'.$res['address1'].'"></td>
        	                    <td '.(($res['address2'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_person_addres2" type="text" value="'.$res['address2'].'"></td>
                            </tr>
                        
        	                <tr>
        	                    <td '.(($res['note'] == '')?'style="display:none;"':'').'><label for="client_person_note">შენიშვნა</label></td>
        	                    <td '.(($res['client_name'] == '')?'style="display:none;"':'').'><label for="client_name">კლიენტის დასახელება</label></td>
        	                </tr>
        	                <tr>
        	                    <td '.(($res['note'] == '')?'style="display:none;"':'').'><textarea id="client_person_note" style="resize: vertical;width: 250px;">'.$res['note'].'</textarea></td>
        	                    <td '.(($res['client_name'] == '')?'style="display:none;"':'').'><input style="width: 250px;" id="client_name" type="text" value="'.$res['client_name'].'"></td>
        	                </tr>
    	                </table>
        	    </div>';
	    }else{
	        $data .= '<table>
                    <tr style="height:20px;">
                    	<td style="padding: 0px 0px 10px 110px;"><input type="radio" style="float:left;" onclick="client_status(\'pers\')" value="1" name="client_status" '.(($res['client_status']=='1')?'checked':"").' '.$dis.' '.$checked.'><span style="display: inline-block; margin: 8px;">ფიზიკური </span></td>
                    	<td style="height:20px;"><input type="radio" style="float:left;" onclick="client_status(\'iuri\')" value="2" name="client_status" '.(($res['client_status']=='2')?'checked':"").' '.$dis.'><span style="display: inline-block; margin: 8px;">იურიდიული </span></td>
                    </tr>
                </table>
	    
        	    <div id="pers">
	               <table class="margin_top_10">
                           <tr>
                               <td><label for="client_person_number">პირადი ნომერი</label></td>
                               
                           </tr>
                           <tr>
                               <td><input style="width: 580px;" id="client_person_number" type="text" value="'.$res['client_person_number'].'" maxlength="11" onkeypress=\'return event.charCode >= 48 && event.charCode <= 57\'></td>
                                  
                           </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td style="width: 328px;"><label for="client_lname">სახელი</label></td>
	                            <td><label for="client_person_fname">გვარი</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_lname" type="text" value="'.$res['client_person_lname'].'"></td>
	                            <td><input style="width: 250px;" id="client_person_fname" type="text" value="'.$res['client_person_fname'].'"></td>
                            </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td style="width: 328px;"><label for="client_person_phone1">ტელეფონი 1</label></td>
        	                    <td><label for="client_person_phone2">ტელეფონი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_phone1" type="text" value="'.$res['client_person_phone1'].'" onkeypress=\'return event.charCode >= 48 && event.charCode <= 57\'></td>
        	                    <td><input style="width: 250px;" id="client_person_phone2" type="text" value="'.$res['client_person_phone2'].'" onkeypress=\'return event.charCode >= 48 && event.charCode <= 57\'></td>
                            </tr>
    	                    <tr>
                                <td style="width: 328px;"><label for="client_person_mail1">ელ-ფოსტა 1</label></td>
        	                    <td><label for="client_person_mail2">ელ-ფოსტა 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_mail1" type="text" value="'.$res['client_person_mail1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_person_mail2" type="text" value="'.$res['client_person_mail2'].'"></td>
                            </tr>
	                        <tr>
                                <td style="width: 328px;"><label for="client_person_addres1">მისამართი 1</label></td>
        	                    <td><label for="client_person_addres2">მისამართი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_addres1" type="text" value="'.$res['client_person_addres1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_person_addres2" type="text" value="'.$res['client_person_addres2'].'"></td>
                            </tr>
                        </table>                	    
    	                <table class="margin_top_10">
        	                <tr>
        	                    <td><label for="client_person_note">შენიშვნა</label></td>
        	                </tr>
        	                <tr>
        	                    <td><textarea id="client_person_note" style="resize: vertical;width: 577px;">'.$res['client_person_note'].'</textarea></td>
        	                </tr>
    	                </table>
        	    </div>
	    
	            <div id="iuri" style="border: 1px solid #ccc;padding: 5px;margin-top: 20px;display:none;">
        	       <span class="client_main" onclick="show_main(\'client_main\',this)" style="border: 1px solid #ccc;border-bottom: 1px solid #F1F1F1;cursor: pointer;margin-top: -32px;margin-left: -6px;display: block;width: 100px;padding: 5px;text-align: center;">ძირითადი</span>
	               <span class="client_other" onclick="show_main(\'client_other\',this)" style="cursor: pointer;margin-top: -27px;margin-left: 108px;display: block;width: 125px;padding: 6px;text-align: center;">წარმომადგენელი</span>
	    
	               <div id="client_main">
                        <table class="margin_top_10">
                           <tr>
                               <td><label for="client_number">საიდენტ. ნომერი</label></td>
                               <td></td>
                           </tr>
                           <tr>
                               <td><input style="width: 483px;" id="client_number" type="text" value="'.$res['client_number'].'"></td>
                               <td><button id="client_checker" style="margin-left: 5px;">შემოწმება</button></td>
                           </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td><label for="client_name">დასახელება</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 565px;" id="client_name" type="text" value="'.$res['client_name1'].'"></td>
                            </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td style="width: 312px;"><label for="client_phone1">ტელეფონი 1</label></td>
        	                    <td><label for="client_phone2">ტელეფონი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_phone1" type="text" value="'.$res['client_phone1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_phone2" type="text" value="'.$res['client_phone2'].'"></td>
                            </tr>
    	                    <tr>
                                <td style="width: 312px;"><label for="client_mail1">ელ-ფოსტა 1</label></td>
        	                    <td><label for="client_mail2">ელ-ფოსტა 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_mail1" type="text" value="'.$res['client_mail1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_mail2" type="text" value="'.$res['client_mail2'].'"></td>
                            </tr>
        	                <tr>
                                <td style="width: 312px;"><label for="client_addres1">მისამართი 1</label></td>
        	                    <td><label for="client_person_addres2">მისამართი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_addres1" type="text" value="'.$res['client_addres1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_addres2" type="text" value="'.$res['client_addres2'].'"></td>
                            </tr>
                        </table>
    	               <table class="margin_top_10">
        	               <tr>
        	                   <td><label for="client_note">შენიშვნა</label></td>
        	               </tr>
        	               <tr>
        	                   <td><textarea id="client_note" style="resize: vertical;width: 565px;">'.$res['client_note'].'</textarea></td>
        	               </tr>
    	               </table>
	               </div>
	    
	               <div id="client_other" style="display:none;">
	                   <table class="margin_top_10">
                           <tr>
                               <td><label for="client_person_number">პირადი ნომერი</label></td>
                               
                           </tr>
                           <tr>
                               <td><input style="width: 565px;" id="client_person_number" type="text" value="'.$res['client_person_number'].'"></td>
                               
                           </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td style="width: 312px;"><label for="client_lname">სახელი</label></td>
	                            <td><label for="client_person_fname">გვარი</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_lname" type="text" value="'.$res['client_person_lname'].'"></td>
	                            <td><input style="width: 250px;" id="client_person_fname" type="text" value="'.$res['client_person_fname'].'"></td>
                            </tr>
                        </table>
                        <table class="margin_top_10">
                            <tr>
                                <td style="width: 312px;"><label for="client_person_phone1">ტელეფონი 1</label></td>
        	                    <td><label for="client_person_phone2">ტელეფონი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_phone1" type="text" value="'.$res['client_person_phone1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_person_phone2" type="text" value="'.$res['client_person_phone2'].'"></td>
                            </tr>
    	                    <tr>
                                <td style="width: 312px;"><label for="client_person_mail1">ელ-ფოსტა 1</label></td>
        	                    <td><label for="client_person_mail2">ელ-ფოსტა 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_mail1" type="text" value="'.$res['client_person_mail1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_person_mail2" type="text" value="'.$res['client_person_mail2'].'"></td>
                            </tr>
        	                <tr>
                                <td style="width: 312px;"><label for="client_person_addres1">მისამართი 1</label></td>
        	                    <td><label for="client_person_addres2">მისამართი 2</label></td>
                            </tr>
    	                    <tr>
                                <td><input style="width: 250px;" id="client_person_addres1" type="text" value="'.$res['client_person_addres1'].'"></td>
        	                    <td><input style="width: 250px;" id="client_person_addres2" type="text" value="'.$res['client_person_addres2'].'"></td>
                            </tr>
                        </table>                	    
    	                <table class="margin_top_10">
        	                <tr>
        	                    <td><label for="client_person_note">შენიშვნა</label></td>
        	                </tr>
        	                <tr>
        	                    <td><textarea id="client_person_note" style="resize: vertical;width: 565px;">'.$res['client_person_note'].'</textarea></td>
        	                </tr>
    	                </table>
	               </div>
	    
        	    </div>';
	    }
            $data .='</fieldset>
            
            <fieldset style="display:none;" id="record">
                <legend>ჩანაწერები</legend>
	            <span class="hide_said_menu">x</span>
	                '.show_record($res).'
            </fieldset>
            
            <fieldset style="display:none; width:600px;" id="file">
                <legend>ფაილი</legend>
	            <span class="hide_said_menu">x</span>
	                '.show_file($res).'
            </fieldset></div>
	       </fieldset>
	    </div>
	</div><input type="hidden" value="'.$res[id].'" id="id">';
	$inc = mysql_fetch_array(mysql_query("  SELECT  `id`+1 AS id
                                            FROM    `task`
                                            ORDER BY `id` DESC
                                            LIMIT 1"));
	
	$data .= '<input type="hidden" value="'.$inc[0].'" id="id_inc">';

	return $data;
}


function show_record($res){
    $ph1 = "`source` LIKE '%test%'";
    $ph2 = "or `source` LIKE '%test%'";
    if(strlen($res[phone1]) > 4){
        $ph1 = "`source` LIKE '%$res[phone1]%'";
    }
    if(strlen($res[phone2]) > 4){
        $ph2 = " or `source` LIKE '%$res[phone2]%'";
    }
    $record_incomming = mysql_query("SELECT  `datetime`,
                                             TIME_FORMAT(SEC_TO_TIME(duration),'%i:%s') AS `duration`,
                                             CONCAT(DATE_FORMAT(asterisk_incomming.call_datetime, '%Y/%m/%d/'),`file_name`) AS file_name
                                     FROM    `asterisk_incomming`
                                     WHERE   $ph1 $ph2 AND disconnect_cause != 'ABANDON'");
    while ($record_res_incomming = mysql_fetch_assoc($record_incomming)) {
        $str_record_incomming .= '<tr>
                                    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">'.$record_res_incomming[datetime].'</td>
                            	    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">'.$record_res_incomming[duration].'</td>
                            	    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;cursor: pointer;" onclick="listen(\''.$record_res_incomming[file_name].'\')"><span>მოსმენა</span></td>
                        	      </tr>';
    }
    
    $ph1 = "`phone` LIKE '%test%'";
    $ph2 = "or `phone` LIKE '%test%'";
    if(strlen($res[phone1]) > 4){
        $ph1 = "`phone` LIKE '%$res[phone1]%'";
    }
    if(strlen($res[phone2]) > 4){
        $ph2 = " or `phone` LIKE '%$res[phone2]%'";
    }
    
    $record_outgoing = mysql_query("SELECT  `call_datetime`,
                                            TIME_FORMAT(SEC_TO_TIME(duration),'%i:%s') AS `duration`,
                                            CONCAT(DATE_FORMAT(asterisk_outgoing.call_datetime, '%Y/%m/%d/'),`file_name`) AS file_name
                                    FROM    `asterisk_outgoing`
                                    WHERE   $ph1 $ph2");
    while ($record_res_outgoing = mysql_fetch_assoc($record_outgoing)) {
        $str_record_outgoing .= '<tr>
                                    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">'.$record_res_outgoing[call_datetime].'</td>
                            	    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">'.$record_res_outgoing[duration].'</td>
                            	    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;cursor: pointer;" onclick="listen(\''.$record_res_outgoing[file_name].'\')"><span>მოსმენა</span></td>
                        	      </tr>';
    }
    
    if($str_record_outgoing == ''){
        $str_record_outgoing = '<tr>
                                    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;" colspan=3>ჩანაწერი არ მოიძებნა</td>
                        	      </tr>';
    }
    
    if($str_record_incomming == ''){
        $str_record_incomming = '<tr>
                                    <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;" colspan=3>ჩანაწერი არ მოიძებნა</td>
                        	      </tr>';
    }
    
    $data = '  <div style="margin-top: 10px;">
                    <audio controls autoplay style="margin-left: 145px;">
                      <source src="" type="audio/wav">
                      Your browser does not support the audio element.
                    </audio>
               </div>
               <fieldset style="display:block !important; margin-top: 10px;">
                    <legend>შემომავალი ზარი</legend>
    	            <table style="margin: auto;">
    	               <tr>
    	                   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">თარიღი</td>
                    	   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">ხანგძლივობა</td>
                    	   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">მოსმენა</td>
                	    </tr>
    	                '.$str_record_incomming.'
            	    </table>
	            </fieldset>
	            <fieldset style="display:block !important; margin-top: 10px;">
                    <legend>გამავალი ზარი</legend>
    	            <table style="margin: auto;">
    	               <tr>
    	                   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">თარიღი</td>
                    	   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">ხანგძლივობა</td>
                    	   <td style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;">მოსმენა</td>
                	    </tr>
    	                '.$str_record_outgoing.'
            	    </table>
	            </fieldset>';
    return $data;
}

function show_file($res){

    $file_incomming = mysql_query(" SELECT `name`,
                                			`rand_name`,
                                			`file_date`,
                                			`id`
                                    FROM   `file`
                                    WHERE  `task_id` = '$res[id]' AND `actived` = 1
                                    UNION ALL
                                    SELECT `name`,
                                    	   `rand_name`,
                                    	   `file_date`,
                                    	   `id`
                                    FROM   `file`
                                    WHERE  `outgoing_id` = '$res[out_id]' AND `actived` = 1
                                    UNION ALL
                                    SELECT `name`,
                                    	   `rand_name`,
                                    	   `file_date`,
                                    	   `id`
                                    FROM   `file`
                                    WHERE  `incomming_call_id` = '$res[incomming_call_id]' AND `actived` = 1");
    while ($file_res_incomming = mysql_fetch_assoc($file_incomming)) {
        $str_file_incomming .= '<div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 180px;float:left;">'.$file_res_incomming[file_date].'</div>
                            	<div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 189px;float:left;">'.$file_res_incomming[name].'</div>
                            	<div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;cursor: pointer;width: 160px;float:left;" onclick="download_file(\''.$file_res_incomming[rand_name].'\')">ჩამოტვირთვა</div>
                            	<div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;cursor: pointer;width: 20px;float:left;" onclick="delete_file(\''.$file_res_incomming[id].'\',\'task\')">-</div>';
    }
    $data = '<div style="margin-top: 15px;>
                    <div style="width: 100%; border:1px solid #CCC;float: left;">    	            
    	                   <div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 180px;float:left;">თარიღი</div>
                    	   <div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 189px;float:left;">დასახელება</div>
                    	   <div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 160px;float:left;">ჩამოტვირთვა</div>
                           <div style="border: 1px solid #CCC;padding: 5px;text-align: center;vertical-align: middle;width: 20px;float:left;">-</div>
    	                   <div style="text-align: center;vertical-align: middle;float: left;width: 595px;"><button id="upload_file1" style="cursor: pointer;background: none;border: none;width: 100%;height: 25px;padding: 0;margin: 0;">აირჩიეთ ფაილი</button><input style="display:none;" type="file" name="file_name1" id="file_name1"></div>
                           <div id="paste_files">
                           '.$str_file_incomming.'
                           </div>
            	    </div>
	            </div>';
    return $data;
}

?>