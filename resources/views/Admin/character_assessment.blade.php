@include('includes.Admin_nav')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content-wrapper" style="font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif; background-color: #FFFFFF;">
    <div class="content mt-3">
        <!-- Header Section -->
        <div class="animated fadeIn">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; border-left: 5px solid #940000 !important; background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1" style="color: #940000; font-weight: 800; letter-spacing: 0.5px;">CHARACTER ASSESSMENT</h4>
                                    <p class="text-muted mb-0 small" style="font-weight: 600;">System Configuration and Student Progress Tracking</p>
                                </div>
                                <button class="btn shadow-sm text-white" id="toggleSettingsBtn" style="border-radius: 0; background: #940000; border: none; font-weight: 700; padding: 10px 20px;">
                                    <i class="fa fa-cog"></i> SETTINGS
                                </button>
                            </div>

                            <!-- Expanded Settings Section -->
                            <div id="expandedSettings" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(148, 0, 0, 0.1); position: relative;">
                                <button type="button" class="close" onclick="$('#expandedSettings').slideUp(300);" style="position: absolute; right: 10px; top: 10px; color: #000000; opacity: 1;">&times;</button>
                                <div class="row text-center">
                                    <div class="col-md-2 border-right">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openAddCategoryModal()">
                                            <i class="fa fa-plus-circle fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">DEFINE CATEGORIES</h6>
                                        </a>
                                    </div>
                                    <div class="col-md-2 border-right">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openViewCategoriesModal()">
                                            <i class="fa fa-table fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">VIEW CATEGORIES</h6>
                                        </a>
                                    </div>
                                    <div class="col-md-2 border-right">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openAddChoicesModal()">
                                            <i class="fa fa-pencil-square fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">DEFINE CHOICES</h6>
                                        </a>
                                    </div>
                                    <div class="col-md-2 border-right">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openViewChoicesModal()">
                                            <i class="fa fa-list-ul fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">VIEW CHOICES</h6>
                                        </a>
                                    </div>
                                    <div class="col-md-2 border-right">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openAddTimetableModal()">
                                            <i class="fa fa-calendar-plus-o fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">DEFINE TIMETABLE</h6>
                                        </a>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="#" class="text-decoration-none d-block py-3 setting-item" onclick="openViewTimetablesModal()">
                                            <i class="fa fa-calendar-check-o fa-2x mb-2" style="color: #940000;"></i>
                                            <h6 class="font-weight-bold mb-0 small" style="color: #2f2f2f;">VIEW TIMETABLE</h6>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Bar (Using Accent Soft Red) -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 0; background-color: #FFF5F5;">
                        <span class="text-uppercase small font-weight-bold mb-2" style="color: #940000; letter-spacing: 1px;">Active Categories</span>
                        <h2 class="mb-0 font-weight-bold" style="color: #2f2f2f;">{{ $categories->count() }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 0; background-color: #FFF5F5;">
                        <span class="text-uppercase small font-weight-bold mb-2" style="color: #940000; letter-spacing: 1px;">Rating Choices</span>
                        <h2 class="mb-0 font-weight-bold" style="color: #2f2f2f;">{{ $globalChoices->count() }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center" style="border-radius: 0; background-color: #FFF5F5;">
                        <span class="text-uppercase small font-weight-bold mb-2" style="color: #940000; letter-spacing: 1px;">Status</span>
                        <h2 class="mb-0 font-weight-bold" style="color: #28a745;">Ready</h2>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 0; background-color: #FFFFFF;">
                        <!-- Filter Bar -->
                        <div class="card-header border-0 p-4" style="background: #fafafa;">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Academic Year</label>
                                    <select id="filterYear" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;">
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->academic_yearID }}" {{ $year->status == 'Active' ? 'selected' : '' }}>{{ $year->year_name ?: $year->year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Term</label>
                                    <select id="filterTerm" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;">
                                        <option value="">-- Choose Term --</option>
                                        <option value="first_term">First Term</option>
                                        <option value="second_term">Second Term</option>
                                        <option value="all_terms">All Terms</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Assessment Period</label>
                                    <select id="filterTimetable" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;" disabled>
                                        <option value="">-- Select Period --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted mb-2 text-uppercase" style="letter-spacing: 1px;">Target Class</label>
                                    <select id="filterClass" class="form-control" style="border-radius:0; border:1px solid #ddd; font-weight:600;" disabled>
                                        <option value="">-- Select Class --</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-block text-white" id="searchReportBtn" style="background:#2f2f2f; border-radius:0; height:calc(1.5em + .75rem + 2px);">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0" id="reportResultContainer">
                            <!-- Initial Welcome Placeholder -->
                            <div class="py-5 text-center" id="dashboardWelcome">
                                <div class="p-4 mb-4 rounded-circle mx-auto" style="background-color: #FFF5F5; width: fit-content;">
                                    <i class="fa fa-shield fa-4x text-muted" style="color: #940000 !important;"></i>
                                </div>
                                <h3 class="font-weight-bold mb-3" style="color: #2f2f2f;">Assessment Reporting</h3>
                                <p class="text-muted mb-4 px-3" style="max-width: 600px; margin: 0 auto;">
                                    Select the filters above to view and analyze student character assessment data. 
                                    Generate visual reports and download individual progress summaries.
                                </p>
                            </div>

                            <!-- PDF PROGRESS MODAL -->
                            <div class="modal fade" id="downloadProgressModal" data-backdrop="static" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg" style="border-radius:0;">
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-4">
                                                <i class="fa fa-file-pdf-o fa-3x text-danger animate-pulse"></i>
                                            </div>
                                            <h5 class="mb-4" style="color: #940000; font-weight: bold;">Generating Assessment Report</h5>
                                            <div class="progress mb-2" style="height: 12px; border-radius: 0;">
                                                <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" 
                                                    role="progressbar" style="width: 0%;"></div>
                                            </div>
                                            <div id="pdfProgressPercent" class="font-weight-bold text-muted" style="font-size: 14px;">0%</div>
                                            <p class="mt-3 text-muted small">Please wait while we compile the character assessment data and generate your document...</p>
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

                            <div id="reportLoading" style="display: none;" class="py-5 text-center">
                                <i class="fa fa-spinner fa-spin fa-3x mb-3" style="color: #940000;"></i>
                                <h5 class="font-weight-bold text-muted">Compiling Report Data...</h5>
                            </div>

                            <!-- Report Content (Initially Hidden) -->
                            <div id="dashboardReportSection" style="display: none;">
                                <!-- Summary Cards -->
                                <div class="p-4" style="background: #FFF5F5; border-bottom: 1px solid rgba(148,0,0,0.05);">
                                    <div class="row d-flex align-items-stretch">
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center bg-white p-3 shadow-sm h-100" style="border-left: 4px solid #940000; border-radius:0; min-height: 85px;">
                                                <div class="mr-3"><i class="fa fa-users fa-2x text-muted opacity-50"></i></div>
                                                <div>
                                                    <span class="text-muted small font-weight-bold text-uppercase d-block">Total Enrollment</span>
                                                    <h4 class="mb-0 font-weight-bold" id="statTotalStudents">0</h4>
                                                    <small class="text-muted"><span id="statMales">0</span> M | <span id="statFemales">0</span> F</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center bg-white p-3 shadow-sm h-100" style="border-left: 4px solid #28a745; border-radius:0; min-height: 85px;">
                                                <div class="mr-3"><i class="fa fa-check-circle fa-2x text-success opacity-50"></i></div>
                                                <div>
                                                    <span class="text-muted small font-weight-bold text-uppercase d-block">Assessed</span>
                                                    <h4 class="mb-0 font-weight-bold text-success" id="statAssessed">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center bg-white p-3 shadow-sm cursor-pointer h-100" onclick="togglePendingList()" style="border-left: 4px solid #dc3545; border-radius:0; min-height: 85px;">
                                                <div class="mr-3"><i class="fa fa-exclamation-triangle fa-2x text-danger opacity-50"></i></div>
                                                <div>
                                                    <span class="text-muted small font-weight-bold text-uppercase d-block">Pending <i class="fa fa-chevron-down ml-1"></i></span>
                                                    <h4 class="mb-0 font-weight-bold text-danger" id="statPending">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-end h-100">
                                                <button class="btn btn-outline-danger font-weight-bold py-3 px-4 w-100" style="border-radius:0; border: 2px solid #940000; min-height: 85px;" onclick="printFullClassReport()">
                                                    <i class="fa fa-file-pdf-o"></i> EXPORT CLASS REPORT
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Expandable Pending Students List -->
                                    <div id="pendingStudentsWrapper" style="display:none;" class="mt-3">
                                        <div class="card border-0 shadow-sm" style="border-radius:0;">
                                            <div class="card-header bg-danger text-white py-2">
                                                <h6 class="mb-0 font-weight-bold small text-uppercase">Students with Missing Assessments</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive" style="max-height: 250px;">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="bg-light">
                                                            <tr class="small text-uppercase font-weight-bold">
                                                                <th class="pl-3">Admission</th>
                                                                <th>Name</th>
                                                                <th>Gender</th>
                                                                <th class="text-right pr-3">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="pendingStudentsBody">
                                                            <!-- Managed via JS -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>

                                <!-- Analytics Section -->
                                <div class="p-4">
                                    <div class="row mb-5">
                                        <div class="col-md-7">
                                            <div class="card border-0 shadow-sm" style="border-radius:0;">
                                                <div class="card-header bg-white py-3">
                                                    <h6 class="mb-0 font-weight-bold text-uppercase small" style="letter-spacing:1px; color:#2f2f2f;">
                                                        <i class="fa fa-bar-chart mr-2 text-danger"></i> Performance Distribution by Category
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="categoryChart" style="height: 300px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="card border-0 shadow-sm" style="border-radius:0; height: 100%;">
                                                <div class="card-header bg-white py-3">
                                                    <h6 class="mb-0 font-weight-bold text-uppercase small" style="letter-spacing:1px; color:#2f2f2f;">
                                                        <i class="fa fa-table mr-2 text-danger"></i> Statistical Analysis
                                                    </h6>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover small mb-0" id="statsTable">
                                                            <thead class="bg-light">
                                                                <tr id="statsTableHeader">
                                                                    <th class="border-0">Category</th>
                                                                    <!-- Choices will be injected here -->
                                                                </tr>
                                                            </thead>
                                                            <tbody id="statsTableBody">
                                                                <!-- Rows will be injected here -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student List Table -->
                                    <div class="card border-0 shadow-none">
                                        <div class="card-header bg-white py-3 px-0 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 font-weight-bold text-uppercase small mr-4" style="letter-spacing:1px; color:#2f2f2f;">
                                                    <i class="fa fa-list mr-2 text-danger"></i> Detailed Student Records
                                                </h6>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-danger font-weight-bold" style="border-radius:0;" onclick="printFullClassReport()">
                                                        <i class="fa fa-file-pdf-o"></i> PDF
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success font-weight-bold" style="border-radius:0;" onclick="exportToExcel()">
                                                        <i class="fa fa-file-excel-o"></i> EXCEL
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="has-search" style="width: 250px;">
                                                <span class="fa fa-search form-control-feedback"></span>
                                                <input type="text" id="studentSearch" class="form-control form-control-sm" placeholder="Search students..." style="border-radius:0;">
                                            </div>
                                        </div>
                                        <div class="table-responsive mt-3">
                                            <table class="table table-hover border-bottom mb-0" id="mainStudentTable">
                                                <thead style="background-color: #f8f9fa;">
                                                    <tr class="small font-weight-bold text-uppercase text-muted">
                                                        <th class="py-3 pl-4 border-0">Student Info</th>
                                                        <th class="py-3 border-0">Class</th>
                                                        <th class="py-3 border-0">Status</th>
                                                        <th class="py-3 border-0">Assessment Summary</th>
                                                        <th class="py-3 pr-4 border-0 text-right">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentReportBody">
                                                    <!-- Students will be injected here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="p-4 text-center border-top">
                                            <p class="text-muted small mb-0">Showing <span id="displayedCount">0</span> of <span id="totalClassCount">0</span> students</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Entry Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" id="categoryModalTitle" style="color: #940000; letter-spacing: 0.5px;">DEFINE ASSESSMENT CATEGORIES</h5>
                <p class="text-muted small mb-0 font-weight-bold">Configure multiple assessment sectors in one session.</p>
            </div>
            <form id="categoryForm">
                <input type="hidden" id="categoryID">
                <div class="modal-body p-4 custom-scrollbar" style="max-height: 450px; overflow-y: auto;">
                    <div id="categoriesContainer">
                        <!-- Rows populated via JS -->
                    </div>
                    
                    <button type="button" class="btn btn-link p-0 font-weight-bold small text-decoration-none mt-3" style="color: #940000;" onclick="addCategoryRow()">
                        <i class="fa fa-plus-circle"></i> ADD ANOTHER CATEGORY
                    </button>

                    <div id="editCategoryWarning" class="alert alert-warning small mt-3 d-none border-0" style="border-radius: 0; background-color: #FFF5F5; color: #940000;">
                        <i class="fa fa-warning mr-2"></i> Update mode: Batch entry is disabled.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 0; background: #f0f0f0; color: #2f2f2f;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white shadow-sm" style="border-radius: 0; background: #940000; border: none;">SAVE CATEGORIES</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Categories Modal -->
