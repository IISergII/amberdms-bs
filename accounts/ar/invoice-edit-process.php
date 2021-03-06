<?php
/*
	accounts/ar/invoice-edit-process.php

	access: accounts_ar_write

	Allows new invoices to be added to the database, or for existing invoices to be adjusted.
*/

// includes
require("../../include/config.php");
require("../../include/amberphplib/main.php");

// custom includes
require("../../include/accounts/inc_invoices_process.php");


if (user_permissions_get('accounts_ar_write'))
{
	/*
		Let the invoices functions do all the work for us
	*/

	if ($_POST["id_invoice"])
	{
		// edit invoice
		$mode			= "edit";
		$returnpage_error	= "accounts/ar/invoice-view.php";
		$returnpage_success	= "accounts/ar/invoice-view.php";
	}
	else
	{
		// new invoice
		$mode			= "add";
		$returnpage_error	= "accounts/ar/invoice-add.php";
		$returnpage_success	= "accounts/ar/invoice-view.php";
	}

	invoice_form_details_process("ar", $mode, $returnpage_error, $returnpage_success);
}
else
{
	// user does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
