<?php
/**
 * School Management System - Web Routes
 * مسیرهای اصلی برنامه
 */

use App\Core\Application;

$router = Application::getInstance()->getRouter();

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
$router->get('/', 'AuthController', 'loginForm', ['Guest']);
$router->get('/login', 'AuthController', 'loginForm', ['Guest']);
$router->post('/login', 'AuthController', 'login', ['Guest', 'Csrf', 'Throttle:5,15']);
$router->get('/logout', 'AuthController', 'logout', ['Auth']);
$router->get('/forgot-password', 'AuthController', 'forgotPasswordForm', ['Guest']);
$router->post('/forgot-password', 'AuthController', 'forgotPassword', ['Guest', 'Csrf', 'Throttle:3,15']);
$router->get('/reset-password/{token}', 'AuthController', 'resetPasswordForm', ['Guest']);
$router->post('/reset-password', 'AuthController', 'resetPassword', ['Guest', 'Csrf']);

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
$router->get('/profile', 'UserController', 'profile', ['Auth']);
$router->put('/profile', 'UserController', 'updateProfile', ['Auth', 'Csrf']);
$router->get('/change-password', 'AuthController', 'changePasswordForm', ['Auth']);
$router->put('/change-password', 'AuthController', 'changePassword', ['Auth', 'Csrf']);

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/
$router->get('/dashboard', 'DashboardController', 'index', ['Auth']);

