@extends('layouts.app')
@section('title','Users')
@section('content')
  <div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Users</h1>
      <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow">+ Add User</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="w-full table-auto">
        <thead class="bg-slate-50 text-slate-600 text-sm">
          <tr>
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">Role</th>
            <th class="px-4 py-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody class="text-sm divide-y">
          @forelse($users as $user)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">{{ $loop->iteration }}</td>
            <td class="px-4 py-3">{{ $user->name }}</td>
            <td class="px-4 py-3">{{ $user->email ?? '-' }}</td>
            <td class="px-4 py-3">{{ $user->role ?? '-' }}</td>
            <td class="px-4 py-3">
              
              <a href="{{ route('users.show', $user) }}" class="text-indigo-600 hover:underline">View</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="p-6 text-center text-slate-500">No users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      @if(method_exists($users, 'links'))
        {{ $users->links() }}
      @else
        <div class="text-sm text-slate-500">
          Showing {{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }}.
        </div>
      @endif
    </div>
  </div>
@endsection
