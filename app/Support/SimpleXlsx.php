<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * 极简 XLSX 写出器（零依赖）。
 *
 * 为什么自己写：
 *   项目 composer.json 里**没有任何 Excel 库**（无 phpoffice/phpspreadsheet、无 maatwebsite/excel），
 *   而前端「导出」按钮下载的文件名写死成 `xxx.xlsx`（见 UserInfoImport.vue:95、
 *   ProductBaseImport.vue 等），直接吐 CSV 会被 Excel 提示"格式与扩展名不符"。
 *   容器里 zip 扩展是有的（php -m 已确认 zip/zlib 都在），而 .xlsx 本质就是一个
 *   装着几段 XML 的 zip 包 —— 所以这里手工拼一个合规的最小工作簿，不引第三方依赖。
 *
 * 用法：
 *   $xlsx = new SimpleXlsx('用户信息');
 *   $xlsx->addHeader(['编号', '昵称']);
 *   $xlsx->addRow([1, '张三']);
 *   $xlsx->download('用户信息.xlsx');   // 或 ->toString() 取二进制
 *
 * 说明：
 *   - 字符串统一走 inlineStr，省掉 sharedStrings 表，体积略大但结构最简单、最不容易出错；
 *   - 数字写成 n 型，Excel 会当数值处理；其余一律当文本，避免 "0001" 被吃掉前导零。
 */
class SimpleXlsx
{
    /** @var string 工作表名（Excel 限制 31 字符，且不能含 : \ / ? * [ ]） */
    private $sheetName;

    /** @var array 行数据，每行是 [列索引 => [值, 是否数值]] */
    private $rows = [];

    public function __construct(string $sheetName = 'Sheet1')
    {
        $clean = preg_replace('#[:\\\\/?*\[\]]#u', '_', $sheetName);
        $this->sheetName = mb_substr($clean ?: 'Sheet1', 0, 31);
    }

    /**
     * 追加一行
     * @param array $cells 一维数组，键会被忽略、按顺序排成 A/B/C...
     */
    public function addRow(array $cells): self
    {
        $row = [];
        foreach (array_values($cells) as $i => $v) {
            $row[$i] = $this->normalize($v);
        }
        $this->rows[] = $row;
        return $this;
    }

    public function addHeader(array $cells): self
    {
        return $this->addRow($cells);
    }

    /**
     * 判断一个值该不该写成数值型。
     * 关键点：字符串形态的数字**不转数值** —— 手机号、身份证、以 0 开头的编号
     * 一旦变成数值就会丢前导零或显示成科学计数法，这是导出功能最常见的坑。
     */
    private function normalize($v): array
    {
        if (is_int($v) || is_float($v)) {
            return [$v, true];
        }
        if ($v === null) {
            return ['', false];
        }
        if (is_bool($v)) {
            return [$v ? 1 : 0, true];
        }
        return [(string)$v, false];
    }

    private function colName(int $index): string
    {
        $name = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $name = chr(65 + $mod) . $name;
            $index = intdiv($index - 1, 26);
        }
        return $name;
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function sheetXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($this->rows as $r => $cells) {
            $rowNo = $r + 1;
            $xml .= '<row r="' . $rowNo . '">';
            foreach ($cells as $c => $pair) {
                [$val, $isNum] = $pair;
                $ref = $this->colName($c) . $rowNo;
                if ($isNum) {
                    $xml .= '<c r="' . $ref . '"><v>' . $val . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">'
                        . self::esc((string)$val) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }
        return $xml . '</sheetData></worksheet>';
    }

    /**
     * 组装成 xlsx 二进制
     */
    public function toString(): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        if ($tmp === false) {
            throw new RuntimeException('无法创建临时文件');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new RuntimeException('无法创建 xlsx 压缩包');
        }

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::esc($this->sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());
        $zip->close();

        $bin = file_get_contents($tmp);
        @unlink($tmp);
        if ($bin === false) {
            throw new RuntimeException('读取 xlsx 内容失败');
        }
        return $bin;
    }

    /**
     * 直接作为下载响应返回（Lumen Response）
     * @param string $filename 下载文件名，需带 .xlsx
     */
    public function download(string $filename = 'export.xlsx')
    {
        // 只生成一次：toString() 每次都会落一个临时文件，调两遍纯属浪费。
        $bin = $this->toString();

        return response($bin, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
            'Content-Length' => (string)strlen($bin),
            'Cache-Control' => 'no-store',
        ]);
    }
}
