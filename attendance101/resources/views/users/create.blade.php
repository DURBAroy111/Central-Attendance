@extends('layouts.app')
@section('title','Add User')
@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Add User</h1>
        <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to users</a>
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

      <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Employee Code</label>
            <input
              name="employee_code"
              type="text"
              value="{{ old('employee_code') }}"
              placeholder="e.g. EMP-001"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p class="text-xs text-slate-400 mt-1">Unique code for the employee (optional).</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input
              name="name"
              type="text"
              value="{{ old('name') }}"
              required
              placeholder="Full name"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Template (base64)</label>
          <textarea
            name="template"
            rows="6"
            placeholder="Paste fingerprint template (base64)..."
            class="w-full px-3 py-2 border rounded-lg font-mono text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-200"
          >{{ old('template') }}</textarea>
          <p class="text-xs text-slate-400 mt-1">If the device returns a base64 template, paste it here. Leave blank if adding later.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Finger index</label>
            <input
              name="finger_index"
              type="number"
              min="0"
              max="10"
              value="{{ old('finger_index') }}"
              placeholder="0"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p class="text-xs text-slate-400 mt-1">Numeric index used by the device (0–10).</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
            <input
              name="role"
              type="text"
              value="{{ old('role') }}"
              placeholder="e.g. employee, admin"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />
            <p class="text-xs text-slate-400 mt-1">Optional role label for this user.</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">Save</button>
          <a href="{{ route('users.index') }}" class="px-4 py-2 border rounded-lg text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
        </div>
      </form>
    </div>
  </div>
@endsection
