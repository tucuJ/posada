<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Scripts personalizados -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/productos.js"></script>

<?php if (isset($customJS)): ?>
    <!-- Scripts personalizados por página -->
    <script src="<?= BASE_URL ?>assets/js/<?= $customJS ?>"></script>
<?php endif; ?>
</body>
</html>