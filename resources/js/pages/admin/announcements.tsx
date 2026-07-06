import AdminLayout from '@/layouts/admin-layout';
import { type AnnouncementItem, type ShopOption } from '@/types/admin';
import { Head, router, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

interface Props {
    announcements: AnnouncementItem[];
    shops: ShopOption[];
}

export default function Announcements({ announcements, shops }: Props) {
    const form = useForm({
        title: '',
        body: '',
        shop_id: '' as string,
        starts_at: '',
        ends_at: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({ ...data, shop_id: data.shop_id === '' ? null : Number(data.shop_id) }));
        form.post(route('admin.announcements.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <AdminLayout>
            <Head title="Announcements" />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_1.3fr]">
                <form onSubmit={submit} className="border-border bg-card flex h-fit flex-col gap-3 rounded-2xl border p-5">
                    <h1 className="text-lg font-extrabold">New announcement</h1>

                    <div>
                        <input
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                            placeholder="Title"
                            className="border-input bg-card focus-visible:border-ring h-11 w-full rounded-lg border px-3 text-[15px] outline-none"
                        />
                        {form.errors.title && <div className="text-destructive mt-1 text-sm font-bold">{form.errors.title}</div>}
                    </div>

                    <div>
                        <textarea
                            value={form.data.body}
                            onChange={(e) => form.setData('body', e.target.value)}
                            placeholder="Message shown on the shop dashboard"
                            rows={3}
                            className="border-input bg-card focus-visible:border-ring w-full rounded-lg border px-3 py-2 text-[15px] outline-none"
                        />
                        {form.errors.body && <div className="text-destructive mt-1 text-sm font-bold">{form.errors.body}</div>}
                    </div>

                    <label className="text-muted-foreground flex flex-col gap-1 text-sm">
                        Audience
                        <select
                            value={form.data.shop_id}
                            onChange={(e) => form.setData('shop_id', e.target.value)}
                            className="border-input bg-card text-foreground focus-visible:border-ring h-11 rounded-lg border px-2.5 text-[15px] outline-none"
                        >
                            <option value="">All shops (broadcast)</option>
                            {shops.map((shop) => (
                                <option key={shop.id} value={shop.id}>
                                    {shop.name}
                                </option>
                            ))}
                        </select>
                    </label>

                    <div className="flex gap-3">
                        <label className="text-muted-foreground flex flex-1 flex-col gap-1 text-sm">
                            Starts (optional)
                            <input
                                type="date"
                                value={form.data.starts_at}
                                onChange={(e) => form.setData('starts_at', e.target.value)}
                                className="border-input bg-card focus-visible:border-ring h-11 rounded-lg border px-2.5 text-[15px] outline-none"
                            />
                        </label>
                        <label className="text-muted-foreground flex flex-1 flex-col gap-1 text-sm">
                            Ends (optional)
                            <input
                                type="date"
                                value={form.data.ends_at}
                                onChange={(e) => form.setData('ends_at', e.target.value)}
                                className="border-input bg-card focus-visible:border-ring h-11 rounded-lg border px-2.5 text-[15px] outline-none"
                            />
                        </label>
                    </div>
                    {form.errors.ends_at && <div className="text-destructive text-sm font-bold">{form.errors.ends_at}</div>}

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="bg-primary text-primary-foreground mt-1 h-11 cursor-pointer rounded-lg text-[15px] font-bold disabled:opacity-60"
                    >
                        Publish
                    </button>
                </form>

                <div className="flex flex-col gap-3">
                    <h2 className="text-lg font-extrabold">
                        Published <span className="text-muted-foreground text-[15px] font-medium">({announcements.length})</span>
                    </h2>

                    {announcements.map((a) => (
                        <div key={a.id} className={`border-border bg-card rounded-2xl border p-4 ${a.isActive ? '' : 'opacity-60'}`}>
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="font-extrabold">{a.title}</div>
                                    <div className="text-muted-foreground mt-0.5 text-sm">{a.body}</div>
                                </div>
                                <span
                                    className={`rounded-full px-2.5 py-1 text-xs font-extrabold whitespace-nowrap ${
                                        a.isActive ? 'bg-success-soft text-success-soft-foreground' : 'bg-muted text-muted-foreground'
                                    }`}
                                >
                                    {a.isActive ? 'Active' : 'Paused'}
                                </span>
                            </div>
                            <div className="text-muted-foreground mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span>
                                    <b className="text-foreground">{a.target}</b>
                                </span>
                                {(a.startsAt || a.endsAt) && (
                                    <span>
                                        {a.startsAt ?? '…'} → {a.endsAt ?? '…'}
                                    </span>
                                )}
                                <span>Created {a.createdAt}</span>
                                <span className="ms-auto flex gap-3">
                                    <button
                                        type="button"
                                        onClick={() => router.post(route('admin.announcements.toggle', a.id), {}, { preserveScroll: true })}
                                        className="text-primary cursor-pointer font-bold"
                                    >
                                        {a.isActive ? 'Pause' : 'Activate'}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => router.delete(route('admin.announcements.destroy', a.id), { preserveScroll: true })}
                                        className="text-destructive cursor-pointer font-bold"
                                    >
                                        Delete
                                    </button>
                                </span>
                            </div>
                        </div>
                    ))}

                    {announcements.length === 0 && (
                        <div className="border-border bg-card text-muted-foreground rounded-2xl border p-8 text-center">No announcements yet.</div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
