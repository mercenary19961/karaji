import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

/**
 * Shop-portal UI translations (Arabic = Ammani dialect, English). Co-located
 * per key so the two languages stay in sync. NOTE: customer-facing WhatsApp
 * message templates deliberately stay Arabic in the components (they go to
 * Arabic-speaking customers) and are NOT translated here. DB-entered data
 * (car labels, customer/service names) also stays in its entered language.
 */
const dict = {
    // Navigation + layout
    'nav.home': { ar: 'الرئيسية', en: 'Home' },
    'nav.reminders': { ar: 'التذكيرات', en: 'Reminders' },
    'nav.reports': { ar: 'التقارير', en: 'Reports' },
    'nav.messages': { ar: 'الرسائل', en: 'Messages' },
    'nav.account': { ar: 'حسابي', en: 'My account' },
    'nav.logout': { ar: 'تسجيل الخروج', en: 'Log out' },

    // Messages + suggestions
    'msg.title': { ar: 'الرسائل والتنبيهات', en: 'Messages & notifications' },
    'msg.inbox': { ar: 'رسائل الإدارة', en: 'From the team' },
    'msg.none': { ar: 'ما في رسائل', en: 'No messages yet' },
    'msg.suggest_title': { ar: 'عندك اقتراح؟', en: 'Have a suggestion?' },
    'msg.suggest_hint': { ar: 'عندك فكرة تحسّن البرنامج؟ ابعتلنا', en: 'An idea to improve the app? Send it our way' },
    'msg.suggest_placeholder': { ar: 'مثلاً: ضيفوا خدمة تغيير زيت الفرامل', en: 'e.g. add a brake-fluid change service' },
    'msg.suggest_send': { ar: 'ابعت الاقتراح', en: 'Send suggestion' },
    'msg.your_suggestions': { ar: 'اقتراحاتك', en: 'Your suggestions' },
    'msg.no_suggestions': { ar: 'ما بعثت أي اقتراح لسا', en: 'No suggestions sent yet' },
    'msg.status_open': { ar: 'قيد المراجعة', en: 'Under review' },
    'msg.status_reviewed': { ar: 'تمت المراجعة', en: 'Reviewed' },

    // Common
    'common.call': { ar: 'اتصال', en: 'Call' },
    'common.whatsapp': { ar: 'واتساب', en: 'WhatsApp' },
    'common.currency': { ar: 'د.أ', en: 'JOD' },

    // Dashboard
    'dash.greeting': { ar: 'أهلين 👋', en: 'Welcome 👋' },
    'dash.subtitle': { ar: 'عندك {count} زبائن لازم تحكي معهم اليوم', en: 'You have {count} customers to reach out to today' },
    'dash.search': { ar: 'رقم اللوحة أو التلفون', en: 'Plate or phone number' },
    'dash.new_visit': { ar: 'زيارة جديدة', en: 'New visit' },
    'dash.today_cars': { ar: 'سيارات اليوم', en: "Today's cars" },
    'dash.due_count': { ar: 'مستحق التذكير', en: 'Due for reminder' },
    'dash.month_income': { ar: 'دخل الشهر', en: "Month's income" },
    'dash.due_today': { ar: 'مستحق التواصل اليوم', en: 'To contact today' },
    'dash.view_all': { ar: 'شوف الكل ←', en: 'View all →' },
    'dash.no_due': { ar: 'ما في حدا لازم تحكي معه اليوم 🎉', en: 'No one to contact today 🎉' },
    'dash.losing': { ar: 'زبائن ما رجعوا', en: 'Slipping away' },
    'dash.losing_window': { ar: '(+6 أشهر)', en: '(6+ months)' },
    'dash.no_losing': { ar: 'ما في زبائن منقطعين 👌', en: 'No lapsed customers 👌' },

    // Reminders
    'rem.title': { ar: 'قائمة التذكيرات', en: 'Reminders' },
    'rem.subtitle': { ar: 'مرتّبة من الأكثر تأخير', en: 'Most overdue first' },
    'rem.contacted': { ar: 'حكيت معه', en: 'Contacted' },
    'rem.contacted_undo': { ar: 'حكيت معه ✓ · تراجع', en: 'Contacted ✓ · undo' },
    'rem.none': { ar: 'ما في تذكيرات لليوم 🎉', en: 'No reminders for today 🎉' },

    // Car profile
    'car.next_oil': { ar: 'الزيت الجاي', en: 'Next oil change' },
    'car.at_km': { ar: 'عند {km} كم', en: 'at {km} km' },
    'car.or': { ar: 'أو', en: 'or' },
    'car.license_month': { ar: 'شهر الترخيص: {month}', en: 'License month: {month}' },
    'car.new_visit': { ar: 'زيارة جديدة لهالسيارة', en: 'New visit for this car' },
    'car.visits_log': { ar: 'سجل الزيارات', en: 'Visit history' },
    'car.no_visits': { ar: 'لسا ما في زيارات لهالسيارة', en: 'No visits yet for this car' },
    'car.odometer': { ar: 'العداد: {km} كم', en: 'Odometer: {km} km' },

    // New visit
    'visit.title': { ar: 'زيارة جديدة', en: 'New visit' },
    'visit.search': { ar: 'دوّر ع السيارة برقم اللوحة أو التلفون', en: 'Find the car by plate or phone' },
    'visit.unregistered': { ar: 'سيارة مش مسجلة؟', en: 'Car not registered?' },
    'visit.back_registered': { ar: 'ارجع لسيارة مسجلة', en: 'Back to a registered car' },
    'visit.new_customer': { ar: 'زبون جديد', en: 'New customer' },
    'visit.name': { ar: 'الاسم', en: 'Name' },
    'visit.phone': { ar: 'رقم التلفون', en: 'Phone number' },
    'visit.plate': { ar: 'رقم اللوحة', en: 'Plate number' },
    'visit.car_optional': { ar: 'السيارة (مثلاً كيا سبورتاج 2019) · اختياري', en: 'Car (e.g. Kia Sportage 2019) · optional' },
    'visit.km_label': { ar: 'قراءة العداد الحالية', en: 'Current odometer reading' },
    'visit.km_placeholder': { ar: 'مثلاً 91300', en: 'e.g. 91300' },
    'visit.services': { ar: 'الخدمات', en: 'Services' },
    'visit.oil_type': { ar: 'نوع الزيت', en: 'Oil type' },
    'visit.oil_brand': { ar: 'ماركة الزيت', en: 'Oil brand' },
    'visit.same_last': { ar: 'زي آخر زيارة · {brand}', en: 'Same as last · {brand}' },
    'visit.price': { ar: 'السعر (اختياري)', en: 'Price (optional)' },
    'visit.saved_title': { ar: 'انحفظت الزيارة ✓', en: 'Visit saved ✓' },
    'visit.saved_meter': { ar: 'عداد {km} كم', en: 'odometer {km} km' },
    'visit.send_summary': { ar: 'ابعت ملخص الزيارة واتساب', en: 'Send summary on WhatsApp' },
    'visit.preview': { ar: 'شوف الرسالة', en: 'Message preview' },
    'visit.back_home': { ar: 'ارجع للرئيسية', en: 'Back to home' },
    'visit.undo': { ar: 'تراجع عن الحفظ', en: 'Undo save' },
    'visit.save': { ar: 'حفظ الزيارة', en: 'Save visit' },

    // Analytics
    'stats.title': { ar: 'التقارير', en: 'Reports' },
    'stats.monthly': { ar: 'الزيارات شهرياً', en: 'Visits per month' },
    'stats.top_services': { ar: 'أكثر الخدمات', en: 'Top services' },
    'stats.no_services': { ar: 'لسا ما في خدمات مسجلة', en: 'No services recorded yet' },
    'stats.losing': { ar: 'زبائن ما رجعوا', en: 'Customers slipping away' },
    'stats.losing_window': { ar: '(أكثر من 6 أشهر)', en: '(more than 6 months)' },
    'stats.no_losing': { ar: 'ما في زبائن منقطعين · ممتاز 👌', en: 'No lapsed customers · great 👌' },
    'stats.prev_month': { ar: 'الشهر السابق', en: 'Previous month' },
    'stats.next_month': { ar: 'الشهر التالي', en: 'Next month' },
    'stats.chart_aria': { ar: 'عدد الزيارات شهرياً', en: 'Visits per month' },

    // Account
    'acct.title': { ar: 'حسابي', en: 'My account' },
    'acct.change_picture': { ar: 'غيّر الصورة', en: 'Change picture' },
    'acct.remove_picture': { ar: 'شيل الصورة', en: 'Remove picture' },
    'acct.language': { ar: 'اللغة', en: 'Language' },
    'acct.change_password': { ar: 'تغيير كلمة المرور', en: 'Change password' },
    'acct.current_pw': { ar: 'كلمة المرور الحالية', en: 'Current password' },
    'acct.new_pw': { ar: 'كلمة المرور الجديدة', en: 'New password' },
    'acct.confirm_pw': { ar: 'تأكيد كلمة المرور الجديدة', en: 'Confirm new password' },
    'acct.save_password': { ar: 'حفظ كلمة المرور', en: 'Save password' },
} as const;

export type TKey = keyof typeof dict;

export function useT() {
    const { locale } = usePage<SharedData>().props;
    const lang: 'ar' | 'en' = locale === 'en' ? 'en' : 'ar';

    return (key: TKey, params?: Record<string, string | number>): string => {
        let text: string = dict[key][lang];

        if (params) {
            for (const [k, v] of Object.entries(params)) {
                text = text.replaceAll(`{${k}}`, String(v));
            }
        }

        return text;
    };
}
