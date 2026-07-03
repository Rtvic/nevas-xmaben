// public/assets/js/qr_generator.js

let currentQR = null;
let currentCode = null;
let savedTemplates = [];

// Initialize QR Styling engine
const qrStyling = new QRCodeStyling({
    width: 300,
    height: 300,
    type: "svg",
    data: "https://qr.io",
    dotsOptions: { color: "#000000", type: "square" },
    backgroundOptions: { color: "#ffffff" },
    imageOptions: { crossOrigin: "anonymous", margin: 10 }
});

// Type switching logic for creation modal
function setQRType(type) {
    document.getElementById('qr_type').value = type;
    document.querySelectorAll('.btn-type').forEach(b => b.classList.remove('active'));
    document.getElementById('btnType' + type.charAt(0).toUpperCase() + type.slice(1)).classList.add('active');

    document.querySelectorAll('.type-content').forEach(d => d.style.display = 'none');
    document.getElementById('content_' + type).style.display = 'block';
    const activeBtn = document.getElementById('btnType' + type.charAt(0).toUpperCase() + type.slice(1));
    activeBtn.style.background = '#4f46e5';
    activeBtn.style.color = 'white';
    activeBtn.style.borderColor = '#4f46e5';
}

// Create Link Handler
document.getElementById('createLinkForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const type = document.getElementById('qr_type').value;
    const campaign = document.getElementById('campaign').value;
    const custom_slug = document.getElementById('custom_slug') ? document.getElementById('custom_slug').value : '';

    const formData = new FormData();
    if (document.getElementById('edit_link_id')?.value) {
        formData.append('id', document.getElementById('edit_link_id').value);
    }

    let content = null;
    let destination_url = '';

    if (type === 'url') {
        destination_url = document.getElementById('destination_url').value;
    } else if (type === 'vcard') {
        content = {
            name: document.getElementById('vName').value,
            last: document.getElementById('vLast').value,
            phone: document.getElementById('vPhone').value,
            email: document.getElementById('vEmail').value,
            work: document.getElementById('vWork').value
        };
    } else if (type === 'wifi') {
        content = {
            ssid: document.getElementById('wifiSsid').value,
            pass: document.getElementById('wifiPass').value,
            enc: document.getElementById('wifiEnc').value
        };
    } else if (type === 'whatsapp') {
        content = {
            phone: document.getElementById('waPhone').value,
            msg: document.getElementById('waMsg').value
        };
    } else if (type === 'social') {
        content = {
            insta: document.getElementById('sInsta').value,
            tiktok: document.getElementById('sTiktok').value,
            fb: document.getElementById('sFb').value,
            yt: document.getElementById('sYt').value,
            li: document.getElementById('sLi').value,
            tw: document.getElementById('sTw').value,
            web: document.getElementById('sWeb').value
        };

        // Collect Theme Data (PRO)
        const sTheme = document.getElementById('sTheme');
        if (sTheme) {
            const themeData = {
                preset: sTheme.value,
                color: document.getElementById('sColor').value,
                font: document.getElementById('sFont').value
            };
            formData.append('theme_data', JSON.stringify(themeData));
        }
    }

    formData.append('type', type);
    formData.append('campaign', campaign);
    formData.append('custom_slug', custom_slug);
    formData.append('destination_url', destination_url);
    if (content) formData.append('content', JSON.stringify(content));

    const apiUrl = formData.has('id') ? 'api/edit_link.php' : 'api/create_link.php';

    if (type === 'pdf') {
        const fileInput = document.getElementById('pdfFile');
        if (fileInput.files.length > 0) {
            formData.append('pdfFile', fileInput.files[0]);
        } else if (!formData.has('id')) {
            alert('Por favor selecciona un archivo PDF');
            return;
        }
    }

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.code || data.id) {
            closeCreateModal();
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo crear el link'));
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

function showQR(code, savedDesign = {}) {
    currentCode = code;
    document.getElementById('qrModal').style.display = 'flex';
    document.getElementById('qrModalTitle').innerText = 'Personalizar Código: ' + code;

    // Reset design fields to defaults or saved values
    document.getElementById('dotsStyle').value = savedDesign.dots_style || 'square';
    document.getElementById('cornersStyle').value = savedDesign.corners_square_style || 'square';
    document.getElementById('qrColor').value = savedDesign.fg_color || '#000000';
    document.getElementById('bgColor').value = savedDesign.bg_color || '#ffffff';
    document.getElementById('logoUrl').value = savedDesign.logo_path || '';
    document.getElementById('labelText').value = savedDesign.label_text || '';
    document.getElementById('frameType').value = savedDesign.frame_type || 'none';
    document.getElementById('frameColor').value = savedDesign.frame_color || '#4f46e5';

    // Gradients
    document.getElementById('fgGradientType').value = savedDesign.fg_gradient_type || 'none';
    document.getElementById('qrColor2').value = savedDesign.fg_color_2 || '#4f46e5';
    document.getElementById('fgRotation').value = savedDesign.fg_gradient_rotation || 0;

    document.getElementById('bgGradientType').value = savedDesign.bg_gradient_type || 'none';
    document.getElementById('bgColor2').value = savedDesign.bg_color_2 || '#f1f5f9';
    document.getElementById('bgRotation').value = savedDesign.bg_gradient_rotation || 0;

    toggleGradientOptions('fg');
    toggleGradientOptions('bg');

    // Load templates
    loadTemplates();

    const path = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    const shortUrl = window.location.origin + path + "r/" + code;
    updateLivePreview(shortUrl);
}

function toggleGradientOptions(prefix) {
    const type = document.getElementById(prefix + 'GradientType').value;
    const optionsDiv = document.getElementById(prefix + 'GradientOptions');
    optionsDiv.style.display = (type === 'none') ? 'none' : 'block';
    updateLivePreview();
}

async function loadTemplates() {
    try {
        const response = await fetch('api/get_templates.php');
        savedTemplates = await response.json();
        const select = document.getElementById('templateSelect');
        select.innerHTML = '<option value="">-- Elige una plantilla --</option>';
        savedTemplates.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.innerText = t.name;
            select.appendChild(opt);
        });
    } catch (e) { console.error(e); }
}

