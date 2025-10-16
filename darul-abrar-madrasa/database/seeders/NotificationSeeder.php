<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\NotificationTrigger;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTemplates();
        $this->seedTriggers();
    }

    /**
     * Seed notification templates.
     */
    protected function seedTemplates(): void
    {
        $templates = [
            // Low Attendance Templates
            [
                'type' => 'low_attendance',
                'channel' => 'email',
                'name' => 'Low Attendance Email',
                'subject' => 'Low Attendance Alert for {{student_name}}',
                'body' => "Assalamu alaikum {{guardian_name}},\n\nThis is to inform you that {{student_name}}'s attendance has fallen below the required threshold.\n\nCurrent Attendance Rate: {{attendance_rate}}%\nRequired Minimum: {{threshold}}%\nAbsent Days (Last {{period_days}} days): {{absent_days}}\nClass: {{class_name}}\n\nRegular attendance is crucial for academic success. Please ensure {{student_name}} attends classes regularly.\n\nJazakAllah khair,\nDarul Abrar Madrasa Administration",
                'available_variables' => ['student_name', 'guardian_name', 'attendance_rate', 'threshold', 'absent_days', 'period_days', 'class_name'],
                'is_active' => true,
            ],
            [
                'type' => 'low_attendance',
                'channel' => 'sms',
                'name' => 'Low Attendance SMS',
                'subject' => null,
                'body' => "Alert: {{student_name}}'s attendance is {{attendance_rate}}% (below {{threshold}}%). Please ensure regular attendance. -Darul Abrar",
                'available_variables' => ['student_name', 'attendance_rate', 'threshold'],
                'is_active' => true,
            ],

            // Poor Performance Templates
            [
                'type' => 'poor_performance',
                'channel' => 'email',
                'name' => 'Poor Performance Email',
                'subject' => 'Academic Performance Alert for {{student_name}}',
                'body' => "Assalamu alaikum {{guardian_name}},\n\nWe would like to inform you about {{student_name}}'s recent academic performance.\n\nCurrent GPA: {{gpa}}\nRequired Minimum: {{threshold}}\nClass: {{class_name}}\nWeak Subjects: {{weak_subjects}}\n\n{{recommendations}}\n\nWe recommend scheduling a meeting with the class teacher to discuss improvement strategies.\n\nJazakAllah khair,\nDarul Abrar Madrasa Administration",
                'available_variables' => ['student_name', 'guardian_name', 'gpa', 'threshold', 'class_name', 'weak_subjects', 'recommendations'],
                'is_active' => true,
            ],
            [
                'type' => 'poor_performance',
                'channel' => 'sms',
                'name' => 'Poor Performance SMS',
                'subject' => null,
                'body' => "Alert: {{student_name}}'s GPA is {{gpa}} (below {{threshold}}). Please contact class teacher. -Darul Abrar",
                'available_variables' => ['student_name', 'gpa', 'threshold'],
                'is_active' => true,
            ],

            // Fee Due Templates
            [
                'type' => 'fee_due',
                'channel' => 'email',
                'name' => 'Fee Due Email',
                'subject' => 'Fee Payment Reminder',
                'body' => "Assalamu alaikum {{guardian_name}},\n\n{{message}}\n\nTotal Pending: ৳ {{total_pending}}\n\nPlease pay at your earliest convenience.\n\nJazakAllah khair,\nDarul Abrar Madrasa Administration",
                'available_variables' => ['guardian_name', 'message', 'total_pending', 'due_amount'],
                'is_active' => true,
            ],
            [
                'type' => 'fee_due',
                'channel' => 'sms',
                'name' => 'Fee Due SMS',
                'subject' => null,
                'body' => "Fee reminder: Total pending ৳ {{due_amount}}. Please pay soon. -Darul Abrar",
                'available_variables' => ['due_amount'],
                'is_active' => true,
            ],

            // Exam Schedule Templates
            [
                'type' => 'exam_schedule',
                'channel' => 'email',
                'name' => 'Exam Schedule Email',
                'subject' => 'Upcoming Exam: {{exam_name}}',
                'body' => "Assalamu alaikum {{guardian_name}},\n\nThis is to inform you about an upcoming exam for {{student_name}}.\n\nExam: {{exam_name}}\nClass: {{class_name}}\nStart Date: {{start_date}}\nEnd Date: {{end_date}}\nSubjects: {{subjects}}\nDays Until Exam: {{days_until}}\n\nPlease ensure {{student_name}} is well-prepared.\n\nJazakAllah khair,\nDarul Abrar Madrasa Administration",
                'available_variables' => ['student_name', 'guardian_name', 'exam_name', 'class_name', 'start_date', 'end_date', 'subjects', 'days_until'],
                'is_active' => true,
            ],
            [
                'type' => 'exam_schedule',
                'channel' => 'sms',
                'name' => 'Exam Schedule SMS',
                'subject' => null,
                'body' => "Exam Alert: {{exam_name}} for {{student_name}} on {{start_date}}. Subjects: {{subjects}}. -Darul Abrar",
                'available_variables' => ['student_name', 'exam_name', 'start_date', 'subjects'],
                'is_active' => true,
            ],

            // Result Published Templates
            [
                'type' => 'result_published',
                'channel' => 'email',
                'name' => 'Result Published Email',
                'subject' => 'Exam Results Published for {{student_name}}',
                'body' => "Assalamu alaikum {{guardian_name}},\n\nThe results for {{exam_name}} have been published.\n\nStudent: {{student_name}}\nClass: {{class_name}}\nGPA: {{gpa}}\nStatus: {{status}}\nTotal Marks: {{total_marks}}\nObtained Marks: {{obtained_marks}}\n\nYou can view detailed results in the guardian portal.\n\nJazakAllah khair,\nDarul Abrar Madrasa Administration",
                'available_variables' => ['student_name', 'guardian_name', 'exam_name', 'class_name', 'gpa', 'status', 'total_marks', 'obtained_marks'],
                'is_active' => true,
            ],
            [
                'type' => 'result_published',
                'channel' => 'sms',
                'name' => 'Result Published SMS',
                'subject' => null,
                'body' => "Results published for {{student_name}} - {{exam_name}}. GPA: {{gpa}}, Status: {{status}}. Check portal for details. -Darul Abrar",
                'available_variables' => ['student_name', 'exam_name', 'gpa', 'status'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'type' => $template['type'],
                    'channel' => $template['channel'],
                ],
                $template
            );
        }

        $this->command->info('Notification templates seeded successfully.');
    }

    /**
     * Seed notification triggers.
     */
    protected function seedTriggers(): void
    {
        $triggers = [
            [
                'type' => 'low_attendance',
                'name' => 'Low Attendance Alert',
                'description' => 'Triggered when student attendance falls below threshold',
                'is_enabled' => true,
                'conditions' => [
                    'attendance_threshold' => 75,
                    'check_period_days' => 30,
                ],
                'frequency' => 'daily',
            ],
            [
                'type' => 'poor_performance',
                'name' => 'Poor Performance Alert',
                'description' => 'Triggered when student GPA falls below threshold',
                'is_enabled' => true,
                'conditions' => [
                    'gpa_threshold' => 2.5,
                ],
                'frequency' => 'weekly',
            ],
            [
                'type' => 'fee_due',
                'name' => 'Fee Due Reminder',
                'description' => 'Triggered for upcoming and overdue fee payments',
                'is_enabled' => true,
                'conditions' => [
                    'days_before_due' => 7,
                ],
                'frequency' => 'daily',
            ],
            [
                'type' => 'exam_schedule',
                'name' => 'Exam Schedule Notification',
                'description' => 'Triggered for upcoming exams',
                'is_enabled' => true,
                'conditions' => [
                    'days_before_exam' => 7,
                ],
                'frequency' => 'daily',
            ],
            [
                'type' => 'result_published',
                'name' => 'Result Publication Alert',
                'description' => 'Triggered when exam results are published',
                'is_enabled' => true,
                'conditions' => [],
                'frequency' => 'immediate',
            ],
        ];

        foreach ($triggers as $trigger) {
            NotificationTrigger::updateOrCreate(
                ['type' => $trigger['type']],
                $trigger
            );
        }

        $this->command->info('Notification triggers seeded successfully.');
    }
}
