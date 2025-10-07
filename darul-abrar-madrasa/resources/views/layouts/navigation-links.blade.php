<div x-data="{
        managementOpen: false,
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

    @if(auth()->check() && auth()->user()->role === 'admin')
        <!-- Management -->
        <button type="button" @click="managementOpen = !managementOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="managementOpen.toString()">
            <span class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                <span>Management</span>
            </span>
            <svg class="sidebar-icon chevron-icon" :class="managementOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="managementOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('users.index') }}" class="nav-group-item {{ request()->routeIs('users.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5-3M9 10a4 4 0 110-8"/></svg>Users</span>
            </a>
            <a href="{{ route('departments.index') }}" class="nav-group-item {{ request()->routeIs('departments.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>Departments</span>
            </a>
            <a href="{{ route('classes.index') }}" class="nav-group-item {{ request()->routeIs('classes.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>Classes</span>
            </a>
            <a href="{{ route('teachers.index') }}" class="nav-group-item {{ request()->routeIs('teachers.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7a4 4 0 118 0M12 11a5 5 0 00-5 5v3h10v-3a5 5 0 00-5-5z"/></svg>Teachers</span>
            </a>
            <a href="{{ route('students.index') }}" class="nav-group-item {{ request()->routeIs('students.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>Students</span>
            </a>
            <a href="{{ route('subjects.index') }}" class="nav-group-item {{ request()->routeIs('subjects.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/></svg>Subjects</span>
            </a>
        </div>

        <!-- Academic -->
        <button type="button" @click="academicOpen = !academicOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="academicOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg><span>Academic</span></span>
            <svg class="sidebar-icon chevron-icon" :class="academicOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="academicOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('exams.index') }}" class="nav-group-item {{ request()->routeIs('exams.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6M5 7h14"/></svg>Exams</span></a>
            <a href="{{ route('attendances.index') }}" class="nav-group-item {{ request()->routeIs('attendances.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 19h14"/></svg>Attendance</span></a>
            <a href="{{ route('results.index') }}" class="nav-group-item {{ request()->routeIs('results.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10V6H5v14z"/></svg>Results</span></a>
            <a href="{{ route('marks.create') }}" class="nav-group-item {{ request()->routeIs('marks.create') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Marks Entry</span></a>
            <a href="{{ route('grading-scales.index') }}" class="nav-group-item {{ request()->routeIs('grading-scales.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/></svg>Grading Scales</span></a>
            <a href="{{ route('lesson-plans.index') }}" class="nav-group-item {{ request()->routeIs('lesson-plans.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/></svg>Lesson Plans</span></a>
        </div>

        <!-- Financial -->
        <button type="button" @click="financialOpen = !financialOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="financialOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/></svg><span>Financial</span></span>
            <svg class="sidebar-icon chevron-icon" :class="financialOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="financialOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('fees.index') }}" class="nav-group-item {{ request()->routeIs('fees.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/></svg>Fees</span></a>
            <a href="{{ route('fees.reports') }}" class="nav-group-item {{ request()->routeIs('fees.reports*') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
                <span class="inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M3 11h18"/></svg>
                    Reports
                </span>
            </a>
        </div>

        <!-- Communication -->
        <button type="button" @click="communicationOpen = !communicationOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="communicationOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-6.219-8.56"/></svg><span>Communication</span></span>
            <svg class="sidebar-icon chevron-icon" :class="communicationOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="communicationOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('notices.index') }}" class="nav-group-item {{ request()->routeIs('notices.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h8m-8 4h6M5 4h14"/></svg>Notices</span></a>
        </div>

        <!-- Public Notices -->
        <a href="{{ route('notices.public') }}" class="{{ request()->routeIs('notices.public') ? 'sidebar-active' : 'sidebar-link' }} touch-target">
            <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-6.219-8.56"/></svg>
            Public Notices
        </a>
    @endif

    @if(auth()->check() && auth()->user()->role === 'teacher')
        <!-- My Classes -->
        <button type="button" @click="myClassesOpen = !myClassesOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myClassesOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg><span>My Classes</span></span>
            <svg class="sidebar-icon chevron-icon" :class="myClassesOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="myClassesOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('attendances.index') }}" class="nav-group-item {{ request()->routeIs('attendances.index') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 19h14"/></svg>Attendance</span></a>
            <a href="{{ route('results.index') }}" class="nav-group-item {{ request()->routeIs('results.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10V6H5v14z"/></svg>Results</span></a>
            <a href="{{ route('marks.create') }}" class="nav-group-item {{ request()->routeIs('marks.create') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Marks Entry</span></a>
        </div>

        <!-- Academic Resources -->
        <button type="button" @click="academicResourcesOpen = !academicResourcesOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="academicResourcesOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"/></svg><span>Academic Resources</span></span>
            <svg class="sidebar-icon chevron-icon" :class="academicResourcesOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="academicResourcesOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('lesson-plans.index') }}" class="nav-group-item {{ request()->routeIs('lesson-plans.*') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6"/></svg>Lesson Plans</span></a>
        </div>
    @endif

    @if(auth()->check() && auth()->user()->role === 'student')
        <!-- My Academic -->
        <button type="button" @click="myAcademicOpen = !myAcademicOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myAcademicOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg><span>My Academic</span></span>
            <svg class="sidebar-icon chevron-icon" :class="myAcademicOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="myAcademicOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('my.attendance') }}" class="nav-group-item {{ request()->routeIs('my.attendance') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18"/></svg>My Attendance</span></a>
            <a href="{{ route('my.results') }}" class="nav-group-item {{ request()->routeIs('my.results') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>My Results</span></a>
            <a href="{{ route('my.materials') }}" class="nav-group-item {{ request()->routeIs('my.materials') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12M6 10h12"/></svg>Study Materials</span></a>
        </div>

        <!-- My Financial -->
        <button type="button" @click="myFinancialOpen = !myFinancialOpen" class="nav-group-header w-full text-left touch-target" :aria-expanded="myFinancialOpen.toString()">
            <span class="flex items-center"><svg class="sidebar-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2-1.72 2-4 2m0-8c2.28 0 4 .895 4 2m-4-2V4m0 12v4"/></svg><span>My Financial</span></span>
            <svg class="sidebar-icon chevron-icon" :class="myFinancialOpen ? 'transform rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="myFinancialOpen" x-transition class="pl-8 space-y-1">
            <a href="{{ route('my.fees') }}" class="nav-group-item {{ request()->routeIs('my.fees') ? 'sidebar-active' : 'sidebar-link' }} touch-target"><span class="inline-flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2"/></svg>My Fees</span></a>
        </div>
    @endif
</div>
