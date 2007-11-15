<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	$DBRESULT =& $pearDB->query("SELECT ldap_host, ldap_port, ldap_base_dn, ldap_login_attrib, ldap_ssl, ldap_auth_enable, ldap_search_user, ldap_search_user_pwd, ldap_search_filter, ldap_search_timeout, ldap_search_limit FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$ldap_auth = array_map("myDecode", $DBRESULT->fetchRow());

	$attrsText 	= array("size"=>"80");
	$attrsText2	= array("size"=>"5");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p );
	$form->addElement('header', 'title',$lang['cct_ldap_search']);

	#
	## Command information
	#
	$form->addElement('header', 'options', $lang['cct_ldap_search_options']);
	$form->addElement('text', 'ldap_search_filter', $lang['cct_ldap_search_filter'], $attrsText );
	$form->addElement('text', 'ldap_base_dn', $lang["genOpt_ldap_base_dn"], $attrsText);
	$form->addElement('text', 'ldap_search_timeout', $lang["genOpt_ldap_search_timeout"], $attrsText2);
	$form->addElement('text', 'ldap_search_limit', $lang["genOpt_ldap_search_limit"], $attrsText2);
	$form->addElement('header', 'result', $lang['cct_ldap_search_result']);
	$form->addElement('header', 'ldap_search_result_output', $lang["cct_ldap_search_result_output"]);

	$link = "LdapSearch()";
	$form->addElement("button", "ldap_search_button", $lang['cct_ldap_search'], array("onClick"=>$link));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'contact_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign('ldap_search_filter_help', $lang["cct_ldap_search_filter_help"]);
	$tpl->assign('ldap_search_filter_help_title', $lang["cct_ldap_search_filter_help_title"]);
	$tpl->assign('javascript', '<script type="text/javascript" src="./include/common/javascript/ajaxLdapSearch.js"></script>');

	# Just watch a contact information
	if ($o == "li")	{
		$subA =& $form->addElement('submit', 'submitA', $lang['cct_ldap_import_users']);
		$form->setDefaults($ldap_auth);
	}

	$valid = false;
	if ($form->validate())	{
		if (isset($_POST["contact_select"]["select"]) ) {
			if ($form->getSubmitValue("submitA"))
				insertLdapContactInDB($_POST["contact_select"]);
			}
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listContact.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("ldapImportContact.ihtml");
	}
?>