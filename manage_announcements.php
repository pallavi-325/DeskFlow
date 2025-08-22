    <script type="module">
        import { initScene } from './js/initScene.js';
        
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 50,
            delay: 100
        });

        // Initialize 3D scene
        const workspace = initScene();

        // Initialize DataTables
        $(document).ready(function() {
            $('#announcementsTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "language": {
                    "search": "Search announcements:",
                    "lengthMenu": "Show _MENU_ announcements per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ announcements",
                    "infoEmpty": "No announcements found",
                    "infoFiltered": "(filtered from _MAX_ total announcements)"
                }
            });
        });
    </script>
</body>
</html> 