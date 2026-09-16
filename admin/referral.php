<?php
require_once 'header.php';
ensureReferralTables();
$settings = getReferralSettings();
$statement = $pdo->query('SELECT * FROM tbl_referral ORDER BY id DESC');
$referrals = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="content-header">
    <div class="content-header-left"><h1>Referral Offer</h1></div>
    <div class="content-header-right"><a href="referral-edit.php" class="btn btn-primary btn-sm">Edit Offer</a></div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-4"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Current offer</h3></div><div class="box-body">
            <p><strong>Status:</strong> <?php echo (int) $settings['is_active'] === 1 ? 'Active' : 'Paused'; ?></p>
            <p><strong>New customer discount:</strong> <?php echo $settings['discount_type'] === 'amount' ? 'NZ$ ' . number_format((float) $settings['discount_value'], 2) : number_format((float) $settings['discount_value'], 2) . '%'; ?></p>
            <p><strong>Bonus points per conversion:</strong> <?php echo number_format((int) ($settings['bonus_points'] ?? 0)); ?></p>
            <p><strong>Points per NZ$1:</strong> <?php echo number_format((int) ($settings['points_per_dollar'] ?? 100)); ?></p>
            <p><strong>Minimum booking:</strong> NZ$ <?php echo number_format((float) $settings['minimum_order_amount'], 2); ?></p>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars((string) $settings['terms'], ENT_QUOTES, 'UTF-8')); ?></p>
        </div></div></div>
        <div class="col-md-8"><div class="box box-info"><div class="box-header with-border"><h3 class="box-title">Referral submissions and rewards</h3></div><div class="box-body table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Code</th><th>Referrer</th><th>Referred customer</th><th>Discount</th><th>Points earned</th><th>Status</th><th>Created</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($referrals as $row): ?><tr><td><code><?php echo htmlspecialchars($row['referral_code'], ENT_QUOTES, 'UTF-8'); ?></code></td><td><?php echo htmlspecialchars($row['referrer_name'], ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars($row['referrer_email'], ENT_QUOTES, 'UTF-8'); ?></small></td><td><?php echo htmlspecialchars($row['referee_name'], ENT_QUOTES, 'UTF-8'); ?><br><small><?php echo htmlspecialchars($row['referee_email'], ENT_QUOTES, 'UTF-8'); ?></small></td><td><?php echo $row['discount_type'] === 'amount' ? 'NZ$ ' . number_format((float) $row['discount_value'], 2) : number_format((float) $row['discount_value'], 2) . '%'; ?></td><td><?php echo number_format((int) ($row['awarded_points'] ?? 0)); ?></td><td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php if ($row['status'] === 'Pending'): ?><a class="btn btn-danger btn-xs" href="referral-delete.php?id=<?php echo (int) $row['id']; ?>">Cancel</a><?php endif; ?></td></tr><?php endforeach; ?>
        <?php if (!$referrals): ?><tr><td colspan="7">No referrals have been submitted.</td></tr><?php endif; ?>
        </tbody></table></div></div></div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
