import { useT, type TKey } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, ChartColumn, Coins, House, Languages, LogOut, MessageSquare, Plus, QrCode, UserRound, type LucideIcon } from 'lucide-react';
import { type PropsWithChildren } from 'react';

interface NavItem {
    labelKey: TKey;
    href: string;
    icon: LucideIcon;
    isActive: (url: string) => boolean;
}

// Shared between the desktop sidebar and the mobile bottom bar.
const primaryNav: NavItem[] = [
    {
        labelKey: 'nav.home',
        href: '/shop',
        icon: House,
        isActive: (url) => url === '/shop' || url.startsWith('/shop/cars'),
    },
    { labelKey: 'nav.reminders', href: '/shop/reminders', icon: Bell, isActive: (url) => url.startsWith('/shop/reminders') },
    { labelKey: 'nav.reports', href: '/shop/analytics', icon: ChartColumn, isActive: (url) => url.startsWith('/shop/analytics') },
    { labelKey: 'nav.messages', href: '/shop/messages', icon: MessageSquare, isActive: (url) => url.startsWith('/shop/messages') },
];

// The primary action — a prominent CTA at the top of the desktop sidebar (the
// mobile equivalent is the dashboard's big "New visit" button).
const newVisitItem: NavItem = {
    labelKey: 'nav.new_visit',
    href: '/shop/entry',
    icon: Plus,
    isActive: (url) => url.startsWith('/shop/entry') || url.startsWith('/shop/visits'),
};

// Settings-level items — desktop sidebar only (mobile reaches them via account)
const servicePricesItem: NavItem = {
    labelKey: 'nav.prices',
    href: '/shop/service-prices',
    icon: Coins,
    isActive: (url) => url.startsWith('/shop/service-prices'),
};

const registrationsItem: NavItem = {
    labelKey: 'nav.registrations',
    href: '/shop/registrations',
    icon: QrCode,
    isActive: (url) => url.startsWith('/shop/registrations'),
};

const accountItem: NavItem = {
    labelKey: 'nav.account',
    href: '/shop/account',
    icon: UserRound,
    isActive: (url) => url.startsWith('/shop/account'),
};

