@if($user_type == 'Admin')
@include('includes.Admin_nav')
@elseif($user_type == 'Staff')
@include('includes.staff_nav')
@else
@include('includes.teacher_nav')
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* Minimal custom CSS - only for #940000 color scheme */
    .bg-primary-custom {
        background-color: #940000 !important;
    }
    .text-primary-custom {
        color: #940000 !important;
    }
    .border-primary-custom {
        border-color: #940000 !important;
    }
    .btn-primary-custom {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #b30000;
        border-color: #b30000;
        color: #ffffff;
    }
    .btn-outline-primary-custom {
        color: #940000;
        border-color: #940000;
    }
    .btn-outline-primary-custom:hover {
        background-color: #940000;
        border-color: #940000;
        color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }

    /* Filters: no border radius */
    #classFilter, #subclassFilter, #genderFilter, #healthFilter, #statusFilter,
    .card .form-select, .card .form-control,
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single,
    .select2-dropdown {
        border-radius: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single,
    .select2-container--default .select2-selection--single {
        border-radius: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown,
    .select2-dropdown {
        border-radius: 0 !important;
    }

    /* Academic Widget Styles */
    .academic-widget {
        transition: all 0.3s ease;
    }
    
    .academic-widget.disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .academic-widget .card-header {
        user-select: none;
        cursor: pointer;
    }
    
    .academic-widget .card-header:hover {
        opacity: 0.9;
    }
    
    .academic-widget .widget-content {
        display: none;
    }
    
    .academic-widget[data-expanded="true"] .widget-content {
        display: block !important;
    }

    /* Enrollment history widgets (student details modal) */
    .enrollment-year-select {
        border-radius: 0 !important;
        border: 1px solid #ced4da;
        width: 100%;
    }
    .enrollment-class-widget {
        border: 1px solid #dee2e6;
        border-radius: 0;
        background: whitesmoke;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    .enrollment-class-widget .enrollment-class-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        cursor: pointer;
        user-select: none;
        background: #fff;
        border-bottom: 1px solid transparent;
        transition: background 0.15s ease;
    }
    .enrollment-class-widget .enrollment-class-header:hover {
        background: #f8f9fa;
    }
    .enrollment-class-widget.is-expanded .enrollment-class-header {
        border-bottom-color: #dee2e6;
        background: #fff;
    }
    .enrollment-class-widget .enrollment-class-title {
        font-weight: 600;
        color: #212529;
        margin: 0;
        font-size: 0.95rem;
    }
    .enrollment-class-widget .enrollment-class-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .enrollment-class-widget .enrollment-toggle-icon {
        color: #940000;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .enrollment-class-widget.is-expanded .enrollment-toggle-icon {
        transform: rotate(180deg);
    }
    .enrollment-class-widget .enrollment-subclasses {
        display: none;
        padding: 0.75rem 1rem 1rem;
        background: whitesmoke;
    }
    .enrollment-class-widget.is-expanded .enrollment-subclasses {
        display: block;
    }
    .enrollment-subclass-widget {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0;
        margin-bottom: 0.5rem;
        overflow: hidden;
    }
    .enrollment-subclass-widget:last-child {
        margin-bottom: 0;
    }
    .enrollment-subclass-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        cursor: pointer;
        user-select: none;
        background: #fff;
    }
    .enrollment-subclass-header:hover {
        background: #f8f9fa;
    }
    .enrollment-subclass-title {
        font-weight: 600;
        color: #212529;
        margin: 0;
        font-size: 0.9rem;
    }
    .enrollment-subclass-widget .enrollment-toggle-icon {
        color: #940000;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .enrollment-subclass-widget.is-expanded .enrollment-toggle-icon {
        transform: rotate(180deg);
    }
    .enrollment-subclass-details {
        display: none;
        padding: 0.65rem 0.85rem 0.85rem;
        background: #fafafa;
        border-top: 1px solid #eee;
    }
    .enrollment-subclass-widget.is-expanded .enrollment-subclass-details {
        display: block;
    }
    .enrollment-subclass-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0;
        padding: 0.75rem 0.9rem;
        margin-bottom: 0.5rem;
    }
    .enrollment-subclass-item:last-child {
        margin-bottom: 0;
    }
    .enrollment-subclass-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
    }
    .enrollment-period-meta {
        color: #555;
        line-height: 1.55;
        border-top: 1px dashed #e0e0e0;
        padding-top: 0.45rem;
        margin-top: 0.35rem;
    }
    .enrollment-period-meta i {
        color: #940000;
        margin-right: 0.25rem;
    }
    .enrollment-badge-active { background: #28a745; }
    .enrollment-badge-completed { background: #6c757d; }
    .enrollment-badge-repeated { background: #fd7e14; }
    .enrollment-badge-graduated { background: #17a2b8; }
    .enrollment-badge-transferred { background: #0d6efd; }
    .enrollment-badge-dropped { background: #dc3545; }
    .enrollment-badge-cancelled { background: #495057; }
    .enrollment-badge-other { background: #940000; }
    .promotion-badge-pending { background: #6c757d; }
    .promotion-badge-promoted { background: #28a745; }
    .promotion-badge-repeated { background: #fd7e14; }
    .promotion-badge-graduated { background: #17a2b8; }
    .promotion-badge-transferred { background: #0d6efd; }
    .promotion-badge-truancy { background: #6f42c1; }
    .promotion-badge-dropout { background: #dc3545; }
    .promotion-badge-other { background: #940000; }
    #viewStudentModal .badge.enrollment-badge-active,
    #viewStudentModal .badge.enrollment-badge-completed,
    #viewStudentModal .badge.enrollment-badge-repeated,
    #viewStudentModal .badge.enrollment-badge-graduated,
    #viewStudentModal .badge.enrollment-badge-other {
        font-weight: 600;
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
        border-radius: 0;
        padding: 0.45rem 0.7rem;
    }
    
    .widget-toggle-icon {
        transition: transform 0.3s ease;
    }

    /* School Details Card Styles (like manage_school) */
    .school-details-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 25px;
    }

    .school-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .school-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    /* Custom Modal Width and Scroll Fix */
    @media (min-width: 1200px) {
        .modal-xl {
            max-width: 95% !important;
        }
    }
    .modal-body-scrollable {
        max-height: 80vh !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    
    .school-logo-preview {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }

    .school-logo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .school-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .info-item i {
        color: #6c757d;
        margin-right: 10px;
        margin-top: 3px;
        font-size: 18px;
        width: 20px;
    }

    .info-item-content {
        flex: 1;
    }

    .info-item-label {
        font-size: 0.75rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }

    .info-item-value {
        font-size: 0.95rem;
        color: #212529;
        font-weight: 500;
    }

    /* No Data Available Message Styles */
    .no-data-message {
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .no-data-message i {
        opacity: 0.5;
    }

    /* Student list / grid layout (like manage_store) */
    .section-title {
        font-weight: 600;
        margin-bottom: 12px;
        color: #940000;
        font-size: 1.05rem;
        font-family: "Century Gothic", Arial, sans-serif;
    }
    .student-section-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
    }
    .student-view-toggle .btn.active {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .student-data-wrapper .student-grid-view { display: none; }
    .student-data-wrapper[data-active-view="list"] .student-list-view { display: block !important; }
    .student-data-wrapper[data-active-view="list"] .student-grid-view { display: none !important; }
    .student-data-wrapper[data-active-view="grid"] .student-list-view { display: none !important; }
    .student-data-wrapper[data-active-view="grid"] .student-grid-view { display: block !important; }

    .student-grid-search-wrap {
        width: 100%;
        margin-bottom: 14px;
    }
    .student-grid-search-wrap .form-control {
        width: 100%;
        border-radius: 0 !important;
        padding: 10px 14px;
    }
    .student-grid-search-wrap .form-control:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }

    .students-widget-grid {
        display: flex;
        flex-wrap: wrap;
        margin-left: -8px;
        margin-right: -8px;
    }
    .students-widget-grid > [class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
        margin-bottom: 16px;
    }

    .student-widget {
        width: 100%;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        font-family: "Century Gothic", Arial, sans-serif;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .student-widget-header {
        background: #940000;
        padding: 16px;
        text-align: center;
        position: relative;
    }

    .student-widget-header .student-widget-actions-wrap {
        position: absolute;
        top: 8px;
        right: 8px;
        margin: 0;
        padding: 0;
        border: 0;
        justify-content: flex-end;
    }

    .student-widget-header .student-action-toggle {
        background: rgba(255,255,255,0.95);
        border-color: rgba(255,255,255,0.5);
        color: #940000;
    }

    .student-widget-header .student-action-dropdown.show .student-action-toggle,
    .student-widget-header .student-action-toggle:hover {
        background: #fff;
        color: #940000;
        border-color: #fff;
    }

    .student-widget-header .student-action-dropdown .dropdown-menu {
        right: 0;
        left: auto;
    }

    .student-widget-header img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.35);
        background: #fff;
    }

    .student-widget-body {
        padding: 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .student-widget-body h3 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 4px;
        color: #222;
        line-height: 1.3;
    }

    .student-widget-body .form {
        color: #940000;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }

    .student-widget-body .info p {
        margin: 0 0 4px;
        font-size: 0.84rem;
        color: #555;
    }

    .student-widget-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f0f0f0;
    }

    .student-grid-empty {
        border: 1px dashed #dee2e6;
        padding: 2rem 1rem;
        text-align: center;
        color: #6c757d;
        width: 100%;
    }

    .student-grid-sentinel {
        width: 100%;
        text-align: center;
        padding: 16px 8px 8px;
        color: #888;
        font-size: 0.85rem;
    }
    .student-grid-sentinel .spinner-border {
        width: 1.25rem;
        height: 1.25rem;
        border-width: 0.15em;
        color: #940000;
    }
    .not-enrolled-bulk-bar {
        padding: 10px 12px;
        background: #fff8f8;
        border: 1px solid #f1c0c0;
        border-left: 4px solid #940000;
        border-radius: 0;
    }
    .not-enrolled-check-col {
        vertical-align: middle !important;
    }
    #studentsTable:not(.not-enrolled-bulk-mode) .not-enrolled-check-cell {
        display: none;
    }
    #studentsTable.not-enrolled-bulk-mode thead .not-enrolled-check-col {
        display: table-cell !important;
    }
    .student-widget-select-check {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
        margin: 0;
    }
    .student-widget-select-check input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .student-widget.not-enrolled-selectable {
        position: relative;
    }

    .student-list-view .dataTables_wrapper .dataTables_filter input,
    .student-list-view .dataTables_wrapper .dataTables_length select {
        border-radius: 0 !important;
        border: 1px solid #ced4da;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }
    .student-list-view .dataTables_wrapper .dataTables_filter input:focus,
    .student-list-view .dataTables_wrapper .dataTables_length select:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
    }
    .student-list-view .dataTables_wrapper .page-item.active .page-link {
        background-color: #940000;
        border-color: #940000;
        color: #fff;
    }
    .student-list-view .dataTables_wrapper .page-link {
        color: #940000;
        border-radius: 0 !important;
    }
    .student-list-view .dataTables_wrapper .page-item.disabled .page-link {
        color: #6c757d;
    }
    .student-list-view .dataTables_info,
    .student-list-view .dataTables_length,
    .student-list-view .dataTables_filter,
    .student-list-view .dataTables_paginate {
        font-size: 0.85rem;
        margin-top: 10px;
        margin-bottom: 10px;
        padding-top: 4px;
    }
    .student-list-view .dataTables_wrapper .row {
        align-items: center;
    }
    .student-list-view .dataTables_filter {
        text-align: right;
    }
    .student-list-view .dataTables_paginate {
        text-align: right;
    }
    .student-list-view .dataTables_length label,
    .student-list-view .dataTables_filter label {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        margin: 0;
        flex-wrap: wrap;
    }
    .student-list-view .dataTables_filter label {
        justify-content: flex-end;
    }
    .student-list-view .table th {
        white-space: nowrap;
        font-size: 0.85rem;
    }
    .student-list-view .table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .student-list-photo {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e0e0e0;
    }
    .enrollment-status-pill {
        display: inline-block;
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 700;
        font-family: 'Century Gothic', CenturyGothic, AppleGothic, sans-serif;
        border-radius: 0;
        color: #fff !important;
        line-height: 1.2;
        white-space: nowrap;
        letter-spacing: 0.02em;
        text-transform: capitalize;
    }
    .student-list-view .enrollment-status-pill {
        font-size: 0.78rem;
        padding: 0.35rem 0.6rem;
    }
    .student-widget-body .enrollment-status-row {
        margin: 0.25rem 0 0.75rem;
    }
    .student-widget-body .enrollment-status-pill {
        font-size: 0.85rem;
        padding: 0.45rem 0.85rem;
    }
    .student-list-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    /* White-smoke statistics cards */
    .student-stat-card {
        background: #f5f5f5;
        border: 1px solid #ececec;
        border-radius: 0;
        color: #333;
        height: 100%;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .student-stat-card h4 {
        color: #940000;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .student-stat-card small {
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-size: 0.72rem;
    }
    .student-stat-card:hover {
        border-color: #d8d8d8;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    /* Actions toggle dropdown */
    .student-action-dropdown {
        display: inline-block;
        position: relative;
    }
    .student-action-toggle {
        padding: 4px 12px;
        color: #555;
        background: #fff;
        border: 1px solid #dee2e6;
        line-height: 1.2;
        border-radius: 0;
        cursor: pointer;
    }
    .student-action-toggle:hover,
    .student-action-dropdown.show .student-action-toggle {
        background: #f5f5f5;
        color: #940000;
        border-color: #ccc;
    }
    .student-action-dropdown .dropdown-menu {
        min-width: 190px;
        padding: 4px 0;
        font-size: 0.875rem;
        border-radius: 0;
        border: 1px solid #dee2e6;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        z-index: 1050;
        background: #fff;
        margin-top: 2px;
    }
    .student-action-dropdown.show > .dropdown-menu {
        display: block;
    }
    .student-action-dropdown .dropdown-item {
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        text-decoration: none;
        border: 0;
        background: transparent;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    .student-action-dropdown .dropdown-item:hover {
        background: #f5f5f5;
        color: #940000;
    }
    .student-action-dropdown .dropdown-item i {
        width: 18px;
        text-align: center;
    }
    .student-action-dropdown .dropdown-item.text-danger {
        color: #dc3545;
    }
    .student-action-dropdown .dropdown-item.text-danger:hover {
        background: #fff5f5;
        color: #b02a37;
    }
    .student-actions-cell {
        width: 70px;
        text-align: center;
        white-space: nowrap;
    }
    .student-widget .student-action-dropdown {
        margin-top: auto;
        align-self: flex-end;
    }
    .student-widget-actions-wrap {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: flex-end;
    }

    /* Header page actions toggle */
    .student-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
    }
    .student-page-header h4 {
        margin: 0;
        flex: 1;
        min-width: 0;
    }
    .student-page-actions-dropdown {
        position: relative;
        flex-shrink: 0;
    }
    .student-page-actions-toggle {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dee2e6;
        color: #940000;
        font-size: 1.25rem;
        cursor: pointer;
        border-radius: 0;
        padding: 0;
    }
    .student-page-actions-toggle:hover,
    .student-page-actions-dropdown.show .student-page-actions-toggle {
        background: #f5f5f5;
        border-color: #940000;
        color: #940000;
    }
    .student-page-actions-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        min-width: 210px;
        background: #fff;
        border: 1px solid #dee2e6;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 1060;
        padding: 4px 0;
    }
    .student-page-actions-dropdown.show .student-page-actions-menu {
        display: block;
    }
    .student-page-actions-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        border: 0;
        background: transparent;
        color: #333;
        text-align: left;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
    }
    .student-page-actions-menu .dropdown-item:hover {
        background: #f5f5f5;
        color: #940000;
    }
    .student-page-actions-menu .dropdown-item i {
        width: 18px;
        text-align: center;
    }
    @media (max-width: 767.98px) {
        .student-page-header h4 {
            font-size: 1.15rem;
        }
    }

    /* System modals: whitesmoke + #940000 only */
    .modal-content {
        border-radius: 0 !important;
        border: 2px solid #940000 !important;
        background: #fff;
    }
    .modal-header,
    .modal .modal-header,
    .modal-header.bg-primary-custom,
    .modal-header.bg-warning,
    .modal-header.bg-success,
    .modal-header.bg-info,
    .modal-header.bg-danger,
    .modal-header.bg-primary {
        background: #f5f5f5 !important;
        color: #940000 !important;
        border-bottom: 2px solid #940000 !important;
        border-radius: 0 !important;
    }
    .modal-header .modal-title,
    .modal-header .btn-close,
    .modal-header .close,
    .modal-header i {
        color: #940000 !important;
    }
    .modal-header .btn-close {
        filter: none;
        opacity: 1 !important;
        box-shadow: none !important;
        background: transparent !important;
        width: 1.5rem;
        height: 1.5rem;
        padding: 0.5rem;
        z-index: 5;
        position: relative;
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    .modal-header .btn-close::before {
        content: "×";
        font-size: 1.75rem;
        line-height: 1;
        color: #940000;
        font-weight: 700;
    }
    /* Hide default BS5 SVG glyph if present, use × so it works on BS4 too */
    .modal-header .btn-close {
        background-image: none !important;
    }
    .modal-header .btn-close-white {
        filter: none !important;
    }
    .modal-footer {
        background: #f5f5f5 !important;
        border-top: 1px solid #e0e0e0 !important;
        border-radius: 0 !important;
    }
    .modal .btn-primary,
    .modal .btn-success,
    .modal .btn-warning,
    .modal .btn-info,
    .modal .btn-danger:not(.btn-outline-danger) {
        background-color: #940000 !important;
        border-color: #940000 !important;
        color: #fff !important;
        border-radius: 0 !important;
    }
    .modal .btn-primary:hover,
    .modal .btn-success:hover,
    .modal .btn-warning:hover,
    .modal .btn-info:hover {
        background-color: #7a0000 !important;
        border-color: #7a0000 !important;
        color: #fff !important;
    }
    .modal .btn-secondary,
    .modal .btn-outline-secondary,
    .modal .btn-light {
        background: #f5f5f5 !important;
        border: 1px solid #940000 !important;
        color: #940000 !important;
        border-radius: 0 !important;
    }
    .modal .alert {
        border-radius: 0 !important;
        background: #f5f5f5 !important;
        border: 1px solid #940000 !important;
        color: #333 !important;
    }
    .student-swal-popup {
        border-radius: 0 !important;
        border: 2px solid #940000 !important;
        font-family: "Century Gothic", Arial, sans-serif !important;
    }
    .student-swal-confirm,
    .student-swal-cancel {
        border-radius: 0 !important;
    }
    /* SweetAlert must sit ABOVE Bootstrap modals so "type yes" is usable */
    .swal2-container,
    .student-swal-container {
        z-index: 20000 !important;
    }
    .move-step-badge {
        background: #f5f5f5 !important;
        color: #666 !important;
        border: 1px solid #ccc;
        border-radius: 0 !important;
        font-weight: 600;
        padding: 6px 10px;
    }
    .move-step-badge.active {
        background: #940000 !important;
        color: #fff !important;
        border-color: #940000;
    }

    /* Manual Modal Styles (when Bootstrap JS is not loaded) */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1050;
        display: none;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
    }

    .modal.show {
        display: block !important;
    }

    .modal-dialog {
        position: relative;
        width: auto;
        margin: 0.5rem;
        pointer-events: none;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 0.3rem;
        outline: 0;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: #000;
    }

    .modal-backdrop.show {
        opacity: 0.5;
    }

    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 500px;
            margin: 1.75rem auto;
        }
    }

    @media (min-width: 992px) {
        .modal-lg {
            max-width: 800px;
        }

        .modal-xl {
            max-width: 1140px;
        }
    }

    /* Select2 Custom Styles */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 9999;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* Ensure Select2 dropdown is visible in modals */
    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 10000 !important;
    }

    /* Search Input Styles */
    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 1;
    }

    .search-input-wrapper #studentSearchInput {
        padding-left: 40px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        height: 45px;
        font-size: 0.95rem;
    }

    .search-input-wrapper #studentSearchInput:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
    }

    .search-input-wrapper #studentSearchInput::placeholder {
        color: #adb5bd;
        font-style: italic;
    }

    /* Status Filter Select Styles */
    #statusFilter {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        height: 45px;
        font-size: 0.95rem;
    }

    #statusFilter:focus {
        border-color: #940000;
        box-shadow: 0 0 0 0.2rem rgba(148, 0, 0, 0.25);
        outline: none;
    }

    /* Student ID Card Styles */
    .id-card-container {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        min-height: 500px;
        width: 100%;
    }

    .student-id-card {
        width: 100%;
        max-width: 450px;
        min-height: 270px;
        max-height: 20000px;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 2px solid #940000;
        border-radius: 0;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: visible;
        transform: scale(1.2);
        transform-origin: center;
        display: flex;
        flex-direction: column;
    }

    .student-id-card {
        --card-color: #940000;
    }

    .student-id-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color, #940000);
    }

    .id-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .id-card-school-logo {
        width: 60px;
        height: 60px;
        border-radius: 0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }

    .id-card-school-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .id-card-school-info {
        flex: 1;
        margin-left: 15px;
    }

    .id-card-school-name {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .id-card-school-reg {
        font-size: 0.75rem;
        color: #6c757d;
        margin: 3px 0 0 0;
    }

    .id-card-body {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        flex: 0 1 auto;
        min-height: 0;
    }

    .id-card-photo-section {
        flex-shrink: 0;
    }

    .id-card-photo {
        width: 110px;
        height: 130px;
        border-radius: 0;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #940000;
    }

    .id-card-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .id-card-details {
        flex: 1;
    }

    .id-card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #212529;
        margin: 0 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .id-card-info-row {
        display: flex;
        margin-bottom: 6px;
        align-items: center;
    }

    .id-card-info-label {
        font-weight: 600;
        color: #495057;
        width: 100px;
        font-size: 0.75rem;
    }

    .id-card-info-value {
        color: #212529;
        font-size: 0.8rem;
        flex: 1;
    }

    .id-card-footer {
        text-align: center;
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px solid #e9ecef;
        flex-shrink: 0;
        width: 100%;
        display: block;
    }

    .id-card-footer-text {
        font-size: 0.7rem;
        color: #6c757d;
        font-style: italic;
        margin: 0;
        display: block;
    }

    .id-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e9ecef;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .student-id-card,
        .student-id-card *,
        .id-card-container {
            visibility: visible !important;
        }

        .id-card-container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 420px;
            height: 270px;
        }

        .id-card-container {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 450px;
            max-width: 100%;
        }

        .student-id-card {
            position: relative;
            width: 100%;
            max-width: 450px;
            min-height: 270px;
            max-height: 20000px;
            box-shadow: none;
            border: 2px solid #940000;
            transform: scale(1);
            margin: 0;
            page-break-after: always;
        }

        .modal-header,
        .modal-footer,
        #printIdCardBtn,
        #downloadIdCardBtn,
        .id-card-color-picker,
        .modal-backdrop,
        body > *:not(.id-card-container):not(.student-id-card) {
            display: none !important;
            visibility: hidden !important;
        }

        .modal,
        .modal-dialog,
        .modal-content,
        .modal-body {
            position: static !important;
            display: block !important;
            visibility: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background: white !important;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    }
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<div class="container-fluid mt-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            @php
                $canCreate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_create');
                $canUpdate = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_update');
                $canDelete = ($user_type ?? '') == 'Admin' || ($teacherPermissions ?? collect())->contains('student_delete');
            @endphp
            <div class="student-page-header">
                <h4 class="mb-0 text-primary-custom">
                    <i class="bi bi-people-fill"></i> Manage Students
                </h4>
                <div class="student-page-actions-dropdown" id="studentPageActionsDropdown">
                    <button type="button" class="student-page-actions-toggle" id="studentPageActionsToggle" title="Actions" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="student-page-actions-menu" role="menu">
                        @if($canCreate)
                        <button class="dropdown-item" id="addStudentBtn" type="button" role="menuitem">
                            <i class="bi bi-person-plus" style="color:#940000;"></i> Register
                        </button>
                        <button class="dropdown-item" id="quickRegisterStudentBtn" type="button" role="menuitem">
                            <i class="bi bi-lightning-charge-fill" style="color:#940000;"></i> Quick Registration
                        </button>
                        <button class="dropdown-item" id="quickTemplateDownloadBtn" type="button" role="menuitem">
                            <i class="bi bi-download" style="color:#940000;"></i> Quick Template
                        </button>
                        <button class="dropdown-item" id="quickImportStudentBtn" type="button" role="menuitem">
                            <i class="bi bi-file-earmark-excel" style="color:#940000;"></i> Quick Import
                        </button>
                        <a href="{{ route('quick_register_export') }}" class="dropdown-item" target="_blank" role="menuitem">
                            <i class="bi bi-file-earmark-spreadsheet" style="color:#940000;"></i> Export Unenrolled
                        </a>
                        <a href="{{ route('download_student_template') }}" class="dropdown-item" target="_blank" role="menuitem">
                            <i class="bi bi-download" style="color:#940000;"></i> Template
                        </a>
                        <button class="dropdown-item" id="importBtn" type="button" role="menuitem">
                            <i class="bi bi-file-earmark-excel" style="color:#940000;"></i> Import
                        </button>
                        @endif
                        @if(($canCreate ?? false) || ($canUpdate ?? false))
                        <button class="dropdown-item" id="enrollStudentBtn" type="button" role="menuitem">
                            <i class="bi bi-person-check-fill" style="color:#940000;"></i> Enroll Student
                        </button>
                        @endif
                        @if($canUpdate ?? false)
                        <button class="dropdown-item" id="moveStudentsBtn" type="button" role="menuitem">
                            <i class="bi bi-arrow-left-right" style="color:#940000;"></i> Move Students
                        </button>
                        <button class="dropdown-item" id="undoMoveBatchesBtn" type="button" role="menuitem">
                            <i class="bi bi-arrow-counterclockwise" style="color:#940000;"></i> Undo Move
                        </button>
                        @endif
                        @if($canDelete ?? false)
                        <button class="dropdown-item text-danger" id="deleteAllNotEnrolledBtn" type="button" role="menuitem">
                            <i class="bi bi-trash"></i> Delete All Not Enrolled
                        </button>
                        @endif
                        <button class="dropdown-item" id="exportPdfBtn" type="button" role="menuitem" title="Export to PDF">
                            <i class="bi bi-file-pdf" style="color:#940000;"></i> Export PDF
                        </button>
                        <button class="dropdown-item" id="exportExcelBtn" type="button" role="menuitem" title="Export to Excel">
                            <i class="bi bi-file-earmark-spreadsheet" style="color:#940000;"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <!-- Filters Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">-- All Classes --</option>
                        <!-- Will be loaded via AJAX -->
                    </select>
                    </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Subclass</label>
                    <select class="form-select" id="subclassFilter">
                        <option value="">-- All Subclasses --</option>
                        <!-- Will be loaded via AJAX -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Gender</label>
                    <select class="form-select" id="genderFilter">
                        <option value="">-- All Genders --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2">Health Condition</label>
                    <select class="form-select" id="healthFilter">
                        <option value="">-- All --</option>
                        <option value="good">Good Health</option>
                        <option value="bad">Bad Health</option>
                    </select>
            </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2" for="statusFilter" title="Enrollment status for the selected academic year">Enrollment Status</label>
                    <select class="form-select" id="statusFilter" title="Enrollment status for the selected academic year">
                        <option value="Active" selected>Active</option>
                        <option value="NotEnrolled">Not Enrolled</option>
                        <option value="Completed">Completed</option>
                        <option value="Transferred">Transferred</option>
                        <option value="Dropped">Dropped</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold mb-2" for="promotionFilter" title="Promotion status for the selected academic year">Promotion Status</label>
                    <select class="form-select" id="promotionFilter" title="Promotion status for the selected academic year">
                        <option value="">-- All --</option>
                        <option value="Pending">Pending</option>
                        <option value="Promoted">Promoted</option>
                        <option value="Repeated">Repeated</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Transferred">Transferred</option>
                        <option value="Truancy">Truancy</option>
                        <option value="Dropout">Dropout</option>
                    </select>
                </div>
    </div>

            <!-- Statistics Display -->
            <div class="row g-3" id="statisticsSection">
                <div class="col-6 col-md">
                    <div class="student-stat-card text-center p-3">
                        <h4 class="mb-0" id="statTotalStudents">0</h4>
                        <small>Total Students</small>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="student-stat-card text-center p-3">
                        <h4 class="mb-0" id="statMaleCount">0</h4>
                        <small>Male</small>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="student-stat-card text-center p-3">
                        <h4 class="mb-0" id="statFemaleCount">0</h4>
                        <small>Female</small>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="student-stat-card text-center p-3">
                        <h4 class="mb-0" id="statGoodHealth">0</h4>
                        <small>Good Health</small>
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="student-stat-card text-center p-3">
                        <h4 class="mb-0" id="statBadHealth">0</h4>
                        <small>Bad Health</small>
                    </div>
                </div>
            </div>
                    </div>
                </div>

    <!-- Students List / Grid Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="student-section-toolbar">
                <div class="section-title mb-0"><i class="bi bi-people-fill"></i> Students</div>
                <div class="btn-group btn-group-sm student-view-toggle" role="group" aria-label="View mode">
                    <button type="button" class="btn btn-outline-secondary" data-view="list" title="List view">
                        <i class="bi bi-list-ul"></i> <span class="d-none d-sm-inline">List</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="grid" title="Grid view">
                        <i class="bi bi-grid-3x3-gap"></i> <span class="d-none d-sm-inline">Grid</span>
                    </button>
                </div>
            </div>

            @if($canDelete ?? false)
            <div id="notEnrolledBulkBar" class="not-enrolled-bulk-bar d-none mb-3">
                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="notEnrolledSelectAllBtn">
                        <i class="bi bi-check2-square"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="notEnrolledClearSelectionBtn">
                        Clear
                    </button>
                    <span class="text-muted small" id="notEnrolledSelectedCount">0 selected</span>
                    <button type="button" class="btn btn-sm btn-danger ml-auto" id="deleteSelectedNotEnrolledBtn" disabled>
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            @endif

            <div class="student-data-wrapper" id="studentsDataWrapper" data-active-view="list">
                <div class="student-list-view">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped w-100" id="studentsTable">
                            <thead class="bg-light">
                                <tr>
                                    @if($canDelete ?? false)
                                    <th class="not-enrolled-check-col d-none text-center" width="40" title="Select on this page">
                                        <input type="checkbox" class="form-check-input m-0" id="notEnrolledSelectPageCb" title="Select all on this page" aria-label="Select all on this page">
                                    </th>
                                    @endif
                                    <th width="40">#</th>
                                    <th width="50">Photo</th>
                                    <th>Admission</th>
                                    <th>Full Name</th>
                                    <th>Class</th>
                                    <th>Enrollment Status</th>
                                    <th>Promotion Status</th>
                                    <th>Gender</th>
                                    <th>Parent</th>
                                    <th>Fingerprint</th>
                                    <th class="student-actions-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="student-grid-view">
                    <div class="student-grid-search-wrap">
                        <input type="search" class="form-control" id="studentGridSearch" placeholder="Search by Admission No, Name, Class, Parent..." autocomplete="off">
                    </div>
                    <div id="studentsWidgetGrid" class="students-widget-grid"></div>
                    <div id="studentGridSentinel" class="student-grid-sentinel d-none">
                        <div class="spinner-border" role="status"></div>
                        <div class="mt-2">Loading more students...</div>
                    </div>
                    <div id="studentGridEnd" class="student-grid-sentinel d-none text-muted">All students loaded</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary-custom text-white">
                <h5 class="modal-title" id="addStudentModalLabel">
                    <i class="bi bi-person-plus"></i> Register New Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body modal-body-scrollable">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="admission_date" class="form-label">Admission Date</label>
                            <input type="date" class="form-control" id="admission_date" name="admission_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subclassID" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="subclassID" name="subclassID" required>
                                <option value="">Choose a class...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parentID" class="form-label">Parent</label>
                            <select class="form-select" id="parentID" name="parentID">
                                <option value="">Choose a parent...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admission_number" class="form-label">Admission Number</label>
                            <input type="text" class="form-control" id="admission_number" name="admission_number" placeholder="Leave empty to auto-generate">
                            <small class="text-muted">If left empty, admission number will be auto-generated</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <small class="text-muted">Max size: 10MB (jpg, jpeg, png)</small>
                    </div>

                    <!-- Additional Particulars Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-vcard"></i> Additional Particulars</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="birth_certificate_number" class="form-label">Birth Certificate No.</label>
                            <input type="text" class="form-control" id="birth_certificate_number" name="birth_certificate_number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="religion" name="religion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="nationality" name="nationality" value="Tanzanian">
                        </div>
                    </div>

                    <!-- Sponsorship Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-handshake-fill"></i> Sponsorship Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_type" class="form-label">Payment Type</label>
                            <select class="form-select" id="payment_type" name="payment_type">
                                <option value="own">Own Payment</option>
                                <option value="sponsor">Sponsor Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="sponsorSelectContainer" style="display: none;">
                            <label for="sponsor_id" class="form-label">Select Sponsor</label>
                            <select class="form-select" id="sponsor_id" name="sponsor_id">
                                <option value="">Choose a sponsor...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="sponsorshipPercentageContainer" style="display: none;">
                        <label for="sponsorship_percentage" class="form-label">Sponsorship Percentage (%)</label>
                        <input type="number" class="form-control" id="sponsorship_percentage" name="sponsorship_percentage" min="0" max="100" step="any" placeholder="e.g., 50.00">
                    </div>

                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="mb-3">
                        <label for="general_health_condition" class="form-label">General Health Condition</label>
                        <textarea class="form-control" id="general_health_condition" name="general_health_condition" rows="2" placeholder="Healthy, Good, etc."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_disability" name="has_disability" value="1">
                                <label class="form-check-label" for="has_disability">Has Disability</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_chronic_illness" name="has_chronic_illness" value="1">
                                <label class="form-check-label" for="has_chronic_illness">Has Chronic Illness</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="is_disabled">Disabled</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="has_epilepsy">Epilepsy</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="has_allergies">Allergies</label>
                            </div>
                        </div>
                    </div>
                    <div id="addHealthDetailsSection">
                        <div class="mb-3" id="disabilityDetailsContainer" style="display: none;">
                            <label for="disability_details" class="form-label">Disability Details</label>
                            <textarea class="form-control" id="disability_details" name="disability_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="chronicIllnessDetailsContainer" style="display: none;">
                            <label for="chronic_illness_details" class="form-label">Chronic Illness Details</label>
                            <textarea class="form-control" id="chronic_illness_details" name="chronic_illness_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="allergiesDetailsContainer" style="display: none;">
                            <label for="allergies_details" class="form-label">Allergies Details</label>
                            <textarea class="form-control" id="allergies_details" name="allergies_details" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="immunization_details" class="form-label">Immunization Details</label>
                        <textarea class="form-control" id="immunization_details" name="immunization_details" rows="2"></textarea>
                    </div>

                    <!-- Emergency Contact Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-telephone"></i> Emergency Contact</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" required>
                        </div>
                    </div>

                    <!-- Official Use Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-badge"></i> Official Use & Declaration</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="declaration_date" class="form-label">Declaration Date</label>
                            <input type="date" class="form-control" id="declaration_date" name="declaration_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registering_officer_name" class="form-label">Registering Officer</label>
                            <input type="text" class="form-control" id="registering_officer_name" name="registering_officer_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registering_officer_title" class="form-label">Officer Title</label>
                            <input type="text" class="form-control" id="registering_officer_title" name="registering_officer_title">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-check-circle"></i> Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Details Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95%; width: 95%;">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-primary-custom text-white" style="border-radius: 0;">
                <h5 class="modal-title" id="viewStudentModalLabel">
                    <i class="bi bi-person-badge"></i> Student Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="studentDetailsContent" style="min-height: 70vh;">
                <!-- Content will be loaded dynamically with tabs -->
            </div>
            <div class="modal-footer" style="border-radius: 0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body modal-body-scrollable">
                    <input type="hidden" id="editStudentID" name="studentID">

                    <!-- Photo Preview -->
                    <div class="mb-3 text-center" id="editPhotoPreview"></div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_gender" class="form-label">Gender</label>
                            <select class="form-select" id="edit_gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_admission_date" class="form-label">Admission Date</label>
                            <input type="date" class="form-control" id="edit_admission_date" name="admission_date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_subclassID" class="form-label">Class</label>
                            <select class="form-select" id="edit_subclassID" name="subclassID">
                                <option value="">Choose a class...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_parentID" class="form-label">Parent</label>
                            <select class="form-select" id="edit_parentID" name="parentID">
                                <option value="">Choose a parent...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_admission_number" class="form-label">Admission Number</label>
                            <input type="text" class="form-control" id="edit_admission_number" name="admission_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Graduated">Graduated</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Applied">Applied</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    
                    <!-- Photo Section -->
                    <div class="mb-3">
                        <label for="edit_photo" class="form-label">Update Photo</label>
                        <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                        <small class="text-muted">Max size: 10MB (jpg, jpeg, png). Leave empty to keep current photo.</small>
                    </div>

                    <!-- Additional Particulars Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-vcard"></i> Additional Particulars</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_birth_certificate_number" class="form-label">Birth Certificate Number</label>
                            <input type="text" class="form-control" id="edit_birth_certificate_number" name="birth_certificate_number">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_religion" class="form-label">Religion</label>
                            <input type="text" class="form-control" id="edit_religion" name="religion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control" id="edit_nationality" name="nationality">
                        </div>
                    </div>
                    
                    <!-- Sponsorship Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-handshake-fill"></i> Sponsorship Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_payment_type" class="form-label">Payment Type</label>
                            <select class="form-select" id="edit_payment_type" name="payment_type">
                                <option value="own">Own Payment</option>
                                <option value="sponsor">Sponsor Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="editSponsorSelectContainer" style="display: none;">
                            <label for="edit_sponsor_id" class="form-label">Select Sponsor</label>
                            <select class="form-select" id="edit_sponsor_id" name="sponsor_id">
                                <option value="">Choose a sponsor...</option>
                                <!-- Will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="editSponsorshipPercentageContainer" style="display: none;">
                        <label for="edit_sponsorship_percentage" class="form-label">Sponsorship Percentage (%)</label>
                        <input type="number" class="form-control" id="edit_sponsorship_percentage" name="sponsorship_percentage" min="0" max="100" step="any" onfocus="this.select()" placeholder="e.g., 50.00">
                        <small class="text-muted">Enter the percentage of fees covered by the sponsor (0-100)</small>
                    </div>

                    <!-- Health Information Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="mb-3">
                        <label for="edit_general_health_condition" class="form-label">General Health Condition</label>
                        <textarea class="form-control" id="edit_general_health_condition" name="general_health_condition" rows="2" placeholder="Describe general health condition"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_disability" name="has_disability" value="1">
                                <label class="form-check-label" for="edit_has_disability">Has Disability</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_chronic_illness" name="has_chronic_illness" value="1">
                                <label class="form-check-label" for="edit_has_chronic_illness">Has Chronic Illness</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_disabled" name="is_disabled" value="1">
                                <label class="form-check-label" for="edit_is_disabled">Disabled</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_epilepsy" name="has_epilepsy" value="1">
                                <label class="form-check-label" for="edit_has_epilepsy">Epilepsy</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_allergies" name="has_allergies" value="1">
                                <label class="form-check-label" for="edit_has_allergies">Allergies</label>
                            </div>
                        </div>
                    </div>

                    <div id="editHealthDetailsSection">
                        <div class="mb-3" id="editDisabilityDetailsContainer" style="display: none;">
                            <label for="edit_disability_details" class="form-label">Disability Details</label>
                            <textarea class="form-control" id="edit_disability_details" name="disability_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="editChronicIllnessDetailsContainer" style="display: none;">
                            <label for="edit_chronic_illness_details" class="form-label">Chronic Illness Details</label>
                            <textarea class="form-control" id="edit_chronic_illness_details" name="chronic_illness_details" rows="2"></textarea>
                        </div>
                        <div class="mb-3" id="editAllergiesDetailsContainer" style="display: none;">
                            <label for="edit_allergies_details" class="form-label">Allergies Details</label>
                            <textarea class="form-control" id="edit_allergies_details" name="allergies_details" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_immunization_details" class="form-label">Immunization Details</label>
                        <textarea class="form-control" id="edit_immunization_details" name="immunization_details" rows="2"></textarea>
                    </div>
                    
                    <!-- Emergency Contact Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-telephone"></i> Emergency Contact</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_name" class="form-label">Contact Name</label>
                            <input type="text" class="form-control" id="edit_emergency_contact_name" name="emergency_contact_name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_relationship" class="form-label">Relationship</label>
                            <input type="text" class="form-control" id="edit_emergency_contact_relationship" name="emergency_contact_relationship">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_emergency_contact_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_emergency_contact_phone" name="emergency_contact_phone">
                        </div>
                    </div>

                    <!-- Official Use Section -->
                    <hr class="my-4">
                    <h6 class="mb-3 text-primary-custom"><i class="bi bi-person-badge"></i> Official Use & Declaration</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_declaration_date" class="form-label">Declaration Date</label>
                            <input type="date" class="form-control" id="edit_declaration_date" name="declaration_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_registering_officer_name" class="form-label">Registering Officer</label>
                            <input type="text" class="form-control" id="edit_registering_officer_name" name="registering_officer_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_registering_officer_title" class="form-label">Officer Title</label>
                            <input type="text" class="form-control" id="edit_registering_officer_title" name="registering_officer_title">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Student ID Card Modal -->
<div class="modal fade" id="studentIdCardModal" tabindex="-1" aria-labelledby="studentIdCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="studentIdCardModalLabel">
                    <i class="bi bi-card-text"></i> Student Identity Card
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded" style="border: 1px solid #dee2e6;">
                    <div class="d-flex align-items-center">
                        <label for="idCardColorPicker" class="form-label mb-0 me-2 font-weight-bold" style="font-size: 0.9rem;"><i class="bi bi-palette"></i> Card Theme Color:</label>
                        <input type="color" class="form-control form-control-color" id="idCardColorPicker" value="#940000" title="Choose card theme color" style="width: 45px; height: 32px; padding: 2px; cursor: pointer;">
                    </div>
                    <span class="text-muted small"><i class="bi bi-info-circle"></i> Theme applies to borders and header bars</span>
                </div>
                <div id="studentIdCardContent" class="id-card-container">
                    <!-- ID Card will be loaded here via AJAX -->
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-success px-3" id="printIdCardBtn">
                        <i class="bi bi-printer"></i> Print ID Card
                    </button>
                    <button type="button" class="btn btn-primary px-3" id="downloadIdCardBtn">
                        <i class="bi bi-file-pdf"></i> Download ID Card
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Students Modal -->
<div class="modal fade" id="importStudentsModal" tabindex="-1" aria-labelledby="importStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0;">
            <div class="modal-header bg-success text-white" style="border-radius: 0;">
                <h5 class="modal-title" id="importStudentsModalLabel">
                    <i class="bi bi-file-earmark-excel"></i> Import Students from Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importStudentsForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="alert alert-info" style="border-radius: 0; border-left: 4px solid #0dcaf0;">
                        <strong><i class="bi bi-info-circle-fill"></i> Instructions:</strong>
                         <ol class="mb-0 mt-2 ps-3">
                            <li>Download the Excel template first.</li>
                            <li>Fill in the <strong>complete registration details</strong> including parent info, health details, and emergency contacts.</li>
                            <li>Make sure to use the exact Subclass ID listed in the <strong>"Valid Subclasses"</strong> sheet.</li>
                            <li>Upload the completed file to register multiple students at once with full details.</li>
                        </ol>
                    </div>
                    
                    <div class="text-center mb-4 mt-3">
                        <a href="{{ route('download_student_template') }}" class="btn btn-outline-success fw-bold" target="_blank">
                            <i class="bi bi-download"></i> Download Excel Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <label for="excelFile" class="form-label fw-bold">Select Completed Excel Data File <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls,.csv" required style="border-radius: 0;">
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="importSubmitBtn">
                        <i class="bi bi-upload"></i> Upload &amp; Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move Students Wizard Modal -->
<div class="modal fade" id="moveStudentsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Move Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap mb-3" style="gap:8px;" id="moveStepIndicators">
                    <span class="badge move-step-badge active" data-step="1">1. Level &amp; Year</span>
                    <span class="badge move-step-badge" data-step="2">2. Select Students</span>
                    <span class="badge move-step-badge" data-step="3">3. Confirm</span>
                </div>

                <div class="move-step" id="moveStep1">
                    @if(!empty($hasALevel))
                    <div class="mb-3" id="moveLevelWrap">
                        <label class="form-label fw-bold">1. Select level</label>
                        <div class="d-flex flex-wrap" style="gap:10px;">
                            <button type="button" class="btn move-level-btn" data-level="o_level" style="border:1px solid #940000;border-radius:0;background:#fff;color:#940000;">
                                O-Level
                                <div class="small" style="font-weight:400;color:#666;">Form One - Form Four</div>
                            </button>
                            <button type="button" class="btn move-level-btn" data-level="a_level" style="border:1px solid #940000;border-radius:0;background:#fff;color:#940000;">
                                A-Level
                                <div class="small" style="font-weight:400;color:#666;">Form Five &amp; Form Six only</div>
                            </button>
                        </div>
                    </div>
                    @endif

                    <div id="moveYearWrap" class="{{ !empty($hasALevel) ? 'd-none' : '' }}">
                        <label class="form-label fw-bold">{{ !empty($hasALevel) ? '2. Select academic year' : 'Select academic year to move students into' }}</label>
                        <select class="form-select" id="moveTargetYear" style="border-radius:0;">
                            <option value="">-- Select Academic Year --</option>
                        </select>
                    </div>

                    <div class="alert mt-3 mb-0" id="moveYearWarning" style="display:none; background:#f5f5f5; border:1px solid #940000; color:#333;">
                        This action will move selected students to the next class <strong>for the selected academic year</strong>.
                        History of previous enrollments will be kept (status Completed). Already-moved students show with a revert icon.
                        For a custom class (not the next year class), use <strong>Move Student</strong> on one student at a time.
                    </div>
                </div>

                <div class="move-step d-none" id="moveStep2">
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Class</label>
                            <select class="form-select" id="moveFilterClass" style="border-radius:0;">
                                <option value="">-- All Classes --</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Subclass</label>
                            <select class="form-select" id="moveFilterSubclass" style="border-radius:0;">
                                <option value="">-- All Subclasses --</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn w-100" id="moveReloadPreviewBtn" title="Refresh" style="background:#940000;color:#fff;border-radius:0;">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                        <div>
                            <label class="mb-0"><input type="checkbox" id="moveSelectAll"> Select All</label>
                            <span class="text-muted small ms-2" id="moveSelectedCount">0 selected</span>
                        </div>
                        <small class="text-muted">Active enrollments load automatically when you select Class / Subclass.</small>
                    </div>
                    <div class="table-responsive" style="max-height:420px; overflow:auto;">
                        <table class="table table-bordered table-sm mb-0" id="movePreviewTable">
                            <thead class="bg-light" style="position:sticky; top:0; z-index:1;">
                                <tr>
                                    <th width="40"></th>
                                    <th>Admission</th>
                                    <th>Name</th>
                                    <th>Current Class</th>
                                    <th>Will Go To</th>
                                    <th>Action</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="movePreviewBody">
                                <tr><td colspan="7" class="text-center text-muted">Select a class to load active enrollments</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="move-step d-none" id="moveStep3">
                    <div class="p-3 mb-3" style="background:#f5f5f5; border:1px solid #940000;">
                        <strong>Summary</strong>
                        <div id="moveConfirmSummary" class="mt-2"></div>
                    </div>
                    <div id="moveConfirmExpand" class="mb-3">
                        <button type="button" class="btn" id="moveExpandConfirmBtn" style="background:#940000;color:#fff;border-radius:0;">
                            Move Students
                        </button>
                    </div>
                    <div id="moveConfirmBox" class="d-none p-3" style="border:1px dashed #940000; background:#fff;">
                        <p class="mb-2 fw-bold" style="color:#940000;">Are you sure you want to perform this action?</p>
                        <p class="small text-muted">Type <strong>yes</strong> or <strong>no</strong></p>
                        <input type="text" class="form-control mb-2" id="moveConfirmYesNo" placeholder="yes / no" style="border-radius:0;">
                        <div id="moveConfirmWordWrap" class="d-none">
                            <p class="small text-muted">If yes, type the word <strong>yes</strong> again to start moving</p>
                            <input type="text" class="form-control mb-2" id="moveConfirmWord" placeholder="Type yes" style="border-radius:0;">
                            <button type="button" class="btn" id="moveExecuteBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                                Confirm &amp; Move
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-secondary d-none" id="movePrevBtn">Back</button>
                <button type="button" class="btn" id="moveNextBtn" style="background:#940000;color:#fff;" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Undo Move Batches Modal -->
<div class="modal fade" id="undoMoveModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Undo Student Move</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Undo restores previous active enrollments and cancels new ones created by the move.</p>
                <div id="undoMoveBatchesList">Loading...</div>
                <div id="undoMoveConfirmBox" class="d-none p-3 mt-3" style="border:1px dashed #940000; background:#fff;">
                    <p class="mb-2 fw-bold" style="color:#940000;">
                        Undo move batch #<span id="undoMoveConfirmBatchLabel">-</span>?
                    </p>
                    <p class="small text-muted mb-1">This restores students to previous enrollments. Type <strong>yes</strong> or <strong>no</strong></p>
                    <input type="text" class="form-control mb-2" id="undoMoveConfirmYesNo" placeholder="yes / no" style="border-radius:0;" autocomplete="off">
                    <div id="undoMoveConfirmWordWrap" class="d-none">
                        <p class="small text-muted mb-1">If yes, type the word <strong>yes</strong> again to confirm undo</p>
                        <input type="text" class="form-control mb-2" id="undoMoveConfirmWord" placeholder="Type yes" style="border-radius:0;" autocomplete="off">
                        <button type="button" class="btn" id="undoMoveExecuteBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                            Confirm &amp; Undo
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Single Student Move Modal (Internal / External) -->
<div class="modal fade" id="singleMoveStudentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Move Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 mb-3" style="background:#f5f5f5; border:1px solid #940000;">
                    <div><strong id="singleMoveStudentName">-</strong></div>
                    <div class="small text-muted">Current class: <span id="singleMoveCurrentClass">-</span></div>
                </div>

                <label class="form-label fw-bold">Move type</label>
                <div class="d-flex flex-wrap mb-3" style="gap:10px;">
                    <button type="button" class="btn single-move-type-btn" data-type="internal" style="border:1px solid #940000;border-radius:0;background:#fff;color:#940000;">
                        Internal
                        <div class="small" style="font-weight:400;color:#666;">Within same class (subclass/stream)</div>
                    </button>
                    <button type="button" class="btn single-move-type-btn" data-type="external" style="border:1px solid #940000;border-radius:0;background:#fff;color:#940000;">
                        External
                        <div class="small" style="font-weight:400;color:#666;">Change main class (higher = Move, lower = Revert)</div>
                    </button>
                </div>

                <div id="singleMoveDestWrap" class="d-none">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6" id="singleMoveClassCol">
                            <label class="form-label fw-bold">Destination class</label>
                            <select class="form-select" id="singleMoveClass" style="border-radius:0;">
                                <option value="">-- Select class --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Destination subclass</label>
                            <select class="form-select" id="singleMoveSubclass" style="border-radius:0;" disabled>
                                <option value="">-- Select subclass --</option>
                            </select>
                        </div>
                    </div>
                    <div id="singleMoveActionHint" class="alert mb-0" style="display:none; background:#f5f5f5; border:1px solid #940000; color:#333;"></div>
                </div>

                <div id="singleMoveConfirmPanel" class="d-none mt-3 p-3" style="border:1px dashed #940000; background:#fff;">
                    <p class="mb-2 fw-bold" style="color:#940000;" id="singleMoveConfirmTitle">Are you sure you want to perform this action?</p>
                    <p class="small text-muted mb-1">Type <strong>yes</strong> or <strong>no</strong></p>
                    <input type="text" class="form-control mb-2" id="singleMoveConfirmYesNo" placeholder="yes / no" style="border-radius:0;" autocomplete="off">
                    <div id="singleMoveConfirmWordWrap" class="d-none">
                        <p class="small text-muted mb-1">If yes, type the word <strong>yes</strong> again to start moving</p>
                        <input type="text" class="form-control mb-2" id="singleMoveConfirmWord" placeholder="Type yes" style="border-radius:0;" autocomplete="off">
                        <button type="button" class="btn" id="singleMoveExecuteBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                            Confirm &amp; Move
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="singleMoveConfirmBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>Move Student</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Enrollment Status Modal (exit + promotion reporting) -->
<div class="modal fade" id="editEnrollmentStatusModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-journal-x"></i> Edit Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 mb-3" style="background:#f5f5f5; border:1px solid #940000;">
                    <div><strong id="editEnrollmentStudentName">-</strong></div>
                    <div class="small text-muted">Current class: <span id="editEnrollmentCurrentClass">-</span></div>
                </div>
                <p class="small text-muted mb-3">
                    Use when the student leaves the school or stops attending.
                    These update <strong>promotion status</strong> for reporting (government stats / student movement).
                    Class/stream changes use <strong>Move Student</strong>.
                </p>
                <label class="form-label fw-bold">Choose action</label>
                <div class="d-flex flex-wrap mb-3" style="gap:10px;">
                    <button type="button" class="btn edit-enrollment-action-btn" data-action="Truancy" style="border:1px solid #6f42c1;border-radius:0;background:#fff;color:#6f42c1;">
                        Truancy
                        <div class="small" style="font-weight:400;color:#666;">Long-term absentee</div>
                    </button>
                    <button type="button" class="btn edit-enrollment-action-btn" data-action="Dropout" style="border:1px solid #dc3545;border-radius:0;background:#fff;color:#dc3545;">
                        Dropout
                        <div class="small" style="font-weight:400;color:#666;">Stopped schooling</div>
                    </button>
                    <button type="button" class="btn edit-enrollment-action-btn" data-action="Dropped" style="border:1px solid #940000;border-radius:0;background:#fff;color:#940000;">
                        Make as Dropped
                        <div class="small" style="font-weight:400;color:#666;">Expelled / left (records as Dropout)</div>
                    </button>
                    <button type="button" class="btn edit-enrollment-action-btn" data-action="Transferred" style="border:1px solid #0d6efd;border-radius:0;background:#fff;color:#0d6efd;">
                        Make as Transferred
                        <div class="small" style="font-weight:400;color:#666;">Left to another school</div>
                    </button>
                </div>
                <div id="editEnrollmentConfirmPanel" class="d-none p-3" style="border:1px dashed #940000; background:#fff;">
                    <p class="mb-2 fw-bold" style="color:#940000;" id="editEnrollmentConfirmTitle">Confirm?</p>
                    <label class="form-label small mb-1">Optional note</label>
                    <input type="text" class="form-control mb-2" id="editEnrollmentNotes" placeholder="Optional reason..." style="border-radius:0;" autocomplete="off">
                    <p class="small text-muted mb-1">Type <strong>yes</strong> or <strong>no</strong></p>
                    <input type="text" class="form-control mb-2" id="editEnrollmentConfirmYesNo" placeholder="yes / no" style="border-radius:0;" autocomplete="off">
                    <div id="editEnrollmentConfirmWordWrap" class="d-none">
                        <p class="small text-muted mb-1">If yes, type the word <strong>yes</strong> again to confirm</p>
                        <input type="text" class="form-control mb-2" id="editEnrollmentConfirmWord" placeholder="Type yes" style="border-radius:0;" autocomplete="off">
                        <button type="button" class="btn" id="editEnrollmentExecuteBtn" style="background:#940000;color:#fff;border-radius:0;" disabled>
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />



{{-- DataTables JS --}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- JsBarcode Library for ID Card --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

{{-- html2canvas Library for ID Card Download --}}
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

{{-- jsPDF and AutoTable for PDF Export --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('includes.footer')


{{-- Page JS served as application/javascript so Cloudflare HTML minify cannot break && --}}
<script>
    window.ManageStudentBoot = {
        canCreate: {{ ($canCreate ?? false) ? 'true' : 'false' }},
        canUpdate: {{ ($canUpdate ?? false) ? 'true' : 'false' }},
        canDelete: {{ ($canDelete ?? false) ? 'true' : 'false' }},
        hasALevel: {{ !empty($hasALevel) ? 'true' : 'false' }}
    };
    if (typeof jQuery !== 'undefined') { window.$ = window.jQuery = jQuery; }
</script>
<script src="{{ route('manage_student_page_js') }}?v={{ @filemtime(resource_path('views/Admin/partials/manage_student_page_js.blade.php')) ?: time() }}"></script>


<!-- Include Class Selector Modal -->
@include('student_registration.class-selector')

<!-- Include Registration Modal -->
@include('student_registration.modal')

{{-- Quick Registration + Enroll Student (existing Register/Template untouched) --}}
@include('Admin.partials.quick_enroll_student_modals', [
    'qeMode' => 'admin',
    'qeLockedSubclassID' => null,
    'qeLockedSubclassLabel' => '',
    'qeCanQuickRegister' => $canCreate ?? false,
])

