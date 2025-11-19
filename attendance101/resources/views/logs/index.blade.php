@extends('layouts.app')
@section('title','Logs')
@section('content')
  <div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Attendance Logs</h1>
      <div class="flex items-center gap-2">
        <form method="GET" action="{{ url('logs') }}" class="flex items-center gap-2">
          <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2 border rounded-lg" />
          <select name="device" class="px-3 py-2 border rounded-lg">
            <option value="">All devices</option>
            @foreach($devices as $d)
              <option value="{{ $d->id }}" @selected(request('device') == $d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Filter</button>
        </form>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="w-full table-auto text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 text-left">Time</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Device</th>
            <th class="px-4 py-3 text-left">Type</th>
            <th class="px-4 py-3 text-left">Note</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($logs as $log)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">{{ optional($log->timestamp)->format('Y-m-d H:i:s') }}</td>
            <td class="px-4 py-3">{{ $log->user->name ?? ($log->employee->name ?? 'Unknown') }}</td>
            <td class="px-4 py-3">{{ $log->device->name ?? '-' }}</td>
            <td class="px-4 py-3">{{ ucfirst($log->type) }}</td>
            <td class="px-4 py-3">{{ $log->note ?? '-' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="p-6 text-center text-slate-500">No attendance logs found for the selected filters.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $logs->withQueryString()->links() }}
    </div>
  </div>
@endsection
