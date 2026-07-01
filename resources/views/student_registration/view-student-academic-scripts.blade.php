    // Function to load academic years for student
    function loadAcademicYearsForStudent(studentID) {
        if (!studentID) {
            console.error('Student ID is required');
            $('#academicYearSelector').html('<option value="">Error: Student ID missing</option>');
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_academic_years", ":id") }}'.replace(':id', studentID),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.years) {
                    let selector = $('#academicYearSelector');
                    if (selector.length === 0) {
                        console.error('Academic year selector not found');
                        return;
                    }
                    
                    selector.html('<option value="">Select Academic Year</option>');
                    
                    response.years.forEach(function(year) {
                        let selected = year.is_active ? 'selected' : '';
                        let statusBadge = year.status === 'Active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Closed</span>';
                        selector.append('<option value="' + year.academic_yearID + '" data-status="' + year.status + '" ' + selected + '>' + year.year_name + ' ' + statusBadge + '</option>');
                    });
                    
                    // Enable widgets if active year is selected by default
                    if (response.years.some(y => y.is_active)) {
                        $('.academic-widget').removeClass('disabled').css('opacity', '1');
                        let activeYear = response.years.find(y => y.is_active);
                        if (activeYear) {
                            loadAcademicDataForYear(studentID, activeYear.academic_yearID, activeYear.status);
                        }
                    }
                } else {
                    console.error('Invalid response:', response);
                    $('#academicYearSelector').html('<option value="">No academic years found</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading academic years:', error);
                console.error('Response:', xhr.responseText);
                console.error('Status:', status);
                $('#academicYearSelector').html('<option value="">Error loading years</option>');
            }
        });
    }
    
    // Handle academic year selection change
    $(document).on('change', '#academicYearSelector', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).val();
        let status = $(this).find('option:selected').data('status');
        
        if (academicYearID) {
            // Enable widgets
            $('.academic-widget').removeClass('disabled').css('opacity', '1');
            loadAcademicDataForYear(studentID, academicYearID, status);
        } else {
            // Reset widgets
            $('.academic-widget').addClass('disabled').css('opacity', '0.6');
            $('#classWidgetContent, #resultsWidgetContent, #attendanceWidgetContent').html('<p class="text-muted text-center py-3">Select Academic Year first</p>');
            // Collapse all widgets
            $('.academic-widget').attr('data-expanded', 'false');
            $('.widget-content').slideUp();
            $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
    });
    
    // Handle widget click to expand/collapse
    $(document).on('click', '.academic-widget .card-header', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let widget = $(this).closest('.academic-widget');
        let isExpanded = widget.attr('data-expanded') === 'true';
        let content = widget.find('.widget-content');
        let icon = widget.find('.widget-toggle-icon');
        let widgetId = widget.attr('id');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        // Check if academic year is selected
        if (!academicYearID) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Academic Year',
                text: 'Please select an academic year first'
            });
            return;
        }
        
        if (isExpanded) {
            // Collapse
            content.slideUp(300, function() {
                widget.attr('data-expanded', 'false');
            });
            icon.removeClass('bi-chevron-up').addClass('bi-chevron-down');
        } else {
            // Expand first
            content.slideDown(300, function() {
                widget.attr('data-expanded', 'true');
            });
            icon.removeClass('bi-chevron-down').addClass('bi-chevron-up');
            
            // Load data if not loaded yet
            let contentText = widget.find('.widget-content').text().trim();
            let needsLoading = contentText.includes('Click to load') || 
                              contentText.includes('Select Academic Year') || 
                              contentText.includes('Loading...') ||
                              contentText === '' ||
                              contentText.length < 50; // If content is too short, probably needs loading
            
            console.log('Widget clicked:', widgetId, 'Needs loading:', needsLoading, 'Content:', contentText);
            
            if (needsLoading && academicYearID) {
                if (widgetId === 'classWidget') {
                    console.log('Loading class data...');
                    loadClassDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'resultsWidget') {
                    console.log('Loading results data...');
                    loadResultsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'attendanceWidget') {
                    console.log('Loading attendance data...');
                    loadAttendanceDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'paymentsWidget') {
                    console.log('Loading payments data...');
                    loadPaymentsDataForWidget(studentID, academicYearID, yearStatus);
                } else if (widgetId === 'debitsWidget') {
                    console.log('Loading debts data...');
                    loadDebitsDataForWidget(studentID, academicYearID, yearStatus);
                }
            }
        }
    });
    
    // Function to load academic data for selected year (prepare widgets)
    function loadAcademicDataForYear(studentID, academicYearID, yearStatus) {
        // Just prepare widgets - data will load when clicked
        // Make sure widgets are visible and ready
        $('.academic-widget').removeClass('disabled').css('opacity', '1');
        $('#classWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load class data...</p>');
        $('#resultsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load results...</p>');
        $('#attendanceWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load attendance...</p>');
        $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load payments...</p>');
        $('#debitsWidgetContent').html('<p class="text-muted text-center py-3"><i class="bi bi-hourglass-split"></i> Click widget header to load debts...</p>');
        
        // Collapse all widgets initially
        $('.academic-widget').attr('data-expanded', 'false');
        $('.widget-content').hide();
        $('.widget-toggle-icon').removeClass('bi-chevron-up').addClass('bi-chevron-down');
    }
    
    // Function to load class data for widget
    function loadClassDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#classWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#classWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading classes...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_classes_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Class data response:', response);
                if (response.success && response.classes && response.classes.length > 0) {
                    let content = '<div class="list-group" style="border-radius: 0;">';
                    response.classes.forEach(function(cls) {
                        content += '<div class="list-group-item class-item" data-class-id="' + cls.class_id + '" data-class-name="' + cls.class_name + '" style="border-radius: 0; cursor: pointer;">';
                        content += '<div class="d-flex justify-content-between align-items-center">';
                        content += '<div><i class="bi bi-book text-primary-custom"></i> <strong>' + cls.class_name + '</strong></div>';
                        content += '<i class="bi bi-chevron-right"></i>';
                        content += '</div>';
                        content += '<div class="subclasses-list mt-2" style="display: none;"></div>';
                        content += '</div>';
                    });
                    content += '</div>';
                    $('#classWidgetContent').html(content);
                } else {
                    $('#classWidgetContent').html('<p class="text-muted text-center py-3">No classes found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading classes:', error, xhr.responseText);
                $('#classWidgetContent').html('<p class="text-danger text-center py-3">Error loading classes. Please try again.</p>');
            }
        });
    }
    
    // Function to load results data for widget
    function loadResultsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_terms_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Results/terms response:', response);
                if (response.success && response.terms && response.terms.length > 0) {
                    let content = '<div class="mb-3">';
                    content += '<label class="form-label fw-bold">Select Term:</label>';
                    content += '<select class="form-select term-selector" style="border-radius: 0;">';
                    content += '<option value="">Select Term</option>';
                    response.terms.forEach(function(term) {
                        content += '<option value="' + term.term + '">' + term.term_name + '</option>';
                    });
                    content += '</select>';
                    content += '</div>';
                    content += '<div id="termResultsOptions" style="display: none;"></div>';
                    $('#resultsWidgetContent').html(content);
                } else {
                    $('#resultsWidgetContent').html('<p class="text-muted text-center py-3">No terms/results found for this academic year</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading results:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading results. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#resultsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load attendance data for widget
    function loadAttendanceDataForWidget(studentID, academicYearID, yearStatus) {
        let content = '<div class="mb-3">';
        content += '<label class="form-label fw-bold">Filter Type:</label>';
        content += '<select class="form-select attendance-filter-type" style="border-radius: 0;">';
        content += '<option value="">Select Filter Type</option>';
        content += '<option value="date_range">Filter by Date Range</option>';
        content += '<option value="month">Filter by Month</option>';
        content += '</select>';
        content += '</div>';
        content += '<div id="attendanceFilterOptions" style="display: none;"></div>';
        content += '<div id="attendanceResults" class="mt-3"></div>';
        $('#attendanceWidgetContent').html(content);
    }
    
    // Function to load payments data for widget
    function loadPaymentsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#paymentsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading payments...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_payments_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Payments response:', response);
                if (response.success) {
                    let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Control Number</th><th>Fee Type</th><th>Amount</th><th>Payment Method</th><th>Receipt</th></tr></thead><tbody>';
                    
                    if (response.payments && response.payments.length > 0) {
                        response.payments.forEach(function(payment) {
                            content += '<tr>';
                            content += '<td>' + (payment.date || 'N/A') + '</td>';
                            content += '<td>' + (payment.control_number || 'N/A') + '</td>';
                            content += '<td>' + (payment.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (payment.amount || 0) + ' TZS</td>';
                            content += '<td>' + (payment.payment_method || 'N/A') + '</td>';
                            content += '<td>' + (payment.receipt_number || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="6" class="text-center text-muted">No payments found for this academic year</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    $('#paymentsWidgetContent').html(content);
                } else {
                    $('#paymentsWidgetContent').html('<p class="text-muted text-center py-3">No payments found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading payments:', error);
                console.error('Response:', xhr.responseText);
                let errorMessage = 'Error loading payments. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        let errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Not JSON, use default message
                    }
                }
                $('#paymentsWidgetContent').html('<p class="text-danger text-center py-3">' + errorMessage + '</p>');
            }
        });
    }
    
    // Function to load debits (debts) data for widget
    function loadDebitsDataForWidget(studentID, academicYearID, yearStatus) {
        if (!studentID || !academicYearID) {
            $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#debitsWidgetContent').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading debts...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_debts_for_year") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Debts response:', response);
                if (response.success) {
                    let content = '<div class="alert alert-warning" style="border-radius: 0;"><strong>Total Outstanding Debt: ' + (response.total_debt || 0) + ' TZS</strong></div>';
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Fee Type</th><th>Required Amount</th><th>Paid Amount</th><th>Outstanding</th></tr></thead><tbody>';
                    
                    if (response.debts && response.debts.length > 0) {
                        response.debts.forEach(function(debt) {
                            content += '<tr>';
                            content += '<td>' + (debt.fee_type || 'N/A') + '</td>';
                            content += '<td>' + (debt.required_amount || 0) + ' TZS</td>';
                            content += '<td>' + (debt.paid_amount || 0) + ' TZS</td>';
                            content += '<td class="text-danger"><strong>' + (debt.outstanding || 0) + ' TZS</strong></td>';
                            content += '</tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-success"><strong>No outstanding debts</strong></td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    
                    // Add library records section (books not returned)
                    if (response.library_records && response.library_records.length > 0) {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Borrow Date</th><th>Book Title</th><th>Subject</th><th>Class</th></tr></thead><tbody>';
                        
                        response.library_records.forEach(function(record) {
                            content += '<tr>';
                            content += '<td>' + (record.borrow_date || 'N/A') + '</td>';
                            content += '<td>' + (record.book_title || 'N/A') + '</td>';
                            content += '<td>' + (record.subject_name || 'N/A') + '</td>';
                            content += '<td>' + (record.class_name || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div></div>';
                    } else {
                        content += '<div class="mt-4"><h6 class="fw-bold mb-3"><i class="bi bi-book"></i> Library Books Not Returned</h6>';
                        content += '<p class="text-muted text-center py-2">No books borrowed or all books have been returned</p></div>';
                    }
                    
                    $('#debitsWidgetContent').html(content);
                } else {
                    $('#debitsWidgetContent').html('<p class="text-muted text-center py-3">No debts found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading debts:', error);
                $('#debitsWidgetContent').html('<p class="text-danger text-center py-3">Error loading debts</p>');
            }
        });
    }
    
    // Handle class item click to show subclasses
    $(document).on('click', '.class-item', function() {
        let classID = $(this).data('class-id');
        let className = $(this).data('class-name');
        let subclassesList = $(this).find('.subclasses-list');
        
        if (subclassesList.is(':visible')) {
            subclassesList.slideUp();
            return;
        }
        
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        subclassesList.html('<div class="text-center py-2"><div class="spinner-border spinner-border-sm" role="status"></div></div>').slideDown();
        
        $.ajax({
            url: '{{ route("get_student_subclasses_for_class") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                class_id: classID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.subclasses && response.subclasses.length > 0) {
                    let html = '<ul class="list-group list-group-flush" style="border-radius: 0;">';
                    response.subclasses.forEach(function(subclass) {
                        html += '<li class="list-group-item" style="border-radius: 0;"><i class="bi bi-layers text-info"></i> ' + subclass.subclass_name + '</li>';
                    });
                    html += '</ul>';
                    subclassesList.html(html);
                } else {
                    subclassesList.html('<p class="text-muted text-center py-2">No subclasses found</p>');
                }
            },
            error: function() {
                subclassesList.html('<p class="text-danger text-center py-2">Error loading subclasses</p>');
            }
        });
    });
    
    // Handle term selection for results widget
    $(document).on('change', '.term-selector', function() {
        let term = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (term && academicYearID) {
            // Show filter options for exam results or term report
            let content = '<div class="mb-3">';
            content += '<label class="form-label fw-bold">View Type:</label>';
            content += '<select class="form-select" id="resultViewType" style="border-radius: 0;">';
            content += '<option value="">Select View Type</option>';
            content += '<option value="exam_results">Exam Results</option>';
            content += '<option value="term_report">Term Report</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="examFilterContainer" style="display: none;" class="mb-3">';
            content += '<label class="form-label fw-bold">Select Exam:</label>';
            content += '<select class="form-select" id="examSelector" style="border-radius: 0;">';
            content += '<option value="">Loading...</option>';
            content += '</select>';
            content += '</div>';
            content += '<div id="resultsDisplayArea"></div>';
            
            $('#termResultsOptions').html(content).slideDown();
            
            // Load exams for this term
            loadExamsForTerm(studentID, academicYearID, term, yearStatus);
        } else {
            $('#termResultsOptions').slideUp().html('');
        }
    });
    
    // Handle result view type change
    $(document).on('change', '#resultViewType', function() {
        let viewType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (viewType === 'exam_results') {
            $('#examFilterContainer').show();
        } else if (viewType === 'term_report') {
            $('#examFilterContainer').hide();
            // Load term report
            if (term) {
                loadTermReport(studentID, academicYearID, term, yearStatus);
            }
        } else {
            $('#examFilterContainer').hide();
            $('#resultsDisplayArea').html('');
        }
    });
    
    // Handle exam selection change
    $(document).on('change', '#examSelector', function() {
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let term = $('.term-selector').val(); // Get term from term selector
        let examID = $(this).val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        console.log('Exam selected:', examID, 'Term:', term, 'Year:', academicYearID, 'Status:', yearStatus);
        
        if (examID && term && academicYearID) {
            $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading results...</p></div>');
            loadExamResults(studentID, academicYearID, term, examID, yearStatus);
        } else {
            $('#resultsDisplayArea').html('<p class="text-warning text-center py-3">Please select term and exam</p>');
        }
    });
    
    // Function to load exams for term
    function loadExamsForTerm(studentID, academicYearID, term, yearStatus) {
        $.ajax({
            url: '{{ route("get_exams_for_term") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let examSelector = $('#examSelector');
                    examSelector.html('<option value="">Select Exam</option>');
                    
                    if (response.exams && response.exams.length > 0) {
                        response.exams.forEach(function(exam) {
                            examSelector.append('<option value="' + exam.exam_id + '">' + exam.exam_name + '</option>');
                        });
                    } else {
                        examSelector.append('<option value="">No exams found</option>');
                    }
                }
            }
        });
    }
    
    // Function to load exam results
    function loadExamResults(studentID, academicYearID, term, examID, yearStatus) {
        if (!studentID || !academicYearID || !term || !examID) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_exam_results") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                exam_id: examID,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Exam results response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Position summary
                    if (response.position && response.total_students) {
                        content += '<div class="alert alert-info mb-3" style="border-radius: 0;">';
                        content += '<strong><i class="bi bi-trophy"></i> Position: ' + response.position + ' out of ' + response.total_students + ' students</strong>';
                        content += '</div>';
                    }
                    
                    content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Remark</th></tr></thead><tbody>';
                    
                    if (response.results && response.results.length > 0) {
                        response.results.forEach(function(result) {
                            content += '<tr><td>' + (result.subject_name || 'N/A') + '</td><td>' + (result.marks || 0) + '</td><td>' + (result.grade || 'N/A') + '</td><td>' + (result.remark || '') + '</td></tr>';
                        });
                    } else {
                        content += '<tr><td colspan="4" class="text-center text-muted">No results found for this exam</td></tr>';
                    }
                    
                    content += '</tbody></table></div>';
                    if (response.results && response.results.length > 0) {
                        content += '<div class="mt-3"><button class="btn btn-primary export-exam-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-exam-id="' + examID + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading exam results:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No results found for this exam</p>');
            }
        });
    }
    
    // Function to load term report
    function loadTermReport(studentID, academicYearID, term, yearStatus) {
        if (!studentID || !academicYearID || !term) {
            $('#resultsDisplayArea').html('<p class="text-danger text-center py-3">Missing required parameters</p>');
            return;
        }
        
        $('#resultsDisplayArea').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2">Loading term report...</p></div>');
        
        $.ajax({
            url: '{{ route("get_student_term_report") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                term: term,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                console.log('Term report response:', response);
                if (response.success) {
                    let content = '';
                    
                    // Student Information Section
                    content += '<div class="card mb-3" style="border-radius: 0;">';
                    content += '<div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-person"></i> Student Information</h5></div>';
                    content += '<div class="card-body">';
                    content += '<div class="row">';
                    content += '<div class="col-md-6"><strong>Student Name:</strong> ' + (response.student_name || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Term:</strong> ' + (response.term || 'N/A') + '</div>';
                    content += '<div class="col-md-3"><strong>Year:</strong> ' + (response.year || 'N/A') + '</div>';
                    content += '</div>';
                    if (response.position && response.total_students) {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Position:</strong> ' + response.position + ' out of ' + response.total_students + '</div>';
                        content += '<div class="col-md-3"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-3"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    } else {
                        content += '<div class="row mt-2">';
                        content += '<div class="col-md-6"><strong>Overall Average:</strong> ' + (response.overall_average || 0) + '</div>';
                        content += '<div class="col-md-6"><strong>Grade:</strong> ' + (response.overall_grade || 'N/A') + '</div>';
                        content += '</div>';
                    }
                    content += '</div></div>';
                    
                    // Examinations Table
                    if (response.examinations && response.examinations.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Examinations</h6></div>';
                        content += '<div class="card-body">';
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Examination</th><th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.examinations.forEach(function(exam) {
                            content += '<tr>';
                            content += '<td>' + (exam.exam_name || 'N/A') + '</td>';
                            content += '<td>' + (exam.average || 0) + '</td>';
                            content += '<td>' + (exam.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    }
                    
                    // Subject Results Table
                    if (response.report && response.report.length > 0) {
                        content += '<div class="card mb-3" style="border-radius: 0;">';
                        content += '<div class="card-header bg-success text-white"><h6 class="mb-0"><i class="bi bi-book"></i> Subject Results</h6></div>';
                        content += '<div class="card-body">';
                        
                        // Build table header with exam names
                        let examNames = [];
                        if (response.examinations && response.examinations.length > 0) {
                            response.examinations.forEach(function(exam) {
                                examNames.push(exam.exam_name);
                            });
                        }
                        
                        content += '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Subject</th>';
                        examNames.forEach(function(examName) {
                            content += '<th>' + examName + '</th>';
                        });
                        content += '<th>Average</th><th>Grade</th></tr></thead><tbody>';
                        
                        response.report.forEach(function(item) {
                            content += '<tr>';
                            content += '<td>' + (item.subject_name || 'N/A') + '</td>';
                            
                            // Add marks for each exam
                            examNames.forEach(function(examName) {
                                if (item.exam_marks && item.exam_marks[examName]) {
                                    let examMark = item.exam_marks[examName];
                                    content += '<td>' + examMark.marks + '-' + examMark.grade + '</td>';
                                } else {
                                    content += '<td>-</td>';
                                }
                            });
                            
                            content += '<td>' + (item.average || 0) + '</td>';
                            content += '<td>' + (item.grade || 'N/A') + '</td>';
                            content += '</tr>';
                        });
                        
                        content += '</tbody></table></div>';
                        content += '</div></div>';
                    } else {
                        content += '<div class="alert alert-info" style="border-radius: 0;">No report data found for this term</div>';
                    }
                    
                    if (response.report && response.report.length > 0) {
                        // Store response data in a global variable for PDF export
                        window.termReportData = response;
                        content += '<div class="mt-3"><button class="btn btn-primary export-term-pdf-btn" data-student-id="' + studentID + '" data-year-id="' + academicYearID + '" data-term="' + term + '" data-year-status="' + yearStatus + '" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button></div>';
                    }
                    
                    $('#resultsDisplayArea').html(content);
                } else {
                    $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading term report:', error);
                console.error('Response:', xhr.responseText);
                $('#resultsDisplayArea').html('<p class="text-muted text-center py-3">No report data found for this term</p>');
            }
        });
    }
    
    // Handle PDF export for exam results
    $(document).on('click', '.export-exam-pdf-btn', function() {
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let examID = $(this).data('exam-id');
        let yearStatus = $(this).data('year-status');
        
        // Get results data from table
        let results = [];
        $('#resultsDisplayArea table tbody tr').each(function() {
            let cells = $(this).find('td');
            if (cells.length >= 3) {
                results.push({
                    subject: $(cells[0]).text().trim(),
                    marks: $(cells[1]).text().trim(),
                    grade: $(cells[2]).text().trim(),
                    remark: $(cells[3]).text().trim() || ''
                });
            }
        });
        
        if (results.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No results to export'
            });
            return;
        }
        
        // Get student name
        let studentName = $('#viewStudentModal .school-title').text().trim() || 'Student';
        
        // Generate PDF using jsPDF
        if (typeof jsPDF === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded'
            });
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Header
        doc.setFontSize(16);
        doc.setTextColor(148, 0, 0);
        doc.text('EXAM RESULTS', 105, 15, { align: 'center' });
        
        // Student info
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text('Student: ' + studentName, 14, 25);
        doc.text('Term: ' + term.replace('_', ' ').toUpperCase(), 14, 32);
        
        // Table
        doc.autoTable({
            startY: 40,
            head: [['Subject', 'Marks', 'Grade', 'Remark']],
            body: results.map(r => [r.subject, r.marks, r.grade, r.remark]),
            theme: 'grid',
            headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
            styles: { fontSize: 9 }
        });
        
        // Add footer on all pages
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(128, 128, 128);
            doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
        }
        
        // Save
        let filename = 'Exam_Results_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
        doc.save(filename);
        
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'PDF exported successfully',
            timer: 2000,
            showConfirmButton: false
        });
    });
    
    // Handle PDF export for term report
    $(document).on('click', '.export-term-pdf-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Export PDF button clicked');
        
        let studentID = $(this).data('student-id');
        let academicYearID = $(this).data('year-id');
        let term = $(this).data('term');
        let yearStatus = $(this).data('year-status');
        
        console.log('Button data:', { studentID, academicYearID, term, yearStatus });
        console.log('Stored termReportData:', window.termReportData);
        
        // Get report data from stored response
        let response = window.termReportData;
        
        if (!response) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Report data not found. Please reload the term report first.'
            });
            return;
        }
        
        if (!response.report || response.report.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'No report data to export'
            });
            return;
        }
        
        // Get student name and other info from response
        let studentName = response.student_name || 'Student';
        let termName = response.term || term.replace('_', ' ').toUpperCase();
        let yearName = response.year || 'N/A';
        let overallAvg = response.overall_average || 0;
        let overallGrade = response.overall_grade || 'N/A';
        let position = response.position || null;
        let totalStudents = response.total_students || null;
        
        // Generate PDF using jsPDF
        if (typeof window.jspdf === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'PDF library not loaded. Please refresh the page.'
            });
            console.error('jsPDF library not found');
            return;
        }
        
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Header
            doc.setFontSize(18);
            doc.setTextColor(148, 0, 0);
            doc.text(studentName.toUpperCase() + ' - TERM REPORT DETAILS', 105, 15, { align: 'center' });
            
            // Student Information Section
            let yPos = 30;
            doc.setFontSize(12);
            doc.setTextColor(148, 0, 0);
            doc.text('Student Information', 14, yPos);
            yPos += 8;
            
            doc.setFontSize(10);
            doc.setTextColor(0, 0, 0);
            doc.text('Student Name: ' + studentName, 14, yPos);
            yPos += 6;
            doc.text('Term: ' + termName, 14, yPos);
            yPos += 6;
            doc.text('Year: ' + yearName, 14, yPos);
            yPos += 6;
            if (position && totalStudents) {
                doc.text('Position: ' + position + ' out of ' + totalStudents, 14, yPos);
                yPos += 6;
            }
            doc.text('Overall Average: ' + overallAvg, 14, yPos);
            yPos += 6;
            doc.text('Grade: ' + overallGrade, 14, yPos);
            yPos += 10;
            
            // Examinations Table
            if (response.examinations && response.examinations.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Examinations', 14, yPos);
                yPos += 8;
                
                let examTableData = response.examinations.map(exam => [
                    exam.exam_name || 'N/A',
                    exam.average || 0,
                    exam.grade || 'N/A'
                ]);
                
                doc.autoTable({
                    startY: yPos,
                    head: [['Examination', 'Average', 'Grade']],
                    body: examTableData,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 9 },
                    margin: { left: 14 }
                });
                
                yPos = doc.lastAutoTable.finalY + 10;
            }
            
            // Subject Results Table
            if (response.report && response.report.length > 0) {
                doc.setFontSize(12);
                doc.setTextColor(148, 0, 0);
                doc.text('Subject Results', 14, yPos);
                yPos += 8;
                
                // Build table header with exam names
                let examNames = [];
                if (response.examinations && response.examinations.length > 0) {
                    response.examinations.forEach(function(exam) {
                        examNames.push(exam.exam_name);
                    });
                }
                
                let tableHead = ['Subject'];
                examNames.forEach(function(examName) {
                    tableHead.push(examName);
                });
                tableHead.push('Average');
                tableHead.push('Grade');
                
                let tableBody = [];
                response.report.forEach(function(item) {
                    let row = [item.subject_name || 'N/A'];
                    
                    // Add marks for each exam
                    examNames.forEach(function(examName) {
                        if (item.exam_marks && item.exam_marks[examName]) {
                            let examMark = item.exam_marks[examName];
                            row.push(examMark.marks + '-' + examMark.grade);
                        } else {
                            row.push('-');
                        }
                    });
                    
                    row.push(item.average || 0);
                    row.push(item.grade || 'N/A');
                    tableBody.push(row);
                });
                
                doc.autoTable({
                    startY: yPos,
                    head: [tableHead],
                    body: tableBody,
                    theme: 'grid',
                    headStyles: { fillColor: [148, 0, 0], textColor: [255, 255, 255] },
                    styles: { fontSize: 8 },
                    margin: { left: 14 },
                    columnStyles: {
                        0: { cellWidth: 50 }, // Subject column
                    }
                });
            }
            
            // Add footer on all pages
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(128, 128, 128);
                doc.text('Powered by: EmCa Technologies LTD', 105, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
            }
            
            // Save
            let filename = 'Term_Report_' + studentName.replace(/\s+/g, '_') + '_' + term + '.pdf';
            doc.save(filename);
            
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'PDF exported successfully',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error generating PDF:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate PDF: ' + (error.message || 'Unknown error')
            });
        }
    });
    
    // Handle attendance filter type selection
    $(document).on('change', '.attendance-filter-type', function() {
        let filterType = $(this).val();
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType && academicYearID) {
            let content = '';
            
            if (filterType === 'date_range') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">From Date</label><input type="date" class="form-control" id="attendanceFromDate" style="border-radius: 0;"></div>';
                content += '<div class="col-md-6"><label class="form-label fw-bold">To Date</label><input type="date" class="form-control" id="attendanceToDate" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="date_range" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            } else if (filterType === 'month') {
                content += '<div class="row g-3 mb-3">';
                content += '<div class="col-md-6"><label class="form-label fw-bold">Month</label><input type="month" class="form-control" id="attendanceMonth" style="border-radius: 0;"></div>';
                content += '</div>';
                content += '<button class="btn btn-primary load-attendance-btn" data-filter-type="month" style="border-radius: 0;"><i class="bi bi-search"></i> Load Attendance</button>';
            }
            
            $('#attendanceFilterOptions').html(content).slideDown();
        } else {
            $('#attendanceFilterOptions').slideUp().html('');
            $('#attendanceResults').html('');
        }
    });
    
    // Handle load attendance button click
    $(document).on('click', '.load-attendance-btn', function() {
        let filterType = $(this).data('filter-type');
        let studentID = $('#academicYearSelector').data('student-id');
        let academicYearID = $('#academicYearSelector').val();
        let yearStatus = $('#academicYearSelector').find('option:selected').data('status');
        
        if (filterType === 'date_range') {
            loadAttendanceByDateRange(studentID, academicYearID, yearStatus);
        } else if (filterType === 'month') {
            loadAttendanceByMonth(studentID, academicYearID, yearStatus);
        }
    });
    
    // Function to load attendance by date range
    function loadAttendanceByDateRange(studentID, academicYearID, yearStatus) {
        let fromDate = $('#attendanceFromDate').val();
        let toDate = $('#attendanceToDate').val();
        
        if (!fromDate || !toDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select both from and to dates'
            });
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_attendance") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                from_date: fromDate,
                to_date: toDate,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, fromDate, toDate);
                }
            }
        });
    }
    
    // Function to load attendance by month
    function loadAttendanceByMonth(studentID, academicYearID, yearStatus) {
        let month = $('#attendanceMonth').val();
        
        if (!month) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please select a month'
            });
            return;
        }
        
        $.ajax({
            url: '{{ route("get_student_attendance") }}',
            type: 'GET',
            data: {
                student_id: studentID,
                academic_year_id: academicYearID,
                month: month,
                year_status: yearStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayAttendanceData(response.attendance, studentID, academicYearID, null, null, month);
                }
            }
        });
    }
    
    // Function to display attendance data
    function displayAttendanceData(attendance, studentID, academicYearID, fromDate, toDate, month) {
        let content = '<div class="table-responsive"><table class="table table-bordered" style="border-radius: 0;"><thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check In</th><th>Check Out</th><th>Remark</th></tr></thead><tbody>';
        
        if (attendance && attendance.length > 0) {
            attendance.forEach(function(record) {
                content += '<tr><td>' + record.date + '</td><td><span class="badge bg-' + (record.status === 'Present' ? 'success' : 'danger') + '">' + record.status + '</span></td><td>' + (record.check_in || 'N/A') + '</td><td>' + (record.check_out || 'N/A') + '</td><td>' + (record.remark || '') + '</td></tr>';
            });
        } else {
            content += '<tr><td colspan="5" class="text-center">No attendance records found</td></tr>';
        }
        
        content += '</tbody></table></div>';
        content += '<div class="mt-3">';
        content += '<button class="btn btn-primary me-2" onclick="exportAttendancePDF(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-pdf"></i> Export PDF</button>';
        content += '<button class="btn btn-success" onclick="exportAttendanceExcel(\'' + studentID + '\', \'' + academicYearID + '\', \'' + fromDate + '\', \'' + toDate + '\', \'' + month + '\')" style="border-radius: 0;"><i class="bi bi-file-excel"></i> Export Excel</button>';
        content += '</div>';
        
        $('#attendanceResults').html(content);
    }
    
    // Export functions (to be implemented)
    function exportResultsPDF(studentID, academicYearID, term, examID, type) {
        // Implementation for PDF export
        window.location.href = '{{ route("export_student_results_pdf") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&term=' + term + '&exam_id=' + examID + '&type=' + type;
    }
    
    function exportAttendancePDF(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for PDF export
        window.location.href = '{{ route("export_student_attendance_pdf") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&from_date=' + fromDate + '&to_date=' + toDate + '&month=' + month;
    }
    
    function exportAttendanceExcel(studentID, academicYearID, fromDate, toDate, month) {
        // Implementation for Excel export
        window.location.href = '{{ route("export_student_attendance_excel") }}?student_id=' + studentID + '&academic_year_id=' + academicYearID + '&from_date=' + fromDate + '&to_date=' + toDate + '&month=' + month;
    }

    window.exportAttendancePDF = exportAttendancePDF;
    window.exportAttendanceExcel = exportAttendanceExcel;

    window.exportAttendancePDF = exportAttendancePDF;
    window.exportAttendanceExcel = exportAttendanceExcel;
