<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiro — HR Management</title>
        
    <link rel="icon" href="{{ asset('images/logo-icon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/compiled/css/app.css ') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/extensions/data-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/compiled/css/ui-icons-dripicons.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/dist/assets/extensions/@icon/dripicons/dripicons.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

</head>

<body>
        <script src="{{ asset('mazer/dist/assets/static/js/initTheme.js') }}"></script>
        <div id="app">
            <div id="sidebar">
                <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ url('/dashboard') }}" style="text-decoration: none;">
                        <img src="{{ asset('images/logo.svg') }}" alt="Hiro" style="height:38px;max-width:180px;object-fit:contain;">
                    </a>
                </div>
                <div class="theme-toggle d-flex gap-2  align-items-center mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                        role="img" class="iconify iconify--system-uicons" width="20" height="20"
                        preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                opacity=".3"></path>
                            <g transform="translate(-210 -1)">
                                <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                <circle cx="220.5" cy="11.5" r="4"></circle>
                                <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                            </g>
                        </g>
                    </svg>
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                        <label class="form-check-label"></label>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                        role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                        viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                        </path>
                    </svg>
                </div>
                <div class="sidebar-toggler  x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                @if (session('role') === 'HR')

                <li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <a href="{{ url('/dashboard')}}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>   
                <li
                    class="sidebar-item {{ request()->is('tasks') ? 'active' : '' }}">
                    <a href="{{ url('/tasks')}}" class='sidebar-link'>
                        <i class="bi bi-check-circle-fill"></i>
                    <span>Tasks</span>
                    </a>
                </li> 
                <li
                    class="sidebar-item {{ request()->is('employees') ? 'active' : '' }}">
                        <a href="{{ url('/employees')}}" class='sidebar-link'>
                            <i class="bi bi-people-fill"></i>
                        <span>Employees</span>
                    </a>
                </li>
                <li
                    class="sidebar-item {{ request()->is('departments') ? 'active' : '' }}">
                        <a href="{{ url('/departments')}}" class='sidebar-link'>
                        <i class="bi bi-briefcase"></i>
                    <span>Departments</span>
                </a>
                </li>
                <li
                    class="sidebar-item {{ request()->is('roles') ? 'active' : '' }}">
                        <a href="{{ url('/roles')}}" class='sidebar-link'>
                        <i class="bi bi-tag"></i>
                        <span>Roles</span>
                    </a>
                </li>
                <li
                    class="sidebar-item {{ request()->is('presences') ? 'active' : '' }}">
                    <a href="{{ url('/presences')}}" class='sidebar-link'>
                        <i class="bi bi-table"></i>
                        <span>Presences</span>
                    </a>
                </li>
                    <li
                    class="sidebar-item {{ request()->is('payrolls') ? 'active' : '' }}">
                    <a href="{{ url('/payrolls')}}" class='sidebar-link'>
                        <i class="bi bi-currency-dollar"></i>
                        <span>Payrolls</span>
                    </a>
                    </li>
                    <li
                    class="sidebar-item {{ request()->is('leave-requests') ? 'active' : '' }}">
                    <a href="{{ url('/leave-requests')}}" class='sidebar-link'>
                        <i class="bi bi-shift-fill"></i>
                        <span>Leave Requests</span>
                    </a>
                </li>
                @endif

                @if(in_array(session('role'), ['Developer', 'Sales']))
                    <li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }} ">
                        <a href="{{ url('/dashboard')}}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                        </a>
                    </li>   
                    <li
                    class="sidebar-item {{ request()->is('tasks') ? 'active' : '' }}">
                        <a href="{{ url('/tasks')}}" class='sidebar-link'>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Tasks</span>
                        </a>
                    </li>
                    
                <li
                    class="sidebar-item {{ request()->is('presences') ? 'active' : '' }}">
                    <a href="{{ url('/presences')}}" class='sidebar-link'>
                        <i class="bi bi-table"></i>
                        <span>Presences</span>
                    </a>
                </li>
                    <li
                    class="sidebar-item {{ request()->is('payrolls') ? 'active' : '' }}">
                    <a href="{{ url('/payrolls')}}" class='sidebar-link'>
                        <i class="bi bi-currency-dollar"></i>
                        <span>Payrolls</span>
                    </a>                
                    </li>
                    <li
                    class="sidebar-item {{ request()->is('leave-requests') ? 'active' : '' }}">
                    <a href="{{ url('/leave-requests')}}" class='sidebar-link'>
                        <i class="bi bi-shift-fill"></i>
                        <span>Leave Requests</span>
                    </a>
                    </li>              
                @endif

                    <li
                    class="sidebar-item">
                    <a href="{{ url('/logout') }}" class='sidebar-link'>
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>    
            </ul>
        </div>
    </div>
</div>
                <div id="main">
                    @yield('content')

                    <footer>
            <div class="footer clearfix mb-0 text-muted">
            </div>
        </footer>
    </div>
</div>
    <script src="{{ asset('mazer/dist/assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('mazer/dist/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('mazer/dist/assets/compiled/js/app.js') }}"></script>

<!-- Need: Apexcharts -->
<script src="{{ asset('mazer/dist/assets/extensions/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('mazer/dist/assets/static/js/pages/dashboard.js') }}"></script>

<script src="{{ asset('mazer/dist/assets/extensions/chart.js/chart.umd.js') }}"></script>

<script src="{{ asset('mazer/dist/assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
<script src="{{ asset('mazer/dist/assets/static/js/pages/simple-datatables.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    let date = flatpickr('.date', {
        dateFormat: "Y-m-d",
    });

    let datetime = flatpickr('.datetime', {
        enableTime: true,
        dateFormat: "Y-m-d H:i:s",
        time_24hr: true,
    });

    var ctxBar = document.getElementById('presence').getContext('2d');
    var myBar = new Chart(ctxBar, {
        type: "bar",
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
                label: 'Total',
                data: [],
                backgroundColor: 'rgba(63, 82, 227, 1',
                borderColor: '#57CAEB'
            }]
        },
        options: {
                responsive: true, 
                maintainAspectRatio: false, // 
                plugins: { // Chart.js v3/v4 menggunakan 'plugins' untuk title
                    title: {
                        display: true,
                        text: 'Latest Presence'
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    function updateData() {
        fetch ('/dashboard/presences')
        .then(response => response.json())
        .then((output) => {
            console.log(output);
            myBar.data.datasets = [
                {
                label: 'Total',
                data: output,
                backgroundColor: 'rgba(63, 82, 227, 1',
                borderColor: '#57CAEB'
                }
            ];
            myBar.update();
        });
    }
    updateData();
</script>

@include('components.toast-notification')
</body>
</html>