<?
global $usr;
?>
<form name="optionsform" style="display:inline;" action='admin_searchuser.php' method="POST">
<table class="content" border="0" cellspacing="0px" cellpadding="0px">
<tr><td class="header"><img src="tpl/stdstyle/images/cache/traditional.png" border="0" width="32" height="32" alt="" title="" align="middle"><font size="4">  <b>Szukaj użytkownika</b></font></td></tr>
<tr><td class="spacer"></td></tr>
<tr>
	<td>
		{not_found}
		<input type="text" name="username" value="{username}" class="input200" /> <input type="submit" value="Szukaj" class="formbuttons" /><br/>
	</td>
</tr>
</table>
</form>