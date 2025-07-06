// Pastikan jQuery sudah di-load sebelum script ini
$(document).ready(function() {
    // SweetAlert2 for Logout Confirmation
    $('#logoutSidebar, #logoutNavbar').on('click', function(e) {
        e.preventDefault(); // Mencegah link langsung redirect

        const logoutUrl = $(this).attr('href'); // Ambil URL logout dari atribut href

        Swal.fire({
            title: 'Yakin ingin keluar?',
            text: "Anda akan keluar dari sesi ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengklik 'Ya, Keluar!', arahkan ke URL logout
                window.location.href = logoutUrl;
            }
        });
    });
});