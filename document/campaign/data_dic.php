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
  	
	var url="action=get_data_dic_list&pages=1&actions="+a_ctions+"&do_actions="+doa_ctions+"&sorts="+$('#sorts').val()+"&order="+$('#order').val()+times+"&"+$('#form1').serialize();
 	
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
  
	max_pages(pagesize);
	var pages=$("#pagecounts").val();
	if(parseInt(page_nums) < 1)page_nums = 1; 
	if(parseInt(page_nums) > parseInt(pages)){
		page_nums = pages;
	}; 
	if(!(parseInt(page_nums) <= parseInt(pages))) page_nums = pages;	 
	
	var url="action=get_data_dic_list&pages="+page_nums+"&actions="+a_ctions+"&do_actions="+doa_ctions+"&sorts="+$('#sorts').val()+"&order="+$('#order').val()+"&pagesize="+pagesize+times+"&"+$('#form1').serialize();
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
					 
					dblclick=" ondblclick='edit_data_dic(\""+con.dic_id+"\")' ";
					do_edit="<a href='javascript:void(0)' onclick='edit_data_dic(\""+con.dic_id+"\")'>修改</a> <a href='javascript:void(0)' onclick='del_(\""+con.dic_id+"\")'>删除</a>";
  					
					tr_str="<tr align=\"left\" id=\"data_dic_list_"+con.dic_id+"\" "+dblclick+">";
					tr_str+="<td align=\"center\"><input name=\"c_id\" type=\"checkbox\" value=\""+con.dic_id+"\" /></td>";
					tr_str+="<td>"+con.dic_id+"</td>";
					tr_str+="<td title='"+con.dic_name+"'><div >"+con.dic_name+"</div></td>";
					tr_str+="<td title='"+con.dic_des+"'><div class='hide_tit'>"+con.dic_des+"</div></td>";
					tr_str+="<td>"+con.counts+"</td>";
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

function add_data_dic(actions){
	var diag =new Dialog("add_data_dic");
    diag.Width = 800;
    diag.Height = 420;
 	diag.Title = "新建数据字典";
	diag.URL = '/document/campaign/list.php?action=add_data_dic&tits='+encodeURIComponent("新建数据字典");
 	diag.OKEvent = set_add_data_dic;
	//diag.CancelEvent = parent_focus;
    diag.show();
}
 
function set_add_data_dic(){
	Zd_DW.do_add_data_dic();
}
  
function edit_data_dic(dic_id){
	var diag =new Dialog("edit_data_dic_"+dic_id);
    diag.Width = 800;
    diag.Height = 420;
 	diag.Title = "数据字典设置";
	diag.URL = '/document/campaign/list.php?action=edit_data_dic&dic_id='+dic_id+'&tits='+encodeURIComponent("数据字典设置");
 	diag.OKEvent = set_edit_data_dic;
	//diag.CancelEvent = parent_focus;
    diag.show();
}
 
function set_edit_data_dic(){
	Zd_DW.do_edit_data_dic();
}

   
function copy_data_dic(){
	var diag =new Dialog("copy_data_dic_");
	diag.Width = 580;
	diag.Height = 240;	
 	diag.Title = "复制数据字典";
	diag.URL = '/document/campaign/list.php?action=copy_data_dic&tits='+encodeURIComponent("复制数据字典");
 	diag.OKEvent = set_copy_data_dic;
    diag.show();
    diag.OKButton.value = "复 制";
}
 
function set_copy_data_dic(){
	Zd_DW.do_copy_data_dic();
}  

   
function check_form(actions){	
	if (actions == "search") {
 		$("#datatable").show();
		GetPageCount('search', "count");
		get_datalist(1,"search", "list",$('#pagesize').val());
	}
}
 
function del_(c_id){	
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
	datas="action=del_data_dic&do_actions=data_dic&c_id="+c_id+times;
 	//alert(datas);
    if(confirm("删除后不可恢复，您确定要删除吗？")){
 
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
		
 	});	$('<input name="a_ctions" type="hidden" id="a_ctions"/> <input name="doa_ctions" type="hidden" id="doa_ctions"/> <input name="recounts" type="hidden" id="recounts"/> <input name="pages" type="hidden" id="pages" value="1"/> <input name="pagecounts" type="hidden" id="pagecounts"/><input name="pagesize" type="hidden" id="pagesize" value="20"/> <input name="sorts" type="hidden" id="sorts" value=""/> <input name="order" type="hidden" id="order"/>').appendTo("body");
	
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
        <td colspan="2"><a href='javascript:void(0);'  class='zPushBtn' hidefocus='true' tabindex='-1' onselectstart='return false' priv="true" onClick="add_data_dic('');" title="新建数据字典"><img src="/images/icons/icons_27.png" style="margin-top:4px"/><b>新建数据字典&nbsp;</b></a><a href='javascript:void(0);'  class='zPushBtn' hidefocus='true' tabindex='-1' onselectstart='return false' priv="true" onClick="copy_data_dic('');" title="复制数据字典"><img src="/images/icons/icons_26.png" style="margin-top:4px"/><b>复制数据字典&nbsp;</b></a></td>
      </tr>
    </table>
                  
     <table width="99%" border="0" align="center" cellpadding="0" class="blocktable" >
        <tr>
            <td style="">
          
          
          <fieldset><legend> <label onClick="show_div('search_list');" title="点击收缩/展开">查询选项</label></legend>
            <form action="" onSubmit=""  method="post" name="form1" id="form1">       
             <table width="100%" border="0" align="center"  cellspacing="0" id="search_list" class="search_table" >
            
               <tr>
                 <td width="8%" align="right">字典ID：</td>
                 <td height="">
                 <input name="data_dic" type="text" class="input_text" id="data_dic" title="输入要查询的原因ID，多个以英文“,”分隔"  size="21" onkeyup="this.value=value.replace(/[^\w\/,]/ig,'')" onafterpaste="this.value=value.replace(/[^\w\/,]/ig,'')" />
                 </td>
                 <td width="8%" height="26" align="right" id="">字典名称：</td>
         		 <td nowrap="nowrap"><input name="data_dic_name" type="text" class="input_text" id="data_dic_name" /></td>
                 <td width="8%" align="right"><div class="div_w1"></div></td>
                 <td><div class="div_w2"></div></td>
                 <td width="8%" align="right"><div class="div_w1"></div></td>
                 <td><div class="div_w2"></div></td>
                 
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
                      <th sort="a.dic_id" >字典ID</th>
                      <th sort="a.dic_name" >字典名称</th>
                      <th sort="a.dic_des" >字典描述</th>
                      <th sort="b.counts" >子项数目</th>
                      <th >操作</th>
                     </tr>
                  </thead>   
                    <tbody>
                    </tbody>
                    <tfoot><tr class='dataTableFoot'><td colspan='15' align='left'><div id='dataTableFoot'><div style='float:right;' id='pagelist'></div><div style='float:left;' id='total'></div></div></td></tr></tfoot>
              </table>
               
         </td>
  </tr>
 </table>  
</div>
 
</body>
</html>
   
