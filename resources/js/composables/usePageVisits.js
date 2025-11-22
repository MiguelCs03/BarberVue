import { ref, computed } from 'vue';

// Shared state for page visits
const pageVisits = ref({});
const currentPageVisits = ref(0);

export function usePageVisits() {
    /**
     * Initialize page visits from localStorage
     */
    const initializePageVisits = () => {
        const stored = localStorage.getItem('barber-page-visits');
        if (stored) {
            try {
                pageVisits.value = JSON.parse(stored);
            } catch (e) {
                console.error('Error parsing page visits:', e);
                pageVisits.value = {};
            }
        }
    };

    /**
     * Save page visits to localStorage
     */
    const savePageVisits = () => {
        localStorage.setItem('barber-page-visits', JSON.stringify(pageVisits.value));
    };

    /**
     * Track a page visit
     * @param {string} pageName - Name of the page (e.g., 'users.index', 'dashboard')
     */
    const trackPageVisit = (pageName) => {
        if (!pageName) return;

        // Initialize if doesn't exist
        if (!pageVisits.value[pageName]) {
            pageVisits.value[pageName] = {
                count: 0,
                lastVisit: null,
                firstVisit: new Date().toISOString(),
            };
        }

        // Increment count
        pageVisits.value[pageName].count++;
        pageVisits.value[pageName].lastVisit = new Date().toISOString();

        // Update current page visits
        currentPageVisits.value = pageVisits.value[pageName].count;

        // Save to localStorage
        savePageVisits();
    };

    /**
     * Get visit count for a specific page
     * @param {string} pageName - Name of the page
     * @returns {number} Visit count
     */
    const getPageVisitCount = (pageName) => {
        return pageVisits.value[pageName]?.count || 0;
    };

    /**
     * Get all page visits sorted by count
     * @returns {Array} Array of {page, count, lastVisit}
     */
    const getAllPageVisits = computed(() => {
        return Object.entries(pageVisits.value)
            .map(([page, data]) => ({
                page,
                count: data.count,
                lastVisit: data.lastVisit,
                firstVisit: data.firstVisit,
            }))
            .sort((a, b) => b.count - a.count);
    });

    /**
     * Get total visits across all pages
     */
    const totalVisits = computed(() => {
        return Object.values(pageVisits.value).reduce((sum, data) => sum + data.count, 0);
    });

    /**
     * Get most visited page
     */
    const mostVisitedPage = computed(() => {
        const sorted = getAllPageVisits.value;
        return sorted.length > 0 ? sorted[0] : null;
    });

    /**
     * Clear all visit data
     */
    const clearAllVisits = () => {
        pageVisits.value = {};
        currentPageVisits.value = 0;
        localStorage.removeItem('barber-page-visits');
    };

    /**
     * Get human-readable page name
     * @param {string} pageName - Technical page name
     * @returns {string} Human-readable name
     */
    const getPageDisplayName = (pageName) => {
        const names = {
            'dashboard': 'Dashboard',
            'users.index': 'Lista de Usuarios',
            'users.create': 'Crear Usuario',
            'users.edit': 'Editar Usuario',
            'users.show': 'Detalle de Usuario',
            'products.index': 'Lista de Productos',
            'products.create': 'Crear Producto',
            'products.edit': 'Editar Producto',
            'products.show': 'Detalle de Producto',
            'profile.edit': 'Editar Perfil',
            'analytics.visits': 'Estadísticas de Visitas',
            'theme.test': 'Prueba de Temas',
        };
        return names[pageName] || pageName;
    };

    return {
        // State
        pageVisits: computed(() => pageVisits.value),
        currentPageVisits: computed(() => currentPageVisits.value),
        totalVisits,
        mostVisitedPage,
        getAllPageVisits,

        // Methods
        initializePageVisits,
        trackPageVisit,
        getPageVisitCount,
        clearAllVisits,
        getPageDisplayName,
    };
}
