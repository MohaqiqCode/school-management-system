/**
 * School Management System - Dashboard JavaScript
 * توابع و نمودارهای داشبورد
 */

"use strict";

// ============================================
// Chart Colors
// ============================================
const chartColors = {
  primary: "#4e73df",
  success: "#1cc88a",
  info: "#36b9cc",
  warning: "#f6c23e",
  danger: "#e74a3b",
  secondary: "#858796",
  dark: "#5a5c69",
  palette: [
    "#4e73df",
    "#1cc88a",
    "#36b9cc",
    "#f6c23e",
    "#e74a3b",
    "#858796",
    "#5a5c69",
    "#2e59d9",
  ],
};

// ============================================
// Chart Defaults
// ============================================
Chart.defaults.font.family = "IRANSans, Tahoma";
Chart.defaults.font.size = 12;
Chart.defaults.color = "#858796";

// ============================================
// Create Bar Chart
// ============================================
function createBarChart(canvasId, labels, data, options = {}) {
  const ctx = document.getElementById(canvasId)?.getContext("2d");
  if (!ctx) return null;

  return new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: options.label || "",
          data: data,
          backgroundColor: options.color || chartColors.primary,
          borderRadius: 5,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: !!options.label, position: "top" },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: options.currency ? (v) => formatNumber(v) + " ت" : v,
          },
        },
      },
    },
  });
}

// ============================================
// Create Line Chart
// ============================================
function createLineChart(canvasId, labels, datasets, options = {}) {
  const ctx = document.getElementById(canvasId)?.getContext("2d");
  if (!ctx) return null;

  return new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: datasets.map((ds, i) => ({
        label: ds.label || "",
        data: ds.data,
        borderColor: ds.color || chartColors.palette[i],
        backgroundColor: ds.fill
          ? (ds.color || chartColors.palette[i]) + "20"
          : "transparent",
        tension: 0.3,
        fill: ds.fill || false,
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
      })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: "top" },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: options.currency ? (v) => formatNumber(v) + " ت" : v,
          },
        },
      },
    },
  });
}

// ============================================
// Create Doughnut Chart
// ============================================
function createDoughnutChart(canvasId, labels, data, options = {}) {
  const ctx = document.getElementById(canvasId)?.getContext("2d");
  if (!ctx) return null;

  return new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: labels,
      datasets: [
        {
          data: data,
          backgroundColor: chartColors.palette.slice(0, data.length),
          hoverBorderColor: "#fff",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          rtl: true,
          labels: {
            padding: 20,
            usePointStyle: true,
            font: { size: 11 },
          },
        },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              const pct = ((ctx.parsed / total) * 100).toFixed(1);
              return `${ctx.label}: ${formatNumber(ctx.parsed)} (${pct}%)`;
            },
          },
        },
      },
    },
  });
}

// ============================================
// Create Pie Chart
// ============================================
function createPieChart(canvasId, labels, data) {
  return createDoughnutChart(canvasId, labels, data); // Same but pie type
}

// ============================================
// Stats Counter Animation
// ============================================
function animateCounter(element, target, duration = 1500) {
  const start = 0;
  const increment = target / (duration / 16);
  let current = start;

  const timer = setInterval(() => {
    current += increment;
    if (current >= target) {
      element.textContent = formatNumber(Math.round(target));
      clearInterval(timer);
    } else {
      element.textContent = formatNumber(Math.round(current));
    }
  }, 16);
}

function initCounters() {
  document.querySelectorAll("[data-count]").forEach((el) => {
    const target = parseInt(el.dataset.count);
    if (target) animateCounter(el, target);
  });
}

// ============================================
// Dashboard Specific Functions
// ============================================
function loadDashboardStats() {
  fetch(BASE_URL + "/api/dashboard/stats")
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        updateStatsCards(data.stats);
      }
    })
    .catch((err) => console.error("Failed to load stats:", err));
}

function updateStatsCards(stats) {
  for (const [key, value] of Object.entries(stats)) {
    const el = document.getElementById(`stat-${key}`);
    if (el) el.textContent = formatNumber(value);
  }
}

// ============================================
// Calendar Widget
// ============================================
function initCalendarWidget(elementId, events) {
  const el = document.getElementById(elementId);
  if (!el || typeof FullCalendar === "undefined") return;

  new FullCalendar.Calendar(el, {
    initialView: "dayGridMonth",
    themeSystem: "bootstrap5",
    direction: "rtl",
    locale: "fa",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "",
    },
    events: events || [],
    height: "auto",
  }).render();
}

// ============================================
// Initialize Dashboard
// ============================================
$(document).ready(function () {
  initCounters();
  loadDashboardStats();
});
