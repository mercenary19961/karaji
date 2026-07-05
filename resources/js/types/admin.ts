// Prop contracts for the admin portal pages, fulfilled by App\Support\AdminDemoData
// until schema v1.

export type SubscriptionStatus = 'Active' | 'Trial' | 'Suspended';

export interface ShopListItem {
    id: string;
    name: string;
    area: string;
    status: SubscriptionStatus;
    visits: number;
    lastActive: string;
}

export interface ActivityEntry {
    id: string;
    text: string;
    at: string;
    undoable: boolean;
}

export interface ShopDetail {
    name: string;
    area: string;
    stats: { label: string; value: number }[];
    subscription: {
        status: SubscriptionStatus;
        plan: string;
        plans: string[];
        renewsAt: string;
    };
    activity: ActivityEntry[];
}
