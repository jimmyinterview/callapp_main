<?php
require_once('../../includes/classes/core.php');
$action	= $_REQUEST['act'];
$error	= '';
$data	= '';
 
switch ($action) {
	case 'get_add_page':
		$page		= GetPage();
		$data		= array('page'	=> $page);

		break;
// 	case 'get_edit_page':
// 		$departmetn_id		= $_REQUEST['id'];
// 	       $page		= GetPage(Getdepartment($departmetn_id));
//            $data		= array('page'	=> $page);

// 		break;
	case 'get_list' :
		$count	= $_REQUEST['count'];
		$hidden	= $_REQUEST['hidden'];
		
		$start	= $_REQUEST['start'];
		$end	= $_REQUEST['end'];
		$agent	= $_REQUEST['agent'];
		
		if ($agent==0) {
		    $agent        = "";
		    $filt_user    = "";
		    $filt_agent   = "";
		    $click_agent  = "";
		}else {
		    if ($agent==203) {
		        $agent_user_id=7;
		        $agent_filt='agent1';
		    }elseif ($agent==204){
		        $agent_user_id=8;
		        $agent_filt='agent2';
		    }
		    $filt         = "AND asterisk_outgoing.extension='$agent'";
		    $filt_user    = "AND sent_mail.user_id='$agent_user_id'";
		    $filt_agent   = "AND access_log.agent='$agent_filt'";
		    $click_agent  = "AND click_log.agent='$agent_filt'";
		}
		$rResult = mysql_query("SELECT  access_log.id,
		                                (SELECT COUNT(asterisk_outgoing.phone) FROM `asterisk_outgoing`
                    					 WHERE LENGTH(asterisk_outgoing.phone)>3 
                    					 AND asterisk_outgoing.duration>0 
                    					 AND asterisk_outgoing.phone != '2555130'
		                                 $filt
					                     AND DATE(asterisk_outgoing.call_datetime) BETWEEN '$start' AND '$end'
                        				) AS coll_count,
                        				( SELECT COUNT(DISTINCT sent_mail.address) FROM `sent_mail`
                        				  WHERE NOT ISNULL(sent_mail.body) 
		                                  AND sent_mail.body!=''
		                                  $filt_user
		                                  AND DATE(sent_mail.date) BETWEEN '$start' AND '$end'
                        				) AS mail_count,
                        				COUNT(DISTINCT access_log.ip) AS visitor_count,
                        				(SELECT COUNT(DISTINCT click_log.ip) 
                                         FROM   `click_log`
                                         WHERE  DATE(click_log.date) BETWEEN '$start' AND '$end'
		                                 $click_agent
		                                ) AS click_count
                                  FROM access_log
		                          WHERE  DATE(access_log.date) BETWEEN '$start' AND '$end ' $filt_agent ");

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
				
			}
			$data['aaData'][] = $row;
		}

		break;
    case 'get_list_visit' :
		    $count	= $_REQUEST['count'];
		    $hidden	= $_REQUEST['hidden'];
		
		    $start	= $_REQUEST['start'];
		    $end	= $_REQUEST['end'];
		    $agent	= $_REQUEST['agent'];
		
		    if ($agent==0) {
		        $filt_agent   = "";
		    }else {
		        if ($agent==203) {
		            $agent_filt='agent1';
		        }elseif ($agent==204){
		           $agent_filt='agent2';
		        }
		        
		        $filt_agent   = " AND access_log.agent='$agent_filt'";
		       
		    }
		    $rResult = mysql_query("SELECT date,
		                                   date,
                                    	   ip,
		                                   agent
                                    FROM `access_log`
                                    WHERE DATE(access_log.date) BETWEEN '$start' AND '$end'  $filt_agent
                                    GROUP BY ip");
		
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
		        }
		        $data['aaData'][] = $row;
		    }
		
		    break;
	case 'get_list_price' :
		    $count	= $_REQUEST['count'];
		    $hidden	= $_REQUEST['hidden'];
		
		    $start	= $_REQUEST['start'];
		    $end	= $_REQUEST['end'];
		    $agent	= $_REQUEST['agent'];
		
		    if ($agent==0) {
		        $filt_agent   = "";
		    }else {
		        if ($agent==203) {
		            $agent_filt='agent1';
		        }elseif ($agent==204){
		           $agent_filt='agent2';
		        }
		        
		        $filt_agent   = " AND click_log.agent='$agent_filt'";
		       
		    }
		    $rResult = mysql_query("SELECT date,
		                                   date,
                                    	   ip,
		                                   agent
                                    FROM `click_log`
                                    WHERE DATE(click_log.date) BETWEEN '$start' AND '$end'  $filt_agent
                                    GROUP BY ip");
		
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
		            
		        }
		        $data['aaData'][] = $row;
		    }
		
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
function CheckdepartmentExist($department_name)
{
	$res = mysql_fetch_assoc(mysql_query("	SELECT `id`
											FROM   `department`
											WHERE  `name` = '$department_name' && `actived` = 1"));
	if($res['id'] != ''){
		return true;
	}
	return false;
}

function GetPage($res = '')
{
	$data = '
	<div id="dialog-form">
	    <fieldset>
	      <div class="" style="width:400px;">           
	            
				<table class="display" id="table_visit" style="width: 100%;">
                    <thead>
                        <tr id="datatable_header">
                            <th>ID</th>
                            <th style="width: 50%;">თარიღი</th>
                            <th style="width: 50%;">IP</th>
	                        <th style="width: 50%;">აგენტი</th>
                        </tr>
                    </thead>
                    <thead>
                        <tr class="search_header">
                            <th class="colum_hidden">
                        	   <input type="text" name="search_id" value="ფილტრი" class="search_init" />
                            </th>
                            <th>
                            	<input type="text" name="search_number" value="ფილტრი" class="search_init" />
                            </th>
	                        <th>
                            	<input style="width: 98%;" type="text" name="search_number" value="ფილტრი" class="search_init" />
                            </th>
	                        <th>
                            	<input style="width: 98%;" type="text" name="search_number" value="ფილტრი" class="search_init" />
                            </th>
                         </tr>
                    </thead>
                </table>
	           </div>
	      </fieldset>
	</div>
    ';
	return $data;
}

?>
