import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Head, useForm, usePage, usePoll } from '@inertiajs/react';
import { Mail, Send } from 'lucide-react';
import { type FormEvent } from 'react';

interface ShopMessage {
    id: number;
    title: string;
    body: string;
    date: string;
    unread: boolean;
}

interface ShopSuggestion {
    id: number;
    body: string;
    status: 'open' | 'reviewed';
    date: string;
}

interface Props {
    shop: Shop;
    messages: ShopMessage[];
    suggestions: ShopSuggestion[];
}

export default function Messages({ shop, messages, suggestions }: Props) {
    const { flash } = usePage<SharedData>().props;
    const t = useT();

    // New messages from the admin arrive without a refresh (the composer's typed
    // text is preserved — polling merges props without remounting the component).
    usePoll(15000, { only: ['messages', 'suggestions'] });

    const form = useForm({ body: '' });

    const send = (e: FormEvent) => {
        e.preventDefault();
        form.post(route('shop.suggestions.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('msg.title')} />

            <h1 className="text-xl font-extrabold">{t('msg.title')}</h1>

            <div className="grid gap-4 md:grid-cols-2 md:items-start md:gap-6">
                {/* Inbox from the admin */}
                <div className="flex flex-col gap-2.5">
                    <h2 className="text-lg font-extrabold">{t('msg.inbox')}</h2>
                    {messages.map((message) => (
                        <div
                            key={message.id}
                            className={`rounded-2xl p-4 shadow-sm ${message.unread ? 'border-primary bg-card border-2' : 'bg-card'}`}
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div className="text-[17px] font-extrabold">{message.title}</div>
                                <div className="text-muted-foreground shrink-0 text-[13px]">{message.date}</div>
                            </div>
                            <div className="text-muted-foreground mt-1 text-[15px] whitespace-pre-line">{message.body}</div>
                        </div>
                    ))}
                    {messages.length === 0 && (
                        <div className="bg-card text-muted-foreground flex flex-col items-center gap-2 rounded-2xl p-6 text-center text-base">
                            <Mail className="size-7" aria-hidden />
                            {t('msg.none')}
                        </div>
                    )}
                </div>

                {/* Suggestions to the admin */}
                <div className="flex flex-col gap-2.5">
                    <h2 className="text-lg font-extrabold">{t('msg.suggest_title')}</h2>

                    {flash.success && (
                        <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-[15px] font-bold">
                            {flash.success}
                        </div>
                    )}

                    <form onSubmit={send} className="bg-card flex flex-col gap-3 rounded-2xl p-4 shadow-sm">
                        <div className="text-muted-foreground text-[15px]">{t('msg.suggest_hint')}</div>
                        <textarea
                            value={form.data.body}
                            onChange={(e) => form.setData('body', e.target.value)}
                            placeholder={t('msg.suggest_placeholder')}
                            rows={3}
                            className="border-input bg-card focus-visible:border-ring w-full rounded-xl border-2 px-4 py-3 text-[16px] outline-none"
                        />
                        {form.errors.body && <div className="text-destructive text-[15px] font-bold">{form.errors.body}</div>}
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="bg-primary text-primary-foreground flex h-12 items-center justify-center gap-2 rounded-xl text-[16px] font-extrabold disabled:opacity-60"
                        >
                            <Send className="size-5" aria-hidden />
                            {t('msg.suggest_send')}
                        </button>
                    </form>

                    <h3 className="mt-1 text-[15px] font-extrabold">{t('msg.your_suggestions')}</h3>
                    {suggestions.map((suggestion) => (
                        <div key={suggestion.id} className="bg-card flex items-start justify-between gap-2 rounded-2xl p-3.5 shadow-sm">
                            <div>
                                <div className="text-[15px]">{suggestion.body}</div>
                                <div className="text-muted-foreground mt-1 text-[13px]">{suggestion.date}</div>
                            </div>
                            <span
                                className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-bold whitespace-nowrap ${
                                    suggestion.status === 'reviewed' ? 'bg-success-soft text-success-soft-foreground' : 'bg-due text-due-foreground'
                                }`}
                            >
                                {suggestion.status === 'reviewed' ? t('msg.status_reviewed') : t('msg.status_open')}
                            </span>
                        </div>
                    ))}
                    {suggestions.length === 0 && <div className="text-muted-foreground px-1 text-[15px]">{t('msg.no_suggestions')}</div>}
                </div>
            </div>
        </ShopLayout>
    );
}
