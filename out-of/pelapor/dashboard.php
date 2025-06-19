<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Office Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .grid-block {
            transition: all 0.3s ease;
        }
        .grid-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: absolute;
                z-index: 10;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="sidebar bg-white w-64 border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-blue-600">Office<span class="text-gray-700">Dash</span></h1>
            </div>
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center p-2 text-blue-600 rounded-lg bg-blue-50">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-calendar-alt mr-3"></i>
                            <span>Calendar</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-tasks mr-3"></i>
                            <span>Tasks</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-envelope mr-3"></i>
                            <span>Messages</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-chart-bar mr-3"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center p-2 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-cog mr-3"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Profile" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <p class="text-sm font-medium">Sarah Johnson</p>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <button id="menu-toggle" class="md:hidden mr-4 text-gray-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-lg font-semibold">Dashboard Overview</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="relative">
                        <i class="fas fa-bell text-xl text-gray-600"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">Total Projects</p>
                                <h3 class="text-2xl font-bold mt-1">24</h3>
                            </div>
                            <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                                <i class="fas fa-folder-open"></i>
                            </div>
                        </div>
                        <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 12% from last month</p>
                    </div>

                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">Tasks Completed</p>
                                <h3 class="text-2xl font-bold mt-1">156</h3>
                            </div>
                            <div class="p-3 rounded-lg bg-green-100 text-green-600">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 8% from last week</p>
                    </div>

                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">Pending Requests</p>
                                <h3 class="text-2xl font-bold mt-1">7</h3>
                            </div>
                            <div class="p-3 rounded-lg bg-yellow-100 text-yellow-600">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <p class="text-xs text-red-500 mt-2"><i class="fas fa-arrow-down mr-1"></i> 3% from yesterday</p>
                    </div>

                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">Team Members</p>
                                <h3 class="text-2xl font-bold mt-1">14</h3>
                            </div>
                            <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <p class="text-xs text-green-500 mt-2"><i class="fas fa-arrow-up mr-1"></i> 2 new hires</p>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Projects Table -->
                    <div class="lg:col-span-2 grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-lg">Recent Projects</h3>
                            <button class="text-blue-600 text-sm font-medium">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Website Redesign</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">In Progress</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">Jun 15, 2023</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: 65%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Mobile App Launch</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">May 28, 2023</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Marketing Campaign</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">Jul 10, 2023</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 20%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Product Research</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">In Progress</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">Jun 30, 2023</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: 45%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tasks -->
                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-lg">My Tasks</h3>
                            <button class="text-blue-600 text-sm font-medium">Add New</button>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Review design mockups</p>
                                    <p class="text-xs text-gray-500">Today, 2:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" checked>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-400 line-through">Team meeting</p>
                                    <p class="text-xs text-gray-500">Completed</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Prepare quarterly report</p>
                                    <p class="text-xs text-gray-500">Tomorrow, 10:00 AM</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Client presentation</p>
                                    <p class="text-xs text-gray-500">Jun 12, 3:30 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <input type="checkbox" class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Update project timeline</p>
                                    <p class="text-xs text-gray-500">Jun 13, 11:00 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Calendar -->
                    <div class="grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-lg">Calendar</h3>
                            <div class="flex space-x-2">
                                <button class="p-1 rounded-full hover:bg-gray-100">
                                    <i class="fas fa-chevron-left text-gray-600"></i>
                                </button>
                                <button class="p-1 rounded-full hover:bg-gray-100">
                                    <i class="fas fa-chevron-right text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-2">
                            <div>S</div>
                            <div>M</div>
                            <div>T</div>
                            <div>W</div>
                            <div>T</div>
                            <div>F</div>
                            <div>S</div>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <div class="h-8"></div>
                            <div class="h-8"></div>
                            <div class="h-8 flex items-center justify-center">1</div>
                            <div class="h-8 flex items-center justify-center">2</div>
                            <div class="h-8 flex items-center justify-center">3</div>
                            <div class="h-8 flex items-center justify-center">4</div>
                            <div class="h-8 flex items-center justify-center">5</div>
                            <div class="h-8 flex items-center justify-center">6</div>
                            <div class="h-8 flex items-center justify-center">7</div>
                            <div class="h-8 flex items-center justify-center">8</div>
                            <div class="h-8 flex items-center justify-center">9</div>
                            <div class="h-8 flex items-center justify-center">10</div>
                            <div class="h-8 flex items-center justify-center bg-blue-100 text-blue-800 rounded-full">11</div>
                            <div class="h-8 flex items-center justify-center">12</div>
                            <div class="h-8 flex items-center justify-center">13</div>
                            <div class="h-8 flex items-center justify-center">14</div>
                            <div class="h-8 flex items-center justify-center">15</div>
                            <div class="h-8 flex items-center justify-center">16</div>
                            <div class="h-8 flex items-center justify-center">17</div>
                            <div class="h-8 flex items-center justify-center">18</div>
                            <div class="h-8 flex items-center justify-center">19</div>
                            <div class="h-8 flex items-center justify-center">20</div>
                            <div class="h-8 flex items-center justify-center">21</div>
                            <div class="h-8 flex items-center justify-center">22</div>
                            <div class="h-8 flex items-center justify-center">23</div>
                            <div class="h-8 flex items-center justify-center">24</div>
                            <div class="h-8 flex items-center justify-center">25</div>
                            <div class="h-8 flex items-center justify-center">26</div>
                            <div class="h-8 flex items-center justify-center">27</div>
                            <div class="h-8 flex items-center justify-center">28</div>
                            <div class="h-8 flex items-center justify-center">29</div>
                            <div class="h-8 flex items-center justify-center">30</div>
                        </div>
                    </div>

                    <!-- Team Activity -->
                    <div class="lg:col-span-2 grid-block bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-lg">Team Activity</h3>
                            <button class="text-blue-600 text-sm font-medium">See All</button>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-10 h-10 rounded-full">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Michael Chen <span class="text-gray-500 font-normal">completed the task</span> Website Redesign</p>
                                    <p class="text-xs text-gray-500">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="w-10 h-10 rounded-full">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Sarah Johnson <span class="text-gray-500 font-normal">commented on</span> Project Timeline</p>
                                    <p class="text-xs text-gray-500">4 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="User" class="w-10 h-10 rounded-full">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">David Wilson <span class="text-gray-500 font-normal">uploaded files to</span> Marketing Materials</p>
                                    <p class="text-xs text-gray-500">Yesterday, 3:45 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="User" class="w-10 h-10 rounded-full">
                                <div class="ml-3">
                                    <p class="text-sm font-medium">Emily Rodriguez <span class="text-gray-500 font-normal">started a new project</span> Product Research</p>
                                    <p class="text-xs text-gray-500">Yesterday, 10:30 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Add hover effect to grid blocks
        const gridBlocks = document.querySelectorAll('.grid-block');
        gridBlocks.forEach(block => {
            block.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                this.style.transform = 'translateY(-2px)';
            });
            block.addEventListener('mouseleave', function() {
                this.style.boxShadow = '';
                this.style.transform = '';
            });
        });
    </script>
</body>
</html>