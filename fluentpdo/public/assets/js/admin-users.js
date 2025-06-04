document.addEventListener('DOMContentLoaded', () => {
    const usersTableBody = document.querySelector('#usersTable tbody');
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const userForm = document.getElementById('userForm');
    const userModalLabel = document.getElementById('userModalLabel');
    const addUserBtn = document.getElementById('addUserBtn');

    function getRoleBadge(role) {
        if (role === 'admin') {
            return '<span class="badge bg-danger">Administrador</span>';
        } else {
            return '<span class="badge bg-dark">Usuário</span>';
        }
    }

    // Carregar usuários
    function loadUsers() {
        fetch('/api/admin?action=list')
            .then(res => res.json())
            .then(data => {
                usersTableBody.innerHTML = '';
                if (data.users && Array.isArray(data.users) && data.users.length > 0) {
                    data.users.forEach(user => {
                        usersTableBody.innerHTML += `
                            <tr>
                                <td>${user.id}</td>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${getRoleBadge(user.role)}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Ações
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item edit-user-btn" href="#" data-id="${user.id}">
                                                    <i class="fas fa-edit me-2"></i>Editar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item delete-user-btn" href="#" data-id="${user.id}">
                                                    <i class="fas fa-trash-alt me-2"></i>Excluir
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum usuário encontrado.</td></tr>';
                }
            })
            .catch(() => {
                usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar usuários.</td></tr>';
            });
    }

    // Abrir modal para novo usuário
    addUserBtn.addEventListener('click', () => {
        userForm.reset();
        userModalLabel.textContent = 'Novo Usuário';
        document.getElementById('userId').value = '';
        document.getElementById('userRole').value = 'user';
    });

    // Editar usuário
    usersTableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.edit-user-btn');
        if (btn) {
            const id = btn.dataset.id;
            fetch(`/api/admin?action=get&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.user) {
                        userForm.reset();
                        userModalLabel.textContent = 'Editar Usuário';
                        document.getElementById('userId').value = data.user.id;
                        document.getElementById('userName').value = data.user.name;
                        document.getElementById('userEmail').value = data.user.email;
                        document.getElementById('userRole').value = data.user.role;
                        document.getElementById('userPassword').value = '';
                        userModal.show();
                    }
                });
        }
    });

    // Remover usuário
    usersTableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-user-btn');
        if (btn) {
            const id = btn.dataset.id;
            if (confirm('Tem certeza que deseja remover este usuário?')) {
                const formData = new FormData();
                formData.append('id', id);
                fetch('/api/admin?action=delete', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(() => loadUsers());
            }
        }
    });

    // Salvar usuário (adicionar/editar)
    userForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(userForm);
        fetch('/api/admin?action=save', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(() => {
                userModal.hide();
                loadUsers();
            });
    });

    loadUsers();
});