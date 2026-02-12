    </div><!-- /.content-area -->
</main><!-- /.main-content -->
</div><!-- /.app-layout -->

<div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('active'); this.classList.remove('active');"></div>

<script>
// Force reload on back button (prevent bfcache)
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (performance.navigation && performance.navigation.type === 2)) {
        window.location.reload();
    }
});

// Sidebar toggle for mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
}

// Auto-hide alerts after 4 seconds
document.querySelectorAll('.alert').forEach(function(alert) {
    setTimeout(function() {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(function() { alert.remove(); }, 300);
    }, 4000);
});
</script>
</body>
</html>
