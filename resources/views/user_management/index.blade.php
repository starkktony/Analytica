<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siel Metrics — User Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            background: #e8ebe8;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .sidebar {
            position: fixed; left: 0; top: 0;
            height: 100vh; width: 210px;
            background: #1a2c1a; color: white;
            transition: width 0.3s ease; z-index: 1000; overflow-y: auto;
        }
        body.sidebar-collapsed .sidebar { width: 68px; }

        .content {
            margin-left: 210px;
            transition: margin-left 0.3s ease;
            height: 100vh; display: flex; flex-direction: column; overflow: hidden;
        }
        body.sidebar-collapsed .content { margin-left: 68px; }

        .fixed-header-section { flex-shrink: 0; background: #e8ebe8; z-index: 100; }

        .header {
            background: #009539; color: white;
            padding: 0 30px; font-size: 32px; font-weight: 800;
            height: 75px; display: flex; align-items: center; gap: 14px;
            font-family: 'Bricolage Grotesque', sans-serif;
        }
        .header i { font-size: 28px; }

        .main-content {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 24px 30px 40px 30px;
        }
        .main-content::-webkit-scrollbar { width: 8px; }
        .main-content::-webkit-scrollbar-track { background: #d4d9d4; border-radius: 4px; }
        .main-content::-webkit-scrollbar-thumb { background: #009539; border-radius: 4px; }

        .page-title-section {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; flex-wrap: wrap; gap: 16px;
        }
        .page-title-section h2 {
            font-size: 24px; font-weight: 700; color: #111827; margin: 0;
            display: flex; align-items: center; gap: 12px;
        }
        .page-title-section h2 i { color: #009539; font-size: 28px; }

        .add-user-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white; border: none; padding: 10px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .add-user-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(34,197,94,0.3); }

        /* Stats row */
        .stats-row { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .stat-card {
            background: white; border-radius: 16px; padding: 16px 24px;
            display: flex; align-items: center; gap: 14px; flex: 1; min-width: 160px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .stat-label { font-size: 12px; color: #6b7280; font-weight: 500; }
        .stat-value { font-size: 22px; font-weight: 800; color: #111827; line-height: 1; }

        /* Table */
        .table-container {
            background: white; border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow-x: auto;
        }
        .user-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
        .user-table thead { background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
        .user-table th {
            padding: 16px 20px; text-align: left; font-size: 12px;
            font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .user-table td { padding: 14px 20px; font-size: 14px; color: #111827; border-bottom: 1px solid #f0f0f0; }
        .user-table tbody tr:last-child td { border-bottom: none; }
        .user-table tbody tr:hover { background: #f9fafb; }

        .role-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .role-Admin                 { background: #fef3c7; color: #92400e; }
        .role-Executive             { background: #ede9fe; color: #5b21b6; }
        .role-Director              { background: #dbeafe; color: #1e40af; }
        .role-Chief                 { background: #e0f2fe; color: #075985; }
        .role-Employee-Teaching     { background: #dcfce7; color: #166534; }
        .role-Employee-Non-Teaching { background: #f3f4f6; color: #374151; }

        .action-buttons { display: flex; gap: 8px; }
        .btn-icon {
            padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; border: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-edit { background: #e8ebe8; color: #009539; }
        .btn-edit:hover { background: #009539; color: white; transform: translateY(-1px); }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #dc2626; color: white; transform: translateY(-1px); }

        /* ============================================================
           SHARED MODAL BASE
        ============================================================ */
        .modal {
            display: none; position: fixed; z-index: 2000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);
            animation: fadeIn 0.2s ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .modal-content {
            background: white; width: 480px; max-width: 90%;
            margin: 5% auto; border-radius: 24px; position: relative;
            animation: slideDown 0.3s ease; box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        @keyframes slideDown {
            from { transform: translateY(-40px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }

        .modal-header {
            padding: 24px 24px 16px; border-bottom: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
        }
        .modal-header h3 {
            font-size: 20px; font-weight: 700; color: #111827; margin: 0;
            display: flex; align-items: center; gap: 10px;
        }
        .modal-header h3 i { color: #009539; }
        .close { font-size: 28px; cursor: pointer; color: #9ca3af; transition: color 0.2s; line-height: 1; }
        .close:hover { color: #111827; }

        .modal-body { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-group label i { margin-right: 6px; color: #009539; }
        .form-group input,
        .form-group select {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s;
            background: white; color: #111827;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none; border-color: #009539; box-shadow: 0 0 0 3px rgba(0,149,57,0.1);
        }
        .form-group input[readonly] { background: #f9fafb; cursor: not-allowed; color: #6b7280; }
        .field-hint { font-size: 11px; color: #6b7280; margin-top: 4px; }

        .modal-footer {
            padding: 16px 24px 24px; display: flex; justify-content: flex-end;
            gap: 12px; border-top: 1px solid #e5e7eb;
        }
        .btn-submit {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white; border: none; padding: 10px 24px; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34,197,94,0.3); }
        .btn-cancel {
            background: #e8ebe8; color: #374151; border: none; padding: 10px 24px;
            border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-cancel:hover { background: #d1d5db; }

        /* Delete modal */
        .delete-message { text-align: center; padding: 20px 0; }
        .delete-message .del-icon { font-size: 52px; color: #dc2626; margin-bottom: 16px; }
        .delete-message p { font-size: 16px; color: #111827; margin: 0; }
        .warning-text { color: #dc2626; font-weight: 600; margin-top: 12px; font-size: 13px; }
        .btn-danger { background: linear-gradient(135deg, #dc2626, #b91c1c) !important; }

        /* ============================================================
           NOTICE POPUP — success / error / validation
        ============================================================ */
        #noticeModal .modal-content {
            width: 380px;
            border-radius: 20px;
            text-align: center;
            padding: 0;
            overflow: hidden;
        }

        .notice-body {
            padding: 40px 32px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        .notice-icon-ring {
            width: 80px; height: 80px; border-radius: 50%;
            border-width: 3px; border-style: solid;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            font-size: 36px;
        }

        /* success = green ring */
        .notice-icon-ring.success {
            border-color: #009539;
            color: #009539;
        }

        /* error = red ring */
        .notice-icon-ring.error {
            border-color: #dc2626;
            color: #dc2626;
        }

        /* validation = amber ring */
        .notice-icon-ring.warning {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .notice-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 12px;
        }

        .notice-message {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .notice-message ul {
            list-style: none;
            padding: 0; margin: 0;
            text-align: left;
        }

        .notice-message ul li::before {
            content: '• ';
            color: #dc2626;
            font-weight: 700;
        }

        .notice-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }

        .notice-btn.success { background: #009539; color: white; }
        .notice-btn.success:hover { background: #016531; }

        .notice-btn.error   { background: #dc2626; color: white; }
        .notice-btn.error:hover { background: #b91c1c; }

        .notice-btn.warning { background: #f59e0b; color: white; }
        .notice-btn.warning:hover { background: #d97706; }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
        .empty-state i { font-size: 64px; margin-bottom: 16px; }
        .empty-state p { font-size: 16px; }

        /* Access legend */
        .access-legend {
            background: white; border-radius: 16px; padding: 20px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px;
        }
        .access-legend h4 {
            font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .legend-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
        .legend-item { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #374151; line-height: 1.4; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }

        @media (max-width: 768px) {
            .header { font-size: 24px; padding: 0 20px; height: 65px; }
            .main-content { padding: 20px 16px 30px; }
            .action-buttons { flex-direction: column; gap: 6px; }
            .btn-icon { width: 100%; justify-content: center; }
            .stats-row { gap: 10px; }
        }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="fixed-header-section">
            <div class="header">
                <i class="fas fa-users-cog"></i>
                User Management
            </div>
        </div>

        <div class="main-content">

            {{-- Page title + Add button --}}
            <div class="page-title-section">
                <h2>
                    <i class="fas fa-user-friends"></i>
                    Manage System Users
                </h2>
                <button class="add-user-btn" onclick="openAddModal()">
                    <i class="fas fa-plus-circle"></i>
                    Add New User
                </button>
            </div>

            {{-- Stats --}}
            <div class="stats-row">
                @php
                    $roleCounts = $users->groupBy('role');
                    $totalUsers = $users->count();
                @endphp
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7; color:#92400e;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value">{{ $totalUsers }}</div>
                    </div>
                </div>
                @foreach([
                    ['Admin',                '#fef3c7','#92400e', 'fa-user-shield'],
                    ['Executive',            '#ede9fe','#5b21b6', 'fa-user-tie'],
                    ['Director',             '#dbeafe','#1e40af', 'fa-briefcase'],
                    ['Chief',                '#e0f2fe','#075985', 'fa-chess-king'],
                    ['Employee-Teaching',    '#dcfce7','#166534', 'fa-chalkboard-teacher'],
                    ['Employee-Non-Teaching','#f3f4f6','#374151', 'fa-user'],
                ] as [$role, $bg, $color, $icon])
                    <div class="stat-card">
                        <div class="stat-icon" style="background:{{ $bg }}; color:{{ $color }};">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div>
                            <div class="stat-label">{{ $role }}</div>
                            <div class="stat-value">{{ $roleCounts->get($role)?->count() ?? 0 }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Access Legend --}}
            <div class="access-legend">
                <h4><i class="fas fa-shield-alt" style="color:#009539;"></i> Role Access Summary</h4>
                <div class="legend-grid">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#92400e;"></div>
                        <div><strong>Admin</strong> — Full access + user management</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#5b21b6;"></div>
                        <div><strong>Executive</strong> — View all pages, read-only</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#1e40af;"></div>
                        <div><strong>Director</strong> — All except EIS</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#075985;"></div>
                        <div><strong>Chief</strong> — All except EIS</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#166534;"></div>
                        <div><strong>Employee-Teaching</strong> — Faculty pages only</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#374151;"></div>
                        <div><strong>Employee-Non-Teaching</strong> — Dashboard only</div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-container">
                @if($users->count() > 0)
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $i => $user)
                        <tr>
                            <td style="color:#9ca3af; font-size:13px;">{{ $i + 1 }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleClass = 'role-' . str_replace(' ', '-', $user->role);
                                    $roleIcons = [
                                        'Admin'                  => 'fa-user-shield',
                                        'Executive'              => 'fa-user-tie',
                                        'Director'               => 'fa-briefcase',
                                        'Chief'                  => 'fa-chess-king',
                                        'Employee-Teaching'      => 'fa-chalkboard-teacher',
                                        'Employee-Non-Teaching'  => 'fa-user',
                                    ];
                                    $icon = $roleIcons[$user->role] ?? 'fa-tag';
                                @endphp
                                <span class="role-badge {{ $roleClass }}">
                                    <i class="fas {{ $icon }}"></i>
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if($user->id !== auth()->id())
                                        <button class="btn-icon btn-edit"
                                            onclick="openEditModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn-icon btn-delete"
                                            onclick="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    @else
                                        <span style="font-size:12px; color:#9ca3af; font-style:italic;">You</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <p>No users found. Click "Add New User" to get started.</p>
                </div>
                @endif
            </div>

        </div>{{-- /main-content --}}
    </div>{{-- /content --}}

    {{-- ============================================================ --}}
    {{-- NOTICE POPUP (success / error / validation)                  --}}
    {{-- ============================================================ --}}
    <div id="noticeModal" class="modal">
        <div class="modal-content">
            <div class="notice-body">
                <div class="notice-icon-ring" id="noticeIconRing">
                    <i id="noticeIcon"></i>
                </div>
                <div class="notice-title" id="noticeTitle">NOTICE</div>
                <div class="notice-message" id="noticeMessage"></div>
                <button class="notice-btn" id="noticeBtn" onclick="closeModal('noticeModal')">OK</button>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ADD MODAL                                                     --}}
    {{-- ============================================================ --}}
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Add New User</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" placeholder="Enter full name" required
                            value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" placeholder="user@clsu.edu.ph" required
                            value="{{ old('email') }}">
                        <div class="field-hint">Must be a @clsu.edu.ph address</div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Role</label>
                        <select name="role" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>— Select a role —</option>
                            @foreach(['Admin','Executive','Director','Chief','Employee-Teaching','Employee-Non-Teaching'] as $r)
                                <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                        <div class="field-hint">Role cannot be changed after creation</div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" placeholder="Min. 6 characters" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('addModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- EDIT MODAL                                                    --}}
    {{-- ============================================================ --}}
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Edit User</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" id="editName" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" id="editEmail" required>
                        <div class="field-hint">Must be a @clsu.edu.ph address</div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Role</label>
                        <input type="text" id="editRole" readonly>
                        <div class="field-hint">Role is locked after creation</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('editModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- DELETE MODAL                                                  --}}
    {{-- ============================================================ --}}
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-trash-alt" style="color:#dc2626;"></i> Delete User</h3>
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="delete-message">
                        <div class="del-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <p id="deleteMessage"></p>
                        <div class="warning-text">
                            <i class="fas fa-info-circle"></i> This action cannot be undone.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-submit btn-danger">
                        <i class="fas fa-trash-alt"></i> Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Pass Laravel session/errors to JS                            --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <script>
            window._notice = {
                type: 'success',
                title: 'SUCCESS',
                message: '{{ addslashes(session('success')) }}',
                icon: 'fas fa-check'
            };
        </script>
    @elseif(session('error'))
        <script>
            window._notice = {
                type: 'error',
                title: 'ERROR',
                message: '{{ addslashes(session('error')) }}',
                icon: 'fas fa-times'
            };
        </script>
    @elseif($errors->any())
        <script>
            window._notice = {
                type: 'warning',
                title: 'NOTICE',
                message: `{!! implode('<br>', array_map(fn($e) => '• ' . addslashes($e), $errors->all())) !!}`,
                icon: 'fas fa-exclamation'
            };
        </script>
    @endif

    <script>
        /* ---------- Notice popup ---------- */
        function showNotice({ type, title, message, icon }) {
            const ring    = document.getElementById('noticeIconRing');
            const iconEl  = document.getElementById('noticeIcon');
            const titleEl = document.getElementById('noticeTitle');
            const msgEl   = document.getElementById('noticeMessage');
            const btn     = document.getElementById('noticeBtn');

            ring.className    = `notice-icon-ring ${type}`;
            iconEl.className  = icon;
            titleEl.textContent = title;
            msgEl.innerHTML   = message;
            btn.className     = `notice-btn ${type}`;

            document.getElementById('noticeModal').style.display = 'block';
        }

        // Fire on page load if Laravel sent a flash message
        document.addEventListener('DOMContentLoaded', () => {
            if (window._notice) showNotice(window._notice);

            // Re-open add modal with old input if validation failed
            @if($errors->any() && old('name'))
                document.getElementById('addModal').style.display = 'block';
            @endif
        });

        /* ---------- CRUD modals ---------- */
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(id, name, email, role) {
            document.getElementById('editName').value  = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value  = role;
            document.getElementById('editForm').action = `/user/management/update/${id}`;
            document.getElementById('editModal').style.display = 'block';
        }

        function openDeleteModal(id, name) {
            document.getElementById('deleteMessage').innerHTML =
                `Are you sure you want to delete <strong>${name}</strong>?`;
            document.getElementById('deleteForm').action = `/user/management/delete/${id}`;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close on backdrop click
        window.onclick = function (event) {
            ['addModal','editModal','deleteModal','noticeModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (event.target === modal) modal.style.display = 'none';
            });
        };
    </script>
</body>
</html>