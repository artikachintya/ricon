@extends('layouts.app')

@section('title', 'Manage Locker')

@push('styles')
<style>
    .table-wrapper {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        margin-top: 20px;
        color: #212529 !important;
    }
    .table thead th {
        background: #f8f9fa;
        color: #212529 !important;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        text-align: center;
    }
    .table tbody td {
        vertical-align: middle;
        background: #ffffff;
        color: #212529 !important;
    }
    .badge {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<div class="hero d-flex justify-content-between align-items-center mb-3">
    <h1 class="fw-bold text-white">Manage Locker</h1>
    <div class="badge bg-light text-dark shadow-sm">
        Status: <span class="text-success fw-bold">{{ ucfirst($booking->status) }}</span>
    </div>
</div>

<div class="table-wrapper">
    <table class="table table-bordered align-middle">
        <thead>
            <tr class="text-center">
                <th>No</th>
                <th>Booked At</th>
                <th>Locker</th>
                <th>Nama Barang</th>
                <th>Detail Barang</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($booking->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $booking->created_at->format('d M Y H:i') }}</td>
                <td class="text-center">Locker {{ $booking->locker_id }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->item_detail ?? '-' }}</td>
                <td class="text-center">
                    @if($item->key)
                        <button class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $item->id }}">
                            <i class="bi bi-qr-code"></i> Show QR
                        </button>
                    @else
                        <span class="text-muted">QR belum tersedia</span>
                    @endif
                </td>
            </tr>

            {{-- MODAL QR PER ITEM --}}
            <div class="modal fade" id="qrModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center">
                        <div class="modal-header">
                            <h5 class="modal-title">QR Access Key</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        {{-- <div class="modal-body p-4">
                            <div class="bg-white p-3 border d-inline-block shadow-sm" style="border-radius: 12px;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ $item->key }}" alt="QR Code">
                            </div>
                            <h5 class="mt-3 fw-bold text-primary">{{ $item->key }}</h5>
                            <p class="text-muted">Gunakan QR ini untuk membuka loker.</p>
                        </div> --}}

                        <div class="modal-body p-4">
                            <div class="bg-white p-3 border d-inline-block shadow-sm" style="border-radius: 12px;">
                                {{-- MENGAMBIL PATH DARI DB --}}
                                @if($item->qr_path)
                                    <img src="{{ asset($item->qr_path) }}" alt="QR Code" width="250">
                                    <div class="mt-3">
                                        <a href="{{ asset($item->qr_path) }}" download="qr_{{ $item->item_detail ?? $item->item_name }}.png" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-download"></i> Download QR
                                        </a>
                                    </div>
                                @else
                                    <p class="text-danger">Gambar QR tidak ditemukan.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Tidak ada item di loker ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@if(session('show_qr'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modals = document.querySelectorAll('.modal[id^="qrModal-"]');
        if (modals.length > 0) {
            new bootstrap.Modal(modals[modals.length - 1]).show();
        }
    });
</script>
@endif
@endpush
