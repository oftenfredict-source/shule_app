<script>
    function viewSchoolDetails(schoolID) {
        $('#schoolDetailsModal').modal('show');

        $('#schoolDetailsContent').html(
            '<div class="text-center py-4">' +
                '<div class="spinner-border text-danger" role="status"><span class="sr-only">Loading...</span></div>' +
                '<p class="mt-2 mb-0">Loading school details...</p>' +
            '</div>'
        );

        $.ajax({
            url: '{{ route("online_application.school_details", ":schoolID") }}'.replace(':schoolID', schoolID),
            method: 'GET',
            success: function(response) {
                if (!response.success) return;

                var html = '<div class="sx-school-details">';
                html += '<div class="text-center mb-4">';
                if (response.school.schoolLogo) {
                    html += '<img src="{{ asset("") }}' + response.school.schoolLogo + '" alt="' + response.school.schoolName + '" class="sx-school-details__logo mb-2">';
                }
                html += '<h4 class="mb-1">' + response.school.schoolName + '</h4>';
                html += '<p class="text-muted mb-0">' + response.school.schoolType + ' - ' + response.school.ownership + '</p>';
                html += '<p class="text-muted small mb-0">' + response.school.region + ', ' + response.school.district + '</p>';
                if (response.school.address) html += '<p class="text-muted small mb-0">' + response.school.address + '</p>';
                if (response.school.phone) html += '<p class="text-muted small mb-0"><i class="fa fa-phone"></i> ' + response.school.phone + '</p>';
                if (response.school.email) html += '<p class="text-muted small mb-0"><i class="fa fa-envelope"></i> ' + response.school.email + '</p>';
                if (response.school.establishedYear) html += '<p class="text-muted small">Established: ' + response.school.establishedYear + '</p>';
                html += '</div>';

                html += '<h5 class="sx-school-details__heading"><i class="fa fa-columns"></i> Classes (' + response.classes.length + ')</h5>';
                html += '<div class="row">';
                response.classes.forEach(function(cls) {
                    html += '<div class="col-md-6 mb-3"><div class="sx-school-details__card">';
                    html += '<h6>' + cls.className + '</h6><p class="mb-2"><strong>Subclasses:</strong> ' + cls.subclassesCount + '</p><div class="small">';
                    cls.subclasses.forEach(function(sub) {
                        html += '<div class="mb-1"><i class="fa fa-circle sx-dot"></i> ' + sub.subclassName + ': ' + sub.studentCount + ' students</div>';
                    });
                    html += '</div></div></div>';
                });
                html += '</div>';

                html += '<h5 class="sx-school-details__heading"><i class="fa fa-book"></i> School Subjects (' + response.schoolSubjects.length + ')</h5><div class="row">';
                response.schoolSubjects.forEach(function(subj) {
                    html += '<div class="col-md-4 mb-2"><div class="sx-school-details__badge"><strong>' + subj.subject_name + '</strong>';
                    if (subj.subject_code) html += '<br><small>Code: ' + subj.subject_code + '</small>';
                    html += '</div></div>';
                });
                html += '</div>';

                html += '<h5 class="sx-school-details__heading"><i class="fa fa-bookmark"></i> Class Subjects</h5>';
                Object.keys(response.classSubjects).forEach(function(classID) {
                    var classData = response.classes.find(function(c) { return c.classID == classID; });
                    var className = classData ? classData.className : 'Unknown Class';
                    var subjects = response.classSubjects[classID];
                    html += '<div class="mb-3"><h6 class="sx-school-details__sub">' + className + '</h6>';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered sx-school-details__table"><thead><tr><th>Subject</th><th>Code</th><th>Teacher</th></tr></thead><tbody>';
                    subjects.forEach(function(subj) {
                        html += '<tr><td>' + subj.subjectName + '</td><td>' + (subj.subjectCode || '-') + '</td><td>' + subj.teacherName + '</td></tr>';
                    });
                    html += '</tbody></table></div></div>';
                });

                html += '<h5 class="sx-school-details__heading"><i class="fa fa-users"></i> Teachers (' + response.teachers.length + ')</h5>';
                html += '<div class="table-responsive"><table class="table table-bordered sx-school-details__table"><thead><tr><th>Name</th><th>Position</th><th>Roles</th></tr></thead><tbody>';
                response.teachers.forEach(function(teacher) {
                    html += '<tr><td>' + teacher.fullName + '</td><td>' + (teacher.position || '-') + '</td><td>';
                    if (teacher.roles.length > 0) {
                        teacher.roles.forEach(function(role) {
                            html += '<span class="badge badge-danger mr-1">' + role + '</span>';
                        });
                    } else {
                        html += '<span class="text-muted">No roles assigned</span>';
                    }
                    html += '</td></tr>';
                });
                html += '</tbody></table></div></div>';

                $('#schoolDetailsContent').html(html);
            },
            error: function() {
                $('#schoolDetailsContent').html(
                    '<div class="alert alert-danger mb-0"><i class="fa fa-exclamation-triangle"></i> Error loading school details. Please try again.</div>'
                );
            }
        });
    }
</script>
