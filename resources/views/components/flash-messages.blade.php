@if (session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm rounded-md px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-3">
        {{ session('error') }}
    </div>
@endif
