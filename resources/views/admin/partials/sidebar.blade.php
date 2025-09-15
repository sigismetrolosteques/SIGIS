<div class="fixed inset-y-0 left-0 w-64 bg-white shadow-md">
    <div class="h-16 flex items-center justify-center shadow-md border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900">Admin Panel</h1>
    </div>
    <nav class="mt-5 px-4">
        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg mb-2 text-gray-700 hover:bg-gray-200">
            Dashboard
        </a>
        @can('view users')
        <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded-lg mb-2 text-gray-700 hover:bg-gray-200">
            Usuarios
        </a>
        @endcan
    </nav>
</div>