// public/js/user-form.js

document.addEventListener('DOMContentLoaded', function() {

    // Initialize Dropify with proper event handling
    $('.dropify').dropify({
        messages: {
            'default': 'Drag and drop a file here or click',
            'replace': 'Drag and drop or click to replace',
            'remove': 'Remove',
            'error': 'Ooops, something wrong happened.'
        },
        error: {
            'fileSize': 'The file size is too big ({{ value }} max).',
            'minWidth': 'The image width is too small ({{ value }}}px min).',
            'maxWidth': 'The image width is too big ({{ value }}}px max).',
            'minHeight': 'The image height is too small ({{ value }}}px min).',
            'maxHeight': 'The image height is too big ({{ value }}}px max).',
            'imageFormat': 'The image format is not allowed ({{ value }} only).'
        }
    });

    // Handle Dropify file removal and deletion from directory
    $('.dropify').on('dropify.beforeClear', function(event, element) {
        // Use jQuery to get the data attribute
        const $element = $(element);
        const currentFile = $element.data('default-file');

        if (currentFile) {
            // Show confirmation dialog
            Swal.fire({
                title: 'Delete Image?',
                text: 'Are you sure you want to delete this image? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete file from server
                    deleteImageFromServer(currentFile, $element);
                } else {
                    // Prevent the clear action
                    return false;
                }
            });

            // Always prevent default until user confirms
            return false;
        }
    });

    // Function to delete image from server directory
    function deleteImageFromServer(filePath, $dropifyElement) {
        // Show loading
        Swal.fire({
            title: 'Deleting Image...',
            text: 'Please wait while we delete the image',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Extract filename from path
        const fileName = filePath.split('/').pop();
        const userId = getUserId();

        fetch('/user/delete-image', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                filename: fileName,
                userId: userId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Successfully deleted from server, now clear dropify
                    $dropifyElement.dropify('reset');

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Image has been deleted successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to delete image from server.',
                        allowOutsideClick: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Network error occurred while deleting image.',
                    allowOutsideClick: false
                });
            });
    }

    // Helper function to get user ID
    function getUserId() {
        const form = document.getElementById('user-form');
        // Try to get from data attribute first
        if (form.dataset.userId) {
            return form.dataset.userId;
        }

        // Try to get from URL or other elements
        const urlParts = window.location.pathname.split('/');
        const editIndex = urlParts.indexOf('edit');
        if (editIndex !== -1 && urlParts[editIndex + 1]) {
            return urlParts[editIndex + 1];
        }

        // Try to get from hidden input or other form elements
        const userIdInput = form.querySelector('input[name*="id"]');
        if (userIdInput && userIdInput.value) {
            return userIdInput.value;
        }

        return null;
    }

    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Custom password validation
    function validatePasswords() {
        const password = document.querySelector('input[name="user[password][first]"]');
        const confirmPassword = document.querySelector('input[name="user[password][second]"]');

        if (!password || !confirmPassword) return true;

        let isValid = true;
        const isEdit = getUserId() !== null;

        // Clear previous errors
        password.classList.remove('is-invalid');
        confirmPassword.classList.remove('is-invalid');

        // For edit mode, password is only required if one of the fields has a value
        const passwordHasValue = password.value.length > 0;
        const confirmHasValue = confirmPassword.value.length > 0;

        // Check if password is required (new user or editing with password entered)
        if (!isEdit && password.value.length === 0) {
            password.classList.add('is-invalid');
            password.parentElement.nextElementSibling.textContent = 'Password is required';
            isValid = false;
        }

        // If either password field has a value, validate both
        if (passwordHasValue || confirmHasValue) {
            // Check password length
            if (password.value.length > 0 && password.value.length < 6) {
                password.classList.add('is-invalid');
                password.parentElement.nextElementSibling.textContent = 'Password must be at least 6 characters long';
                isValid = false;
            }

            // Check if passwords match
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                confirmPassword.parentElement.nextElementSibling.textContent = 'Passwords do not match';
                isValid = false;
            }

            // In edit mode, if one field has value, both must have values
            if (isEdit) {
                if (passwordHasValue && !confirmHasValue) {
                    confirmPassword.classList.add('is-invalid');
                    confirmPassword.parentElement.nextElementSibling.textContent = 'Please confirm your password';
                    isValid = false;
                } else if (!passwordHasValue && confirmHasValue) {
                    password.classList.add('is-invalid');
                    password.parentElement.nextElementSibling.textContent = 'Please enter a password';
                    isValid = false;
                }
            }
        }

        return isValid;
    }

    // Real-time password validation
    document.querySelectorAll('.password-input').forEach(input => {
        input.addEventListener('input', function() {
            setTimeout(validatePasswords, 100);
        });
        input.addEventListener('blur', validatePasswords);
    });

    // Form validation
    function validateForm() {
        const form = document.getElementById('user-form');
        let isValid = true;

        // Clear previous errors
        clearFormErrors();

        // Validate required fields
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback') ||
                    field.closest('.mb-3').querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = 'This field is required';
                }
                isValid = false;
            }
        });

        // Validate email
        const emailField = document.querySelector('input[type="email"]');
        if (emailField && emailField.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                const feedback = emailField.parentElement.querySelector('.invalid-feedback') ||
                    emailField.closest('.mb-3').querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = 'Please enter a valid email address';
                }
                isValid = false;
            }
        }

        // Validate passwords
        if (!validatePasswords()) {
            isValid = false;
        }

        return isValid;
    }

    // Form submission with AJAX
    const form = document.getElementById('user-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!validateForm()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please correct the errors below and try again',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            const submitSpinner = document.getElementById('submit-spinner');
            const submitText = document.getElementById('submit-text');
            const originalText = submitText.textContent;
            const isUpdate = getUserId() !== null;

            // Show loading state
            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            submitText.textContent = isUpdate ? 'Updating...' : 'Creating...';

            // Block UI
            Swal.fire({
                title: isUpdate ? 'Updating User...' : 'Creating User...',
                text: 'Please wait while we save the user information',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        Swal.close();
                        displayFormErrors(data.errors);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Network error occurred. Please try again.',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('d-none');
                    submitText.textContent = originalText;
                });
        });
    }

    function clearFormErrors() {
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.textContent = '';
        });
    }

    function displayFormErrors(errors) {
        for (const fieldName in errors) {
            if (fieldName === 'form') continue;

            const field = document.querySelector(`[name*="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback') ||
                    field.closest('.mb-3').querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[fieldName].join(', ');
                }
            }
        }

        // Show general form errors
        if (errors.form) {
            Swal.fire({
                icon: 'error',
                title: 'Form Error!',
                text: errors.form.join(', '),
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        }
    }

});
