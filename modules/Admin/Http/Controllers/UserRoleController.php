<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Tampilkan halaman indeks peran.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('user-role/Index');
    }

    /**
     * Dapatkan data peran dalam format paginasi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = Role::query();

        if (!empty($filter['search'])) {
            $q->where('name', 'like', '%' . $filter['search'] . '%');
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }


    public function detail($id = 0)
    {
        return inertia('user-role/Detail', [
            'data' => Role::with(['permissions', 'users'])->findOrFail($id),
        ]);
    }

    /**
     * Tampilkan halaman editor untuk membuat atau mengedit peran.
     *
     * @param int $id
     * @return \Inertia\Response
     */
    public function editor($id = 0)
    {
        $item = $id ? Role::with('permissions')->findOrFail($id) : new Role();
        $permissions = Permission::all()->toArray();

        return inertia('user-role/Editor', [
            'data' => $item,
            'permissions' => $permissions
        ]);
    }

    /**
     * Simpan peran baru atau perbarui peran yang sudah ada.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $permissions = collect($request->permissions)->map(function ($permission) {
            return is_array($permission) ? $permission['id'] : $permission;
        })->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:40',
            'description' => 'nullable|string|max:200',
            // 'permissions' => 'nullable|array',
            // 'permissions.*' => 'exists:acl_permissions,id',
        ]);

        try {
            $role = $request->id ? Role::findOrFail($request->id) : new Role();
            $role->name = $validated['name'];
            $role->description = $validated['description'];
            $role->save();

            $role->syncPermissions($permissions);

            $message = "Role '{$role->name}' telah berhasil disimpan.";

            return redirect()->route('admin.user-role.index')
                ->with('success', $message);
        } catch (Exception $ex) {

            return back()->with('error', 'Terjadi kesalahan: ' . $ex->getMessage());
        }
    }

    /**
     * Hapus peran yang sudah ada.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $role = Role::findOrFail($id);
            $roleName = $role->name;
            $role->delete();

            return JsonResponseHelper::success(null, "Role '{$roleName}' telah berhasil dihapus.");
        } catch (Exception $ex) {
            return JsonResponseHelper::error("Terdapat kesalahan saat menghapus role: " . $ex->getMessage(), 500);
        }
    }
}
