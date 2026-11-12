<div class="modal fade" id="schoolDetailsModal" tabindex="-1" role="dialog" aria-labelledby="schoolDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content sx-apply-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="schoolDetailsModalLabel">{{ __('site.apply_school_details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="schoolDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status"><span class="sr-only">Loading...</span></div>
                    <p class="mt-2 mb-0">{{ __('site.apply_loading_details') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('site.apply_close') }}</button>
            </div>
        </div>
    </div>
</div>
