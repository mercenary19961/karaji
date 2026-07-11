// Prop contracts for the admin portal pages, fulfilled by the Admin\* controllers.

export type SubscriptionStatus = 'active' | 'trial' | 'suspended';

export interface ShopListItem {
    id: number;
    name: string;
    area: string | null;
    status: SubscriptionStatus | null;
    visits: number;
    lastActive: string;
}

export interface PlanOption {
    key: string;
    label: string;
}

export interface ActivityEntry {
    id: number;
    text: string;
    at: string;
    undoable: boolean;
    undone: boolean;
    /** Entry produced by reverting another entry (reverting it again = redo). */
    isRevert: boolean;
}

export interface AdminMessage {
    id: number;
    title: string;
    body: string;
    at: string;
    read: boolean;
}

export interface AdminSuggestion {
    id: number;
    shop: string;
    body: string;
    status: 'open' | 'reviewed';
    date: string;
}

export interface ShopDetail {
    id: number;
    name: string;
    nameEn: string | null;
    area: string | null;
    areaEn: string | null;
    phone: string | null;
    defaultDailyKm: number;
    stats: { label: string; value: number }[];
    subscription: {
        status: SubscriptionStatus;
        plan: string;
        plans: PlanOption[];
        renewsAt: string | null;
        trialEndsAt: string | null;
    } | null;
    activity: ActivityEntry[];
    messages: AdminMessage[];
}

export interface AnnouncementItem {
    id: number;
    title: string;
    body: string;
    target: string;
    isActive: boolean;
    startsAt: string | null;
    endsAt: string | null;
    createdAt: string;
}

export interface ShopOption {
    id: number;
    name: string;
}

export interface AnnouncementTemplate {
    key: string;
    label: string;
    title: { ar: string; en: string };
    body: { ar: string; en: string };
}
