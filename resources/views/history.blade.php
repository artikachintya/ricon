@extends('layouts.app')

@section('title', 'History')

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('#jenis-sampah');
            if (table) {
                new DataTable('#jenis-sampah', {
                    responsive: true
                });
            }

            const createModal = document.getElementById('createPenugasanModal');

            createModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget; // tombol yang diklik
                const orderId = button.getAttribute('data-order-id');

                // 2) kalau pakai hidden input
                const orderInput = createModal.querySelector('#orderIdInput');
                if (orderInput) orderInput.value = orderId;
            });

            // DELETE
            const deleteModal = document.getElementById('deleteTrashModal');

            deleteModal.addEventListener('show.bs.modal', event => {
                const btn = event.relatedTarget;
                const form = deleteModal.querySelector('#deleteTrashForm');

                form.action = btn.dataset.action; // ← uses the route() URL passed from Blade
            });
        });
    </script>
@endpush

@section('content')
<div class="hero d-flex justify-content-between align-items-center">
   <div>
        <h1 class="fw-bold">Riwayat</h1>
    </div>
</div>

@endsection

