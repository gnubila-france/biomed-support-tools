<?php 
//---------------------------------------------------------------------------------
// Global definitions 
//
// Author: Franck MICHEL, CNRS, I3S lab. fmichel[at]i3s[dot]unice[dot]fr
//---------------------------------------------------------------------------------

// Version marker
define("VERSION", "v1.01 - Aug. 31st, 2011. Author: F.Michel, <a href=\"http://www.i3s.unice.fr/\">CNRS I3S</a>");

// Max number of SE by page
$PAGE_MAX_SIZE = 25;

// Log file to trace accesses 
define("LOG_FILE_ACCESS", "logs/access.log");

// $list_se: this is the list of Storage Elements supporting the Biomed VO
// The list of SEs can be used either in one bloc all on the same page or with a paging mechanism
$list_se = array("arc.univ.kiev.ua","axon-g05.ieeta.pt","bohr3226.tier2.hep.manchester.ac.uk","ccsrm02.in2p3.fr","ccsrm.ihep.ac.cn","cirigridse01.univ-bpclermont.fr","clrlcgse01.in2p3.fr","cn.atauni.edu.tr", "darkmass.wcss.wroc.pl", "dc2-grid-64.brunel.ac.uk","dcache-se-desy.desy.de","dgc-grid-50.brunel.ac.uk","dpm.cyf-kr.edu.pl","egee2.irb.hr","egee-se.grid.niif.hu","epgse1.ph.bham.ac.uk","eymir.grid.metu.edu.tr","fal-pygrid-30.lancs.ac.uk","fornax-se2.itwm.fhg.de","fornax-se.itwm.fhg.de","gfe02.grid.hep.ph.ic.ac.uk","gilda-02.pd.infn.it","grid001.jet.efda.org","grid002.ics.forth.gr","grid003.ca.infn.it","grid02.erciyes.edu.tr","grid05.lal.in2p3.fr","grid2.fe.infn.it","griditse01.na.infn.it","grid-se.ii.edu.mk","gridse.ilc.cnr.it","grid-se.lns.infn.it","gridsrm.pi.infn.it","gridsrm.ts.infn.it","gridsrv3-4.dir.garr.it","gridstore.cs.tcd.ie","gridstore.scg.nuigalway.ie","gridstore.ucc.ie","grisuse.scope.unina.it","grive12.ibcp.fr","hades.up.pt","hepgrid11.ph.liv.ac.uk","heplnx204.pp.rl.ac.uk","lcg59.sinp.msu.ru","lcgse0.shef.ac.uk","lnx097.eela.if.ufrj.br","lnx105.eela.if.ufrj.br","lpsc-se-dpm-server.in2p3.fr","lptadpmsv.msfg.fr","lxse-dc01.jinr.ru","marsedpm.in2p3.fr","moboro.uniandes.edu.co","ngiesse.i3m.upv.es","node12.datagrid.cea.fr","ophelia.zih.tu-dresden.de","plethon.grid.ucy.ac.cy","polgrid4.in2p3.fr","prod-se-01.pd.infn.it","prod-se-02.ct.infn.it","prod-se-02.pd.infn.it","reyhan.grid.boun.edu.tr","sampase.if.usp.br","sbgse1.in2p3.fr","scaise-2.scai.fraunhofer.de","se001.grid.uni-sofia.bg","se001.imbm.bas.bg","se001.ipp.acad.bg","se01.afroditi.hellasgrid.gr","se01.ariagni.hellasgrid.gr","se01.athena.hellasgrid.gr","se01.cat.cbpf.br","se01.dur.scotgrid.ac.uk","se01.grid.auth.gr","se01.grid.uoi.gr","se01.isabella.grnet.gr","se01.kallisto.hellasgrid.gr","se01.marie.hellasgrid.gr","se01-tic.ciemat.es","se02.cat.cbpf.br","se02.marie.hellasgrid.gr","se03.grid.acad.bg","se05.lip.pt","se0.m3pec.u-bordeaux1.fr","se1-egee.fesb.hr","se1-egee.srce.hr","se2.egee.cesga.es","se2.ppgrid1.rhul.ac.uk","se-dpm-server-grid.obspm.fr","se.grid.rug.nl","se.ngcc.acad.bg","se.polgrid.pl","se.reef.man.poznan.pl","sereine.univ-lille1.fr","serv02.hep.phy.cam.ac.uk","se.scope.unina.it","se-srm-00.to.infn.it","se.ui.savba.sk","spacina-se.scope.unina.it","srm01.ncg.ingrid.pt","srm02.ncg.ingrid.pt","srm2.grid.sinica.edu.tw","srm.ciemat.es","srm.gridc.lip.pt","storm-01.roma3.infn.it","stormfe1.pi.infn.it","storm.gridc.lip.pt","storm.ifca.es","storm-se-01.ba.infn.it","svr018.gla.scotgrid.ac.uk","tbn18.nikhef.nl","torik1.ulakbim.gov.tr","vm005.one.ypepth.grnet.gr");

