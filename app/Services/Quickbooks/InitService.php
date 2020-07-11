<?php


namespace App\Services\Quickbooks;


use App\Models\CRM\Quickbooks\Item;
use App\Models\CRM\Quickbooks\ItemNew;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class InitService
 *
 *
 * @package App\Services\Quickbooks
 */
class InitService
{
    public function initQbItemsNewForDealer($dealerId)
    {
        DB::beginTransaction();

        try {
            // 1-2
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Inventories',
                    'type' => 'Category',
                    'sub_item' => 0,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $lastId = $item->id;

            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Unit Sales',
                    'description' => 'An item for all the inventories.',
                    'type' => 'NonInventory',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();

            // 3-4
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Parts',
                    'type' => 'Category',
                    'sub_item' => 0,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $lastId = $item->id;

            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Part Sales',
                    'description' => 'An item for all the parts.',
                    'type' => 'NonInventory',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();

            // 5-6
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'System Wide',
                    'type' => 'Category',
                    'sub_item' => 0,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $lastId = $item->id;

            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Discounts',
                    'description' => 'An item for all the discounts.',
                    'type' => 'Service',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();

            // 7-8
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Services',
                    'type' => 'Category',
                    'sub_item' => 0,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $lastId = $item->id;

            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Service Income',
                    'description' => 'An item for all the services.',
                    'type' => 'Service',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();

            // 9-11
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Taxes and Fees',
                    'type' => 'Category',
                    'sub_item' => 0,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $lastId = $item->id;

            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Taxes',
                    'description' => 'An item for all the taxes.',
                    'type' => 'Service',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();
            $item = new ItemNew(
                [
                    'dealer_id' => $dealerId,
                    'name' => 'Fees',
                    'description' => 'An item for all the fees.',
                    'type' => 'Service',
                    'sub_item' => 1,
                    'parent_id' => $lastId,
                    'is_default' => 0,
                    'in_simple_mode' => 1,
                ]
            );
            $item->saveOrFail();

            DB::commit();
            return true;

        } catch (\Throwable $e) {
            Log::error("Qb Items New init exception: " . $e->getMessage());
            print $e->getMessage();

            DB::rollBack();
            throw $e; // throw it again?
        }
    }
}
