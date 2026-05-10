<?php

// Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\DrugSalesService;

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Dispense Drug")]);
    exit;
}

$saleId = (int)($_REQUEST['sale_id'] ?? 0);
if ($saleId <= 0) {
    die(xlt('Missing sale_id'));
}

$labelData  = (new DrugSalesService())->renderBottleLabel($saleId);
$headerText = $labelData['header_text'];
$labelText  = $labelData['label_text'];

$dconfig = $GLOBALS['oer_config']['druglabels'];

// We originally went for PDF output on the theory that output formatting
// would be more controlled.  However the clumsiness of invoking a PDF
// viewer from the browser becomes intolerable in a POS environment, and
// printing HTML is much faster and easier if the browser's page setup is
// configured properly.
//
if (false) { // if PDF output is desired
    $pdf = new Cezpdf($dconfig['paper_size']);
    $pdf->ezSetMargins($dconfig['top'], $dconfig['bottom'], $dconfig['left'], $dconfig['right']);
    $pdf->selectFont('Helvetica');
    $pdf->ezSetDy(20); // dunno why we have to do this...
    $pdf->ezText($headerText, 7, ['justification' => 'center']);
    if (!empty($dconfig['logo'])) {
        $pdf->ezSetDy(-5); // add space (move down) before the image
        $pdf->ezImage($dconfig['logo'], 0, 180, '', 'left');
        $pdf->ezSetDy(8);  // reduce space (move up) after the image
    }

    $pdf->ezText($labelText, 9, ['justification' => 'center']);
    $pdf->ezStream();
} else { // HTML output
    ?>
<html>
    <script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
<head>
<style>
body {
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
}
.labtop {
    color: #000000;
    font-family: sans-serif;
    font-size: 7pt;
    font-weight: normal;
    text-align: center;
    padding-bottom: 1pt;
}
.labbot {
    color: #000000;
    font-family: sans-serif;
    font-size: 9pt;
    font-weight: normal;
    text-align: center;
    padding-top: 2pt;
}
</style>
   <title><?php echo xlt('Prescription Label'); ?></title>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>
<table border='0' cellpadding='0' cellspacing='0' style='width: 200pt'>
 <tr><td class="labtop" nowrap>
        <?php echo nl2br(text($headerText)); ?>
 </td></tr>
 <tr><td style='background-color: #000000; height: 5pt;'></td></tr>
 <tr><td class="labbot" nowrap>
        <?php echo nl2br(text($labelText)); ?>
 </td></tr>
</table>
</center>
<script>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
</script>
</body>
</html>
    <?php
}
?>
