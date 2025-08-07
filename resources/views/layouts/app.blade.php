<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PvP Calculator')</title>
    
    <!-- Preload background image for faster loading -->
    <link rel="preload" href="/image.png" as="image">
    
    <link rel="stylesheet" href="/css/app.css">
    <!-- Add Toastr for notifications -->
    <!-- Remova o Toastr e use Notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <!-- Add SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navigation">
        <ul class="navbits">
            <li><a href="/" title="Calculator" class="{{ request()->is('/') ? 'active' : '' }}">CALCULATOR</a></li>
            <!-- <li><a href="/macros" title="Macros & WoW Guides">MACROS & GUIDES</a></li> -->
        </ul>
    </nav>

    <div class="container">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" id="error-alert">
                <i class="fas fa-exclamation-triangle"></i>
                {{ session('error') }}
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" id="warning-alert">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('warning') }}
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        @endif

        @yield('content')
    </div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="/js/app.js"></script>
    
    <!-- Pass PHP session data to JavaScript -->
    <script>
        window.sessionMessages = {
            success: {!! session('success') ? json_encode(session('success')) : 'null' !!},
            error: {!! session('error') ? json_encode(session('error')) : 'null' !!},
            warning: {!! session('warning') ? json_encode(session('warning')) : 'null' !!},
            info: {!! session('info') ? json_encode(session('info')) : 'null' !!}
        };
    </script>
    
    <!-- Notification Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.sessionMessages.success) {
                toastr.success(window.sessionMessages.success);
            }
            
            if (window.sessionMessages.error) {
                toastr.error(window.sessionMessages.error);
            }
            
            if (window.sessionMessages.warning) {
                toastr.warning(window.sessionMessages.warning);
            }
            
            if (window.sessionMessages.info) {
                toastr.info(window.sessionMessages.info);
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>