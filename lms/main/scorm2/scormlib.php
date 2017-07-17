<?php
function scorm_get_resources($blocks) {
	$resources = array ();
	foreach ( $blocks as $block ) {
		if ($block ['name'] == 'RESOURCES') {
			foreach ( $block ['children'] as $resource ) {
				if ($resource ['name'] == 'RESOURCE') {
					$resources [addslashes_js ( $resource ['attrs'] ['IDENTIFIER'] )] = $resource ['attrs'];
				}
			}
		}
	}
	return $resources;
}

function scorm_get_manifest($blocks, $scoes) {
	static $parents = array ();
	static $resources;
	
	static $manifest;
	static $organization;
	
	if (count ( $blocks ) > 0) {
		foreach ( $blocks as $block ) {
			switch ($block ['name']) {
				case 'METADATA' :
					if (isset ( $block ['children'] )) {
						foreach ( $block ['children'] as $metadata ) {
							if ($metadata ['name'] == 'SCHEMAVERSION') {
								if (empty ( $scoes->version )) {
									if (isset ( $metadata ['tagData'] ) && (preg_match ( "/^(1\.2)$|^(CAM )?(1\.3)$/", $metadata ['tagData'], $matches ))) {
										$scoes->version = 'SCORM_' . $matches [count ( $matches ) - 1];
									} else {
										if (isset ( $metadata ['tagData'] ) && (preg_match ( "/^2004 (3rd|4th) Edition$/", $metadata ['tagData'], $matches ))) {
											$scoes->version = 'SCORM_1.3';
										} else {
											$scoes->version = 'SCORM_1.2';
										}
									}
								}
							}
						}
					}
					break;
				case 'MANIFEST' :
					$manifest = addslashes_js ( $block ['attrs'] ['IDENTIFIER'] );
					$organization = '';
					$resources = array ();
					$resources = scorm_get_resources ( $block ['children'] );
					$scoes = scorm_get_manifest ( $block ['children'], $scoes );
					if (count ( $scoes->elements ) <= 0) {
						foreach ( $resources as $item => $resource ) {
							if (! empty ( $resource ['HREF'] )) {
								$sco = new stdClass ();
								$sco->identifier = $item;
								$sco->title = $item;
								$sco->parent = '/';
								$sco->launch = addslashes_js ( $resource ['HREF'] );
								$sco->scormtype = addslashes_js ( $resource ['ADLCP:SCORMTYPE'] );
								$scoes->elements [$manifest] [$organization] [$item] = $sco;
							}
						}
					}
					break;
				case 'ORGANIZATIONS' :
					if (! isset ( $scoes->defaultorg ) && isset ( $block ['attrs'] ['DEFAULT'] )) {
						$scoes->defaultorg = addslashes_js ( $block ['attrs'] ['DEFAULT'] );
					}
					$scoes = scorm_get_manifest ( $block ['children'], $scoes );
					break;
				case 'ORGANIZATION' :
					$identifier = addslashes_js ( $block ['attrs'] ['IDENTIFIER'] );
					$organization = '';
					$scoes->elements [$manifest] [$organization] [$identifier]->identifier = $identifier;
					$scoes->elements [$manifest] [$organization] [$identifier]->parent = '/';
					$scoes->elements [$manifest] [$organization] [$identifier]->launch = '';
					$scoes->elements [$manifest] [$organization] [$identifier]->scormtype = '';
					
					$parents = array ();
					$parent = new stdClass ();
					$parent->identifier = $identifier;
					$parent->organization = $organization;
					array_push ( $parents, $parent );
					$organization = $identifier;
					
					$scoes = scorm_get_manifest ( $block ['children'], $scoes );
					
					array_pop ( $parents );
					break;
				case 'ITEM' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					
					$identifier = addslashes_js ( $block ['attrs'] ['IDENTIFIER'] );
					$scoes->elements [$manifest] [$organization] [$identifier]->identifier = $identifier;
					$scoes->elements [$manifest] [$organization] [$identifier]->parent = $parent->identifier;
					if (! isset ( $block ['attrs'] ['ISVISIBLE'] )) {
						$block ['attrs'] ['ISVISIBLE'] = 'true';
					}
					$scoes->elements [$manifest] [$organization] [$identifier]->isvisible = addslashes_js ( $block ['attrs'] ['ISVISIBLE'] );
					if (! isset ( $block ['attrs'] ['PARAMETERS'] )) {
						$block ['attrs'] ['PARAMETERS'] = '';
					}
					$scoes->elements [$manifest] [$organization] [$identifier]->parameters = addslashes_js ( $block ['attrs'] ['PARAMETERS'] );
					if (! isset ( $block ['attrs'] ['IDENTIFIERREF'] )) {
						$scoes->elements [$manifest] [$organization] [$identifier]->launch = '';
						$scoes->elements [$manifest] [$organization] [$identifier]->scormtype = 'asset';
					} else {
						$idref = addslashes_js ( $block ['attrs'] ['IDENTIFIERREF'] );
						$base = '';
						if (isset ( $resources [$idref] ['XML:BASE'] )) {
							$base = $resources [$idref] ['XML:BASE'];
						}
						$scoes->elements [$manifest] [$organization] [$identifier]->launch = addslashes_js ( $base . $resources [$idref] ['HREF'] );
						if (empty ( $resources [$idref] ['ADLCP:SCORMTYPE'] )) {
							$resources [$idref] ['ADLCP:SCORMTYPE'] = 'asset';
						}
						$scoes->elements [$manifest] [$organization] [$identifier]->scormtype = addslashes_js ( $resources [$idref] ['ADLCP:SCORMTYPE'] );
					}
					
					$parent = new stdClass ();
					$parent->identifier = $identifier;
					$parent->organization = $organization;
					array_push ( $parents, $parent );
					
					$scoes = scorm_get_manifest ( $block ['children'], $scoes );
					
					array_pop ( $parents );
					break;
				case 'TITLE' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->title = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLCP:PREREQUISITES' :
					if ($block ['attrs'] ['TYPE'] == 'aicc_script') {
						$parent = array_pop ( $parents );
						array_push ( $parents, $parent );
						if (! isset ( $block ['tagData'] )) {
							$block ['tagData'] = '';
						}
						$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->prerequisites = addslashes_js ( $block ['tagData'] );
					}
					break;
				case 'ADLCP:MAXTIMEALLOWED' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->maxtimeallowed = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLCP:TIMELIMITACTION' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->timelimitaction = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLCP:DATAFROMLMS' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->datafromlms = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLCP:MASTERYSCORE' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->masteryscore = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLCP:COMPLETIONTHRESHOLD' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! isset ( $block ['tagData'] )) {
						$block ['tagData'] = '';
					}
					$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->threshold = addslashes_js ( $block ['tagData'] );
					break;
				case 'ADLNAV:PRESENTATION' :
					$parent = array_pop ( $parents );
					array_push ( $parents, $parent );
					if (! empty ( $block ['children'] )) {
						foreach ( $block ['children'] as $adlnav ) {
							if ($adlnav ['name'] == 'ADLNAV:NAVIGATIONINTERFACE') {
								foreach ( $adlnav ['children'] as $adlnavInterface ) {
									if ($adlnavInterface ['name'] == 'ADLNAV:HIDELMSUI') {
										if ($adlnavInterface ['tagData'] == 'continue') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hidecontinue = 1;
										}
										if ($adlnavInterface ['tagData'] == 'previous') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hideprevious = 1;
										}
										if ($adlnavInterface ['tagData'] == 'exit') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hideexit = 1;
										}
										if ($adlnavInterface ['tagData'] == 'exitAll') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hideexitall = 1;
										}
										if ($adlnavInterface ['tagData'] == 'abandon') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hideabandon = 1;
										}
										if ($adlnavInterface ['tagData'] == 'abandonAll') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hideabandonall = 1;
										}
										if ($adlnavInterface ['tagData'] == 'suspendAll') {
											$scoes->elements [$manifest] [$parent->organization] [$parent->identifier]->hidesuspendall = 1;
										}
									}
								}
							}
						}
					}
					break;
				case 'IMSSS:SEQUENCING' :
					
					break;
			}
		}
	}
	return $scoes;
}

