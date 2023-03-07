<?php

use App\Models\Inventory\Inventory;
use Illuminate\Support\LazyCollection;
use App\Services\Export\FilesystemPdfExporter;

/** @var $data array{data:Inventory[]|LazyCollection,orientation: string}  * */

$dateFormat = isset($data['orientation']) && $data['orientation'] === FilesystemPdfExporter::ORIENTATION_LANDSCAPE ?
    'm/d/Y H:i' :
    'm/d/Y';
?>
<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    th {
        background-color: #eeeeee;
        padding: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    td {
        overflow-wrap: break-word;
        word-break: break-all;
    }

    td, th {
        border: 1px solid #dddddd;
        text-align: left;
        vertical-align: top;
        padding: 8px;
        font-size: 11px;
    }

    .totals-row td {
        border-top: 2px solid #dddddd;
        background-color: #eeeeee;
        font-weight: bold;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .text-right {
        text-align: right;
    }

    .small {
        font-size: 10px;
    }

    .nb {
        word-break: initial;
        white-space: nowrap;
    }

    table {
        table-layout: fixed;
    }
</style>
<div class="row">
    <table id="bulkInventoryPrint">
        <tr>
            <th>Stock #</th>
            <th>Title</th>
            <th>Category</th>
            <th>Manufacturer</th>
            <th style="width: 140px">Notes</th>
            <th>Model</th>
            <th>Status</th>
            <th>Sales Price</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>VIN</th>
        </tr>
        @foreach($data['data'] as $inventory)
            <tr>
                <td>
                    {{ $inventory->stock }}
                </td>
                <td>
                    {{ $inventory->title }}
                </td>
                <td>
                    {{ $inventory->category_label }}
                </td>
                <td style="width: 90px">
                    {{ $inventory->manufacturer }}
                </td>
                <td style="width: 140px">
                    <span class="small">{{ $inventory->notes }}</span>
                </td>
                <td>
                    {{ $inventory->model }}
                </td>
                <td>
                    {{ $inventory->status_label }}
                </td>
                <td class="text-right">
                    ${{ number_format($inventory->sales_price ?: 0, 2) }}
                </td>
                <td>
                    <span
                        class="small">{{ $inventory->created_at->format($dateFormat) }}</span>
                </td>
                <td>
                    <span
                        class="small">{{ $inventory->updated_at->format($dateFormat) }}</span>
                </td>
                <td>
                    {{ $inventory->vin }}
                </td>
            </tr>
        @endforeach
    </table>
</div>
