<?php
// ============================================================
// Owner Statement PDF Generator — WordPress admin page
// LBS
//
// Structured the same way as prop_hub.php: everything lives
// inside functions hooked into WordPress, nothing runs at
// require-time. That matters here specifically because this file
// used to be a standalone script that executed immediately and
// called exit() on a plain page load — safe when run alone with
// `php generate_statement.php`, but fatal/broken when required
// directly from functions.php, since that ran on every single
// page load on the site.
// ============================================================

// Guard against redeclaring FPDF in case another plugin/theme
// file already bundles a PDF library under the same class name.
if (!class_exists('FPDF')) {
    require_once __DIR__ . '/lib/fpdf.php';
}

// --- Register the admin menu page ---
add_action('admin_menu', 'lbs_add_owner_statement_page');

function lbs_add_owner_statement_page() {
    add_menu_page(
        'Owner Statement',
        'Owner Statement',
        'manage_options',
        'lbs-owner-statement',
        'lbs_render_owner_statement_page',
        'dashicons-media-spreadsheet',
        4
    );
}

// --- Company config (fixed header info, same on every statement) ---
function lbs_owner_statement_company() {
    return [
        'name'          => 'LITTLE BOOK STAYS LLC',
        'address_lines' => ['54 State Street,', 'Ste 804 #15431', 'Albany, NY 12207'],
        'phone_lines'   => ['Phone - Office - 620-678-9482', 'Phone - Mobile - 570-260-6473'],
        'footer_left'   => 'LITTLE BOOK STAYS',
        'footer_right'  => 'https://www.littlebookstays.com',
    ];
}

