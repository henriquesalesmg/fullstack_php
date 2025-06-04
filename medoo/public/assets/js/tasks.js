// Função utilitária para escapar HTML (proteção XSS)
function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.addEventListener('DOMContentLoaded', () => {
    // Elementos do formulário de criação e edição
    const taskModal = new bootstrap.Modal(document.getElementById('createTaskModal'));
    const taskForm = document.getElementById('createTaskForm');
    const taskTitleInput = document.getElementById('createTitle');
    const taskDescriptionInput = document.getElementById('createDescription');
    const taskCategoryInput = document.getElementById('createCategory');
    const addTaskButton = document.getElementById('addTaskButton');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    const editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
    const editTaskForm = document.getElementById('editTaskForm');
    const editTaskIdInput = document.getElementById('editTaskId');
    const editTitleInput = document.getElementById('editTitle');
    const editDescriptionInput = document.getElementById('editDescription');
    const editCategoryInput = document.getElementById('editCategory');
    const editCompletedInput = document.getElementById('editCompleted');

    // Filtros
    const filterStatus = document.getElementById('filterStatus');
    const filterCategory = document.getElementById('filterCategory');
    const orderByDateBtn = document.getElementById('orderByDate');
    const resetFiltersBtn = document.getElementById('resetFilters');

    let dataTable;
    let dateOrderAsc = true;

    // Carregar tarefas com filtros
    async function loadTasks() {
        const status = filterStatus ? filterStatus.value : '';
        const category = filterCategory ? filterCategory.value : '';

        if (dataTable) {
            dataTable.destroy();
        }

        // Monta as colunas dinamicamente conforme o perfil do usuário
        let columns = [
            { data: 'id' },
            { 
                data: 'title',
                render: function(data) { return escapeHtml(data); }
            },
            { 
                data: 'category', 
                defaultContent: '',
                render: function(data) { return escapeHtml(data); }
            },
            { 
                data: 'description',
                render: function(data) { return escapeHtml(data); }
            },
            {
                data: 'status',
                render: function (data, type, row) {
                    if (data === 'completed') {
                        return `<span class="btn btn-success btn-sm toggle-completed" style="cursor:pointer;" data-id="${row.id}" data-completed="1">Concluída</span>`;
                    } else {
                        return `<span class="btn btn-warning btn-sm text-dark toggle-completed" style="cursor:pointer;" data-id="${row.id}" data-completed="0">Pendente</span>`;
                    }
                }
            },
            { data: 'created_at' }
        ];

        // Adiciona a coluna Usuário antes das ações, somente se for admin
        if (window.isAdmin) {
            columns.push({ 
                data: 'user_name', 
                title: 'Usuário',
                render: function(data) { return escapeHtml(data); }
            });
        }

        // Coluna de ações sempre no final
        columns.push({
            data: null,
            render: function (data, type, row) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Ações
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item edit-task-btn" href="#" data-id="${row.id}">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item delete-task-btn" href="#" data-id="${row.id}">
                                    <i class="fas fa-trash-alt me-2"></i>Excluir
                                </a>
                            </li>
                        </ul>
                    </div>
                `;
            }
        });

        dataTable = $('#tasksTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/api/tasks?action=list',
                type: 'GET',
                data: function (d) {
                    d.status = status;
                    d.category = category;
                },
                dataSrc: 'tasks',
                error: function (xhr, error, thrown) {
                    console.error('Erro ao buscar tarefas:', thrown, xhr.responseText);
                    alert('Erro ao carregar tarefas: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Erro desconhecido.'));
                }
            },
            columns: columns,
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/pt-BR.json'
            }
        });
    }

    // Atualiza a tabela ao mudar filtros
    if (filterStatus) filterStatus.addEventListener('change', loadTasks);
    if (filterCategory) filterCategory.addEventListener('change', loadTasks);

    // Botão de ordenação por data
    if (orderByDateBtn) {
        orderByDateBtn.addEventListener('click', () => {
            if (dataTable) {
                // Descobre o índice da coluna 'created_at'
                let createdAtIndex = window.isAdmin ? 6 : 5;
                dataTable.order([createdAtIndex, dateOrderAsc ? 'asc' : 'desc']).draw();
                dateOrderAsc = !dateOrderAsc;
            }
        });
    }

    // Botão para resetar filtros
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            if (filterStatus) filterStatus.value = '';
            if (filterCategory) filterCategory.value = '';
            loadTasks();
        });
    }

    // Submeter formulário de criação
    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const title = taskTitleInput.value;
        const description = taskDescriptionInput.value;
        const categoryId = taskCategoryInput.value;

        const url = '/api/tasks?action=create';
        const method = 'POST';

        const formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
        formData.append('category_id', categoryId);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch(url, {
                method: method,
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showStatusMessage(escapeHtml(result.message));
                taskModal.hide();
                loadTasks();
            } else {
                alert('Erro: ' + escapeHtml(result.message));
            }
        } catch (error) {
            console.error('Erro ao salvar tarefa:', error);
            alert('Erro ao salvar tarefa. Verifique a conexão ou os logs.');
        }
    });

    // Submeter formulário de edição
    editTaskForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = editTaskIdInput.value;
        const title = editTitleInput.value;
        const description = editDescriptionInput.value;
        const categoryId = editCategoryInput.value;
        const completed = editCompletedInput.checked ? 1 : 0;

        const url = '/api/tasks?action=update';
        const method = 'POST';

        const formData = new FormData();
        formData.append('id', id);
        formData.append('title', title);
        formData.append('description', description);
        formData.append('category_id', categoryId);
        formData.append('completed', completed);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch(url, {
                method: method,
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showStatusMessage(escapeHtml(result.message));
                editTaskModal.hide();
                loadTasks();
            } else {
                alert('Erro: ' + escapeHtml(result.message));
            }
        } catch (error) {
            console.error('Erro ao atualizar tarefa:', error);
            alert('Erro ao atualizar tarefa. Verifique a conexão ou os logs.');
        }
    });

    // Carregar dados para edição
    async function loadEditTask(id) {
        try {
            const response = await fetch(`/api/tasks?action=edit&id=${id}`);
            const result = await response.json();

            if (result.success && result.task) {
                const task = result.task;
                editTaskIdInput.value = task.id;
                editTitleInput.value = task.title;
                editDescriptionInput.value = task.description;
                editCategoryInput.value = task.category_id || '';
                editCompletedInput.checked = task.status === 'completed';
                document.getElementById('editTaskModalLabel').textContent = 'Editar Tarefa';
                editTaskModal.show();
            } else {
                alert('Erro ao carregar tarefa para edição: ' + escapeHtml(result.message));
            }
        } catch (error) {
            console.error('Erro ao carregar tarefa para edição:', error);
            alert('Erro ao carregar tarefa para edição.');
        }
    }

    // Excluir tarefa
    async function deleteTask(id) {
        if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('/api/tasks?action=delete', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showStatusMessage(escapeHtml(result.message));
                loadTasks();
            } else {
                alert('Erro: ' + escapeHtml(result.message));
            }
        } catch (error) {
            console.error('Erro ao excluir tarefa:', error);
            alert('Erro ao excluir tarefa.');
        }
    }

    // Botão para abrir modal de criação
    addTaskButton.addEventListener('click', () => {
        taskForm.reset();
        document.getElementById('createTaskModalLabel').textContent = 'Criar Nova Tarefa';
    });

    // Função para exibir mensagens de status
    function showStatusMessage(msg) {
        let msgDiv = document.getElementById('statusMessage');
        if (msgDiv) {
            msgDiv.textContent = msg;
            msgDiv.style.display = 'block';
            msgDiv.className = 'alert alert-success text-center mx-auto';
            setTimeout(() => { msgDiv.style.display = 'none'; }, 4000);
        }
    }

    // Carregar categorias para os selects e filtro
    async function loadCategories() {
        try {
            const response = await fetch('/api/tasks?action=categories');
            const result = await response.json();
            if (result.categories) {
                // Preenche os selects de categoria
                const createSelect = document.getElementById('createCategory');
                const editSelect = document.getElementById('editCategory');
                const filterSelect = document.getElementById('filterCategory');

                if (createSelect) {
                    createSelect.innerHTML = '<option value="">Selecione...</option>';
                }
                if (editSelect) {
                    editSelect.innerHTML = '<option value="">Selecione...</option>';
                }
                if (filterSelect) {
                    filterSelect.innerHTML = '<option value="">Todas</option>';
                }

                result.categories.forEach(cat => {
                    const opt1 = document.createElement('option');
                    opt1.value = cat.id;
                    opt1.textContent = escapeHtml(cat.name);
                    if (createSelect) createSelect.appendChild(opt1);

                    const opt2 = document.createElement('option');
                    opt2.value = cat.id;
                    opt2.textContent = escapeHtml(cat.name);
                    if (editSelect) editSelect.appendChild(opt2);

                    const opt3 = document.createElement('option');
                    opt3.value = cat.id;
                    opt3.textContent = escapeHtml(cat.name);
                    if (filterSelect) filterSelect.appendChild(opt3);
                });
            }
        } catch (e) {
            console.error('Erro ao carregar categorias:', e);
        }
    }

    // Eventos para editar e excluir tarefas
    $('#tasksTable tbody').on('click', '.edit-task-btn', function () {
        const taskId = this.dataset.id;
        loadEditTask(taskId);
    });

    $('#tasksTable tbody').on('click', '.delete-task-btn', function () {
        const taskId = this.dataset.id;
        deleteTask(taskId);
    });

    // Alternar status (concluída/pendente)
    $('#tasksTable tbody').on('click', '.toggle-completed', async function () {
        const id = $(this).data('id');
        const completed = $(this).data('completed') == 1 ? 0 : 1;

        const formData = new FormData();
        formData.append('id', id);
        formData.append('completed', completed);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('/api/tasks?action=update', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                if (result.task_title && result.task_status) {
                    const statusLabel = result.task_status === 'completed' ? 'Concluída' : 'Pendente';
                    showStatusMessage(`Status da tarefa "${escapeHtml(result.task_title)}" alterado para ${escapeHtml(statusLabel)}.`);
                } else {
                    showStatusMessage(escapeHtml(result.message));
                }
                loadTasks();
            } else {
                alert('Erro: ' + escapeHtml(result.message));
            }
        } catch (error) {
            alert('Erro ao atualizar status da tarefa.');
        }
    });

    // Inicializa a tabela e categorias
    loadTasks();
    loadCategories();
});