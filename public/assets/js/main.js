/**
 * School Management System - Main JavaScript
 * توابع اصلی و عمومی سیستم
 */

"use strict";

// ============================================
// Global Variables
// ============================================
const APP = window.APP || {};
const BASE_URL = APP.BASE_URL || "";
const CSRF_TOKEN =
  document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
  "";

// ============================================
// Document Ready
// ============================================
$(document).ready(function () {
  initSidebar();
  initTooltips();
  initPopovers();
  initPasswordToggles();
  initAutoDismissAlerts();
  initDarkMode();
  initConfirmActions();
  initPrintButtons();
  hidePageLoader();
});

// ============================================
// Sidebar
// ============================================
function initSidebar() {
  const sidebar = document.getElementById("sidebar");
  const toggle = document.getElementById("sidebarToggle");
  const content = document.getElementById("mainContent");

  if (!sidebar || !toggle) return;

  // Toggle sidebar
  toggle.addEventListener("click", function () {
    sidebar.classList.toggle("show");
    sidebar.classList.toggle("collapsed");
    if (content) content.classList.toggle("expanded");

    // Save state
    const isCollapsed = sidebar.classList.contains("collapsed");
    localStorage.setItem("sidebarCollapsed", isCollapsed);
  });

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 991) {
      if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove("show");
      }
    }
  });

  // Restore state
  if (localStorage.getItem("sidebarCollapsed") === "true") {
    sidebar.classList.add("collapsed");
    if (content) content.classList.add("expanded");
  }

  // Handle submenu
  const submenuToggles = sidebar.querySelectorAll(
    '[data-bs-toggle="collapse"]',
  );
  submenuToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const icon = this.querySelector(".fa-chevron-down, .fa-chevron-left");
      if (icon) {
        icon.classList.toggle("fa-chevron-down");
        icon.classList.toggle("fa-chevron-left");
      }
    });
  });

  // Auto-expand submenu based on current URL
  const currentPath = window.location.pathname;
  const submenuLinks = sidebar.querySelectorAll(".collapse .nav-link");
  submenuLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPath) {
      link.classList.add("active");
      const collapse = link.closest(".collapse");
      if (collapse) {
        collapse.classList.add("show");
        const toggle = sidebar.querySelector(
          `[data-bs-target="#${collapse.id}"]`,
        );
        if (toggle) {
          const icon = toggle.querySelector(".fa-chevron-down");
          if (icon) {
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-left");
          }
        }
      }
    }
  });
}

// ============================================
// Bootstrap Tooltips
// ============================================
function initTooltips() {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (el) {
    return new bootstrap.Tooltip(el, {
      boundary: "window",
      container: "body",
    });
  });
}

// ============================================
// Bootstrap Popovers
// ============================================
function initPopovers() {
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  popoverTriggerList.map(function (el) {
    return new bootstrap.Popover(el, {
      container: "body",
    });
  });
}

// ============================================
// Password Toggle Visibility
// ============================================
function initPasswordToggles() {
  document.querySelectorAll(".password-toggle").forEach((btn) => {
    btn.addEventListener("click", function () {
      const input = this.parentElement.querySelector(
        'input[type="password"], input[type="text"]',
      );
      const icon = this.querySelector("i");

      if (input) {
        if (input.type === "password") {
          input.type = "text";
          icon?.classList.replace("fa-eye", "fa-eye-slash");
        } else {
          input.type = "password";
          icon?.classList.replace("fa-eye-slash", "fa-eye");
        }
      }
    });
  });
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  if (input) {
    input.type = input.type === "password" ? "text" : "password";
  }
}

// ============================================
// Auto Dismiss Alerts
// ============================================
function initAutoDismissAlerts() {
  setTimeout(function () {
    document.querySelectorAll(".alert-dismissible").forEach((alert) => {
      alert.style.transition = "opacity 0.5s";
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 500);
    });
  }, 5000);
}

// ============================================
// Dark Mode
// ============================================
function initDarkMode() {
  const darkCSS = document.getElementById("darkModeCSS");
  const toggleBtn = document.getElementById("darkModeToggle");

  if (!darkCSS || !toggleBtn) return;

  // Check saved preference
  const saved = localStorage.getItem("darkMode");
  if (saved === "true") {
    darkCSS.disabled = false;
    document.body.classList.add("dark-mode");
    updateDarkModeButton(true);
  }

  toggleBtn.addEventListener("click", function () {
    const isDark = !darkCSS.disabled;
    darkCSS.disabled = isDark;

    if (isDark) {
      document.body.classList.remove("dark-mode");
      localStorage.setItem("darkMode", "false");
    } else {
      document.body.classList.add("dark-mode");
      localStorage.setItem("darkMode", "true");
    }

    updateDarkModeButton(!isDark);
  });
}

function updateDarkModeButton(isDark) {
  const btn = document.getElementById("darkModeToggle");
  if (btn) {
    btn.innerHTML = isDark
      ? '<i class="fas fa-sun"></i>'
      : '<i class="fas fa-moon"></i>';
    btn.title = isDark ? "حالت روشن" : "حالت تاریک";
  }
}

