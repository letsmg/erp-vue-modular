<?php

namespace App\Http\Controllers;

use App\Services\StoreService;
use Modules\Product\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    protected $service;

    public function __construct(StoreService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'min_price', 'max_price', 'brand', 'sort']);
        $data = $this->service->getDataForIndex($filters);

        return Inertia::render('Store/Index', $data);
    }

    /**
     * 🔥 CORRIGIDO: agora usa Route Model Binding com SLUG
     */
    public function show(Product $product)
    {
        // Garante que apenas produtos ativos sejam exibidos
        abort_if(!$product->is_active, 404);

        // Carrega relações
        $product->load(['images', 'seo']);

        // Produtos relacionados
        $relatedProducts = Product::with('images')
            ->where('brand', $product->brand)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return Inertia::render('Store/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    public function acceptTerms(Request $request)
    {
        $this->service->recordTermAcceptance($request);

        return back()->with('success', 'Termos aceitos.');
    }

    /**
     * Endpoint JSON para Load More
     */
    public function getProducts(Request $request)
    {
        $filters = $request->only(['search', 'min_price', 'max_price', 'brand', 'sort', 'page']);
        $products = $this->service->getFilteredProducts($filters);

        return response()->json($products);
    }
}