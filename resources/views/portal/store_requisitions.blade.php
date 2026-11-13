@php
    $navInclude = ($portalRole ?? '') === 'teacher' ? 'includes.teacher_nav' : 'includes.staff_nav';
@endphp
@include($navInclude)
@include('portal.partials.store_requisitions_body', ['useTeacherCorp' => false])
