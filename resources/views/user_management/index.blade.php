<div>
    <h2>User Management</h2>

    <button onclick="openAddModal()">Add User</button>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th colspan="2">Actions</th>
        </tr>

        @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td>
                    <button
                        onclick="openEditModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}')">
                        Edit
                    </button>
                </td>
                <td>
                    <button
                        onclick="openDeleteModal('{{ $user->id }}', '{{ $user->name }}')">
                        Delete
                    </button>
                </td>
            </tr>
        @endforeach
    </table>
</div>

{{-- Add Modal --}}
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h3>Add User</h3>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div>
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div>
                <label>Role:</label>
                <input type="text" name="role" required>
            </div>

            <div>
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

            <br>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit User</h3>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div>
                <label>Name:</label>
                <input type="text" name="name" id="editName" required>
            </div>

            <div>
                <label>Email:</label>
                <input type="email" name="email" id="editEmail" required>
            </div>

            <div>
                <label>Role:</label>
                <input type="text" name="role" id="editRole" readonly >
            </div>

            <br>
            <button type="submit">Update</button>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        <h3>Delete User</h3>

        <p id="deleteMessage"></p>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit">Yes, Delete</button>
            <button type="button" onclick="closeModal('deleteModal')">Cancel</button>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: #fff;
        width: 400px;
        max-width: 90%;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 22px;
        cursor: pointer;
    }

    input {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 10px;
    }

    button {
        padding: 8px 14px;
        cursor: pointer;
    }
</style>

<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    function openEditModal(id, name, email, role) {
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editForm').action = `/user/management/update/${id}`;
        document.getElementById('editModal').style.display = 'block';
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteMessage').innerText = `Are you sure you want to delete ${name}?`;
        document.getElementById('deleteForm').action = `/user/management/delete/${id}`;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    window.onclick = function(event) {
        const addModal = document.getElementById('addModal');
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');

        if (event.target == addModal) addModal.style.display = 'none';
        if (event.target == editModal) editModal.style.display = 'none';
        if (event.target == deleteModal) deleteModal.style.display = 'none';
    }
</script>