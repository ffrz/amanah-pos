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

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderPayment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\PurchaseOrder\GetDataRequest;
use Modules\Admin\Http\Requests\PurchaseOrder\SaveRequest;
use Modules\Admin\Services\PurchaseOrderPaymentService;
use Modules\Admin\Services\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $service,
        protected PurchaseOrderPaymentService $paymentService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        return inertia('purchase-order/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $items = $this->service->getData($request->validated())
            ->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function editor($id = 0)
    {
        if (!$id) {
            $this->authorize('create', PurchaseOrder::class);

            $item = $this->service->create();

            return redirect(route('admin.purchase-order.edit', $item->id));
        }

        $item = $this->service->edit($id);

        $this->authorize('update', $item);

        return inertia('purchase-order/Editor', [
            'data' => $item,
            'accounts' => $this->service->getFinanceAccounts(),
        ]);
    }

    public function update(SaveRequest $request)
    {
        $item = PurchaseOrder::find($request->post('id'));

        $this->authorize('update', $item);

        $this->service->save($item, $request->validated());

        return JsonResponseHelper::success($item, 'Order telah diperbarui');
    }

    public function cancel($id)
    {
        $item = $this->service->find($id);

        $this->authorize('cancel', $item);

        $this->service->cancel($item);

        return JsonResponseHelper::success(
            ['id' => $item->id],
            "Transaksi #$item->formatted_id telah dibatalkan."
        );
    }

    public function delete($id)
    {
        $item = $this->service->find($id);

        $this->authorize('delete', $item);

        $item = $this->service->delete($item);

        return JsonResponseHelper::success($item, "Transaksi #$item->formatted_id telah dihapus.");
    }

    public function detail($id)
    {
        $item = $this->service->find($id);

        $this->authorize('view', $item);

        return inertia('purchase-order/Detail', [
            'data' => $item,
            'accounts' => $this->service->getFinanceAccounts()
        ]);
    }

    public function addItem(Request $request)
    {
        $order = $this->service->find($request->post('order_id'));
        $merge = $request->post('merge', false);
        $detail = $this->service->addItem($order, $request->all(), $merge);

        return JsonResponseHelper::success([
            'item' => $detail,
            'mergeItem' => $merge,
        ], 'Item telah ditambahkan');
    }

    public function updateItem(Request $request)
    {
        $item = $this->service->findOrderDetail($request->id);

        $this->service->updateItem($item, $request->all());

        return JsonResponseHelper::success($item, 'Item telah diperbarui.');
    }

    public function removeItem(Request $request)
    {
        $item = $this->service->findOrderDetail($request->id);

        $this->service->removeItem($item);

        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function close(Request $request)
    {
        $order = $this->service->find($request->id);

        $this->service->closeOrder($order, $request->all());

        return JsonResponseHelper::success($order, "Order telah selesai.");
    }

    public function addPayment(Request $request)
    {
        $order = $this->service->find($request->post('order_id'));

        $this->paymentService->addPayment($order, $request->post('payments', []));

        return JsonResponseHelper::success($order, "Pembayaran berhasil dicatat.");
    }

    /**
     * Menangani penghapusan pembayaran untuk Sales Order.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePayment(Request $request)
    {
        $payment = $this->paymentService->findOrFail($request->id);

        $this->paymentService->deletePayment($payment);

        return JsonResponseHelper::success(message: "Pembayaran berhasil dihapus.");
    }
}
