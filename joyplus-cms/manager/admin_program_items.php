<?php
require_once ("admin_conn.php");
require_once ("./collect/collect_vod_program_item.php");
chkLogin();

$action = be("all","action");
switch($action)
{
	case "editall" : editall();break;
	case "collecProgram" : collecProgram();break;
	default : headAdmin ("电视直播管理");main();break;
}
dispseObj();

function  collecProgram()
{
	global $db,$cache;
	
	 $tv_id = be("all", "tv_id"); 
     if(!isNum($tv_id)) { 
     	echo '参数非法。'; 
     } else { 
     	  $tv_id = intval($tv_id);
     	  $row=$db->getRow("SELECT tv_id, tv_code, tv_playfrom FROM mac_tv_egp_config where tv_id=".$tv_id .' GROUP BY tv_id order by tv_playfrom asc');
     	  //var_dump($row);
     	  if($row){
			    $day = be("all", "day");
			    if(isN($day)){
			      parseVodPad(array(
			        'id'=>$row['tv_id'],
			        'tv_code'=>$row['tv_code'],
			        'tv_playfrom'=>$row['tv_playfrom'],
			      ));
			    }else{
			    	parseVodPadSimple($row['tv_id'],$row['tv_code'],$day,$row['tv_playfrom']);
			    }
			    echo '采集完成。';
     	  }else {
     	  	echo '指定的电视台没有相应的采集编码。'; 
     	  }
     }
	 
	
}
function editall()
{
	global $db;
	$t_id = be("arr","t_id");
	$ids = explode(",",$t_id);
	//var_dump($ids);
	foreach( $ids as $id){
		$play_time = be("post","play_time" .$id);
		$video_name = be("post","video_name" .$id);
		$program_type = be("post","program_type" .$id);
		//update config tabel
		if(!isN($program_type)){
			$row = $db->getRow("select * from  mac_tv_program_type_item where program_type ='".$program_type."' and program_name='".$video_name."'");
			// var_dump("select * from  mac_tv_program_type_item where program_type ='".$program_type."' and program_name='".$video_name."'");
			// var_dump($row);
			if(!$row){
				 $db->query("insert into mac_tv_program_type_item(program_type,program_name) values('".$program_type."','".$video_name."')");
//				 var_dump("insert into mac_tv_program_type_item(program_type,program_name) values('".$program_type."','".$video_name."')");
			}			
			$db->query("update mac_tv_program_item set program_type='".$program_type ."' where video_name='".$video_name."'");
			//var_dump("update mac_tv_program_item set program_type='".$program_type ."' where video_name='".$video_name."'");
		}
		$db->Update ("{pre}tv_program_item",array("play_time", "video_name","program_type"),array($play_time,$video_name,$program_type),"id=".$id);
	}
	
	echo "修改完毕";
}

