
document.addEventListener('DOMContentLoaded', () => {
    const validateForm = (formId, fields) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function (e) {
            let isValid = true;

            form.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            fields.forEach(field => {
                const input = form.querySelector(`#${field.id}`);
                const errorElement = form.querySelector(`#${field.id}-error`);
                
                if (!input || !errorElement) return;

                if (field.mandatory && input.value.trim() === '') {
                    errorElement.textContent = `${field.label} is required.`;
                    isValid = false;
                } 
                
                else if (field.type === 'email' && input.value.trim() !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        errorElement.textContent = `Please enter a valid email address.`;
                        isValid = false;
                    }
                }

                else if (field.id === 'contact_number' && input.value.trim() !== '') {
                    const phoneRegex = /^[0-9\s-]{7,15}$/;
                    if (!phoneRegex.test(input.value.trim())) {
                        errorElement.textContent = `Invalid contact number format.`;
                        isValid = false;
                    }
                }

                else if (field.id === 'student_id' && input.value.trim() !== '') {
                    const studentIdRegex = /^[A-Za-z0-9]{5,10}$/;
                    if (!studentIdRegex.test(input.value.trim())) {
                        errorElement.textContent = `Student ID must be 5-10 alphanumeric characters.`;
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                e.preventDefault(); 
            }
        });
    };

    validateForm('event-registration-form', [
        { id: 'name', label: 'Name', mandatory: true },
        { id: 'student_id', label: 'Student ID', mandatory: true },
        { id: 'email', label: 'Email', mandatory: true, type: 'email' },
        { id: 'contact_number', label: 'Contact Number', mandatory: false, type: 'text' },
    ]);
    
    validateForm('user-register-form', [
        { id: 'reg_name', label: 'Name', mandatory: true },
        { id: 'reg_student_id', label: 'Student ID', mandatory: true },
        { id: 'reg_email', label: 'Email', mandatory: true, type: 'email' },
        { id: 'reg_password', label: 'Password', mandatory: true },
    ]);
    
    validateForm('user-login-form', [
        { id: 'login_email', label: 'Email', mandatory: true, type: 'email' },
        { id: 'login_password', label: 'Password', mandatory: true },
    ]);
});


