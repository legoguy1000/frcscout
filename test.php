<?php
if(empty($_SERVER["SSL_CLIENT_S_DN_CN"]))
{
	die();
}
$nameArr = explode('.',$_SERVER["SSL_CLIENT_S_DN_CN"]);
$fname = $nameArr[1];
$lname = $nameArr[0];
$mI = $nameArr[2];
$idNum = $nameArr[3];

echo $_SERVER["SSL_CLIENT_S_DN_CN"];
ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
putenv('TLS_REQCERT=never');
$ds=ldap_connect("ldaps://dod411.gds.disa.mil",636);  // must be a valid LDAP server!
if ($ds) 
{ 
	if(!ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)){
		print "Could not set LDAPv3\r\n";
	}
	else
	{	
		$r=ldap_bind($ds);  
		if($r)
		{
			$query = 'cn=*.'.$idNum;
			$sr=ldap_search($ds, "ou=pki,ou=dod,o=u.s. government,c=us", $query);
			$num = ldap_count_entries($ds, $sr) ;
			$info = ldap_get_entries($ds, $sr);

			for ($i=0; $i<$info["count"]; $i++) 
			{
				foreach($info[$i] as $attrib=>$values)
				{
					if(is_numeric($attrib))
					{
						continue;
					}
					if(is_array($values))
					{
						if(count($values) > 1)
						{
							echo '<b>'.$attrib."</b><br />";
							for($i=0;$i<count($values);$i++)
							{
								echo $values[$i]."<br />";
							}
						}
						else
						{
							echo '<b>'.$attrib.'</b>  '.$values[0].'<br />';
						}
					}
					else
					{
						echo '<b>'.$attrib.'</b>  '.$values.'<br />';
					}
				}
			}
		}
	}
}

?>
