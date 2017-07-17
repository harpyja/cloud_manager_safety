<?php
$language_file = 'survey';
require_once ('../inc/global.inc.php');

api_protect_admin_script ();

require_once ('survey.inc.php');

$survey_id = isset ( $_REQUEST ['survey_id'] ) ? intval(getgpc("survey_id","G")) : "";
$tool_name = get_lang ( 'SurveyList' );

$redirect = 'main/survey/index.php';
$g_action=getgpc("action","G");   
if (isset ( $g_action ) && is_not_blank ( $survey_id )) {
	switch (getgpc("action","G")) {
		case 'delete' :
			$survey_data = SurveyManager::get_survey ( $survey_id );
			$return = SurveyManager::delete_survey ( intval($_GET ['survey_id'] ));
			if ($return) {
//				Display::display_msgbox ( get_lang ( 'SurveyDeleted' ), $redirect );
                                tb_close('index.php');
			} else {
//				Display::display_msgbox ( get_lang ( 'ErrorOccurred' ), $redirect, 'error' );
                                 tb_close('index.php');
			}
			break;
		case 'enabled' :
			$sqlwhere = "id=" . Database::escape ( $survey_id );
			$sql_data = array ("status" => STATE_PUBLISHED );
			$sql = Database::sql_update ( $tbl_survey, $sql_data, $sqlwhere );
			if (api_sql_query ( $sql, __FILE__, __LINE__ )) api_redirect ( "index.php" );
			break;
		case 'disabled' :
			$sqlwhere = "id=" . Database::escape ( $survey_id );
			$sql_data = array ("status" => STATE_EDIT );
			$sql = Database::sql_update ( $tbl_survey, $sql_data, $sqlwhere );
			$result = api_sql_query ( $sql, __FILE__, __LINE__ );
			if (api_sql_query ( $sql, __FILE__, __LINE__ )) api_redirect ( "index.php" );
			break;
	}
}
 
if (isset ( $_POST['action'] )) {
	switch (getgpc ( 'action', 'P' )) {
		case 'delete' :
                   $ids= $_POST['id'];
			if (is_array ($ids )) {
				foreach ( $ids as $key => $value ) {
					$survey_data = SurveyManager::get_survey ( $value );
					SurveyManager::delete_survey ( $value );
				}
				//Display::display_confirmation_message ( get_lang ( '操作成功！' ), false );
			} else {
				Display::display_error_message ( get_lang ( 'NoSurveysSelected' ), false );
			}
			break;
	}
}

$htmlHeadXtra [] = Display::display_thickbox ();
Display::display_header ( $tool_name );

$form = new FormValidator ( 'search_simple', 'get', '', '', '_self', false );
$renderer = $form->defaultRenderer ();
$renderer->setElementTemplate ( '<span>{label}&nbsp;{element}</span> ' );
$form->addElement ( 'text', 'keyword', $keyword_tip, array ('style' => "width:50%", 'class' => 'inputText','id'=>'searchkey','value'=>'输入搜索关键词' ) );
$form->addElement ( 'submit', 'submit', get_lang ( 'Query' ), 'class="inputSubmit"' );




function display_survey_list() {
	$parameters = array ();
	$parameters ['keyword'] = getgpc ( 'keyword', 'G' );
	
	$table = new SortableTable ( 'surveys', 'get_number_of_surveys', 'get_survey_data', 1 );
	$table->set_additional_parameters ( $parameters );
	$index = 0;
	$table->set_header ( $index ++, '', false );
	$table->set_header ( $index ++, get_lang ( 'SurveyName' ), false, null, array ('width' => '20%' ) );
	$table->set_header ( $index ++, get_lang ( 'SurveyCode' ), false, null, array ('width' => '17%' ) );
	$table->set_header ( $index ++, get_lang ( 'ValidDuration' ), false, null, array ('width' => '15%' ) );
	$table->set_header ( $index ++, get_lang ( 'NumberOfQuestions' ), false, null, array ('width' => '10%' ) );
	//	$table->set_header ( $index ++, get_lang ( 'Creator' ) );
	$table->set_header ( $index ++, get_lang ( 'SurveyUsers' ), false, null, array ('width' => '10%' ) );
	$table->set_header ( $index ++, get_lang ( 'State' ), false, null, array ('width' => '10%' ) );
	$table->set_header ( $index ++, get_lang ( 'Actions' ), false, null, array ('style' => 'width:15%' ) );
	$table->set_form_actions ( array ('delete' => get_lang ( 'DeleteSurvey' ) ) );
	$table->display ();
}

function get_sqlwhere() {
	global $objDept;
	$sql_where = "";
        $g_keyword=  getgpc('keyword');
	if (isset ( $g_keyword ) && ! empty ( $g_keyword )) {
        if($g_keyword=='输入搜索关键词'){
            $g_keyword='';
        }
		$keyword = escape ( $g_keyword, TRUE );
		$sql_where .= " AND (title LIKE '%" . $keyword . "%'  OR code LIKE '%" . $keyword . "%') ";
	}
	$sql_where = trim ( $sql_where );
	if ($sql_where)
		return substr ( ltrim ( $sql_where ), 3 );
	else return "";
}

function get_number_of_surveys() {
	global $tbl_survey;
	$sql = "SELECT count(id) AS total_number_of_items FROM " . $tbl_survey;
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	
	$res = api_sql_query ( $sql, __FILE__, __LINE__ );
	$obj = Database::fetch_object ( $res );
	return $obj->total_number_of_items;
}

