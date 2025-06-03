document.addEventListener('DOMContentLoaded', () => {
    const taskModal = new bootstrap.Modal(document.getElementById('createTaskModal'));
    const taskForm = document.getElementById('createTaskForm');
    const taskIdInput = document.getElementById('taskId');
    const taskTitleInput = document.getElementById('taskTitle');
    const taskDescriptionInput = document.getElementById('taskDescription');
    const taskCompletedInput = document.getElementById('taskCompleted');
    const addTaskButton = document.getElementById('addTaskButton');
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;

    let dataTable;

    // Registre os eventos UMA ÚNICA VEZ, fora do loadTasks()
    $('#tasksTable tbody').off('click', '.edit-task-btn');
    $('#tasksTable tbody').off('click', '.delete-task-btn');
    $('#tasksTable tbody').off('click', '.toggle-completed');

    $('#tasksTable tbody').on('click', '.edit-task-btn', async function () {
        const taskId = this.dataset.id;
        await editTask(taskId);
    });

    $('#tasksTable tbody').on('click', '.delete-task-btn', async function () {
        const taskId = this.dataset.id;
        await deleteTask(taskId);
    });

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
                    showStatusMessage(`Status da tarefa "${result.task_title}" alterado para ${statusLabel}.`);
                } else {
                    showStatusMessage(result.message);
                }
                loadTasks();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Erro ao atualizar status da tarefa.');
        }
    });

    async function loadTasks() {
        if (dataTable) {
            dataTable.destroy();
        }

        dataTable = $('#tasksTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/api/tasks?action=list',
                type: 'GET',
                dataSrc: 'tasks',
                error: function (xhr, error, thrown) {
                    console.error('Erro ao buscar tarefas:', thrown, xhr.responseText);
                    alert('Erro ao carregar tarefas: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Erro desconhecido.'));
                }
            },
            columns: [
                { data: 'id' },
                { data: 'title' },
                { data: 'description' },
                {
                    data: 'status',
                    render: function (data, type, row) {
                        if (data === 'completed') {
                            return `<span class="badge bg-success toggle-completed" style="cursor:pointer;" data-id="${row.id}" data-completed="1">Concluída</span>`;
                        } else {
                            return `<span class="badge bg-warning text-dark toggle-completed" style="cursor:pointer;" data-id="${row.id}" data-completed="0">Pendente</span>`;
                        }
                    }
                },
                { data: 'created_at' },
                { data: 'user_name', title: 'Usuário' },
                {
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
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/pt-BR.json'
            }
        });
    }

    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = taskIdInput.value;
        const title = taskTitleInput.value;
        const description = taskDescriptionInput.value;
        const completed = taskCompletedInput.checked ? 1 : 0;

        const action = id ? 'update' : 'create';
        const url = `/api/tasks?action=${action}`;
        const method = 'POST';

        const formData = new FormData();
        if (id) formData.append('id', id);
        formData.append('title', title);
        formData.append('description', description);
        formData.append('completed', completed);
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch(url, {
                method: method,
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                taskModal.hide();
                loadTasks();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao salvar tarefa:', error);
            alert('Erro ao salvar tarefa. Verifique a conexão ou os logs.');
        }
    });

    async function editTask(id) {
        try {
            const response = await fetch(`/api/tasks?action=get&id=${id}`);
            const result = await response.json();

            if (result.success && result.task) {
                const task = result.task;
                taskIdInput.value = task.id;
                taskTitleInput.value = task.title;
                taskDescriptionInput.value = task.description;
                taskCompletedInput.checked = task.status === 'completed';
                document.getElementById('taskModalLabel').textContent = 'Editar Tarefa';
                taskModal.show();
            } else {
                alert('Erro ao carregar tarefa para edição: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao carregar tarefa para edição:', error);
            alert('Erro ao carregar tarefa para edição.');
        }
    }

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
                if (result.task_title && result.task_status) {
                    const statusLabel = result.task_status === 'completed' ? 'Concluída' : 'Pendente';
                    showStatusMessage(`Status da tarefa "${result.task_title}" alterado para ${statusLabel}.`);
                } else {
                    showStatusMessage(result.message);
                }
                loadTasks();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao excluir tarefa:', error);
            alert('Erro ao excluir tarefa.');
        }
    }

    addTaskButton.addEventListener('click', () => {
        taskForm.reset();
        taskIdInput.value = '';
        document.getElementById('taskModalLabel').textContent = 'Adicionar Tarefa';
    });
    function showStatusMessage(msg) {
        let msgDiv = document.getElementById('statusMessage');
        if (msgDiv) {
            msgDiv.textContent = msg;
            msgDiv.style.display = 'block';
            msgDiv.className = 'alert alert-success text-center mx-auto';
            setTimeout(() => { msgDiv.style.display = 'none'; }, 4000);
        }
    }
    loadTasks();
});