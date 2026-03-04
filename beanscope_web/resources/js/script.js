// BeanScope - Main JavaScript

// ========================================
// Home Page Functions
// ========================================

// Smooth scroll for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// Navbar active state management
function initNavbarActiveState() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const activeNav = localStorage.getItem('activeNav');

    if (activeNav) {
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-nav') === activeNav) {
                link.classList.add('active');
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            localStorage.setItem('activeNav', this.getAttribute('data-nav'));
        });
    });

    if (window.location.pathname === '/' || window.location.pathname.includes('home')) {
        window.addEventListener('scroll', function () {
            const scrollPos = window.scrollY + 100;
            document.querySelectorAll('section[id]').forEach(section => {
                const sectionId = section.getAttribute('id');
                if (scrollPos >= section.offsetTop && scrollPos < section.offsetTop + section.offsetHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('data-nav') === sectionId) {
                            link.classList.add('active');
                            localStorage.setItem('activeNav', sectionId);
                        }
                    });
                }
            });
        });
    }
}

// ========================================
// Predict Page Functions
// ========================================

// Image preview function (called via onchange attribute on the file input)
function previewImage(input) {
    const preview          = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    const fileNameEl       = document.getElementById('fileName');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            fileNameEl.textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ========================================
// Batch Test Page Functions
// ========================================

// Called via onchange on the ZIP file input
function onFileSelected(input) {
    const f = input.files[0];
    if (!f) return;
    const fileNameEl = document.getElementById('fileName');
    const fileSizeEl = document.getElementById('fileSize');
    const fileInfo   = document.getElementById('fileInfo');
    const submitBtn  = document.getElementById('submitBtn');
    if (fileNameEl) fileNameEl.textContent = f.name;
    if (fileSizeEl) fileSizeEl.textContent = '(' + (f.size / 1024 / 1024).toFixed(2) + ' MB)';
    if (fileInfo)   fileInfo.classList.remove('d-none');
    if (submitBtn)  submitBtn.disabled = false;
}

// Drag & Drop and progress bar for the batch form
function initBatchTest() {
    const dropZone  = document.getElementById('dropZone');
    const zipInput  = document.getElementById('zipInput');
    const batchForm = document.getElementById('batchForm');
    const submitBtn = document.getElementById('submitBtn');

    if (!dropZone || !zipInput) return;

    // Drag events
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, ev => {
            ev.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, ev => {
            ev.preventDefault();
            dropZone.classList.remove('drag-over');
        });
    });
    dropZone.addEventListener('drop', ev => {
        const files = ev.dataTransfer.files;
        if (files.length) {
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            zipInput.files = dt.files;
            onFileSelected(zipInput);
        }
    });

    // Fake upload progress on form submit
    if (batchForm) {
        batchForm.addEventListener('submit', function () {
            const progressWrap = document.getElementById('progressWrap');
            const bar          = document.getElementById('progressBar');
            const pctLabel     = document.getElementById('progressPct');
            if (progressWrap) progressWrap.classList.remove('d-none');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses ...';
            }
            let pct = 0;
            setInterval(() => {
                pct = Math.min(pct + Math.random() * 4, 92);
                if (bar)      bar.style.width      = pct.toFixed(0) + '%';
                if (pctLabel) pctLabel.textContent = pct.toFixed(0) + '%';
            }, 400);
        });
    }
}

// Table filter for batch results
function initBatchFilter() {
    const searchInput  = document.getElementById('searchInput');
    const filterClass  = document.getElementById('filterClass');
    const filterResult = document.getElementById('filterResult');

    // Only run on batch-test page when result table exists
    if (!searchInput && !filterClass && !filterResult) return;

    function applyFilter() {
        const q   = searchInput  ? searchInput.value.toLowerCase() : '';
        const cls = filterClass  ? filterClass.value               : '';
        const res = filterResult ? filterResult.value              : '';
        const rows = document.querySelectorAll('.result-row');
        let visible = 0;
        rows.forEach(row => {
            const fn   = row.cells[1].textContent.toLowerCase();
            const show = (!q   || fn.includes(q))
                      && (!cls || row.dataset.true    === cls)
                      && (!res || row.dataset.correct === res);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const cnt = document.getElementById('showingCount');
        if (cnt) cnt.textContent = 'Menampilkan ' + visible + ' dari ' + rows.length + ' gambar';
    }

    if (searchInput)  searchInput.addEventListener('input',  applyFilter);
    if (filterClass)  filterClass.addEventListener('change', applyFilter);
    if (filterResult) filterResult.addEventListener('change', applyFilter);
    applyFilter(); // initial run
}

// ========================================
// Initialize on DOM Ready
// ========================================

document.addEventListener('DOMContentLoaded', function () {
    initSmoothScroll();
    initNavbarActiveState();
    initBatchTest();
    initBatchFilter();
});

// Expose functions called from HTML inline attributes
window.previewImage   = previewImage;
window.onFileSelected = onFileSelected;
