<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class CommonController extends Controller
{
    /**
     * Get branches list
     * GET /api/v1/branches
     */
    public function branches()
    {
        $branches = $this->db->select(
            "SELECT id, branch_code, name, city, is_active FROM branches WHERE is_active = 1 ORDER BY name"
        );
        $this->json(['success' => true, 'data' => $branches]);
    }

    /**
     * Get classes list
     * GET /api/v1/classes
     */
    public function classes()
    {
        $branchId = $this->request->get('branch_id') ?? $this->session->get('branch_id');

        $sql = "SELECT id, class_name, class_level, class_code FROM classes WHERE is_active = 1";
        $params = [];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY class_level";
        $classes = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $classes]);
    }

    /**
     * Get sections for a class
     * GET /api/v1/sections/{classId}
     */
    public function sections($classId)
    {
        $sections = $this->db->select(
            "SELECT id, section_name, section_code, capacity FROM sections WHERE class_id = ? AND is_active = 1 ORDER BY section_name",
            [$classId]
        );
        $this->json(['success' => true, 'data' => $sections]);
    }

    /**
     * Get subjects for a class
     * GET /api/v1/subjects/{classId}
     */
    public function subjects($classId)
    {
        $subjects = $this->db->select(
            "SELECT s.id, s.subject_name, s.subject_code, s.credit_hours
             FROM class_subjects cs 
             JOIN subjects s ON cs.subject_id = s.id 
             WHERE cs.class_id = ? AND cs.is_active = 1 AND s.is_active = 1
             ORDER BY s.subject_name",
            [$classId]
        );
        $this->json(['success' => true, 'data' => $subjects]);
    }

    /**
     * Get academic years
     * GET /api/v1/academic-years
     */
    public function academicYears()
    {
        $branchId = $this->request->get('branch_id') ?? $this->session->get('branch_id');

        $sql = "SELECT id, year_name, start_date, end_date, is_current FROM academic_years WHERE status = 'active'";
        $params = [];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY start_date DESC";
        $years = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $years]);
    }

    /**
     * Get semesters
     * GET /api/v1/semesters
     */
    public function semesters()
    {
        $academicYearId = $this->request->get('academic_year_id', '');

        $sql = "SELECT id, semester_name, semester_number, start_date, end_date, is_current FROM semesters WHERE 1=1";
        $params = [];

        if ($academicYearId) {
            $sql .= " AND academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $sql .= " ORDER BY semester_number";
        $semesters = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $semesters]);
    }

    /**
     * Get departments
     * GET /api/v1/departments
     */
    public function departments()
    {
        $branchId = $this->request->get('branch_id') ?? $this->session->get('branch_id');

        $sql = "SELECT id, name, department_code FROM departments WHERE is_active = 1";
        $params = [];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY name";
        $departments = $this->db->select($sql, $params);

        $this->json(['success' => true, 'data' => $departments]);
    }

    /**
     * Get roles
     * GET /api/v1/roles
     */
    public function roles()
    {
        $roles = $this->db->select("SELECT id, name, display_name FROM roles ORDER BY display_name");
        $this->json(['success' => true, 'data' => $roles]);
    }

    /**
     * Get teachers list (simplified)
     * GET /api/v1/teachers/list
     */
    public function teachersList()
    {
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name, t.teacher_code
             FROM teachers t JOIN users u ON t.user_id = u.id 
             WHERE u.is_active = 1 ORDER BY u.last_name, u.first_name"
        );
        $this->json(['success' => true, 'data' => $teachers]);
    }
}