// ---------------------------------------------------------------
// Main admin page — just renders the input form. PDF generation
// happens in a completely separate admin-post handler below, so
// a raw PDF download response never has to compete with
// WordPress's own admin header/nav/footer HTML on this page.
// ---------------------------------------------------------------
function lbs_render_owner_statement_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $statement_period = get_option('ols_statement_period', 'July 1 - July 31, 2026');
    $owner_name       = get_option('ols_owner_name', 'Benjamin Louie');
    $cleaning_fee     = (float) get_option('ols_cleaning_fee', 220.00);
    $cohost_pct       = (float) get_option('ols_cohost_pct', 15);
    $owner_pct        = 100 - $cohost_pct;
    ?>
    <div class="wrap">
        <h1>Owner Statement</h1>

        <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
            <input type="hidden" name="action" value="lbs_generate_owner_statement">
            <?php wp_nonce_field('lbs_owner_statement', 'lbs_owner_statement_nonce'); ?>

            <div class="ols-top-inputs">
                <label for="statement_period">Statement Period</label>
                <input type="text" id="statement_period" name="statement_period"
                       value="<?= esc_attr($statement_period) ?>" placeholder="July 1 - July 31, 2026">

                <label for="owner_name">Owner Name</label>
                <select id="owner_name" name="owner_name">
                    <?php
                    $ols_owner_choices = ['Benjamin Louie', 'Sandra Tendilla', 'Thomas Warner'];
                    foreach ($ols_owner_choices as $ols_owner_choice) :
                    ?>
                        <option value="<?= esc_attr($ols_owner_choice) ?>" <?= selected($owner_name, $ols_owner_choice, false) ?>><?= esc_html($ols_owner_choice) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cleaning_fee">Cleaning Fee ($)</label>
                <input type="number" step="0.01" id="cleaning_fee" name="cleaning_fee"
                       value="<?= esc_attr($cleaning_fee) ?>">

                <div class="ols-pct-row">
                    <div>
                        <label for="cohost_pct">Co-Host Fee (%)</label>
                        <input type="number" step="0.01" id="cohost_pct" name="cohost_pct"
                               value="<?= esc_attr($cohost_pct) ?>">
                    </div>
                    <div>
                        <label for="owner_pct">Owner Fee (%)</label>
                        <input type="number" step="0.01" id="owner_pct" readonly
                               value="<?= esc_attr($owner_pct) ?>">
                    </div>
                </div>
            </div>

            <h2>Payouts</h2>
            <table id="ols-payouts-table">
                <thead>
                    <tr>
                        <th>RSVP Date</th>
                        <th>RSVP Name</th>
                        <th>RSVP Fee</th>
                        <th>Base Fee</th>
                        <th>Cleaning Fee</th>
                        <th>Co-Host Payout</th>
                        <th>Owner Payout</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="ols-payouts-body">
                    <!-- rows added here by JS -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Totals</td>
                        <td id="ols-total-base">$0.00</td>
                        <td id="ols-total-cleaning">$0.00</td>
                        <td id="ols-total-cohost">$0.00</td>
                        <td id="ols-total-owner">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <p>
                <button type="button" id="ols-add-row-btn" class="button">+ Add Row</button>
            </p>

            <h2>Expenses</h2>
            <table id="ols-expenses-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="ols-expenses-body">
                    <!-- rows added here by JS -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Totals</td>
                        <td id="ols-total-expenses">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <p>
                <button type="button" id="ols-add-expense-btn" class="button">+ Add Row</button>
                <button type="submit" class="button button-primary">Generate PDF</button>
            </p>
        </form>
    </div>

    <style>
        .ols-top-inputs { max-width: 420px; }
        .ols-top-inputs label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; margin-top: 16px; }
        .ols-top-inputs input[type="text"], .ols-top-inputs input[type="number"] {
            width: 100%; padding: 8px 10px; font-size: 14px; box-sizing: border-box;
            border: 1px solid #ccc; border-radius: 5px;
        }
        #owner_pct { background: #f2f2f2; color: #666; }
        .ols-pct-row { display: flex; gap: 12px; max-width: 420px; }
        .ols-pct-row > div { flex: 1; }
        #ols-payouts-table { width: 100%; max-width: 900px; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        #ols-payouts-table th, #ols-payouts-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        #ols-payouts-table th { background: #f7f7f7; font-size: 11px; }
        #ols-payouts-table td input {
            width: 100%; box-sizing: border-box; padding: 5px 6px; font-size: 13px;
            border: 1px solid #ccc; border-radius: 3px;
        }
        #ols-payouts-table tfoot td { font-weight: 600; background: #fafafa; }
        .ols-remove-row-btn { background: none; border: none; color: #c33; font-size: 15px; cursor: pointer; }
        #ols-expenses-table { width: 100%; max-width: 700px; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        #ols-expenses-table th, #ols-expenses-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        #ols-expenses-table th { background: #f7f7f7; font-size: 11px; }
        #ols-expenses-table td input, #ols-expenses-table td select {
            width: 100%; box-sizing: border-box; padding: 5px 6px; font-size: 13px;
            border: 1px solid #ccc; border-radius: 3px;
        }
        #ols-expenses-table tfoot td { font-weight: 600; background: #fafafa; }
    </style>

    <script>
    (function () {
        const cleaningFeeInput = document.getElementById('cleaning_fee');
        const cohostPctInput   = document.getElementById('cohost_pct');
        const ownerPctInput    = document.getElementById('owner_pct');
        const tbody            = document.getElementById('ols-payouts-body');
        const addRowBtn        = document.getElementById('ols-add-row-btn');
        let rowCounter = 0;

        // Base Fee = RSVP Fee - Cleaning Fee
        // Cleaning Fee is shown as its own column (same fixed amount every row)
        // Co-Host Payout = Base Fee * cohost%   (does NOT include the cleaning fee)
        // Owner Payout   = Base Fee - (Base Fee * cohost%)
        // This mirrors the PHP math in the admin-post handler exactly —
        // this copy is only for what's shown on screen before submitting.
        function recalcRow(tr) {
            const fee         = parseFloat(tr.querySelector('.fee-input').value) || 0;
            const cleaningFee = parseFloat(cleaningFeeInput.value) || 0;
            const cohostPct   = parseFloat(cohostPctInput.value) || 0;

            const base          = fee - cleaningFee;
            const cohostPortion = base * (cohostPct / 100);
            const owner         = base - cohostPortion;

            tr.querySelector('.base-cell').textContent     = '$' + base.toFixed(2);
            tr.querySelector('.cleaning-cell').textContent = '$' + cleaningFee.toFixed(2);
            tr.querySelector('.cohost-cell').textContent    = '$' + cohostPortion.toFixed(2);
            tr.querySelector('.owner-cell').textContent     = '$' + owner.toFixed(2);
        }

        function recalcTotals() {
            let totalBase = 0, totalCleaning = 0, totalCohost = 0, totalOwner = 0;
            tbody.querySelectorAll('tr').forEach(function (tr) {
                totalBase     += parseFloat(tr.querySelector('.base-cell').textContent.replace('$', '')) || 0;
                totalCleaning += parseFloat(tr.querySelector('.cleaning-cell').textContent.replace('$', '')) || 0;
                totalCohost   += parseFloat(tr.querySelector('.cohost-cell').textContent.replace('$', '')) || 0;
                totalOwner    += parseFloat(tr.querySelector('.owner-cell').textContent.replace('$', '')) || 0;
            });
            document.getElementById('ols-total-base').textContent     = '$' + totalBase.toFixed(2);
            document.getElementById('ols-total-cleaning').textContent = '$' + totalCleaning.toFixed(2);
            document.getElementById('ols-total-cohost').textContent   = '$' + totalCohost.toFixed(2);
            document.getElementById('ols-total-owner').textContent    = '$' + totalOwner.toFixed(2);
        }

        function recalcAll() {
            tbody.querySelectorAll('tr').forEach(recalcRow);
            recalcTotals();
        }

        function addRow() {
            const idx = rowCounter++; // shared index for all 3 fields in this row
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td><input type="date" name="rows[' + idx + '][date]"></td>' +
                '<td><input type="text" name="rows[' + idx + '][name]"></td>' +
                '<td><input type="number" step="0.01" class="fee-input" name="rows[' + idx + '][fee]" value="0"></td>' +
                '<td class="base-cell">$0.00</td>' +
                '<td class="cleaning-cell">$0.00</td>' +
                '<td class="cohost-cell">$0.00</td>' +
                '<td class="owner-cell">$0.00</td>' +
                '<td><button type="button" class="ols-remove-row-btn">&times;</button></td>';
            tbody.appendChild(tr);

            tr.querySelector('.fee-input').addEventListener('input', function () {
                recalcRow(tr);
                recalcTotals();
            });
            tr.querySelector('.ols-remove-row-btn').addEventListener('click', function () {
                tr.remove();
                recalcTotals();
            });
        }

        addRowBtn.addEventListener('click', addRow);
        addRow(); // start with one empty row so the table isn't blank

        cohostPctInput.addEventListener('input', function () {
            const cohost = parseFloat(this.value) || 0;
            ownerPctInput.value = (100 - cohost).toFixed(2);
            recalcAll();
        });
        cleaningFeeInput.addEventListener('input', recalcAll);

        // --- Expenses table (Category / Item Name / Amount) ---
        const expenseCategories = ['Supplies', 'Cleaning Fees', 'Refund', 'Other Expenses'];
        const expenseTbody      = document.getElementById('ols-expenses-body');
        const addExpenseBtn     = document.getElementById('ols-add-expense-btn');
        let expenseRowCounter   = 0;

        function recalcExpenseTotal() {
            let total = 0;
            expenseTbody.querySelectorAll('.expense-amount-input').forEach(function (input) {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('ols-total-expenses').textContent = '$' + total.toFixed(2);
        }

        function addExpenseRow() {
            const idx = expenseRowCounter++;
            const tr = document.createElement('tr');

            const categoryOptions = expenseCategories.map(function (cat) {
                return '<option value="' + cat + '">' + cat + '</option>';
            }).join('');

            tr.innerHTML =
                '<td><select name="expenses[' + idx + '][category]">' + categoryOptions + '</select></td>' +
                '<td><input type="text" name="expenses[' + idx + '][name]"></td>' +
                '<td><input type="number" step="0.01" class="expense-amount-input" name="expenses[' + idx + '][amount]" value="0"></td>' +
                '<td><button type="button" class="ols-remove-row-btn">&times;</button></td>';
            expenseTbody.appendChild(tr);

            tr.querySelector('.expense-amount-input').addEventListener('input', recalcExpenseTotal);
            tr.querySelector('.ols-remove-row-btn').addEventListener('click', function () {
                tr.remove();
                recalcExpenseTotal();
            });
        }

        addExpenseBtn.addEventListener('click', addExpenseRow);
        addExpenseRow(); // start with one empty row so the table isn't blank
    })();
    </script>
    <?php
}

// ---------------------------------------------------------------
// PDF generation + download — hooked via admin-post.php instead of
// living on the same page as the form. This handler owns the whole
// HTTP response, so streaming a PDF binary here can't collide with
// WordPress's own admin page headers/chrome.
// ---------------------------------------------------------------
add_action('admin_post_lbs_generate_owner_statement', 'lbs_generate_owner_statement');

function lbs_generate_owner_statement() {
    if (!current_user_can('manage_options')) {
        wp_die('Not allowed');
    }
    check_admin_referer('lbs_owner_statement', 'lbs_owner_statement_nonce');

    $statement_period = trim($_POST['statement_period'] ?? '');
    $owner_name       = trim($_POST['owner_name'] ?? '');
    $cleaning_fee     = isset($_POST['cleaning_fee']) ? (float) $_POST['cleaning_fee'] : 0.0;
    $cohost_pct       = isset($_POST['cohost_pct']) ? (float) $_POST['cohost_pct'] : 0.0;
    $owner_pct        = 100 - $cohost_pct; // always derived, never trusted from POST directly

    update_option('ols_statement_period', $statement_period);
    update_option('ols_owner_name', $owner_name);
    update_option('ols_cleaning_fee', $cleaning_fee);
    update_option('ols_cohost_pct', $cohost_pct);
    update_option('ols_owner_pct', $owner_pct);

    // ---------------------------------------------------------------
    // Payouts — one row per booking. Base Fee / Co-Host Payout /
    // Owner Payout are always recalculated here, never trusted from
    // the browser, since form values can be edited client-side before
    // submit.
    //
    //   Base Fee       = RSVP Fee - Cleaning Fee
    //   Cleaning Fee    = fixed amount, shown as its own column every row
    //   Co-Host Payout  = Base Fee * (cohost_pct / 100)   (does NOT include
    //                     the cleaning fee — that's its own column)
    //   Owner Payout    = Base Fee - (Base Fee * cohost_pct / 100)
    // ---------------------------------------------------------------
    $inputRows = $_POST['rows'] ?? [];
    $payouts = [];

    foreach ($inputRows as $r) {
        $date = trim($r['date'] ?? '');
        $name = trim($r['name'] ?? '');
        $fee  = (float) ($r['fee'] ?? 0);

        // Skip rows nobody filled in (e.g. a blank row left in the form).
        if ($date === '' && $name === '' && $fee == 0.0) {
            continue;
        }

        $base      = $fee - $cleaning_fee;
        $cohostCut = $base * ($cohost_pct / 100);
        $owner     = $base - $cohostCut;

        $payouts[] = [
            'date'     => $date,
            'name'     => $name,
            'fee'      => $fee,
            'base'     => $base,
            'cleaning' => $cleaning_fee,
            'cohost'   => $cohostCut,
            'owner'    => $owner,
        ];
    }

    // ---------------------------------------------------------------
    // Expenses — Category / Item Name / Amount. Nothing is derived or
    // recalculated here; the amount is whatever was typed in.
    // ---------------------------------------------------------------
    $inputExpenses = $_POST['expenses'] ?? [];
    $expenses = [];

    foreach ($inputExpenses as $e) {
        $category = trim($e['category'] ?? '');
        $name     = trim($e['name'] ?? '');
        $amount   = (float) ($e['amount'] ?? 0);

        // Skip rows nobody filled in (e.g. a blank row left in the form).
        if ($name === '' && $amount == 0.0) {
            continue;
        }

        $expenses[] = compact('category', 'name', 'amount');
    }

    lbs_output_owner_statement_pdf($statement_period, $owner_name, $cleaning_fee, $cohost_pct, $owner_pct, $payouts, $expenses);
    exit;
}

// ---------------------------------------------------------------
// PDF class with a reusable header/footer (so later steps that add
// more pages get the same letterhead automatically)
// ---------------------------------------------------------------
class OwnerStatementPDF extends FPDF
{
    public array $company;

    function Header()
    {
        $c = $this->company;

        // --- Logo mark: simple blue square with a "B", stands in for the real logo ---
        $logoX = 15;
        $logoY = 15;
        $logoSize = 10;
        $this->SetFillColor(30, 90, 220); // brand blue
        $this->Rect($logoX, $logoY, $logoSize, $logoSize, 'F');
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY($logoX, $logoY + 1.5);
        $this->Cell($logoSize, $logoSize - 3, 'B', 0, 0, 'C');

        // --- Company name next to logo ---
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 15);
        $this->SetXY($logoX + $logoSize + 4, $logoY - 1);
        $this->Cell(0, 6, $c['name'], 0, 1);

        // --- Address block (right side, upper) ---
        $this->SetFont('Helvetica', '', 9);
        $this->SetXY(108, $logoY - 1);
        foreach ($c['address_lines'] as $i => $line) {
            $this->SetX(108);
            $this->Cell(36, 4.5, $line, 0, 2);
        }

        // --- Phone block (right side, further right; width 0 lets FPDF size
        // it to the printable margin automatically instead of a fixed mm
        // guess, so it can't run past the page edge) ---
        $this->SetFont('Helvetica', '', 8.5);
        $this->SetXY(146, $logoY - 1);
        foreach ($c['phone_lines'] as $line) {
            $this->SetX(146);
            $this->Cell(0, 4.5, $line, 0, 2);
        }

        // --- Thick black bar under the letterhead ---
        $this->SetY(32);
        $this->SetFillColor(0, 0, 0);
        $this->Rect(15, 32, 180, 4, 'F');

        // --- Big "Owner Statement" title ---
        $this->SetY(42);
        $this->SetFont('Helvetica', 'B', 26);
        $this->Cell(0, 12, 'Owner Statement', 0, 1);

        $this->Ln(4);
    }

    function Footer()
    {
        $c = $this->company;
        $this->SetY(-15);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(90, 90, 90);
        $this->Cell(90, 6, $c['footer_left'], 0, 0, 'L');
        $this->Cell(90, 6, $c['footer_right'], 0, 0, 'R');
    }
}

