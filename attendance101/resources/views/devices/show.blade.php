@extends('layouts.app')
@section('title', 'Device: ' . $device->name)

@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="mb-4">
      <a href="{{ route('devices.index') }}" class="text-sm text-indigo-600 hover:underline">
        ← Back to devices
      </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold">{{ $device->name }}</h1>
          <p class="text-sm text-slate-500">Device details & management</p>
        </div>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
          {{ $device->status === 'online' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
          <span class="w-2 h-2 rounded-full mr-2
            {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
          {{ strtoupper($device->status ?? 'unknown') }}
        </span>
      </div>

      <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
          <dt class="text-slate-500">IP Address</dt>
          <dd class="font-medium">{{ $device->ip_address }}</dd>
        </div>

        <div>
          <dt class="text-slate-500">Port</dt>
          <dd class="font-medium">{{ $device->port ?? '4370' }}</dd>
        </div>

        <div>
          <dt class="text-slate-500">Serial Number</dt>
          <dd class="font-medium">{{ $device->serial_number ?? '—' }}</dd>
        </div>

        <div>
          <dt class="text-slate-500">Model</dt>
          <dd class="font-medium">{{ $device->model ?? '—' }}</dd>
        </div>

        <div>
          <dt class="text-slate-500">Last Seen</dt>
          <dd class="font-medium">
            @if($device->last_seen_at)
              {{ $device->last_seen_at->format('Y-m-d H:i:s') }}
            @else
              Never checked
            @endif
          </dd>
        </div>
      </dl>

      <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-100">
        <form method="POST" action="{{ route('devices.ping', $device) }}">
        @csrf
        <button type="submit"
                class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            Ping Now
        </button>
        </form>


        <a href="{{ route('devices.edit', $device) }}"
           class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
          Update Device
        </a>

        <form method="POST"
              action="{{ route('devices.destroy', $device) }}"
              onsubmit="return confirm('Are you sure you want to delete this device? This cannot be undone.');">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
            Delete Device
          </button>
        </form>
      </div>
    </div>
  </div>
@endsection
