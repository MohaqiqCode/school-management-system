<?php
/**
 * School Management System - API Routes
 * مسیرهای REST API
 */

use App\Core\Application;

$router = Application::getInstance()->getRouter();

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/api/v1', 'namespace' => 'Api'], function($router) {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    $router->post('/auth/login', 'AuthController', 'login', ['Throttle:5,15']);
    $router->post('/auth/register', 'AuthController', 'register', ['Throttle:3,60']);
    $router->post('/auth/forgot-password', 'AuthController', 'forgotPassword', ['Throttle:3,15']);
    $router->post('/auth/reset-password', 'AuthController', 'resetPassword');
    $router->post('/auth/refresh-token', 'AuthController', 'refreshToken');
    $router->get('/auth/me', 'AuthController', 'me', ['ApiAuth']);
    $router->post('/auth/logout', 'AuthController', 'logout', ['ApiAuth']);

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/users', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/', 'UserController', 'index');
        $router->get('/{id}', 'UserController', 'show');
        $router->post('/', 'UserController', 'store');
        $router->put('/{id}', 'UserController', 'update');
        $router->delete('/{id}', 'UserController', 'delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/students', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/', 'StudentController', 'index');
        $router->get('/{id}', 'StudentController', 'show');
        $router->post('/', 'StudentController', 'store');
        $router->put('/{id}', 'StudentController', 'update');
        $router->delete('/{id}', 'StudentController', 'delete');
        
        // Attendance
        $router->get('/{id}/attendance', 'StudentController', 'attendance');
        $router->get('/{id}/attendance/stats', 'StudentController', 'attendanceStats');
        
        // Results
        $router->get('/{id}/results', 'StudentController', 'results');
        $router->get('/{id}/report-card', 'StudentController', 'reportCard');
        
        // Fees
        $router->get('/{id}/fees', 'StudentController', 'fees');
        $router->post('/{id}/fees/pay', 'StudentController', 'payFee');
        
        // Documents
        $router->get('/{id}/documents', 'StudentController', 'documents');
        $router->post('/{id}/documents', 'StudentController', 'uploadDocument');
    });

    /*
    |--------------------------------------------------------------------------
    | Teachers
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/teachers', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/', 'TeacherController', 'index');
        $router->get('/{id}', 'TeacherController', 'show');
        $router->post('/', 'TeacherController', 'store');
        $router->put('/{id}', 'TeacherController', 'update');
        $router->delete('/{id}', 'TeacherController', 'delete');
        
        $router->get('/{id}/timetable', 'TeacherController', 'timetable');
        $router->get('/{id}/workload', 'TeacherController', 'workload');
        $router->get('/{id}/attendance', 'TeacherController', 'attendance');
    });

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/attendance', 'middleware' => ['ApiAuth']], function($router) {
        // Student Attendance
        $router->get('/students', 'AttendanceController', 'studentIndex');
        $router->post('/students', 'AttendanceController', 'storeStudent');
        $router->post('/students/bulk', 'AttendanceController', 'bulkStoreStudent');
        $router->get('/students/report', 'AttendanceController', 'studentReport');
        
        // Teacher Attendance
        $router->get('/teachers', 'AttendanceController', 'teacherIndex');
        $router->post('/teachers', 'AttendanceController', 'storeTeacher');
        $router->post('/teachers/bulk', 'AttendanceController', 'bulkStoreTeacher');
    });

    /*
    |--------------------------------------------------------------------------
    | Exams & Results
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/exams', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/', 'ExamController', 'index');
        $router->get('/{id}', 'ExamController', 'show');
        $router->post('/', 'ExamController', 'store');
        $router->put('/{id}', 'ExamController', 'update');
        $router->delete('/{id}', 'ExamController', 'delete');
        
        $router->get('/{id}/results', 'ExamController', 'results');
        $router->post('/{id}/marks', 'ExamController', 'storeMarks');
    });

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/finance', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/fees', 'FinanceController', 'fees');
        $router->get('/fees/{id}', 'FinanceController', 'feeDetail');
        $router->post('/fees/pay', 'FinanceController', 'processPayment');
        $router->get('/fees/report', 'FinanceController', 'feeReport');
        
        $router->get('/expenses', 'FinanceController', 'expenses');
        $router->post('/expenses', 'FinanceController', 'storeExpense');
    });

    /*
    |--------------------------------------------------------------------------
    | Library
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/library', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/books', 'LibraryController', 'index');
        $router->get('/books/{id}', 'LibraryController', 'show');
        $router->post('/books', 'LibraryController', 'store');
        $router->get('/books/search', 'LibraryController', 'search');
        
        $router->get('/borrowings', 'LibraryController', 'borrowings');
        $router->post('/borrow', 'LibraryController', 'borrowBook');
        $router->post('/return', 'LibraryController', 'returnBook');
        $router->get('/fines', 'LibraryController', 'fines');
    });

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/transport', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/vehicles', 'TransportController', 'vehicles');
        $router->get('/routes', 'TransportController', 'routes');
        $router->get('/routes/{id}/stops', 'TransportController', 'stops');
        $router->get('/allocations', 'TransportController', 'allocations');
        $router->post('/allocate', 'TransportController', 'allocateStudent');
        $router->post('/deallocate/{id}', 'TransportController', 'deallocateStudent');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/notifications', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/', 'NotificationController', 'index');
        $router->get('/unread', 'NotificationController', 'unread');
        $router->get('/count', 'NotificationController', 'count');
        $router->post('/{id}/read', 'NotificationController', 'markAsRead');
        $router->post('/mark-all-read', 'NotificationController', 'markAllAsRead');
        $router->post('/', 'NotificationController', 'store');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/reports', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/students', 'ReportController', 'students');
        $router->get('/attendance', 'ReportController', 'attendance');
        $router->get('/financial', 'ReportController', 'financial');
        $router->get('/exams', 'ReportController', 'exams');
        $router->get('/payroll', 'ReportController', 'payroll');
        $router->get('/library', 'ReportController', 'library');
        
        $router->get('/export/{type}', 'ReportController', 'export');
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => '/dashboard', 'middleware' => ['ApiAuth']], function($router) {
        $router->get('/stats', 'DashboardController', 'stats');
        $router->get('/charts/revenue', 'DashboardController', 'revenueChart');
        $router->get('/charts/attendance', 'DashboardController', 'attendanceChart');
        $router->get('/recent-activities', 'DashboardController', 'recentActivities');
    });

    /*
    |--------------------------------------------------------------------------
    | Common Data Endpoints
    |--------------------------------------------------------------------------
    */
    $router->group(['middleware' => ['ApiAuth']], function($router) {
        $router->get('/branches', 'CommonController', 'branches');
        $router->get('/classes', 'CommonController', 'classes');
        $router->get('/sections/{classId}', 'CommonController', 'sections');
        $router->get('/subjects/{classId}', 'CommonController', 'subjects');
        $router->get('/academic-years', 'CommonController', 'academicYears');
        $router->get('/semesters', 'CommonController', 'semesters');
        $router->get('/departments', 'CommonController', 'departments');
        $router->get('/roles', 'CommonController', 'roles');
        $router->get('/teachers/list', 'CommonController', 'teachersList');
    });

    /*
    |--------------------------------------------------------------------------
    | File Upload
    |--------------------------------------------------------------------------
    */
    $router->post('/upload', 'FileController', 'upload', ['ApiAuth']);
    $router->post('/upload/multiple', 'FileController', 'uploadMultiple', ['ApiAuth']);
    $router->delete('/upload/{id}', 'FileController', 'delete', ['ApiAuth']);

    /*
    |--------------------------------------------------------------------------
    | System
    |--------------------------------------------------------------------------
    */
    $router->get('/health', 'SystemController', 'health');
    $router->get('/ping', 'SystemController', 'ping');

});

