import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, ChartColumn, House, LogOut, UserRound, type LucideIcon } from 'lucide-react';
import { type PropsWithChildren } from 'react';

interface NavItem {
    label: string;
    href: string;
    icon: LucideIcon;
    isActive: (url: string) => boolean;
}

// Shared between the desktop sidebar and the mobile bottom bar.
const primaryNav: NavItem[] = [
    {
        label: 'الرئيسية',
        href: '/shop',
        icon: House,
        isActive: (url) => url === '/shop' || url.startsWith('/shop/visits') || url.startsWith('/shop/cars'),
    },
    { label: 'التذكيرات', href: '/shop/reminders', icon: Bell, isActive: (url) => url.startsWith('/shop/reminders') },
    { label: 'التقارير', href: '/shop/analytics', icon: ChartColumn, isActive: (url) => url.startsWith('/shop/analytics') },
];

const accountItem: NavItem = {
    label: 'حسابي',
    href: '/shop/account',
    icon: UserRound,
    isActive: (url) => url.startsWith('/shop/account'),
};

export default function ShopLayout({ shop, children }: PropsWithChildren<{ shop: Shop }>) {
    const { name, impersonating } = usePage<SharedData>().props;
    const { url } = usePage();

    const sidebarNav = [...primaryNav, accountItem];

    return (
        <div className="bg-muted flex min-h-screen">
            {/* ===== Desktop sidebar (tablet & up) ===== */}
            <aside className="bg-primary text-primary-foreground hidden w-64 shrink-0 flex-col p-4 md:flex">
                <div className="flex items-center gap-2.5 px-2 pt-2 pb-5">
                    {/* Brand from APP_NAME via shared `name` — never hardcoded */}
                    <div className="text-[26px] font-extrabold tracking-wide">{name}</div>
                </div>

                <nav className="flex flex-col gap-1">
                    {sidebarNav.map((item) => {
                        const active = item.isActive(url);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`flex items-center gap-3 rounded-xl px-3 py-3 text-[16px] font-bold transition-colors ${
                                    active
                                        ? 'bg-cta text-cta-foreground'
                                        : 'text-primary-foreground/70 hover:text-primary-foreground hover:bg-white/10'
                                }`}
                            >
                                <item.icon className="size-5" aria-hidden />
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                <div className="mt-auto border-t border-white/15 pt-4">
                    <div className="flex items-center gap-2.5 px-2 pb-2">
                        <div className="flex size-9 items-center justify-center rounded-full bg-white/10">
                            <UserRound className="size-5" aria-hidden />
                        </div>
                        <div className="min-w-0">
                            <div className="truncate text-sm font-bold">{shop.name}</div>
                            <div className="text-primary-foreground/60 truncate text-xs">{shop.area}</div>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={() => router.post(route('logout'))}
                        className="text-primary-foreground/70 mt-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/15 py-2.5 text-sm font-bold hover:bg-white/10"
                    >
                        <LogOut className="size-4" aria-hidden />
                        تسجيل الخروج
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

                {/* Mobile header (phones only) — unchanged from the current design */}
                <header className="bg-primary text-primary-foreground flex items-center justify-between gap-3 px-5 pt-4 pb-3.5 md:hidden">
                    <div className="min-w-0">
                        <div className="text-2xl font-extrabold tracking-wide">{name}</div>
                        <div className="text-primary-foreground/70 truncate text-[13px] font-medium">
                            {shop.name} — {shop.area}
                        </div>
                    </div>
                    <Link
                        href={route('shop.account')}
                        aria-label="حسابي"
                        className={`flex size-11 shrink-0 items-center justify-center rounded-full ${
                            accountItem.isActive(url) ? 'bg-white/25' : 'bg-white/10'
                        }`}
                    >
                        <UserRound className="size-6" aria-hidden />
                    </Link>
                </header>

                {/* Capped like the old design on phones; opens up on tablet+ */}
                <main className="mx-auto flex w-full max-w-md flex-1 flex-col gap-4 px-4 pt-5 pb-28 md:max-w-6xl md:gap-5 md:px-8 md:pt-8 md:pb-12">
                    {children}
                </main>

                {/* Mobile bottom nav (phones only) — unchanged from the current design */}
                <nav className="fixed inset-x-0 bottom-0 z-10 md:hidden">
                    <div className="border-border bg-card mx-auto grid max-w-md grid-cols-3 border-t pb-[env(safe-area-inset-bottom)]">
                        {primaryNav.map((item) => {
                            const active = item.isActive(url);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`flex min-h-16 flex-col items-center justify-center gap-1 text-[15px] font-bold ${
                                        active ? 'text-primary' : 'text-muted-foreground'
                                    }`}
                                >
                                    <item.icon className="size-6" aria-hidden />
                                    {item.label}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
            </div>
        </div>
    );
}