// ============================================
// Confirm Actions
// ============================================
function initConfirmActions() {
  document.querySelectorAll("[data-confirm]").forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();
      const message = this.dataset.confirm || "آیا اطمینان دارید؟";
      const form = this.closest("form");

      Swal.fire({
        title: "تأیید",
        text: message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "بله",
        cancelButtonText: "انصراف",
        confirmButtonColor: "#e74a3b",
      }).then((result) => {
        if (result.isConfirmed && form) {
          form.submit();
        }
      });
    });
  });
}

// ============================================
// Print Buttons
// ============================================
function initPrintButtons() {
  document.querySelectorAll("[data-print]").forEach((btn) => {
    btn.addEventListener("click", function () {
      const target = this.dataset.print;
      if (target) {
        const el = document.querySelector(target);
        if (el) {
          const content = el.innerHTML;
          const printWindow = window.open("", "_blank");
          printWindow.document.write(`
                        <!DOCTYPE html>
                        <html dir="rtl">
                        <head>
                            <meta charset="UTF-8">
                            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
                            <style>body{font-family:Tahoma;padding:20px;direction:rtl}@page{margin:10mm}</style>
                        </head>
                        <body>${content}</body>
                        </html>
                    `);
          printWindow.document.close();
          printWindow.focus();
          setTimeout(() => printWindow.print(), 500);
        }
      }
    });
  });
}

// ============================================
// Page Loader
// ============================================
function hidePageLoader() {
  const loader = document.getElementById("pageLoader");
  if (loader) {
    setTimeout(() => {
      loader.classList.add("hidden");
      setTimeout(() => loader.remove(), 300);
    }, 400);
  }
}

// ============================================
// Notifications
// ============================================
function loadNotifications() {
  fetch(BASE_URL + "/api/notifications/unread")
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        updateNotificationBadge(data.data.count);
        updateNotificationList(data.data.notifications);
      }
    })
    .catch((err) => console.error("Failed to load notifications:", err));
}

function updateNotificationBadge(count) {
  const badge = document.querySelector(".notification-badge");
  if (badge) {
    if (count > 0) {
      badge.textContent = count > 99 ? "99+" : count;
      badge.style.display = "inline";
    } else {
      badge.style.display = "none";
    }
  }
}

function updateNotificationList(notifications) {
  const list = document.querySelector(".notification-list");
  if (!list) return;

  if (!notifications || notifications.length === 0) {
    list.innerHTML =
      '<div class="text-center py-3 text-muted">اعلانی وجود ندارد</div>';
    return;
  }

  const icons = {
    announcement: "bullhorn",
    reminder: "clock",
    alert: "exclamation-triangle",
    info: "info-circle",
    emergency: "exclamation-circle",
  };
  const colors = {
    announcement: "primary",
    reminder: "info",
    alert: "warning",
    info: "secondary",
    emergency: "danger",
  };

  list.innerHTML = notifications
    .map(
      (n) => `
        <a href="${BASE_URL}/notifications/${n.id}/read" class="dropdown-item notification-item ${!n.read_at ? "bg-light" : ""}">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-${icons[n.notification_type] || "bell"} text-${colors[n.notification_type] || "secondary"} fa-lg mt-1"></i>
                </div>
                <div class="flex-grow-1 ms-2">
                    <div class="small fw-bold">${escapeHtml(n.title)}</div>
                    <div class="small text-muted">${escapeHtml(n.message?.substring(0, 60))}...</div>
                    <div class="small text-muted mt-1"><i class="far fa-clock me-1"></i>${timeAgo(n.created_at)}</div>
                </div>
            </div>
        </a>
    `,
    )
    .join("");
}

// ============================================
// Utility Functions
// ============================================
function escapeHtml(str) {
  if (!str) return "";
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}

function timeAgo(dateString) {
  const now = new Date();
  const then = new Date(dateString);
  const diff = Math.floor((now - then) / 1000);

  if (diff < 60) return "همین الان";
  if (diff < 3600) return Math.floor(diff / 60) + " دقیقه پیش";
  if (diff < 86400) return Math.floor(diff / 3600) + " ساعت پیش";
  if (diff < 2592000) return Math.floor(diff / 86400) + " روز پیش";
  return Math.floor(diff / 2592000) + " ماه پیش";
}

function formatNumber(num) {
  return new Intl.NumberFormat("fa-IR").format(num);
}

function formatCurrency(amount) {
  return formatNumber(amount) + " تومان";
}

function formatDate(date, format) {
  if (!date) return "";
  const pd = new persianDate(date);
  return pd.format(format || "YYYY/MM/DD");
}

function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => toastr.success("کپی شد"));
  } else {
    const ta = document.createElement("textarea");
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand("copy");
    document.body.removeChild(ta);
    toastr.success("کپی شد");
  }
}

// ============================================
// Keyboard Shortcuts
// ============================================
document.addEventListener("keydown", function (e) {
  if ((e.ctrlKey || e.metaKey) && e.key === "s") {
    e.preventDefault();
    const form = document.querySelector("form");
    if (form) form.submit();
  }

  if ((e.ctrlKey || e.metaKey) && e.key === "d") {
    e.preventDefault();
    window.location.href = BASE_URL + "/dashboard";
  }
});

// ============================================
// Refresh notifications periodically
// ============================================
setInterval(loadNotifications, 30000);
loadNotifications();
