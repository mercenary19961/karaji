<?php

/*
 | Prewritten, bilingual seasonal announcements the admin can pick from when
 | publishing (they pre-fill the form and stay editable). Arabic is the copy
 | shops see by default; English shows when a shop switches its UI to English.
 | Add a season here and it appears in the picker — no code change needed.
 */
return [
    [
        'key' => 'winter_check',
        'label' => 'Winter check (Nov–Dec)',
        'title' => ['ar' => 'فحص الشتاء ❄️🚗', 'en' => 'Winter check ❄️🚗'],
        'body' => [
            'ar' => 'ذكّر زبائنك يفحصوا البطارية والإطارات وماي الرديتر قبل الشتا · عندنا عرض خاص هالشهر.',
            'en' => 'Remind your customers to check the battery, tires and coolant before winter · special offer this month.',
        ],
    ],
    [
        'key' => 'summer_ac',
        'label' => 'Summer A/C (Apr–May)',
        'title' => ['ar' => 'جاهز للصيف؟ ☀️❄️', 'en' => 'Ready for summer? ☀️❄️'],
        'body' => [
            'ar' => 'قرب الصيف · افحص المكيف وماي الرديتر قبل الحر · احجز دورك من هلأ.',
            'en' => 'Summer is near · check the A/C and coolant before the heat · book your slot now.',
        ],
    ],
    [
        'key' => 'ramadan',
        'label' => 'Ramadan greeting',
        'title' => ['ar' => 'رمضان كريم 🌙', 'en' => 'Ramadan Kareem 🌙'],
        'body' => [
            'ar' => 'كل عام وانتو بخير · مواعيد الكراج بتتغير برمضان، تواصل معنا لأي خدمة.',
            'en' => 'Ramadan Kareem to you and your family · our hours change during Ramadan, reach out for any service.',
        ],
    ],
    [
        'key' => 'eid',
        'label' => 'Eid greeting',
        'title' => ['ar' => 'عيدكم مبارك 🎉', 'en' => 'Eid Mubarak 🎉'],
        'body' => [
            'ar' => 'كل عام وانتو بخير · قبل ما تسافر للعيد، مرّ علينا لفحص سريع ع السيارة.',
            'en' => 'Eid Mubarak · before you travel for the holiday, drop by for a quick car check.',
        ],
    ],
    [
        'key' => 'license_season',
        'label' => 'License renewal reminder',
        'title' => ['ar' => 'موسم تجديد الترخيص 📋', 'en' => 'License renewal season 📋'],
        'body' => [
            'ar' => 'ذكّر زبائنك بموعد الترخيص والفحص · جهّزنا سيارتك للفحص قبل ما تروح.',
            'en' => 'Remind your customers about license renewal and inspection · we prep the car before you go.',
        ],
    ],
];
