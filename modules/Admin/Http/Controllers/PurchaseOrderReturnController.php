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
use App\Models\PurchaseOrderReturn;
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
        protected PurchaseOrderReturnDetailService $detailService,
        protected PurchaseOrderReturnRefundService $refundService,
        protected FinanceAccountService $financeAccountService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', PurchaseOrderReturn::class);
        return inertia('purchase-order-return/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', PurchaseOrderReturn::class);
        $orders = $this->service->getData($request->validated())
            ->withQueryString();
        return JsonResponseHelper::success($orders);
    }

    public function editor($id = 0)
    {
        if (!$id) {
            $this->authorize('create', PurchaseOrderReturn::class);
            $order = $this->service->createOrder();
            return redirect(route('admin.purchase-order-return.edit', $order->id));
        }

        $order = $this->service->editOrder($id);
        $this->authorize('update', $order);
        return inertia('purchase-order-return/Editor', [
            'data' => $order,
            'accounts' => $this->financeAccountService->getFinanceAccounts(),
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
            "Transaksi #$order->code telah dibatalkan."
        );
    }

    public function delete($id)
    {
        $order = $this->service->findOrderOrFail($id);
        $this->authorize('delete', $order);
        $order = $this->service->deleteOrder($order);
        return JsonResponseHelper::success($order, "Transaksi #$order->code telah dihapus.");
    }

    public function detail($id)
    {
        $order = $this->service->findOrderOrFail($id);
        $this->authorize('view', $order);
        return inertia('purchase-order-return/Detail', [
            'data' => $order,
            'accounts' => $this->financeAccountService->getFinanceAccounts()
        ]);
    }

    public function addItem(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('order_id'));
        $this->authorize('update', $order);
        $merge = $request->post('merge', false);
        $item = $this->detailService->addItem($order, $request->all(), $merge);
        return JsonResponseHelper::success([
            'item' => $item,
            'mergeItem' => $merge,
        ], 'Item telah ditambahkan');
    }

    public function updateItem(Request $request)
    {
        $item = $this->service->findOrderDetailOrFail($request->id);
        $this->authorize('update', $item->parent);
        $this->detailService->updateItem($item, $request->all());
        return JsonResponseHelper::success($item, 'Item telah diperbarui.');
    }

    public function removeItem(Request $request)
    {
        $item = $this->service->findOrderDetailOrFail($request->id);
        $this->authorize('update', $item->parent);
        $this->detailService->removeItem($item);
        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function close(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->id);
        $this->authorize('update', $order);
        $this->service->closeOrder($order, $request->all());
        return JsonResponseHelper::success($order, "Order telah selesai.");
    }

    public function addPayment(Request $request)
    {
        $order = $this->service->findOrderOrFail($request->post('order_id'));
        $this->authorize('update', $order);
        $this->refundService->addPayments($order, $request->post('payments', []));
        return JsonResponseHelper::success($order, "Refund Pembayaran berhasil dicatat.");
    }

    public function deletePayment(Request $request)
    {
        $payment = $this->refundService->findOrFail($request->id);
        $this->authorize('update', $payment->order);
        $this->refundService->deletePayment($payment);
        return JsonResponseHelper::success(message: "Refund Pembayaran berhasil dihapus.");
    }
}
