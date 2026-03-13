import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

export function useStoreIndex(props) {
    const search = ref(props.filters?.search || '');
    const minPrice = ref(props.filters?.min_price || '');
    const maxPrice = ref(props.filters?.max_price || '');
    const brand = ref(props.filters?.brand || '');
    const category = ref(props.filters?.category || '');

    // Função que dispara a busca para o servidor
    const filterProducts = debounce(() => {
        // Só filtra por busca se tiver 0 (vazio) ou mais de 2 caracteres
        const searchTerm = search.value.length > 0 && search.value.length < 3 ? '' : search.value;

        router.get(route('store.index'), {
            search: searchTerm,
            min_price: minPrice.value,
            max_price: maxPrice.value,
            brand: brand.value,
            category: category.value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true // Evita encher o histórico do navegador a cada tecla
        });
    }, 500);

    // Observa mudanças nos filtros
    watch([search, minPrice, maxPrice, brand, category], () => {
        filterProducts();
    });

    return {
        search, minPrice, maxPrice, brand, category,
        filterProducts
    };
}