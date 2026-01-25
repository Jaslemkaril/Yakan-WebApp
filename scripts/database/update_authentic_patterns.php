<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\YakanPattern;

// Clean and refined Yakan patterns with traditional weaving motifs
$authenticPatterns = [
    'Sinaluan' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="sinaluan" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                <rect width="50" height="50" fill="#7D1935"/>
                <polygon points="25,10 40,25 25,40 10,25" fill="#E8C547" stroke="#FFFFFF" stroke-width="1"/>
                <polygon points="25,17 33,25 25,33 17,25" fill="#2E1A47"/>
                <rect x="23" y="23" width="4" height="4" fill="#FFFFFF"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#sinaluan)"/>
    </svg>',
    
    'Bunga Sama' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="bunga" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                <rect width="40" height="40" fill="#C93756"/>
                <circle cx="20" cy="20" r="10" fill="#E8C547"/>
                <circle cx="20" cy="20" r="5" fill="#FFFFFF"/>
                <circle cx="10" cy="10" r="3" fill="#E8C547" opacity="0.7"/>
                <circle cx="30" cy="10" r="3" fill="#E8C547" opacity="0.7"/>
                <circle cx="10" cy="30" r="3" fill="#E8C547" opacity="0.7"/>
                <circle cx="30" cy="30" r="3" fill="#E8C547" opacity="0.7"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#bunga)"/>
    </svg>',
    
    'Pinalantikan' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="pinalantikan" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                <rect width="50" height="50" fill="#2E1A47"/>
                <polygon points="25,8 38,25 25,42 12,25" fill="#E8C547"/>
                <polygon points="25,14 32,25 25,36 18,25" fill="#7D1935"/>
                <polygon points="25,20 26,25 25,30 24,25" fill="#FFFFFF"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#pinalantikan)"/>
    </svg>',
    
    'Suhul' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="suhul" x="0" y="0" width="60" height="25" patternUnits="userSpaceOnUse">
                <rect width="60" height="25" fill="#2F5F7F"/>
                <path d="M0,12 Q15,5 30,12 T60,12" stroke="#67C4E8" stroke-width="2.5" fill="none"/>
                <path d="M0,18 Q15,11 30,18 T60,18" stroke="#94D5EC" stroke-width="1.5" fill="none" opacity="0.8"/>
                <path d="M0,6 Q15,-1 30,6 T60,6" stroke="#1E4D6B" stroke-width="2" fill="none"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#suhul)"/>
    </svg>',
    
    'Kabkaban' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="kabkaban" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse">
                <rect width="12.5" height="12.5" fill="#6B4423"/>
                <rect x="12.5" y="12.5" width="12.5" height="12.5" fill="#6B4423"/>
                <rect x="12.5" y="0" width="12.5" height="12.5" fill="#A67C52"/>
                <rect x="0" y="12.5" width="12.5" height="12.5" fill="#A67C52"/>
                <rect x="4" y="4" width="5" height="5" fill="#D4A76A"/>
                <rect x="16.5" y="16.5" width="5" height="5" fill="#D4A76A"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#kabkaban)"/>
    </svg>',
    
    'Laggi' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="laggi" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                <rect width="60" height="60" fill="#7D1935"/>
                <polygon points="30,10 40,20 45,30 40,40 30,50 20,40 15,30 20,20" fill="#E8C547" stroke="#FFFFFF" stroke-width="1.5"/>
                <polygon points="30,18 36,25 38,30 36,35 30,42 24,35 22,30 24,25" fill="#D4A034"/>
                <circle cx="30" cy="30" r="4" fill="#FFFFFF"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#laggi)"/>
    </svg>',
    
    'Bennig' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="bennig" x="0" y="0" width="35" height="35" patternUnits="userSpaceOnUse">
                <rect width="35" height="35" fill="#C93756"/>
                <line x1="0" y1="0" x2="35" y2="35" stroke="#2E1A47" stroke-width="2.5"/>
                <line x1="35" y1="0" x2="0" y2="35" stroke="#2E1A47" stroke-width="2.5"/>
                <line x1="17.5" y1="0" x2="17.5" y2="35" stroke="#E8C547" stroke-width="2"/>
                <line x1="0" y1="17.5" x2="35" y2="17.5" stroke="#E8C547" stroke-width="2"/>
                <circle cx="17.5" cy="17.5" r="5" fill="#FFFFFF"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#bennig)"/>
    </svg>',
    
    'Pangapun' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="pangapun" x="0" y="0" width="30" height="60" patternUnits="userSpaceOnUse">
                <rect width="30" height="60" fill="#1B4D3E"/>
                <rect x="7" y="8" width="16" height="12" fill="#6B4423"/>
                <rect x="7" y="24" width="16" height="12" fill="#A67C52"/>
                <rect x="7" y="40" width="16" height="12" fill="#6B4423"/>
                <line x1="15" y1="0" x2="15" y2="60" stroke="#E8C547" stroke-width="1.5"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#pangapun)"/>
    </svg>',
    
    'Sarang Kayu' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="sarang" x="0" y="0" width="35" height="35" patternUnits="userSpaceOnUse">
                <rect width="35" height="35" fill="#4A3728"/>
                <polygon points="0,12 12,0 23,12 12,23" fill="#A67C52"/>
                <polygon points="12,23 23,12 35,23 23,35" fill="#6B4423"/>
                <polygon points="23,0 35,12 23,23 12,12" fill="#8B6F47"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#sarang)"/>
    </svg>',
    
    'Ikan Mas' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="ikan" x="0" y="0" width="50" height="35" patternUnits="userSpaceOnUse">
                <rect width="50" height="35" fill="#2F5F7F"/>
                <ellipse cx="25" cy="17.5" rx="14" ry="8" fill="#E89547"/>
                <polygon points="11,17.5 7,14 7,21" fill="#E8C547"/>
                <circle cx="32" cy="15" r="2" fill="#1A1A1A"/>
                <path d="M25,10 Q30,10 33,13 Q36,16 33,19 Q30,22 25,22" stroke="#D4A034" stroke-width="1.2" fill="none"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#ikan)"/>
    </svg>',
    
    'Kalasag' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="kalasag" x="0" y="0" width="40" height="55" patternUnits="userSpaceOnUse">
                <rect width="40" height="55" fill="#7D1935"/>
                <path d="M20,8 Q28,18 20,28 Q12,18 20,8 Z" fill="#E8C547" stroke="#FFFFFF" stroke-width="1.2"/>
                <path d="M20,28 Q28,38 20,47 Q12,38 20,28 Z" fill="#2E1A47" stroke="#E8C547" stroke-width="1.2"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#kalasag)"/>
    </svg>',
    
    'Tali' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="tali" x="0" y="0" width="25" height="50" patternUnits="userSpaceOnUse">
                <rect width="25" height="50" fill="#6B4423"/>
                <path d="M8,0 Q12,12 8,25 Q4,37 8,50" stroke="#A67C52" stroke-width="3.5" fill="none"/>
                <path d="M17,0 Q13,12 17,25 Q21,37 17,50" stroke="#A67C52" stroke-width="3.5" fill="none"/>
                <line x1="8" y1="12" x2="17" y2="12" stroke="#E8C547" stroke-width="1.5"/>
                <line x1="8" y1="37" x2="17" y2="37" stroke="#E8C547" stroke-width="1.5"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#tali)"/>
    </svg>',
    
    'Langgal' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="langgal" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                <rect width="50" height="50" fill="#2E1A47"/>
                <polygon points="25,8 40,25 25,42 10,25" fill="#E8C547"/>
                <polygon points="25,13 35,25 25,37 15,25" fill="#D4A034"/>
                <polygon points="25,18 30,25 25,32 20,25" fill="#C93756"/>
                <rect x="23" y="23" width="4" height="4" fill="#FFFFFF"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#langgal)"/>
    </svg>',
    
    'Saput Tangan' => '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="saput" x="0" y="0" width="35" height="35" patternUnits="userSpaceOnUse">
                <rect width="35" height="35" fill="#7D1935"/>
                <rect x="6" y="6" width="6" height="6" fill="#E8C547"/>
                <rect x="23" y="6" width="6" height="6" fill="#E8C547"/>
                <rect x="6" y="23" width="6" height="6" fill="#E8C547"/>
                <rect x="23" y="23" width="6" height="6" fill="#E8C547"/>
                <rect x="14.5" y="14.5" width="6" height="6" fill="#2E1A47"/>
            </pattern>
        </defs>
        <rect width="200" height="200" fill="url(#saput)"/>
    </svg>',
];

$updated = 0;
foreach ($authenticPatterns as $name => $svg) {
    $pattern = YakanPattern::where('name', $name)->first();
    if ($pattern) {
        $pattern->pattern_data = $svg;
        $pattern->save();
        echo "✓ Updated: $name\n";
        $updated++;
    } else {
        echo "✗ Not found: $name\n";
    }
}

echo "\n✓ Successfully updated $updated patterns with authentic Yakan designs!\n";
