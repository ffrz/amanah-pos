<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 *
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 *
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReturn;
use App\Models\Setting;
use Modules\Admin\Services\CashierSessionService;
use Modules\Admin\Services\FinanceTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\PurchaseOrderReturn\GetDataRequest;
use Modules\Admin\Http\Requests\PurchaseOrderReturn\SaveRequest;
use Modules\Admin\Services\FinanceAccountService;
use Modules\Admin\Services\PurchaseOrderReturnDetailService;
use Modules\Admin\Services\PurchaseOrderReturnRefundService;
use Modules\Admin\Services\PurchaseOrderReturnService;

class PurchaseOrderReturnController extends Controller
{
    public function __construct(
        protected PurchaseOrderReturnService $service,
        protected PurchaseOrderReturnRefundService $refundService,
        protected FinanceTransactionService $financeTransactionService,
        protected CashierSessionService $cashierSessionService,
        protected FinanceAccountService $financeAccountService,
        protected PurchaseOrderReturnDetailService $detailService,
    ) {}


    public function index()
    {
        $this->authorize("viewAny", PurchaseOrderReturn::class);
        return inertia('purchase-order-return/Index');
    }


    public function data(GetDataRequest $request)
    {
        $this->authorize("viewAny", PurchaseOrderReturn::class);
        $orders = $this->service->getData($request->validated())->withQueryString();
        return JsonResponseHelper::success($orders);
    }


    public function add(Request $request)
    {
        $this->authorize("create", PurchaseOrderReturn::class);

        $data = [
            'code' => $request->post('code'),
        ];

        if ($request->isMethod(Request::METHOD_POST)) {
            $PurchaseOrder = PurchaseOrder::where('code', $data['code'])
                ->first();

            if (!$PurchaseOrder) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['code' => 'Transaksi penjualan tidak ditemukan.']);
            }

            if ($PurchaseOrder->status !== PurchaseOrder::Status_Closed) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['code' => 'Transaksi penjualan belum ditutup / tidak valid.']);
            }

            $PurchaseOrderReturn = $this->service->createOrderReturn($PurchaseOrder);
            return redirect(route('admin.purchase-order-return.edit', $PurchaseOrderReturn->id));
        }

        return inertia('purchase-order-return/Create', [
            'data' => $data,
        ]);
    }


    public function editor($id)
    {
        $order = $this->service->findOrderOrFail($id);
        $this->authorize("update", $order);
        $order = $this->service->editOrder($order);
        return inertia('purchase-order-return/Editor', [
            'data' => $order,
            'accounts' => $this->financeAccountService->getFinanceAccounts(),
            'settings' => [
                'default_payment_mode' => Setting::value('pos.default_payment_mode', 'cash'),
                'default_print_size'   => Setting::value('pos.default_print_size', '58mm'),
                'after_payment_action' => Setting::value('pos.after_payment_action', 'new-order'),
            ]
        ]);
    }


    public function update(SaveRequest $request)
    {
        $order = $this->service->findOrderOrFail($request->post('id'));
        $this->authorize('update', $order);
        $this->service->updateOrder($order, $request->validated());
        return JsonResponseHelper::success($order, 'Order telah diperbarui');
    }


    public function cancel($id)
    {
        $orderReturn = $this->service->findOrderOrFail($id, []);
        $this->authorize('cancel', $orderReturn);
        $this->service->cancelOrder($orderReturn);
        return JsonResponseHelper::success(
            ['id' => $orderReturn->id],
            "Transaksi Retur #$orderReturn->code telah dibatalkan."
        );
    }

    public function close(Request $request)
    {
        $orderReturn = $this->service->findOrderOrFail($request->id);
        $this->authorize('update', $orderReturn);
        $this->service->closeOrderReturn($orderReturn, $request->all());
        return JsonResponseHelper::success($orderReturn, "Order telah selesai.");
    }


    public function delete($id)
    {
        $orderReturn = $this->service->findOrderOrFail($id);
        $this->authorize('delete', $orderReturn);
        $this->service->deleteOrderReturn($orderReturn);
        return JsonResponseHelper::success($orderReturn, "Transaksi #$orderReturn->code telah dihapus.");
    }

    public function detail($id)
    {
        $order = $this->service->getOrderWithDetails($id);
        $this->authorize('view', $order);
        return inertia('purchase-order-return/Detail', [
            'data' => $order,
            'accounts' => $this->financeAccountService->getFinanceAccounts()
        ]);
    }

    public function print($id, Request $request)
    {
        $item = $this->service->findOrderOrFail($id);

        $this->authorize('view', $item);

        $size = $request->get('size', 'a4');
        if (!in_array($size, ['a4', '58mm'])) {
            $size = 'a4';
        }

        if ($request->get('output') == 'pdf') {
            $pdf = Pdf::loadView('modules.admin.pages.purchase-order-return.print-' . $size, [
                'item' => $item,
                'pdf'  => true,
            ])
                ->setPaper($size, 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            return $pdf->download(env('APP_NAME') . '_' . $item->code . '.pdf');
        }

        return view('modules.admin.pages.purchase-order-return.print-' . $size, [
            'item' => $item,
        ]);
    }


    public function addItem(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('order_id', null));
        $this->authorize('update', $order);
        $detail = $this->detailService->addItem($order, $request->all());
        $detail->loadMissing('product');
        return JsonResponseHelper::success([
            'item' => $detail,
            'mergeItem' => $request->post('merge', false),
        ], 'Item telah ditambahkan');
    }


    public function updateItem(Request $request)
    {
        $detail = $this->detailService->findItemOrFail($request->post('id'));
        $this->authorize('update', $detail->return);
        $this->detailService->updateItem($detail, $request->all());
        $detail->loadMissing('product');
        return JsonResponseHelper::success($detail, 'Item telah diperbarui.');
    }


    public function removeItem(Request $request)
    {
        $item = $this->detailService->findItemOrFail($request->id);
        $this->authorize('update', $item->return);
        $this->detailService->deleteItem($item);
        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function addRefund(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('order_id'));
        $this->authorize('update', $order);
        $this->refundService->addRefund($order, $request->all());
        return JsonResponseHelper::success($order, "Pengembalian dana berhasil dicatat.");
    }

    public function deleteRefund(Request $request)
    {
        $refund = $this->refundService->findOrFail($request->id);
        $this->authorize('update', $refund->PurchaseOrderReturn);
        $PurchaseOrderReturn = $this->refundService->deleteRefund($refund);
        return JsonResponseHelper::success($PurchaseOrderReturn, "Pengembalian dana berhasil dihapus.");
    }
}
