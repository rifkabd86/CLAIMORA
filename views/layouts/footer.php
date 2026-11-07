    <!-- Interactive Javascript Files -->
    <script src="js/app.js"></script>
    <script src="js/policy.js"></script>
    <script src="js/upload.js"></script>
    <script src="js/chart.js"></script>

    <?php
    // Render any active flash message via JS Toast on page load
    $flashes = \Core\Session::getAllFlashes();
    if (!empty($flashes)):
        foreach ($flashes as $type => $message):
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode($message) ?>, <?= json_encode($type) ?>);
            });
        </script>
    <?php
        endforeach;
    endif;
    ?>
</body>
</html>
