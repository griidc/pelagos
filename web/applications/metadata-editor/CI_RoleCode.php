<?php
#include 'CI_RoleCode_DL.php';


class CI_RoleCode
{
	
	public function __construct($instanceName)
	{
		echo '<fieldset>';
		echo '<legend>Role_'.$instanceName.'</legend>';
		
		echo '<label for="gmd:role_'.$instanceName.'">role</label>';
		echo '<select name="gmd:role_'.$instanceName.'" xmlclass="gmd:role" xmltype="<gmd:CI_RoleCode>"</select>';
		echo '<option>CI_RoleCode_resourceProvider</option>';
		echo '<option>CI_RoleCode_custodian</option>';
		echo '<option>CI_RoleCode_owner</option>';
		echo '<option>CI_RoleCode_user</option>';
		echo '<option>CI_RoleCode_distributor</option>';
		echo '<option>CI_RoleCode_originator</option>';
		echo '<option>CI_RoleCode_pointOfContact</option>';
		echo '<option selected>CI_RoleCode_principalInvestigator</option>';
		echo '<option>CI_RoleCode_processor</option>';
		echo '<option>CI_RoleCode_publisher</option>';
		echo '<option>CI_RoleCode_author</option>';
		echo '</select>';
		
		echo '</fieldset>';
	}
	
}


		
		/*		
	<fieldset>
	<legend>Role</legend>
	
		<label for="gmd:role">role</label>
		<select name="gmd:role" xmltype="<gmd:CI_RoleCode>"</select>
			<option>CI_RoleCode_resourceProvider</option>
			<option>CI_RoleCode_custodian</option>
			<option>CI_RoleCode_owner</option>
			<option>CI_RoleCode_user</option>
			<option>CI_RoleCode_distributor</option>
			<option>CI_RoleCode_originator</option>
			<option>CI_RoleCode_pointOfContact</option>
			<option selected>CI_RoleCode_principalInvestigator</option>
			<option>CI_RoleCode_processor</option>
			<option>CI_RoleCode_publisher</option>
			<option>CI_RoleCode_author</option>
		</select>
		
		

	
	</fieldset>
	*/
?>	