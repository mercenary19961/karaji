// Prop contracts for the shop portal pages, fulfilled by the Shop\* controllers
// (real Eloquent queries since schema v1).

export interface Shop {
    /** Display name in the current UI locale (Arabic or English). */
    name: string;
    area: string | null;
    /** Always the Arabic name — for the customer-facing WhatsApp templates. */
    nameAr: string;
}

export interface ShopStats {
    todayVisits: number;
    dueCount: number;
    monthRevenue: string;
}

export interface DashboardAnnouncement {
    id: number;
    title: string;
    body: string;
}

export interface DueTodayItem {
    car: string;
    owner: string;
    due: string;
    overdueLabel: string;
}

/** A car in the entry-page client-side search index. */
export interface CarSearchItem {
    id: number;
    plate: string;
    label: string;
    owner: string;
    phone: string;
    lastVisit: string | null;
}

export interface ServiceTypeOption {
    id: number;
    /** Stable Arabic key (used for form matching + the Arabic WhatsApp summary). */
    name: string;
    /** Localized chip caption. */
    label: string;
}

/** Car context shown at the top of the new-visit form. */
export interface FormCar {
    id: number;
    label: string;
    plate: string;
    owner: string;
    phone: string;
    lastService: string | null;
    lastOilBrand: string | null;
    lastOilType: string | null;
}

export interface OilTypeOption {
    key: string;
    label: string;
}

/** Everything the post-save success state needs (incl. the WhatsApp summary). */
export interface SavedVisit {
    id: number;
    carId: number;
    carLabel: string;
    /** Arabic label + owner for the customer-facing WhatsApp summary. */
    carLabelAr: string;
    plate: string;
    owner: string;
    ownerAr: string;
    whatsapp: string;
    km: string;
    services: string[];
    oilBrand: string | null;
    nextDueKm: string | null;
    nextDueDate: string | null;
}

export interface CarVisit {
    date: string;
    km: string;
    price: string | null;
    services: string[];
}

export interface CarProfile {
    id: number;
    label: string;
    /** Arabic label + owner for the customer-facing WhatsApp greeting. */
    labelAr: string;
    plate: string;
    owner: string;
    ownerAr: string;
    phone: string;
    whatsapp: string;
    lastService: string | null;
    licenseMonth: string | null;
    nextDue: { km: string | null; date: string | null } | null;
    visits: CarVisit[];
}

export interface Reminder {
    id: number;
    car: string;
    carAr: string;
    owner: string;
    ownerAr: string;
    phone: string;
    whatsapp: string;
    due: string;
    dueAr: string;
    overdueLabel: string;
    contacted: boolean;
}

export interface Analytics {
    months: { label: string; month: number; year: number; visits: number }[];
    topServices: { label: string; count: number }[];
    lostCustomers: { owner: string; ownerAr: string; car: string; carAr: string; lastVisit: string; whatsapp: string }[];
    /** The month the chart window ends at (and the highlighted bar). */
    selected: { year: number; month: number };
    /** Upper bound for the picker — the current month (no future data). */
    max: { year: number; month: number };
    /** Localized month names, index 0 = month 1. */
    monthNames: string[];
}
