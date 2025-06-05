<x-app-layout>

    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Data Cuti</h2>
                    <a href="{{ route('leaves.create') }}"
                    class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Create
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table id="leaves-table" class="display w-full">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Pegawai</th>
                                <th>Alasan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Durasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let userId = '{{ $leaves->first()->user_id ?? '' }}';

            $('#leaves-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ url('leaves/dataDetail') }}/' + userId,
                    columns: [
                        {
                            data: null,
                            name: 'rownum',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        { data: 'employee', name: 'employee' },
                        { data: 'reason', name: 'reason' },
                        { data: 'start_date', name: 'start_date' },
                        { data: 'end_date', name: 'end_date' },
                        { data: 'duration', name: 'duration' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ]
                });
            
            document.addEventListener('click', function (e) {
                if (e.target.closest('.delete-btn')) {
                    e.preventDefault();
                    const form = e.target.closest('.delete-form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>

</x-app-layout>
