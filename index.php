<?php
require_once 'backend/config.php';
require_once 'backend/session_check.php';

$namaUser = $_SESSION['user']['nm_pegawai'];
$levelUser = $_SESSION['user']['level'];
$pelihatUser = $_SESSION['user']['pelihat'];
$unitUser = $_SESSION['user']['kd_unit'];
$jabatanUser = $_SESSION['user']['id_jabatan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Surat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <i class='bx bxs-envelope logo-icon'></i>
                    <span class="logo-text">E-Surat</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active"><a href="#" class="nav-link" data-target="home-section"><i class='bx bxs-home-smile'></i><span>Home</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link" data-target="disposisi-section"><i class='bx bx-file-find'></i><span>Disposisi</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link" data-target="schedule-section"><i class='bx bx-calendar'></i><span>Schedule</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link" data-target="report-section"><i class='bx bx-line-chart'></i><span>Report</span></a></li>
                    <li class="nav-item"><a href="#" class="nav-link" data-target="notifications-section"><i class='bx bxs-bell'></i><span>Notifications</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <i class='bx bx-menu' id="hamburger-button"></i>
                    <div class="header-greeting">
                        <p>Hello!</p>
                        <h1><?php echo htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8'); ?></h1>
                    </div>
                </div>
                <div class="header-user">
                    <div class="theme-switcher" id="theme-switcher-button" title="Ganti Tema">
                        <i class='bx bxs-sun sun-icon'></i>
                        <i class='bx bxs-moon moon-icon'></i>
                    </div>
                    <div class="user-profile">
                        <img src="https://i.pravatar.cc/150?u=<?php echo urlencode($namaUser); ?>" alt="User Avatar" class="user-avatar" id="avatarButton">
                        <div class="dropdown-menu" id="dropdownMenu">
                            <div class="dropdown-header">
                                <h5 class="user-name"><?php echo htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="user-level"><?php echo htmlspecialchars(ucfirst($levelUser), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <ul>
                                <li><a href="#"><i class='bx bx-user'></i><span>Profile</span></a></li>
                                <li><a href="#"><i class='bx bx-key'></i><span>Ganti Password</span></a></li>
                                <li class="divider"></li>
                                <li><a href="logout.php" id="logout-button"><i class='bx bx-log-out'></i><span>Logout</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <section id="home-section" class="content-section active">
                <div class="widget-grid">
                    <div class="widget progress-widget full-width">
                        <div class="widget-header"><h2>Progress Pemeriksaan Pasien</h2></div>
                        <div class="progress-bar-container"><div class="progress-bar" style="width: 70%;"></div></div>
                        <span class="progress-status">7/10 Pasien Selesai</span>
                    </div>
                    <div class="promo-banner full-width">
                        <div class="promo-content"><div class="promo-icon"><i class='bx bxs-shield-plus'></i></div><div class="promo-text"><h3>Get the Best Medical Services</h3><p>Layanan medis kualitas terbaik tanpa biaya tambahan.</p></div></div>
                        <a href="#" class="promo-action">Lihat Promo</a>
                    </div>
                    <div class="widget services-widget full-width">
                        <div class="widget-header"><h2>Services</h2><a href="#" class="view-all">View All</a></div>
                        <div class="services-grid">
                            <a href="#" class="service-card nav-link" data-target="disposisi-section">
                                <div class="service-icon" style="--icon-bg: #E0F2F1; --icon-color: #009688;"><i class='bx bxs-file-import'></i></div>
                                <div class="service-info"><h4>Disposisi Surat</h4><p>Lihat surat masuk</p></div>
                            </a>
                            <a href="#" class="service-card"><div class="service-icon" style="--icon-bg: #FFF3E0; --icon-color: #FF9800;"><i class='bx bxs-capsule'></i></div><div class="service-info"><h4>Tebus Obat</h4><p>Resep & pengantaran</p></div></a>
                            <a href="#" class="service-card"><div class="service-icon" style="--icon-bg: #E3F2FD; --icon-color: #2196F3;"><i class='bx bxs-file-blank'></i></div><div class="service-info"><h4>Rekam Medis</h4><p>Lihat riwayat medis</p></div></a>
                            <a href="#" class="service-card"><div class="service-icon" style="--icon-bg: #F3E5F5; --icon-color: #9C27B0;"><i class='bx bxs-conversation'></i></div><div class="service-info"><h4>Konsultasi</h4><p>Tanya jawab online</p></div></a>
                        </div>
                    </div>
                    <div class="widget appointments-widget full-width">
                        <div class="widget-header"><h2>Upcoming Appointments</h2><a href="#" class="view-all">View All</a></div>
                        <ul class="appointments-list">
                            <li><a href="#" class="appointment-item"><div class="appointment-time"><i class='bx bx-time-five'></i>08:00</div><div class="appointment-details"><h4 class="doctor">Dr. Zuyan Shah</h4><span class="reason">Medical Checkup</span></div><div class="appointment-status pending">Pending</div></a></li>
                            <li><a href="#" class="appointment-item"><div class="appointment-time"><i class='bx bx-time-five'></i>09:30</div><div class="appointment-details"><h4 class="doctor">Dr. Mim Akhter</h4><span class="reason">Konsultasi Psikologi</span></div><div class="appointment-status confirmed">Confirmed</div></a></li>
                            <li><a href="#" class="appointment-item"><div class="appointment-time"><i class='bx bx-time-five'></i>11:00</div><div class="appointment-details"><h4 class="doctor">Dr. John Doe</h4><span class="reason">Perawatan Gigi</span></div><div class="appointment-status confirmed">Confirmed</div></a></li>
                        </ul>
                    </div>
                </div>
            </section>
            
            <section id="disposisi-section" class="content-section">
                <div class="widget">
                    <div class="widget-header">
                        <h2>Daftar Disposisi Surat Masuk</h2>
                    </div>

                    <div class="table-controls-dispo1">
                        <div class="table-controls-left-dispo2">
                            <div class="entries-control-dispo1">
                                <label for="entries-select-dispo1">Tampilkan</label>
                                <select id="entries-select-dispo1">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label for="entries-select-dispo1">data</label>
                            </div>
                            <div class="filter-control-dispo2">
                                <label for="filter-select-dispo2">Status Balasan:</label>
                                <select id="filter-select-dispo2">
                                    <option value="all">Semua</option>
                                    <option value="0">Belum Dijawab</option>
                                    <option value="1">Sudah Dijawab</option>
                                </select>
                            </div>
                            <button id="reset-filter-dispo3" class="btn-reset-dispo3">Reset Filter</button>
                        </div>
                        <div class="search-control-dispo1">
                            <label for="search-input-dispo1">Cari:</label>
                            <input type="text" id="search-input-dispo1" placeholder="Ketik untuk mencari...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="disposisi-table">
                            <thead>
                                <tr>
                                    <th>No. Agenda</th>
                                    <th>No. Surat & Pengirim</th>
                                    <th>Sifat Surat</th>
                                    <th>Perihal</th>
                                    <th>File Surat</th>
                                    <th>Detail Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="disposisi-table-body">
                                </tbody>
                        </table>
                    </div>
                    <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
                        <i class='bx bx-loader-alt bx-spin' style='font-size: 40px; color: var(--primary-color);'></i>
                    </div>

                    <div class="pagination-controls-dispo1">
                        <span id="pagination-info-dispo1"></span>
                        <div class="pagination-buttons-dispo1" id="pagination-buttons-dispo1">
                            </div>
                    </div>

                </div>
            </section>
            
            <section id="schedule-section" class="content-section"><div class="widget"><h2>Jadwal (Schedule)</h2><p>Konten untuk jadwal.</p></div></section>
            <section id="report-section" class="content-section"><div class="widget"><h2>Laporan (Report)</h2><p>Konten untuk laporan.</p></div></section>
            <section id="notifications-section" class="content-section"><div class="widget"><h2>Notifikasi (Notifications)</h2><p>Konten untuk notifikasi.</p></div></section>
        </main>
    </div>

    <div id="status-modal" class="modal-container-status">
        <div class="modal-content-status">
            <div class="modal-header-status">
                <h3>Detail Status Disposisi</h3>
                <span class="modal-close-status">&times;</span>
            </div>
            <div class="modal-body-status" id="modal-body-content-status"></div>
        </div>
    </div>

    <div id="pdf-modal" class="modal-container-pdf">
        <div class="modal-content-pdf">
            <div class="modal-header-pdf">
                <h3 id="pdf-modal-title">Tampilan Dokumen</h3>
                <div class="modal-header-actions-pdf">
                    <button id="pdf-download-btn" class="modal-action-btn-pdf" title="Download Watermarked PDF"><i class='bx bx-download'></i></button>
                    <span class="modal-close-pdf">&times;</span>
                </div>
            </div>
            <div class="modal-body-pdf">
                <div class="pdf-viewer-container">
                    <canvas id="pdf-canvas"></canvas>
                </div>
                <div id="pdf-loader" class="pdf-loader">
                    <i class='bx bx-loader-alt bx-spin'></i> Memuat Dokumen...
                </div>
            </div>
            <div class="pdf-navigation">
                <button id="pdf-prev-btn"><i class='bx bx-chevron-left'></i> Sebelumnya</button>
                <span id="pdf-page-indicator"></span>
                <button id="pdf-next-btn">Berikutnya <i class='bx bx-chevron-right'></i></button>
            </div>
        </div>
    </div>
    
    <nav class="mobile-nav">
        <a href="#" class="nav-link active" data-target="home-section"><i class='bx bxs-home'></i><span>Home</span></a>
        <a href="#" class="nav-link" data-target="disposisi-section"><i class='bx bx-file-find'></i><span>Disposisi</span></a>
        <a href="#" class="nav-link" data-target="schedule-section"><i class='bx bx-calendar'></i><span>Schedule</span></a>
        <a href="#" class="nav-link" data-target="report-section"><i class='bx bxs-file-doc'></i><span>Report</span></a>
        <a href="#" class="nav-link" data-target="notifications-section"><i class='bx bxs-bell'></i><span>Notifications</span></a>
    </nav>

    <script src="assets/js/pdfjs/pdf.mjs" type="module"></script>
    <script src="assets/js/script.js" type="module"></script>

</body>
</html>