<div class="modal fade" id="viewCategoriesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">ASSESSMENT CATEGORIES</h5>
                <p class="text-muted small mb-0 font-weight-bold">View and manage existing behavior sectors.</p>
            </div>
            <div class="modal-body p-0 custom-scrollbar" style="max-height: 500px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #fcfcfc;">
                            <tr>
                                <th class="border-0 small font-weight-bold px-4" style="color: #940000;">#</th>
                                <th class="border-0 small font-weight-bold" style="color: #940000;">SECTOR NAME</th>
                                <th class="border-0 small font-weight-bold" style="color: #940000;">TYPE</th>
                                <th class="border-0 small font-weight-bold text-right px-4" style="color: #940000;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Choice Entry Modal -->
<div class="modal fade" id="choicesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">DEFINE GLOBAL CHOICES</h5>
                <p class="text-muted small mb-0 font-weight-bold">Configure ratings that apply to all behavior sectors.</p>
            </div>
            <form id="choiceForm">
                <div class="modal-body p-4 custom-scrollbar" style="max-height: 450px; overflow-y: auto;">
                    <div id="choicesContainer">
                        <!-- Rows populated via JS -->
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-link p-0 font-weight-bold small text-decoration-none" style="color: #940000;" onclick="addChoiceRow()">
                            <i class="fa fa-plus-circle"></i> ADD ANOTHER CHOICE
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold" style="border-radius: 0; font-size: 11px; border: 2px solid #940000; color: #940000;" onclick="loadStandardChoices()">
                            <i class="fa fa-magic"></i> LOAD STANDARD TEMPLATE
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 0; background: #f0f0f0; color: #2f2f2f;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white shadow-sm" style="border-radius: 0; background: #940000; border: none;">SAVE RATINGS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Choices Modal -->
<div class="modal fade" id="viewChoicesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">GLOBAL RATING CHOICES</h5>
                <p class="text-muted small mb-0 font-weight-bold">Currently system ratings for choice categories.</p>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0 text-center">
                    <thead style="background-color: #fcfcfc;">
                        <tr>
                            <th class="border-0 small font-weight-bold" style="color: #940000;">RANK/WEIGHT</th>
                            <th class="border-0 small font-weight-bold" style="color: #940000;">RATING LABEL</th>
                        </tr>
                    </thead>
                    <tbody id="choicesTableBody">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Timetable Entry Modal -->
