/**
 * School Management System - Form Validation
 * اعتبارسنجی فرم‌ها
 */

"use strict";

// ============================================
// Validation Rules
// ============================================
const validationRules = {
  required: {
    validate: (value) =>
      value !== null && value !== undefined && value.toString().trim() !== "",
    message: "این فیلد الزامی است",
  },
  email: {
    validate: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
    message: "لطفاً یک ایمیل معتبر وارد کنید",
  },
  min: {
    validate: (value, param) => value.length >= param,
    message: (param) => `حداقل ${param} کاراکتر وارد کنید`,
  },
  max: {
    validate: (value, param) => value.length <= param,
    message: (param) => `حداکثر ${param} کاراکتر مجاز است`,
  },
  numeric: {
    validate: (value) => !isNaN(value) && value !== "",
    message: "لطفاً یک عدد وارد کنید",
  },
  integer: {
    validate: (value) => Number.isInteger(Number(value)),
    message: "لطفاً یک عدد صحیح وارد کنید",
  },
  phone: {
    validate: (value) => /^09\d{9}$/.test(value.replace(/[^0-9]/g, "")),
    message: "شماره موبایل نامعتبر است",
  },
  nationalCode: {
    validate: (value) => {
      const code = value.replace(/[^0-9]/g, "");
      if (code.length !== 10) return false;
      let sum = 0;
      for (let i = 0; i < 9; i++) sum += parseInt(code[i]) * (10 - i);
      const rem = sum % 11;
      const control = parseInt(code[9]);
      return (rem < 2 && control === rem) || (rem >= 2 && control === 11 - rem);
    },
    message: "کد ملی نامعتبر است",
  },
  match: {
    validate: (value, param) => value === document.querySelector(param)?.value,
    message: "مقادیر مطابقت ندارند",
  },
  date: {
    validate: (value) => !isNaN(Date.parse(value)),
    message: "تاریخ نامعتبر است",
  },
  fileSize: {
    validate: (value, param) => {
      const file = document.querySelector(value)?.files?.[0];
      return file ? file.size <= param * 1024 * 1024 : true;
    },
    message: (param) => `حجم فایل نباید بیشتر از ${param}MB باشد`,
  },
  fileType: {
    validate: (value, param) => {
      const file = document.querySelector(value)?.files?.[0];
      if (!file) return true;
      const ext = file.name.split(".").pop().toLowerCase();
      return param.split(",").includes(ext);
    },
    message: (param) => `فرمت مجاز: ${param}`,
  },
};

// ============================================
// Validator Class
// ============================================
class FormValidator {
  constructor(form) {
    this.form = form;
    this.errors = {};
    this.init();
  }

  init() {
    this.form.setAttribute("novalidate", "");

    this.form.addEventListener("submit", (e) => {
      if (!this.validate()) {
        e.preventDefault();
        this.showErrors();
      }
    });

    // Real-time validation on blur
    this.form.querySelectorAll("[data-validate]").forEach((field) => {
      field.addEventListener("blur", () => this.validateField(field));
      field.addEventListener("input", () => {
        if (field.classList.contains("is-invalid")) {
          this.validateField(field);
        }
      });
    });
  }

  validate() {
    this.errors = {};
    let isValid = true;

    this.form.querySelectorAll("[data-validate]").forEach((field) => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  validateField(field) {
    const rules = field.dataset.validate.split("|");
    const name = field.name || field.id;
    let fieldValid = true;

    rules.forEach((rule) => {
      let ruleName = rule;
      let param = null;

      if (rule.includes(":")) {
        [ruleName, param] = rule.split(":");
      }

      const validator = validationRules[ruleName];
      if (validator) {
        const value = field.value;
        const valid = validator.validate(value, param);

        if (!valid) {
          fieldValid = false;
          const message =
            typeof validator.message === "function"
              ? validator.message(param)
              : validator.message;
          this.addError(name, message);
        }
      }
    });

    if (fieldValid) {
      field.classList.remove("is-invalid");
      field.classList.add("is-valid");
    } else {
      field.classList.add("is-invalid");
      field.classList.remove("is-valid");
    }

    return fieldValid;
  }

  addError(field, message) {
    if (!this.errors[field]) this.errors[field] = [];
    this.errors[field].push(message);
  }

  showErrors() {
    // Show first error as toast
    const firstError = Object.values(this.errors).flat()[0];
    if (firstError) toastr.error(firstError);

    // Scroll to first invalid field
    const firstInvalid = this.form.querySelector(".is-invalid");
    if (firstInvalid) {
      firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
      firstInvalid.focus();
    }
  }
}

// ============================================
// Initialize Validation
// ============================================
$(document).ready(function () {
  document.querySelectorAll("form[data-validate]").forEach((form) => {
    new FormValidator(form);
  });

  // Initialize Bootstrap validation styles
  document.querySelectorAll("form.needs-validation").forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add("was-validated");
    });
  });
});

// ============================================
// Password Strength Checker
// ============================================
function checkPasswordStrength(password) {
  let score = 0;

  if (password.length >= 8) score += 20;
  if (password.length >= 12) score += 10;
  if (/[A-Z]/.test(password)) score += 20;
  if (/[a-z]/.test(password)) score += 15;
  if (/[0-9]/.test(password)) score += 20;
  if (/[^A-Za-z0-9]/.test(password)) score += 15;

  return {
    score: Math.min(100, score),
    level: score < 40 ? "weak" : score < 70 ? "medium" : "strong",
    label: score < 40 ? "ضعیف" : score < 70 ? "متوسط" : "قوی",
  };
}

function updatePasswordStrength(inputId, barId, labelId) {
  const input = document.getElementById(inputId);
  const bar = document.getElementById(barId);
  const label = document.getElementById(labelId);

  if (!input || !bar) return;

  input.addEventListener("input", function () {
    const result = checkPasswordStrength(this.value);
    bar.style.width = result.score + "%";
    bar.className =
      "progress-bar bg-" +
      (result.level === "weak"
        ? "danger"
        : result.level === "medium"
          ? "warning"
          : "success");
    if (label) {
      label.textContent = result.label;
      label.className =
        "fw-bold text-" +
        (result.level === "weak"
          ? "danger"
          : result.level === "medium"
            ? "warning"
            : "success");
    }
  });
}