function applySelectedTemplate() {
    const id = document.getElementById('templateSelect').value;
    const template = savedTemplates.find(t => t.id == id);
    if (!template) return;

    document.getElementById('dotsStyle').value = template.dots_style;
    document.getElementById('cornersStyle').value = template.corners_square_style;
    document.getElementById('qrColor').value = template.fg_color;
    document.getElementById('bgColor').value = template.bg_color;
    document.getElementById('logoUrl').value = template.logo_path || '';
    document.getElementById('labelText').value = template.label_text || '';
    document.getElementById('frameType').value = template.frame_type;
    document.getElementById('frameColor').value = template.frame_color;

    document.getElementById('fgGradientType').value = template.fg_gradient_type || 'none';
    document.getElementById('qrColor2').value = template.fg_color_2 || '#000000';
    document.getElementById('fgRotation').value = template.fg_gradient_rotation || 0;

    document.getElementById('bgGradientType').value = template.bg_gradient_type || 'none';
    document.getElementById('bgColor2').value = template.bg_color_2 || '#ffffff';
    document.getElementById('bgRotation').value = template.bg_gradient_rotation || 0;

    toggleGradientOptions('fg');
    toggleGradientOptions('bg');

    updateLivePreview();
}

async function saveAsTemplatePrompt() {
    const name = prompt('Nombre para esta plantilla:', 'Mi Estilo');
    if (!name) return;

    const design = {
        dotsStyle: document.getElementById('dotsStyle').value,
        cornersStyle: document.getElementById('cornersStyle').value,
        fgColor: document.getElementById('qrColor').value,
        bgColor: document.getElementById('bgColor').value,
        logoUrl: document.getElementById('logoUrl').value,
        label_text: document.getElementById('labelText').value,
        frameType: document.getElementById('frameType').value,
        frameColor: document.getElementById('frameColor').value,
        fgColor2: document.getElementById('qrColor2').value,
        fgGradientType: document.getElementById('fgGradientType').value,
        fgRotation: document.getElementById('fgRotation').value,
        bgColor2: document.getElementById('bgColor2').value,
        bgGradientType: document.getElementById('bgGradientType').value,
        bgRotation: document.getElementById('bgRotation').value
    };

    try {
        const response = await fetch('api/save_template.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, design })
        });
        const data = await response.json();
        if (data.success) {
            alert('¡Plantilla guardada!');
            loadTemplates();
        }
    } catch (e) { console.error(e); }
}