// --- Builds and streams the PDF as a browser download ---
function lbs_output_owner_statement_pdf($statement_period, $owner_name, $cleaning_fee, $cohost_pct, $owner_pct, $payouts, $expenses = []) {
    $pdf = new OwnerStatementPDF('P', 'mm', 'Letter');
    $pdf->company = lbs_owner_statement_company();
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // --- Owner name + statement period ---
    $pdf->SetFont('Helvetica', 'B', 13);
    $pdf->Cell(0, 8, $owner_name, 0, 1);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell(0, 6, $statement_period, 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(2);

    // --- Cleaning Fee / Co-Host % / Owner % summary line ---
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(60, 6, 'Cleaning Fee: $' . number_format($cleaning_fee, 2), 0, 0);
    $pdf->Cell(60, 6, 'Co-Host Fee: ' . number_format($cohost_pct, 2) . '%', 0, 0);
    $pdf->Cell(0, 6, 'Owner Fee: ' . number_format($owner_pct, 2) . '%', 0, 1);
    $pdf->Ln(4);

    // --- Column widths (mm), totalling 180 to match the letterhead bar ---
    $colWidths = ['date' => 22, 'name' => 30, 'fee' => 22, 'base' => 22, 'cleaning' => 24, 'cohost' => 30, 'owner' => 30];

    $pdf->SetFont('Helvetica', 'B', 13);
    $pdf->Cell(0, 8, 'Payouts', 0, 1);

    // --- Table header row ---
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(247, 247, 247);
    $pdf->Cell($colWidths['date'],     7, 'RSVP Date',      1, 0, 'L', true);
    $pdf->Cell($colWidths['name'],     7, 'RSVP Name',      1, 0, 'L', true);
    $pdf->Cell($colWidths['fee'],      7, 'RSVP Fee',       1, 0, 'R', true);
    $pdf->Cell($colWidths['base'],     7, 'Base Fee',       1, 0, 'R', true);
    $pdf->Cell($colWidths['cleaning'], 7, 'Cleaning Fee',   1, 0, 'R', true);
    $pdf->Cell($colWidths['cohost'],   7, 'Co-Host Payout', 1, 0, 'R', true);
    $pdf->Cell($colWidths['owner'],    7, 'Owner Payout',   1, 1, 'R', true);

    // --- Table data rows ---
    $pdf->SetFont('Helvetica', '', 9);
    $totalBase = $totalCleaning = $totalCohost = $totalOwner = 0.0;

    if (empty($payouts)) {
        $pdf->Cell(array_sum($colWidths), 8, 'No bookings for this period.', 1, 1, 'C');
    } else {
        foreach ($payouts as $p) {
            $pdf->Cell($colWidths['date'],     6, $p['date'], 1, 0, 'L');
            $pdf->Cell($colWidths['name'],     6, $p['name'], 1, 0, 'L');
            $pdf->Cell($colWidths['fee'],      6, '$' . number_format($p['fee'], 2), 1, 0, 'R');
            $pdf->Cell($colWidths['base'],     6, '$' . number_format($p['base'], 2), 1, 0, 'R');
            $pdf->Cell($colWidths['cleaning'], 6, '$' . number_format($p['cleaning'], 2), 1, 0, 'R');
            $pdf->Cell($colWidths['cohost'],   6, '$' . number_format($p['cohost'], 2), 1, 0, 'R');
            $pdf->Cell($colWidths['owner'],    6, '$' . number_format($p['owner'], 2), 1, 1, 'R');

            $totalBase     += $p['base'];
            $totalCleaning += $p['cleaning'];
            $totalCohost   += $p['cohost'];
            $totalOwner    += $p['owner'];
        }

        // --- Totals row ---
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(250, 250, 250);
        $pdf->Cell($colWidths['date'] + $colWidths['name'] + $colWidths['fee'], 7, 'Totals', 1, 0, 'R', true);
        $pdf->Cell($colWidths['base'],     7, '$' . number_format($totalBase, 2), 1, 0, 'R', true);
        $pdf->Cell($colWidths['cleaning'], 7, '$' . number_format($totalCleaning, 2), 1, 0, 'R', true);
        $pdf->Cell($colWidths['cohost'],   7, '$' . number_format($totalCohost, 2), 1, 0, 'R', true);
        $pdf->Cell($colWidths['owner'],    7, '$' . number_format($totalOwner, 2), 1, 1, 'R', true);
    }

    // ---------------------------------------------------------------
    // Expenses section — Category / Item Name / Amount
    // ---------------------------------------------------------------
    $pdf->Ln(6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Helvetica', 'B', 13);
    $pdf->Cell(0, 8, 'Expenses', 0, 1);

    $expenseColWidths = ['category' => 55, 'name' => 70, 'amount' => 55];

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetFillColor(247, 247, 247);
    $pdf->Cell($expenseColWidths['category'], 7, 'Category',   1, 0, 'L', true);
    $pdf->Cell($expenseColWidths['name'],     7, 'Item Name',  1, 0, 'L', true);
    $pdf->Cell($expenseColWidths['amount'],   7, 'Amount',     1, 1, 'R', true);

    $pdf->SetFont('Helvetica', '', 9);
    $totalExpenses = 0.0;

    if (empty($expenses)) {
        $pdf->Cell(array_sum($expenseColWidths), 8, 'No expenses for this period.', 1, 1, 'C');
    } else {
        foreach ($expenses as $e) {
            $pdf->Cell($expenseColWidths['category'], 6, $e['category'], 1, 0, 'L');
            $pdf->Cell($expenseColWidths['name'],     6, $e['name'], 1, 0, 'L');
            $pdf->Cell($expenseColWidths['amount'],   6, '$' . number_format($e['amount'], 2), 1, 1, 'R');
            $totalExpenses += $e['amount'];
        }

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetFillColor(250, 250, 250);
        $pdf->Cell($expenseColWidths['category'] + $expenseColWidths['name'], 7, 'Totals', 1, 0, 'R', true);
        $pdf->Cell($expenseColWidths['amount'], 7, '$' . number_format($totalExpenses, 2), 1, 1, 'R', true);
    }

    $pdf->Output('D', 'owner_statement.pdf');
}