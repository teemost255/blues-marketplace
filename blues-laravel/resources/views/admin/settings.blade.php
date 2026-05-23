@extends('layouts.admin')
@section('title','Settings')
@section('page-title','Settings')
@section('content')
<div class="bg-slate-800 border border-slate-700 rounded-xl p-8 max-w-xl">
    <h2 class="font-semibold text-white mb-6">Platform Settings</h2>
    <div class="space-y-4 text-sm text-slate-300">
        <div class="flex justify-between py-3 border-b border-slate-700">
            <span>App Name</span><span class="text-white font-medium">BluesMarketplace</span>
        </div>
        <div class="flex justify-between py-3 border-b border-slate-700">
            <span>Database</span><span class="text-white font-medium">SQLite (local)</span>
        </div>
        <div class="flex justify-between py-3 border-b border-slate-700">
            <span>Framework</span><span class="text-white font-medium">Laravel {{ app()->version() }}</span>
        </div>
        <div class="flex justify-between py-3 border-b border-slate-700">
            <span>PHP Version</span><span class="text-white font-medium">{{ phpversion() }}</span>
        </div>
        <div class="flex justify-between py-3">
            <span>Adminer (DB Browser)</span>
            <a href="/adminer" target="_blank" class="text-sky-400 hover:underline font-medium">Open Adminer →</a>
        </div>
    </div>
</div>
@endsection