function scorm_parse_scorm($pkgdir, $scormid) {
	global $CFG;
	global $tbl_crs_scorm, $tbl_crs_scorm_scoes, $tbl_crs_scorm_scoes_data, $tbl_crs_scorm_scoes_track;
	
	$launch = 0;
	$manifestfile = $pkgdir . '/imsmanifest.xml';
	
	if (is_file ( $manifestfile )) {
		
		$xmltext = file_get_contents ( $manifestfile );
		
		$pattern = '/&(?!\w{2,6};)/';
		$replacement = '&amp;';
		$xmltext = preg_replace ( $pattern, $replacement, $xmltext );
		
		$objXML = new xml2Array ();
		$manifests = $objXML->parse ( $xmltext );
		//print_object($manifests);
		$scoes = new stdClass ();
		$scoes->version = '';
		$scoes = scorm_get_manifest ( $manifests, $scoes );
		//print_object($scoes);
		if (count ( $scoes->elements ) > 0) {
			$olditems = get_records ( $tbl_crs_scorm_scoes, 'scorm', $scormid );
			foreach ( $scoes->elements as $manifest => $organizations ) {
				foreach ( $organizations as $organization => $items ) {
					foreach ( $items as $identifier => $item ) {
						// This new db mngt will support all SCORM future extensions
						$newitem = new stdClass ();
						$newitem->scorm = $scormid;
						$newitem->manifest = $manifest;
						$newitem->organization = $organization;
						$standarddatas = array ('parent', 'identifier', 'launch', 'scormtype', 'title' );
						foreach ( $standarddatas as $standarddata ) {
							if (isset ( $item->$standarddata )) {
								$newitem->$standarddata = addslashes_js ( $item->$standarddata );
							}
						}
						
						// Insert the new SCO, and retain the link between the old and new for later adjustment
						$id = insert_record ( $tbl_crs_scorm_scoes, $newitem );
						if (! empty ( $olditems ) && ($olditemid = scorm_array_search ( 'identifier', $newitem->identifier, $olditems ))) {
							$olditems [$olditemid]->newid = $id;
						}
						
						if ($optionaldatas = scorm_optionals_data ( $item, $standarddatas )) {
							$data = new stdClass ();
							$data->scoid = $id;
							foreach ( $optionaldatas as $optionaldata ) {
								if (isset ( $item->$optionaldata )) {
									$data->name = $optionaldata;
									$data->value = addslashes_js ( $item->$optionaldata );
									$dataid = insert_record ( 'scorm_scoes_data', $data );
								}
							}
						}
						
						if (($launch == 0) && ((empty ( $scoes->defaultorg )) || ($scoes->defaultorg == $identifier))) {
							$launch = $id;
						}
					}
				}
			}
			if (! empty ( $olditems )) {
				foreach ( $olditems as $olditem ) {
					delete_records ( $tbl_crs_scorm_scoes, 'id', $olditem->id );
					delete_records ( $tbl_crs_scorm_scoes_data, 'scoid', $olditem->id );
					if (isset ( $olditem->newid )) {
						set_field ( $tbl_crs_scorm_scoes_track, 'scoid', $olditem->newid, 'scoid', $olditem->id );
					}
					delete_records ( $tbl_crs_scorm_scoes_track, 'scoid', $olditem->id );
				
				}
			}
			if (empty ( $scoes->version )) {
				$scoes->version = 'SCORM_1.2';
			}
			set_field ( $tbl_crs_scorm, 'version', $scoes->version, 'id', $scormid );
			$scorm->version = $scoes->version;
		}
	}
	
	return $launch;
}

