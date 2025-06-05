<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class LeaveController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('leaves')->get();

            return view('leaves.index', compact('users'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load leave data: '.$e->getMessage());
        }
    }

    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $leavesSummary = Leave::with('user:id,first_name,last_name')
                    ->select('user_id', DB::raw('SUM(DATEDIFF(end_date, start_date) + 1) as total_leave_days'))
                    ->groupBy('user_id');

                return DataTables::of($leavesSummary)
                    ->addColumn('employee', function ($leave) {
                        return $leave->user->first_name.' '.$leave->user->last_name;
                    })
                    ->addColumn('duration', function ($leave) {
                        return $leave->total_leave_days.' days';
                    })
                    ->addColumn('action', function ($leave) {
                        $detailsUrl = route('leaves.show', $leave->user_id);

                        return '
                            <a href="'.$detailsUrl.'" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                Details
                            </a>
                        ';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch leave summary: '.$e->getMessage()], 500);
        }
    }

    public function create()
    {
        try {
            $employees = User::where('role', 'employee')->get();

            return view('leaves.create', compact('employees'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load employee data: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'reason' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $userId = $data['user_id'];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $year = $startDate->year;

            $daysRequested = $startDate->diffInDays($endDate) + 1;

            $leavesThisYear = Leave::where('user_id', $userId)
                ->whereYear('start_date', $year)
                ->get();

            $totalDaysTaken = 0;
            foreach ($leavesThisYear as $leave) {
                $sd = Carbon::parse($leave->start_date);
                $ed = Carbon::parse($leave->end_date);
                $totalDaysTaken += $sd->diffInDays($ed) + 1;
            }

            if (($totalDaysTaken + $daysRequested) > 12) {
                throw ValidationException::withMessages([
                    'start_date' => 'Leave days exceeded the annual limit of 12 days.',
                ]);
            }

            $dateCursor = $startDate->copy();
            while ($dateCursor <= $endDate) {
                $yearMonth = $dateCursor->format('Y-m');

                $leavesInMonth = Leave::where('user_id', $userId)
                    ->where(function ($query) use ($yearMonth) {
                        $query->whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$yearMonth])
                            ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m') = ?", [$yearMonth]);
                    })
                    ->get();

                foreach ($leavesInMonth as $existingLeave) {
                    $existingStart = Carbon::parse($existingLeave->start_date);
                    $existingEnd = Carbon::parse($existingLeave->end_date);

                    $daysInThisMonth = 0;
                    $temp = $existingStart->copy();
                    while ($temp <= $existingEnd) {
                        if ($temp->format('Y-m') == $yearMonth) {
                            $daysInThisMonth++;
                        }
                        $temp->addDay();
                    }

                    if ($daysInThisMonth >= 1) {
                        throw ValidationException::withMessages([
                            'start_date' => "You can only take 1 day of leave in {$yearMonth}.",
                        ]);
                    }
                }

                $daysInRequestMonth = 0;
                $temp = $startDate->copy();
                while ($temp <= $endDate) {
                    if ($temp->format('Y-m') == $yearMonth) {
                        $daysInRequestMonth++;
                    }
                    $temp->addDay();
                }

                if ($daysInRequestMonth > 1) {
                    throw ValidationException::withMessages([
                        'start_date' => "Only 1 day of leave is allowed in the month of {$yearMonth}.",
                    ]);
                }

                $dateCursor->addMonthNoOverflow();
            }

            Leave::create([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'reason' => $data['reason'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

            return redirect()->route('leaves.index')->with('success', 'Leave created successfully.');
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Exception $e) {
            return back()->withInput()->withErrors('Failed to create leave: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $leaves = Leave::where('user_id', $id)->get();

            return view('leaves.show', compact('leaves'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load leave details: '.$e->getMessage());
        }
    }

    public function getDataDetail(Request $request, $id)
    {
        try {
            if ($request->ajax()) {
                $leaves = Leave::with('user:id,first_name,last_name')
                    ->where('user_id', $id)
                    ->select(['id', 'user_id', 'reason', 'start_date', 'end_date']);

                return DataTables::of($leaves)
                    ->addColumn('employee', function ($leave) {
                        return $leave->user->first_name.' '.$leave->user->last_name;
                    })
                    ->addColumn('duration', function ($leave) {
                        return Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 .' days';
                    })
                    ->addColumn('action', function ($leave) {
                        $editUrl = route('leaves.edit', $leave->id);
                        $deleteUrl = route('leaves.destroy', $leave->id);

                        return '
                              <a href="'.$editUrl.'" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    Edit
                                </a>
                                <form action="'.$deleteUrl.'" method="POST" class="inline delete-form" style="display:inline;">
                                    '.csrf_field().method_field('DELETE').'
                                    <button type="submit" class="delete-btn inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400">
                                        Delete
                                    </button>
                                </form>
                        ';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch leave details: '.$e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $leave = Leave::findOrFail($id);
            $employees = User::where('id', $leave['user_id'])->get();

            return view('leaves.edit', compact('leave', 'employees'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load leave for editing: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $leave = Leave::findOrFail($id);

            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'reason' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $userId = $data['user_id'];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $year = $startDate->year;

            $daysRequested = $startDate->diffInDays($endDate) + 1;

            $leavesThisYear = Leave::where('user_id', $userId)
                ->whereYear('start_date', $year)
                ->where('id', '!=', $leave->id)
                ->get();

            $totalDaysTaken = 0;
            foreach ($leavesThisYear as $l) {
                $sd = Carbon::parse($l->start_date);
                $ed = Carbon::parse($l->end_date);
                $totalDaysTaken += $sd->diffInDays($ed) + 1;
            }

            if (($totalDaysTaken + $daysRequested) > 12) {
                throw ValidationException::withMessages([
                    'start_date' => 'Leave days exceeded the annual limit of 12 days.',
                ]);
            }

            $dateCursor = $startDate->copy();
            while ($dateCursor <= $endDate) {
                $yearMonth = $dateCursor->format('Y-m');

                $leavesInMonth = Leave::where('user_id', $userId)
                    ->where('id', '!=', $leave->id)
                    ->where(function ($query) use ($yearMonth) {
                        $query->whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$yearMonth])
                            ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m') = ?", [$yearMonth]);
                    })
                    ->get();

                foreach ($leavesInMonth as $existingLeave) {
                    $existingStart = Carbon::parse($existingLeave->start_date);
                    $existingEnd = Carbon::parse($existingLeave->end_date);

                    $daysInThisMonth = 0;
                    $temp = $existingStart->copy();
                    while ($temp <= $existingEnd) {
                        if ($temp->format('Y-m') == $yearMonth) {
                            $daysInThisMonth++;
                        }
                        $temp->addDay();
                    }

                    if ($daysInThisMonth >= 1) {
                        throw ValidationException::withMessages([
                            'start_date' => "You can only take 1 day of leave in {$yearMonth}.",
                        ]);
                    }
                }

                $daysInRequestMonth = 0;
                $temp = $startDate->copy();
                while ($temp <= $endDate) {
                    if ($temp->format('Y-m') == $yearMonth) {
                        $daysInRequestMonth++;
                    }
                    $temp->addDay();
                }

                if ($daysInRequestMonth > 1) {
                    throw ValidationException::withMessages([
                        'start_date' => "Only 1 day of leave is allowed in the month of {$yearMonth}.",
                    ]);
                }

                $dateCursor->addMonthNoOverflow();
            }

            $leave->update([
                'user_id' => $userId,
                'reason' => $data['reason'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

            return redirect()->route('leaves.index')->with('success', 'Leave updated successfully.');
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Exception $e) {
            return back()->withInput()->withErrors('Failed to update leave: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $leave = Leave::findOrFail($id);
            $leave->delete();

            return redirect()->route('leaves.index')->with('success', 'Leave deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete leave: '.$e->getMessage());
        }
    }
}
