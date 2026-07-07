import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { type ComponentType, type ReactElement, type ReactNode, useLayoutEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { route as routeFn } from 'ziggy-js';
import { initializeTheme } from './hooks/use-appearance';

declare global {
    const route: typeof routeFn;
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Keep <html lang/dir> in sync with the shared `locale` prop. Blade sets these
// on the first full page load, but a language toggle is a client-side Inertia
// visit that never re-renders <html> — without this the direction stays stale
// (English text still laid out RTL) until a hard refresh. Driving it from a
// React effect on the page's merged props (the same source useT() reads) makes
// it fire on every visit, the AR/EN toggle included.
function LocaleDirection({ locale, children }: { locale: unknown; children: ReactNode }): ReactElement {
    useLayoutEffect(() => {
        const lang = locale === 'en' ? 'en' : 'ar';
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'en' ? 'ltr' : 'rtl';
    }, [locale]);

    return <>{children}</>;
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // Inertia v3 dropped the auto-unwrap of the page module's default export,
    // so resolvePageComponent's Promise<{ default: Component }> must be
    // unwrapped to the component with .then((m) => m.default).
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx')).then((m) => m.default),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <App {...props}>
                {({ Component, props: pageProps, key }) => (
                    <LocaleDirection locale={(pageProps as { locale?: string }).locale}>
                        <Component key={key} {...pageProps} />
                    </LocaleDirection>
                )}
            </App>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