function updateLivePreview(url = null) {
    if (!url) {
        const path = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
        url = window.location.origin + path + "r/" + currentCode;
    }

    const dotsStyle = document.getElementById('dotsStyle').value;
    const cornersStyle = document.getElementById('cornersStyle').value;
    const qrColor = document.getElementById('qrColor').value;
    const bgColor = document.getElementById('bgColor').value;
    const logoUrl = document.getElementById('logoUrl').value;
    const labelText = document.getElementById('labelText').value;
    const frameType = document.getElementById('frameType').value;
    const frameColor = document.getElementById('frameColor').value;

    const fgGradientType = document.getElementById('fgGradientType').value;
    const qrColor2 = document.getElementById('qrColor2').value;
    const fgRotation = document.getElementById('fgRotation').value;

    const bgGradientType = document.getElementById('bgGradientType').value;
    const bgColor2 = document.getElementById('bgColor2').value;
    const bgRotation = document.getElementById('bgRotation').value;

    const dotsOptions = { type: dotsStyle, color: qrColor };
    if (fgGradientType !== 'none') {
        dotsOptions.gradient = {
            type: fgGradientType,
            rotation: (fgRotation * Math.PI) / 180,
            colorStops: [{ offset: 0, color: qrColor }, { offset: 1, color: qrColor2 }]
        };
        delete dotsOptions.color;
    }

    const backgroundOptions = { color: bgColor };
    if (bgGradientType !== 'none') {
        backgroundOptions.gradient = {
            type: bgGradientType,
            rotation: (bgRotation * Math.PI) / 180,
            colorStops: [{ offset: 0, color: bgColor }, { offset: 1, color: bgColor2 }]
        };
        delete backgroundOptions.color;
    }

    qrStyling.update({
        data: url,
        dotsOptions: dotsOptions,
        cornersSquareOptions: { type: cornersStyle, color: qrColor },
        cornersDotOptions: { type: cornersStyle, color: qrColor },
        backgroundOptions: backgroundOptions,
        image: logoUrl || ""
    });

    const qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = "";
    qrContainer.style.background = bgColor;
    qrContainer.style.padding = "15px";
    qrContainer.style.borderRadius = "12px";
    qrContainer.style.position = "relative";
    qrContainer.style.border = "none";

    if (frameType === 'basic') {
        qrContainer.style.border = `4px solid ${frameColor}`;
    } else if (frameType === 'header' || frameType === 'label') {
        qrContainer.style.paddingBottom = "50px";
        qrContainer.style.border = `2px solid ${frameColor}`;
    }

    qrStyling.append(qrContainer);

    if (labelText && frameType !== 'none') {
        const labelDiv = document.createElement('div');
        labelDiv.innerText = labelText;
        labelDiv.style.position = "absolute";
        labelDiv.style.left = "0";
        labelDiv.style.right = "0";
        labelDiv.style.backgroundColor = frameColor;
        labelDiv.style.color = "white";
        labelDiv.style.padding = "8px";
        labelDiv.style.textAlign = "center";
        labelDiv.style.fontWeight = "bold";
        labelDiv.style.fontSize = "0.9rem";

        if (frameType === 'header') {
            labelDiv.style.top = "0";
            qrContainer.style.paddingTop = "40px";
            qrContainer.style.paddingBottom = "15px";
        } else {
            labelDiv.style.bottom = "0";
        }
        qrContainer.appendChild(labelDiv);
    }
}

async function saveDesign() {
    const design = {
        dotsStyle: document.getElementById('dotsStyle').value,
        cornersStyle: document.getElementById('cornersStyle').value,
        fgColor: document.getElementById('qrColor').value,
        bgColor: document.getElementById('bgColor').value,
        logoUrl: document.getElementById('logoUrl').value,
        label_text: document.getElementById('labelText').value,
        frameType: document.getElementById('frameType').value,
        frameColor: document.getElementById('frameColor').value,
        fgColor2: document.getElementById('qrColor2').value,
        fgGradientType: document.getElementById('fgGradientType').value,
        fgRotation: document.getElementById('fgRotation').value,
        bgColor2: document.getElementById('bgColor2').value,
        bgGradientType: document.getElementById('bgGradientType').value,
        bgRotation: document.getElementById('bgRotation').value
    };

    try {
        const response = await fetch('api/save_design.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: currentCode, design: design })
        });
        const data = await response.json();
        if (data.success) {
            alert('¡Diseño guardado con éxito!');
            window.location.reload(); // Reload to update the list view state
        } else {
            alert('Error al guardar: ' + data.error);
        }
    } catch (e) {
        console.error(e);
    }
}