function scorm_optionals_data($item, $standarddata) {
	$result = array ();
	$sequencingdata = array ('sequencingrules', 'rolluprules', 'objectives' );
	foreach ( $item as $element => $value ) {
		if (! in_array ( $element, $standarddata )) {
			if (! in_array ( $element, $sequencingdata )) {
				$result [] = $element;
			}
		}
	}
	return $result;
}

function scorm_is_leaf($sco) {
	global $tbl_crs_scorm_scoes;
	if (get_record ( $tbl_crs_scorm_scoes, 'scorm', $sco->scorm, 'parent', $sco->identifier )) {
		return false;
	}
	return true;
}

function scorm_get_parent($sco) {
	global $tbl_crs_scorm_scoes;
	if ($sco->parent != '/') {
		$sql = "SELECT * FROM $tbl_crs_scorm_scoes WHERE scorm='" . $sco->scorm . "' AND identifier='" . $sco->parent . "'";
		$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
		$parent = Database::fetch_object ( $rs );
		if ($parent) {
			return scorm_get_sco ( $parent->id );
		}
	}
	return null;
}

function scorm_get_children($sco) {
	global $tbl_crs_scorm_scoes;
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes WHERE scorm='" . $sco->scorm . "' AND parent='" . $sco->identifier . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$children = Database::fetch_object ( $rs );
	if ($children) { //originally this said parent instead of childrean
		return $children;
	}
	return null;
}

function scorm_get_available_children($sco) {
	global $tbl_crs_scorm_scoes_track;
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes_track WHERE scoid='" . $scoid . "' AND userid='" . $userid . "' AND element='availablechildren'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$res = Database::fetch_object ( $rs );
	if (! $res || $res == null) {
		return false;
	} else {
		return unserialize ( $res->value );
	}
}

