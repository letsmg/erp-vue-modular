<?php

namespace App\Repositories;

use Modules\Product\Models\Product;
use App\Services\SmartSearchService;
use Illuminate\Http\Request;

class StoreRepository
{
    protected SmartSearchService $smartSearchService;

    public function __construct(SmartSearchService $smartSearchService)
    {
        $this->smartSearchService = $smartSearchService;
    }

    /**
     * 🔍 Produtos com filtros (usando busca inteligente)
     */
    public function getFilteredProducts(array $filters)
    {
        $searchTerm = $filters['search'] ?? '';
        $otherFilters = collect($filters)->except(['search'])->toArray();

        // Usa busca inteligente (Redis/PostgreSQL)
        $products = $this->smartSearchService->search($searchTerm, $otherFilters);

        // Aplica paginação manualmente
        $page = $filters['page'] ?? 1;
        $perPage = 12;
        
        $total = $products->count();
        $offset = ($page - 1) * $perPage;
        
        $paginatedProducts = $products->slice($offset, $perPage)->values();
        
        // Cria objeto de paginação compatível com Laravel
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedProducts,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        // Adiciona withQueryString para manter filtros
        $pagination->withQueryString();

        return $pagination;
    }

    /**
     * Produtos em destaque
     */
    public function getFeaturedProducts(int $limit = 5)
    {
        return Product::with(['images', 'seo'])
            ->where('is_active', true)
            ->where('is_featured', true) // 🔥 corrigido (antes estava random)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * 💸 Produtos mais baratos (promoção simples)
     */
    public function getOnSaleProducts(int $limit = 8)
    {
        return Product::with(['images'])
            ->where('is_active', true)
            ->orderBy('sale_price', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * 🏷️ Lista de marcas
     */
    public function getAllBrands()
    {
        return Product::query()
            ->whereNotNull('brand')
            ->where('is_active', true)
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
    }

    /**
     * Limpa strings para busca numérica (preços).
     */
    private function parseNumeric($value) 
    {
        $cleaned = preg_replace('/[^0-9,.]/', '', str_replace(',', '.', $value));
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
}