function downloadAs(ext) {
    qrStyling.download({ name: `qr-${currentCode}`, extension: ext });
}

async function deleteLink(id) {
    if (!confirm('¿Estás seguro de eliminar este código QR?')) return;
    try {
        const response = await fetch('api/delete_link.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await response.json();
        if (data.success) window.location.reload();
    } catch (error) { console.error('Error:', error); }
}

// Legacy editLink removed, now using modal-based openEditModal in dashboard.php

async function createNewFolder() {
    const name = prompt('Nombre de la nueva carpeta:');
    if (!name) return;
    try {
        const response = await fetch('api/create_folder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        const data = await response.json();
        if (data.success) window.location.reload();
    } catch (e) { console.error(e); }
}

async function renameFolder(id, currentName) {
    const newName = prompt('Nuevo nombre de la carpeta:', currentName);
    if (!newName || newName === currentName) return;
    try {
        const response = await fetch('api/manage_folders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'rename', folder_id: id, name: newName })
        });
        const data = await response.json();
        if (data.success) window.location.reload();
        else alert('Error: ' + data.error);
    } catch (e) { console.error(e); }
}

async function deleteFolder(id) {
    if (!confirm('¿Estás seguro de eliminar esta carpeta? Los códigos QR que contiene volverán al panel principal.')) return;
    try {
        const response = await fetch('api/manage_folders.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', folder_id: id })
        });
        const data = await response.json();
        if (data.success) {
            // If we are currently viewing this folder, redirect to main and then reload
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('folder_id') == id) {
                window.location.href = 'dashboard.php';
            } else {
                window.location.reload();
            }
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) { console.error(e); }
}

async function toggleArchive(id, currentlyArchived) {
    try {
        const response = await fetch('api/archive_link.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, archived: currentlyArchived ? 0 : 1 })
        });
        const data = await response.json();
        if (data.success) window.location.reload();
    } catch (e) { console.error(e); }
}

let pendingFolderLinkId = null;
function openFolderModal(linkId) {
    pendingFolderLinkId = linkId;
    document.getElementById('folderModal').style.display = 'flex';
}

function closeFolderModal() { document.getElementById('folderModal').style.display = 'none'; }

async function confirmFolderMove() {
    const folderId = document.getElementById('folderSelect').value;
    try {
        const response = await fetch('api/assign_folder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ link_id: pendingFolderLinkId, folder_id: folderId || null })
        });
        const data = await response.json();
        if (data.success) window.location.reload();
    } catch (e) { console.error(e); }
}

// Search Filtering
const searchInput = document.querySelector('input[placeholder="Buscar código QR"]');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.qr-item').forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(term) ? 'flex' : 'none';
        });
    });
}

// Individual Stats Logic
let indivOsChart = null;
let indivBrowserChart = null;
let indivDeviceChart = null;
let indivCountryChart = null;
let dailyChart = null;

