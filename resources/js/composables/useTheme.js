import { ref, computed, watch, onMounted } from 'vue';

// Available themes
const THEMES = {
  KIDS: 'kids',
  YOUTH: 'youth',
  ADULTS: 'adults',
};

// Time modes
const TIME_MODES = {
  DAY: 'day',
  NIGHT: 'night',
};

// Shared state (singleton pattern)
const currentTheme = ref(THEMES.ADULTS);
const currentTimeMode = ref(TIME_MODES.DAY);
const isAutoTimeMode = ref(true);

export function useTheme() {
  /**
   * Determine if current time is day or night
   * Day: 6:00 AM - 6:00 PM
   * Night: 6:00 PM - 6:00 AM
   */
  const getTimeMode = () => {
    const hour = new Date().getHours();
    return hour >= 6 && hour < 18 ? TIME_MODES.DAY : TIME_MODES.NIGHT;
  };

  /**
   * Initialize theme from localStorage or defaults
   */
  const initializeTheme = () => {
    // Load theme preference
    const savedTheme = localStorage.getItem('barber-theme');
    if (savedTheme && Object.values(THEMES).includes(savedTheme)) {
      currentTheme.value = savedTheme;
    }

    // Load time mode preference
    const savedTimeMode = localStorage.getItem('barber-time-mode');
    const savedAutoMode = localStorage.getItem('barber-auto-time-mode');
    
    if (savedAutoMode !== null) {
      isAutoTimeMode.value = savedAutoMode === 'true';
    }

    if (isAutoTimeMode.value) {
      currentTimeMode.value = getTimeMode();
    } else if (savedTimeMode && Object.values(TIME_MODES).includes(savedTimeMode)) {
      currentTimeMode.value = savedTimeMode;
    }

    applyThemeToDocument();
  };

  /**
   * Apply theme classes to document
   */
  const applyThemeToDocument = () => {
    const html = document.documentElement;
    
    // Remove all theme classes
    Object.values(THEMES).forEach(theme => {
      html.classList.remove(`theme-${theme}`);
    });
    
    // Remove time mode classes
    Object.values(TIME_MODES).forEach(mode => {
      html.classList.remove(`mode-${mode}`);
    });

    // Add current theme and time mode
    html.classList.add(`theme-${currentTheme.value}`);
    html.classList.add(`mode-${currentTimeMode.value}`);
  };

  /**
   * Set theme
   */
  const setTheme = (theme) => {
    if (Object.values(THEMES).includes(theme)) {
      currentTheme.value = theme;
      localStorage.setItem('barber-theme', theme);
      applyThemeToDocument();
    }
  };

  /**
   * Set time mode (day/night)
   */
  const setTimeMode = (mode) => {
    if (Object.values(TIME_MODES).includes(mode)) {
      currentTimeMode.value = mode;
      isAutoTimeMode.value = false;
      localStorage.setItem('barber-time-mode', mode);
      localStorage.setItem('barber-auto-time-mode', 'false');
      applyThemeToDocument();
    }
  };

  /**
   * Toggle between day and night
   */
  const toggleTimeMode = () => {
    const newMode = currentTimeMode.value === TIME_MODES.DAY 
      ? TIME_MODES.NIGHT 
      : TIME_MODES.DAY;
    setTimeMode(newMode);
  };

  /**
   * Enable auto time mode
   */
  const enableAutoTimeMode = () => {
    isAutoTimeMode.value = true;
    currentTimeMode.value = getTimeMode();
    localStorage.setItem('barber-auto-time-mode', 'true');
    applyThemeToDocument();
  };

  /**
   * Get current theme class for styling
   */
  const themeClass = computed(() => `theme-${currentTheme.value} mode-${currentTimeMode.value}`);

  /**
   * Check if a specific theme is active
   */
  const isThemeActive = (theme) => currentTheme.value === theme;

  /**
   * Check if day mode is active
   */
  const isDayMode = computed(() => currentTimeMode.value === TIME_MODES.DAY);

  /**
   * Check if night mode is active
   */
  const isNightMode = computed(() => currentTimeMode.value === TIME_MODES.NIGHT);

  // Watch for time changes when auto mode is enabled
  if (isAutoTimeMode.value) {
    setInterval(() => {
      if (isAutoTimeMode.value) {
        const newMode = getTimeMode();
        if (newMode !== currentTimeMode.value) {
          currentTimeMode.value = newMode;
          applyThemeToDocument();
        }
      }
    }, 60000); // Check every minute
  }

  return {
    // State
    currentTheme: computed(() => currentTheme.value),
    currentTimeMode: computed(() => currentTimeMode.value),
    isAutoTimeMode: computed(() => isAutoTimeMode.value),
    themeClass,
    isDayMode,
    isNightMode,
    
    // Constants
    THEMES,
    TIME_MODES,
    
    // Methods
    initializeTheme,
    setTheme,
    setTimeMode,
    toggleTimeMode,
    enableAutoTimeMode,
    isThemeActive,
  };
}
