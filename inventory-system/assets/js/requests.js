// Status update modal templates
const statusModals = {
    // Form template for receiving a request
    receive: `
        <form id="receiveForm" class="needs-validation" novalidate>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Request Type</label>
                            <select class="form-select" name="request_type" required>
                                <option value="">Select Type</option>
                                <option value="Hardware">Hardware</option>
                                <option value="Software">Software</option>
                                <option value="Network">Network</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial/Property Number</label>
                            <input type="text" class="form-control" name="serial_property_number" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Other Details</label>
                            <textarea class="form-control" name="other_details" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Evaluation Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Pre-maintenance Evaluation</label>
                            <textarea class="form-control" name="pre_maintenance_eval" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Inspected By</label>
                            <input type="text" class="form-control" name="inspected_by" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Inspection Date</label>
                            <input type="date" class="form-control" name="inspection_date" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Reception Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Received By</label>
                            <input type="text" class="form-control" name="received_by" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Received</label>
                            <input type="date" class="form-control" name="date_received" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Approved By</label>
                            <input type="text" class="form-control" name="approved_by">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `,
    
    // Form template for completing a request
    complete: `
        <form id="completeForm" class="needs-validation" novalidate>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Maintenance Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Corrective Action</label>
                            <textarea class="form-control" name="corrective_action" rows="3" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Result</label>
                            <textarea class="form-control" name="result" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Recommendation</label>
                            <textarea class="form-control" name="recommendation" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accomplished By</label>
                            <input type="text" class="form-control" name="accomplished_by" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date Accomplished</label>
                            <input type="date" class="form-control" name="date_accomplished" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Degree</label>
                            <select class="form-select" name="service_degree" required>
                                <option value="">Select Degree</option>
                                <option value="Minor">Minor</option>
                                <option value="Major">Major</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Disposal Information</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="forDisposal" name="for_disposal">
                        <label class="form-check-label" for="forDisposal">Mark for Disposal</label>
                    </div>
                </div>
                <div class="card-body" id="disposalFields" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Disposal Type</label>
                            <select class="form-select" name="disposal_type">
                                <option value="">Select Type</option>
                                <option value="Condemned">Condemned</option>
                                <option value="Donated">Donated</option>
                                <option value="Sold">Sold</option>
                                <option value="Recycled">Recycled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Equipment Type</label>
                            <input type="text" class="form-control" name="disposal_equipment_type">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Property Number</label>
                            <input type="text" class="form-control" name="disposal_property_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" class="form-control" name="disposal_serial_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmed By</label>
                            <input type="text" class="form-control" name="disposal_confirmed_by">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accepted By</label>
                            <input type="text" class="form-control" name="disposal_accepted_by">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `
};

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle disposal fields toggle in add request form
    const forDisposalCheckbox = document.getElementById('forDisposal');
    const disposalFields = document.querySelector('.disposal-fields');
    
    if (forDisposalCheckbox && disposalFields) {
        forDisposalCheckbox.addEventListener('change', function() {
            disposalFields.style.display = this.checked ? 'block' : 'none';
            
            // Toggle required attribute on disposal fields
            const fields = disposalFields.querySelectorAll('input, select');
            fields.forEach(field => {
                field.required = this.checked;
            });
        });
    }
});

