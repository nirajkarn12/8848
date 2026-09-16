<?php
require_once 'header.php';
ensureReferralTables();
$settings = getReferralSettings();
if (isset($_POST['form1'])) {
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $discountType = in_array($_POST['discount_type'] ?? '', ['percent', 'amount'], true) ? $_POST['discount_type'] : 'percent';
    $discountValue = max(0, (float) ($_POST['discount_value'] ?? 0));
    if ($discountType === 'percent') {
        $discountValue = min(100, $discountValue);
    }
    $minimumOrder = max(0, (float) ($_POST['minimum_order_amount'] ?? 0));
    $terms = trim($_POST['terms'] ?? '');
    $update = $pdo->prepare('UPDATE tbl_referral_settings SET is_active = ?, discount_type = ?, discount_value = ?, minimum_order_amount = ?, terms = ?, updated_at = NOW() WHERE id = 1');
    $update->execute([$isActive, $discountType, $discountValue, $minimumOrder, $terms]);
    $settings = getReferralSettings();
    $success_message = 'Referral offer settings updated successfully.';
}
?>
<section class="content-header"><div class="content-header-left"><h1>Edit Referral Offer</h1></div><div class="content-header-right"><a href="referral.php" class="btn btn-primary btn-sm">View Referrals</a></div></section>
<section class="content"><div class="row"><div class="col-md-8"><form class="form-horizontal" method="post"><div class="box box-info"><div class="box-body">
<?php if (!empty($success_message)): ?><div class="callout callout-success"><p><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p></div><?php endif; ?>
<div class="form-group"><label class="col-sm-4 control-label">Offer status</label><div class="col-sm-6"><label class="checkbox-inline"><input type="checkbox" name="is_active" value="1" <?php echo (int) $settings['is_active'] === 1 ? 'checked' : ''; ?>> Active</label></div></div>
<div class="form-group"><label class="col-sm-4 control-label">Discount type</label><div class="col-sm-6"><select name="discount_type" class="form-control"><option value="percent" <?php echo $settings['discount_type'] === 'percent' ? 'selected' : ''; ?>>Percentage</option><option value="amount" <?php echo $settings['discount_type'] === 'amount' ? 'selected' : ''; ?>>Fixed amount</option></select></div></div>
<div class="form-group"><label class="col-sm-4 control-label">Discount value</label><div class="col-sm-6"><input type="number" min="0" step="0.01" name="discount_value" class="form-control" value="<?php echo htmlspecialchars((string) $settings['discount_value'], ENT_QUOTES, 'UTF-8'); ?>"><span class="help-block">Percentage is capped at 100.</span></div></div>
<div class="form-group"><label class="col-sm-4 control-label">Minimum booking amount</label><div class="col-sm-6"><input type="number" min="0" step="0.01" name="minimum_order_amount" class="form-control" value="<?php echo htmlspecialchars((string) $settings['minimum_order_amount'], ENT_QUOTES, 'UTF-8'); ?>"></div></div>
<div class="form-group"><label class="col-sm-4 control-label">Terms shown to customers</label><div class="col-sm-6"><textarea name="terms" rows="5" class="form-control"><?php echo htmlspecialchars((string) $settings['terms'], ENT_QUOTES, 'UTF-8'); ?></textarea></div></div>
<div class="form-group"><div class="col-sm-offset-4 col-sm-6"><button type="submit" name="form1" class="btn btn-success">Save Offer</button></div></div>
</div></div></form></div></div></section>
<?php require_once 'footer.php'; ?>
