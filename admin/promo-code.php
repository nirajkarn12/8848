<?php
require_once 'header.php';
ensurePromoCodeTable();
$statement = $pdo->query('SELECT * FROM tbl_promo_code ORDER BY created_at DESC, id DESC');
$promoCodes = $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<section class="content-header">
    <div class="content-header-left">
        <h1>Promo Codes</h1>
    </div>
    <div class="content-header-right">
        <a href="promo-code-add.php" class="btn btn-primary btn-sm">Add New</a>
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
                                <th>#</th>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Minimum</th>
                                <th>Usage</th>
                                <th>Valid</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($promoCodes as $row) {
                                $i++;
                                $discountValue = (float) ($row['discount_value'] ?? 0);
                                $discountText = $row['discount_type'] === 'amount'
                                    ? 'NZ$ ' . number_format($discountValue, 2)
                                    : number_format($discountValue, 2) . '%';
                                $usageText = ($row['usage_limit'] > 0)
                                    ? ((int) $row['used_count']) . ' / ' . (int) $row['usage_limit']
                                    : ((int) $row['used_count']) . ' used';
                                $validFrom = trim((string) ($row['valid_from'] ?? ''));
                                $validTo = trim((string) ($row['valid_to'] ?? ''));
                                $validText = 'Always';
                                if ($validFrom !== '' || $validTo !== '') {
                                    $validText = trim($validFrom !== '' ? date('d M Y', strtotime($validFrom)) : '', ' ') . ($validFrom !== '' && $validTo !== '' ? ' → ' : '') . ($validTo !== '' ? date('d M Y', strtotime($validTo)) : '');
                                }
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><strong><?php echo htmlspecialchars(strtoupper((string) $row['code']), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($discountText, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>NZ$ <?php echo number_format((float) ($row['minimum_order_amount'] ?? 0), 2); ?></td>
                                    <td><?php echo htmlspecialchars($usageText, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($validText, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if ((int) ($row['is_active'] ?? 0) === 1) { ?>
                                            <span class="label label-success">Active</span>
                                        <?php } else { ?>
                                            <span class="label label-default">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="promo-code-edit.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="promo-code-delete.php?id=<?php echo (int) $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php }
                            if (empty($promoCodes)) {
                                ?><tr><td colspan="8">No promo codes created yet.</td></tr><?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this promo code?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
