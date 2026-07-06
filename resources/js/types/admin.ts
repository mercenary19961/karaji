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

export interface ShopDetail {
    id: number;
    name: string;
    area: string | null;
    stats: { label: string; value: number }[];
    subscription: {
        status: SubscriptionStatus;
        plan: string;
        plans: PlanOption[];
        renewsAt: string | null;
        trialEndsAt: string | null;
    } | null;
    activity: ActivityEntry[];
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
