<?php
function slugify(string $string): string {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower($string));
}

$products = [
    'Configure your SIP DDI\'s User Requirements' => [
        ['name' => 'HV Select SIP DDI User', 'price' => '£-  ', 'type' => 'Monthly Recurring'],
        ['name' => 'HV Select SIP DDI Functional User', 'price' => '£-  ', 'type' => 'Monthly Recurring'],
        ['name' => 'HV Select SIP DDI Fixed User', 'price' => '£1.52', 'type' => 'Monthly Recurring'],
        ['name' => 'HV Select SIP DDI Mobile User', 'price' => '£1.83', 'type' => 'Monthly Recurring'],
    ],
    'enhanced feature sets' => [
        ['name' => 'Receptionist Enterprise (Site Specific)', 'price' => '£20.90', 'type' => 'Monthly Recurring'],
        ['name' => 'Fax to Email ', 'price' => '£2.72', 'type' => 'Monthly Recurring'],
        ['name' => 'Hosted Voicemail (Foundation & SIP Users Only)', 'price' => '£0.99', 'type' => 'Monthly Recurring'],
        ['name' => 'Go Integrator Lite (SIP Users Only)', 'price' => '£1.31', 'type' => 'Monthly Recurring'],
        ['name' => 'Go Integrator DB ', 'price' => '£2.87', 'type' => 'Monthly Recurring'],
        ['name' => 'Go Tapi', 'price' => '£1.88', 'type' => 'Monthly Recurring'],
        ['name' => 'UC Team', 'price' => '£2.19', 'type' => 'Monthly Recurring'],
        ['name' => 'UC Office Desktop', 'price' => '£2.19', 'type' => 'Monthly Recurring'],
        ['name' => 'Softphone with Cisco Webex  for a Hosted Mobile User Licence', 'type' => 'Monthly Recurring'],
        ['name' => 'Basic collaboration with Cisco Webex for a Hosted  Mobile User Licence', 'price' => '£-  ', 'type' => 'Monthly Recurring'],
        ['name' => 'Standard collaboration with Cisco Webex for a Hosted Mobile User Licence', 'price' => '£4.10', 'type' => 'Monthly Recurring'],
    ],
    'headsets and accessories' => [
        ['name' => 'Jabra PRO 920 - Mono for Polycom', 'price' => '£150.00', 'type' => 'One-off'],
        ['name' => 'Jabra PRO 920 - Duo for Polycom', 'price' => '£165.00', 'type' => 'One-off'],
        ['name' => 'Jabra PRO 920 - Mono for Yealink', 'price' => '£150.00', 'type' => 'One-off'],
        ['name' => 'Jabra PRO 920 - Duo for Yealink', 'price' => '£165.00', 'type' => 'One-off'],
        ['name' => 'Jabra Pro 930 Mono', 'price' => '£131.24', 'type' => 'One-off'],
        ['name' => 'Jabra Pro 930 Duo', 'price' => '£150.63', 'type' => 'One-off'],
        ['name' => 'Polycom IP 5000/7000 PSU', 'price' => '£57.00', 'type' => 'One-off'],
        ['name' => 'Polycom PSU for CCX500 CCX600 CCX700', 'price' => '£25.77', 'type' => 'One-off'],
        ['name' => 'Polycom Calisto 5300', 'price' => '£97.44', 'type' => 'One-off'],
        ['name' => 'End User Training Day (up to 15 users)', 'price' => '£411.73', 'type' => 'One-off'],
        ['name' => 'IP Webex End User training (1 hour)', 'price' => '£52.25', 'type' => 'One-off'],
        ['name' => 'IP Webex End User training (Half day)', 'price' => '£193.33', 'type' => 'One-off'],
    ],
    'category1' => [
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 1',
            'type' => 'One-off',
            'price' => '£100',
            'description' => 'This is a great product.',
        ],
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 2',
            'type' => 'Monthly recurring',
            'price' => '£100/mo',
            'description' => 'This is a great product.',
        ],
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 3A',
            'type' => 'Usage',
            'price' => '£100 per minute',
            'description' => 'This is a great product.',
        ]
    ],
    'category2' => [
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 1',
            'type' => 'One-off',
            'price' => '£100',
            'description' => 'This is a great product.',
        ],
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 2',
            'type' => 'Monthly recurring',
            'price' => '£100/mo',
            'description' => 'This is a great product.',
        ],
        [
            'image' => '/mockup/images/product.png',
            'name' => 'Product 3A',
            'type' => 'Usage',
            'price' => '£100 per minute',
            'description' => 'This is a great product.',
        ]
    ]
];

