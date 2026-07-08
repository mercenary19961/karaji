import { useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

// v1 is light-only: garage tablets live in direct sunlight and there is no
// designed dark palette (the scaffold `.dark` tokens clash with our light
// mapping). applyTheme always clears the `dark` class so neither the OS
// preference nor a stale localStorage value can force dark onto any portal.
const applyTheme = () => {
    document.documentElement.classList.remove('dark');
};

export function initializeTheme() {
    applyTheme();
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('light');

    const updateAppearance = (mode: Appearance) => {
        setAppearance(mode);
        localStorage.setItem('appearance', mode);
        applyTheme();
    };

    useEffect(() => {
        applyTheme();
    }, []);

    return { appearance, updateAppearance };
}