<div class="modal fade" id="timetableModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">CREATE ASSESSMENT TIMETABLE</h5>
                <p class="text-muted small mb-0 font-weight-bold">Schedule behavior assessment periods and assign responsible teachers.</p>
            </div>
            <form id="timetableForm">
                <input type="hidden" id="ttID" name="timetableID">
                <div class="modal-body p-4 custom-scrollbar" style="max-height: 75vh; overflow-y: auto;">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase" style="color:#666;">Academic Year</label>
                            <select class="form-control" name="academic_yearID" id="ttYear" required style="border-radius: 0;">
                                @foreach($academicYears as $ay)
                                    <option value="{{ $ay->academic_yearID }}" {{ $ay->status == 'Active' ? 'selected' : '' }}>{{ $ay->year_name ?: $ay->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase" style="color:#666;">Select Term</label>
                            <select class="form-control" name="term" id="ttTerm" required style="border-radius: 0;">
                                <option value="">Select Term</option>
                                <option value="first_term">First Term</option>
                                <option value="second_term">Second Term</option>
                                <option value="all_terms">All Terms</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase" style="color:#666;">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="ttStart" required style="border-radius: 0;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase" style="color:#666;">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="ttEnd" required style="border-radius: 0;">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="p-3 bg-light" style="border-left: 4px solid #940000;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold mb-0" style="color: #940000;">1. SELECT TARGET CLASSES</h6>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="selectAllClasses">
                                        <label class="custom-control-label small font-weight-bold" for="selectAllClasses">SELECT ALL</label>
                                    </div>
                                </div>
                                <div class="row mx-0" id="classesCheckboxes">
                                    @foreach($classes as $cls)
                                    <div class="col-md-3 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input class-selector" id="cls_{{ $cls->classID }}" value="{{ $cls->classID }}">
                                            <label class="custom-control-label small font-weight-bold" for="cls_{{ $cls->classID }}">{{ $cls->class_name }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="p-3" style="border: 2px dashed #eee; background: #fafafa;">
                                <h6 class="font-weight-bold mb-3" style="color: #940000;">2. ASSIGN TEACHER RESPONSIBILITIES</h6>
                                <div id="teacherAssignmentsContainer">
                                    <!-- Dynamic Rows -->
                                </div>
                                <button type="button" class="btn btn-link p-0 font-weight-bold small text-decoration-none mt-2" style="color: #940000;" onclick="addAssignmentRow()">
                                    <i class="fa fa-plus-circle"></i> ADD CLASS ASSIGNMENT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 0; background: #f0f0f0; color: #2f2f2f;">CANCEL</button>
                    <button type="submit" class="btn px-4 font-weight-bold text-white shadow-sm" style="border-radius: 0; background: #940000; border: none;">SAVE TIMETABLE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Timetables Modal -->
<div class="modal fade" id="viewTimetablesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 0;">
            <div class="modal-header border-0 p-4 d-block" style="background: linear-gradient(135deg, #FFF5F5 0%, #FFFFFF 100%);">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #940000; opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title font-weight-bold mb-1" style="color: #940000; letter-spacing: 0.5px;">CHARACTER ASSESSMENT SCHEDULES</h5>
                <p class="text-muted small mb-0 font-weight-bold">Review and manage upcoming or past assessment periods.</p>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3 border-bottom d-flex gap-3">
                    <select class="form-control form-control-sm" id="filterTtYear" onchange="loadTimetables()" style="width: 150px; border-radius: 0;">
                        <option value="">All Years</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->academic_yearID }}" {{ $ay->status == 'Active' ? 'selected' : '' }}>{{ $ay->year_name ?: $ay->year }}</option>
                        @endforeach
                    </select>
                    <select class="form-control form-control-sm" id="filterTtTerm" onchange="loadTimetables()" style="width: 150px; border-radius: 0;">
                        <option value="">All Terms</option>
                        <option value="first_term">First Term</option>
                        <option value="second_term">Second Term</option>
                        <option value="all_terms">All Terms</option>
                    </select>
                    <button class="btn btn-sm btn-outline-dark font-weight-bold px-3 d-none" style="border-radius: 0;" onclick="loadTimetables()">FILTER</button>
                </div>
                <div class="custom-scrollbar" style="max-height: 500px; overflow-y: auto;">
                    <div id="timetablesListContainer">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const categoryModal = $('#categoryModal');
    const viewCategoriesModal = $('#viewCategoriesModal');
    const choicesModal = $('#choicesModal');
    const viewChoicesModal = $('#viewChoicesModal');
    const timetableModal = $('#timetableModal');
    const viewTimetablesModal = $('#viewTimetablesModal');

    $('#toggleSettingsBtn').on('click', function() {
        $('#expandedSettings').slideToggle(300);
    });

    let categoriesList = @json($categories);
    let choicesList = @json($globalChoices);
    const classesData = @json($classes);
    const subclassesData = @json($subclasses);
    const teachersData = @json($teachers);

    // --- CATEGORY ACTIONS ---
    function openAddCategoryModal() {
        $('#categoryModalTitle').text('DEFINE ASSESSMENT CATEGORIES');
        $('#categoryID').val('');
        $('#categoriesContainer').html('');
        addCategoryRow();
        $('#addMoreCategoryContainer').show();
        $('#editCategoryWarning').addClass('d-none');
        categoryModal.modal('show');
    }

    function addCategoryRow() {
        const row = `
            <div class="category-row row mb-3 align-items-center animate__animated animate__fadeIn">
                <div class="col-md-7">
                    <label class="small font-weight-bold text-uppercase pb-1" style="color:#666; font-size:10px;">Sector Name</label>
                    <input type="text" class="form-control category-name" placeholder="e.g., Cleanliness" required style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold text-uppercase pb-1" style="color:#666; font-size:10px;">Type</label>
                    <select class="form-control category-type" required style="border-radius: 0;">
                        <option value="choice">Choices</option>
                        <option value="text">Free Text</option>
                    </select>
                </div>
                <div class="col-md-1 text-center pt-3">
                    <button type="button" class="btn btn-link text-danger p-0 remove-cat-btn" onclick="$(this).closest('.category-row').remove(); toggleCatRemove();">
                        <i class="fa fa-times-circle fa-lg"></i>
                    </button>
                </div>
            </div>`;
        $('#categoriesContainer').append(row);
        toggleCatRemove();
    }

    function toggleCatRemove() {
        const rows = $('.category-row');
        $('.remove-cat-btn').toggle(rows.length > 1);
    }

    function openViewCategoriesModal() {
        let html = '';
        categoriesList.forEach((cat, index) => {
            html += `
                <tr style="border-left: 3px solid transparent;" onmouseover="this.style.borderLeft='3px solid #940000'; this.style.backgroundColor='#FFF5F5'" onmouseout="this.style.borderLeft='3px solid transparent'; this.style.backgroundColor='transparent'">
                    <td class="px-4 align-middle font-weight-bold">${index + 1}</td>
                    <td class="align-middle font-weight-bold" style="color: #2f2f2f;">${cat.name}</td>
                    <td class="align-middle">
                        <span class="badge px-3 py-2 text-white" style="border-radius:0; background: ${cat.type == 'choice' ? '#28a745' : '#007bff'}; font-size: 9px; font-weight: 800;">${cat.type.toUpperCase()}</span>
                    </td>
                    <td class="text-right px-4 align-middle">
                        <button class="btn btn-sm btn-link font-weight-bold" style="color: #2f2f2f;" onclick="openEditCategoryModal(${JSON.stringify(cat).replace(/"/g, '&quot;')})"><i class="fa fa-edit"></i> EDIT</button>
                        <button class="btn btn-sm btn-link font-weight-bold text-danger" onclick="deleteCategory(${cat.categoryID})"><i class="fa fa-trash"></i> DELETE</button>
                    </td>
                </tr>`;
        });
        if (categoriesList.length === 0) html = '<tr><td colspan="4" class="text-center py-5 text-muted font-weight-bold">No categories defined.</td></tr>';
        $('#categoriesTableBody').html(html);
        viewCategoriesModal.modal('show');
    }

    function openEditCategoryModal(cat) {
        viewCategoriesModal.modal('hide');
        $('#categoryModalTitle').text('EDIT ASSESSMENT CATEGORY');
        $('#categoryID').val(cat.categoryID);
        const row = `
            <div class="category-row row mb-2 align-items-center">
                <div class="col-md-7">
                    <label class="small font-weight-bold" style="color: #940000;">Sector Name</label>
                    <input type="text" class="form-control category-name" value="${cat.name}" required style="border-radius: 0;">
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold" style="color: #940000;">Type</label>
                    <select class="form-control category-type" required style="border-radius: 0;">
                        <option value="choice" ${cat.type === 'choice' ? 'selected' : ''}>Choices</option>
                        <option value="text" ${cat.type === 'text' ? 'selected' : ''}>Free Text</option>
                    </select>
                </div>
                <div class="col-md-1"></div>
            </div>`;
        $('#categoriesContainer').html(row);
        $('#addMoreCategoryContainer').hide();
        $('#editCategoryWarning').removeClass('d-none');
        categoryModal.modal('show');
    }

    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#categoryID').val();
        const data = id ? { _token: '{{ csrf_token() }}', name: $('.category-name').val(), type: $('.category-type').val() } : { _token: '{{ csrf_token() }}', categories: getCategoryRowsData() };
        const url = id ? `{{ url('update_assessment_category') }}/${id}` : `{{ route('store_assessment_category') }}`;
        
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            beforeSend: function() { Swal.showLoading(); },
            success: handleResponse
        });
    });

    function getCategoryRowsData() {
        const categories = [];
        $('.category-row').each(function() {
            categories.push({ name: $(this).find('.category-name').val(), type: $(this).find('.category-type').val() });
        });
        return categories;
    }

    // --- CHOICE ACTIONS ---
    function openAddChoicesModal() {
        $('#choicesContainer').html('');
        if (choicesList.length > 0) {
            choicesList.forEach(c => addChoiceRow(c.choice_name, c.weight));
        } else {
            addChoiceRow();
        }
        choicesModal.modal('show');
    }

    function addChoiceRow(name = '', weight = 1) {
        const row = `
            <div class="choice-row row mb-3 align-items-center animate__animated animate__fadeIn">
                <div class="col-md-8">
                    <label class="small font-weight-bold text-uppercase pb-1" style="color:#666; font-size:10px;">Rating Label</label>
                    <input type="text" class="form-control choice-name" placeholder="e.g., Perfect" value="${name}" required style="border-radius: 0;">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-uppercase pb-1" style="color:#666; font-size:10px;">Rank/Weight</label>
                    <input type="number" class="form-control choice-weight" value="${weight}" min="1" required style="border-radius: 0;">
                </div>
                <div class="col-md-1 text-center pt-3">
                    <button type="button" class="btn btn-link text-danger p-0 remove-choice-btn" onclick="$(this).closest('.choice-row').remove(); toggleChoiceRemove();">
                        <i class="fa fa-times-circle fa-lg"></i>
                    </button>
                </div>
            </div>`;
        $('#choicesContainer').append(row);
        toggleChoiceRemove();
    }

    function toggleChoiceRemove() {
        const rows = $('.choice-row');
        $('.remove-choice-btn').toggle(rows.length > 1);
    }

    function openViewChoicesModal() {
        let html = '';
        choicesList.forEach(c => {
            html += `<tr onmouseover="this.style.backgroundColor='#FFF5F5'" onmouseout="this.style.backgroundColor='transparent'"><td class="align-middle font-weight-bold px-4 pt-3 pb-3" style="color: #940000; font-size:1.1rem;">${c.weight}</td><td class="align-middle font-weight-bold" style="color: #2f2f2f;">${c.choice_name}</td></tr>`;
        });
        if (choicesList.length === 0) html = '<tr><td colspan="2" class="py-5 text-muted font-weight-bold">No global ratings defined.</td></tr>';
        $('#choicesTableBody').html(html);
        viewChoicesModal.modal('show');
    }

    function loadStandardChoices() {
        const template = [
            { name: 'Perfect', weight: 1 }, { name: 'Excellent', weight: 2 }, { name: 'Very Good', weight: 3 },
            { name: 'Good', weight: 4 }, { name: 'Fair', weight: 5 }, { name: 'Poor', weight: 6 }
        ];
        $('#choicesContainer').html('');
        template.forEach(c => addChoiceRow(c.name, c.weight));
    }

    $('#choiceForm').on('submit', function(e) {
        e.preventDefault();
        const choices = [];
        $('.choice-row').each(function() {
            choices.push({ name: $(this).find('.choice-name').val(), weight: $(this).find('.choice-weight').val() });
        });
        $.ajax({
            url: `{{ route('store_assessment_choice') }}`,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', choices },
            beforeSend: function() { Swal.showLoading(); },
            success: handleResponse
        });
    });

    // --- TIMETABLE ACTIONS ---
    function openAddTimetableModal() {
        $('#timetableForm')[0].reset();
        $('#ttID').val('');
        $('#teacherAssignmentsContainer').html('');
        $('#selectAllClasses').prop('checked', false);
        $('.class-selector').prop('checked', false);
        timetableModal.modal('show');
    }

    $('#selectAllClasses').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.class-selector').prop('checked', isChecked).trigger('change');
    });

    $('.class-selector').on('change', function() {
        syncClassDropdowns();
    });

    function syncClassDropdowns() {
        const selectedClasses = [];
        $('.class-selector:checked').each(function() {
            selectedClasses.push({ 
                id: $(this).val(), 
                name: $(this).closest('div').find('label').text().trim() 
            });
        });

        $('.tt-class-select').each(function() {
            const currentSelect = $(this);
            const currentVal = currentSelect.val();
            
            let options = '<option value="">Select Target Class</option>';
            selectedClasses.forEach(c => {
                options += `<option value="${c.id}" ${c.id == currentVal ? 'selected' : ''}>${c.name}</option>`;
            });
            currentSelect.html(options);
        });
        checkClassAvailability();
    }

    function checkClassAvailability() {
        const assignedClasses = [];
        $('.tt-class-select').each(function() {
            const val = $(this).val();
            if (val) assignedClasses.push(val);
        });

        $('.tt-class-select').each(function() {
            const currentSelect = $(this);
            const currentVal = currentSelect.val();

            currentSelect.find('option').each(function() {
                const opt = $(this);
                const val = opt.val();
                if (!val) return;

                const isAlreadySelected = assignedClasses.includes(val) && val != currentVal;
                if (isAlreadySelected) {
                    opt.text(opt.text().replace(' (Assigned)', '') + ' (Assigned)');
                    opt.css({ 'color': '#999', 'font-style': 'italic' });
                } else {
                    opt.text(opt.text().replace(' (Assigned)', ''));
                    opt.css({ 'color': '#000', 'font-style': 'normal' });
                }
            });

            // Add visual feedback to the row if it's "Ready" (fully assigned)
            const row = currentSelect.closest('.assignment-row');
            if (currentVal && row.find('.teacher-badge').length > 0) {
                row.css({
                    'background-color': '#f8fff9',
                    'box-shadow': '0 4px 15px rgba(40, 167, 69, 0.1)',
                    'border-color': '#28a745'
                });
                if (!row.find('.ready-badge').length) {
                    row.append('<span class="badge badge-success ready-badge" style="position: absolute; right: 40px; top: 10px; border-radius: 0; font-size: 10px;">READY <i class="fa fa-check"></i></span>');
                }
            } else {
                row.css({
                    'background-color': '#fff',
                    'box-shadow': 'none',
                    'border-color': '#ced4da'
                });
                row.find('.ready-badge').remove();
            }
        });
    }

    function addAssignmentRow() {
        const rowId = 'row_' + Date.now();
        const selectedClasses = [];
        $('.class-selector:checked').each(function() {
            const id = $(this).val();
            const name = $(this).closest('div').find('label').text().trim();
            selectedClasses.push({ id, name });
        });

        if (selectedClasses.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Classes Selected',
                text: 'Please select at least one target class first from step 1.',
                confirmButtonColor: '#940000'
            });
            return;
        }

        let classOptions = '<option value="">Select Target Class</option>';
        selectedClasses.forEach(c => {
            classOptions += `<option value="${c.id}">${c.name}</option>`;
        });

        const row = `
            <div class="assignment-row mb-3 p-3 bg-white shadow-sm border animate__animated animate__fadeInUp" id="${rowId}" style="position: relative; border-left: 5px solid #940000 !important;">
                <button type="button" class="close text-danger" onclick="$('#${rowId}').remove()" style="position: absolute; right: 10px; top: 10px; font-size: 1.2rem; z-index: 10;">&times;</button>
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">1. Target Class</label>
                        <select class="form-control form-control-sm tt-class-select" required onchange="filterTtSubclasses('${rowId}', this.value); checkClassAvailability();" style="border-radius: 0; font-weight: bold;">
                            ${classOptions}
                        </select>
                    </div>
                    <div class="col-md-3 border-left">
                        <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">2. Subclass</label>
                        <select class="form-control form-control-sm tt-subclass-select" style="border-radius: 0;">
                            <option value="all">Apply to All Subclasses</option>
                        </select>
                    </div>
                    <div class="col-md-6 border-left">
                        <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">3. Assign Teachers</label>
                        <div class="teacher-list d-flex flex-wrap gap-1 mb-2 p-2 border bg-light" id="teachers_${rowId}" style="min-height: 40px; border-style: dashed !important; background-color: #fcfcfc;">
                            <!-- Selected teachers badges go here -->
                        </div>
                        <div class="input-group input-group-sm">
                            <select class="form-control tt-teacher-select" style="border-radius: 0;">
                                <option value="">Choose Teacher...</option>
                                ${getTeacherOptions()}
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-outline-danger font-weight-bold" type="button" onclick="addTeacherToAsgn('${rowId}')" style="border-radius: 0; border: 1px solid #ced4da; border-left: none; background: #940000; color: white;">+ ADD</button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 border-top pt-3">
                        <div class="col-md-12">
                            <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">4. Select Assessment Sectors (Order of Assessment)</label>
                            <div class="d-flex flex-wrap gap-2 category-selection-grid" id="categories_${rowId}">
                                ${getCategoryCheckboxesHtml(rowId)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        $('#teacherAssignmentsContainer').append(row);
    }

    function getCategoryCheckboxesHtml(rowId, selectedCategories = []) {
        let html = '';
        categoriesList.forEach(cat => {
            const selectedIdx = selectedCategories.indexOf(cat.categoryID.toString());
            const isChecked = selectedIdx !== -1;
            const sequenceNum = isChecked ? selectedIdx + 1 : '';
            
            html += `
                <div class="category-checkbox-item p-2 border mb-2 d-flex align-items-center bg-white cursor-pointer" 
                     onclick="handleCategoryToggle('${rowId}', ${cat.categoryID})" 
                     style="min-width: 180px; border-radius: 0; transition: 0.3s; position: relative;"
                     id="cat_wrap_${rowId}_${cat.categoryID}">
                    <div class="custom-control custom-checkbox mr-2 pointer-events-none">
                        <input type="checkbox" class="custom-control-input cat-check" 
                               id="check_${rowId}_${cat.categoryID}" 
                               data-id="${cat.categoryID}" 
                               ${isChecked ? 'checked' : ''}>
                        <label class="custom-control-label" for="check_${rowId}_${cat.categoryID}"></label>
                    </div>
                    <span class="small font-weight-bold" style="font-size: 11px;">${cat.name}</span>
                    <span class="seq-badge badge badge-dark ml-auto" style="border-radius: 0; display: ${isChecked ? 'inline-block' : 'none'};">${sequenceNum}</span>
                </div>`;
        });
        return html;
    }

    function handleCategoryToggle(rowId, catId) {
        const checkbox = $(`#check_${rowId}_${catId}`);
        const wrapper = $(`#cat_wrap_${rowId}_${catId}`);
        const badge = wrapper.find('.seq-badge');
        
        const isCurrentlyChecked = checkbox.is(':checked');
        
        if (!isCurrentlyChecked) {
            // Check it
            checkbox.prop('checked', true);
            wrapper.addClass('border-danger').css('background-color', '#fff5f5');
            
            const currentSelectedCount = $(`#categories_${rowId} .cat-check:checked`).length;
            badge.text(currentSelectedCount).show();
        } else {
            // Uncheck it
            const currentNum = parseInt(badge.text());
            checkbox.prop('checked', false);
            wrapper.removeClass('border-danger').css('background-color', '#fff');
            badge.hide().text('');
            
            // Reorder others
            $(`#categories_${rowId} .seq-badge`).each(function() {
                const num = parseInt($(this).text());
                if (num > currentNum) {
                    $(this).text(num - 1);
                }
            });
        }
    }

    function getTeacherOptions() {
        let options = '';
        teachersData.forEach(t => {
            options += `<option value="${t.id}">${t.first_name} ${t.last_name}</option>`;
        });
        return options;
    }

    function filterTtSubclasses(rowId, classId) {
        let options = '<option value="all">Apply to All Subclasses</option>';
        const filtered = subclassesData.filter(s => s.classID == classId);
        filtered.forEach(s => {
            options += `<option value="${s.subclassID}">${s.subclass_name}</option>`;
        });
        $('#' + rowId + ' .tt-subclass-select').html(options);
    }

    function addTeacherToAsgn(rowId) {
        const select = $('#' + rowId + ' .tt-teacher-select');
        const teacherId = select.val();
        if (!teacherId) return;

        const teacherName = select.find('option:selected').text();
        
        // Prevent duplicate in same row
        if ($(`#${rowId} .teacher-badge[data-id="${teacherId}"]`).length) return;

        const badge = `
            <span class="badge teacher-badge px-3 py-2 text-white d-flex align-items-center gap-2" data-id="${teacherId}" style="background: #940000; border-radius: 0; font-size: 11px;">
                ${teacherName}
                <i class="fa fa-times cursor-pointer" onclick="$(this).parent().remove()"></i>
            </span>`;
        $('#teachers_' + rowId).append(badge);
        select.val('');
    }

    $('#timetableForm').on('submit', function(e) {
        e.preventDefault();
        
        const targetClasses = [];
        $('.class-selector:checked').each(function() {
            targetClasses.push($(this).val());
        });

        if (targetClasses.length === 0) {
            Swal.fire('Error', 'Please select at least one target class', 'error');
            return;
        }

        const assignments = [];
        $('.assignment-row').each(function() {
            const rowId = $(this).attr('id');
            const classID = $(this).find('.tt-class-select').val();
            const subclassID = $(this).find('.tt-subclass-select').val();
            
            $(this).find('.teacher-badge').each(function() {
                const selectedCats = [];
                const badges = $(`#categories_${rowId} .seq-badge:visible`);
                
                // Get categories in order
                const catPairs = [];
                $(`#categories_${rowId} .cat-check:checked`).each(function() {
                    const cid = $(this).data('id');
                    const order = parseInt($(`#cat_wrap_${rowId}_${cid} .seq-badge`).text());
                    catPairs.push({ id: cid, order: order });
                });
                catPairs.sort((a, b) => a.order - b.order);
                const finalSelectedCategories = catPairs.map(p => ({
                    categoryID: p.id,
                    number: p.order
                }));

                assignments.push({
                    classID: classID,
                    subclassID: subclassID,
                    teacherID: $(this).data('id'),
                    selected_categories: finalSelectedCategories
                });
            });
        });

        const data = {
            _token: '{{ csrf_token() }}',
            timetableID: $('#ttID').val(),
            academic_yearID: $('#ttYear').val(),
            term: $('#ttTerm').val(),
            start_date: $('#ttStart').val(),
            end_date: $('#ttEnd').val(),
            target_classes: targetClasses,
            assignments: assignments
        };

        const jsonData = {
            timetableID: data.timetableID,
            academic_yearID: data.academic_yearID,
            term: data.term,
            start_date: data.start_date,
            end_date: data.end_date,
            target_classes: data.target_classes,
            assignments: data.assignments
        };

        $.ajax({
            url: `{{ route('store_assessment_timetable') }}`,
            type: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: JSON.stringify(jsonData),
            beforeSend: function() { Swal.showLoading(); },
            success: handleResponse,
            error: function(xhr) {
                Swal.close();
                let msg = 'An error occurred. Please try again.';
                try {
                    const res = JSON.parse(xhr.responseText);
                    msg = res.message || msg;
                } catch(e) {}
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    });

    function openViewTimetablesModal() {
        loadTimetables();
        viewTimetablesModal.modal('show');
    }

    function loadTimetables() {
        const year = $('#filterTtYear').val();
        const term = $('#filterTtTerm').val();
        
        $.get(`{{ url('get_assessment_timetables') }}`, { year, term }, function(res) {
            if (res.success) {
                renderTimetables(res.timetables);
            }
        });
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    function renderTimetables(timetables) {
        console.log('Timetables data:', timetables);
        let html = '';
        timetables.forEach(tt => {
            const ay = tt.academic_year || tt.academicYear;
            const yearLabel = ay ? ay.year : 'N/A';
            const td = tt.term_details || tt.termDetails;
            const termLabel = tt.term ? tt.term.replace('_', ' ').toUpperCase() : 'N/A';
            const classesCount = tt.target_classes ? (Array.isArray(tt.target_classes) ? tt.target_classes.length : 0) : 0;
            
            html += `
                <div class="timetable-card m-3 p-4 bg-white shadow-sm border-left" style="border-left: 5px solid #940000 !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="font-weight-bold mb-1" style="color: #940000;">${termLabel} - ${yearLabel}</h5>
                            <p class="text-muted small font-weight-bold mb-0">
                                <i class="fa fa-calendar-o"></i> ${formatDate(tt.start_date)} TO ${formatDate(tt.end_date)} 
                                <span class="mx-2">|</span> 
                                <i class="fa fa-users"></i> ${classesCount} Main Classes Scheduled
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark" onclick="openEditTimetable(${tt.timetableID})" style="border-radius:0;">EDIT</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTimetable(${tt.timetableID})" style="border-radius:0;">DELETE</button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="small font-weight-bold text-uppercase text-muted" style="font-size: 10px;">Assigned Teachers & Groups:</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 small">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Class</th>
                                        <th>Subclass</th>
                                        <th>Teachers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${renderAssignmentTableRows(tt.assignments)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
        });
        if (timetables.length === 0) html = '<div class="text-center py-5 text-muted font-weight-bold">No timetables found.</div>';
        $('#timetablesListContainer').html(html);
    }

    function renderAssignmentTableRows(assignments) {
        const grouped = {};
        if (assignments && Array.isArray(assignments)) {
            assignments.forEach(a => {
                const ttClass = a.class || a.Class;
                const className = (ttClass ? ttClass.class_name : 'N/A');
                const ttSubclass = a.subclass || a.Subclass;
                const key = className + ' - ' + (ttSubclass ? ttSubclass.subclass_name : 'All');
                if (!grouped[key]) grouped[key] = { class: ttClass, subclass: ttSubclass, teachers: [] };
                
                const ttTeacher = a.teacher || a.Teacher;
                const fullName = ttTeacher ? `${ttTeacher.first_name} ${ttTeacher.last_name}` : 'N/A';
                const teacherId = ttTeacher ? ttTeacher.id : null;
                if (teacherId) {
                    grouped[key].teachers.push({ id: teacherId, name: fullName });
                }
            });
        }

        let rows = '';
        for (const key in grouped) {
            const className = grouped[key].class ? grouped[key].class.class_name : 'N/A';
            rows += `
                <tr>
                    <td class="font-weight-bold">${className}</td>
                    <td>${grouped[key].subclass ? grouped[key].subclass.subclass_name : '<span class="badge badge-info" style="border-radius:0;">ALL</span>'}</td>
                    <td>${grouped[key].teachers.map(t => t.name).join(', ')}</td>
                </tr>`;
        }
        return rows;
    }

    function openEditTimetable(id) {
        $.get(`{{ url('get_assessment_timetables') }}`, { id: id }, function(res) {
            if (res.success && res.timetables.length > 0) {
                const tt = res.timetables[0];
                viewTimetablesModal.modal('hide');
                
                $('#timetableForm')[0].reset();
                $('#ttID').val(tt.timetableID);
                $('#ttYear').val(tt.academic_yearID);
                $('#ttTerm').val(tt.term);
                $('#ttStart').val(tt.start_date);
                $('#ttEnd').val(tt.end_date);
                
                $('.class-selector').prop('checked', false);
                if (tt.target_classes) {
                    tt.target_classes.forEach(cid => {
                        $(`#cls_${cid}`).prop('checked', true);
                    });
                }
                
                $('#teacherAssignmentsContainer').html('');
                const rowGroups = {};
                const assignments = tt.assignments || tt.Assignments;
                if (assignments) {
                    assignments.forEach(a => {
                        const classID = a.classID || a.ClassID;
                        const subclassID = a.subclassID || a.SubclassID;
                        const key = classID + '_' + (subclassID || 'all');
                        if (!rowGroups[key]) rowGroups[key] = { 
                            classID: classID, 
                            subclassID: subclassID || 'all', 
                            teachers: [],
                            selected_categories: a.selected_categories || []
                        };
                        rowGroups[key].teachers.push(a.teacher || a.Teacher);
                    });
                }
                
                for (const key in rowGroups) {
                    const grp = rowGroups[key];
                    const rowId = 'row_' + Date.now() + Math.random().toString(36).substr(2, 5);
                    let classOptions = '<option value="">Select Main Class</option>';
                    classesData.forEach(c => {
                        classOptions += `<option value="${c.classID}" ${c.classID == grp.classID ? 'selected' : ''}>${c.class_name}</option>`;
                    });

                    const row = `
                        <div class="assignment-row mb-3 p-3 bg-white shadow-sm border animate__animated animate__fadeInUp" id="${rowId}" style="position: relative; border-left: 5px solid #940000 !important;">
                            <button type="button" class="close text-danger" onclick="$('#${rowId}').remove()" style="position: absolute; right: 10px; top: 10px; font-size: 1.2rem; z-index: 10;">&times;</button>
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">1. Target Class</label>
                                    <select class="form-control form-control-sm tt-class-select" required onchange="filterTtSubclasses('${rowId}', this.value); checkClassAvailability();" style="border-radius: 0; font-weight: bold;">
                                        ${classOptions}
                                    </select>
                                </div>
                                <div class="col-md-3 border-left">
                                    <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">2. Subclass</label>
                                    <select class="form-control form-control-sm tt-subclass-select" style="border-radius: 0;">
                                        ${getTtSubclassOptions(grp.classID, grp.subclassID)}
                                    </select>
                                </div>
                                <div class="col-md-6 border-left">
                                    <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">3. Assign Teachers</label>
                                    <div class="teacher-list d-flex flex-wrap gap-1 mb-2 p-2 border bg-light" id="teachers_${rowId}" style="min-height: 40px; border-style: dashed !important; background-color: #fcfcfc;">
                                        ${grp.teachers.map(t => `
                                            <span class="badge teacher-badge px-3 py-2 text-white d-flex align-items-center gap-2" data-id="${t.id}" style="background: #940000; border-radius: 0; font-size: 11px;">
                                                ${t.first_name} ${t.last_name}
                                                <i class="fa fa-times cursor-pointer" onclick="$(this).parent().remove(); checkTeacherAvailability();"></i>
                                            </span>
                                        `).join('')}
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <select class="form-control tt-teacher-select" style="border-radius: 0;">
                                            <option value="">Choose Teacher...</option>
                                            ${getTeacherOptions()}
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-danger font-weight-bold" type="button" onclick="addTeacherToAsgn('${rowId}')" style="border-radius: 0; border: 1px solid #ced4da; border-left: none; background: #940000; color: white;">+ ADD</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 border-top pt-3">
                                <div class="col-md-12">
                                    <label class="small font-weight-bold text-uppercase" style="font-size: 9px; color: #940000;">4. Select Assessment Sectors (Order of Assessment)</label>
                                    <div class="d-flex flex-wrap gap-2 category-selection-grid" id="categories_${rowId}">
                                        ${getCategoryCheckboxesHtml(rowId, grp.selected_categories || [])}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    $('#teacherAssignmentsContainer').append(row);
                }
                
                checkTeacherAvailability();
                timetableModal.modal('show');
            }
        });
    }

    function getTtSubclassOptions(classId, selectedId = 'all') {
        let options = `<option value="all" ${selectedId == 'all' ? 'selected' : ''}>Apply to All Subclasses</option>`;
        const filtered = subclassesData.filter(s => s.classID == classId);
        filtered.forEach(s => {
            options += `<option value="${s.subclassID}" ${selectedId == s.subclassID ? 'selected' : ''}>${s.subclass_name}</option>`;
        });
        return options;
    }

    function checkTeacherAvailability() {
        const assignedIds = [];
        $('.teacher-badge').each(function() {
            assignedIds.push(parseInt($(this).data('id')));
        });

        $('.tt-teacher-select option').each(function() {
            const id = $(this).val();
            if (id && assignedIds.includes(parseInt(id))) {
                if (!$(this).text().includes('(Assigned)')) {
                    $(this).text($(this).text() + ' (Assigned)');
                }
                $(this).css({ 'color': '#999', 'font-style': 'italic', 'opacity': '0.7' });
            } else {
                $(this).text($(this).text().replace(' (Assigned)', ''));
                $(this).css({ 'color': '#000', 'font-style': 'normal', 'opacity': '1' });
            }
        });
    }

    // Call checkTeacherAvailability whenever a badge is added or removed
    function addTeacherToAsgn(rowId) {
        const select = $('#' + rowId + ' .tt-teacher-select');
        const teacherId = select.val();
        if (!teacherId) return;

        const teacherName = select.find('option:selected').text().replace(' (Assigned)', '');
        
        if ($(`#${rowId} .teacher-badge[data-id="${teacherId}"]`).length) return;

        const badge = `
            <span class="badge teacher-badge px-3 py-2 text-white d-flex align-items-center gap-2" data-id="${teacherId}" style="background: #940000; border-radius: 0; font-size: 11px;">
                ${teacherName}
                <i class="fa fa-times cursor-pointer" onclick="$(this).parent().remove(); checkTeacherAvailability();"></i>
            </span>`;
        $('#teachers_' + rowId).append(badge);
        select.val('');
        checkTeacherAvailability();
        checkClassAvailability();
    }

    function deleteTimetable(id) {
        Swal.fire({
            title: 'Delete Timetable?',
            text: "This will remove the current assessment schedule.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Yes, Delete'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({ url: `{{ url('delete_assessment_timetable') }}/${id}`, type: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: handleResponse });
            }
        });
    }

    function handleResponse(res) {
        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Action Successful', text: res.message, showConfirmButton: false, timer: 1500 });
            // Instead of reloading, we refresh the UI parts
            if (timetableModal.is(':visible')) timetableModal.modal('hide');
            if (viewTimetablesModal.is(':visible')) loadTimetables();
        } else {
            Swal.fire({ icon: 'error', title: 'System Error', text: res.message });
        }
    }

    function deleteCategory(id) {
        Swal.fire({
            title: 'Delete this category?',
            text: "All associated progress data will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#940000',
            confirmButtonText: 'Yes, Delete Permanentally'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({ url: `{{ url('delete_assessment_category') }}/${id}`, type: 'DELETE', data: { _token: '{{ csrf_token() }}' }, success: handleResponse });
            }
        });
    }

    /* Character Assessment Dashboard JS */
    let myCategoryChart = null;

    $(document).ready(function() {

        // Term filter change -> Load Timetables
        $('#filterTerm').on('change', function() {
            const termID = $(this).val();
            if(!termID) {
                $('#filterTimetable').val("").prop('disabled', true);
                $('#filterClass').val("").prop('disabled', true);
                return;
            }

            // Show loading state
            $('#filterTimetable').html('<option value="">Loading periods...</option>').prop('disabled', true);
            $('#filterClass').val("").prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.character.timetables_by_term') }}",
                type: 'GET',
                data: { termID: termID },
                success: function(res) {
                    if(res.success) {
                        let html = '<option value="">-- Select Period --</option>';
                        res.timetables.forEach(t => {
                            html += `<option value="${t.timetableID}">${formatDate(t.start_date)} - ${formatDate(t.end_date)}</option>`;
                        });
                        $('#filterTimetable').html(html).prop('disabled', false);
                    }
                }
            });
        });

        // Timetable change -> Load Classes
        $('#filterTimetable').on('change', function() {
            const ttID = $(this).val();
            if(!ttID) {
                $('#filterClass').val("").prop('disabled', true);
                return;
            }

            // Show loading state
            $('#filterClass').html('<option value="">Loading classes...</option>').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.character.classes_by_timetable') }}",
                type: 'GET',
                data: { timetableID: ttID },
                success: function(res) {
                    if(res.success) {
                        let html = '<option value="">-- Select Class --</option>';
                        res.classes.forEach(c => {
                            html += `<option value="${c.classID}">${c.class_name}</option>`;
                        });
                        $('#filterClass').html(html).prop('disabled', false);
                    }
                }
            });
        });

        // Search Report
        $('#searchReportBtn').on('click', function() {
            const ttID = $('#filterTimetable').val();
            const classID = $('#filterClass').val();

            if(!ttID || !classID) {
                Swal.fire('Filter Missing', 'Please select both Period and Class.', 'warning');
                return;
            }

            $('#dashboardWelcome').hide();
            $('#dashboardReportSection').hide();
            $('#reportLoading').show();

            $.ajax({
                url: "{{ route('admin.character.report_data') }}",
                type: 'GET',
                data: { timetableID: ttID, classID: classID },
                success: function(res) {
                    $('#reportLoading').hide();
                    if(res.success) {
                        displayReport(res);
                        $('#dashboardReportSection').fadeIn(300);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        $('#dashboardWelcome').show();
                    }
                },
                error: function() {
                    $('#reportLoading').hide();
                    Swal.fire('Error', 'Internal Server error.', 'error');
                }
            });
        });

        // Local Search
        $('#studentSearch').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            let count = 0;
            $('#studentReportBody tr').each(function() {
                const text = $(this).text().toLowerCase();
                if(text.indexOf(val) > -1) {
                    $(this).show();
                    count++;
                } else {
                    $(this).hide();
                }
            });
            $('#displayedCount').text(count);
        });
    });

    function displayReport(data) {
        // 1. Stats
        $('#statTotalStudents').text(data.summary.total);
        $('#statMales').text(data.summary.males);
        $('#statFemales').text(data.summary.females);
        $('#statAssessed').text(data.summary.assessed);
        $('#statPending').text(data.summary.pending);
        
        $('#totalClassCount').text(data.summary.total);
        if (data.studentList) {
            $('#displayedCount').text(data.studentList.length);
        }

        // 2. Pending List
        renderPendingStudents(data.studentList);

        // 3. Chart
        renderChart(data);

        // 4. Stats Table
        renderStatsTable(data);

        // 5. Student List
        renderStudentTable(data);
    }

    function togglePendingList() {
        $('#pendingStudentsWrapper').slideToggle();
    }

    function renderPendingStudents(list) {
        const pending = list.filter(s => !s.is_ready);
        let html = '';
        if (pending.length === 0) {
            html = '<tr><td colspan="4" class="text-center py-3 text-success font-weight-bold">All students have been assessed!</td></tr>';
        } else {
            pending.forEach(s => {
                html += `
                    <tr class="small">
                        <td class="pl-3 font-weight-bold text-danger">${s.admission_number}</td>
                        <td class="font-weight-bold">${s.first_name} ${s.last_name}</td>
                        <td>${s.gender}</td>
                        <td class="text-right pr-3"><span class="badge badge-danger">PENDING</span></td>
                    </tr>
                `;
            });
        }
        $('#pendingStudentsBody').html(html);
    }

    function renderChart(data) {
        if (myCategoryChart) {
            myCategoryChart.destroy();
        }

        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        // Prepare datasets - each choice is a dataset
        const datasets = data.allChoices.map((choice, index) => {
            const colors = ['#940000', '#2f2f2f', '#007bff', '#28a745', '#ffc107', '#17a2b8'];
            return {
                label: choice.choice_name,
                backgroundColor: colors[index % colors.length],
                data: data.chartData.map(cat => {
                    const found = cat.choices.find(c => c.name === choice.choice_name);
                    return found ? found.count : 0;
                })
            };
        });

        myCategoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.chartData.map(c => c.category_name),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, padding: 15, font: { weight: 'bold' } } }
                }
            }
        });
    }

    function renderStatsTable(data) {
        let headerHtml = '<th class="border-0">Category</th>';
        data.allChoices.forEach(c => {
            headerHtml += `<th class="border-0">${c.choice_name} (%)</th>`;
        });
        $('#statsTableHeader').html(headerHtml);

        let bodyHtml = '';
        data.statsTable.forEach(row => {
            bodyHtml += `<tr><td class="font-weight-bold" style="color:#2f2f2f;">${row.category}</td>`;
            row.choices.forEach(ch => {
                bodyHtml += `
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 mr-2" style="height: 6px; border-radius:10px;">
                                <div class="progress-bar" style="width: ${ch.percentage}%; background-color:#940000;"></div>
                            </div>
                            <span class="font-weight-bold">${ch.percentage}%</span>
                        </div>
                    </td>`;
            });
            bodyHtml += '</tr>';
        });
        $('#statsTableBody').html(bodyHtml);
    }

    function renderStudentTable(data) {
        let html = '';
        data.studentList.forEach(s => {
            const fullName = `${s.first_name} ${s.last_name}`;
            const photo = s.photo ? `{{ asset('userImages/') }}/${s.photo}` : (s.gender === 'Female' ? `{{ asset('images/female.png') }}` : `{{ asset('images/male.png') }}`);
            const status = s.is_ready ? `<span class="badge badge-success px-3 py-1" style="border-radius:0;">ASSESSED</span>` : `<span class="badge badge-light text-muted border px-3 py-1" style="border-radius:0;">PENDING</span>`;
            
            html += `
                <tr id="row_st_${s.studentID}">
                    <td class="pl-4">
                        <div class="d-flex align-items-center">
                            <img src="${photo}" class="rounded-circle mr-3" style="width:35px; height:35px; border:1px solid #eee;">
                            <div>
                                <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">${fullName}</h6>
                                <span class="text-muted" style="font-size: 11px;">ADM: ${s.admission_number}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="small font-weight-bold">${$('#filterClass option:selected').text()}</span></td>
                    <td>${status}</td>
                    <td>
                        <div class="small text-muted" style="font-style: italic;">
                            ${s.summary.length ? s.summary.join(' | ') : 'No data yet'}
                        </div>
                    </td>
                    <td class="pr-4 text-right">
                        <button class="btn btn-sm btn-light border" style="border-radius:0;" onclick="toggleStudentDetail(${s.studentID})">
                            <i class="fa fa-eye"></i> VIEW
                        </button>
                        <button class="btn btn-sm text-white" style="border-radius:0; background:#940000;" onclick="downloadSinglePDF(${s.studentID}, '${fullName}')">
                            <i class="fa fa-download"></i> PDF
                        </button>
                    </td>
                </tr>
                <tr id="detail_st_${s.studentID}" style="display:none; background:#fafafa;">
                    <td colspan="5" class="p-4 border-top">
                        <div class="px-4">
                            <h6 class="font-weight-bold mb-3 small text-uppercase" style="letter-spacing:1px; color:#940000;">Full Character Profile</h6>
                            <div class="row" id="detail_content_${s.studentID}">
                                <div class="col-12 text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });
        $('#studentReportBody').html(html);
    }

    function toggleStudentDetail(studentID) {
        const row = $(`#detail_st_${studentID}`);
        if(row.is(':visible')) {
            row.fadeOut(200);
        } else {
            row.fadeIn(300);
            // Load detail content via AJAX or from local data if available
            fetchStudentDetail(studentID);
        }
    }

    function fetchStudentDetail(studentID) {
        const ttID = $('#filterTimetable').val();
        $.ajax({
            url: "{{ route('teacher.get_student_assessment_history') }}",
            type: 'GET',
            data: { studentID: studentID, timetableID: ttID },
            success: function(res) {
                let html = '';
                if(res.history.length === 0) {
                    html = '<div class="col-12"><p class="text-muted">No records found for this period.</p></div>';
                } else {
                    res.history.forEach(item => {
                        html += `
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-white border border-left-danger" style="border-left-width:3px !important;">
                                    <span class="small text-muted text-uppercase d-block mb-1" style="font-size:10px;">${item.category.name}</span>
                                    <div class="font-weight-bold" style="color:#2f2f2f;">${item.choice ? item.choice.choice_name : 'No Rating'}</div>
                                    <p class="small text-muted mb-0 mt-2">${item.description || ''}</p>
                                </div>
                            </div>
                        `;
                    });
                }
                $(`#detail_content_${studentID}`).html(html);
            }
        });
    }

    function downloadAssessmentPdf(params, filename) {
        let url = "{{ route('admin.character.download_pdf') }}";
        const query = new URLSearchParams(params).toString();
        url += '?' + query;

        $('#downloadProgressModal').modal('show');
        $('#pdfProgressBar').css('width', '0%').attr('aria-valuenow', 0);
        $('#pdfProgressPercent').text('0%');

        let progress = 0;
        const interval = setInterval(() => {
            if (progress < 95) {
                let inc = 2 + Math.random() * 5;
                if (progress > 50) inc = 1 + Math.random() * 2;
                progress += inc;
                if (progress > 95) progress = 95;
                $('#pdfProgressBar').css('width', progress + '%').attr('aria-valuenow', Math.round(progress));
                $('#pdfProgressPercent').text(Math.round(progress) + '%');
            }
        }, 400);

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Generation failed');
                return res.blob();
            })
            .then(blob => {
                clearInterval(interval);
                $('#pdfProgressBar').css('width', '100%').attr('aria-valuenow', 100);
                $('#pdfProgressPercent').text('100%');
                
                setTimeout(() => {
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(downloadUrl);
                    $('#downloadProgressModal').modal('hide');
                }, 400);
            })
            .catch(err => {
                clearInterval(interval);
                $('#downloadProgressModal').modal('hide');
                alert('Failed to generate PDF. Please try again.');
            });
    }

    function downloadSinglePDF(studentID, name) {
        const ttID = $('#filterTimetable').val();
        if (!ttID) return;
        downloadAssessmentPdf(
            { scope: 'student', timetableID: ttID, studentID: studentID },
            `Assessment_Report_${name ? name.replace(/ /g, '_') : studentID}.pdf`
        );
    }

    function printFullClassReport() {
        const ttID = $('#filterTimetable').val();
        const classID = $('#filterClass').val();
        if (!ttID || !classID) return;

        downloadAssessmentPdf(
            { scope: 'class', timetableID: ttID, classID: classID },
            `Class_Assessment_Report_${classID}.pdf`
        );
    }

    function exportToExcel() {
        const ttID = $('#filterTimetable').val();
        const classID = $('#filterClass').val();
        if (!ttID || !classID) {
            Swal.fire('Filter Missing', 'Select Period and Class first.', 'warning');
            return;
        }

        window.location.href = `{{ route('admin.character.download_excel') }}?timetableID=${ttID}&classID=${classID}`;
    }
</script>

<style>
    /* Font family global for this blade */
    body, div, h1, h2, h3, h4, h5, h6, p, span, table, button, input, select {
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif !important;
    }

    .dropdown-item {
        color: #2f2f2f !important;
        transition: 0.3s;
    }
    .dropdown-item:hover {
        background: #FFF5F5 !important;
        color: #940000 !important;
        padding-left: 20px !important;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: whitesmoke;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cfcfcf;
        border-radius: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #940000;
    }

    .setting-item {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .setting-item:hover {
        background: #FFF5F5;
        transform: translateY(-3px);
    }
    .gap-3 { gap: 1rem; }
    
    .category-checkbox-item {
        user-select: none;
        cursor: pointer;
    }
    .category-checkbox-item:hover {
        border-color: #940000 !important;
    }
    .category-checkbox-item.border-danger {
        box-shadow: 0 2px 8px rgba(148, 0, 0, 0.1);
    }
    .pointer-events-none {
        pointer-events: none;
    }
</style>
