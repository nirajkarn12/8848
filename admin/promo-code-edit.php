<?php
require_once 'header.php';
ensurePromoCodeTable();

if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

$statement = $pdo->prepare('SELECT * FROM tbl_promo_code WHERE id = ? LIMIT 1');
$statement->execute([(int) $_REQUEST['id']]);
$promoCode = $statement->fetch(PDO::FETCH_ASSOC);

if (!$promoCode) {
    header('location: logout.php');
    exit;
}

if (isset($_POST['form1'])) {
    $valid = 1;
    $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
    $discountType = in_array($_POST['discount_type'] ?? '', ['percent', 'amount'], true) ? $_POST['discount_type'] : 'percent';
    $discountValue = max(0, (float) ($_POST['discount_value'] ?? 0));
    if ($discountType === 'percent') {
        $discountValue = min(100, $discountValue);
    }
    $minimumOrderAmount = max(0, (float) ($_POST['minimum_order_amount'] ?? 0));
    $usageLimit = max(0, (int) ($_POST['usage_limit'] ?? 0));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $description = trim((string) ($_POST['description'] ?? ''));
    $validFrom = trim((string) ($_POST['valid_from'] ?? ''));
    $validTo = trim((string) ($_POST['valid_to'] ?? ''));

    if ($code === '') {
        $valid = 0;
        $error_message .= 'Promo code can not be empty.<br>';
    }

    if ($valid === 1) {
        $dupe = $pdo->prepare('SELECT id FROM tbl_promo_code WHERE code = ? AND id != ? LIMIT 1');
        $dupe->execute([$code, (int) $_REQUEST['id']]);
        if ($dupe->fetch()) {
            $valid = 0;
            $error_message .= 'Promo code already exists.<br>';
        }
    }

    if ($valid === 1) {
        $update = $pdo->prepare('UPDATE tbl_promo_code SET code = ?, discount_type = ?, discount_value = ?, minimum_order_amount = ?, is_active = ?, usage_limit = ?, valid_from = ?, valid_to = ?, description = ?, updated_at = NOW() WHERE id = ?');
        $update->execute([
            $code,
            $discountType,
            $discountValue,
            $minimumOrderAmount,
            $isActive,
            $usageLimit,
            $validFrom !== '' ? date('Y-m-d H:i:s', strtotime($validFrom)) : null,
            $validTo !== '' ? date('Y-m-d H:i:s', strtotime($validTo)) : null,
            $description,
            (int) $_REQUEST['id'],
        ]);
        $success_message = 'Promo code is updated successfully.';
        $promoCode = array_merge($promoCode, [
            'code' => $code,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'minimum_order_amount' => $minimumOrderAmount,
            'is_active' => $isActive,
            'usage_limit' => $usageLimit,
            'valid_from' => $validFrom !== '' ? date('Y-m-d\TH:i', strtotime($validFrom)) : '',
            'valid_to' => $validTo !== '' ? date('Y-m-d\TH:i', strtotime($validTo)) : '',
            'description' => $description,
        ]);
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Promo Code</h1>
    </div>
    <div class="content-header-right">
        <a href="promo-code.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($error_message)): ?>
                <div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Code <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control text-uppercase" name="code" value="<?php echo htmlspecialchars((string) ($promoCode['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Discount type</label>
                            <div class="col-sm-4">
                                <select name="discount_type" class="form-control">
                                    <option value="percent" <?php echo (($promoCode['discount_type'] ?? 'percent') === 'percent') ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="amount" <?php echo (($promoCode['discount_type'] ?? '') === 'amount') ? 'selected' : ''; ?>>Fixed amount</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Discount value</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" step="0.01" class="form-control" name="discount_value" value="<?php echo htmlspecialchars((string) ($promoCode['discount_value'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Minimum order</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" step="0.01" class="form-control" name="minimum_order_amount" value="<?php echo htmlspecialchars((string) ($promoCode['minimum_order_amount'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Usage limit</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" step="1" class="form-control" name="usage_limit" value="<?php echo htmlspecialchars((string) ($promoCode['usage_limit'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="help-block">0 means unlimited usage.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Valid from</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" class="form-control" name="valid_from" value="<?php echo htmlspecialchars((string) (!empty($promoCode['valid_from']) ? date('Y-m-d\TH:i', strtotime($promoCode['valid_from'])) : ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Valid to</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" class="form-control" name="valid_to" value="<?php echo htmlspecialchars((string) (!empty($promoCode['valid_to']) ? date('Y-m-d\TH:i', strtotime($promoCode['valid_to'])) : ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Description</label>
                            <div class="col-sm-4">
                                <textarea name="description" rows="3" class="form-control"><?php echo htmlspecialchars((string) ($promoCode['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Active</label>
                            <div class="col-sm-4">
                                <label class="checkbox-inline"><input type="checkbox" name="is_active" value="1" <?php echo ((int) ($promoCode['is_active'] ?? 0) === 1) ? 'checked' : ''; ?>> Yes</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
