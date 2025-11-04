<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Access Denied</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 flex items-center justify-center h-screen">
        
        <div class="max-w-md w-full bg-white shadow-md rounded-lg p-8 m-4">
            <div class="text-center">
                
                <svg class="w-16 h-16 mx-auto text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"></path>
                </svg>

                <h1 class="mt-4 text-2xl font-bold text-gray-900">
                    Access Denied
                </h1>

                @if(Auth::check() && Auth::user()->getRoleNames()->isNotEmpty())
                    <p class="mt-2 text-gray-600">
                        You do not have the required permissions to view this page.
                    </p>
                    <p class="mt-2 text-sm text-gray-500">
                        Your current role is: <strong class="text-gray-700">{{ Auth::user()->getRoleNames()->join(', ') }}</strong>.
                        <br>
                        This area is restricted to users with the "Admin" role.
                    </p>
                @else
                    <p class="mt-2 text-gray-600">
                        You do not have the required permissions to access this page.
                    </p>
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