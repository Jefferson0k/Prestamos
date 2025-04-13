import { ref, computed, watch } from 'vue';
import { Table } from '@tanstack/vue-table';

export function usePagination<T>(table: Table<T>, data: T[]) {
    const pageSize = ref(10);
    const currentPage = ref(0);

    const totalPages = computed(() => Math.ceil(data.length / pageSize.value));

    const paginatedData = computed(() => {
        const start = currentPage.value * pageSize.value;
        return data.slice(start, start + pageSize.value);
    });

    const setPage = (page: number) => {
        if (page >= 0 && page < totalPages.value) {
            currentPage.value = page;
        }
    };

    watch(pageSize, () => {
        currentPage.value = 0;
    });

    return {
        pageSize,
        currentPage,
        totalPages,
        paginatedData,
        setPage,
    };
}