/*
|--------------------------------------------------------------------------
| Branch Management
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/branches', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'BranchController', 'index', ['Permission:view_branches']);
    $router->get('/create', 'BranchController', 'create', ['Permission:create_branches']);
    $router->post('/', 'BranchController', 'store', ['Permission:create_branches', 'Csrf']);
    $router->get('/{id}', 'BranchController', 'show', ['Permission:view_branches']);
    $router->get('/{id}/edit', 'BranchController', 'edit', ['Permission:edit_branches']);
    $router->put('/{id}', 'BranchController', 'update', ['Permission:edit_branches', 'Csrf']);
    $router->delete('/{id}', 'BranchController', 'delete', ['Permission:delete_branches', 'Csrf']);
    $router->get('/{id}/settings', 'BranchController', 'settings', ['Permission:manage_branch_settings']);
    $router->put('/{id}/settings', 'BranchController', 'updateSettings', ['Permission:manage_branch_settings', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| User Management
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/users', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'UserController', 'index', ['Permission:view_users']);
    $router->get('/create', 'UserController', 'create', ['Permission:create_users']);
    $router->post('/', 'UserController', 'store', ['Permission:create_users', 'Csrf']);
    $router->get('/{id}', 'UserController', 'show', ['Permission:view_users']);
    $router->get('/{id}/edit', 'UserController', 'edit', ['Permission:edit_users']);
    $router->put('/{id}', 'UserController', 'update', ['Permission:edit_users', 'Csrf']);
    $router->delete('/{id}', 'UserController', 'delete', ['Permission:delete_users', 'Csrf']);
    $router->post('/{id}/toggle-status', 'UserController', 'toggleStatus', ['Permission:change_user_status']);
});

/*
|--------------------------------------------------------------------------
| Role & Permission Management
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/roles', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'RoleController', 'index', ['Permission:view_roles']);
    $router->get('/create', 'RoleController', 'create', ['Permission:create_roles']);
    $router->post('/', 'RoleController', 'store', ['Permission:create_roles', 'Csrf']);
    $router->get('/{id}/edit', 'RoleController', 'edit', ['Permission:edit_roles']);
    $router->put('/{id}', 'RoleController', 'update', ['Permission:edit_roles', 'Csrf']);
    $router->delete('/{id}', 'RoleController', 'delete', ['Permission:delete_roles', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Department Management
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/departments', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'DepartmentController', 'index', ['Permission:view_departments']);
    $router->get('/create', 'DepartmentController', 'create', ['Permission:create_departments']);
    $router->post('/', 'DepartmentController', 'store', ['Permission:create_departments', 'Csrf']);
    $router->get('/{id}/edit', 'DepartmentController', 'edit', ['Permission:edit_departments']);
    $router->put('/{id}', 'DepartmentController', 'update', ['Permission:edit_departments', 'Csrf']);
    $router->delete('/{id}', 'DepartmentController', 'delete', ['Permission:delete_departments', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Academic Years
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/academic-years', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'AcademicYearController', 'index', ['Permission:view_academic_years']);
    $router->get('/create', 'AcademicYearController', 'create', ['Permission:create_academic_years']);
    $router->post('/', 'AcademicYearController', 'store', ['Permission:create_academic_years', 'Csrf']);
    $router->get('/{id}/edit', 'AcademicYearController', 'edit', ['Permission:edit_academic_years']);
    $router->put('/{id}', 'AcademicYearController', 'update', ['Permission:edit_academic_years', 'Csrf']);
    $router->delete('/{id}', 'AcademicYearController', 'delete', ['Permission:delete_academic_years', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Semesters
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/semesters', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'SemesterController', 'index', ['Permission:view_semesters']);
    $router->get('/create', 'SemesterController', 'create', ['Permission:create_semesters']);
    $router->post('/', 'SemesterController', 'store', ['Permission:create_semesters', 'Csrf']);
    $router->get('/{id}/edit', 'SemesterController', 'edit', ['Permission:edit_semesters']);
    $router->put('/{id}', 'SemesterController', 'update', ['Permission:edit_semesters', 'Csrf']);
    $router->delete('/{id}', 'SemesterController', 'delete', ['Permission:delete_semesters', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Classes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/classes', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'ClassController', 'index', ['Permission:view_classes']);
    $router->get('/create', 'ClassController', 'create', ['Permission:create_classes']);
    $router->post('/', 'ClassController', 'store', ['Permission:create_classes', 'Csrf']);
    $router->get('/{id}/edit', 'ClassController', 'edit', ['Permission:edit_classes']);
    $router->put('/{id}', 'ClassController', 'update', ['Permission:edit_classes', 'Csrf']);
    $router->delete('/{id}', 'ClassController', 'delete', ['Permission:delete_classes', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Sections
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/sections', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'SectionController', 'index', ['Permission:view_sections']);
    $router->get('/create', 'SectionController', 'create', ['Permission:create_sections']);
    $router->post('/', 'SectionController', 'store', ['Permission:create_sections', 'Csrf']);
    $router->get('/{id}/edit', 'SectionController', 'edit', ['Permission:edit_sections']);
    $router->put('/{id}', 'SectionController', 'update', ['Permission:edit_sections', 'Csrf']);
    $router->delete('/{id}', 'SectionController', 'delete', ['Permission:delete_sections', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Subjects
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/subjects', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'SubjectController', 'index', ['Permission:view_subjects']);
    $router->get('/create', 'SubjectController', 'create', ['Permission:create_subjects']);
    $router->post('/', 'SubjectController', 'store', ['Permission:create_subjects', 'Csrf']);
    $router->get('/{id}/edit', 'SubjectController', 'edit', ['Permission:edit_subjects']);
    $router->put('/{id}', 'SubjectController', 'update', ['Permission:edit_subjects', 'Csrf']);
    $router->delete('/{id}', 'SubjectController', 'delete', ['Permission:delete_subjects', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Timetable
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/timetable', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'TimetableController', 'index', ['Permission:view_timetable']);
    $router->get('/create', 'TimetableController', 'create', ['Permission:create_timetable']);
    $router->post('/', 'TimetableController', 'store', ['Permission:create_timetable', 'Csrf']);
    $router->get('/{id}/edit', 'TimetableController', 'edit', ['Permission:edit_timetable']);
    $router->put('/{id}', 'TimetableController', 'update', ['Permission:edit_timetable', 'Csrf']);
    $router->delete('/{id}', 'TimetableController', 'delete', ['Permission:delete_timetable', 'Csrf']);
    $router->get('/view/{classId}/{sectionId?}', 'TimetableController', 'viewByClass', ['Permission:view_timetable']);
});

/*
|--------------------------------------------------------------------------
| Students
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/students', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'StudentController', 'index', ['Permission:view_students']);
    $router->get('/create', 'StudentController', 'create', ['Permission:create_students']);
    $router->post('/', 'StudentController', 'store', ['Permission:create_students', 'Csrf']);
    $router->get('/{id}', 'StudentController', 'show', ['Permission:view_students']);
    $router->get('/{id}/edit', 'StudentController', 'edit', ['Permission:edit_students']);
    $router->put('/{id}', 'StudentController', 'update', ['Permission:edit_students', 'Csrf']);
    $router->delete('/{id}', 'StudentController', 'delete', ['Permission:delete_students', 'Csrf']);
    
    // Promotion
    $router->get('/{id}/promote', 'StudentController', 'promote', ['Permission:promote_students']);
    $router->post('/{id}/promote', 'StudentController', 'storePromotion', ['Permission:promote_students', 'Csrf']);
    
    // Documents
    $router->get('/{id}/documents', 'StudentController', 'documents', ['Permission:view_student_documents']);
    $router->post('/{id}/documents', 'StudentController', 'uploadDocument', ['Permission:manage_student_documents', 'Csrf']);
    $router->post('/document/{documentId}/verify', 'StudentController', 'verifyDocument', ['Permission:manage_student_documents']);
});

/*
|--------------------------------------------------------------------------
| Parents
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/parents', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'ParentController', 'index', ['Permission:view_parents']);
    $router->get('/{id}', 'ParentController', 'show', ['Permission:view_parents']);
    $router->get('/{id}/edit', 'ParentController', 'edit', ['Permission:edit_parents']);
    $router->put('/{id}', 'ParentController', 'update', ['Permission:edit_parents', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Teachers
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/teachers', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'TeacherController', 'index', ['Permission:view_teachers']);
    $router->get('/create', 'TeacherController', 'create', ['Permission:create_teachers']);
    $router->post('/', 'TeacherController', 'store', ['Permission:create_teachers', 'Csrf']);
    $router->get('/{id}', 'TeacherController', 'show', ['Permission:view_teachers']);
    $router->get('/{id}/edit', 'TeacherController', 'edit', ['Permission:edit_teachers']);
    $router->put('/{id}', 'TeacherController', 'update', ['Permission:edit_teachers', 'Csrf']);
    $router->delete('/{id}', 'TeacherController', 'delete', ['Permission:delete_teachers', 'Csrf']);
    $router->get('/{id}/workload', 'TeacherController', 'workload', ['Permission:view_teacher_workload']);
});

/*
|--------------------------------------------------------------------------
| Employees
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/employees', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'EmployeeController', 'index', ['Permission:view_employees']);
    $router->get('/create', 'EmployeeController', 'create', ['Permission:create_employees']);
    $router->post('/', 'EmployeeController', 'store', ['Permission:create_employees', 'Csrf']);
    $router->get('/{id}/edit', 'EmployeeController', 'edit', ['Permission:edit_employees']);
    $router->put('/{id}', 'EmployeeController', 'update', ['Permission:edit_employees', 'Csrf']);
    $router->delete('/{id}', 'EmployeeController', 'delete', ['Permission:delete_employees', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Attendance
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/attendance', 'middleware' => ['Auth']], function($router) {
    // Student Attendance
    $router->get('/students', 'AttendanceController', 'studentIndex', ['Permission:view_attendance']);
    $router->get('/students/take', 'AttendanceController', 'takeStudentAttendance', ['Permission:mark_attendance']);
    $router->post('/students', 'AttendanceController', 'storeStudentAttendance', ['Permission:mark_attendance', 'Csrf']);
    $router->get('/students/bulk', 'AttendanceController', 'bulkEntry', ['Permission:bulk_attendance']);
    $router->post('/students/bulk', 'AttendanceController', 'storeBulkAttendance', ['Permission:bulk_attendance', 'Csrf']);
    $router->get('/students/monthly', 'AttendanceController', 'monthlyReport', ['Permission:view_attendance_reports']);
    $router->get('/students/export', 'AttendanceController', 'exportAttendance', ['Permission:export_attendance']);
    
    // Teacher Attendance
    $router->get('/teachers', 'AttendanceController', 'teacherIndex', ['Permission:view_attendance']);
    $router->post('/teachers', 'AttendanceController', 'storeTeacherAttendance', ['Permission:mark_attendance', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Examinations
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/exams', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'ExamController', 'index', ['Permission:view_exams']);
    $router->get('/create', 'ExamController', 'create', ['Permission:create_exams']);
    $router->post('/', 'ExamController', 'store', ['Permission:create_exams', 'Csrf']);
    $router->get('/{id}/edit', 'ExamController', 'edit', ['Permission:edit_exams']);
    $router->put('/{id}', 'ExamController', 'update', ['Permission:edit_exams', 'Csrf']);
    $router->delete('/{id}', 'ExamController', 'delete', ['Permission:delete_exams', 'Csrf']);
    
    // Marks Entry
    $router->get('/{id}/marks', 'ExamController', 'enterMarks', ['Permission:enter_marks']);
    $router->post('/{id}/marks', 'ExamController', 'storeMarks', ['Permission:enter_marks', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Results
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/results', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'ExamController', 'results', ['Permission:view_results']);
    $router->get('/{studentId}/report-card', 'ExamController', 'reportCard', ['Permission:view_report_cards']);
    $router->get('/{studentId}/report-card/print', 'ExamController', 'printReportCard', ['Permission:print_report_cards']);
});

/*
|--------------------------------------------------------------------------
| Finance
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/fees', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'FinanceController', 'index', ['Permission:view_fees']);
    $router->get('/structures', 'FinanceController', 'structures', ['Permission:create_fee_structures']);
    $router->post('/structures', 'FinanceController', 'storeStructure', ['Permission:create_fee_structures', 'Csrf']);
    $router->get('/collect', 'FinanceController', 'collect', ['Permission:collect_fees']);
    $router->post('/collect', 'FinanceController', 'processPayment', ['Permission:collect_fees', 'Csrf']);
    $router->get('/categories', 'FinanceController', 'categories', ['Permission:manage_fee_categories']);
    $router->post('/categories', 'FinanceController', 'storeCategory', ['Permission:manage_fee_categories', 'Csrf']);
});

$router->group(['prefix' => '/expenses', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'FinanceController', 'expenses', ['Permission:manage_expenses']);
    $router->post('/', 'FinanceController', 'storeExpense', ['Permission:manage_expenses', 'Csrf']);
    $router->put('/{id}', 'FinanceController', 'updateExpense', ['Permission:manage_expenses', 'Csrf']);
    $router->delete('/{id}', 'FinanceController', 'deleteExpense', ['Permission:manage_expenses', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Payroll
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/payroll', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'PayrollController', 'index', ['Permission:view_payroll']);
    $router->get('/structures', 'PayrollController', 'structures', ['Permission:manage_salary_structures']);
    $router->post('/structures', 'PayrollController', 'storeStructure', ['Permission:manage_salary_structures', 'Csrf']);
    $router->get('/process', 'PayrollController', 'process', ['Permission:process_payroll']);
    $router->post('/process', 'PayrollController', 'storePayroll', ['Permission:process_payroll', 'Csrf']);
    $router->get('/{id}/payslip', 'PayrollController', 'payslip', ['Permission:generate_payslips']);
    $router->post('/{id}/mark-paid', 'PayrollController', 'markAsPaid', ['Permission:process_payroll']);
});

/*
|--------------------------------------------------------------------------
| Library
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/library', 'middleware' => ['Auth']], function($router) {
    $router->get('/books', 'LibraryController', 'index', ['Permission:view_books']);
    $router->get('/books/add', 'LibraryController', 'create', ['Permission:add_books']);
    $router->post('/books', 'LibraryController', 'store', ['Permission:add_books', 'Csrf']);
    $router->get('/books/{id}/edit', 'LibraryController', 'edit', ['Permission:edit_books']);
    $router->put('/books/{id}', 'LibraryController', 'update', ['Permission:edit_books', 'Csrf']);
    $router->delete('/books/{id}', 'LibraryController', 'delete', ['Permission:delete_books', 'Csrf']);
    
    $router->get('/borrowings', 'LibraryController', 'borrowings', ['Permission:issue_books']);
    $router->post('/issue', 'LibraryController', 'issueBook', ['Permission:issue_books', 'Csrf']);
    $router->post('/return', 'LibraryController', 'returnBook', ['Permission:return_books', 'Csrf']);
    
    $router->get('/fines', 'LibraryController', 'fines', ['Permission:view_fines']);
    $router->post('/fines/{id}/pay', 'LibraryController', 'payFine', ['Permission:manage_fines']);
});

/*
|--------------------------------------------------------------------------
| Transport
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/transport', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'TransportController', 'index', ['Permission:view_vehicles']);
    
    // Vehicles
    $router->get('/vehicles', 'TransportController', 'vehicles', ['Permission:view_vehicles']);
    $router->get('/vehicles/create', 'TransportController', 'createVehicle', ['Permission:manage_vehicles']);
    $router->post('/vehicles', 'TransportController', 'storeVehicle', ['Permission:manage_vehicles', 'Csrf']);
    $router->get('/vehicles/{id}/edit', 'TransportController', 'editVehicle', ['Permission:manage_vehicles']);
    $router->put('/vehicles/{id}', 'TransportController', 'updateVehicle', ['Permission:manage_vehicles', 'Csrf']);
    $router->delete('/vehicles/{id}', 'TransportController', 'deleteVehicle', ['Permission:manage_vehicles', 'Csrf']);
    
    // Routes
    $router->get('/routes', 'TransportController', 'routes', ['Permission:view_routes']);
    $router->get('/routes/create', 'TransportController', 'createRoute', ['Permission:manage_routes']);
    $router->post('/routes', 'TransportController', 'storeRoute', ['Permission:manage_routes', 'Csrf']);
    $router->get('/routes/{id}/edit', 'TransportController', 'editRoute', ['Permission:manage_routes']);
    $router->put('/routes/{id}', 'TransportController', 'updateRoute', ['Permission:manage_routes', 'Csrf']);
    $router->delete('/routes/{id}', 'TransportController', 'deleteRoute', ['Permission:manage_routes', 'Csrf']);
    
    // Stops
    $router->post('/stops', 'TransportController', 'storeStop', ['Permission:manage_routes']);
    $router->put('/stops/{id}', 'TransportController', 'updateStop', ['Permission:manage_routes']);
    $router->delete('/stops/{id}', 'TransportController', 'deleteStop', ['Permission:manage_routes']);
    
    // Student Allocation
    $router->get('/student-allocation', 'TransportController', 'studentAllocation', ['Permission:allocate_transport']);
    $router->post('/allocate-student', 'TransportController', 'allocateStudent', ['Permission:allocate_transport', 'Csrf']);
    $router->post('/deallocate/{id}', 'TransportController', 'deallocateStudent', ['Permission:allocate_transport']);
});

/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/inventory', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'InventoryController', 'index', ['Permission:view_inventory']);
    $router->get('/create', 'InventoryController', 'create', ['Permission:manage_inventory']);
    $router->post('/', 'InventoryController', 'store', ['Permission:manage_inventory', 'Csrf']);
    $router->get('/{id}/edit', 'InventoryController', 'edit', ['Permission:manage_inventory']);
    $router->put('/{id}', 'InventoryController', 'update', ['Permission:manage_inventory', 'Csrf']);
    $router->delete('/{id}', 'InventoryController', 'delete', ['Permission:manage_inventory', 'Csrf']);
    
    // Stock Movement
    $router->post('/{id}/stock-in', 'InventoryController', 'stockIn', ['Permission:add_stock']);
    $router->post('/{id}/stock-out', 'InventoryController', 'stockOut', ['Permission:remove_stock']);
    
    // Categories
    $router->get('/categories', 'InventoryController', 'categories', ['Permission:manage_categories']);
    $router->post('/categories', 'InventoryController', 'storeCategory', ['Permission:manage_categories', 'Csrf']);
    
    // Stock Movement History
    $router->get('/{id}/movements', 'InventoryController', 'stockMovement', ['Permission:view_stock_reports']);
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/notifications', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'NotificationController', 'index', ['Permission:view_notifications']);
    $router->get('/create', 'NotificationController', 'create', ['Permission:send_notifications']);
    $router->post('/', 'NotificationController', 'store', ['Permission:send_notifications', 'Csrf']);
    $router->delete('/{id}', 'NotificationController', 'delete', ['Permission:send_notifications', 'Csrf']);
    $router->get('/{id}/read', 'NotificationController', 'markAsRead', ['Auth']);
    $router->post('/mark-all-read', 'NotificationController', 'markAllAsRead', ['Auth']);
    
    // Templates
    $router->get('/templates', 'NotificationController', 'templates', ['Permission:manage_notification_templates']);
    $router->post('/templates', 'NotificationController', 'storeTemplate', ['Permission:manage_notification_templates', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/reports', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'ReportController', 'index', ['Permission:view_reports']);
    $router->get('/students', 'ReportController', 'studentReport', ['Permission:view_reports']);
    $router->get('/attendance', 'ReportController', 'attendanceReport', ['Permission:view_reports']);
    $router->get('/financial', 'ReportController', 'financialReport', ['Permission:view_financial_reports']);
    $router->get('/exam', 'ReportController', 'examReport', ['Permission:view_reports']);
    $router->get('/payroll', 'ReportController', 'payrollReport', ['Permission:view_reports']);
    $router->get('/library', 'ReportController', 'libraryReport', ['Permission:view_reports']);
    $router->get('/transport', 'ReportController', 'transportReport', ['Permission:view_reports']);
    $router->get('/inventory', 'ReportController', 'inventoryReport', ['Permission:view_reports']);
    
    // Export
    $router->get('/export/{type}', 'ReportController', 'export', ['Permission:export_reports']);
});

/*
|--------------------------------------------------------------------------
| Audit & System Logs
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/audit-logs', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'AuditController', 'index', ['Permission:view_audit_logs']);
    $router->get('/export', 'AuditController', 'export', ['Permission:export_audit_logs']);
});

$router->group(['prefix' => '/system-logs', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'AuditController', 'systemLogs', ['Permission:view_system_logs']);
});

$router->group(['prefix' => '/login-history', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'AuditController', 'loginHistory', ['Permission:view_audit_logs']);
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/settings', 'middleware' => ['Auth']], function($router) {
    $router->get('/', 'SettingController', 'index', ['Permission:view_settings']);
    $router->get('/general', 'SettingController', 'general', ['Permission:manage_general_settings']);
    $router->post('/general', 'SettingController', 'updateGeneral', ['Permission:manage_general_settings', 'Csrf']);
    
    // Backup
    $router->get('/backup', 'SettingController', 'backup', ['Permission:manage_backup']);
    $router->post('/backup/create', 'SettingController', 'createBackup', ['Permission:manage_backup', 'Csrf']);
    $router->get('/backup/download/{id}', 'SettingController', 'downloadBackup', ['Permission:manage_backup']);
    $router->post('/backup/delete/{id}', 'SettingController', 'deleteBackup', ['Permission:manage_backup']);
    $router->post('/backup/restore/{id}', 'SettingController', 'restoreBackup', ['Permission:manage_backup']);
    $router->post('/backup/schedule', 'SettingController', 'saveSchedule', ['Permission:manage_backup', 'Csrf']);
});

/*
|--------------------------------------------------------------------------
| Search (Global)
|--------------------------------------------------------------------------
*/
$router->get('/search', 'SearchController', 'index', ['Auth']);

/*
|--------------------------------------------------------------------------
| API Data Endpoints (for AJAX)
|--------------------------------------------------------------------------
*/
$router->get('/api/sections/{classId}', 'ApiController', 'getSections', ['Auth']);
$router->get('/api/subjects/{classId}', 'ApiController', 'getSubjects', ['Auth']);
$router->get('/api/route-stops/{routeId}', 'ApiController', 'getRouteStops', ['Auth']);
$router->get('/api/search-students', 'ApiController', 'searchStudents', ['Auth']);
$router->get('/api/search-users', 'ApiController', 'searchUsers', ['Auth']);
$router->get('/api/search-books', 'ApiController', 'searchBooks', ['Auth']);
$router->get('/api/notifications/unread', 'ApiController', 'getUnreadNotifications', ['Auth']);
$router->get('/api/notifications/count', 'ApiController', 'getUnreadCount', ['Auth']);