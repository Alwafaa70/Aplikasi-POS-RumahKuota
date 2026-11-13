<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi POS') - Rumah Kuota</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e293b">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PWA Script -->
    <script src="{{ asset('js/pwa.js') }}" defer></script>

    <style>
        :root { --primary: #4f46e5; --bg: #f8fafc; --text: #1e293b; }
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 0; }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar for sidebar menu */
        .sidebar-menu::-webkit-scrollbar { width: 5px; }
        .sidebar-menu::-webkit-scrollbar-track { background: transparent; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 9999px; }
        .sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    
    <div class="min-h-screen flex flex-col lg:flex-row relative" 
         x-data="{ 
             sidebarOpen: window.innerWidth >= 1024, 
             isMobile: window.innerWidth < 1024 
         }" 
         x-init="
             const handleResize = () => {
                 isMobile = window.innerWidth < 1024;
                 if (!isMobile) sidebarOpen = true;
             };
             window.addEventListener('resize', handleResize);
         " 
         x-cloak>

        <!-- Mobile Backdrop Overlay -->
        <div x-show="isMobile && sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden">
        </div>

        <!-- Sidebar Navigation -->
        <aside class="fixed lg:sticky top-0 inset-y-0 left-0 z-50 w-72 lg:w-64 bg-slate-900 text-slate-200 flex flex-col h-screen shrink-0 border-r border-slate-800/80 shadow-2xl lg:shadow-none transition-all duration-300 ease-in-out"
               :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full lg:-ml-64': !sidebarOpen
               }">
            
            <!-- Sidebar Header Brand -->
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-600/30 text-lg">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-white tracking-tight flex items-center gap-1.5">
                            <span>Rumah Kuota</span>
                            <span class="text-[9px] bg-indigo-500/30 text-indigo-300 border border-indigo-400/30 px-1.5 py-0.2 rounded font-mono">POS</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium">Sistem Operasional Terpadu</div>
                    </div>
                </div>
                <!-- Close Button on Mobile -->
                <button class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition" @click="sidebarOpen = false">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Sidebar Nav Links (Scrollable & Responsive) -->
            <div class="sidebar-menu flex-1 overflow-y-auto px-3 py-3 space-y-1">
                @auth
                    @if(auth()->user()->role === 'owner')
                        <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-gauge text-[9px] text-indigo-400"></i>
                            <span>Dashboards</span>
                        </div>
                        <a href="{{ route('owner.dashboard') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('owner.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-chart-pie"></i></span>
                            <span class="flex-1 leading-snug">Ringkasan Eksekutif</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-database text-[9px] text-indigo-400"></i>
                            <span>Manajemen Data Pusat</span>
                        </div>
                        <a href="{{ route('master.cabang.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('master.cabang.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('master.cabang.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-store"></i></span>
                            <span class="flex-1 leading-snug">Daftar Cabang</span>
                        </a>
                        <a href="{{ route('master.barang.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('master.barang.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('master.barang.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <span class="flex-1 leading-snug">Super Master Barang</span>
                        </a>
                        <a href="{{ route('master.aplikasi_layanan.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('master.aplikasi_layanan.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('master.aplikasi_layanan.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-mobile-screen-button"></i></span>
                            <span class="flex-1 leading-snug">Produk Layanan Digital</span>
                        </a>
                        <a href="{{ route('master.dompet_pusat.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('master.dompet_pusat.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('master.dompet_pusat.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-wallet"></i></span>
                            <span class="flex-1 leading-snug">Pengaturan Rekening Pusat</span>
                        </a>
                        <a href="{{ route('users.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('users.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/25' : 'text-purple-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-purple-400"><i class="fa-solid fa-users-gear"></i></span>
                            <span class="flex-1 leading-snug">Manajemen Akses Pegawai</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-chart-line text-[9px] text-amber-400"></i>
                            <span>Laporan & Analisis AI</span>
                        </div>
                        <a href="{{ route('owner.gudang.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.gudang.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/25' : 'text-amber-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-amber-400"><i class="fa-solid fa-warehouse"></i></span>
                            <span class="flex-1 leading-snug">Pemantauan Gudang Pusat</span>
                        </a>
                        <a href="{{ route('owner.ses_debugger') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.ses_debugger') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-indigo-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-indigo-400"><i class="fa-solid fa-flask"></i></span>
                            <span class="flex-1 leading-snug">Laboratorium Riset SES (AI)</span>
                        </a>
                        <a href="{{ route('owner.ews.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.ews.*') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'text-rose-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-rose-400"><i class="fa-solid fa-bell"></i></span>
                            <span class="flex-1 leading-snug">Sistem Monitor Buffer EWS</span>
                        </a>
                        <a href="{{ route('owner.historical.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.historical.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/25' : 'text-sky-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-sky-400"><i class="fa-solid fa-file-import"></i></span>
                            <span class="flex-1 leading-snug">Impor Data Historis</span>
                        </a>
                        <a href="{{ route('owner.laporan_pusat.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.laporan_pusat.*') ? 'bg-red-600 text-white shadow-md shadow-red-600/25' : 'text-red-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-red-400"><i class="fa-solid fa-folder-open"></i></span>
                            <span class="flex-1 leading-snug">Pusat Laporan & Pembukuan</span>
                        </a>

                    @elseif(auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'admin')
                        <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-gauge text-[9px] text-indigo-400"></i>
                            <span>Dashboards</span>
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-chart-pie"></i></span>
                            <span class="flex-1 leading-snug">Ringkasan Operasional</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-brain text-[9px] text-indigo-400"></i>
                            <span>Peramalan & Analisis</span>
                        </div>
                        <a href="{{ route('owner.ses_debugger') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.ses_debugger') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-indigo-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-indigo-400"><i class="fa-solid fa-flask"></i></span>
                            <span class="flex-1 leading-snug">Laboratorium Riset SES (AI)</span>
                        </a>
                        <a href="{{ route('owner.ews.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('owner.ews.*') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'text-rose-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-rose-400"><i class="fa-solid fa-bell"></i></span>
                            <span class="flex-1 leading-snug">Sistem Monitor Buffer EWS</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-[9px] text-indigo-400"></i>
                            <span>Master Data</span>
                        </div>
                        <a href="{{ route('master.barang.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('master.barang.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('master.barang.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <span class="flex-1 leading-snug">Super Master Barang</span>
                        </a>
                        <a href="{{ route('admin.dompet_pusat.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dompet_pusat.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('admin.dompet_pusat.*') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-wallet"></i></span>
                            <span class="flex-1 leading-snug">Kas & Dompet Pusat</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-truck-ramp-box text-[9px] text-amber-400"></i>
                            <span>Inventory & Operasional</span>
                        </div>
                        <a href="{{ route('admin.pembelian.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.pembelian.index') || request()->routeIs('admin.pembelian.create') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/25' : 'text-amber-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-amber-400"><i class="fa-solid fa-truck-loading"></i></span>
                            <span class="flex-1 leading-snug">Penerimaan Barang (PO)</span>
                        </a>
                        <a href="{{ route('admin.pembelian.historis') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.pembelian.historis') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-slate-400 group-hover:text-indigo-400"><i class="fa-solid fa-clipboard-list"></i></span>
                            <span class="flex-1 leading-snug">Riwayat Pembelian</span>
                        </a>
                        <a href="{{ route('distribusi.mutasi.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('distribusi.mutasi.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-slate-400 group-hover:text-indigo-400"><i class="fa-solid fa-dolly"></i></span>
                            <span class="flex-1 leading-snug">Distribusi & Mutasi Cabang</span>
                        </a>
                        <a href="{{ route('admin.request_operasional.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.request_operasional.*') ? 'bg-red-600 text-white shadow-md shadow-red-600/25' : 'text-red-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-red-400"><i class="fa-solid fa-bell"></i></span>
                            <span class="flex-1 leading-snug">Request Operasional Cabang</span>
                        </a>

                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-money-bill-wave text-[9px] text-sky-400"></i>
                            <span>Keuangan & Setoran</span>
                        </div>
                        <a href="{{ route('admin.closing.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.closing.*') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/25' : 'text-sky-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-sky-400"><i class="fa-solid fa-money-bill-wave"></i></span>
                            <span class="flex-1 leading-snug">Verifikasi Closing Kasir</span>
                        </a>

                    @elseif(auth()->user()->role === 'kasir')
                        <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-desktop text-[9px] text-indigo-400"></i>
                            <span>Tampilan Utama</span>
                        </div>
                        <a href="{{ route('kasir.dashboard') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('kasir.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 {{ request()->routeIs('kasir.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"><i class="fa-solid fa-chart-line"></i></span>
                            <span class="flex-1 leading-snug">Ringkasan Kasir</span>
                        </a>
                        <div class="pt-1 pb-2">
                            <a href="{{ route('kasir.pos') }}" class="flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-cash-register"></i>
                                <span>Buka Layar Kasir</span>
                            </a>
                        </div>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-list-check text-[9px] text-emerald-400"></i>
                            <span>Aktivitas Operasional</span>
                        </div>
                        <a href="{{ route('kasir.penerimaan.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('kasir.penerimaan.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25' : 'text-emerald-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-emerald-400"><i class="fa-solid fa-boxes-packing"></i></span>
                            <span class="flex-1 leading-snug">Penerimaan Mutasi Barang & Uang</span>
                        </a>
                        <a href="{{ route('kasir.request_operasional.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('kasir.request_operasional.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-slate-400 group-hover:text-indigo-400"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                            <span class="flex-1 leading-snug">Pengajuan Dana & Bantuan</span>
                        </a>
                        
                        <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-widest text-slate-400/90 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock-rotate-left text-[9px] text-rose-400"></i>
                            <span>Administrasi Shift</span>
                        </div>
                        <a href="{{ route('kasir.closing.index') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('kasir.closing.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-slate-400 group-hover:text-indigo-400"><i class="fa-solid fa-lock"></i></span>
                            <span class="flex-1 leading-snug">Laporan Tutup Shift (Closing)</span>
                        </a>
                        <a href="{{ route('kasir.closing.minus') }}" class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('kasir.closing.minus') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25' : 'text-rose-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-5 text-center shrink-0 text-rose-400"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                            <span class="flex-1 leading-snug">Pengembalian Minus (Reimburse)</span>
                        </a>
                    @endif
                @endauth
            </div>
            
            <!-- Sidebar Footer User Card -->
            @auth
            <div class="p-3 border-t border-slate-800 bg-slate-950/60 shrink-0">
                <div class="flex items-center justify-between gap-2 mb-2 px-1">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-slate-400 capitalize truncate">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-1.5 px-3 bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white border border-red-500/30 rounded-lg text-xs font-bold transition">
                        <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
            @endauth
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-x-hidden">
            
            <!-- Sleek Responsive Topbar -->
            <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-3 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between gap-2 shadow-2xs">
                
                <div class="flex items-center gap-2 sm:gap-3 overflow-hidden min-w-0">
                    <!-- Hamburger Toggle Button -->
                    <button class="p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition flex items-center justify-center shrink-0" 
                            @click="sidebarOpen = !sidebarOpen" 
                            title="Buka/Tutup Navigasi">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                    
                    <!-- Kembali ke Dashboard Button (Responsive) -->
                    @if(!request()->routeIs('owner.dashboard') && !request()->routeIs('admin.dashboard') && !request()->routeIs('kasir.dashboard'))
                        @php
                            $dashboardUrl = '#';
                            if(Auth::check()) {
                                if(Auth::user()->role === 'owner') {
                                    $dashboardUrl = route('owner.dashboard');
                                } elseif(Auth::user()->role === 'admin' || Auth::user()->role === 'admin_gudang') {
                                    $dashboardUrl = route('admin.dashboard');
                                } else {
                                    $dashboardUrl = route('kasir.dashboard');
                                }
                            }
                        @endphp
                        <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl border border-slate-200/80 transition shadow-2xs shrink-0" title="Kembali ke Dashboard Utama">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span class="hidden sm:inline">Kembali ke </span>Dashboard
                        </a>
                    @endif

                    <!-- Page Title (Responsive with truncate) -->
                    <h1 class="text-xs sm:text-sm md:text-base font-extrabold text-slate-800 tracking-tight truncate pl-1">
                        @yield('title', 'Dashboard')
                    </h1>
                </div>

                <!-- Network Status Indicator -->
                <div class="shrink-0">
                    <span id="global-net-badge" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 transition shadow-2xs">
                        <span id="global-net-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="global-net-status" class="hidden sm:inline">Online</span>
                    </span>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-3 sm:p-5 lg:p-6 overflow-y-auto">
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-2xs">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 text-base shrink-0"></i>
                        <div class="text-xs sm:text-sm font-semibold leading-relaxed">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-2xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5 text-base shrink-0"></i>
                        <div class="text-xs sm:text-sm font-semibold leading-relaxed">{{ session('error') }}</div>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Global Online/Offline Listener -->
    <script>
        const updateNetStatus = () => {
            const badge = document.getElementById('global-net-badge');
            const dot = document.getElementById('global-net-dot');
            const label = document.getElementById('global-net-status');
            if(badge && dot && label) {
                if (navigator.onLine) {
                    badge.className = 'inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 transition shadow-2xs';
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
                    label.innerText = 'Online';
                } else {
                    badge.className = 'inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 transition shadow-2xs';
                    dot.className = 'w-2 h-2 rounded-full bg-rose-500';
                    label.innerText = 'Offline';
                }
            }
        };
        window.addEventListener('online', updateNetStatus);
        window.addEventListener('offline', updateNetStatus);
        updateNetStatus();
    </script>

    @stack('scripts')
</body>
</html>
