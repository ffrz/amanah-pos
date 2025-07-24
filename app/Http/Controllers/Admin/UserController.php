<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        allowed_roles(User::Role_Admin);
    }

    public function index()
    {
        return inertia('admin/user/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/user/Detail', [
            'data' => User::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = User::query();
        $q->orderBy($orderBy, $orderType);

        if (!empty($filter['role'] && $filter['role'] != 'all')) {
            $q->where('role', '=', $filter['role']);
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        $users = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($users);
    }

    public function duplicate($id)
    {
        $user = User::findOrFail($id);
        $user->id = null;
        $user->created_at = null;
        return inertia('admin/user/Editor', [
            'data' => $user,
        ]);
    }

    public function editor($id = 0)
    {
        $user = $id ? User::findOrFail($id) : new User();

        if (!$id) {
            $user->active = true;
            $user->admin = true;
        } else if ($user == Auth::user()) {
            return redirect(route('admin.user.index'))->with('warning', 'TIdak dapat mengubah akun anda sendiri.');
        }

        return inertia('admin/user/Editor', [
            'data' => $user,
        ]);
    }

    public function save(Request $request)
    {
        $isNew = empty($request->id);
        $password = $request->get('password');

        $rules = [
            'name'     => 'required|max:255',
            'role'     => 'required',
            'username' => [
                'required',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($request->id),
            ],
        ];

        if ($isNew || !empty($password)) {
            $rules['password'] = 'required|min:5|max:40';
        }

        $request->validate($rules);

        $user = $isNew ? new User() : User::findOrFail($request->id);

        $fields = ['name', 'username', 'role', 'active'];
        $user->fill($request->only($fields));

        if (!empty($password)) {
            $user->password = Hash::make($password);
        }

        $user->save();

        $action = $isNew ? 'ditambahkan' : 'diperbarui';
        $message = "Pengguna {$user->username} telah {$action}.";

        return redirect(route('admin.user.index'))->with('success', $message);
    }


    public function delete($id)
    {
        $user = User::findOrFail($id);

        if ($user->id == Auth::user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun sendiri!'
            ], 409);
        }

        $user->delete();

        return response()->json([
            'message' => "Pengguna {$user->username} telah dihapus!"
        ]);
    }
}
