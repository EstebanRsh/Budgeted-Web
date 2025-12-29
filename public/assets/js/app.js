document.addEventListener('DOMContentLoaded', () => {
    const root   = document.documentElement;
    const toggle = document.getElementById('theme-toggle');
    const label  = document.getElementById('theme-label');

    function getCurrentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncLabel() {
        if (!label) return;
        const theme = getCurrentTheme();
        label.textContent = (theme === 'dark') ? 'Oscuro' : 'Claro';
    }

    // Al cargar la página sólo sincronizamos el texto
    syncLabel();

    if (!toggle) return;

    toggle.addEventListener('click', () => {
        const current = getCurrentTheme();
        const next = (current === 'dark') ? 'light' : 'dark';

        root.setAttribute('data-theme', next);

        try {
            localStorage.setItem('theme', next);
        } catch (e) {
            // ignoramos errores de localStorage
        }

        syncLabel();
    });

    // ========================================
    // VALIDACIONES DE FORMULARIOS
    // ========================================

    // Validación de email en tiempo real
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !validarEmail(email)) {
                mostrarError(this, 'Email inválido');
            } else {
                limpiarError(this);
            }
        });
    });

    // Validación de teléfono argentino
    const telInputs = document.querySelectorAll('input[name="telefono"]');
    telInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const tel = this.value.trim();
            if (tel && !validarTelefonoAr(tel)) {
                mostrarError(this, 'Teléfono inválido. Formato: +54 11 1234-5678');
            } else {
                limpiarError(this);
            }
        });
    });

    // Validación de CUIT/DNI
    const cuitInputs = document.querySelectorAll('input[name="cuit_dni"]');
    cuitInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const cuit = this.value.trim();
            if (cuit && !validarCuitDni(cuit)) {
                mostrarError(this, 'CUIT/DNI inválido');
            } else {
                limpiarError(this);
            }
        });
    });

    // Validación de contraseña segura - DESACTIVADA TEMPORALMENTE
    /*
    const passwordInputs = document.querySelectorAll('input[name="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const password = this.value;
            const feedback = this.parentElement.querySelector('.password-feedback');

            if (password.length > 0) {
                const isSecure = validarPasswordSegura(password);
                if (!isSecure) {
                    mostrarError(this, 'Mínimo 8 caracteres, una mayúscula, una minúscula y un número');
                } else {
                    limpiarError(this);
                }
            }
        });
    });
    */

    // Validación de números positivos
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        if (input.min && parseFloat(input.min) >= 0) {
            input.addEventListener('blur', function() {
                const val = this.value.trim();
                if (val && (isNaN(val) || parseFloat(val) < 0)) {
                    mostrarError(this, 'Debe ser un número positivo');
                } else {
                    limpiarError(this);
                }
            });
        }
    });

    // Prevenir envío de formularios con errores - DESACTIVADO TEMPORALMENTE
    /*
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const errors = this.querySelectorAll('.is-invalid');
            if (errors.length > 0) {
                e.preventDefault();
                errors[0].focus();
                if (typeof showToast === 'function') {
                    showToast('Por favor corrige los errores en el formulario', 'danger');
                } else {
                    alert('Por favor corrige los errores en el formulario');
                }
            }
        });
    });
    */

    // ========================================
    // FUNCIONES DE VALIDACIÓN
    // ========================================

    function validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validarTelefonoAr(telefono) {
        const tel = telefono.replace(/[\s\-\(\)]/g, '');
        return /^\+?\d{10,13}$/.test(tel);
    }

    function validarCuitDni(cuit) {
        const num = cuit.replace(/[\-\s]/g, '');
        return /^\d{8,11}$/.test(num);
    }

    function validarPasswordSegura(password) {
        if (password.length < 8) return false;
        if (!/[A-Z]/.test(password)) return false;
        if (!/[a-z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        return true;
    }

    function mostrarError(input, mensaje) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        
        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }
        feedback.textContent = mensaje;
    }

    function limpiarError(input) {
        input.classList.remove('is-invalid');
        if (input.value.trim()) {
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
        }
        
        const feedback = input.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
});
