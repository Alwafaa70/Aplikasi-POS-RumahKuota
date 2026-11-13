<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rumah Kuota POS</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#10B981', // Emerald 500
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center relative overflow-hidden font-sans selection:bg-primary selection:text-white">

    <!-- Background Decorative Elements -->
    <div class="absolute inset-0 z-0">
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-indigo-50 via-white to-blue-50"></div>
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-[20%] right-[-5%] w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md px-6 animate-fade-in-up">
        
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-gray-900 tracking-tight">Rumah Kuota POS</h1>
            <p class="text-gray-500 mt-2 font-medium">Masuk untuk mengelola bisnis Anda</p>
        </div>

        <!-- Glassmorphism Card -->
        <div class="bg-white/70 backdrop-blur-xl border border-white/50 shadow-2xl rounded-3xl p-8">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border-l-4 border-red-500 flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">Login Gagal</h3>
                        <ul class="text-xs text-red-700 mt-1 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Username Input -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700 ml-1">Username</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="username" value="{{ old('username') }}" required autofocus
                            class="w-full pl-11 pr-4 py-3.5 bg-white/50 border border-gray-200 rounded-xl text-gray-800 font-medium placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary focus:bg-white transition-all duration-300"
                            placeholder="Masukkan username Anda">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-sm font-bold text-gray-700">Password</label>
                        <a href="#" class="text-xs font-semibold text-primary hover:text-indigo-800 transition">Lupa sandi?</a>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="w-full pl-11 pr-12 py-3.5 bg-white/50 border border-gray-200 rounded-xl text-gray-800 font-medium placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary focus:bg-white transition-all duration-300"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me (Dummy visually for aesthetic) -->
                <div class="flex items-center pt-2">
                    <input type="checkbox" id="remember" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2 cursor-pointer">
                    <label for="remember" class="ml-2 text-sm font-medium text-gray-600 cursor-pointer">Ingat sesi saya</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-primary to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-primary/30 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-primary/50 flex justify-center items-center gap-2 group">
                    <span>Masuk ke Sistem</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
            
        </div>
        
        <!-- Footer Info -->
        <p class="text-center text-sm text-gray-500 mt-8 font-medium">
            &copy; {{ date('Y') }} Rumah Kuota POS. All rights reserved.
        </p>
    </div>

    <!-- Script for toggle password visibility -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 011.563-3.029M21.438 12a9.97 9.97 0 01-1.563 3.029m-5.858-.908a3 3 0 01-4.243-4.243m4.243 4.243L21 21" />`;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    </script>
</body>
</html>
