<div class="right" style="height: 180px">
<div class="user_up"></div>
<div class="user">
<div style="float: left; border: 1px solid #dcdddd; margin: 13px 4px;">
<div
	style="height: 88px; width: 80px; display: table-cell; text-align: center; vertical-align: middle;"><img
	<?php
	echo get_user_picture ( $user_id );
	?> /></div>
</div>
<ul class="info_list">
	<li class="dd2">登录名：<?=$userNo?></li>
	<li class="dd2">中文名：<?=$userName?></li>
	<li class="dd2">学习状态：
            <?php
												if ($learn_status == LEARNING_STATE_NOTATTEMPT)
													echo "未开始";
												elseif ($learn_status == LEARNING_STATE_COMPLETED)
													echo "已学完";
												elseif ($learn_status == LEARNING_STATE_IMCOMPLETED)
													echo "学习中";
												elseif ($learn_status == LEARNING_STATE_PASSED)
													echo "已通过";
												?>
            </li>
	<li class="dd2"><span style="float: left;"> 进度：</span>
	<div
		style="width: 80px; height: 13px; border: 1px solid rgb(0, 0, 0); float: left; margin: 5px 1px;">
	<div style="background: none repeat scroll 0% 0% #B31000; height: 13px; width: <?=$progress?>;"></div>
	</div>
	<div class="de1" style="float: left; font-size: 11px"><?=$progress?></div>
	</li>
</ul>
<div style="height: 0px; clear: both; overflow: hidden;"></div>

<div class="clearall" style="height: 20px;"></div>
</div>

<div class="user_down" style="margin-bottom: 16px"></div>
<div class="clearall"></div>
</div>
