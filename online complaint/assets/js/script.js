/**
 * Online Complaint Registration System - JavaScript Helper
 */

document.addEventListener('DOMContentLoaded', function () {
    // Enable Bootstrap Tooltips everywhere
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // File Upload Preview & Size Validation
    const fileInput = document.getElementById('attachment');
    const previewContainer = document.getElementById('file-preview-container');
    
    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', function (e) {
            previewContainer.innerHTML = '';
            const file = e.target.files[0];
            
            if (!file) return;

            // Max file size 5MB (5 * 1024 * 1024 bytes)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File size exceeds the 5MB limit. Please select a smaller file.');
                fileInput.value = '';
                return;
            }

            const fileSizeFormatted = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            const fileName = file.name;
            const fileExt = fileName.split('.').pop().toLowerCase();

            const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt);

            const card = document.createElement('div');
            card.className = 'card p-2 mt-2 bg-light border d-flex flex-row align-items-center justify-content-between';

            if (isImage) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    card.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="${event.target.result}" alt="Preview" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <div class="fw-semibold small text-truncate" style="max-width: 200px;">${fileName}</div>
                                <span class="badge bg-secondary">${fileSizeFormatted}</span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-file">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    `;
                    setupRemoveButton();
                };
                reader.readAsDataURL(file);
            } else {
                card.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded p-2 me-3 text-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-file-pdf fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small text-truncate" style="max-width: 200px;">${fileName}</div>
                            <span class="badge bg-secondary">${fileSizeFormatted}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-file">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                `;
                setupRemoveButton();
            }

            previewContainer.appendChild(card);

            function setupRemoveButton() {
                const removeBtn = document.getElementById('btn-remove-file');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        fileInput.value = '';
                        previewContainer.innerHTML = '';
                    });
                }
            }
        });
    }

    // Password Match Validation in Citizen Registration
    const regForm = document.getElementById('citizenRegistrationForm');
    if (regForm) {
        regForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match! Please check again.');
            }
        });
    }
});

/**
 * Copy text helper function (for Complaint IDs)
 */
function copyComplaintId(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function () {
            alert('Complaint ID copied to clipboard: ' + text);
        }).catch(function () {
            prompt('Copy your Complaint ID:', text);
        });
    } else {
        prompt('Copy your Complaint ID:', text);
    }
}

/**
 * Confirm delete action dialog
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed with this action? This cannot be undone.');
}
