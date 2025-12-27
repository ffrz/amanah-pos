<?php

namespace Modules\Service\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\ServiceTechnician;
use Illuminate\Http\Request;
use Modules\Admin\Services\CommonDataService;
use Modules\Service\Http\Requests\ServiceOrder\GetDataRequest;
use Modules\Service\Http\Requests\ServiceOrder\SaveRequest;
use Modules\Service\Services\ServiceOrderService;

class ServiceOrderController extends Controller
{
    public function __construct(
        protected ServiceOrderService $serviceOrderService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', ServiceOrder::class);
        return inertia('service-order/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);
        $items = $this->serviceOrderService->getData($request->validated());
        return JsonResponseHelper::success($items);
    }

    public function detail($id)
    {
        $item = $this->serviceOrderService->find($id);
        $this->authorize('view', $item);

        return inertia('service-order/Detail', ['data' => $item]);
    }

    public function editor(Request $request, $id = 0)
    {
        $item = $this->serviceOrderService->findOrCreate($id);
        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('service-order/Editor', [
            'data'        => $item,
            'technicians' => ServiceTechnician::where('active', true)->get(['id', 'name']),
            'customers'   => app(CommonDataService::class)->getCustomers(['id', 'code', 'name', 'phone', 'address']),
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->serviceOrderService->findOrCreate($request->id);
        $this->authorize($item->id ? 'update' : 'create', $item);

        $this->serviceOrderService->save($item, $request->validated());

        return $request->expectsJson()
            ? JsonResponseHelper::success($item)
            : redirect()->route('service.service-order.detail', $item->id)->with('success', "Order {$item->order_code} disimpan.");
    }

    public function delete($id)
    {
        $item = $this->serviceOrderService->find($id);
        $this->authorize('delete', $item);

        $this->serviceOrderService->delete($item);
        return JsonResponseHelper::success(null, "Order berhasil dihapus.");
    }
}
