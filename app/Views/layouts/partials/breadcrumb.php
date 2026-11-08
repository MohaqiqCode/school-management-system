<?php
/**
 * Breadcrumb Partial
 * مسیر راهنما (Breadcrumb)
 * 
 * Usage:
 * $breadcrumb = [
 *     ['title' => 'داشبورد', 'url' => '/dashboard', 'icon' => 'fas fa-home'],
 *     ['title' => 'دانش‌آموزان', 'url' => '/students', 'icon' => 'fas fa-user-graduate'],
 *     ['title' => 'ثبت نام جدید', 'active' => true],
 * ];
 */
 
$breadcrumb = $breadcrumb ?? [];
$currentPage = $breadcrumb[count($breadcrumb) - 1] ?? null;
?>

<?php if (!empty($breadcrumb)): ?>
<!-- Breadcrumb -->
<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-3 rounded-3 shadow-sm">
                <?php foreach ($breadcrumb as $index => $item): ?>
                    <?php if (isset($item['active']) && $item['active']): ?>
                        <!-- Current Page -->
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php if (isset($item['icon'])): ?>
                                <i class="<?= $item['icon'] ?> me-1"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($item['title']) ?>
                        </li>
                    <?php else: ?>
                        <!-- Link -->
                        <li class="breadcrumb-item">
                            <a href="<?= url($item['url'] ?? '#') ?>" class="text-decoration-none text-secondary">
                                <?php if (isset($item['icon'])): ?>
                                    <i class="<?= $item['icon'] ?> me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- Quick Actions Dropdown -->
                <?php if (isset($breadcrumbActions) && !empty($breadcrumbActions)): ?>
                <li class="breadcrumb-item ms-auto d-none d-md-block">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($breadcrumbActions as $action): ?>
                            <li>
                                <a class="dropdown-item" href="<?= url($action['url'] ?? '#') ?>">
                                    <?php if (isset($action['icon'])): ?>
                                        <i class="<?= $action['icon'] ?> me-2"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($action['title']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Page Title & Description -->
<?php if ($currentPage): ?>
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="mb-1 fw-bold text-gray-800">
            <?php if (isset($currentPage['icon'])): ?>
                <i class="<?= $currentPage['icon'] ?> me-2 text-primary"></i>
            <?php endif; ?>
            <?= htmlspecialchars($currentPage['title']) ?>
        </h4>
        <?php if (isset($currentPage['description'])): ?>
        <p class="text-muted mb-0 small">
            <i class="fas fa-info-circle me-1"></i>
            <?= htmlspecialchars($currentPage['description']) ?>
        </p>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <?php if (isset($currentPage['badge'])): ?>
        <span class="badge bg-<?= $currentPage['badge_color'] ?? 'primary' ?> fs-6">
            <?= htmlspecialchars($currentPage['badge']) ?>
        </span>
        <?php endif; ?>
        
        <?php if (isset($currentPage['actions']) && is_array($currentPage['actions'])): ?>
            <?php foreach ($currentPage['actions'] as $action): ?>
                <a href="<?= url($action['url'] ?? '#') ?>" 
                   class="btn btn-sm btn-<?= $action['color'] ?? 'primary' ?> ms-1"
                   <?php if (isset($action['onclick'])): ?>onclick="<?= $action['onclick'] ?>"<?php endif; ?>
                   <?php if (isset($action['target'])): ?>target="<?= $action['target'] ?>"<?php endif; ?>>
                    <?php if (isset($action['icon'])): ?>
                        <i class="<?= $action['icon'] ?> me-1"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($action['title'] ?? '') ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Default Breadcrumb (based on URL) -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-3 rounded-3 shadow-sm">
                <li class="breadcrumb-item">
                    <a href="<?= url('/dashboard') ?>" class="text-decoration-none text-secondary">
                        <i class="fas fa-home me-1"></i> داشبورد
                    </a>
                </li>
                <?php
                // Auto-generate breadcrumb from URL
                $segments = explode('/', trim($_SERVER['REQUEST_URI'] ?? '', '/'));
                $currentUrl = '';
                $segmentLabels = [
                    'branches' => 'شعب',
                    'users' => 'کاربران',
                    'roles' => 'نقش‌ها',
                    'departments' => 'دپارتمان‌ها',
                    'students' => 'دانش‌آموزان',
                    'parents' => 'والدین',
                    'teachers' => 'معلمان',
                    'employees' => 'کارمندان',
                    'attendance' => 'حضور و غیاب',
                    'exams' => 'آزمون‌ها',
                    'results' => 'نتایج',
                    'fees' => 'شهریه',
                    'expenses' => 'هزینه‌ها',
                    'payroll' => 'حقوق',
                    'library' => 'کتابخانه',
                    'transport' => 'حمل و نقل',
                    'inventory' => 'انبار',
                    'notifications' => 'اعلان‌ها',
                    'reports' => 'گزارش‌ها',
                    'audit-logs' => 'ممیزی',
                    'settings' => 'تنظیمات',
                    'create' => 'ایجاد',
                    'edit' => 'ویرایش',
                    'profile' => 'پروفایل',
                ];
                
                $count = count($segments);
                foreach ($segments as $i => $segment):
                    if (empty($segment) || is_numeric($segment)) continue;
                    $currentUrl .= '/' . $segment;
                    $label = $segmentLabels[$segment] ?? ucfirst(str_replace('-', ' ', $segment));
                ?>
                    <?php if ($i < $count - 1 && !is_numeric($segments[$i + 1] ?? '')): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= url($currentUrl) ?>" class="text-decoration-none text-secondary">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($label) ?>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>
<?php endif; ?>

<style>
    .breadcrumb {
        --bs-breadcrumb-divider: "\f053";
        --bs-breadcrumb-divider-font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 13px;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: var(--bs-breadcrumb-divider);
        font-family: var(--bs-breadcrumb-divider-font-family);
        font-weight: 900;
        font-size: 10px;
        color: #aaa;
        padding: 0 8px;
        float: right;
    }
    
    .breadcrumb-item a:hover {
        color: #4e73df !important;
    }
    
    .breadcrumb-item.active {
        color: #333;
        font-weight: 600;
    }
    
    @media print {
        .breadcrumb {
            display: none !important;
        }
    }
</style>