<div x-data="{
        usersOpen: false,
        academicOpen: false,
        financialOpen: false,
        communicationOpen: false,
        myClassesOpen: false,
        academicResourcesOpen: false,
        myAcademicOpen: false,
        myFinancialOpen: false
    }" class="space-y-1 custom-scrollbar">

    <!-- Dashboard -->
    <a href="{{ route('dashboard') }}"
       class="{{ request()->routeIs('dashboard') ? 'sidebar-active' : 'sidebar-link' }} touch-target"
       aria-label="Dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2 7-7 7 7M5 10v10h14V10" />
        </svg>
        Dashboard
    </a>

    @role('admin')
        <!-- Users -->
        <button type="button" @click="usersOpen = !usersOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="usersOpen.toString()">
            <span class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3m-2-4a4 4 0 110-8m-6 12h10v-2a3 3 0 00-3-3H9a3 3 0 00-3 3v2z"/>
                </svg>
                <span>Users</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="usersOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="usersOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('teachers.index') }}" class="nav-group-item {{ request()->routeIs('teachers.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Teachers
                </span>
            </a>
            <a href="{{ route('students.index') }}" class="nav-group-item {{ request()->routeIs('students.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    </svg>
                    Students
                </span>
            </a>
            <a href="{{ route('accountants.index') }}" class="nav-group-item {{ request()->routeIs('accountants.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Accountants
                </span>
            </a>
            <a href="{{ route('users.index') }}" class="nav-group-item {{ request()->routeIs('users.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users (All)
                </span>
            </a>
        </div>

        <!-- Academic -->
        <button type="button" @click="academicOpen = !academicOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="academicOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                <span>Academic</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="academicOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="academicOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('departments.index') }}" class="nav-group-item {{ request()->routeIs('departments.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Departments
                </span>
            </a>
            <a href="{{ route('classes.index') }}" class="nav-group-item {{ request()->routeIs('classes.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    </svg>
                    Classes
                </span>
            </a>
            <a href="{{ route('subjects.index') }}" class="nav-group-item {{ request()->routeIs('subjects.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Subjects
                </span>
            </a>
            <a href="{{ route('exams.index') }}" class="nav-group-item {{ request()->routeIs('exams.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6M5 7h14"/>
                    </svg>
                    Exams
                </span>
            </a>
            <a href="{{ route('attendances.index') }}" class="nav-group-item {{ request()->routeIs('attendances.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 19h14"/>
                    </svg>
                    Attendance
                </span>
            </a>
            <a href="{{ route('teacher-attendances.index') }}" class="nav-group-item {{ request()->routeIs('teacher-attendances.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Teacher Attendance
                </span>
            </a>
            <a href="{{ route('results.index') }}" class="nav-group-item {{ request()->routeIs('results.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10V6H5v14z"/>
                    </svg>
                    Results
                </span>
            </a>
            <a href="{{ route('marks.create') }}" class="nav-group-item {{ request()->routeIs('marks.create') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Marks Entry
                </span>
            </a>
            <a href="{{ route('grading-scales.index') }}" class="nav-group-item {{ request()->routeIs('grading-scales.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/>
                    </svg>
                    Grading Scales
                </span>
            </a>
            <a href="{{ route('lesson-plans.index') }}" class="nav-group-item {{ request()->routeIs('lesson-plans.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6"/>
                    </svg>
                    Lesson Plans
                </span>
            </a>
            <a href="{{ route('study-materials.index') }}" class="nav-group-item {{ request()->routeIs('study-materials.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Study Materials
                </span>
            </a>
            <a href="{{ route('periods.index') }}" class="nav-group-item {{ request()->routeIs('periods.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Periods
                </span>
            </a>
            <a href="{{ route('timetables.index') }}" class="nav-group-item {{ request()->routeIs('timetables.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Timetables
                </span>
            </a>
        </div>

        <!-- Financial -->
        <button type="button" @click="financialOpen = !financialOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="financialOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/>
                </svg>
                <span>Financial</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="financialOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="financialOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('fees.index') }}" class="nav-group-item {{ request()->routeIs('fees.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/>
                    </svg>
                    Fees
                </span>
            </a>
            <a href="{{ route('fees.reports') }}" class="nav-group-item {{ request()->routeIs('fees.reports*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Reports
                </span>
            </a>
        </div>

        <!-- Communication -->
        <button type="button" @click="communicationOpen = !communicationOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="communicationOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-6.219-8.56"/>
                </svg>
                <span>Communication</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="communicationOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="communicationOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('notices.index') }}" class="nav-group-item {{ request()->routeIs('notices.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h8m-8 4h6M5 4h14"/>
                    </svg>
                    Notices
                </span>
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-group-item {{ request()->routeIs('notifications.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </span>
            </a>
        </div>

        {{-- System Health - Temporarily disabled for debugging --}}
        {{-- <a href="{{ route('admin.system-health') }}" 
           class="nav-item {{ request()->routeIs('admin.system-health*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
            <span class="inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                System Health
            </span>
        </a> --}}

        <!-- Public Notices -->
        <a href="{{ route('notices.public') }}" class="{{ request()->routeIs('notices.public') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-6.219-8.56"/>
            </svg>
            Public Notices
        </a>
    @endrole

    @role('teacher')
        <!-- My Classes -->
        <button type="button" @click="myClassesOpen = !myClassesOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myClassesOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                <span>My Classes</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="myClassesOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="myClassesOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('attendances.index') }}" class="nav-group-item {{ request()->routeIs('attendances.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 19h14"/>
                    </svg>
                    Attendance
                </span>
            </a>
            <a href="{{ route('results.index') }}" class="nav-group-item {{ request()->routeIs('results.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10V6H5v14z"/>
                    </svg>
                    Results
                </span>
            </a>
            <a href="{{ route('marks.create') }}" class="nav-group-item {{ request()->routeIs('marks.create') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Marks Entry
                </span>
            </a>
        </div>

        <!-- Academic Resources -->
        <button type="button" @click="academicResourcesOpen = !academicResourcesOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="academicResourcesOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/>
                </svg>
                <span>Academic Resources</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="academicResourcesOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="academicResourcesOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('lesson-plans.index') }}" class="nav-group-item {{ request()->routeIs('lesson-plans.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6"/>
                    </svg>
                    Lesson Plans
                </span>
            </a>
            <a href="{{ route('study-materials.index') }}" class="nav-group-item {{ request()->routeIs('study-materials.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Study Materials
                </span>
            </a>
            <a href="{{ route('subjects.index') }}" class="nav-group-item {{ request()->routeIs('subjects.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/>
                    </svg>
                    Subjects
                </span>
            </a>
            <a href="{{ route('my.timetable') }}" class="nav-group-item {{ request()->routeIs('my.timetable') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    My Timetable
                </span>
            </a>
        </div>
    @endrole

    @role('student')
        <!-- My Academic -->
        <button type="button" @click="myAcademicOpen = !myAcademicOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myAcademicOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                <span>My Academic</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="myAcademicOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="myAcademicOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('my.attendance') }}" class="nav-group-item {{ request()->routeIs('my.attendance') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18"/>
                    </svg>
                    My Attendance
                </span>
            </a>
            <a href="{{ route('my.results') }}" class="nav-group-item {{ request()->routeIs('my.results') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                    </svg>
                    My Results
                </span>
            </a>
            <a href="{{ route('my.materials') }}" class="nav-group-item {{ request()->routeIs('my.materials') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12M6 10h12"/>
                    </svg>
                    Study Materials
                </span>
            </a>
        </div>

        <!-- My Financial -->
        <button type="button" @click="myFinancialOpen = !myFinancialOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myFinancialOpen.toString()">
            <span class="flex items-center">
                <svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/>
                </svg>
                <span>My Financial</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="myFinancialOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="myFinancialOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('my.fees') }}" class="nav-group-item {{ request()->routeIs('my.fees') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2"/>
                    </svg>
                    My Fees
                </span>
            </a>
        </div>

    @endrole

    @role('guardian')
        <!-- My Children -->
        <a href="{{ route('guardian.children') }}" class="{{ request()->routeIs('guardian.children') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3m-2-4a4 4 0 110-8m-6 12h10v-2a3 3 0 00-3-3H9a3 3 0 00-3 3v2z"/>
            </svg>
            My Children
        </a>

        <!-- Notification Settings -->
        <a href="{{ route('guardian.notification-preferences') }}" class="{{ request()->routeIs('guardian.notification-preferences') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Notification Settings
        </a>
    @endrole
</div>
