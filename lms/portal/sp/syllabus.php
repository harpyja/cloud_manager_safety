<?php
$cidReset = true;
include_once ("inc/app.inc.php");
$user_id = api_get_user_id ();
include_once ("inc/page_header.php");
$objStat = new ScormTrackStat ();
api_session_unregister ( 'oLP' );
api_session_unregister ( 'lpobject' );
$sql = "SELECT type,count(*) FROM `syllabus` WHERE training_class_id=0  GROUP BY code";
$category_cnt = Database::get_into_array2 ( $sql );


$get_category_id=  getgpc('category_id');
$get_class=  getgpc('class');
$get_professional=  getgpc('professional');
$get_week=  getgpc('week');
$get_keyword=  getgpc('keyword');

if (isset ( $get_category_id ) && is_not_blank ( $get_category_id )) {
    $sql_where .= " AND category_code=" . Database::escape ( getgpc ( 'category_id', 'G' ) );
    $param .= "category_id=" . getgpc ( 'category_id', 'G' );
}

if (isset ( $get_class ) && is_not_blank ( $get_class )) {
    $sql_where .= " AND class=" . Database::escape ( getgpc ( 'class', 'G' ) );
    $param .= "class=" . getgpc ( 'class', 'G' );
}

if (isset ( $get_professional ) && is_not_blank ( $get_professional )) {
    $sql_where .= " AND professional=" . Database::escape ( getgpc ( 'professional', 'G' ) );
    $param .= "professional=" . getgpc ( 'professional', 'G' );
}

if (isset ( $get_week ) && is_not_blank ( $get_week )) {
    if($get_week=='---起止周---'){
        $get_week = '';
    }
    $sql_where .= " AND week=" . Database::escape ( getgpc ( 'week', 'G' ) );
    $param .= "week=" . getgpc ( 'week', 'G' );
}

if (isset ( $get_keyword ) && is_not_blank ( $get_keyword )) {
    $keyword = Database::escape_str ( urldecode ( $get_keyword ), TRUE );
    $sql_where .= " AND (code LIKE '%" . $keyword . "%')";
    $param .= "&keyword=" . urlencode ( $keyword );
}

if ($param {0} == "&") $param = substr ( $param, 1 );
    $page_size='10';

    $sql1 = "SELECT COUNT(*) FROM `syllabus` WHERE `type`=`type`";
    if ($sql_where) $sql1 .=$sql_where;
    $total_rows = Database::get_scalar_value ( $sql1 );

    $sql1 = "SELECT * FROM  `syllabus` WHERE `type`=`type`";
    if ($sql_where) $sql1 .=$sql_where;
    $sql1 .= " ORDER BY category_code,code";
    $offset = getgpc('offset');
    if (isset($page_size)) $sql1 .= " LIMIT " . (empty ( $offset ) ? 0 : $offset) . "," . $page_size;

    $c = api_sql_query_array_assoc ( $sql1, __FILE__, __LINE__ );
    $rtn_data=array ("data_list" => $c, "total_rows" => $total_rows );

//$rtn_data = get_my_course_list ( $user_id, $sql_where, NUMBER_PAGE, getgpc ( "offset", "G" ) );
//var_dump($rtn_data);

$personal_course_list = $rtn_data ["data_list"];
$total_rows = $rtn_data ["total_rows"];
$url = WEB_QH_PATH . "syllabus.php?" . $param;
$pagination_config = Pagination::get_defult_config ( $total_rows, $url, NUMBER_PAGE );
$pagination = new Pagination ( $pagination_config );

$interbreadcrumb [] = array ("url" => 'syllabus.php', "name" => "&nbsp;课程表" );

display_tab ( TAB_SYLLABUS );
?> 
<link
	href="<?=api_get_path ( WEB_JS_PATH )?>yui/tabview/assets/skins/sam/tabview.css"
	rel="stylesheet" type="text/css" />
<script type="text/javascript">
	$(document).ready( function() {
		$("body").addClass("yui-skin-sam");
	});
</script>



