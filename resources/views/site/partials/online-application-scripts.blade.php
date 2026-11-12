<script>
(function () {
    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showSchoolDetailsModal() {
        var el = document.getElementById('schoolDetailsModal');
        if (!el) return null;

        if (window.bootstrap && bootstrap.Modal) {
            var instance = typeof bootstrap.Modal.getInstance === 'function'
                ? bootstrap.Modal.getInstance(el)
                : null;
            if (!instance) {
                instance = new bootstrap.Modal(el);
            }
            return instance;
        }

        if (window.jQuery && typeof jQuery(el).modal === 'function') {
            return {
                show: function () { jQuery(el).modal('show'); },
                hide: function () { jQuery(el).modal('hide'); }
            };
        }

        return {
            show: function () {
                el.classList.add('show');
                el.style.display = 'block';
                el.removeAttribute('aria-hidden');
                document.body.classList.add('modal-open');
                if (!document.querySelector('.modal-backdrop')) {
                    var backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            },
            hide: function () {
                el.classList.remove('show');
                el.style.display = 'none';
                el.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(function (node) { node.remove(); });
            }
        };
    }

    function loadingHtml() {
        return '<div class="text-center py-4">' +
            '<div class="spinner-border text-danger" role="status"><span class="sr-only">Loading...</span></div>' +
            '<p class="mt-2 mb-0">{{ __("site.apply_loading_details") }}</p>' +
            '</div>';
    }

    function uniqueSubjects(list) {
        var seen = {};
        var out = [];
        (list || []).forEach(function (subj) {
            var name = (subj.subjectName || subj.subject_name || '').trim();
            if (!name) return;
            var key = name.toLowerCase();
            if (seen[key]) return;
            seen[key] = true;
            out.push({
                name: name,
                code: (subj.subjectCode || subj.subject_code || '').trim()
            });
        });
        return out;
    }

    function renderSchoolDetails(response) {
        var school = response.school || {};
        var classes = response.classes || [];
        var schoolSubjects = uniqueSubjects(response.schoolSubjects || []);
        var classSubjects = response.classSubjects || [];
        var subjectsByClass = {};
        classSubjects.forEach(function (group) {
            subjectsByClass[String(group.classID)] = uniqueSubjects(group.subjects || []);
        });
        var html = '<div class="sx-school-details">';

        html += '<div class="sx-school-details__hero">';
        if (school.schoolLogoUrl) {
            html += '<img src="' + escapeHtml(school.schoolLogoUrl) + '" alt="" class="sx-school-details__logo" onerror="this.style.display=\'none\'">';
        } else {
            html += '<div class="sx-school-details__logo sx-school-details__logo--ph">' +
                escapeHtml((school.schoolName || 'S').charAt(0).toUpperCase()) +
                '</div>';
        }
        html += '<div class="sx-school-details__hero-text">';
        if (school.schoolType) {
            html += '<span class="sx-school-details__type">' + escapeHtml(school.schoolType) + '</span>';
        }
        html += '<h4>' + escapeHtml(school.schoolName || '') + '</h4>';
        html += '<p class="sx-school-details__place"><i class="fa fa-map-marker"></i> ' +
            escapeHtml([school.region, school.district].filter(Boolean).join(', ')) + '</p>';
        if (school.phone || school.email) {
            html += '<div class="sx-school-details__contacts">';
            if (school.phone) html += '<span><i class="fa fa-phone"></i> ' + escapeHtml(school.phone) + '</span>';
            if (school.email) html += '<span><i class="fa fa-envelope"></i> ' + escapeHtml(school.email) + '</span>';
            html += '</div>';
        }
        html += '</div></div>';

        html += '<div class="sx-school-details__section">';
        if (response.needsLogin) {
            var loginUrl = response.loginUrl || '{{ route("online_application.auth.login") }}';
            html += '<div class="sx-apply-login-gate sx-apply-login-gate--modal">';
            html += '<div class="sx-apply-login-gate__icon" aria-hidden="true"><i class="fa fa-lock"></i></div>';
            html += '<h4>{{ __("site.apply_login_to_apply") }}</h4>';
            html += '<p>{{ __("site.apply_login_gate_text") }}</p>';
            html += '<a href="' + escapeHtml(loginUrl) + '" class="sx-apply-btn-sm sx-apply-btn-sm--block">';
            html += '<i class="fa fa-sign-in" aria-hidden="true"></i> <span>{{ __("site.apply_login_to_apply") }}</span></a>';
            html += '</div>';
        } else {
            html += '<div class="sx-school-details__section-head">';
            html += '<h5><i class="fa fa-graduation-cap"></i> {{ __("site.apply_available_classes") }}</h5>';
            html += '</div>';

            if (!classes.length) {
                html += '<div class="sx-school-details__empty">{{ __("site.apply_no_classes") }}</div>';
            } else {
                html += '<div class="sx-school-details__class-list">';
                classes.forEach(function (cls) {
                    var clsSubjects = subjectsByClass[String(cls.classID)] || [];
                    html += '<div class="sx-school-details__class' + (cls.hasSpaces ? '' : ' is-full') + '">';
                    html += '<div class="sx-school-details__class-top">';
                    html += '<div class="sx-school-details__class-info">';
                    html += '<strong>' + escapeHtml(cls.className) + '</strong>';
                    if (cls.academicYearName) {
                        html += '<small>' + escapeHtml(cls.academicYearName) + '</small>';
                    }
                    html += '</div>';
                    if (cls.hasSpaces && cls.applyUrl) {
                        html += '<a href="' + escapeHtml(cls.applyUrl) + '" class="sx-school-details__apply">' +
                            '<i class="fa fa-paper-plane"></i> <span>{{ __("site.apply_now") }}</span></a>';
                    }
                    html += '</div>';

                    if (clsSubjects.length) {
                        html += '<div class="sx-school-details__class-subjects">';
                        html += '<div class="sx-school-details__class-subjects-label"><i class="fa fa-book"></i> {{ __("site.apply_subjects") }} (' + clsSubjects.length + ')</div>';
                        html += '<div class="sx-school-details__subjects">';
                        clsSubjects.forEach(function (subj) {
                            html += '<span class="sx-school-details__subject">' + escapeHtml(subj.name);
                            if (subj.code) html += ' <em>' + escapeHtml(subj.code) + '</em>';
                            html += '</span>';
                        });
                        html += '</div></div>';
                    } else {
                        html += '<div class="sx-school-details__class-subjects is-empty">{{ __("site.apply_no_subjects") }}</div>';
                    }

                    html += '</div>';
                });
                html += '</div>';
            }
        }
        html += '</div>';

        if (schoolSubjects.length) {
            html += '<div class="sx-school-details__section">';
            html += '<div class="sx-school-details__section-head">';
            html += '<h5><i class="fa fa-book"></i> {{ __("site.apply_all_subjects") }}</h5>';
            html += '<span class="sx-school-details__count">' + schoolSubjects.length + '</span>';
            html += '</div>';
            html += '<div class="sx-school-details__subjects">';
            schoolSubjects.forEach(function (subj) {
                html += '<span class="sx-school-details__subject">' + escapeHtml(subj.name);
                if (subj.code) html += ' <em>' + escapeHtml(subj.code) + '</em>';
                html += '</span>';
            });
            html += '</div></div>';
        }

        html += '</div>';
        return html;
    }

    window.viewSchoolDetails = function (schoolID) {
        if (!schoolID) return;

        var content = document.getElementById('schoolDetailsContent');
        var title = document.getElementById('schoolDetailsModalLabel');
        if (content) content.innerHTML = loadingHtml();
        if (title) title.textContent = '{{ __("site.apply_school_details") }}';

        var modal = showSchoolDetailsModal();
        if (modal && typeof modal.show === 'function') modal.show();

        var url = '{{ route("online_application.school_details", ":schoolID") }}'.replace(':schoolID', encodeURIComponent(schoolID));

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Failed');
            return res.json();
        })
        .then(function (response) {
            if (!response.success || !content) return;
            if (title && response.school && response.school.schoolName) {
                title.textContent = response.school.schoolName;
            }
            content.innerHTML = renderSchoolDetails(response);
        })
        .catch(function () {
            if (!content) return;
            content.innerHTML =
                '<div class="alert alert-danger mb-0"><i class="fa fa-exclamation-triangle"></i> {{ __("site.apply_details_error") }}</div>';
        });
    };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.sx-view-school-details');
        if (!btn) return;
        e.preventDefault();
        window.viewSchoolDetails(btn.getAttribute('data-school-id'));
    });
})();
</script>
