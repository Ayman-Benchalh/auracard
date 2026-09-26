import { useState, useEffect, useCallback } from 'react';

export type AuraTheme = 'light' | 'dark';

const STORAGE_KEY = 'aura-theme';
const THEME_CHANGE_EVENT = 'aura-theme-change';

/**
 * Get current AURA CARD theme preference.
 * Priority:
 * 1. Saved localStorage preference ('dark' or 'light')
 * 2. Strict default: 'light' (never OS preference)
 */
export function getStoredTheme(): AuraTheme {
    if (typeof window === 'undefined') {
        return 'light';
    }
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
    } catch {
        // Fallback for sandboxed environments
    }
    return 'light';
}

/**
 * Apply theme to document element
 */
export function applyTheme(theme: AuraTheme): void {
    if (typeof document === 'undefined') return;

    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Fallback for restricted storage
    }
}

export function useTheme() {
    const [theme, setThemeState] = useState<AuraTheme>(() => getStoredTheme());

    useEffect(() => {
        // Sync on mount
        const initialTheme = getStoredTheme();
        setThemeState(initialTheme);
        applyTheme(initialTheme);

        // Listen for in-app theme changes
        const handleThemeChange = (e: CustomEvent<AuraTheme>) => {
            setThemeState(e.detail);
        };

        // Listen for cross-tab storage changes
        const handleStorageChange = (e: StorageEvent) => {
            if (e.key === STORAGE_KEY && (e.newValue === 'light' || e.newValue === 'dark')) {
                setThemeState(e.newValue);
                applyTheme(e.newValue);
            }
        };

        window.addEventListener(THEME_CHANGE_EVENT as any, handleThemeChange);
        window.addEventListener('storage', handleStorageChange);

        return () => {
            window.removeEventListener(THEME_CHANGE_EVENT as any, handleThemeChange);
            window.removeEventListener('storage', handleStorageChange);
        };
    }, []);

    const setTheme = useCallback((newTheme: AuraTheme) => {
        const validTheme: AuraTheme = newTheme === 'dark' ? 'dark' : 'light';
        setThemeState(validTheme);
        applyTheme(validTheme);
        window.dispatchEvent(new CustomEvent(THEME_CHANGE_EVENT, { detail: validTheme }));
    }, []);

    const toggleTheme = useCallback(() => {
        setThemeState((prevTheme) => {
            const nextTheme: AuraTheme = prevTheme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent(THEME_CHANGE_EVENT, { detail: nextTheme }));
            }
            return nextTheme;
        });
    }, []);

    return {
        theme,
        isDark: theme === 'dark',
        setTheme,
        toggleTheme,
    };
}
