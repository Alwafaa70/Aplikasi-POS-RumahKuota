@extends('layouts.app')
@section('title', 'Master Kategori')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .topbar { background-color: white !important; }
    .header { background-color: #4A90E2 !important; }
</style>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen" x-data="{ modalOpen: false }">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Kategori Bersarang</h1>
                <p class="text-sm text-gray-500">Buat hierarki kategori tak terbatas untuk sistem POS Anda.</p>
            </div>
            <button @click="modalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition">
                + Kategori Baru
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden p-6">
            <h2 class="text-lg font-bold mb-4 border-b pb-2">Pohon Kategori Terdaftar</h2>
            
            @if($categories->isEmpty())
                <div class="text-gray-500 text-center py-4">Belum ada kategori. Buat kategori pertama Anda!</div>
            @else
                <ul class="space-y-2">
                    @foreach($categories as $parent)
                        <li class="p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-indigo-700">📦 {{ $parent->name }}</span>
                                <form action="{{ route('master.kategori.destroy', $parent->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Hapus kategori ini beserta seluruh sub-kategorinya?')">Hapus</button>
                                </form>
                            </div>
                            
                            <!-- Sub Kategori Level 1 -->
                            @if($parent->children->isNotEmpty())
                                <ul class="mt-2 ml-6 space-y-1 border-l-2 border-indigo-200 pl-4">
                                    @foreach($parent->children as $child)
                                        <li class="py-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-700">↳ {{ $child->name }}</span>
                                                <form action="{{ route('master.kategori.destroy', $child->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 text-xs" onclick="return confirm('Hapus?')">Hapus</button>
                                                </form>
                                            </div>
                                            
                                            <!-- Sub Kategori Level 2 -->
                                            @if($child->children->isNotEmpty())
                                                <ul class="mt-1 ml-4 space-y-1 border-l border-gray-300 pl-4">
                                                    @foreach($child->children as $subchild)
                                                        <li class="py-1 flex justify-between items-center">
                                                            <span class="text-gray-500 text-sm">- {{ $subchild->name }}</span>
                                                            <form action="{{ route('master.kategori.destroy', $subchild->id) }}" method="POST" class="inline">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-red-300 hover:text-red-500 text-xs" onclick="return confirm('Hapus?')">Hapus</button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="modalOpen" class="fixed inset-0 bg-gray-900 opacity-60" @click="modalOpen = false"></div>
                <div x-show="modalOpen" class="bg-white rounded-lg shadow-xl relative w-full max-w-md p-6 z-50">
                    <h3 class="text-lg font-bold mb-4">Tambah Kategori Baru</h3>
                    <form action="{{ route('master.kategori.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Kategori</label>
                            <input type="text" name="name" required class="w-full border-gray-300 rounded-md p-2 border focus:ring-indigo-500">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Induk Kategori (Opsional)</label>
                            <select name="parent_id" class="w-full border-gray-300 rounded-md p-2 border focus:ring-indigo-500">
                                <option value="">-- Jadikan Kategori Utama --</option>
                                @foreach($allCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Jika dipilih, kategori baru akan menjadi sub-kategori dari induk ini.</p>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
