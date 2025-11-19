@extends('layouts.app')
@section('title','Devices')
@section('content')
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Devices</h1>
      
      <a href="{{ route('devices.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">+ Add Device</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      @forelse($devices as $device)
        <div class="bg-white rounded-lg p-4 shadow flex items-start justify-between">
          <div>
            <div class="font-semibold">{{ $device->name }}</div>
            <div class="text-sm text-slate-500">IP: {{ $device->ip_address }}</div>
            <div class="text-sm text-slate-500 mt-1">Location: {{ $device->location ?? '—' }}</div>
          </div>
          <div class="text-right">
            <div class="px-3 py-1 rounded-full text-xs font-medium {{ $device->status ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
              {{ $device->status ? 'Online' : 'Offline' }}
            </div>
            <div class="mt-2">
              
              <a href="{{ route('devices.show',$device) }}" class="text-indigo-600 hover:underline">Manage</a>
            </div>
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-slate-500">No devices found.</div>
      @endforelse
    </div>
  </div>
@endsection
