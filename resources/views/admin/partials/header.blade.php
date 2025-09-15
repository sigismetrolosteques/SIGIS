<header class="flex items-center justify-between h-16 px-6 bg-white shadow-md border-b border-gray-200">
    <div class="flex items-center space-x-4">
        <span class="text-gray-800 font-semibold">Hola, {{ auth()->user()->name }}</span>
        <span class="text-sm text-gray-500">
            ({{ auth()->user()->roles->first()->name }})
        </span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-red-600 hover:underline">
            Salir
        </button>
    </form>
</header>