// $list_se_by_page: list of SE in paging mode, i.e. each entry is a list of a given $PAGE_MAX_SIZE of SEs
$nbSe = count($list_se);
$list_se_by_page = array();
$index = 0;

while ($index < $nbSe) {
	$nbRemain = $PAGE_MAX_SIZE;
	if ($nbSe - $index < $PAGE_MAX_SIZE)
		$nbRemain = $nbSe - $index;
	$list_se_by_page[] = array_slice($list_se, $index, $nbRemain);
	$index += $PAGE_MAX_SIZE;
}

$nbPage = count($list_se_by_page);

//---------------------------------------------------------------------------------
// Function logAccess()
// 			Description: writes a string with date, time, script name,
//						 and identity of client into the log file
//			Paramètres: n/a
//---------------------------------------------------------------------------------
function logAccess()
{
	$fileName = "";
	// Rebuild the root path of the current script
	$result = explode("/", $_SERVER["SCRIPT_NAME"]);

	if (isHacker())
		$str = "### HACK ### ";
	else 
		$str = "";

	$path = "/";
	for ($i=0; $i<count($result) - 1; $i++)
		if ($result[$i] != "")
			$path .= $result[$i]."/";
			
	$fileName = $_SERVER['DOCUMENT_ROOT'].$path.LOG_FILE_ACCESS;

	$fh = fopen($fileName, "a+");
	if ($fh == true) {
		$str .= makeAccessLogString();
		fputs($fh, $str);
		fclose($fh); 
	} else {
		print_r(error_get_last());
	}
}

//---------------------------------------------------------------------------------
// Function makeAccessLogString()
// 			Description: returns a string with date, time, called script,
//						 and client user agent
//			Parameters: n/a
//---------------------------------------------------------------------------------
function makeAccessLogString()
{
	global $lang;
	
	$str = date("d-m-y H:i:s");
	$str .= " - ".$_SERVER['REQUEST_METHOD']." ".$_SERVER['REQUEST_URI']." ".$_SERVER['REMOTE_ADDR']." [".gethostbyaddr($_SERVER['REMOTE_ADDR'])."] ";
	if (array_key_exists('HTTP_REFERER', $_SERVER) && $_SERVER['HTTP_REFERER'] != "")
		$str .= "Ref=".$_SERVER['HTTP_REFERER'];
	//$str .= " Client=".$_SERVER['HTTP_USER_AGENT'];
	//$str .= " HttpAcceptLang=".$_SERVER['HTTP_ACCEPT_LANGUAGE'];
	return $str."\n";
}

//---------------------------------------------------------------------------------
// Function getCookie()
// 			Description: returns the cookie value if it exists, or an empty string other wise
//			Parameters:
//				$cookieName
//---------------------------------------------------------------------------------
function getCookie($cookieName) {
if (array_key_exists($cookieName, $_COOKIE))
	return $_COOKIE[$cookieName];
else
	return "";
}

function isHacker() {
	if (($_SERVER['REMOTE_ADDR'] == '222.66.119.2') || 
		(substr_count(gethostbyaddr($_SERVER['REMOTE_ADDR']), "163data.com.cn") > 0) ||
		($_SERVER['REMOTE_ADDR'] == '74.125.78.83') || 
		(substr_count($_SERVER['QUERY_STRING'], "%20and%20")) ||
		(substr_count($_SERVER['QUERY_STRING'], "%2Band%2B")) ||
		(substr_count($_SERVER['QUERY_STRING'], "%20%61%6E"))
	)
	
		return true;
	else
		return false;
}


?>