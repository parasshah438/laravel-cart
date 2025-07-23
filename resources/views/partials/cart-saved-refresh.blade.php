<div id="saved-items-section">
    <h5 class="mt-5">Saved for Later</h5>
    @if($savedItems->isEmpty())
        <div class="alert alert-info">No items saved for later.</div>
    @else
    <table class="table table-bordered">
        <tbody>
            @include('partials._saved_items', ['savedItems' => $savedItems])
        </tbody>
    </table>
    @endif
</div>
