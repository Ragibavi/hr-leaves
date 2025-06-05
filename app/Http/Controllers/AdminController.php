<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function index()
    {
        try {
            return view('admin.index');
        } catch (Exception $e) {
            return back()->withErrors('Failed to load admin list: '.$e->getMessage());
        }
    }

    public function getData()
    {
        try {
            return DataTables::of(User::where('role', 'admin')->select(['id', 'first_name', 'last_name', 'email', 'birth_date', 'gender']))
                ->addColumn('action', function ($row) {
                    $editUrl = route('admins.edit', $row->id);
                    $deleteUrl = route('admins.destroy', $row->id);

                    return '
                        <div class="flex gap-2">
                            <a href="'.$editUrl.'" class="inline-flex items-center justify-center px-4 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                Edit
                            </a>
                            <form action="'.$deleteUrl.'" method="POST" class="delete-form">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="delete-btn inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400">
                                    Delete
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch data: '.$e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            return view('admin.create');
        } catch (Exception $e) {
            return back()->withErrors('Failed to load create form: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'birth_date' => 'required',
                'gender' => 'required',
            ]);

            User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'role' => 'admin',
            ]);

            return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to create admin: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $admin = User::findOrFail($id);

            return view('admin.edit', compact('admin'));
        } catch (Exception $e) {
            return back()->withErrors('Failed to load edit form: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'birth_date' => 'required',
                'gender' => 'required',
            ]);

            $admin = User::findOrFail($id);

            if ($request->password) {
                $request->validate([
                    'password' => 'required',
                ]);
                $admin->password = bcrypt($request->password);
            }

            $admin->first_name = $request->first_name;
            $admin->last_name = $request->last_name;
            $admin->email = $request->email;
            $admin->birth_date = $request->birth_date;
            $admin->gender = $request->gender;
            $admin->save();

            return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to update admin: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $admin = User::findOrFail($id);
            $admin->delete();

            return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to delete admin: '.$e->getMessage());
        }
    }
}
