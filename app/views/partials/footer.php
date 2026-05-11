<?php
$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $currentUser = true;
}
?>
<?php if ($currentUser): ?>
    </div><!-- .main-content -->
</div><!-- .d-flex -->
<?php else: ?>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
document.addEventListener('click', function(e) {
    var drop = document.getElementById('notifDrop');
    var bell = document.getElementById('notifBellBtn');
    if (drop && bell && !bell.contains(e.target)) { drop.classList.remove('show'); }
});
</script>
</body>
</html>
