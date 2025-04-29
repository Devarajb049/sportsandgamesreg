<?php
class SimpleXLSX {
    private $sst = [];
    private $sheet_names = [];
    private $sheets = [];
    
    public function __construct($filename) {
        if (!$this->parse($filename)) {
            throw new Exception('Failed to parse XLSX file');
        }
    }
    
    private function parse($filename) {
        $zip = new ZipArchive;
        if ($zip->open($filename) !== TRUE) return false;

        // Read shared strings
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->si as $si) {
                if (isset($si->t)) {
                    $this->sst[] = (string) $si->t;
                } else {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string) $r->t;
                    }
                    $this->sst[] = $t;
                }
            }
        }

        // Read sheet names
        if (($xml = $zip->getFromName('xl/workbook.xml')) !== false) {
            $sx = simplexml_load_string($xml);
            foreach ($sx->sheets->sheet as $sheet) {
                $this->sheet_names[] = (string) $sheet['name'];
            }
        }

        // Read sheet data
        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet_xml !== false) {
            $sx = simplexml_load_string($sheet_xml);
            $rows = [];
            foreach ($sx->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $v = isset($c->v) ? (string) $c->v : '';
                    if (isset($c['t']) && $c['t'] == 's') {
                        $v = $this->sst[(int) $v] ?? '';
                    }
                    $cells[] = $v;
                }
                $rows[] = $cells;
            }
            $this->sheets[] = $rows;
        }

        $zip->close();
        return true;
    }

    public function rows($sheet = 0) {
        return $this->sheets[$sheet] ?? [];
    }
}
