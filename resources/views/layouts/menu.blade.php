<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            {{-- DIHAPUS: Bagian logo SVG dihilangkan --}}
            <span class="app-brand-text demo menu-text fw-bolder ms-2">AI English</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- semua role --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        @auth
            {{-- Dosen --}}
            @if (auth()->user()->role == 'dosen')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Dosen Area</span>
                </li>
                <li class="menu-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                    <a href="{{ route('courses.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-book-content"></i>
                        <div>My Courses</div>
                    </a>
                </li>
            @endif

            {{-- Mahasiswa --}}
            @if (auth()->user()->role == 'mahasiswa')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Mahasiswa Area</span>
                </li>
                {{-- Nanti link ini bisa diganti menjadi 'Enrolled Courses' --}}
                <li class="menu-item {{ request()->routeIs('submission.create') ? 'active' : '' }}">
                    <a href="{{ route('submission.create') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-upload"></i>
                        <div>Submit Assignment</div>
                    </a>
                </li>
            @endif


            {{-- Admin --}}
            @if (auth()->user()->role == 'admin')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Administration</span>
                </li>
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bxs-user-detail"></i>
                        <div>User Management</div>
                    </a>
                </li>
            @endif
        @endauth
        {{-- semua role --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Account</span>
        </li>
        <li class="menu-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <a href="{{ route('profile.edit') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div>My Profile</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('submission.create') ? 'active' : '' }}">
            <a href="{{ route('submission.create') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-upload"></i>
                <div>Submit (Test)</div>
            </a>
        </li>
    </ul>
</aside>
