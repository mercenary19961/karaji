import { type CarSearchItem } from '@/types/shop';

/**
 * Ranks a car against the query. Numbers match plate/phone (a leading match
 * beats a contains match); text matches owner/label. Returns 0 for no match.
 * Shared by the new-visit entry typeahead and the clients directory.
 */
export function scoreCar(car: CarSearchItem, query: string, digits: string): number {
    const plate = car.plate.toLowerCase();
    const plateDigits = plate.replace(/\D/g, '');
    const phone = car.phone.replace(/\D/g, '');
    const owner = car.owner.toLowerCase();
    const label = car.label.toLowerCase();

    let score = 0;
    if (digits) {
        if (plateDigits.startsWith(digits)) score = Math.max(score, 100);
        else if (plateDigits.includes(digits)) score = Math.max(score, 72);
        if (phone.startsWith(digits)) score = Math.max(score, 92);
        else if (phone.includes(digits)) score = Math.max(score, 62);
    }
    if (owner.includes(query)) score = Math.max(score, owner.startsWith(query) ? 88 : 56);
    if (label.includes(query)) score = Math.max(score, label.startsWith(query) ? 82 : 52);
    if (plate.includes(query)) score = Math.max(score, 66); // plate typed with its dash
    return score;
}

/**
 * Filter + rank a car list by a raw query. Empty query returns [] (the caller
 * decides what to show instead — recents, or the whole directory).
 */
export function searchCars(cars: CarSearchItem[], rawQuery: string, limit?: number): CarSearchItem[] {
    const query = rawQuery.trim().toLowerCase();
    const digits = query.replace(/\D/g, '');
    if (!query) return [];

    const ranked = cars
        .map((car) => ({ car, score: scoreCar(car, query, digits) }))
        .filter((result) => result.score > 0)
        .sort((a, b) => b.score - a.score)
        .map((result) => result.car);

    return limit ? ranked.slice(0, limit) : ranked;
}
