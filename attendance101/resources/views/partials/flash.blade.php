@if(session('success') || session('error'))
  <div class="mb-4">
    <div class="max-w-3xl mx-auto">
      @if(session('success'))
        <div class="rounded-lg p-4 bg-green-50 border border-green-200 text-green-800">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="rounded-lg p-4 bg-red-50 border border-red-200 text-red-800">{{ session('error') }}</div>
      @endif
    </div>
  </div>
@endif
