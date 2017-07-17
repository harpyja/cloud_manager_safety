<?php
$cidReset = true;
$language_file = array ('courses', 'admin' );
include_once ("../../inc/global.inc.php");
//api_protect_admin_script ();

//include_once (api_get_path ( SYS_CODE_PATH ) . 'course/course.inc.php');
//require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');

$htmlHeadXtra [] =import_assets('commons.js');

$htmlHeadXtra [] = '<script type="text/javascript">
if(document.attachEvent)  window.attachEvent("onload",  iframeAutoFit);  
else  window.addEventListener("load",  iframeAutoFit,  false);
</script>';

$htmlHeadXtra [] = Display::display_thickbox ();
$htmlHeadXtra [] = '<script type="text/javascript">
	function refresh_tree(){ parent.CategoryTree.location.reload();/*parent.CategoryTree.d.openAll();*/ }
	</script>';
Display::display_header ();

/*
 ==============================================================================
 课程分类管理
 ==============================================================================
 */

    $language_file = 'admin';
    $cidReset = true;
    include_once ("../../inc/global.inc.php");
    $this_section = SECTION_PLATFORM_ADMIN;

//    api_protect_admin_script ();

    require_once (api_get_path ( LIBRARY_PATH ) . 'course.lib.php');
    require_once (api_get_path ( SYS_CODE_PATH ) . 'admin/course/course_category.inc.php');

    $org_id = (isset ( $_REQUEST ["org_id"] ) ? intval(getgpc ( 'org_id' )) : "-1");
    $category = (isset ( $_REQUEST ["category"] ) ? intval(getgpc ( 'category' )) : "0");
    $action = getgpc ( 'action' );

    $tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
    $tbl_category = Database::get_main_table ( TABLE_MAIN_CATEGORY );

    $sql = "SELECT parent_id FROM $tbl_category WHERE id=" . intval(Database::escape (getgpc("id","G") ));
    $parent_id = Database::get_scalar_value ( $sql );

    if (! empty ( $action )) {
        if ($action == 'delete') {
            $rtn=deleteNode (intval(getgpc('id')) );
            if($rtn==101){

            }
             tb_close("course_category_iframe.php");
            //api_redirect ( $_SERVER ['PHP_SELF'] . '?category=' . $parent_id . "&result=success" );
        }elseif ($action == 'moveUp') {
            moveNodeUp (intval(getgpc('id')), getgpc('tree_pos'), $category );
            api_redirect ( $_SERVER ['PHP_SELF'] . '?category=' . $parent_id . "&result=success" );
        }
    }

   // Display::display_header ( NULL ,FALSE);

    if (empty ( $action )) {
        $sql = "SELECT t1.id,t1.name,t1.code,t1.parent_id,t1.tree_pos,t1.children_count,COUNT(DISTINCT t3.code) AS nbr_courses
		 FROM $tbl_category t1 LEFT JOIN $tbl_category t2 ON t1.id=t2.parent_id
		 LEFT JOIN $tbl_course t3 ON t3.category_code=t1.id
	     GROUP BY t1.name,t1.parent_id,t1.tree_pos,t1.children_count
		 ORDER BY t1.tree_pos";
        	// WHERE t1.parent_id =" . Database::escape ( $category ) . " GROUP BY t1.name,t1.parent_id,t1.tree_pos,t1.children_count
        //echo $sql;
        $result = api_sql_query ( $sql, __FILE__, __LINE__ );
        $Categories = api_store_result ( $result );
    }

    if (! empty ( $category ) && empty ( $action )) {
        $result = api_sql_query ( "SELECT parent_id,name FROM $tbl_category WHERE id='$category'", __FILE__, __LINE__ );
        list ( $parent_id, $categoryName ) = mysql_fetch_row ( $result );
    }

    $objCrsMng = new CourseManager ();
    $category_tree = $objCrsMng->get_all_categories_tree ( TRUE );

    function _get_course_count($parent_id) {
        $GLOBALS ['objCrsMng']->sub_category_ids = array ();
        $sub_category_ids = $GLOBALS ['objCrsMng']->get_sub_category_tree_ids ( $parent_id, TRUE );
        $tbl_course = Database::get_main_table ( TABLE_MAIN_COURSE );
        $sql = "SELECT COUNT(*) FROM " . $tbl_course . " WHERE category_code " . Database::create_in ( $sub_category_ids );
        //echo $sql;
        return Database::get_scalar_value ( $sql );
    }



    $table_header [] = array (get_lang ( 'CategoryName' ) );
    $table_header [] = array (get_lang ( 'CategoryCode' ) );
//$table_header [] = array (get_lang ( 'SubCategoryCount' ), false, null, array ('style' => 'width:100px' ) );
    $table_header [] = array (get_lang ( 'CourseCount' ), false, null, array ('style' => 'width:80px' ) );
    $table_header [] = array (get_lang ( 'Actions' ), false, null, array ('style' => 'width:80px' ) );
