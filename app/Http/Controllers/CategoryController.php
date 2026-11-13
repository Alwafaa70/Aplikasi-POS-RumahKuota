<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // Ambil kategori parent beserta anaknya
        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        // Ambil semua untuk dropdown parent
        $allCategories = Category::all();
        return view('master_kategori.index', compact('categories', 'allCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete(); // Karena on cascade, anak-anaknya otomatis terhapus
        return back()->with('success', 'Kategori dan sub-kategorinya berhasil dihapus.');
    }
}
