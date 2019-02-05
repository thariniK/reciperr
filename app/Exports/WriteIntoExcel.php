<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WriteIntoExcel implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public $title;
    public $data;
    public $header;

    public function __construct($title, $data, $header) {
        $this->title = $title;
        $this->data = $data;
        $this->header = $header;
    }

    public function collection(){
        return collect($this->data);
    }
    
    public function title(): string {
        return $this->title;
    }
    
    public function headings(): array {
        return $this->header;
    }
    
}
