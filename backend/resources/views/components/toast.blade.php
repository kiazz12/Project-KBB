@if (session('success'))
    <div id="flash-success" class="fixed top-4 right-4 z-50 mb-6 px-4 py-3 bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-xl text-sm flex items-center gap-2 shadow-lg" style="animation: flashIn 0.25s ease-out">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="flex-1">{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">&times;</button>
    </div>
    <script>setTimeout(() => { const e = document.getElementById('flash-success'); if(e) e.remove(); }, 5000);</script>
@endif

@if (session('error'))
    <div id="flash-error" class="fixed top-4 right-4 z-50 mb-6 px-4 py-3 bg-red-50 dark:bg-red-500/15 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-xl text-sm flex items-center gap-2 shadow-lg" style="animation: flashIn 0.25s ease-out">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="flex-1">{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">&times;</button>
    </div>
    <script>setTimeout(() => { const e = document.getElementById('flash-error'); if(e) e.remove(); }, 5000);</script>
@endif
