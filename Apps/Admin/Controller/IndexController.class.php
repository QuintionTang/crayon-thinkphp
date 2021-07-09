<?php
// +----------------------------------------------------------------------
// | Rusty Crayon WebThink[锈蜡笔Web基础架构]
// +----------------------------------------------------------------------
// | ProductionSteps：生产流程信息Model
// +----------------------------------------------------------------------
// | Copyright (c) rusty-crayon 2021-2023 https://www.rusty-crayon.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: QuintionTang <QuintionTang@gmail.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CommonController {
    protected $_verify_code = '';
    
    public function _initialize() {
        parent::_initialize();
        $captcha_status = safty_value(getConfig("captcha_status"),0,'intval');
        if($captcha_status>0){
            $config = array('fontSize' => 18, 'length' => 4, 'imageW' => 130, 'bg' => array(57, 179, 215), 'imageH' => 42, 'useCurve' => true, 'useNoise' => true);
            $this->_verify_code = new \Think\Verify($config);
        }
       
    }

    public function index(){
        $excel_data = get_file_cache("excel_data");
        $this->assign("excel_rows",$excel_data);
        $this->display();
    }

    public function import(){
        $response = $this->fetch();
        $this->ajaxReturn(1, '', $response);
    }

    public function verify_code() {
        ob_end_clean();
        $this->_verify_code->entry();
    }

    public function export(){
        $excel_detail = array(
            "author"=>"devpoint",
            "date"=>join(" ",$artifacts_full)
        );
        // 定义导出Excel表格信息
        $sheets = array(); // Excel表信息，一维代表一个数据表
        // 定义表头
        $first_cells = array(
            array("serial","序号"),
            array("title","名称"),
            array("logo","logo"),
            array("remark","描述")
        );
        // 为表增加数据
        $excel_data = get_file_cache("excel_data");
        $first_rows_data = array();
        // 数据与上面表头对应
        foreach ($excel_data as $key => $row_info) {
            array_push($first_rows_data,array(
                "serial"=>$row_info['serial'],
                "title"=>$row_info['title'],
                "logo"=>$row_info['logo_path'],
                "remark"=>$row_info['remark']
            ));
        }
        array_push($sheets,array(
            "title"=>"前端项目流行框架",
            "cells"=>$first_cells,
            "rows"=>$first_rows_data
        ));
        $xlsName  = "Excel数据导出";
        $xlsName = $xlsName  . date('YmdHis');
        $this->exportExcel($xlsName,$sheets,$excel_detail);
    }
}

?>