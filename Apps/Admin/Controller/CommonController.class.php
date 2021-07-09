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
class CommonController extends Controller {
    protected $_name = '';   //模块名称
    protected $_site_domain = '';
    protected $_site_url = "";
    protected $_mod;
    protected $_pk;
    protected $list_extend = true;
    public function _initialize() {
        $Input = new \Org\Util\Input;
        $Input::noGPC();

        $settings = array(
            "site_name"=>"Craymon Admin",
            "attach_url"=>"./",
            "attach_path"=>"./data/attach/",
            "attr_allow_size"=>10240000,
            "attr_allow_exts"=>"doc,docx,xls,xlsx"
        );
        C($settings);

        $this -> assign('server', $_SERVER);
        $this -> assign('Think', array('get' => $_GET, 'post' => $_POST, 'request' => $_REQUEST, 'server' => $_SERVER, 'cookie' => $_COOKIE, 'session' => $_SESSION, ));
        $site_domain = $_SERVER['HTTP_HOST'];
        $site_scheme = $_SERVER['REQUEST_SCHEME'];
        $this->_name = $this->get_controller_name();
       
        $this->_site_domain = $site_domain;

        $this -> assign('settings',$settings);
        $this->assign('site_domain', $site_domain);
        $this->assign('dom_serial', time());
        $this->assign('action_name',strtolower(ACTION_NAME));
    }

    /**
     * 获取控制器名称
     */
    private function get_controller_name(){
        $controller_name = CONTROLLER_NAME;
        return $controller_name;
    }
    
    public function index() {

        $this->display();
    }

    protected function ajaxReturn($status = 1, $msg = '', $data = '', $dialog = '') {
        parent::ajaxReturn(array('status' => $status, 'msg' => $msg, 'data' => $data, 'dialog' => $dialog));
    }

    protected function remoteReturn($status){
        if (!$status){
            $httpStatus = 'HTTP/1.1 404 Not Found';
            header($httpStatus);
        } else{
            parent::ajaxReturn(array('status' => $status));
        }
        
    }

    /**
     * set_header 设置数据通信HEADER
     * @param string $str_post 发送的数据，为JSON格式的字符串
     * @return array content
     */
    private function set_header($str_post){
        $array_header_info = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($str_post)
        );
        return $array_header_info;
    }


    protected function exportExcel($expTitle,$xlsSheets,$detail){
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $fileName = $expTitle;
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
        // Excel列名称
		$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        foreach ($xlsSheets as $index => $sheet_info) {
            $sheet_title = $sheet_info['title'];
            if ($index>0){
                // Excel默认已经建好的数据表，超过一张需要执行这里创建一个工作表
                $newSheet = new \PHPExcel_Worksheet($objPHPExcel, $sheet_title); //创建一个工作表
                $objPHPExcel->addSheet($newSheet);
            } else {
                $objPHPExcel->getActiveSheet($index)->setTitle($sheet_title);
            }
            $expCellName = $sheet_info['cells'];
            $expTableData = $sheet_info['rows'];
            $cellNum = count($expCellName);
            $dataNum = count($expTableData);
            $cellmerget = "";
            $cellWidths = array();
            $sheet_head_title = $sheet_title;
            // 下面需要为每个工作表定义宽度
            switch ($index) {
                case 1: // 每张表的索引从 0 开始计算
                    $cellmerget = 'A1:E1';
                    $cellWidths=array(16,16,16,28,16);
                    break;
                default:
                    $cellmerget = 'A1:D1';
                    $sheet_head_title = $sheet_title ;
                    $cellWidths=array(16,16,16,36);
                    break;
            }
            $activeSheet = $objPHPExcel->setActiveSheetIndex($index);

            for($i=0;$i<$cellNum;$i++){
                $currentCellName = $cellName[$i];
                $activeSheet->getRowDimension(1)->setRowHeight(36);
                $activeSheet->getColumnDimension($currentCellName)->setWidth($cellWidths[$i]);
                $activeSheet->getStyle($currentCellName.'1')->getFont()->setSize(12)->setBold(true);
                $activeSheet->getStyle($currentCellName.'1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }

            $activeSheet->mergeCells($cellmerget);//合并单元格
            $activeSheet->setCellValue('A1', $sheet_head_title);
            $activeSheet->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('A1')->getFont()->setSize(20);
            $activeSheet->getRowDimension(1)->setRowHeight(50);
            $styleThinBlackBorderOutline = array(  
                    'borders' => array (  
                        'outline' => array (  
                                'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,   //设置border样式
                                'color' => array ('argb' => 'FF9b9b9b'),          //设置border颜色  
                        ),  
                ),  
            );  
            for($i=0;$i<$cellNum;$i++){
                $currentCellName = $cellName[$i];
                $activeSheet->getRowDimension(2)->setRowHeight(36);
                $activeSheet->getColumnDimension($currentCellName)->setWidth($cellWidths[$i]);
                $activeSheet->setCellValue($currentCellName.'2', $expCellName[$i][1]);
                $activeSheet->getStyle($currentCellName.'2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $activeSheet->getStyle($currentCellName.'2')->getFill()->getStartColor()->setARGB('FFc6efcd');
                $activeSheet->getStyle($currentCellName.'2')->getFont()->setSize(12)->setBold(true);
                $activeSheet->getStyle($currentCellName.'2')->applyFromArray($styleThinBlackBorderOutline);  
                $activeSheet->getStyle($currentCellName.'2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $activeSheet->freezePane($currentCellName.'3');  // 锁定表头，3 意味着锁定第3行上面的
            }
            switch ($index) {
                case 1:

                    break;
                default:
                    $start_row_index = 3; // 数据开始索引行
                    for($i1=0;$i1<$dataNum;$i1++){
                        $objPHPExcel->getActiveSheet()->getRowDimension($i1+3)->setRowHeight(60);
                        for($j1=0;$j1<$cellNum;$j1++){
                            if ($j1===2){
                                $logo_path = $expTableData[$i1][$expCellName[$j1][0]];
                                if ($logo_path!=="" && file_exists($logo_path)){
                                    $objDrawing = new \PHPExcel_Worksheet_Drawing();
                                    $objDrawing->setPath($logo_path);
                                    $objDrawing->setHeight(60);
                                    $objDrawing->setWidth(60);
                                
                                    $objDrawing->setOffsetX(5);
                                    $objDrawing->setOffsetY(5);
                                    $objDrawing->setCoordinates($cellName[$j1].($i1+$start_row_index));
                                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                                } else {
                                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j1].($i1+$start_row_index), "");
                                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setWrapText(true);
                                }
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j1].($i1+$start_row_index), $expTableData[$i1][$expCellName[$j1][0]]);
                                $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                                $objPHPExcel->getActiveSheet()->getStyle($cellName[$j1].($i1+$start_row_index))->getAlignment()->setWrapText(true);
                            }
                        }
                    }
                    break;
            }
            
        }
        $objPHPExcel->setActiveSheetIndex(0);

		header('pragma:public');
		header('Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8;name="'.$fileName.'.xlsx"');
		header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
    }
}
?>