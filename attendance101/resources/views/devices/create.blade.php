@extends('layouts.app')
@section('title','Add Device')
@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Add Device</h1>
        <a href="{{ route('devices.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to devices</a>
      </div>

      
      @if ($errors->any())
        <div class="mb-4">
          <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            <strong class="block mb-1">Please fix the following:</strong>
            <ul class="list-disc ml-5">
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('devices.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Device Name <span class="text-red-500">*</span></label>
            <input
              name="name"
              type="text"
              value="{{ old('name') }}"
              required
              placeholder="e.g. Main Gate Scanner"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">IP Address <span class="text-red-500">*</span></label>
            <input
              name="ip_address"
              type="text"
              value="{{ old('ip_address') }}"
              required
              placeholder="e.g. 192.168.1.50"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
          </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Port</label>
            <input
              name="port"
              type="number"
              value="{{ old('port', '4370') }}"
              placeholder="4370"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p class="text-xs text-slate-400 mt-1">Default ZKTeco port is 4370.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Serial Number</label>
            <input
              name="serial_number"
              type="text"
              value="{{ old('serial_number') }}"
              placeholder="e.g. ABC123456"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
          </div>

        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
            Save
          </button>

          <a href="{{ route('devices.index') }}"
             class="px-4 py-2 border rounded-lg text-sm text-slate-600 hover:bg-slate-50">
            Cancel
          </a>
        </div>

      </form>
    </div>
  </div>
@endsection
