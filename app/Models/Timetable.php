<?php
namespace App\Models;

/**
 * Timetable Model
 * مدیریت برنامه هفتگی
 */
class Timetable extends Model
{
    protected $table = 'timetables';
    protected $primaryKey = 'id';
    protected $timestamps = false;
    
    protected $fillable = [
        'section_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room_number',
        'semester_id',
    ];
    
    /**
     * Get timetable for section
     */
    public function getBySection($sectionId, $semesterId = null)
    {
        $sql = "SELECT tt.*, s.subject_name, s.subject_code,
                       CONCAT(u.first_name, ' ', u.last_name) as teacher_name
                FROM timetables tt 
                JOIN subjects s ON tt.subject_id = s.id 
                JOIN teachers t ON tt.teacher_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE tt.section_id = ?";
        
        $params = [$sectionId];
        
        if ($semesterId) {
            $sql .= " AND tt.semester_id = ?";
            $params[] = $semesterId;
        }
        
        $sql .= " ORDER BY FIELD(tt.day_of_week, 'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'), 
                          tt.start_time";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get teacher timetable
     */
    public function getByTeacher($teacherId, $semesterId = null)
    {
        $sql = "SELECT tt.*, sec.section_name, s.subject_name, c.class_name
                FROM timetables tt 
                JOIN sections sec ON tt.section_id = sec.id 
                JOIN subjects s ON tt.subject_id = s.id 
                JOIN classes c ON sec.class_id = c.id 
                WHERE tt.teacher_id = ?";
        
        $params = [$teacherId];
        
        if ($semesterId) {
            $sql .= " AND tt.semester_id = ?";
            $params[] = $semesterId;
        }
        
        $sql .= " ORDER BY FIELD(tt.day_of_week, 'Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'), 
                          tt.start_time";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Check for timetable conflicts
     */
    public function checkConflicts($data)
    {
        $conflicts = [];
        
        // Check teacher conflict
        $teacherConflict = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables 
             WHERE teacher_id = ? AND day_of_week = ? 
             AND semester_id = ?
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
             AND id != ?",
            [
                $data['teacher_id'], $data['day_of_week'], $data['semester_id'],
                $data['start_time'], $data['end_time'],
                $data['start_time'], $data['end_time'],
                $data['id'] ?? 0
            ]
        );
        
        if ($teacherConflict['count'] > 0) {
            $conflicts[] = 'teacher';
        }
        
        // Check section conflict
        $sectionConflict = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM timetables 
             WHERE section_id = ? AND day_of_week = ? 
             AND semester_id = ?
             AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
             AND id != ?",
            [
                $data['section_id'], $data['day_of_week'], $data['semester_id'],
                $data['start_time'], $data['end_time'],
                $data['start_time'], $data['end_time'],
                $data['id'] ?? 0
            ]
        );
        
        if ($sectionConflict['count'] > 0) {
            $conflicts[] = 'section';
        }
        
        // Check room conflict
        if (!empty($data['room_number'])) {
            $roomConflict = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM timetables 
                 WHERE room_number = ? AND day_of_week = ? 
                 AND semester_id = ?
                 AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
                 AND id != ?",
                [
                    $data['room_number'], $data['day_of_week'], $data['semester_id'],
                    $data['start_time'], $data['end_time'],
                    $data['start_time'], $data['end_time'],
                    $data['id'] ?? 0
                ]
            );
            
            if ($roomConflict['count'] > 0) {
                $conflicts[] = 'room';
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Get weekly view organized by days
     */
    public function getWeeklyView($sectionId, $semesterId = null)
    {
        $timetables = $this->getBySection($sectionId, $semesterId);
        
        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $organized = [];
        
        foreach ($days as $day) {
            $organized[$day] = array_filter($timetables, function($t) use ($day) {
                return $t['day_of_week'] === $day;
            });
        }
        
        return $organized;
    }
    
    /**
     * Add period to timetable
     */
    public function addPeriod($data)
    {
        // Check conflicts first
        $conflicts = $this->checkConflicts($data);
        
        $periodId = $this->db->insert('timetables', [
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
        
        return [
            'id' => $periodId,
            'conflicts' => $conflicts,
        ];
    }
}