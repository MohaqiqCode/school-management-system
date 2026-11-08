<?php
namespace App\Controllers;

use App\Core\Controller;

class TimetableController extends Controller
{
    public function index()
    {
        $classId = $_GET['class_id'] ?? '';
        $sectionId = $_GET['section_id'] ?? '';
        $teacherId = $_GET['teacher_id'] ?? '';
        $semesterId = $_GET['semester_id'] ?? '';
        
        $sql = "SELECT tt.*, sec.section_name, s.subject_name, s.subject_code,
                       c.class_name, c.class_level, sem.semester_name,
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                FROM timetables tt 
                JOIN sections sec ON tt.section_id = sec.id 
                JOIN subjects s ON tt.subject_id = s.id 
                JOIN classes c ON sec.class_id = c.id 
                JOIN semesters sem ON tt.semester_id = sem.id 
                JOIN teachers t ON tt.teacher_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($classId) {
            $sql .= " AND c.id = ?";
            $params[] = $classId;
        }
        
        if ($sectionId) {
            $sql .= " AND sec.id = ?";
            $params[] = $sectionId;
        }
        
        if ($teacherId) {
            $sql .= " AND tt.teacher_id = ?";
            $params[] = $teacherId;
        }
        
        if ($semesterId) {
            $sql .= " AND tt.semester_id = ?";
            $params[] = $semesterId;
        }
        
        $sql .= " ORDER BY FIELD(tt.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), tt.start_time";
        $timetables = $this->db->select($sql, $params);
        
        // Get filter data
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name"
        );
        $semesters = $this->db->select("SELECT id, semester_name FROM semesters WHERE is_current = 1 ORDER BY semester_number");
        
