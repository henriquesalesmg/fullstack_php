
document.addEventListener('DOMContentLoaded', function () {
    const settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
    const openSettingsBtn = document.getElementById('openSettingsModal');
    const settingsForm = document.getElementById('settingsForm');
    const settingsError = document.getElementById('settingsError');
    const settingsSuccess = document.getElementById('settingsSuccess');
    const settingsName = document.getElementById('settingsName');
    const settingsPassword = document.getElementById('settingsPassword');
    const userName = document.querySelector('#userDropdown .badge')?.getAttribute('data-username') || '';
    settingsName.value = userName;
    if (openSettingsBtn) {
        openSettingsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            settingsError.classList.add('d-none');
            settingsSuccess.classList.add('d-none');
            settingsForm.reset();
            // Pegue o nome do usuário do badge do menu
            const userName = document.querySelector('#userDropdown .badge')?.textContent.trim() || '';
            settingsName.value = userName;
            settingsModal.show();
        });
    }

    function validatePassword(password) {
        if (!password) return true; // Allow blank (no change)
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;
        return regex.test(password);
    }

    settingsForm.addEventListener('submit', function (e) {
        e.preventDefault();
        settingsError.classList.add('d-none');
        settingsSuccess.classList.add('d-none');

        const name = settingsName.value.trim();
        const password = settingsPassword.value;

        if (!name) {
            settingsError.textContent = "O nome é obrigatório.";
            settingsError.classList.remove('d-none');
            return;
        }

        if (password && !validatePassword(password)) {
            settingsError.textContent = "A senha deve ter pelo menos 6 caracteres, incluindo maiúscula, minúscula, número e caractere especial.";
            settingsError.classList.remove('d-none');
            return;
        }

        const formData = new FormData();
        formData.append('name', name);
        if (password) formData.append('password', password);

        fetch('/api/user?action=settings', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    settingsSuccess.textContent = "Dados atualizados com sucesso!";
                    settingsSuccess.classList.remove('d-none');
                    setTimeout(() => {
                        settingsModal.hide();
                        location.reload();
                    }, 1200);
                } else {
                    settingsError.textContent = data.message || "Erro ao atualizar dados.";
                    settingsError.classList.remove('d-none');
                }
            })
            .catch(() => {
                settingsError.textContent = "Erro ao atualizar dados.";
                settingsError.classList.remove('d-none');
            });
    });
});