		</div>

		<footer class="main-footer">
			<strong>Staff Panel</strong> — assigned jobs only
		</footer>
	</div>

	<script src="<?php echo BASE_URL; ?>admin/js/jquery-2.2.4.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/dataTables.bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/jquery.slimscroll.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/fastclick.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/app.min.js"></script>
	<script>
	$(function () {
		if ($('#example1').length) {
			$('#example1').DataTable();
		}
	});
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('<?php echo STAFF_URL; ?>sw.js').catch(function () {});
	}
	</script>
</body>
</html>
