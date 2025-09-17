import * as pdfjsLib from './pdfjs/pdf.mjs';

if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = './assets/js/pdfjs/pdf.worker.mjs';
}

document.addEventListener('DOMContentLoaded', function() {

    // ===== 1. LOGIKA DROPDOWN AVATAR =====
    const avatarButton = document.getElementById('avatarButton');
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (avatarButton && dropdownMenu) {
        avatarButton.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
    }
    window.addEventListener('click', () => {
        if (dropdownMenu && dropdownMenu.classList.contains('show')) {
            dropdownMenu.classList.remove('show');
        }
    });

    // ===== 2. LOGIKA THEME SWITCHER =====
    const themeSwitcherButton = document.getElementById('theme-switcher-button');
    if (themeSwitcherButton) {
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark-mode') {
            document.body.classList.add('dark-mode');
        }
        themeSwitcherButton.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark-mode' : 'light');
        });
    }

    // ===== 3. LOGIKA NAVIGASI & TABEL DISPOSISI =====
    const allNavLinks = document.querySelectorAll('.nav-link');
    const contentSections = document.querySelectorAll('.content-section');

    let disposisiState = {
        allData: [],
        filteredData: [],
        currentPage: 1,
        entriesPerPage: 10,
        searchTerm: '',
        filterStatus: 'all'
    };
    
    const statusModal = document.getElementById('status-modal');
    const statusModalClose = statusModal?.querySelector('.modal-close-status');
    const pdfModal = document.getElementById('pdf-modal');
    const pdfModalClose = pdfModal?.querySelector('.modal-close-pdf');
    const pdfCanvas = document.getElementById('pdf-canvas');
    const pdfTitle = document.getElementById('pdf-modal-title');
    const pdfPrevBtn = document.getElementById('pdf-prev-btn');
    const pdfNextBtn = document.getElementById('pdf-next-btn');
    const pdfPageNum = document.getElementById('pdf-page-indicator');
    const pdfLoader = document.getElementById('pdf-loader');
    const pdfDownloadBtn = document.getElementById('pdf-download-btn');

    let pdfState = {
        pdfDoc: null,
        pageNum: 1,
        pageRendering: false,
        pageNumPending: null,
        loadingTask: null,
        currentFileToken: ''
    };

    function renderPage(num) {
        pdfState.pageRendering = true;
        pdfState.pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: 1.5 });
            pdfCanvas.height = viewport.height;
            pdfCanvas.width = viewport.width;
            const renderContext = { canvasContext: pdfCanvas.getContext('2d'), viewport };
            page.render(renderContext).promise.then(() => {
                pdfState.pageRendering = false;
                if (pdfState.pageNumPending !== null) {
                    renderPage(pdfState.pageNumPending);
                    pdfState.pageNumPending = null;
                }
            });
        });
        pdfPageNum.textContent = `Halaman ${num} dari ${pdfState.pdfDoc.numPages}`;
        pdfPrevBtn.disabled = num <= 1;
        pdfNextBtn.disabled = num >= pdfState.pdfDoc.numPages;
    }

    function queueRenderPage(num) {
        if (pdfState.pageRendering) pdfState.pageNumPending = num;
        else renderPage(num);
    }

    pdfPrevBtn?.addEventListener('click', () => { if (pdfState.pageNum > 1) { pdfState.pageNum--; queueRenderPage(pdfState.pageNum); } });
    pdfNextBtn?.addEventListener('click', () => { if (pdfState.pageNum < pdfState.pdfDoc.numPages) { pdfState.pageNum++; queueRenderPage(pdfState.pageNum); } });
    pdfDownloadBtn?.addEventListener('click', () => { if (pdfState.currentFileToken) window.open(`backend/download_watermarked.php?token=${pdfState.currentFileToken}`, '_blank'); });

    function openModal(modalElement, content, title) {
        if (modalElement.id === 'status-modal') {
            modalElement.querySelector('.modal-body-status').innerHTML = content;
        } else if (modalElement.id === 'pdf-modal') {
            pdfState.currentFileToken = title.token;
            pdfTitle.textContent = title.title;
            pdfLoader.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Memuat Dokumen...";
            pdfLoader.style.display = 'flex';
            pdfCanvas.style.display = 'none';
            if (pdfState.loadingTask) pdfState.loadingTask.destroy();
            pdfState.loadingTask = pdfjsLib.getDocument(content);
            pdfState.loadingTask.promise.then(pdfDoc_ => {
                pdfState.pdfDoc = pdfDoc_;
                pdfState.pageNum = 1;
                pdfLoader.style.display = 'none';
                pdfCanvas.style.display = 'block';
                renderPage(pdfState.pageNum);
            }).catch(reason => {
                console.error('Error loading PDF:', reason);
                pdfLoader.innerHTML = "Gagal memuat dokumen.";
            });
        }
        modalElement.style.display = "block";
    }

    function closeModal(modalElement) {
        if (modalElement) modalElement.style.display = "none";
        if (modalElement && modalElement.id === 'pdf-modal') {
            if (pdfState.loadingTask) pdfState.loadingTask.destroy();
            if (pdfState.pdfDoc) pdfState.pdfDoc.destroy();
            pdfState = { pdfDoc: null, pageNum: 1, pageRendering: false, pageNumPending: null, loadingTask: null, currentFileToken: '' };
        }
    }

    if(statusModalClose) statusModalClose.onclick = () => closeModal(statusModal);
    if(pdfModalClose) pdfModalClose.onclick = () => closeModal(pdfModal);
    window.onclick = function(event) {
        if (event.target == statusModal) closeModal(statusModal);
        if (event.target == pdfModal) closeModal(pdfModal);
    }

    function renderDisposisiTable() {
        const tableBody = document.getElementById('disposisi-table-body');
        if (!tableBody) return;

        let dataToProcess = disposisiState.allData;
        if (disposisiState.filterStatus !== 'all') {
            dataToProcess = disposisiState.allData.filter(surat => surat.is_balas === disposisiState.filterStatus);
        }

        const searchTerm = disposisiState.searchTerm.toLowerCase();
        disposisiState.filteredData = dataToProcess.filter(surat => {
            const searchFields = [surat.no_agenda, surat.no_surat, surat.pengirim, surat.perihal, surat.s_surat];
            return searchFields.some(field => String(field || '').toLowerCase().includes(searchTerm));
        });

        const totalEntries = disposisiState.filteredData.length;
        const totalPages = Math.ceil(totalEntries / disposisiState.entriesPerPage);
        if (disposisiState.currentPage > totalPages && totalPages > 0) disposisiState.currentPage = totalPages;
        else if (totalPages === 0) disposisiState.currentPage = 1;
        const startIndex = (disposisiState.currentPage - 1) * disposisiState.entriesPerPage;
        const endIndex = startIndex + disposisiState.entriesPerPage;
        const paginatedData = disposisiState.filteredData.slice(startIndex, endIndex);

        tableBody.innerHTML = '';
        if (paginatedData.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>`;
        } else {
            paginatedData.forEach(surat => {
                const noSuratPengirim = `<div class="text-primary-dispo1">${surat.no_surat || ''}</div><div class="text-secondary-dispo1">${surat.pengirim || 'Pengirim tidak diketahui'}</div>`;
                const fileButton = surat.file_token ? `<button class="btn-lihat-file-dispo1" data-token="${surat.file_token}" data-filename="${surat.file_surat}"><i class='bx bxs-file-pdf'></i> Lihat File</button>` : 'Tidak Ada File';
                const statusButton = `<button class="btn-lihat-status-dispo1" data-status='${JSON.stringify(surat.status_disposisi_all)}'>Lihat Status</button>`;
                const btnJawabText = surat.is_balas === '0' ? 'Jawab' : 'Jawab Lagi';
                const btnJawabClass = surat.is_balas === '0' ? 'btn-jawab-dispo1' : 'btn-jawab-lagi-dispo1';
                const actionButtons = `<div class="action-buttons-group-dispo1"><button class="${btnJawabClass}" data-id="${surat.id_disposisi_unit}">${btnJawabText}</button><button class="btn-baca-dispo1" data-id="${surat.id_surat}">Baca Disposisi</button></div>`;
                const row = `<tr><td data-label="No. Agenda">${surat.no_agenda}</td><td data-label="No. Surat & Pengirim">${noSuratPengirim}</td><td data-label="Sifat Surat">${surat.s_surat}</td><td data-label="Perihal">${surat.perihal}</td><td data-label="File Surat">${fileButton}</td><td data-label="Detail Status">${statusButton}</td><td data-label="Aksi">${actionButtons}</td></tr>`;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        }
        
        const info = document.getElementById('pagination-info-dispo1');
        const startEntry = totalEntries > 0 ? startIndex + 1 : 0;
        const endEntry = Math.min(endIndex, totalEntries);
        info.textContent = `Menampilkan ${startEntry} sampai ${endEntry} dari ${totalEntries} data`;
        renderPaginationButtons(totalPages);
    }

    function renderPaginationButtons(totalPages) {
        const container = document.getElementById('pagination-buttons-dispo1');
        container.innerHTML = '';
        if (totalPages <= 1) return;
        const prevButton = document.createElement('button');
        prevButton.innerHTML = '&laquo;';
        prevButton.disabled = disposisiState.currentPage === 1;
        prevButton.onclick = () => { if (disposisiState.currentPage > 1) { disposisiState.currentPage--; renderDisposisiTable(); } };
        container.appendChild(prevButton);
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            if (i === disposisiState.currentPage) pageButton.classList.add('active');
            pageButton.onclick = () => { disposisiState.currentPage = i; renderDisposisiTable(); };
            container.appendChild(pageButton);
        }
        const nextButton = document.createElement('button');
        nextButton.innerHTML = '&raquo;';
        nextButton.disabled = disposisiState.currentPage === totalPages;
        nextButton.onclick = () => { if (disposisiState.currentPage < totalPages) { disposisiState.currentPage++; renderDisposisiTable(); } };
        container.appendChild(nextButton);
    }
    
    document.getElementById('disposisi-table-body')?.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;
        if (target.classList.contains('btn-lihat-status-dispo1')) {
            try {
                const statusData = JSON.parse(target.dataset.status);
                const modalContent = (statusData || 'Belum Ada Status').split(' | ').map(s => {
                    const [unit, status] = s.split(' = ');
                    const statusClass = status === 'Sudah' ? 'status-sudah-text-dispo1' : 'status-belum-text-dispo1';
                    return `<div class="status-item-dispo1"><span class="unit-name-dispo1">${unit}:</span> <span class="${statusClass}">${status}</span></div>`;
                }).join('');
                openModal(statusModal, `<div class="status-list-dispo1">${modalContent}</div>`);
            } catch(error) {
                openModal(statusModal, `<div class="status-list-dispo1">Gagal menampilkan detail status.</div>`);
            }
        }
        if (target.classList.contains('btn-lihat-file-dispo1')) {
            const token = target.dataset.token;
            const fileName = target.dataset.filename;
            const filePath = `backend/view_file.php?token=${token}`;
            openModal(pdfModal, filePath, { title: `Dokumen: ${fileName}`, token: token });
        }
    });

    async function fetchDisposisiData() {
        const spinner = document.getElementById('loading-spinner');
        spinner.style.display = 'block';
        try {
            const response = await fetch('backend/CRUD/api_get_disposisi.php');
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            disposisiState.allData = await response.json();
            renderDisposisiTable();
        } catch (error) {
            document.getElementById('disposisi-table-body').innerHTML = `<tr><td colspan="7" style="text-align:center; color: var(--error-text);">${error.message}</td></tr>`;
        } finally {
            spinner.style.display = 'none';
        }
    }
    
    document.getElementById('entries-select-dispo1')?.addEventListener('change', (e) => {
        disposisiState.entriesPerPage = parseInt(e.target.value, 10);
        disposisiState.currentPage = 1;
        renderDisposisiTable();
    });

    document.getElementById('search-input-dispo1')?.addEventListener('input', (e) => {
        disposisiState.searchTerm = e.target.value;
        disposisiState.currentPage = 1;
        renderDisposisiTable();
    });
    
    document.getElementById('filter-select-dispo2')?.addEventListener('change', (e) => {
        disposisiState.filterStatus = e.target.value;
        disposisiState.currentPage = 1;
        renderDisposisiTable();
    });

    document.getElementById('reset-filter-dispo3')?.addEventListener('click', () => {
        disposisiState.searchTerm = '';
        disposisiState.filterStatus = 'all';
        disposisiState.currentPage = 1;

        document.getElementById('search-input-dispo1').value = '';
        document.getElementById('filter-select-dispo2').value = 'all';

        fetchDisposisiData();
    });

    let dataFetched = false;
    allNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target;
            const targetSection = document.getElementById(targetId);
            if(targetSection) targetSection.classList.add('active');
            contentSections.forEach(section => { if(section.id !== targetId) section.classList.remove('active'); });
            allNavLinks.forEach(syncLink => {
                const parent = syncLink.closest('.nav-item') || syncLink;
                const isActive = (syncLink.dataset.target === targetId);
                if (parent) parent.classList.toggle('active', isActive);
                else syncLink.classList.toggle('active', isActive);
            });
            if (targetId === 'disposisi-section' && !dataFetched) {
                fetchDisposisiData();
                dataFetched = true;
            }
        });
    });

    // ===== 4. LOGIKA HAMBURGER MENU =====
    const hamburgerButton = document.getElementById('hamburger-button');
    const dashboardContainer = document.querySelector('.dashboard-container');
    if (hamburgerButton && dashboardContainer) {
        const currentSidebarState = localStorage.getItem('sidebarState');
        if (currentSidebarState === 'collapsed') {
            dashboardContainer.classList.add('sidebar-collapsed');
        }
        hamburgerButton.addEventListener('click', function() {
            dashboardContainer.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', dashboardContainer.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });
    }

    // ===== 5. LOGIKA LOGOUT DENGAN PEMBERSIHAN TOKEN =====
    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hapus token JWT dari localStorage jika ada
            localStorage.removeItem('jwt_token'); 
            
            // Hapus token JWT dari sessionStorage jika ada
            sessionStorage.removeItem('jwt_token');

            // Arahkan ke skrip logout PHP untuk menghancurkan sesi server
            window.location.href = 'logout.php';
        });
    }
});