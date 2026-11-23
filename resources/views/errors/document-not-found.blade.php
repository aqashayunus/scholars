<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Not Found | {{ config('app.name', 'Olympian Scholars') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 antialiased text-gray-800">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden text-center p-8">
        {{-- Icon --}}
        <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-5 border border-amber-200">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-2">Document File Not Found</h1>
        <p class="text-xs text-gray-600 mb-5 leading-relaxed">
            The database record for this document exists, but the physical file is currently unavailable on server storage.
        </p>

        {{-- Details Box --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left text-xs mb-6 space-y-2">
            @if(!empty($documentType))
                <div class="flex justify-between items-center py-1 border-b border-gray-200">
                    <span class="text-gray-500 font-medium">Document:</span>
                    <span class="text-gray-800 font-semibold">{{ $documentType }}</span>
                </div>
            @endif
            @if(!empty($applicantName))
                <div class="flex justify-between items-center py-1 border-b border-gray-200">
                    <span class="text-gray-500 font-medium">Applicant:</span>
                    <span class="text-gray-800 font-medium">{{ $applicantName }}</span>
                </div>
            @endif
            @if(!empty($fileName))
                <div class="flex justify-between items-center py-1">
                    <span class="text-gray-500 font-medium">File Name:</span>
                    <span class="font-mono text-[11px] text-gray-700 truncate max-w-[200px]" title="{{ $fileName }}">{{ $fileName }}</span>
                </div>
            @endif
        </div>

        <p class="text-[11px] text-gray-400 mb-6 leading-normal">
            If you recently deployed or migrated, run <code class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-mono text-[10px]">php artisan storage:link</code> or request the applicant to re-upload.
        </p>

        {{-- Actions --}}
        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="window.close()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition cursor-pointer">
                Close Window
            </button>
            <a href="{{ route('admin.dashboard') }}#teachers" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition shadow-xs">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