async function openIndividualStats(linkId, campaignName) {
    document.getElementById('individualStatsModal').style.display = 'flex';
    document.getElementById('indivStatsTitle').innerText = 'Estadísticas: ' + campaignName;
    document.getElementById('csvExportBtn').onclick = () => window.location.href = `api/export_stats.php?link_id=${linkId}`;

    // Show loading state (optional)
    try {
        const response = await fetch(`api/stats.php?link_id=${linkId}`);
        const data = await response.json();

        document.getElementById('indivTotalScans').innerText = data.total_scans;
        document.getElementById('indivUniqueScans').innerText = data.unique_scans;

        // Destroy old charts
        if (indivOsChart) indivOsChart.destroy();
        if (indivBrowserChart) indivBrowserChart.destroy();
        if (indivDeviceChart) indivDeviceChart.destroy();
        if (indivCountryChart) indivCountryChart.destroy();
        if (dailyChart) dailyChart.destroy();

        // 1. Daily Chart (Line)
        dailyChart = new Chart(document.getElementById('dailyStatsChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.daily.map(i => i.label),
                datasets: [{
                    label: 'Escaneos',
                    data: data.daily.map(i => i.value),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. OS Chart
        indivOsChart = new Chart(document.getElementById('indivOsChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.os.map(i => i.label),
                datasets: [{ data: data.os.map(i => i.value), backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // 3. Browser Chart
        indivBrowserChart = new Chart(document.getElementById('indivBrowserChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.browsers.map(i => i.label),
                datasets: [{ data: data.browsers.map(i => i.value), backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // 4. Device Chart
        indivDeviceChart = new Chart(document.getElementById('indivDeviceChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: data.devices.map(i => i.label),
                datasets: [{ data: data.devices.map(i => i.value), backgroundColor: ['#4f46e5', '#10b981', '#f59e0b'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // 3. Country Chart
        indivCountryChart = new Chart(document.getElementById('indivCountryChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.countries.map(i => i.label),
                datasets: [{ data: data.countries.map(i => i.value), backgroundColor: ['#ec4899', '#f97316', '#06b6d4', '#8b5cf6', '#10b981'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 4. Render Leads
        const leadsSection = document.getElementById('leadsSection');
        const leadsTableBody = document.getElementById('leadsTableBody');
        const exportLeadsBtn = document.getElementById('exportLeadsBtn');

        exportLeadsBtn.onclick = () => {
            window.location.href = `api/export_leads.php?link_id=${linkId}`;
        };

        if (data.leads && data.leads.length > 0) {
            leadsSection.style.display = 'block';
            leadsTableBody.innerHTML = data.leads.map(lead => `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #1e293b; font-weight: 600;">${lead.name}</td>
                    <td style="padding: 1rem; color: #475569;">${lead.email}</td>
                    <td style="padding: 1rem; color: #475569;">${lead.phone || '-'}</td>
                    <td style="padding: 1rem; color: #94a3b8; font-size: 0.8rem;">${new Date(lead.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        } else {
            leadsSection.style.display = 'none';
        }

    } catch (e) { console.error(e); }
}

function closeIndivStats() {
    document.getElementById('individualStatsModal').style.display = 'none';
}

// Bulk Actions Logic
let selectedIds = [];

function updateBulkSelection() {
    selectedIds = Array.from(document.querySelectorAll('.qr-checkbox:checked')).map(cb => cb.value);
    const bar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('bulkCount');

    if (selectedIds.length > 0) {
        bar.style.display = 'flex';
        countSpan.innerText = `${selectedIds.length} seleccionados`;
    } else {
        bar.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.qr-checkbox').forEach(cb => cb.checked = false);
    updateBulkSelection();
}

async function bulkArchive() {
    if (selectedIds.length === 0) return;
    if (!confirm(`¿Archivar ${selectedIds.length} elementos?`)) return;
    performBulkAction('archive');
}

async function bulkDelete() {
    if (selectedIds.length === 0) return;
    if (!confirm(`¿Eliminar permanentemente ${selectedIds.length} elementos?`)) return;
    performBulkAction('delete');
}

function openBulkFolderModal() {
    if (selectedIds.length === 0) return;
    document.getElementById('bulkFolderModal').style.display = 'flex';
}

function closeBulkFolderModal() {
    document.getElementById('bulkFolderModal').style.display = 'none';
}

async function confirmBulkMove() {
    const folderId = document.getElementById('bulkFolderSelect').value;
    performBulkAction('move', folderId);
}

async function performBulkAction(action, folderId = null) {
    try {
        const response = await fetch('api/bulk_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: selectedIds, action, folder_id: folderId })
        });
        const data = await response.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        console.error(e);
    }
}

function applyThemePreset(preset) {
    const colorInput = document.getElementById('sColor');
    const fontSelect = document.getElementById('sFont');
    if (!colorInput || !fontSelect) return;

    switch (preset) {
        case 'glass':
            colorInput.value = '#4f46e5';
            fontSelect.value = "'Plus Jakarta Sans', sans-serif";
            break;
        case 'dark':
            colorInput.value = '#ffffff';
            fontSelect.value = "'Inter', sans-serif";
            break;
        case 'minimal':
            colorInput.value = '#000000';
            fontSelect.value = "'Inter', sans-serif";
            break;
        case 'vibrant':
            colorInput.value = '#ec4899';
            fontSelect.value = "'Outfit', sans-serif";
            break;
        case 'retro':
            colorInput.value = '#22c55e';
            fontSelect.value = "'Roboto Mono', monospace";
            break;
    }
}