export default function ShopLayout({ shop, children }: PropsWithChildren<{ shop: Shop }>) {
    const { name, impersonating, auth, locale, shopUnread, pendingCount } = usePage<SharedData>().props;
    const { url } = usePage();
    const t = useT();

    const sidebarNav = [...primaryNav, servicePricesItem, registrationsItem, accountItem];
    const avatarUrl = auth.user.avatar_url;

    const otherLocale = locale === 'en' ? 'ar' : 'en';
    const switchLanguage = () => router.get(route('locale', otherLocale), {}, { preserveScroll: true });

    return (
        <div className="bg-muted flex min-h-screen">
            {/* ===== Desktop sidebar (tablet & up) ===== */}
            <aside className="bg-primary text-primary-foreground hidden w-64 shrink-0 flex-col p-4 md:flex">
                <div className="flex items-center gap-2.5 px-2 pt-2 pb-5">
                    {/* Brand from APP_NAME via shared `name` — never hardcoded */}
                    <div className="text-[26px] font-extrabold tracking-wide">{name}</div>
                </div>

                <nav className="flex flex-col gap-1">
                    {/* Primary action: start a new visit */}
                    <Link
                        href={newVisitItem.href}
                        className={`bg-cta text-cta-foreground shadow-cta/30 mb-2 flex items-center gap-3 rounded-xl px-3 py-3 text-[16px] font-extrabold shadow-lg transition-transform hover:scale-[1.02] ${
                            newVisitItem.isActive(url) ? 'ring-2 ring-white/40' : ''
                        }`}
                    >
                        <newVisitItem.icon className="size-5" aria-hidden />
                        {t(newVisitItem.labelKey)}
                    </Link>

                    {sidebarNav.map((item) => {
                        const active = item.isActive(url);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-3 rounded-xl px-3 py-3 text-[16px] font-bold transition-colors ${
                                    active
                                        ? 'text-primary-foreground bg-white/15'
                                        : 'text-primary-foreground/70 hover:text-primary-foreground hover:bg-white/10'
                                }`}
                            >
                                <item.icon className="size-5" aria-hidden />
                                {t(item.labelKey)}
                                {item.href === '/shop/messages' && shopUnread > 0 && (
                                    <span className="bg-destructive text-destructive-foreground ms-auto flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-extrabold">
                                        {shopUnread}
                                    </span>
                                )}
                                {item.href === '/shop/registrations' && pendingCount > 0 && (
                                    <span className="bg-cta text-cta-foreground ms-auto flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-extrabold">
                                        {pendingCount}
                                    </span>
                                )}
                            </Link>
                        );
                    })}
                </nav>

                <div className="mt-auto border-t border-white/15 pt-4">
                    <div className="flex items-center gap-2.5 px-2 pb-2">
                        <div className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/10">
                            {avatarUrl ? (
                                <img src={avatarUrl} alt="" className="size-full object-cover" />
                            ) : (
                                <UserRound className="size-5" aria-hidden />
                            )}
                        </div>
                        <div className="min-w-0">
                            <div className="truncate text-sm font-bold">{shop.name}</div>
                            <div className="text-primary-foreground/60 truncate text-xs">{shop.area}</div>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={switchLanguage}
                        className="text-primary-foreground/70 mt-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/15 py-2.5 text-sm font-bold hover:bg-white/10"
                    >
                        <Languages className="size-4" aria-hidden />
                        {locale === 'en' ? 'العربية' : 'English'}
                    </button>
                    <button
                        type="button"
                        onClick={() => router.post(route('logout'))}
                        className="text-primary-foreground/70 mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/15 py-2.5 text-sm font-bold hover:bg-white/10"
                    >
                        <LogOut className="size-4" aria-hidden />
                        {t('nav.logout')}
                    </button>
                </div>
            </aside>

            {/* ===== Content column (mobile header + main + mobile bottom nav) ===== */}
            <div className="flex min-h-screen w-full flex-col">
                {/* Operator-only banner while "Login as shop" is active — EN on purpose */}
                {impersonating && (
                    <div dir="ltr" className="bg-cta text-cta-foreground flex items-center justify-between px-4 py-1.5 text-sm font-bold">
                        Viewing as shop
                        <button
                            type="button"
                            onClick={() => router.post(route('impersonation.leave'))}
                            className="min-h-10 cursor-pointer px-2 underline"
                        >
                            Return to admin
                        </button>
                    </div>
                )}

                {/* Mobile header (phones only) */}
                <header className="bg-primary text-primary-foreground flex items-center justify-between gap-3 px-5 pt-4 pb-3.5 md:hidden">
                    <div className="min-w-0">
                        <div className="text-2xl font-extrabold tracking-wide">{name}</div>
                        <div className="text-primary-foreground/70 truncate text-[13px] font-medium">
                            {shop.name} · {shop.area}
                        </div>
                    </div>
                    <div className="flex shrink-0 items-center gap-2">
                        <button
                            type="button"
                            onClick={switchLanguage}
                            aria-label={locale === 'en' ? 'التبديل إلى العربية' : 'Switch to English'}
                            className="flex h-11 items-center justify-center gap-1 rounded-full bg-white/10 px-3 text-[13px] font-extrabold"
                        >
                            <Languages className="size-5" aria-hidden />
                            {locale === 'en' ? 'ع' : 'EN'}
                        </button>
                        <Link
                            href={route('shop.account')}
                            aria-label={t('nav.account')}
                            className={`flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full ${
                                accountItem.isActive(url) ? 'bg-white/25' : 'bg-white/10'
                            }`}
                        >
                            {avatarUrl ? (
                                <img src={avatarUrl} alt="" className="size-full object-cover" />
                            ) : (
                                <UserRound className="size-6" aria-hidden />
                            )}
                        </Link>
                    </div>
                </header>

                {/* Capped like the old design on phones; opens up on tablet+ */}
                <main className="mx-auto flex w-full max-w-md flex-1 flex-col gap-4 px-4 pt-5 pb-28 md:max-w-6xl md:gap-5 md:px-8 md:pt-8 md:pb-12">
                    {children}
                </main>

                {/* Mobile bottom nav (phones only) */}
                <nav className="fixed inset-x-0 bottom-0 z-10 md:hidden">
                    <div className="border-border bg-card mx-auto grid max-w-md grid-cols-4 border-t pb-[env(safe-area-inset-bottom)]">
                        {primaryNav.map((item) => {
                            const active = item.isActive(url);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`flex min-h-16 flex-col items-center justify-center gap-1 text-[13px] font-bold ${
                                        active ? 'text-primary' : 'text-muted-foreground'
                                    }`}
                                >
                                    <span className="relative">
                                        <item.icon className="size-6" aria-hidden />
                                        {item.href === '/shop/messages' && shopUnread > 0 && (
                                            <span className="bg-destructive text-destructive-foreground absolute -end-2.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-extrabold">
                                                {shopUnread}
                                            </span>
                                        )}
                                    </span>
                                    {t(item.labelKey)}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
            </div>
        </div>
    );
}
