<header class="bg-white shadow">
  <div class="container mx-auto px-4 py-4 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-md bg-indigo-600 flex items-center justify-center text-white font-bold">CA</div>
      <div>
        <div class="font-semibold">Central Attendance</div>
        <div class="text-xs text-slate-500">Centralized device & attendance management</div>
      </div>
    </a>

    <nav class="hidden md:flex items-center gap-4">
      <a href="{{ route('devices.index') }}" class="hover:text-indigo-600">Devices</a>
      <a href="{{ route('users.index') }}" class="hover:text-indigo-600">Users</a>
      <a href="{{ url('logs') }}" class="hover:text-indigo-600">Logs</a>
    </nav>

    <div class="flex items-center gap-3">
      <form action="{{ url()->current() }}" method="GET" class="hidden sm:block">
        <input name="q" placeholder="Search..." value="{{ request('q') }}" class="px-3 py-2 border rounded-lg text-sm" />
      </form>

      <div class="text-sm text-slate-600">
        @auth
          <span>{{ auth()->user()->name }}</span>
        @endauth
      </div>
    </div>

    
    <div class="md:hidden">
      <button x-data="{}" @click="window.dispatchEvent(new CustomEvent('toggle-mobile'))" class="p-2 rounded-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
  </div>

  
  <div x-data="{ open: false }" x-on:toggle-mobile.window="open = !open" class="md:hidden">
    <div x-show="open" x-transition class="px-4 pb-4">
      <a href="{{ route('devices.index') }}" class="block py-2">Devices</a>
      <a href="{{ route('users.index') }}" class="block py-2">Users</a>
      <a href="{{ url('logs') }}" class="block py-2">Logs</a>
    </div>
  </div>
</header>
