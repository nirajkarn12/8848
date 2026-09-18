<?php
require_once 'header.php';
ensureReferralTables();
$settings = getReferralSettings();
$statement = $pdo->query('SELECT * FROM tbl_referral ORDER BY id DESC');
$referrals = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="content-header">
    <div class="content-header-left">
        <h1>Referral Offer</h1>
    </div>
    <div class="content-header-right">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#current-offer-modal">Current Offer</button>
        <a href="referral-edit.php" class="btn btn-default btn-sm">Edit Offer</a>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Referral submissions and rewards</h3>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Referrer</th>
                                <th>Referred customer</th>
                                <th>Discount</th>
                                <th>Points earned</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $row): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($row['referral_code'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['referrer_name'], ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars($row['referrer_email'], ENT_QUOTES, 'UTF-8'); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['referee_name'], ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars($row['referee_email'], ENT_QUOTES, 'UTF-8'); ?></small></td>
                                    <td><?php echo $row['discount_type'] === 'amount' ? 'NZ$ ' . number_format((float) $row['discount_value'], 2) : number_format((float) $row['discount_value'], 2) . '%'; ?></td>
                                    <td><?php echo number_format((int) ($row['awarded_points'] ?? 0)); ?></td>
                                    <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <a class="btn btn-danger btn-xs" href="referral-delete.php?id=<?php echo (int) $row['id']; ?>">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$referrals): ?><tr><td colspan="8">No referrals have been submitted.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="current-offer-modal" tabindex="-1" role="dialog" aria-labelledby="current-offer-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="current-offer-label">Current Offer</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped mb-0">
                    <tbody>
                        <tr>
                            <th width="35%">Status</th>
                            <td><?php echo (int) $settings['is_active'] === 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Paused</span>'; ?></td>
                        </tr>
                        <tr>
                            <th>New customer discount</th>
                            <td><?php echo $settings['discount_type'] === 'amount' ? 'NZ$ ' . number_format((float) $settings['discount_value'], 2) : number_format((float) $settings['discount_value'], 2) . '%'; ?></td>
                        </tr>
                        <tr>
                            <th>Bonus points per conversion</th>
                            <td><?php echo number_format((int) ($settings['bonus_points'] ?? 0)); ?></td>
                        </tr>
                        <tr>
                            <th>Points per NZ$1</th>
                            <td><?php echo number_format((int) ($settings['points_per_dollar'] ?? 100)); ?></td>
                        </tr>
                        <tr>
                            <th>Minimum booking</th>
                            <td>NZ$ <?php echo number_format((float) $settings['minimum_order_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Terms</th>
                            <td><?php echo nl2br(htmlspecialchars((string) $settings['terms'], ENT_QUOTES, 'UTF-8')); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <a href="referral-edit.php" class="btn btn-success">Edit Offer</a>
            </div>
        </div>
    </div>
</div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
