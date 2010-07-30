<?php
/*
	admin/templates.php
	
	access: admin users only

	Allows administrators to configure which template is used when generating PDF documents.
*/

class page_output
{
	var $obj_form;
	var $obj_form_invoice_email;
	var $obj_form_quote_email;
	//var $obj_sql_ar_invoice;
	var $obj_sql_invoice_data = array();

	function page_output()
	{
		// define page dependencies
		$this->requires["css"][]		= "include/admin/css/templates.css";
		$this->requires["javascript"][]		= "include/admin/javascript/templates.js";
	}
	
	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing to do
		return 1;
	}


	function execute()
	{

		/*
			Fetch Template Information
		*/
		$obj_sql = New sql_query;
		$obj_sql->string	= "SELECT id, active, template_file, template_name, template_description FROM templates WHERE template_type IN('ar_invoice_tex', 'ar_invoice_htmltopdf') ORDER BY template_name";
		$obj_sql->execute();
		$obj_sql->fetch_array();
		$this->obj_sql_invoice_data['ar_invoice'] = array(
			'name' => "AR Invoices",
			'templates' => $obj_sql->data
		);
		unset($obj_sql);
		
		
		$obj_sql = New sql_query;
		$obj_sql->string	= "SELECT id, active, template_file, template_name, template_description FROM templates WHERE template_type IN('quotes_invoice_tex', 'quotes_invoice_htmltopdf') ORDER BY template_name";
		$obj_sql->execute();
		$obj_sql->fetch_array();
		$this->obj_sql_invoice_data['quotes_invoice'] = array(
			'name' => "Quotes",
			'templates' => $obj_sql->data
		);
		unset($obj_sql);
		
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->action = "admin/templates-process.php";
		$this->obj_form->method = "post";
		
		
		
		/*
			Define invoice email template form
		*/
		
		$this->obj_form_invoice_email = New form_input;
		$this->obj_form_invoice_email->formname = "invoice_email_template";
		$this->obj_form_invoice_email->language = $_SESSION["user"]["lang"];

		$this->obj_form_invoice_email->action = "admin/templates-process.php";
		$this->obj_form_invoice_email->method = "post";
		
		// message
		$structure = NULL;
		$structure["fieldname"] 	= "email_message";
		$structure["type"]		= "textarea";
		$structure["defaultvalue"]	= sql_get_singlevalue("SELECT value FROM config WHERE name IN('TEMPLATE_INVOICE_EMAIL') LIMIT 1");
		$structure["options"]["width"]	= "600";
		$structure["options"]["height"]	= "100";
		$this->obj_form_invoice_email->add_input($structure);
		
		// action type
		$structure = NULL;
		$structure["fieldname"]		= "action";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= 'email_template';
		$this->obj_form_invoice_email->add_input($structure);	
		
		// template type
		$structure = NULL;
		$structure["fieldname"]		= "template_type";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= 'invoice';
		$this->obj_form_invoice_email->add_input($structure);	
		
		// submit button
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save";
		$this->obj_form_invoice_email->add_input($structure);
		
					
		$this->obj_form_invoice_email->subforms["hidden"]			= array("action", "template_type");
		$this->obj_form_invoice_email->subforms["Invoice Email"]	= array("email_message", "submit");

		
		/*
			Define quote email template form
		*/
		$this->obj_form_quote_email = New form_input;
		$this->obj_form_quote_email->formname = "invoice_email_template";
		$this->obj_form_quote_email->language = $_SESSION["user"]["lang"];

		$this->obj_form_quote_email->action = "admin/templates-process.php";
		$this->obj_form_quote_email->method = "post";
		
		
		// message
		$structure = NULL;
		$structure["fieldname"] 	= "email_message";
		$structure["type"]		= "textarea";
		$structure["defaultvalue"]	= sql_get_singlevalue("SELECT value FROM config WHERE name IN('TEMPLATE_QUOTE_EMAIL') LIMIT 1");
		$structure["options"]["width"]	= "600";
		$structure["options"]["height"]	= "100";
		$this->obj_form_quote_email->add_input($structure);
		
		// action type
		$structure = NULL;
		$structure["fieldname"]		= "action";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= 'email_template';
		$this->obj_form_quote_email->add_input($structure);	
		
		// template type
		$structure = NULL;
		$structure["fieldname"]		= "template_type";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= 'quote';
		$this->obj_form_quote_email->add_input($structure);	
		
		
		// submit button
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save";
		$this->obj_form_quote_email->add_input($structure);
					
		$this->obj_form_quote_email->subforms["hidden"]			= array("action", "template_type");
		$this->obj_form_quote_email->subforms["Quote Email"]	= array("email_message", "submit");
		
		
		
	}



	function render_html()
	{
		// Title + Summary
		print "<h3>TEMPLATE SELECTION</h3><br>";
		print "<p>You can adjust the PDFs generated by the Amberdms Billing System by using different templates - these templates may include different languages, different layouts or other styling effects.</p>";

		format_msgbox("info", "<p>If you have made your own template you would like to contribute back or if you need customisation work, please contact <a href=\"mailto:support@amberdms.com\">support@amberdms.com</a> for details and we will be happy to assist.</p>");
		
		
		foreach ( $this->obj_sql_invoice_data as $invoice_type_name => $invoice_type_data )
		{
			
			
			
			foreach ($invoice_type_data['templates'] as $data_sql)
			{
				if ($data_sql["active"])
				{
					$current_template_url = $data_sql["template_file"]. "_icon.png";
					$current_template_name = $data_sql["template_name"];
					$current_template_description = $data_sql["template_description"];
					break;
				}
			}
			
			print "<form action=". $this->obj_form->action ." method=". $this->obj_form->method .">";
			print "<br /><br /><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" class=\"template_table\">";
				print "<tr class=\"current_template_row\">";
					print "<td class=\"current_template_cell\" colspan=\"2\">";
					print "<div class=\"current_template_header\"><p><h3>".$invoice_type_data['name'].":</h3></p></div>";
					print "<p><img class=\"current_template_image\" src=\"". $current_template_url ."\" /></p>";
						print "<p class=\"current_template_description\"><b>". $current_template_name ."</b><br />";
						print  $current_template_description ."</p><br />";
						print "<p><strong><a class=\"change_template\" id=\"change_template\" href=\"\">Change...</a></strong></p>";
					print "</td>";
					
					print "<td class=\"filler_cell\">";
					print "</td>";
					
					print "<td class=\"filler_cell\">";
					print "</td>";
				print "</tr>";
				
				$j = -1;
				$array = array();
				for ($i=0; $i<count($invoice_type_data['templates']); $i++)
				{
					if ($i%3 == 0)
					{
						$j++;
					}
					
					$array[$j][] = $i;
				}
				
	
				for ($j=0; $j<count($array); $j++)
				{
					//images
					print "<tr class=\"available_templates_row ar_invoices_templates\">";
						for ($i=0; $i<3; $i++)
						{
							if (isset($array[$j][$i]))
							{
								$id = $array[$j][$i];
								print "<td class=\"available_templates_cell\">";
									print "<img  src=\"". $invoice_type_data['templates'][$id]["template_file"] ."_icon.png\">";
								print "</td>";
							}
							else
							{
								print "<td class=\"filler_cell\">";
								print "</td>";	
							}
						}
					print "</tr>";
					
					//details
					print "<tr class=\"available_templates_row ar_invoices_templates\">";
						for ($i=0; $i<3; $i++)
						{
							if (isset($array[$j][$i]))
							{
								$id = $array[$j][$i];
								print "<td class=\"available_templates_cell\" valign=\"top\">";
									print "<p><strong>". $invoice_type_data['templates'][$id]["template_name"] ."</strong></p>";
									print "<p>" .$invoice_type_data['templates']["template_description"] ."</p>";
								print "</td>";
							}
							else
							{
								print "<td class=\"filler_cell\">";
								print "</td>";	
							}
						}
					print "</tr>";
					
					//select
					print "<tr class=\"available_templates_row ".$invoice_type_name."_templates\">";
						for ($i=0; $i<3; $i++)
						{
							if (isset($array[$j][$i]))
							{
								$id = $array[$j][$i];
								print "<td class=\"available_templates_cell\">";
									print "<p><input type=\"radio\" name=\"selected_template\" id=\"".$invoice_type_data['templates'][$id]["id"]."\" value=\"". $invoice_type_data['templates'][$id]["id"] ."\" ";
										if ($invoice_type_data['templates'][$id]["active"])
										{
											print "checked ";
										}
										print "><label for=\"". $invoice_type_data['templates'][$id]["id"]. "\"> ". lang_trans("use_this_template") ."</label></p>";
								print "</td>";
							}
							else
							{
								print "<td class=\"filler_cell\">";
								print "</td>";	
							}
						}
					print "</tr>";
				}
				
				print "<tr class=\"available_templates_row ar_invoices_templates\">";
					print "<td class=\"available_templates_cell\" colspan=\"3\">";
						print "<input type=\"hidden\" name=\"action\" value=\"pdf_template\">&nbsp;&nbsp;";
						print "<input type=\"hidden\" name=\"template_type\" value=\"".$invoice_type_name."\">&nbsp;&nbsp;";
						print "<input type=\"submit\" value=\"Save Changes\">&nbsp;&nbsp;";
						print "<input type=\"button\" value=\"Cancel\" class=\"cancelbutton\">";
						print "<br />&nbsp;";
					print "</td>";
				print "</tr>";
			print "</table>";
			print "</form>";
		}
		
		// Email Templates
		print "<div>"; 
			print "<p><h3>Email Templates:</h3></p>";
			// Invoice Email Template
			$this->obj_form_invoice_email->render_form();
		
		
			// Quote Email Template
		
			$this->obj_form_quote_email->render_form();
		print "</div>";
		
	}

	

}
?>
