// Prop contracts for the shop portal pages. Currently fulfilled by
// App\Support\ShopDemoData; schema v1 swaps the source, not the shapes.

export interface Shop {
    name: string;
    area: string;
}

export interface ShopStats {
    todayVisits: number;
    dueCount: number;
    monthRevenue: string;
}

export interface DueTodayItem {
    car: string;
    owner: string;
    due: string;
    overdueLabel: string;
}

export interface CarVisit {
    date: string;
    km: string;
    price: string;
    services: string[];
}

export interface Car {
    label: string;
    plate: string;
    owner: string;
    phone: string;
    whatsapp: string;
    lastService: string;
    licenseMonth: string;
    nextDue: { km: string; date: string };
    visits: CarVisit[];
}

export interface Reminder {
    id: string;
    car: string;
    owner: string;
    phone: string;
    whatsapp: string;
    due: string;
    overdueLabel: string;
}

export interface Analytics {
    months: { label: string; visits: number }[];
    topServices: { label: string; count: number }[];
    lostCustomers: { owner: string; car: string; lastVisit: string; whatsapp: string }[];
}
