@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Global Typography & Corporate Theme */
    body, h1, h2, h3, h4, h5, h6, p, span, table, button, input, select, textarea, .list-group-item {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }

    :root {
        --primary-custom: #940000;
        --secondary-custom: #FFF5F5;
        --accent-soft: #FFF5F5;
        --header-gradient: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);
    }

    .bg-primary-custom { background: #940000 !important; }
    .text-primary-custom { color: #940000 !important; }
    .btn-primary-custom { background: #940000; color: white; border: none; border-radius: 0; font-weight: 700; transition: all 0.3s ease; }
    .btn-primary-custom:hover { background: #7a0000; color: white; transform: translateY(-2px); }
    
    .card { border-radius: 0 !important; border: none !important; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    
    .setting-item {
        transition: all 0.3s ease;
        border: 1px solid transparent;
        padding: 15px;
    }
    .setting-item:hover {
        background: #FFF5F5;
        transform: translateY(-5px);
    }
    
    /* Stats Card Modernization */
    .stat-card-new {
        background: #fff;
        border-top: 4px solid #940000;
        padding: 20px;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
    }
    .stat-card-new:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(148, 0, 0, 0.1);
        background: #FFF5F5;
    }
    .stat-card-new i {
        color: #940000;
        margin-bottom: 15px;
        opacity: 0.8;
    }
    .stat-card-new h4 {
        color: #940000;
        font-weight: 800;
        margin-bottom: 5px;
    }
    .stat-card-new p {
        color: #444;
        font-weight: 700;
        letter-spacing: 1px;
        font-size: 11px;
    }

    /* Table & UI Standardization */
    .table-responsive { border-radius: 0; }
    .table thead th { background: #FFF5F5; color: #940000; border-bottom: 2px solid #940000; font-weight: 700; text-transform: uppercase; font-size: 12px; }
    
    /* Search Filter Section */
    .search-filter-section {
        background: white;
        padding: 20px;
        margin-bottom: 20px;
        border-bottom: 2px solid #FFF5F5;
    }

    .form-label {
        font-weight: 700;
        color: #495057;
        margin-bottom: 8px;
        font-size: 13px;
        text-transform: uppercase;
    }

    .library-menu .list-group-item {
        cursor: pointer;
        border-radius: 0 !important;
        border: none;
        border-left: 4px solid transparent;
        padding: 12px 20px;
        transition: all 0.2s;
    }
    .library-menu .list-group-item.active {
        border-left-color: #940000;
        background: #fff5f5;
        color: #940000;
        font-weight: 700;
    }

    .library-management-body {
        max-height: 80vh;
        overflow-y: auto;
    }

    /* Book & Student Widgets */
    .book-widget, .student-widget {
        border-radius: 0 !important;
        border: 1px solid #eee;
        transition: all 0.3s;
    }
    .book-widget:hover { border-color: #940000; box-shadow: 0 5px 15px rgba(148,0,0,0.05); }

    .book-widget.available { border-left: 4px solid #28a745; background: #fafffa; }
    .book-widget.borrowed { border-left: 4px solid #ffc107; background: #fffdf5; }
    .book-widget.overdue { border-left: 4px solid #dc3545; background: #fffafa; }
    .book-widget.lost { border-left: 4px solid #6f42c1; background: #f9f5ff; }
    .book-widget.damaged { border-left: 4px solid #fd7e14; background: #fff9f5; }

    .student-widget { border: 1px solid #eee; background: #fff; }
    .student-header { background: #940000; padding: 15px; }
    .student-body h3 { color: #940000; font-weight: 700; margin-top: 10px; }

    /* DataTable Customization */
    .dataTables_wrapper .dataTables_filter input, 
    .dataTables_wrapper .dataTables_length select {
        border-radius: 0 !important;
        border: 1px solid #940000 !important;
        font-family: 'Century Gothic', sans-serif;
    }
    .page-item.active .page-link { background-color: #940000; border-color: #940000; }
    .page-link { color: #940000; }

    .student-body .form {
        font-weight: bold;
        color: #444;
        margin-bottom: 10px;
    }

    .student-body .info p {
        margin: 6px 0;
        font-size: 14px;
    }

    .return-date {
        color: #940000;
        font-weight: bold;
    }

    /* New Sidebar Layout Styles */
    .library-main-container {
        display: flex;
        gap: 25px;
        align-items: flex-start;
    }
    
    .library-content-area {
        flex-grow: 1;
        min-width: 0;
    }

    /* Book Grid System */
    .book-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }
    
    .book-card-widget {
        background: #fff;
        padding: 20px 15px;
        text-align: center;
        border: 1px solid #eee;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .book-card-widget:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(148,0,0,0.08);
        border-color: #940000;
    }
    
    .book-card-widget .book-icon {
        font-size: 50px;
        margin-bottom: 15px;
        display: block;
    }
    
    .book-card-widget .book-title {
        font-size: 12px;
        font-weight: 800;
        color: #333;
        display: block;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .book-card-widget .book-author {
        font-size: 10px;
        color: #777;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Status Color Coding */
    .book-status-badge {
        position: absolute;
        top: 0;
        right: 0;
        width: 40px;
        height: 40px;
        background: #eee;
        clip-path: polygon(100% 0, 0 0, 100% 100%);
    }
    
    .book-card-widget.available { border-bottom: 4px solid #28a745; }
    .book-card-widget.available .book-icon { color: #28a745; }
    
    .book-card-widget.borrowed { border-bottom: 4px solid #ffc107; }
    .book-card-widget.borrowed .book-icon { color: #ffc107; }
    
    .book-card-widget.overdue { border-bottom: 4px solid #dc3545; }
    .book-card-widget.overdue .book-icon { color: #dc3545; }
    
    .book-card-widget.lost { border-bottom: 4px solid #6f42c1; }
    .book-card-widget.lost .book-icon { color: #6f42c1; }
    
    .book-card-widget.lost_resolved { border-bottom: 4px solid #a29bfe; background: #faf9ff; }
    .book-card-widget.lost_resolved .book-icon { color: #a29bfe; }
    .book-card-widget.lost_resolved .book-status-badge { background: #a29bfe; }
    
    .book-card-widget.damaged { border-bottom: 4px solid #fd7e14; }
    .book-card-widget.damaged .book-icon { color: #fd7e14; }

    .book-card-widget.damaged_resolved { border-bottom: 4px solid #ffbe76; background: #fffcf9; }
    .book-card-widget.damaged_resolved .book-icon { color: #ffbe76; }
    .book-card-widget.damaged_resolved .book-status-badge { background: #ffbe76; }

    /* Graph Container */
    .report-card {
        background: #fff;
        padding: 25px;
        margin-bottom: 25px;
        border-top: 4px solid #940000;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    @media (max-width: 991px) {
        .library-main-container {
            flex-direction: column;
        }
        .library-sidebar {
            width: 100%;
            position: relative;
            top: 0;
        }
    }
</style>

<div class="content mt-3">
    <!-- Header Section (Always Visible Actions) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">LIBRARY MANAGEMENT SYSTEM</h4>
                            <p class="text-muted mb-0 small" style="font-weight: 600;">Monitor book circulation, inventory health, and student borrowing activities.</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <button class="btn shadow-sm text-white" id="toggleActionsBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 25px; transition: all 0.3s ease;">
                                <i class="fa fa-cogs"></i> ACTION <i class="fa fa-chevron-down ml-2" id="actionChevron"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Expanded Actions Section -->
                    <div id="expandedActions" style="display: none; margin-top: 25px; padding-top: 25px; border-top: 1px solid rgba(148, 0, 0, 0.1); position: relative;">
                        <button type="button" class="close" onclick="jQuery('#expandedActions').slideUp(300); jQuery('#actionChevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');" style="position: absolute; right: 10px; top: 10px; color: #000; opacity: 0.5;">&times;</button>
                        <div class="row text-center">
                            <div class="col-md-3 border-right">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="showAddBookModal()">
                                    <i class="fa fa-plus-circle fa-2x mb-3 text-success"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">ADD BOOK</h6>
                                </a>
                            </div>
                            <div class="col-md-3 border-right">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="showBorrowBookModal()">
                                    <i class="fa fa-hand-paper-o fa-2x mb-3 text-primary"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">BORROW / LEND</h6>
                                </a>
                            </div>
                            <div class="col-md-3 border-right">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="showDamagedBookModal()">
                                    <i class="fa fa-wrench fa-2x mb-3 text-warning"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">RECORD DAMAGE</h6>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="javascript:void(0)" class="text-decoration-none d-block py-4 setting-item" onclick="showLostBookModal()">
                                    <i class="fa fa-times-circle fa-2x mb-3 text-danger"></i>
                                    <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f; letter-spacing: 0.5px;">RECORD LOSS</h6>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats Inline -->
                    <div class="row mt-4 no-gutters text-center border-top pt-3">
                        <div class="col">
                            <h6 class="text-muted small font-weight-bold mb-1">TOTAL</h6>
                            <h5 class="text-primary-custom font-weight-bold mb-0">{{ $totalBooks ?? 0 }}</h5>
                        </div>
                        <div class="col border-left">
                            <h6 class="text-muted small font-weight-bold mb-1">AVAILABLE</h6>
                            <h5 class="text-success font-weight-bold mb-0">{{ $availableBooks ?? 0 }}</h5>
                        </div>
                        <div class="col border-left">
                            <h6 class="text-muted small font-weight-bold mb-1">BORROWED</h6>
                            <h5 class="text-warning font-weight-bold mb-0">{{ $issuedBooks ?? 0 }}</h5>
                        </div>
                        <div class="col border-left">
                            <h6 class="text-muted small font-weight-bold mb-1">OVERDUE</h6>
                            <h5 class="text-danger font-weight-bold mb-0">{{ $overdueBooks ?? 0 }}</h5>
                        </div>
                        <div class="col border-left">
                            <h6 class="text-muted small font-weight-bold mb-1">LOST</h6>
                            <h5 class="text-indigo font-weight-bold mb-0" style="color: #6f42c1;">{{ $lostBooks ?? 0 }}</h5>
                        </div>
                        <div class="col border-left">
                            <h6 class="text-muted small font-weight-bold mb-1">DAMAGED</h6>
                            <h5 class="text-orange font-weight-bold mb-0" style="color: #fd7e14;">{{ $damagedBooks ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="library-content-area" style="width: 100%;">
        <!-- Filter Bar -->
        <div class="report-card mb-4 p-3 filters-bar shadow-sm">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 10px;">Filter by Class</label>
                    <select class="form-control form-control-sm" id="filterClass" style="border-radius: 0; font-weight: 700;">
                        <option value="">ALL CLASSES</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 10px;">Filter by Subject</label>
                    <select class="form-control form-control-sm" id="filterSubject" style="border-radius: 0; font-weight: 700;">
                        <option value="">ALL SUBJECTS</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->subjectID }}">{{ $subject->subject_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 10px;">Status</label>
                    <select class="form-control form-control-sm" id="filterStatus" style="border-radius: 0; font-weight: 700;">
                        <option value="">ANY STATUS</option>
                        <option value="available">AVAILABLE</option>
                        <option value="borrowed">BORROWED</option>
                        <option value="overdue">OVERDUE</option>
                        <option value="lost">LOST</option>
                        <option value="damaged">DAMAGED</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 10px;">Search Keyword</label>
                    <input type="text" class="form-control form-control-sm" id="searchBook" placeholder="Title, Unique ID, Author..." style="border-radius: 0;">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block" style="font-size: 10px; opacity: 0;">Actions</label>
                    <div class="btn-group w-100">
                        <button class="btn btn-primary-custom btn-sm" onclick="loadBooks()" title="Refresh Inventory">
                            <i class="fa fa-refresh"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="exportLibraryPdf()" title="Download PDF Report">
                            <i class="fa fa-file-pdf-o"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Catalog -->
        <div class="report-card mb-0" style="border-top: none; background: transparent; padding: 0;">
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white shadow-sm">
                <h5 class="mb-0 font-weight-bold" style="color: #940000;"><i class="fa fa-th"></i> BOOKS CATALOG</h5>
                <div class="text-muted small font-weight-bold" id="booksCountInfo">Loading inventory...</div>
            </div>
            
            <!-- Pattern Key Legend -->
            <div class="mb-3 px-3 d-flex flex-wrap align-items-center bg-white p-2 border-bottom shadow-sm">
                <span class="mr-3 small"><strong>PATTERN KEY:</strong></span>
                <span class="mr-3 small"><i class="fa fa-circle text-success"></i> AVAILABLE</span>
                <span class="mr-3 small"><i class="fa fa-circle text-warning"></i> OCCUPIED</span>
                <span class="mr-3 small"><i class="fa fa-circle text-danger"></i> OVERDUE</span>
                <span class="mr-3 small"><i class="fa fa-circle" style="color: #6f42c1;"></i> LOST</span>
                <span class="mr-3 small"><i class="fa fa-check-circle" style="color: #a29bfe;"></i> LOST & RESOLVED</span>
                <span class="mr-3 small"><i class="fa fa-circle" style="color: #fd7e14;"></i> DAMAGED</span>
                <span class="mr-3 small"><i class="fa fa-check-circle" style="color: #ffbe76;"></i> DAMAGED & RESOLVED</span>
            </div>

            <div class="position-relative" style="min-height: 400px;">
                <div id="loaderOverlay" style="display:none; position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(255,255,255,0.7); z-index: 10; align-items:center; justify-content:center;">
                    <div class="text-center">
                        <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;"></div>
                        <h6 class="mt-2 font-weight-bold" style="color: #940000;">SYNCING...</h6>
                    </div>
                </div>
                <div id="booksGrid" class="book-grid-container px-2">
                    <!-- Widgets will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Books Modal -->
    <!-- Download Progress Modal -->
    <div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fa fa-file-pdf-o fa-3x text-danger animate-pulse"></i>
                    </div>
                    <h5 class="mb-4" style="color: #940000; font-weight: bold;">Generating Library Inventory Report</h5>
                    <div class="progress mb-2" style="height: 12px; border-radius: 6px;">
                        <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                            role="progressbar" style="width: 0%;"></div>
                    </div>
                    <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                    <p class="mt-3 text-muted small">Please wait, this might take a moment if the library has many books.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>

<div class="modal fade" id="viewBooksModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBooksModalTitle">
                    <i class="fa fa-th"></i> View Books
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="me-2"><strong>Pattern Key:</strong></span>
                    <span class="pattern-key">
                        <span class="pattern-swatch available"></span> Available
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch borrowed"></span> Occupied
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch overdue"></span> Overdue
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch lost"></span> Lost
                    </span>
                    <span class="pattern-key">
                        <span class="pattern-swatch damaged"></span> Damaged
                    </span>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" id="viewBooksSearch" placeholder="Search by Unique ID, title, author...">
                </div>
                <div class="mb-3">
                    <select class="form-control" id="viewBooksFilter" style="width: 240px; display: inline-block;">
                        <option value="">All</option>
                        <option value="free">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="overdue">Overdue</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="row" id="viewBooksWidgetContainer">
                    <div class="col-12 text-center">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Book Modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookModalTitle">
                    <i class="fa fa-book"></i> Add New Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="bookForm">
                    <input type="hidden" id="bookID">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Book Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="bookType" onchange="toggleBookTypeFields()" required>
                                <option value="Class">Class Book (Curriculum)</option>
                                <option value="Other">Other (Story, Novel, Play, etc.)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="classSelectionDiv">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-control" id="bookClassID" onchange="getSubjects(this.value)" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->classID }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="subjectSelectionDiv">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-control" id="bookSubjectID" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Book Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bookTitle" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" id="bookAuthor">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publisher</label>
                            <input type="text" class="form-control" id="bookPublisher">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Publication Year</label>
                            <input type="number" class="form-control" id="bookPublicationYear" min="1900" max="{{ date('Y') }}">
                        </div>
                        <div class="col-md-6 mb-3" id="bookStatusDiv" style="display: none;">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="bookStatus">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="bookDescription" rows="3"></textarea>
                        </div>
                    </div>
                </form>
                <div id="bookEntriesSection" class="mt-3">
                    <h6 class="mb-2">Unique ID List</h6>
                    <div id="isbnList">
                        <div class="row align-items-end isbn-row mb-2">
                            <div class="col-md-8">
                                <label class="form-label">Unique ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control isbn-input" placeholder="Enter Unique ID">
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-danger remove-isbn-btn d-none">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addIsbnRowBtn">
                        <i class="fa fa-plus"></i> Add ISBN
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBooksBtn" onclick="saveBooks()">
                    <i class="fa fa-save"></i> Save All
                </button>
                <button type="button" class="btn btn-primary-custom d-none" id="saveBookBtn" onclick="saveBook()">
                    <i class="fa fa-save"></i> <span id="saveBookBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Borrow Book Modal -->
<div class="modal fade" id="borrowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" style="color: #940000;">
                    <i class="fa fa-hand-paper-o"></i> LEND / BORROW BOOK
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="borrowForm">
                    <div class="mb-4">
                        <label class="form-label">Book Unique ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="borrowISBN" placeholder="Scan or type Unique ID..." required>
                            <div class="input-group-append d-none" id="borrowIsbnSpinner">
                                <span class="input-group-text bg-white"><i class="fa fa-spinner fa-spin text-primary"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="borrowBookInfo" style="display: none;">
                        <div class="p-3 border rounded shadow-sm" id="borrowBookDetailCard">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong id="borrowBookTitle" class="h6 mb-0 text-primary"></strong>
                                <span id="borrowBookStatusBadge" class="badge"></span>
                            </div>
                            <div class="small">
                                <div><strong>Class:</strong> <span id="borrowBookClass"></span></div>
                                <div><strong>Subject:</strong> <span id="borrowBookSubject"></span></div>
                                <div id="occupiedByDiv" class="mt-2 p-2 bg-warning-light rounded d-none" style="border-left: 4px solid #ffc107;">
                                    <i class="fa fa-user"></i> <strong>Occupied By:</strong> <span id="borrowOccupiedBy"></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="isBookAvailableForBorrow" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Borrower Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="borrowerType" onchange="toggleBorrowerSearch()" style="font-weight:700;">
                            <option value="" selected disabled>SELECT BORROWER TYPE</option>
                            <option value="student">STUDENT</option>
                            <option value="teacher">TEACHER</option>
                            <option value="staff">OTHER STAFF</option>
                        </select>
                    </div>
                    <div class="mb-3" id="borrowerSelectionContainer" style="display: none;">
                        <label class="form-label" id="borrowerSearchLabel">Select Borrowers <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchStudent" placeholder="Type to search and add...">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fa fa-users"></i></span>
                            </div>
                        </div>
                        <div id="selectedBorrowersList" class="mt-2 d-flex flex-wrap gap-2">
                            <!-- Chips of selected borrowers will appear here -->
                        </div>
                        <div id="studentResults" class="mt-1 shadow border rounded bg-white" style="max-height: 200px; overflow-y: auto; display: none; position: absolute; width: 93%; z-index: 1050;"></div>
                    </div>
                    <input type="hidden" id="borrowStudentID"> <!-- Legacy placeholder, we will use a different way to collect multiple IDs -->
                
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expectedReturnDate" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="borrowNotes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" id="saveBorrowBtn" onclick="saveBorrow()">
                    <i class="fa fa-save"></i> <span id="saveBorrowBtnText">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lost Book Modal -->
<div class="modal fade" id="lostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-ban"></i> Record Lost Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="lostForm">
                    <div class="mb-4">
                        <label class="form-label">Book Unique ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="lostIsbn" placeholder="Scan or type Unique ID..." required>
                            <div class="input-group-append d-none" id="lostIsbnSpinner">
                                <span class="input-group-text bg-white"><i class="fa fa-spinner fa-spin text-indigo"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="lostBookInfo" style="display:none;">
                        <div class="p-2 border rounded bg-light">
                            <div><strong>Title:</strong> <span id="lostBookTitle"></span></div>
                            <div><strong>Class:</strong> <span id="lostBookClass"></span></div>
                            <div><strong>Subject:</strong> <span id="lostBookSubject"></span></div>
                            <div><strong>Status:</strong> <span id="lostBookStatus"></span></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lost By <span class="text-danger">*</span></label>
                        <select class="form-control" id="lostBy" onchange="toggleIncidentBy('lost')" style="font-weight: 700;">
                            <option value="" selected disabled>SELECT LOSTER</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Other Staff</option>
                            <option value="other">General / Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="lostStudentSection" style="display: none;">
                        <label class="form-label" id="lostSearchLabel">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lostStudentSearch" placeholder="Search by name or reference...">
                        <input type="hidden" id="lostStudentID">
                        <input type="hidden" id="lostTeacherID">
                        <input type="hidden" id="lostStaffID">
                        <div id="lostStudentResults" class="mt-2 shadow-sm border rounded bg-white" style="max-height: 200px; overflow-y: auto; display: none; position: relative; z-index: 100;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" id="lostReason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveLostBook()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Damaged Book Modal -->
<div class="modal fade" id="damagedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-wrench"></i> Record Damaged Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="damagedForm">
                    <div class="mb-4">
                        <label class="form-label">Book Unique ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="damagedIsbn" placeholder="Scan or type Unique ID..." required>
                            <div class="input-group-append d-none" id="damagedIsbnSpinner">
                                <span class="input-group-text bg-white"><i class="fa fa-spinner fa-spin text-orange"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="damagedBookInfo" style="display:none;">
                        <div class="p-2 border rounded bg-light">
                            <div><strong>Title:</strong> <span id="damagedBookTitle"></span></div>
                            <div><strong>Class:</strong> <span id="damagedBookClass"></span></div>
                            <div><strong>Subject:</strong> <span id="damagedBookSubject"></span></div>
                            <div><strong>Status:</strong> <span id="damagedBookStatus"></span></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Damaged By <span class="text-danger">*</span></label>
                        <select class="form-control" id="damagedBy" onchange="toggleIncidentBy('damaged')" style="font-weight: 700;">
                            <option value="" selected disabled>SELECT DAMAGER</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Other Staff</option>
                            <option value="other">General / Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="damagedStudentSection" style="display: none;">
                        <label class="form-label" id="damagedSearchLabel">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="damagedStudentSearch" placeholder="Search by name or reference...">
                        <input type="hidden" id="damagedStudentID">
                        <input type="hidden" id="damagedTeacherID">
                        <input type="hidden" id="damagedStaffID">
                        <div id="damagedStudentResults" class="mt-2 shadow-sm border rounded bg-white" style="max-height: 200px; overflow-y: auto; display: none; position: relative; z-index: 100;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" id="damagedReason" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveDamagedBook()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Book Details Popup Modal -->
<div class="modal fade" id="bookDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 0; border-top: 5px solid #940000;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" style="color: #940000;">
                    <i class="fa fa-book"></i> BOOK INFORMATION
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="bookDetailsContent">
                <div class="p-5 text-center">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 0;">CLOSE</button>
                <div id="bookActionFooter"></div>
            </div>
        </div>
    </div>
</div>

<!-- Return Book Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-check"></i> Return Book</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="returnBorrowID">
                <div class="mb-2"><strong>Expected Return:</strong> <span id="returnExpectedDate"></span></div>
                <div class="mb-3">
                    <label class="form-label">Actual Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="actualReturnDate" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="submitReturn()">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resolve Incident Modal -->
<div class="modal fade" id="resolveIncidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-gavel"></i> RESOLVE INCIDENT</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="resolveIncidentID">
                <input type="hidden" id="resolveIncidentType">
                <input type="hidden" id="resolveBookID">

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Resolution Method <span class="text-danger">*</span></label>
                    <select class="form-control" id="resolutionMethod" onchange="toggleResolutionFields()" style="font-weight: 700;">
                        <option value="returned">Returned / Found / Fixed</option>
                        <option value="replacenew">Replace New (Provide New ISBN)</option>
                        <option value="cash">Cash Payment (Tsh)</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="replacementIsbnSection">
                    <label class="form-label font-weight-bold">New ISBN for Replacement <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="replacementIsbn" placeholder="Enter new ISBN">
                </div>

                <div class="mb-3 d-none" id="paymentAmountSection">
                    <label class="form-label font-weight-bold">Amount Paid (Tsh) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="paymentAmount" placeholder="Enter amount">
                </div>
                
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Resolution Notes</label>
                    <textarea class="form-control" id="resolveNotes" rows="2" placeholder="Notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">CLOSE</button>
                <button type="button" class="btn btn-primary-custom" onclick="submitResolution()">
                    <i class="fa fa-check"></i> SUBMIT RESOLUTION
                </button>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        let searchTimer = null;
        let allBooks = [];
        let selectedBookID = null;
        let selectedBorrowers = [];
        let $;
        
        function initLibrary() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initLibrary, 100);
                return;
            }
            $ = jQuery;
            
            $(document).ready(function() {
                loadBooks();
                loadBorrows();
                
                $('#filterClass, #filterSubject, #filterStatus').on('change', loadBooks);
                $('#searchBook').on('input', () => {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(loadBooks, 500);
                });

                $('#toggleActionsBtn').on('click', function() {
                    const section = $('#expandedActions');
                    const chevron = $('#actionChevron');
                    if (section.is(':visible')) {
                        section.slideUp(300);
                        chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    } else {
                        section.slideDown(300);
                        chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    }
                });

                $('#bookModal').on('hidden.bs.modal', function() {
                    $('#bookForm')[0].reset();
                    $('#bookID').val('');
                    $('#isbnList').html(getIsbnRowHtml());
                });

                $('#addIsbnRowBtn').on('click', addIsbnRow);
                $(document).on('click', '.remove-isbn-btn', function() { $(this).closest('.isbn-row').remove(); });
                $('#borrowerType').on('change', toggleBorrowerSearch);
                $('#bookType').on('change', toggleBookTypeFields);
                
                $(document).on('input', '#searchStudent', function() {
                    const search = $(this).val();
                    if (searchTimer) clearTimeout(searchTimer);
                    if (search.length >= 2) searchTimer = setTimeout(() => searchStudents(search), 300);
                    else $('#studentResults').hide();
                });

                $('#lostIsbn, #damagedIsbn').on('input', function() {
                    const isbn = $(this).val();
                    const prefix = $(this).attr('id').replace('Isbn', '');
                    if (isbn.length >= 1) lookupBookByIsbn(isbn, false, prefix);
                    else $(`#${prefix}BookInfo`).hide();
                });

                $('#lostStudentSearch, #damagedStudentSearch').on('input', function() {
                    const search = $(this).val();
                    const prefix = $(this).attr('id').replace('StudentSearch', '');
                    const type = $(`#${prefix}By`).val();
                    if (search.length >= 2) searchStudents(search, prefix, type);
                    else $(`#${prefix}StudentResults`).hide();
                });

                $('#borrowISBN').on('input', function() {
                    const isbn = $(this).val();
                    if (isbn.length >= 1) {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => lookupBookByIsbn(isbn, false, 'borrow'), 500);
                    } else {
                        $('#borrowBookInfo').hide();
                    }
                });
            });
        }

        function loadBooks() {
            const data = { 
                classID: $('#filterClass').val(), 
                subjectID: $('#filterSubject').val(), 
                search: $('#searchBook').val() 
            };
            $('#loaderOverlay').css('display', 'flex').show();
            $.get('{{ route("get_books") }}', data, function(res) {
                if (res.success) {
                    allBooks = res.books;
                    $('#booksCountInfo').text(`Showing ${allBooks.length} Books`);
                    renderBooksGrid(allBooks, $('#filterStatus').val());
                }
            }).fail(function(err) {
                console.error("Failed to load books:", err);
                Swal.fire('Error', 'Failed to sync library inventory. Please try again.', 'error');
            }).always(function() {
                $('#loaderOverlay').hide();
            });
        }

        function exportLibraryPdf() {
            const classID = $('#filterClass').val() || '';
            const subjectID = $('#filterSubject').val() || '';
            const status = $('#filterStatus').val() || '';
            const search = $('#searchBook').val() || '';
            
            const url = `{{ route('export_library_pdf') }}?classID=${classID}&subjectID=${subjectID}&status=${status}&search=${encodeURIComponent(search)}`;
            
            // Show progress modal
            const progressModal = new bootstrap.Modal(document.getElementById('downloadProgressModal'));
            progressModal.show();
            
            $('#pdfProgressBar').css('width', '0%');
            $('#pdfProgressPercent').text('0%');

            let progress = 0;
            const interval = setInterval(function() {
                if (progress < 95) {
                    let inc = 2 + Math.random() * 5;
                    if (progress > 80) inc = 0.5 + Math.random();
                    progress += inc;
                    if (progress > 95) progress = 95;
                    $('#pdfProgressBar').css('width', progress + '%');
                    $('#pdfProgressPercent').text(Math.round(progress) + '%');
                }
            }, 400);

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Server error: ' + response.statusText);
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(interval);
                    $('#pdfProgressBar').css('width', '100%');
                    $('#pdfProgressPercent').text('100%');
                    
                    setTimeout(() => {
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = `Library_Report_${new Date().getTime()}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);
                        
                        setTimeout(() => {
                            progressModal.hide();
                            setTimeout(() => {
                                $('#pdfProgressBar').css('width', '0%');
                                $('#pdfProgressPercent').text('0%');
                            }, 500);
                        }, 800);
                    }, 400);
                })
                .catch(error => {
                    clearInterval(interval);
                    progressModal.hide();
                    Swal.fire('Error', 'Failed to generate PDF: ' + error.message, 'error');
                });
        }

        function renderBooksGrid(books, statusFilter) {
            let html = '';
            const filtered = statusFilter 
                ? books.filter(b => {
                    const s = b.is_lost ? 'lost' : (b.is_damaged ? 'damaged' : (b.is_overdue ? 'overdue' : (b.is_available ? 'available' : 'borrowed')));
                    return s === statusFilter;
                })
                : books;

            if (filtered.length === 0) {
                $('#booksGrid').html('<div class="col-12 text-center py-5"><h6>NO BOOKS FOUND</h6></div>');
                return;
            }

            filtered.forEach(book => {
                let s = 'available';
                if (book.is_lost) s = (book.loss_info && book.loss_info.payment_status === 'paid') ? 'lost_resolved' : 'lost';
                else if (book.is_damaged) s = (book.damage_info && book.damage_info.payment_status === 'paid') ? 'damaged_resolved' : 'damaged';
                else if (book.is_overdue) s = 'overdue';
                else if (!book.is_available) s = 'borrowed';

                html += `
                    <div class="book-card-widget ${s}" onclick="openBookDetails('${book.bookID}')">
                        <div class="book-status-badge"></div>
                        <i class="fa fa-book book-icon"></i>
                        <span class="book-title">${book.book_title}</span>
                        <span class="book-author">${book.author || 'N/A'}</span>
                        <div class="mt-3 small font-weight-bold" style="font-size: 8px; color: #999;">${book.isbn}</div>
                    </div>`;
            });
            $('#booksGrid').html(html);
        }

        function openBookDetails(bookID) {
            console.log("Opening details for bookID:", bookID);
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) {
                console.warn("Book not found in allBooks array. ID:", bookID);
                return;
            }

            const s = book.is_lost ? 'lost' : (book.is_damaged ? 'damaged' : (book.is_overdue ? 'overdue' : (book.is_available ? 'available' : 'borrowed')));
            const statusColor = book.is_lost ? '#6f42c1' : (book.is_damaged ? '#fd7e14' : (book.is_overdue ? '#dc3545' : (book.is_available ? '#28a745' : '#ffc107')));
            let possessionLabel = book.is_lost ? 'LOST BY' : (book.is_damaged ? 'DAMAGED BY' : (!book.is_available ? 'OCCUPIED BY' : 'POSSESSION'));

            let content = `
                <div class="p-4 bg-light text-center border-bottom">
                    <i class="fa fa-book fa-5x mb-3" style="color: ${statusColor}"></i>
                    <h5 class="font-weight-bold mb-1">${book.book_title}</h5>
                    <p class="text-muted small mb-0">${book.author || 'UNKNOWN'}</p>
                </div>
                <div class="p-4">
                    <div class="row">
                        <div class="col-6 mb-4"><label class="small text-muted font-weight-bold d-block">SERIAL/ISBN</label><strong>${book.isbn}</strong></div>
                        <div class="col-6 mb-4 text-right"><label class="small text-muted font-weight-bold d-block">STATUS</label><span class="badge" style="background:${statusColor};color:#fff">${s.toUpperCase()}</span></div>
                        <div class="col-12 mb-4">
                            <label class="small text-muted font-weight-bold d-block border-bottom pb-1">${possessionLabel}</label>
                            ${book.occupants && book.occupants.length > 0 ? `
                                <div class="occupants-list">
                                    ${book.occupants.map(occ => `
                                        <div class="d-flex align-items-center p-2 border mb-2 shadow-sm rounded bg-white">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">${occ.name}</h6>
                                                <small class="text-muted">${(occ.type || 'STUDENT').toUpperCase()} | DUE: ${formatDate(occ.expected_return_date)}</small>
                                            </div>
                                            <button class="btn btn-success btn-xs py-1" onclick="openReturnModal('${occ.borrowID}', '${occ.expected_return_date}')"><i class="fa fa-undo"></i> RETURN</button>
                                        </div>`).join('')}
                                </div>
                            ` : (book.loss_info?.student ? `<div class="p-3 border-left border-danger bg-light"><strong>LOST BY:</strong> ${book.loss_info.student.name}</div>` : 
                                 (book.damage_info?.student ? `<div class="p-3 border-left border-warning bg-light"><strong>DAMAGED BY:</strong> ${book.damage_info.student.name}</div>` : 
                                 `<div class="p-2 text-success font-weight-bold"><i class="fa fa-check-circle"></i> AVAILABLE IN LIBRARY</div>`))}
                        </div>
                    </div>
                </div>`;
            
            $('#bookDetailsContent').html(content);
            let buttons = `
                <button class="btn btn-outline-danger btn-sm mr-auto" onclick="deleteBook('${book.bookID}', 'details')">
                    <i class="fa fa-trash"></i> DELETE
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="editBook('${book.bookID}')">EDIT</button>`;
            if (!book.is_available && !book.is_lost && !book.is_damaged) 
                buttons += `<button class="btn btn-success btn-sm ml-2" onclick="lookupBookByIsbn('${book.isbn}', true)">QUICK RETURN</button>`;
            if (book.is_available)
                buttons += `<button class="btn btn-warning btn-sm ml-2" onclick="borrowSpecificBook('${book.isbn}')">LEND BOOK</button>`;
            if (book.is_lost || book.is_damaged)
                buttons += `<button class="btn btn-primary-custom btn-sm ml-2" onclick="showResolveModal('${book.bookID}', '${book.is_lost ? 'loss' : 'damage'}')">RESOLVE</button>`;
            
            $('#bookActionFooter').html(buttons);
            $('#bookDetailsModal').modal('show');
        }

        function borrowSpecificBook(isbn) { $('#bookDetailsModal').modal('hide'); showBorrowBookModal(isbn); }

        function loadBorrows() {
            $.get('{{ route("get_book_borrows") }}', { status: 'borrowed' }, function(res) {
                if (res.success) {
                    let html = '';
                    res.borrows.forEach((b, i) => {
                        let name = b.student ? b.student.first_name + ' ' + b.student.last_name : (b.teacher ? b.teacher.first_name : 'Staff');
                        html += `<tr><td>${i+1}</td><td>${name}</td><td>${b.book.book_title}</td><td>${formatDate(b.borrow_date)}</td><td><span class="badge badge-warning">BORROWED</span></td><td><button class="btn btn-xs btn-success" onclick="openReturnModal('${b.borrowID}', '${b.expected_return_date}')">RETURN</button></td></tr>`;
                    });
                    $('#borrowsTableBody').html(html || '<tr><td colspan="6" class="text-center">No active borrows found</td></tr>');
                }
            });
        }

        function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'; }

        function showBorrowBookModal(isbn = '') {
            $('#borrowForm')[0].reset();
            $('#borrowBookInfo').hide();
            selectedBorrowers = [];
            $('#selectedBorrowersList').empty();
            if (isbn) { $('#borrowISBN').val(isbn); lookupBookByIsbn(isbn); }
            $('#borrowModal').modal('show');
        }

        function lookupBookByIsbn(isbn, autoReturn = false, prefix = 'borrow') {
            $(`#${prefix}IsbnSpinner`).removeClass('d-none');
            $.get('{{ route("get_book_by_isbn") }}', { isbn }, function(res) {
                console.log("ISBN Lookup Response:", res);
                if (res.success) {
                    if (res.multiple) {
                        let html = '<div class="p-2 border mb-2 bg-light rounded"><h6 class="font-weight-bold mb-3 text-primary"><i class="fa fa-copy"></i> Multiple Books Found:</h6>';
                        res.results.forEach(it => {
                            const b = it.book;
                            const cls = (b.class && b.class.class_name) ? b.class.class_name : 'N/A';
                            const sub = (b.subject && b.subject.subject_name) ? b.subject.subject_name : 'N/A';
                            
                            let statusText = 'AVAILABLE';
                            let statusClass = 'badge-success';
                            let borderStyle = 'border-left: 5px solid #28a745 !important;';
                            let canSelect = it.is_available;
                            
                            if (!it.is_available) {
                                if (b.is_lost || b.status === 'Lost') { statusText = 'LOST'; statusClass = 'badge-dark'; borderStyle = 'border-left: 4px solid #6f42c1 !important; opacity: 0.8;'; }
                                else if (b.is_damaged || b.status === 'Damaged') { statusText = 'DAMAGED'; statusClass = 'badge-warning'; borderStyle = 'border-left: 4px solid #fd7e14 !important; opacity: 0.8;'; }
                                else { statusText = 'OCCUPIED'; statusClass = 'badge-danger'; borderStyle = 'border-left: 4px solid #dc3545 !important; opacity: 0.8;'; }
                            }
                            
                            let clickHandler = canSelect ? `displayBookInfo(${JSON.stringify(it).replace(/"/g, '&quot;')}, '${prefix}', ${autoReturn})` : 'void(0)';
                            
                            html += `
                                <div class="p-3 border rounded mb-3 bg-white shadow-sm ${canSelect ? 'hover-select-card' : ''}" 
                                     style="${borderStyle} cursor:${canSelect ? 'pointer' : 'not-allowed'}; position: relative;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div style="max-width: 70%;">
                                            <strong class="text-dark d-block" style="font-size: 14px;">${b.book_title}</strong>
                                            <small class="text-muted">ISBN: ${b.isbn}</small>
                                        </div>
                                        <span class="badge ${statusClass} px-2 py-1">${statusText}</span>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        <div><i class="fa fa-tag"></i> <strong>Class:</strong> ${cls} | <strong>Subject:</strong> ${sub}</div>
                                        ${!it.is_available && it.borrower ? `<div class="mt-1 text-danger fw-bold"><i class="fa fa-user-clock"></i> With: ${it.borrower.name} (${it.borrower.type.toUpperCase()})</div>` : ''}
                                    </div>
                                    <div class="mt-3">
                                        ${canSelect ? 
                                            `<button class="btn btn-sm btn-primary btn-block shadow-sm" onclick="${clickHandler}">
                                                <i class="fa fa-check"></i> SELECT THIS COPY
                                            </button>` : 
                                            `<button class="btn btn-sm btn-secondary btn-block" disabled>
                                                <i class="fa fa-lock"></i> ${statusText}
                                            </button>`
                                        }
                                    </div>
                                </div>`;
                        });
                        $(`#${prefix}BookInfo`).html(html + '</div>').slideDown();
                    } else {
                        displayBookInfo(res, prefix, autoReturn);
                    }
                }
            }).fail(function(err) {
                $(`#${prefix}BookInfo`).html('<div class="p-2 text-danger small"><i class="fa fa-exclamation-circle"></i> Book not found or invalid ID</div>').show();
                if(autoReturn) Swal.fire('Error', 'Book not found with this ISBN', 'error');
            }).always(function() {
                $(`#${prefix}IsbnSpinner`).addClass('d-none');
            });
        }

        function displayBookInfo(res, prefix, autoReturn) {
            console.log("Displaying Book Info:", res);
            const book = res.book;
            selectedBookID = book.bookID;
            const cls = (book.class && book.class.class_name) ? book.class.class_name : 'N/A';
            const sub = (book.subject && book.subject.subject_name) ? book.subject.subject_name : 'N/A';
            
            // Clear input and hide multiple results if it was a selection
            if (prefix === 'borrow') {
                $(`#borrowISBN`).val(book.isbn); // Keep ISBN but selection is confirmed
            }

            let html = `
                <div class="p-3 border rounded shadow-sm bg-white animate__animated animate__pulse" style="border-left: 8px solid ${res.is_available ? '#28a745' : '#dc3545'} !important; background: #fdfdfd;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge badge-success mb-1 shadow-sm px-3"><i class="fa fa-check-circle"></i> CONFIRMED SELECTION</span>
                            <strong class="h5 d-block mb-0 text-primary mt-1">${book.book_title}</strong>
                        </div>
                        <span class="badge ${res.is_available ? 'badge-success' : 'badge-danger'} px-3 py-2">${res.is_available ? 'AVAILABLE' : 'BUSY / OCCUPIED'}</span>
                    </div>
                    <div class="small">
                        <div class="mb-1"><strong>Class:</strong> ${cls} | <strong>Subject:</strong> ${sub}</div>
                        <div class="mb-1"><strong>Author:</strong> ${book.author || 'N/A'} | <strong>ISBN:</strong> ${book.isbn}</div>
                    </div>
                    ${!res.is_available && res.borrower ? `
                        <div class="mt-3 p-2 bg-warning-light rounded border-left border-warning" style="background: #fffdf5; border-left: 3px solid #ffc107 !important;">
                            <small class="d-block text-muted font-weight-bold uppercase" style="font-size: 10px;">CURRENTLY WITH</small>
                            <div class="font-weight-bold text-dark" style="font-size: 13px;">${res.borrower.name}</div>
                            <small class="text-muted">${res.borrower.type.toUpperCase()} | Due: ${formatDate(res.borrower.expected_return_date)}</small>
                        </div>
                    ` : ''}
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button class="btn btn-xs btn-outline-danger" onclick="deleteBook(${book.bookID}, '${prefix}')">
                            <i class="fa fa-trash"></i> DELETE BOOK
                        </button>
                        <button class="btn btn-xs btn-outline-secondary" onclick="clearBookSelection('${prefix}')">
                            <i class="fa fa-times"></i> REMOVE SELECTION
                        </button>
                    </div>
                </div>`;
            $(`#${prefix}BookInfo`).html(html).slideDown();
            $(`#isBookAvailableForBorrow`).val(res.is_available ? '1' : '0');
            if (autoReturn && res.borrower) openReturnModal(res.borrower.borrowID, res.borrower.expected_return_date);
        }

        function clearBookSelection(prefix) {
            selectedBookID = null;
            $(`#${prefix}ISBN`).val('');
            $(`#${prefix}BookInfo`).slideUp().html('');
            $(`#isBookAvailableForBorrow`).val('0');
        }

        function deleteBook(bookID, prefix) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This book will be permanently deleted from the inventory!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('delete_book') }}/${bookID}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Deleted!', 'The book has been deleted.', 'success');
                                if (prefix === 'details') {
                                    $('#bookDetailsModal').modal('hide');
                                    loadBooks();
                                } else {
                                    clearBookSelection(prefix);
                                }
                            } else {
                                Swal.fire('Error', res.message || 'Could not delete book', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'An error occurred while deleting the book.', 'error');
                        }
                    });
                }
            });
        }

        function searchStudents(search, prefix = '', forcedType = null) {
            const type = forcedType || (prefix ? $(`#${prefix}By`).val() : $('#borrowerType').val());
            const url = type === 'teacher' ? '{{ route("search_teachers") }}' : (type === 'staff' ? '{{ route("search_staff") }}' : '{{ route("get_students") }}');
            $.get(url, { search }, function(res) {
                if (res.success) {
                    let html = '';
                    const items = type === 'teacher' ? res.teachers : (type === 'staff' ? res.staff : res.students);
                    if (items && items.length > 0) {
                        items.forEach(it => {
                            let name = it.name || `${it.first_name} ${it.last_name}`;
                            html += `<div class="p-2 border-bottom" style="cursor:pointer" onclick="selectStudent(${it.studentID || it.id}, '${name.replace(/'/g, "\\'")}', '${prefix}', '${type}')">
                                        <strong>${name}</strong>
                                     </div>`;
                        });
                    } else {
                        html = '<div class="p-2 text-muted text-center small">No matches found</div>';
                    }
                    $(`#${prefix ? prefix + 'StudentResults' : 'studentResults'}`).html(html).show();
                }
            });
        }

        function selectStudent(id, name, prefix, type) {
            if (prefix) {
                $(`#${prefix}StudentID, #${prefix}TeacherID, #${prefix}StaffID`).val('');
                if (type === 'student') $(`#${prefix}StudentID`).val(id);
                else if (type === 'teacher') $(`#${prefix}TeacherID`).val(id);
                else $(`#${prefix}StaffID`).val(id);
                $(`#${prefix}StudentSearch`).val(name);
                $(`#${prefix}StudentResults`).hide();
            } else {
                if (!selectedBorrowers.some(b => b.id == id && b.type == type)) {
                    selectedBorrowers.push({ id, name, type });
                    renderBorrowerChips();
                }
                $('#searchStudent').val(''); $('#studentResults').hide();
            }
        }

        function renderBorrowerChips() {
            let html = '';
            selectedBorrowers.forEach((b, i) => {
                html += `<div class="badge badge-primary p-2 mr-1 mb-1 shadow-sm d-inline-flex align-items-center">
                            <span class="mr-2">${b.name} (${b.type.toUpperCase()})</span>
                            <i class="fa fa-times-circle cursor-pointer" onclick="removeBorrower(${i})" style="font-size: 14px;"></i>
                         </div>`;
            });
            $('#selectedBorrowersList').html(html);
        }

        function removeBorrower(index) {
            selectedBorrowers.splice(index, 1);
            renderBorrowerChips();
        }

        function toggleBorrowerSearch() {
            const type = $('#borrowerType').val();
            if (!type) {
                $('#borrowerSelectionContainer').hide();
                return;
            }
            $('#borrowerSelectionContainer').show();
            $('#borrowerSearchLabel').text('Search ' + type.charAt(0).toUpperCase() + type.slice(1));
            $('#searchStudent').attr('placeholder', 'Enter ' + type + ' name...').val('');
            $('#studentResults').hide();
        }

        function saveBorrow() {
            if ($('#isBookAvailableForBorrow').val() === '0') { Swal.fire('Error', 'Book is currently unavailable', 'error'); return; }
            if (selectedBorrowers.length === 0) { Swal.fire('Error', 'Please select at least one borrower', 'warning'); return; }
            const expectedReturnDate = $('#expectedReturnDate').val();
            if (!expectedReturnDate) { Swal.fire('Error', 'Please select an expected return date', 'warning'); return; }

            $('#saveBorrowBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> SAVING...');
            $.post('{{ route("borrow_book") }}', {
                _token: '{{ csrf_token() }}',
                bookID: selectedBookID,
                isbn: $('#borrowISBN').val(),
                borrower_ids: selectedBorrowers.map(b => b.id),
                borrower_types: selectedBorrowers.map(b => b.type),
                expected_return_date: expectedReturnDate
            }, function(res) {
                $('#saveBorrowBtn').prop('disabled', false).html('<i class="fa fa-save"></i> SAVE');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Borrowed', text: res.message, timer: 2000, showConfirmButton: false });
                    $('#borrowModal').modal('hide');
                    loadBooks();
                    loadBorrows();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }).fail(() => {
                $('#saveBorrowBtn').prop('disabled', false).html('<i class="fa fa-save"></i> SAVE');
                Swal.fire('Error', 'Server connection failed', 'error');
            });
        }

        function submitReturn() {
            const borrowID = $('#returnBorrowID').val();
            const actualDate = $('#actualReturnDate').val();
            if(!borrowID || !actualDate) { Swal.fire('Error', 'Missing required data', 'warning'); return; }
            $.post(`{{ url('return_book') }}/${borrowID}`, { _token: '{{ csrf_token() }}', return_date: actualDate }, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Returned', text: 'Book returned successfully', timer: 2000, showConfirmButton: false });
                    $('#returnModal').modal('hide'); $('#bookDetailsModal').modal('hide');
                    loadBooks(); loadBorrows();
                } else Swal.fire('Error', res.message, 'error');
            });
        }

        function showAddBookModal() {
            $('#bookForm')[0].reset();
            $('#bookID').val('');
            $('#isbnList').html(getIsbnRowHtml());
            $('#bookEntriesSection').removeClass('d-none');
            $('#bookModalTitle').html('<i class="fa fa-book"></i> Add New Book Entries');
            $('#saveBooksBtn').removeClass('d-none');
            $('#saveBookBtn').addClass('d-none');
            $('#bookStatusDiv').hide();
            $('#bookModal').modal('show');
        }

        function addIsbnRow() {
            $('#isbnList').append(getIsbnRowHtml(true));
        }

        function getIsbnRowHtml(withRemove = false) {
            return `<div class="row align-items-end isbn-row mb-2">
                <div class="${withRemove ? 'col-md-10' : 'col-md-12'}">
                    <label class="form-label font-weight-bold">ISBN/SERIAL <span class="text-danger">*</span></label>
                    <input type="text" class="form-control isbn-input" placeholder="ENTER SERIAL/ISBN" required>
                </div>
                ${withRemove ? `<div class="col-md-2 text-right"><button type="button" class="btn btn-danger btn-sm remove-isbn-btn"><i class="fa fa-trash"></i></button></div>` : ''}
            </div>`;
        }

        function showDamagedBookModal() {
            $('#damagedForm')[0].reset();
            $('#damagedBookInfo').hide();
            $('#damagedStudentSection').hide();
            $('#damagedModal').modal('show');
        }

        function showLostBookModal() {
            $('#lostForm')[0].reset();
            $('#lostBookInfo').hide();
            $('#lostStudentSection').hide();
            $('#lostModal').modal('show');
        }

        function toggleIncidentBy(prefix) {
            const val = $(`#${prefix}By`).val();
            if (val === 'other') $(`#${prefix}StudentSection`).slideUp();
            else $(`#${prefix}StudentSection`).slideDown();
            $(`#${prefix}SearchLabel`).text('Search ' + val.charAt(0).toUpperCase() + val.slice(1));
            $(`#${prefix}StudentID, #${prefix}TeacherID, #${prefix}StaffID`).val('');
            $(`#${prefix}StudentSearch`).val('').attr('placeholder', 'Search ' + val + ' name...');
        }

        function saveDamagedBook() {
            const isbn = $('#damagedIsbn').val();
            const damaged_by = $('#damagedBy').val();
            const studentID = $('#damagedStudentID').val();
            const teacherID = $('#damagedTeacherID').val();
            const staffID = $('#damagedStaffID').val();
            if(!isbn) { Swal.fire('Error', 'ISBN is required', 'warning'); return; }
            $.post('{{ route("store_book_damage") }}', { _token: '{{ csrf_token() }}', isbn, bookID: selectedBookID, damaged_by, studentID, teacherID, staffID, notes: $('#damagedReason').val() }, function(res) {
                if(res.success) { Swal.fire('Saved', 'Damage recorded', 'success'); $('#damagedModal').modal('hide'); loadBooks(); }
                else Swal.fire('Error', res.message, 'error');
            });
        }

        function saveLostBook() {
            const isbn = $('#lostIsbn').val();
            const lost_by = $('#lostBy').val();
            const studentID = $('#lostStudentID').val();
            const teacherID = $('#lostTeacherID').val();
            const staffID = $('#lostStaffID').val();
            if(!isbn) { Swal.fire('Error', 'ISBN is required', 'warning'); return; }
            $.post('{{ route("store_book_loss") }}', { _token: '{{ csrf_token() }}', isbn, bookID: selectedBookID, lost_by, studentID, teacherID, staffID, notes: $('#lostReason').val() }, function(res) {
                if(res.success) { Swal.fire('Saved', 'Loss recorded', 'success'); $('#lostModal').modal('hide'); loadBooks(); }
                else Swal.fire('Error', res.message, 'error');
            });
        }

        function saveBooks() {
            let isbns = [];
            $('.isbn-input').each(function() { if($(this).val()) isbns.push($(this).val()); });
            if (isbns.length === 0 || !$('#bookTitle').val()) { Swal.fire('Error', 'Title and at least one ISBN are required', 'warning'); return; }
            $.post('{{ route("store_book") }}', {
                _token: '{{ csrf_token() }}', book_type: $('#bookType').val(), classID: $('#bookClassID').val(), subjectID: $('#bookSubjectID').val(),
                book_title: $('#bookTitle').val(), author: $('#bookAuthor').val(), publisher: $('#bookPublisher').val(),
                publication_year: $('#bookPublicationYear').val(), description: $('#bookDescription').val(), isbns
            }, function(res) {
                if(res.success) { Swal.fire('Saved', 'Books added successfully', 'success'); $('#bookModal').modal('hide'); loadBooks(); }
                else Swal.fire('Error', res.message, 'error');
            });
        }

        function saveBook() {
            const bookID = $('#bookID').val();
            $.post('{{ url("update_book") }}/' + bookID, {
                _token: '{{ csrf_token() }}', book_type: $('#bookType').val(), classID: $('#bookClassID').val(), subjectID: $('#bookSubjectID').val(),
                book_title: $('#bookTitle').val(), author: $('#bookAuthor').val(), publisher: $('#bookPublisher').val(),
                publication_year: $('#bookPublicationYear').val(), description: $('#bookDescription').val(), status: $('#bookStatus').val(),
                isbn: $('.isbn-input').first().val()
            }, function(res) {
                if(res.success) { Swal.fire('Updated', 'Book updated successfully', 'success'); $('#bookModal').modal('hide'); loadBooks(); }
                else Swal.fire('Error', res.message, 'error');
            });
        }

        function showResolveModal(bookID, type) {
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) return;
            $('#resolveBookID').val(book.bookID);
            $('#resolveIncidentType').val(type);
            $('#resolveIncidentID').val(type === 'loss' ? book.loss_info.lossID : book.damage_info.damageID);
            $('#resolutionMethod').val('returned');
            $('#resolveNotes').val(''); $('#replacementIsbn').val(''); $('#paymentAmount').val('');
            toggleResolutionFields(); $('#bookDetailsModal').modal('hide'); $('#resolveIncidentModal').modal('show');
        }

        function editBook(bookID) {
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) return;
            $('#bookForm')[0].reset();
            $('#bookID').val(book.bookID); $('#bookType').val(book.book_type); $('#bookClassID').val(book.classID);
            $('#bookTitle').val(book.book_title); $('#bookAuthor').val(book.author);
            $('#bookPublisher').val(book.publisher); $('#bookPublicationYear').val(book.publication_year);
            $('#bookDescription').val(book.description); $('#bookStatus').val(book.status);
            if (book.classID) getSubjects(book.classID, book.subjectID);
            $('#isbnList').html(`<div class="row isbn-row mb-2"><div class="col-12"><label class="form-label font-weight-bold">ISBN</label><input type="text" class="form-control isbn-input" value="${book.isbn}" required></div></div>`);
            $('#bookEntriesSection').addClass('d-none'); $('#bookModalTitle').text('Edit Book');
            $('#saveBooksBtn').addClass('d-none'); $('#saveBookBtn').removeClass('d-none');
            $('#bookStatusDiv').show(); $('#bookDetailsModal').modal('hide'); $('#bookModal').modal('show');
        }

        function getSubjects(classID, selectedID = null) {
            $.get('{{ route("get_subjects_by_class") }}', { classID }, function(res) {
                if(res.success) {
                    let html = '<option value="">Select Subject</option>';
                    res.subjects.forEach(s => { html += `<option value="${s.subjectID}" ${selectedID == s.subjectID ? 'selected' : ''}>${s.subject_name}</option>`; });
                    $('#bookSubjectID').html(html);
                }
            });
        }

        function toggleResolutionFields() {
            const method = $('#resolutionMethod').val();
            $('#replacementIsbnSection').toggleClass('d-none', method !== 'replacenew');
            $('#paymentAmountSection').toggleClass('d-none', method !== 'cash');
        }

        function submitResolution() {
            const type = $('#resolveIncidentType').val();
            const method = $('#resolutionMethod').val();
            if (method === 'replacenew' && !$('#replacementIsbn').val()) { Swal.fire('Error', 'New ISBN is required for replacement', 'warning'); return; }
            if (method === 'cash' && !$('#paymentAmount').val()) { Swal.fire('Error', 'Payment amount is required', 'warning'); return; }
            $.post('{{ route("resolve_book_incident") }}', {
                _token: '{{ csrf_token() }}', incident_type: type, incidentID: $('#resolveIncidentID').val(), bookID: $('#resolveBookID').val(),
                method, replacement_isbn: $('#replacementIsbn').val(), amount: $('#paymentAmount').val(), notes: $('#resolveNotes').val()
            }, function(res) {
                if(res.success) { Swal.fire('Resolved', 'Incident has been resolved', 'success'); $('#resolveIncidentModal').modal('hide'); loadBooks(); }
                else Swal.fire('Error', res.message, 'error');
            });
        }

        function toggleBookTypeFields() {
            const type = $('#bookType').val();
            if (type === 'Other') {
                $('#classSelectionDiv, #subjectSelectionDiv').slideUp();
                $('#bookClassID, #bookSubjectID').val('').prop('required', false);
            } else {
                $('#classSelectionDiv, #subjectSelectionDiv').slideDown();
                $('#bookClassID, #bookSubjectID').prop('required', true);
            }
        }

        // Global exports
        window.openBookDetails = openBookDetails;
        window.openReturnModal = (id, date) => { $('#returnBorrowID').val(id); $('#returnExpectedDate').text(formatDate(date)); $('#returnModal').modal('show'); };
        window.loadBooks = loadBooks;
        window.showBorrowBookModal = showBorrowBookModal;
        window.showAddBookModal = showAddBookModal;
        window.showDamagedBookModal = showDamagedBookModal;
        window.showLostBookModal = showLostBookModal;
        window.borrowSpecificBook = borrowSpecificBook;
        window.selectStudent = selectStudent;
        window.lookupBookByIsbn = lookupBookByIsbn;
        window.saveBorrow = saveBorrow;
        window.submitReturn = submitReturn;
        window.saveBooks = saveBooks;
        window.saveBook = saveBook;
        window.saveDamagedBook = saveDamagedBook;
        window.saveLostBook = saveLostBook;
        window.showResolveModal = showResolveModal;
        window.editBook = editBook;
        window.toggleResolutionFields = toggleResolutionFields;
        window.submitResolution = submitResolution;
        window.toggleIncidentBy = toggleIncidentBy;
        window.toggleBorrowerSearch = toggleBorrowerSearch;
        window.toggleBookTypeFields = toggleBookTypeFields;
        window.displayBookInfo = displayBookInfo;
        window.clearBookSelection = clearBookSelection;
        window.deleteBook = deleteBook;
        window.getSubjects = getSubjects;
        window.removeBorrower = removeBorrower;
        window.exportLibraryPdf = exportLibraryPdf;

        initLibrary();
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('inventoryChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Available', 'Borrowed', 'Lost', 'Damaged'],
                    datasets: [{
                        data: [{{ $availableBooks }}, {{ $issuedBooks }}, {{ $lostBooks }}, {{ $damagedBooks }}],
                        backgroundColor: ['#28a745', '#ffc107', '#6f42c1', '#fd7e14'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
                }
            });
        }
    });
</script>

</body>
</html>
