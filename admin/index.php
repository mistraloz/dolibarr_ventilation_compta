<?php
/* Copyright (C) 2013-2014 Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2013-2014 Alexandre Spangaro	<alexandre.spangaro@gmail.com>
 * Copyright (C) 2014      Ari Elbaz (elarifr)	<github@accedinfo.com>
 * Copyright (C) 2014      Marcos García        <marcosgdf@gmail.com> 
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * \file		htdocs/accountingex/admin/index.php
 * \ingroup		Accounting Expert
 * \brief		Setup page to configure accounting expert module
 */

// Dolibarr environment
$res = @include ("../main.inc.php");
if (! $res && file_exists("../main.inc.php"))
	$res = @include ("../main.inc.php");
if (! $res && file_exists("../../main.inc.php"))
	$res = @include ("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php"))
	$res = @include ("../../../main.inc.php");
if (! $res)
	die("Include of main fails");
	
	// Class
dol_include_once("/core/lib/admin.lib.php");
dol_include_once("/accountingex/core/lib/account.lib.php");

$langs->load("compta");
$langs->load("bills");
$langs->load('admin');
$langs->load('accountingex@accountingex');

// Security check
if ($user->societe_id > 0)
	accessforbidden();
if (! $user->rights->accountingex->admin)
	accessforbidden();

$action = GETPOST('action', 'alpha');

// Other parameters COMPTA_* & ACCOUNTINGEX_*
$list = array (
		'ACCOUNTINGEX_LIMIT_LIST_VENTILATION',
		'ACCOUNTINGEX_LENGTH_GACCOUNT',
		'ACCOUNTINGEX_LENGTH_AACCOUNT',
		'COMPTA_ACCOUNT_CUSTOMER',
		'COMPTA_ACCOUNT_SUPPLIER',
		'COMPTA_PRODUCT_BUY_ACCOUNT',
		'COMPTA_PRODUCT_SOLD_ACCOUNT',
		'COMPTA_SERVICE_BUY_ACCOUNT',
		'COMPTA_SERVICE_SOLD_ACCOUNT',
		'ACCOUNTINGEX_ACCOUNT_SUSPENSE',
		'ACCOUNTINGEX_ACCOUNT_TRANSFER_CASH' 
);

/*
 * Actions
 */
 
$compta_mode = defined('COMPTA_MODE')?COMPTA_MODE:'RECETTES-DEPENSES';

if ($action == 'update')
{
    $error = 0;

    $compta_modes = array(
        'RECETTES-DEPENSES',
        'CREANCES-DETTES'
    );

    $compta_mode = GETPOST('compta_mode','alpha');
	
	if (in_array($compta_mode,$compta_modes)) {

        if (!dolibarr_set_const($db, 'COMPTA_MODE', $compta_mode, 'chaine', 0, '', $conf->entity)) {
            $error++;
        }
    } else {
        $error++;
    }
	
	$chartofaccounts = GETPOST('chartofaccounts', 'int');
	
	if (! empty($chartofaccounts)) {
		
		if (! dolibarr_set_const($db, 'CHARTOFACCOUNTS', $chartofaccounts, 'chaine', 0, '', $conf->entity)) {
			$error ++;
		}
	} else {
		$error ++;
	}
	
    foreach ($list as $constname) {
        $constvalue = GETPOST($constname, 'alpha');

        if (!dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity)) {
            $error++;
        }
    }

    if (! $error)
    {
        setEventMessage($langs->trans("SetupSaved"));
    }
    else
    {
        setEventMessage($langs->trans("Error"),'errors');
    }
}

if ($action == 'setlistsorttodo') {
	$setlistsorttodo = GETPOST('value', 'int');
	$res = dolibarr_set_const($db, "ACCOUNTINGEX_LIST_SORT_VENTILATION_TODO", $setlistsorttodo, 'yesno', 0, '', $conf->entity);
	if (! $res > 0)
		$error ++;
	
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"), 'mesgs');
	} else {
		setEventMessage($langs->trans("Error"), 'mesgs');
	}
}

if ($action == 'setlistsortdone') {
	$setlistsortdone = GETPOST('value', 'int');
	$res = dolibarr_set_const($db, "ACCOUNTINGEX_LIST_SORT_VENTILATION_DONE", $setlistsortdone, 'yesno', 0, '', $conf->entity);
	if (! $res > 0)
		$error ++;
	if (! $error) {
		setEventMessage($langs->trans("SetupSaved"), 'mesgs');
	} else {
		setEventMessage($langs->trans("Error"), 'mesgs');
	}
}

/*
 * View
 */

llxHeader();

$form = new Form($db);

print_fiche_titre($langs->trans('ConfigAccountingExpert'));

$head = admin_account_prepare_head($accounting);

dol_fiche_head($head, 'general', $langs->trans("Configuration"), 0, 'cron');

print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder" width="100%">';

// Cas du parametre COMPTA_MODE

