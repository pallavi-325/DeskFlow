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
            $('#paymentsTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "language": {
                    "search": "Search payments:",
                    "lengthMenu": "Show _MENU_ payments per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ payments",
                    "infoEmpty": "No payments found",
                    "infoFiltered": "(filtered from _MAX_ total payments)"
                }
            });
        });
    </script>
</body>
</html> 