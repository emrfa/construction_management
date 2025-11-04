<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Server Error</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 flex items-center justify-center h-screen">
        
        <div class="max-w-md w-full bg-white shadow-md rounded-lg p-8 m-4">
            <div class="text-center">
                
                <svg class="w-16 h-16 mx-auto text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.008v.008H12v-.008Z"></path>
                </svg>

                <h1 class="mt-4 text-2xl font-bold text-gray-900">
                    Whoops! Something went wrong.
                </h1>

                <p class="mt-2 text-gray-600">
                    Our team has been notified. If you need immediate help, please contact support and provide the Error ID below.
                </p>

                @if(isset($error_id))
                    <div class="mt-4 p-3 bg-gray-50 border rounded-md">
                        <label class="text-xs font-medium text-gray-500">Error Reference ID:</label>
                        <input 
                            type="text" 
                            readonly 
                            value="{{ $error_id }}" 
                            class="w-full font-mono text-sm bg-gray-100 border-gray-300 rounded p-1 text-center"
                            onclick="this.select()"
                        >
                    </div>
                @endif
                <div class="mt-8">
                    <button 
                        onclick="history.back()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        &larr; Go Back
                    </button>
                </div>

            </div>
        </div>

    </body>
</html>