<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Modules\Product\Models\Product;
use Illuminate\Support\Collection;

class SmartSearchService
{
    private const CACHE_TTL = 3600; // 1 hora
    private const SHORT_SEARCH_LIMIT = 4; // Busca curta usa Redis (até 4 caracteres)

    /**
     * Busca inteligente: Redis para todas as buscas, PostgreSQL como fallback
     */
    public function search(string $term, array $filters = []): Collection
    {
        $term = trim($term);
        
        // Se termo for vazio, retorna todos os produtos ativos (com filtros aplicados)
        if (empty($term)) {
            \Log::info('Busca vazia - retornando todos os produtos ativos', ['filters' => $filters]);
            return $this->searchPostgres($term, $filters);
        }

        // Sempre tenta buscar no Redis primeiro (desde a primeira letra)
        try {
            \Log::info('Tentando busca no Redis', ['term' => $term, 'length' => strlen($term)]);
            $result = $this->searchRedis($term, $filters);
            \Log::info('Busca no Redis bem-sucedida', ['term' => $term, 'results_count' => $result->count()]);
            return $result;
        } catch (\Exception $e) {
            // Se Redis não estiver disponível, fallback para PostgreSQL
            \Log::warning('Redis não disponível, usando PostgreSQL', [
                'term' => $term, 
                'error' => $e->getMessage(),
                'fallback' => 'PostgreSQL'
            ]);
            return $this->searchPostgres($term, $filters);
        }
    }

