@include('Admin.partials.student_id_card_helpers')
@foreach($students as $student)
    @include('Admin.partials.student_id_card_item')
@endforeach
