import { ref, computed } from 'vue';

// Font size levels
const FONT_SIZES = {
    SMALL: 'small',
    MEDIUM: 'medium',
    LARGE: 'large',
    EXTRA_LARGE: 'extra-large',
};

// Contrast modes
const CONTRAST_MODES = {
    NORMAL: 'normal',
    HIGH: 'high',
};

// Shared state
const fontSize = ref(FONT_SIZES.MEDIUM);
const contrastMode = ref(CONTRAST_MODES.NORMAL);

export function useAccessibility() {
    /**
     * Initialize accessibility settings from localStorage
     */
    const initializeAccessibility = () => {
        // Load font size
        const savedFontSize = localStorage.getItem('barber-font-size');
        if (savedFontSize && Object.values(FONT_SIZES).includes(savedFontSize)) {
            fontSize.value = savedFontSize;
        }

        // Load contrast mode
        const savedContrast = localStorage.getItem('barber-contrast-mode');
        if (savedContrast && Object.values(CONTRAST_MODES).includes(savedContrast)) {
            contrastMode.value = savedContrast;
        }

        applyAccessibilityToDocument();
    };

    /**
     * Apply accessibility classes to document
     */
    const applyAccessibilityToDocument = () => {
        const html = document.documentElement;

        // Remove all font size classes
        Object.values(FONT_SIZES).forEach(size => {
            html.classList.remove(`font-${size}`);
        });

        // Remove all contrast classes
        Object.values(CONTRAST_MODES).forEach(mode => {
            html.classList.remove(`contrast-${mode}`);
        });

        // Add current settings
        html.classList.add(`font-${fontSize.value}`);
        html.classList.add(`contrast-${contrastMode.value}`);
    };

    /**
     * Set font size
     */
    const setFontSize = (size) => {
        if (Object.values(FONT_SIZES).includes(size)) {
            fontSize.value = size;
            localStorage.setItem('barber-font-size', size);
            applyAccessibilityToDocument();
        }
    };

    /**
     * Increase font size
     */
    const increaseFontSize = () => {
        const sizes = Object.values(FONT_SIZES);
        const currentIndex = sizes.indexOf(fontSize.value);
        if (currentIndex < sizes.length - 1) {
            setFontSize(sizes[currentIndex + 1]);
        }
    };

    /**
     * Decrease font size
     */
    const decreaseFontSize = () => {
        const sizes = Object.values(FONT_SIZES);
        const currentIndex = sizes.indexOf(fontSize.value);
        if (currentIndex > 0) {
            setFontSize(sizes[currentIndex - 1]);
        }
    };

    /**
     * Reset font size to medium
     */
    const resetFontSize = () => {
        setFontSize(FONT_SIZES.MEDIUM);
    };

    /**
     * Set contrast mode
     */
    const setContrastMode = (mode) => {
        if (Object.values(CONTRAST_MODES).includes(mode)) {
            contrastMode.value = mode;
            localStorage.setItem('barber-contrast-mode', mode);
            applyAccessibilityToDocument();
        }
    };

    /**
     * Toggle contrast mode
     */
    const toggleContrastMode = () => {
        const newMode = contrastMode.value === CONTRAST_MODES.NORMAL
            ? CONTRAST_MODES.HIGH
            : CONTRAST_MODES.NORMAL;
        setContrastMode(newMode);
    };

    /**
     * Get Tailwind text size class
     */
    const textSizeClass = computed(() => {
        const sizeMap = {
            [FONT_SIZES.SMALL]: 'text-sm',
            [FONT_SIZES.MEDIUM]: 'text-base',
            [FONT_SIZES.LARGE]: 'text-lg',
            [FONT_SIZES.EXTRA_LARGE]: 'text-xl',
        };
        return sizeMap[fontSize.value];
    });

    /**
     * Check if high contrast is enabled
     */
    const isHighContrast = computed(() => contrastMode.value === CONTRAST_MODES.HIGH);

    /**
     * Check if font size can be increased
     */
    const canIncreaseFontSize = computed(() => {
        const sizes = Object.values(FONT_SIZES);
        return sizes.indexOf(fontSize.value) < sizes.length - 1;
    });

    /**
     * Check if font size can be decreased
     */
    const canDecreaseFontSize = computed(() => {
        const sizes = Object.values(FONT_SIZES);
        return sizes.indexOf(fontSize.value) > 0;
    });

    return {
        // State
        fontSize: computed(() => fontSize.value),
        contrastMode: computed(() => contrastMode.value),
        textSizeClass,
        isHighContrast,
        canIncreaseFontSize,
        canDecreaseFontSize,

        // Constants
        FONT_SIZES,
        CONTRAST_MODES,

        // Methods
        initializeAccessibility,
        setFontSize,
        increaseFontSize,
        decreaseFontSize,
        resetFontSize,
        setContrastMode,
        toggleContrastMode,
    };
}
