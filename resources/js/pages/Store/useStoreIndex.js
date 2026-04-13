import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';

export function useStoreIndex(props) {
    const page = usePage();

    // --- FILTROS ---
    const search = ref(props.filters?.search || '');
    const minPrice = ref(props.filters?.min_price || '');
    const maxPrice = ref(props.filters?.max_price || '');
    const brand = ref(props.filters?.brand || '');
    const sortBy = ref(props.filters?.sort || 'created_at_desc');

    // --- MODAL (APENAS TERMOS) ---
    const showTermsModal = ref(false);
    const termsAccepted = ref(false);

    // --- AUXILIARES ---
    const getNormalizedLength = (text) => {
        return text?.normalize('NFD').replace(/[\u0300-\u036f]/g, "").trim().length || 0;
    };

    const getImageUrl = (path) => {
        if (!path) return 'https://placehold.co/600x800';
        return path.startsWith('http') ? path : `/storage/products/${path}`;
    };

    // --- FILTROS ---
    const filterProducts = () => {
        const params = {};
        if (search.value) params.search = search.value;
        if (maxPrice.value) params.max_price = maxPrice.value;
        if (brand.value) params.brand = brand.value;
        if (sortBy.value) params.sort = sortBy.value;

        router.get(route('store.index'), params, {
            preserveState: true,
            preserveScroll: false,
            replace: true,
            onSuccess: () => {
                // Preload da próxima página se houver
                preloadNextPage();
            }
        });
    };

    // --- PRELOAD DA PRÓXIMA PÁGINA ---
    const preloadNextPage = () => {
        setTimeout(() => {
            const nextPageLink = document.querySelector('[rel="next"]');
            if (nextPageLink) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = nextPageLink.href;
                document.head.appendChild(link);
            }
        }, 2000); // Aguarda 2s para não impactar o carregamento atual
    };

    // --- WATCHERS ---
    watch(search, debounce((value) => {
        // Removida a trava de 3 caracteres - busca agora funciona com qualquer termo
        filterProducts();
    }, 500));

    watch([maxPrice, brand, sortBy], () => filterProducts());

    // --- TERMOS ---
    const acceptTerms = () => {
        if (!termsAccepted.value) return;

        localStorage.setItem('erp_terms_accepted', 'true');
        showTermsModal.value = false;

        router.post(route('store.terms.accept'), {}, {
            preserveScroll: true
        });
    };

    // --- CARROSSEL ---
    const scroll = (id, direction) => {
        const el = document.getElementById(id);
        if (!el) return;

        const isAtEnd = el.scrollLeft + el.offsetWidth >= el.scrollWidth - 10;

        if (direction === 'right' && isAtEnd) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            const offset = direction === 'left' ? -el.offsetWidth : el.offsetWidth;
            el.scrollBy({ left: offset, behavior: 'smooth' });
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') showTermsModal.value = false;
    };

    let autoPlay = null;

    onMounted(() => {
        const alreadyAccepted = localStorage.getItem('erp_terms_accepted');
        showTermsModal.value = alreadyAccepted !== 'true';

        window.addEventListener('keydown', handleKeyDown);

        autoPlay = setInterval(() => {
            if (document.getElementById('hero-carousel')) {
                scroll('hero-carousel', 'right');
            }
        }, 7000);
    });

    onUnmounted(() => {
        if (autoPlay) clearInterval(autoPlay);
        window.removeEventListener('keydown', handleKeyDown);
    });

    // --- SEO ---
    const seoData = computed(() => page.props.store_seo ?? {
        title: "Vitrine Premium | Erp Vue Modular",
        description: "Explore nossa seleção exclusiva de produtos.",
        h1: "Catálogo de Produtos"
    });

    return {
        search,
        minPrice,
        maxPrice,
        brand,
        sortBy,
        showTermsModal,
        termsAccepted,
        acceptTerms,
        scroll,
        seoData,
        getImageUrl
    };
}