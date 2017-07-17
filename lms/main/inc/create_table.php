<?php
//路由交换类型表 auth@changzf  2013/11/20
create_table("labs_type","CREATE TABLE IF NOT EXISTS `labs_type` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                                            `name` varchar(50) NOT NULL COMMENT '名称',
                                            `desc` varchar(128) NOT NULL COMMENT '描述',
                                            PRIMARY KEY (`id`)
                                          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='路由交换类型表' AUTO_INCREMENT=0;");

//路由运行表
create_table("task","CREATE TABLE IF NOT EXISTS `labs_run_devices` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `course_name` varchar(256) NOT NULL,
		  `labs_name` varchar(32) NOT NULL,
		  `p_id` int(11) NOT NULL,
		  `USERID` int(11) NOT NULL,
		  `GROUPID` int(11) DEFAULT NULL,
		  `LEADID` int(11) DEFAULT NULL,
		  `PORT` int(11) NOT NULL,
		  `DEVICEID` varchar(256) NOT NULL,
		  `DEVICEDNAME` varchar(256) NOT NULL,
		  `ROUTETYPE` varchar(256) NOT NULL,
		  `ROUTEMOD` varchar(256) NOT NULL,
		  `DEVICEDTYPE` varchar(256) NOT NULL,
		  `status` int(11) NOT NULL,
		  `uport` varchar(256) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COMMENT='路由运行表' AUTO_INCREMENT=0 ;");

//监控平台任务表
create_table("task","CREATE TABLE IF NOT EXISTS `task` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                      `name` varchar(128) NOT NULL COMMENT '任务名称',
                      `description` varchar(256) NOT NULL COMMENT '任务描述',
                      `group` int(11) NOT NULL COMMENT '用户组',
                      `status` int(11) NOT NULL DEFAULT '1' COMMENT '是否发布：1为发布，0为未发布',
                      `red_vm` varchar(256) NOT NULL COMMENT '靶机模板',
                      `blue_vm` varchar(256) NOT NULL COMMENT '渗透模板',
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='监控平台任务表' AUTO_INCREMENT=0 ;");

//监控平台用户分组表
create_table("task_group","CREATE TABLE IF NOT EXISTS `task_group` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                      `group` varchar(128) NOT NULL COMMENT '分组名称',
                      `task_id` varchar(128) NOT NULL COMMENT '任务编号',
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='监控平台任务分组表' AUTO_INCREMENT=0 ;");

//试卷表
create_table("exam_type","CREATE TABLE IF NOT EXISTS `exam_type` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                      `name` varchar(128) CHARACTER SET utf8 NOT NULL COMMENT '试卷名称',
                      `description` text CHARACTER SET utf8 NOT NULL COMMENT '描述',
                      `enable` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='试卷表' AUTO_INCREMENT=0 ;");

//大赛简介表
create_table("summary","CREATE TABLE IF NOT EXISTS `summary` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `title` varchar(128) NOT NULL,
                      `created_user` int(11) DEFAULT NULL,
                      `visible` tinyint(1) NOT NULL DEFAULT '1',
                      `content` text NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='简介表' AUTO_INCREMENT=0 ;");

//拓扑设备表  态势展示
create_table("topomap","CREATE TABLE IF NOT EXISTS `topomap` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `topo_id` int(128) NOT NULL,
                      `nodeId` int(128) NOT NULL,
                      `nodename` varchar(32) CHARACTER SET utf8 NOT NULL,
                      `nodeType` varchar(256) CHARACTER SET utf8 NOT NULL,
                      `offset` varchar(256) CHARACTER SET utf8 NOT NULL,
                      `nodeDesc` varchar(256) CHARACTER SET utf8 NOT NULL,
                      `desc_position` varchar(50) CHARACTER SET utf8 NOT NULL,
                      `ports` varchar(50) CHARACTER SET utf8 NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='拓扑设备表' AUTO_INCREMENT=0 ;");

//监控平台小组用户表
create_table("group_user","CREATE TABLE IF NOT EXISTS `group_user` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
                      `name` varchar(128) NOT NULL COMMENT '分组名称',
                      `userId` varchar(128) NOT NULL COMMENT '用户编号',
                      `is_leader` int(11) NOT NULL COMMENT '是否组长',
                      `type` int(11) NOT NULL DEFAULT '1' COMMENT '用户类型,1为红方，2为蓝方',
                      `description` text NOT NULL COMMENT '分组描述',
                      `tasks_id` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='监控平台小组用户表' AUTO_INCREMENT=0");

//导调工具表
create_table("tools","CREATE TABLE IF NOT EXISTS `tools` (
                      `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '编号',
                      `title` varchar(128) NOT NULL COMMENT '工具名称',
                      `created_user` int(11) DEFAULT NULL COMMENT '创建用户',
                      `date_start` datetime NOT NULL COMMENT '创建时间',
                      `visible` tinyint(1) NOT NULL COMMENT '是否开放',
                      `content` text NOT NULL COMMENT '工具描述',
                      `file` varchar(128) DEFAULT NULL COMMENT '工具文件',
                      `type` varchar(128) DEFAULT NULL COMMENT '类型',
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='导调工具表' AUTO_INCREMENT=0 ;");

//评估名称
create_table("project","CREATE TABLE IF NOT EXISTS `project` (
                       `id` int(11) NOT NULL AUTO_INCREMENT,
                       `name` varchar(32) NOT NULL,
                       `upfile` varchar(128) NOT NULL,
                       `release` int(11) NOT NULL DEFAULT 0,
                       `des` varchar(32) NOT NULL,
                       PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");

//检查方法
create_table("assess","CREATE TABLE IF NOT EXISTS `assess` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `pro_id` int(128) DEFAULT NULL,
                      `class` varchar(128) DEFAULT NULL,
                      `check` text,
                      `risk_level` int(11) NOT NULL,
                      `reinforcement_suggestions` text NOT NULL,
                      `num` tinyint(4) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");

//检查项
create_table("check_items","CREATE TABLE IF NOT EXISTS `check_items` (
                       `id` int(11) NOT NULL AUTO_INCREMENT,
                        `item_id` int(11) DEFAULT NULL,
                        `name` varchar(128) DEFAULT NULL,
                        `des` text NOT NULL,
                        `assess_id` int(11) NOT NULL,
                        PRIMARY KEY (`id`,`assess_id`),
                        KEY `fk_check_items_assess1` (`assess_id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");


//前台评估
create_table("assessment_result","CREATE TABLE IF NOT EXISTS `assessment_result` (
                       `id` int(11) NOT NULL AUTO_INCREMENT,
                        `pro_id` int(11) DEFAULT NULL,
                        `user_id` int(11) DEFAULT NULL,
                        `assess_id` int(11) DEFAULT NULL,
                        `check_id` int(11) DEFAULT NULL,
                        `result` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");

//夺旗、分组对抗报告表
create_table("reporting_info","CREATE TABLE IF NOT EXISTS `reporting_info` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `report_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '报告名称',
                          `user` varchar(128) NOT NULL COMMENT '用户',
                          `submit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '提交时间',
                          `screenshot_file` varchar(50) NOT NULL COMMENT '学生提交文件',
                          `status` int(11) NOT NULL COMMENT '学生提交状态',
                          `score` int(11) NOT NULL COMMENT '得分',
                          `comment` text NOT NULL COMMENT '评语',
                          `return` int(11) NOT NULL COMMENT '批改结果',
                          `marking_status` int(11) NOT NULL COMMENT '教师批改状态',
                          `description` text NOT NULL COMMENT '描述',
                          `type` int(11) NOT NULL DEFAULT '1' COMMENT '报告类型：1为夺旗报告，2为分组对抗报告',
                          `key` varchar(128) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='夺旗、分组对抗报告表' AUTO_INCREMENT=0;");

//实验报告表
create_table("report","CREATE TABLE IF NOT EXISTS `report` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `report_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '报告名称',
                        `user` varchar(128) NOT NULL COMMENT '用户',
                        `code` varchar(128) NOT NULL COMMENT '学习课程',
                        `submit_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '提交时间',
                        
                        `purpose` text  NOT NULL COMMENT '实验目的',
                        `equipment` text NOT NULL COMMENT '实验设备环境',
                        `content` text NOT NULL COMMENT '实验内容和步骤',
                        `result` text NOT NULL COMMENT '实验结果',
                        `analysis` text NOT NULL COMMENT '实验分析和讨论',
                        
                        `screenshot_file` varchar(50) NOT NULL COMMENT '学生提交内容',
                        `status` int(11) NOT NULL COMMENT '提交状态',
                        `score` int(11) NOT NULL COMMENT '得分',
                        `comment` text NOT NULL COMMENT '评语',
                        `return` int(11) NOT NULL COMMENT '批改结果',
                        `marking_status` int(11) NOT NULL COMMENT '教师批改状态',
                        `description` text NOT NULL COMMENT '描述',
                        `key` varchar(128) NOT NULL,
                        `type` int(11) NOT NULL DEFAULT '0' COMMENT '是否有课程，1为为有课程，0为没有课程',
                        PRIMARY KEY (`id`)
                      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='实验报告表' AUTO_INCREMENT=0;");
//夺旗表
create_table("flag","CREATE TABLE IF NOT EXISTS `flag` (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
                          `date_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发布时间',
                          `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
                          `title` varchar(250) NOT NULL DEFAULT '' COMMENT '标题',
                          `content` text NOT NULL COMMENT '旗子位置描述',
                          `created_user` int(11) DEFAULT NULL,
                          `user` varchar(256) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='夺旗表' AUTO_INCREMENT=0");

//分组任务表
create_table("renwu","CREATE TABLE IF NOT EXISTS `renwu` (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
                          `name` varchar(256) NOT NULL DEFAULT '' COMMENT '标题',
                          `description` text NOT NULL COMMENT '描述',
                          `red_group` varchar(256) DEFAULT NULL,
                          `blue_group` varchar(256) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='分组表' AUTO_INCREMENT=0");

//对抗部署表
create_table("deploy","CREATE TABLE IF NOT EXISTS `deploy` (
                          `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
                          `template_id` int(11) NOT NULL COMMENT '模板',
                          `user_id` int(11) NOT NULL COMMENT '用户',
                          `task_id` int(11) NOT NULL COMMENT '任务编号',
                          `ip` varchar(128) NOT NULL COMMENT 'IP',
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='对抗部署表' AUTO_INCREMENT=0");

//课程体系表
create_table("setup","CREATE TABLE IF NOT EXISTS `setup` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `title` varchar(64) CHARACTER SET utf8 NOT NULL,
                        `description` text CHARACTER SET utf8 NOT NULL,
                        `subclass` varchar(128) CHARACTER SET utf8 NOT NULL,
                        PRIMARY KEY (`id`)
                      ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='课程体系分类' AUTO_INCREMENT=0 ;");

//信息传递表
create_table("message","CREATE TABLE IF NOT EXISTS  `message` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `date_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `recipient` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='信息传递';");


//截屏录屏表
create_table("snapshot","CREATE TABLE IF NOT EXISTS `snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addres` varchar(128) NOT NULL ,
  `system` varchar(128) NOT NULL COMMENT '系统',
  `user_id`int(11) NOT NULL COMMENT '用户编号',
  `lesson_id` varchar(50) NOT NULL COMMENT '课程编号',
  `vmid` int(20) NOT NULL,
  `port` int(30) NOT NULL,
  `mac_id` varchar(128) NOT NULL,
  `proxy_port` int(11) NOT NULL  COMMENT '代理端口',
  `status` int(11) NOT NULL  COMMENT '状态：1为进行，0为关闭',
  `type` int(11) NOT NULL  COMMENT '类型：1为截屏，2为录屏',
  `filename` varchar(128) NOT NULL  COMMENT '文件',
  `time` varchar(20) NOT NULL  COMMENT '时间',
  `snapshotdesc` text NOT NULL  COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8  COMMENT '截屏录屏表' AUTO_INCREMENT=0 ;");


//课程license管理
create_table("course_license","CREATE TABLE IF NOT EXISTS `course_license` (
    `id` int(11) NOT NULL AUTO_INCREMENT, 
    `course_category` varchar(100) NOT NULL COMMENT '课程分类名称',
    `description` varchar(128) NOT NULL COMMENT '描述' ,
    `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '时间' ,
    `filename` varchar(128) NOT NULL COMMENT '包名',
    `license` varchar(128) NOT NULL COMMENT 'license',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8  COMMENT '课程license管理' AUTO_INCREMENT=0;");

//虚拟机vmstartinfo表
create_table("vmstartinfo","CREATE TABLE  IF NOT EXISTS `vmstartinfo` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `addres` varchar(256) CHARACTER SET utf8 NOT NULL,
    `nicnum` int(11) NOT NULL,
    `system` varchar(32) CHARACTER SET utf8 NOT NULL,
    `user_id` int(11) NOT NULL,
    `lesson_id` bigint(20) NOT NULL,
    `stat_id` int(11) NOT NULL,
    `group_id` int(11) NOT NULL,
    `mac_id` varchar(256) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0");

//虚拟机vmtotal表
create_table("vmtotal","CREATE TABLE IF NOT EXISTS `vmtotal` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `addres` varchar(256) CHARACTER SET utf8 NOT NULL,
    `nicnum` int(11) NOT NULL,
    `system` varchar(32) CHARACTER SET utf8 NOT NULL,
    `user_id` int(11) NOT NULL,
    `lesson_id` bigint(20) NOT NULL,
    `vmid` int(11) NOT NULL,
    `port` int(11) NOT NULL,
    `group_id` int(11) NOT NULL,
    `mac_id` varchar(256) NOT NULL,
    `proxy_port` varchar(256) NOT NULL,
    `manage` VARCHAR(5) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MEMORY AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;");

//虚拟机开启最大数量
create_table("vm_max_num","CREATE TABLE IF NOT EXISTS `vm_max_num` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `number` varchar(256) CHARACTER SET utf8 NOT NULL, 
    `description` varchar(128) NOT NULL COMMENT '描述' ,
    PRIMARY KEY (`id`)
  ) ENGINE=MEMORY AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT '虚拟机开启最大数量';");
  
  //趋势图
create_table("run_chart","CREATE TABLE IF NOT EXISTS `run_chart` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `cpu` smallint(100) unsigned NOT NULL COMMENT 'cpu使用率',
		  `memory` smallint(100) unsigned NOT NULL COMMENT '内存使用率',
		  `virtual_number` int(11) unsigned NOT NULL COMMENT '在线虚拟机数量',
		  `disc_1` smallint(100) unsigned NOT NULL COMMENT '磁盘1使用率',
		  `disc_2` smallint(100) unsigned NOT NULL COMMENT '磁盘2使用率',
		  `ip_location` char(100) NOT NULL COMMENT 'ip地址',
		  `online_number` int(11) unsigned NOT NULL COMMENT '在线用户数',
		  `time` char(50) NOT NULL COMMENT '时间',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='趋势图表' AUTO_INCREMENT=0 ;");

  //课程分类表
create_table("course_category","CREATE TABLE IF NOT EXISTS `course_category` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `parent_id` varchar(40) DEFAULT NULL COMMENT '上级分类id',
                `sn` varchar(64) DEFAULT NULL COMMENT '分类自动创建编号',
                `name` varchar(100) NOT NULL COMMENT '分类名',
                `code` varchar(40) DEFAULT NULL COMMENT '分类手动创建编号',
                `tree_pos` int(10) unsigned DEFAULT NULL COMMENT '显示顺序',
                `children_count` smallint(6) DEFAULT NULL,
                `auth_cat_child` enum('TRUE','FALSE') DEFAULT 'TRUE',
                `last_updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `org_id` int(11) DEFAULT '-1',
                `CourseDescription` blob NOT NULL,
                `CurriculumStandards` blob NOT NULL,
                `AssessmentCriteria` blob NOT NULL,
                `TeachingProgress` blob NOT NULL,
                `StudyGuide` blob NOT NULL,
                `TeachingGuide` blob NOT NULL,
                `InstructionalDesignEvaluation` blob NOT NULL,
                `status` int(1) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY `parent_id` (`parent_id`),
                KEY `tree_pos` (`tree_pos`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='课程分类表' AUTO_INCREMENT=0;");
