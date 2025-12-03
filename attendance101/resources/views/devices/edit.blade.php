@extends('layouts.app')
@section('title','Edit Device')

@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Edit Device</h1>
        <a href="{{ route('devices.show', $device) }}"
           class="text-sm text-indigo-600 hover:underline">← Back to device</a>
      </div>

      @if ($errors->any())
        <div class="mb-4">
          <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            <strong class="block mb-1">Please fix the following:</strong>
            <ul class="list-disc ml-5">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('devices.update', $device) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
          <input type="text" name="name"
                 value="{{ old('name', $device->name) }}"
                 class="block w-full rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">IP Address</label>
            <input type="text" name="ip_address"
                   value="{{ old('ip_address', $device->ip_address) }}"
                   class="block w-full rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Port</label>
            <input type="number" name="port"
                   value="{{ old('port', $device->port ?? 4370) }}"
                   class="block w-full rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Serial Number</label>
            <input type="text" name="serial_number"
                   value="{{ old('serial_number', $device->serial_number) }}"
                   class="block w-full rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Model</label>
            <input type="text" name="model"
                   value="{{ old('model', $device->model) }}"
                   class="block w-full rounded-md border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <button type="submit"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
            Save Changes
          </button>

          <a href="{{ route('devices.show', $device) }}"
             class="px-4 py-2 border rounded-lg text-sm text-slate-600 hover:bg-slate-50">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection
