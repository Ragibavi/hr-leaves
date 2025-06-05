<x-app-layout>

    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Leave</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('leaves.update', $leave->id) }}" method="POST" class="space-y-6" id="leaveForm">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee</label>
                            <select name="user_id" id="user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select an employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ $employee->id == $leave->user_id ? 'selected' : '' }}>
                                        {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="text" name="start_date" id="start_date" required
                                value="{{ old('start_date', $leave->start_date) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="text" name="end_date" id="end_date" required
                                value="{{ old('end_date', $leave->end_date) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                            <textarea name="reason" id="reason" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">{{ old('reason', $leave->reason) }}</textarea>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <a href="{{ redirect()->back()->getTargetUrl() }}"
                               class="inline-block px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        @if ($errors->any())
            let errorMessages = '';
            @foreach ($errors->all() as $error)
                errorMessages += `{{ $error }}<br>`;
            @endforeach

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: errorMessages,
                confirmButtonColor: '#6366f1'
            });
        @endif

        document.getElementById('leaveForm').addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to update this leave request?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>

</x-app-layout>
