import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

// EN/LTR by design (SetAdminLocale middleware sets the locale server-side;
// dir="ltr" here is defensive for any client-side visit that skipped a full
// page load). Denser UI than the shop portal is fine — the operator is us.
export default function AdminLayout({ children }: PropsWithChildren) {
    const { auth, flash } = usePage<SharedData>().props;

    const today = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    return (
        <div dir="ltr" className="bg-background text-foreground min-h-screen text-start">
            <header className="bg-foreground text-white">
                <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                    <div className="text-lg font-extrabold">Admin Portal</div>
                    <div className="text-sm text-white/60">
                        {auth.user.email} • {today}
                    </div>
                </div>
            </header>

            <main className="mx-auto flex max-w-5xl flex-col gap-6 px-6 py-7">
                {flash.success && (
                    <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-sm font-bold">{flash.success}</div>
                )}
                {flash.error && <div className="bg-destructive/10 text-destructive rounded-xl px-4 py-2.5 text-sm font-bold">{flash.error}</div>}
                {children}
            </main>
        </div>
    );
}