/*
|--------------------------------------------------------------------------
| Webhook Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => '/webhooks'], function($router) {
    $router->post('/payment/callback/{gateway}', 'WebhookController', 'paymentCallback');
    $router->post('/sms/callback/{provider}', 'WebhookController', 'smsCallback');
    $router->get('/email/track/{id}', 'WebhookController', 'emailTrack');
});

/*
|--------------------------------------------------------------------------
| API Documentation (Optional)
|--------------------------------------------------------------------------
*/
$router->get('/api/docs', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'api' => 'School Management System API',
        'version' => '1.0.0',
        'base_url' => config('app_url') . '/api/v1',
        'endpoints' => [
            'auth' => [
                'POST /auth/login',
                'POST /auth/register',
                'POST /auth/forgot-password',
                'POST /auth/reset-password',
                'GET /auth/me',
                'POST /auth/logout',
            ],
            'students' => ['GET', 'POST', 'PUT', 'DELETE'],
            'teachers' => ['GET', 'POST', 'PUT', 'DELETE'],
            'attendance' => ['GET', 'POST'],
            'exams' => ['GET', 'POST', 'PUT', 'DELETE'],
            'finance' => ['GET', 'POST'],
            'library' => ['GET', 'POST'],
            'transport' => ['GET', 'POST'],
            'notifications' => ['GET', 'POST'],
            'reports' => ['GET'],
        ]
    ]);
});