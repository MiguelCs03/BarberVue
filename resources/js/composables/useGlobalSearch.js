import { ref } from 'vue';

// Searchable content from all pages
const searchableContent = [
    // Users
    { id: 'users-1', title: 'Juan Pérez', description: 'Propietario - juan.perez@barbershop.com', route: 'users.show', params: 1, category: 'Usuarios' },
    { id: 'users-2', title: 'María González', description: 'Barbero - maria.gonzalez@barbershop.com', route: 'users.show', params: 2, category: 'Usuarios' },
    { id: 'users-3', title: 'Carlos Rodríguez', description: 'Barbero - carlos.rodriguez@barbershop.com', route: 'users.show', params: 3, category: 'Usuarios' },
    { id: 'users-list', title: 'Lista de Usuarios', description: 'Gestión de usuarios del sistema', route: 'users.index', category: 'Usuarios' },
    { id: 'users-create', title: 'Crear Usuario', description: 'Agregar nuevo usuario al sistema', route: 'users.create', category: 'Usuarios' },

    // Products
    { id: 'products-1', title: 'Shampoo profesional', description: 'Shampoo para todo tipo de cabello 500ml - Bs. 8.50', route: 'products.show', params: 1, category: 'Productos' },
    { id: 'products-2', title: 'Pomada', description: 'Pomada para peinar 100ml - Bs. 6.00', route: 'products.show', params: 2, category: 'Productos' },
    { id: 'products-3', title: 'Acondicionador', description: 'Acondicionador 500ml - Bs. 7.50', route: 'products.show', params: 3, category: 'Productos' },
    { id: 'products-4', title: 'Gel fijador', description: 'Gel para peinar 150ml - Bs. 5.50', route: 'products.show', params: 4, category: 'Productos' },
    { id: 'products-list', title: 'Lista de Productos', description: 'Gestión de productos e inventario', route: 'products.index', category: 'Productos' },
    { id: 'products-create', title: 'Crear Producto', description: 'Agregar nuevo producto al inventario', route: 'products.create', category: 'Productos' },

    // Services
    { id: 'services-1', title: 'Corte de cabello', description: 'Corte clásico de caballero - 30 min - Bs. 15.00', route: 'services.show', params: 1, category: 'Servicios' },
    { id: 'services-2', title: 'Afeitado', description: 'Afeitado con navaja y toalla caliente - 20 min - Bs. 10.00', route: 'services.show', params: 2, category: 'Servicios' },
    { id: 'services-3', title: 'Corte + Barba', description: 'Corte y arreglo de barba - 45 min - Bs. 25.00', route: 'services.show', params: 3, category: 'Servicios' },
    { id: 'services-4', title: 'Coloración', description: 'Tinte y tratamiento - 60 min - Bs. 40.00', route: 'services.show', params: 4, category: 'Servicios' },
    { id: 'services-list', title: 'Lista de Servicios', description: 'Gestión de servicios de la barbería', route: 'services.index', category: 'Servicios' },
    { id: 'services-create', title: 'Crear Servicio', description: 'Agregar nuevo servicio', route: 'services.create', category: 'Servicios' },

    // Inventory
    { id: 'inventory-list', title: 'Movimientos de Inventario', description: 'Historial de ingresos, salidas y ajustes', route: 'inventory.index', category: 'Inventario' },
    { id: 'inventory-create', title: 'Registrar Movimiento', description: 'Registrar ingreso, salida o ajuste de inventario', route: 'inventory.create', category: 'Inventario' },

    // Analytics
    { id: 'analytics', title: 'Estadísticas de Visitas', description: 'Análisis de páginas más visitadas', route: 'analytics.visits', category: 'Reportes' },
];

export function useGlobalSearch() {
    const searchQuery = ref('');
    const searchResults = ref([]);
    const isSearching = ref(false);

    /**
     * Perform search across all content
     */
    const performSearch = (query) => {
        searchQuery.value = query;

        if (!query || query.length < 2) {
            searchResults.value = [];
            return;
        }

        const lowerQuery = query.toLowerCase();

        // Search in title and description
        const results = searchableContent.filter(item => {
            const titleMatch = item.title.toLowerCase().includes(lowerQuery);
            const descMatch = item.description.toLowerCase().includes(lowerQuery);
            const categoryMatch = item.category.toLowerCase().includes(lowerQuery);

            return titleMatch || descMatch || categoryMatch;
        });

        // Sort by relevance (title matches first)
        results.sort((a, b) => {
            const aTitle = a.title.toLowerCase().includes(lowerQuery);
            const bTitle = b.title.toLowerCase().includes(lowerQuery);

            if (aTitle && !bTitle) return -1;
            if (!aTitle && bTitle) return 1;
            return 0;
        });

        searchResults.value = results.slice(0, 10); // Limit to 10 results
    };

    /**
     * Clear search
     */
    const clearSearch = () => {
        searchQuery.value = '';
        searchResults.value = [];
    };

    /**
     * Get route for result
     */
    const getResultRoute = (result) => {
        if (result.params) {
            return route(result.route, result.params);
        }
        return route(result.route);
    };

    return {
        searchQuery,
        searchResults,
        isSearching,
        performSearch,
        clearSearch,
        getResultRoute,
    };
}
