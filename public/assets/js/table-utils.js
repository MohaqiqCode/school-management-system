/**
 * School Management System - Table Utilities
 * توابع کمکی برای کار با جداول
 */

"use strict";

// ============================================
// DataTable Default Config
// ============================================
const dataTableDefaults = {
  responsive: true,
  paging: true,
  searching: true,
  ordering: true,
  info: true,
  lengthChange: true,
  pageLength: 25,
  lengthMenu: [
    [10, 25, 50, 100, -1],
    [10, 25, 50, 100, "همه"],
  ],
  language: {
    sEmptyTable: "هیچ داده‌ای در جدول وجود ندارد",
    sInfo: "نمایش _START_ تا _END_ از _TOTAL_ رکورد",
    sInfoEmpty: "نمایش 0 تا 0 از 0 رکورد",
    sInfoFiltered: "(فیلتر شده از _MAX_ رکورد)",
    sLengthMenu: "نمایش _MENU_ رکورد در صفحه",
    sLoadingRecords: "در حال بارگذاری...",
    sProcessing: "در حال پردازش...",
    sSearch: "جستجو:",
    sZeroRecords: "رکوردی با این مشخصات یافت نشد",
    oPaginate: {
      sFirst: "ابتدا",
      sLast: "انتها",
      sNext: "بعدی",
      sPrevious: "قبلی",
    },
  },
  dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
};

// ============================================
// Initialize DataTable
// ============================================
function initDataTable(tableId, options = {}) {
  const table = document.getElementById(tableId);
  if (!table) return null;

  const config = { ...dataTableDefaults, ...options };

  return $(table).DataTable(config);
}

// ============================================
// Export Table to Excel
// ============================================
function exportTableToExcel(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) {
    toastr.error("جدول یافت نشد");
    return;
  }

  try {
    const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
    const date = new persianDate().format("YYYY-MM-DD");
    XLSX.writeFile(wb, `${filename || "export"}_${date}.xlsx`);
    toastr.success("فایل Excel با موفقیت ساخته شد");
  } catch (e) {
    toastr.error("خطا در ساخت فایل Excel");
    console.error(e);
  }
}

// ============================================
// Export Table to PDF
// ============================================
function exportTableToPDF(tableId, title) {
  const table = document.getElementById(tableId);
  if (!table) {
    toastr.error("جدول یافت نشد");
    return;
  }

  try {
    const rows = [];
    const headers = [];

    table
      .querySelectorAll("thead th")
      .forEach((th) => headers.push(th.textContent.trim()));
    table.querySelectorAll("tbody tr").forEach((tr) => {
      const row = [];
      tr.querySelectorAll("td").forEach((td) =>
        row.push(td.textContent.trim()),
      );
      rows.push(row);
    });

    const docDefinition = {
      pageSize: "A4",
      pageOrientation: "landscape",
      rtl: true,
      content: [
        {
          text: title || "گزارش",
          style: "header",
          alignment: "center",
          margin: [0, 0, 0, 20],
        },
        {
          table: {
            headerRows: 1,
            widths: Array(headers.length).fill("*"),
            body: [headers, ...rows],
          },
          layout: "lightHorizontalLines",
        },
      ],
      styles: {
        header: { fontSize: 16, bold: true },
      },
      defaultStyle: { font: "Tahoma" },
    };

    pdfMake.createPdf(docDefinition).download(`${title || "report"}.pdf`);
    toastr.success("فایل PDF با موفقیت ساخته شد");
  } catch (e) {
    toastr.error("خطا در ساخت فایل PDF");
    console.error(e);
  }
}

// ============================================
// Print Table
// ============================================
function printTable(tableId) {
  printElement(tableId);
}

// ============================================
// Filter Table Rows
// ============================================
function filterTable(tableId, filterFn) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const rows = table.querySelectorAll("tbody tr");
  let visibleCount = 0;

  rows.forEach((row) => {
    if (filterFn(row)) {
      row.style.display = "";
      visibleCount++;
    } else {
      row.style.display = "none";
    }
  });

  // Update counter if exists
  const counter = document.getElementById(tableId + "-count");
  if (counter) counter.textContent = visibleCount;

  return visibleCount;
}

// ============================================
// Sort Table
// ============================================
function sortTable(tableId, columnIndex, ascending = true) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const tbody = table.querySelector("tbody");
  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((a, b) => {
    const aVal = a.cells[columnIndex]?.textContent.trim() || "";
    const bVal = b.cells[columnIndex]?.textContent.trim() || "";

    // Try numeric comparison
    const aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ""));
    const bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ""));

    if (!isNaN(aNum) && !isNaN(bNum)) {
      return ascending ? aNum - bNum : bNum - aNum;
    }

    return ascending
      ? aVal.localeCompare(bVal, "fa")
      : bVal.localeCompare(aVal, "fa");
  });

  rows.forEach((row) => tbody.appendChild(row));

  // Update sort indicators
  table
    .querySelectorAll("th.sortable")
    .forEach((th) => th.classList.remove("sort-asc", "sort-desc"));
  const header = table.querySelectorAll("th")[columnIndex];
  if (header) header.classList.add(ascending ? "sort-asc" : "sort-desc");
}

// ============================================
// Select All Checkboxes
// ============================================
function toggleSelectAll(selectAllCheckbox, tableId) {
  const table = document.getElementById(tableId);
  if (!table) return;

  const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
  checkboxes.forEach((cb) => (cb.checked = selectAllCheckbox.checked));
}

// ============================================
// Get Selected Row IDs
// ============================================
function getSelectedIds(tableId) {
  const table = document.getElementById(tableId);
  if (!table) return [];

  const ids = [];
  table
    .querySelectorAll('tbody input[type="checkbox"]:checked')
    .forEach((cb) => {
      const row = cb.closest("tr");
      if (row && row.dataset.id) ids.push(row.dataset.id);
    });

  return ids;
}

// ============================================
// Bulk Delete
// ============================================
function bulkDelete(tableId, deleteUrl) {
  const ids = getSelectedIds(tableId);

  if (ids.length === 0) {
    toastr.warning("لطفاً حداقل یک ردیف را انتخاب کنید");
    return;
  }

  Swal.fire({
    title: "حذف گروهی",
    text: `آیا از حذف ${ids.length} ردیف انتخاب شده اطمینان دارید؟`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "حذف",
    confirmButtonColor: "#e74a3b",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(deleteUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": CSRF_TOKEN,
        },
        body: JSON.stringify({ ids: ids }),
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
// Search Table
// ============================================
function searchTable(tableId, query) {
  const table = document.getElementById(tableId);
  if (!table) return 0;

  const q = query.toLowerCase().trim();
  let count = 0;

  table.querySelectorAll("tbody tr").forEach((row) => {
    const text = row.textContent.toLowerCase();
    if (q === "" || text.includes(q)) {
      row.style.display = "";
      count++;
    } else {
      row.style.display = "none";
    }
  });

  return count;
}
