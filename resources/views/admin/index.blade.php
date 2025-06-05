<x-app-layout>

    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Data Admin</h2>
                    <a href="{{ route('admins.create') }}" 
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
                    <table id="user-table" class="display w-full">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Depan</th>
                                <th>Nama Belakang</th>
                                <th>Email</th>
                                <th>Tanggal Lahir</th>
                                <th>Jenis Kelamin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $('#user-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admins.data') }}',
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
                    {
                        data: 'first_name',
                        name: 'first_name',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                        }
                    },
                    {
                        data: 'last_name',
                        name: 'last_name',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                        }
                    },
                    { data: 'email', name: 'email' },
                    { data: 'birth_date', name: 'birth_date' },
                    {
                        data: 'gender',
                        name: 'gender',
                        render: function(data) {
                            if (!data) return '';
                            return data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>

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
    document.addEventListener('DOMContentLoaded', function () {
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