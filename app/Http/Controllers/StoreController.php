<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['images'])
            ->where('is_active', true);

        // Filtros (Busca, Preço, Marca...)
        if ($request->filled('search') && strlen($request->search) >= 3) {
            $query->where('description', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        return Inertia::render('Store/Index', [
            // Paginação de 10 itens com persistência de filtros na URL
            'products' => $query->orderBy('created_at', 'desc')
                                ->paginate(10)
                                ->withQueryString(),
            'brands'   => Product::distinct()->whereNotNull('brand')->pluck('brand'),
            'filters'  => $request->only(['search', 'min_price', 'max_price', 'brand'])
        ]);
    }
}