<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;

class PatchStockMovementParent extends Command
{
    protected $signature = 'patch:stock-movement-parent {--chunk=500}';
    protected $description = 'Patch parent_id, parent_ref_type, party, document_code, document_datetime';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');

        StockMovement::orderBy('id')
            ->chunkById($chunkSize, function ($rows) {
                foreach ($rows as $row) {

                    switch ($row->ref_type) {

                        /** --------------------------------------------------------
                         * SALES ORDER DETAIL
                         * --------------------------------------------------------*/
                        case StockMovement::RefType_SalesOrderDetail:

                            $parent = DB::table('sales_order_details')
                                ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_details.order_id')
                                ->where('sales_order_details.id', $row->ref_id)
                                ->select(
                                    'sales_orders.id as parent_id',
                                    'sales_orders.code as document_code',
                                    'sales_orders.datetime as document_datetime',
                                    'sales_orders.customer_id as party_id',
                                    'sales_orders.customer_code as party_code',
                                    'sales_orders.customer_name as party_name'
                                )
                                ->first();

                            if ($parent) {
                                $row->parent_id = $parent->parent_id;
                                $row->parent_ref_type = 'sales_order';
                                $row->party_id = $parent->party_id;
                                $row->party_code = $parent->party_code;
                                $row->party_name = $parent->party_name;
                                $row->party_type = 'customer';
                                $row->document_code = $parent->document_code;
                                $row->document_datetime = $parent->document_datetime;
                            }
                            break;

                        /** --------------------------------------------------------
                         * SALES ORDER RETURN
                         * --------------------------------------------------------*/
                        case StockMovement::RefType_SalesOrderReturnDetail:

                            $parent = DB::table('sales_order_details')
                                ->join('sales_order_returns', 'sales_order_returns.id', '=', 'sales_order_details.return_id')
                                ->where('sales_order_details.id', $row->ref_id)
                                ->select(
                                    'sales_order_returns.id as parent_id',
                                    'sales_order_returns.code as document_code',
                                    'sales_order_returns.datetime as document_datetime',
                                    'sales_order_returns.customer_id as party_id',
                                    'sales_order_returns.customer_code as party_code',
                                    'sales_order_returns.customer_name as party_name'
                                )
                                ->first();

                            if ($parent) {
                                $row->parent_id = $parent->parent_id;
                                $row->parent_ref_type = 'sales_order_return';
                                $row->party_id = $parent->party_id;
                                $row->party_code = $parent->party_code;
                                $row->party_name = $parent->party_name;
                                $row->party_type = 'customer';
                                $row->document_code = $parent->document_code;
                                $row->document_datetime = $parent->document_datetime;
                            }
                            break;

                        /** --------------------------------------------------------
                         * PURCHASE ORDER
                         * --------------------------------------------------------*/
                        case StockMovement::RefType_PurchaseOrderDetail:

                            $parent = DB::table('purchase_order_details')
                                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.order_id')
                                ->where('purchase_order_details.id', $row->ref_id)
                                ->select(
                                    'purchase_orders.id as parent_id',
                                    'purchase_orders.code as document_code',
                                    'purchase_orders.datetime as document_datetime',
                                    'purchase_orders.supplier_id as party_id',
                                    'purchase_orders.supplier_code as party_code',
                                    'purchase_orders.supplier_name as party_name'
                                )
                                ->first();

                            if ($parent) {
                                $row->parent_id = $parent->parent_id;
                                $row->parent_ref_type = 'purchase_order';
                                $row->party_id = $parent->party_id;
                                $row->party_code = $parent->party_code;
                                $row->party_name = $parent->party_name;
                                $row->party_type = 'supplier';
                                $row->document_code = $parent->document_code;
                                $row->document_datetime = $parent->document_datetime;
                            }
                            break;

                        /** --------------------------------------------------------
                         * PURCHASE ORDER RETURN
                         * --------------------------------------------------------*/
                        case StockMovement::RefType_PurchaseOrderReturnDetail:

                            $parent = DB::table('purchase_order_details')
                                ->join('purchase_order_returns', 'purchase_order_returns.id', '=', 'purchase_order_details.return_id')
                                ->where('purchase_order_details.id', $row->ref_id)
                                ->select(
                                    'purchase_order_returns.id as parent_id',
                                    'purchase_order_returns.code as document_code',
                                    'purchase_order_returns.datetime as document_datetime',
                                    'purchase_order_returns.supplier_id as party_id',
                                    'purchase_order_returns.supplier_code as party_code',
                                    'purchase_order_returns.supplier_name as party_name'
                                )
                                ->first();

                            if ($parent) {
                                $row->parent_id = $parent->parent_id;
                                $row->parent_ref_type = 'purchase_order_return';
                                $row->party_id = $parent->party_id;
                                $row->party_code = $parent->party_code;
                                $row->party_name = $parent->party_name;
                                $row->party_type = 'supplier';
                                $row->document_code = $parent->document_code;
                                $row->document_datetime = $parent->document_datetime;
                            }
                            break;

                        /** --------------------------------------------------------
                         * STOCK ADJUSTMENT
                         * --------------------------------------------------------*/
                        case StockMovement::RefType_StockAdjustmentDetail:

                            $parent = DB::table('stock_adjustment_details')
                                ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_details.parent_id')
                                ->where('stock_adjustment_details.id', $row->ref_id)
                                ->select(
                                    'stock_adjustments.id as parent_id',
                                    'stock_adjustments.code as document_code',
                                    'stock_adjustments.datetime as document_datetime'
                                )
                                ->first();

                            if ($parent) {
                                $row->parent_id = $parent->parent_id;
                                $row->parent_ref_type = 'stock_adjustment';
                                $row->party_id = null;
                                $row->party_name = null;
                                $row->party_type = null;
                                $row->document_code = $parent->document_code;
                                $row->document_datetime = $parent->document_datetime;
                            }
                            break;

                        /** --------------------------------------------------------
                         * DEFAULT (initial_stock / manual)
                         * --------------------------------------------------------*/
                        default:
                            // biarkan null
                            break;
                    }

                    $row->save();
                }

                $this->info("Chunk processed…");
            });

        $this->info("Patch selesai!");
    }
}
