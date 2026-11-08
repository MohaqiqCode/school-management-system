<?php
/**
 * Status Badge Component
 * 
 * Usage:
 *   echo status_badge('active', 'user');     // فعال
 *   echo status_badge('paid', 'payment');    // پرداخت شده
 *   echo status_badge('overdue', 'fee');     // سررسید گذشته
 */

$statusConfigs = [
    'user' => [
        'active' => ['label'=>'فعال','color'=>'success','icon'=>'fa-check-circle'],
        'inactive' => ['label'=>'غیرفعال','color'=>'danger','icon'=>'fa-times-circle'],
        'banned' => ['label'=>'مسدود','color'=>'dark','icon'=>'fa-ban'],
        'suspended' => ['label'=>'تعلیق','color'=>'warning','icon'=>'fa-pause-circle'],
    ],
    'payment' => [
        'paid' => ['label'=>'پرداخت شده','color'=>'success','icon'=>'fa-check'],
        'pending' => ['label'=>'در انتظار','color'=>'warning','icon'=>'fa-clock'],
        'overdue' => ['label'=>'سررسید گذشته','color'=>'danger','icon'=>'fa-exclamation'],
        'partial' => ['label'=>'پرداخت جزئی','color'=>'info','icon'=>'fa-adjust'],
        'waived' => ['label'=>'بخشیده شده','color'=>'secondary','icon'=>'fa-hand-holding-heart'],
        'cancelled' => ['label'=>'لغو شده','color'=>'dark','icon'=>'fa-ban'],
    ],
    'attendance' => [
        'present' => ['label'=>'حاضر','color'=>'success','icon'=>'fa-check'],
        'absent' => ['label'=>'غایب','color'=>'danger','icon'=>'fa-times'],
        'late' => ['label'=>'تأخیر','color'=>'warning','icon'=>'fa-clock'],
        'half_day' => ['label'=>'نیمه وقت','color'=>'info','icon'=>'fa-adjust'],
        'excused' => ['label'=>'موجه','color'=>'secondary','icon'=>'fa-check-double'],
        'holiday' => ['label'=>'تعطیل','color'=>'dark','icon'=>'fa-calendar-times'],
    ],
    'exam' => [
        'scheduled' => ['label'=>'برنامه‌ریزی شده','color'=>'info','icon'=>'fa-calendar'],
        'ongoing' => ['label'=>'در حال برگزاری','color'=>'warning','icon'=>'fa-spinner'],
        'completed' => ['label'=>'تکمیل شده','color'=>'success','icon'=>'fa-check-circle'],
        'cancelled' => ['label'=>'لغو شده','color'=>'danger','icon'=>'fa-ban'],
    ],
    'inventory' => [
        'ok' => ['label'=>'موجود','color'=>'success','icon'=>'fa-check-circle'],
        'low' => ['label'=>'کمبود','color'=>'warning','icon'=>'fa-exclamation-triangle'],
        'out' => ['label'=>'ناموجود','color'=>'danger','icon'=>'fa-times-circle'],
    ],
    'borrowing' => [
        'borrowed' => ['label'=>'امانت','color'=>'warning','icon'=>'fa-book'],
        'returned' => ['label'=>'برگشتی','color'=>'success','icon'=>'fa-undo'],
        'overdue' => ['label'=>'دیرکرد','color'=>'danger','icon'=>'fa-exclamation-circle'],
        'lost' => ['label'=>'گم شده','color'=>'dark','icon'=>'fa-question-circle'],
    ],
    'document' => [
        'pending' => ['label'=>'در انتظار','color'=>'warning','icon'=>'fa-clock'],
        'verified' => ['label'=>'تأیید شده','color'=>'success','icon'=>'fa-check-circle'],
        'rejected' => ['label'=>'رد شده','color'=>'danger','icon'=>'fa-times-circle'],
    ],
    'expense' => [
        'pending' => ['label'=>'در انتظار','color'=>'warning','icon'=>'fa-clock'],
        'approved' => ['label'=>'تأیید شده','color'=>'info','icon'=>'fa-check'],
        'paid' => ['label'=>'پرداخت شده','color'=>'success','icon'=>'fa-money-bill'],
        'cancelled' => ['label'=>'لغو شده','color'=>'secondary','icon'=>'fa-ban'],
    ],
];

function status_badge($status, $type = 'user', $showIcon = true) {
    global $statusConfigs;
    $config = $statusConfigs[$type][$status] ?? ['label'=>$status,'color'=>'secondary','icon'=>'fa-circle'];
    $icon = $showIcon ? "<i class=\"fas {$config['icon']} me-1\"></i>" : '';
    return "<span class=\"badge bg-{$config['color']}\">{$icon}{$config['label']}</span>";
}

function status_label($status, $type = 'user') {
    global $statusConfigs;
    return $statusConfigs[$type][$status]['label'] ?? $status;
}

function status_color($status, $type = 'user') {
    global $statusConfigs;
    return $statusConfigs[$type][$status]['color'] ?? 'secondary';
}
?>