function scorm_get_available_descendent($descend = array(), $sco) {
	if ($sco == null) {
		return $descend;
	} else {
		$avchildren = scorm_get_available_children ( $sco );
		foreach ( $avchildren as $avchild ) {
			array_push ( $descend, $avchild );
		}
		foreach ( $avchildren as $avchild ) {
			scorm_get_available_descendent ( $descend, $avchild );
		}
	}
}

function scorm_get_siblings($sco) {
	global $tbl_crs_scorm_scoes;
	$sql = "SELECT * FROM $tbl_crs_scorm_scoes WHERE scorm='" . $sco->scorm . "' AND parent='" . $sco->parent . "'";
	$rs = api_sql_query ( $sql, __FILE__, __LINE__ );
	$siblings = Database::fetch_object ( $rs );
	if ($siblings) {
		unset ( $siblings [$sco->id] );
		if (! empty ( $siblings )) {
			return $siblings;
		}
	}
	return null;
}

function scorm_get_ancestors($sco) {
	if ($sco->parent != '/') {
		return array_push ( scorm_get_ancestors ( scorm_get_parent ( $sco ) ) );
	} else {
		return $sco;
	}
}

function scorm_get_preorder($preorder = array(), $sco) {
	
	if ($sco != null) {
		array_push ( $preorder, $sco );
		$children = scorm_get_children ( $sco );
		foreach ( $children as $child ) {
			scorm_get_preorder ( $sco );
		}
	} else {
		return $preorder;
	}
}

function scorm_find_common_ancestor($ancestors, $sco) {
	$pos = scorm_array_search ( 'identifier', $sco->parent, $ancestors );
	if ($sco->parent != '/') {
		if ($pos === false) {
			return scorm_find_common_ancestor ( $ancestors, scorm_get_parent ( $sco ) );
		}
	}
	return $pos;
}

/* Usage
 Grab some XML data, either from a file, URL, etc. however you want. Assume storage in $strYourXML;

 $objXML = new xml2Array();
 $arrOutput = $objXML->parse($strYourXML);
 print_r($arrOutput); //print it out, or do whatever!

*/
class xml2Array {
	
	var $arrOutput = array ();
	var $resParser;
	var $strXmlData;
	
	/**
	 * Convert a utf-8 string to html entities
	 *
	 * @param string $str The UTF-8 string
	 * @return string
	 */
	function utf8_to_entities($str) {
		global $CFG;
		
		$entities = '';
		$values = array ();
		$lookingfor = 1;
		
		return $str;
	}
	
	/**
	 * Parse an XML text string and create an array tree that rapresent the XML structure
	 *
	 * @param string $strInputXML The XML string
	 * @return array
	 */
	function parse($strInputXML) {
		$this->resParser = xml_parser_create ( 'UTF-8' );
		xml_set_object ( $this->resParser, $this );
		xml_set_element_handler ( $this->resParser, "tagOpen", "tagClosed" );
		
		xml_set_character_data_handler ( $this->resParser, "tagData" );
		
		$this->strXmlData = xml_parse ( $this->resParser, $strInputXML );
		if (! $this->strXmlData) {
			die ( sprintf ( "XML error: %s at line %d", xml_error_string ( xml_get_error_code ( $this->resParser ) ), xml_get_current_line_number ( $this->resParser ) ) );
		}
		
		xml_parser_free ( $this->resParser );
		
		return $this->arrOutput;
	}
	
	function tagOpen($parser, $name, $attrs) {
		$tag = array ("name" => $name, "attrs" => $attrs );
		array_push ( $this->arrOutput, $tag );
	}
	
	function tagData($parser, $tagData) {
		if (trim ( $tagData )) {
			if (isset ( $this->arrOutput [count ( $this->arrOutput ) - 1] ['tagData'] )) {
				$this->arrOutput [count ( $this->arrOutput ) - 1] ['tagData'] .= $this->utf8_to_entities ( $tagData );
			} else {
				$this->arrOutput [count ( $this->arrOutput ) - 1] ['tagData'] = $this->utf8_to_entities ( $tagData );
			}
		}
	}
	
	function tagClosed($parser, $name) {
		$this->arrOutput [count ( $this->arrOutput ) - 2] ['children'] [] = $this->arrOutput [count ( $this->arrOutput ) - 1];
		array_pop ( $this->arrOutput );
	}

}

?>