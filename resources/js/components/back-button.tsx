import { useT } from '@/lib/i18n';
import { ChevronRight } from 'lucide-react';

/**
 * Goes to the previous page in the SPA history. Shown by ShopLayout on every
 * drill-down (non-tab) screen so the owner never has to restart from the nav.
 */
export function BackButton() {
    const t = useT();

    return (
        <button
            type="button"
            onClick={() => window.history.back()}
            className="text-muted-foreground hover:text-foreground -ms-1 flex h-10 w-fit items-center gap-1 text-[15px] font-bold"
        >
            {/* Points "back" — right in RTL, mirrored to left in LTR */}
            <ChevronRight className="size-5 ltr:-scale-x-100" aria-hidden />
            {t('common.back')}
        </button>
    );
}