    /**
     * Verifica se Redis está disponível
     */
    private function isRedisAvailable(): bool
    {
        // Primeiro verifica se a classe Redis existe
        if (!class_exists('Redis')) {
            return false;
        }

        try {
            // Tenta fazer uma conexão teste com Redis
            Redis::ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Busca no Redis (para consultas curtas)
     */
    private function searchRedis(string $term, array $filters = []): Collection
    {
        // Verifica se Redis está disponível antes de tentar usar
        if (!$this->isRedisAvailable()) {
            throw new \Exception('Redis não está disponível');
        }

        $cacheKey = $this->getCacheKey($term, $filters);
        \Log::info('Verificando cache Redis', ['cache_key' => $cacheKey]);
        
        // Tenta buscar do cache primeiro
        $cached = Redis::get($cacheKey);
        if ($cached) {
            \Log::info('Cache HIT - dados encontrados no Redis', [
                'term' => $term, 
                'cache_key' => $cacheKey,
                'cached_data_size' => strlen($cached)
            ]);
            return collect(json_decode($cached, true));
        }

        \Log::info('Cache MISS - buscando no PostgreSQL e cacheando', [
            'term' => $term, 
            'cache_key' => $cacheKey
        ]);

        // Se não tiver no cache, faz busca no PostgreSQL e cacheia
        $products = $this->searchPostgres($term, $filters);
        
        // Cacheia o resultado por 1 hora
        $jsonData = $products->toJson();
        Redis::setex($cacheKey, self::CACHE_TTL, $jsonData);
        
        \Log::info('Dados cacheados no Redis', [
            'term' => $term,
            'cache_key' => $cacheKey,
            'cache_ttl' => self::CACHE_TTL,
            'data_size' => strlen($jsonData),
            'results_count' => $products->count()
        ]);
        
        return $products;
    }

    /**
     * Busca no PostgreSQL (para consultas longas ou cache miss)
     */
    private function searchPostgres(string $term, array $filters = []): Collection
    {
        \Log::info('Busca no PostgreSQL', [
            'term' => $term,
            'filters' => $filters,
            'reason' => strlen($term) <= 3 ? 'cache_miss_or_redis_unavailable' : 'busca_longa'
        ]);
        
        $query = Product::with(['images', 'supplier'])
            ->where('is_active', true);

        // Aplica filtros adicionais
        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('sale_price', '>=', (float) $filters['price_min']);
        }

        if (!empty($filters['max_price'])) {  // Corrigido: max_price em vez de price_max
            $query->where('sale_price', '<=', (float) $filters['max_price']);
        }

        // Aplica busca textual
        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $searchTerm = "%{$term}%";
                
                // Busca por descrição, marca e modelo
                $q->where(function ($sub) use ($searchTerm) {
                    $sub->whereRaw("unaccent(description) ilike unaccent(?)", [$searchTerm])
                       ->orWhereRaw("unaccent(brand) ilike unaccent(?)", [$searchTerm])
                       ->orWhereRaw("unaccent(model) ilike unaccent(?)", [$searchTerm]);
                });

                // Busca por preço (se for numérico)
                $numericValue = $this->parseNumeric($term);
                if ($numericValue > 0) {
                    $q->orWhere(function ($priceSub) use ($numericValue) {
                        $priceSub->where('sale_price', '<=', $numericValue)
                               ->orWhere('promo_price', '<=', $numericValue);
                    });
                }
            });
        }

        // Executa a busca principal
        $results = $query->get();
        
        // Se não encontrou resultados, tenta busca aproximada
        if ($results->isEmpty() && !empty($term)) {
            \Log::info('Busca sem resultados, tentando busca aproximada', ['term' => $term]);
            
            // Remove busca anterior e tenta com termos parciais
            $query = Product::with(['images', 'supplier'])
                ->where('is_active', true);
            
            // Reaplica filtros
            if (!empty($filters['brand'])) {
                $query->where('brand', $filters['brand']);
            }
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }
            if (!empty($filters['price_min'])) {
                $query->where('sale_price', '>=', (float) $filters['price_min']);
            }
            if (!empty($filters['max_price'])) {
                $query->where('sale_price', '<=', (float) $filters['max_price']);
            }
            
            // Tenta busca com cada palavra do termo
            $words = explode(' ', trim($term));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) >= 2) { // Só busca palavras com 2+ caracteres
                        $partialTerm = "%{$word}%";
                        $q->orWhereRaw("unaccent(description) ilike unaccent(?)", [$partialTerm])
                          ->orWhereRaw("unaccent(brand) ilike unaccent(?)", [$partialTerm])
                          ->orWhereRaw("unaccent(model) ilike unaccent(?)", [$partialTerm]);
                    }
                }
            });
            
            $results = $query->get();
            
            // Se ainda não encontrou, mostra todos os produtos (fallback final)
            if ($results->isEmpty()) {
                \Log::info('Busca aproximada também sem resultados, mostrando todos produtos', ['term' => $term]);
                
                $query = Product::with(['images', 'supplier'])
                    ->where('is_active', true);
                
                // Reaplica filtros (exceto busca textual)
                if (!empty($filters['brand'])) {
                    $query->where('brand', $filters['brand']);
                }
                if (!empty($filters['category_id'])) {
                    $query->where('category_id', $filters['category_id']);
                }
                if (!empty($filters['price_min'])) {
                    $query->where('sale_price', '>=', (float) $filters['price_min']);
                }
                if (!empty($filters['max_price'])) {
                    $query->where('sale_price', '<=', (float) $filters['max_price']);
                }
                
                $results = $query->get();
            }
        }

        // Aplica ordenação
        $sortBy = $filters['sort'] ?? 'created_at_desc';
        switch ($sortBy) {
            case 'description_asc':
                $query->orderBy('description', 'asc');
                break;
            case 'description_desc':
                $query->orderBy('description', 'desc');
                break;
            case 'sale_price_asc':
                $query->orderBy('sale_price', 'asc');
                break;
            case 'sale_price_desc':
                $query->orderBy('sale_price', 'desc');
                break;
            case 'stock_quantity_asc':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_quantity_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->get();
    }

    /**
     * Gera chave de cache única para busca
     */
    private function getCacheKey(string $term, array $filters): string
    {
        $filterString = http_build_query($filters);
        return "search:" . md5($term . $filterString);
    }

    /**
     * Limpa cache de busca específico
     */
    public function clearSearchCache(string $term = null, array $filters = []): void
    {
        // Verifica se Redis está disponível antes de tentar usar
        if (!class_exists('Redis')) {
            \Log::warning('Extensão Redis não está instalada');
            return;
        }

        try {
            if ($term) {
                $cacheKey = $this->getCacheKey($term, $filters);
                Redis::del($cacheKey);
            } else {
                // Limpa todo o cache de busca
                $keys = Redis::keys("search:*");
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erro ao limpar cache Redis: ' . $e->getMessage());
            // Não falha completamente se Redis não estiver disponível
        }
    }

    /**
     * Pré-aquece cache com buscas populares
     */
    public function warmupCache(array $popularTerms = []): void
    {
        // Verifica se Redis está disponível antes de tentar usar
        if (!class_exists('Redis')) {
            \Log::info('Extensão Redis não está instalada, pulando warmup de cache');
            return;
        }

        if (!$this->isRedisAvailable()) {
            \Log::info('Redis não disponível, pulando warmup de cache');
            return;
        }

        $defaultTerms = ['a', 'b', 'c', 'd', 'e', '1', '2', '3', '4', '5'];
        $terms = array_merge($defaultTerms, $popularTerms);

        foreach ($terms as $term) {
            try {
                $this->search($term); // Isso vai cachear automaticamente
            } catch (\Exception $e) {
                \Log::warning("Erro ao cachear termo '{$term}': " . $e->getMessage());
            }
        }
    }

    /**
     * Retorna estatísticas do cache
     */
    public function getCacheStats(): array
    {
        // Verifica se Redis está disponível antes de tentar usar
        if (!class_exists('Redis')) {
            return [
                'total_cached_searches' => 0,
                'memory_usage' => 0,
                'cache_hits' => 0,
                'cache_misses' => 0,
                'redis_available' => false,
                'error' => 'Extensão Redis não está instalada',
            ];
        }

        try {
            $keys = Redis::keys("search:*");
            $stats = [
                'total_cached_searches' => count($keys),
                'memory_usage' => 0,
                'cache_hits' => 0,
                'cache_misses' => 0,
                'redis_available' => true,
            ];

            foreach ($keys as $key) {
                $stats['memory_usage'] += Redis::memory('usage', $key);
            }

            return $stats;
        } catch (\Exception $e) {
            return [
                'total_cached_searches' => 0,
                'memory_usage' => 0,
                'cache_hits' => 0,
                'cache_misses' => 0,
                'redis_available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Converte string para valor numérico
     */
    private function parseNumeric(string $value): float
    {
        $cleaned = preg_replace('/[^0-9,.]/', '', str_replace(',', '.', $value));
        return is_numeric($cleaned) ? (float) $cleaned : 0;
    }
}
