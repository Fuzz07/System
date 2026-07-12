<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\SchoolYear;
use App\Models\Budget;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\Expense;
use App\Models\Feedback;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Seed School Years
        SchoolYear::create(['label' => '2023-2024', 'is_active' => false]);
        SchoolYear::create(['label' => '2024-2025', 'is_active' => false]);
        $activeSy = SchoolYear::create(['label' => '2025-2026', 'is_active' => true]);

        // 2. Seed Users
        $admin = User::create([
            'fullname' => 'System Administrator',
            'email' => 'admin@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'admin',
            'department' => 'Administration',
            'status' => 'active'
        ]);

        $treasurer = User::create([
            'fullname' => 'SSC Treasurer',
            'email' => 'treasurer@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'treasurer',
            'department' => 'Finance',
            'status' => 'active'
        ]);

        $officer = User::create([
            'fullname' => 'SSC Officer',
            'email' => 'officer@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'officer',
            'department' => 'Events',
            'status' => 'active'
        ]);

        $student = User::create([
            'first_name' => 'Juan',
            'last_name' => 'dela Cruz',
            'fullname' => 'Juan dela Cruz',
            'email' => 'student@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'student',
            'department' => 'BSIT',
            'student_id' => '2026-0001',
            'age' => 20,
            'year_level' => '3rd Year',
            'status' => 'active'
        ]);

        // Seed Deans
        User::create([
            'first_name' => 'IT',
            'last_name' => 'Dean',
            'fullname' => 'IT Department Dean',
            'email' => 'dean.it@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'dean',
            'department' => 'BSIT',
            'status' => 'active'
        ]);

        User::create([
            'first_name' => 'BA',
            'last_name' => 'Dean',
            'fullname' => 'BA Department Dean',
            'email' => 'dean.ba@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'dean',
            'department' => 'BSBA',
            'status' => 'active'
        ]);

        User::create([
            'first_name' => 'HM',
            'last_name' => 'Dean',
            'fullname' => 'HM Department Dean',
            'email' => 'dean.hm@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'dean',
            'department' => 'BSHM',
            'status' => 'active'
        ]);

        User::create([
            'first_name' => 'Education',
            'last_name' => 'Dean',
            'fullname' => 'Education Department Dean',
            'email' => 'dean.ed@mcclawis.edu.ph',
            'password' => $password,
            'role' => 'dean',
            'department' => 'BSED/BEED',
            'status' => 'active'
        ]);

        // 3. Seed Budgets
        $b1 = Budget::create([
            'title' => 'General Operations Fund',
            'department' => 'Administration',
            'allocated_amount' => 150000.00,
            'remaining_balance' => 98500.00,
            'school_year' => '2025-2026',
            'status' => 'Approved',
            'created_by' => $treasurer->id,
            'approved_by' => $admin->id
        ]);

        $b2 = Budget::create([
            'title' => 'Events and Activities Fund',
            'department' => 'Events',
            'allocated_amount' => 80000.00,
            'remaining_balance' => 42000.00,
            'school_year' => '2025-2026',
            'status' => 'Approved',
            'created_by' => $treasurer->id,
            'approved_by' => $admin->id
        ]);

        $b3 = Budget::create([
            'title' => 'Sports Development Fund',
            'department' => 'Sports',
            'allocated_amount' => 50000.00,
            'remaining_balance' => 35000.00,
            'school_year' => '2025-2026',
            'status' => 'Approved',
            'created_by' => $treasurer->id,
            'approved_by' => $admin->id
        ]);

        $b4 = Budget::create([
            'title' => 'Academic Programs Fund',
            'department' => 'Academics',
            'allocated_amount' => 60000.00,
            'remaining_balance' => 55000.00,
            'school_year' => '2025-2026',
            'status' => 'Approved',
            'created_by' => $treasurer->id,
            'approved_by' => $admin->id
        ]);

        $b5 = Budget::create([
            'title' => 'Community Outreach Fund',
            'department' => 'Community',
            'allocated_amount' => 40000.00,
            'remaining_balance' => 28000.00,
            'school_year' => '2025-2026',
            'status' => 'Approved',
            'created_by' => $treasurer->id,
            'approved_by' => $admin->id
        ]);

        $b6 = Budget::create([
            'title' => 'IT & Digital Fund',
            'department' => 'BSIT',
            'allocated_amount' => 30000.00,
            'remaining_balance' => 30000.00,
            'school_year' => '2025-2026',
            'status' => 'Pending',
            'created_by' => $treasurer->id
        ]);

        // 4. Seed Announcements
        Announcement::create([
            'title' => 'SSC General Assembly — May 20, 2026',
            'content' => 'All students are invited to attend the SSC General Assembly on May 20, 2026, at 2:00 PM in the school gymnasium. Attendance is strongly encouraged.',
            'created_by' => $admin->id
        ]);

        Announcement::create([
            'title' => 'Budget Transparency Report Released',
            'content' => 'The SSC is proud to release the Q1 2025-2026 Financial Transparency Report. Students may view and download the full report from the Reports section.',
            'created_by' => $admin->id
        ]);

        Announcement::create([
            'title' => 'Project Proposal Deadline',
            'content' => 'SSC Officers: The deadline for submitting project proposals for Semester 2 is on May 31, 2026. Please submit your proposals before the deadline.',
            'created_by' => $officer->id
        ]);

        Announcement::create([
            'title' => 'Feedback System Now Open',
            'content' => 'Students can now submit questions and concerns directly to the SSC through the Feedback System. We aim to respond within 3-5 working days.',
            'created_by' => $admin->id
        ]);

        // 5. Seed Proposals
        $p1 = Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Inter-School Sports Fest 2026',
            'requested_budget' => 45000.00,
            'approved_budget' => 40000.00,
            'description' => 'An inter-school sports competition open to all students, covering basketball, volleyball, and badminton tournaments. This event aims to promote sportsmanship and school spirit.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        $p2 = Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Academic Excellence Night',
            'requested_budget' => 25000.00,
            'approved_budget' => 22000.00,
            'description' => 'An awards night celebrating academic achievers, honor students, and outstanding graduates of the school year 2025-2026.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        $p3 = Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Community Feeding Program',
            'requested_budget' => 18000.00,
            'description' => 'A community outreach feeding program for underprivileged children in nearby barangays. Estimated 200 beneficiaries.',
            'status' => 'Pending'
        ]);

        $p4 = Proposal::create([
            'officer_id' => $officer->id,
            'project_title' => 'Leadership and Values Seminar',
            'requested_budget' => 12000.00,
            'description' => 'A two-day leadership seminar for SSC officers and class representatives, focusing on governance, accountability, and values formation.',
            'status' => 'Pending'
        ]);

        // 6. Seed Expenses
        Expense::create([
            'budget_id' => $b1->id,
            'officer_id' => $officer->id,
            'expense_title' => 'Office Supplies Purchase',
            'amount' => 3500.00,
            'description' => 'Bond paper, pens, folders, and other office supplies for SSC office use.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        Expense::create([
            'budget_id' => $b2->id,
            'officer_id' => $officer->id,
            'expense_title' => 'Tarpaulins and Banners for Foundation Day',
            'amount' => 8500.00,
            'description' => 'Design and printing of tarpaulins and promotional banners for Foundation Day celebration.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        Expense::create([
            'budget_id' => $b2->id,
            'officer_id' => $officer->id,
            'expense_title' => 'Sound System Rental',
            'amount' => 15000.00,
            'description' => 'Rental of professional sound system and lighting for the Foundation Day event.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        Expense::create([
            'budget_id' => $b3->id,
            'officer_id' => $officer->id,
            'expense_title' => 'Sports Equipment Purchase',
            'amount' => 12000.00,
            'description' => 'Basketballs, volleyballs, badminton sets, and other sports equipment for the Inter-School Sports Fest.',
            'status' => 'Pending'
        ]);

        Expense::create([
            'budget_id' => $b4->id,
            'officer_id' => $officer->id,
            'expense_title' => 'Seminar Resource Speaker Honorarium',
            'amount' => 5000.00,
            'description' => 'Professional fee for the invited resource speaker during the Academic Excellence Program.',
            'status' => 'Approved',
            'approved_by' => $admin->id
        ]);

        // 7. Seed Feedback
        Feedback::create([
            'student_id' => $student->id,
            'message' => 'Can we see the complete breakdown of how the Events Fund was used for Foundation Day?',
            'status' => 'Replied',
            'reply' => 'Thank you for your inquiry! The detailed breakdown of the Foundation Day expenses is available in the Reports section under "Events and Activities Fund - 2025-2026". You may download the full liquidation report there.',
            'replied_by' => $admin->id
        ]);

        Feedback::create([
            'student_id' => $student->id,
            'message' => 'I would like to suggest that the SSC organize a mental health awareness week for students.',
            'status' => 'Pending'
        ]);
    }
}
