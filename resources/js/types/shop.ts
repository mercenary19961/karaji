// Prop contracts for the shop portal pages, fulfilled by the Shop\* controllers
// (real Eloquent queries since schema v1).

export interface Shop {
    name: string;
    area: string | null;
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

export interface ServiceTypeOption {
    id: number;
    name: string;
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
    plate: string;
    owner: string;
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
    plate: string;
    owner: string;
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
    owner: string;
    phone: string;
    whatsapp: string;
    due: string;
    overdueLabel: string;
    contacted: boolean;
}

export interface Analytics {
    months: { label: string; year: number; visits: number }[];
    topServices: { label: string; count: number }[];
    lostCustomers: { owner: string; car: string; lastVisit: string; whatsapp: string }[];
}
