<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            return view('employee.index');
        } catch (Exception $e) {
            return back()->withErrors('Failed to load employee list: '.$e->getMessage());
        }
    }

    public function getData()
    {
        try {
            return DataTables::of(User::where('role', 'employee')->select(['id', 'first_name', 'last_name', 'email', 'phone', 'address', 'gender']))
                ->addColumn('action', function ($row) {
                    $editUrl = route('employees.edit', $row->id);
                    $deleteUrl = route('employees.destroy', $row->id);

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
            return response()->json(['error' => 'Failed to fetch employee data: '.$e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            return view('employee.create');
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
                'phone' => 'required',
                'address' => 'required',
                'gender' => 'required',
            ]);

            User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => bcrypt('qwertyuiop1234567890'),
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'role' => 'employee',
            ]);

            return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to create employee: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $employee = User::findOrFail($id);

            return view('employee.edit', compact('employee'));
        } catch (Exception $e) {
            return back()->withErrors('Failed to load employee data: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,'.$id,
                'phone' => 'required',
                'address' => 'required',
                'gender' => 'required',
            ]);

            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
            ];

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            User::where('id', $id)->update($data);

            return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to update employee: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            User::findOrFail($id)->delete();

            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
        } catch (Exception $e) {
            return back()->withErrors('Failed to delete employee: '.$e->getMessage());
        }
    }
}
