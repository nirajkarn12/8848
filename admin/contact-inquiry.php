<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Contact Inquiries</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th width="10">#</th>
								<th width="150">Name</th>
								<th width="150">Email Address</th>
								<th width="100">Phone</th>
								<th width="200">Subject</th>
								<th width="130">Promo / Referral Code</th>
								<th>Message</th>
								<th width="150">Date</th>
								<th width="80">Action</th>
							</tr>
						</thead>

						<tbody>
							<?php
							$i = 0;

							$statement = $pdo->prepare("
								SELECT *
								FROM tbl_contact_inquiry
								ORDER BY id DESC
							");

							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result as $row) {
								$i++;
							?>
								<tr>
									<td><?php echo $i; ?></td>

									<td>
										<?php echo htmlspecialchars($row['name'] ?? ''); ?>
									</td>

									<td>
										<?php echo htmlspecialchars($row['email'] ?? ''); ?>
									</td>

									<td>
										<?php echo htmlspecialchars($row['phone'] ?? ''); ?>
									</td>

									<td>
										<?php echo htmlspecialchars($row['subject'] ?? ''); ?>
									</td>

									<td>
										<?php echo htmlspecialchars($row['promo_code'] ?? ''); ?>
									</td>

									<td>
										<?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?>
									</td>

									<td>
										<?php echo htmlspecialchars($row['created_at'] ?? ''); ?>
									</td>

									<td>
										<a href="#"
										   class="btn btn-danger btn-xs"
										   data-href="contact-inquiry-delete.php?id=<?php echo $row['id']; ?>"
										   data-toggle="modal"
										   data-target="#confirm-delete">
											Delete
										</a>
									</td>
								</tr>
							<?php
							}
							?>
						</tbody>

					</table>
				</div>
			</div>
		</div>
	</div>
</section>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-hidden="true">&times;</button>

                <h4 class="modal-title" id="myModalLabel">
                    Delete Confirmation
                </h4>
            </div>

            <div class="modal-body">
                Are you sure want to delete this item?
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-default"
                        data-dismiss="modal">
                    Cancel
                </button>

                <a class="btn btn-danger btn-ok">
                    Delete
                </a>
            </div>

        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>