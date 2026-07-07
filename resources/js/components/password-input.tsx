import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import * as React from 'react';

const labels = {
    ar: { show: 'إظهار كلمة المرور', hide: 'إخفاء كلمة المرور' },
    en: { show: 'Show password', hide: 'Hide password' },
};

/**
 * A password field with a show/hide toggle. Drop-in for <Input type="password">.
 * The eye button sits at the logical end (RTL-safe) and is skipped in the tab
 * order so it never interrupts typing → submit.
 */
const PasswordInput = React.forwardRef<HTMLInputElement, React.ComponentProps<'input'>>(({ className, ...props }, ref) => {
    const { locale } = usePage<SharedData>().props;
    const l = locale === 'ar' ? labels.ar : labels.en;
    const [visible, setVisible] = React.useState(false);

    return (
        <div className="relative">
            <Input {...props} ref={ref} type={visible ? 'text' : 'password'} className={cn(className, 'pe-11')} />
            <button
                type="button"
                tabIndex={-1}
                onClick={() => setVisible((v) => !v)}
                aria-label={visible ? l.hide : l.show}
                aria-pressed={visible}
                className="text-muted-foreground hover:text-foreground absolute inset-y-0 end-0 flex items-center px-3"
            >
                {visible ? <EyeOff className="size-5" aria-hidden /> : <Eye className="size-5" aria-hidden />}
            </button>
        </div>
    );
});
PasswordInput.displayName = 'PasswordInput';

export { PasswordInput };
