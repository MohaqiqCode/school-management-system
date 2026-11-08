/**
 * School Management System - App JavaScript
 * توابع اصلی برنامه
 */

"use strict";

// ============================================
// Global App Object
// ============================================
window.App = {
  BASE_URL: BASE_URL,
  CSRF_TOKEN: CSRF_TOKEN,

  // Initialize all components
  init: function () {
    this.initSelect2();
    this.initDatePickers();
    this.initDropzone();
    this.initCharts();
    this.initFormValidation();
    this.initAjaxSetup();
  },

  // Select2
  initSelect2: function () {
    $(".select2").select2({
      theme: "bootstrap-5",
      language: "fa",
      dir: "rtl",
      width: "100%",
    });
  },

  // Persian Datepicker
  initDatePickers: function () {
    $(".persian-datepicker").each(function () {
      $(this).persianDatepicker({
        format: "YYYY/MM/DD",
        autoClose: true,
        initialValue: !!this.value,
        observer: true,
      });
    });
  },

  // Dropzone
  initDropzone: function () {
    if (typeof Dropzone !== "undefined") {
      Dropzone.autoDiscover = false;
    }
  },

  // Chart.js Global Config
  initCharts: function () {
    if (typeof Chart !== "undefined") {
      Chart.defaults.font.family = "IRANSans, Tahoma";
      Chart.defaults.font.size = 12;
    }
  },

  // Form Validation
  initFormValidation: function () {
    document.querySelectorAll("form[data-validate]").forEach((form) => {
      form.addEventListener("submit", function (e) {
        if (!validateForm(this)) {
          e.preventDefault();
        }
      });
    });
  },

  // AJAX Setup
  initAjaxSetup: function () {
    $.ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": CSRF_TOKEN,
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    $(document).ajaxError(function (event, xhr) {
      if (xhr.status === 419) {
        toastr.error("نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید.");
      } else if (xhr.status === 500) {
        toastr.error("خطای سرور رخ داده است.");
      }
    });
  },
};

// ============================================
// Form Validation
// ============================================
function validateForm(form) {
  let isValid = true;

  form.querySelectorAll("[required]").forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("is-invalid");
      isValid = false;
    } else {
      field.classList.remove("is-invalid");
    }
  });

  // Email validation
  form.querySelectorAll('input[type="email"]').forEach((field) => {
    if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
      field.classList.add("is-invalid");
      isValid = false;
    }
  });

  return isValid;
}

// ============================================
// Confirm Delete
// ============================================
function confirmDelete(url, message = "آیا از حذف این آیتم اطمینان دارید؟") {
  Swal.fire({
    title: "تأیید حذف",
    text: message,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "حذف",
    cancelButtonText: "انصراف",
    confirmButtonColor: "#e74a3b",
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form");
      form.method = "POST";
      form.action = url;
      form.innerHTML = `
                <input type="hidden" name="csrf_token" value="${CSRF_TOKEN}">
                <input type="hidden" name="_method" value="DELETE">
            `;
      document.body.appendChild(form);
      form.submit();
    }
  });
}

// ============================================
// Toggle Status
// ============================================
function toggleStatus(url, currentStatus) {
  const newStatus = currentStatus ? 0 : 1;
  const action = newStatus ? "فعال" : "غیرفعال";

  Swal.fire({
    title: `تغییر وضعیت به ${action}`,
    text: "آیا اطمینان دارید؟",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "بله",
    cancelButtonText: "خیر",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": CSRF_TOKEN,
        },
        body: JSON.stringify({ status: newStatus }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 800);
          } else {
            toastr.error(data.message);
          }
        });
    }
  });
}

// ============================================
// Export Data
// ============================================
function exportData(url, format = "excel") {
  window.location.href = `${url}?format=${format}`;
  toastr.info("در حال آماده‌سازی فایل...");
}

// ============================================
// Print Element
// ============================================
function printElement(elementId) {
  const el = document.getElementById(elementId);
  if (!el) return;

  const win = window.open("", "_blank");
  win.document.write(`
        <!DOCTYPE html>
        <html dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>چاپ</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
            <style>
                body { font-family: Tahoma; padding: 20px; }
                @page { margin: 10mm; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>${el.innerHTML}</body>
        </html>
    `);
  win.document.close();
  win.focus();
  setTimeout(() => win.print(), 500);
}

// ============================================
// Initialize on DOM Ready
// ============================================
$(document).ready(function () {
  App.init();

  // Initialize Persian Datepickers
  if (typeof persianDate !== "undefined") {
    $(".persian-datepicker").persianDatepicker({
      format: "YYYY/MM/DD",
      autoClose: true,
    });
  }

  // Initialize Flatpickr
  if (typeof flatpickr !== "undefined") {
    flatpickr.localize(flatpickr.l10ns.fa);
    $(".flatpickr-date").flatpickr({ dateFormat: "Y/m/d", locale: "fa" });
  }
});