<section id="main" class="column" style="width:100%;">
    <h4 class="page-mark">当前位置：<a href="index.php">首页</a> &gt; <?php
        echo display_interbreadcrumb ( $interbreadcrumb, $nameTools );
        ?></h4>
    <article class="module width_full">
        <header>
            <h3>课程表</h3>
            <div class="submit_link">
                <form action="syllabus.php" method="get" >
                    <span class="link_title">按课程关键字：</span><input type="text" name="keyword"  value="<?=getgpc ( 'keyword' )?>" onfocus="this.select();" />
                    <span class="link_title">按分类：</span>
        <?php
        $objCrsMng = new CourseManager ();
        $category_tree = $objCrsMng->get_all_categories_tree ( TRUE );
        $cate_options [""] = "---所有分类---";
        foreach ( $category_tree as $item ) {
            $cate_name = $item ['name'] . (($category_cnt [$item ['id']]) ? "&nbsp;(" . $category_cnt [$item ['id']] . ")" : "");
            $cate_options [$item ['id']] = str_repeat ( "&nbsp;&nbsp;&nbsp;&nbsp;", intval ( $item ['level'] ) + 1 ) . $cate_name;
        }

        echo form_dropdown ( "category_id", $cate_options, getgpc ( 'category_id' ), 'style="height:22px;border: 1px solid #666666;"' );
        //echo form_hidden ( "learn_status", getgpc ( 'learn_status' ) );
        ?>

                    <span class="link_title">按年级：</span>
        <select  name = "class">
            <option value=''>---所有年级---</option>
            <?php
            $class = "select distinct class from `syllabus`";
            $r = api_sql_query ( $class, __FILE__, __LINE__ );
            $vm= array ();

            while ( $cl = Database::fetch_row ( $r) ) {

                $cls [] = $cl[0];
            }

            //array_unshift($cls,"---所有年级---");
            // $array_ab=array_combine($cls,$cls);

            foreach ($cls as $k => $v ){
                echo "<option value='$v'>$v</option>";
            }
            ?>
        </select>

                    <span class="link_title">按专业：</span>
        <select  name = "professional">
            <option value=''>---所有专业---</option>
            <?php
            $professional = "select distinct professional from `syllabus`";
            $res = api_sql_query ( $professional, __FILE__, __LINE__ );
            $p= array ();

            while ( $p = Database::fetch_row ( $res) ) {

                $ps [] = $p[0];
            }
            foreach ($ps as $k => $v ){
                echo "<option value='$v'>$v</option>";
            }
            ?>
        </select>

                    <span class="link_title">按周：</span>
        <select  name = "week">
            <option value=''>---起止周---</option>
            <?php
            $sql = "select distinct week from `syllabus`";
            $res = api_sql_query ( $sql, __FILE__, __LINE__ );
            $w= array ();

            while ( $w = Database::fetch_row ( $res) ) {

                $ws [] = $w[0];
            }
            foreach ($ws as $k => $v ){
                echo "<option value='$v'>$v</option>";
            }
            ?>
        </select>
                    <input type="submit" value=" 搜 索 " class="submit alt_btn"  />
                </form>
            </div>
        </header>
        <div class="module_content">
            <table cellspacing="0" cellpadding="0" class="p-table">
                <tr>
                    <th>课程类别</th>
                    <th>课程名称</th>
                    <th>课程类型</th>
                    <th>学分</th>
                    <th>学时</th>
                    <th>讲师</th>
                    <th>专业</th>
                    <th>年级</th>
                    <th>起止周</th>
                    <th>星期一</th>
                    <th>星期二</th>
                    <th>星期三</th>
                    <th>星期四</th>
                    <th>星期五</th>
                    <th>星期六</th>
                    <th>星期日</th>
                </tr>

                <?php
                if (is_array ( $personal_course_list ) && $personal_course_list) {
                    foreach ( $personal_course_list as $course ) {
                        $title = $state = '';
                        $course_system_code = $course ['code'];
                        $course_title = $course ['title'];
                        $course_visibility = $course ['visibility'];
                        $is_course_admin = CourseManager::is_course_admin ( $user_id, $course_system_code );
                        if ($course_visibility != COURSE_VISIBILITY_CLOSED or $is_course_admin) {
                            if (($course ['is_valid_date'] == 1) or api_is_platform_admin ()) {
                                $title .= '<a class="de1" href="' . WEB_QH_PATH . 'course_home.php?cidReq=' . $course_system_code . '&action=introduction" title="' . $course_title . '">' . api_trunc_str2 ( $course_title ) . '</a>';
                            } else {
                                $title .= '<a class="de1" href="javascript:void(0);" disabled="true">' . $course_title . '</a>';
                            }

                            if ($course ['is_valid_date'] == 0) {
                                $state .= '<span style="padding-right:4px;padding-left:20px;font-size:12px;font-style:italic;">(已过有效期)</span>';
                            }
                        }
                        //$icon = Display::return_icon ( "course.gif", get_lang ( "Student" ),array ('style' => 'vertical-align: middle;') );
                        ?>
                    <tr>
                        <td>
                            <?php
                            $s = "SELECT name FROM course_category WHERE id=".$course['category_code'];
                            $category = Database::getval ( $s, __FILE__, __LINE__ );
                            echo $category;
                            ?>
                        </td>
                        <td><?=$course['code']?></td>
                        <td>
                            <?php
                            if($course['type']==0){
                                echo '必修';
                            }
                            if($course['type']==1){
                                echo '选修';
                            }
                            ?>
                        </td>
                        <td><?=$course ["credit"]?></td>
                        <td><?=$course ["credit_hours"]?></td>
                        <td><?=$course ["teacher"]?></td>
                        <td><?=$course ["professional"]?></td>
                        <td><?=$course ["class"]?></td>
                        <td><?=$course ["week"]?></td>
                        <td>
                            <?php
                            $mon=unserialize($course ["mon"]);
                            //  var_dump($mon);
                            if($mon['monday']==1){
                                if($mon['class1']==1){
                                    echo '第一节;';
                                }
                                if($mon['class2']==1){
                                    echo '第二节;';
                                }
                                if($mon['class3']==1){
                                    echo '第三节;';
                                }
                                if($mon['class4']==1){
                                    echo '第四节;';
                                }
                                if($mon['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $tue=unserialize($course ["tue"]);
                            //  var_dump($tue);
                            if($tue['tuesday']==1){
                                if($tue['class1']==1){
                                    echo '第一节;';
                                }
                                if($tue['class2']==1){
                                    echo '第二节;';
                                }
                                if($tue['class3']==1){
                                    echo '第三节;';
                                }
                                if($tue['class4']==1){
                                    echo '第四节;';
                                }
                                if($tue['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $wed=unserialize($course ["wed"]);
                            //  var_dump($wed);
                            if($wed['wednesday']==1){
                                if($wed['class1']==1){
                                    echo '第一节;';
                                }
                                if($wed['class2']==1){
                                    echo '第二节;';
                                }
                                if($wed['class3']==1){
                                    echo '第三节;';
                                }
                                if($wed['class4']==1){
                                    echo '第四节;';
                                }
                                if($wed['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $thu=unserialize($course ["thu"]);
                            //  var_dump($thu);
                            if($thu['thursday']==1){
                                if($thu['class1']==1){
                                    echo '第一节;';
                                }
                                if($thu['class2']==1){
                                    echo '第二节;';
                                }
                                if($thu['class3']==1){
                                    echo '第三节;';
                                }
                                if($thu['class4']==1){
                                    echo '第四节;';
                                }
                                if($thu['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $fri=unserialize($course ["fri"]);
                            //  var_dump($fri);
                            if($fri['friday']==1){
                                if($fri['class1']==1){
                                    echo '第一节;';
                                }
                                if($fri['class2']==1){
                                    echo '第二节;';
                                }
                                if($fri['class3']==1){
                                    echo '第三节;';
                                }
                                if($fri['class4']==1){
                                    echo '第四节;';
                                }
                                if($fri['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $sat=unserialize($course ["sat"]);
                            //  var_dump($sat);
                            if($sat['saturday']==1){
                                if($sat['class1']==1){
                                    echo '第一节;';
                                }
                                if($sat['class2']==1){
                                    echo '第二节;';
                                }
                                if($sat['class3']==1){
                                    echo '第三节;';
                                }
                                if($sat['class4']==1){
                                    echo '第四节;';
                                }
                                if($sat['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                        <td>
                            <?php
                            $sun=unserialize($course ["sun"]);
                            //  var_dump($sun);
                            if($sun['sunday']==1){
                                if($sun['class1']==1){
                                    echo '第一节;';
                                }
                                if($sun['class2']==1){
                                    echo '第二节;';
                                }
                                if($sun['class3']==1){
                                    echo '第三节;';
                                }
                                if($sun['class4']==1){
                                    echo '第四节;';
                                }
                                if($sun['class5']==1){
                                    echo '晚自习';
                                }
                            }else{
                                //echo '<img src="../../themes/img/delete_na.gif"/>';
                                echo '';
                            }
                            ?></td>
                    </tr>

                        <?php
                    }
                }else{ ?>

                <tr>
                    <td colspan="100"  class="error b"> 没有相关课程表</td>
                </tr>

                    <?php }?>

            </table>
        </div>
    </article>
</section>
</body>
        </html>