print '<tr class="liste_titre">';
print '<td>'.$langs->trans('OptionMode').'</td><td>'.$langs->trans('Description').'</td>';
print "</tr>\n";
print '<tr '.$bc[false].'><td width="200"><input type="radio" name="compta_mode" value="RECETTES-DEPENSES"'.($compta_mode != 'CREANCES-DETTES' ? ' checked' : '').'> '.$langs->trans('OptionModeTrue').'</td>';
print '<td colspan="2">'.nl2br($langs->trans('OptionModeTrueDesc'));
// Write info on way to count VAT
//if (! empty($conf->global->MAIN_MODULE_COMPTABILITE))
//{
//	//	print "<br>\n";
//	//	print nl2br($langs->trans('OptionModeTrueInfoModuleComptabilite'));
//}
//else
//{
//	//	print "<br>\n";
//	//	print nl2br($langs->trans('OptionModeTrueInfoExpert'));
//}
print "</td></tr>\n";
print '<tr '.$bc[true].'><td width="200"><input type="radio" name="compta_mode" value="CREANCES-DETTES"'.($compta_mode == 'CREANCES-DETTES' ? ' checked' : '').'> '.$langs->trans('OptionModeVirtual').'</td>';
print '<td colspan="2">'.nl2br($langs->trans('OptionModeVirtualDesc'))."</td></tr>\n";
print '</form>';

print "</table>\n";

print "<br>\n";

/*
 *  Define Chart of accounts
 */
print '<table class="noborder" width="100%">';
$var = True;

print '<tr class="liste_titre">';
print '<td colspan="3">';
print $langs->trans("Chartofaccounts") . '</td>';
print "</tr>\n";
$var = ! $var;
print '<tr ' . $bc[$var] . '>';
print "<td>" . $langs->trans("Selectchartofaccounts") . "</td>";
print "<td>";
print '<select class="flat" name="chartofaccounts" id="chartofaccounts">';

$sql = "SELECT rowid, pcg_version, fk_pays, label, active";
$sql .= " FROM " . MAIN_DB_PREFIX . "accounting_system";
$sql .= " WHERE active = 1";
$sql .= " AND fk_pays = " . $mysoc->country_id;

dol_syslog('accountingex/admin/index.php:: $sql=' . $sql);
$resql = $db->query($sql);

$var = True;

if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;
	while ( $i < $num ) {
		$var = ! $var;
		$row = $db->fetch_row($resql);
		
		print '<option value="' . $row[0] . '"';
		print $conf->global->CHARTOFACCOUNTS == $row[0] ? ' selected="selected"' : '';
		print '>' . $row[1] . ' - ' . $row[3] . '</option>';
		
		$i ++;
	}
}
print "</select>";
print "</td></tr>";
print "</table>";

print "<br>\n";

/*
 *  Others params
 */
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td colspan="3">' . $langs->trans('OtherOptions') . '</td>';
print "</tr>\n";

foreach ($list as $key)
{
	$var=!$var;

	print '<tr '.$bc[$var].' class="value">';

	// Param
	$label = $langs->trans($key); 
	print '<td><label for="'.$key.'">'.$label.'</label></td>';

	// Value
	print '<td>';
	print '<input type="text" size="20" id="'.$key.'" name="'.$key.'" value="'.$conf->global->$key.'">';
	print '</td></tr>';
}

$var = ! $var;
print "<tr " . $bc[$var] . ">";
print '<td width="80%">' . $langs->trans("ACCOUNTINGEX_LIST_SORT_VENTILATION_TODO") . '</td>';
if (! empty($conf->global->ACCOUNTINGEX_LIST_SORT_VENTILATION_TODO)) {
	print '<td align="center" colspan="2"><a href="' . $_SERVER['PHP_SELF'] . '?action=setlistsorttodo&value=0">';
	print img_picto($langs->trans("Activated"), 'switch_on');
	print '</a></td>';
} else {
	print '<td align="center" colspan="2"><a href="' . $_SERVER['PHP_SELF'] . '?action=setlistsorttodo&value=1">';
	print img_picto($langs->trans("Disabled"), 'switch_off');
	print '</a></td>';
}
print '</tr>';

$var = ! $var;
print "<tr " . $bc[$var] . ">";
print '<td width="80%">' . $langs->trans("ACCOUNTINGEX_LIST_SORT_VENTILATION_DONE") . '</td>';
if (! empty($conf->global->ACCOUNTINGEX_LIST_SORT_VENTILATION_DONE)) {
	print '<td align="center" colspan="2"><a href="' . $_SERVER['PHP_SELF'] . '?action=setlistsortdone&value=0">';
	print img_picto($langs->trans("Activated"), 'switch_on');
	print '</a></td>';
} else {
	print '<td align="center" colspan="2"><a href="' . $_SERVER['PHP_SELF'] . '?action=setlistsortdone&value=1">';
	print img_picto($langs->trans("Disabled"), 'switch_off');
	print '</a></td>';
}
print '</tr>';

print '</form>';
print "</table>\n";

print '<br /><div style="text-align:center"><input type="submit" class="button" value="'.$langs->trans('Modify').'" name="button"></div>';

llxFooter();
$db->close();