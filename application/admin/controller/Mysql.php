<?php


namespace app\admin\controller;


use think\Db;

class Mysql
{
    public function index(){




        $table_result = Db::query("show tables");

        $no_show_table = array();    //不需要显示的表
        $no_show_field = array();   //不需要显示的字段
//取得所有的表名
        $tables = array();
        foreach ($table_result as $key =>$value){
            if(isset($value["Tables_in_bry"])){
                $tables[]['TABLE_NAME'] = $value["Tables_in_bry"];
            }

        }
        //


//循环取得所有表的备注及表中列消息
        foreach ($tables as $k=>$v) {
            $sql  = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.TABLES ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}'";

            $table_result = Db::query("$sql");
            if ($table_result ) {
                $tables[$k]['TABLE_COMMENT'] = $table_result[0]['TABLE_COMMENT'];
            }

            $sql  = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}'";

            $field_result =Db::query($sql);

            $tables[$k]['COLUMN'] = $field_result;
        }

        $html = '';
//循环所有表
        foreach ($tables as $k=>$v) {
            $html .= '	<h3>' . ($k + 1) . '、' . $v['TABLE_COMMENT'] .'  （'. $v['TABLE_NAME']. '）</h3>'."\n";
            $html .= '	<table border="1" cellspacing="0" cellpadding="0" width="100%">'."\n";
            $html .= '		<tbody>'."\n";
            $html .= '			<tr>'."\n";
            $html .= '				<th>字段名</th>'."\n";
            $html .= '				<th>数据类型</th>'."\n";
            $html .= '				<th>默认值</th>'."\n";
            $html .= '				<th>允许非空</th>'."\n";
            $html .= '				<th>自动递增</th>'."\n";
            $html .= '				<th>备注</th>'."\n";
            $html .= '			</tr>'."\n";

            foreach ($v['COLUMN'] as $f) {

                $html .= '			<tr>'."\n";
                $html .= '				<td class="c1">' . $f['COLUMN_NAME'] . '</td>'."\n";
                $html .= '				<td class="c2">' . $f['COLUMN_TYPE'] . '</td>'."\n";
                $html .= '				<td class="c3">' . $f['COLUMN_DEFAULT'] . '</td>'."\n";
                $html .= '				<td class="c4">' . $f['IS_NULLABLE'] . '</td>'."\n";
                $html .= '				<td class="c5">' . ($f['EXTRA']=='auto_increment'?'是':'&nbsp;') . '</td>'."\n";
                $html .= '				<td class="c6">' . $f['COLUMN_COMMENT'] . '</td>'."\n";
                $html .= '			</tr>'."\n";

            }
            $html .= '		</tbody>'."\n";
            $html .= '	</table>'."\n";
        }
        $text = "<!doctype html>
                <html>
                <head>
                <meta charset=\"utf-8\">
                <title>数据库数据字典生成代码</title>
                <meta name=\"generator\" content=\"ThinkDb V1.0\" />
            
                <meta name=\"copyright\" content=\"2008-2014 Tensent Inc.\" />
                <style>
                body, td, th { font-family: \"微软雅黑\"; font-size: 14px; }
                .warp{margin:auto; width:900px;}
                .warp h3{margin:0px; padding:0px; line-height:30px; margin-top:10px;}
                table { border-collapse: collapse; border: 1px solid #CCC; background: #efefef; }
                table th { text-align: left; font-weight: bold; height: 26px; line-height: 26px; font-size: 14px; text-align:center; border: 1px solid #CCC; padding:5px;}
                table td { height: 20px; font-size: 14px; border: 1px solid #CCC; background-color: #fff; padding:5px;}
                .c1 { width: 120px; }
                .c2 { width: 120px; }
                .c3 { width: 150px; }
                .c4 { width: 80px; text-align:center;}
                .c5 { width: 80px; text-align:center;}
                .c6 { width: 270px; }
                </style>
                </head>
                <body>
                <div class=\"warp\">
                    <h1 style=\"text-align:center;\">数据字典生成代码</h1>
                    ".$html."
                </div>
                </body>
                </html>";
        return $text;
    }
}
