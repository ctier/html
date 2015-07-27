<?php
require("../../inc/pub_func.php");
require("../../inc/pub_set.php");
        
switch($action){
	 
    //客户清单列表
	case "get_lists":
  		 
		if($list_id<>""){
 			
			if(strpos($list_id,",")>-1){
				$list_id=str_replace(",","','",$list_id);
				$list_id="'".$list_id."'";
				$sql_list_id=" in(".$list_id.") ";
			}else{
				$sql_list_id=" like '%".$list_id."%' ";
			}
			$sql1=" and a.list_id ".$sql_list_id."";
		}
		
		if($list_name<>""){
 			$sql2=" and a.list_name like '%".$list_name."%'";
		} 
 		
		if($campaign_id<>""){
			if(strpos($campaign_id,",")>-1){
				$campaign_id=str_replace(",","','",$campaign_id);
				$campaign_id="'".$campaign_id."'";
				$sql_campaign_id=" in(".$campaign_id.") ";
			}else{
				$sql_campaign_id=" ='".$campaign_id."' ";
			}
			$sql3=" and a.campaign_id ".$sql_campaign_id."";		
		} 
		
		if($active<>""){
			$sql4=" and a.active='".$active."'";		
		} 
		
		if($list_description<>""){
			$sql5=" and a.list_description like '%".$list_description."%' ";	
		} 
		
		$wheres=$sql1.$sql2.$sql3.$sql4.$sql5;
		//获取记录集个数
		if($do_actions=="count"){
			
			$sql="select count(*) from vicidial_lists a left join (select '00' as list_id ) b on a.list_id=b.list_id left join vicidial_campaigns c on a.campaign_id=c.campaign_id  where 1=1 ".$wheres." ";
			//echo $sql;
			$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
			$counts=$rows[0];
			if(!$counts){$counts="0";}
			$des="d";
			$list_arr=array('id'=>'none');
			
		}else if($do_actions=="list"){
		
			$offset=($pages-1)*$pagesize;
			
			$sql="select a.list_id,a.list_name,case when a.active='Y' then '启用' else '禁用' end as active,a.list_description,a.list_changedate,a.list_lastcalldate,ifnull(b.counts,0) as counts,ifnull(concat(a.campaign_id,' [',c.campaign_name,']'),concat(a.campaign_id,' [未知业务]')) as campaign_name from vicidial_lists a inner join (select count(*) as counts,a.list_id from vicidial_lists a left join vicidial_list  b on a.list_id=b.list_id where 1=1 ".$wheres." group by a.list_id ".$sort_sql." limit ".$offset.",".$pagesize.") b on a.list_id=b.list_id left join vicidial_campaigns c on a.campaign_id=c.campaign_id  ";
			
 			//echo $sql;
			
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			$list_arr=array();
			 
			if ($row_counts_list!=0) {
				while($rs= mysqli_fetch_array($rows)){ 
				 
					$list=array("list_id"=>$rs['list_id'],"list_name"=>$rs['list_name'],"campaign_name"=>$rs['campaign_name'],"active"=>$rs['active'],"list_description"=>$rs['list_description'],"list_changedate"=>$rs['list_changedate'],"list_lastcalldate"=>$rs['list_lastcalldate'],"counts"=>$rs['counts']);
					 
					array_push($list_arr,$list);
				}
				$counts="1";
				$des="获取成功!";
			}else {
				$counts="0";
				$des="未找到符合条件的数据!";
				$list_arr=array('id'=>'none');
			}
  			
		} 
   	 
		mysqli_free_result($rows);
	
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
	 
	break;
	
   	
	//验证客户清单是否存在
	case "check_list":
 		
		if($list_id!=""){
			$sql="select list_id from vicidial_lists where list_id='".$list_id."' limit 0,1";
			
			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			if ($row_counts_list!=0) {
 				 
				$counts="0";
				$des="该客户清单ID已存在，请检查更换其他!";
			}else {
				$counts="1";
				$des="";
			}
			
			mysqli_free_result($rows);
			
		}else{
			$counts="-1";
			$des="未输入客户清单ID!";
		}
		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
		 
 		$json_data.="}";
		
		echo $json_data;
	
	break;	
	
	//按统计导出、重置、删除
	case "leads_count_set":
 		
		if($list_id!=""){
			$sql="select list_id from vicidial_lists where list_id='".$list_id."' limit 0,1";
			
			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			if ($row_counts_list!=0) {
 				 
				$counts="0";
				$des="该客户清单ID已存在，请检查更换其他!";
			}else {
				$counts="1";
				$des="";
			}
			
			mysqli_free_result($rows);
			
		}else{
			$counts="-1";
			$des="未输入客户清单ID!";
		}
		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
 	
	//添加、修改客户清单
	case "leads_list_set":
  		
		if($do_actions=="add"){
			
			$sql="select list_id from vicidial_lists where list_id='".$list_id."' limit 0,1";
 			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			mysqli_free_result($rows);
			
			if ($row_counts_list!=0) {
 				$counts="0";
				$des="该客户清单ID已存在，请检查更换其他!";
				
			}else {
   			
				 $sql="insert into vicidial_lists (list_id,list_name,campaign_id,active,list_description)
				  select '".$list_id."','".$list_name."','".$campaign_id."','".$active."','".$list_description."' from (select '".$list_id."' as list_id ) datas where not exists(select list_id from vicidial_lists a where a.list_id=datas.list_id );";
  			 	//echo $sql;
  			 $sql2="create table list_".$list_id."_fields(id int(11) not null PRIMARY KEY AUTO_INCREMENT, lead_id int(9) unsigned  not null)ENGINE=INNODB DEFAULT CHARSET=utf8";
  			 
  			//$sql2="create table list_".$list_id."_fields(id int(11) not null PRIMARY KEY AUTO_INCREMENT, FOREIGN KEY(lead_id) REFERENCES references vicidial_list(lead_id) on delete cascade on update cascade)ENGINE=INNODB DEFAULT CHARSET=utf8";
  			
				if(mysqli_query($db_conn,$sql)&&mysqli_query($db_conn,$sql2)){
					
  				$counts="1";
					$des="新建客户清单 ".$list_name." [".$list_id."] 成功!";
					
 				}else{
					$counts="0";
					$des="新建客户清单 ".$list_name." [".$list_id."] 失败，请检查重试!";
 				}
 				
 				 $sql3="alter table list_".$list_id."_fields add foreign key(lead_id) references vicidial_list(lead_id) on delete cascade on update cascade;";
 				mysqli_query($db_conn,$sql3);
 				
 			 				
			}
 			
		}else if($do_actions=="update"){			
			if($list_id!=""){
				//,agent_script_override='".$agent_script_override."',campaign_cid_override='".$campaign_cid_override."',am_message_exten_override='".$am_message_exten_override."',drop_inbound_group_override='".$drop_inbound_group_override."',xferconf_a_number='".$xferconf_a_number."',xferconf_b_number='".$xferconf_b_number."',xferconf_c_number='".$xferconf_c_number."',xferconf_d_number='".$xferconf_d_number."',xferconf_e_number='".$xferconf_e_number."'
				if ($reset_list == 'Y'){
					$reset_time=$SQLdate;
					//$set_time_sql=",reset_time='".$reset_time."'";
				}else{
					$reset_time="";
					$set_time_sql="";
				}
				$sql="update vicidial_lists set list_name='".$list_name."',campaign_id='".$campaign_id."',active='".$active."',list_description='".$list_description."',list_changedate='".$SQLdate."' ".$set_time_sql." where list_id='".$list_id."';";
				//echo $sql;
				if(mysqli_query($db_conn,$sql)){
  					
					//重置客户清单号码 
					if ($reset_list == 'Y'){
						 
						$sql_2="update vicidial_list set called_since_last_reset='N' where list_id='".$list_id."';";
						mysqli_query($db_conn,$sql_2);
 					}
					//从活动客户清单的期望表删除号码
					if ($campaign_id != "$old_campaign_id"){
						 
						$sql_3="delete from vicidial_hopper where list_id='".$list_id."' and campaign_id='".$old_campaign_id."';";
						mysqli_query($db_conn,$sql_3);
					}
			 
 					$counts="1";
					$des="客户清单 ".$list_name." [".$list_id."] 修改成功!";
				 
 				}else{
					$counts="0";
					$des="客户清单 ".$list_name." [".$list_id."] 修改失败，请检查相关设置重试!";
				 
				}
				
 			}else{
				$counts="0";
				$des="修改失败，客户清单ID不存在!";
			}
						
		}else{
			if($list_id!=""){
				
				$sql_1="update vicidial_list set called_since_last_reset='N' where list_id='".$list_id."';";
 				//$sql_2="update vicidial_lists set reset_time='".$SQLdate."' where list_id='".$list_id."'";
				
				if(mysqli_query($db_conn,$sql_1)){
					$counts="1";
					$des="重置号码成功!请加入相应呼叫状态到活动可呼叫状态中!";
					$reset_time=$SQLdate;
				}else{
					$counts="0";
					$des="重置号码失败，系统错误，请检查重试!";
					$reset_time="";
				}
				
			}else{
				$counts="0";
				$des="修改失败，客户清单ID不存在!";
			}
 		}
 		//echo $sql;
  		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"set_time\":".json_encode($reset_time).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
	
	break;
    	
	//删除客户清单
  	case "del_leads_list":
 		
		if($cid!=""){
			
			if(strpos($cid,",")>-1){
				$cid=str_replace(",","','",$cid);
				$cid="'".$cid."'";
				$where_sql=" in(".$cid.") ";
			}else{
				$where_sql=" ='".$cid."' ";
			}
 		
			//删除期望表
			$sql_1="delete from vicidial_hopper where list_id ".$where_sql." ";
			
			//删除客户清单号码
			$sql_2="delete from vicidial_list where list_id ".$where_sql." ";
			
 			//删除客户清单
			$sql_3="delete from vicidial_lists where list_id ".$where_sql." ";
			
			//删除自定义字段属性内容
			$sql_4="delete from list_fields where list_id ".$where_sql." ";

			//删除自定义字段表
			$sql_5="Drop table list_".$cid."_fields";
				
			if (mysqli_query($db_conn,$sql_1)&&mysqli_query($db_conn,$sql_2)&&mysqli_query($db_conn,$sql_3)&&mysqli_query($db_conn,$sql_4)&&mysqli_query($db_conn,$sql_5)){
				$counts="1";
				$des="删除成功!";
			}else{
				$counts="0";
				$des="删除失败，请检查相关设置重试!";
			}
			
		}else{
			$counts="0";
			$des="删除失败，请输入要删除的行!";			
		} 		 
 		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
 		
	//导出全部 
  	case "export_leads_list":
 		$file_type=trim($_REQUEST["file_type"]);
		if($list_id!=""){
			
			$sql="select a.lead_id as '号码ID',phone_number as '号码',a.status as '状态码',b.status_name as '呼叫结果',case when called_since_last_reset='N' then '是' else '否' end as '是否可拨',user as '呼叫人',title as '标题',first_name as '名字',middle_initial as '中间名',last_name as '姓氏',address1 as '地址1',address2 as '地址2',address3 as '地址3',city as '城市',phone_code as '区号',state as '地区',postal_code as '邮编',province as '省份',gender as '性别',alt_phone as '备用电话',email as '邮箱',comments as '描述',date_of_birth as '生日',called_count as '呼叫次数',last_local_call_time as '呼叫时间',entry_date as '导入时间' from vicidial_list a left join data_sys_status b on a.status=b.status and b.Status_Type='call_status' where a.list_id='".$list_id."'";
			//echo $sql;
			echo json_encode(save_detail_excel($sql,"客户清单",$file_type));
 			
		}else{
			$counts="0";
			$des="导出失败，请输入要导出的清单ID!";
			 		
			$json_data="{";
			$json_data.="\"counts\":".json_encode($counts).",";
			$json_data.="\"des\":".json_encode($des)."";
			$json_data.="}";
			
			echo $json_data;
		
		}
 		 
 	break;
	
	//获取客户清单列表
	case "get_campaign_leads_list":
 		
		
 		if($list_id!=""){
			 
			$wheres=" and a.list_id='".$list_id."'";
			if($list_active!=""){
				$wheres.=" and a.active='".$list_active."'";
			} 
			if($do_actions=="count"){
				
				$sql="select count(*) from vicidial_lists a left join (select ifnull(count(*),0) as counts,list_id from vicidial_list GROUP BY list_id) b on a.list_id=b.list_id where 1=1 ".$wheres." ";
				//echo $sql;
				$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
				$counts=$rows[0];
				if(!$counts){$counts="0";}
				$des="d";
				$list_arr=array('id'=>'none');
				
			}else if($do_actions=="list"){
			
				$offset=($pages-1)*$pagesize;
				
				$sql="select a.list_id,list_name,list_description,case when active='Y' then '启用' else '禁用' end as active,list_lastcalldate,b.counts from vicidial_lists a left join (select ifnull(count(*),0) as counts,list_id from vicidial_list GROUP BY list_id) b on a.list_id=b.list_id where 1=1 ".$wheres." ".$sort_sql."  limit ".$offset.",".$pagesize." ";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				$list_arr=array();
				 
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("list_id"=>$rs['list_id'],"list_name"=>$rs['list_name'],"list_description"=>$rs['list_description'],"active"=>$rs['active'],"list_lastcalldate"=>$rs['list_lastcalldate'],"counts"=>$rs['counts']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			} 
		 
			mysqli_free_result($rows);
			
		}else{
			$counts="0";
			$des="未输入客户清单ID!";
			$list_arr=array('id'=>'none');
		}
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
		
	break;
	
	//获取期望表清单列表
	case "get_campaign_lead_hopper_list":
 		
  		if($list_id!=""){
			 
			$wheres=" and a.list_id='".$list_id."' and a.status='READY'";
			 
			if($do_actions=="count"){
				
				$sql="select count(*) from vicidial_hopper a left join vicidial_list b on a.lead_id=b.lead_id left join data_sys_status c on b.status=c.status and c.status_type='call_status' left join vicidial_lists d on a.list_id=d.list_id where 1=1 ".$wheres." ";
				//echo $sql;
				$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
				$counts=$rows[0];
				if(!$counts){$counts="0";}
				$des="d";
				$list_arr=array('id'=>'none');
				
			}else if($do_actions=="list"){
			
				$offset=($pages-1)*$pagesize;
				
				$sql="select a.priority,a.lead_id,a.list_id,a.gmt_offset_now,a.state,a.alt_dial,c.status_name,b.called_count,b.phone_number,d.list_name from vicidial_hopper a left join vicidial_list b on a.lead_id=b.lead_id left join data_sys_status c on b.status=c.status and c.status_type='call_status' left join vicidial_lists d on a.list_id=d.list_id where 1=1 ".$wheres." ".$sort_sql."  limit ".$offset.",".$pagesize." ";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				$list_arr=array();
				 
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("list_id"=>$rs['list_id'],"priority"=>$rs['priority'],"lead_id"=>$rs['lead_id'],"gmt_offset_now"=>$rs['gmt_offset_now'],"state"=>$rs['state'],"alt_dial"=>$rs['alt_dial'],"status_name"=>$rs['status_name'],"called_count"=>$rs['called_count'],"phone_number"=>$rs['phone_number'],"list_name"=>$rs['list_name']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			} 
		 
			mysqli_free_result($rows);
			
		}else{
			$counts="0";
			$des="未输入客户清单ID!";
			$list_arr=array('id'=>'none');
		}
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
		
	 break;
 			
	//获取客户清单各项统计
	case "get_leads_count":
 		 
 		if($list_id!=""){
			$list_arr=array();
			 
			
			//呼叫结果统计	
			if($do_actions=="status_count"){
  				
				$sql="select case when datas.status is null and b.status is null then '合计' else datas.status end as status
,ifnull(b.status_name,'') as status_name,datas.call_1,datas.call_0 from (SELECT status,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by status with rollup )datas left join data_sys_status b on datas.status=b.status and b.status_type='call_status'";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("status"=>$rs['status'],"status_name"=>$rs['status_name'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			}else if($do_actions=="entry_date_status_count"){ //导入时间、呼叫结果统计
  				
				$sql="select case when entry_date is null and datas.status is null then '总计' else entry_date end as entry_date
,ifnull(case when entry_date is not null and datas.status is null and b.status is null then '合计' else datas.status end,'') as status ,ifnull(b.status_name,'') as status_name,datas.call_1,datas.call_0 from( SELECT left(entry_date,10) as entry_date,status,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by left(entry_date,10),status with rollup )datas left join data_sys_status b on datas.status=b.status and b.status_type='call_status'";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("entry_date"=>$rs['entry_date'],"status"=>$rs['status'],"status_name"=>$rs['status_name'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			}else if($do_actions=="entry_date_count"){ //导入时间统计
  				
				$sql="select case when datas.entry_date is null then '合计' else datas.entry_date end as entry_date,datas.call_1,datas.call_0 from ( select left(entry_date,10) as entry_date,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by left(entry_date,10) with rollup)datas ";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("entry_date"=>$rs['entry_date'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			}else if($do_actions=="owner_count"){ //导入时间统计
  				
				$sql="select ifnull(case when datas.owner is null and b.user is null then '合计' else concat(b.full_name,' [',datas.owner,']') end,'未指定') as owner,b.full_name,datas.call_1,datas.call_0 from (select owner,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by owner with rollup )datas left join vicidial_users b on datas.owner=b.user";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("owner"=>$rs['owner'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			}else if($do_actions=="last_call_status_count"){ //呼叫时间、呼叫结果统计 时区+8 为北京时间
  				
				$sql="select case when last_local_call_time is null and datas.status is null then '总计' else last_local_call_time end as last_local_call_time,ifnull(case when last_local_call_time is not null and datas.status is null and b.status is null then '合计' else datas.status end,'') as status,ifnull(b.status_name,'') as status_name,datas.call_1,datas.call_0 from (SELECT left(last_local_call_time,10) as last_local_call_time,status,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by left(last_local_call_time,10),status with rollup )datas left join data_sys_status b on datas.status=b.status and b.status_type='call_status'";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("last_local_call_time"=>$rs['last_local_call_time'],"status"=>$rs['status'],"status_name"=>$rs['status_name'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			}else if($do_actions=="last_call_count"){ //呼叫时间统计
  				
				$sql="select case when datas.last_local_call_time is null then '合计' else datas.last_local_call_time end as last_local_call_time,datas.call_1,datas.call_0 from ( select left(last_local_call_time,10) as last_local_call_time,ifnull(sum(case when called_since_last_reset!='N' then 1 else 0 end),0) as 'call_1',ifnull(sum(case when called_since_last_reset='N' then 1 else 0 end),0) as 'call_0' from vicidial_list where list_id='".$list_id."' group by left(last_local_call_time,10),called_since_last_reset with rollup )datas";
 				//echo $sql;
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("last_local_call_time"=>$rs['last_local_call_time'],"call_1"=>$rs['call_1'],"call_0"=>$rs['call_0']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			} 
  			
			mysqli_free_result($rows);
			
		}else{
			$counts="0";
			$des="未输入客户清单ID!";
			$list_arr=array('id'=>'none');
		}
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
		
	break;
	
	//删除、导出、重置
	case "reset_list_":
 		$f1=trim($_REQUEST["f1"]);
 		$f2=trim($_REQUEST["f2"]);
		$f3=trim($_REQUEST["f3"]);
		$file_type=trim($_REQUEST["file_type"]);
		
  		if($list_id!=""){
			$reset_time="";
			$wheres=" list_id='".$list_id."' ";
			$wheres_2=" a.list_id='".$list_id."' ";
			
			if($f1!=""&&$f2!=""){
				
				if($f2=="合计"){
					$wheres.=" and ".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";
					$wheres_2.=" and a.".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";
					
				}else if($f1=="总计"){
					$wheres.=" ";
					$wheres_2.=" ";
				}else{
					$wheres.=" and status='".$f2."' and ".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";	
					$wheres_2.=" and a.status='".$f2."' and a.".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";	
				}
				
			}else if($f1==""&&$f2!=""){
				
				if($f2=="合计"){
					$wheres.=" ";
					$wheres_2.=" ";
				}else{
					$wheres.=" and status='".$f2."' ";
					$wheres_2.=" and a.status='".$f2."' ";
				}
				
			}else{
				
				if($f1=="总计"){
					$wheres.=" ";
					$wheres_2.=" ";
				}else{
					$wheres.=" and ".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";
					$wheres_2.=" and a.".$f3." between '".$f1." 00:00:00' and '".$f1." 23:59:59' ";
				}
				
			}
			
 			if($do_actions=="reset"){
  				$reset_time=$SQLdate;
				$sql_1="update vicidial_list set called_since_last_reset='N' where ".$wheres.";";
 				//$sql_2="update vicidial_lists set reset_time='".$SQLdate."' where list_id='".$list_id."' ";
				//echo $sql_2;
				if(mysqli_query($db_conn,$sql_1)){
					$counts="1";
					$des="重置号码成功!请加入相应呼叫状态到活动可呼叫状态中!";
					$reset_time=$SQLdate;
				}else{
					$counts="0";
					$des="重置号码失败，系统错误，请检查重试!";
					$reset_time="";
				}
  				
			}else if($do_actions=="del"){
  				 
				//删除期望表
				$sql_1="delete from vicidial_hopper where lead_id in(select lead_id from (select lead_id from vicidial_list where ".$wheres.")temp_tbl) ";
				if(mysqli_query($db_conn,$sql_1)){
					$del_1="1";	
				}else{
					$del_1="0";	
				}
				
				//删除客户清单号码
				$sql_2="delete from vicidial_list where ".$wheres." ";
				if(mysqli_query($db_conn,$sql_2)){
					$del_2="1";	
				}else{
					$del_2="0";	
				}
				 
				if($del_1=="1"&&$del_2=="1"){
					$counts="1";
					$des="删除号码成功!";
					$reset_time="";
				}else{
					$counts="0";
					$des="删除号码失败，请检查重试!";
					$reset_time="";
				}
  				
			}else{
				
				$sql="select lead_id as '号码ID',phone_number as '号码',a.status as '状态码',b.status_name as '呼叫结果',case when called_since_last_reset='N' then '是' else '否' end as '是否可拨',user as '呼叫人',title as '标题',first_name as '名字',middle_initial as '中间名',last_name as '姓氏',address1 as '地址1',address2 as '地址2',address3 as '地址3',city as '城市',phone_code as '区号',state as '地区',postal_code as '邮编',province as '省份',gender as '性别',alt_phone as '备用电话',email as '邮箱',comments as '描述',date_of_birth as '生日',called_count as '呼叫次数',last_local_call_time as '呼叫时间',entry_date as '导入时间' from vicidial_list a left join data_sys_status b on a.status=b.status and b.Status_Type='call_status' where ".$wheres_2." ";
				//echo $sql;
				echo json_encode(save_detail_excel($sql,"客户清单",$file_type));
			}
		 
 			
		}else{
			$counts="0";
			$des="未输入客户清单ID!";
 		}
		
		if($do_actions!="export"){
			$json_data="{";
			$json_data.="\"counts\":".json_encode($counts).",";
			$json_data.="\"set_time\":".json_encode($reset_time).",";
			$json_data.="\"des\":".json_encode($des)."";
			//$json_data.="\"sql\":".json_encode($wheres)."";
			$json_data.="}";
 			echo $json_data;
		}
		
	break;
 			
    //清单号码列表
	case "get_lead_lists":
  		$called_since_last_reset=trim($_REQUEST["called_since_last_reset"]);
		
		
		$day_part=(strtotime($s_date)-strtotime($e_date))/86400;
			
		if($day_part>31||$day_part<-31){
		 
			$field_name_list=array("查询时间跨度超过31天");
			$list_arr=array('id'=>'none');
			
			$json_data="{";
			$json_data.="\"counts\":".json_encode(0).",";
			$json_data.="\"des\":".json_encode("本查询只可查询时间跨度为31天内数据!").",";
 			$json_data.="\"datalist\":".json_encode($list_arr)."";
			$json_data.="}";
			
			echo $json_data;
			
			die();
		}
		
 		
		if($lead_id<>""){
 			
			if(strpos($lead_id,",")>-1){
				$lead_id=str_replace(",","','",$lead_id);
				$lead_id="'".$lead_id."'";
				$sql_lead_id=" in(".$lead_id.") ";
			}else{
				$sql_lead_id=" ='".$lead_id."' ";
			}
			$sql1=" and vicidial_list.lead_id ".$sql_lead_id."";
		}
		
		if($phone_lists<>""){
 			
			if(strpos($phone_lists,",")>-1){
				$phone_lists=str_replace(",","','",$phone_lists);
				$phone_lists="'".$phone_lists."'";
				$sql_list_id=" in(".$phone_lists.") ";
			}else{
				$sql_list_id=" ='".$phone_lists."' ";
			}
			$sql2=" and vicidial_list.list_id ".$sql_list_id."";
		}
 		
		if($phone_number<>""){
   				
			if ($search_accuracy=="="){
				$exist_sql=" = '".$phone_number."'";
			}elseif($search_accuracy=="in"){
				$exist_sql="in('".$phone_number."')";
			}elseif($search_accuracy=="not in"){
				$exist_sql="not in('".$phone_number."')";
			}elseif($search_accuracy=="like_top"){
				$exist_sql="like '".$phone_number."%'";
			}elseif($search_accuracy=="like_end"){
				$exist_sql="like '%".$phone_number."'";
			}elseif($search_accuracy=="like"){
				$exist_sql="like '%".$phone_number."%'";
			}
 			$sql3=" and vicidial_list.phone_number ".$exist_sql;
		}	
		
		if($dial_status<>""){
			$sql4=" and vicidial_list.status='".$dial_status."'";		
		} 
 		
		if($title<>""){
 			$sql5=" and vicidial_list.title like '%".$title."%'";
		} 
		
		if($comments_des<>""){
 			$sql6=" and vicidial_list.comments like '%".$comments_des."%'";
		} 
  		
		if($called_since_last_reset<>""){
			if($called_since_last_reset=="N"){
				$called_since_last_reset_w=" ='N' ";
			}else{
				$called_since_last_reset_w=" !='N' ";
 			}
			$sql7=" and vicidial_list.called_since_last_reset ".$called_since_last_reset_w." ";		
		} 
		if($s_date!=""&&$e_date!=""){
			$sql8=" and vicidial_list.entry_date between '".$start_date."' and '".$end_date."'"; 
		}
		$wheres=$sql1.$sql2.$sql3.$sql4.$sql5.$sql6.$sql7.$sql8;
		//echo $wheres;
		//获取记录集个数
		if($do_actions=="count"){
			
			$sql="select count(*) from vicidial_list left join data_sys_status b on vicidial_list.status=b.status and b.status_type='call_status' left join vicidial_lists c on vicidial_list.list_id=c.list_id where 1=1 ".$wheres." ";
			//echo $sql;
			$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
			$counts=$rows[0];
			if(!$counts){$counts="0";}
			$des="d";
			$list_arr=array('id'=>'none');
			
		}else if($do_actions=="list"){
		
			$offset=($pages-1)*$pagesize;
 			
			$sql="select lead_id, vicidial_list.list_id, phone_number,title,comments,last_local_call_time,entry_date,case when called_since_last_reset='N' then '是' else '否' end as called_since_last_reset,concat(b.status_name,' [',b.status,']') as status_names,ifnull(concat(c.list_name,' [',c.list_id,']'),'[清单不存在]') as list_names from vicidial_list left join data_sys_status b on vicidial_list.status=b.status and b.status_type='call_status' left join vicidial_lists c on vicidial_list.list_id=c.list_id where 1=1 ".$wheres." ".$sort_sql." limit ".$offset.",".$pagesize." ";
			
 		//	echo $sql;
			
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			$list_arr=array();
			 
			if ($row_counts_list!=0) {
				while($rs= mysqli_fetch_array($rows)){ 
				 
					$list=array("lead_id"=>$rs['lead_id'], "list_id"=>$rs['list_id'],"phone_number"=>$rs['phone_number'],"title"=>$rs['title'],"comments"=>$rs['comments'],"last_local_call_time"=>$rs['last_local_call_time'],"entry_date"=>$rs['entry_date'],"called_since_last_reset"=>$rs['called_since_last_reset'],"status_names"=>$rs['status_names'],"list_names"=>$rs['list_names']);
					 
					array_push($list_arr,$list);
				}
				$counts="1";
				$des="获取成功!";
			}else {
				$counts="0";
				$des="未找到符合条件的数据!";
				$list_arr=array('id'=>'none');
			}
  			
		}else if($do_actions=="del"){
		
			//删除期望表
			$sql_1="delete from vicidial_hopper where lead_id in(select lead_id from (select lead_id from vicidial_list where 1=1 ".$wheres.")temp_tbl) ";
			if(mysqli_query($db_conn,$sql_1)){
				$del_1="1";	
			}else{
				$del_1="0";	
			}
			
			//删除客户清单号码
			$sql_2="delete from vicidial_list where 1=1 ".$wheres." ";
			if(mysqli_query($db_conn,$sql_2)){
				$del_2="1";	
			}else{
				$del_2="0";	
			}
			//echo $sql_1."<br><br>".$sql_2;
			 
			if($del_1=="1"&&$del_2=="1"){
				$counts="1";
				$des="删除号码成功!";
				$reset_time="";
			}else{
				$counts="0";
				$des="删除号码失败，请检查重试!";
				$reset_time="";
			}
  			
		}else if($do_actions=="txt"||$do_actions=="xls"||$do_actions=="csv"){
			
			$sql="select lead_id as '号码ID',phone_number as '号码',vicidial_list.status as '状态码',b.status_name as '呼叫结果',case when called_since_last_reset='N' then '是' else '否' end as '是否可拨',user as '呼叫人',title as '标题',first_name as '名字',middle_initial as '中间名',last_name as '姓氏',address1 as '地址1',address2 as '地址2',address3 as '地址3',city as '城市',phone_code as '区号',state as '地区',postal_code as '邮编',province as '省份',gender as '性别',alt_phone as '备用电话',email as '邮箱',comments as '描述',date_of_birth as '生日',called_count as '呼叫次数',last_local_call_time as '呼叫时间',entry_date as '导入时间' from vicidial_list left join data_sys_status b on vicidial_list.status=b.status and b.Status_Type='call_status' where 1=1 ".$wheres." ";
			echo $sql;
			
			
			//$sql="select lead_id as '号码ID',phone_number as '号码',vicidial_list.status as '状态码',b.status_name as '呼叫结果',case when called_since_last_reset='N' then '是' else '否' end as '是否可拨',user as '呼叫人',title as '标题',first_name as '名字',middle_initial as '中间名',last_name as '姓氏',address1 as '地址1',address2 as '地址2',address3 as '地址3',city as '城市',phone_code as '区号',state as '地区',postal_code as '邮编',province as '省份',gender as '性别',alt_phone as '备用电话',email as '邮箱',comments as '描述',date_of_birth as '生日',called_count as '呼叫次数',last_local_call_time as '呼叫时间',entry_date as '导入时间' from vicidial_list left join data_sys_status b on vicidial_list.status=b.status and b.Status_Type='call_status' where 1=1 ".$wheres." ";
			
			
			
			
			
			echo json_encode(save_detail_excel($sql,"客户清单",$do_actions));
		}
   	 
		mysqli_free_result($rows);
		
		if($do_actions!="txt"&&$do_actions!="xls"&&$do_actions!="csv"){
			$json_data="{";
			$json_data.="\"counts\":".json_encode($counts).",";
			$json_data.="\"des\":".json_encode($des).",";
			$json_data.="\"datalist\":".json_encode($list_arr)."";
			$json_data.="}";
			
			echo $json_data;
		}
	 
	break;
	
	//删除客户清单号码
  	case "del_leads":
 		
		if($cid!=""){
			
			if(strpos($cid,",")>-1){
				$cid=str_replace(",","','",$cid);
				$cid="'".$cid."'";
				$where_sql=" in(".$cid.") ";
			}else{
				$where_sql=" ='".$cid."' ";
			}
 		
			//删除期望表
			$sql_1="delete from vicidial_hopper where lead_id ".$where_sql." ";
			
			//删除客户清单号码
			$sql_2="delete from vicidial_list where lead_id ".$where_sql." ";
			
			//删除自定义字段
			//$sql_3="delete from list_".$list_id."_fields where lead_id ".$where_sql." ";
			
			//echo " sql_1;".$sql_1;
			//echo " sql_2;".$sql_2;
			//echo " sql_3;".$sql_3;
			
 			
			if (mysqli_query($db_conn,$sql_1)&&mysqli_query($db_conn,$sql_2)){
				$counts="1";
				$des="删除成功!";
			}else{
				$counts="0";
				$des="删除失败，请检查相关设置重试!";
			}
			
		}else{
			$counts="0";
			$des="删除失败，请输入要删除的行!";			
		}
 		 
 		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
  			
	//客户清单列表
	case "get_lead_call_lists":
  		if($lead_id!=""){
			$wheres=" a.lead_id='".$lead_id."' ";
			//获取记录集个数
			if($do_actions=="count"){
				
				$sql="select count(*) from vicidial_log a left join vicidial_users b on a.user=b.user left join data_sys_status c on a.status=c.status and c.status_type='call_status' left join data_sys_status d on a.quality_status=d.status and d.status_type='qua_status' left join recording_log e on a.uniqueid=e.vicidial_id and a.lead_id=e.lead_id left join vicidial_campaigns f on a.campaign_id=f.campaign_id where ".$wheres." ";
				//echo $sql;
				$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
				$counts=$rows[0];
				if(!$counts){$counts="0";}
				$des="d";
				$list_arr=array('id'=>'none');
				
			}else if($do_actions=="list"){
			
				$offset=($pages-1)*$pagesize;
				
				$sql="select a.phone_number,a.call_date,concat(b.full_name,' [',b.user,']') as users,concat(c.status_name,' [',c.status,']') as call_status,d.status_name as qua_status,case when left(e.location,4)='http' then '同步中' else replace(e.location,'".$record_ip."','".$record_web."') end as location,ifnull(concat(f.campaign_name,' [',f.campaign_id,']'),concat('未知业务 [',a.campaign_id,']')) as campaign_name from vicidial_log a left join vicidial_users b on a.user=b.user left join data_sys_status c on a.status=c.status and c.status_type='call_status' left join data_sys_status d on a.quality_status=d.status and d.status_type='qua_status' left join recording_log e on a.uniqueid=e.vicidial_id and a.lead_id=e.lead_id left join vicidial_campaigns f on a.campaign_id=f.campaign_id  where ".$wheres." ".$sort_sql." limit ".$offset.",".$pagesize." ";
				
				//echo $sql;
				
				$rows=mysqli_query($db_conn,$sql);
				$row_counts_list=mysqli_num_rows($rows);			
				
				$list_arr=array();
				 
				if ($row_counts_list!=0) {
					while($rs= mysqli_fetch_array($rows)){ 
					 
						$list=array("phone_number"=>$rs['phone_number'],"call_date"=>$rs['call_date'],"users"=>$rs['users'],"call_status"=>$rs['call_status'],"qua_status"=>$rs['qua_status'],"location"=>$rs['location'],"campaign_name"=>$rs['campaign_name']);
						 
						array_push($list_arr,$list);
					}
					$counts="1";
					$des="获取成功!";
				}else {
					$counts="0";
					$des="未找到符合条件的数据!";
					$list_arr=array('id'=>'none');
				}
				
			} 
		 
			mysqli_free_result($rows);
			
		}else {
			$counts="0";
			$des="请输入要查询的号码ID!";
			$list_arr=array('id'=>'none');
		}
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
	 
	break;			
			
	//修改客户清单号码
  	case "update_leads":
 		
		if($lead_id!=""){
 			
			$title=trim($_REQUEST["title"]);
			$first_name=trim($_REQUEST["first_name"]);
			$middle_initial=trim($_REQUEST["middle_initial"]);
			$last_name=trim($_REQUEST["last_name"]);
			$address1=trim($_REQUEST["address1"]);			
			$address2=trim($_REQUEST["address2"]);
			$address3=trim($_REQUEST["address3"]);
			$city=trim($_REQUEST["city"]);
			$state=trim($_REQUEST["state"]);
			$province=trim($_REQUEST["province"]);
			$gender=trim($_REQUEST["gender"]);
			$alt_phone=trim($_REQUEST["alt_phone"]);
			$postal_code=trim($_REQUEST["postal_code"]);
			$email=trim($_REQUEST["email"]);
			$comments=trim($_REQUEST["comments"]);
			$date_of_birth=trim($_REQUEST["date_of_birth"]);
			$last_local_call_time=trim($_REQUEST["last_local_call_time"]);
			$entry_date=trim($_REQUEST["entry_date"]);
			$modify_date=trim($_REQUEST["modify_date"]);
			$list_id=trim($_REQUEST["list_id"]);
 		 	
 			$sql_1="update vicidial_list set status='".$status."',called_since_last_reset='".$called_since_last_reset."',title='".$title."',first_name='".$first_name."',middle_initial='".$middle_initial."',last_name='".$last_name."',address1='".$address1."',address2='".$address2."',address3='".$address3."',city='".$city."',state='".$state."',postal_code='".$postal_code."',province='".$province."',gender='".$gender."',alt_phone='".$alt_phone."',email='".$email."',comments='".$comments."',date_of_birth='".$date_of_birth."' where lead_id='".$lead_id."' ";
   			//echo $sql_1;
   			
   			
   			
	 	
 			$sql_2="update list_".$list_id."_fields set lead_id='".$lead_id."' ";

			$rslt=mysqli_query($db_conn,"select field_name from list_fields  where list_id=".$list_id);
			
			while($con=mysqli_fetch_assoc($rslt)){//通过循环读取数据内容				
				$sql_2 .= ",".$con['field_name']." ='".trim($_REQUEST[$con['field_name']])."'";				
			}
			$sql_2 .= " where lead_id='".$lead_id."'";
			mysqli_free_result($rslt);
	 		
 		//	echo $sql_2;
 		
			if (mysqli_query($db_conn,$sql_1)&&mysqli_query($db_conn,$sql_2)){
				
				if($called_since_last_reset=="Y"){
					$del_sql="delete from vicidial_hopper where list_id='".$list_id."'";
					mysqli_query($db_conn,$del_sql);
				}
 				
				$counts="1";
				$des="修改成功!";
			}else{
				$counts="0";
				$des="修改失败，请检查相关设置重试!";
			}
			
		}else{
			$counts="0";
			$des="修改失败，请输入要修改的号码ID!";			
		}
 		 
 		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
	
 	//选择号码导入字段
 	case "lead_import_select":
	
 		$file_path=trim($_REQUEST["file_path"]);
		//echo $file_path;
		$submit_file=trim($_REQUEST["submit_file"]);
		$submit=trim($_REQUEST["submit"]); 		 
		$file_layout=trim($_REQUEST["file_layout"]);
		$OK_to_process=trim($_REQUEST["OK_to_process"]);
		$vendor_lead_code_field=trim($_REQUEST["vendor_lead_code_field"]);
		$source_id_field=trim($_REQUEST["source_id_field"]);
		$list_id_field=trim($_REQUEST["list_id_field"]);
		$phone_code_field=trim($_REQUEST["phone_code_field"]);
		$phone_number_field=trim($_REQUEST["phone_number_field"]);
		$title_field=trim($_REQUEST["title_field"]);
		$first_name_field=trim($_REQUEST["first_name_field"]);
		$middle_initial_field=trim($_REQUEST["middle_initial_field"]);
		$last_name_field=trim($_REQUEST["last_name_field"]);
		$address1_field=trim($_REQUEST["address1_field"]);
		$address2_field=trim($_REQUEST["address2_field"]);
		$address3_field=trim($_REQUEST["ddress3_field"]);
		$city_field=trim($_REQUEST["city_field"]);
		$state_field=trim($_REQUEST["state_field"]);
		$province_field=trim($_REQUEST["province_field"]);
		$postal_code_field=trim($_REQUEST["postal_code_field"]);
		$country_code_field=trim($_REQUEST["country_code_field"]);
		$gender_field=trim($_REQUEST["gender_field"]);
		$date_of_birth_field=trim($_REQUEST["date_of_birth_field"]);
		$alt_phone_field=trim($_REQUEST["alt_phone_field"]);
		$email_field=trim($_REQUEST["email_field"]);
		$security_phrase_field=trim($_REQUEST["security_phrase_field"]);
		$comments_field=trim($_REQUEST["comments_field"]);
		$rank_field=trim($_REQUEST["rank_field"]);
		$owner_field=trim($_REQUEST["owner_field"]);
		$list_id_override=trim($_REQUEST["list_id_override"]);
		$list_id_override = (preg_replace("/\D/","",$list_id_override));
 		$dupcheck=trim($_REQUEST["dupcheck"]);
		$postalgmt=trim($_REQUEST["postalgmt"]);
		$phone_code_override=trim($_REQUEST["phone_code_override"]);
		$phone_code_override = (preg_replace("/\D/","",$phone_code_override));
  		 
  		 
  		 
  		 
  		 
  		 
  		 
		if ($file_path!=""&&is_file(utf82gb($file_path))) {
			     
				$total=0; $good=0; $bad=0; $dup=0; $post=0; $phone_list='';
			 
			 	echo "目标客户清单：<span style='color:#08d;'>$list_name</span><BR>";
 
 				echo "<table border=\"0\" width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"dataTable\" >\r\n<tr height='40'>\r\n";
				echo "  <td align=\"center\" colspan='6'><a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='lead_import_insert()' name='OK_to_process_top' value='开始导入' class='inputButton'></a> <a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='reset' value=\"重新选择\" name='reset_page_top' class='inputButton'></a> <a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='reload_file()' value=\"重新上传\" name='reload_page_top' class='inputButton'></a></td>\r\n";
 				echo "  </tr><thead>\r\n";
				echo "    <tr align=\"left\" class=\"dataHead\">\r\n";
				echo "      <th align=\"right\" width=\"46%\">数据库字段</th>\r\n";
				echo "      <th >文件字段</th>\r\n";
				echo "    </tr>\r\n";
				echo "  </thead>\r\n<tbody>\r\n";
				$rslt=mysqli_query($db_conn,"select phone_number,title,first_name,middle_initial,last_name,province,city, state,address1,address2, address3,postal_code,gender,date_of_birth,alt_phone,email,vendor_lead_code,security_phrase,comments,owner from vicidial_list limit 1");
 		
			
 				 
 				$file=fopen(utf82gb($file_path),"r");
				
 				if($file){
					
					$buffer=rtrim(fgets($file,4096));
					
					$tab_count=substr_count($buffer,"\t");
					$pipe_count=substr_count($buffer,"|");
					
					if ($tab_count>$pipe_count) {
						$delimiter="\t";
					} else {
						$delimiter="|";
					}
					
					//$field_check=explode($delimiter,$buffer);
					flush();
					
					$buffer=stripslashes($buffer);
					$row=explode($delimiter,eregi_replace("[\'\"]","",$buffer));
 					$fields = mysqli_fetch_fields($rslt);
					
					for ($i=0; $i<mysqli_num_fields($rslt); $i++) {
 						
						if($i%2==0){
							$tr_class="";
						}else{
							$tr_class=" class='alt'";	
						}
						echo "  <tr ".$tr_class.">\r\n";
						echo "    <td align='right'>".ParseDataBaseFieldNameToChinese(strtoupper(eregi_replace("_"," ",$fields[$i]->name)))."：</td>\r\n";
						echo "    <td><select name='".$fields[$i]->name."_field' id='".$fields[$i]->name."_field'>\r\n";
						echo "     <option value='-1'>- 不导入数据 -</option>\r\n";
		
						for ($j=0;$j<count($row);$j++) {
							eregi_replace("\"", "", $row[$j]);
							echo " <option value='$j'>“ ".gb2utf8($row[$j])." ”</option>\r\n";
						}
						echo "    </select></td>\r\n";
						echo "  </tr>\r\n";
						 
					}
					mysqli_free_result($rslt);
					
					$aaaa="select field_name,field_label from list_fields  where list_id=".$list_id_override." order by field_id asc";
					//$aaaa="select * from list_fields where list_id=3333";
					$rslt=mysqli_query($db_conn,$aaaa);
					
					//$field_rows_count = mysqli_num_rows($rslt);
					//echo $aaaa;
					//echo " field_rows_count:".$field_rows_count;
					
					while($con=mysqli_fetch_assoc($rslt)){//通过循环读取数据内容
 					///	echo " | ";		
 					//	echo " list_id:".$con['list_id'];
 					//	echo " field_label:".$con['field_label'];
 					//	echo " field_name:".$con['field_name'];

						
						if($i%2==0){
							$tr_class="";
						}else{
							$tr_class=" class='alt'";	
						}
						
						echo "  <tr ".$tr_class.">\r\n";
						echo "    <td align='right'>".$con['field_label']."</td>\r\n";
						echo "    <td><select name='".$con['field_name']."_field' id='".$con['field_name']."_field'>\r\n";
						echo "     <option value='-1'>- 不导入数据 -</option>\r\n";
		
						for ($j=0;$j<count($row);$j++) {
							eregi_replace("\"", "", $row[$j]);
							echo " <option value='$j'>“ ".gb2utf8($row[$j])." ”</option>\r\n";
						}
						echo "    </select></td>\r\n";
						echo "  </tr>\r\n";
						
						$i++;
					}
				 
					echo "  </tbody><tfoot><tr height='40'>\r\n";
					echo "  <td align=\"center\" colspan='6'><a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='lead_import_insert()' name='OK_to_process' value='开始导入' class='inputButton'></a> <a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='reset' value=\"重新选择\" name='reset_page' class='inputButton'></a> <a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='reload_file()' value=\"重新上传\" name='reload_page' class='inputButton'></a></td>\r\n";
					echo "  </tr></tfoot>\r\n";
					echo "</table>\r\n";
					echo "  <input type='hidden' name='list_name' value='$list_name'>\r\n";
					echo "  <input type='hidden' name='dupcheck' value='$dupcheck'>\r\n";
					echo "  <input type='hidden' name='postalgmt' value='$postalgmt'>\r\n";
					echo "  <input type='hidden' name='file_path' value='$file_path'>\r\n";
					echo "  <input type='hidden' name='list_id_override' value='$list_id_override'>\r\n";
					echo "  <input type='hidden' name='phone_code_override' value='$phone_code_override'>\r\n";
					
					fclose($file);
					mysqli_free_result($rslt);
				
			}else{
				echo "<script>alert('文件解析失败，请检查上传的号码文件格式是否正确!');reload_file();</script>";
				die();
			}
			
		}else{
			echo "<script>alert('文件上传失败，未找到上传文件，请检查重试!');reload_file();</script>";
			die();
		}
   
 	break;
	
	//执行导入
	case "lead_import_insert":
		
 		$file_path=trim($_REQUEST["file_path"]);
		//echo $file_path."21313";
		//exit();
		$submit_file=trim($_REQUEST["submit_file"]);
		$submit=trim($_REQUEST["submit"]);
 		 
		$file_layout=trim($_REQUEST["file_layout"]);
		$OK_to_process=trim($_REQUEST["OK_to_process"]);
		$vendor_lead_code_field=trim($_REQUEST["vendor_lead_code_field"]);
		$source_id_field=trim($_REQUEST["source_id_field"]);
		$list_id_field=trim($_REQUEST["list_id_field"]);
		$phone_code_field=trim($_REQUEST["phone_code_field"]);
		$phone_number_field=trim($_REQUEST["phone_number_field"]);
		$title_field=trim($_REQUEST["title_field"]);
		$first_name_field=trim($_REQUEST["first_name_field"]);
		$middle_initial_field=trim($_REQUEST["middle_initial_field"]);
		$last_name_field=trim($_REQUEST["last_name_field"]);
		$address1_field=trim($_REQUEST["address1_field"]);
		$address2_field=trim($_REQUEST["address2_field"]);
		$address3_field=trim($_REQUEST["address3_field"]);
		$city_field=trim($_REQUEST["city_field"]);
		$state_field=trim($_REQUEST["state_field"]);
		$province_field=trim($_REQUEST["province_field"]);
		$postal_code_field=trim($_REQUEST["postal_code_field"]);
		$country_code_field=trim($_REQUEST["country_code_field"]);
		$gender_field=trim($_REQUEST["gender_field"]);
		$date_of_birth_field=trim($_REQUEST["date_of_birth_field"]);
		$alt_phone_field=trim($_REQUEST["alt_phone_field"]);
		$email_field=trim($_REQUEST["email_field"]);
		$security_phrase_field=trim($_REQUEST["security_phrase_field"]);
		$comments_field=trim($_REQUEST["comments_field"]);
		$rank_field=trim($_REQUEST["rank_field"]);
		$owner_field=trim($_REQUEST["owner_field"]);
		$list_id_override=trim($_REQUEST["list_id_override"]);
		$list_id_override = (preg_replace("/\D/","",$list_id_override));
 		$dupcheck=trim($_REQUEST["dupcheck"]);
		$postalgmt=trim($_REQUEST["postalgmt"]);
		$phone_code_override=trim($_REQUEST["phone_code_override"]);
		$phone_code_override = (preg_replace("/\D/","",$phone_code_override));
		
//add		

		$have_fields = "0";
		$rslt=mysqli_query($db_conn,"select field_name from list_fields  where list_id=".$list_id_override);
		
		$field_arr=array();
		while($con=mysqli_fetch_assoc($rslt)){//通过循环读取数据内容
			
			$value=trim($_REQUEST[$con['field_name']."_field"]);
			$value = (preg_replace("/\D/","",$value));
		
			$list=array("field"=>$con['field_name'],"key"=>$value, "value"=>"");
			array_push($field_arr,$list);
			$have_fields="1";
		}
		mysqli_free_result($rslt);

//end
 		
		$total=1; $good=0; $bad=0; $dup=0; $post=0; $phone_list='';$dcity='';
 		
  		if(is_file(utf82gb($file_path))){
			$file=fopen(utf82gb($file_path), "r");
			
			$buffer=fgets($file, 4096);
			$tab_count=substr_count($buffer, "\t");
			$pipe_count=substr_count($buffer, "|");
	
			if ($tab_count>$pipe_count) {
				$delimiter="\t";
			}else{
				$delimiter="|";
			}
			
			$field_check=explode($delimiter, $buffer);
	 
			if (count($field_check)>=1){
				flush();
				//$file=fopen("$lead_file","r");
				
				echo "目标客户清单：<span style='color:#08d;'>$list_name</span>&nbsp;&nbsp;&nbsp;&nbsp;<span id='insert_result_des'></span>&nbsp;&nbsp;&nbsp;&nbsp;<span id='result_top_des'></span><BR>";
				
				echo "<table border=\"0\" width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"dataTable\" >\r\n";
				echo "  <thead>\r\n";
				echo "    <tr align=\"left\" class=\"dataHead\" width=\"30%\">\r\n";
				echo "      <th >编号</th>\r\n";
				echo "      <th >号码</th>\r\n";
				echo "      <th >客户清单</th>\r\n";
				echo "      <th >结果描述</th>\r\n";
				echo "    </tr>\r\n";
				echo "  </thead>\r\n<tbody>\r\n";
 								
				while (!feof($file)) {
					$record++;
					$buffer=rtrim(fgets($file, 4096));
					$buffer=stripslashes($buffer);
	
					if (strlen($buffer)>0) {
						//$buffer=str_replace(array("\r\n", "\r", "\n"), "", $buffer);
						$row=explode($delimiter, eregi_replace("[\'\"]", "", $buffer));						
	
						$pulldate=date("Y-m-d H:i:s");
						$entry_date =			"$pulldate";
						$modify_date =			"";
						$status =				"NEW";
						$user ="";
						$vendor_lead_code =		gb2utf8($row[$vendor_lead_code_field]);
						$source_code =			gb2utf8($row[$source_id_field]);
						$source_id=$source_code;
						$list_id =				$row[$list_id_field];
						$gmt_offset =			'0';
						$called_since_last_reset='N';
						$phone_code =			eregi_replace("[^0-9]", "", $row[$phone_code_field]);
						$phone_number =			eregi_replace("[^0-9]","", $row[$phone_number_field]);
						$USarea = 			    substr($phone_number, 0, 3);
						$title =				gb2utf8(trim($row[$title_field]));
						$first_name =			gb2utf8(trim($row[$first_name_field]));
						$middle_initial =		gb2utf8(trim($row[$middle_initial_field]));
						$last_name =			gb2utf8(trim($row[$last_name_field]));
						$address1 =				gb2utf8(trim($row[$address1_field]));
						$address2 =				gb2utf8(trim($row[$address2_field]));
						$address3 =				gb2utf8(trim($row[$address3_field]));
						$city =				    gb2utf8(trim($row[$city_field]));
						$state =				gb2utf8(trim($row[$state_field]));
						$province =				gb2utf8(trim($row[$province_field]));
						$postal_code =			gb2utf8(trim($row[$postal_code_field]));
						$country_code =			gb2utf8(trim($row[$country_code_field]));
						$gender =				gb2utf8(trim($row[$gender_field]));
						$date_of_birth =		gb2utf8(trim($row[$date_of_birth_field]));
						$alt_phone =			gb2utf8(trim(eregi_replace("[^0-9]","",$row[$alt_phone_field])));
						$email =				gb2utf8(trim($row[$email_field]));
						$security_phrase =		gb2utf8(trim($row[$security_phrase_field]));
						$comments =				gb2utf8(trim($row[$comments_field]));
						$rank =					gb2utf8(trim($row[$rank_field]));
						$owner =				gb2utf8(trim($row[$owner_field]));
					 
						$value_arr = array();
						
						foreach($field_arr as $list){
							
							$list['value'] = gb2utf8(trim($row[$list['key']]));
														
							array_push($value_arr,$list);
							//echo $list['field'].":".$list['key'].":".$list['value']."|";
						}
						//echo "   "."<br/>";
						
						if (strlen($list_id_override)>0){
							$list_id = $list_id_override;
						}
						
						if (strlen($phone_code_override)>0){
							$phone_code = $phone_code_override;
						}
						
						$bad_type="1";
 						$dup_lead=0;
						//在同一业务活动中校验
						if (eregi("DUPCAMP",$dupcheck)){
							$dup_lead=0;
							$dup_lists='';
							$sql="select campaign_id from vicidial_lists where list_id='$list_id';";
							$rslt=mysqli_query($db_conn,$sql);
							$ci_recs = mysqli_num_rows($rslt);
							
							if ($ci_recs > 0){
								$row=mysqli_fetch_row($rslt);
								$dup_camp =	$row[0];
	
								$sql="select list_id from vicidial_lists where campaign_id='$dup_camp';";
								$rslt_1=mysqli_query($db_conn,$sql);
								$li_recs = mysqli_num_rows($rslt_1);
								
								if ($li_recs > 0){
									$L=0;
									while ($li_recs > $L){
										$row=mysqli_fetch_row($rslt_1);
										$dup_lists .=	"'$row[0]',";
										$L++;
									}
									
									$dup_lists = eregi_replace(",$",'',$dup_lists);
									$sql="select list_id from vicidial_list where phone_number='$phone_number' and list_id IN($dup_lists) limit 1;";
									$rslt_2=mysqli_query($db_conn,$sql);
									$pc_recs = mysqli_num_rows($rslt_2);
									if ($pc_recs > 0){
										$dup_lead=1;
										$row=mysqli_fetch_row($rslt_2);
										$dup_lead_list =$row[0];
									}
									if ($dup_lead < 1){
										if (eregi("$phone_number$US$list_id",$phone_list)){
											$dup_lead++;
											$dup++;
										}
									}
									mysqli_free_result($rslt_2);
									
								}
								mysqli_free_result($rslt_1);
								
							}
							mysqli_free_result($rslt);
						}
	
						//在全系统中校验
						if (eregi("DUPSYS",$dupcheck)){
							$dup_lead=0;
							$sql="select list_id from vicidial_list where phone_number='$phone_number';";
							$rslt=mysqli_query($db_conn,$sql);
							$pc_recs = mysqli_num_rows($rslt);
							if ($pc_recs > 0){
								$dup_lead=1;
								$row=mysqli_fetch_row($rslt);
								$dup_lead_list =	$row[0];
							}
							
							if ($dup_lead < 1){
								if (eregi("$phone_number$US$list_id",$phone_list)){
									$dup_lead++;
									$dup++;
								}
							}
							
							mysqli_free_result($rslt);
						}
	
						//在同一客户清单中校验
						if (eregi("DUPLIST",$dupcheck)){
							$dup_lead=0;
							$sql="select count(*) from vicidial_list where phone_number='$phone_number' and list_id='$list_id';";
							$rslt=mysqli_query($db_conn,$sql);
							$pc_recs = mysqli_num_rows($rslt);
							
							if ($pc_recs > 0){
								$row=mysqli_fetch_row($rslt);
								$dup_lead =			$row[0];
								$dup_lead_list =	$list_id;
							}
							
							if ($dup_lead < 1){
								if (eregi("$phone_number$US$list_id",$phone_list)){
									$dup_lead++;
									$dup++;
								}
							}
							mysqli_free_result($rslt);
						}
	
						//在同一客户清单中(标题、备用号)校验
						if (eregi("DUPTITLEALTPHONELIST",$dupcheck)){
							$dup_lead=0;
							$sql="select count(*) from vicidial_list where title='$title' and alt_phone='$alt_phone' and list_id='$list_id';";
							$rslt=mysqli_query($db_conn,$sql);
							$pc_recs = mysqli_num_rows($rslt);
							
							if ($pc_recs > 0){
								$row=mysqli_fetch_row($rslt);
								$dup_lead =			$row[0];
								$dup_lead_list =	$list_id;
							}
							
							if ($dup_lead < 1){
								if (eregi("$alt_phone$title$US$list_id",$phone_list)){
									$dup_lead++; $dup++;
								}
							}
							mysqli_free_result($rslt);
						}
	
						//在全系统中(标题、备用号)校验
						if (eregi("DUPTITLEALTPHONESYS",$dupcheck)){
							
							$dup_lead=0;
							$sql="select list_id from vicidial_list where title='$title' and alt_phone='$alt_phone';";
							$rslt=mysqli_query($db_conn,$sql);
							$pc_recs = mysqli_num_rows($rslt);
							
							if ($pc_recs > 0){
								$dup_lead=1;
								$row=mysqli_fetch_row($rslt);
								$dup_lead_list =	$row[0];
							}
							if ($dup_lead < 1){
								if (eregi("$alt_phone$title$US$list_id",$phone_list)){
									$dup_lead++;
									$dup++;
								}
							}
							mysqli_free_result($rslt);
						}
 						
						//过滤黑名单
						$sql_dnc="select phone_number from vicidial_dnc where  phone_number='".$phone_number."' limit 1 union all select phone_number from vicidial_campaign_dnc where campaign_id=(select campaign_id from vicidial_lists where list_id='".$list_id."') and phone_number='".$phone_number."' limit 1;";
						$rslt_dnc=mysqli_query($db_conn,$sql_dnc);
						$pc_recs_dnc = mysqli_num_rows($rslt_dnc);
						if ($pc_recs_dnc > 0){
							$dup_lead=1;
							$row_dnc=mysqli_fetch_row($rslt_dnc);
							$dup_lead_list =$row[0];
							$bad_type="2";
						}
						
						mysqli_free_result($rslt_dnc);
						
						//同文件内过滤
						/*if (!eregi("NONE",$dupcheck)){
							
							if(empty($phone_numbers[$phone_number])){
								$phone_numbers[$phone_number] = 1;
							}else{
								$dup_lead=1;
								$bad_type="1";
							}
						}*/
						
						if ((strlen($phone_number)>5) and ($dup_lead<1) ){
							
							if (strlen($phone_code)<1) {
								$phone_code = '1';
							}
	
							if (eregi("TITLEALTPHONE",$dupcheck)){
								$phone_list .= "$alt_phone$title$US$list_id|";
								
							}else{
								$phone_list .= "$phone_number$US$list_id|";
							}
							
							//时区	
							$gmt_offset = "8";
							if($have_fields=="1"){
									$sql_insert = "INSERT INTO vicidial_list (entry_date,modify_date,status,user,vendor_lead_code,source_id,list_id,gmt_offset_now,called_since_last_reset,phone_code,phone_number,title,first_name,middle_initial,last_name,address1,address2,address3,city,state,province,postal_code,country_code,gender,date_of_birth,alt_phone,email,security_phrase,comments,called_count,last_local_call_time,rank,owner) values('$entry_date','$modify_date','$status','$user','$vendor_lead_code','$source_id','$list_id','$gmt_offset','$called_since_last_reset','$phone_code','$phone_number','$title','$first_name','$middle_initial','$last_name','$address1','$address2','$address3','$city','$state','$province','$postal_code','$country_code','$gender','$date_of_birth','$alt_phone','$email','$security_phrase','$comments',0,'2008-01-01 00:00:00','$rank','$owner');";
									mysqli_query($db_conn,$sql_insert);
									
									$lead_id = mysqli_insert_id($db_conn);
									$sql_insert_fields = "INSERT INTO list_".$list_id."_fields (lead_id";
									$sql_insert_values = " values('".$lead_id."'";
									foreach($value_arr as $list){
										$sql_insert_fields .= ",".$list['field'];
										$sql_insert_values .= ", '".$list['value']."'";
					
									}
									$sql_insert_fields .= ")";
									$sql_insert_values .= ")";
									//echo $sql_insert_fields.$sql_insert_values."<br/>";
									mysqli_query($db_conn,$sql_insert_fields.$sql_insert_values);
									
									$sql_insert_fields ='';
									$sql_insert_values ='';

		
							}else{
							
								if ($multi_insert_counter > 8) {
									 
									$sql_insert = "INSERT INTO vicidial_list (entry_date,modify_date,status,user,vendor_lead_code,source_id,list_id,gmt_offset_now,called_since_last_reset,phone_code,phone_number,title,first_name,middle_initial,last_name,address1,address2,address3,city,state,province,postal_code,country_code,gender,date_of_birth,alt_phone,email,security_phrase,comments,called_count,last_local_call_time,rank,owner) values$multistmt('$entry_date','$modify_date','$status','$user','$vendor_lead_code','$source_id','$list_id','$gmt_offset','$called_since_last_reset','$phone_code','$phone_number','$title','$first_name','$middle_initial','$last_name','$address1','$address2','$address3','$city','$state','$province','$postal_code','$country_code','$gender','$date_of_birth','$alt_phone','$email','$security_phrase','$comments',0,'2008-01-01 00:00:00','$rank','$owner');";
									
									//echo $sql_insert."<br/>";
									mysqli_query($db_conn,$sql_insert);
									
									$multistmt='';
									$multi_insert_counter=0;
		
								} else {
									$multistmt .= "('$entry_date','$modify_date','$status','$user','$vendor_lead_code','$source_id','$list_id','$gmt_offset','$called_since_last_reset','$phone_code','$phone_number','$title','$first_name','$middle_initial','$last_name','$address1','$address2','$address3','$city','$state','$province','$postal_code','$country_code','$gender','$date_of_birth','$alt_phone','$email','$security_phrase','$comments',0,'2008-01-01 00:00:00','$rank','$owner'),";
									
									$multi_insert_counter++;
								}
							}
							
							$good++;
							
						} else {
							
							$bad++;
							
							if($bad%2==1){
								$tr_class="";
							}else{
								$tr_class=" class='alt'";	
							}
							
							if(strlen($phone_number)<6){
								$bad_type="3";
							}
							
							if($bad_type=="1"){
								$bad_des="号码已存在";
							}else if($bad_type=="2"){
								$bad_des="黑名单号码";
							}else{
								$bad_des="号码为空或位数不足6位";
							}
  							
							$row_list.="$total,'$phone_number,$list_name,$bad_des,\n";
  							
							if ($bad < 31) {
								echo "<tr ".$tr_class.">\n";
								echo "  <td >".$total."</td>\n";
								echo "  <td ><span class='red'>".$phone_number."</span></td>\n";
 								echo "  <td >".$list_name."</td>\n";
								echo "  <td >".$bad_des."</td>\n";
								echo "  </td>\n";
								echo "</tr>\n";
							}
							
						}
						$total++;
						
						if ($total%100==0) {
							usleep(500);
							flush();
						}
					}
				}
				
				if ($multi_insert_counter!=0) {
					
					$sql="INSERT INTO vicidial_list (entry_date,modify_date,status,user,vendor_lead_code,source_id,list_id,gmt_offset_now,called_since_last_reset,phone_code,phone_number,title,first_name,middle_initial,last_name,address1,address2,address3,city,state,province,postal_code,country_code,gender,date_of_birth,alt_phone,email,security_phrase,comments,called_count,last_local_call_time,rank,owner) values ".substr($multistmt, 0, -1).";";
					//echo $sql;
					mysqli_query($db_conn,$sql);
					
				}
				
				if($bad!="0"){
					$file_path=excel_file("导入失败详单_","csv");
					$fp=fopen($file_path[2],"w");
					 
					$filed_list="编号,号码,业务活动,结果描述,\n";
					 
					fwrite($fp,utf82gb($filed_list));
 					fwrite($fp,utf82gb($row_list));
					 
					fclose($fp);
					
					$bad_detail_file="&nbsp;&nbsp;&nbsp;<a href='".gb2utf8(str_replace("../../data/","/data/",$file_path[1])).gb2utf8($file_path[0])."' target='_blank' title='下载查看导入失败号码详单'>下载查看</a>";
					
				}
				
 				unset($phone_numbers);
				unset($row_list);
				
				$insert_result_des="全部：<span class='blue_tip font_w'>".($total-1)."</span>&nbsp;&nbsp;导入成功：<span class='green font_w'>$good</span>&nbsp;&nbsp;导入失败：<span class='red font_w'>$bad</span>".$bad_detail_file;
				
				echo "  </tbody><tfoot><tr height='40'>\r\n";
				echo "  <td colspan='6'>".$insert_result_des."&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='reload_file()' value=\"继续导入\" name='reload_page' class='inputButton'></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' class='zInputBtn' hidefocus='true' tabindex='-1'><input type='button' onClick='javascript:Dialog.close()' value=\"关 闭\" name='reload_page' class='inputButton'></a></td>\r\n";
				echo "  </tr></tfoot>\r\n";
				echo "</table>\r\n";
				
				echo "<script>";
					echo "$('#insert_result_des').html(\"".$insert_result_des."\");";
					if($bad>0){
						if($bad<30){
							$bads=$bad;
						}else{
							$bads=30;
						}
						echo "$('#result_top_des').html('失败详单：<span class=\"gray\">前 <span class=\"green\">$bads</span> 行</span> ');";
					}
				
				echo "</script>";
				
				//$log_sql="INSERT INTO vicidial_admin_log set event_date='$SQLdate',user='".$_SESSION["user"]."', ip_address='$ip', event_section='LISTS', event_type='LOAD', record_id='$list_id', event_code='导入客户清单 ".$insert_result_des." ', event_sql=\"$SQL_log\", event_notes='".utf82gb($file_path)."'";
				//echo $log_sql;
				//mysqli_query($log_sql, $db_conn);
			}  
			fclose($file);
		
		}else{
			echo "<script>alert('文件解析失败，请检查上传的号码文件格式是否正确!');reload_file();</script>";
			die();
	}
	break;
	
	
    //黑名单列表
	case "get_dnc_lists":
  		 
 		if($phone_number<>""){
 			$sql1=" and datas.phone_number like '%".$phone_number."%'";
		} 
 		
		if($campaign_id<>""){
 			$sql2=" and datas.campaign_id='".$campaign_id."'";		
		} 
		
 		$wheres=$sql1.$sql2;
		//获取记录集个数
		if($do_actions=="count"){
			
			$sql="select count(*) from (select phone_number,campaign_id from vicidial_campaign_dnc union ALL select phone_number,'System' as campaign_id from vicidial_dnc )datas left join vicidial_campaigns b on datas.campaign_id=b.campaign_id where 1=1 ".$wheres." ";
			//echo $sql;
			$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
			$counts=$rows[0];
			if(!$counts){$counts="0";}
			$des="d";
			$list_arr=array('id'=>'none');
			
		}else if($do_actions=="list"){
		
			$offset=($pages-1)*$pagesize;
			
			$sql="select phone_number,datas.campaign_id,case when datas.campaign_id='system' then '系统黑名单' else ifnull(concat(b.campaign_name,' [',datas.campaign_id,']'),concat('未知业务 [',datas.campaign_id,']')) end as campaign_name from (select phone_number,campaign_id from vicidial_campaign_dnc union ALL select phone_number,'system' as campaign_id from vicidial_dnc )datas left join vicidial_campaigns b on datas.campaign_id=b.campaign_id where 1=1 ".$wheres." ".$sort_sql." limit ".$offset.",".$pagesize." ";
			
 			//echo $sql;
			
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			$list_arr=array();
			 
			if ($row_counts_list!=0) {
				while($rs= mysqli_fetch_array($rows)){ 
				 
					$list=array("phone_number"=>$rs['phone_number'],"campaign_id"=>$rs['campaign_id'],"campaign_name"=>$rs['campaign_name']);
					 
					array_push($list_arr,$list);
				}
				$counts="1";
				$des="获取成功!";
			}else {
				$counts="0";
				$des="未找到符合条件的数据!";
				$list_arr=array('id'=>'none');
			}
  			
		} 
   	 
		mysqli_free_result($rows);
	
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
	 
	break;

	//删除黑名单
  	case "del_dnc_list":
  		 
		if($cid!=""){
		 
 			$phones_ary = explode(",",$cid); 
 		 
			foreach($phones_ary as $phone_list){ 
			
  				$phone_list_ary = explode("|",$phone_list);
 				
				/*while(1){
					
 					mysqli_query("DELETE FROM logs WHERE log_date <= '2009-11-01' LIMIT 1000");
				 
					if (mysqli_affected_rows() == 0) {
						break;
					}
					usleep(50000);
				}*/
				
				if($phone_list_ary[1]=="system"){
					$sql="delete from vicidial_dnc where phone_number='".$phone_list_ary[0]."' ";
				}else{
					$sql="delete from vicidial_campaign_dnc where phone_number='".$phone_list_ary[0]."' and  campaign_id='".$phone_list_ary[1]."' ";
				}
				
				if (mysqli_query($db_conn,$sql)){
					$result="1";
				}else{
					$result="0";
				}
				
				
 			} 						
   		 
			if ($result=="1"){
				$counts="1";
				$des="删除成功!";
			}else{
				$counts="0";
				$des="删除失败，请检查相关设置重试!";
			}
			
		}else{
			$counts="0";
			$des="删除失败，请输入要删除的行!";			
		}
 		 
 		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
	
	//添加黑名单
  	case "add_dnc_list":
  		 
		if($phones!=""){
 			 
 			$phones_ary = explode("\n",$phones); 
			$pcount1=count($phones_ary);
			
			$phones_ary2=array_unique($phones_ary);
 			$pcount2=count($phones_ary2);
			$list_arr=array();
			
			$phone_bad=0;
			if($pcount2>0){
				
 				foreach($phones_ary2 as $phone){
					$phone_old=$phone;
					$phone=trim(eregi_replace("[^0-9]","",$phone));
					
					if($phone<>""&&preg_match("/^\d*$/",$phone)){
 						
 						if($campaign_id=="system"){
							
							$sql="select count(*) from vicidial_dnc where phone_number='".$phone."';";
							$rs=mysqli_query($db_conn,$sql);
							$row=mysqli_fetch_row($rs);
							unset($sql);
							if ($row[0] > 0){
								 
								array_push($list_arr,array("phone"=>"<em class='blue_tip'>".$phone_old."</em> -- <em class='red'>号码已存在</em>"));
								$phone_bad++;
							}else{
 								$phone_list.="('".$phone."'),";
 							}
							mysqli_free_result($rs);
							
						}else{
							
							$sql="select count(*) from vicidial_campaign_dnc where phone_number='".$phone."' and campaign_id='".$campaign_id."' ;";
							$rs=mysqli_query($db_conn,$sql);
							$row=mysqli_fetch_row($rs);
							unset($sql);
							if ($row[0] > 0){
								 
								array_push($list_arr,array("phone"=>"<em class='blue_tip'>".$phone_old."</em> -- <em class='red'>号码已存在</em>"));
								$phone_bad++;
							}else{
 								$phone_list.="('".$phone."','".$campaign_id."'),";
 							}
							mysqli_free_result($rs);
 							
						}
 						
					}else{
						 
						array_push($list_arr,array("phone"=>"<em class='blue_tip'>".$phone_old."</em> -- <em class='red'>号码错误</em>"));
						$phone_bad++;
					} 
				}
				
				if($campaign_id=="system"){
					$in_sql="INSERT IGNORE INTO vicidial_dnc(phone_number) values ".substr($phone_list,0,-1)." ";	
				}else{
					$in_sql="INSERT IGNORE INTO vicidial_campaign_dnc(phone_number,campaign_id) values ".substr($phone_list,0,-1)." ";
				}
				
				$pcount2=$pcount2-$phone_bad;
 				 
				if($phone_list!=""&&mysqli_query($db_conn,$in_sql)){
					$result="1";
					$counts="1";
					$des="添加黑名单完成!\n\n号码总个数：".$pcount1."\n添加成功：".$pcount2."\n添加失败：".$phone_bad."";
				}else{
					$result="0";
					$counts="0";
					$des="添加黑名单失败!系统执行发生异常错误，请删减一半左右号码数，重新提交!";
				}
				 
 			 
 			}else{
				$counts="0";
				$des="添加黑名单失败，电话号码不能为空!";
			}
 			
		}else{
			$counts="0";
			$des="添加黑名单失败，请输入要添加的号码!";			
		}
  		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"datalist\":".json_encode($list_arr).",";
		$json_data.="\"phone_bad\":".json_encode($phone_bad).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
	
    //筛选规则列表
	case "get_filter_lists":
  		 
		if($lead_filter_id<>""){
 			
			if(strpos($lead_filter_id,",")>-1){
				$lead_filter_id=str_replace(",","','",$lead_filter_id);
				$lead_filter_id="'".$lead_filter_id."'";
				$sql_list_id=" in(".$lead_filter_id.") ";
			}else{
				$sql_list_id=" like '%".$lead_filter_id."%' ";
			}
			$sql1=" and a.lead_filter_id ".$sql_list_id."";
		}
		
		if($lead_filter_name<>""){
 			$sql2=" and a.lead_filter_name like '%".$lead_filter_name."%'";
		} 
 		
		if($lead_filter_comments<>""){
			$sql3=" and a.lead_filter_comments like '%".$lead_filter_comments."%' ";	
		} 
		
		$wheres=$sql1.$sql2.$sql3.$sql4.$sql5;
		//获取记录集个数
		if($do_actions=="count"){
			
			$sql="select count(*) from vicidial_lead_filters a left join (select lead_filter_id,count(*) as counts from data_lead_filter group by lead_filter_id) b on a.lead_filter_id=b.lead_filter_id where 1=1 ".$wheres." ";
			//echo $sql;
			$rows=mysqli_fetch_row(mysqli_query($db_conn,$sql));
			$counts=$rows[0];
			if(!$counts){$counts="0";}
			$des="d";
			$list_arr=array('id'=>'none');
			
		}else if($do_actions=="list"){
		
			$offset=($pages-1)*$pagesize;
			
			$sql="select a.lead_filter_id,lead_filter_name,lead_filter_comments,ifnull(b.counts,0) as counts from vicidial_lead_filters a left join (select lead_filter_id,count(*) as counts from data_lead_filter group by lead_filter_id) b on a.lead_filter_id=b.lead_filter_id where 1=1 ".$wheres." ".$sort_sql." limit ".$offset.",".$pagesize." ";
			
 			//echo $sql;
			
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			$list_arr=array();
			 
			if ($row_counts_list!=0) {
				while($rs= mysqli_fetch_array($rows)){ 
				 
					$list=array("lead_filter_id"=>$rs['lead_filter_id'],"lead_filter_name"=>$rs['lead_filter_name'],"lead_filter_comments"=>$rs['lead_filter_comments'],"counts"=>$rs['counts']);
					 
					array_push($list_arr,$list);
				}
				$counts="1";
				$des="获取成功!";
			}else {
				$counts="0";
				$des="未找到符合条件的数据!";
				$list_arr=array('id'=>'none');
			}
  			
		} 
   	 
		mysqli_free_result($rows);
	
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
	 
	break;
  	
	//客户清单字段
	case "get_field_list":
	 
  		$list_arr=array();
 			
		foreach($filter_list_ary as $field_id =>$field_name ){
 		 
 			$list=array("field_name"=>"$field_name","field_id"=>"$field_id");
 			array_push($list_arr,$list);
		}
		$counts="1";
		$des="success";
		$json_data="{";
		$json_data.="\"counts\":".$counts.",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
    		
	break;
	
	//验证筛选规则是否存在
	case "check_lead_filter_id":
 		
		if($lead_filter_id!=""){
			$sql="select lead_filter_id from vicidial_lead_filters where lead_filter_id='".$lead_filter_id."' limit 0,1";
			
			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			if ($row_counts_list!=0) {
 				 
				$counts="0";
				$des="该过滤规则ID已存在，请检查更换其他!";
			}else {
				$counts="1";
				$des="";
			}
			
			mysqli_free_result($rows);
			
		}else{
			$counts="-1";
			$des="未输入过滤规则ID!";
		}
		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
	
  	//添加、修改晒选规则
	case "lead_filter_set":
  		
		if($do_actions=="add"){
			
			$sql="select lead_filter_id from vicidial_lead_filters where lead_filter_id='".$lead_filter_id."' limit 0,1";
 			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			mysqli_free_result($rows);
			
			if ($row_counts_list!=0) {
 				$counts="0";
				$des="该过滤规则ID已存在，请检查更换其他!";
				
			}else {
   			
				 $sql="insert into vicidial_lead_filters (lead_filter_id,lead_filter_name,lead_filter_comments,lead_filter_sql)
				  select '".$lead_filter_id."','".$lead_filter_name."','".$lead_filter_comments."','".mysqli_real_escape_string($db_conn,$lead_filter_sql)."' from (select '".$lead_filter_id."' as lead_filter_id ) datas where not exists(select lead_filter_id from vicidial_lead_filters a where a.lead_filter_id=datas.lead_filter_id );";
  			 	//echo $sql;
				if(mysqli_query($db_conn,$sql)){
 					 
					$lead_filter_fields=explode("|",$lead_filter_field);
 
					foreach($lead_filter_fields as $filter_fields){
						$value_list=explode("#_#",$filter_fields);
 						 
						$filter_field=$value_list[0];
						$filter_term=$value_list[1];
 						$filter_value=$value_list[2];
						$filter_if_begin=$value_list[3];
						$filter_if_end=$value_list[4];
						$filter_if=$value_list[5];
 						 
						$in_sql="insert into data_lead_filter(lead_filter_id,filter_field,filter_term,filter_value,filter_if_begin,filter_if_end,filter_if) select '".$lead_filter_id."','".$filter_field."','".$filter_term."','".mysqli_real_escape_string($db_conn,$filter_value)."','".mysqli_real_escape_string($db_conn,$filter_if_begin)."','".mysqli_real_escape_string($db_conn,$filter_if_end)."','".$filter_if."'";
						//echo $in_sql."<br>";
 						mysqli_query($db_conn,$in_sql);
 					}
 					
   					$counts="1";
					$des="新建过滤规则成功!";
  				}else{
					$counts="0";
					$des="新建过滤规则失败，请检查重试!";
 				}
			}
 			
		}else if($do_actions=="update"){			
			if($lead_filter_id!=""){
 				  
				$sql="update vicidial_lead_filters set lead_filter_name='".$lead_filter_name."',lead_filter_comments='".$lead_filter_comments."',lead_filter_sql='".mysqli_real_escape_string($db_conn,$lead_filter_sql)."' where lead_filter_id='".$lead_filter_id."';";
				//echo $sql;
				if(mysqli_query($db_conn,$sql)){
  					
					$del_sql="delete from data_lead_filter where lead_filter_id='".$lead_filter_id."'";
					mysqli_query($db_conn,$del_sql);
					
					$lead_filter_fields=explode("|",$lead_filter_field);
 
					foreach($lead_filter_fields as $filter_fields){
						$value_list=explode("#_#",$filter_fields);
 						 
						$filter_field=$value_list[0];
						$filter_term=$value_list[1];
 						$filter_value=$value_list[2];
						$filter_if_begin=$value_list[3];
						$filter_if_end=$value_list[4];
						$filter_if=$value_list[5];
 						 
						$in_sql="insert into data_lead_filter(lead_filter_id,filter_field,filter_term,filter_value,filter_if_begin,filter_if_end,filter_if) select '".$lead_filter_id."','".$filter_field."','".$filter_term."','".mysqli_real_escape_string($db_conn,$filter_value)."','".mysqli_real_escape_string($db_conn,$filter_if_begin)."','".mysqli_real_escape_string($db_conn,$filter_if_end)."','".$filter_if."'";
						//echo $in_sql."<br>";
 						mysqli_query($db_conn,$in_sql);
 					}
			 
 					$counts="1";
					$des="修改成功!";
				 
 				}else{
					$counts="0";
					$des="修改失败，请检查相关设置重试!";
				 
				}
				
 			}else{
				$counts="0";
				$des="修改失败，过滤规则ID不存在!";
			}
						
		} 
 		//echo $sql;
  		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
  		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
	
	break;
	
	//删除筛选规则
  	case "del_lead_filter":
 		
		if($cid!=""){
			
			if(strpos($cid,",")>-1){
				$cid=str_replace(",","','",$cid);
				$cid="'".$cid."'";
				$where_sql=" in(".$cid.") ";
			}else{
				$where_sql=" ='".$cid."' ";
			}
 		
			//删除过滤规则
			$sql_1="delete from vicidial_lead_filters where lead_filter_id ".$where_sql." ";
			
			//删除规则条件
			$sql_2="delete from data_lead_filter where lead_filter_id ".$where_sql." ";
			
 			
			if (mysqli_query($db_conn,$sql_1)&&mysqli_query($db_conn,$sql_2)){
				$counts="1";
				$des="删除成功!";
			}else{
				$counts="0";
				$des="删除失败，请检查相关设置重试!";
			}
			
		}else{
			$counts="0";
			$des="删除失败，请输入要删除的行!";			
		}
 		 
 		
 		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
  		$json_data.="}";
		
		echo $json_data;
		
	break;
	
	//或许过滤规则条件列表
	case "get_filter_field_list":
	
		$sql="select id,filter_field,filter_term,filter_value,filter_if_begin,filter_if_end,filter_if from data_lead_filter where lead_filter_id='".$lead_filter_id."' ";
  		
		$rows=mysqli_query($db_conn,$sql);
		$row_counts_list=mysqli_num_rows($rows);
		
		$list_arr=array();
		 
		if ($row_counts_list!=0) {
			while($rs= mysqli_fetch_array($rows)){ 
			 
				$list=array("id"=>$rs['id'],"filter_field"=>$rs['filter_field'],"filter_term"=>$rs['filter_term'],"filter_value"=>$rs['filter_value'],"filter_if_begin"=>$rs['filter_if_begin'],"filter_if_end"=>$rs['filter_if_end'],"filter_if"=>$rs['filter_if']);
				 
				array_push($list_arr,$list);
			}
			$counts="1";
			$des="获取成功!";
		}else {
			$counts="0";
			$des="未找到符合条件的数据!";
			$list_arr=array('id'=>'none');
		}
     	 
		mysqli_free_result($rows);
	
		$json_data="{";
		$json_data.="\"counts\":".json_encode($counts).",";
		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
		$json_data.="}";
		
		echo $json_data;
	 
	break;
	
	//或许过滤规则条件列表
	case "test_filter":
		
		$sql="SELECT dial_statuses,local_call_time,lead_filter_id,drop_lockout_time from vicidial_campaigns where campaign_id='".$campaign_id."';";
		
		$rslt=mysqli_query($db_conn,$sql);
		$row=mysqli_fetch_row($rslt);
		$dial_statuses =		$row[0];
		$local_call_time =		$row[1];
		$drop_lockout_time =	$row[3];
		if ($lead_filter_id==''){
			$lead_filter_id =	$row[2];
			if ($lead_filter_id=='') {
				$lead_filter_id='NONE';
			}
		}
		mysqli_free_result($rslt);
		
		$sql="SELECT list_id,active,list_name from vicidial_lists where campaign_id='".$campaign_id."'";
		$rslt=mysqli_query($db_conn,$sql);
		$lists_to_print = mysqli_num_rows($rslt);
		$camp_lists='';
		$o=0;
		while ($lists_to_print > $o){
			$rowx=mysqli_fetch_row($rslt);
			$o++;
			if (ereg("Y", $rowx[1])) {$camp_lists .= "'$rowx[0]',";}
		}
		$camp_lists = eregi_replace(".$","",$camp_lists);
		
		
		$sql="SELECT lead_filter_sql from vicidial_lead_filters where lead_filter_id='".$lead_filter_id."';";
		$rslt=mysqli_query($db_conn,$sql);
		$row=mysqli_fetch_row($rslt);
		$filterSQL =$row[0];
 		 
		mysqli_free_result($rslt);
		
 		$filterSQL = preg_replace("/\\\\/","",$filterSQL);
		$filterSQL = eregi_replace("^and|and$|^or|or$","",$filterSQL);
		
		if (strlen($filterSQL)>4){
			$fSQL = "and $filterSQL";
		}else{
			$fSQL = '';
		}
 		mysqli_free_result($rslt);
		
		echo "业务活动：<span class='blue'>$campaign_id</span> <br/>\n";
		echo "客户清单：<span class='blue'>$camp_lists</span><br/>\n";
		echo "呼叫状态：<span class='blue'>$dial_statuses</span><br/>\n";
		echo "过滤规则：<span class='blue'>$lead_filter_id</span><br/>\n";
		echo "呼叫时间：<span class='blue'>$local_call_time</span><br/><br/>\n";
   		
		//dialable_leads($DB,$local_call_time,$dial_statuses,$camp_lists,$drop_lockout_time,$fSQL);
 	 
		//function dialable_leads($DB,$local_call_time,$dial_statuses,$camp_lists,$drop_lockout_time,$fSQL){
			//global $db_conn;
		if (isset($camp_lists)){
			if (strlen($camp_lists)>1){
				if (strlen($dial_statuses)>2){
					$g=0;
					$p='13';
					$GMT_gmt[0] = '';
					$GMT_hour[0] = '';
					$GMT_day[0] = '';
					while ($p > -13){
						$pzone=3600 * $p;
						$pmin=(gmdate("i", time() + $pzone));
						$phour=( (gmdate("G", time() + $pzone)) * 100);
						$pday=gmdate("w", time() + $pzone);
						$tz = sprintf("%.2f", $p);	
						$GMT_gmt[$g] = "$tz";
						$GMT_day[$g] = "$pday";
						$GMT_hour[$g] = ($phour + $pmin);
						$p = ($p - 0.25);
						$g++;
					}
		
					$sql="SELECT call_time_id,call_time_name,call_time_comments,ct_default_start,ct_default_stop,ct_sunday_start,ct_sunday_stop,ct_monday_start,ct_monday_stop,ct_tuesday_start,ct_tuesday_stop,ct_wednesday_start,ct_wednesday_stop,ct_thursday_start,ct_thursday_stop,ct_friday_start,ct_friday_stop,ct_saturday_start,ct_saturday_stop,ct_state_call_times FROM vicidial_call_times where call_time_id='".$local_call_time."';";
					if ($DB) {echo "$sql\n";}
					$rslt=mysqli_query($db_conn,$sql);
					$rowx=mysqli_fetch_row($rslt);
					$Gct_default_start =	"$rowx[3]";
					$Gct_default_stop =		"$rowx[4]";
					$Gct_sunday_start =		"$rowx[5]";
					$Gct_sunday_stop =		"$rowx[6]";
					$Gct_monday_start =		"$rowx[7]";
					$Gct_monday_stop =		"$rowx[8]";
					$Gct_tuesday_start =	"$rowx[9]";
					$Gct_tuesday_stop =		"$rowx[10]";
					$Gct_wednesday_start =	"$rowx[11]";
					$Gct_wednesday_stop =	"$rowx[12]";
					$Gct_thursday_start =	"$rowx[13]";
					$Gct_thursday_stop =	"$rowx[14]";
					$Gct_friday_start =		"$rowx[15]";
					$Gct_friday_stop =		"$rowx[16]";
					$Gct_saturday_start =	"$rowx[17]";
					$Gct_saturday_stop =	"$rowx[18]";
					$Gct_state_call_times = "$rowx[19]";
		
					$ct_states = '';
					$ct_state_gmt_SQL = '';
					$ct_srs=0;
					$b=0;
					if (strlen($Gct_state_call_times)>2){
						$state_rules = explode('|',$Gct_state_call_times);
						$ct_srs = ((count($state_rules)) - 2);
					}
					while($ct_srs >= $b)
						{
						if (strlen($state_rules[$b])>1)
							{
							$sql="SELECT state_call_time_id,state_call_time_state,state_call_time_name,state_call_time_comments,sct_default_start,sct_default_stop,sct_sunday_start,sct_sunday_stop,sct_monday_start,sct_monday_stop,sct_tuesday_start,sct_tuesday_stop,sct_wednesday_start,sct_wednesday_stop,sct_thursday_start,sct_thursday_stop,sct_friday_start,sct_friday_stop,sct_saturday_start,sct_saturday_stop from vicidial_state_call_times where state_call_time_id='".$state_rules[$b]."';";
							$rslt=mysqli_query($db_conn,$sql);
							$row=mysqli_fetch_row($rslt);
							$Gstate_call_time_id =		"$row[0]";
							$Gstate_call_time_state =	"$row[1]";
							$Gsct_default_start =		"$row[4]";
							$Gsct_default_stop =		"$row[5]";
							$Gsct_sunday_start =		"$row[6]";
							$Gsct_sunday_stop =			"$row[7]";
							$Gsct_monday_start =		"$row[8]";
							$Gsct_monday_stop =			"$row[9]";
							$Gsct_tuesday_start =		"$row[10]";
							$Gsct_tuesday_stop =		"$row[11]";
							$Gsct_wednesday_start =		"$row[12]";
							$Gsct_wednesday_stop =		"$row[13]";
							$Gsct_thursday_start =		"$row[14]";
							$Gsct_thursday_stop =		"$row[15]";
							$Gsct_friday_start =		"$row[16]";
							$Gsct_friday_stop =			"$row[17]";
							$Gsct_saturday_start =		"$row[18]";
							$Gsct_saturday_stop =		"$row[19]";
		
							$ct_states .="'$Gstate_call_time_state',";
		
							$r=0;
							$state_gmt='';
							while($r < $g)
								{
								if ($GMT_day[$r]==0)	#### Sunday local time
									{
									if (($Gsct_sunday_start==0) and ($Gsct_sunday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_sunday_start) and ($GMT_hour[$r]<$Gsct_sunday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==1)	#### Monday local time
									{
									if (($Gsct_monday_start==0) and ($Gsct_monday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_monday_start) and ($GMT_hour[$r]<$Gsct_monday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==2)	#### Tuesday local time
									{
									if (($Gsct_tuesday_start==0) and ($Gsct_tuesday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_tuesday_start) and ($GMT_hour[$r]<$Gsct_tuesday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==3)	#### Wednesday local time
									{
									if (($Gsct_wednesday_start==0) and ($Gsct_wednesday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_wednesday_start) and ($GMT_hour[$r]<$Gsct_wednesday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==4)	#### Thursday local time
									{
									if (($Gsct_thursday_start==0) and ($Gsct_thursday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_thursday_start) and ($GMT_hour[$r]<$Gsct_thursday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==5)	#### Friday local time
									{
									if (($Gsct_friday_start==0) and ($Gsct_friday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_friday_start) and ($GMT_hour[$r]<$Gsct_friday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								if ($GMT_day[$r]==6)	#### Saturday local time
									{
									if (($Gsct_saturday_start==0) and ($Gsct_saturday_stop==0))
										{
										if ( ($GMT_hour[$r]>=$Gsct_default_start) and ($GMT_hour[$r]<$Gsct_default_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									else
										{
										if ( ($GMT_hour[$r]>=$Gsct_saturday_start) and ($GMT_hour[$r]<$Gsct_saturday_stop) )
											{$state_gmt.="'$GMT_gmt[$r]',";}
										}
									}
								$r++;
								}
							$state_gmt = "$state_gmt'99'";
							$ct_state_gmt_SQL .= "or (state='$Gstate_call_time_state' and gmt_offset_now IN($state_gmt)) ";
							}
		
						$b++;
						}
					if (strlen($ct_states)>2)
						{
						$ct_states = eregi_replace(",$",'',$ct_states);
						$ct_statesSQL = "and state NOT IN($ct_states)";
						}
					else
						{
						$ct_statesSQL = "";
						}
		
					$r=0;
					$default_gmt='';
					while($r < $g)
						{
						if ($GMT_day[$r]==0)	#### Sunday local time
							{
							if (($Gct_sunday_start==0) and ($Gct_sunday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_sunday_start) and ($GMT_hour[$r]<$Gct_sunday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==1)	#### Monday local time
							{
							if (($Gct_monday_start==0) and ($Gct_monday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_monday_start) and ($GMT_hour[$r]<$Gct_monday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==2)	#### Tuesday local time
							{
							if (($Gct_tuesday_start==0) and ($Gct_tuesday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_tuesday_start) and ($GMT_hour[$r]<$Gct_tuesday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==3)	#### Wednesday local time
							{
							if (($Gct_wednesday_start==0) and ($Gct_wednesday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_wednesday_start) and ($GMT_hour[$r]<$Gct_wednesday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==4)	#### Thursday local time
							{
							if (($Gct_thursday_start==0) and ($Gct_thursday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_thursday_start) and ($GMT_hour[$r]<$Gct_thursday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==5)	#### Friday local time
							{
							if (($Gct_friday_start==0) and ($Gct_friday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_friday_start) and ($GMT_hour[$r]<$Gct_friday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						if ($GMT_day[$r]==6)	#### Saturday local time
							{
							if (($Gct_saturday_start==0) and ($Gct_saturday_stop==0))
								{
								if ( ($GMT_hour[$r]>=$Gct_default_start) and ($GMT_hour[$r]<$Gct_default_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							else
								{
								if ( ($GMT_hour[$r]>=$Gct_saturday_start) and ($GMT_hour[$r]<$Gct_saturday_stop) )
									{$default_gmt.="'$GMT_gmt[$r]',";}
								}
							}
						$r++;
						}
		
					$default_gmt = "$default_gmt'99'";
					$all_gmtSQL = "(gmt_offset_now IN($default_gmt) $ct_statesSQL) $ct_state_gmt_SQL";
		
					$dial_statuses = preg_replace("/ -$/","",$dial_statuses);
					$Dstatuses = explode(" ", $dial_statuses);
					$Ds_to_print = (count($Dstatuses) - 0);
					$Dsql = '';
					$o=0;
					while ($Ds_to_print > $o) 
						{
						$o++;
						$Dsql .= "'$Dstatuses[$o]',";
						}
					$Dsql = preg_replace("/,$/","",$Dsql);
					if (strlen($Dsql) < 2) {$Dsql = "''";}
		
					$DLTsql='';
					if ($drop_lockout_time > 0){
						$DLseconds = ($drop_lockout_time * 3600);
						$DLseconds = floor($DLseconds);
						$DLseconds = intval("$DLseconds");
						$DLTsql = "and ( ( (status IN('DROP','XDROP')) and (last_local_call_time < CONCAT(DATE_ADD(NOW(), INTERVAL -$DLseconds SECOND),' ',CURTIME()) ) ) or (status NOT IN('DROP','XDROP')) )";
					}
		
					$sql="SELECT count(*) FROM vicidial_list where called_since_last_reset='N' and status IN($Dsql) and list_id IN($camp_lists) and ($all_gmtSQL) $DLTsql $fSQL";
					#$DB=1;
					if ($DB) {echo "$sql\n";}
					$rslt=mysqli_query($db_conn,$sql);
					$rslt_rows = mysqli_num_rows($rslt);
					if ($rslt_rows){
						$rowx=mysqli_fetch_row($rslt);
						$active_leads = "$rowx[0]";
					}else{
						$active_leads = '0';
					}
		
						echo "该业务活动中有 <strong class='green'>$active_leads</strong> 个号码可以拨打\n";
					}else{
						echo "<span class='red'>该活动没有可用的拨号状态</span>";
					}
				}else{
					echo "<span class='red'>该活动没有可用的客户清单</span>\n";
				}
				//mysqli_free_result($rslt);
			}else{
				echo "<span class='red'>该活动没有可用的客户清单</span>\n";
			}
		//}
	
	break;
	
	case "get_cam_lists":
 	
		$sql="select campaign_id,campaign_name from vicidial_campaigns order by campaign_name,campaign_id;";
		
 		$rows=mysqli_query($db_conn,$sql);
		$row_counts_list=mysqli_num_rows($rows);
		
		$list_arr=array();
		if($active){
			$where=" and active='$active'";	
		}
 		if ($row_counts_list!=0) {
			while($rs= mysqli_fetch_array($rows)){ 
  				$lists_arr=array();	
				$sql_form="select list_id,list_name from vicidial_lists where campaign_id='".$rs["campaign_id"]."' $where order by list_name,list_id ";	
				$rows2=mysqli_query($db_conn,$sql_form);
				
				if(mysqli_num_rows($rows2)!=0){
					while($rs2= mysqli_fetch_array($rows2)){ 
						
						$lists=array("o_val"=>$rs2['list_id'],"o_name"=>$rs2['list_name']);
						array_push($lists_arr,$lists);
					}
				} 
				mysqli_free_result($rows2);
				
  				$list=array("o_val"=>$rs['campaign_id'],"o_name"=>$rs['campaign_name'],"o_c_list"=>$lists_arr);
  				array_push($list_arr,$list);
 				
 			}
 			$counts="1";
			$des="获取成功！";
		}else {
			$counts="0";
			$des="未找到符合条件的数据！";
 		}
   		mysqli_free_result($rows);
 		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
	
	
	case "get_lead_filter_list":
 	
		$sql="select lead_filter_id,lead_filter_name from vicidial_lead_filters order by lead_filter_id";
 		//echo $sql;
		$rows=mysqli_query($db_conn,$sql);
		$row_counts_list=mysqli_num_rows($rows);			
		
		$list_arr=array();
		 
 		if ($row_counts_list!=0) {
			while($rs= mysqli_fetch_array($rows)){ 
			
				$list=array("o_val"=>$rs['lead_filter_id'],"o_name"=>$rs['lead_filter_name']);
				array_push($list_arr,$list);
  			}
 			$counts="1";
			$des="获取成功！";
		}else {
			$counts="0";
			$des="未找到符合条件的数据！";
 		}
  		
  		mysqli_free_result($rows);
 		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
	
	case "get_list_fields":
 	
		$sql="select list_id,field_id,field_name,field_label,field_type,field_options,field_description,field_default from list_fields  where list_id='".$list_id."'order by field_id";
 		//echo $sql;
		$rows=mysqli_query($db_conn,$sql);
		$row_counts_list=mysqli_num_rows($rows);			
		
		$list_arr=array();

		 
 		if ($row_counts_list!=0) {
			while($rs= mysqli_fetch_array($rows)){ 
			
				//$list=array("o_val"=>$rs['lead_filter_id'],"o_name"=>$rs['lead_filter_name']);
				//array_push($list_arr,$list);
				$list_option =explode(',',$rs['field_options']); 
				$list=array( "list_id"=>$rs['list_id'],"field_id"=>$rs['field_id'],"field_name"=>$rs['field_name'],"field_label"=>$rs['field_label'],"field_type"=>$rs['field_type'],"field_options"=>$list_option,"field_description"=>$rs['field_description'],"field_default"=>$rs['field_default']);
				
				array_push($list_arr,$list);
  			}
 			$counts="1";
			$des="获取成功！";
		}else {
			$counts="0";
			$des="未找到符合条件的数据！";
 		}
  		
  		mysqli_free_result($rows);
 		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
	
	
	case "add_modify_remove_custom_fields":
 	
 		$counts="1";	
 		if($do_actions=="add"){			
 			$field_name = "custom_".$field_name;
			$sql="select field_name from list_fields where list_id='".$list_id."' and field_name='".$field_name."'";
 			//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			mysqli_free_result($rows);
			
			if ($row_counts_list!=0) {
 				$counts="0";
				$des="该字段名称已存在，请检查更换其他!";
				
			}else
			{				
				$counts="1";			
				$sql="insert into list_fields (list_id,field_name,field_label,field_type,field_options,field_description,field_default) values ('".$list_id."','".$field_name."','".$field_label."','".$field_type."','".$field_option."','".$field_description."','".$field_default."')";
				$des="添加";
			}
		}else if($do_actions=="modify"){
			//$sql="update list_fields set field_name='".$field_name."',  field_label='".$field_label."', field_type='".$field_type."', field_options='".$field_options."',  field_description='".$field_description."', field_default='".$field_default."' where field_id='".$field_id."'";
			$sql="update list_fields set  field_label='".$field_label."', field_type='".$field_type."', field_options='".$field_option."',  field_description='".$field_description."', field_default='".$field_default."' where field_id='".$field_id."'";

			
			$des="修改";
		}else if($do_actions=="remove"){
			$sql="delete from list_fields where field_id='".$field_id."'";
			$des="删除";
		}else{
			$sql="";
		}

		if($counts=="1"){
			$rows=mysqli_query($db_conn,$sql);
	
			if($rows){
				$sql="";
				if($do_actions=="add"){				
					$sql="alter table list_".$list_id."_fields add ".$field_name." NVARCHAR(255) default '".$field_default."'";
					$rows=mysqli_query($db_conn,$sql);		
				}else if($do_actions=="modify"){

				}else if($do_actions=="remove"){
					$sql="alter table list_".$list_id."_fields drop COLUMN ".$field_name;
					$rows=mysqli_query($db_conn,$sql);		
				}
		 		//echo $sql;
		 		//
				
				
			}		
			if($rows){
					
	  		$counts="1";
				$des.="自定义字段成功!";
						
	 		}else{
				$counts="0";
				$des.="自定义字段失败，请检查重试!".$sql;
	 		}
	  		
	  	mysqli_free_result($rows);
	 		
 		}
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;	
	
	case "get_fileld_info":
 	
 	 	
		$sql="select field_name,field_label,field_type,field_options,field_description,field_default from list_fields  where field_id='".$field_id."'order by field_id";
 		//echo $sql;
		$rows=mysqli_query($db_conn,$sql);
		$row_counts_list=mysqli_num_rows($rows);			
		
		$list_arr=array();

		 
 		if ($row_counts_list > 0) {
			$rs= mysqli_fetch_array($rows);
			$list_arr=array("field_name"=>$rs['field_name'],"field_label"=>$rs['field_label'],"field_type"=>$rs['field_type'],"field_options"=>$rs['field_options'],"field_description"=>$rs['field_description'],"field_default"=>$rs['field_default']);					
 			$counts="1";
			$des="获取成功！";
		}else {
			$counts="0";
			$des="未找到符合条件的数据！";
 		}
  		
  		mysqli_free_result($rows);
 		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
		$json_data.="\"datalist\":".json_encode($list_arr)."";
 		$json_data.="}";
		
		echo $json_data;
	
	break;
	
	//获取自定字段信息及内容
	case "get_custom_fields":
 		$counts="1";
 	 	
		$sql="select list_id from vicidial_list  where lead_id='".$lead_id."'";
 		//echo $sql;
		$rows=mysqli_query($db_conn,$sql);
		$rs=mysqli_fetch_array($rows);		
		$list_id=$rs['list_id'];
		mysqli_free_result($rows);
		

		if($counts=="1"){
			$sql="select list_id,field_id,field_name,field_label,field_type,field_options from list_fields  where list_id='".$list_id."'order by field_id";
	 		//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			$row_counts_list=mysqli_num_rows($rows);			
			
			$field_arr=array();
			$field_value_arr=array();
			 
	 		if ($row_counts_list!=0) {
				while($rs= mysqli_fetch_array($rows)){ 
		
					$list_option =explode(',',$rs['field_options']); 
					$list=array( "list_id"=>$rs['list_id'],"field_id"=>$rs['field_id'],"field_name"=>$rs['field_name'],"field_label"=>$rs['field_label'],"field_type"=>$rs['field_type'],"field_options"=>$list_option);
					
					array_push($field_arr,$list);
	  			}
	 			$counts="1";
				$des="获取自定义字段信息成功！";
			}else {
				$counts="0";
				$des="未找到符合条件的自定义字段信息！";
	 		}
  		
  		mysqli_free_result($rows);
 		
	 		
 		}
 		if($counts=="1"){
 			$sql="select * from list_".$list_id."_fields  where lead_id='".$lead_id."'";
	 		//echo $sql;
			$rows=mysqli_query($db_conn,$sql);
			
			$rs=mysqli_fetch_array($rows);		
			foreach($field_arr as $list){	
				$list['value'] = $rs[$list['field_name']];					
				array_push($field_value_arr, $list);
			}
			mysqli_free_result($rows);
 		}
 		
		$json_data="{";
 		$json_data.="\"counts\":".json_encode($counts).",";
 		$json_data.="\"des\":".json_encode($des).",";
 		$json_data.="\"listId\":".json_encode($list_id).",";
		$json_data.="\"datalist\":".json_encode($field_value_arr)."";
 		$json_data.="}";
		
		echo $json_data;

	break;	
 	default :
}

function ParseDataBaseFieldNameToChinese($fieldName){
	$ret=$fieldName;
	if ($fieldName == "VENDOR LEAD CODE")
		$ret="代理商ID";
	if ($fieldName == "SOURCE ID")
		$ret="资源ID";
	if ($fieldName == "PHONE NUMBER")
		$ret="电话号码  <span class='red'>* 必选";
	if ($fieldName == "TITLE")
		$ret="标题";
	if ($fieldName == "FIRST NAME")
		$ret="名字";
	if ($fieldName == "MIDDLE INITIAL")
		$ret="中间名";
	if ($fieldName == "LAST NAME")
		$ret="姓氏";
	if ($fieldName == "ADDRESS1")
		$ret="地址1";
	if ($fieldName == "ADDRESS2")
		$ret="地址2";
	if ($fieldName == "ADDRESS3")
		$ret="地址3";	
	if ($fieldName == "CITY")
		$ret="城市";
	if ($fieldName == "STATE")
		$ret="地区";
	if ($fieldName == "PROVINCE")
		$ret="省份";
	if ($fieldName == "POSTAL CODE")
		$ret="邮编";
	if ($fieldName == "COUNTRY CODE")
		$ret="国家代号";
	if ($fieldName == "GENDER")
		$ret="性别";
	if ($fieldName == "DATE OF BIRTH")
		$ret="生日";
	if ($fieldName == "ALT PHONE")
		$ret="备用电话";
	if ($fieldName == "DATE OF BIRTH")
		$ret="生日";
	if ($fieldName == "EMAIL")
		$ret="邮箱";
	if ($fieldName == "SECURITY PHRASE")
		$ret="安全密码";
	if ($fieldName == "COMMENTS")
		$ret="描述";
	if ($fieldName == "RANK")
		$ret="等级";
	if ($fieldName == "OWNER")
		$ret="所有者";
	return $ret;	
} 
  

unset($list_arr);
unset($lists_arr); 
unset($json_data);
unset($sql); 
mysqli_close($db_conn);



?>


