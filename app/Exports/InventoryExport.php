<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;

class InventoryExport implements FromCollection
{
    protected $inventoryData;

    public function __construct($inventoryData)
    {
        $this->inventoryData = $inventoryData;
    }

    public function collection()
    {
        return $this->inventoryData;
    }
}
