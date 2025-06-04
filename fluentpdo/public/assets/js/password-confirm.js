['resetPasswordForm', 'registerForm'].forEach(function(formId) {
    var form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var pwd = document.getElementById('password').value;
        var cpwd = document.getElementById('confirm_password').value;
        var errorDiv = document.getElementById('passwordError');
        var strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;

        errorDiv.classList.add('d-none');
        errorDiv.innerText = '';

        if (pwd !== cpwd) {
            errorDiv.innerText = 'As senhas não conferem.';
            errorDiv.classList.remove('d-none');
            e.preventDefault();
            return false;
        }
        if (!strong.test(pwd)) {
            errorDiv.innerText = 'A senha não atende aos requisitos de segurança.';
            errorDiv.classList.remove('d-none');
            e.preventDefault();
            return false;
        }
    });
});