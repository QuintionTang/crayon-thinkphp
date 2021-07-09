<?php
function page_title($title,$seo_title){
	return !empty($seo_title) ? strip_tags($seo_title) : strip_tags($title);
}
function get_sect_detail($blanking_id,$report_id){
	return D("ReportSects")->detail($blanking_id,$report_id);
}
function get_diameter_value($str,$typeC){
	$tmparray = explode($typeC,$str);
	return safty_value($tmparray[1],0,'intval');
}
function init_length_select($defLength =0){
	$array_length = get_allow_lengths();
	$array_options = array();
	foreach ($array_length as $key => $value) {
		$title = safty_value($value,0,"intval");
		$length_value = $title * 1000;
		$selected = false;
		if ($defLength === $length_value){
			$selected = true;
		}
		array_push($array_options,array(
				"value"=>$length_value,
				"title"=>$title,
				"selected"=>$selected
		));
	}
	return $array_options;
}
function getAttach($attach_id){
	$attach_info = D("Attach")->get_attach_info($attach_id);
	$attach_path = $attach_info['save_path'].$attach_info['save_name'];
	return RJ($attach_path);
}
function getBlankingWeight($diameter,$length_value,$total){
	$result = $total * $diameter * $diameter * 0.00617 * $length_value /1000;
	$result =  safty_value($result,0,'floatval');
	return sprintf("%01.2f",$result);
}
function getShapePic($pic_path){
	$picurl = safty_value($pic_path,'','trim');
	if ($picurl ==""){
		$picurl = "/data/attach/nopic.png";
	} else {
		$picurl = RJ($picurl);
	}
	return $picurl;
}
function get_allow_lengths(){
	$str_length = C('allow_lengths');
	$array_length = explode(",",$str_length);
	sort($array_length);
	return $array_length;
}
function getButtonColor($index){
	$button_color = array("btn-primary","btn-success","btn-purple","btn-dark","btn-info");
	return $button_color[$index-1];
}
function getBadgeStatus($status){
	$status_html = '';
	$status = safty_value($status,0,'intval');
	switch ($status) {
		case 1:
			$status_html = '<span class="badge badge-soft-primary">处理中</span>';
			break;
		case 2:
			$status_html = '<span class="badge badge-soft-success">已完成</span>';
			break;
		default:
			$status_html = '<span class="badge badge-soft-danger">未提交</span>';
			break;
	}
	return $status_html;
}

function getProjectTitle($project_id){
	return safty_value(D("RebarProjects")->get_title($project_id),"--","trim");
}
function getParentActionUrl($action_info,$primary_id=0,$primary_name="id",$parent_feature_id=0){
	$feature_id = $action_info['feature_id'];
	if ($parent_feature_id>0){
		return U($action_info['module_name'] . '/' . $action_info['action_name'],array("parent_feature_id"=>$parent_feature_id,"feature_id"=>$feature_id,$primary_name=>$primary_id));
	} else {
		return U($action_info['module_name'] . '/' . $action_info['action_name'],array("feature_id"=>$feature_id,$primary_name=>$primary_id));
	}
    
}

function  array_countvalue($arr)
{
	if(is_array($arr))
	{
		$values = array_values($arr); //取得数组中数值
		$count = array();//初始化数组
		foreach ($values as $value)
		{
			if(!array_key_exists($value,$count))
			{
				//如果键不存在则创建关联键
				$temp = array();
				//连接两个数组实际上相当于往里面添加元素
				$count=array_merge($count,$temp);
			}
		}
		foreach ($values as $key)
		{
			$count[$key]++;
		}
		return $count;
	} else {
		return  $arr;
	}
}
?>