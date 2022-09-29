<?php

use App\Models\Inventory\Inventory;
use Illuminate\Support\LazyCollection;

/** @var $data Inventory[]|LazyCollection */
?>
<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    th {
        background-color: #eeeeee;
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

    .font-weight-bold{
        font-weight: bold;
    }

    .text-right{
        text-align: right;
    }

    .small{
        font-size: 8px;
    }

    .nb{
        word-break: initial;
        white-space: nowrap;
    }
</style>

<div class="row">
    <table id="bulkInventoryPrint">
        <tr>
            <th>Stock #</th>
            <th>Title</th>
            <th>Category</th>
            <th>Manufacturer</th>
            <th>Notes</th>
            <th>Model</th>
            <th>Status</th>
            <th>Sales price</th>
            <th>True cost</th>
            <th>Created at</th>
        </tr>
        @foreach($data as $inventory)
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
                <td>
                    {{ $inventory->manufacturer }}
                </td>
                <td>
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
                <td class="text-right">
                    ${{ number_format($inventory->true_cost ?: 0, 2) }}
                </td>
                <td>
                    <span class="small nb">{{ $inventory->created_at }}</span>
                </td>
            </tr>
        @endforeach
    </table>
</div>
