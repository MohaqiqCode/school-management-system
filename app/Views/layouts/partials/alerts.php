<?php
$alerts = [];
$session = \App\Core\Application::getInstance()->getSession();

// Success messages
if ($session->has('success')) {
    $alerts['success'] = $session->flash('success');
}

// Error messages
if ($session->has('error')) {
    $alerts['danger'] = $session->flash('error');
}

// Warning messages
if ($session->has('warning')) {
    $alerts['warning'] = $session->flash('warning');
}

// Info messages
if ($session->has('info')) {
    $alerts['info'] = $session->flash('info');
}

// Validation errors
if ($session->has('errors')) {
    $errors = $session->flash('errors');
    if (is_array($errors)) {
        $errorMessages = [];
        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                $errorMessages = array_merge($errorMessages, $messages);
            } else {
                $errorMessages[] = $messages;
            }
        }
        $alerts['danger'] = implode('<br>', $errorMessages);
    }
}
?>

<?php if (!empty($alerts)): ?>
<div class="alerts-container px-3 pt-3" id="alertsContainer">
    <?php foreach ($alerts as $type => $message): ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show d-flex align-items-center" role="alert">
        <?php
        $icon = match($type) {
            'success' => 'fa-check-circle',
            'danger' => 'fa-times-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle',
            default => 'fa-bell',
        };
        ?>
        <i class="fas <?= $icon ?> fa-lg me-2"></i>
        <div><?= $message ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endforeach; ?>
</div>

<script>
    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
</script>
<?php endif; ?>