function main()
{
	global $db,$cache;
	
	 $tv_id = be("all", "tv_id"); 	 
	 $day = be("all", "day"); 
	
    if(!isNum($tv_id)) { $tv_id = 0; } else { $tv_id = intval($tv_id);}
    if(isN($day)){
      $day=date('Y-m-d',time());
    }
    
    $where = " 1=1 ";

    $where .= " AND tv_id =".$tv_id;
    $where .= " AND day ='".$day."'";
    
    
	$pagenum = be("all","page");
	if (!isNum($pagenum)){ $pagenum = 1;} else { $pagenum = intval($pagenum);}
	if ($pagenum < 1) { $pagenum = 1; }
	$sql = "SELECT count(*) FROM {pre}tv_program_item as a "." where ".$where;;
	$nums = $db->getOne($sql);
	$pagecount=ceil($nums/app_pagenum);
	$sql1 = "SELECT * FROM {pre}tv_program_item  where  ".$where." and play_time<='12:00'  order by play_time asc ";
	$sql2 = "SELECT * FROM {pre}tv_program_item  where  ".$where." and play_time>'12:00'  order by play_time asc ";
//	var_dump($sql);
	$rs1 = $db->query($sql1);
	$rs2 = $db->query($sql2);
?>
<script language="javascript">
function filter(){
	var tv_id=$("#tv_id").val();
	var day  =$("#date").val();
	if(day ==''){
		var url = "admin_program_items_day.php?tv_id="+tv_id+"&day="+day;
	}else {
		var url = "admin_program_items.php?tv_id="+tv_id+"&day="+day;
	}
	
	
	window.location.href=url;
}

function collecProgram(){
	var tv_id=$("#tv_id").val();
	var day  =$("#date").val();
	var url = "admin_program_items.php?action=collecProgram&tv_id="+tv_id+"&day="+day;
	$.get(url,"", function(obj) {
		alert(obj);
	});
	
}

$(document).ready(function(){
	
	$('#form1').form({
		onSubmit:function(){
			if(!$("#form1").valid()) {return false;}
		},
	    success:function(data){
	        $.messager.alert('系统提示', data, 'info',function(){
	        	location.href=location.href;
	        });
	    }
	});
	
	$("#btnDel").click(function(){
			if(confirm('确定要删除吗')){
				$("#form1").attr("action","admin_ajax.php?action=del&flag=batch&tab={pre}vod_topic_items");
				$("#form1").submit();
			}
			else{return false}
	});
	$("#btnEdit").click(function(){
		$("#form1").attr("action","?action=editall");
		$("#form1").submit();
	});
	$("#btnAdd").click(function(){
		$('#form2').form('clear');
		$("#flag").val("add");
		$('#win1').window('open');
		
	});
	$("#btnAdd_down").click(function(){
		$('#form2').form('clear');
		$("#flag").val("add");
		$('#win1').window('open');
		
	});

	$('#form2').form({
		onSubmit:function(){
			if(!$("#form2").valid()) {return false;}
		},
	    success:function(data){
	        $.messager.alert('系统提示', data, 'info');
	    }
	});
	
	
//	$("#btnAdd").click(function(){
//		window.location.href="admin_vod.php?topic_id=<?php echo $topic_id?>";
//	});
	$("#btnCancel").click(function(){
		location.href= location.href;
	});
});
function edit(id)
{
	$('#form2').form('clear');
	$("#flag").val("edit");
	$('#win1').window('open');
	$('#form2').form('load','admin_ajax.php?action=getinfo&tab={pre}vod_topic&col=t_id&val='+id);
}
</script>
<script type="text/javascript" src="/js/calendar.js"></script>
<table class="admin_program_items tb">
	<tr>
	<td>
	<table border="0" cellpadding="3" cellspacing="1">
	<tr>
	<td colspan="2">
	过滤条件：频道 <select id="tv_id" name="tv_id" >
	
	<?php echo makeSelectWhere("{pre}tv","id","tv_name","tv_type","","&nbsp;|&nbsp;&nbsp;",$tv_id," where status=1")?>
	</select>
	
	<input id="date" name="date" type="text" onclick="new Calendar().show(this);" value="<?php echo $day; ?>" readonly="readonly"/>
	<input class="input" type="button" value="搜索" id="btnsearch" onClick="filter();">	 |  <input class="input" type="button" value="采集节目单" id="btnsearch1" onClick="collecProgram();">	 | <a class="input" href="admin_program.php">返回电视直播</a>
	</td> 
	</tr>
	
	</table>
	</td>
	</tr>
</table>

<table class="tb">
<form action="" method="post" id="form1" name="form1">
	<tr>
	<td align="left">AM 00:00-12:00节目单</td>
	<td align=""left"" width="50%">AM 12:00-24:00节目单   <input type="button" value="添加" id="btnAdd" class="input" /> </td>
	</tr>
	<?php
		if($nums==0){
	?>
    <tr class="formlast"><td align="center" colspan="2">没有任何记录!</td></tr>
    <?php
		}
		else{
			
		  		
	?>
    <tr>
	  <td align="left" valign="top">
	  <?php 
		
	  while ($row1 = $db ->fetch_array($rs1))
		  	{
		  		$t_id1=$row1["id"];
	    ?>
	      <input name="t_id[]" type="checkbox" id="t_id" value="<?php echo $t_id1?>" /> 
	       <input type="text" name="play_time<?php echo $t_id1?>" value="<?php echo $row1["play_time"]?>" size="5"/>
	       <input type="text" name="video_name<?php echo $t_id1?>" value="<?php echo $row1["video_name"]?>" size="40"/> 
	       <select id="program_type<?php echo $t_id1?>" name="program_type<?php echo $t_id1?>" >
	<option value=''>节目类别</option>
	<?php echo makeSelectTV_live("prod_type", $row1["program_type"] )?>
	</select><br/>
	    <?php 
		  		
		  	}
	  ?>
	  </td>
	<td align="left">
	    <?php 
		while ($row2 = $db ->fetch_array($rs2))
		  	{
		  		$t_id2=$row2["id"];
	    ?>
	      <input name="t_id[]" type="checkbox" id="t_id" value="<?php echo $t_id2?>" />
	       <input type="text" name="play_time<?php echo $t_id2?>" value="<?php echo $row2["play_time"]?>" size="5"/>
	       <input type="text" name="video_name<?php echo $t_id2?>" value="<?php echo $row2["video_name"]?>" size="40"/> 
     <select id="program_type<?php echo $t_id2?>" name="program_type<?php echo $t_id2?>" >
	<option value=''>节目类别</option>
	<?php echo makeSelectTV_live("prod_type",  $row2["program_type"])?>
	</select><br/>
	    <?php 
		  	}
	  ?>
	  </td>
	 
   
    </tr>
	<?php
			
		}
	?>
	<tr class="formlast">
	<td  colspan="7"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onClick="checkAll(this.checked,'t_id[]')" /> 全选 
<!--	<input type="button" value="批量删除" id="btnDel" class="input"  />-->
	&nbsp;<input type="button" value="批量修改" id="btnEdit" class="input" />
	&nbsp;<input type="button" value="添加" id="btnAdd_down" class="input" />
	</td></tr>
</table>
</form>


<div id="win1" class="easyui-window" title="窗口" style="padding:5px;width:450px;" closed="true" closable="false" minimizable="false" maximizable="false">
<form action="admin_ajax.php?action=save&tab={pre}tv_program_item&tv_id=<?php echo $tv_id ;?>&day=<?php echo $day;?>" method="post" name="form2" id="form2">
<table class="tb">
	<input id="id" name="id" type="hidden" value="">
	<input id="flag" name="flag" type="hidden" value="">
	<tr>
	<td width="30%">播放时间：</td>
	<td><input id="play_time" size=5 value="" name="play_time">（格式xx:xx, 比如23:20）
	</td>
	</tr>
	<tr>
	<td width="30%">播放节目：</td>
	<td><input id="video_name" size=40 value="" name="video_name">
	</td>
	</tr>
	<tr>
	<td width="30%">节目类别：</td>
	<td> <select id="program_type" name="program_type" >
	<option value=''></option>
	<?php echo makeSelectTV_live("prod_type",  '')?>
	</select>
	</td>
	</tr>
	 
    <tr align="center" >
      <td colspan="2"><input class="input" type="submit" value="保存" id="btnSave"> <input class="input" type="button" value="返回" id="btnCancel"></td>
    </tr>
</table>
</form>
</div>
</body>
</html>
<?php
unset($rs1);
unset($rs2);
}
?>