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
use App\Models\SalesOrder;
use App\Models\Setting;
use Modules\Admin\Services\CashierSessionService;
use Modules\Admin\Services\FinanceTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\SalesOrderReturn\GetDataRequest;
use Modules\Admin\Http\Requests\SalesOrderReturn\SaveRequest;
use Modules\Admin\Services\FinanceAccountService;
use Modules\Admin\Services\SalesOrderReturnDetailService;
use Modules\Admin\Services\SalesOrderReturnRefundService;
use Modules\Admin\Services\SalesOrderReturnService;

class SalesOrderReturnController extends Controller
{
    public function __construct(
        protected SalesOrderReturnService $service,
        protected SalesOrderReturnRefundService $refundService,
        protected FinanceTransactionService $financeTransactionService,
        protected CashierSessionService $cashierSessionService,
        protected FinanceAccountService $financeAccountService,
        protected SalesOrderReturnDetailService $detailService,
    ) {}

    public function index()
    {
        $this->authorize("viewAny", SalesOrder::class);
        return inertia('sales-order-return/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize("viewAny", SalesOrder::class);
        $orders = $this->service->getData($request->validated())->withQueryString();
        return JsonResponseHelper::success($orders);
    }

    public function editor($id = 0)
    {
        if (!$id) {
            $this->authorize("create", SalesOrder::class);
            $order = $this->service->createOrder();
            return redirect(route('admin.sales-order-return.edit', $order->id));
        }

        $order = $this->service->findOrderOrFail($id);
        $this->authorize("update", $order);
        $order = $this->service->editOrder($order);
        return inertia('sales-order-return/Editor', [
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
        $order = $this->service->findOrderOrFail($id);
        $this->authorize('cancel', $order);
        $this->service->cancelOrder($order);
        return JsonResponseHelper::success(
            ['id' => $order->id],
            "Transaksi #$order->formatted_id telah dibatalkan."
        );
    }


    public function close(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('id'));
        $this->authorize('update', $order);
        $this->service->closeOrder($order, $request->all());
        return JsonResponseHelper::success($order, "Order telah selesai.");
    }

    public function delete($id)
    {
        $order = $this->service->findOrderOrFail($id);
        $this->authorize('delete', $order);
        $this->service->deleteOrder($order);
        return JsonResponseHelper::success($order, "Transaksi #$order->formatted_id telah dihapus.");
    }

    public function detail($id)
    {
        $order = $this->service->getOrderWithDetails($id);
        $this->authorize('view', $order);
        return inertia('sales-order-return/Detail', [
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
            $pdf = Pdf::loadView('modules.admin.pages.sales-order-return.print-' . $size, [
                'item' => $item,
                'pdf'  => true,
            ])
                ->setPaper($size, 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            return $pdf->download(env('APP_NAME') . '_' . $item->formatted_id . '.pdf');
        }

        return view('modules.admin.pages.sales-order-return.print-' . $size, [
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
        $this->authorize('update', $detail->parent);
        $this->detailService->updateItem($detail, $request->all());
        $detail->loadMissing('product');
        return JsonResponseHelper::success($detail, 'Item telah diperbarui.');
    }

    public function removeItem(Request $request)
    {
        $item = $this->detailService->findItemOrFail($request->id);
        $this->authorize('update', $item->parent);
        $this->detailService->deleteItem($item);
        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function addPayment(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('order_id'));
        $this->authorize('update', $order);
        $this->refundService->addPayments($order, $request->post('payments', []));
        return JsonResponseHelper::success($order, "Pembayaran berhasil dicatat.");
    }

    public function deletePayment(Request $request)
    {
        $payment = $this->refundService->findOrFail($request->id);
        $this->authorize('update', $payment->order);
        $order = $this->refundService->deletePayment($payment);
        return JsonResponseHelper::success($order, "Pembayaran berhasil dihapus.");
    }
}
