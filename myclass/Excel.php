<?php

namespace myclass;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use ZipArchive;

class Excel
{
    /* 导出Excel
     * $header Excel表头 格式如下，
     *      column   必须      excel所在列
     *      name     必须      excel中的标题
     *      key      非必须     data数据中对应的键
     *      width    非必须     列表宽度，默认为15cm
     *      iscenter 非必须     该列的数据内容是否居中显示
     *      type     非必须     data数据对应的类型，默认为text，支持timestamp，image，remote_image，autoincrement，function，如果为function时必须传入方法名
     *      method  非必须     方法名，当type为function时，必须传入方法名，会自动调用该方法
     * $data 要导出的数据信息
     * $filename 要导出的文件名*/
    public static function dataToExcel(array $header, array $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();

        // 全局垂直居中
        $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        // 全局水平居中
        // $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // 全局自动换行
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        // 设置Excel文档属性
        $spreadsheet->getProperties()
            ->setCreator("editor")                               // 作者
            ->setLastModifiedBy("editor")                     // 最后修改者
            ->setTitle("Office 2007 XLSX Test Document")          // 标题
            ->setSubject("Office 2007 XLSX Test Document")     // 副标题
            ->setDescription("这是一一个用php构建的Excel文档。") // 描述
            ->setKeywords("office php excel")                // 关键字
            ->setCategory("Test result file");                // 分类

        // $spreadsheet->createSheet(0);   // 传入索引，0代表第一个工作表-这个在多个工作表创建的时候要用
        // $spreadsheet->setActiveSheetIndex(0); // 设置Excel文件打开后默认显示第一个工作表-这个在多个工作表创建的时候要用
        $sheet = $spreadsheet->getActiveSheet(); // 获取当前激活的工作表

        $sheet->setTitle($filename); //设置工作表标题
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        // 生成表头（两行，第一行合并居中显示表名；第二行显示表头如：编号、标题、时间...）
        $row_start = 2; // 表格开始行--因为第一行要合并居中用来显示表格标题
        $min_column = $max_column = 'A'; // 设置起始列和结束列
        foreach ($header as $hv) {
            $sheet->getColumnDimension($hv['column'])->setWidth(empty($hv['width']) ? 15 : (int)$hv['width']); //设置列宽
            $sheet->getRowDimension($row_start)->setRowHeight(26); //设置表头行高
            $sheet->getStyle($hv['column'] . $row_start)->getFont()->setBold(true)->setSize(14); // 设置表头加粗、和字体大小
            $sheet->getStyle($hv['column'] . $row_start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // 设置表头内容水平居中
            $sheet->setCellValue($hv['column'] . $row_start, $hv['name']); //设置表格头部内容，如：编号、标题、时间...
            if (self::compareStr($max_column, $hv['column']) == -1) {
                $max_column = $hv['column'];
            }
        }
        // 设置表的主标题
        $min_column .= '1';
        $max_column .= '1';
        $sheet->mergeCells($min_column . ':' . $max_column); //合并单元格（从起始列合并到结束列）
        $sheet->getStyle($min_column)->getFont()->setBold(true)->setSize(16); // 设置表头加粗和字体大小
        $sheet->getStyle($min_column)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // 设置表头内容水平居中
        $sheet->getRowDimension(1)->setRowHeight(30); //设置表格标题行高
        $sheet->setCellValue($min_column, $filename);

        // 循环生成下面的数据
        $row_start = 3;
        $autoincrement_start = 1; //定义自增起始值
        foreach ($data as $dv) {
            $sheet->getRowDimension($row_start)->setRowHeight(-1); //设置表格数据行高
            foreach ($header as $hv) {
                $type = empty($hv['type']) ? 'text' : $hv['type'];
                $iscenter = empty($hv['iscenter']) ? 0 : (int)$hv['iscenter'];
                if ($iscenter == 1) $sheet->getStyle($hv['column'] . $row_start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // 设置哪些内容居中
                switch ($type) {
                    case 'timestamp':
                        if (empty($hv['key'])) {
                            $timestamp = time();
                        } else {
                            $timestamp = $dv[$hv['key']];
                        }
                        $sheet->setCellValue($hv['column'] . $row_start, date('Y-m-d H:i:s', $timestamp));
                        break;
                    case 'autoincrement':
                        $sheet->setCellValue($hv['column'] . $row_start, $autoincrement_start);
                        break;
                    default:
                        $sheet->setCellValue($hv['column'] . $row_start, $dv[$hv['key']]);
                }
            }
            $autoincrement_start++;
            $row_start++;
        }

        // MIME 协议，文件的类型，不设置，会默认html
        // header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // MIME 协议的扩展
        // $filename .= '_' . date('Y-m-d');
        // header('Content-Disposition:attachment;filename=' . $filename . '.xlsx');
        // 缓存控制
        // header('Cache-Control:max-age=0');
        // $write = IOFactory::createWriter($spreadsheet, 'Xlsx');
        // $write->save('php://output');

        // 将生成的文件，放到指定文件夹（download）以供下载
        $write = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $write->save(dirname(__DIR__) . '/media_library/download/' . $filename . '.xlsx');
        return true;
    }

    /**
     * 比较两个字符串大小
     */
    public static function compareStr($str1, $str2)
    {
        if ($str1 == $str2) return 0;
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        if ($len1 > $len2) return 1;
        if ($len1 < $len2) return -1;
        for ($i = 0; $i < $len1; $i++) {
            if ($str1[$i] == $str2[$i]) continue;
            if ($str1[$i] > $str2[$i]) return 1;
            if ($str1[$i] < $str2[$i]) return -1;
        }
        return 0;
    }

    /* Excel读取数据
     * excelFile 文件路径
     * startRow 开始行
     * endRow 结束行
     * startColumn 开始列
     * endColumn 结束列*/
    public static function readExcel($excelFile, $startRow = 1, $endRow = 0, $startColumn = 1, $endColumn = 0)
    {
        $file_type = IOFactory::identify($excelFile); //判断文件类型（返回的是字符串，例如：Excel2007、Excel5 等），目的是帮助你决定使用哪种读写方法来处理文件，从而避免打开一个不兼容的文件类型

        try {
            $excelReader = IOFactory::createReader($file_type);
            $excelReader->setReadDataOnly(true); // 只读数据，不加载样式
            $phpexcel = $excelReader->load($excelFile);
            $activeSheet = $phpexcel->getActiveSheet();
            if (!$endRow) {
                $endRow = $activeSheet->getHighestRow(); //总行数
            }
            if (!$endColumn) {
                $highestColumn = $activeSheet->getHighestColumn(); //最后列数所对应的字母，例如第2列就是B
                $endColumn = Coordinate::columnIndexFromString($highestColumn); //总列数
            }
            $data = array();
            for ($row = $startRow; $row <= $endRow; $row++) {
                for ($col = $startColumn; $col <= $endColumn; $col++) {
                    $alpha = Coordinate::stringFromColumnIndex($col);
                    $cellValue = $activeSheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $data[$row - $startRow][$alpha] = empty($cellValue) ? '' : trim($cellValue);
                }
            }
            return $data;
        } catch (\Exception $e) {
            throw new \Exception("Excel 读取失败: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // 读取压缩包中的文件
    public static function readExcelFromZip($zipFile, $excelFileInZip)
    {
        // 创建 ZipArchive 对象
        $zip = new ZipArchive();
        // 打开压缩包
        if ($zip->open($zipFile) === TRUE) {
            // 检查指定的 Excel 文件是否存在于压缩包中
            if (($index = $zip->locateName($excelFileInZip)) !== false) {
                // 从压缩包中提取指定的 Excel 文件到内存
                $data = $zip->getFromIndex($index);
                // 创建一个临时文件
                $tmpfname = tempnam(sys_get_temp_dir(), "excel");
                // 将数据写入临时文件
                file_put_contents($tmpfname, $data);

                // 使用 PhpSpreadsheet 读取 Excel 文件
                $reader = IOFactory::createReaderForFile($tmpfname);
                $spreadsheet = $reader->load($tmpfname);
                // 读取第一个工作表
                $sheet = $spreadsheet->getActiveSheet();

                $rowCount = $sheet->getHighestRow(); //数据总行数
                $colCount = $sheet->getHighestColumn(); //数据总列数
                $colCountIndex = Coordinate::columnIndexFromString($colCount); //数据总列数（字母列转数字）
                $rowDataRow = []; //每一行的数据

                //读取数据
                for ($row = 1; $row <= $rowCount; $row++) {
                    $cellData = []; //每一列的数据
                    for ($col = 1; $col <= $colCountIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                        $cellData[] = $cell;
                    }
                    $rowDataRow[] = $cellData;
                }

                // 删除临时文件
                unlink($tmpfname);
                return $rowDataRow;
            } else {
                return "The file $excelFileInZip does not exist in the zip archive.";
            }
            // 关闭压缩包
            $zip->close();
        } else {
            return "Cannot open the zip file.";
        }
    }

    // 读取压缩包中的文件
    public static function readExcelFromZip2($zipFile, $excelFileInZip)
    {
        // 创建 ZipArchive 对象
        $zip = new ZipArchive();
        // 打开压缩包
        if ($zip->open($zipFile) === TRUE) {
            //读取指定的Excel文件
            $excelData = $zip->getFromName($excelFileInZip);

            //解析Excel数据
            $reader = new Xlsx();
            $spreadsheet = $reader->loadFromString($excelData);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // 关闭压缩包
            $zip->close();
            return $sheetData;
        } else {
            return "Cannot open the zip file.\n";
        }
    }

}