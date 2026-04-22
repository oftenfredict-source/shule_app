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
                <div class="col-md-1">
                    <button class="btn btn-primary-custom btn-sm w-100" onclick="loadBooks()">
                        <i class="fa fa-refresh"></i>
                    </button>
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
                                <small class="text-danger d-none isbn-error">Unique ID is already taken.</small>
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
                            <div class="input-group-append d-none" id="borrowIsbnSpiner">
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
                        <select class="form-control" id="borrowerType" onchange="toggleBorrowerSearch()">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Other Staff</option>
                        </select>
                    </div>
                    <div class="mb-3" id="studentSearchDiv">
                        <label class="form-label" id="borrowerSearchLabel">Search Student <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="searchStudent" placeholder="Search by name or reference...">
                        <input type="hidden" id="borrowStudentID">
                        <input type="hidden" id="borrowTeacherID">
                        <input type="hidden" id="borrowStaffID">
                        <div id="studentResults" class="mt-2 shadow-sm border rounded bg-white" style="max-height: 200px; overflow-y: auto; display: none; position: relative; z-index: 100;"></div>
                    </div>
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
                            <div class="input-group-append d-none" id="lostIsbnSpiner">
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
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Other Staff</option>
                            <option value="other">General / Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="lostStudentSection">
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
                            <div class="input-group-append d-none" id="damagedIsbnSpiner">
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
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="staff">Other Staff</option>
                            <option value="other">General / Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="damagedStudentSection">
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

<!-- Book Details Detail Modal (The Popup requested) -->
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
                <!-- Content loaded dynamically -->
                <div class="p-5 text-center">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary-custom"></i>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 0;">CLOSE</button>
                <div id="bookActionFooter">
                    <!-- Dynamic action buttons (Edit, Return, etc.) -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Parent Message Modal -->
<div class="modal fade" id="parentMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-envelope"></i> Message Parent
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="parentMessageStudentID">
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" id="parentMessageText" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom" onclick="sendParentMessage()">
                    <i class="fa fa-paper-plane"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Return Book Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-check"></i> Return Book
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="returnBorrowID">
                <div class="mb-2"><strong>Expected Return:</strong> <span id="returnExpectedDate"></span></div>
                <div class="mb-3">
                    <label class="form-label">Actual Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="actualReturnDate" onchange="handleReturnDateChange()">
                </div>
                <div class="mb-3" id="lateReasonSection" style="display:none;">
                    <label class="form-label">Why is it late?</label>
                    <textarea class="form-control" id="lateReason" rows="2" placeholder="Reason for late return..."></textarea>
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
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-gavel"></i> RESOLVE INCIDENT
                </h5>
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
                    <input type="text" class="form-control" id="replacementIsbn" placeholder="Enter new ISBN for the new copy">
                </div>

                <div class="mb-3 d-none" id="paymentAmountSection">
                    <label class="form-label font-weight-bold">Amount Paid (Tsh) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="paymentAmount" placeholder="Enter amount paid">
                </div>
                
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Resolution Notes</label>
                    <textarea class="form-control" id="resolveNotes" rows="2" placeholder="Any additional notes..."></textarea>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">



