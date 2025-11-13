
const roleButtons = document.querySelectorAll('.role-btn');
const hiddenRoleInput = document.getElementById('role');
const matriculeField = document.getElementById('matriculeField');
const specialiteField = document.getElementById('specialiteField');


roleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        roleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const selectedRole = btn.getAttribute('data-role');
        hiddenRoleInput.value = selectedRole;

        if (selectedRole === 'Etudiant') {
            matriculeField.classList.remove('d-none');
            specialiteField.classList.add('d-none');
        } else if (selectedRole === 'Enseignant') {
            matriculeField.classList.remove('d-none');
            specialiteField.classList.remove('d-none');
        } else {
            matriculeField.classList.add('d-none');
            specialiteField.classList.add('d-none');
        }
    });
});