function survey_search_restriction() {
    $g_do_search=  getgpc('do_search');
    $g_keyword_title=  getgpc('keyword_title');
    $g_keyword_code=  getgpc('keyword_code');
	if (isset ( $g_do_search )) {
		if ($g_keyword_title != '') {
			$search_term [] = 'title like "%" \'' . Database::escape_string ($g_keyword_title ) . '\' "%"';
		}
		if ($g_keyword_code != '') {
			$search_term [] = 'code =\'' . Database::escape_string ($g_keyword_code ) . '\'';
		}
		
		$my_search_term = ($search_term == null) ? array () : $search_term;
		$search_restriction = implode ( ' AND ', $my_search_term );
		return $search_restriction;
	} else {
		return false;
	}
}

function get_survey_data($from, $number_of_items, $column, $direction) {
	global $tbl_survey, $tbl_survey_question;
	$table_user = Database::get_main_table ( TABLE_MAIN_USER );
	$search_restriction = survey_search_restriction ();
	if ($search_restriction) {
		$search_restriction = ' AND ' . $search_restriction;
	}
	
	//CONCAT('<a href=\"survey_invitation.php?view=answered&amp;survey_id=',survey.id,'\">',survey.answered,'</a> / <a href=\"survey_invitation.php?view=invited&amp;survey_id=',survey.id,'\">',survey.invited, '</a>')	AS col5,
	$sql = "SELECT survey.id AS col0,
	                survey.title		AS col1,
					survey.code		AS col2,
					CONCAT(survey.avail_from,' ~ ',survey.avail_till) AS col3,
					count(survey_question.id)			AS col4,
	                CONCAT(survey.answered,' / ',survey.invited)	AS col5,
	                survey.status	AS col6,
	                survey.id	AS col7,
	                created_user AS col8
	             FROM $tbl_survey survey
				 LEFT JOIN $tbl_survey_question survey_question ON survey.id = survey_question.survey_id";
	$sql_where = get_sqlwhere ();
	if ($sql_where) $sql .= " WHERE " . $sql_where;
	$sql .= " GROUP BY survey.id";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $sql;
	$res = Database::query ( $sql, __FILE__, __LINE__, 0, 'NUM' );
	$surveys = array ();
	while ( $survey = Database::fetch_row ( $res ) ) {
		if ($survey [6] == STATE_EDIT) {
			$survey [6] = '<a href="index.php?action=enabled&survey_id=' . $survey [0] . '" title="' . get_lang ( 'StatusEdit' ) . '">' . Display::return_icon ( 'wrong.gif', get_lang ( 'StatusEdit' ) ) . '</a>';
		} elseif ($survey [6] == STATE_PUBLISHED) {
			//$survey [6] = '<a href="index.php?action=disabled&id=' . $survey [0] . '">' . Display::return_icon ( 'right.gif', get_lang ( 'StatusPublished' ) ) . "&nbsp;" . get_lang ( 'StatusPublished' ) . '</a>';
			$survey [6] = Display::return_icon ( 'right.gif', get_lang ( 'StatusPublished' ) ) . "&nbsp;" . get_lang ( 'StatusPublished' );
		} elseif ($survey [6] == STATE_DISABLED) {
			$survey [6] = get_lang ( 'StatusDisabled' );
		}
		$survey [7] = action_filter ( $survey [7], can_do_my_bo ( $survey [8] ) );
		unset($survey [8]);
		$surveys [] = $survey;
	}
	return $surveys;
}

function action_filter($survey_id, $can_do_my_bo) {
	$survey_id = Security::remove_XSS ( $survey_id );
	$return = '';
	if ($can_do_my_bo) {
		$return .= link_button ( 'settings.gif', 'Setting', 'survey_users.php?survey_id=' . $survey_id, '90%', '90%', FALSE );
		$href = 'index.php?action=delete&survey_id=' . $survey_id."&".api_get_cidreq ();
		$return .= '&nbsp;' . confirm_href ( 'delete.gif', 'DeleteSurvey', 'Delete', $href );
	}
	$return .= '&nbsp;' . '<a href="preview.php?survey_id=' . $survey_id . '" target="_blank">' . Display::return_icon ( 'preview.gif', get_lang ( 'Preview' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
	//$return.='&nbsp;'.link_button('preview.gif','Preview','preview.php?survey_id=' . $survey_id,null,null,null,FALSE);
	//	$return.='&nbsp;'.link_button('survey_publish.gif','Publish','survey_invite.php?survey_id=' . $survey_id,null,null,null,FALSE);
	$return .= '&nbsp;' . link_button ( 'edit_group.gif', 'Query', 'survey_query.php?survey_id=' . $survey_id, '90%', '90%', FALSE );
	//	$return.='&nbsp;'.link_button('statistics.gif','Reporting','reporting.php?survey_id=' . $survey_id,null,null,null,FALSE);
	$return .= '&nbsp;' . '<a href="reporting.php?survey_id=' . $survey_id . '" target="_blank">' . Display::return_icon ( 'statistics.gif', get_lang ( 'Reporting' ), array ('style' => 'vertical-align: middle;' ) ) . '</a>&nbsp;';
	return $return;
}

//Display::display_footer ( TRUE );?>

<!--<aside id="sidebar" class="column exercice open">-->
<aside id="sidebar" class="column course open">

    <div id="flexButton" class="closeButton close">

    </div>
</aside>

<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a> &gt; 调查问卷</h4>
    
    <div class="managerSearch">
    <span class="searchtxt right">
		<?php 
            echo link_button ( 'surveyadd.gif', 'CreateNewSurvey', 'survey_add.php', '80%', '70%', TRUE );
        ?>
    </span> 
      <?php $form->display ();?>    
    </div>
    <article class="module width_full hidden">
        <form name="form1" method="post" action="">
            <table cellspacing="0" cellpadding="0" class="p-table">
               <?php display_survey_list ();

                ?>

            </table>
        </form>

    </article>
</section>
</body>
</html>
