<?php require("../../inc/config.ini.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xHTML1/DTD/xHTML1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xHTML">
<head>
<meta http-equiv="Content-Type" content="text/HTML; charset=utf-8" />
<title> <?php echo $system_name ?></title>
<link href="/css/main.css?v=<?php echo $today ?>" rel="stylesheet" type="text/css">
<link href="/css/day.css" rel="stylesheet" type="text/css">
<script src="/js/jquery-1.8.3.min.js"></script>
<script src="/js/main.js?v=<?php echo $today ?>"></script>
<script src="/js/pub_func.js?v=<?php echo $today ?>"></script>
<script>
function GetPageCount(a_ctions,doa_ctions){
	$("#a_ctions").val(a_ctions);
	$("#doa_ctions").val(doa_ctions);
  	
	var url="action=get_lists&pages=1&actions="+a_ctions+"&do_actions="+doa_ctions+"&sorts="+$('#sorts').val()+"&order="+$('#order').val()+times+"&"+$('#form1').serialize();
 	
	$.ajax({
		
		url: "send.php",
		data: url,
 		
		cache: false,
 		success: function(msg){
			 
			$("#recounts").val(msg.counts);
			max_pages($("#pagesize").val());
			OutputHtml($("#pages").val(),$("#pagesize").val());
		}
	});
	 
}
   
function get_datalist(page_nums,a_ctions,doa_ctions,pagesize){

	$('#load').show();
	//$("#excel_addr").html('');
	max_pages(pagesize);
	var pages=$("#pagecounts").val();
	if(parseInt(page_nums) < 1)page_nums = 1; 
	if(parseInt(page_nums) > parseInt(pages)){
		page_nums = pages;
	}; 
	if(!(parseInt(page_nums) <= parseInt(pages))) page_nums = pages;	 
	
	var url="action=get_lists&pages="+page_nums+"&actions="+a_ctions+"&do_actions="+doa_ctions+"&sorts="+$('#sorts').val()+"&order="+$('#order').val()+"&pagesize="+pagesize+times+"&"+$('#form1').serialize();
	//alert(url);
	//return false;
	
	$.ajax({
		 
		url: "send.php",
		data:url,
		
		beforeSend:function(){$('#load').css("top",$(document).scrollTop()).show('100');},
		complete :function(){$('#load').hide('100');},
		success: function(json){ 
			
			$("#datatable tfoot tr").show();
			if(parseInt(json.counts)>0){
				 
				$("#datatable tbody tr").remove();
				var tits="",td_str="",fun_str="",qua_str="";
				$.each(json.datalist, function(index,con){
					if(con.list_id=="998"||con.list_id=="999"){
						dblclick="";
						disabled=" disabled='disabled'";
						do_edit="修改 删除";
					}else{
						disabled="";
 						dblclick=" ondblclick='edit_list(\""+con.list_id+"\")' ";
						do_edit="<a href='javascript:void(0)' onclick='edit_list(\""+con.list_id+"\")'>修改</a> <a href='javascript:void(0)' onclick='del_(\""+con.list_id+"\")'>删除</a>";
					}
					list_changedate=con.list_changedate;
					if(!list_changedate){
						list_changedate="";	
					}
					list_lastcalldate=con.list_lastcalldate;
					if(!list_lastcalldate){
						list_lastcalldate="";	
					}
					
					
					tr_str="<tr align=\"left\" id=\"leads_list_"+con.list_id+"\" "+dblclick+" >";
					tr_str+="<td align=\"center\"><input name=\"c_id\" type=\"checkbox\" value=\""+con.list_id+"\" "+disabled+" /></td>";
					tr_str+="<td>"+con.list_id+"</td>";
					tr_str+="<td title='"+con.list_name+" "+con.list_description+"'><div class='hide_tit'>"+con.list_name+"</div></td>";
  					tr_str+="<td>"+list_lastcalldate+"</td>";
					tr_str+="<td>"+list_changedate+"</td>";
					tr_str+="<td>"+con.campaign_name+"</td>";
 					tr_str+="<td>"+con.counts+"</td>";
					tr_str+="<td>"+con.active+"</td>";
					tr_str+="<td>"+do_edit+"</td>";
					tr_str+="</tr>";
					$("#datatable tbody").append(tr_str);
				}); 
				
				OutputHtml(page_nums,pagesize);
  			
			}else{
				 
				$("#datatable tbody tr").remove();
 				$("#datatable tfoot tr").hide();
				tr_str="<tr><td colspan=\"12\" align=\"center\">"+json.des+"</td></tr>"
				$("#datatable").append(tr_str);
 			}  
			d_table_i();
  		
		},error:function(XMLHttpRequest,textStatus ){
			alert("页面请求错误，请检查重试或联系管理员！\n"+textStatus);
		}
	});
	 
}

function add_list(actions){
	var diag =new Dialog("add_list");
    diag.Width = 620;
    diag.Height = 320;
 	diag.Title = "新建客户清单";
	diag.URL = '/document/lists/list.php?action=add_list&tits='+encodeURIComponent("新建客户清单");
 	diag.OKEvent = set_add_list;
	//diag.CancelEvent = parent_focus; 
    diag.show();
}
/* add_for_custom_field begin */ 
function custom_field(actions){
	var diag =new Dialog("custom_field");
    diag.Width = 620;
    diag.Height = 320;
 	diag.Title = "新建自定义字段";
	diag.URL = '/document/lists/list.php?action=custom_field&tits='+encodeURIComponent("新建自定义字段");
 	diag.OKEvent = set_custom_field;
	//diag.CancelEvent = parent_focus; 
    diag.show();
}

function set_custom_field(){
	//Zd_DW.do_add_list();
}  
/* add_for_custom_field end */  

function set_add_list(){
	Zd_DW.do_add_list();
}
  
  

    
  
function edit_list(list_id){
	var diag =new Dialog("edit_list_"+list_id);
    diag.Width = $(window).width() - 26;
    diag.Height = $(window).height() -60;
 	diag.Title = "客户清单设置";
	diag.URL = '/document/lists/list.php?action=edit_list&list_id='+list_id+'&tits='+encodeURIComponent("客户清单设置");
 	diag.OKEvent = set_edit_list;
	//diag.CancelEvent = parent_focus;
    diag.show();
}
 
function set_edit_list(){
	Zd_DW.do_edit_list();
}

function parent_focus(){
	Zd_DW.parent_focus();
}
    
    
function check_form(actions) {	
     if (actions == "search") {
  		 
  		$("#datatable").show();
        GetPageCount('search', "count");
        get_datalist(1,"search", "list",$('#pagesize').val());
    }
    	  
}


function del_(c_id)
{	
 	var datas="";
 	
	if (c_id!="0"&&c_id!=""){
		
	}else{
		c_id="";
 		$('input[name="c_id"]:checked').each(function(i){
			c_id+=""+$(this).val()+",";
 		}); 
		
		if(c_id!=""&&c_id.substr(c_id.length-1)==","){
			c_id=c_id.substr(0,c_id.length-1);
		}
 	}
	if (c_id==""){
		alert("请选择要删除的行！");
		return false;
	}
	datas="action=del_leads_list&c_id="+c_id+times;
 	//alert(datas);
    if(confirm("客户清单包含号码基本资料、呼叫描述等信息，删除后不可恢复！\n如果近期呼叫过本清单号码，建议先导出备份或隔段时间后再行删除！\n\n您确定要删除本清单吗？")){
 
		$('#load').show();
		$.ajax({
			 
			url: "send.php",
			data:datas,
			
			beforeSend:function(){$('#load').css("top",$(document).scrollTop()).show('100');},
			complete :function(){$('#load').hide('100');},
			success: function(json){ 
				request_tip(json.des,json.counts);
				if(json.counts=="1"){
					$("#CheckedAll").attr("checked",false);
					GetPageCount($("#a_ctions").val(),"count");
					get_datalist($("#pages").val(),$("#a_ctions").val(),"list",$('#pagesize').val());
 				}else{
					alert(json.des);   
				}
 									
			},error:function(XMLHttpRequest,textStatus ){
				alert("页面请求错误，请检查重试或联系管理员!\n"+textStatus);
			}
		});
   	}
}


function leads_load(){
	var diag =new Dialog("leads_load_");
    diag.Width = $(window).width() - 26;
    diag.Height = $(window).height()-50;
 	diag.Title = "导入清单号码";
	diag.URL = '/document/lists/list.php?action=leads_load&tits='+encodeURIComponent("导入清单号码");
  	diag.show();
	diag.OKButton.hide();
	diag.CancelButton.value="关 闭";
}
  
 
$(document).ready(function(){
	$("#CheckedAll").click(function(){
		var checkbox=$('[name=c_id]:checkbox:enabled');
 		if(this.checked==true){
			$(checkbox).attr("checked",this.checked).parent().parent().addClass("click");
 		}else{
			$(checkbox).attr("checked",this.checked).parent().parent().removeClass("click");
		}
	});	
	
 	var Sorts_Order=0;
	$("#datatable .dataHead th[sort]").map(function(){
		Sorts_Order=Sorts_Order+1;
		
		html=$(this).html();
		
		$(this).attr("id","DadaSorts_"+Sorts_Order).off().on("click",function(){
			Sorts_new("datatable",$(this).attr("id"),$("#pagesize").val());	
		}).html("<div>"+html+"<span class='sorting'></span></div>");
		
 	});
 
 	$('<input name="a_ctions" type="hidden" id="a_ctions"/> <input name="doa_ctions" type="hidden" id="doa_ctions"/> <input name="recounts" type="hidden" id="recounts"/> <input name="pages" type="hidden" id="pages" value="1"/> <input name="pagecounts" type="hidden" id="pagecounts"/><input name="pagesize" type="hidden" id="pagesize" value="20"/> <input name="sorts" type="hidden" id="sorts" value=" "/> <input name="order" type="hidden" id="order"/>').appendTo("body");
 	
	GetPageCount('search',"count");
	get_datalist(1,"search","list",$('#pagesize').val());
});
  
</script>
<style>
.hide_tit{width:120px;}
</style>

</head>
<body>
<div id="load" class="load_layer"><img src="/images/loading.gif"align="absmiddle"/>数据加载中...</div>
<div id="auto_save_res" class="load_layer"></div>
 
    
<div class="page_main">
    
     <table border="0" cellpadding="0" cellspacing="0" class="menu_list">
     <tr>
        <td colspan="2">
        	<a href='javascript:void(0);'  class='zPushBtn' hidefocus='true' tabindex='-1' onselectstart='return false' priv="true" onClick="add_list('');" title="新建客户清单！"><img src="/images/icons/telephone6.png" style="margin-top:6px"/><b>新建客户清单&nbsp;</b></a>
        	<a href='javascript:void(0);'  class='zPushBtn' hidefocus='true' tabindex='-1' onselectstart='return false' priv="true" onClick="leads_load();" title="导入清单号码！"><img src="/images/icons/telephone3.png" style="margin-top:6px"/><b>导入清单号码&nbsp;</b></a>
        	<a href='javascript:void(0);'  class='zPushBtn' hidefocus='true' tabindex='-1' onselectstart='return false' priv="true" onClick="custom_field('');" title="管理自定义字段！"><img src="/images/icons/telephone7.png" style="margin-top:6px"/><b>管理自定义字段&nbsp;</b></a>	
        </td>
      </tr>
    </table>
                  
     <table width="99%" border="0" align="center" cellpadding="0" class="blocktable" >
        <tr>
            <td style="">
          <input type="hidden" name="get_dial_method" id="get_dial_method" value="0" />
          <input type="hidden" name="get_dial_level" id="get_dial_level" value="0" />
          
          <fieldset><legend> <label onClick="show_div('search_list');" title="点击收缩/展开">查询选项</label></legend>
            <form action="" onSubmit=""  method="post" name="form1" id="form1">   
             <table width="100%" border="0" align="center"  cellspacing="0" id="search_list" class="search_table" >
            
               <tr>
                 <td width="8%" align="right">清单ID：</td>
                 <td height="">
                 <input name="list_id" type="text" class="input_text" id="list_id" title="输入要查询的客户清单ID，多个以英文“,”分隔"  size="21" onkeyup="this.value=value.replace(/[^\w\/,]/ig,'')" onafterpaste="this.value=value.replace(/[^\w\/,]/ig,'')" />
                 </td>
                 <td width="8%" height="26" align="right" id="">清单名称：</td>
         		 <td nowrap="nowrap"><input name="list_name" type="text" class="input_text" id="list_name" /></td>
                 <td width="8%" align="right">清单描述：</td>
                 <td><input name="campaign_description" type="text" class="input_text" id="campaign_description" /></td>
                 <td width="8%" align="right" id="td">所属业务：</td>
                 <td><input name="cmpaign_id_list" type="text" class="input_text2" id="campaign_id_list"  title="双击选择业务活动"  size="16"  readonly="readonly"  onDblClick="c_campaign_id_list('get_campaign_id_list');" /><a class="sel_" hidefocus="true" href="javascript:void(0);" title="点击选择业务活动" onClick="c_campaign_id_list('get_campaign_id_list');"></a>
                 <input type="hidden" name="campaign_id" id="campaign_id" value="" />
                 </td>
                 
               </tr>
               <tr>
                 <td align="right">激活状态：</td>
                 <td><select name="active" class="s_option" id="active">
                   <option value="">未指定</option>
                   <option value="Y">启用</option>
                   <option value="N">禁用</option>
                 </select></td>
                 <td height="" align="right" id="td2">&nbsp;</td>
                 <td height="" nowrap="nowrap">&nbsp;</td>
                 <td align="right">&nbsp;</td>
                 <td>&nbsp;</td>
                 <td height="" align="right" id="td3">&nbsp;</td>
                 <td>&nbsp;</td>
               </tr>
               <tr>
                 <td align="right"> 
                  </td>
                 <td colspan="7"><input type="button" name="form_submit" value="提交查询" onClick="check_form('search');" style="cursor:pointer" />
                   <input type="reset" name="button" id="button" value="重置" /></td>
               </tr>
              </table> 
      
            </form>
          </fieldset>       
             
            <table border="0" width="100%" align="center" cellpadding="0" cellspacing="0" class="dataTable" id="datatable" >
                  <thead>
                    <tr align="left" class="dataHead">
                      <th style="width:4%;"><input name="CheckedAll" type="checkbox" id="CheckedAll" /><a href="javascript:void(0);" onclick="del_(0);" title="删除选定数据" style="font-weight:normal">删除</a></th>             
                      <th sort="a.list_id" >清单ID</th>
                      <th sort="a.list_name" >清单名称</th>
                      <th sort="a.list_lastcalldate" >最后呼叫时间</th>
                      <th sort="a.list_changedate" >修改时间</th>
                      <th sort="a.campaign_id" >业务活动</th>
                       
                      <th sort="counts" >号码数量</th>
                      <th sort="a.active" >激活</th>
                      <th >操作</th>
                     </tr>
                  </thead>   
                    <tbody>
                    </tbody>
                    <tfoot><tr class='dataTableFoot'><td colspan='14' align='left'><div id='dataTableFoot'><div style='float:right;' id='pagelist'></div><div style='float:left;' id='total'></div></div></td></tr></tfoot>
              </table>
               
         </td>
  </tr>
 </table>  
</div>
 
</body>
</html>
   