// View request details
function viewRequest(id, mode = 'view') {
    fetch(`../php/maintenance/get_request.php?id=${id}&mode=${mode}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('viewRequestModal'));
                document.getElementById('requestDetails').innerHTML = data.html;
                
                if (mode === 'edit') {
                    // Handle form submission
                    document.getElementById('editRequestForm').onsubmit = function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        formData.append('id', id);
                        
                        fetch(`../php/maintenance/get_request.php?id=${id}&mode=update`, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                modal.hide();
                                showToast('success', 'Request updated successfully');
                                location.reload();
                            } else {
                                showToast('error', result.message || 'Error updating request');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('error', 'Error updating request');
                        });
                    };
                }
                
                modal.show();
            } else {
                showToast('error', data.message || 'Error loading request details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error loading request details');
        });
}

// Submit new request
function submitRequest() {
    const form = document.getElementById('requestForm');
    const formData = new FormData(form);
    
    // Add date requested
    formData.append('date_requested', new Date().toISOString().split('T')[0]);

    fetch('../php/maintenance/add_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal and show success message
            bootstrap.Modal.getInstance(document.getElementById('addRequestModal')).hide();
            alert('Request added successfully');
            location.reload();
        } else {
            alert(data.message || 'Error adding request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding request');
    });
}

// Show status update modal
function updateStatus(id) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    // This line loads the receive form template into the modal body
    document.getElementById('statusModalBody').innerHTML = statusModals.receive;

    // First fetch existing request data
    fetch(`../php/maintenance/get_request.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statusModalBody').innerHTML = statusModals.receive;
                
                // Populate form with existing data
                const form = document.getElementById('receiveForm');
                if (data.request) {
                    for (const [key, value] of Object.entries(data.request)) {
                        const input = form.elements[key];
                        if (input) {
                            input.value = value;
                        }
                    }
                }
                
                // Add loading state to save button
                const saveBtn = document.getElementById('saveStatus');
                saveBtn.onclick = function() {
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                    
                    const formData = new FormData(form);
                    formData.append('id', id);
                    formData.append('action', 'receive');
                    
                    submitStatusUpdate(formData, modal, saveBtn);
                };
                
                modal.show();
            } else {
                showToast('error', 'Error loading request details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error loading request details');
        });
}

// Complete request
function completeRequest(id) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    
    // First fetch existing request data
    fetch(`../php/maintenance/get_request.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statusModalBody').innerHTML = statusModals.complete;
                
                // Populate form with existing data
                const form = document.getElementById('completeForm');
                if (data.request) {
                    for (const [key, value] of Object.entries(data.request)) {
                        const input = form.elements[key];
                        if (input && input.type === 'checkbox') {
                            input.checked = value === '1' || value === true;
                        } else if (input) {
                            input.value = value;
                        }
                    }
                }
                
                // Handle disposal fields visibility
                const disposalFields = document.getElementById('disposalFields');
                const forDisposalCheckbox = document.getElementById('forDisposal');
                
                forDisposalCheckbox.onchange = function() {
                    disposalFields.style.display = this.checked ? 'block' : 'none';
                    const fields = disposalFields.querySelectorAll('input, select');
                    fields.forEach(field => field.required = this.checked);
                };
                
                // Trigger change event to set initial state
                forDisposalCheckbox.dispatchEvent(new Event('change'));
                
                // Add loading state to save button
                const saveBtn = document.getElementById('saveStatus');
                saveBtn.onclick = function() {
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }
                    
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                    
                    const formData = new FormData(form);
                    formData.append('id', id);
                    formData.append('action', forDisposalCheckbox.checked ? 'dispose' : 'complete');
                    
                    submitStatusUpdate(formData, modal, saveBtn);
                };
                
                modal.show();
            } else {
                showToast('error', 'Error loading request details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error loading request details');
        });
}

// Submit status update
function submitStatusUpdate(formData, modal) {
    fetch('../api/maintenance/update_request_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Request updated successfully');
            modal.hide();
            location.reload();
        } else {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
            showToast('error', data.message || 'Error updating request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Changes';
        showToast('error', 'Error updating request');
    });
}

// Helper function to show toast notifications
function showToast(type, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);   
    new bootstrap.Toast(toast).show();    toastContainer.appendChild(toast);
}

// Helper function to create toast container
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

// Export to Excel function
function exportToExcel() {
    // Get table data
    const table = document.querySelector('table');
    const rows = Array.from(table.querySelectorAll('tr'));
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add headers
    const headers = Array.from(rows[0].querySelectorAll('th'))
        .map(header => header.textContent.trim())
        .join(',');
    csvContent += headers + "\r\n";
    // Add data rows
    rows.slice(1).forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        const rowData = cells.map(cell => {
            // Get text content and clean it
            let text = cell.textContent.trim();
            // Remove any commas to avoid CSV issues
            text = text.replace(/,/g, ' ');
            // Wrap in quotes if contains spaces
            return text.includes(' ') ? `"${text}"` : text;
        });
        csvContent += rowData.join(',') + "\r\n";
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'maintenance_requests_' + formatDate(new Date()) + '.csv');
    document.body.appendChild(link);
    link.click();   document.body.appendChild(link);
    document.body.removeChild(link);    link.click();
}

// Helper function to format date for filename
function formatDate(date) {

    return date.toISOString().split('T')[0];
}
function printRequest(id) {
    // Open print view in a new window
    const printWindow = window.open('../php/maintenance/print_request.php?id=' + id, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}