<script>
    (function() {
        // Global State
        let searchTimer = null;
        let isbnCheckTimeout = null;
        let allBooks = [];

        let $;
        
        function initLibrary() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initLibrary, 100);
                return;
            }
            $ = jQuery;
            
            $(document).ready(function() {
                // Initialize default view
                loadBooks();
                loadBorrows();
                
                // Filter Events
                $('#filterClass, #filterSubject, #filterStatus').on('change', function() {
                    loadBooks();
                });
                
                $('#searchBook').on('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(loadBooks, 500);
                });

                $('.filterBorrowStatusBtn').on('click', function() {
                    $('.filterBorrowStatusBtn').removeClass('active');
                    $(this).addClass('active');
                    loadBorrows();
                });

                $('#toggleActionsBtn').on('click', function() {
                    $('#expandedActions').slideToggle(300, function() {
                        const isVisible = $(this).is(':visible');
                        $('#actionChevron').toggleClass('fa-chevron-up', isVisible).toggleClass('fa-chevron-down', !isVisible);
                    });
                });

                $('#filterBorrowDue').on('change', loadBorrows);

                // Modal close behavior (Reset forms)
                $('#bookModal').on('hidden.bs.modal', function() {
                    $('#bookForm')[0].reset();
                    $('#bookID').val('');
                    $('#isbnList').html(getIsbnRowHtml());
                });
                
                // Add ISBN Row logic
                $('#addIsbnRowBtn').on('click', addIsbnRow);
                $(document).on('click', '.remove-isbn-btn', function() {
                    $(this).closest('.isbn-row').remove();
                });

                // Borrower type toggle
                $('#borrowerType').on('change', toggleBorrowerSearch);
                
                // Student search for borrow
                $(document).on('input', '#searchStudent', function() {
                    const search = $(this).val();
                    if (searchTimer) clearTimeout(searchTimer);
                    if (search.length >= 2) {
                        searchTimer = setTimeout(() => searchStudents(search), 300);
                    } else {
                        $('#studentResults').hide();
                    }
                });

                // Incident ISBN Checkers
                $('#lostIsbn, #damagedIsbn').on('input', function() {
                    const isbn = $(this).val();
                    const prefix = $(this).attr('id').replace('Isbn', '');
                    if (isbn.length >= 1) {
                        lookupBookByIsbn(isbn, false, prefix);
                    } else {
                        $(`#${prefix}BookInfo`).hide();
                    }
                });

                // Incident Student Searchers
                $('#lostStudentSearch, #damagedStudentSearch').on('input', function() {
                    const search = $(this).val();
                    const prefix = $(this).attr('id').replace('StudentSearch', '');
                    const type = $(`#${prefix}By`).val();
                    if (search.length >= 2) {
                        searchStudents(search, prefix, type);
                    } else {
                        $(`#${prefix}StudentResults`).hide();
                    }
                });

                $('#borrowISBN').on('input', function() {
                    const isbn = $(this).val();
                    if (isbn.length >= 1) {
                        lookupBookByIsbn(isbn, false, 'borrow');
                    } else {
                        $('#borrowBookInfo').hide();
                    }
                });
            });
        }

        // --- Data Loading Functions ---

        function loadBooks() {
            const classID = $('#filterClass').val();
            const subjectID = $('#filterSubject').val();
            const status = $('#filterStatus').val();
            const search = $('#searchBook').val();
            
            $('#loaderOverlay').css('display', 'flex').show();
            $('#booksCountInfo').html('<i class="fa fa-spinner fa-spin mr-2"></i> SYNCING...');
            
            $.get('{{ route("get_books") }}', { classID, subjectID, search }, function(response) {
                $('#loaderOverlay').hide();
                if (response.success) {
                    allBooks = response.books;
                    $('#booksCountInfo').text(`Showing ${allBooks.length} Books`);
                    renderBooksGrid(allBooks, status);
                } else {
                    $('#booksGrid').html('<div class="col-12 text-center text-danger py-5">FAILED TO LOAD RECORDS</div>');
                }
            }).fail(function() {
                $('#booksGrid').html('<div class="col-12 text-center text-danger py-5">NETWORK ERROR</div>');
            });
        }

        function renderBooksGrid(books, statusFilter) {
            let html = '';
            const filteredBooks = statusFilter 
                ? books.filter(b => {
                    const s = b.is_lost ? 'lost' : (b.is_damaged ? 'damaged' : (b.is_overdue ? 'overdue' : (b.is_available ? 'available' : 'borrowed')));
                    return s === statusFilter;
                })
                : books;

            if (filteredBooks.length === 0) {
                $('#booksGrid').html('<div class="col-12 text-center py-5"><i class="fa fa-info-circle fa-2x mb-2 text-muted"></i><h6>NO BOOKS MATCH THIS CRITERIA</h6></div>');
                return;
            }

            filteredBooks.forEach(book => {
                let s = 'available';
                if (book.is_lost) {
                    s = (book.loss_info && book.loss_info.payment_status === 'paid') ? 'lost_resolved' : 'lost';
                } else if (book.is_damaged) {
                    s = (book.damage_info && book.damage_info.payment_status === 'paid') ? 'damaged_resolved' : 'damaged';
                } else if (book.is_overdue) {
                    s = 'overdue';
                } else if (!book.is_available) {
                    s = 'borrowed';
                }

                const statusLabel = s.toUpperCase().replace('_', ' ');
                
                html += `
                    <div class="book-card-widget ${s}" onclick="openBookDetails(${book.bookID})">
                        <div class="book-status-badge"></div>
                        <i class="fa fa-book book-icon"></i>
                        <span class="book-title">${book.book_title}</span>
                        <span class="book-author">${book.author || 'N/A'}</span>
                        <div class="mt-3 small font-weight-bold" style="font-size: 8px; color: #999; letter-spacing: 0.5px;">
                            ${book.isbn}
                        </div>
                    </div>
                `;
            });
            $('#booksGrid').html(html);
        }

        // --- Detail Modal (The "Popup") ---

        function openBookDetails(bookID) {
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) return;

            const s = book.is_lost ? 'lost' : (book.is_damaged ? 'damaged' : (book.is_overdue ? 'overdue' : (book.is_available ? 'available' : 'borrowed')));
            const statusColor = book.is_lost ? '#6f42c1' : (book.is_damaged ? '#fd7e14' : (book.is_overdue ? '#dc3545' : (book.is_available ? '#28a745' : '#ffc107')));
            
            // Dynamic Label for Possession
            let possessionLabel = 'POSSESSION DETAILS';
            if (book.is_lost) possessionLabel = 'LOST BY';
            else if (book.is_damaged) possessionLabel = 'DAMAGED BY';
            else if (!book.is_available) possessionLabel = 'OCCUPIED BY';

            let content = `
                <div class="p-4 bg-light text-center border-bottom">
                    <i class="fa fa-book fa-5x mb-3" style="color: ${statusColor}"></i>
                    <h5 class="font-weight-bold mb-1" style="color: #333">${book.book_title}</h5>
                    <p class="text-muted small font-weight-bold mb-0">${book.author || 'UNKNOWN AUTHOR'}</p>
                </div>
                <div class="p-4">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <label class="small text-muted font-weight-bold mb-1 d-block">SERIAL / ISBN</label>
                            <span class="font-weight-bold" style="font-size: 13px;">${book.isbn}</span>
                        </div>
                        <div class="col-6 mb-4 text-right">
                            <label class="small text-muted font-weight-bold mb-1 d-block">STATUS</label>
                            <span class="badge px-3 py-2" style="background-color: ${statusColor}; color: #fff;">
                                ${s.toUpperCase()}
                            </span>
                        </div>
                        <div class="col-12 mb-4">
                            <label class="small text-muted font-weight-bold mb-2 d-block border-bottom pb-1">${possessionLabel}</label>
                            ${book.occupied_by ? `
                                <div class="d-flex align-items-center p-3 bg-white border">
                                    <div class="mr-3 text-center" style="width: 40px;">
                                        <i class="fa fa-user-circle fa-2x text-muted"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">${book.occupied_by.name}</h6>
                                        <small class="text-muted">${book.occupied_by.class_name} | ${book.occupied_by.admission_number}</small>
                                    </div>
                                </div>
                            ` : (book.loss_info && book.loss_info.student ? `
                                <div class="d-flex align-items-center p-3 bg-white border border-danger">
                                    <div class="mr-3 text-center" style="width: 40px;">
                                        <i class="fa fa-user-times fa-2x text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">${book.loss_info.student.name}</h6>
                                        <small class="text-muted">${book.loss_info.student.class_name} | ${book.loss_info.student.admission_number}</small>
                                    </div>
                                </div>
                            ` : (book.damage_info && book.damage_info.student ? `
                                <div class="d-flex align-items-center p-3 bg-white border border-warning">
                                    <div class="mr-3 text-center" style="width: 40px;">
                                        <i class="fa fa-user fa-2x text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">${book.damage_info.student.name}</h6>
                                        <small class="text-muted">${book.damage_info.student.class_name} | ${book.damage_info.student.admission_number}</small>
                                    </div>
                                </div>
                            ` : `<div class="p-3 text-success font-weight-bold border rounded bg-white">✓ AVAILABLE IN LIBRARY</div>`))}
                            
                            ${book.loss_info && book.loss_info.description ? `
                                <div class="mt-2 p-2 border-left border-danger" style="background: #fff5f5;">
                                    <label class="small font-weight-bold mb-0 text-danger">REASON FOR LOSS:</label>
                                    <p class="mb-0 small text-muted">${book.loss_info.description}</p>
                                    ${book.loss_info.payment_method ? `<div class="badge badge-success mt-2">RESOLVED: ${book.loss_info.payment_method.toUpperCase()} ${book.loss_info.payment_amount ? book.loss_info.payment_amount : ''}</div>` : ''}
                                </div>
                            ` : ''}
                            ${book.damage_info && book.damage_info.description ? `
                                <div class="mt-2 p-2 border-left border-warning" style="background: #fffcf5;">
                                    <label class="small font-weight-bold mb-0 text-warning">REASON FOR DAMAGE:</label>
                                    <p class="mb-0 small text-muted">${book.damage_info.description}</p>
                                    ${book.damage_info.payment_method ? `<div class="badge badge-success mt-2">RESOLVED: ${book.damage_info.payment_method.toUpperCase()} ${book.damage_info.payment_amount ? book.damage_info.payment_amount : ''}</div>` : ''}
                                </div>
                            ` : ''}
                        </div>
                        ${book.borrow_expected_return_date ? `
                            <div class="col-12 mb-4">
                                <label class="small text-muted font-weight-bold mb-1 d-block">DUE FOR RETURN ON</label>
                                <h6 class="font-weight-bold ${book.is_overdue ? 'text-danger' : 'text-primary'}" style="letter-spacing: 0.5px;">
                                    ${formatDate(book.borrow_expected_return_date)}
                                </h6>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            $('#bookDetailsContent').html(content);
            
            let buttons = `<button class="btn btn-outline-secondary btn-sm px-3" onclick="editBook(${book.bookID})"><i class="fa fa-edit"></i> EDIT</button>`;
            
            if (!book.is_available && !book.is_lost && !book.is_damaged) {
                buttons += `<button class="btn btn-success btn-sm px-3 ml-2" onclick="lookupBookByIsbn('${book.isbn}', true)"><i class="fa fa-check-circle"></i> RETURN</button>`;
            }
            
            if (book.is_lost && (!book.loss_info || book.loss_info.payment_status !== 'paid')) {
                buttons += `<button class="btn btn-indigo btn-sm px-3 ml-2 text-white" style="background: #6f42c1;" onclick="showResolveModal(${book.bookID}, 'loss')"><i class="fa fa-gavel"></i> RESOLVE LOSS</button>`;
            }
            
            if (book.is_damaged && (!book.damage_info || book.damage_info.payment_status !== 'paid')) {
                buttons += `<button class="btn btn-orange btn-sm px-3 ml-2 text-white" style="background: #fd7e14;" onclick="showResolveModal(${book.bookID}, 'damage')"><i class="fa fa-wrench"></i> RESOLVE DAMAGE</button>`;
            }
 
            if (book.is_available) {
                buttons += `<button class="btn btn-warning btn-sm px-3 ml-2" onclick="borrowSpecificBook('${book.isbn}')"><i class="fa fa-share"></i> LEND</button>`;
            }
            
            $('#bookActionFooter').html(buttons);
            $('#bookDetailsModal').modal('show');
        }

        function borrowSpecificBook(isbn) {
            $('#bookDetailsModal').modal('hide');
            showBorrowBookModal(isbn);
        }

        function loadBorrows() {
            const status = $('.filterBorrowStatusBtn.active').data('status');
            const dueFilter = $('#filterBorrowDue').val();
            $('#borrowsTableBody').html('<tr><td colspan="9" class="text-center py-4">LOADING CIRCULATION DATA...</td></tr>');
            $.get('{{ route("get_book_borrows") }}', { status, dueFilter }, function(response) {
                if (response.success) {
                    let html = '';
                    response.borrows.forEach((b, i) => {
                        let borrower = b.student ? b.student.first_name + ' ' + b.student.last_name : (b.teacher ? b.teacher.first_name + ' ' + b.teacher.last_name : 'Staff');
                        let ref = b.student ? b.student.admission_number : (b.teacher ? b.teacher.employee_number : '-');
                        html += `<tr>
                                    <td>${i+1}</td>
                                    <td class="font-weight-bold">${borrower}</td>
                                    <td>${ref}</td>
                                    <td>${b.book.book_title}</td>
                                    <td>${formatDate(b.borrow_date)}</td>
                                    <td class="text-danger">${formatDate(b.expected_return_date)}</td>
                                    <td class="text-success">${formatDate(b.return_date)}</td>
                                    <td><span class="badge ${b.status === 'borrowed' ? 'badge-warning' : 'badge-success'}">${b.status.toUpperCase()}</span></td>
                                    <td>${b.status === 'borrowed' ? `<button class="btn btn-xs btn-success py-1 px-2" onclick="openReturnModal(${b.borrowID}, '${b.expected_return_date}')">RETURN</button>` : '✓'}</td>
                                </tr>`;
                    });
                    $('#borrowsTableBody').html(html);
                }
            });
        }

        function formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function showBorrowBookModal(isbn = '') {
            $('#borrowForm')[0].reset();
            $('#borrowBookInfo').hide();
            $('#studentResults').hide();
            if (isbn) { 
                $('#borrowISBN').val(isbn); 
                lookupBookByIsbn(isbn); 
            }
            $('#borrowModal').modal('show');
        }

        function lookupBookByIsbn(isbn, autoReturn = false, prefix = 'borrow') {
            if (!isbn) return;
            $(`#${prefix}IsbnSpiner`).removeClass('d-none');
            $.get('{{ route("get_book_by_isbn") }}', { isbn }, function(res) {
                $(`#${prefix}IsbnSpiner`).addClass('d-none');
                if (res.success) {
                    const book = res.book;
                    $(`#${prefix}BookTitle`).text(book.book_title);
                    
                    const className = book.class ? book.class.class_name : (book.class_name || 'N/A');
                    const subjectName = book.subject ? book.subject.subject_name : (book.subject_name || 'N/A');
                    
                    $(`#${prefix}BookClass`).text(className);
                    $(`#${prefix}BookSubject`).text(subjectName);
                    
                    if (prefix === 'borrow') {
                        const isAva = res.is_available;
                        $('#isBookAvailableForBorrow').val(isAva ? '1' : '0');
                        
                        const badge = $('#borrowBookStatusBadge');
                        const card = $('#borrowBookDetailCard');
                        const occDiv = $('#occupiedByDiv');
                        
                        badge.removeClass('badge-success badge-danger badge-warning');
                        card.css('border-left', '4px solid transparent');
                        occDiv.addClass('d-none');

                        if (isAva) {
                            badge.addClass('badge-success').text('AVAILABLE (FREE)');
                            card.css('border-left', '4px solid #28a745');
                        } else {
                            badge.addClass('badge-danger').text('UNAVAILABLE');
                            card.css('border-left', '4px solid #dc3545');
                            if (res.borrower) {
                                occDiv.removeClass('d-none');
                                $('#borrowOccupiedBy').text(res.borrower.name + ' (' + res.borrower.type.toUpperCase() + ')');
                                badge.text('OCCUPIED');
                            } else if (book.status === 'lost') {
                                badge.text('LOST');
                            } else if (book.status === 'damaged') {
                                badge.text('DAMAGED');
                            }
                        }
                    } else {
                        $(`#${prefix}BookStatus`).text(res.is_available ? 'AVAILABLE' : 'OCCUPIED/INACTIVE');
                    }
                    
                    $(`#${prefix}BookInfo`).slideDown();
                    
                    if (autoReturn && res.borrower) {
                        openReturnModal(res.borrower.borrowID || (book.active_borrows && book.active_borrows[0] ? book.active_borrows[0].borrowID : null), 
                                       (book.active_borrows && book.active_borrows[0] ? book.active_borrows[0].expected_return_date : null));
                    }
                } else {
                    $(`#${prefix}BookInfo`).hide();
                }
            }).fail(() => $(`#${prefix}IsbnSpiner`).addClass('d-none'));
        }

        function toggleIncidentBy(prefix) {
            const val = $(`#${prefix}By`).val();
            $(`#${prefix}SearchLabel`).text('Search ' + val.charAt(0).toUpperCase() + val.slice(1));
            $(`#${prefix}StudentSearch`).attr('placeholder', 'Searching for ' + val + '...');
            
            if (val === 'other') {
                $(`#${prefix}StudentSection`).slideUp();
                $(`#${prefix}StudentID, #${prefix}TeacherID, #${prefix}StaffID`).val('');
            } else {
                $(`#${prefix}StudentSection`).slideDown();
                $(`#${prefix}StudentID, #${prefix}TeacherID, #${prefix}StaffID`).val('');
                $(`#${prefix}StudentSearch`).val('');
            }
        }

        function searchStudents(search, prefix = '', forcedType = null) {
            const bType = forcedType || (prefix ? 'student' : $('#borrowerType').val());
            const resultsDiv = prefix ? `#${prefix}StudentResults` : '#studentResults';
            const url = bType === 'teacher' ? '{{ route("search_teachers") }}' : (bType === 'staff' ? '{{ route("search_staff") }}' : '{{ route("get_students") }}');
            
            $.get(url, { search }, function(res) {
                if (res.success) {
                    let html = '';
                    const items = bType === 'teacher' ? res.teachers : (bType === 'staff' ? res.staff : res.students);
                    if (items.length === 0) {
                        html = '<div class="p-3 text-center text-muted small">NO RESULTS FOUND</div>';
                    } else {
                        items.forEach(it => {
                            let name = it.name || `${it.first_name || ''} ${it.last_name || ''}`.trim();
                            let sub = bType === 'student' ? (it.subclass ? it.subclass.class.class_name : 'No Class') : (it.department || (bType === 'teacher' ? 'Teaching Staff' : 'Staff'));
                            html += `<div class="p-2 border-bottom student-search-item" style="cursor:pointer" onclick="selectStudent(${it.studentID || it.id}, '${name.replace(/'/g, "\\'")}', '${prefix}', '${bType}')">
                                        <strong>${name}</strong><br><small class="text-muted">${sub}</small>
                                     </div>`;
                        });
                    }
                    $(resultsDiv).html(html).show();
                }
            });
        }

        function selectStudent(id, name, prefix = '', bType = '') {
            if (prefix) {
                $(`#${prefix}StudentID, #${prefix}TeacherID, #${prefix}StaffID`).val('');
                if (bType === 'student') $(`#${prefix}StudentID`).val(id);
                else if (bType === 'teacher') $(`#${prefix}TeacherID`).val(id);
                else $(`#${prefix}StaffID`).val(id);
                
                $(`#${prefix}StudentSearch`).val(name);
                $(`#${prefix}StudentResults`).hide();
            } else {
                const type = $('#borrowerType').val();
                $('#borrowStudentID, #borrowTeacherID, #borrowStaffID').val('');
                if (type === 'student') $('#borrowStudentID').val(id);
                else if (type === 'teacher') $('#borrowTeacherID').val(id);
                else $('#borrowStaffID').val(id);
                $('#searchStudent').val(name);
                $('#studentResults').hide();
            }
        }

        function toggleBorrowerSearch() {
            const type = $('#borrowerType').val();
            const label = type.charAt(0).toUpperCase() + type.slice(1);
            $('#borrowerSearchLabel').text('Search ' + label);
            $('#searchStudent').attr('placeholder', 'Enter ' + type + ' name or reference...').val('');
            $('#borrowStudentID, #borrowTeacherID, #borrowStaffID').val('');
            $('#studentResults').hide();
        }

        function openReturnModal(id, date) {
            $('#returnBorrowID').val(id);
            $('#returnExpectedDate').text(formatDate(date));
            $('#returnModal').modal('show');
        }
        
        function addIsbnRow() {
            $('#isbnList').append('<div class="row align-items-end isbn-row mb-2"><div class="col-md-9"><input type="text" class="form-control isbn-input" placeholder="ENTER SERIAL/ISBN"></div><div class="col-md-3"><button type="button" class="btn btn-danger btn-sm remove-isbn-btn"><i class="fa fa-trash"></i></button></div></div>');
        }

        function submitReturn() {
            const borrowID = $('#returnBorrowID').val();
            const returnDate = $('#actualReturnDate').val();
            if (!borrowID || !returnDate) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Missing required return details' });
                return;
            }
            $.post(`{{ url('return_book') }}/${borrowID}`, { _token: '{{ csrf_token() }}', return_date: returnDate }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Returned', text: 'Book returned successfully', timer: 2000, showConfirmButton: false });
                    $('#returnModal').modal('hide');
                    loadBorrows();
                    loadBooks();
                }
            });
        }

        function saveBorrow() {
            const isAvailable = $('#isBookAvailableForBorrow').val();
            if (isAvailable === '0') {
                Swal.fire({ icon: 'error', title: 'Action Blocked', text: 'This book is not available for borrowing. It must be FREE first.' });
                return;
            }

            const isbn = $('#borrowISBN').val();
            const type = $('#borrowerType').val();
            const sid = $('#borrowStudentID').val();
            const tid = $('#borrowTeacherID').val();
            const aid = $('#borrowStaffID').val();
            const date = $('#expectedReturnDate').val();

            if (!isbn || !date || (!sid && !tid && !aid)) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Please fill all lending details' });
                return;
            }

            $.post('{{ route("borrow_book") }}', { 
                _token: '{{ csrf_token() }}', 
                isbn, borrower_type: type, 
                studentID: sid, teacherID: tid, staffID: aid, 
                expected_return_date: date 
            }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Borrowed', text: 'Book lent successfully', timer: 2000, showConfirmButton: false });
                    $('#borrowModal').modal('hide');
                    loadBorrows();
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Lending failed' });
                }
            });
        }
        function getIsbnRowHtml() {
            return '<div class="row align-items-end isbn-row mb-2"><div class="col-md-8"><label class="form-label">Unique ID <span class="text-danger">*</span></label><input type="text" class="form-control isbn-input" placeholder="Enter Unique ID"><small class="text-danger d-none isbn-error">Unique ID is already taken.</small></div><div class="col-md-4"><button type="button" class="btn btn-danger remove-isbn-btn d-none"><i class="fa fa-trash"></i> Remove</button></div></div>';
        }

        function showAddBookModal() {
            $('#bookForm')[0].reset();
            $('#bookID').val('');
            $('#isbnList').html(getIsbnRowHtml());
            $('#bookEntriesSection').removeClass('d-none');
            $('#bookModalTitle').html('<i class="fa fa-book"></i> Add New Book');
            $('#saveBooksBtn').removeClass('d-none');
            $('#saveBookBtn').addClass('d-none');
            $('#bookStatusDiv').hide();
            $('#bookModal').modal('show');
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

        function saveDamagedBook() {
            const isbn = $('#damagedIsbn').val();
            const damaged_by = $('#damagedBy').val();
            const studentID = $('#damagedStudentID').val();
            const teacherID = $('#damagedTeacherID').val();
            const staffID = $('#damagedStaffID').val();
            const notes = $('#damagedReason').val();
            
            if(!isbn) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'ISBN is required' });
                return;
            }
            if(damaged_by !== 'other' && !studentID && !teacherID && !staffID) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Selection is required' });
                return;
            }
            
            $.post('{{ route("store_book_damage") }}', {
                _token: '{{ csrf_token() }}',
                isbn: isbn,
                damaged_by: damaged_by,
                studentID: studentID,
                teacherID: teacherID,
                staffID: staffID,
                notes: notes
            }, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Record saved successfully', timer: 2000, showConfirmButton: false });
                    $('#damagedModal').modal('hide');
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save record' });
                }
            });
        }

        function saveLostBook() {
            const isbn = $('#lostIsbn').val();
            const lost_by = $('#lostBy').val();
            const studentID = $('#lostStudentID').val();
            const teacherID = $('#lostTeacherID').val();
            const staffID = $('#lostStaffID').val();
            const notes = $('#lostReason').val();
            
            if(!isbn) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'ISBN is required' });
                return;
            }
            if(lost_by !== 'other' && !studentID && !teacherID && !staffID) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Selection is required' });
                return;
            }
            
            $.post('{{ route("store_book_loss") }}', {
                _token: '{{ csrf_token() }}',
                isbn: isbn,
                lost_by: lost_by,
                studentID: studentID,
                teacherID: teacherID,
                staffID: staffID,
                notes: notes
            }, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Record saved successfully', timer: 2000, showConfirmButton: false });
                    $('#lostModal').modal('hide');
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save record' });
                }
            });
        }

        function saveBooks() {
            const bookType = $('#bookType').val();
            const classID = $('#bookClassID').val();
            const subjectID = $('#bookSubjectID').val();
            const title = $('#bookTitle').val();
            const author = $('#bookAuthor').val();
            const publisher = $('#bookPublisher').val();
            const year = $('#bookPublicationYear').val();
            const desc = $('#bookDescription').val();
            
            let isbns = [];
            $('.isbn-input').each(function() {
                if($(this).val()) isbns.push($(this).val());
            });

            if (isbns.length === 0 || !title) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Title and at least one ISBN are required' });
                return;
            }

            $.post('{{ route("store_book") }}', {
                _token: '{{ csrf_token() }}',
                book_type: bookType,
                classID: classID,
                subjectID: subjectID,
                book_title: title,
                author: author,
                publisher: publisher,
                publication_year: year,
                description: desc,
                isbns: isbns
            }, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Books saved successfully', timer: 2000, showConfirmButton: false });
                    $('#bookModal').modal('hide');
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save books' });
                }
            });
        }

        function saveBook() {
            const bookID = $('#bookID').val();
            const data = {
                _token: '{{ csrf_token() }}',
                book_type: $('#bookType').val(),
                classID: $('#bookClassID').val(),
                subjectID: $('#bookSubjectID').val(),
                book_title: $('#bookTitle').val(),
                author: $('#bookAuthor').val(),
                publisher: $('#bookPublisher').val(),
                publication_year: $('#bookPublicationYear').val(),
                description: $('#bookDescription').val(),
                status: $('#bookStatus').val(),
                isbn: $('.isbn-input').first().val()
            };

            if (!data.book_title || !data.isbn) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Title and ISBN are required' });
                return;
            }

            let url = '{{ url("update_book") }}/' + bookID;
            $.post(url, data, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Updated', text: 'Book updated successfully', timer: 2000, showConfirmButton: false });
                    $('#bookModal').modal('hide');
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Update failed' });
                }
            });
        }

        function showResolveModal(bookID, type) {
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) return;

            $('#resolveBookID').val(book.bookID);
            $('#resolveIncidentType').val(type);
            $('#resolveIncidentID').val(type === 'loss' ? book.loss_info.lossID : book.damage_info.damageID);
            $('#resolutionMethod').val('returned');
            $('#resolveNotes').val('');
            $('#replacementIsbn').val('');
            $('#paymentAmount').val('');
            toggleResolutionFields();
            $('#bookDetailsModal').modal('hide');
            $('#resolveIncidentModal').modal('show');
        }

        function editBook(bookID) {
            const book = allBooks.find(b => b.bookID == bookID);
            if (!book) return;

            $('#bookForm')[0].reset();
            $('#bookID').val(book.bookID);
            $('#bookType').val(book.book_type);
            $('#bookClassID').val(book.classID);
            $('#bookTitle').val(book.book_title);
            $('#bookAuthor').val(book.author);
            $('#bookPublisher').val(book.publisher);
            $('#bookPublicationYear').val(book.publication_year);
            $('#bookDescription').val(book.description);
            $('#bookStatus').val(book.status);

            // Handle Subjects (trigger change or fetch if needed)
            if (book.classID) {
                getSubjects(book.classID, book.subjectID);
            }

            // Single ISBN for edit mode
            $('#isbnList').html(`
                <div class="row align-items-end isbn-row mb-2">
                    <div class="col-md-12">
                        <label class="form-label">ISBN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control isbn-input" value="${book.isbn}" required>
                    </div>
                </div>
            `);
            $('#bookEntriesSection').addClass('d-none'); // Hide add multiple during single edit

            $('#bookModalTitle').text('Edit Book');
            $('#saveBooksBtn').addClass('d-none');
            $('#saveBookBtn').removeClass('d-none');
            $('#bookStatusDiv').show();
            
            $('#bookDetailsModal').modal('hide');
            $('#bookModal').modal('show');
        }

        function getSubjects(classID, selectedID = null) {
            $.get('{{ route("get_subjects_by_class") }}', { classID }, function(res) {
                if(res.success) {
                    let html = '<option value="">Select Subject</option>';
                    res.subjects.forEach(s => {
                        html += `<option value="${s.subjectID}" ${selectedID == s.subjectID ? 'selected' : ''}>${s.subject_name}</option>`;
                    });
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
            const id = $('#resolveIncidentID').val();
            const bookID = $('#resolveBookID').val();
            const method = $('#resolutionMethod').val();
            const replacementIsbn = $('#replacementIsbn').val();
            const amount = $('#paymentAmount').val();
            const notes = $('#resolveNotes').val();

            if (method === 'replacenew' && !replacementIsbn) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Please provide the new ISBN' });
                return;
            }
            if (method === 'cash' && !amount) {
                Swal.fire({ icon: 'warning', title: 'Error', text: 'Please enter the payment amount' });
                return;
            }

            $.post('{{ route("resolve_book_incident") }}', {
                _token: '{{ csrf_token() }}',
                incident_type: type,
                incidentID: id,
                bookID: bookID,
                method: method,
                replacement_isbn: replacementIsbn,
                amount: amount,
                notes: notes
            }, function(res) {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Resolved', text: 'Incident has been resolved', timer: 2000, showConfirmButton: false });
                    $('#resolveIncidentModal').modal('hide');
                    loadBooks();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Resolution failed' });
                }
            });
        }

        // Global exports
        window.openBookDetails = openBookDetails;
        window.openReturnModal = openReturnModal;
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
        function toggleBookTypeFields() {
            const type = $('#bookType').val();
            if (type === 'Other') {
                $('#classSelectionDiv').slideUp();
                $('#subjectSelectionDiv').slideUp();
                $('#bookClassID').val('').prop('required', false);
                $('#bookSubjectID').val('').prop('required', false);
            } else {
                $('#classSelectionDiv').slideDown();
                $('#subjectSelectionDiv').slideDown();
                $('#bookClassID').prop('required', true);
                $('#bookSubjectID').prop('required', true);
            }
        }
        
        window.toggleBorrowerSearch = toggleBorrowerSearch;
        window.toggleBookTypeFields = toggleBookTypeFields;
        window.getSubjects = getSubjects;

        initLibrary();
    })();
</script>
