import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, ChartColumn, House, UserRound, type LucideIcon } from 'lucide-react';
import { type PropsWithChildren } from 'react';

interface Tab {
    label: string;
    href: string;
    icon: LucideIcon;
    isActive: (url: string) => boolean;
}

const tabs: Tab[] = [
    {
        label: 'الرئيسية',
        href: '/shop',
        icon: House,
        isActive: (url) => url === '/shop' || url.startsWith('/shop/visits') || url.startsWith('/shop/cars'),
    },
    { label: 'التذكيرات', href: '/shop/reminders', icon: Bell, isActive: (url) => url.startsWith('/shop/reminders') },
    { label: 'التقارير', href: '/shop/analytics', icon: ChartColumn, isActive: (url) => url.startsWith('/shop/analytics') },
];

export default function ShopLayout({ shop, children }: PropsWithChildren<{ shop: Shop }>) {
    const { name, impersonating } = usePage<SharedData>().props;
    const { url } = usePage();

    const accountActive = url.startsWith('/shop/account');

    return (
        // Muted backdrop so the phone-width app reads as a deliberate centered
        // card on laptops instead of a lost column. Mobile-first stays intact.
        <div className="bg-muted flex min-h-screen justify-center">
            <div className="bg-background text-foreground md:border-border relative flex min-h-screen w-full max-w-md flex-col md:border-x md:shadow-2xl">
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
                <header className="bg-primary text-primary-foreground flex items-center justify-between gap-3 px-5 pt-4 pb-3.5">
                    <div className="min-w-0">
                        {/* Brand comes from APP_NAME via the shared `name` prop — never hardcode it */}
                        <div className="text-2xl font-extrabold tracking-wide">{name}</div>
                        <div className="text-primary-foreground/70 truncate text-[13px] font-medium">
                            {shop.name} — {shop.area}
                        </div>
                    </div>
                    <Link
                        href={route('shop.account')}
                        aria-label="حسابي"
                        className={`flex size-11 shrink-0 items-center justify-center rounded-full ${accountActive ? 'bg-white/25' : 'bg-white/10'}`}
                    >
                        <UserRound className="size-6" aria-hidden />
                    </Link>
                </header>

                <main className="flex flex-1 flex-col gap-4 px-4 pt-5 pb-28">{children}</main>

                <nav className="fixed inset-x-0 bottom-0 z-10">
                    <div className="border-border bg-card mx-auto grid max-w-md grid-cols-3 border-t pb-[env(safe-area-inset-bottom)]">
                        {tabs.map((tab) => {
                            const active = tab.isActive(url);

                            return (
                                <Link
                                    key={tab.href}
                                    href={tab.href}
                                    className={`flex min-h-16 flex-col items-center justify-center gap-1 text-[15px] font-bold ${
                                        active ? 'text-primary' : 'text-muted-foreground'
                                    }`}
                                >
                                    <tab.icon className="size-6" aria-hidden />
                                    {tab.label}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
            </div>
        </div>
    );
}