//var_dump($Categories);
/**
    foreach ( $Categories as $enreg ) {
        $row = array ();
        $course_count = _get_course_count ( $enreg ['id'] );
        //if ($enreg ['children_count']) {
        //$row [] = "<a href='" . $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['id'] ) . "'>" . $enreg ['name'] . "</a>";
        $row [] = $enreg ['name'];
        $row [] = $enreg ['code'];
        //$row [] = $enreg ['children_count'];
        //}
        $row [] = ($course_count ? $course_count : "");

        if ($enreg ['children_count']) {
            $action_html = "&nbsp;".icon_href('folder_document.gif',   "OpenNode" ,$_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['id'] ));
        } else {
            $action_html = "";
        }

        $action_html .= "&nbsp;&nbsp;" . link_button ( 'edit.gif', 'EditNode', 'course_category_add_edit.php?action=edit&category=' . urlencode ( $enreg ['id'] ), '90%', '95%', FALSE );
        $action_html .= "&nbsp;&nbsp;" . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'DeleteNode', $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['id'] ) . "&action=delete&id=" . urlencode ( $enreg ['id'] ) );

        //$action_html .= "&nbsp;<a href='" . $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( $enreg ['parent_id'] ) . "&amp;action=moveUp&amp;id=" . urlencode ( $enreg ['id'] ) . "&amp;tree_pos=" . $enreg ['tree_pos'] . "'>" . Display::return_icon ( 'up.gif', get_lang ( "UpInSameLevel" ) ) . "</a>";
        $row [] = $action_html;
        $table_data [] = $row;
    }
**/ 
$Category_id = $Categories;
//var_dump($course_count);
 //   unset ( $Categories );
    //echo Display::display_category_table ( $table_header, $table_data );
    //Display::display_footer ();
?>
<aside id="sidebar" class="column course open">
    <div id="flexButton" class="closeButton close">

    </div>
</aside>
<section id="main" class="column">
    <h4 class="page-mark">当前位置：<a href="<?=URL_APPEDND;?>/main/admin/index.php">平台首页</a> &gt; 
        <a href="<?=URL_APPEDND;?>/main/admin/course/course_list.php">课程管理</a> &gt; 课程分类</h4>
    <article class="module width_full hidden ip">
        <table cellpadding="0" cellspacing="0" id="course-table">
            <tbody>
            <tr>
                <td class="course-dtd">
                    <div class="course-title"><strong>课程分类管理</strong></div>
                    <div class="course-add"><?php    echo link_button ( 'folder_new.gif', 'AddACategory', 'course_category_add_edit.php?action=add&category=' . $category,'90%','95%');?></div>
                </td>
            </tr>
            
            <tr><?php 
                if($Categories==null){
                    echo "<td class='course-info' style='text-align:center'> 没有相关的课程分类 </td>";
                }else{
                    echo '<td class="course-info"  style="color: #ff0000">'.'提示：鼠标点击父菜单，即可查看子菜单栏目。'.'</td>';
                }
            ?></tr>
            <?php
            //echo '<pre>';var_dump($Categories);echo '</pre>';
             foreach($Categories as $name){?>
                <?php if($name['parent_id']==0) {
                    $pid = intval($name['id']);
                    ?>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" class="course-list">

                        <tr class="bline">

                            <td class="course-win2">  <?php echo ($name['name']);?></td>
                            <td class="course-win3">课程总数:<?php $count = _get_course_count(intval($name['id'])); echo $count;?></td>
                            <?php 
                                        if($name ['children_count']){
                                          echo '<td class="course-win2 opens"><img src="../../../themes/img/folder_document.gif" title="进入分类"/></td>';
                                        }else{
                                          echo '<td class="course-win2 opens">&nbsp;</td>';
                                        }
                            ?>
                            <td class="course-win4">
                                <?php
                                 echo "&nbsp;/&nbsp;";
                                 echo link_button ( 'edit.gif', 'EditNode', 'course_category_add_edit.php?action=edit&category=' . urlencode ( intval($name['id']) ), '90%', '95%', FALSE );?>   
                                <?php
                                echo '&nbsp;/&nbsp;'.confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'DeleteNode', $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( intval($name['id']) ) . "&action=delete&id=" . urlencode ( intval($name['id']) ) );
                                
                                ?></td>
                        </tr>
                        <?php foreach($Categories as $id){?>
                            <?php if($id['parent_id'] ==$pid ){?>
                        <tr class="bline-m hide">
                            <td colspan="4">
                                <table cellpadding="0" cellspacing="0" class="course-list-list">
                                    <tr>
                                        <td class="course-win2"><?php echo $id['name'];?></td>
                                        <td class="course-win3">课程总数:<?php $count = _get_course_count(intval($id['id'])); echo $count;?></td>
                                        <td class="course-win4">
                                <?php  
                                        echo link_button ( 'edit.gif', 'EditNode', 'course_category_add_edit.php?action=edit&category=' . urlencode ( intval($id['id']) ), '90%', '95%', FALSE );?>
                                <?php 
                                 
                                    echo '&nbsp;/&nbsp;' . confirm_href ( 'delete.gif', 'ConfirmYourChoice', 'DeleteNode', $_SERVER ['PHP_SELF'] . "?category=" . urlencode ( intval($id['id']) ) . "&action=delete&id=" . urlencode ( intval($id['id']) ) );
                                ?>
                                            </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
            
                        <?php }?>
                        <?php }?>

                    </table>
                </td>
            </tr>

                <?php }?>
            <?php }?>
           
            </tbody>
        </table>
    </article> 
</section>
</body>
</html>