        $this->view('timetable/index', [
            'title' => 'برنامه هفتگی',
            'timetables' => $timetables,
            'classes' => $classes,
            'teachers' => $teachers,
            'semesters' => $semesters,
            'selectedClass' => $classId,
            'selectedSection' => $sectionId,
            'selectedTeacher' => $teacherId,
            'selectedSemester' => $semesterId
        ]);
    }
    
    public function create()
    {
        $classes = $this->db->select("SELECT id, class_name FROM classes WHERE is_active = 1 ORDER BY class_level");
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name"
        );
        $semesters = $this->db->select("SELECT id, semester_name, academic_year_id FROM semesters WHERE is_current = 1 ORDER BY semester_number");
        
        $this->view('timetable/create', [
            'title' => 'ایجاد برنامه جدید',
            'classes' => $classes,
            'teachers' => $teachers,
            'semesters' => $semesters
        ]);
    }
    
    public function store()
    {
        $data = $_POST;
        
        // Validate
        $errors = [];
        
        if (empty($data['section_id'])) {
            $errors['section_id'] = 'انتخاب بخش الزامی است';
        }
        
        if (empty($data['subject_id'])) {
            $errors['subject_id'] = 'انتخاب درس الزامی است';
        }
        
        if (empty($data['teacher_id'])) {
            $errors['teacher_id'] = 'انتخاب معلم الزامی است';
        }
        
        if (empty($data['day_of_week'])) {
            $errors['day_of_week'] = 'انتخاب روز هفته الزامی است';
        }
        
        if (empty($data['start_time'])) {
            $errors['start_time'] = 'زمان شروع الزامی است';
        }
        
        if (empty($data['end_time'])) {
            $errors['end_time'] = 'زمان پایان الزامی است';
        }
        
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
                $errors['end_time'] = 'زمان پایان باید بعد از زمان شروع باشد';
            }
        }
        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $data);
            $this->redirect('/timetable/create');
            return;
        }
        
        // Check for conflicts
        $conflicts = $this->checkTimetableConflicts($data);
        
        if (!empty($conflicts)) {
            $this->session->flash('warning', 'تداخل زمانی تشخیص داده شد: ' . implode(', ', $conflicts));
        }
        
        $this->db->insert('timetables', [
            'section_id' => $data['section_id'],
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room_number' => $data['room_number'] ?? null,
            'semester_id' => $data['semester_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logActivity('timetable', 'create', "برنامه جدید برای روز {$data['day_of_week']} ایجاد شد");
        
        $this->session->flash('success', 'برنامه با موفقیت ایجاد شد');
        $this->redirect('/timetable');
    }
    
    public function edit($id)
    {
        $timetable = $this->db->selectOne(
            "SELECT tt.*, c.id as class_id 
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN classes c ON sec.class_id = c.id 
             WHERE tt.id = ?",
            [$id]
        );
        
        if (!$timetable) {
            $this->session->flash('error', 'برنامه مورد نظر یافت نشد');
            $this->redirect('/timetable');
            return;
        }
        
        $sections = $this->db->select("SELECT id, section_name FROM sections WHERE class_id = ? AND is_active = 1", [$timetable['class_id']]);
        $subjects = $this->db->select(
            "SELECT s.id, s.subject_name FROM class_subjects cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.class_id = ? AND cs.is_active = 1",
            [$timetable['class_id']]
        );
        $teachers = $this->db->select(
            "SELECT t.id, u.first_name, u.last_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.is_active = 1 ORDER BY u.last_name"
        );
        $semesters = $this->db->select("SELECT id, semester_name FROM semesters ORDER BY semester_number");
        
        $this->view('timetable/edit', [
            'title' => 'ویرایش برنامه',
            'timetable' => $timetable,
            'sections' => $sections,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'semesters' => $semesters
        ]);
    }
    
    public function update($id)
    {
        $timetable = $this->db->selectOne("SELECT * FROM timetables WHERE id = ?", [$id]);
        
        if (!$timetable) {
            $this->session->flash('error', 'برنامه یافت نشد');
            $this->redirect('/timetable');
            return;
        }
        
        $data = $_POST;
        
        $this->db->update('timetables', [
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room_number' => $data['room_number'] ?? null
        ], 'id = ?', [$id]);
        
        $this->logActivity('timetable', 'update', "برنامه {$id} به‌روزرسانی شد");
        
        $this->session->flash('success', 'برنامه به‌روزرسانی شد');
        $this->redirect('/timetable');
    }
    
    public function delete($id)
    {
        $timetable = $this->db->selectOne("SELECT * FROM timetables WHERE id = ?", [$id]);
        
        if (!$timetable) {
            $this->json(['success' => false, 'message' => 'برنامه یافت نشد'], 404);
            return;
        }
        
        $this->db->delete('timetables', 'id = ?', [$id]);
        
        $this->logActivity('timetable', 'delete', "برنامه {$id} حذف شد");
        
        $this->json(['success' => true, 'message' => 'برنامه حذف شد']);
    }
    
    public function getSections($classId)
    {
        $sections = $this->db->select(
            "SELECT id, section_name FROM sections WHERE class_id = ? AND is_active = 1",
            [$classId]
        );
        
        $this->json(['success' => true, 'data' => $sections]);
    }
    
    public function getSubjects($classId)
    {
        $subjects = $this->db->select(
            "SELECT s.id, s.subject_name, s.subject_code 
             FROM class_subjects cs 
             JOIN subjects s ON cs.subject_id = s.id 
             WHERE cs.class_id = ? AND cs.is_active = 1",
            [$classId]
        );
        
        $this->json(['success' => true, 'data' => $subjects]);
    }
    
    public function viewByClass($classId, $sectionId = null)
    {
        $timetables = $this->db->select(
            "SELECT tt.*, s.subject_name, CONCAT(u.first_name, ' ', u.last_name) as teacher_name,
                    sec.section_name
             FROM timetables tt 
             JOIN sections sec ON tt.section_id = sec.id 
             JOIN subjects s ON tt.subject_id = s.id 
             JOIN teachers t ON tt.teacher_id = t.id
             JOIN users u ON t.user_id = u.id
             WHERE sec.class_id = ?" . ($sectionId ? " AND sec.id = ?" : "") . "
             ORDER BY FIELD(tt.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), tt.start_time",
            $sectionId ? [$classId, $sectionId] : [$classId]
        );
        
        // Organize by day
        $organized = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            $organized[$day] = array_filter($timetables, function($t) use ($day) {
                return $t['day_of_week'] === $day;
            });
        }
        
        $this->view('timetable/view_by_class', [
            'title' => 'برنامه هفتگی کلاس',
            'timetables' => $organized,
            'days' => $days
        ]);
    }
    
    private function checkTimetableConflicts($data)
    {
        $conflicts = [];
        
        // Check teacher conflict
        $teacherConflict = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables 
             WHERE teacher_id = ? AND day_of_week = ? 
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?) OR (start_time <= ? AND end_time >= ?))",
            [$data['teacher_id'], $data['day_of_week'], 
             $data['start_time'], $data['end_time'],
             $data['start_time'], $data['end_time'],
             $data['start_time'], $data['end_time']]
        );
        
        if ($teacherConflict['count'] > 0) {
            $conflicts[] = 'تداخل زمانی برای معلم';
        }
        
        // Check section conflict
        $sectionConflict = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables 
             WHERE section_id = ? AND day_of_week = ? 
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))",
            [$data['section_id'], $data['day_of_week'], 
             $data['start_time'], $data['end_time'],
             $data['start_time'], $data['end_time']]
        );
        
        if ($sectionConflict['count'] > 0) {
            $conflicts[] = 'تداخل زمانی برای بخش';
        }
        
        // Check room conflict
        if (!empty($data['room_number'])) {
            $roomConflict = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM timetables 
                 WHERE room_number = ? AND day_of_week = ? 
                 AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))",
                [$data['room_number'], $data['day_of_week'], 
                 $data['start_time'], $data['end_time'],
                 $data['start_time'], $data['end_time']]
            );
            
            if ($roomConflict['count'] > 0) {
                $conflicts[] = 'تداخل زمانی برای کلاس درس';
            }
        }
        
        return $conflicts;
    }
    
    private function logActivity($module, $action, $description)
    {
        $userId = $this->session->get('user_id');
        
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}