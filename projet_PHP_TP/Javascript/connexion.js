document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
// document.getElementById('loginForm').addEventListener('submit', function(e) {
//     e.preventDefault();
//     const email = document.getElementById('email').value;
//     const password = document.getElementById('password').value;
//     const rememberMe = document.getElementById('rememberMe').checked;
//     if (!email || !password) {
//         alert('Veuillez remplir tous les champs');
//         return;
//     }
//     const loginBtn = document.querySelector('.btn-login');
//     const originalText = loginBtn.innerHTML;
//     loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Connexion...';
//     loginBtn.disabled = true;
//     setTimeout(() => {
//         loginBtn.innerHTML = originalText;
//         loginBtn.disabled = false;
//         alert(`Connexion réussie! Bienvenue, ${email}`);
//     }, 1500);
// });