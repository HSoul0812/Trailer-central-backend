<?php

use App\Repositories\Dms\StockRepositoryInterface;
use Brick\Money\Money;

/** @var array $data */

// grand totals
$allPartTotalQty = 0;
$allPartTotalCost = 0;
$allPartTotalPrice = 0;
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
</style>
<div class="row">
    <table id="partsReportTable">
        <tr>
            <th>Sku/Stock</th>
            <th>Title</th>
            <th>Cost</th>
            <th>Price</th>
            <th></th>
        </tr>
        <?php

        foreach($data as $partId => $reportPart) {
        $firstPart = reset($reportPart);
        $isPartType = $firstPart['part']->source === StockRepositoryInterface::STOCK_TYPE_PARTS;

        $partTotalQty = 0;
        $partTotalCost = 0;
        $partTotalPrice = 0;
        ?>
        <tr>
            <td><?= $firstPart['part']->reference ?></td>
            <td><?= $firstPart['part']->title ?></td>
            <td><?= $firstPart['part']->dealer_cost ?></td>
            <td><?= $firstPart['part']->price ?></td>
            <td>
                <table style="width: 100%;">
                    <tr>
                        <?php if ($isPartType) : ?>
                        <th>Bin name</th>
                        <?php endif; ?>
                        <th>Qty</th>
                        <th>Total Cost</th>
                        <th>Total Price</th>
                    </tr>
                    <?php
                    foreach($reportPart as $reportBinQty) {
                    $part = $reportBinQty['part'];

                    $partTotalQty += $part->qty;
                    $partTotalCost += $part->dealer_cost * $part->qty;
                    $partTotalPrice += $part->price * $part->qty;
                    ?>
                    <?php if ($isPartType) : ?>
                        <tr>
                            <td><?= $part->bin_name ?></td>
                            <td><?= $part->qty ?></td>
                            <td><?= $part->dealer_cost * $part->qty ?></td>
                            <td><?= $part->price * $part->qty ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php
                    }

                    $allPartTotalQty += $partTotalQty;
                    $allPartTotalCost += $partTotalCost;
                    $allPartTotalPrice += $partTotalPrice;
                    ?>
                    <tr class="<?= $isPartType ? 'totals-row' : '' ?>">
                        <?php if ($isPartType) : ?>
                            <td></td>
                        <?php endif; ?>
                        <td class="text-right font-weight-bold">
                            <?= $partTotalQty ?>
                        </td>
                        <td class="text-right font-weight-bold">
                            <?= Money::of($partTotalCost, 'USD') ?>
                        </td>
                        <td class="text-right font-weight-bold">
                            <?= Money::of($partTotalPrice, 'USD') ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td class="font-weight-bold" colspan="4">Totals</td>
            <td>
                <table style="width: 100%;">
                    <tr class="totals-row">
                        <td></td>
                        <td class="text-right font-weight-bold">
                            <?= $allPartTotalQty ?>
                        </td>
                        <td class="text-right font-weight-bold">
                            <?= Money::of($allPartTotalCost, 'USD') ?>
                        </td>
                        <td class="text-right font-weight-bold">
                            <?= Money::of($allPartTotalPrice, 'USD') ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
